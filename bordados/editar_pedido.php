<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require 'conexion.php';

// Verificar autenticación y permisos
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Verificar ID de pedido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: lista_pedidos.php");
    exit();
}

$pedido_id = intval($_GET['id']);

// Obtener datos actuales del pedido
$pedido = $conn->query("SELECT * FROM pedidos WHERE id = $pedido_id")->fetch_assoc();

if (!$pedido) {
    header("Location: lista_pedidos.php");
    exit();
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    // Recoger y sanitizar datos
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $rut = $conn->real_escape_string($_POST['rut']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $email = $conn->real_escape_string($_POST['email']);
    $linea1 = $conn->real_escape_string($_POST['linea1']);
    $linea2 = $conn->real_escape_string($_POST['linea2'] ?? '');
    $linea3 = $conn->real_escape_string($_POST['linea3'] ?? '');
    $observacion = $conn->real_escape_string($_POST['observacion'] ?? '');
    $color_letra = $conn->real_escape_string($_POST['color_letra']);
    $numero_letra = intval($_POST['numero_letra']);
    $fecha_entrega = $conn->real_escape_string($_POST['fecha_entrega']);
    $hora_entrega = $conn->real_escape_string($_POST['hora_entrega']);
    $estado = $conn->real_escape_string($_POST['estado']);

    // Actualizar en base de datos
    $sql = "UPDATE pedidos SET 
            nombre = '$nombre',
            rut = '$rut',
            telefono = '$telefono',
            email = '$email',
            linea1 = '$linea1',
            linea2 = '$linea2',
            linea3 = '$linea3',
            observacion = '$observacion',
            color_letra = '$color_letra',
            numero_letra = $numero_letra,
            fecha_entrega = '$fecha_entrega',
            hora_entrega = '$hora_entrega',
            estado = '$estado'
            WHERE id = $pedido_id";

    if ($conn->query($sql)) {
        $mensaje_exito = "Pedido actualizado correctamente";
        // Actualizar datos locales
        $pedido = $conn->query("SELECT * FROM pedidos WHERE id = $pedido_id")->fetch_assoc();
    } else {
        $mensaje_error = "Error al actualizar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pedido #<?php echo $pedido_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .badge-pendiente { background-color: #ffc107; }
        .badge-en-proceso { background-color: #17a2b8; }
        .badge-completado { background-color: #28a745; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <img class="img-fluid" src="img/gb-logo02.svg" alt="Goldenblaze" class="logo" style="height: 70px; max-width:100%">
            <a class="navbar-brand" href="#">Bordados Personalizados</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Hola, <?php echo $_SESSION['username']; ?></span>
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="logout.php">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="form-container">
            <h2 class="mb-4">Editar Pedido #<?php echo $pedido_id; ?></h2>
            
            <?php if (isset($mensaje_exito)): ?>
                <div class="alert alert-success"><?php echo $mensaje_exito; ?></div>
            <?php endif; ?>
            
            <?php if (isset($mensaje_error)): ?>
                <div class="alert alert-danger"><?php echo $mensaje_error; ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="row g-3">
                    <!-- Sección Datos del Cliente -->
                    <div class="col-md-12">
                        <h4 class="mb-3">Datos del Cliente</h4>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" 
                               value="<?php echo htmlspecialchars($pedido['nombre']); ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="rut" class="form-label">RUT</label>
                        <input type="text" class="form-control" id="rut" name="rut" 
                               value="<?php echo htmlspecialchars($pedido['rut']); ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" 
                               value="<?php echo htmlspecialchars($pedido['telefono']); ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($pedido['email']); ?>" required>
                    </div>
                    
                    <!-- Sección Detalles del Bordado -->
                    <div class="col-md-12 mt-4">
                        <h4 class="mb-3">Detalles del Bordado</h4>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="linea1" class="form-label">Línea 1</label>
                        <input type="text" class="form-control" id="linea1" name="linea1" 
                               value="<?php echo htmlspecialchars($pedido['linea1']); ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="linea2" class="form-label">Línea 2</label>
                        <input type="text" class="form-control" id="linea2" name="linea2" 
                               value="<?php echo htmlspecialchars($pedido['linea2']); ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="linea3" class="form-label">Línea 3</label>
                        <input type="text" class="form-control" id="linea3" name="linea3" 
                               value="<?php echo htmlspecialchars($pedido['linea3']); ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="color_letra" class="form-label">Color de Letra</label>
                        <input type="text" class="form-control" id="color_letra" name="color_letra" 
                               value="<?php echo htmlspecialchars($pedido['color_letra']); ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="numero_letra" class="form-label">Número de Letra</label>
                        <input type="number" class="form-control" id="numero_letra" name="numero_letra" 
                               value="<?php echo htmlspecialchars($pedido['numero_letra']); ?>" required>
                    </div>
                    
                    <div class="col-md-12">
                        <label for="observacion" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observacion" name="observacion" rows="2"><?php echo htmlspecialchars($pedido['observacion']); ?></textarea>
                    </div>
                    
                    <!-- Sección Entrega -->
                    <div class="col-md-12 mt-4">
                        <h4 class="mb-3">Datos de Entrega</h4>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="fecha_entrega" class="form-label">Fecha de Entrega</label>
                        <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega" 
                               value="<?php echo $pedido['fecha_entrega']; ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="hora_entrega" class="form-label">Hora de Entrega</label>
                        <input type="time" class="form-control" id="hora_entrega" name="hora_entrega" 
                               value="<?php echo $pedido['hora_entrega']; ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="pendiente" <?php echo $pedido['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="en_proceso" <?php echo $pedido['estado'] == 'en_proceso' ? 'selected' : ''; ?>>En Proceso</option>
                            <option value="completado" <?php echo $pedido['estado'] == 'completado' ? 'selected' : ''; ?>>Completado</option>
                        </select>
                    </div>
                    
                    <div class="col-md-12 mt-4">
                        <button type="submit" name="actualizar" class="btn btn-primary">Guardar Cambios</button>
                        <a href="ver_pedido.php?id=<?php echo $pedido_id; ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Formatear fecha para el input date (YYYY-MM-DD)
        document.addEventListener('DOMContentLoaded', function() {
            const fechaEntrega = "<?php echo $pedido['fecha_entrega']; ?>";
            if (fechaEntrega) {
                document.getElementById('fecha_entrega').value = fechaEntrega;
            }
        });
    </script>
</body>
</html>