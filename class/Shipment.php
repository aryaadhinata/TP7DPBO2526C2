<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/Port.php';
require_once __DIR__ . '/ShipmentEntity.php';
require_once __DIR__ . '/ShipEntity.php';

class Shipment extends BaseModel {
    const TYPE_EXPORT = 'Ekspor';
    const TYPE_IMPORT = 'Impor';
    const STATUS_PENDING = 'Menunggu';

    protected string $notFoundMessage = 'Data transaksi tidak ditemukan.';

    // Cache nilai ENUM per proses; sumber kebenarannya adalah skema database.
    private static array $enums = [];

    /** Nilai ENUM kolom `type` / `status`, dibaca dari skema agar tidak ditulis dua kali. */
    public function enumValues(string $column): array {
        if (!isset(self::$enums[$column])) {
            $rows = $this->fetchAll(
                "SELECT COLUMN_NAME, COLUMN_TYPE FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'shipments' AND COLUMN_NAME IN ('type', 'status')"
            );
            foreach ($rows as $r) {
                if (preg_match_all("/'((?:[^']|'')*)'/", $r['COLUMN_TYPE'], $m)) {
                    self::$enums[$r['COLUMN_NAME']] = array_map(fn($v) => str_replace("''", "'", $v), $m[1]);
                }
            }
        }
        return self::$enums[$column] ?? [];
    }

    // Relasi: shipments -> ships, cargos, dan ports (asal + tujuan)
    public function getAll(int $limit = 10, int $offset = 0): array {
        $rows = $this->fetchAll(
            "SELECT sp.*, sh.name AS ship_name, c.name AS cargo_name,
                    CONCAT(po.name, ', ', po.country) AS origin_port_label,
                    CONCAT(pd.name, ', ', pd.country) AS destination_port_label
            FROM shipments sp
            JOIN ships sh ON sp.ship_id = sh.id
            JOIN cargos c ON sp.cargo_id = c.id
            JOIN ports po ON sp.origin_port_id = po.id
            JOIN ports pd ON sp.destination_port_id = pd.id
            ORDER BY sp.shipment_date DESC, sp.id DESC" . $this->limitClause($limit, $offset)
        );
        return array_map([ShipmentEntity::class, 'fromArray'], $rows);
    }

    public function countAll(): int {
        $row = $this->fetchOne("SELECT COUNT(*) AS total FROM shipments");
        return $row ? (int)$row['total'] : 0;
    }

    public function countByType(string $type): int {
        if (!in_array($type, [self::TYPE_EXPORT, self::TYPE_IMPORT], true)) {
            return 0;
        }
        $row = $this->fetchOne("SELECT COUNT(*) AS total FROM shipments WHERE type = ?", [$type]);
        return $row ? (int)$row['total'] : 0;
    }

    public function getById($id): ?ShipmentEntity {
        $row = $this->fetchOne("SELECT * FROM shipments WHERE id = ?", [$this->toId($id)]);
        return $row ? ShipmentEntity::fromArray($row) : null;
    }

    public function create($entity): bool {
        $this->assertEntity($entity, ShipmentEntity::class);
        if (!$this->validateAll($entity)) return false;

        return $this->execute(
            "INSERT INTO shipments (ship_id, cargo_id, type, origin_port_id, destination_port_id, quantity_ton, shipment_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            $this->params($entity)
        ) !== false;
    }

    public function update($entity): bool {
        $this->assertEntity($entity, ShipmentEntity::class);
        if (!$this->validateAll($entity)) return false;

        return $this->executeExisting(
            "UPDATE shipments
            SET ship_id = ?, cargo_id = ?, type = ?, origin_port_id = ?, destination_port_id = ?,
                quantity_ton = ?, shipment_date = ?, status = ?
            WHERE id = ?",
            array_merge($this->params($entity), [$entity->getId()])
        );
    }

    public function delete($id): bool {
        return $this->executeExisting("DELETE FROM shipments WHERE id = ?", [$this->toId($id)]);
    }

    private function params(ShipmentEntity $e): array {
        return [
            $e->getShipId(), $e->getCargoId(), $e->getType(), $e->getOriginPortId(),
            $e->getDestinationPortId(), $e->getQuantityTon(), $e->getShipmentDate(), $e->getStatus(),
        ];
    }

    /** Validasi format (entity) + nilai ENUM + aturan bisnis yang butuh data dari database. */
    private function validateAll(ShipmentEntity $e): bool {
        if (($this->error = $e->validate()) !== '') return false;

        if (!in_array($e->getType(), $this->enumValues('type'), true)) {
            // pesan galat database (jika enumValues gagal) tetap dipertahankan
            $this->error = $this->error ?: 'Jenis transaksi tidak valid.';
            return false;
        }
        if (!in_array($e->getStatus(), $this->enumValues('status'), true)) {
            $this->error = $this->error ?: 'Status pengiriman tidak valid.';
            return false;
        }

        $ship  = $this->fetchOne("SELECT capacity_teu FROM ships WHERE id = ?", [$e->getShipId()]);
        $cargo = $this->fetchOne("SELECT id FROM cargos WHERE id = ?", [$e->getCargoId()]);
        $origin = $this->fetchOne("SELECT country FROM ports WHERE id = ?", [$e->getOriginPortId()]);
        $dest   = $this->fetchOne("SELECT country FROM ports WHERE id = ?", [$e->getDestinationPortId()]);
        if ($this->error !== '') return false; // kegagalan database pada salah satu SELECT di atas

        if (!$ship)   return $this->reject('Kapal yang dipilih tidak ditemukan.');
        if (!$cargo)  return $this->reject('Barang yang dipilih tidak ditemukan.');
        if (!$origin || !$dest) return $this->reject('Pelabuhan yang dipilih tidak ditemukan.');

        // Aturan ekspor/impor terhadap pelabuhan dalam negeri
        $originLocal = Port::isDomestic($origin['country']);
        $destLocal   = Port::isDomestic($dest['country']);
        $domestic    = Port::DOMESTIC_COUNTRY;
        if ($e->getType() === self::TYPE_EXPORT && !($originLocal && !$destLocal)) {
            return $this->reject("Ekspor harus berangkat dari pelabuhan $domestic dan menuju pelabuhan luar negeri.");
        }
        if ($e->getType() === self::TYPE_IMPORT && !(!$originLocal && $destLocal)) {
            return $this->reject("Impor harus berangkat dari pelabuhan luar negeri dan tiba di pelabuhan $domestic.");
        }

        // Satuan: kapasitas kapal (TEU) vs muatan (ton)
        $maxTon = (int)$ship['capacity_teu'] * ShipEntity::MAX_TON_PER_TEU;
        if ((float)$e->getQuantityTon() > $maxTon) {
            return $this->reject("Muatan melebihi daya angkut kapal (maks. $maxTon ton = {$ship['capacity_teu']} TEU × "
                . ShipEntity::MAX_TON_PER_TEU . ' ton/TEU).');
        }

        // Tanggal harus cocok dengan status: pengiriman yang sudah berlayar/tiba/selesai tidak boleh berdatum masa depan
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        if ($e->getStatus() !== self::STATUS_PENDING && $e->getShipmentDate() > $today) {
            return $this->reject('Tanggal pengiriman di masa depan hanya boleh berstatus "' . self::STATUS_PENDING . '".');
        }
        return true;
    }

    private function reject(string $message): bool {
        $this->error = $message;
        return false;
    }
}
