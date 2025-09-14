// JavaScript para Gestionar Boletos - Éxito Dorado MX
let boletosSeleccionados = [];

function actualizarSeleccionados() {
    const checkboxes = document.querySelectorAll('.boleto-checkbox:checked');
    boletosSeleccionados = Array.from(checkboxes).map(cb => parseInt(cb.value));
    const infoElement = document.getElementById('selected-info');
    if (infoElement) {
        infoElement.textContent = boletosSeleccionados.length + ' boletos seleccionados';
    }
}

function toggleSelectAll() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.boleto-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    actualizarSeleccionados();
}

function seleccionarTodos() {
    const checkboxes = document.querySelectorAll('.boleto-checkbox');
    checkboxes.forEach(cb => cb.checked = true);
    const selectAll = document.getElementById('select-all');
    if (selectAll) selectAll.checked = true;
    actualizarSeleccionados();
}

function deseleccionarTodos() {
    const checkboxes = document.querySelectorAll('.boleto-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    const selectAll = document.getElementById('select-all');
    if (selectAll) selectAll.checked = false;
    actualizarSeleccionados();
}

function cambiarRango(nuevoEstado) {
    const desde = document.getElementById('desde').value;
    const hasta = document.getElementById('hasta').value;
    const nombre = document.getElementById('nombre_rango').value || '';
    const telefono = document.getElementById('telefono_rango').value || '';
    
    if (!desde || !hasta) {
        alert('Por favor ingresa el rango completo (desde y hasta)');
        return;
    }
    
    if (parseInt(desde) > parseInt(hasta)) {
        alert('El número inicial debe ser menor al final');
        return;
    }
    
    if (!confirm(`¿Cambiar boletos del ${desde} al ${hasta} a estado "${nuevoEstado}"?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('accion', 'cambiar_rango');
    formData.append('desde', desde);
    formData.append('hasta', hasta);
    formData.append('nuevo_estado', nuevoEstado);
    formData.append('nombre_cliente', nombre);
    formData.append('telefono_cliente', telefono);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión: ' + error.message);
    });
}

function cambiarMultiple(nuevoEstado) {
    if (boletosSeleccionados.length === 0) {
        alert('Selecciona al menos un boleto usando los checkboxes');
        return;
    }
    
    const nombre = document.getElementById('nombre_multiple').value || '';
    const telefono = document.getElementById('telefono_multiple').value || '';
    
    if (!confirm(`¿Cambiar ${boletosSeleccionados.length} boletos seleccionados a estado "${nuevoEstado}"?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('accion', 'cambiar_multiple');
    formData.append('boletos_ids', JSON.stringify(boletosSeleccionados));
    formData.append('nuevo_estado', nuevoEstado);
    formData.append('nombre_cliente', nombre);
    formData.append('telefono_cliente', telefono);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión: ' + error.message);
    });
}

function cambiarEstado(boletoId, nuevoEstado) {
    let nombre = '';
    let telefono = '';
    
    if (nuevoEstado === 'apartado' || nuevoEstado === 'pagado') {
        nombre = prompt('Nombre del cliente (opcional):') || '';
        telefono = prompt('Teléfono del cliente (opcional):') || '';
    }
    
    const formData = new FormData();
    formData.append('accion', 'cambiar_estado');
    formData.append('boleto_id', boletoId);
    formData.append('nuevo_estado', nuevoEstado);
    formData.append('nombre_cliente', nombre);
    formData.append('telefono_cliente', telefono);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Estado actualizado correctamente');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión: ' + error.message);
    });
}

function aplicarFiltro() {
    const filtro = document.getElementById('filtro').value;
    const url = new URL(window.location);
    url.searchParams.set('filtro', filtro);
    url.searchParams.set('pagina', '1');
    window.location.href = url.toString();
}

// Funciones de utilidad
function limpiarFormularios() {
    // Limpiar rango
    document.getElementById('desde').value = '';
    document.getElementById('hasta').value = '';
    document.getElementById('nombre_rango').value = '';
    document.getElementById('telefono_rango').value = '';
    
    // Limpiar múltiple
    document.getElementById('nombre_multiple').value = '';
    document.getElementById('telefono_multiple').value = '';
    
    // Deseleccionar todo
    deseleccionarTodos();
}

// Función para mostrar información de ayuda
function mostrarAyuda() {
    const ayuda = `
    🚀 GUÍA RÁPIDA DE USO:
    
    📝 CAMBIO POR RANGO:
    • Ingresa números: del 1 al 50
    • Agrega nombre y teléfono (opcional)
    • Haz clic en Liberar, Apartar o Pagado
    
    📋 SELECCIÓN MÚLTIPLE:
    • Marca checkboxes de boletos específicos
    • Agrega datos del cliente (opcional)
    • Usa botones para cambiar estado
    
    ⚡ ACCIONES RÁPIDAS:
    • Seleccionar Todos: marca todos los visibles
    • Deseleccionar: quita todas las marcas
    
    🔍 FILTROS:
    • Cambia el filtro para ver solo ciertos estados
    • Usa paginación para navegar entre páginas
    
    💡 TIPS:
    • Para ventas masivas usa "Cambio por Rango"
    • Para boletos específicos usa "Selección Múltiple"
    • Los cambios son inmediatos y permanentes
    `;
    
    alert(ayuda);
}

// Inicializar cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistema de gestión de boletos iniciado');
    actualizarSeleccionados();
    
    // Agregar event listeners adicionales si es necesario
    const checkboxes = document.querySelectorAll('.boleto-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', actualizarSeleccionados);
    });
    
    // Agregar teclas rápidas
    document.addEventListener('keydown', function(e) {
        // Ctrl + A para seleccionar todos
        if (e.ctrlKey && e.key === 'a') {
            e.preventDefault();
            seleccionarTodos();
        }
        
        // Escape para deseleccionar todos
        if (e.key === 'Escape') {
            deseleccionarTodos();
        }
    });
    
    console.log('Event listeners configurados correctamente');
});