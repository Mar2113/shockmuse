<?php require page('includes/cabecera')?>


<section class="content" style="
        display: flex;
    flex-direction: row;
    align-items: center;
    flex-wrap: wrap;
    justify-content: center;
    /* overflow-y: auto; */
    ">
    <?php 
        // Procesar la consulta de búsqueda
        if (isset($_GET['query'])) {
            $query = $_GET['query'];
            // Consulta SQL dinámica para buscar en múltiples campos
            $sql = "SELECT * FROM canciones 
                    WHERE title LIKE :query 
                    OR category_id IN (SELECT id FROM categorias WHERE category LIKE :query)
                    OR artist_id IN (SELECT id FROM artistas WHERE name LIKE :query)
                    OR user_id IN (SELECT id FROM usuarios WHERE username LIKE :query AND role != 'admin')";
            $rows = db_query($sql, [':query' => '%' . $query . '%']);


            // Mostrar los resultados
            if (!empty($rows)) {
                ?>
                <h2 style="
                width: 100%;
                padding: 10px;
                "
                >Canciones:</h2>
                <?php
                foreach ($rows as $row) {
                    include page('includes/cancion');
                }
            } else {
                echo '<div>No se encontraron Canciones: ' . htmlspecialchars($query) . '</div>';
            }
        } else {
			echo 'no entra';
		}
    ?>
</section>

<section class="content" style="
        display: flex;
    flex-direction: row;
    align-items: center;
    flex-wrap: wrap;
    justify-content: center;
    /* overflow-y: auto; */
    padding-bottom: 200px;
    ">
    <?php 
        // Procesar la consulta de búsqueda
        if (isset($_GET['query'])) {
            $query = $_GET['query'];
            // Consulta SQL dinámica para buscar en múltiples campos
            $sql = "SELECT * FROM artistas 
                    WHERE name LIKE :query";
            $rows = db_query($sql, [':query' => '%' . $query . '%']);


            // Mostrar los resultados
            if (!empty($rows)) {
                ?>
                <h2 style="
                width: 100%;
                padding: 10px;
                "
                >Artistas:</h2>
                <?php
                foreach ($rows as $row) {
                    include page('includes/artista');
                }
            } else {
                echo '<div>No se encontraron Artistas: ' . htmlspecialchars($query) . '</div>';
            }
        } else {
			echo 'no entra';
		}
    ?>
</section>

<?php require page('includes/pie')?>
