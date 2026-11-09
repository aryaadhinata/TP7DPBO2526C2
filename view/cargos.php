<?php
$isEdit = !empty($formData['id']);
$v = fn($key) => $formData[$key] ?? '';
?>
<h3>Daftar Barang</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama Barang</th>
            <th>Kode HS</th>
            <th>Kategori</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($cargo->getAll() as $c): ?>
        <tr>
            <td><?= e($c['id']) ?></td>
            <td><?= e($c['name']) ?></td>
            <td><?= e($c['hs_code']) ?></td>
            <td><?= e($c['category']) ?></td>
            <td class="table-actions">
                <a class="btn btn-warning" href="?page=cargos&action=edit&id=<?= e($c['id']) ?>">Ubah</a>
                <form method="POST" action="index.php?page=cargos" style="display:inline;padding:0;margin:0;border:0;background:none;" onsubmit="return confirm('Hapus barang ini?');">
                    <input type="hidden" name="op" value="delete">
                    <input type="hidden" name="id" value="<?= e($c['id']) ?>">
                    <button type="submit" class="btn-danger" style="width:auto;margin:2px;">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3><?= $isEdit ? 'Ubah Barang' : 'Tambah Barang' ?></h3>
<form method="POST" action="index.php?page=cargos">
    <input type="hidden" name="op" value="<?= $isEdit ? 'update' : 'create' ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= e($v('id')) ?>">
    <?php endif; ?>

    <label for="name">Nama Barang</label>
    <input type="text" id="name" name="name" maxlength="100" value="<?= e($v('name')) ?>" required>

    <label for="hs_code">Kode HS (6-10 digit)</label>
    <input type="text" id="hs_code" name="hs_code" maxlength="10" pattern="\d{6,10}" value="<?= e($v('hs_code')) ?>" required>

    <label for="category">Kategori</label>
    <input type="text" id="category" name="category" maxlength="50" value="<?= e($v('category')) ?>" required>

    <button type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah' ?></button>
    <?php if ($isEdit): ?>
        <a class="btn btn-danger" href="?page=cargos">Batal</a>
    <?php endif; ?>
</form>
