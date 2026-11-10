<?php /** @var array<string, int> $summary */ ?>
<h2>Selamat Datang</h2>
<p>Aplikasi ini mencatat aktivitas ekspor dan impor di pelabuhan internasional.</p>
<section class="summary-grid" aria-label="Ringkasan data">
    <article class="summary-card"><span>Kapal</span><strong><?= e($summary['ships']) ?></strong></article>
    <article class="summary-card"><span>Barang</span><strong><?= e($summary['cargos']) ?></strong></article>
    <article class="summary-card"><span>Ekspor</span><strong><?= e($summary['exports']) ?></strong></article>
    <article class="summary-card"><span>Impor</span><strong><?= e($summary['imports']) ?></strong></article>
</section>
<ul class="feature-grid">
    <li><strong>Kapal</strong><span>Data kapal pengangkut, nomor IMO, bendera, dan kapasitas TEU.</span></li>
    <li><strong>Barang</strong><span>Komoditas yang diperdagangkan beserta kode HS dan kategori.</span></li>
    <li><strong>Pelabuhan</strong><span>Daftar pelabuhan asal dan tujuan beserta negaranya.</span></li>
    <li><strong>Transaksi Ekspor/Impor</strong><span>Pengiriman yang menghubungkan kapal, barang, dan pelabuhan.</span></li>
</ul>
