<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bordados_personalizados";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer charset utf8
$conn->set_charset("utf8");

// Función para validar RUT chileno
function validarRUT($rut) {
    $rut = preg_replace('/[^0-9kK]/i', '', $rut);
    $dv = substr($rut, -1);
    $numero = substr($rut, 0, strlen($rut)-1);
    $i = 2;
    $suma = 0;
    
    foreach(array_reverse(str_split($numero)) as $v) {
        if($i == 8) $i = 2;
        $suma += $v * $i;
        $i++;
    }
    
    $dvr = 11 - ($suma % 11);
    if($dvr == 11) $dvr = 0;
    if($dvr == 10) $dvr = 'K';
    
    return strtoupper($dv) == strtoupper($dvr);
}

// Variable para almacenar resultados
$resultados = [];
$rut_consultado = '';

// Procesar consulta por RUT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut = preg_replace('/[^0-9kK]/', '', strtoupper($_POST['rutConsulta']));
    $rut_consultado = substr($rut, 0, -1) . '-' . substr($rut, -1);
    
    if (!validarRUT($rut_consultado)) {
        $_SESSION['consulta_error'] = 'RUT inválido';
        header("Location: consulta.php");
        exit();
    }
    
    $stmt = $conn->prepare("SELECT * FROM pedidos WHERE rut = ? ORDER BY fecha DESC");
    $stmt->bind_param("s", $rut_consultado);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $resultados = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $_SESSION['consulta_error'] = 'No se encontraron registros para este RUT';
        header("Location: consulta.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Consulta de Clientes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .container {
      max-width: 1000px;
      margin-top: 20px;
    }
    .alert {
      margin-top: 20px;
    }
    .table-responsive {
      margin-top: 20px;
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
        <a class="nav-link" href="dashboard.php">Volver al Dashboard</a>
        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
      </div>
    </div>
  </nav>

  <div class="container">
    <h2 class="mb-4">Consulta de Clientes por RUT</h2>
    
    <?php if (isset($_SESSION['consulta_error'])): ?>
      <div class="alert alert-danger"><?php echo $_SESSION['consulta_error']; unset($_SESSION['consulta_error']); ?></div>
    <?php endif; ?>
    
    <form method="post" action="consulta.php" class="mb-4">
      <div class="row g-3 align-items-center">
        <div class="col-md-8">
          <label for="rutConsulta" class="form-label">Ingrese RUT del cliente</label>
          <input type="text" class="form-control" id="rutConsulta" name="rutConsulta" 
                 placeholder="Ej: 12345678-9 o 12.345.678-9" required
                 value="<?php echo htmlspecialchars($rut_consultado); ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary">Consultar</button>
        </div>
      </div>
    </form>

    <?php if (!empty($resultados)): ?>
    <div class="table-responsive">
      <h4>Pedidos encontrados para RUT: <?php echo htmlspecialchars($rut_consultado); ?></h4>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>ID Pedido</th>
            <th>Fecha</th>
            <th>Nombre</th>
            <th>Bordado</th>
            <th>Entrega</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resultados as $pedido): ?>
          <tr>
            <td><?php echo $pedido['id']; ?></td>
            <td><?php echo date('d/m/Y', strtotime($pedido['fecha'])); ?></td>
            <td><?php echo htmlspecialchars($pedido['nombre']); ?></td>
            <td><?php echo htmlspecialchars($pedido['linea1']); ?></td>
            <td>
              <?php echo date('d/m/Y', strtotime($pedido['fecha_entrega'])); ?>
              <br>
              <?php echo $pedido['hora_entrega']; ?>
            </td>
            <td>
              <span class="badge <?php 
                echo $pedido['estado'] == 'pendiente' ? 'badge-pendiente' : 
                     ($pedido['estado'] == 'en_proceso' ? 'badge-en-proceso' : 'badge-completado'); 
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
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <div class="mt-3">
      <a href="formulario.php" class="btn btn-success">Nuevo Pedido</a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>