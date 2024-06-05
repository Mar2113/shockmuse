<?php require page('includes/cabecera')?>
	

	<center><div class="section-title">Cantante:</div></center>

    <section class="content-featured">
		
		<?php 
			$id = $URL[1] ?? null;
			$query = "select * from artistas where id = :id limit 1";
			$row = db_query_one($query,['id'=>$id]);

		?>

		<?php if(!empty($row)):?>
			<?php include page('artista-pagina')?>
		<?php endif;?>

	</section>

<?php require page('includes/pie')?>