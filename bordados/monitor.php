<?php
$conn = new mysqli("localhost", "usuario", "contraseña", "bordados_personalizados");
$result = $conn->query("SHOW PROCESSLIST");
echo "<h3>Conexiones activas: " . $result->num_rows . "</h3>";
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['Id']} - Usuario: {$row['User']} - DB: {$row['db']} - Comando: {$row['Command']}<br>";
}
?>