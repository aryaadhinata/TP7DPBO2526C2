<?php
require_once 'class/Ship.php';
require_once 'class/Cargo.php';
require_once 'class/Shipment.php';

// Escape output agar aman dari XSS
function e($value) {
    return is_scalar($value) ? htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') : '';
}

$ship = new Ship();
$cargo = new Cargo();
$shipment = new Shipment();

$models = [
    'ships'     => $ship,
    'cargos'    => $cargo,
    'shipments' => $shipment,
];

$page   = is_string($_GET['page'] ?? null) ? $_GET['page'] : 'home';
$action = is_string($_GET['action'] ?? null) ? $_GET['action'] : 'list';
if (!isset($models[$page])) {
    $page = 'home';
}

$op       = '';
$error    = '';
$formData = [];

$flashMessages = [
    'created' => 'Data berhasil ditambahkan.',
    'updated' => 'Data berhasil diperbarui.',
    'deleted' => 'Data berhasil dihapus.',
];
$msgKey = is_string($_GET['msg'] ?? null) ? $_GET['msg'] : '';
$flash  = $flashMessages[$msgKey] ?? '';

// ---------- Handler aksi (Create, Update, Delete) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $page !== 'home') {
    $op = is_string($_POST['op'] ?? null) ? $_POST['op'] : '';
    $result = false;
    $handled = true;

    if ($page === 'ships') {
        if ($op === 'create') {
            $result = $ship->create($_POST['name'] ?? '', $_POST['imo_number'] ?? '', $_POST['flag_country'] ?? '', $_POST['capacity_teu'] ?? '');
        } elseif ($op === 'update') {
            $result = $ship->update($_POST['id'] ?? 0, $_POST['name'] ?? '', $_POST['imo_number'] ?? '', $_POST['flag_country'] ?? '', $_POST['capacity_teu'] ?? '');
        } elseif ($op === 'delete') {
            $result = $ship->delete($_POST['id'] ?? 0);
        } else {
            $handled = false;
        }
    } elseif ($page === 'cargos') {
        if ($op === 'create') {
            $result = $cargo->create($_POST['name'] ?? '', $_POST['hs_code'] ?? '', $_POST['category'] ?? '');
        } elseif ($op === 'update') {
            $result = $cargo->update($_POST['id'] ?? 0, $_POST['name'] ?? '', $_POST['hs_code'] ?? '', $_POST['category'] ?? '');
        } elseif ($op === 'delete') {
            $result = $cargo->delete($_POST['id'] ?? 0);
        } else {
            $handled = false;
        }
    } elseif ($page === 'shipments') {
        if ($op === 'create') {
            $result = $shipment->create(
                $_POST['ship_id'] ?? 0, $_POST['cargo_id'] ?? 0, $_POST['type'] ?? '',
                $_POST['origin_port'] ?? '', $_POST['destination_port'] ?? '',
                $_POST['quantity_ton'] ?? '', $_POST['shipment_date'] ?? '', $_POST['status'] ?? ''
            );
        } elseif ($op === 'update') {
            $result = $shipment->update(
                $_POST['id'] ?? 0, $_POST['ship_id'] ?? 0, $_POST['cargo_id'] ?? 0, $_POST['type'] ?? '',
                $_POST['origin_port'] ?? '', $_POST['destination_port'] ?? '',
                $_POST['quantity_ton'] ?? '', $_POST['shipment_date'] ?? '', $_POST['status'] ?? ''
            );
        } elseif ($op === 'delete') {
            $result = $shipment->delete($_POST['id'] ?? 0);
        } else {
            $handled = false;
        }
    }

    if ($handled) {
        if ($result) {
            // Post/Redirect/Get: cegah submit ulang saat halaman di-refresh
            $codes = ['create' => 'created', 'update' => 'updated', 'delete' => 'deleted'];
            header('Location: index.php?page=' . $page . '&msg=' . $codes[$op]);
            exit;
        }
        $error = $models[$page]->getError();
        if ($op !== 'delete') {
            $formData = $_POST; // isi ulang form agar input tidak hilang
        }
    }
}

// ---------- Muat data untuk form ubah ----------
if ($page !== 'home' && $action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $row = $models[$page]->getById($_GET['id'] ?? 0);
    if ($row !== null) {
        $formData = $row;
    } else {
        $error = 'Data tidak ditemukan.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Ekspor Impor Pelabuhan Internasional</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'view/header.php'; ?>
    <nav>
        <ul>
            <li><a href="index.php">Beranda</a></li>
            <li><a href="?page=ships">Kapal</a></li>
            <li><a href="?page=cargos">Barang</a></li>
            <li><a href="?page=shipments">Transaksi Ekspor/Impor</a></li>
        </ul>
    </nav>
    <main>
        <?php if ($flash !== ''): ?>
            <div class="alert alert-success"><?= e($flash) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <?php
        if ($page === 'ships') include 'view/ships.php';
        elseif ($page === 'cargos') include 'view/cargos.php';
        elseif ($page === 'shipments') include 'view/shipments.php';
        else include 'view/home.php';
        ?>
    </main>
    <?php include 'view/footer.php'; ?>
</body>
</html>
