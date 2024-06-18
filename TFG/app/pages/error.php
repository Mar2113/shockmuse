<?php
// error.php

// Incluir configuración y funciones necesarias
require_once __DIR__ . "/../core/config.php";
require_once __DIR__ . "/../core/functions.php";

// Obtener el código de error, el mensaje de error y la excepción opcional
$error_code = isset($_GET['code']) ? $_GET['code'] : 'default';
$error_message = isset($_GET['message']) ? $_GET['message'] : '';
$exception_message = isset($_GET['exception']) ? $_GET['exception'] : '';

$error_title = '';
$error_description = '';

// Usar un switch para determinar el mensaje basado en el código de error
switch ($error_code) {
    case '404':
        $error_title = '404';
        $error_description = '¡Vaya, no fue posible la conexión!';
        $error_message = 'Lo sentimos, pero la página que estás buscando no existe.';
        break;
    case '500':
        $error_title = '500';
        $error_description = 'Error del Servidor';
        $error_message = 'Lo sentimos, pero ha ocurrido un error en el servidor.';
        break;
    case 'db':
        $error_title = 'Error de Base de Datos';
        $error_description = 'Error de Conexión a la Base de Datos';
        $error_message = $error_message ?: 'No se pudo conectar a la base de datos. Por favor, intenta de nuevo más tarde.';
        break;
    default:
        $error_title = 'Error';
        $error_description = 'Ocurrió un Error';
        $error_message = $error_message ?: 'Algo salió mal. Por favor, intenta de nuevo más tarde.';
        break;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title><?= htmlspecialchars($error_title) ?></title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
            color: #333;
            font-family: Arial, sans-serif;
        }
        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .error-container h1 {
            font-size: 6rem;
            margin: 0;
            color: #dc3545;
        }
        .error-container h2 {
            font-size: 2rem;
            margin: 20px 0;
        }
        .error-container p {
            margin: 20px 0;
        }
        .error-container a {
            color: #007bff;
            text-decoration: none;
        }
        .error-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1><?= htmlspecialchars($error_title) ?></h1>
        <h2><?= htmlspecialchars($error_description) ?></h2>
        <p><?= htmlspecialchars($error_message) ?></p>
        <p>Excepción: <span id="exception"><?= htmlspecialchars($exception_message) ?></span></p>
        <p><a href="javascript:history.back()">volver a la página anterior</a>.</p>
    </div>
    <script>
    </script>
</body>
</html>
