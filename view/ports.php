<?php
/** @var PortEntity $entity */
/** @var string $slug */
/** @var PortEntity[] $rows */
/** @var array $pagination */
$isEdit = $entity->getId() > 0;
?>
<h3>Daftar Pelabuhan</h3>
<?php
render_view('table', [
    'slug' => $slug, 'rows' => $rows, 'deleteConfirm' => 'Hapus pelabuhan ini?',
    'columns' => [
        'ID'              => fn($p) => $p->getId(),
        'Nama Pelabuhan'  => fn($p) => $p->getName(),
        'Negara'          => fn($p) => $p->getCountry(),
    ],
]);
render_view('pagination', ['slug' => $slug, 'pagination' => $pagination]);
?>

<h3><?= $isEdit ? 'Ubah Pelabuhan' : 'Tambah Pelabuhan' ?></h3>
<?= form_open($slug, $isEdit, $entity->getId()) ?>
    <?= input_field('name', 'Nama Pelabuhan', $entity->getName(), ['maxlength' => 100, 'required' => true]) ?>
    <?= input_field('country', 'Negara (pelabuhan "' . Port::DOMESTIC_COUNTRY . '" dianggap dalam negeri)', $entity->getCountry(), ['maxlength' => 60, 'required' => true]) ?>
<?= form_close($slug, $isEdit) ?>
