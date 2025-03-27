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

// Procesar datos de consulta si vienen de la página de consulta
if (isset($_GET['consultar']) && $_GET['consultar'] == 1) {
    $datosConsulta = [
        'rut' => isset($_GET['rut']) ? $_GET['rut'] : '',
        'nombre' => isset($_GET['nombre']) ? urldecode($_GET['nombre']) : '',
        'telefono' => isset($_GET['telefono']) ? urldecode($_GET['telefono']) : '',
        'email' => isset($_GET['email']) ? urldecode($_GET['email']) : '',
        'linea1' => isset($_GET['linea1']) ? urldecode($_GET['linea1']) : '',
        'linea2' => isset($_GET['linea2']) ? urldecode($_GET['linea2']) : '',
        'linea3' => isset($_GET['linea3']) ? urldecode($_GET['linea3']) : '',
        'observacion' => isset($_GET['observacion']) ? urldecode($_GET['observacion']) : '',
        'colorLetra' => isset($_GET['colorLetra']) ? urldecode($_GET['colorLetra']) : '',
        'numeroLetra' => isset($_GET['numeroLetra']) ? urldecode($_GET['numeroLetra']) : ''
    ];
} 
else {
    $datosConsulta = [];
}

// Procesar guardado de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    // Limpiar y formatear RUT
    $rut = strtoupper(preg_replace('/[^0-9kK]/', '', $_POST['rut']));
    $rut = substr($rut, 0, -1) . '-' . substr($rut, -1);
    
   // if (!validarRUT($rut)) {
    //   die("RUT inválido. Por favor ingrese un RUT válido (Ej: 12345678-9)");
    //}
    
    // Preparar datos para guardar
    $datos = [
        'fecha' => date('Y-m-d H:i:s'), // Usamos fecha actual del servidor
        'rut' => $rut,
        'nombre' => $conn->real_escape_string($_POST['nombre']),
        'telefono' => $conn->real_escape_string($_POST['telefono']),
        'email' => $conn->real_escape_string($_POST['email']),
        'linea1' => $conn->real_escape_string($_POST['linea1']),
        'linea2' => isset($_POST['linea2']) ? $conn->real_escape_string($_POST['linea2']) : '',
        'linea3' => isset($_POST['linea3']) ? $conn->real_escape_string($_POST['linea3']) : '',
        'observacion' => isset($_POST['observacion']) ? $conn->real_escape_string($_POST['observacion']) : '',
        'colorLetra' => $conn->real_escape_string($_POST['colorLetra']),
        'numeroLetra' => intval($_POST['numeroLetra']),
        'fechaEntrega' => $conn->real_escape_string($_POST['fechaEntrega']),
        'horaEntrega' => $conn->real_escape_string($_POST['horaEntrega']),
        'usuario_id' => intval($_SESSION['user_id'])
    ];
    
    // Insertar en base de datos
    $sql = "INSERT INTO pedidos (fecha, rut, nombre, telefono, email, linea1, linea2, linea3, observacion, color_letra, numero_letra, fecha_entrega, hora_entrega, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("sssssssssssssi", 
        $datos['fecha'],
        $datos['rut'],
        $datos['nombre'],
        $datos['telefono'],
        $datos['email'],
        $datos['linea1'],
        $datos['linea2'],
        $datos['linea3'],
        $datos['observacion'],
        $datos['colorLetra'],
        $datos['numeroLetra'],
        $datos['fechaEntrega'],
        $datos['horaEntrega'],
        $datos['usuario_id']
    );
    
    if ($stmt->execute()) {
        $pedido_id = $stmt->insert_id;
        header("Location: formato_imprimible.php?id=$pedido_id");
        exit();
    } else {
        die("Error al guardar: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulario de Bordados</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Saira:wght@500;600;700&display=swap" rel="stylesheet"> 
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <style>
    /* Estilos del formulario */
    #resumen-container {
      display: none;
      margin-top: 20px;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 5px;
      background-color: #f9f9f9;
    }
    #resumen-datos {
      margin-bottom: 20px;
    }
    #resumen-container h3 {
      margin-bottom: 15px;
      color: #333;
    }
    .resumen-item {
      margin-bottom: 8px;
    }
    .resumen-item strong {
      display: inline-block;
      width: 150px;
    }
    .action-buttons {
      display: flex;
      gap: 10px;
    }
    label[for="Rut"] { display: none; }
    label[for="Nombre y Apellido"] { display: none; }
    label[for="fecha"]:not([for="fecha"]:first-of-type) { display: none; }
    label[for="Linea 1"], 
    label[for="linea 2"], 
    label[for="Linea 3"], 
    label[for="Observacion"], 
    label[for="Color Letra"], 
    label[for="Numero de Letra"] { display: none; }
    
    /* Estilo para mensajes de error */
    .error-message {
      color: #e74c3c;
      font-size: 0.8em;
      margin-top: 5px;
      display: none;
    }
    input.error {
      border-color: #e74c3c;

    }
  </style>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
        <img class="img-fluid" src="img/gb-logo02.svg" alt="Goldenblaze" class="logo" style="height: 70px; max-width:100%">
            <a class="navbar-brand" href="#">Bordados Personalizados</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Hola, <?php echo $_SESSION['username']; ?></span>
                <a class="nav-link" href="dashboard.php">Volver al Dashboard</a>
                <a class="nav-link" href="consulta.php">Consultar Cliente</a>
                <a class="nav-link" href="logout.php">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

  <!-- Multistep form -->
  <form id="msform" method="post" action="formulario.php">
    <!-- Progress bar -->
    <ul id="progressbar">
      <li class="active">Cliente</li>
      <li>Bordado</li>
      <li>Entrega</li>
      <li>Resumen</li>
    </ul>
  
    <!-- Paso 1: Datos del Cliente -->
    <fieldset>
      <h2 class="fs-title">Formulario de Bordado</h2>
      <h3 class="fs-subtitle">Datos Cliente</h3>
      
      <label for="fecha">Fecha</label>
      <input type="date" name="fecha" id="fecha" required />
      <label for="rut">Rut</label>
      <input type="text" name="rut" id="rutInput" placeholder="RUT (Ej: 12345678-9 o 12.345.678-9)" 
             pattern="[\d\.]{7,10}-?[\dkK]" title="Ingrese un RUT válido (12345678-9 o 12.345.678-9)" required />
      <div id="rutError" class="error-message">Por favor ingrese un RUT válido</div>
      <label for="nombre">Nombre </label>
      <input type="text" name="nombre" placeholder="Nombre y Apellido" required />
      <label for="telefono">Telefono</label>
      <input type="tel" name="telefono" placeholder="+56" required />
      <label for="email">Correo</label>
      <input type="email" name="email" placeholder="Email" required />
      
      <input type="button" name="next" class="next action-button" value="Siguiente" >
    </fieldset>
  
    <!-- Paso 2: Datos del Bordado -->
    <fieldset>
      <h2 class="fs-title">Formulario de Bordado</h2>
      <h3 class="fs-subtitle">Datos Bordado</h3>
      <label for="linea1">Linea 1</label>
      <input type="text" name="linea1" placeholder="Línea #1" required />
      <label for="linea2">Linea 2</label>
      <input type="text" name="linea2" placeholder="Línea #2" />
      <label for="linea3">Linea 3</label>
      <input type="text" name="linea3" placeholder="Línea #3" />
      <label for="observacion">Observacion</label>
      <textarea name="observacion" placeholder="Observación"></textarea>
      <label for="colorLetra">Color de Letra</label>
      <input type="text" name="colorLetra" placeholder="Color de Letra" required />
      <label for="numeroLetra">Numero de Letra</label>
      <input type="number" name="numeroLetra" placeholder="Número de Letra" required />
      
      <input type="button" name="previous" class="previous action-button" value="Anterior" />
      <input type="button" name="next" class="next action-button" value="Siguiente" />
    </fieldset>
  
    <!-- Paso 3: Datos de Entrega -->
    <fieldset>
      <h2 class="fs-title">Formato de Bordado</h2>
      <h3 class="fs-subtitle">Entrega</h3>
      
      <label for="fechaEntrega">Fecha de entrega</label>
      <input type="date" name="fechaEntrega" id="fechaEntrega" required />
      
      <label for="horaEntrega">Hora de entrega</label>
      <input type="time" name="horaEntrega" id="horaEntrega" required />
      
      <input type="button" name="previous" class="previous action-button" value="Anterior" />
      <input type="button" name="next" class="next action-button" value="Ver Resumen" />
    </fieldset>

    <!-- Paso 4: Resumen -->
    <fieldset>
      <h2 class="fs-title">Resumen del Pedido</h2>
      <h3 class="fs-subtitle">Revise los datos antes de guardar</h3>
      
      <div id="resumen-datos"></div>
      
      <input type="button" name="previous" class="previous action-button" value="Anterior" />
      <input type="hidden" name="guardar" value="1">
      <input type="submit" class="submit action-button" value="Guardar y Ver Etiqueta" />
    </fieldset>
  </form>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function() {
      // Configuración inicial de fechas
      const today = new Date().toISOString().split('T')[0];
      $('#fecha').val(today);
      
      const entrega = new Date();
      entrega.setDate(entrega.getDate() + 3);
      $('#fechaEntrega').val(entrega.toISOString().split('T')[0]);

      // Rellenar datos si vienen de consulta
      <?php if (!empty($datosConsulta)): ?>
        $('input[name="rut"]').val('<?php echo $datosConsulta['rut']; ?>');
        $('input[name="nombre"]').val('<?php echo $datosConsulta['nombre']; ?>');
        $('input[name="telefono"]').val('<?php echo $datosConsulta['telefono']; ?>');
        $('input[name="email"]').val('<?php echo $datosConsulta['email']; ?>');
        $('input[name="linea1"]').val('<?php echo $datosConsulta['linea1']; ?>');
        $('input[name="linea2"]').val('<?php echo $datosConsulta['linea2']; ?>');
        $('input[name="linea3"]').val('<?php echo $datosConsulta['linea3']; ?>');
        $('textarea[name="observacion"]').val('<?php echo $datosConsulta['observacion']; ?>');
        $('input[name="colorLetra"]').val('<?php echo $datosConsulta['colorLetra']; ?>');
        $('input[name="numeroLetra"]').val('<?php echo $datosConsulta['numeroLetra']; ?>');
      <?php endif; ?>

      // Variables de control
      let animating = false;

      // Función para validar RUT
      function validarRutCliente() {
          const rutInput = $('#rutInput').val();
          const rut = rutInput.replace(/[^0-9kK]/gi, '').toUpperCase();
          
          if (rut.length < 8 || !/^[0-9]{7,8}[0-9kK]{1}$/.test(rut)) {
              $('#rutError').show();
              return false;
          }
          
          $('#rutError').hide();
          return true;
      }

      // Navegación entre pasos
      $(".next").click(function() {
          if (animating) return false;
          animating = true;
          
          const current_fs = $(this).closest('fieldset');
          const next_fs = current_fs.next('fieldset');
          
          // Validación especial para el primer paso
          if (current_fs.index() === 0 && !validarRutCliente()) {
              animating = false;
              return false;
          }
          
          // Validar campos requeridos
          let valid = true;
          current_fs.find('input[required], textarea[required]').each(function() {
              if (!$(this).val()) {
                  valid = false;
                  $(this).addClass('error');
              } else {
                  $(this).removeClass('error');
              }
          });
          
          if (!valid) {
              animating = false;
              alert('Por favor complete todos los campos requeridos');
              return false;
          }
          
          // Actualizar resumen si es el último paso
          if (next_fs.find('#resumen-datos').length) {
              actualizarResumen();
          }
          
          // Animación
          current_fs.fadeOut(400, function() {
              next_fs.fadeIn(400, function() {
                  animating = false;
                  // Actualizar progressbar
                  $("#progressbar li").removeClass("active");
                  $("#progressbar li").eq(next_fs.index()).addClass("active");
              });
          });
      });

      // Navegación hacia atrás
      $(".previous").click(function() {
          if (animating) return false;
          animating = true;
          
          const current_fs = $(this).closest('fieldset');
          const previous_fs = current_fs.prev('fieldset');
          
          current_fs.fadeOut(400, function() {
              previous_fs.fadeIn(400, function() {
                  animating = false;
                  // Actualizar progressbar
                  $("#progressbar li").removeClass("active");
                  $("#progressbar li").eq(previous_fs.index()).addClass("active");
              });
          });
      });

      // Función para actualizar el resumen
      function actualizarResumen() {
          const datos = {
              fecha: $('#fecha').val(),
              rut: $('input[name="rut"]').val(),
              nombre: $('input[name="nombre"]').val(),
              telefono: $('input[name="telefono"]').val(),
              email: $('input[name="email"]').val(),
              linea1: $('input[name="linea1"]').val(),
              linea2: $('input[name="linea2"]').val() || 'N/A',
              linea3: $('input[name="linea3"]').val() || 'N/A',
              observacion: $('textarea[name="observacion"]').val() || 'Ninguna',
              colorLetra: $('input[name="colorLetra"]').val(),
              numeroLetra: $('input[name="numeroLetra"]').val(),
              fechaEntrega: $('#fechaEntrega').val(),
              horaEntrega: $('#horaEntrega').val()
          };

          const resumenHTML = `
              <div class="resumen-item"><strong>Fecha:</strong> ${datos.fecha}</div>
              <div class="resumen-item"><strong>RUT:</strong> ${datos.rut}</div>
              <div class="resumen-item"><strong>Nombre:</strong> ${datos.nombre}</div>
              <div class="resumen-item"><strong>Teléfono:</strong> ${datos.telefono}</div>
              <div class="resumen-item"><strong>Email:</strong> ${datos.email}</div>
              <div class="resumen-item"><strong>Línea 1:</strong> ${datos.linea1}</div>
              <div class="resumen-item"><strong>Línea 2:</strong> ${datos.linea2}</div>
              <div class="resumen-item"><strong>Línea 3:</strong> ${datos.linea3}</div>
              <div class="resumen-item"><strong>Color:</strong> ${datos.colorLetra}</div>
              <div class="resumen-item"><strong>Tamaño:</strong> ${datos.numeroLetra}</div>
              <div class="resumen-item"><strong>Observaciones:</strong> ${datos.observacion}</div>
              <div class="resumen-item"><strong>Fecha Entrega:</strong> ${datos.fechaEntrega}</div>
              <div class="resumen-item"><strong>Hora Entrega:</strong> ${datos.horaEntrega}</div>
          `;

          $('#resumen-datos').html(resumenHTML);
      }
    });
  </script>
</body>
</html>