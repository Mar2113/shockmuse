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
    header("Location: " . ROOT . "admin/generos");
    exit();
}

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    require page('includes/cabecera-admin');
}

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

                // Invalidar el token CSRF después de usarlo
                unset($_SESSION['csrf_token']);

                // Obtener y sanitizar los datos del formulario
                $category = sanitize_input($_POST['category']);
                $estado = sanitize_input($_POST['estado']);

                // Validar la longitud de la categoría
                if (strlen($category) > 0) {
                    try {
                        // Insertar los datos en la base de datos
                        $query = "INSERT INTO categorias (category, disabled) VALUES (:category, :disabled)";
                        $data = [
                            ':category' => $category,
                            ':disabled' => $estado
                        ];
                        db_query($query, $data);
                        message("Categoría creada correctamente", true, "success");
                        header("Location: " . ROOT . "admin/generos");
                    } catch (PDOException $e) {
                        message("Error al insertar datos: " . $e->getMessage(), true, "error");
                    }
                } else {
                    message("El nombre de la categoría no puede estar vacío", true, "error");
                }
            }
        } catch (Exception $e) {
            message("Error inesperado: " . $e->getMessage(), true, "error");
        }
    ?>
        <div class="form-container">
            <h2>Menú - Añadir Categoría</h2>
            <form id="myForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="form-group">
                    <label for="category">Nombre de la categoría</label>
                    <input class="form-control" type="text" id="category" name="category" placeholder="Nombre de la categoría" required>
                </div>
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select class="form-control" id="estado" name="estado" required>
                        <option value="" disabled selected>Seleccione un estado</option>
                        <option value="0">Visible</option>
                        <option value="1">Invisible</option>
                    </select>
                </div>
                <div class="button-group-adduser">
                    <button id="buttonAddUser" class="buttonAddUser" type="submit">Crear</button>
                    <a class="button" href="<?= ROOT ?>admin/generos">Volver</a>
                </div>
            </form>
        </div>


        <!-- ################################################################ -->
        <!--                       EDITAR                                             -->
        <!-- ################################################################ -->

        <?php elseif ($action == 'editar') :
        // Limpiar mensajes al cargar la página
        message('', true);

        // Obtener el ID de la categoría desde la URL
        $categoria_id = $URL[3];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar el formulario de edición
            try {
                $category = $_POST['category'];
                $estado = $_POST['estado'];

                // Actualizar los datos de la categoría en la base de datos
                $query = "UPDATE categorias SET category = :category, disabled = :disabled WHERE id = :id";
                $data = [
                    ':category' => $category,
                    ':disabled' => $estado,
                    ':id' => $categoria_id
                ];

                db_query($query, $data);
                message("Cambios realizados con éxito.", true, "success");
                header("Location: " . ROOT . "admin/generos");
            } catch (Exception $e) {
                // Manejar cualquier excepción que pueda ocurrir durante la actualización de datos
                error_handler($e);
                message("Error: " . $e->getMessage(), true, "error");
            }
        }

        // Obtener los datos de la categoría para pre-rellenar el formulario
        try {
            $query = "SELECT * FROM categorias WHERE id = :id";
            $data = [':id' => $categoria_id];
            $categoria = db_query_one($query, $data);

            if (!empty($categoria)) {
                // Asignar los valores de la categoría a las variables
                $category = $categoria['category'];
                $estado = $categoria['disabled'];
        ?>
                <div class="form-container">
                    <h2>Menú - Editar Categoría</h2>
                    <form id="myForm" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <div class="form-group">
                            <label for="category">Nombre de la categoría</label>
                            <input class="form-control" type="text" id="category" name="category" placeholder="Nombre de la categoría" value="<?= htmlspecialchars($category) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <select class="form-control" id="estado" name="estado" required>
                                <option value="0" <?= $estado == '0' ? 'selected' : '' ?>>Activo</option>
                                <option value="1" <?= $estado == '1' ? 'selected' : '' ?>>Desactivado</option>
                            </select>
                        </div>
                        <div class="button-group-adduser">
                            <button id="buttonAddUser" class="buttonAddUser">Guardar Cambios</button>
                            <a class="button" href="<?= ROOT ?>admin/generos">Volver</a>
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


        try {
            // Obtener la ID de la categoría de la URL
            $categoria_id = $URL[3];

            // Consulta para obtener los datos de la categoría a eliminar
            $query = "SELECT * FROM categorias WHERE id = :categoria_id";
            $data = [':categoria_id' => $categoria_id];
            $categoria = db_query_one($query, $data);

            // Mostrar los datos de la categoría a eliminar
            if (!empty($categoria)) {
                $category = $categoria['category'];
                $estado = $categoria['disabled'];

                // Si se envió el formulario para borrar la categoría
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_categoria'])) {
                    // Obtener la ID de la categoría a eliminar del formulario
                    $categoria_id_to_delete = $_POST['categoria_id'];

                    // Realizar la consulta SQL para eliminar la categoría
                    $delete_query = "DELETE FROM categorias WHERE id = :categoria_id";
                    $delete_data = [':categoria_id' => $categoria_id_to_delete];
                    db_query($delete_query, $delete_data);

                    // Mostrar mensaje de éxito
                    message("Categoría \"$category\" eliminada correctamente", true, "success");

                    // Redireccionar a la página de categorías
                    header("Location: " . ROOT . "admin/generos");
                    exit();
                }
        ?>
                <div class="form-container">
                    <h2>Menú - Eliminar Categoría</h2>
                    <p>Nombre: <?= $category ?></p>
                    <p>Estado: <?= $estado == 0 ? 'Activo' : 'Desactivado' ?></p>
                    <div class="button-group-adduser">
                        <form class="form-deleteCategoria" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="categoria_id" value="<?= $categoria_id ?>">
                            <button type="submit" name="delete_categoria" class="buttonAddUser">Borrar</button>
                        </form>
                        <a class="button" href="<?= ROOT ?>admin/generos">Volver</a>
                    </div>
                </div>
        <?php
            } else {
                message("No se encontraron datos de la categoría con ID $categoria_id", true, "warning");
            }
        } catch (Exception $e) {
            // Mostrar mensaje de error si se produce una excepción
            message("Error al intentar eliminar la categoría: " . $e->getMessage(), true, "error");
        }
        ?>



        <!-- ################################################################ -->
        <!--                       LISTAS                                             -->
        <!-- ################################################################ -->


    <?php else :
        try {
            $query = "SELECT * FROM categorias ORDER BY id DESC";
            $rows = db_query($query);
        } catch (Exception $e) {
            error_handler($e);
        }
    ?>
        <div class="contenedorTituloListaUsuarios">
            <div class="user-list-header">
                <h2>Lista de Géneros</h2>
                <button class="add-button"><a href="<?= ROOT ?>admin/generos/añadir"><i class="fas fa-plus"></i> Añadir</a></button>
            </div>
            <div class="contenedorListaUsuarios">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rows)) : ?>
                            <?php foreach ($rows as $row) : ?>
                                <tr>
                                    <td class="sortable" data-column="0"><?= $row['id'] ?></td>
                                    <td class="sortable" data-column="1"><?= $row['category'] ?></td>
                                    <td class="sortable" data-column="2"><?= $row['disabled'] == 0 ? 'Activo' : 'Desactivado' ?></td>
                                    <td class="listaUsuariosAcciones">
                                        <a href="<?= ROOT ?>admin/generos/editar/<?= $row['id'] ?>">
                                            <img class="bi" src="<?= ROOT ?>/assets/icons/pencil-square.svg" style="margin-right: 10px; margin-left: auto;">
                                        </a>
                                        <a href="<?= ROOT ?>admin/generos/borrar/<?= $row['id'] ?>">
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