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
    header("Location: " . ROOT . "admin/artistas");
    exit();
}

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

require page('includes/cabecera-admin');
?>

<section class="content-featured">

    <!-- ########################################       AÑADIR   -->

    <?php if ($action == 'añadir') :
        // Limpiar mensajes al cargar la página
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            message('', true);
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validar token CSRF
                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                    csrf_error_handler();
                }

                // Obtener y sanitizar los datos del formulario
                $name = sanitize_input($_POST['name']);
                $bio = sanitize_input($_POST['bio']);
                $user_id = $_SESSION['USER']['id']; // Obtener el ID de usuario de la sesión

                // Procesar la imagen subida
                $image = null;
                if (!empty($_FILES['image']['name'])) {
                    $folder = "uploads/";
                    if (!file_exists($folder)) {
                        mkdir($folder, 0777, true);
                        file_put_contents($folder . "index.php", "");
                    }

                    $allowed = ['image/jpeg', 'image/png'];
                    if ($_FILES['image']['error'] == 0 && in_array($_FILES['image']['type'], $allowed)) {
                        $image = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $destination = $folder . $image;
                        move_uploaded_file($_FILES['image']['tmp_name'], $destination);
                    } else {
                        throw new Exception("Imagen no válida. Los tipos de archivo permitidos son " . implode(",", $allowed));
                    }
                } else {
                    throw new Exception("Se requiere una imagen");
                }

                // Insertar los datos en la base de datos
                $query = "INSERT INTO artistas (name, bio, user_id, image) VALUES (:name, :bio, :user_id, :image)";
                $data = [
                    ':name' => $name,
                    ':bio' => $bio,
                    ':user_id' => $user_id,
                    ':image' => $image // Guardar solo el nombre de archivo
                ];
                db_query($query, $data);
                message("Artista añadido correctamente", true, "success");
                header("Location: " . ROOT . "admin/artistas");
            }
        } catch (Exception $e) {
            message("Error inesperado: " . $e->getMessage(), true, "error");
        }
    ?>



        <div class="form-container">
            <h2>Añadir Artista</h2>
            <form id="myForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input class="form-control" type="text" id="name" name="name" placeholder="Nombre del artista" required>
                <textarea class="form-control" id="bio" name="bio" placeholder="Biografía" rows="4" required></textarea>
                <input type="file" id="image" name="image" accept="image/*" required>
                <div class="button-group-adduser">
                    <button id="buttonAddUser" class="buttonAddUser">Añadir Artista</button>
                    <a class="button" href="<?= ROOT ?>admin/artistas">Volver</a>
                </div>
            </form>
        </div>




        <!-- ################################################################ -->
        <!--                       EDITAR                                             -->
        <!-- ################################################################ -->

        <?php elseif ($action == 'editar') :
        // Limpiar mensajes al cargar la página
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            message('', true);
        }

        // Obtener el ID del artista desde la URL
        $artista_id = intval($URL[3]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar el formulario de edición
            try {
                $name = $_POST['name'];
                $bio = $_POST['bio'];
                $image = $_FILES['image'];

                // Procesar la imagen subida
                $image_name = null;
                if (!empty($image['name'])) {
                    $folder = "uploads/";
                    if (!file_exists($folder)) {
                        mkdir($folder, 0777, true);
                        file_put_contents($folder . "index.php", "");
                    }

                    $allowed = ['image/jpeg', 'image/png'];
                    if ($image['error'] == 0 && in_array($image['type'], $allowed)) {
                        $image_name = uniqid() . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
                        $destination = $folder . $image_name;
                        move_uploaded_file($image['tmp_name'], $destination);
                    } else {
                        throw new Exception("Imagen no válida. Los tipos de archivo permitidos son " . implode(",", $allowed));
                    }
                }

                // Obtener la imagen existente si no se subió una nueva
                if (empty($image_name)) {
                    $query = "SELECT image FROM artistas WHERE id = :id";
                    $data = [':id' => $artista_id];
                    $existing_image = db_query_one($query, $data)['image'];
                }

                // Actualizar los datos del artista en la base de datos
                $query = "UPDATE artistas SET name = :name, bio = :bio, image = :image WHERE id = :id";
                $data = [
                    ':name' => $name,
                    ':bio' => $bio,
                    ':image' => isset($image_name) ? $image_name : $existing_image,
                    ':id' => $artista_id
                ];

                db_query($query, $data);
                message("Cambios realizados con éxito.", true, "success");
                header("Location: " . ROOT . "admin/artistas");
                exit();
            } catch (Exception $e) {
                // Manejar cualquier excepción que pueda ocurrir durante la actualización de datos
                error_handler($e);
                message("Error: " . $e->getMessage(), true, "error");
            }
        }

        // Obtener los datos del artista para pre-rellenar el formulario
        try {
            $query = "SELECT * FROM artistas WHERE id = :id";
            $data = [':id' => $artista_id];
            $artista = db_query_one($query, $data);

            if (!empty($artista)) {
                // Asignar los valores del artista a las variables
                $name = $artista['name'];
                $bio = $artista['bio'];
                $image = $artista['image'];
        ?>
                <div class="form-container">
                    <h2>Editar Artista</h2>
                    <form id="myForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <div class="form-group">
                            <label for="name">Nombre del artista</label>
                            <input class="form-control" type="text" id="name" name="name" placeholder="Nombre del artista" value="<?= htmlspecialchars($name) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="bio">Biografía</label>
                            <textarea class="form-control" id="bio" name="bio" placeholder="Biografía" rows="4" required><?= htmlspecialchars($bio) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="image">Imagen del artista</label>
                            <img src="<?= ROOT ?>uploads/<?= $image ?>" alt="<?= htmlspecialchars($name) ?>" style="width: 100px; height: 100px;">
                            <input type="file" id="image" name="image" accept="image/*">
                        </div>
                        <div class="button-group-adduser">
                            <button id="buttonAddUser" class="buttonAddUser">Guardar Cambios</button>
                            <a class="button" href="<?= ROOT ?>admin/artistas">Volver</a>
                        </div>
                    </form>
                </div>
        <?php
            } else {
                // Si no se encontraron datos de la categoría, mostrar un mensaje de error
                message("No se encontraron datos de la categoría con ID " . $URL[3], true, "warning");
            }
        } catch (Exception $e) {
            // Manejar cualquier excepción que pueda ocurrir durante la obtención de datos
            error_handler($e);
        }
        ?>


        <!-- ################################################################ -->
        <!--                       BORRAR                                             -->
        <!-- ################################################################ -->
        
        <?php elseif ($action == 'borrar') :


        // Limpiar mensajes al cargar la página
        message('', true);


    // Obtener el ID del artista desde la URL
    $artista_id = intval($URL[3]);

    try {
        // Obtener los datos del artista
        $query = "SELECT * FROM artistas WHERE id = :artista_id";
        $data = [':artista_id' => $artista_id];
        $artista = db_query_one($query, $data);

        if (!empty($artista)) {
            // Asignar los valores del artista a las variables
            $name = $artista['name'];
            $bio = $artista['bio'];
            $image = $artista['image'];

                    // Si se envió el formulario para borrar el artista
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_artista'])) {
            // Obtener la ID del artista a eliminar del formulario
            $artista_id_to_delete = $_POST['artista_id'];

            // Realizar la consulta SQL para eliminar el artista
            $delete_query = "DELETE FROM artistas WHERE id = :artista_id";
            $delete_data = [':artista_id' => $artista_id_to_delete];
            db_query($delete_query, $delete_data);

            // Mostrar mensaje de éxito
            message("Artista eliminado correctamente", true, "success");

            // Redireccionar a la página de artistas
            header("Location: " . ROOT . "admin/artistas");
            exit();
        }
?>
            <div class="form-container">
                <h2>Eliminar Artista</h2>
                <p>Nombre: <?= $name ?></p>
                <p>Biografía: <?= $bio ?></p>
                <?php if (!empty($image)) : ?>
                    <img src="<?= ROOT ?>uploads/<?= $image ?>" alt="<?= htmlspecialchars($name) ?>" style="width: 100px; height: 100px;">
                <?php endif; ?>
                <p>¿Estás seguro de que deseas eliminar este artista?</p>
                <div class="button-group-adduser">
                    <form class="form-deleteArtista" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="artista_id" value="<?= $artista_id ?>">
                        <button type="submit" name="delete_artista" class="buttonAddUser">Eliminar</button>
                    </form>
                    <a class="button" href="<?= ROOT ?>admin/artistas">Volver</a>
                </div>
            </div>
<?php
        } else {
            // Mostrar mensaje de error si no se encuentra el artista
            message("No se encontraron datos del artista con ID $artista_id", true, "warning");
        }
    } catch (Exception $e) {
        // Mostrar mensaje de error si se produce una excepción
        message("Error al intentar eliminar el artista: " . $e->getMessage(), true, "error");
    }

?>



        <!-- ########################################       LISTAS   -->

    <?php else :
        try {
            $query = "SELECT * FROM artistas ORDER BY id ASC";
            $rows = db_query($query);
        } catch (Exception $e) {
            error_handler($e);
        }
    ?>
        <div class="contenedorTituloListaUsuarios">
            <div class="user-list-header">
                <h2>Lista de Artistas</h2>
                <button class="add-button"><a href="<?= ROOT ?>admin/artistas/añadir"><i class="fas fa-plus"></i> Añadir</a></button>
            </div>
            <div class="contenedorListaUsuarios">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th class="columnaNombre">Nombre</th>
                            <th class="columnaCorreo">Imagen</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rows)) : ?>
                            <?php foreach ($rows as $row) : ?>
                                <tr>
                                    <td class="sortable" data-column="0"><?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="sortable columnaNombre" data-column="1"><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="sortable">
                                        <?php if (!empty($row['image']) && file_exists("uploads/" . $row['image'])) : ?>
                                            <img src="<?= ROOT ?>uploads/<?= htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>" style="width: 100px; height: 100px;">
                                        <?php else : ?>
                                            No Image
                                        <?php endif; ?>
                                    </td>
                                    <td class="listaUsuariosAcciones">
                                        <a href="<?= ROOT ?>admin/artistas/editar/<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>">
                                            <img class="bi" src="<?= ROOT ?>/assets/icons/pencil-square.svg" style="margin-right: 10px; margin-left: auto;">
                                        </a>
                                        <a href="<?= ROOT ?>admin/artistas/borrar/<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>">
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
</script>

<?php require page('includes/pie-admin'); ?>