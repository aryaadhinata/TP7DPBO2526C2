<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Kelas induk untuk semua model.
 * Seluruh akses database wajib lewat fetchAll / fetchOne / execute,
 * sehingga semua query otomatis memakai prepared statement.
 */
abstract class BaseModel {
    protected $db;
    protected $error = '';

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    // Kontrak yang harus dimiliki setiap model
    abstract public function getAll();
    abstract public function getById($id);
    abstract public function delete($id);

    public function getError() {
        return $this->error;
    }

    protected function fetchAll($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected function fetchOne($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    // Untuk INSERT / UPDATE / DELETE. Error database diterjemahkan ke pesan yang ramah.
    protected function execute($sql, $params = []) {
        $this->error = '';
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->error = $this->translateError($e);
            return false;
        }
    }

    // Membersihkan input: hanya nilai skalar, spasi pinggir dibuang
    protected function clean($value) {
        return is_scalar($value) ? trim((string)$value) : '';
    }

    // Mengubah input menjadi id integer (0 jika tidak valid)
    protected function toId($value) {
        $value = $this->clean($value);
        return ctype_digit($value) ? (int)$value : 0;
    }

    private function translateError(PDOException $e) {
        $code = $e->errorInfo[1] ?? 0;
        switch ($code) {
            case 1062:
                return 'Data duplikat: nilai yang harus unik sudah dipakai.';
            case 1451:
                return 'Data tidak dapat dihapus karena masih dipakai oleh data lain.';
            case 1452:
                return 'Referensi kapal atau barang tidak valid.';
            default:
                return 'Terjadi kesalahan pada database.';
        }
    }
}
?>
