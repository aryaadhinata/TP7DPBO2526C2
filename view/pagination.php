<?php
/** Variabel: $slug, $pagination ['page', 'pages', 'total'] */
if ($pagination['pages'] > 1): ?>
<div class="pagination" role="navigation" aria-label="Halaman data">
    <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
        <?php if ($i === $pagination['page']): ?>
            <span class="current"><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= e($slug) ?>&amp;p=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <span class="total"><?= e($pagination['total']) ?> data</span>
</div>
<?php endif; ?>
