<?php
/**
 * Tabel data generik.
 * Variabel: $slug, $rows (entity), $columns [label => fn(entity) => nilai], $deleteConfirm
 */
?>
<div class="table-scroll<?= !empty($tableClass) ? ' ' . e($tableClass) : '' ?>">
<table>
    <thead>
        <tr>
            <?php foreach (array_keys($columns) as $label): ?>
                <th><?= e($label) ?></th>
            <?php endforeach; ?>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($rows)): ?>
        <tr><td colspan="<?= count($columns) + 1 ?>">Belum ada data.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $row): ?>
        <tr>
            <?php foreach ($columns as $getter): ?>
                <td>
                    <?php $cell = $getter($row); ?>
                    <?php if (is_array($cell) && isset($cell['badge'], $cell['tone'])): ?>
                        <?php $badgeClass = ['export' => 'badge-export', 'import' => 'badge-import', 'pending' => 'badge-pending', 'active' => 'badge-active', 'complete' => 'badge-complete'][$cell['tone']] ?? ''; ?>
                        <span class="badge <?= e($badgeClass) ?>"><?= e($cell['badge']) ?></span>
                    <?php else: ?>
                        <?= e($cell) ?>
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
            <td class="table-actions">
                <a class="btn btn-warning" href="?page=<?= e($slug) ?>&amp;action=edit&amp;id=<?= e($row->getId()) ?>#<?= e($slug) ?>-form">Ubah</a>
                <form method="POST" action="index.php?page=<?= e($slug) ?>" class="inline-form"
                      onsubmit="return confirm('<?= e($deleteConfirm) ?>');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="op" value="delete">
                    <input type="hidden" name="id" value="<?= e($row->getId()) ?>">
                    <button type="submit" class="btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
