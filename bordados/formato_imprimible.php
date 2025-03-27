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
$conn->set_charset("utf8");

// Verificar que se haya pasado el ID del pedido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de pedido no válido");
}

$pedido_id = intval($_GET['id']);

// Consultar el pedido en la base de datos
$sql = "SELECT * FROM pedidos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No se encontró el pedido solicitado");
}

$pedido = $result->fetch_assoc();

// Formatear datos para mostrar
$datos = [
    'fecha' => date('d/m/Y', strtotime($pedido['fecha'])),
    'rut' => htmlspecialchars($pedido['rut']),
    'nombre' => htmlspecialchars($pedido['nombre']),
    'telefono' => htmlspecialchars($pedido['telefono']),
    'email' => htmlspecialchars($pedido['email']),
    'linea1' => htmlspecialchars($pedido['linea1']),
    'linea2' => !empty($pedido['linea2']) ? htmlspecialchars($pedido['linea2']) : '',
    'linea3' => !empty($pedido['linea3']) ? htmlspecialchars($pedido['linea3']) : '',
    'observacion' => !empty($pedido['observacion']) ? htmlspecialchars($pedido['observacion']) : 'Ninguna',
    'colorLetra' => htmlspecialchars($pedido['color_letra']),
    'numeroLetra' => htmlspecialchars($pedido['numero_letra']),
    'fechaEntrega' => date('d/m/Y', strtotime($pedido['fecha_entrega'])),
    'horaEntrega' => date('H:i', strtotime($pedido['hora_entrega']))
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pedido #<?php echo $pedido_id; ?> - Bordados Personalizados</title>
    <style>
        /* Estilos generales */
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.5;
            color: #333;
            padding: 0;
            margin: 0;
            background-color: #f9f9f9;
        }
        
        /* Contenedor principal para dos copias */
        .contenedor-copias {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            max-width: 210mm;
            margin: 0 auto;
            padding: 10px;
        }
        
        /* Cada copia del formulario */
        .copia-formulario {
            width: 48%;
            border: 1px solid #ddd;
            padding: 15px;
            background-color: white;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            box-sizing: border-box;
        }
        
        /* Encabezado con logo */
        .encabezado {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2c3e50;
        }
        
        .info-empresa {
            flex: 1;
        }
        
        .logo {
            max-width: 80px;
            height: auto;
        }
        
        /* Secciones del formulario */
        .seccion {
            margin-bottom: 15px;
            page-break-inside: avoid;
            font-size: 0.9em;
        }
        
        .seccion h2 {
            background-color: #f2f2f2;
            padding: 8px;
            color: #2c3e50;
            border-left: 4px solid #e74c3c;
            font-size: 1.1em;
            margin: 10px 0;
        }
        
        /* Campos del formulario */
        .campo {
            margin-bottom: 10px;
            display: flex;
            flex-wrap: wrap;
        }
        
        .campo label {
            width: 120px;
            font-weight: bold;
            color: #34495e;
            font-size: 0.85em;
        }
        
        .campo .valor {
            flex: 1;
            padding: 5px;
            border-bottom: 1px solid #ddd;
            min-height: 20px;
            font-size: 0.85em;
        }
        
        /* Diferenciar copias */
        .copia-tienda {
            border-top: 3px solid #e74c3c;
        }
        
        .copia-cliente {
            border-top: 3px solid #3498db;
        }
        
        .titulo-copia {
            text-align: center;
            font-weight: bold;
            margin: -15px -15px 10px -15px;
            padding: 5px;
            font-size: 1em;
        }
        
        .copia-tienda .titulo-copia {
            background-color: #e74c3c;
            color: white;
        }
        
        .copia-cliente .titulo-copia {
            background-color: #3498db;
            color: white;
        }
        
        /* Redes sociales */
        .redes-sociales {
            display: flex;
            gap: 10px;
            margin-top: 8px;
            align-items: center;
        }
        
        .red-social {
            width: 20px;
            height: 20px;
        }
        
        /* Advertencias */
        .advertencia {
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 15px 0;
            font-size: 0.8em;
        }
        
        /* Firma y número de pedido */
        .firma {
            margin-top: 30px;
            font-size: 0.8em;
        }
        
        .numero-pedido {
            text-align: center;
            font-size: 0.9em;
            color: #7f8c8d;
            margin-top: 15px;
        }
        
        /* Estilos para impresión */
        @media print {
            body {
                padding: 0;
                background: white;
            }
            
            .no-print {
                display: none !important;
            }
            
            .contenedor-copias {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: space-between;
                padding: 0;
            }
            
            .copia-formulario {
                width: 48%;
                height: 270mm;
                page-break-after: always;
                page-break-inside: avoid;
                box-shadow: none;
                border: 1px solid #ccc;
                margin-bottom: 0;
            }
            
            @page {
                margin: 0;
                size: A4 portrait;
            }
            
            body {
                margin: 0;
                padding: 5mm;
            }
            
            .copia-cliente {
                border-top: none;
            }
        }

        /* Estilos para los botones */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .action-button {
            padding: 10px 20px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .action-button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .print-btn {
            background: #2c3e50;
        }
        
        .pdf-btn {
            background: #e74c3c;
        }
        
        .back-btn {
            background: #3498db;
        }
        
        /* Mejorar visualización en pantalla */
        @media screen {
            .contenedor-copias {
                max-width: 210mm;
                margin: 20px auto;
            }
            
            .copia-formulario {
                margin-bottom: 30px;
            }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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

    <div class="contenedor-copias">
        <!-- Copia para la Tienda -->
        <div class="copia-formulario copia-tienda">
            <div class="titulo-copia">COPIA PARA LA TIENDA</div>
            
            <div class="encabezado">
                <div class="info-empresa">
                    <h1 style="margin: 0; color: #2c3e50; font-size: 1.3em;">Bordados Personalizados</h1>
                    <p style="margin: 3px 0; color: #7f8c8d; font-size: 0.8em;">Barros Arana 460, Locales 15-20, Concepción. Galería Portales 854, local 3 Temuco</p>
                    <p style="margin: 3px 0; color: #7f8c8d; font-size: 0.8em;">Tel: +56975496325</p>
                </div>
                
                <img src="img/gb-logo02.svg" alt="Logo" class="logo">
            </div>
            
            <!-- Datos del Cliente -->
            <div class="seccion">
                <h2>Datos del Cliente</h2>
                
                <div class="campo">
                    <label>Fecha:</label>
                    <div class="valor" id="fecha-tienda"><?php echo $datos['fecha']; ?></div>
                </div>
                
                <div class="campo">
                    <label>RUT:</label>
                    <div class="valor" id="rut-tienda"><?php echo $datos['rut']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Nombre:</label>
                    <div class="valor" id="nombre-tienda"><?php echo $datos['nombre']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Teléfono:</label>
                    <div class="valor" id="telefono-tienda"><?php echo $datos['telefono']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Correo:</label>
                    <div class="valor" id="email-tienda"><?php echo $datos['email']; ?></div>
                </div>
            </div>
            
            <!-- Detalles del Pedido -->
            <div class="seccion">
                <h2>Detalles del Pedido</h2>
                
                <div class="campo">
                    <label>Línea 1:</label>
                    <div class="valor" id="linea1-tienda"><?php echo $datos['linea1']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Línea 2:</label>
                    <div class="valor" id="linea2-tienda"><?php echo $datos['linea2']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Línea 3:</label>
                    <div class="valor" id="linea3-tienda"><?php echo $datos['linea3']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Color:</label>
                    <div class="valor" id="colorLetra-tienda"><?php echo $datos['colorLetra']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Tamaño:</label>
                    <div class="valor" id="numeroLetra-tienda"><?php echo $datos['numeroLetra']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Observaciones:</label>
                    <div class="valor" id="observacion-tienda"><?php echo $datos['observacion']; ?></div>
                </div>
            </div>
            
            <!-- Entrega -->
            <div class="seccion">
                <h2>Datos de Entrega</h2>
                
                <div class="campo">
                    <label>Fecha Entrega:</label>
                    <div class="valor" id="fechaEntrega-tienda"><?php echo $datos['fechaEntrega']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Hora Entrega:</label>
                    <div class="valor" id="horaEntrega-tienda"><?php echo $datos['horaEntrega']; ?></div>
                </div>
            </div>
            
            <!-- Nota especial para tienda -->
            <div class="advertencia">
                <strong>IMPORTANTE:</strong> "Declaro haber revisado el bordado, y corresponde según ortografía, tipo de letra, color, tipo de logo y cualquier observación que haya entregado al momento de firmar. Cualquier error cometido que no sea responsabilidad de la tienda no dará derecho a reclamo posterior"
            </div>
            
            <!-- Firma y número de pedido -->
            <div class="firma">
                <p style="border-top: 1px solid #333; padding-top: 5px; margin-bottom: 30px;">Firma del Cliente</p>
            </div>
            
            <div class="numero-pedido">
                <p>N° de Pedido: <span id="numeroPedido-tienda" style="font-weight: bold;"></span></p>
            </div>
        </div>
        
        <!-- Copia para el Cliente -->
        <div class="copia-formulario copia-cliente">
            <div class="titulo-copia">COPIA PARA EL CLIENTE</div>
            
            <div class="encabezado">
                <div class="info-empresa">
                    <h1 style="margin: 0; color: #2c3e50; font-size: 1.3em;">Bordados Personalizados</h1>
                    <p style="margin: 3px 0; color: #7f8c8d; font-size: 0.8em;">Barros Arana 460, Locales 15-20, Concepción. Galería Portales 854, local 3 Temuco</p>
                    <p style="margin: 3px 0; color: #7f8c8d; font-size: 0.8em;">Tel: +56975496325</p>
                    
                    <div class="redes-sociales">
                        <img src="https://cdn-icons-png.flaticon.com/512/2111/2111463.png" alt="Instagram" class="red-social">
                        <span>@uniformesclinicosgoldenblaze</span>
                    </div>
                </div>
                
                <img src="img/gb-logo02.svg" alt="Logo" class="logo">
            </div>
            
            <!-- Datos del Cliente -->
            <div class="seccion">
                <h2>Datos del Cliente</h2>
                
                <div class="campo">
                    <label>Fecha:</label>
                    <div class="valor" id="fecha-cliente"><?php echo $datos['fecha']; ?></div>
                </div>
                
                <div class="campo">
                    <label>RUT:</label>
                    <div class="valor" id="rut-cliente"><?php echo $datos['rut']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Nombre:</label>
                    <div class="valor" id="nombre-cliente"><?php echo $datos['nombre']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Teléfono:</label>
                    <div class="valor" id="telefono-cliente"><?php echo $datos['telefono']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Correo:</label>
                    <div class="valor" id="email-cliente"><?php echo $datos['email']; ?></div>
                </div>
            </div>
            
            <!-- Detalles del Pedido -->
            <div class="seccion">
                <h2>Detalles del Pedido</h2>
                
                <div class="campo">
                    <label>Línea 1:</label>
                    <div class="valor" id="linea1-cliente"><?php echo $datos['linea1']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Línea 2:</label>
                    <div class="valor" id="linea2-cliente"><?php echo $datos['linea2']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Línea 3:</label>
                    <div class="valor" id="linea3-cliente"><?php echo $datos['linea3']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Color:</label>
                    <div class="valor" id="colorLetra-cliente"><?php echo $datos['colorLetra']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Tamaño:</label>
                    <div class="valor" id="numeroLetra-cliente"><?php echo $datos['numeroLetra']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Observaciones:</label>
                    <div class="valor" id="observacion-cliente"><?php echo $datos['observacion']; ?></div>
                </div>
            </div>
            
            <!-- Entrega -->
            <div class="seccion">
                <h2>Datos de Entrega</h2>
                
                <div class="campo">
                    <label>Fecha Entrega:</label>
                    <div class="valor" id="fechaEntrega-cliente"><?php echo $datos['fechaEntrega']; ?></div>
                </div>
                
                <div class="campo">
                    <label>Hora Entrega:</label>
                    <div class="valor" id="horaEntrega-cliente"><?php echo $datos['horaEntrega']; ?></div>
                </div>
            </div>
            
            <!-- Advertencia para cliente -->
            <div class="advertencia">
                <strong>IMPORTANTE:</strong> Este comprobante es su garantía. Preséntelo al retirar su pedido. 
                Los bordados personalizados no tienen cambio ni devolución una vez confeccionados. 
                El tiempo de entrega es aproximado y puede variar según la carga de trabajo.
            </div>
            
            <!-- Firma y número de pedido -->
            <div class="firma">
                <p style="border-top: 1px solid #333; padding-top: 5px;">Firma del Cliente</p>
            </div>
            
            <div class="numero-pedido">
                <p>N° de Pedido: <span id="numeroPedido-cliente" style="font-weight: bold;"></span></p>
                <p style="font-size: 0.8em; margin-top: 5px;">Gracias por su preferencia</p>
            </div>
        </div>
    </div>
    
    <!-- Botones (no se imprimirán) -->
    <div class="no-print action-buttons">
        <button onclick="window.print()" class="action-button print-btn">
            <i class="fas fa-print"></i> Imprimir Comprobante
        </button>
        <button id="pdfButton" class="action-button pdf-btn">
            <i class="fas fa-file-pdf"></i> Guardar como PDF
        </button>
        <a href="dashboard.php" class="action-button back-btn">
            <i class="fas fa-arrow-left"></i> Regresar 
        </a>
    </div>

    <!-- Script para cargar datos y generar PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        // Cargar datos desde PHP
        document.addEventListener('DOMContentLoaded', function() {
            const numPedido = `BP-<?php echo str_pad($pedido_id, 5, '0', STR_PAD_LEFT); ?>`;
            
            document.getElementById('numeroPedido-tienda').textContent = numPedido;
            document.getElementById('numeroPedido-cliente').textContent = numPedido;

            // Configurar el botón de PDF
            document.getElementById('pdfButton').addEventListener('click', function() {
                const elemento = document.querySelector('.contenedor-copias');
                const rut = document.getElementById('rut-tienda').textContent.replace(/\//g, '-');
                const fecha = document.getElementById('fecha-tienda').textContent.replace(/\//g, '-');
                
                const opciones = {
                    margin: 10,
                    filename: `Comprobante_${rut}_${fecha}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { 
                        scale: 2,
                        logging: true,
                        useCORS: true
                    },
                    jsPDF: { 
                        unit: 'mm', 
                        format: 'a4', 
                        orientation: 'portrait' 
                    }
                };
                
                // Generar PDF con manejo de errores
                html2pdf().set(opciones).from(elemento).save()
                    .then(() => console.log('PDF generado exitosamente'))
                    .catch(err => console.error('Error al generar PDF:', err));
            });
        });
        
        // Configurar para imprimir en una sola hoja
        window.addEventListener('beforeprint', function() {
            document.querySelector('.contenedor-copias').style.display = 'block';
            document.querySelectorAll('.copia-formulario').forEach(el => {
                el.style.width = '100%';
                el.style.marginBottom = '0';
                el.style.borderBottom = 'none';
            });
            document.querySelector('.copia-cliente').style.borderTop = 'none';
        });
    </script>
</body>
</html>