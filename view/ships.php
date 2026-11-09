<?php
$isEdit = !empty($formData['id']);
$v = fn($key) => $formData[$key] ?? '';
?>
<h3>Daftar Kapal</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama Kapal</th>
            <th>No. IMO</th>
            <th>Negara Bendera</th>
            <th>Kapasitas (TEU)</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($ship->getAll() as $s): ?>
        <tr>
            <td><?= e($s['id']) ?></td>
            <td><?= e($s['name']) ?></td>
            <td><?= e($s['imo_number']) ?></td>
            <td><?= e($s['flag_country']) ?></td>
            <td><?= e($s['capacity_teu']) ?></td>
            <td class="table-actions">
                <a class="btn btn-warning" href="?page=ships&action=edit&id=<?= e($s['id']) ?>">Ubah</a>
                <form method="POST" action="index.php?page=ships" style="display:inline;padding:0;margin:0;border:0;background:none;" onsubmit="return confirm('Hapus kapal ini?');">
                    <input type="hidden" name="op" value="delete">
                    <input type="hidden" name="id" value="<?= e($s['id']) ?>">
                    <button type="submit" class="btn-danger" style="width:auto;margin:2px;">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3><?= $isEdit ? 'Ubah Kapal' : 'Tambah Kapal' ?></h3>
<form method="POST" action="index.php?page=ships">
    <input type="hidden" name="op" value="<?= $isEdit ? 'update' : 'create' ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= e($v('id')) ?>">
    <?php endif; ?>

    <label for="name">Nama Kapal</label>
    <input type="text" id="name" name="name" maxlength="100" value="<?= e($v('name')) ?>" required>

    <label for="imo_number">Nomor IMO (7 digit)</label>
    <input type="text" id="imo_number" name="imo_number" maxlength="7" pattern="\d{7}" value="<?= e($v('imo_number')) ?>" required>

    <label for="flag_country">Negara Bendera</label>
    <input type="text" id="flag_country" name="flag_country" maxlength="60" value="<?= e($v('flag_country')) ?>" required>

    <label for="capacity_teu">Kapasitas (TEU)</label>
    <input type="number" id="capacity_teu" name="capacity_teu" min="1" value="<?= e($v('capacity_teu')) ?>" required>

    <button type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah' ?></button>
    <?php if ($isEdit): ?>
        <a class="btn btn-danger" href="?page=ships">Batal</a>
    <?php endif; ?>
</form>
