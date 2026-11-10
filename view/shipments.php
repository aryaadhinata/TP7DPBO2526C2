<?php
/** @var ShipmentEntity $entity */
/** @var string $slug */
/** @var ShipmentEntity[] $rows */
/** @var array $pagination */
/** @var array $shipOptions */
/** @var array $cargoOptions */
/** @var array $portOptions */
/** @var array $typeOptions */
/** @var array $statusOptions */
$isEdit = $entity->getId() > 0;
?>
<h3>Daftar Transaksi Ekspor / Impor</h3>
<?php
render_view('table', [
    'slug' => $slug, 'rows' => $rows, 'deleteConfirm' => 'Hapus transaksi ini?',
    'tableClass' => 'shipment-table',
    'columns' => [
        'ID'            => fn($s) => $s->getId(),
        'Jenis'         => fn($s) => ['badge' => $s->getType(), 'tone' => $s->getType() === Shipment::TYPE_EXPORT ? 'export' : 'import'],
        'Kapal'         => fn($s) => $s->getShipName(),
        'Barang'        => fn($s) => $s->getCargoName(),
        'Rute'          => fn($s) => $s->getOriginPortLabel() . ' -> ' . $s->getDestinationPortLabel(),
        'Muatan (ton)'  => fn($s) => number_format((float)$s->getQuantityTon(), 2, ',', '.'),
        'Tanggal'       => fn($s) => (new DateTimeImmutable($s->getShipmentDate()))->format('d M Y'),
        'Status'        => fn($s) => [
            'badge' => $s->getStatus(),
            'tone' => match ($s->getStatus()) {
                Shipment::STATUS_PENDING => 'pending',
                'Dalam Pelayaran' => 'active',
                'Tiba', 'Selesai' => 'complete',
                default => 'pending',
            },
        ],
    ],
]);
render_view('pagination', ['slug' => $slug, 'pagination' => $pagination]);
?>

<h3><?= $isEdit ? 'Ubah Transaksi' : 'Tambah Transaksi' ?></h3>
<?= form_open($slug, $isEdit, $entity->getId()) ?>
    <?= select_field('ship_id', 'Kapal', $shipOptions, $entity->getShipId(), '-- Pilih kapal --') ?>
    <?= select_field('cargo_id', 'Barang', $cargoOptions, $entity->getCargoId(), '-- Pilih barang --') ?>
    <?= select_field('type', 'Jenis Transaksi', $typeOptions, $entity->getType()) ?>
    <?= select_field('origin_port_id', 'Pelabuhan Asal', $portOptions, $entity->getOriginPortId(), '-- Pilih pelabuhan --') ?>
    <?= select_field('destination_port_id', 'Pelabuhan Tujuan', $portOptions, $entity->getDestinationPortId(), '-- Pilih pelabuhan --') ?>
    <?= input_field('quantity_ton', 'Jumlah Muatan (ton)', $entity->getQuantityTon(), ['min' => '0.01', 'step' => '0.01', 'required' => true], 'number') ?>
    <?= input_field('shipment_date', 'Tanggal Pengiriman', $entity->getShipmentDate(), ['required' => true], 'date') ?>
    <?= select_field('status', 'Status', $statusOptions, $entity->getStatus()) ?>
<?= form_close($slug, $isEdit) ?>
