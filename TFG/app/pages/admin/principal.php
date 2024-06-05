<?php require page('includes/cabecera-admin') 
?>
<?php 
// Limpiar mensajes al cargar la página
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    message('', true);
}
?>

<section class="content-featured">
    <!-- <h3 class="section-title">Featured</h3> -->
    <h2>Seccion Principal</h2>
</section>
<?php require page('includes/pie-admin') ?>



