<?php
require_once __DIR__ . '/BaseEntity.php';

class PortEntity extends BaseEntity {
    private string $name = '';
    private string $country = '';

    public function getName(): string { return $this->name; }
    public function setName($v): static { $this->name = static::str($v); return $this; }

    public function getCountry(): string { return $this->country; }
    public function setCountry($v): static { $this->country = static::str($v); return $this; }

    public function getLabel(): string { return $this->name . ', ' . $this->country; }

    public static function fromArray(array $data): static {
        $e = new static();
        $e->hydrateAudit($data);
        return $e->setName($data['name'] ?? '')->setCountry($data['country'] ?? '');
    }

    public function validate(): string {
        if ($this->name === '' || $this->country === '') {
            return 'Nama pelabuhan dan negara wajib diisi.';
        }
        if (static::tooLong($this->name, 100)) return 'Nama pelabuhan maksimal 100 karakter.';
        if (static::tooLong($this->country, 60)) return 'Negara maksimal 60 karakter.';
        return '';
    }
}
