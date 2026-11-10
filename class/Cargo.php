<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/CargoEntity.php';

/** Akses CRUD barang; data hasil query dipetakan menjadi CargoEntity. */
class Cargo extends BaseModel {
    protected array $uniqueLabels = ['hs_code' => 'Kode HS'];
    protected string $inUseMessage = 'Barang tidak dapat dihapus karena sudah dipakai pada transaksi.';
    protected string $notFoundMessage = 'Data barang tidak ditemukan.';

    public function getAll(int $limit = 10, int $offset = 0): array {
        $rows = $this->fetchAll("SELECT * FROM cargos ORDER BY id DESC" . $this->limitClause($limit, $offset));
        return array_map([CargoEntity::class, 'fromArray'], $rows);
    }

    public function getOptions(int $limit = 500): array {
        $rows = $this->fetchAll("SELECT * FROM cargos ORDER BY name" . $this->limitClause($limit, 0));
        return array_map([CargoEntity::class, 'fromArray'], $rows);
    }

    public function countAll(): int {
        $row = $this->fetchOne("SELECT COUNT(*) AS total FROM cargos");
        return $row ? (int)$row['total'] : 0;
    }

    public function getById($id): ?CargoEntity {
        $row = $this->fetchOne("SELECT * FROM cargos WHERE id = ?", [$this->toId($id)]);
        return $row ? CargoEntity::fromArray($row) : null;
    }

    public function create($entity): bool {
        $this->assertEntity($entity, CargoEntity::class);
        if (($this->error = $entity->validate()) !== '') return false;

        return $this->execute(
            "INSERT INTO cargos (name, hs_code, category) VALUES (?, ?, ?)",
            [$entity->getName(), $entity->getHsCode(), $entity->getCategory()]
        ) !== false;
    }

    public function update($entity): bool {
        $this->assertEntity($entity, CargoEntity::class);
        if (($this->error = $entity->validate()) !== '') return false;

        return $this->executeExisting(
            "UPDATE cargos SET name = ?, hs_code = ?, category = ? WHERE id = ?",
            [$entity->getName(), $entity->getHsCode(), $entity->getCategory(), $entity->getId()]
        );
    }

    public function delete($id): bool {
        return $this->executeExisting("DELETE FROM cargos WHERE id = ?", [$this->toId($id)]);
    }
}
