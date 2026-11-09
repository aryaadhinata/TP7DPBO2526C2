# Sistem Ekspor Impor Pelabuhan Internasional

Aplikasi web PHP Native dengan pola OOP yang mengikuti repositori acuan `TP7DPBO2526C2`
(`config/` untuk koneksi, `class/` untuk model, `view/` untuk tampilan, `index.php` sebagai router).

## Cara menjalankan

1. Salin folder ini ke `htdocs` (XAMPP) atau document root web server.
2. Import `database/db_port.sql` lewat phpMyAdmin (database `db_port` dibuat otomatis).
3. Sesuaikan kredensial di `config/db.php` bila perlu (default: user `root`, password kosong).
4. Buka `http://localhost/pelabuhan-ekspor-impor/`.

## Struktur

```
index.php            router + handler aksi (create/update/delete)
config/db.php        class Database (koneksi PDO)
class/BaseModel.php  kelas induk abstrak: helper prepared statement + penanganan error
class/Ship.php       model kapal
class/Cargo.php      model barang
class/Shipment.php   model transaksi ekspor/impor (relasi ke Ship dan Cargo)
view/                header, footer, home, ships, cargos, shipments
database/db_port.sql skema + data contoh
```

## Database

| Tabel | Keterangan |
|---|---|
| `ships` | kapal (nama, nomor IMO unik, negara bendera, kapasitas TEU) |
| `cargos` | barang (nama, kode HS unik, kategori) |
| `shipments` | transaksi; `ship_id` FK ke `ships`, `cargo_id` FK ke `cargos` |

Foreign key memakai `ON DELETE RESTRICT`, jadi kapal atau barang yang sudah dipakai
di transaksi tidak bisa dihapus (aplikasi menampilkan pesan, bukan error mentah).

## Ketentuan yang dipenuhi

- CRUD lengkap untuk ketiga entitas.
- Semua query memakai prepared statement: seluruh akses database lewat `fetchAll`,
  `fetchOne`, dan `execute` di `BaseModel` (semuanya memanggil `prepare()` lalu `execute()`).
  Tidak ada `query()` langsung, dan `PDO::ATTR_EMULATE_PREPARES` dimatikan.
- Validasi input di sisi server pada tiap model, output di-escape dengan `htmlspecialchars`.
- Hapus data lewat POST (bukan link GET), dan pola Post/Redirect/Get setelah aksi berhasil.
