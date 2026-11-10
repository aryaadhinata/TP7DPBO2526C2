<?php
require_once __DIR__ . '/BaseEntity.php';

/** Data kapal dan validasi format; aturan muatan transaksi berada di model Ship. */
class ShipEntity extends BaseEntity {
    const MAX_CAPACITY_TEU = 100000;
    /** Asumsi konversi satuan: kapasitas dalam TEU, muatan dalam ton (maks. bobot kotor per TEU). */
    const MAX_TON_PER_TEU = 24;

    private string $name = '';
    private string $imoNumber = '';
    private string $flagCountry = '';
    private int $capacityTeu = 0;

    public function getName(): string { return $this->name; }
    public function setName($v): static { $this->name = static::str($v); return $this; }

    public function getImoNumber(): string { return $this->imoNumber; }
    public function setImoNumber($v): static { $this->imoNumber = static::str($v); return $this; }

    public function getFlagCountry(): string { return $this->flagCountry; }
    public function setFlagCountry($v): static { $this->flagCountry = static::str($v); return $this; }

    public function getCapacityTeu(): int { return $this->capacityTeu; }
    public function setCapacityTeu($v): static { $this->capacityTeu = static::toInt($v); return $this; }

    public static function fromArray(array $data): static {
        $e = new static();
        $e->hydrateAudit($data);
        return $e->setName($data['name'] ?? '')
            ->setImoNumber($data['imo_number'] ?? '')
            ->setFlagCountry($data['flag_country'] ?? '')
            ->setCapacityTeu($data['capacity_teu'] ?? 0);
    }

    public function validate(): string {
        if ($this->name === '' || $this->flagCountry === '') {
            return 'Nama kapal dan negara bendera wajib diisi.';
        }
        if (static::tooLong($this->name, 100)) return 'Nama kapal maksimal 100 karakter.';
        if (static::tooLong($this->flagCountry, 60)) return 'Negara bendera maksimal 60 karakter.';
        if (!preg_match('/^\d{7}$/', $this->imoNumber)) {
            return 'Nomor IMO harus terdiri dari 7 digit angka.';
        }
        if ($this->capacityTeu < 1 || $this->capacityTeu > self::MAX_CAPACITY_TEU) {
            return 'Kapasitas (TEU) harus bilangan bulat antara 1 dan ' . self::MAX_CAPACITY_TEU . '.';
        }
        return '';
    }
}
