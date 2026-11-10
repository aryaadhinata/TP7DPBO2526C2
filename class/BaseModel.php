<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/BaseEntity.php';

/**
 * Kelas induk untuk semua model.
 * Seluruh akses database lewat fetchAll / fetchOne / execute (selalu prepared statement).
 * Koneksi dipakai bersama (Database::getConnection), bukan dibuat per model.
 */
abstract class BaseModel {
    protected PDO $db;
    protected string $error = '';

    /** kolom/nama indeks unik => label untuk pesan duplikat, mis. ['imo_number' => 'Nomor IMO'] */
    protected array $uniqueLabels = [];
    protected string $inUseMessage = 'Data tidak dapat dihapus karena masih dipakai oleh data lain.';
    protected string $notFoundMessage = 'Data tidak ditemukan.';

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getConnection();
    }

    // Kontrak yang harus dimiliki setiap model (entity = turunan BaseEntity)
    abstract public function getAll(int $limit = 10, int $offset = 0): array;
    abstract public function countAll(): int;
    abstract public function getById($id);
    abstract public function create($entity): bool;
    abstract public function update($entity): bool;
    abstract public function delete($id): bool;

    public function getError(): string {
        return $this->error;
    }

    protected function fetchAll(string $sql, array $params = []): array {
        $this->error = '';
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->fail($e, 'Gagal membaca data dari database.');
            return [];
        }
    }

    protected function fetchOne(string $sql, array $params = []): ?array {
        $this->error = '';
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch();
            return $row === false ? null : $row;
        } catch (PDOException $e) {
            $this->fail($e, 'Gagal membaca data dari database.');
            return null;
        }
    }

    /** Untuk INSERT / UPDATE / DELETE. Mengembalikan jumlah baris terdampak, atau false jika gagal. */
    protected function execute(string $sql, array $params = []) {
        $this->error = '';
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->fail($e, $this->translateError($e));
            return false;
        }
    }

    /** Menjalankan INSERT/UPDATE/DELETE dan memetakan "0 baris" menjadi "tidak ditemukan". */
    protected function executeExisting(string $sql, array $params): bool {
        $affected = $this->execute($sql, $params);
        if ($affected === false) return false;
        if ($affected === 0) {
            $this->error = $this->notFoundMessage;
            return false;
        }
        return true;
    }

    protected function toId($value): int {
        $value = is_scalar($value) ? trim((string)$value) : '';
        return ctype_digit($value) && strlen($value) <= 18 ? (int)$value : 0;
    }

    // LIMIT/OFFSET di-cast ke integer lalu ditulis langsung (aman, bukan input mentah)
    protected function limitClause(int $limit, int $offset): string {
        return ' LIMIT ' . max(1, $limit) . ' OFFSET ' . max(0, $offset);
    }

    protected function assertEntity($entity, string $class): void {
        if (!$entity instanceof $class) {
            throw new InvalidArgumentException(static::class . ' membutuhkan objek ' . $class);
        }
    }

    // Semua kegagalan DB dicatat ke log; pengguna hanya melihat pesan ramah.
    private function fail(PDOException $e, string $message): void {
        error_log(sprintf('[db] %s: [%s] %s', static::class, $e->errorInfo[1] ?? $e->getCode(), $e->getMessage()));
        $this->error = $message;
    }

    private function translateError(PDOException $e): string {
        $code = $e->errorInfo[1] ?? 0;
        switch ($code) {
            case 1062:
                // Pesan MySQL menyebut nama indeks/kolom yang bentrok
                foreach ($this->uniqueLabels as $key => $label) {
                    if (str_contains($e->getMessage(), "'" . $key . "'") || str_contains($e->getMessage(), '.' . $key . "'")) {
                        return $label . ' sudah terdaftar.';
                    }
                }
                return 'Data duplikat: nilai yang harus unik sudah dipakai.';
            case 1451:
                return $this->inUseMessage;
            case 1452:
                return 'Data referensi (kapal, barang, atau pelabuhan) tidak valid.';
            case 1406:
                return 'Salah satu isian terlalu panjang.';
            default:
                return 'Terjadi kesalahan pada database.';
        }
    }
}
