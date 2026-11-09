<?php
$isEdit = !empty($formData['id']);
$v = fn($key) => $formData[$key] ?? '';
$allShips = $ship->getAll();
$allCargos = $cargo->getAll();
?>
<h3>Daftar Transaksi Ekspor / Impor</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Jenis</th>
            <th>Kapal</th>
            <th>Barang</th>
            <th>Asal</th>
            <th>Tujuan</th>
            <th>Muatan (ton)</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($shipment->getAll() as $sp): ?>
        <tr>
            <td><?= e($sp['id']) ?></td>
            <td><?= e($sp['type']) ?></td>
            <td><?= e($sp['ship_name']) ?></td>
            <td><?= e($sp['cargo_name']) ?></td>
            <td><?= e($sp['origin_port']) ?></td>
            <td><?= e($sp['destination_port']) ?></td>
            <td><?= e($sp['quantity_ton']) ?></td>
            <td><?= e($sp['shipment_date']) ?></td>
            <td><?= e($sp['status']) ?></td>
            <td class="table-actions">
                <a class="btn btn-warning" href="?page=shipments&action=edit&id=<?= e($sp['id']) ?>">Ubah</a>
                <form method="POST" action="index.php?page=shipments" style="display:inline;padding:0;margin:0;border:0;background:none;" onsubmit="return confirm('Hapus transaksi ini?');">
                    <input type="hidden" name="op" value="delete">
                    <input type="hidden" name="id" value="<?= e($sp['id']) ?>">
                    <button type="submit" class="btn-danger" style="width:auto;margin:2px;">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3><?= $isEdit ? 'Ubah Transaksi' : 'Tambah Transaksi' ?></h3>
<form method="POST" action="index.php?page=shipments">
    <input type="hidden" name="op" value="<?= $isEdit ? 'update' : 'create' ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= e($v('id')) ?>">
    <?php endif; ?>

    <label for="ship_id">Kapal</label>
    <select id="ship_id" name="ship_id" required>
        <option value="">-- Pilih kapal --</option>
        <?php foreach ($allShips as $s): ?>
            <option value="<?= e($s['id']) ?>" <?= (string)$v('ship_id') === (string)$s['id'] ? 'selected' : '' ?>>
                <?= e($s['name']) ?> (IMO <?= e($s['imo_number']) ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <label for="cargo_id">Barang</label>
    <select id="cargo_id" name="cargo_id" required>
        <option value="">-- Pilih barang --</option>
        <?php foreach ($allCargos as $c): ?>
            <option value="<?= e($c['id']) ?>" <?= (string)$v('cargo_id') === (string)$c['id'] ? 'selected' : '' ?>>
                <?= e($c['name']) ?> (HS <?= e($c['hs_code']) ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <label for="type">Jenis Transaksi</label>
    <select id="type" name="type" required>
        <?php foreach (Shipment::TYPES as $t): ?>
            <option value="<?= e($t) ?>" <?= $v('type') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="origin_port">Pelabuhan Asal</label>
    <input type="text" id="origin_port" name="origin_port" maxlength="100" value="<?= e($v('origin_port')) ?>" required>

    <label for="destination_port">Pelabuhan Tujuan</label>
    <input type="text" id="destination_port" name="destination_port" maxlength="100" value="<?= e($v('destination_port')) ?>" required>

    <label for="quantity_ton">Jumlah Muatan (ton)</label>
    <input type="number" id="quantity_ton" name="quantity_ton" min="0.01" step="0.01" value="<?= e($v('quantity_ton')) ?>" required>

    <label for="shipment_date">Tanggal Pengiriman</label>
    <input type="date" id="shipment_date" name="shipment_date" value="<?= e($v('shipment_date')) ?>" required>

    <label for="status">Status</label>
    <select id="status" name="status" required>
        <?php foreach (Shipment::STATUSES as $st): ?>
            <option value="<?= e($st) ?>" <?= $v('status') === $st ? 'selected' : '' ?>><?= e($st) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah' ?></button>
    <?php if ($isEdit): ?>
        <a class="btn btn-danger" href="?page=shipments">Batal</a>
    <?php endif; ?>
</form>
