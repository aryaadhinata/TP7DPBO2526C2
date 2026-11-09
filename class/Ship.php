<?php
require_once __DIR__ . '/BaseModel.php';

class Ship extends BaseModel {

    public function getAll() {
        return $this->fetchAll("SELECT * FROM ships ORDER BY id DESC");
    }

    public function getById($id) {
        return $this->fetchOne("SELECT * FROM ships WHERE id = ?", [$this->toId($id)]);
    }

    public function create($name, $imo, $flag, $capacity) {
        $name = $this->clean($name);
        $imo = $this->clean($imo);
        $flag = $this->clean($flag);
        $capacity = $this->clean($capacity);

        if (!$this->validate($name, $imo, $flag, $capacity)) {
            return false;
        }
        return $this->execute(
            "INSERT INTO ships (name, imo_number, flag_country, capacity_teu) VALUES (?, ?, ?, ?)",
            [$name, $imo, $flag, (int)$capacity]
        );
    }

    public function update($id, $name, $imo, $flag, $capacity) {
        $id = $this->toId($id);
        $name = $this->clean($name);
        $imo = $this->clean($imo);
        $flag = $this->clean($flag);
        $capacity = $this->clean($capacity);

        if (!$this->validate($name, $imo, $flag, $capacity)) {
            return false;
        }
        if ($this->getById($id) === null) {
            $this->error = 'Data kapal tidak ditemukan.';
            return false;
        }
        return $this->execute(
            "UPDATE ships SET name = ?, imo_number = ?, flag_country = ?, capacity_teu = ? WHERE id = ?",
            [$name, $imo, $flag, (int)$capacity, $id]
        );
    }

    public function delete($id) {
        $id = $this->toId($id);
        if ($this->getById($id) === null) {
            $this->error = 'Data kapal tidak ditemukan.';
            return false;
        }
        return $this->execute("DELETE FROM ships WHERE id = ?", [$id]);
    }

    private function validate($name, $imo, $flag, $capacity) {
        $this->error = '';
        if ($name === '' || $flag === '') {
            $this->error = 'Nama kapal dan negara bendera wajib diisi.';
            return false;
        }
        if (!preg_match('/^\d{7}$/', $imo)) {
            $this->error = 'Nomor IMO harus terdiri dari 7 digit angka.';
            return false;
        }
        if (!ctype_digit($capacity) || (int)$capacity <= 0) {
            $this->error = 'Kapasitas (TEU) harus bilangan bulat positif.';
            return false;
        }
        return true;
    }
}
?>
