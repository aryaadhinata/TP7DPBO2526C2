<?php
/**
 * Konfigurasi aplikasi. Nilai dapat ditimpa lewat environment variable
 * (mis. DB_HOST, DB_USER, DB_PASS) sehingga kredensial tidak perlu
 * diubah di dalam kode / di-commit ke repositori.
 */
return [
    'db' => [
        'host'    => getenv('DB_HOST') ?: 'localhost',
        'port'    => getenv('DB_PORT') ?: '3306',
        'name'    => getenv('DB_NAME') ?: 'db_port',
        'user'    => getenv('DB_USER') ?: 'root',
        'pass'    => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
        'charset' => 'utf8mb4',
    ],
    'timezone' => 'Asia/Jakarta',
    'per_page' => 10,
];
