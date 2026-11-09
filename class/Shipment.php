<?php
require_once __DIR__ . '/BaseModel.php';

class Shipment extends BaseModel {
    const TYPES = ['Ekspor', 'Impor'];
    const STATUSES = ['Menunggu', 'Dalam Pelayaran', 'Tiba', 'Selesai'];

    // Relasi: shipments -> ships dan shipments -> cargos
    public function getAll() {
        return $this->fetchAll(
            "SELECT sp.*, sh.name AS ship_name, c.name AS cargo_name
            FROM shipments sp
            JOIN ships sh ON sp.ship_id = sh.id
            JOIN cargos c ON sp.cargo_id = c.id
            ORDER BY sp.shipment_date DESC, sp.id DESC"
        );
    }

    public function getById($id) {
        return $this->fetchOne("SELECT * FROM shipments WHERE id = ?", [$this->toId($id)]);
    }

    public function create($shipId, $cargoId, $type, $origin, $destination, $quantity, $date, $status) {
        $data = $this->normalize($shipId, $cargoId, $type, $origin, $destination, $quantity, $date, $status);
        if (!$this->validate($data)) {
            return false;
        }
        return $this->execute(
            "INSERT INTO shipments (ship_id, cargo_id, type, origin_port, destination_port, quantity_ton, shipment_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['ship_id'], $data['cargo_id'], $data['type'], $data['origin'],
                $data['destination'], $data['quantity'], $data['date'], $data['status']
            ]
        );
    }

    public function update($id, $shipId, $cargoId, $type, $origin, $destination, $quantity, $date, $status) {
        $id = $this->toId($id);
        $data = $this->normalize($shipId, $cargoId, $type, $origin, $destination, $quantity, $date, $status);
        if (!$this->validate($data)) {
            return false;
        }
        if ($this->getById($id) === null) {
            $this->error = 'Data transaksi tidak ditemukan.';
            return false;
        }
        return $this->execute(
            "UPDATE shipments
            SET ship_id = ?, cargo_id = ?, type = ?, origin_port = ?, destination_port = ?,
                quantity_ton = ?, shipment_date = ?, status = ?
            WHERE id = ?",
            [
                $data['ship_id'], $data['cargo_id'], $data['type'], $data['origin'],
                $data['destination'], $data['quantity'], $data['date'], $data['status'], $id
            ]
        );
    }

    public function delete($id) {
        $id = $this->toId($id);
        if ($this->getById($id) === null) {
            $this->error = 'Data transaksi tidak ditemukan.';
            return false;
        }
        return $this->execute("DELETE FROM shipments WHERE id = ?", [$id]);
    }

    private function normalize($shipId, $cargoId, $type, $origin, $destination, $quantity, $date, $status) {
        return [
            'ship_id'     => $this->toId($shipId),
            'cargo_id'    => $this->toId($cargoId),
            'type'        => $this->clean($type),
            'origin'      => $this->clean($origin),
            'destination' => $this->clean($destination),
            'quantity'    => $this->clean($quantity),
            'date'        => $this->clean($date),
            'status'      => $this->clean($status),
        ];
    }

    private function validate($d) {
        $this->error = '';
        if ($d['ship_id'] <= 0 || $d['cargo_id'] <= 0) {
            $this->error = 'Kapal dan barang wajib dipilih.';
            return false;
        }
        if (!in_array($d['type'], self::TYPES, true)) {
            $this->error = 'Jenis transaksi harus Ekspor atau Impor.';
            return false;
        }
        if (!in_array($d['status'], self::STATUSES, true)) {
            $this->error = 'Status pengiriman tidak valid.';
            return false;
        }
        if ($d['origin'] === '' || $d['destination'] === '') {
            $this->error = 'Pelabuhan asal dan tujuan wajib diisi.';
            return false;
        }
        if (strcasecmp($d['origin'], $d['destination']) === 0) {
            $this->error = 'Pelabuhan asal dan tujuan tidak boleh sama.';
            return false;
        }
        if (!is_numeric($d['quantity']) || (float)$d['quantity'] <= 0 || (float)$d['quantity'] > 99999999.99) {
            $this->error = 'Jumlah muatan (ton) harus angka lebih dari 0.';
            return false;
        }
        $date = DateTime::createFromFormat('Y-m-d', $d['date']);
        if (!$date || $date->format('Y-m-d') !== $d['date']) {
            $this->error = 'Tanggal pengiriman tidak valid.';
            return false;
        }
        return true;
    }
}
?>
