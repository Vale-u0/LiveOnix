<?php
// db_config.php - CONEXIÓN A LA BASE DE DATOS

// Configuración de la base de datos (ajústala si usas una contraseña diferente a la predeterminada de WAMP)
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');     // Contraseña vacía por defecto en WAMP
define('DB_NAME', 'liveonix_db'); // Asegúrate de haber creado esta base de datos en phpMyAdmin

// Intentar la conexión
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar la conexión
if($conn->connect_error){
    // Este mensaje de error solo debe mostrarse durante el desarrollo
    die("ERROR: No se pudo conectar a MySQL. " . $conn->connect_error);
}

// Opcional: Establecer el juego de caracteres
$conn->set_charset("utf8mb4");

// $conn es el objeto de conexión que se usa en otros scripts
?>