<?php
/** Exception khusus agar index.php bisa menampilkan pesan umum tanpa membocorkan detail. */
class DatabaseException extends RuntimeException {}

/**
 * Koneksi PDO tunggal (satu koneksi dipakai bersama oleh semua model dalam satu request).
 * Kredensial dibaca dari config/config.php, bukan ditulis di dalam kelas.
 */
class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $cfg = (require __DIR__ . '/config.php')['db'];
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']
            );
            try {
                self::$instance = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Prepared statement asli di sisi server MySQL (bukan emulasi PDO)
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    // rowCount() UPDATE = baris yang cocok (bukan hanya yang berubah),
                    // sehingga "data tidak ditemukan" bisa dideteksi tanpa query tambahan.
                    PDO::MYSQL_ATTR_FOUND_ROWS   => true,
                ]);
            } catch (PDOException $e) {
                // Detail (host, nama database) hanya masuk log server, tidak ke browser.
                error_log('[db] Koneksi gagal: ' . $e->getMessage());
                throw new DatabaseException('Tidak dapat terhubung ke database.', 0, $e);
            }
        }
        return self::$instance;
    }
}
