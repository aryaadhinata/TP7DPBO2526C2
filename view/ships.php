<?php
/** @var ShipEntity $entity */
/** @var string $slug */
/** @var ShipEntity[] $rows */
/** @var array $pagination */
$isEdit = $entity->getId() > 0;
?>
<h3>Daftar Kapal</h3>
<?php
render_view('table', [
    'slug' => $slug, 'rows' => $rows, 'deleteConfirm' => 'Hapus kapal ini?',
    'columns' => [
        'ID'              => fn($s) => $s->getId(),
        'Nama Kapal'      => fn($s) => $s->getName(),
        'No. IMO'         => fn($s) => $s->getImoNumber(),
        'Negara Bendera'  => fn($s) => $s->getFlagCountry(),
        'Kapasitas (TEU)' => fn($s) => $s->getCapacityTeu(),
    ],
]);
render_view('pagination', ['slug' => $slug, 'pagination' => $pagination]);
?>

<h3><?= $isEdit ? 'Ubah Kapal' : 'Tambah Kapal' ?></h3>
<?= form_open($slug, $isEdit, $entity->getId()) ?>
    <?= input_field('name', 'Nama Kapal', $entity->getName(), ['maxlength' => 100, 'required' => true]) ?>
    <?= input_field('imo_number', 'Nomor IMO (7 digit)', $entity->getImoNumber(), ['maxlength' => 7, 'pattern' => '\d{7}', 'required' => true]) ?>
    <?= input_field('flag_country', 'Negara Bendera', $entity->getFlagCountry(), ['maxlength' => 60, 'required' => true]) ?>
    <?= input_field('capacity_teu', 'Kapasitas (TEU)', $entity->getCapacityTeu() ?: '', ['min' => 1, 'max' => ShipEntity::MAX_CAPACITY_TEU, 'required' => true], 'number') ?>
<?= form_close($slug, $isEdit) ?>
