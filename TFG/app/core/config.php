<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!defined('ROOT')) {
    if ($_SERVER['SERVER_NAME'] == 'localhost') {
        define("ROOT", "http://localhost/TFG/public/");
    } else {
        $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === false ? 'http' : 'https';
        define("ROOT", $protocol . "://" . $_SERVER['HTTP_HOST']);
    }
}

if (!defined('DBDRIVER')) {
    define("DBDRIVER", "mysql");
}

if (!defined('DBHOST')) {
    define("DBHOST", "localhost:3307");
}

if (!defined('DBUSER')) {
    define("DBUSER", "root");
}

if (!defined('DBPASS')) {
    define("DBPASS", "");
}

if (!defined('DBNAME')) {
    define("DBNAME", "music_website_db");
}

// Intenta conectar a la base de datos
// try {
//     $pdo = new PDO(DBDRIVER . ":host=" . DBHOST . ";dbname=" . DBNAME, DBUSER, DBPASS);
//     echo "¡Conexión exitosa a la base de datos!";
// } catch (PDOException $e) {
//     die("Error de conexión a la base de datos: " . $e->getMessage());
// }
// 
