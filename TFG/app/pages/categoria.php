<?php require page('includes/cabecera')?>
    
    <div class="section-title"></div>

    <section class="content">
        
        <?php 
            $category = $URL[1] ?? null;
            $query = "select * from canciones where category_id = :category";
            try {
                $rows = db_query($query,['category'=>$category]);
            } catch (Exception $e) {
                echo '<div class="alert alert-danger" role="alert">Error: ' . $e->getMessage() . '</div>';
            }
        ?>

        <?php if(!empty($rows)):?>
            <?php foreach($rows as $row):?>
                <?php include page('includes/cancion')?>
            <?php endforeach;?>
        <?php else:?>
            <div class="m-2">¡Ups, no hay nada que mirar!</div>
        <?php endif;?>

    </section>

<?php require page('includes/pie')?>
