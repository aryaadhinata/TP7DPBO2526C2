<?php
require_once __DIR__ . '/BaseEntity.php';

class CargoEntity extends BaseEntity {
    private string $name = '';
    private string $hsCode = '';
    private string $category = '';

    public function getName(): string { return $this->name; }
    public function setName($v): static { $this->name = static::str($v); return $this; }

    public function getHsCode(): string { return $this->hsCode; }
    public function setHsCode($v): static { $this->hsCode = static::str($v); return $this; }

    public function getCategory(): string { return $this->category; }
    public function setCategory($v): static { $this->category = static::str($v); return $this; }

    public static function fromArray(array $data): static {
        $e = new static();
        $e->hydrateAudit($data);
        return $e->setName($data['name'] ?? '')
            ->setHsCode($data['hs_code'] ?? '')
            ->setCategory($data['category'] ?? '');
    }

    public function validate(): string {
        if ($this->name === '' || $this->category === '') {
            return 'Nama barang dan kategori wajib diisi.';
        }
        if (static::tooLong($this->name, 100)) return 'Nama barang maksimal 100 karakter.';
        if (static::tooLong($this->category, 50)) return 'Kategori maksimal 50 karakter.';
        if (!preg_match('/^\d{6,10}$/', $this->hsCode)) {
            return 'Kode HS harus terdiri dari 6 sampai 10 digit angka.';
        }
        return '';
    }
}
