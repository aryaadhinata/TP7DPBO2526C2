<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/ShipEntity.php';

/** Akses CRUD kapal, termasuk pemeriksaan kapasitas terhadap muatan transaksi. */
class Ship extends BaseModel {
    protected array $uniqueLabels = ['imo_number' => 'Nomor IMO'];
    protected string $inUseMessage = 'Kapal tidak dapat dihapus karena sudah dipakai pada transaksi.';
    protected string $notFoundMessage = 'Data kapal tidak ditemukan.';

    public function getAll(int $limit = 10, int $offset = 0): array {
        $rows = $this->fetchAll("SELECT * FROM ships ORDER BY id DESC" . $this->limitClause($limit, $offset));
        return array_map([ShipEntity::class, 'fromArray'], $rows);
    }

    /** Daftar ringkas untuk dropdown (diurutkan per nama, dibatasi agar tetap ringan). */
    public function getOptions(int $limit = 500): array {
        $rows = $this->fetchAll("SELECT * FROM ships ORDER BY name" . $this->limitClause($limit, 0));
        return array_map([ShipEntity::class, 'fromArray'], $rows);
    }

    public function countAll(): int {
        $row = $this->fetchOne("SELECT COUNT(*) AS total FROM ships");
        return $row ? (int)$row['total'] : 0;
    }

    public function getById($id): ?ShipEntity {
        $row = $this->fetchOne("SELECT * FROM ships WHERE id = ?", [$this->toId($id)]);
        return $row ? ShipEntity::fromArray($row) : null;
    }

    public function create($entity): bool {
        $this->assertEntity($entity, ShipEntity::class);
        if (($this->error = $entity->validate()) !== '') return false;

        return $this->execute(
            "INSERT INTO ships (name, imo_number, flag_country, capacity_teu) VALUES (?, ?, ?, ?)",
            [$entity->getName(), $entity->getImoNumber(), $entity->getFlagCountry(), $entity->getCapacityTeu()]
        ) !== false;
    }

    public function update($entity): bool {
        $this->assertEntity($entity, ShipEntity::class);
        if (($this->error = $entity->validate()) !== '') return false;

        // Kapasitas baru tidak boleh lebih kecil dari muatan transaksi yang sudah ada
        $maxTon = $entity->getCapacityTeu() * ShipEntity::MAX_TON_PER_TEU;
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS total FROM shipments WHERE ship_id = ? AND quantity_ton > ?",
            [$entity->getId(), $maxTon]
        );
        if ($row && (int)$row['total'] > 0) {
            $this->error = 'Kapasitas terlalu kecil: ada transaksi kapal ini dengan muatan melebihi '
                . $maxTon . ' ton.';
            return false;
        }
        if ($this->error !== '') return false;

        return $this->executeExisting(
            "UPDATE ships SET name = ?, imo_number = ?, flag_country = ?, capacity_teu = ? WHERE id = ?",
            [$entity->getName(), $entity->getImoNumber(), $entity->getFlagCountry(), $entity->getCapacityTeu(), $entity->getId()]
        );
    }

    public function delete($id): bool {
        return $this->executeExisting("DELETE FROM ships WHERE id = ?", [$this->toId($id)]);
    }
}
