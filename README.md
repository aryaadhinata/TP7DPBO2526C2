# Sistem Ekspor Impor Pelabuhan Internasional

Aplikasi web PHP Native dengan pola OOP + PDO yang mengikuti repositori acuan `TP7DPBO2526C2`
(`config/` koneksi, `class/` model, `view/` tampilan, `index.php` sebagai front controller).

## Cara menjalankan

Kebutuhan: PHP 8.0+ dengan `pdo_mysql`, serta MySQL/MariaDB.

1. Salin folder ini ke `htdocs` (XAMPP) atau document root web server.
2. Import `database/db_port.sql` lewat phpMyAdmin (database `db_port` dibuat otomatis).
   **Skrip ini boleh diimport berulang, tetapi setiap import menghapus semua data lalu mengisi ulang data contoh.**
3. Kredensial database ada di `config/config.php` (default: `root`, tanpa password) dan dapat ditimpa
   lewat environment variable `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.
4. Buka `http://localhost/pelabuhan-ekspor-impor/`.

## Struktur

```
index.php                  front controller: sesi, routing, dan layout
style.css                  gaya antarmuka
config/config.php          konfigurasi (kredensial DB, zona waktu, jumlah data per halaman)
config/db.php              Database::getConnection() (satu koneksi PDO bersama) + DatabaseException
class/                     model, entity, controller, dan helper PHP native
class/BaseModel.php        induk model: kontrak CRUD, prepared statement, penanganan & log error
class/Ship|Cargo|Port|Shipment.php   model; mengembalikan entity, bukan array mentah
view/                      halaman tampilan serta template tabel dan pagination bersama
database/db_port.sql       skema + data contoh
```

Alur: `index.php` -> controller menyiapkan data -> view hanya menampilkan (view tidak memanggil model).
Beranda menampilkan ringkasan jumlah kapal, barang, ekspor, dan impor; tabel transaksi memakai badge status/jenis
serta dapat digeser secara horizontal pada layar kecil.

## Database

| Tabel | Keterangan |
|---|---|
| `ships` | kapal (nama, nomor IMO unik, bendera, kapasitas **TEU**) |
| `cargos` | barang (nama, kode HS unik, kategori) |
| `ports` | pelabuhan (nama + negara, unik per pasangan) |
| `shipments` | transaksi; FK ke `ships`, `cargos`, dan `ports` (asal dan tujuan); muatan dalam **ton** |

Semua tabel punya `created_at` dan `updated_at`. Foreign key memakai `ON DELETE RESTRICT`: data yang sudah
dipakai transaksi tidak bisa dihapus (aplikasi menampilkan pesan, bukan error mentah).
Nilai ENUM `type` dan `status` hanya ditulis di SQL; aplikasi membacanya dari skema database.

## Aturan bisnis (divalidasi di server)

- Ekspor: berangkat dari pelabuhan dalam negeri, tujuan luar negeri. Impor sebaliknya.
  Pelabuhan "dalam negeri" = negaranya `Indonesia` (`Port::DOMESTIC_COUNTRY`).
- Pelabuhan asal dan tujuan tidak boleh sama.
- Muatan (ton) tidak boleh melebihi kapasitas kapal: `kapasitas_TEU x 24 ton/TEU` (`ShipEntity::MAX_TON_PER_TEU`).
  Kapasitas kapal juga tidak boleh diturunkan di bawah muatan transaksi yang sudah ada.
- Transaksi bertanggal masa depan hanya boleh berstatus `Menunggu`.

## Keamanan dan penanganan error

- Semua query memakai prepared statement (`ATTR_EMULATE_PREPARES` dimatikan); output di-escape dengan `htmlspecialchars`.
- Token CSRF pada semua form tambah, ubah, dan hapus (sesi PHP, dibandingkan dengan `hash_equals`).
- Panjang dan format input divalidasi di server, bukan hanya lewat atribut HTML.
- Kegagalan database (koneksi maupun query) dicatat ke log PHP (`error_log`); pengunjung hanya melihat pesan umum.
- Pesan duplikat menyebut kolomnya (Nomor IMO, Kode HS, atau pasangan nama + negara pelabuhan).

## Batasan yang diketahui

- **Tidak ada autentikasi/otorisasi**: siapa pun yang bisa membuka aplikasi dapat mengubah dan menghapus data.
  Cukup untuk tugas praktikum, tidak untuk produksi.
- Konversi 24 ton/TEU adalah asumsi sederhana (bobot kotor maksimum kontainer 20 kaki), bukan perhitungan muat sebenarnya.
  Muatan dicek per transaksi, bukan dijumlahkan per kapal pada tanggal yang sama.
- Mengubah negara sebuah pelabuhan tidak memvalidasi ulang transaksi lama yang memakainya.
- Dropdown pada form transaksi dibatasi 500 data pertama per entitas.
- Jika database belum diimport ulang dari versi lama, kolom `origin_port` / `destination_port` (teks bebas)
  sudah diganti `origin_port_id` / `destination_port_id`: import `db_port.sql` yang baru.
