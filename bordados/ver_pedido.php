<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require 'conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: lista_pedidos.php");
    exit();
}

$pedido_id = intval($_GET['id']);
$pedido = $conn->query("SELECT * FROM pedidos WHERE id = $pedido_id")->fetch_assoc();

if (!$pedido) {
    header("Location: lista_pedidos.php");
    exit();
}

// Procesar cambio de estado si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado']) && $_SESSION['rol'] == 'admin') {
    $nuevo_estado = $conn->real_escape_string($_POST['estado']);
    $conn->query("UPDATE pedidos SET estado = '$nuevo_estado' WHERE id = $pedido_id");
    // Actualizar los datos del pedido
    $pedido = $conn->query("SELECT * FROM pedidos WHERE id = $pedido_id")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pedido #<?php echo $pedido_id; ?></title>
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
        <h2>Detalles del Pedido #<?php echo $pedido_id; ?></h2>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Cliente: <?php echo htmlspecialchars($pedido['nombre']); ?></h5>
                <p class="card-text">
                    <strong>RUT:</strong> <?php echo htmlspecialchars($pedido['rut']); ?><br>
                    <strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['telefono']); ?><br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($pedido['email']); ?>
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Detalles del Bordado</h5>
                <p class="card-text">
                    <strong>Línea 1:</strong> <?php echo htmlspecialchars($pedido['linea1']); ?><br>
                    <strong>Línea 2:</strong> <?php echo htmlspecialchars($pedido['linea2']); ?><br>
                    <strong>Línea 3:</strong> <?php echo htmlspecialchars($pedido['linea3']); ?><br>
                    <strong>Color:</strong> <?php echo htmlspecialchars($pedido['color_letra']); ?><br>
                    <strong>Tamaño:</strong> <?php echo htmlspecialchars($pedido['numero_letra']); ?>
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Entrega</h5>
                <p class="card-text">
                    <strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($pedido['fecha_entrega'])); ?><br>
                    <strong>Hora:</strong> <?php echo $pedido['hora_entrega']; ?><br>
                    <strong>Estado actual:</strong> 
                    <span class="badge bg-<?php 
                        echo $pedido['estado'] == 'pendiente' ? 'warning' : 
                             ($pedido['estado'] == 'en_proceso' ? 'info' : 'success'); 
                    ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $pedido['estado'])); ?>
                    </span>
                </p>

                <?php if ($_SESSION['rol'] == 'admin'): ?>
                <form method="post" class="mt-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label for="estado" class="col-form-label">Cambiar estado:</label>
                        </div>
                        <div class="col-auto">
                            <select name="estado" id="estado" class="form-select">
                                <option value="pendiente" <?php echo $pedido['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="en_proceso" <?php echo $pedido['estado'] == 'en_proceso' ? 'selected' : ''; ?>>En proceso</option>
                                <option value="completado" <?php echo $pedido['estado'] == 'completado' ? 'selected' : ''; ?>>Completado</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="cambiar_estado" class="btn btn-primary">Actualizar</button>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-3">
            <a href="lista_pedidos.php" class="btn btn-secondary">Volver a la lista</a>
            <?php if ($_SESSION['rol'] == 'admin'): ?>
                <a href="editar_pedido.php?id=<?php echo $pedido_id; ?>" class="btn btn-primary">Editar</a>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>