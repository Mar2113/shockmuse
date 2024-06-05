<?php

// Verificar si hay una sesión de usuario activa
if (!isset($_SESSION)) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['USER']['id'])) {
    // Si el usuario no está autenticado, redirigir al inicio de sesión
    header("Location: " . ROOT . "login");
    exit();
}

// var_dump($_SESSION);


// Incluir el archivo de configuración y funciones
require "../app/core/config.php";
require "../app/core/functions.php";

// Limpiar mensajes al cargar la página
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    message('', true);
}

// Obtener el ID de usuario de la sesión
$user_id = $_SESSION['USER']['id'];

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener los datos del formulario
        $username = $_POST['username'];
        $password = $_POST['password'];
        $new_password = $_POST['new_password'];
        $retype_password = $_POST['retype_password'];

        // Obtener los datos del usuario desde la base de datos
        $query = "SELECT * FROM usuarios WHERE id = :user_id";
        $data = [':user_id' => $user_id];
        $user = db_query_one($query, $data);

        // Verificar si el usuario existe y si la contraseña es correcta
        if (!empty($user) && password_verify($password, $user['password'])) {
            // Verificar que las contraseñas nuevas coincidan
            if ($new_password === $retype_password) {
                // Actualizar los datos del usuario en la base de datos
                $query = "UPDATE usuarios SET username = :username, password = :password WHERE id = :user_id";
                $data = [
                    ':username' => $username,
                    ':password' => password_hash($new_password, PASSWORD_DEFAULT),
                    ':user_id' => $user_id
                ];
                db_query($query, $data);

                // Mostrar mensaje de éxito
                message("Cambios realizados con éxito.", true, "success");
            } else {
                throw new Exception("Las contraseñas nuevas no coinciden.");
            }
        } else {
            throw new Exception("La contraseña actual es incorrecta.");
        }
    } catch (Exception $e) {
        // Manejar cualquier excepción que pueda ocurrir durante la actualización de datos
        error_handler($e);
        message("Error: " . $e->getMessage(), true, "error");
    }
}

// Obtener los datos del usuario para pre-rellenar el formulario
try {
    $query = "SELECT * FROM usuarios WHERE id = :user_id";
    $data = [':user_id' => $user_id];
    $user = db_query_one($query, $data);

    if (!empty($user)) {
        // Asignar los valores del usuario a las variables
        $username = $user['username'];
?>
<?php require page('includes/cabecera') ?>
<section class="content-featured">
    <div class="form-container">
        <h2>Configuración de Usuario</h2>
        <form id="myForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-group">
                <label for="username">Nombre de usuario</label>
                <input class="form-control" type="text" id="username" name="username" placeholder="Nombre de usuario" value="<?= htmlspecialchars($username) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña actual</label>
                <input class="form-control" type="password" id="password" name="password" placeholder="Contraseña actual" required>
            </div>
            <div class="form-group">
                <label for="new_password">Nueva contraseña</label>
                <input class="form-control" type="password" id="new_password" name="new_password" placeholder="Nueva contraseña" required>
            </div>
            <div class="form-group">
                <label for="retype_password">Repetir nueva contraseña</label>
                <input class="form-control" type="password" id="retype_password" name="retype_password" placeholder="Repetir nueva contraseña" required>
            </div>
            <div class="button-group-adduser">
                <button id="buttonAddUser" class="buttonAddUser" type="submit">Guardar Cambios</button>
                <a class="button" href="<?= ROOT ?>admin/principal">Volver</a>
            </div>
        </form>
    </div>
</section>
<?php
    } else {
        // Si no se encontraron datos del usuario
        message("No se encontraron datos del usuario con ID " . $user_id, true, "warning");
    }
} catch (Exception $e) {
    // Manejar cualquier excepción que pueda ocurrir durante la obtención de datos
    error_handler($e);
}
?>
</section>
<?php require page('includes/pie') ?>
