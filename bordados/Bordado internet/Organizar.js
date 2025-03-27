// Variables globales
let elementosBordado = [];
let prendaSeleccionada = null;
let elementosEnPrenda = [];

// Inicialización al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Ocultar sección móvil si no es móvil
    if (window.innerWidth > 768) {
        document.querySelector('.no-disponible-mobile').style.display = 'none';
    }
    
    // Cargar datos del localStorage
    cargarDatosBordado();
    
    // Configurar arrastrable y soltable
    configurarDragAndDrop();
    
    // Configurar eventos
    document.getElementById('btn-confirmar').addEventListener('click', mostrarConfirmacion);
    document.querySelector('.cerrar-modal').addEventListener('click', cerrarModal);
    document.getElementById('btn-cancelar').addEventListener('click', cerrarModal);
    document.getElementById('btn-aceptar').addEventListener('click', confirmarDiseño);
    
    // Redimensionar en cambios de tamaño
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            document.querySelector('.no-disponible-mobile').style.display = 'block';
            document.getElementById('organizador-container').style.display = 'none';
        } else {
            document.querySelector('.no-disponible-mobile').style.display = 'none';
            document.getElementById('organizador-container').style.display = 'flex';
        }
    });
});

// Carga los datos del bordado desde el localStorage
function cargarDatosBordado() {
    const bordadoGuardado = localStorage.getItem('bordadoPendiente');
    
    if (bordadoGuardado) {
        const datos = JSON.parse(bordadoGuardado);
        elementosBordado = datos.elementos;
        prendaSeleccionada = datos.prenda;
        
        // Mostrar resumen
        mostrarResumenBordado();
        
        // Cargar elementos en el organizador
        cargarElementosOrganizador();
        
        // Cargar imagen de la prenda
        if (prendaSeleccionada) {
            document.getElementById('imagen-prenda').src = prendaSeleccionada.imagen;
            document.getElementById('imagen-prenda').alt = prendaSeleccionada.nombre;
        }
    } else {
        // Redirigir si no hay datos
        window.location.href = '/comenzar/';
    }
}

// Muestra un resumen del bordado pendiente
function mostrarResumenBordado() {
    const contenedor = document.getElementById('bordado-pendiente');
    let html = '<div class="resumen-bordado"><h3>Tu bordado:</h3><ul>';
    
    if (prendaSeleccionada) {
        html += `<li><strong>Prenda:</strong> ${prendaSeleccionada.nombre}</li>`;
    }
    
    elementosBordado.forEach((elemento, index) => {
        if (elemento.tipo === 'linea') {
            html += `<li><strong>Línea ${index + 1}:</strong> "${elemento.texto}" (${elemento.fuenteNombre}, ${elemento.colorNombre})</li>`;
        } else if (elemento.tipo === 'logo') {
            html += `<li><strong>Logo:</strong> ${elemento.nombre}</li>`;
        } else if (elemento.tipo === 'icono') {
            html += `<li><strong>Ícono:</strong> ${elemento.nombre}</li>`;
        }
    });
    
    html += '</ul></div>';
    contenedor.innerHTML = html;
}

// Carga los elementos en el organizador
function cargarElementosOrganizador() {
    const contenedor = document.getElementById('lista-elementos-organizar');
    contenedor.innerHTML = '';
    
    elementosBordado.forEach((elemento, index) => {
        const divElemento = document.createElement('div');
        divElemento.className = 'elemento-organizar';
        divElemento.dataset.index = index;
        divElemento.dataset.tipo = elemento.tipo;
        
        if (elemento.tipo === 'linea') {
            divElemento.innerHTML = `
                <h4>Línea de texto</h4>
                <p>"${elemento.texto}"</p>
                <p><small>${elemento.fuenteNombre}, ${elemento.colorNombre}</small></p>
            `;
        } else if (elemento.tipo === 'logo') {
            divElemento.innerHTML = `
                <h4>Logo personalizado</h4>
                <p><small>${elemento.nombre}</small></p>
            `;
        } else if (elemento.tipo === 'icono') {
            divElemento.innerHTML = `
                <h4>Ícono prediseñado</h4>
                <p><small>${elemento.nombre}</small></p>
            `;
        }
        
        contenedor.appendChild(divElemento);
    });
}

// Configura la funcionalidad de arrastrar y soltar
function configurarDragAndDrop() {
    // Hacer los elementos arrastrables
    interact('.elemento-organizar').draggable({
        inertia: true,
        modifiers: [
            interact.modifiers.restrictRect({
                restriction: 'parent',
                endOnly: true
            })
        ],
        autoScroll: true,
        
        // listeners for drag events
        listeners: {
            start(event) {
                event.target.classList.add('arrastrando');
            },
            
            move(event) {
                const target = event.target;
                const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
                
                target.style.transform = `translate(${x}px, ${y}px)`;
                target.setAttribute('data-x', x);
                target.setAttribute('data-y', y);
            },
            
            end(event) {
                event.target.classList.remove('arrastrando');
                
                // Verificar si se soltó sobre la prenda
                const prendaRect = document.getElementById('prenda-container').getBoundingClientRect();
                const elementoRect = event.target.getBoundingClientRect();
                
                if (
                    elementoRect.right > prendaRect.left &&
                    elementoRect.left < prendaRect.right &&
                    elementoRect.bottom > prendaRect.top &&
                    elementoRect.top < prendaRect.bottom
                ) {
                    // Se soltó dentro de la prenda, crear elemento en la prenda
                    agregarElementoAPrenda(event.target);
                    
                    // Resetear posición del elemento original
                    event.target.style.transform = 'none';
                    event.target.removeAttribute('data-x');
                    event.target.removeAttribute('data-y');
                }
            }
        }
    });
    
    // Hacer la prenda soltable
    interact('#prenda-container').dropzone({
        accept: '.elemento-organizar',
        overlap: 0.5,
        
        ondropactivate: function(event) {
            event.target.classList.add('soltar-activo');
        },
        
        ondragenter: function(event) {
            event.relatedTarget.classList.add('sobre-prenda');
        },
        
        ondragleave: function(event) {
            event.relatedTarget.classList.remove('sobre-prenda');
        },
        
        ondrop: function(event) {
            event.relatedTarget.classList.remove('sobre-prenda');
        },
        
        ondropdeactivate: function(event) {
            event.target.classList.remove('soltar-activo');
        }
    });
}

// Agrega un elemento a la prenda
function agregarElementoAPrenda(elemento) {
    const index = elemento.dataset.index;
    const tipo = elemento.dataset.tipo;
    const datosElemento = elementosBordado[index];
    
    // Crear elemento en la prenda
    const divElementoPrenda = document.createElement('div');
    divElementoPrenda.className = 'elemento-prenda';
    divElementoPrenda.dataset.index = index;
    divElementoPrenda.dataset.tipo = tipo;
    
    // Posición central inicial
    const prendaContainer = document.getElementById('prenda-container');
    const rect = prendaContainer.getBoundingClientRect();
    const centerX = rect.width / 2 - 100;
    const centerY = rect.height / 2 - 50;
    
    divElementoPrenda.style.left = `${centerX}px`;
    divElementoPrenda.style.top = `${centerY}px`;
    
    // Contenido según tipo
    if (tipo === 'linea') {
        divElementoPrenda.innerHTML = `
            <p style="font-family: ${obtenerFamiliaFuente(datosElemento.fuente)}; color: ${obtenerColorCodigo(datosElemento.color)}">
                ${datosElemento.texto}
            </p>
        `;
    } else if (tipo === 'logo') {
        // Mostrar vista previa del logo
        const reader = new FileReader();
        reader.onload = function(e) {
            divElementoPrenda.innerHTML = `<img src="${e.target.result}" alt="Logo" style="max-width: 100%;">`;
        };
        reader.readAsDataURL(datosElemento.archivo);
    } else if (tipo === 'icono') {
        divElementoPrenda.innerHTML = `<i class="fas fa-${datosElemento.icono}" style="font-size: 24px;"></i>`;
    }
    
    // Hacer el elemento arrastrable en la prenda
    interact(divElementoPrenda).draggable({
        inertia: true,
        modifiers: [
            interact.modifiers.restrict({
                restriction: 'parent',
                endOnly: true
            })
        ],
        autoScroll: true,
        
        listeners: {
            move(event) {
                const target = event.target;
                const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
                
                target.style.transform = `translate(${x}px, ${y}px)`;
                target.setAttribute('data-x', x);
                target.setAttribute('data-y', y);
            }
        }
    });
    
    // Agregar botón para eliminar
    const btnEliminar = document.createElement('button');
    btnEliminar.className = 'btn-eliminar';
    btnEliminar.innerHTML = '<i class="fas fa-times"></i>';
    btnEliminar.addEventListener('click', function() {
        divElementoPrenda.remove();
    });
    
    divElementoPrenda.appendChild(btnEliminar);
    prendaContainer.appendChild(divElementoPrenda);
    elementosEnPrenda.push(divElementoPrenda);
}

// Muestra el modal de confirmación
function mostrarConfirmacion() {
    if (elementosEnPrenda.length === 0) {
        mostrarError('Debes colocar al menos un elemento en la prenda');
        return;
    }
    
    // Crear vista previa
    const modal = document.getElementById('modal-confirmacion');
    const vistaPrevia = document.getElementById('vista-previa-confirmacion');
    
    vistaPrevia.innerHTML = '<h4>Vista previa de tu diseño:</h4>';
    
    // Clonar la prenda con los elementos para la vista previa
    const prendaClone = document.getElementById('prenda-container').cloneNode(true);
    prendaClone.style.width = '100%';
    prendaClone.style.height = 'auto';
    prendaClone.style.minHeight = '300px';
    
    vistaPrevia.appendChild(prendaClone);
    
    // Mostrar modal
    modal.style.display = 'block';
}

// Cierra el modal
function cerrarModal() {
    document.getElementById('modal-confirmacion').style.display = 'none';
}

// Confirma el diseño y guarda los datos
function confirmarDiseño() {
    // Recopilar posiciones de los elementos
    const diseño = {
        prenda: prendaSeleccionada,
        elementos: [],
        posiciones: []
    };
    
    elementosEnPrenda.forEach(elemento => {
        const index = elemento.dataset.index;
        const tipo = elemento.dataset.tipo;
        const x = parseFloat(elemento.getAttribute('data-x')) || 0;
        const y = parseFloat(elemento.getAttribute('data-y')) || 0;
        
        diseño.elementos.push(elementosBordado[index]);
        diseño.posiciones.push({
            tipo,
            x,
            y
        });
    });
    
    // Guardar en localStorage
    localStorage.setItem('diseñoConfirmado', JSON.stringify(diseño));
    
    // Redirigir a la página de pago o confirmación
    window.location.href = '/confirmar/';
}

// Funciones auxiliares
function obtenerFamiliaFuente(idFuente) {
    const fuentes = {
        1: "'Seagull', cursive",
        2: "'Diana Script', cursive",
        3: "'Times New Roman', serif"
    };
    return fuentes[idFuente] || 'Arial, sans-serif';
}

function obtenerColorCodigo(idColor) {
    const colores = {
        1: '#ffffff',
        2: '#ffff00',
        3: '#ffa500',
        // ... más colores
        18: '#000000'
    };
    return colores[idColor] || '#000000';
}

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


function continuarPedido() {
    const bordado = {
        prenda: { nombre: "Camisa blanca", imagen: "camisa-blanca.png" },
        elementos: elementosSeleccionados
    };
    
    localStorage.setItem('bordadoPendiente', JSON.stringify(bordado));
    window.location.href = '/organizar/';
}