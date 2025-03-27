<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require 'conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener estadísticas de pedidos
$total_pedidos = $conn->query("SELECT COUNT(*) as total FROM pedidos")->fetch_assoc()['total'];
$pendientes = $conn->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'")->fetch_assoc()['total'];
$en_proceso = $conn->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'en_proceso'")->fetch_assoc()['total'];

// Obtener últimos pedidos
$pedidos = $conn->query("SELECT * FROM pedidos ORDER BY fecha_entrega ASC LIMIT 10");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card { margin-bottom: 20px; }
        .stats-card { text-align: center; padding: 20px; border-radius: 5px; color: white; }
        .stats-card.pendientes { background: #ffc107; }
        .stats-card.en-proceso { background: #17a2b8; }
        .stats-card.completados { background: #28a745; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
        <img class="img-fluid" src="img/gb-logo02.svg" alt="Goldenblaze" class="logo" style="height: 70px; max-width:100%">
            <a class="navbar-brand" href="#">Bordados Personalizados</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Hola, <?php echo $_SESSION['username']; ?></span>
                <a class="nav-link" href="consulta.php">Consultar Cliente</a>
                <a class="nav-link" href="logout.php">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="stats-card pendientes">
                    <h3><?php echo $pendientes; ?></h3>
                    <p>Pedidos Pendientes</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card en-proceso">
                    <h3><?php echo $en_proceso; ?></h3>
                    <p>En Proceso</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card completados">
                    <h3><?php echo $total_pedidos - $pendientes - $en_proceso; ?></h3>
                    <p>Completados</p>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h4>Próximos Pedidos a Entregar</h4>
            </div>
            <div class="card-body">
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
            </div>
        </div>

        <div class="mt-3">
            <a href="formulario.php" class="btn btn-success">Nuevo Pedido</a>
            <a href="ver_pedido.php?todos=1" class="btn btn-info">Ver Todos los Pedidos</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>