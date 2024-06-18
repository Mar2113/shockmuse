<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// session_start(); // Asegúrate de que la sesión esté iniciada

// Función para manejar errores CSRF de manera amigable
function csrf_error_handler()
{
    message("Error: Solicitud no válida. Por favor, inténtelo de nuevo.", true, "error");
    header("Location: " . ROOT . "admin/canciones");
    exit();
}

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// if (!isset($_SESSION['USER']['id'])) {
//     // Manejar el error: la sesión no tiene un 'user_id'
//     var_dump($_SESSION);
//     die('Error: user_id no está definido en la sesión.');
// }

$user_id = $_SESSION['USER']['id'];

try {
    require "../app/core/config.php";
    require "../app/core/functions.php";
} catch (Exception $e) {
    // Manejar cualquier excepción que pueda ocurrir durante la inclusión de archivos y la configuración inicial
    error_handler($e);
    die("Error durante la carga de archivos y configuración inicial: " . $e->getMessage());
}

if ($action == 'añadir') :
    // Limpiar mensajes al cargar la página
    message('', true);

    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                // csrf_error_handler();
            }

            // Invalidar el token CSRF después de usarlo
            unset($_SESSION['csrf_token']);

            // Función para generar un slug
            function generate_slug($string)
            {
                $string = strtolower($string);
                $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
                $string = preg_replace('/[\s-]+/', '-', $string);
                return trim($string, '-');
            }

            // Obtener y sanitizar los datos del formulario
            $title = sanitize_input($_POST['title']);
            $slug = generate_slug($title); // Generar slug a partir del título
            $genero_id = sanitize_input($_POST['genero']);
            $artista_id = sanitize_input($_POST['artista']);
            $date = date('Y-m-d H:i:s');
            $views = 0;

            // Verificar si se cargó una imagen
            if (!empty($_FILES['image']['name'])) {
                $folder = "uploads/";
                if (!file_exists($folder)) {
                    mkdir($folder, 0777, true);
                    file_put_contents($folder . "index.php", "");
                }

                $allowed_image_types = ['image/jpeg', 'image/png'];
                if ($_FILES['image']['error'] == 0 && in_array($_FILES['image']['type'], $allowed_image_types)) {
                    $destination_image = $folder . $_FILES['image']['name'];
                    move_uploaded_file($_FILES['image']['tmp_name'], $destination_image);
                } else {
                    message("Error: Imagen no válida. Tipos permitidos: " . implode(",", $allowed_image_types), true, "error");
                    // header("Location: " . ROOT . "admin/canciones/añadir");
                    // exit();
                }
            } else {
                message("Error: Se requiere una imagen", true, "error");
                header("Location: " . ROOT . "admin/canciones/añadir");
                exit();
            }

            // Verificar si se cargó un archivo de audio
            if (!empty($_FILES['file']['name'])) {
                $allowed_audio_types = ['audio/mpeg'];
                if ($_FILES['file']['error'] == 0 && in_array($_FILES['file']['type'], $allowed_audio_types)) {
                    $folder = "uploads/";
                    if (!file_exists($folder)) {
                        mkdir($folder, 0777, true);
                        file_put_contents($folder . "index.php", "");
                    }

                    $destination_file = $folder . $_FILES['file']['name'];
                    move_uploaded_file($_FILES['file']['tmp_name'], $destination_file);
                } else {
                    message("Error: Archivo de audio no válido. Tipos permitidos: " . implode(",", $allowed_audio_types), true, "error");
                    header("Location: " . ROOT . "admin/canciones/añadir");
                    exit();
                }
            } else {
                message("Error: Se requiere un archivo de audio", true, "error");
                header("Location: " . ROOT . "admin/canciones/añadir");
                exit();
            }

            // Insertar los datos en la base de datos
            $query = "INSERT INTO canciones (user_id, artist_id, image, category_id, date, views, file, slug, title) VALUES (:user_id, :artist_id, :image, :category_id, :date, :views, :file, :slug, :title)";
            $data = [
                ':user_id' => $user_id,
                ':artist_id' => $artista_id,
                ':image' => $destination_image,
                ':category_id' => $genero_id,
                ':date' => $date,
                ':views' => $views,
                ':file' => $destination_file,
                ':slug' => $slug,
                ':title' => $title
            ];
            db_query($query, $data);
            message("Canción creada correctamente", true, "success");
            header("Location: " . ROOT . "admin/canciones");
            // exit();
        } else {
            require page('includes/cabecera-admin');
        }

        // Obtener lista de géneros y artistas desde la base de datos
        $query_generos = "SELECT * FROM categorias";
        $generos = db_query($query_generos);

        $query_artistas = "SELECT * FROM artistas";
        $artistas = db_query($query_artistas);
    } catch (Exception $e) {
        message("Error inesperado: " . $e->getMessage(), true, "error");
    }


?>

    <section class="content-featured">

        <!-- ########################################       AÑADIR   -->



        <div class="contenedorFormulario">
            <h2>Añadir Nueva Canción</h2>
            <form id="myForm" action="<?= ROOT ?>admin/canciones/añadir" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Título:</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="image">Imagen:</label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="genero">Género:</label>
                    <select id="genero" name="genero" class="form-control" required>
                        <option value="">Seleccionar género</option>
                        <?php foreach ($generos as $genero) : ?>
                            <option value="<?= $genero['id'] ?>"><?= $genero['category'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="artista">Artista:</label>
                    <select id="artista" name="artista" class="form-control" required>
                        <option value="">Seleccionar artista</option>
                        <?php foreach ($artistas as $artista) : ?>
                            <option value="<?= $artista['id'] ?>"><?= $artista['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="file">Audio:</label>
                    <input type="file" id="file" name="file" accept="audio/mpeg" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="slug">Slug:</label>
                    <input type="text" id="slug" name="slug" class="form-control" readonly required>
                </div>
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" class="btn btn-primary">Añadir Canción</button>
            </form>
        </div>

        <script>
            document.getElementById('title').addEventListener('input', function() {
                var title = this.value;
                var slug = generateSlug(title);
                document.getElementById('slug').value = slug;
            });

            function generateSlug(text) {
                return text.toString().toLowerCase()
                    .replace(/\s+/g, '-') // Reemplazar espacios por -
                    .replace(/[^\w\-]+/g, '') // Eliminar todos los caracteres no alfanuméricos
                    .replace(/\-\-+/g, '-') // Reemplazar múltiples - por uno solo
                    .replace(/^-+/, '') // Eliminar - al inicio
                    .replace(/-+$/, ''); // Eliminar - al final
            }
        </script>


        <?php
    elseif ($action == 'editar') :
        // Limpiar mensajes al cargar la página
        message('', true);

        // Obtener el ID de la canción desde la URL
        $cancion_id = $URL[3];
        try {
            // Validar token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                // csrf_error_handler();
            }

            // Obtener lista de géneros y artistas desde la base de datos
            $query_generos = "SELECT * FROM categorias";
            $generos = db_query($query_generos);

            $query_artistas = "SELECT * FROM artistas";
            $artistas = db_query($query_artistas);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    // Procesar el formulario de edición

                    // Obtener y sanitizar los datos del formulario
                    $title = sanitize_input($_POST['title']);
                    $category_id = sanitize_input($_POST['category_id']);
                    $artist_id = sanitize_input($_POST['artist_id']);
                    $slug = sanitize_input($_POST['slug']);
                    $file = $_FILES['file'];

                    if (!empty($_FILES['image']['name'])) {
                        $image = $_FILES['image'];
                    } else {
                        // Si no se cargó una nueva imagen, mantener la imagen existente
                        $query_image = "SELECT image FROM canciones WHERE id = :id";
                        $image = db_query_one($query_image, [':id' => $cancion_id])['image'];
                    }

                    // Actualizar los datos de la canción en la base de datos
                    $query = "UPDATE canciones SET title = :title, category_id = :category_id, artist_id = :artist_id, image = :image, file = :file, slug = :slug WHERE id = :id";
                    $data = [
                        ':title' => $title,
                        ':category_id' => $category_id,
                        ':artist_id' => $artist_id,
                        ':image' => $image,
                        // Cambia $file por $file['name'] para obtener solo el nombre del archivo
                        ':file' => $file['name'],
                        ':id' => $cancion_id,
                        ':slug' => $slug
                    ];

                    db_query($query, $data);
                    message("Cambios realizados con éxito.", true, "success");
                    header("Location: " . ROOT . "admin/canciones");
                } catch (Exception $e) {
                    // Manejar cualquier excepción que pueda ocurrir durante la actualización de datos
                    error_handler($e);
                    message("Error: " . $e->getMessage(), true, "error");
                }
            }  else {
                require page('includes/cabecera-admin');
            }
        } catch (Exception $e) {
            // Manejar cualquier excepción que pueda ocurrir durante la obtención de datos preliminares
            error_handler($e);
            message("Error: " . $e->getMessage(), true, "error");
        }


        // Obtener los datos de la canción para pre-rellenar el formulario
        try {
            $query = "SELECT * FROM canciones WHERE id = :id";
            $data = [':id' => $cancion_id];
            $cancion = db_query_one($query, $data);

            if (!empty($cancion)) {
                // Asignar los valores de la canción a las variables
                $title = $cancion['title'];
                $category_id = $cancion['category_id'];
                $artist_id = $cancion['artist_id'];
        ?>
                <form id="myForm" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Título:</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?= $title ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="image">Imagen:</label>
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png" class="form-control">
                        <?php if (empty($_FILES['image']['name'])) : ?>
                            <p>Ruta predeterminada: <?= $cancion['image'] ?></p>
                        <?php endif; ?>
                        <input type="hidden" name="current_image" value="<?= $cancion['image'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="genero">Género:</label>
                        <select id="genero" name="category_id" class="form-control" required>
                            <option value=""><?php echo get_category($category_id); ?></option>
                            <?php foreach ($generos as $genero) : ?>
                                <option value="<?= $genero['id'] ?>" <?= ($genero['id'] == $category_id) ? 'selected' : '' ?>>
                                    <?= $genero['category'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="artista">Artista:</label>
                        <select id="artista" name="artist_id" class="form-control" required>
                            <option value=""><?php echo get_artist($artist_id); ?></option>
                            <?php foreach ($artistas as $artista) : ?>
                                <option value="<?= $artista['id'] ?>" <?= ($artista['id'] == $artist_id) ? 'selected' : '' ?>>
                                    <?= $artista['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="file">Audio:</label>
                        <input type="file" id="file" name="file" accept="audio/mpeg" class="form-control">
                        <?php if (empty($_FILES['file']['name'])) : ?>
                            <p>Ruta predeterminada: <?= $cancion['file'] ?></p>
                        <?php endif; ?>
                        <input type="hidden" name="current_file" value="<?= $cancion['file'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug:</label>
                        <input type="text" id="slug" name="slug" class="form-control" value="<?= $cancion['slug'] ?>" readonly required>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>

                <script>
                    document.getElementById('title').addEventListener('input', function() {
                        var title = this.value;
                        var slug = generateSlug(title);
                        document.getElementById('slug').value = slug;
                    });

                    function generateSlug(text) {
                        return text.toString().toLowerCase()
                            .replace(/\s+/g, '-') // Reemplazar espacios por -
                            .replace(/[^\w\-]+/g, '') // Eliminar todos los caracteres no alfanuméricos
                            .replace(/\-\-+/g, '-') // Reemplazar múltiples - por uno solo
                            .replace(/^-+/, '') // Eliminar - al inicio
                            .replace(/-+$/, ''); // Eliminar - al final
                    }
                </script>

        <?php
            } else {
                // Si no se encuentra la canción, mostrar un mensaje de error
                message("No se encontró ninguna canción con el ID proporcionado.", true, "error");
            }
        } catch (Exception $e) {
            // Manejar cualquier excepción que pueda ocurrir durante la obtención de datos de la canción
            error_handler($e);
            message("Error: " . $e->getMessage(), true, "error");
        }
        ?>


    <?php elseif ($action == 'borrar') :
        // Limpiar mensajes al cargar la página
        message('', true);

        try {
            // Obtener el ID de la canción desde la URL
            $cancion_id = $URL[3];

            // Obtener los datos de la canción que se va a borrar
            $query_cancion = "SELECT * FROM canciones WHERE id = :id";
            $data_cancion = [':id' => $cancion_id];
            $cancion = db_query_one($query_cancion, $data_cancion);

            // Verificar si se ha enviado una solicitud POST para confirmar el borrado
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validar token CSRF
                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                    csrf_error_handler();
                }

                // Realizar la lógica para eliminar la canción con el ID proporcionado
                $query = "DELETE FROM canciones WHERE id = :id";
                $data = [':id' => $cancion_id];
                db_query($query, $data);

                // Redireccionar a la página de administración de canciones después de borrar
                header("Location: " . ROOT . "admin/canciones", true, 0);
                exit();
            } else {
                require page('includes/cabecera-admin');
            }
        } catch (Exception $e) {
            // Manejar cualquier excepción que pueda ocurrir durante el proceso de borrado
            error_handler($e);
            message("Error: " . $e->getMessage(), true, "error");
        }
    ?>
        <!-- Mostrar formulario de confirmación de borrado con los datos de la canción -->
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card">
                        <div class="card-header">
                            Confirmar Borrado
                        </div>
                        <div class="card-body">
                            <p>¿Está seguro de que desea eliminar la siguiente canción?</p>
                            <ul>
                                <li><strong>Título:</strong> <?= $cancion['title'] ?></li>
                                <!-- Agrega más detalles de la canción aquí según sea necesario -->
                            </ul>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <button type="submit" class="btn btn-danger">Sí, Borrar</button>
                                <a href="<?= ROOT . 'admin/canciones' ?>" class="btn btn-secondary">Cancelar</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>



    <?php else :
        require page('includes/cabecera-admin');
        try {
            $query = "SELECT * FROM canciones ORDER BY id DESC";
            $rows = db_query($query);
        } catch (Exception $e) {
            error_handler($e);
        }
    ?>
        <div class="contenedorTituloListaUsuarios">
            <div class="user-list-header">
                <h2>Lista de Canciones</h2>
                <button class="add-button"><a href="<?= ROOT ?>admin/canciones/añadir"><i class="fas fa-plus"></i> Añadir</a></button>
            </div>
            <div class="contenedorListaUsuarios">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titulo</th>
                            <th>Imagen</th>
                            <th>Genero</th>
                            <th>Artista</th>
                            <th>Audio</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rows)) : ?>
                            <?php foreach ($rows as $row) : ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= $row['title'] ?></td>
                                    <td><img src="<?= ROOT ?>/<?= $row['image'] ?>" style="width:100px;height: 100px;object-fit: cover;"></td>
                                    <td><?= get_category($row['category_id']) ?></td>
                                    <td><?= get_artist($row['artist_id']) ?></td>
                                    <td>
                                        <audio controls>
                                            <source src="<?= ROOT ?>/<?= $row['file'] ?>" type="audio/mpeg">
                                        </audio>
                                    </td>
                                    <td class="listaUsuariosAcciones">
                                        <a href="<?= ROOT ?>admin/canciones/editar/<?= $row['id'] ?>">
                                            <img class="bi" src="<?= ROOT ?>/assets/icons/pencil-square.svg" style="margin-right: 10px; margin-left: auto;">
                                        </a>
                                        <a href="<?= ROOT ?>admin/canciones/borrar/<?= $row['id'] ?>">
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

    <?php require page('includes/pie-admin'); ?>