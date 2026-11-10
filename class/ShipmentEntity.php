<?php
require_once __DIR__ . '/BaseEntity.php';

class ShipmentEntity extends BaseEntity {
    private int $shipId = 0;
    private int $cargoId = 0;
    private string $type = '';
    private int $originPortId = 0;
    private int $destinationPortId = 0;
    private string $quantityTon = '';
    private string $shipmentDate = '';
    private string $status = '';

    // Kolom tampilan hasil JOIN (hanya terisi saat membaca daftar)
    private string $shipName = '';
    private string $cargoName = '';
    private string $originPortLabel = '';
    private string $destinationPortLabel = '';

    public function getShipId(): int { return $this->shipId; }
    public function setShipId($v): static { $this->shipId = static::toInt($v); return $this; }

    public function getCargoId(): int { return $this->cargoId; }
    public function setCargoId($v): static { $this->cargoId = static::toInt($v); return $this; }

    public function getType(): string { return $this->type; }
    public function setType($v): static { $this->type = static::str($v); return $this; }

    public function getOriginPortId(): int { return $this->originPortId; }
    public function setOriginPortId($v): static { $this->originPortId = static::toInt($v); return $this; }

    public function getDestinationPortId(): int { return $this->destinationPortId; }
    public function setDestinationPortId($v): static { $this->destinationPortId = static::toInt($v); return $this; }

    public function getQuantityTon(): string { return $this->quantityTon; }
    public function setQuantityTon($v): static { $this->quantityTon = static::str($v); return $this; }

    public function getShipmentDate(): string { return $this->shipmentDate; }
    public function setShipmentDate($v): static { $this->shipmentDate = static::str($v); return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus($v): static { $this->status = static::str($v); return $this; }

    public function getShipName(): string { return $this->shipName; }
    public function setShipName($v): static { $this->shipName = static::str($v); return $this; }

    public function getCargoName(): string { return $this->cargoName; }
    public function setCargoName($v): static { $this->cargoName = static::str($v); return $this; }

    public function getOriginPortLabel(): string { return $this->originPortLabel; }
    public function setOriginPortLabel($v): static { $this->originPortLabel = static::str($v); return $this; }

    public function getDestinationPortLabel(): string { return $this->destinationPortLabel; }
    public function setDestinationPortLabel($v): static { $this->destinationPortLabel = static::str($v); return $this; }

    public static function fromArray(array $data): static {
        $e = new static();
        $e->hydrateAudit($data);
        return $e->setShipId($data['ship_id'] ?? 0)
            ->setCargoId($data['cargo_id'] ?? 0)
            ->setType($data['type'] ?? '')
            ->setOriginPortId($data['origin_port_id'] ?? 0)
            ->setDestinationPortId($data['destination_port_id'] ?? 0)
            ->setQuantityTon($data['quantity_ton'] ?? '')
            ->setShipmentDate($data['shipment_date'] ?? '')
            ->setStatus($data['status'] ?? '')
            ->setShipName($data['ship_name'] ?? '')
            ->setCargoName($data['cargo_name'] ?? '')
            ->setOriginPortLabel($data['origin_port_label'] ?? '')
            ->setDestinationPortLabel($data['destination_port_label'] ?? '');
    }

    /** Validasi format. Nilai ENUM dan aturan bisnis dicek di model Shipment. */
    public function validate(): string {
        if ($this->shipId <= 0 || $this->cargoId <= 0) {
            return 'Kapal dan barang wajib dipilih.';
        }
        if ($this->originPortId <= 0 || $this->destinationPortId <= 0) {
            return 'Pelabuhan asal dan tujuan wajib dipilih.';
        }
        if ($this->originPortId === $this->destinationPortId) {
            return 'Pelabuhan asal dan tujuan tidak boleh sama.';
        }
        // DECIMAL(10,2): maksimal 8 digit bulat + 2 desimal
        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $this->quantityTon) || (float)$this->quantityTon <= 0) {
            return 'Jumlah muatan (ton) harus angka lebih dari 0 (maks. 8 digit, 2 desimal).';
        }
        $date = DateTime::createFromFormat('Y-m-d', $this->shipmentDate);
        if (!$date || $date->format('Y-m-d') !== $this->shipmentDate) {
            return 'Tanggal pengiriman tidak valid.';
        }
        return '';
    }
}
