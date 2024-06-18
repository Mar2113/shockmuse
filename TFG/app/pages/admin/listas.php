<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "../app/core/config.php";
require "../app/core/functions.php";

// Función para manejar errores CSRF de manera amigable
function csrf_error_handler()
{
    message("Error: Solicitud no válida. Por favor, inténtelo de nuevo.", true, "error");
    // header("Location: " . ROOT . "admin/listas");
    exit();
}

require page('includes/cabecera-admin');
?>

<section class="content-featured">

  <!-- ########################################       AÑADIR   -->

  <?php if ($action == 'añadir') :
        // Limpiar mensajes al cargar la página
        message('', true);

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Obtener y sanitizar los datos del formulario
                $name = sanitize_input($_POST['name']);
                $id_lista_tipo = sanitize_input($_POST['id_lista_tipo']);
                $user_id = sanitize_input($_POST['user_id']);
                $artist_id = sanitize_input($_POST['artist_id']);
                $songs = isset($_POST['songs']) ? $_POST['songs'] : [];

                // Insertar los datos en la base de datos
                $query = "INSERT INTO listas (name, id_lista_tipo, user_id, artist_id) VALUES (:name, :id_lista_tipo, :user_id, :artist_id)";
                $data = [
                    ':name' => $name,
                    ':id_lista_tipo' => $id_lista_tipo,
                    ':user_id' => $user_id,
                    ':artist_id' => $artist_id
                ];
                db_query($query, $data);

                // Obtener el ID de la lista recién creada
                $lista_id = db_last_insert_id(); // Aquí debes usar la función que te proporcioné en la respuesta anterior

                // Insertar canciones si es un álbum
                if ($id_lista_tipo == 1 && !empty($songs)) {
                    foreach ($songs as $song) {
                        $song_name = sanitize_input($song);
                        if (!empty($song_name)) {
                            $song_query = "INSERT INTO songs (lista_id, name) VALUES (:lista_id, :name)";
                            $song_data = [
                                ':lista_id' => $lista_id,
                                ':name' => $song_name
                            ];
                            db_query($song_query, $song_data);
                        }
                    }
                }

                message("Lista creada correctamente", true, "success");
                header("Location: " . ROOT . "admin/listas");
                exit();
            }
        } catch (Exception $e) {
            message("Error inesperado: " . $e->getMessage(), true, "error");
        }
    ?>
        <div class="form-container">
            <h2>Menú - Añadir Lista</h2>
            <form id="myForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="form-group">
                    <label for="name">Nombre de la lista</label>
                    <input class="form-control" type="text" id="name" name="name" placeholder="Nombre de la lista" required>
                </div>
                <div class="form-group">
                    <label for="id_lista_tipo">Tipo de Lista</label>
                    <select class="form-control" id="id_lista_tipo" name="id_lista_tipo" required>
                        <option value="" disabled selected>Seleccione un tipo</option>
                        <option value="1">Carpeta</option>
                        <option value="2">Álbum</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="user_id">ID de Usuario</label>
                    <input class="form-control" type="number" id="user_id" name="user_id">
                </div>
                <div class="form-group">
                    <label for="artist_id">ID de Artista</label>
                    <input class="form-control" type="number" id="artist_id" name="artist_id">
                </div>
                <div id="album-songs" style="display:none;">
                    <h3>Añadir Canciones</h3>
                    <div class="form-group">
                        <input class="form-control" type="text" name="songs[]" placeholder="Nombre de la canción">
                    </div>
                    <button type="button" onclick="addSongInput()">Añadir otra canción</button>
                </div>
                <div class="button-group-adduser">
                    <button id="buttonAddUser" class="buttonAddUser" type="submit">Crear</button>
                    <a class="button" href="<?= ROOT ?>admin/listas">Volver</a>
                </div>
            </form>
        </div>
        <script>
            document.getElementById('id_lista_tipo').addEventListener('change', function() {
                if (this.value == '2') {
                    document.getElementById('album-songs').style.display = 'block';
                } else {
                    document.getElementById('album-songs').style.display = 'none';
                }
            });

            function addSongInput() {
                var div = document.createElement('div');
                div.className = 'form-group';
                div.innerHTML = '<input class="form-control" type="text" name="songs[]" placeholder="Nombre de la canción">';
                document.getElementById('album-songs').appendChild(div);
            }
        </script>





        <!-- ################################################################ -->
        <!--                       EDITAR                                             -->
        <!-- ################################################################ -->

        <?php elseif ($action == 'editar') :
        // Limpiar mensajes al cargar la página
        message('', true);

        // Obtener el ID de la lista desde la URL
        $lista_id = $URL[3];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar el formulario de edición
            try {
                $name = $_POST['name'];
                $id_lista_tipo = $_POST['id_lista_tipo'];
                $user_id = $_POST['user_id'];
                $artist_id = $_POST['artist_id'];

                // Actualizar los datos de la lista en la base de datos
                $query = "UPDATE listas SET name = :name, id_lista_tipo = :id_lista_tipo, user_id = :user_id, artist_id = :artist_id WHERE id = :id";
                $data = [
                    ':name' => $name,
                    ':id_lista_tipo' => $id_lista_tipo,
                    ':user_id' => $user_id,
                    ':artist_id' => $artist_id,
                    ':id' => $lista_id
                ];

                db_query($query, $data);
                message("Cambios realizados con éxito.", true, "success");
                header("Location: " . ROOT . "admin/listas");
                exit();
            } catch (Exception $e) {
                // Manejar cualquier excepción que pueda ocurrir durante la actualización de datos
                error_handler($e);
                message("Error: " . $e->getMessage(), true, "error");
            }
        }

        // Obtener los datos de la lista para pre-rellenar el formulario
        try {
            $query = "SELECT * FROM listas WHERE id = :id";
            $data = [':id' => $lista_id];
            $lista = db_query_one($query, $data);

            if (!empty($lista)) {
                // Asignar los valores de la lista a las variables
                $name = $lista['name'];
                $id_lista_tipo = $lista['id_lista_tipo'];
                $user_id = $lista['user_id'];
                $artist_id = $lista['artist_id'];
        ?>
                <div class="form-container">
                    <h2>Menú - Editar Lista</h2>
                    <form id="myForm" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <div class="form-group">
                            <label for="name">Nombre de la lista</label>
                            <input class="form-control" type="text" id="name" name="name" placeholder="Nombre de la lista" value="<?= htmlspecialchars($name) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="id_lista_tipo">Tipo de Lista</label>
                            <select class="form-control" id="id_lista_tipo" name="id_lista_tipo" required>
                                <option value="0" <?= $id_lista_tipo == '0' ? 'selected' : '' ?>>Carpeta</option>
                                <option value="1" <?= $id_lista_tipo == '1' ? 'selected' : '' ?>>Álbum</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="user_id">ID de Usuario</label>
                            <input class="form-control" type="number" id="user_id" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
                        </div>
                        <div class="form-group">
                            <label for="artist_id">ID de Artista</label>
                            <input class="form-control" type="number" id="artist_id" name="artist_id" value="<?= htmlspecialchars($artist_id) ?>">
                        </div>
                        <div class="button-group-adduser">
                            <button id="buttonAddUser" class="buttonAddUser" type="submit">Guardar Cambios</button>
                            <a class="button" href="<?= ROOT ?>admin/listas">Volver</a>
                        </div>
                    </form>
                </div>
        <?php
            } else {
                message("No se encontraron datos de la lista con ID $lista_id", true, "warning");
                header("Location: " . ROOT . "admin/listas");
                exit();
            }
        } catch (Exception $e) {
            // Manejar cualquier excepción que pueda ocurrir durante la obtención de datos
            error_handler($e);
            message("Error: " . $e->getMessage(), true, "error");
        }
        ?>


        <!-- ################################################################ -->
        <!--                       BORRAR                                             -->
        <!-- ################################################################ -->

        <?php elseif ($action == 'borrar') :
        // Limpiar mensajes al cargar la página
        message('', true);

        // Obtener la ID de la lista de la URL
        $lista_id = $URL[3] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_lista'])) {
            // Validar token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                csrf_error_handler();
            }

            // Invalidar el token CSRF después de usarlo
            unset($_SESSION['csrf_token']);

            try {
                // Eliminar la lista de la base de datos
                $query = "DELETE FROM listas WHERE id = :id";
                $data = [':id' => $lista_id];
                db_query($query, $data);
                message("Lista eliminada correctamente", true, "success");
                header("Location: " . ROOT . "admin/listas");
                exit();
            } catch (Exception $e) {
                message("Error inesperado: " . $e->getMessage(), true, "error");
            }
        }

        // Obtener los datos de la lista para mostrar la confirmación
        try {
            $query = "SELECT * FROM listas WHERE id = :id";
            $data = [':id' => $lista_id];
            $lista = db_query_one($query, $data);

            if (!empty($lista)) {
                $name = $lista['name'];
        ?>
                <div class="form-container">
                    <h2>Menú - Borrar Lista</h2>
                    <form id="myForm" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <p>¿Está seguro que desea borrar la lista <strong><?= htmlspecialchars($name) ?></strong>?</p>
                        <div class="button-group-adduser">
                            <button id="buttonAddUser" class="buttonAddUser" name="delete_lista" type="submit">Borrar</button>
                            <a class="button" href="<?= ROOT ?>admin/listas">Volver</a>
                        </div>
                    </form>
                </div>
        <?php
            } else {
                message("No se encontraron datos de la lista con ID $lista_id", true, "warning");
                header("Location: " . ROOT . "admin/listas");
                exit();
            }
        } catch (Exception $e) {
            // Manejar cualquier excepción que pueda ocurrir durante la obtención de datos
            error_handler($e);
            message("Error: " . $e->getMessage(), true, "error");
        }
        ?>


        <!-- ################################################################ -->
        <!--                       LISTA                                            -->
        <!-- ################################################################ -->

    <?php else : ?>
        <?php
        $query = "SELECT * FROM listas";
        $listas = db_query($query);
        ?>
        <div class="contenedorTituloListaUsuarios">
            <div class="user-list-header">
                <h2>Listas</h2>
                <button class="add-button"><a href="<?= ROOT ?>admin/listas/añadir"><i class="fas fa-plus"></i> Añadir</a></button>
            </div>
            <div class="contenedorListaUsuarios">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Tipo de Lista</th>
                            <th>Usuario</th>
                            <th>Artista</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listas as $lista) : ?>
                            <tr>
                                <td><?= htmlspecialchars($lista['id']) ?></td>
                                <td><?= htmlspecialchars($lista['name']) ?></td>
                                <td><?= htmlspecialchars($lista['id_lista_tipo']) == 0 ? 'Carpeta' : 'Álbum' ?></td>
                                <td><?= htmlspecialchars($lista['user_id']) ?></td>
                                <td><?= htmlspecialchars($lista['artist_id']) ?></td>
                                <td>
                                    <a href="<?= ROOT ?>admin/listas/editar/<?= htmlspecialchars($lista['id']) ?>" class="button">Editar</a>
                                    <a href="<?= ROOT ?>admin/listas/borrar/<?= htmlspecialchars($lista['id']) ?>" class="button">Borrar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
</script>

<?php require page('includes/pie-admin'); ?>