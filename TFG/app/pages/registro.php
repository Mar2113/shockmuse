<?php

// Limpiar mensajes al cargar la página
message('', true);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validar token CSRF
        // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        //     csrf_error_handler();
        // }

        // Obtener y sanitizar los datos del formulario
        $username = sanitize_input($_POST['username']);
        if (check_duplicate_name($username)) {
        } else {
            $email = sanitize_input($_POST['email']);
            $role = sanitize_input($_POST['role']);
            $password = $_POST['password'];
            $retype_password = $_POST['retype_password'];

            // Validar que las contraseñas coinciden
            if ($password !== $retype_password) {
                message("Las contraseñas no coinciden", true, "error");
            } else {
                // Validar que la contraseña tenga al menos 8 caracteres
                if (validate_password($password)) {
                    // Validar el formato del correo electrónico
                    if (validate_email($email)) {
                        try {
                            // Verificar si el correo electrónico ya está en uso
                            $query = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
                            $data = [':email' => $email];
                            $result = db_query($query, $data);
                            $email_exists = ($result[0]['COUNT(*)'] > 0);

                            if ($email_exists) {
                                // Ejemplo de uso
                                $message = "El correo electrónico ya está en uso.";
                                message("Error al insertar datos: " . $message, true, "error");
                                header("Location: " . ROOT . "admin/usuarios/añadir");
                            } else {
                                // Insertar los datos en la base de datos
                                $query = "INSERT INTO usuarios (username, email, role, password, date) VALUES (:username, :email, :role, :password, :date)";
                                $data = [
                                    ':username' => $username,
                                    ':email' => $email,
                                    ':role' => 'user', //Valor rol USUARIO predeterminado
                                    ':password' => password_hash($password, PASSWORD_DEFAULT),
                                    ':date' => date("Y-m-d H:i:s")
                                ];
                                db_query($query, $data);
                                message("Usuario creado correctamente", true, "success");
                                header("Location: " . ROOT . "login");
                            }
                        } catch (PDOException $e) {
                            message("Error al insertar datos: " . $e->getMessage(), true, "error");
                        }
                    } else {
                        message("El formato del correo electrónico es inválido", true, "error");
                    }
                } else {
                    message("La contraseña debe tener al menos 8 caracteres", true, "error");
                }
            }
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
            <h1>Registro</h1>
        </div>
        <!-- <div class="header-right">
            <button id="registerBtn" title="registrarse" tabindex="0"></button>
        </div> -->
    </header>


    <?php if ($msg = message()) : ?>
        <div class="alert <?= $msg['estado'] == 'success' ? 'alert-success' : 'alert-danger' ?>">
            <?= $msg['text'] ?>
        </div>
    <?php endif; ?>
    <div class="contenedorRegistro" id="contenedorRegistro" style="
    
        padding: 50px;
        ">
        <div class="form-container">
            <!-- <h2>Registrarse</h2> -->
            <form id="myForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div style="padding: 10px; font-size: 25px;">Nombre</div>
                <input class="form-control" type="text" id="username" name="username" placeholder="Nombre de usuario" required style="padding-top: 20px;">
                <div style="padding: 10px; font-size: 25px;">Correo electrónico</div>
                <input class="form-control" type="email" id="email" name="email" placeholder="Correo electrónico" required style="padding-top: 20px;">
                <!-- <select class="form-control" id="role" name="role" required>
                    <option value="" disabled selected>Seleccione un rol</option>
                    <option value="admin">Administrador</option>
                    <option value="user">Usuario</option>
                </select> -->
                <div style="padding: 10px; font-size: 25px;">Contraseña</div>
                <input class="form-control" type="password" id="password" name="password" placeholder="Contraseña" required style="padding-top: 20px;">
                <div style="padding: 10px; font-size: 25px;">Confirmar contraseña</div>
                <input class="form-control" type="password" id="retype_password" name="retype_password" placeholder="Repite la contraseña" required style="padding-top: 20px;">
                <div class="button-group-adduser" style="
                    display: flex;
                    padding-top: 20px;
                    flex-direction: row;
                    justify-content: space-around;
                ">
                    <button id="buttonAddUser" class="buttonAddUser"
                    style="
    background-color: #007bff; /* Color azul */
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;

                    "
                    
                    >Crear</button>
                    <a class="button" href="<?= ROOT ?>login">Volver</a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p title="Derecho de autor">© 2024 Shockmuse Web. Todos los derechos reservados. <a href="accesibilidad.html" style="color: #fff; text-decoration: underline;" title="Ir a la página de accesibilidad">Accesibilidad</a></p>
    </footer>

</body>

</html>