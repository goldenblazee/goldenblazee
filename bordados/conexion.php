<?php
header('Content-Type: text/html; charset=utf-8');
$servername = "192.168.1.146"; // Cambia si tu DB está en otro servidor
$username = "admin"; // Usuario con permisos para la DB
$password = "Golden2025"; // Contraseña del usuario
$dbname = "bordados_personalizados"; // Nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar charset
$conn->set_charset("utf8mb4");
?>