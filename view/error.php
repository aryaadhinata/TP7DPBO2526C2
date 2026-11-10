<?php /** @var string|null $fatalMessage Pesan aman untuk ditampilkan kepada pengunjung. */ ?>
<h2>Terjadi Kesalahan</h2>
<p><?= e($fatalMessage ?? 'Terjadi kesalahan pada server.') ?></p>
<p>Silakan coba lagi beberapa saat lagi. Jika berlanjut, hubungi administrator.</p>
