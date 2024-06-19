<?php require page('includes/cabecera') ?>

<?php
$category = $URL[1] ?? null;
$query = "select * from canciones where category_id = :category";
$query2 = "select category from categorias where id = :category";
try {
    $rows = db_query($query, ['category' => $category]);
    $nombreCategoria = db_query($query2, ['category' => $category]);
} catch (Exception $e) {
    echo '<div class="alert alert-danger" role="alert">Error: ' . $e->getMessage() . '</div>';
}
?>
<div style="
	    padding: 10px 10px 10px 70px;
    width: 100%;
    font-size: 30px;
	">
    <?php echo $nombreCategoria[0]['category']; ?>
</div>

<section class="content" style="
        display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    align-content: center;
    ">



    <?php if (!empty($rows)) : ?>
        <?php foreach ($rows as $row) : ?>
            <?php include page('includes/cancion') ?>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="estaVacioCategoria">
            <div class="m-2">¡Ups, no hay nada que mirar!</div>
            <p><a href="javascript:history.back()">volver a la página anterior</a>.</p>
        </div>
    <?php endif; ?>

</section>

<?php require page('includes/pie') ?>