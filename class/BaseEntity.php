<?php
/**
 * Induk semua entity: properti private + getter/setter.
 * Entity hanya memegang data dan validasi format (panjang, pola, rentang).
 * Aturan yang butuh database (relasi, kapasitas, dsb.) ada di model.
 */
abstract class BaseEntity {
    private int $id = 0;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function getId(): int { return $this->id; }
    public function setId($id): static { $this->id = static::toInt($id); return $this; }

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function setCreatedAt($v): static { $this->createdAt = $v === null ? null : static::str($v); return $this; }

    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function setUpdatedAt($v): static { $this->updatedAt = $v === null ? null : static::str($v); return $this; }

    /** Membuat entity dari array (baris database ATAU data $_POST). */
    abstract public static function fromArray(array $data): static;

    /** Mengembalikan pesan kesalahan, atau string kosong jika valid. */
    abstract public function validate(): string;

    // Hanya nilai skalar, spasi pinggir dibuang
    protected static function str($value): string {
        return is_scalar($value) ? trim((string)$value) : '';
    }

    // Integer tak negatif (0 jika tidak valid)
    protected static function toInt($value): int {
        $value = static::str($value);
        return ctype_digit($value) && strlen($value) <= 18 ? (int)$value : 0;
    }

    // Panjang dihitung dalam karakter (bukan byte); tetap jalan tanpa ekstensi mbstring.
    protected static function tooLong(string $value, int $max): bool {
        $length = function_exists('mb_strlen')
            ? mb_strlen($value, 'UTF-8')
            : (preg_match_all('/./us', $value) ?: strlen($value));
        return $length > $max;
    }

    protected function hydrateAudit(array $data): static {
        return $this->setId($data['id'] ?? 0)
            ->setCreatedAt($data['created_at'] ?? null)
            ->setUpdatedAt($data['updated_at'] ?? null);
    }
}
