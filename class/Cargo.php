<?php
require_once __DIR__ . '/BaseModel.php';

class Cargo extends BaseModel {

    public function getAll() {
        return $this->fetchAll("SELECT * FROM cargos ORDER BY id DESC");
    }

    public function getById($id) {
        return $this->fetchOne("SELECT * FROM cargos WHERE id = ?", [$this->toId($id)]);
    }

    public function create($name, $hsCode, $category) {
        $name = $this->clean($name);
        $hsCode = $this->clean($hsCode);
        $category = $this->clean($category);

        if (!$this->validate($name, $hsCode, $category)) {
            return false;
        }
        return $this->execute(
            "INSERT INTO cargos (name, hs_code, category) VALUES (?, ?, ?)",
            [$name, $hsCode, $category]
        );
    }

    public function update($id, $name, $hsCode, $category) {
        $id = $this->toId($id);
        $name = $this->clean($name);
        $hsCode = $this->clean($hsCode);
        $category = $this->clean($category);

        if (!$this->validate($name, $hsCode, $category)) {
            return false;
        }
        if ($this->getById($id) === null) {
            $this->error = 'Data barang tidak ditemukan.';
            return false;
        }
        return $this->execute(
            "UPDATE cargos SET name = ?, hs_code = ?, category = ? WHERE id = ?",
            [$name, $hsCode, $category, $id]
        );
    }

    public function delete($id) {
        $id = $this->toId($id);
        if ($this->getById($id) === null) {
            $this->error = 'Data barang tidak ditemukan.';
            return false;
        }
        return $this->execute("DELETE FROM cargos WHERE id = ?", [$id]);
    }

    private function validate($name, $hsCode, $category) {
        $this->error = '';
        if ($name === '' || $category === '') {
            $this->error = 'Nama barang dan kategori wajib diisi.';
            return false;
        }
        if (!preg_match('/^\d{6,10}$/', $hsCode)) {
            $this->error = 'Kode HS harus terdiri dari 6 sampai 10 digit angka.';
            return false;
        }
        return true;
    }
}
?>
