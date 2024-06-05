<?php require page('includes/cabecera')?>


<section class="content" style="display: flex;
    flex-direction: column;
    align-items: center;">
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
                foreach ($rows as $row) {
					?>
					<h2>Canciones:</h2>
					<?php
                    include page('includes/cancion');
                }
            } else {
                echo '<div>No se encontraron resultados para la consulta: ' . htmlspecialchars($query) . '</div>';
            }
        } else {
			echo 'no entra';
		}
    ?>
</section>

<?php require page('includes/pie')?>
