<?php require page('includes/cabecera')?>
	
	<div 
	style="
	    padding: 10px 10px 10px 70px;
    width: 100%;
    font-size: 30px;
	"
	>Artistas:</div>

    <section class="content-featured">
		
		<?php 
			try {
				$rows = db_query("select * from artistas order by id desc limit 24");
			} catch (Exception $e) {
				echo '<div class="alert alert-danger" role="alert">Error: ' . $e->getMessage() . '</div>';
				message("Error: Solicitud no válida. Por favor, inténtelo de nuevo.", true, "error");
            }
		?>

		<?php if(!empty($rows)):?>
			<?php foreach($rows as $row):?>
				<?php include page('includes/artista')?>
			<?php endforeach;?>
		<?php endif;?>

	</section>

<?php require page('includes/pie')?>
