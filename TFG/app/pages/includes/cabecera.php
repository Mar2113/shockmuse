<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Variable icon font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <link href="<?= ROOT ?>assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Shockmuse</title>
    <style>

    </style>
</head>

<body>

    <header>
        <div class="menu-icon" id="menu-icon">
            <!-- Icono de menú -->
            <i class="fas fa-bars fa-lg"></i>
        </div>
        <!-- Agregar el breadcrumb -->
        <div class="breadcrumbHeader">SHOCKMUSE</div>
        <div class="search-container">
            <form class="form-buscador" method="GET" action="<?= ROOT ?>buscador" style="    
        height: 0px;
        margin-bottom: 50px;
        background-color: #444;">
                <div class="form-group">
                    <input class="form-control" type="text" placeholder="Buscar . . ." name="query" id="search-input">
                    <button class="btn" id="search-icon" type="submit">Search</button>
                </div>
            </form>
        </div>
    </header>

    <!-- <form action/search">
				<div class="form-group">
					<input class="form-control" type="text" placeholder="Search for music" name="find">
					<button class="btn">Search</button>
				</div>
			</form> -->

    <!-- <div class="header-right">
            <a href="landingPage.html" title="Cerrar sesión" tabindex="0">
                <i alt="Cerrar sesión" class="material-symbols-outlined" style="color:#FFFFFF;">logout</i>
            </a>
        </div> -->

    <sidebar id="sidebar">
        <div class="main-nav">
            <!-- Menú -->
            <div class="menu">
                <ul class="menu-item">
                    <li><a href="<?= ROOT ?>">Principal</a></li>
                    <li><a href="<?= ROOT ?>favoritos">Favoritos</a></li>
                    <li><a href="<?= ROOT ?>listas">Listas</a></li>

                    <?php
                    try {
                        $query = "SELECT id, category FROM categorias ORDER BY category ASC";
                        $categorias = db_query($query);
                    } catch (Exception $e) {
                        // Manejo de errores
                        echo '<div class="alert alert-danger" role="alert">Error: ' . $e->getMessage() . '</div>';
                        $categorias = [];
                    }
                    ?>

                    <li class="menu-item-with-submenu">
                        <a href="#">Géneros</a>
                        <ul class="submenu">
                            <?php if (!empty($categorias)) : ?>
                                <?php foreach ($categorias as $categoria) : ?>
                                    <li class="submenu-item">
                                        <?php try {
                                            $url = ROOT . 'categoria/' . esc($categoria['id']);
                                        ?>
                                            <a href="<?= $url ?>">
                                                <?= esc($categoria['category']) ?>
                                            </a>
                                        <?php } catch (Exception $e) {
                                            echo '<script>console.error("Error en la generación del enlace:", ' . json_encode($e->getMessage()) . ');</script>';
                                        } ?>
                                    </li>

                                <?php endforeach; ?>
                            <?php else : ?>
                                <li class="submenu-item"><a href="#">No hay generos disponibles</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <li><a href="<?= ROOT ?>artistas">Artistas</a></li>
                    <li><a href="<?= ROOT ?>knowus">Quienes somos</a></li>
                    <li><a href="<?= ROOT ?>contacto">Contacto</a></li>
                    <li><a href="<?= ROOT ?>includes/configuracion">Configuracion</a></li>
                    <li><a href="<?= ROOT ?>login"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>

                </ul>
            </div>
        </div>
    </sidebar>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Manejar el clic en el icono de búsqueda
            $('#search-icon').click(function() {
                realizarBusqueda();
            });

            // Manejar la pulsación de la tecla Enter en el campo de búsqueda
            $('#search-input').keypress(function(e) {
                if (e.which == 13) {
                    realizarBusqueda();
                }
            });

            // Función para realizar la búsqueda
            function realizarBusqueda() {
                // Obtener el valor del campo de búsqueda
                var query = $('#search-input').val().trim();

                // Validar si la consulta no está vacía
                if (query !== '') {
                    // Realizar la solicitud AJAX al servidor
                    $.ajax({
                        url: 'ruta_a_tu_script_php.php',
                        type: 'GET',
                        data: {
                            q: query
                        },
                        success: function(response) {
                            // Manejar la respuesta del servidor
                            // Aquí puedes mostrar los resultados de la búsqueda en tu página
                            console.log(response);
                        },
                        error: function(xhr, status, error) {
                            // Manejar errores de la solicitud AJAX
                            console.error(error);
                        }
                    });
                }
            }
        });
    </script>