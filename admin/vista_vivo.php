<?php
// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

try {
    require_once '../config.php';
    
    // Verificar conexión a la base de datos
    if (!isset($pdo)) {
        throw new Exception("Error: No se pudo establecer conexión con la base de datos");
    }
    
    // Obtener boletos recientes (últimas 24 horas)
    $stmt = $pdo->prepare("
        SELECT b.numero_boleto, b.nombre_cliente, b.telefono_cliente, b.estado, 
               b.fecha_apartado, b.fecha_pagado, r.nombre as rifa_nombre
        FROM boletos b 
        JOIN rifas r ON b.rifa_id = r.id 
        WHERE (b.fecha_apartado >= DATE_SUB(NOW(), INTERVAL 24 HOUR) OR 
               b.fecha_pagado >= DATE_SUB(NOW(), INTERVAL 24 HOUR))
        AND (b.estado = 'apartado' OR b.estado = 'pagado')
        ORDER BY GREATEST(COALESCE(b.fecha_apartado, '1970-01-01'), COALESCE(b.fecha_pagado, '1970-01-01')) DESC
        LIMIT 50
    ");
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . implode(", ", $pdo->errorInfo()));
    }
    
    $stmt->execute();
    $boletos_recientes = $stmt->fetchAll();
    
    if ($boletos_recientes === false) {
        throw new Exception("Error al ejecutar la consulta: " . implode(", ", $stmt->errorInfo()));
    }
    
    // Si es una petición AJAX, devolver solo los datos
    if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
        header('Content-Type: application/json');
        echo json_encode($boletos_recientes);
        exit();
    }
    
} catch (Exception $e) {
    // Si es AJAX, devolver error en JSON
    if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
    
    // Si no es AJAX, mostrar error en la página
    $error_message = $e->getMessage();
    $boletos_recientes = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista en Vivo - Éxito Dorado MX</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 25%, #16213e 50%, #0f3460 75%, #533483 100%);
            color: #ffffff;
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Header */
        .header {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
            padding: 1.5rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 3px solid #d4af37;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.5);
            border: 2px solid #d4af37;
        }
        
        .brand-info h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 0.2rem;
            text-shadow: 0 2px 10px rgba(212, 175, 55, 0.5);
        }
        
        .brand-info p {
            font-size: 0.9rem;
            color: #d4af37;
            font-weight: 500;
        }
        
        .live-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #ff4757, #ff3838);
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 20px rgba(255, 71, 87, 0.4);
            animation: pulse 2s infinite;
        }
        
        .live-dot {
            width: 12px;
            height: 12px;
            background: #ffffff;
            border-radius: 50%;
            animation: blink 1s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }
        
        .back-link {
            color: #ffffff;
            text-decoration: none;
            padding: 0.8rem 1.5rem;
            border: 2px solid #d4af37;
            border-radius: 15px;
            transition: all 0.3s ease;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(212, 175, 55, 0.1);
        }
        
        .back-link:hover {
            background: #d4af37;
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
        }
        
        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 20px;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-title h2 {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #d4af37, #f4e794);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            text-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
        }
        
        .page-subtitle {
            font-size: 1.2rem;
            color: #cccccc;
            font-weight: 500;
        }
        
        /* Stats Section */
        .stats-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 3rem;
            border: 1px solid rgba(212, 175, 55, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }
        
        .stat-card {
            text-align: center;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: #d4af37;
        }
        
        .stat-label {
            color: #cccccc;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Boletos List */
        .boletos-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(212, 175, 55, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #d4af37;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .boletos-list {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        .boleto-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            animation: slideIn 0.5s ease-out;
        }
        
        .boleto-item:hover {
            transform: translateX(10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .boleto-item.new {
            animation: newBoleto 1s ease-out;
            border-color: #d4af37;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.5);
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes newBoleto {
            0% {
                background: rgba(212, 175, 55, 0.3);
                transform: scale(1.05);
            }
            100% {
                background: rgba(255, 255, 255, 0.1);
                transform: scale(1);
            }
        }
        
        .boleto-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .boleto-numero {
            font-size: 1.5rem;
            font-weight: 800;
            color: #d4af37;
        }
        
        .boleto-estado {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .estado-apartado {
            background: linear-gradient(135deg, #ffc107, #ffb300);
            color: #000;
        }
        
        .estado-pagado {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #fff;
        }
        
        .boleto-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-icon {
            color: #d4af37;
            font-size: 1.1rem;
        }
        
        .info-text {
            color: #cccccc;
            font-weight: 500;
        }
        
        .boleto-time {
            text-align: right;
            color: #999;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        /* Auto-refresh indicator */
        .refresh-indicator {
            position: fixed;
            top: 100px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: #d4af37;
            padding: 1rem;
            border-radius: 10px;
            border: 1px solid #d4af37;
            font-weight: 600;
            z-index: 1000;
            display: none;
        }
        
        .refresh-indicator.active {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }
        
        .empty-icon {
            font-size: 4rem;
            color: #d4af37;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-text {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        /* Scrollbar */
        .boletos-list::-webkit-scrollbar {
            width: 8px;
        }
        
        .boletos-list::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        .boletos-list::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #d4af37, #b8941f);
            border-radius: 10px;
        }
        
        .boletos-list::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #b8941f, #d4af37);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .page-title h2 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .boleto-header {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }
            
            .boleto-info {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
            
            .boleto-time {
                text-align: left;
            }
            
            .container {
                padding: 1rem 15px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <img src="../logo.jpg" alt="Éxito Dorado MX" class="logo">
                <div class="brand-info">
                    <h1>ÉXITO DORADO MX</h1>
                    <p>Vista en Vivo</p>
                </div>
            </div>
            
            <div class="live-indicator">
                <div class="live-dot"></div>
                EN VIVO
            </div>
            
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Volver al inicio
            </a>
        </div>
    </header>

    <div class="container">
        <?php if (isset($error_message)): ?>
            <div style="background: #ff4757; color: white; padding: 2rem; border-radius: 10px; margin: 2rem 0; text-align: center;">
                <h3>Error 500 - Error del Servidor</h3>
                <p><strong>Detalles del error:</strong></p>
                <p><?php echo htmlspecialchars($error_message); ?></p>
                <hr style="margin: 1rem 0; border-color: rgba(255,255,255,0.3);">
                <p><small>Si el problema persiste, contacta al administrador del sistema.</small></p>
            </div>
        <?php endif; ?>
        
        <div class="page-title">
            <h2>
                <i class="fas fa-eye"></i>
                Vista en Vivo
            </h2>
            <p class="page-subtitle">Boletos que se van comprando en tiempo real</p>
        </div>

        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" id="total-apartados">0</div>
                    <div class="stat-label">Apartados Hoy</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="total-pagados">0</div>
                    <div class="stat-label">Pagados Hoy</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="total-actividad">0</div>
                    <div class="stat-label">Actividad Total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="ultima-actualizacion">--:--</div>
                    <div class="stat-label">Última Actualización</div>
                </div>
            </div>
        </div>

        <div class="boletos-section">
            <h3 class="section-title">
                <i class="fas fa-ticket-alt"></i>
                Actividad Reciente (Últimas 24 horas)
            </h3>
            
            <div class="boletos-list" id="boletos-list">
                <!-- Los boletos se cargarán aquí dinámicamente -->
            </div>
        </div>
    </div>

    <div class="refresh-indicator" id="refresh-indicator">
        <i class="fas fa-sync-alt fa-spin"></i>
        Actualizando...
    </div>

    <script>
        let ultimaActualizacion = new Date();
        let boletosAnteriores = [];

        function formatearFecha(fecha) {
            const ahora = new Date();
            const fechaBoleto = new Date(fecha);
            const diferencia = ahora - fechaBoleto;
            
            const minutos = Math.floor(diferencia / (1000 * 60));
            const horas = Math.floor(diferencia / (1000 * 60 * 60));
            
            if (minutos < 1) {
                return 'Hace un momento';
            } else if (minutos < 60) {
                return `Hace ${minutos} minuto${minutos > 1 ? 's' : ''}`;
            } else if (horas < 24) {
                return `Hace ${horas} hora${horas > 1 ? 's' : ''}`;
            } else {
                return fechaBoleto.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }

        function actualizarEstadisticas(boletos) {
            const apartados = boletos.filter(b => b.estado === 'apartado').length;
            const pagados = boletos.filter(b => b.estado === 'pagado').length;
            const total = boletos.length;
            
            document.getElementById('total-apartados').textContent = apartados;
            document.getElementById('total-pagados').textContent = pagados;
            document.getElementById('total-actividad').textContent = total;
            document.getElementById('ultima-actualizacion').textContent = 
                ultimaActualizacion.toLocaleTimeString('es-ES', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
        }

        function renderizarBoletos(boletos) {
            const container = document.getElementById('boletos-list');
            
            if (boletos.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="empty-text">
                            No hay actividad reciente en las últimas 24 horas
                        </div>
                    </div>
                `;
                return;
            }

            // Detectar boletos nuevos
            const numerosAnteriores = boletosAnteriores.map(b => b.numero_boleto);
            const numerosActuales = boletos.map(b => b.numero_boleto);
            const nuevos = boletos.filter(b => !numerosAnteriores.includes(b.numero_boleto));

            container.innerHTML = boletos.map(boleto => {
                const esNuevo = nuevos.some(n => n.numero_boleto === boleto.numero_boleto);
                const fechaActividad = boleto.estado === 'pagado' ? boleto.fecha_pagado : boleto.fecha_apartado;
                
                return `
                    <div class="boleto-item ${esNuevo ? 'new' : ''}">
                        <div class="boleto-header">
                            <div class="boleto-numero">
                                Boleto #${boleto.numero_boleto}
                            </div>
                            <div class="boleto-estado estado-${boleto.estado}">
                                ${boleto.estado.toUpperCase()}
                            </div>
                        </div>
                        
                        <div class="boleto-info">
                            <div class="info-item">
                                <i class="fas fa-trophy info-icon"></i>
                                <span class="info-text">${boleto.rifa_nombre}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-user info-icon"></i>
                                <span class="info-text">${boleto.nombre_cliente || 'Sin nombre'}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-phone info-icon"></i>
                                <span class="info-text">${boleto.telefono_cliente || 'Sin teléfono'}</span>
                            </div>
                        </div>
                        
                        <div class="boleto-time">
                            <i class="fas fa-clock"></i>
                            ${formatearFecha(fechaActividad)}
                        </div>
                    </div>
                `;
            }).join('');

            boletosAnteriores = [...boletos];
        }

        function mostrarIndicadorActualizacion() {
            const indicator = document.getElementById('refresh-indicator');
            indicator.classList.add('active');
            
            setTimeout(() => {
                indicator.classList.remove('active');
            }, 1500);
        }

        async function cargarBoletos() {
            try {
                mostrarIndicadorActualizacion();
                
                const response = await fetch('vista_vivo.php?ajax=1');
                const boletos = await response.json();
                
                ultimaActualizacion = new Date();
                actualizarEstadisticas(boletos);
                renderizarBoletos(boletos);
                
            } catch (error) {
                console.error('Error al cargar boletos:', error);
            }
        }

        // Cargar boletos inicialmente
        cargarBoletos();

        // Actualizar cada 5 segundos
        setInterval(cargarBoletos, 5000);

        // Actualizar los tiempos relativos cada minuto
        setInterval(() => {
            const timeElements = document.querySelectorAll('.boleto-time');
            timeElements.forEach(element => {
                // Aquí podrías actualizar los tiempos relativos si guardas las fechas originales
            });
        }, 60000);
    </script>
</body>
</html>