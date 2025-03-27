// Variables globales
const elementosSeleccionados = [];
let opcionActual = 0;
let precios = {
    linea1: 3000,
    lineaExtra: 2000,
    logo: 5000,
    icono: 3500
};

// Inicialización al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar la primera opción por defecto
    verOpcion(0);
    
    // Configurar eventos
    configurarEventos();
    
    // Cargar íconos si existe la opción
    if (document.getElementById('opcion-2')) {
        cargarIconos();
    }
});

// Configura los eventos de la página
function configurarEventos() {
    // Evento para el menú móvil
    const menuMobile = document.querySelector('.menu-mobile');
    if (menuMobile) {
        menuMobile.addEventListener('click', function() {
            const nav = document.querySelector('nav');
            nav.classList.toggle('active');
        });
    }
    
    // Evento para cerrar menú al hacer clic en un enlace
    document.querySelectorAll('nav a').forEach(enlace => {
        enlace.addEventListener('click', function() {
            const nav = document.querySelector('nav');
            nav.classList.remove('active');
        });
    });
}

// Muestra la opción seleccionada
function verOpcion(num) {
    opcionActual = num;
    
    // Oculta todas las opciones
    document.querySelectorAll('.detalle-opcion').forEach(opcion => {
        opcion.style.display = 'none';
    });
    
    // Muestra la opción seleccionada
    const opcion = document.getElementById('opcion-' + num);
    if (opcion) opcion.style.display = 'block';
    
    // Resalta el botón seleccionado
    document.querySelectorAll('.boton-opcion').forEach(boton => {
        boton.style.backgroundColor = 'white';
        boton.style.border = '1px solid #ddd';
    });
    
    const botonSeleccionado = document.getElementById('boton-' + num);
    if (botonSeleccionado) {
        botonSeleccionado.style.backgroundColor = '#f8f9fa';
        botonSeleccionado.style.border = '1px solid var(--color-primario)';
    }
}

// Agrega una nueva línea de texto
function agregarLinea() {
    const form = document.getElementById('form-linea');
    const fuente = form.querySelector('input[name="fuente"]:checked');
    const color = form.querySelector('input[name="color"]:checked');
    const texto = form.querySelector('input[name="texto"]').value.trim();
    
    // Validaciones
    if (!fuente) {
        mostrarError('Por favor selecciona una fuente');
        return;
    }
    
    if (!color) {
        mostrarError('Por favor selecciona un color');
        return;
    }
    
    if (!texto) {
        mostrarError('Por favor ingresa el texto a bordar');
        return;
    }
    
    if (texto.length > 27) {
        mostrarError('El texto no puede exceder los 27 caracteres');
        return;
    }
    
    // Contar líneas existentes del mismo tipo
    const lineasExistentes = elementosSeleccionados.filter(item => item.tipo === 'linea').length;
    
    // Calcular precio
    let precio = lineasExistentes === 0 ? precios.linea1 : precios.lineaExtra;
    
    // Crear objeto de línea
    const linea = {
        tipo: 'linea',
        fuente: fuente.value,
        fuenteNombre: fuente.nextElementSibling.textContent,
        color: color.value,
        colorNombre: color.parentElement.querySelector('.cuadro').title,
        texto: texto,
        precio: precio
    };
    
    // Agregar a la lista
    elementosSeleccionados.push(linea);
    
    // Actualizar vista
    actualizarElementosSeleccionados();
    
    // Limpiar formulario
    form.querySelector('input[name="texto"]').value = '';
    
    // Mostrar confirmación
    mostrarExito('Línea agregada correctamente');
}

// Sube un logo personalizado
function subirLogo() {
    const input = document.getElementById('form-logo-cliente').querySelector('input[type="file"]');
    const archivo = input.files[0];
    
    // Validaciones
    if (!archivo) {
        mostrarError('Por favor selecciona un archivo');
        return;
    }
    
    // Validar tamaño (máx 5MB)
    if (archivo.size > 5 * 1024 * 1024) {
        mostrarError('El archivo no puede exceder los 5MB');
        return;
    }
    
    // Validar tipo de archivo
    const tiposPermitidos = ['image/jpeg', 'image/png', 'image/svg+xml'];
    if (!tiposPermitidos.includes(archivo.type)) {
        mostrarError('Formato no válido. Usa JPG, PNG o SVG');
        return;
    }
    
    // Mostrar carga
    mostrarCarga('Procesando tu logo...');
    
    // Simular carga (en producción sería una petición AJAX)
    setTimeout(() => {
        // Crear objeto de logo
        const logo = {
            tipo: 'logo',
            nombre: archivo.name,
            tamaño: (archivo.size / 1024).toFixed(2) + ' KB',
            precio: precios.logo,
            archivo: archivo
        };
        
        // Agregar a la lista
        elementosSeleccionados.push(logo);
        
        // Actualizar vista
        actualizarElementosSeleccionados();
        
        // Limpiar input
        input.value = '';
        
        // Ocultar carga
        ocultarCarga();
        
        // Mostrar confirmación
        mostrarExito('Logo subido correctamente');
    }, 1500);
}

// Carga los íconos disponibles
function cargarIconos() {
    const contenedor = document.getElementById('form-icono');
    
    // Simulación de íconos (en producción vendrían de una API)
    const iconos = [
        { id: 1, nombre: 'Corazón', categoria: 'Símbolos', precio: precios.icono },
        { id: 2, nombre: 'Estrella', categoria: 'Símbolos', precio: precios.icono },
        { id: 3, nombre: 'Flor', categoria: 'Naturaleza', precio: precios.icono },
        { id: 4, nombre: 'Árbol', categoria: 'Naturaleza', precio: precios.icono },
        { id: 5, nombre: 'Animal', categoria: 'Naturaleza', precio: precios.icono },
        { id: 6, nombre: 'Deporte', categoria: 'Actividades', precio: precios.icono }
    ];
    
    // Crear HTML para los íconos
    let html = '<h5>Selecciona una categoría</h5>';
    html += '<div class="categorias">';
    
    // Obtener categorías únicas
    const categorias = [...new Set(iconos.map(icono => icono.categoria))];
    
    categorias.forEach(categoria => {
        html += `<button class="btn-categoria" onclick="filtrarIconos('${categoria}')">${categoria}</button>`;
    });
    
    html += '</div>';
    html += '<div class="grid-iconos" id="grid-iconos"></div>';
    
    contenedor.innerHTML = html;
    
    // Mostrar todos los íconos inicialmente
    mostrarIconos(iconos);
}

// Muestra los íconos en el grid
function mostrarIconos(iconos) {
    const grid = document.getElementById('grid-iconos');
    if (!grid) return;
    
    grid.innerHTML = '';
    
    iconos.forEach(icono => {
        grid.innerHTML += `
            <div class="card-icono" onclick="seleccionarIcono(${icono.id})">
                <div class="icono-preview">[Icono ${icono.nombre}]</div>
                <h4>${icono.nombre}</h4>
                <p>$${icono.precio.toLocaleString()}</p>
            </div>
        `;
    });
}

// Filtra íconos por categoría
function filtrarIconos(categoria) {
    // En una implementación real, esto haría una petición filtrada al servidor
    console.log(`Filtrando por categoría: ${categoria}`);
}

// Selecciona un ícono para agregarlo
function seleccionarIcono(idIcono) {
    // En una implementación real, se obtendrían los detalles del ícono del servidor
    const icono = {
        tipo: 'icono',
        id: idIcono,
        nombre: `Icono ${idIcono}`,
        precio: precios.icono
    };
    
    // Agregar a la lista
    elementosSeleccionados.push(icono);
    
    // Actualizar vista
    actualizarElementosSeleccionados();
    
    // Mostrar confirmación
    mostrarExito('Ícono agregado correctamente');
}

// Actualiza la lista de elementos seleccionados
function actualizarElementosSeleccionados() {
    const contenedor = document.getElementById('listado-elemento');
    if (!contenedor) return;
    
    // Calcular total
    const total = elementosSeleccionados.reduce((sum, item) => sum + item.precio, 0);
    
    // Crear HTML
    let html = '<div class="lista-elementos">';
    
    if (elementosSeleccionados.length === 0) {
        html += '<p class="vacio">No hay elementos seleccionados</p>';
    } else {
        elementosSeleccionados.forEach((item, index) => {
            html += `<div class="elemento" data-index="${index}">`;
            
            if (item.tipo === 'linea') {
                html += `
                    <div class="elemento-info">
                        <h4>Línea de texto</h4>
                        <p>Texto: "${item.texto}"</p>
                        <p>Fuente: ${item.fuenteNombre}</p>
                        <p>Color: ${item.colorNombre}</p>
                    </div>
                `;
            } else if (item.tipo === 'logo') {
                html += `
                    <div class="elemento-info">
                        <h4>Logo personalizado</h4>
                        <p>Archivo: ${item.nombre}</p>
                        <p>Tamaño: ${item.tamaño}</p>
                    </div>
                `;
            } else if (item.tipo === 'icono') {
                html += `
                    <div class="elemento-info">
                        <h4>Ícono prediseñado</h4>
                        <p>Nombre: ${item.nombre}</p>
                    </div>
                `;
            }
            
            html += `
                <div class="elemento-precio">
                    <p>$${item.precio.toLocaleString()}</p>
                    <button class="btn-eliminar" onclick="eliminarElemento(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            
            html += '</div>';
        });
        
        html += `
            <div class="total">
                <h4>Total:</h4>
                <h4>$${total.toLocaleString()}</h4>
            </div>
            <button class="btn-continuar" onclick="continuarPedido()">
                Continuar con el pedido <i class="fas fa-arrow-right"></i>
            </button>
        `;
    }
    
    contenedor.innerHTML = html;
}

// Elimina un elemento de la lista
function eliminarElemento(index) {
    if (index >= 0 && index < elementosSeleccionados.length) {
        elementosSeleccionados.splice(index, 1);
        actualizarElementosSeleccionados();
        mostrarExito('Elemento eliminado');
    }
}

// Continúa con el proceso de pedido
function continuarPedido() {
    if (elementosSeleccionados.length === 0) {
        mostrarError('Agrega al menos un elemento para continuar');
        return;
    }
    
    // Aquí redirigirías a la página de checkout o mostrarías un formulario
    alert('Redirigiendo a la página de checkout con ' + elementosSeleccionados.length + ' elementos');
    console.log('Elementos a bordar:', elementosSeleccionados);
}

// Muestra un mensaje de error
function mostrarError(mensaje) {
    const notificacion = document.createElement('div');
    notificacion.className = 'notificacion error';
    notificacion.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${mensaje}`;
    
    document.body.appendChild(notificacion);
    
    setTimeout(() => {
        notificacion.classList.add('fade-out');
        setTimeout(() => notificacion.remove(), 500);
    }, 3000);
}

// Muestra un mensaje de éxito
function mostrarExito(mensaje) {
    const notificacion = document.createElement('div');
    notificacion.className = 'notificacion exito';
    notificacion.innerHTML = `<i class="fas fa-check-circle"></i> ${mensaje}`;
    
    document.body.appendChild(notificacion);
    
    setTimeout(() => {
        notificacion.classList.add('fade-out');
        setTimeout(() => notificacion.remove(), 500);
    }, 3000);
}

// Muestra un indicador de carga
function mostrarCarga(mensaje) {
    const carga = document.createElement('div');
    carga.id = 'carga-global';
    carga.innerHTML = `
        <div class="carga-contenido">
            <div class="spinner"></div>
            <p>${mensaje}</p>
        </div>
    `;
    
    document.body.appendChild(carga);
}

// Oculta el indicador de carga
function ocultarCarga() {
    const carga = document.getElementById('carga-global');
    if (carga) {
        carga.classList.add('fade-out');
        setTimeout(() => carga.remove(), 500);
    }
}
// Funciones comunes utilizadas en toda la aplicación

// Formatea un número como precio
function formatoPrecio(numero) {
    return '$' + numero.toLocaleString('es-CL');
}

// Valida un email
function validarEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
}

// Maneja errores de AJAX
function manejarErrorAJAX(error) {
    console.error('Error en la solicitud:', error);
    mostrarError('Ocurrió un error al procesar la solicitud');
}

// Cierra sesión del usuario
function cerrarSesion() {
    // Limpiar datos de sesión del cliente
    localStorage.removeItem('token');
    localStorage.removeItem('usuario');
    
    // Redirigir al login
    window.location.href = '/login/';
}

// Verifica si el usuario está autenticado
function estaAutenticado() {
    return localStorage.getItem('token') !== null;
}

// Obtiene datos del usuario actual
function obtenerUsuario() {
    const usuario = localStorage.getItem('usuario');
    return usuario ? JSON.parse(usuario) : null;
}

// Muestra u oculta el menú móvil
function toggleMenu() {
    const nav = document.querySelector('nav');
    nav.classList.toggle('active');
}

// Configura el menú móvil
function configurarMenuMobile() {
    const menuBtn = document.querySelector('.menu-mobile');
    if (menuBtn) {
        menuBtn.addEventListener('click', toggleMenu);
    }
}

// Inicializa componentes comunes
function inicializarComunes() {
    configurarMenuMobile();
    
    // Verificar autenticación en páginas que lo requieran
    if (document.body.classList.contains('requiere-autenticacion') && !estaAutenticado()) {
        window.location.href = '/login/';
    }
}

// Ejecutar inicialización al cargar
document.addEventListener('DOMContentLoaded', inicializarComunes);

// Configuración principal de la aplicación
const App = {
    init: function() {
        // Cargar scripts adicionales
        this.cargarScripts();
        
        // Inicializar componentes
        this.inicializarComponentes();
    },
    
    cargarScripts: function() {
        // Cargar scripts definidos en _archivosJS
        if (typeof _archivosJS !== 'undefined') {
            _archivosJS.forEach(script => {
                const tag = document.createElement('script');
                tag.src = script;
                document.body.appendChild(tag);
            });
        }
    },
    
    inicializarComponentes: function() {
        // Inicializar tooltips
        if (typeof $ !== 'undefined' && $.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip();
        }
        
        // Inicializar modales
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('.modal').modal();
        }
        
        // Configurar eventos globales
        document.addEventListener('click', function(e) {
            // Cerrar menú al hacer clic fuera
            const nav = document.querySelector('nav.active');
            if (nav && !e.target.closest('nav') && !e.target.closest('.menu-mobile')) {
                nav.classList.remove('active');
            }
        });
    }
};

