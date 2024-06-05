<?php
// Ajusta esta ruta según la ubicación real de functions.php
$functions_path = __DIR__ . "/../core/functions.php";

// Verifica si el archivo existe antes de requerirlo
if (file_exists($functions_path)) {
    require_once $functions_path; // Utiliza require_once para evitar múltiples inclusiones
} else {
    die("El archivo functions.php no se encuentra en la ruta especificada.");
}

session_start();

try {

    if (!logged_in()) {
        message("Por favor, inicie sesión", true, "error");
        redirect('login');
    }

    $role = $_SESSION['USER']['role'] ?? null;

    // Debugging purpose
    // var_dump($_SESSION['USER']);

    // $role = 'admin';

    if ($role === 'admin') {
        try {
            $inicial = $URL[0];
            $section = $URL[1] ?? "principal";
            $action = $URL[2] ?? null;
            $id = $URL[3] ?? null;

            // var_dump($URL[0]);

            // Si la primera parte de la URL es 'index' o la URL está vacía
            if ($inicial === 'index' || empty($inicial)) {
                // Redireccionar a admin/principal
                redirect('admin/principal');
            }
            try {
                switch ($section) {
                    case 'principal':
                        require page('admin/principal');
                        break;
                    case 'canciones':
                        require page('admin/canciones');
                        break;
                    case 'generos':
                        require page('admin/generos');
                        break;
                    case 'usuarios':
                        require page('admin/usuarios');
                        break;
                    case 'listas':
                        require page('admin/listas');
                        break;
                    case 'artistas':
                        require page('admin/artistas');
                        break;
                    case 'configuracion':
                        require page('admin/configuracion');
                        break;
                    default:
                        require page('error');
                        break;
                }
            } catch (Exception $e) {
                error_handler($e);
                message("Ha ocurrido un error al cargar la sección de administración.", true, "error");
                redirect('admin/principal');
            }
        } catch (Exception $e) {
            // Manejo de errores para la lógica de administración
            error_handler($e);
            message("Ha ocurrido un error al cargar la sección de administración.", true, "error");
            redirect('admin/principal');
        }
    } else if ($role === 'user') {
        // Redirigir a la página de usuario o mostrar un mensaje de acceso denegado
        message("Acceso denegado a la sección de administración", true, "error");
        redirect('login');
    } else {
        // Si el rol no es ni admin ni user
        message("Acceso denegado", true, "error");
        redirect('login');
    }
} catch (Exception $e) {
    // Manejar cualquier excepción que pueda ocurrir durante la verificación de autenticación
    error_handler($e);
    message("Error inesperado: " . $e->getMessage(), true, "error");
    redirect('login');
}
