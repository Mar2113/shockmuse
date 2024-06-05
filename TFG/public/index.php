<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../app/core/init.php";
require "../app/core/config.php";
require "../app/core/functions.php";

$URL = $_GET['url'] ?? "index";

$URL = explode("/", $URL);

$file = page($URL[0]); // Construye la ruta del archivo

try {
    if (file_exists($file)) { // Verifica si el archivo existe
        require $file; // Incluye el archivo si existe
    } else {
        // Redirige a error.php con el código de error 404 y la excepción
        header("Location: ../app/pages/error.php?code=404&exception=" . urlencode("El archivo $file no existe."));
        exit();
    }
} catch (Exception $e) {
    error_handler($e);
}
?>
