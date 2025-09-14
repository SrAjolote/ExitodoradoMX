<?php
require_once '../config.php';
requireAdmin();

// DEBUG: Verificar columnas de la tabla rifas
try {
    $debug_columns = $pdo->query("SHOW COLUMNS FROM rifas");
    $columns = $debug_columns->fetchAll();
    echo "<!-- DEBUG: Columnas de la tabla rifas: ";
    foreach ($columns as $col) {
        echo $col['Field'] . " ";
    }
    echo " -->";
} catch (Exception $e) {
    echo "<!-- DEBUG: Error consultando columnas: " . $e->getMessage() . " -->";
}

$rifas_stmt = $pdo->query("SELECT * FROM rifas ORDER BY fecha_creacion DESC");
$rifas = $rifas_stmt->fetchAll();

if (isset($_GET['eliminar_rifa'])) {
    $rifa_id = (int)$_GET['eliminar_rifa'];
    
    try {
        // Obtener la imagen antes de eliminar
        $stmt = $pdo->prepare("SELECT imagen_premio FROM rifas WHERE id = ?");
        $stmt->execute([$rifa_id]);
        $rifa = $stmt->fetch();
        
        // Eliminar imagen si existe
        if ($rifa && !empty($rifa['imagen_premio'])) {
            $ruta_imagen = '../uploads/rifas/' . $rifa['imagen_premio'];
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen);
            }
        }
        
        // Eliminar rifa (los boletos y combos se eliminan automáticamente por CASCADE)
        $pdo->prepare("DELETE FROM rifas WHERE id = ?")->execute([$rifa_id]);
        header('Location: index.php?mensaje=rifa_eliminada');
        exit();
    } catch (Exception $e) {
        header('Location: index.php?error=error_eliminar');
        exit();
    }
}

$mensaje = '';
if (isset($_GET['mensaje'])) {
    switch ($_GET['mensaje']) {
        case 'rifa_eliminada':
            $mensaje = 'Rifa eliminada correctamente';
            break;
    }
}

$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'error_eliminar':
            $error = 'Error al eliminar la rifa';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - Éxito Dorado MX</title>
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #fdf6e3 0%, #f5f1e3 100%);
    color: #333;
    line-height: 1.6;
    min-height: 100vh;
}

/* Debug Info */
.debug-info {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 0.75rem;
    margin: 1rem 0;
    border-radius: 8px;
    font-size: 0.85rem;
    font-family: 'Courier New', monospace;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Header */
.header {
    background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
    color: white;
    padding: 1rem 0;
    box-shadow: 0 4px 20px rgba(212, 175, 55, 0.4);
    position: sticky;
    top: 0;
    z-index: 100;
    backdrop-filter: blur(10px);
}

.header .container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.header h1 {
    font-size: clamp(1.2rem, 4vw, 1.8rem);
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.9rem;
}

/* Container */
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 1rem;
}

/* Navigation Menu */
.nav-menu {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    margin: 1.5rem 0;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    border: 2px solid #d4af37;
    backdrop-filter: blur(10px);
}

.nav-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

/* Buttons */
.btn {
    padding: 1rem 1.5rem;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    position: relative;
    overflow: hidden;
    min-height: 50px;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn-primary {
    background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(27, 94, 32, 0.3);
}

.btn-secondary {
    background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
}

.btn-danger {
    background: linear-gradient(135deg, #c62828 0%, #d32f2f 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(198, 40, 40, 0.3);
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.25);
}

.btn:active {
    transform: translateY(-1px);
}

/* Alerts */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-error {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
    border-left: 4px solid #dc3545;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 2rem 1.5rem;
    text-align: center;
    border: 2px solid #d4af37;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4af37, #b8941f);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.15);
    border-color: #b8941f;
}

.stat-number {
    font-size: clamp(1.8rem, 5vw, 2.5rem);
    font-weight: 800;
    color: #1b5e20;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-label {
    color: #666;
    font-size: 0.95rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Rifas Section */
.rifas-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    margin: 2rem 0;
    box-shadow: 0 12px 40px rgba(0,0,0,0.12);
    border: 2px solid #d4af37;
    position: relative;
}

.section-title {
    color: #d4af37;
    font-size: clamp(1.3rem, 4vw, 1.8rem);
    font-weight: 700;
    margin-bottom: 2rem;
    text-align: center;
    border-bottom: 3px solid #d4af37;
    padding-bottom: 1rem;
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #1b5e20, #2e7d32);
}

/* Rifas Grid */
.rifas-grid {
    display: grid;
    gap: 2rem;
}

.rifa-card {
    background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
    border: 1px solid #e0e0e0;
    border-radius: 20px;
    padding: 2rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    position: relative;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.rifa-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4af37, #1b5e20);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.rifa-card:hover::before {
    transform: scaleX(1);
}

.rifa-card:hover {
    box-shadow: 0 12px 40px rgba(0,0,0,0.18);
    transform: translateY(-5px);
    border-color: #d4af37;
}

/* Rifa Header */
.rifa-header {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
    margin-bottom: 2rem;
}

/* Image Container */
.rifa-image-container {
    position: relative;
    flex-shrink: 0;
}

.rifa-image-admin {
    width: 140px;
    height: 140px;
    background: linear-gradient(135deg, #d4af37, #f4e794);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.rifa-image-admin::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 20px;
    padding: 2px;
    background: linear-gradient(135deg, #d4af37, #1b5e20);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask-composite: xor;
    -webkit-mask-composite: xor;
}

.rifa-image-admin:hover {
    transform: scale(1.05) rotate(2deg);
    box-shadow: 0 12px 35px rgba(212, 175, 55, 0.4);
}

.rifa-image-admin img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}

.rifa-image-admin .default-icon {
    font-size: 3.5rem;
    color: white;
    text-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

/* Image Badge */
.image-badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background: #4caf50;
    color: white;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    border: 3px solid white;
}

.no-image-badge {
    background: #ff9800;
}

/* Rifa Details */
.rifa-details {
    flex: 1;
    min-width: 0;
}

.rifa-title-container {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.rifa-title {
    color: #1b5e20;
    font-size: clamp(1.2rem, 3vw, 1.5rem);
    margin: 0;
    font-weight: 700;
    line-height: 1.3;
}

.rifa-status {
    padding: 0.6rem 1.2rem;
    border-radius: 25px;
    font-size: 0.85rem;
    font-weight: 700;
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.status-activa {
    background: linear-gradient(135deg, #c8e6c9, #a5d6a7);
    color: #1b5e20;
}

.status-inactiva {
    background: linear-gradient(135deg, #ffcdd2, #ef9a9a);
    color: #c62828;
}

.rifa-description {
    color: #555;
    font-size: 1rem;
    margin: 1rem 0;
    font-style: italic;
    background: linear-gradient(135deg, #f0f8ff, #e6f3ff);
    padding: 1.2rem;
    border-radius: 12px;
    border-left: 4px solid #d4af37;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* Rifa Info Grid */
.rifa-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    border: 1px solid #e0e0e0;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.info-item {
    text-align: center;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.info-item:hover {
    background: linear-gradient(135deg, #fafafa, #f0f0f0);
    transform: translateY(-2px);
}

.info-label {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 600;
}

.info-value {
    font-weight: 800;
    color: #333;
    font-size: clamp(1rem, 2.5vw, 1.2rem);
}

.precio-info {
    color: #1b5e20;
    font-size: clamp(1.1rem, 3vw, 1.4rem);
}

.porcentaje-vendido {
    color: #d4af37;
    font-size: clamp(1.1rem, 3vw, 1.4rem);
}

/* Rifa Actions */
.rifa-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
}

.btn-sm {
    padding: 0.8rem 1.5rem;
    font-size: 0.9rem;
    border-radius: 25px;
    min-width: 120px;
    font-weight: 600;
}

/* Logout */
.logout {
    background: transparent;
    border: 2px solid white;
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 25px;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.85rem;
}

.logout:hover {
    background: white;
    color: #d4af37;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255,255,255,0.3);
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    animation: fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(5px);
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 95%;
    max-height: 95%;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0,0,0,0.5);
    animation: scaleIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-content img {
    width: 100%;
    height: auto;
    display: block;
}

.modal-close {
    position: absolute;
    top: 20px;
    right: 30px;
    color: white;
    font-size: 2.5rem;
    font-weight: bold;
    cursor: pointer;
    background: rgba(0,0,0,0.7);
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid rgba(255,255,255,0.3);
}

.modal-close:hover {
    background: rgba(0,0,0,0.9);
    transform: scale(1.1);
    border-color: white;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.8);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
}

/* Scrollbar personalizado */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #d4af37, #b8941f);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #b8941f, #d4af37);
}

/* Media Queries Responsive */
@media (max-width: 1024px) {
    .container {
        padding: 0.75rem;
    }
    
    .nav-buttons {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }
    
    .stat-card {
        padding: 1.5rem 1rem;
    }
    
    .rifa-card {
        padding: 1.5rem;
    }
    
    .rifa-image-admin {
        width: 120px;
        height: 120px;
    }
}

@media (max-width: 768px) {
    .header .container {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .nav-buttons {
        grid-template-columns: 1fr;
        gap: 0.8rem;
    }
    
    .btn {
        padding: 1rem;
        font-size: 0.95rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .stat-card {
        padding: 1.2rem 0.8rem;
    }
    
    .rifa-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 1.5rem;
    }
    
    .rifa-image-admin {
        width: 100px;
        height: 100px;
    }
    
    .rifa-title-container {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 1rem;
    }
    
    .rifa-info {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        padding: 1rem;
    }
    
    .rifa-actions {
        flex-direction: column;
        gap: 0.8rem;
    }
    
    .btn-sm {
        width: 100%;
        min-width: auto;
    }
    
    .rifas-section {
        padding: 1.5rem;
        margin: 1rem 0;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0.5rem;
    }
    
    .nav-menu {
        padding: 1rem;
        margin: 1rem 0;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .rifa-card {
        padding: 1rem;
    }
    
    .rifa-info {
        grid-template-columns: 1fr;
        gap: 0.8rem;
    }
    
    .info-item {
        padding: 0.8rem;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        background: #fafafa;
    }
    
    .modal-close {
        top: 10px;
        right: 15px;
        width: 40px;
        height: 40px;
        font-size: 1.8rem;
    }
    
    .rifa-image-admin {
        width: 80px;
        height: 80px;
    }
    
    .rifa-image-admin .default-icon {
        font-size: 2.5rem;
    }
}

/* Optimizaciones de rendimiento */
.rifa-card,
.stat-card,
.btn {
    will-change: transform;
}

.modal {
    will-change: opacity;
}

/* Estados de focus para accesibilidad */
.btn:focus,
.logout:focus {
    outline: 3px solid rgba(212, 175, 55, 0.5);
    outline-offset: 2px;
}

/* Mejoras para impresión */
@media print {
    .header,
    .nav-menu,
    .rifa-actions,
    .modal {
        display: none !important;
    }
    
    .container {
        max-width: none;
        padding: 0;
    }
    
    .rifa-card {
        break-inside: avoid;
        box-shadow: none;
        border: 1px solid #ccc;
    }
}
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>🎰 Panel Administrativo - Éxito Dorado MX</h1>
            <div class="user-info">
                <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="logout">Cerrar Sesión</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- DEBUG: Mostrar información del sistema -->
        <div class="debug-info">
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="nav-menu">
            <div class="nav-buttons">
                <a href="crear_rifa.php" class="btn btn-primary">➕ Crear Nueva Rifa</a>
                <a href="gestionar_boletos.php" class="btn btn-secondary">🎫 Gestionar Boletos</a>
                <a href="../admin/vista_vivo.php" class="btn btn-secondary" target="_blank">👁️ Vista en Vivo</a>
                <a href="../index.php" class="btn btn-secondary" target="_blank">🌐 Ver Sitio Web</a>
            </div>
        </div>
        
        <?php
        $total_rifas = count($rifas);
        $rifas_activas = count(array_filter($rifas, function($r) { return $r['activa']; }));
        
        $boletos_stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM boletos GROUP BY estado");
        $boletos_stats = [];
        while ($row = $boletos_stmt->fetch()) {
            $boletos_stats[$row['estado']] = $row['total'];
        }
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_rifas; ?></div>
                <div class="stat-label">Total de Rifas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $rifas_activas; ?></div>
                <div class="stat-label">Rifas Activas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $boletos_stats['pagado'] ?? 0; ?></div>
                <div class="stat-label">Boletos Pagados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $boletos_stats['apartado'] ?? 0; ?></div>
                <div class="stat-label">Boletos Apartados</div>
            </div>
        </div>
        
        <div class="rifas-section">
            <h2 class="section-title">📋 Gestión de Rifas</h2>
            
            <?php if (empty($rifas)): ?>
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <h3>No hay rifas creadas</h3>
                    <p>Crea tu primera rifa para comenzar</p>
                    <a href="crear_rifa.php" class="btn btn-primary" style="margin-top: 1rem;">➕ Crear Primera Rifa</a>
                </div>
            <?php else: ?>
                <div class="rifas-grid">
                    <?php foreach ($rifas as $rifa): ?>
                        <?php
                        $boletos_rifa_stmt = $pdo->prepare("SELECT estado, COUNT(*) as total FROM boletos WHERE rifa_id = ? GROUP BY estado");
                        $boletos_rifa_stmt->execute([$rifa['id']]);
                        $boletos_rifa = [];
                        while ($row = $boletos_rifa_stmt->fetch()) {
                            $boletos_rifa[$row['estado']] = $row['total'];
                        }
                        
                        $total_boletos = array_sum($boletos_rifa);
                        $porcentaje_vendido = $total_boletos > 0 ? (($boletos_rifa['pagado'] ?? 0) / $total_boletos) * 100 : 0;
                        
                        // DEBUG: Información detallada de imagen
                        $imagen_campo = isset($rifa['imagen_premio']) ? $rifa['imagen_premio'] : 'CAMPO_NO_EXISTE';
                        $ruta_relativa = '../uploads/rifas/' . $imagen_campo;
                        $ruta_absoluta = realpath($ruta_relativa);
                        $imagen_existe = !empty($imagen_campo) && $imagen_campo !== 'CAMPO_NO_EXISTE' && file_exists($ruta_relativa);
                        ?>
                        
                        <!-- DEBUG para cada rifa -->
                        
                        <div class="rifa-card">
                            <div class="rifa-header">
                                <div class="rifa-image-container">
                                    <div class="rifa-image-admin" onclick="<?php echo $imagen_existe ? "openModal('" . htmlspecialchars($ruta_relativa) . "')" : ''; ?>">
                                        <?php if ($imagen_existe): ?>
                                            <img src="<?php echo htmlspecialchars($ruta_relativa); ?>" 
                                                 alt="<?php echo htmlspecialchars($rifa['nombre']); ?>"
                                                 loading="lazy"
                                                 onerror="this.style.display='none'; this.parentNode.innerHTML='<div class=\'default-icon\'>❌</div><div class=\'image-badge no-image-badge\'>ERR</div>';">
                                            <div class="image-badge">📷</div>
                                        <?php else: ?>
                                            <div class="default-icon">🎁</div>
                                            <div class="image-badge no-image-badge">❌</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="rifa-details">
                                    <div class="rifa-title-container">
                                        <h3 class="rifa-title"><?php echo htmlspecialchars($rifa['nombre']); ?></h3>
                                        <span class="rifa-status <?php echo $rifa['activa'] ? 'status-activa' : 'status-inactiva'; ?>">
                                            <?php echo $rifa['activa'] ? '✅ Activa' : '❌ Inactiva'; ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (isset($rifa['descripcion_premio']) && !empty($rifa['descripcion_premio'])): ?>
                                        <div class="rifa-description">
                                            🎁 <strong>Premio:</strong> <?php echo htmlspecialchars($rifa['descripcion_premio']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="rifa-info">
                                        <div class="info-item">
                                            <div class="info-label">Fecha Entrega</div>
                                            <div class="info-value"><?php echo date('d/m/Y', strtotime($rifa['fecha_entrega'])); ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Total Boletos</div>
                                            <div class="info-value"><?php echo number_format($rifa['total_boletos']); ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Precio</div>
                                            <div class="info-value precio-info">$<?php echo number_format($rifa['precio_boleto'], 2); ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Pagados</div>
                                            <div class="info-value"><?php echo $boletos_rifa['pagado'] ?? 0; ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Apartados</div>
                                            <div class="info-value"><?php echo $boletos_rifa['apartado'] ?? 0; ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">% Vendido</div>
                                            <div class="info-value porcentaje-vendido"><?php echo number_format($porcentaje_vendido, 1); ?>%</div>
                                        </div>
                                    </div>
                                    
                                    <div class="rifa-actions">
                                        <a href="gestionar_boletos.php?rifa=<?php echo $rifa['id']; ?>" class="btn btn-primary btn-sm">📋 Gestionar</a>
                                        <a href="editar_rifa.php?id=<?php echo $rifa['id']; ?>" class="btn btn-secondary btn-sm">✏️ Editar</a>
                                        <a href="?eliminar_rifa=<?php echo $rifa['id']; ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('¿Estás seguro de eliminar esta rifa? Esta acción no se puede deshacer.')">🗑️ Eliminar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal para mostrar imagen completa -->
    <div id="imageModal" class="modal" onclick="closeModal()">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <div class="modal-content" onclick="event.stopPropagation()">
            <img id="modalImage" src="" alt="Imagen del premio">
        </div>
    </div>
    
    <script>
        function openModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.style.display = 'block';
            modalImg.src = imageSrc;
            
            // Prevenir scroll del body
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            const modal = document.getElementById('imageModal');
            modal.style.display = 'none';
            
            // Restaurar scroll del body
            document.body.style.overflow = 'auto';
        }
        
        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>