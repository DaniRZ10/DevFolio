<?php
// archivo: includes/conexion.php
// Conexión a la base de datos con PDO. Se incluye con require_once en cada página que la necesite.
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones ante errores
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Filas como arrays asociativos
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Sentencias preparadas nativas
    ]);
} catch (PDOException $e) {
    // El error real se registra en el log del servidor; al usuario solo se le muestra un mensaje genérico
    error_log('Error de conexión: ' . $e->getMessage());
    die('No se ha podido establecer la conexión. Inténtalo más tarde.');
}
