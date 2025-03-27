<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require 'conexion.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$pedido_id = $_POST['pedido_id'];
$estado = $_POST['estado'];

$stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
$stmt->bind_param("si", $estado, $pedido_id);
$stmt->execute();

header("Location: ver_pedido.php?id=$pedido_id");
exit();
?>