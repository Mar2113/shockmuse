<?php require page('includes/cabecera')?>

<section class="content">
    <?php 
        try {
            $slug = $URL[1] ?? null;
            if ($slug) {
                $query = "SELECT * FROM canciones WHERE slug = :slug LIMIT 1";
                $row = db_query_one($query, ['slug' => $slug]);
            } else {
                throw new Exception("No se proporcionó un slug válido.");
            }
        } catch (Exception $e) {
            error_handler($e);
            message("Error: " . $e->getMessage(), true, "error");
            $row = null;
        }
    ?>

    <?php if(!empty($row)):?>
        <?php include page('cancion-pagina')?>
    <?php endif;?>
</section>

<?php require page('includes/pie')?>
