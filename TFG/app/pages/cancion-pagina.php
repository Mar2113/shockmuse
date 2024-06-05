<?php
try {
    db_query("update canciones set views = views + 1 where id = :id limit 1", ['id' => $row['id']]);
} catch (Exception $e) {
    // Manejo de errores
    echo '<div class="alert alert-danger" role="alert">Error: ' . $e->getMessage() . '</div>';
    message("Error: Hubo un problema al actualizar las vistas.", true, "error");
}
?>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<!--start music card-->
<section class="content-featured" style="overflow-y: none;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-light shadow-lg p-3 mb-5 bg-white rounded">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="card-title text-primary m-0"><?= esc($row['title']) ?></h2>
                    <a href="<?= ROOT ?>artista/<?= $row['artist_id'] ?>" class="btn btn-link text-secondary">
                        <i class="fas fa-arrow-left"></i><?= esc(get_artist($row['artist_id'])) ?>
                    </a>
                </div>
                <div class="overflow-hidden text-center mt-4">
                    <img src="<?= ROOT ?>/<?= $row['image'] ?>" alt="<?= esc($row['title']) ?>" class="img-fluid rounded">
                </div>
                <div class="card-content mt-3">
                    <div class="audio-player mt-3">
                        <audio controls class="w-100">
                            <source src="<?= ROOT ?>/<?= $row['file'] ?>" type="audio/mpeg">
                        </audio>
                    </div>
                    <div class="text-center mt-3">
                        <span class="badge bg-info text-dark">Visitas: <?= $row['views'] ?></span>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">Fecha: <?= get_date($row['date']) ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--end music card-->

<style>
    .badge {
        font-size: 1rem;
    }
    .btn-link {
        font-size: 1.2rem;
    }
    .fas {
        margin-right: 0.5rem;
    }
</style>
