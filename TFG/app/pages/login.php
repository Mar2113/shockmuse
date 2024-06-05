<?php

session_start();

try {
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        try {
            $errors = [];
            $values = [];
            $values['email'] = trim($_POST['email']);
            $values['username'] = trim($_POST['email']); // También se usa el correo electrónico como nombre de usuario
            $query = "SELECT * FROM usuarios WHERE email = :email OR username = :username LIMIT 1";
            $row = db_query_one($query, $values);

            if (!empty($row)) {
                if (password_verify($_POST['password'], $row['password'])) {
                    authenticate($row);
                    echo "Usuario autenticado<br>";

                    if (is_admin()) {
                        redirect('admin');
                    } else if (is_user()) {
                        redirect('index');
                    }
                } else {
                    message("Nombre o contraseña incorrecta", true, "error");
                }
            } else {
                message("Nombre o contraseña incorrecta", true, "error");
            }
        } catch (Exception $e) {
            // Manejo de errores
            error_handler($e);
            message("Ha ocurrido un error al intentar iniciar sesión", true, "error");
        }
    }
} catch (Exception $e) {
    message("Error inesperado: " . $e->getMessage(), true, "error");
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shockmuse - LandingPage</title>
    <!-- Variable icon font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <!-- Importar la biblioteca de iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Icono del corazón vacío -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Icons">
    <!-- Variable icon font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="<?= ROOT ?>assets/css/styleLandinPage.css" rel="stylesheet">
</head>

<body>
    <header>
        <div class="header-left">
            <h1>¡LA MEJOR MÚSICA DEL MOMENTO!</h1>
        </div>
        <div class="header-right">
            <button id="registerBtn" title="registrarse" tabindex="0">REGISTRARSE</button>
        </div>
    </header>


    <?php if ($msg = message()) : ?>
        <div class="alert <?= $msg['estado'] == 'success' ? 'alert-success' : 'alert-danger' ?>">
            <?= $msg['text'] ?>
        </div>
    <?php endif; ?>


    <aside id="myAside">
        <i title="Cerrar: registrarse" id="closeAside" class="fas fa-times" tabindex="0"></i>
        <!-- Icono "X" para cerrar el aside -->
        <div class="login-auth-page">
            <div class="login-auth-wrapper">
                <img src="imagenes/Logo (2).png" alt="Logo Shockmuse" title="logotipo Shockmuse">
                <h3>¡Y a disfrutar!</h3>
                <div class="login-social-zone">
                    <button class="social-btn btn-fb" title="Inciar Sesión: Facebook"> <i class="fab fa-facebook-f"></i>
                        Continuar con Facebook</button>
                    <button class="social-btn btn-apple" title="Inciar Sesión: Apple"><i class="fab fa-apple"></i>
                        Continuar con Apple</button>
                    <button class="social-btn btn-google" title="Inciar Sesión: Google"><i class="fab fa-google"></i>
                        Continuar con Google</button>
                </div>
                <div class="separator"></div>
                <form class="login-auth-form" method="POST" action="">
                    <div class="form-group">
                        <label for="email">Dirección de correo o nombre de usuario</label>
                        <input title="Campo: email o nombre de usuario" formControlName="email" type="text" placeholder="Dirección de correo o nombre de usuario" class="form-input" name="email">
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input aria-label="Campo: contraseña" alt="Campo: contraseña" formControlName="password" type="password" placeholder="Contraseña" class="form-input" title="Campo: contraseña" name="password">
                    </div>
                    <div class="form-steps">
                        <a class="link" href="#" title="Recuperar contraseña">¿Se te ha olvidado la
                            contraseña?</a>
                    </div>
                    <div class="form-action">
                        <button type="submit" class="login" title="Iniciar Sesión">
                            <span>Iniciar sesión</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </aside>

    <main>
        <img title="Logotipo Shockmuse" class="logotype" src="imagenes/Logo (2).png" alt="Logotipo Shockmuse">
        <div id="cuadrado">
            <div class="container">
                <div class="square" id="square1" onclick="aumentar(this)" alt="Imagen Pop" title="Imagen Pop">
                </div>
                <div class="square" id="square2" onclick="aumentar(this)" alt="Imageb Jazz" title="Imagen Jazz">
                </div>
                <div class="square" id="square3" onclick="aumentar(this)" alt="Imagen Rock" title="Imagen Rock">
                </div>
                <div class="square" id="square4" onclick="aumentar(this)" alt="Imagen Rap" title="Imagen Rap">
                </div>
                <div class="square" id="square5" onclick="aumentar(this)" alt="Imagen Reggeton" title="Imagen Reggeton">
                </div>
            </div>
        </div>
        <h2 class="texto2" alt="¡Regístrate!" title="¡Regístrate!" style="font-family: 'Segoe UI';">¡Regístrate para
            escuchar a tus artistas
            favoritos!</h2>
    </main>

    <footer>
        <p title="Derecho de autor">© 2024 Shockmuse Web. Todos los derechos reservados. <a href="accesibilidad.html" style="color: #fff; text-decoration: underline;" title="Ir a la página de accesibilidad">Accesibilidad</a></p>
    </footer>

    <script>
        document.getElementById('registerBtn').addEventListener('click', function() {
            document.getElementById('myAside').style.display = 'block'; // Muestra el aside al hacer clic en el botón de registro
        });

        // Agregar evento de clic al icono "X" para ocultar el aside
        document.getElementById('closeAside').addEventListener('click', function() {
            document.getElementById('myAside').style.display = 'none'; // Oculta el aside al hacer clic en el icono "X"
        });
        document.addEventListener("DOMContentLoaded", function() {
            const cuadrado = document.getElementById("cuadrado");

            cuadrado.addEventListener("click", function() {
                const posicionActual = window.innerHeight - cuadrado.getBoundingClientRect().bottom;
                const nuevaPosicion = posicionActual + (window.innerHeight * 0.1);
                cuadrado.style.bottom = `${nuevaPosicion}px`;

                setTimeout(function() {
                    cuadrado.style.bottom = "0px";
                }, 4000);
            });
        });

        function redirectToHomePage() {
            console.log("Redireccionando a la página de inicio desde: " + window.location.href);
            window.location.href = "homePage.html";
            console.log("Página actual: " + window.location.href);
        }
    </script>


</body>

</html>