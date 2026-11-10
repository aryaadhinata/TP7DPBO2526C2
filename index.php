<?php
// Front controller: memilih controller, menjalankannya, lalu menampilkan layout + view.
require_once __DIR__ . '/class/Helpers.php';
require_once __DIR__ . '/class/Controllers.php';

$config = require __DIR__ . '/config/config.php';
date_default_timezone_set($config['timezone']);

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

$routes = [
    'ships'     => ShipController::class,
    'cargos'    => CargoController::class,
    'ports'     => PortController::class,
    'shipments' => ShipmentController::class,
];

$page = is_string($_GET['page'] ?? null) && isset($routes[$_GET['page']]) ? $_GET['page'] : 'home';

$flashMessages = [
    'created' => 'Data berhasil ditambahkan.',
    'updated' => 'Data berhasil diperbarui.',
    'deleted' => 'Data berhasil dihapus.',
];
$msgKey = is_string($_GET['msg'] ?? null) ? $_GET['msg'] : '';
$flash  = $flashMessages[$msgKey] ?? '';

$viewFile = 'home';
$data = [];
try {
    if ($page === 'home') {
        require_once __DIR__ . '/class/Ship.php';
        require_once __DIR__ . '/class/Cargo.php';
        require_once __DIR__ . '/class/Shipment.php';
        $shipments = new Shipment();
        $data['summary'] = [
            'ships' => (new Ship())->countAll(),
            'cargos' => (new Cargo())->countAll(),
            'exports' => $shipments->countByType(Shipment::TYPE_EXPORT),
            'imports' => $shipments->countByType(Shipment::TYPE_IMPORT),
        ];
    } else {
        $controller = new $routes[$page]();
        $data = $controller->handle();
        $viewFile = $controller->viewFile();
    }
} catch (Throwable $ex) {
    // Detail hanya ke log server; pengunjung melihat pesan umum.
    error_log('[app] ' . get_class($ex) . ': ' . $ex->getMessage() . ' @ ' . $ex->getFile() . ':' . $ex->getLine());
    http_response_code(500);
    $viewFile = 'error';
    $data = ['fatalMessage' => $ex instanceof DatabaseException
        ? 'Layanan database sedang tidak tersedia.'
        : 'Terjadi kesalahan pada server.'];
    $flash = '';
}
$error = $data['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kelola data kapal, barang, pelabuhan, dan transaksi ekspor-impor dalam satu aplikasi.">
    <link rel="icon" href="icon.png" type="image/png" sizes="32x32">
    <title>Sistem Ekspor Impor Pelabuhan Internasional</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a class="brand" href="index.php" aria-label="PortExim, Beranda">PortExim</a>
        <ul>
            <li><a href="index.php"<?= $page === 'home' ? ' aria-current="page"' : '' ?>>Beranda</a></li>
            <li><a href="?page=ships"<?= $page === 'ships' ? ' aria-current="page"' : '' ?>>Kapal</a></li>
            <li><a href="?page=cargos"<?= $page === 'cargos' ? ' aria-current="page"' : '' ?>>Barang</a></li>
            <li><a href="?page=ports"<?= $page === 'ports' ? ' aria-current="page"' : '' ?>>Pelabuhan</a></li>
            <li><a href="?page=shipments"<?= $page === 'shipments' ? ' aria-current="page"' : '' ?>>Transaksi Ekspor/Impor</a></li>
        </ul>
    </nav>
    <?php if ($page === 'home' && $viewFile === 'home'): ?>
        <?php include 'view/header.php'; ?>
    <?php endif; ?>
    <main>
        <?php if ($flash !== ''): ?>
            <div class="alert alert-success"><?= e($flash) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <?php render_view($viewFile, $data); ?>
    </main>
    <?php include 'view/footer.php'; ?>
</body>
</html>
