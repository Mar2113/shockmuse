<!--start music card-->
<div class="music-card-full container mt-5">
    <div class="row align-items-center">
        <div class="col-md-4 text-center">
            <img src="<?= ROOT ?>uploads/<?= htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>" class="img-fluid rounded shadow-lg">
        </div>
        <div class="col-md-8">
            <h2 class="card-title"><?= esc($row['name']) ?></h2>
            <div class="card-subtitle mb-3">
                <strong>#:</strong> <?= esc(get_categories_artist($row['user_id'])) ?> <!-- Assuming get_artist_genres is a function that fetches the artist's genres -->
            </div>
            <div class="card-content mb-3">
                <p><?= esc($row['bio']) ?></p>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <h3>Canciones:</h3>
        <div class="row">
            <?php 
                $query = "SELECT * FROM canciones WHERE artist_id = :artist_id ORDER BY views DESC LIMIT 20";
                $rows = db_query($query, ['artist_id' => $row['id']]);
                // var_dump($rows);
            ?>

            <?php if (!empty($rows)): ?>
                <?php foreach ($rows as $row): ?>
                    <!-- <div class="col-md-4 mb-3"> -->
                        <?php include page('includes/cancion') ?>
                    <!-- </div> -->
                <?php endforeach; ?>
            <?php else: ?>
                <p>No songs found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<!--end music card-->

<style>
    .music-card-full {
        background: #fff;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    .music-card-full .card-title {
        font-size: 2rem;
        font-weight: bold;
    }

    .music-card-full .card-subtitle {
        font-size: 1.1rem;
        color: #6c757d;
    }

    .music-card-full .card-content p {
        font-size: 1rem;
        line-height: 1.5;
    }

    .music-card-full img {
        max-width: 100%;
        height: auto;
        border-radius: 15px;
    }

    .music-card-full .row {
        margin: 0 -15px;
    }

    .music-card-full .col-md-4, .music-card-full .col-md-8 {
        padding: 0 15px;
    }

    .music-card-full .col-md-4 img {
        max-width: 250px;
    }

    .music-card-full .row .col-md-4 {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .music-card-full .mt-5 {
        margin-top: 3rem !important;
    }

    .music-card-full .mb-3 {
        margin-bottom: 1rem !important;
    }

    .music-card-full h3 {
        font-size: 1.75rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .music-card-full .row .col-md-4 {
        display: flex;
        justify-content: center;
    }

    .music-card-full .row .col-md-4 mb-3 {
        margin-bottom: 1rem !important;
    }
</style>
