<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require 'conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pedidos = $conn->query("SELECT * FROM pedidos ORDER BY fecha_entrega DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Todos los Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <img class="img-fluid" src="img/gb-logo02.svg" alt="Goldenblaze" class="logo" style="height: 70px; max-width:100%">
            <a class="navbar-brand" href="#">Bordados Personalizados</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Hola, <?php echo $_SESSION['username']; ?></span>
                <a class="nav-link" href="consulta.php">Consultar Cliente</a>
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="logout.php">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Todos los Pedidos</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Bordado</th>
                    <th>Entrega</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($pedido = $pedidos->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $pedido['id']; ?></td>
                    <td><?php echo $pedido['nombre']; ?></td>
                    <td><?php echo $pedido['linea1']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($pedido['fecha_entrega'])); ?> a las <?php echo $pedido['hora_entrega']; ?></td>
                    <td>
                        <span class="badge bg-<?php 
                            echo $pedido['estado'] == 'pendiente' ? 'warning' : 
                                 ($pedido['estado'] == 'en_proceso' ? 'info' : 'success'); 
                        ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $pedido['estado'])); ?>
                        </span>
                    </td>
                    <td>
                    <a href="ver_pedido.php?id=<?php echo $pedido['id']; ?>" class="btn btn-sm btn-primary">Ver</a>
                        <?php if ($_SESSION['rol'] == 'admin'): ?>
                            <a href="editar_pedido.php?id=<?php echo $pedido['id']; ?>" class="btn btn-sm btn-secondary">Editar</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn btn-secondary">Volver al Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>