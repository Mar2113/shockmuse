<?php
require_once "../app/core/config.php"; // Usamos require_once para evitar inclusiones múltiples
require_once "../app/core/functions.php"; // Usamos require_once para evitar inclusiones múltiples

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    message('', true);
}

session_start();

try {

    if (!logged_in()) {
        message("Por favor, inicie sesión", true, "error");
        redirect('login');
    }

    $role = $_SESSION['USER']['role'] ?? null;

    if ($role === 'user') {
    }
} catch (Exception $e) {
    // Manejar cualquier excepción que pueda ocurrir durante la verificación de autenticación
    error_handler($e);
    message("Error inesperado: " . $e->getMessage(), true, "error");
    redirect('login');
}

require page('includes/cabecera')

?>


<section>
    <!-- <img class="imagenFondo" src="<?= ROOT ?>/assets/images/music_AdobeStock_329594746.original.jpg"> -->
</section>

<!-- Seccion Featured -->

<section class="content-featured">
    <h3 class="section-title"></h3>

    <?php

    $rows = db_query("select * from canciones order by id desc limit 16");

    ?>

    <?php if (!empty($rows)) : ?>
        <?php foreach ($rows as $row) : ?>
            <?php include page('includes/cancion') ?>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="m-2">No songs found</div>
    <?php endif; ?>

    </div>

</section>

<?php require page('includes/pie') ?>