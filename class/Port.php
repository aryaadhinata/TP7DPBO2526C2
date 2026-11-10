<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/PortEntity.php';

/** Akses CRUD pelabuhan dan aturan pengenalan negara asal domestik. */
class Port extends BaseModel {
    /** Pelabuhan "dalam negeri" = pelabuhan dengan negara ini (dipakai aturan ekspor/impor). */
    const DOMESTIC_COUNTRY = 'Indonesia';

    protected array $uniqueLabels = ['uq_ports_name_country' => 'Pelabuhan dengan nama dan negara tersebut'];
    protected string $inUseMessage = 'Pelabuhan tidak dapat dihapus karena sudah dipakai pada transaksi.';
    protected string $notFoundMessage = 'Data pelabuhan tidak ditemukan.';

    public static function isDomestic(string $country): bool {
        return strcasecmp(trim($country), self::DOMESTIC_COUNTRY) === 0;
    }

    public function getAll(int $limit = 10, int $offset = 0): array {
        $rows = $this->fetchAll("SELECT * FROM ports ORDER BY id DESC" . $this->limitClause($limit, $offset));
        return array_map([PortEntity::class, 'fromArray'], $rows);
    }

    public function getOptions(int $limit = 500): array {
        $rows = $this->fetchAll("SELECT * FROM ports ORDER BY country, name" . $this->limitClause($limit, 0));
        return array_map([PortEntity::class, 'fromArray'], $rows);
    }

    public function countAll(): int {
        $row = $this->fetchOne("SELECT COUNT(*) AS total FROM ports");
        return $row ? (int)$row['total'] : 0;
    }

    public function getById($id): ?PortEntity {
        $row = $this->fetchOne("SELECT * FROM ports WHERE id = ?", [$this->toId($id)]);
        return $row ? PortEntity::fromArray($row) : null;
    }

    public function create($entity): bool {
        $this->assertEntity($entity, PortEntity::class);
        if (($this->error = $entity->validate()) !== '') return false;

        return $this->execute(
            "INSERT INTO ports (name, country) VALUES (?, ?)",
            [$entity->getName(), $entity->getCountry()]
        ) !== false;
    }

    public function update($entity): bool {
        $this->assertEntity($entity, PortEntity::class);
        if (($this->error = $entity->validate()) !== '') return false;

        return $this->executeExisting(
            "UPDATE ports SET name = ?, country = ? WHERE id = ?",
            [$entity->getName(), $entity->getCountry(), $entity->getId()]
        );
    }

    public function delete($id): bool {
        return $this->executeExisting("DELETE FROM ports WHERE id = ?", [$this->toId($id)]);
    }
}
