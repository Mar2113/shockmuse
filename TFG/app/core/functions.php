<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!function_exists('error_handler')) {
    function error_handler($e)
    {
        // Log de la excepción en el archivo de registro de errores

        // Determinar el código de error según el tipo de excepción
        $error_code = 'default'; // Por defecto
        if ($e->getCode() == 404) {
            $error_code = '404';
        } elseif ($e->getCode() == 500) {
            $error_code = '500';
        } elseif ($e->getCode() == 'db') {
            $error_code = 'db';
        }

        // Redirige a error.php con el código de error correspondiente, el mensaje de error y la excepción
        $error_message = urlencode($e->getMessage());
        $exception_message = urlencode($e->getMessage());
        header("Location: ../app/pages/error.php?code=$error_code&message=$error_message&exception=$exception_message");
        exit();
    }
}


if (!function_exists('show')) {
    function show($stuff)
    {
        try {
            if (!isset($stuff)) {
                throw new Exception('Variable $stuff is not set.', 500); // Código de error 500
            }

            echo "<pre>";
            print_r($stuff);
            echo "</pre>";
        } catch (Exception $e) {
            error_handler($e);
        }
    }
}

if (!function_exists('page')) {
    function page($file)
    {
        try {
            if ($file) {
                return "../app/pages/" . $file . ".php";
            } else {
                throw new Exception("Archivo no encontrado.", 404); // Código de error 404
            }
        } catch (Exception $e) {
            error_handler($e);
        }
    }
}

// ###############################################################
//  [ 1  ]                    DDDBB FUNCTIONS
// ###############################################################

if (!function_exists('db_connect')) {
    function db_connect()
    {
        try {
            $string = DBDRIVER . ":host=" . DBHOST . ";dbname=" . DBNAME;
            $con = new PDO($string, DBUSER, DBPASS);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $con;
        } catch (Exception $e) {
            error_handler($e);
        }
    }
}

if (!function_exists('db_query')) {
    function db_query($query, $data = array())
    {
        try {
            $con = db_connect();
            $stm = $con->prepare($query);
            if ($stm) {
                $check = $stm->execute($data);
                if ($check) {
                    $result = $stm->fetchAll(PDO::FETCH_ASSOC);
                    if ($result !== false) {
                        return $result;
                    }
                }
            }
            if (!is_string($query)) {
                throw new Exception("Error executing database query: Query must be a string");
            }
            throw new Exception("Error executing database query: " . print_r($query, true));
        } catch (Exception $e) {
            error_handler($e);
            return false; // Devuelve false en caso de error
        }
    }
}




if (!function_exists('db_query_one')) {
    function db_query_one($query, $data = array())
    {
        try {
            $con = db_connect();

            $stm = $con->prepare($query);
            if ($stm) {
                $check = $stm->execute($data);
                if ($check) {
                    $result = $stm->fetchAll(PDO::FETCH_ASSOC);

                    if (is_array($result) && count($result) > 0) {
                        return $result[0];
                    }
                }
            }
            return false;
        } catch (Exception $e) {
            // Manejo de errores
            error_handler($e);
            return false;
        }
    }
}


if (!function_exists('message')) {
    function message($message = '', $clear = false, $estado = '')
    {
        try {
            if (!empty($message)) {
                $_SESSION['message'] = ['text' => $message, 'estado' => $estado];
            } else {
                if (!empty($_SESSION['message'])) {
                    $msg = $_SESSION['message'];
                    if ($clear) {
                        unset($_SESSION['message']);
                    }
                    return $msg;
                }
            }
            return false;
        } catch (Exception $e) {
            error_handler($e);
        }
    }
}

if (!function_exists('show_confirmation_dialog')) {
    function show_confirmation_dialog($message, $yes_action, $no_action)
    {
        return '
        <div class="confirmation-dialog" id="confirmationDialog">
            <div class="confirmation-dialog-content">
                <p id="confirmationMessage">' . $message . '</p>
                <div class="confirmation-dialog-buttons">
                    <button id="confirmYes">Sí</button>
                    <button id="confirmNo">No</button>
                </div>
            </div>
        </div>';
    }
}


// Función para obtener el nombre de la categoría
if (!function_exists('get_category')) {
    function get_category($id)
    {
        try {
            $query = "SELECT category FROM categorias WHERE id = :id LIMIT 1";
            $row = db_query_one($query, ['id' => $id]);

            if (!empty($row['category'])) {
                return $row['category'];
            } else {
                return "Unknown";
            }
        } catch (Exception $e) {
            error_handler($e);
            return "Unknown";
        }
    }
}

// Función para obtener el nombre del artista
if (!function_exists('get_artist')) {
    function get_artist($id)
    {
        try {
            $query = "SELECT name FROM artistas WHERE id = :id LIMIT 1";
            $row = db_query_one($query, ['id' => $id]);

            if (!empty($row['name'])) {
                return $row['name'];
            } else {
                return "Unknown";
            }
        } catch (Exception $e) {
            error_handler($e);
            return "Unknown";
        }
    }
}

if (!function_exists('get_categories_artist')) {
    function get_categories_artist($artist_id)
    {
        try {
            // Obtener todas las categorías asociadas a las canciones del artista
            $query = "
                SELECT DISTINCT c.category 
                FROM canciones AS s 
                JOIN categorias AS c ON s.category_id = c.id 
                WHERE s.artist_id = :artist_id
            ";
            $categories = db_query($query, ['artist_id' => $artist_id]);

            if ($categories && count($categories) > 0) {
                $category_names = [];
                foreach ($categories as $category) {
                    $category_names[] = $category['category']; // Solo agregar el nombre de la categoría
                }
                return implode(', ', $category_names); // Convertir el array en una cadena de texto
            } else {
                return "#";
            }
        } catch (Exception $e) {
            error_handler($e);
            return "Error al cargar los géneros";
        }
    }
}





// ####################################################################
//                          FILTROS FORMULARIO
// ####################################################################


if (!function_exists('sanitize_input')) {
    function sanitize_input($data)
    {
        try {
            $data = trim($data);  // Elimina espacios en blanco al principio y al final
            $data = stripslashes($data);  // Elimina barras invertidas
            $data = htmlspecialchars($data);  // Convierte caracteres especiales en entidades HTML
            return $data;
        } catch (Exception $e) {
            error_handler($e);
        }
    }
}

if (!function_exists('check_duplicate_name')) {
    function check_duplicate_name($name)
    {
        // Sanitiza el nombre para evitar inyecciones SQL
        $sanitized_name = sanitize_input($name);

        // Realiza la consulta para verificar si el nombre ya existe
        $query = "SELECT COUNT(*) FROM usuarios WHERE username = :name";
        $data = [':name' => $sanitized_name];
        $result = db_query($query, $data);
        $name_exists = ($result[0]['COUNT(*)'] > 0);

        // Si el nombre ya existe, muestra un mensaje de advertencia
        if ($name_exists) {
            message("El nombre ya está en uso. Por favor, elija otro nombre.", true, "warning");
            return true; // Retorna true si el nombre ya existe
        }

        return false; // Retorna false si el nombre no existe
    }
}


if (!function_exists('validate_email')) {
    function validate_email($email)
    {
        try {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        } catch (Exception $e) {
            error_handler($e);
        }
    }
}

if (!function_exists('validate_password')) {
    function validate_password($password)
    {
        try {
            // Implementar validación de contraseña fuerte
            return strlen($password) >= 8;
        } catch (Exception $e) {
            error_handler($e);
        }
    }
}


if (!function_exists('get_date')) {
    function get_date($date)
    {
        try {
            // Obtener la fecha en formato Unix
            $timestamp = strtotime($date);

            // Verificar si se pudo convertir la fecha correctamente
            if ($timestamp === false) {
                throw new Exception("Fecha inválida");
            }

            // Formatear la fecha y hora
            return date("jS M, Y H:i:s", $timestamp);
        } catch (Exception $e) {
            // Manejar el error y mostrar un mensaje amigable
            return "Error: " . $e->getMessage();
        }
    }
}


if (!function_exists('redirect')) {
    function redirect($page)
    {
        try {
            header("Location: " . ROOT . "" . $page);
            die;
        } catch (Exception $e) {
            error_handler($e);
        }
    }
}

// if (!function_exists('set_value')) {
//     function set_value($key, $default = '')
//     {
//         try {
//             if (!empty($_POST[$key])) {
//                 return $_POST[$key];
//             } else {
//                 return $default;
//             }
//             return '';
//         } catch (Exception $e) {
//             error_handler($e);
//         }
//     }
// }

// if (!function_exists('set_select')) {
//     function set_select($key, $value, $default = '')
//     {
//         try {
//             if (!empty($_POST[$key])) {
//                 if ($_POST[$key] == $value) {
//                     return " selected ";
//                 }
//             } else {
//                 if ($default == $value) {
//                     return " selected ";
//                 }
//             }
//             return '';
//         } catch (Exception $e) {
//             error_handler($e);
//         }
//     }
// }


// #############################################################################
//                          LOGIN / LOGOUT
// #############################################################################


if (!function_exists('logged_in')) {
    function logged_in()
    {
        try {
            if (!empty($_SESSION['USER']) && is_array($_SESSION['USER'])) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            // Manejo de errores
            error_handler($e);
            return false;
        }
    }
}

if (!function_exists('is_admin')) {
    function is_admin()
    {
        try {
            if (!empty($_SESSION['USER']['role']) && $_SESSION['USER']['role'] == 'admin') {
                return true;
            }
            return false;
        } catch (Exception $e) {
            // Manejo de errores
            error_handler($e);
            message("Acceso no garantizado", true, "warning");
            return false;
        }
    }
}

if (!function_exists('is_user')) {
    function is_user()
    {
        try {
            if (!empty($_SESSION['USER']['role']) && $_SESSION['USER']['role'] == 'user') {
                return true;
            }
            return false;
        } catch (Exception $e) {
            // Manejo de errores
            error_handler($e);
            message("Acceso no garantizado", true, "warning");
            return false;
        }
    }
}



if (!function_exists('authenticate')) {
    function authenticate($row)
    {
        try {
            $_SESSION['USER'] = $row;
        } catch (Exception $e) {
            // Manejo de errores
            error_handler($e);
        }
    }
}

if (!function_exists('esc')) {
    function esc($string)
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

