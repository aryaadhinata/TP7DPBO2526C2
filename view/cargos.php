<?php
/** @var CargoEntity $entity */
/** @var string $slug */
/** @var CargoEntity[] $rows */
/** @var array $pagination */
$isEdit = $entity->getId() > 0;
?>
<h3>Daftar Barang</h3>
<?php
render_view('table', [
    'slug' => $slug, 'rows' => $rows, 'deleteConfirm' => 'Hapus barang ini?',
    'columns' => [
        'ID'          => fn($c) => $c->getId(),
        'Nama Barang' => fn($c) => $c->getName(),
        'Kode HS'     => fn($c) => $c->getHsCode(),
        'Kategori'    => fn($c) => $c->getCategory(),
    ],
]);
render_view('pagination', ['slug' => $slug, 'pagination' => $pagination]);
?>

<h3><?= $isEdit ? 'Ubah Barang' : 'Tambah Barang' ?></h3>
<?= form_open($slug, $isEdit, $entity->getId()) ?>
    <?= input_field('name', 'Nama Barang', $entity->getName(), ['maxlength' => 100, 'required' => true]) ?>
    <?= input_field('hs_code', 'Kode HS (6-10 digit)', $entity->getHsCode(), ['maxlength' => 10, 'pattern' => '\d{6,10}', 'required' => true]) ?>
    <?= input_field('category', 'Kategori', $entity->getCategory(), ['maxlength' => 50, 'required' => true]) ?>
<?= form_close($slug, $isEdit) ?>
