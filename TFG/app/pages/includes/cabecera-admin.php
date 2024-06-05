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
        <div class="breadcrumbHeader">SHOCKMUSE - ADMIN</div>
        <div class="search-container">
            <!-- Campo de búsqueda -->
            <input type="text" placeholder="Buscar...">
            <!-- Icono de búsqueda -->
            <div class="search-icon">
                <i class="fas fa-search fa-lg"></i>
            </div>
        </div>
    </header>
    <?php if ($msg = message()) : ?>
        <div class="alert <?= $msg['estado'] == 'success' ? 'alert-success' : 'alert-danger' ?>">
            <?= $msg['text'] ?>
        </div>
    <?php endif; ?>
    <sidebar id="sidebar">
        <div class="main-nav">
            <!-- Menú -->
            <div class="menu">
                <ul class="menu-item">
                    <li><a href="<?= ROOT ?>admin/principal">Principal</a></li>
                    <li class="menu-item-with-submenu">
                        <a href="#">Canciones</a>
                        <ul class="submenu">
                            <li class="submenu-item"><a href="<?= ROOT ?>admin/canciones">Ver Lista</a></li>
                            <li class="submenu-item"><a href="<?= ROOT ?>admin/canciones/añadir">Añadir</a></li>
                        </ul>
                    </li>
                    <li class="menu-item-with-submenu">
                        <a href="#">Generos</a>
                        <ul class="submenu">
                            <li class="submenu-item"><a href="<?= ROOT ?>admin/generos">Ver Lista</a></li>
                            <li class="submenu-item"><a href="<?= ROOT ?>admin/generos/añadir">Añadir</a></li>
                        </ul>
                    </li>
                    <li class="menu-item-with-submenu">
                        <a href="#">Usuarios</a>
                        <ul class="submenu">
                            <li class="submenu-item"><a href="<?= ROOT ?>admin/usuarios">Ver Lista</a></li>
                            <li class="submenu-item"><a href="<?= ROOT ?>admin/usuarios/añadir">Añadir</a></li>
                        </ul>
                    </li>
                    <li class="menu-item-with-submenu">
                        <a href="#">Artistas</a>
                        <ul class="submenu">
                            <li class="submenu-item"><a href="<?= ROOT ?>admin/artistas">Ver Lista</a></li>
                            <li class="submenu-item"><a href="<?= ROOT ?>admin/artistas/añadir">Añadir</a></li>
                        </ul>
                    </li>
                    <li><a href="<?= ROOT ?>admin/listas">Albumes</a></li>
                    <li><a href="<?= ROOT ?>admin/configuracion">Configuracion</a></li>
                    <li><a href="<?= ROOT ?>login"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>

                </ul>
            </div>
        </div>
    </sidebar>