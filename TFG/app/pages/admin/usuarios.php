<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// session_start();

require "../app/core/config.php";
require "../app/core/functions.php";

// Función para manejar errores CSRF de manera amigable
function csrf_error_handler()
{
    message("Error: Solicitud no válida. Por favor, inténtelo de nuevo.", true, "error");
    header("Location: " . ROOT . "/admin/usuarios");
    exit();
}



// SEGURIDAD - seguridad
// Generar token CSRF
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

require page('includes/cabecera-admin');
?>

<section class="content-featured">

    <!-- ########################################       AÑADIR   -->

    <?php if ($action == 'añadir') :
        // Limpiar mensajes al cargar la página
        message('', true);

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validar token CSRF
                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                    csrf_error_handler();
                }

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
                                        $message = "El correo electrónico ya está en uso. ¿Desea recuperar la cuenta antigua o crear una nueva?";
                                        $yes_action = 'console.log("Recuperar cuenta antigua");'; // Acción para Sí
                                        $no_action = 'console.log("Crear nueva cuenta");'; // Acción para No
                                        $confirmation_dialog = show_confirmation_dialog($message, $yes_action, $no_action);
                                        echo $confirmation_dialog;
                                    } else {
                                        // Insertar los datos en la base de datos
                                        $query = "INSERT INTO usuarios (username, email, role, password, date) VALUES (:username, :email, :role, :password, :date)";
                                        $data = [
                                            ':username' => $username,
                                            ':email' => $email,
                                            ':role' => $role,
                                            ':password' => password_hash($password, PASSWORD_DEFAULT),
                                            ':date' => date("Y-m-d H:i:s")
                                        ];
                                        db_query($query, $data);
                                        message("Usuario creado correctamente", true, "success");
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

        <div class="form-container">
            <h2>Menú - Añadir Usuario</h2>
            <form id="myForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input class="form-control" type="text" id="username" name="username" placeholder="Nombre de usuario" required>
                <input class="form-control" type="email" id="email" name="email" placeholder="Correo electrónico" required>
                <select class="form-control" id="role" name="role" required>
                    <option value="" disabled selected>Seleccione un rol</option>
                    <option value="admin">Administrador</option>
                    <option value="user">Usuario</option>
                </select>
                <input class="form-control" type="password" id="password" name="password" placeholder="Contraseña" required>
                <input class="form-control" type="password" id="retype_password" name="retype_password" placeholder="Repite la contraseña" required>
                <div class="button-group-adduser">
                    <button id="buttonAddUser" class="buttonAddUser">Crear</button>
                    <a class="button" href="<?= ROOT ?>admin/usuarios">Volver</a>
                </div>
            </form>
        </div>

        <!-- ########################################       EDITAR   -->

        <?php elseif ($action == 'editar') :
        // Limpiar mensajes al cargar la página
        message('', true);

        // Obtener el ID del usuario desde la URL
        $user_id = $URL[3];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar el formulario de edición
            try {
                $username = $_POST['username'];
                $email = $_POST['email'];
                $role = $_POST['role'];
                $password = $_POST['password'];
                $retype_password = $_POST['retype_password'];

                // Verificar que las contraseñas coincidan
                if ($password !== $retype_password) {
                    throw new Exception("Las contraseñas no coinciden.");
                }

                // Actualizar los datos del usuario en la base de datos
                $query = "UPDATE usuarios SET username = :username, email = :email, role = :role, password = :password WHERE id = :id";
                $data = [
                    ':username' => $username,
                    ':email' => $email,
                    ':role' => $role,
                    ':password' => password_hash($password, PASSWORD_DEFAULT),
                    ':id' => $user_id
                ];

                db_query($query, $data);
                message("Cambios realizados con éxito.", true, "success");
            } catch (Exception $e) {
                // Manejar cualquier excepción que pueda ocurrir durante la actualización de datos
                error_handler($e);
                message("Error: " . $e->getMessage(), true, "error");
            }
        }

        // Obtener los datos del usuario para pre-rellenar el formulario
        try {
            $query = "SELECT * FROM usuarios WHERE id = :id";
            $data = [':id' => $user_id];
            $user = db_query_one($query, $data);

            if (!empty($user)) {
                // Asignar los valores del usuario a las variables
                $username = $user['username'];
                $email = $user['email'];
                $role = $user['role'];
        ?>
                <div class="form-container">
                    <h2>Menú - Editar Usuario</h2>
                    <form id="myForm" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input class="form-control" type="text" id="username" name="username" placeholder="Nombre de usuario" value="<?= htmlspecialchars($username) ?>" required>
                        <input class="form-control" type="email" id="email" name="email" placeholder="Correo electrónico" value="<?= htmlspecialchars($email) ?>" required>
                        <select class="form-control" id="role" name="role" required>
                            <option value="" disabled>Seleccione un rol</option>
                            <option value="admin" <?= $role == 'admin' ? 'selected' : '' ?>>Administrador</option>
                            <option value="user" <?= $role == 'user' ? 'selected' : '' ?>>Usuario</option>
                        </select>
                        <input class="form-control" type="password" id="password" name="password" placeholder="Contraseña" required>
                        <input class="form-control" type="password" id="retype_password" name="retype_password" placeholder="Repite la contraseña" required>
                        <div class="button-group-adduser">
                            <button id="buttonAddUser" class="buttonAddUser">Guardar Cambios</button>
                            <a class="button" href="<?= ROOT ?>admin/usuarios">Volver</a>
                        </div>
                    </form>
                </div>
        <?php
            } else {
                // Si no se encontraron datos del usuario, mostrar un mensaje de error
                message("No se encontraron datos del usuario con ID " . $URL[3], true, "warning");
            }
        } catch (Exception $e) {
            // Manejar cualquier excepción que pueda ocurrir durante la obtención de datos
            error_handler($e);
        }
        ?>

        <!-- ########################################       BORRAR   -->


        <?php elseif ($action == 'borrar') :
        // Limpiar mensajes al cargar la página
        message('', true);
        try {
            // Obtener la ID del usuario de la URL
            $user_id = $URL[3];

            // Consulta para obtener los datos del usuario a eliminar
            $query = "SELECT * FROM usuarios WHERE id = :user_id";
            $data = [':user_id' => $user_id];
            $user = db_query_one($query, $data);

            // Mostrar los datos del usuario a eliminar
            if (!empty($user)) {
                $username = $user['username'];
                $email = $user['email'];
                $role = $user['role'];

                // Si se envió el formulario para borrar al usuario
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
                    // Obtener la ID del usuario a eliminar del formulario
                    $user_id_to_delete = $_POST['user_id'];

                    // Realizar la consulta SQL para eliminar al usuario
                    $delete_query = "DELETE FROM usuarios WHERE id = :user_id";
                    $delete_data = [':user_id' => $user_id_to_delete];
                    db_query($delete_query, $delete_data);

                    // Mostrar mensaje de éxito
                    message("Usuario \"$username\" eliminado correctamente", true, "success");

                    // Redireccionar a /usuarios
                    header("Location: " . ROOT . "admin/usuarios");
                    exit();
                }
        ?>
                <div class="form-container">
                    <h2>Menú - Eliminar Usuario</h2>
                    <p>Nombre: <?= $username ?></p>
                    <p>Correo: <?= $email ?></p>
                    <p>Rol: <?= $role ?></p>
                    <div class="button-group-adduser">
                        <form class="form-deleteUser" ="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="user_id" value="<?= $user_id ?>">
                            <button type="submit" name="delete_user" class="buttonAddUser">Borrar</button>
                        </form>
                        <a class="button" href="<?= ROOT ?>admin/usuarios">Volver</a>
                    </div>
                </div>
        <?php
            } else {
                message("No se encontraron datos del usuario con ID $user_id", true, "warning");
            }
        } catch (Exception $e) {
            // Mostrar mensaje de error si se produce una excepción
            message("Error al intentar eliminar al usuario: " . $e->getMessage(), true, "error");
        }
        ?>




        <!-- ########################################       LISTAS   -->

    <?php else :
        // // Limpiar mensajes al cargar la página
        // if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        //     message('', true);
        // }

        try {
            $query = "select * from usuarios order by id desc "; //limit 20
            $rows = db_query($query);
        } catch (Exception $e) {
            error_handler($e);
        }

        ?>
        <div class="contenedorTituloListaUsuarios">
            <div class="user-list-header">
                <h2>Lista de Usuarios</h2>
                <button class="add-button"><a href="<?= ROOT ?>admin/usuarios/añadir"><i class="fas fa-plus"></i> Añadir</a></button>
            </div>
            <div class="contenedorListaUsuarios">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th class="columnaNombre">Nombre</th>
                            <th class="columnaCorreo">Correo</th>
                            <th>Rol</th>
                            <th>Fecha</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rows)) : ?>
                            <?php foreach ($rows as $row) : ?>
                                <tr>
                                    <th class="sortable" data-column="0"><?= $row['id'] ?></th>
                                    <th class="sortable columnaNombre" data-column="1"><?= $row['username'] ?></th>
                                    <th class="sortable columnaCorreo" data-column="2"><?= $row['email'] ?></th>
                                    <th class="sortable" data-column="3"><?= $row['role'] ?></th>
                                    <th class="sortable" data-column="4"><?= get_date($row['date']) ?></th>

                                    <td class="listaUsuariosAcciones">
                                        <a href="<?= ROOT ?>admin/usuarios/editar/<?= $row['id'] ?>">
                                            <img class="bi" src="<?= ROOT ?>/assets/icons/pencil-square.svg" style="margin-right: 10px; margin-left: auto;">
                                        </a>
                                        <a href="<?= ROOT ?>admin/usuarios/borrar/<?= $row['id'] ?>">
                                            <img class="bi" src="<?= ROOT ?>/assets/icons/trash3.svg">
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var cells = document.querySelectorAll('.sortable');

        cells.forEach(function(cell) {
            cell.addEventListener('click', function() {
                var column = this.dataset.column;
                sortTable(column);
            });
        });

        function sortTable(column) {
            var table, rows, switching, i, x, y, shouldSwitch;
            table = document.querySelector(".table");
            switching = true;

            while (switching) {
                switching = false;
                rows = table.rows;

                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[column];
                    y = rows[i + 1].getElementsByTagName("TD")[column];

                    if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                        shouldSwitch = true;
                        break;
                    }
                }

                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                }
            }
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        var form = document.getElementById('myForm');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            form.submit();
        });
    });

    // Función para mostrar el cuadro de diálogo de confirmación
    function showConfirmationDialog(message, yesAction, noAction) {
        var confirmationDialog = document.getElementById("confirmationDialog");
        var confirmationMessage = document.getElementById("confirmationMessage");
        var confirmYes = document.getElementById("confirmYes");
        var confirmNo = document.getElementById("confirmNo");

        confirmationMessage.textContent = message;

        confirmYes.onclick = function() {
            confirmationDialog.style.display = "none";
            eval(yesAction);
        };

        confirmNo.onclick = function() {
            confirmationDialog.style.display = "none";
            eval(noAction);
        };

        confirmationDialog.style.display = "flex";
    }

    // Ejemplo de uso
    var message = "El correo electrónico ya está en uso. ¿Desea recuperar la cuenta antigua o crear una nueva?";
    var yesAction = 'console.log("Recuperar cuenta antigua");'; // Acción para Sí
    var noAction = 'console.log("Crear nueva cuenta");'; // Acción para No
    showConfirmationDialog(message, yesAction, noAction);
</script>

<?php require page('includes/pie-admin'); ?>