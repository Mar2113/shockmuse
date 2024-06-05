<!--start music card-->
<div class="music-card">
    <div style="overflow: hidden;">
        <a href="<?= ROOT ?>artista/<?= $row['id'] ?>"><img src="<?= ROOT ?>uploads/<?= htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>"></a>
    </div>
    <div class="card-content">
        <div class="card-title"><?= esc(ucwords($row['name'])) ?></div>
    </div>
</div>
<!--end music card-->