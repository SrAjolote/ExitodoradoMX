<?php
require_once '../config.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Obtener ID de rifa
$rifa_id = isset($_GET['rifa']) ? (int)$_GET['rifa'] : 0;

// Si no hay rifa especificada, obtener la primera disponible
if (!$rifa_id) {
    try {
        $stmt = $pdo->query("SELECT id FROM rifas ORDER BY fecha_creacion DESC LIMIT 1");
        $primera_rifa = $stmt->fetch();
        if ($primera_rifa) {
            $rifa_id = $primera_rifa['id'];
        }
    } catch (Exception $e) {
        // Error en consulta
    }
}

$rifa = null;
$boletos = [];
$stats = ['disponible' => 0, 'apartado' => 0, 'pagado' => 0];
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
$telefono_filtro = isset($_GET['telefono']) ? trim($_GET['telefono']) : '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 100;

// Procesar acciones AJAX
if (isset($_POST['accion'])) {
    header('Content-Type: application/json');
    
    try {
        if ($_POST['accion'] == 'cambiar_estado' && isset($_POST['boleto_id']) && isset($_POST['nuevo_estado'])) {
            $boleto_id = (int)$_POST['boleto_id'];
            $nuevo_estado = $_POST['nuevo_estado'];
            $nombre = trim($_POST['nombre_cliente'] ?? '');
            $telefono = trim($_POST['telefono_cliente'] ?? '');
            
            if (in_array($nuevo_estado, ['disponible', 'apartado', 'pagado'])) {
                $fecha_apartado = ($nuevo_estado == 'apartado') ? date('Y-m-d H:i:s') : null;
                $fecha_pagado = ($nuevo_estado == 'pagado') ? date('Y-m-d H:i:s') : null;
                
                if ($nuevo_estado == 'disponible') {
                    $nombre = null;
                    $telefono = null;
                }
                
                $stmt = $pdo->prepare("UPDATE boletos SET estado = ?, nombre_cliente = ?, telefono_cliente = ?, fecha_apartado = ?, fecha_pagado = ? WHERE id = ?");
                $stmt->execute([$nuevo_estado, $nombre, $telefono, $fecha_apartado, $fecha_pagado, $boleto_id]);
                
                echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Estado no válido']);
            }
        
        } elseif ($_POST['accion'] == 'cambiar_rango') {
            $desde = (int)$_POST['desde'];
            $hasta = (int)$_POST['hasta'];
            $nuevo_estado = $_POST['nuevo_estado'];
            $nombre = trim($_POST['nombre_cliente'] ?? '');
            $telefono = trim($_POST['telefono_cliente'] ?? '');
            
            if ($desde > $hasta || $desde < 1) {
                echo json_encode(['success' => false, 'message' => 'Rango no válido']);
                exit();
            }
            
            if (!in_array($nuevo_estado, ['disponible', 'apartado', 'pagado'])) {
                echo json_encode(['success' => false, 'message' => 'Estado no válido']);
                exit();
            }
            
            $stmt = $pdo->prepare("SELECT id FROM boletos WHERE rifa_id = ? AND numero_boleto BETWEEN ? AND ?");
            $stmt->execute([$rifa_id, $desde, $hasta]);
            $boletos_rango = $stmt->fetchAll();
            
            $fecha_apartado = ($nuevo_estado == 'apartado') ? date('Y-m-d H:i:s') : null;
            $fecha_pagado = ($nuevo_estado == 'pagado') ? date('Y-m-d H:i:s') : null;
            
            if ($nuevo_estado == 'disponible') {
                $nombre = null;
                $telefono = null;
            }
            
            foreach ($boletos_rango as $boleto) {
                $stmt = $pdo->prepare("UPDATE boletos SET estado = ?, nombre_cliente = ?, telefono_cliente = ?, fecha_apartado = ?, fecha_pagado = ? WHERE id = ?");
                $stmt->execute([$nuevo_estado, $nombre, $telefono, $fecha_apartado, $fecha_pagado, $boleto['id']]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Rango actualizado: ' . count($boletos_rango) . ' boletos modificados']);
        
        } elseif ($_POST['accion'] == 'cambiar_multiple') {
            $boletos_ids = json_decode($_POST['boletos_ids'], true);
            $nuevo_estado = $_POST['nuevo_estado'];
            $nombre = trim($_POST['nombre_cliente'] ?? '');
            $telefono = trim($_POST['telefono_cliente'] ?? '');
            
            if (!is_array($boletos_ids) || empty($boletos_ids)) {
                echo json_encode(['success' => false, 'message' => 'No hay boletos seleccionados']);
                exit();
            }
            
            $fecha_apartado = ($nuevo_estado == 'apartado') ? date('Y-m-d H:i:s') : null;
            $fecha_pagado = ($nuevo_estado == 'pagado') ? date('Y-m-d H:i:s') : null;
            
            if ($nuevo_estado == 'disponible') {
                $nombre = null;
                $telefono = null;
            }
            
            foreach ($boletos_ids as $boleto_id) {
                $stmt = $pdo->prepare("UPDATE boletos SET estado = ?, nombre_cliente = ?, telefono_cliente = ?, fecha_apartado = ?, fecha_pagado = ? WHERE id = ?");
                $stmt->execute([$nuevo_estado, $nombre, $telefono, $fecha_apartado, $fecha_pagado, (int)$boleto_id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Estados actualizados: ' . count($boletos_ids) . ' boletos modificados']);
        
        } else {
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit();
}

// Obtener información de la rifa
if ($rifa_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM rifas WHERE id = ?");
        $stmt->execute([$rifa_id]);
        $rifa = $stmt->fetch();
        
        if ($rifa) {
            // Obtener estadísticas
            $stmt = $pdo->prepare("SELECT estado, COUNT(*) as total FROM boletos WHERE rifa_id = ? GROUP BY estado");
            $stmt->execute([$rifa_id]);
            while ($row = $stmt->fetch()) {
                $stats[$row['estado']] = $row['total'];
            }
            
            // Preparar consulta para boletos con filtros
            $where_conditions = ["rifa_id = ?"];
            $params = [$rifa_id];
            
            if ($filtro != 'todos') {
                $where_conditions[] = "estado = ?";
                $params[] = $filtro;
            }
            
            if (!empty($telefono_filtro)) {
                $where_conditions[] = "telefono_cliente LIKE ?";
                $params[] = '%' . $telefono_filtro . '%';
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            // Contar total
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM boletos WHERE $where_clause");
            $stmt->execute($params);
            $total_boletos = $stmt->fetchColumn();
            
            // Obtener boletos con paginación - CORREGIDO para MariaDB/MySQL
            $offset = ($pagina - 1) * $por_pagina;
            $limit_query = "SELECT * FROM boletos WHERE $where_clause ORDER BY numero_boleto ASC LIMIT $por_pagina OFFSET $offset";
            $stmt = $pdo->prepare($limit_query);
            $stmt->execute($params);
            $boletos = $stmt->fetchAll();
            
            $total_paginas = ceil($total_boletos / $por_pagina);
        }
    } catch (Exception $e) {
        $error_msg = "Error de base de datos: " . $e->getMessage();
    }
}

// Obtener todas las rifas para el selector
$todas_rifas = [];
try {
    $stmt = $pdo->query("SELECT id, nombre FROM rifas ORDER BY fecha_creacion DESC");
    $todas_rifas = $stmt->fetchAll();
} catch (Exception $e) {
    // Error al obtener rifas
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Boletos - Éxito Dorado MX</title>
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

/* Header */
.header {
    background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
    color: white;
    padding: 1.5rem 0;
    text-align: center;
    box-shadow: 0 4px 25px rgba(212, 175, 55, 0.4);
    position: sticky;
    top: 0;
    z-index: 100;
    backdrop-filter: blur(10px);
}

.header h1 {
    font-size: clamp(1.3rem, 4vw, 2rem);
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    margin-bottom: 1rem;
}

/* Container */
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 1.5rem;
}

/* Cards */
.card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    margin: 1.5rem 0;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    border: 2px solid #d4af37;
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(10px);
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4af37, #1b5e20);
}

.card h3 {
    color: #1b5e20;
    font-size: clamp(1.2rem, 3vw, 1.5rem);
    font-weight: 700;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #d4af37;
    position: relative;
}

.card h3::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 60px;
    height: 2px;
    background: #1b5e20;
}

/* Rifa Selector */
.rifa-selector select {
    width: 100%;
    padding: 1rem;
    border: 2px solid #d4af37;
    border-radius: 12px;
    font-size: 1rem;
    background: white;
    color: #333;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    appearance: none;
    background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4 5"><path fill="%23d4af37" d="M2 0L0 2h4zm0 5L0 3h4z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1rem auto;
    padding-right: 3rem;
}

.rifa-selector select:focus {
    outline: none;
    border-color: #b8941f;
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
    transform: translateY(-2px);
}

.rifa-selector select:hover {
    border-color: #b8941f;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.stat-card {
    background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
    padding: 2rem 1.5rem;
    border-radius: 16px;
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
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-card div:last-child {
    color: #666;
    font-size: 0.95rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-disponible { color: #666; }
.stat-apartado { color: #ff9800; }
.stat-pagado { color: #4caf50; }

/* Controls Section */
.controls-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 16px;
    padding: 2rem;
    margin: 2rem 0;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    border: 2px solid #d4af37;
    position: relative;
}

.controls-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #1b5e20, #2e7d32);
}

.controls-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
}

.control-group {
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 1.5rem;
    background: white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    position: relative;
}

.control-group:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.control-title {
    font-weight: 700;
    color: #1b5e20;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #d4af37;
    font-size: 1.1rem;
    position: relative;
}

.control-title::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 40px;
    height: 2px;
    background: #1b5e20;
}

/* Inputs */
.control-group input {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 0.95rem;
    margin-bottom: 0.75rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
    color: #333;
}

.control-group input:focus {
    outline: none;
    border-color: #d4af37;
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
    transform: translateY(-1px);
}

.control-group input:hover {
    border-color: #d4af37;
}

.control-group input::placeholder {
    color: #999;
    font-style: italic;
}

/* Flex inputs */
.control-group div[style*="display: flex"] {
    display: flex !important;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.control-group div[style*="display: flex"] input {
    flex: 1;
    margin-bottom: 0;
}

/* Quick Buttons */
.quick-buttons {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin: 1.5rem 0;
}

.btn-quick {
    padding: 1rem;
    font-size: 0.9rem;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.btn-quick::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.btn-quick:hover::before {
    left: 100%;
}

.btn-quick:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.btn-quick:active {
    transform: translateY(-1px);
}

.btn-quick.btn-primary {
    background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
    color: white;
}

.btn-quick.btn-warning {
    background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
    color: white;
}

.btn-quick.btn-success {
    background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
    color: white;
}

/* Selected Info */
.selected-info {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    border: 2px solid #d4af37;
    padding: 1rem;
    border-radius: 10px;
    margin: 1rem 0;
    font-weight: 700;
    color: #856404;
    text-align: center;
    box-shadow: 0 2px 8px rgba(212, 175, 55, 0.2);
}

/* Filters Section */
.filters-section {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border-radius: 12px;
    padding: 1.5rem;
    margin: 1.5rem 0;
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 1px solid #d4af37;
}

.filters-section label {
    font-weight: 600;
    color: #1b5e20;
    font-size: 1rem;
}

.filters-section select {
    padding: 0.75rem 1rem;
    border: 2px solid #d4af37;
    border-radius: 8px;
    font-size: 0.95rem;
    background: white;
    color: #333;
    cursor: pointer;
    transition: all 0.3s ease;
    appearance: none;
    background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4 5"><path fill="%23d4af37" d="M2 0L0 2h4zm0 5L0 3h4z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 0.8rem auto;
    padding-right: 2.5rem;
    min-width: 150px;
}

.filters-section select:focus {
    outline: none;
    border-color: #b8941f;
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
}

/* Boletos Table */
.boletos-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border-radius: 12px;
    overflow: hidden;
    background: white;
}

.boletos-table th,
.boletos-table td {
    padding: 1rem 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
    vertical-align: middle;
}

.boletos-table th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    font-weight: 700;
    position: sticky;
    top: 0;
    z-index: 10;
    color: #1b5e20;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.9rem;
    border-bottom: 2px solid #d4af37;
}

.boletos-table tbody tr {
    transition: all 0.3s ease;
}

.boletos-table tbody tr:hover {
    background: rgba(212, 175, 55, 0.1);
    transform: scale(1.01);
}

.boleto-disponible { 
    background: linear-gradient(135deg, #f5f5f5 0%, #eeeeee 100%);
    border-left: 4px solid #9e9e9e;
}

.boleto-apartado { 
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    border-left: 4px solid #ff9800;
}

.boleto-pagado { 
    background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
    border-left: 4px solid #4caf50;
}

/* Estado Badges */
.estado-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    border: 2px solid transparent;
}

.estado-disponible {
    background: linear-gradient(135deg, #e0e0e0, #bdbdbd);
    color: #424242;
    border-color: #9e9e9e;
}

.estado-apartado {
    background: linear-gradient(135deg, #ffe0b2, #ffcc02);
    color: #ef6c00;
    border-color: #ff9800;
}

.estado-pagado {
    background: linear-gradient(135deg, #c8e6c9, #81c784);
    color: #1b5e20;
    border-color: #4caf50;
}

/* Buttons */
.btn {
    padding: 0.6rem 1.2rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    margin: 0.25rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.btn:active {
    transform: translateY(0);
}

.btn-primary { 
    background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
    color: white;
}

.btn-warning { 
    background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
    color: white;
}

.btn-success { 
    background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
    color: white;
}

.btn-secondary { 
    background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
    color: white;
}

/* Checkboxes */
.boletos-table input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #d4af37;
    transform: scale(1.2);
}

.boletos-table input[type="checkbox"]:checked {
    background: #d4af37;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.pagination a, 
.pagination span {
    padding: 0.75rem 1.25rem;
    border: 2px solid #d4af37;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
    min-width: 45px;
    text-align: center;
}

.pagination a:hover {
    background: #d4af37;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
}

.pagination .current {
    background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
    color: white;
    border-color: #1b5e20;
    box-shadow: 0 4px 15px rgba(27, 94, 32, 0.3);
}

/* Error Message */
.error-message {
    background: linear-gradient(135deg, #ffebee, #ffcdd2);
    color: #c62828;
    padding: 1.5rem;
    border-radius: 12px;
    margin: 1.5rem 0;
    border: 2px solid #f44336;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(244, 67, 54, 0.2);
}

/* Tips Section */
.control-group div:last-child {
    font-size: 0.9rem;
    line-height: 1.5;
    color: #666;
    background: linear-gradient(135deg, #f0f8ff, #e6f3ff);
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #d4af37;
    margin-top: 1rem;
}

.control-group div:last-child strong {
    color: #1b5e20;
}

/* Info Box */
div[style*="background: #e8f5e8"] {
    background: linear-gradient(135deg, #e8f5e8, #c8e6c9) !important;
    border: 2px solid #4caf50 !important;
    border-radius: 12px !important;
    padding: 2rem !important;
    margin-top: 2rem !important;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.2) !important;
}

div[style*="background: #e8f5e8"] h4 {
    color: #1b5e20 !important;
    font-size: 1.2rem !important;
    margin-bottom: 1rem !important;
    font-weight: 700 !important;
}

div[style*="background: #e8f5e8"] ul {
    margin-left: 2rem !important;
    margin-top: 1rem !important;
}

div[style*="background: #e8f5e8"] li {
    margin-bottom: 0.5rem !important;
    font-size: 0.95rem !important;
    line-height: 1.5 !important;
}

div[style*="background: #e8f5e8"] strong {
    color: #2e7d32 !important;
}

/* Scrollbar personalizado */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
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

/* Animaciones */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

.card {
    animation: fadeIn 0.6s ease-out;
}

.stat-card {
    animation: slideIn 0.6s ease-out;
}

/* Media Queries */
@media (max-width: 1200px) {
    .container {
        padding: 1rem;
    }
    
    .controls-grid {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
}

@media (max-width: 768px) {
    .container {
        padding: 0.75rem;
    }
    
    .header {
        padding: 1rem 0;
        text-align: left;
    }
    
    .header h1 {
        font-size: 1.4rem;
        margin-bottom: 0.8rem;
        word-break: break-word;
    }
    
    .header .btn {
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
        display: inline-block;
        margin-top: 0.5rem;
    }
    
    .card {
        padding: 1.5rem;
        margin: 1rem 0;
        border-radius: 12px;
        border-width: 1px;
    }
    
    .card h3 {
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }
    
    .rifa-selector select {
        padding: 0.875rem;
        font-size: 0.9rem;
        border-radius: 10px;
        padding-right: 2.5rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .stat-card {
        padding: 1.5rem 1rem;
    }
    
    .stat-number {
        font-size: 1.8rem;
        margin-bottom: 0.3rem;
    }
    
    .stat-card div:last-child {
        font-size: 0.85rem;
    }
    
    .controls-section {
        padding: 1.5rem;
        margin: 1rem 0;
        border-radius: 12px;
        border-width: 1px;
    }
    
    .controls-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .control-group {
        padding: 1.25rem;
        border-radius: 10px;
    }
    
    .control-title {
        font-size: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
    }
    
    .control-group input {
        padding: 0.75rem;
        font-size: 0.9rem;
        margin-bottom: 0.6rem;
        border-radius: 8px;
    }
    
    .control-group input:focus {
        transform: none;
    }
    
    .quick-buttons {
        grid-template-columns: 1fr;
        gap: 0.75rem;
        margin: 1rem 0;
    }
    
    .btn-quick {
        padding: 1rem;
        font-size: 0.9rem;
        border-radius: 8px;
        min-height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .selected-info {
        padding: 1rem;
        font-size: 0.9rem;
        border-radius: 8px;
        margin: 0.75rem 0;
        text-align: center;
    }
    
    .filters-section {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem;
        margin: 1rem 0;
        border-radius: 10px;
        align-items: stretch;
    }
    
    .filters-section select {
        padding: 0.75rem;
        font-size: 0.9rem;
        border-radius: 8px;
        width: 100%;
        padding-right: 2.5rem;
    }
    
    .boletos-table {
        font-size: 0.85rem;
        margin-top: 1rem;
        border-radius: 10px;
    }
    
    .boletos-table th,
    .boletos-table td {
        padding: 0.75rem 0.5rem;
    }
    
    .boletos-table th {
        font-size: 0.8rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    /* Ocultar columnas menos importantes en móviles */
    .boletos-table th:nth-child(4),
    .boletos-table td:nth-child(4),
    .boletos-table th:nth-child(5),
    .boletos-table td:nth-child(5) {
        display: none;
    }
    
    .estado-badge {
        padding: 0.3rem 0.6rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: bold;
        white-space: nowrap;
    }
    
    .boletos-table .btn {
        padding: 0.4rem 0.6rem;
        margin: 0.1rem;
        font-size: 0.75rem;
        border-radius: 6px;
        display: block;
        margin-bottom: 0.25rem;
        width: 100%;
        text-align: center;
        min-height: 36px;
    }
    
    .boletos-table input[type="checkbox"] {
        width: 18px;
        height: 18px;
    }
    
    .pagination {
        flex-wrap: wrap;
        gap: 0.4rem;
        margin: 1.5rem 0;
    }
    
    .pagination a, 
    .pagination span {
        padding: 0.6rem 0.8rem;
        font-size: 0.85rem;
        min-width: 40px;
        border-radius: 6px;
    }
    
    .error-message {
        padding: 1rem;
        margin: 1rem 0;
        border-radius: 10px;
        font-size: 0.9rem;
    }
    
    .control-group div:last-child {
        font-size: 0.85rem;
        line-height: 1.4;
        padding: 0.875rem;
        margin-top: 0.75rem;
    }
    
    div[style*="background: #e8f5e8"] {
        padding: 1.5rem !important;
        margin-top: 1.5rem !important;
        border-radius: 10px !important;
    }
    
    div[style*="background: #e8f5e8"] h4 {
        font-size: 1.1rem !important;
        margin-bottom: 0.75rem !important;
    }
    
    div[style*="background: #e8f5e8"] li {
        margin-bottom: 0.4rem !important;
        font-size: 0.85rem !important;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0.5rem;
    }
    
    .header {
        padding: 0.75rem 0.25rem;
    }
    
    .header h1 {
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
    }
    
    .card {
        padding: 1rem;
        margin: 0.75rem 0;
    }
    
    .card h3 {
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .stat-card {
        padding: 1rem 0.75rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .controls-section {
        padding: 1rem;
    }
    
    .control-group {
        padding: 1rem;
    }
    
    .control-title {
        font-size: 0.95rem;
        margin-bottom: 0.75rem;
    }
    
    .control-group input {
        padding: 0.625rem;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }
    
    .btn-quick {
        padding: 0.875rem;
        font-size: 0.85rem;
        min-height: 44px;
    }
    
    .boletos-table {
        font-size: 0.75rem;
    }
    
    .boletos-table th,
    .boletos-table td {
        padding: 0.5rem 0.25rem;
    }
    
    .boletos-table .btn {
        padding: 0.3rem 0.4rem;
        font-size: 0.7rem;
        margin: 0.05rem;
        min-height: 32px;
    }
    
    .boletos-table th:nth-child(1),
    .boletos-table td:nth-child(1) {
        width: 40px;
    }
    
    .estado-badge {
        padding: 0.2rem 0.4rem;
        font-size: 0.65rem;
    }
    
    .pagination a, 
    .pagination span {
        padding: 0.5rem 0.6rem;
        font-size: 0.8rem;
        min-width: 36px;
    }
    
    .rifa-selector select {
        padding: 0.75rem;
        font-size: 0.85rem;
    }
    
    .filters-section {
        padding: 0.75rem;
    }
    
    .filters-section select {
        padding: 0.625rem;
        font-size: 0.85rem;
    }
}

/* Orientación landscape en móviles */
@media (max-width: 768px) and (orientation: landscape) {
    .header {
        padding: 0.75rem 0.5rem;
    }
    
    .header h1 {
        font-size: 1.3rem;
        margin-bottom: 0.5rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
    }
    
    .controls-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .quick-buttons {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }
    
    .btn-quick {
        padding: 0.75rem;
        font-size: 0.8rem;
    }
    
    /* Mostrar más columnas en landscape */
    .boletos-table th:nth-child(4),
    .boletos-table td:nth-child(4) {
        display: table-cell;
    }
}

/* Mejoras de accesibilidad táctil */
@media (max-width: 768px) {
    /* Áreas de toque más grandes */
    button, 
    input[type="button"], 
    input[type="submit"], 
    .btn {
        min-height: 44px;
        min-width: 44px;
        cursor: pointer;
        touch-action: manipulation;
        -webkit-tap-highlight-color: rgba(0,0,0,0.1);
    }
    
    /* Selectores más grandes */
    select {
        min-height: 44px;
        cursor: pointer;
        touch-action: manipulation;
    }
    
    /* Checkboxes más grandes */
    input[type="checkbox"] {
        min-width: 20px;
        min-height: 20px;
        cursor: pointer;
        touch-action: manipulation;
    }
    
    /* Prevenir zoom en inputs */
    input[type="text"],
    input[type="number"],
    input[type="tel"],
    input[type="email"],
    select,
    textarea {
        font-size: 16px !important; /* Previene zoom en iOS */
    }
    
    /* Mejorar contraste para pantallas móviles */
    .boleto-disponible { 
        background: linear-gradient(135deg, #f0f0f0, #e8e8e8);
        border-left: 4px solid #757575;
    }
    
    .boleto-apartado { 
        background: linear-gradient(135deg, #fff3e0, #ffe0b2);
        border-left: 4px solid #ff9800;
    }
    
    .boleto-pagado { 
        background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
        border-left: 4px solid #4caf50;
    }
    
    /* Estados más visibles */
    .estado-disponible {
        background: linear-gradient(135deg, #e0e0e0, #bdbdbd);
        color: #424242;
        border: 2px solid #9e9e9e;
    }
    
    .estado-apartado {
        background: linear-gradient(135deg, #ffe0b2, #ffcc02);
        color: #ef6c00;
        border: 2px solid #ff9800;
    }
    
    .estado-pagado {
        background: linear-gradient(135deg, #c8e6c9, #81c784);
        color: #1b5e20;
        border: 2px solid #4caf50;
    }
}

/* Animaciones suaves para móviles */
@media (max-width: 768px) {
    .btn,
    .btn-quick,
    input,
    select {
        transition: all 0.2s ease;
    }
    
    .btn:hover,
    .btn-quick:hover {
        transform: none; /* Quitar hover effects en móviles */
    }
    
    .btn:active,
    .btn-quick:active {
        transform: scale(0.95);
        transition: transform 0.1s ease;
    }
    
    /* Animación de carga para acciones */
    .loading {
        opacity: 0.7;
        pointer-events: none;
        position: relative;
    }
    
    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
}

/* Mejoras para scroll en móviles */
@media (max-width: 768px) {
    .boletos-table {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Scrollbar personalizada para móviles */
    .boletos-table::-webkit-scrollbar {
        height: 8px;
    }
    
    .boletos-table::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .boletos-table::-webkit-scrollbar-thumb {
        background: #d4af37;
        border-radius: 4px;
    }
    
    .boletos-table::-webkit-scrollbar-thumb:hover {
        background: #b8941f;
    }
}

/* Dark mode considerations para móviles */
@media (max-width: 768px) and (prefers-color-scheme: dark) {
    .card {
        background: #2d2d2d;
        color: #ffffff;
        border-color: #d4af37;
    }
    
    .control-group {
        background: #3d3d3d;
        border-color: #555;
    }
    
    .boletos-table th {
        background: linear-gradient(135deg, #3d3d3d, #2d2d2d);
        color: #ffffff;
    }
    
    .boletos-table {
        background: #2d2d2d;
    }
    
    input, select {
        background: #3d3d3d;
        color: #ffffff;
        border-color: #555;
    }
    
    input::placeholder {
        color: #aaa;
    }
}

/* Fixes específicos para iOS */
@supports (-webkit-touch-callout: none) {
    @media (max-width: 768px) {
        input, select, textarea {
            -webkit-appearance: none;
            border-radius: 8px;
        }
        
        .btn, .btn-quick {
            -webkit-appearance: none;
            border-radius: 8px;
        }
        
        /* Fix para inputs en iOS */
        input[type="number"],
        input[type="tel"],
        input[type="text"] {
            -webkit-appearance: none;
            -moz-appearance: textfield;
        }
    }
}

/* Fixes específicos para Android */
@media (max-width: 768px) {
    input[type="number"] {
        -moz-appearance: textfield;
    }
    
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
}

/* Optimizaciones de rendimiento */
.card,
.stat-card,
.btn,
.btn-quick {
    will-change: transform;
}

.boletos-table tbody tr {
    will-change: transform, background-color;
}

/* Estados de focus para accesibilidad */
.btn:focus,
.btn-quick:focus,
input:focus,
select:focus {
    outline: 3px solid rgba(212, 175, 55, 0.5);
    outline-offset: 2px;
}

/* Mejoras para impresión */
@media print {
    .header,
    .controls-section,
    .filters-section,
    .pagination,
    .btn,
    .btn-quick {
        display: none !important;
    }
    
    .container {
        max-width: none;
        padding: 0;
    }
    
    .card {
        break-inside: avoid;
        box-shadow: none;
        border: 1px solid #ccc;
        page-break-inside: avoid;
    }
    
    .boletos-table {
        font-size: 10px;
    }
    
    .boletos-table th,
    .boletos-table td {
        padding: 0.25rem;
        border: 1px solid #ccc;
    }
    
    .boletos-table th:nth-child(4),
    .boletos-table td:nth-child(4),
    .boletos-table th:nth-child(5),
    .boletos-table td:nth-child(5),
    .boletos-table th:nth-child(6),
    .boletos-table td:nth-child(6) {
        display: none;
    }
}

/* Animaciones adicionales para elementos interactivos */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.stat-card:hover {
    animation: pulse 0.6s ease-in-out;
}

.btn-quick:active {
    animation: pulse 0.3s ease-in-out;
}

/* Indicadores de carga */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}

.loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid rgba(212, 175, 55, 0.3);
    border-top: 4px solid #d4af37;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Alertas móviles personalizadas */
.alerta-movil {
    position: fixed;
    top: 20px;
    left: 15px;
    right: 15px;
    padding: 1rem;
    border-radius: 10px;
    z-index: 10001;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    backdrop-filter: blur(10px);
    text-align: center;
    font-size: 0.95rem;
}

/* Modales responsive */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    backdrop-filter: blur(5px);
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    width: 100%;
    max-width: 450px;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    position: relative;
    -webkit-overflow-scrolling: touch;
}

.modal-content h3 {
    margin: 0 0 1.5rem 0;
    color: #1b5e20;
    text-align: center;
    font-size: 1.3rem;
    font-weight: 700;
}

.modal-content input {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.modal-content input:focus {
    border-color: #d4af37;
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
    outline: none;
}

.modal-content .btn-group {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.modal-content .btn-group button {
    flex: 1;
    padding: 1rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

/* Tooltips para móviles */
.tooltip {
    position: relative;
    display: inline-block;
}

.tooltip .tooltiptext {
    visibility: hidden;
    width: 200px;
    background-color: rgba(0,0,0,0.9);
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 8px 12px;
    position: absolute;
    z-index: 1001;
    bottom: 125%;
    left: 50%;
    margin-left: -100px;
    font-size: 0.85rem;
    opacity: 0;
    transition: opacity 0.3s;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}

/* Mejoras adicionales para UX móvil */
@media (max-width: 768px) {
    /* Mejorar spacing entre elementos tocables */
    .btn + .btn {
        margin-top: 0.5rem;
    }
    
    /* Mejorar legibilidad en pantallas pequeñas */
    body {
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
    }
    
    /* Optimizar imágenes y media queries */
    img {
        max-width: 100%;
        height: auto;
    }
    
    /* Prevenir problemas de layout en móviles */
    * {
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
    }
    
    /* Mejorar performance de scroll */
    .container {
        -webkit-overflow-scrolling: touch;
        overflow-x: hidden;
    }
    
    /* Optimizar transiciones para dispositivos móviles */
    * {
        -webkit-transform: translate3d(0,0,0);
        transform: translate3d(0,0,0);
    }
}
    </style>
</head>
<body>
    <div class="header">
        <h1>🎫 Gestionar Boletos - Éxito Dorado MX</h1>
        <a href="index.php" class="btn btn-secondary">← Volver al Panel</a>
    </div>
    
    <div class="container">
        <?php if (isset($error_msg)): ?>
            <div class="error-message">
                <strong>Error:</strong> <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($todas_rifas)): ?>
            <div class="card">
                <h3>⚠️ No hay rifas disponibles</h3>
                <p>Necesitas crear una rifa primero para gestionar boletos.</p>
                <a href="crear_rifa.php" class="btn btn-primary">➕ Crear Primera Rifa</a>
            </div>
        <?php else: ?>
            
            <?php if (count($todas_rifas) > 1): ?>
            <div class="card">
                <h3>📋 Seleccionar Rifa</h3>
                <div class="rifa-selector">
                    <select onchange="window.location.href='?rifa=' + this.value">
                        <?php foreach ($todas_rifas as $r): ?>
                        <option value="<?php echo $r['id']; ?>" <?php echo ($r['id'] == $rifa_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($r['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($rifa): ?>
            <div class="card">
                <h3>📊 Estadísticas - <?php echo htmlspecialchars($rifa['nombre']); ?></h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number stat-disponible"><?php echo $stats['disponible']; ?></div>
                        <div>Disponibles</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number stat-apartado"><?php echo $stats['apartado']; ?></div>
                        <div>Apartados</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number stat-pagado"><?php echo $stats['pagado']; ?></div>
                        <div>Pagados</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo array_sum($stats); ?></div>
                        <div>Total</div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h3>⚡ Marcado Rápido de Boletos</h3>
                
                <div class="controls-section">
                    <div class="controls-grid">
                        <!-- Cambio por Rango -->
                        <div class="control-group">
                            <div class="control-title">📝 Cambio por Rango</div>
                            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <input type="number" id="desde" placeholder="Desde" min="1" style="flex: 1; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                                <input type="number" id="hasta" placeholder="Hasta" min="1" style="flex: 1; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                                <input type="text" id="nombre_rango" placeholder="Nombre cliente" style="flex: 1; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                                <input type="tel" id="telefono_rango" placeholder="Teléfono" style="flex: 1; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                            <div class="quick-buttons">
                                <button class="btn-quick btn-primary" onclick="cambiarRango('disponible')">🟢 Liberar</button>
                                <button class="btn-quick btn-warning" onclick="cambiarRango('apartado')">🟡 Apartar</button>
                                <button class="btn-quick btn-success" onclick="cambiarRango('pagado')">✅ Pagado</button>
                            </div>
                        </div>
                        
                        <!-- Selección Múltiple -->
                        <div class="control-group">
                            <div class="control-title">📋 Selección Múltiple</div>
                            <div class="selected-info" id="selected-info">0 boletos seleccionados</div>
                            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <input type="text" id="nombre_multiple" placeholder="Nombre cliente" style="flex: 1; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                                <input type="tel" id="telefono_multiple" placeholder="Teléfono" style="flex: 1; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                            <div class="quick-buttons">
                                <button class="btn-quick btn-primary" onclick="cambiarMultiple('disponible')">🟢 Liberar</button>
                                <button class="btn-quick btn-warning" onclick="cambiarMultiple('apartado')">🟡 Apartar</button>
                                <button class="btn-quick btn-success" onclick="cambiarMultiple('pagado')">✅ Pagado</button>
                            </div>
                        </div>
                        
                        <!-- Acciones Rápidas -->
                        <div class="control-group">
                            <div class="control-title">⚡ Acciones Rápidas</div>
                            <div style="margin-bottom: 1rem;">
                                <button class="btn btn-secondary" onclick="seleccionarTodos()" style="width: 100%; margin-bottom: 0.5rem;">✅ Seleccionar Todos</button>
                                <button class="btn btn-secondary" onclick="deseleccionarTodos()" style="width: 100%; margin-bottom: 0.5rem;">❌ Deseleccionar</button>
                                <button class="btn btn-primary" onclick="mostrarAyuda()" style="width: 100%;">❓ Ayuda</button>
                            </div>
                            <div style="font-size: 0.9rem; color: #666;">
                                <strong>💡 Tips:</strong><br>
                                • Ctrl+A: Seleccionar todos<br>
                                • Escape: Deseleccionar<br>
                                • Los cambios son permanentes
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3>🎫 Lista de Boletos</h3>
                    <div class="filters-section">
                        <label>Filtro:</label>
                        <select id="filtro" onchange="aplicarFiltro()" style="padding: 0.5rem;">
                            <option value="todos" <?php echo ($filtro == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="disponible" <?php echo ($filtro == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                            <option value="apartado" <?php echo ($filtro == 'apartado') ? 'selected' : ''; ?>>Apartado</option>
                            <option value="pagado" <?php echo ($filtro == 'pagado') ? 'selected' : ''; ?>>Pagado</option>
                        </select>
                        
                        <label>Teléfono:</label>
                        <input type="text" id="telefono_filtro" placeholder="Número de teléfono" value="<?php echo htmlspecialchars($telefono_filtro); ?>" onkeyup="filtrarPorTelefono(event)" style="padding: 0.5rem; border: 2px solid #d4af37; border-radius: 8px;">
                        
                        <?php if (!empty($telefono_filtro)): ?>
                            <button type="button" onclick="limpiarFiltroTelefono()" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Limpiar</button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($boletos)): ?>
                <div style="overflow-x: auto;">
                    <table class="boletos-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all" onchange="toggleSelectAll()"></th>
                                <th>Número</th>
                                <th>Estado</th>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $digitos = strlen((string)$rifa['total_boletos']);
                            foreach ($boletos as $boleto): 
                                $numero_formateado = str_pad($boleto['numero_boleto'], $digitos, '0', STR_PAD_LEFT);
                            ?>
                            <tr class="boleto-<?php echo $boleto['estado']; ?>">
                                <td><input type="checkbox" class="boleto-checkbox" value="<?php echo $boleto['id']; ?>" onchange="actualizarSeleccionados()"></td>
                                <td><strong><?php echo $numero_formateado; ?></strong></td>
                                <td>
                                    <span class="estado-badge estado-<?php echo $boleto['estado']; ?>">
                                        <?php echo ucfirst($boleto['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($boleto['nombre_cliente'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($boleto['telefono_cliente'] ?? '-'); ?></td>
                                <td>
                                    <button class="btn btn-warning" onclick="cambiarEstado(<?php echo $boleto['id']; ?>, 'apartado')">Apartar</button>
                                    <button class="btn btn-success" onclick="cambiarEstado(<?php echo $boleto['id']; ?>, 'pagado')">Pagado</button>
                                    <button class="btn btn-primary" onclick="cambiarEstado(<?php echo $boleto['id']; ?>, 'disponible')">Liberar</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (isset($total_paginas) && $total_paginas > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <?php if ($i == $pagina): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?rifa=<?php echo $rifa_id; ?>&filtro=<?php echo $filtro; ?>&pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
                
                <div style="margin-top: 2rem; padding: 1rem; background: #e8f5e8; border-radius: 8px;">
                    <h4>🚀 Funciones de Marcado Rápido:</h4>
                    <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                        <li><strong>Por Rango:</strong> Cambia del boleto X al Y (ej: del 100 al 200)</li>
                        <li><strong>Selección Múltiple:</strong> Marca checkboxes y cambia todos juntos</li>
                        <li><strong>Individual:</strong> Botones en cada fila para cambios específicos</li>
                        <li><strong>Filtros:</strong> Ve solo disponibles, apartados o pagados</li>
                    </ul>
                </div>
                
                <?php else: ?>
                <div style="text-align: center; padding: 2rem; color: #666;">
                    <h3>No hay boletos para mostrar</h3>
                    <p>Esta rifa no tiene boletos o no coinciden con el filtro seleccionado.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <?php else: ?>
            <div class="card">
                <h3>⚠️ Rifa no encontrada</h3>
                <p>La rifa seleccionada no existe o no es válida.</p>
                <a href="index.php" class="btn btn-primary">← Volver al Panel</a>
            </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>
    
    <script>
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
                alert('Error de conexión. Verifica tu conexión e intenta nuevamente.');
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
                alert('Error de conexión. Verifica tu conexión e intenta nuevamente.');
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
                alert('Error de conexión. Verifica tu conexión e intenta nuevamente.');
            });
        }
        
        function aplicarFiltro() {
            const filtro = document.getElementById('filtro').value;
            const url = new URL(window.location);
            url.searchParams.set('filtro', filtro);
            url.searchParams.set('pagina', '1');
            window.location.href = url.toString();
        }

        function filtrarPorTelefono(event) {
            if (event.key === 'Enter') {
                const telefono = document.getElementById('telefono_filtro').value;
                const url = new URL(window.location);
                if (telefono.trim()) {
                    url.searchParams.set('telefono', telefono);
                } else {
                    url.searchParams.delete('telefono');
                }
                url.searchParams.set('pagina', '1');
                window.location.href = url.toString();
            }
        }

        function limpiarFiltroTelefono() {
            const url = new URL(window.location);
            url.searchParams.delete('telefono');
            url.searchParams.set('pagina', '1');
            window.location.href = url.toString();
        }
        
        function mostrarAyuda() {
            const ayuda = `🚀 GUÍA RÁPIDA DE USO:

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
• Ctrl+A: Seleccionar todos
• Escape: Deseleccionar todos

📞 EJEMPLOS DE USO:
• Cliente compra 50 boletos: "1 al 50" → Apartar
• Cliente paga: Filtrar "apartado" → Seleccionar → Pagado
• Liberar reservas vencidas: Seleccionar → Liberar`;
            
            alert(ayuda);
        }
        
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
        
        // Inicializar cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Sistema de gestión de boletos iniciado');
            actualizarSeleccionados();
            
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
                
                // Ctrl + L para limpiar formularios
                if (e.ctrlKey && e.key === 'l') {
                    e.preventDefault();
                    limpiarFormularios();
                }
            });
            
            console.log('Event listeners configurados correctamente');
        });
        
        // ====== MEJORAS JAVASCRIPT PARA MÓVILES - PANEL ADMIN ======

// Detectar si es móvil
function esMobile() {
    return window.innerWidth <= 768;
}

// Mejorar la función cambiarRango para móviles
function cambiarRango(nuevoEstado) {
    const desde = document.getElementById('desde').value;
    const hasta = document.getElementById('hasta').value;
    const nombre = document.getElementById('nombre_rango').value || '';
    const telefono = document.getElementById('telefono_rango').value || '';
    
    if (!desde || !hasta) {
        mostrarAlertaMovil('Por favor ingresa el rango completo (desde y hasta)', 'error');
        return;
    }
    
    if (parseInt(desde) > parseInt(hasta)) {
        mostrarAlertaMovil('El número inicial debe ser menor al final', 'error');
        return;
    }
    
    const mensaje = esMobile() ? 
        `¿Cambiar ${desde}-${hasta} a ${nuevoEstado}?` :
        `¿Cambiar boletos del ${desde} al ${hasta} a estado "${nuevoEstado}"?`;
    
    if (!confirmarAccion(mensaje)) {
        return;
    }
    
    // Mostrar loading
    mostrarLoading(true);
    
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
        mostrarLoading(false);
        mostrarAlertaMovil(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(error => {
        mostrarLoading(false);
        console.error('Error:', error);
        mostrarAlertaMovil('Error de conexión. Verifica tu conexión e intenta nuevamente.', 'error');
    });
}

// Mejorar la función cambiarMultiple para móviles
function cambiarMultiple(nuevoEstado) {
    if (boletosSeleccionados.length === 0) {
        mostrarAlertaMovil('Selecciona al menos un boleto usando los checkboxes', 'warning');
        return;
    }
    
    const nombre = document.getElementById('nombre_multiple').value || '';
    const telefono = document.getElementById('telefono_multiple').value || '';
    
    const mensaje = esMobile() ? 
        `¿Cambiar ${boletosSeleccionados.length} boletos a ${nuevoEstado}?` :
        `¿Cambiar ${boletosSeleccionados.length} boletos seleccionados a estado "${nuevoEstado}"?`;
    
    if (!confirmarAccion(mensaje)) {
        return;
    }
    
    mostrarLoading(true);
    
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
        mostrarLoading(false);
        mostrarAlertaMovil(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(error => {
        mostrarLoading(false);
        console.error('Error:', error);
        mostrarAlertaMovil('Error de conexión. Verifica tu conexión e intenta nuevamente.', 'error');
    });
}

// Mejorar la función cambiarEstado para móviles
function cambiarEstado(boletoId, nuevoEstado) {
    let nombre = '';
    let telefono = '';
    
    if (nuevoEstado === 'apartado' || nuevoEstado === 'pagado') {
        if (esMobile()) {
            // En móviles, usar un modal más amigable
            mostrarModalDatos(boletoId, nuevoEstado);
            return;
        } else {
            nombre = prompt('Nombre del cliente (opcional):') || '';
            telefono = prompt('Teléfono del cliente (opcional):') || '';
        }
    }
    
    ejecutarCambioEstado(boletoId, nuevoEstado, nombre, telefono);
}

// Función para ejecutar cambio de estado
function ejecutarCambioEstado(boletoId, nuevoEstado, nombre = '', telefono = '') {
    mostrarLoading(true);
    
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
        mostrarLoading(false);
        if (data.success) {
            mostrarAlertaMovil('Estado actualizado correctamente', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarAlertaMovil('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        mostrarLoading(false);
        console.error('Error:', error);
        mostrarAlertaMovil('Error de conexión. Verifica tu conexión e intenta nuevamente.', 'error');
    });
}

// Modal para datos del cliente en móviles
function mostrarModalDatos(boletoId, nuevoEstado) {
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
    `;
    
    const content = document.createElement('div');
    content.style.cssText = `
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    `;
    
    const estadoTexto = {
        'apartado': 'Apartar Boleto',
        'pagado': 'Marcar como Pagado'
    };
    
    content.innerHTML = `
        <h3 style="margin: 0 0 1rem 0; color: #333; text-align: center;">
            ${estadoTexto[nuevoEstado]}
        </h3>
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #555;">
                Nombre del cliente:
            </label>
            <input type="text" id="modalNombre" placeholder="Nombre completo" 
                   style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 6px; font-size: 16px;">
        </div>
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #555;">
                Teléfono del cliente:
            </label>
            <input type="tel" id="modalTelefono" placeholder="Número de teléfono" 
                   style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 6px; font-size: 16px;">
        </div>
        <div style="display: flex; gap: 0.8rem;">
            <button id="modalCancelar" style="flex: 1; padding: 0.8rem; border: 1px solid #ddd; background: #f5f5f5; 
                                              color: #333; border-radius: 6px; font-size: 0.9rem; cursor: pointer;">
                Cancelar
            </button>
            <button id="modalConfirmar" style="flex: 1; padding: 0.8rem; border: none; background: #d4af37; 
                                               color: white; border-radius: 6px; font-size: 0.9rem; cursor: pointer;">
                Confirmar
            </button>
        </div>
    `;
    
    modal.appendChild(content);
    document.body.appendChild(modal);
    
    // Prevenir scroll del body
    document.body.style.overflow = 'hidden';
    
    // Focus en primer input
    setTimeout(() => {
        document.getElementById('modalNombre').focus();
    }, 100);
    
    // Event listeners
    document.getElementById('modalCancelar').onclick = () => {
        document.body.removeChild(modal);
        document.body.style.overflow = '';
    };
    
    document.getElementById('modalConfirmar').onclick = () => {
        const nombre = document.getElementById('modalNombre').value.trim();
        const telefono = document.getElementById('modalTelefono').value.trim();
        
        document.body.removeChild(modal);
        document.body.style.overflow = '';
        
        ejecutarCambioEstado(boletoId, nuevoEstado, nombre, telefono);
    };
    
    // Cerrar al tocar fuera
    modal.onclick = (e) => {
        if (e.target === modal) {
            document.body.removeChild(modal);
            document.body.style.overflow = '';
        }
    };
}

// Función para mostrar alertas optimizadas para móviles
function mostrarAlertaMovil(mensaje, tipo = 'info') {
    const colores = {
        error: '#dc3545',
        success: '#28a745',
        warning: '#ffc107',
        info: '#17a2b8'
    };
    
    const iconos = {
        error: '❌',
        success: '✅',
        warning: '⚠️',
        info: 'ℹ️'
    };
    
    // Remover alertas anteriores
    const alertasAnteriores = document.querySelectorAll('.alerta-movil');
    alertasAnteriores.forEach(alerta => alerta.remove());
    
    const alerta = document.createElement('div');
    alerta.className = 'alerta-movil';
    
    if (esMobile()) {
        alerta.style.cssText = `
            position: fixed;
            top: 20px;
            left: 10px;
            right: 10px;
            background: ${colores[tipo]};
            color: white;
            padding: 1rem;
            border-radius: 8px;
            z-index: 10001;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            font-weight: 600;
            animation: slideDownMobile 0.4s ease-out;
            text-align: center;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        `;
    } else {
        alerta.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${colores[tipo]};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            z-index: 10001;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            font-weight: 600;
            animation: slideInRight 0.4s ease-out;
            max-width: 350px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        `;
    }
    
    alerta.innerHTML = `${iconos[tipo]} ${mensaje}`;
    document.body.appendChild(alerta);
    
    const timeout = esMobile() ? 3000 : 4000;
    setTimeout(() => {
        alerta.style.animation = esMobile() ? 'slideUpMobile 0.4s ease-out' : 'slideOutRight 0.4s ease-out';
        setTimeout(() => {
            if (alerta.parentNode) {
                alerta.remove();
            }
        }, 400);
    }, timeout);
}

// Función para confirmar acciones (optimizada para móviles)
function confirmarAccion(mensaje) {
    if (esMobile()) {
        return mostrarConfirmacionMovil(mensaje);
    } else {
        return confirm(mensaje);
    }
}

// Modal de confirmación para móviles
function mostrarConfirmacionMovil(mensaje) {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        `;
        
        const content = document.createElement('div');
        content.style.cssText = `
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            width: 100%;
            max-width: 350px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
        `;
        
        content.innerHTML = `
            <div style="margin-bottom: 1.5rem; font-size: 1.1rem; color: #333; line-height: 1.4;">
                ${mensaje}
            </div>
            <div style="display: flex; gap: 0.8rem;">
                <button id="confirmarNo" style="flex: 1; padding: 0.8rem; border: 1px solid #ddd; background: #f5f5f5; 
                                                  color: #333; border-radius: 6px; font-size: 0.9rem; cursor: pointer;">
                    Cancelar
                </button>
                <button id="confirmarSi" style="flex: 1; padding: 0.8rem; border: none; background: #d4af37; 
                                                 color: white; border-radius: 6px; font-size: 0.9rem; cursor: pointer;">
                    Confirmar
                </button>
            </div>
        `;
        
        modal.appendChild(content);
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
        
        document.getElementById('confirmarNo').onclick = () => {
            document.body.removeChild(modal);
            document.body.style.overflow = '';
            resolve(false);
        };
        
        document.getElementById('confirmarSi').onclick = () => {
            document.body.removeChild(modal);
            document.body.style.overflow = '';
            resolve(true);
        };
        
        modal.onclick = (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
                document.body.style.overflow = '';
                resolve(false);
            }
        };
    });
}

// Función para mostrar loading
function mostrarLoading(mostrar) {
    let overlay = document.getElementById('loadingOverlay');
    
    if (mostrar) {
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loadingOverlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
                backdrop-filter: blur(5px);
            `;
            
            const spinner = document.createElement('div');
            spinner.style.cssText = `
                width: 50px;
                height: 50px;
                border: 4px solid rgba(212, 175, 55, 0.3);
                border-top: 4px solid #d4af37;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            `;
            
            overlay.appendChild(spinner);
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    } else {
        if (overlay) {
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
}

// Función para optimizar la ayuda en móviles
function mostrarAyuda() {
    const ayudaTexto = esMobile() ? `
🚀 GUÍA RÁPIDA:

📝 CAMBIO POR RANGO:
• Pon números: del 1 al 50
• Agrega datos del cliente
• Toca Liberar/Apartar/Pagado

📋 SELECCIÓN MÚLTIPLE:
• Marca checkboxes
• Agrega datos del cliente
• Usa botones para cambiar

⚡ ACCIONES RÁPIDAS:
• Seleccionar Todos
• Deseleccionar
• Esta ayuda

🔍 FILTROS:
• Ve solo ciertos estados
• Navega entre páginas

💡 TIPS:
• Para ventas masivas: "Rango"
• Para específicos: "Múltiple"
• Los cambios son permanentes

📞 EJEMPLOS:
• Cliente compra 50: "1 al 50" → Apartar
• Cliente paga: Filtrar → Pagado
• Liberar reservas: Seleccionar → Liberar
    ` : `🚀 GUÍA RÁPIDA DE USO:

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
• Ctrl+A: Seleccionar todos
• Escape: Deseleccionar todos

📞 EJEMPLOS DE USO:
• Cliente compra 50 boletos: "1 al 50" → Apartar
• Cliente paga: Filtrar "apartado" → Seleccionar → Pagado
• Liberar reservas vencidas: Seleccionar → Liberar`;
    
    if (esMobile()) {
        mostrarModalAyuda(ayudaTexto);
    } else {
        alert(ayudaTexto);
    }
}

// Modal de ayuda para móviles
function mostrarModalAyuda(texto) {
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
    `;
    
    const content = document.createElement('div');
    content.style.cssText = `
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        width: 100%;
        max-width: 400px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        -webkit-overflow-scrolling: touch;
    `;
    
    content.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #d4af37;">
            <h3 style="margin: 0; color: #333;">📖 Guía de Uso</h3>
            <button id="cerrarAyuda" style="background: none; border: none; font-size: 1.5rem; color: #666; cursor: pointer; padding: 0.2rem;">✕</button>
        </div>
        <div style="white-space: pre-line; font-size: 0.9rem; line-height: 1.5; color: #333;">
            ${texto}
        </div>
        <div style="margin-top: 1.5rem; text-align: center;">
            <button id="cerrarAyudaBtn" style="padding: 0.8rem 2rem; background: #d4af37; color: white; border: none; border-radius: 6px; font-size: 0.9rem; cursor: pointer;">
                Entendido
            </button>
        </div>
    `;
    
    modal.appendChild(content);
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    const cerrarModal = () => {
        document.body.removeChild(modal);
        document.body.style.overflow = '';
    };
    
    document.getElementById('cerrarAyuda').onclick = cerrarModal;
    document.getElementById('cerrarAyudaBtn').onclick = cerrarModal;
    
    modal.onclick = (e) => {
        if (e.target === modal) {
            cerrarModal();
        }
    };
}

// Función para optimizar scroll en tabla móvil
function optimizarScrollTabla() {
    if (esMobile()) {
        const tabla = document.querySelector('.boletos-table');
        if (tabla) {
            // Mejorar scroll horizontal en móviles
            tabla.parentElement.style.overflowX = 'auto';
            tabla.parentElement.style.webkitOverflowScrolling = 'touch';
            
            // Indicador de scroll
            const container = tabla.parentElement;
            let scrollIndicador = null;
            
            container.addEventListener('scroll', () => {
                if (!scrollIndicador) {
                    scrollIndicador = document.createElement('div');
                    scrollIndicador.style.cssText = `
                        position: fixed;
                        bottom: 20px;
                        right: 20px;
                        background: rgba(212, 175, 55, 0.9);
                        color: white;
                        padding: 0.5rem;
                        border-radius: 6px;
                        font-size: 0.8rem;
                        z-index: 1000;
                        pointer-events: none;
                    `;
                    scrollIndicador.textContent = '← Desliza para ver más →';
                    document.body.appendChild(scrollIndicador);
                }
                
                clearTimeout(scrollIndicador.timeout);
                scrollIndicador.style.display = 'block';
                
                scrollIndicador.timeout = setTimeout(() => {
                    if (scrollIndicador) {
                        scrollIndicador.style.display = 'none';
                    }
                }, 2000);
            });
        }
    }
}

// Función para manejar orientación
function manejarOrientacion() {
    function ajustarPorOrientacion() {
        setTimeout(() => {
            const statsGrid = document.querySelector('.stats-grid');
            const controlsGrid = document.querySelector('.controls-grid');
            
            if (window.innerHeight < window.innerWidth && esMobile()) {
                // Landscape
                if (statsGrid) {
                    statsGrid.style.gridTemplateColumns = 'repeat(4, 1fr)';
                }
                if (controlsGrid) {
                    controlsGrid.style.gridTemplateColumns = 'repeat(2, 1fr)';
                }
            } else if (esMobile()) {
                // Portrait
                if (statsGrid) {
                    statsGrid.style.gridTemplateColumns = 'repeat(2, 1fr)';
                }
                if (controlsGrid) {
                    controlsGrid.style.gridTemplateColumns = '1fr';
                }
            }
        }, 100);
    }
    
    window.addEventListener('orientationchange', ajustarPorOrientacion);
    window.addEventListener('resize', ajustarPorOrientacion);
    ajustarPorOrientacion();
}

// Función para optimizar inputs en móviles
function optimizarInputsMovil() {
    if (esMobile()) {
        const inputs = document.querySelectorAll('input[type="number"]');
        inputs.forEach(input => {
            // Agregar teclado numérico en móviles
            input.setAttribute('inputmode', 'numeric');
            input.setAttribute('pattern', '[0-9]*');
            
            // Mejorar UX en móviles
            input.addEventListener('focus', () => {
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
        
        const telInputs = document.querySelectorAll('input[type="tel"]');
        telInputs.forEach(input => {
            input.setAttribute('inputmode', 'tel');
        });
    }
}

// Mejorar la función de seleccionar todos para móviles
function seleccionarTodos() {
    const checkboxes = document.querySelectorAll('.boleto-checkbox');
    checkboxes.forEach(cb => cb.checked = true);
    const selectAll = document.getElementById('select-all');
    if (selectAll) selectAll.checked = true;
    actualizarSeleccionados();
    
    // Feedback visual en móviles
    if (esMobile()) {
        mostrarAlertaMovil(`${checkboxes.length} boletos seleccionados`, 'info');
        
        // Vibración si está disponible
        if (navigator.vibrate) {
            navigator.vibrate(50);
        }
    }
}

// Mejorar la función de deseleccionar para móviles
function deseleccionarTodos() {
    const checkboxes = document.querySelectorAll('.boleto-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    const selectAll = document.getElementById('select-all');
    if (selectAll) selectAll.checked = false;
    actualizarSeleccionados();
    
    if (esMobile()) {
        mostrarAlertaMovil('Selección eliminada', 'info');
    }
}

// Agregar CSS para animaciones
function agregarAnimacionesCSS() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideDownMobile {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideUpMobile {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-50px);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
}

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un poco para que todo esté cargado
    setTimeout(() => {
        agregarAnimacionesCSS();
        optimizarScrollTabla();
        manejarOrientacion();
        optimizarInputsMovil();
        
        // Agregar clase para móviles
        if (esMobile()) {
            document.body.classList.add('mobile-device');
            console.log('📱 Optimizaciones móviles del panel admin activadas');
        }
        
        // Event listeners para teclas rápidas (solo en desktop)
        if (!esMobile()) {
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'a') {
                    e.preventDefault();
                    seleccionarTodos();
                }
                
                if (e.key === 'Escape') {
                    deseleccionarTodos();
                }
                
                if (e.ctrlKey && e.key === 'l') {
                    e.preventDefault();
                    limpiarFormularios();
                }
            });
        }
        
        // Actualizar contador inicial
        actualizarSeleccionados();
        
    }, 300);
});
// ====== SISTEMA DE AUTOCOMPLETADO DE DATOS DE CLIENTE ======

// Base de datos de clientes en memoria (se actualiza dinámicamente)
let clientesDatabase = new Map();
let clienteSeleccionado = null;

// Inicializar sistema al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    inicializarSistemaClientes();
    configurarEventListeners();
});

// Función principal de inicialización
function inicializarSistemaClientes() {
    // Extraer datos de clientes existentes de la tabla
    extraerClientesDeTabla();
    
    // Configurar autocompletado en todos los inputs de clientes
    configurarAutocompletado();
    
    // Agregar botones de autocompletado
    agregarBotonesAutocompletado();
    
    console.log('📋 Sistema de autocompletado de clientes iniciado');
    console.log(`👥 ${clientesDatabase.size} clientes en base de datos`);
}

// Extraer clientes existentes de la tabla de boletos
function extraerClientesDeTabla() {
    const filas = document.querySelectorAll('.boletos-table tbody tr');
    
    filas.forEach(fila => {
        const nombreCell = fila.querySelector('td:nth-child(4)');
        const telefonoCell = fila.querySelector('td:nth-child(5)');
        
        if (nombreCell && telefonoCell) {
            const nombre = nombreCell.textContent.trim();
            const telefono = telefonoCell.textContent.trim();
            
            if (nombre !== '-' && telefono !== '-' && nombre !== '' && telefono !== '') {
                const clienteKey = nombre.toLowerCase();
                clientesDatabase.set(clienteKey, {
                    nombre: nombre,
                    telefono: telefono,
                    ultimaCompra: new Date(),
                    totalBoletos: (clientesDatabase.get(clienteKey)?.totalBoletos || 0) + 1
                });
            }
        }
    });
}

// Configurar autocompletado en inputs
function configurarAutocompletado() {
    const inputsNombre = document.querySelectorAll('#nombre_rango, #nombre_multiple');
    const inputsTelefono = document.querySelectorAll('#telefono_rango, #telefono_multiple');
    
    inputsNombre.forEach(input => {
        configurarInputNombre(input);
    });
    
    inputsTelefono.forEach(input => {
        configurarInputTelefono(input);
    });
}

// Configurar input de nombre con autocompletado
function configurarInputNombre(input) {
    let timeoutId;
    let dropdownVisible = false;
    
    // Crear dropdown para sugerencias
    const dropdown = crearDropdownSugerencias(input, 'nombre');
    
    input.addEventListener('input', function() {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            const query = this.value.toLowerCase().trim();
            
            if (query.length >= 2) {
                const sugerencias = buscarClientesPorNombre(query);
                mostrarSugerenciasNombre(dropdown, sugerencias, input);
                dropdownVisible = sugerencias.length > 0;
            } else {
                ocultarDropdown(dropdown);
                dropdownVisible = false;
            }
        }, 300);
    });
    
    input.addEventListener('blur', function() {
        // Delay para permitir click en sugerencias
        setTimeout(() => {
            if (dropdownVisible) {
                ocultarDropdown(dropdown);
                dropdownVisible = false;
            }
        }, 150);
    });
    
    input.addEventListener('keydown', function(e) {
        if (dropdownVisible) {
            navegarSugerencias(e, dropdown);
        }
    });
}

// Configurar input de teléfono con autocompletado
function configurarInputTelefono(input) {
    let timeoutId;
    let dropdownVisible = false;
    
    const dropdown = crearDropdownSugerencias(input, 'telefono');
    
    input.addEventListener('input', function() {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            const query = this.value.trim();
            
            if (query.length >= 3) {
                const sugerencias = buscarClientesPorTelefono(query);
                mostrarSugerenciasTelefono(dropdown, sugerencias, input);
                dropdownVisible = sugerencias.length > 0;
            } else {
                ocultarDropdown(dropdown);
                dropdownVisible = false;
            }
        }, 300);
    });
    
    input.addEventListener('blur', function() {
        setTimeout(() => {
            if (dropdownVisible) {
                ocultarDropdown(dropdown);
                dropdownVisible = false;
            }
        }, 150);
    });
}

// Crear dropdown de sugerencias
function crearDropdownSugerencias(input, tipo) {
    const dropdown = document.createElement('div');
    dropdown.className = `sugerencias-dropdown sugerencias-${tipo}`;
    dropdown.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #d4af37;
        border-top: none;
        border-radius: 0 0 8px 8px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        display: none;
    `;
    
    // Hacer el contenedor del input relativo
    const container = input.parentElement;
    if (getComputedStyle(container).position === 'static') {
        container.style.position = 'relative';
    }
    
    container.appendChild(dropdown);
    return dropdown;
}

// Buscar clientes por nombre
function buscarClientesPorNombre(query) {
    const resultados = [];
    
    clientesDatabase.forEach((cliente, key) => {
        if (key.includes(query)) {
            resultados.push(cliente);
        }
    });
    
    // Ordenar por relevancia y frecuencia
    return resultados.sort((a, b) => {
        const scoreA = calcularScoreRelevancia(a.nombre.toLowerCase(), query) + (a.totalBoletos * 0.1);
        const scoreB = calcularScoreRelevancia(b.nombre.toLowerCase(), query) + (b.totalBoletos * 0.1);
        return scoreB - scoreA;
    }).slice(0, 5);
}

// Buscar clientes por teléfono
function buscarClientesPorTelefono(query) {
    const resultados = [];
    
    clientesDatabase.forEach((cliente) => {
        if (cliente.telefono.includes(query)) {
            resultados.push(cliente);
        }
    });
    
    return resultados.sort((a, b) => b.totalBoletos - a.totalBoletos).slice(0, 5);
}

// Calcular score de relevancia
function calcularScoreRelevancia(texto, query) {
    if (texto.startsWith(query)) return 10;
    if (texto.includes(' ' + query)) return 7;
    if (texto.includes(query)) return 5;
    return 0;
}

// Mostrar sugerencias de nombre
function mostrarSugerenciasNombre(dropdown, sugerencias, input) {
    if (sugerencias.length === 0) {
        ocultarDropdown(dropdown);
        return;
    }
    
    dropdown.innerHTML = '';
    
    sugerencias.forEach((cliente, index) => {
        const item = document.createElement('div');
        item.className = 'sugerencia-item';
        item.style.cssText = `
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        `;
        
        const infoCliente = document.createElement('div');
        infoCliente.innerHTML = `
            <div style="font-weight: 600; color: #1b5e20;">${cliente.nombre}</div>
            <div style="font-size: 0.85rem; color: #666;">${cliente.telefono}</div>
        `;
        
        const badge = document.createElement('div');
        badge.style.cssText = `
            background: linear-gradient(135deg, #d4af37, #b8941f);
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: bold;
        `;
        badge.textContent = `${cliente.totalBoletos} boletos`;
        
        item.appendChild(infoCliente);
        item.appendChild(badge);
        
        item.addEventListener('mouseenter', function() {
            this.style.background = 'linear-gradient(135deg, #f8f9fa, #e9ecef)';
            this.style.borderLeft = '4px solid #d4af37';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.background = 'white';
            this.style.borderLeft = 'none';
        });
        
        item.addEventListener('click', function() {
            seleccionarCliente(cliente, input);
            ocultarDropdown(dropdown);
        });
        
        dropdown.appendChild(item);
    });
    
    dropdown.style.display = 'block';
}

// Mostrar sugerencias de teléfono
function mostrarSugerenciasTelefono(dropdown, sugerencias, input) {
    if (sugerencias.length === 0) {
        ocultarDropdown(dropdown);
        return;
    }
    
    dropdown.innerHTML = '';
    
    sugerencias.forEach((cliente) => {
        const item = document.createElement('div');
        item.className = 'sugerencia-item';
        item.style.cssText = `
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        `;
        
        const infoCliente = document.createElement('div');
        infoCliente.innerHTML = `
            <div style="font-weight: 600; color: #1b5e20;">${cliente.nombre}</div>
            <div style="font-size: 0.85rem; color: #666;">${cliente.telefono}</div>
        `;
        
        const badge = document.createElement('div');
        badge.style.cssText = `
            background: linear-gradient(135deg, #4caf50, #388e3c);
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: bold;
        `;
        badge.textContent = `${cliente.totalBoletos} boletos`;
        
        item.appendChild(infoCliente);
        item.appendChild(badge);
        
        item.addEventListener('mouseenter', function() {
            this.style.background = 'linear-gradient(135deg, #f8f9fa, #e9ecef)';
            this.style.borderLeft = '4px solid #4caf50';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.background = 'white';
            this.style.borderLeft = 'none';
        });
        
        item.addEventListener('click', function() {
            seleccionarCliente(cliente, input);
            ocultarDropdown(dropdown);
        });
        
        dropdown.appendChild(item);
    });
    
    dropdown.style.display = 'block';
}

// Seleccionar cliente y autocompletar campos
function seleccionarCliente(cliente, inputOrigen) {
    clienteSeleccionado = cliente;
    
    // Determinar si viene de rango o múltiple
    const esRango = inputOrigen.id.includes('rango');
    const prefijo = esRango ? 'rango' : 'multiple';
    
    // Autocompletar ambos campos
    const inputNombre = document.getElementById(`nombre_${prefijo}`);
    const inputTelefono = document.getElementById(`telefono_${prefijo}`);
    
    if (inputNombre) {
        inputNombre.value = cliente.nombre;
        inputNombre.style.background = 'linear-gradient(135deg, #e8f5e8, #c8e6c9)';
        inputNombre.style.borderColor = '#4caf50';
    }
    
    if (inputTelefono) {
        inputTelefono.value = cliente.telefono;
        inputTelefono.style.background = 'linear-gradient(135deg, #e8f5e8, #c8e6c9)';
        inputTelefono.style.borderColor = '#4caf50';
    }
    
    // Mostrar notificación de cliente seleccionado
    mostrarNotificacionCliente(cliente);
    
    // Vibración en móviles si está disponible
    if (navigator.vibrate) {
        navigator.vibrate(50);
    }
    
    console.log('👤 Cliente seleccionado:', cliente.nombre);
}

// Mostrar notificación de cliente seleccionado
function mostrarNotificacionCliente(cliente) {
    const notificacion = document.createElement('div');
    notificacion.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: linear-gradient(135deg, #4caf50, #388e3c);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(76, 175, 80, 0.3);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
        animation: slideInRight 0.4s ease-out;
        max-width: 300px;
    `;
    
    notificacion.innerHTML = `
        <div style="font-size: 1.2rem;">✅</div>
        <div>
            <div style="font-size: 0.9rem;">Cliente seleccionado</div>
            <div style="font-size: 0.8rem; opacity: 0.9;">${cliente.nombre} - ${cliente.totalBoletos} boletos</div>
        </div>
    `;
    
    document.body.appendChild(notificacion);
    
    setTimeout(() => {
        notificacion.style.animation = 'slideOutRight 0.4s ease-out';
        setTimeout(() => {
            if (notificacion.parentNode) {
                notificacion.remove();
            }
        }, 400);
    }, 3000);
}

// Agregar botones de autocompletado rápido
function agregarBotonesAutocompletado() {
    const controlGroups = document.querySelectorAll('.control-group');
    
    controlGroups.forEach((group, index) => {
        if (index < 2) { // Solo en rango y múltiple
            const prefijo = index === 0 ? 'rango' : 'multiple';
            const titulo = index === 0 ? 'Rango' : 'Múltiple';
            
            // Crear botón de clientes frecuentes
            const botonClientesFrecuentes = document.createElement('button');
            botonClientesFrecuentes.type = 'button';
            botonClientesFrecuentes.className = 'btn-quick btn-secondary';
            botonClientesFrecuentes.style.cssText = `
                margin-top: 0.5rem;
                font-size: 0.8rem;
                padding: 0.6rem;
                background: linear-gradient(135deg, #17a2b8, #138496);
                width: 100%;
            `;
            botonClientesFrecuentes.innerHTML = `👥 Clientes Frecuentes`;
            
            botonClientesFrecuentes.addEventListener('click', function() {
                mostrarModalClientesFrecuentes(prefijo);
            });
            
            // Crear botón de limpiar
            const botonLimpiar = document.createElement('button');
            botonLimpiar.type = 'button';
            botonLimpiar.className = 'btn-quick';
            botonLimpiar.style.cssText = `
                margin-top: 0.5rem;
                font-size: 0.8rem;
                padding: 0.6rem;
                background: linear-gradient(135deg, #6c757d, #545b62);
                color: white;
                width: 100%;
            `;
            botonLimpiar.innerHTML = `🧹 Limpiar Datos`;
            
            botonLimpiar.addEventListener('click', function() {
                limpiarDatosCliente(prefijo);
            });
            
            // Insertar botones después de los quick-buttons existentes
            const quickButtons = group.querySelector('.quick-buttons');
            if (quickButtons) {
                quickButtons.parentNode.insertBefore(botonClientesFrecuentes, quickButtons.nextSibling);
                quickButtons.parentNode.insertBefore(botonLimpiar, botonClientesFrecuentes.nextSibling);
            }
        }
    });
}

// Mostrar modal de clientes frecuentes
function mostrarModalClientesFrecuentes(prefijo) {
    const clientesFrecuentes = Array.from(clientesDatabase.values())
        .sort((a, b) => b.totalBoletos - a.totalBoletos)
        .slice(0, 10);
    
    if (clientesFrecuentes.length === 0) {
        if (esMobile && esMobile()) {
            mostrarAlertaMovil('No hay clientes registrados aún', 'info');
        } else {
            alert('No hay clientes registrados aún');
        }
        return;
    }
    
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        backdrop-filter: blur(5px);
    `;
    
    const content = document.createElement('div');
    content.className = 'modal-content';
    content.style.cssText = `
        background: white;
        border-radius: 16px;
        padding: 2rem;
        width: 100%;
        max-width: 500px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        position: relative;
    `;
    
    content.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #d4af37;">
            <h3 style="margin: 0; color: #1b5e20; font-size: 1.3rem;">👥 Clientes Frecuentes</h3>
            <button id="cerrarModal" style="background: none; border: none; font-size: 1.5rem; color: #666; cursor: pointer; padding: 0.2rem;">✕</button>
        </div>
        <div id="listaClientes" style="max-height: 400px; overflow-y: auto;"></div>
        <div style="margin-top: 1.5rem; text-align: center;">
            <button id="cerrarModalBtn" style="padding: 0.8rem 2rem; background: #d4af37; color: white; border: none; border-radius: 8px; font-size: 0.9rem; cursor: pointer;">
                Cerrar
            </button>
        </div>
    `;
    
    modal.appendChild(content);
    
    // Llenar lista de clientes
    const listaClientes = content.querySelector('#listaClientes');
    clientesFrecuentes.forEach((cliente, index) => {
        const clienteItem = document.createElement('div');
        clienteItem.style.cssText = `
            padding: 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        `;
        
        clienteItem.innerHTML = `
            <div>
                <div style="font-weight: 600; color: #1b5e20; margin-bottom: 0.25rem;">${cliente.nombre}</div>
                <div style="font-size: 0.85rem; color: #666;">${cliente.telefono}</div>
            </div>
            <div style="text-align: center;">
                <div style="background: linear-gradient(135deg, #d4af37, #b8941f); color: white; padding: 0.3rem 0.6rem; border-radius: 15px; font-size: 0.8rem; font-weight: bold; margin-bottom: 0.25rem;">
                    ${cliente.totalBoletos} boletos
                </div>
                <div style="font-size: 0.7rem; color: #999;">
                    #${index + 1} frecuente
                </div>
            </div>
        `;
        
        clienteItem.addEventListener('mouseenter', function() {
            this.style.background = 'linear-gradient(135deg, #f8f9fa, #e9ecef)';
            this.style.borderColor = '#d4af37';
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
        });
        
        clienteItem.addEventListener('mouseleave', function() {
            this.style.background = 'white';
            this.style.borderColor = '#e0e0e0';
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
        
        clienteItem.addEventListener('click', function() {
            // Simular input para trigger autocompletado
            const inputNombre = document.getElementById(`nombre_${prefijo}`);
            seleccionarCliente(cliente, inputNombre);
            cerrarModal();
        });
        
        listaClientes.appendChild(clienteItem);
    });
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    const cerrarModal = () => {
        document.body.removeChild(modal);
        document.body.style.overflow = '';
    };
    
    content.querySelector('#cerrarModal').onclick = cerrarModal;
    content.querySelector('#cerrarModalBtn').onclick = cerrarModal;
    
    modal.onclick = (e) => {
        if (e.target === modal) {
            cerrarModal();
        }
    };
}

// Limpiar datos de cliente
function limpiarDatosCliente(prefijo) {
    const inputNombre = document.getElementById(`nombre_${prefijo}`);
    const inputTelefono = document.getElementById(`telefono_${prefijo}`);
    
    if (inputNombre) {
        inputNombre.value = '';
        inputNombre.style.background = '';
        inputNombre.style.borderColor = '';
    }
    
    if (inputTelefono) {
        inputTelefono.value = '';
        inputTelefono.style.background = '';
        inputTelefono.style.borderColor = '';
    }
    
    clienteSeleccionado = null;
    
    if (typeof mostrarAlertaMovil !== 'undefined' && esMobile && esMobile()) {
        mostrarAlertaMovil('Datos limpiados', 'info');
    }
}

// Ocultar dropdown
function ocultarDropdown(dropdown) {
    dropdown.style.display = 'none';
}

// Navegar sugerencias con teclado
function navegarSugerencias(e, dropdown) {
    const items = dropdown.querySelectorAll('.sugerencia-item');
    let selectedIndex = -1;
    
    // Encontrar item seleccionado actual
    items.forEach((item, index) => {
        if (item.classList.contains('selected')) {
            selectedIndex = index;
        }
    });
    
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex = Math.max(selectedIndex - 1, -1);
    } else if (e.key === 'Enter' && selectedIndex >= 0) {
        e.preventDefault();
        items[selectedIndex].click();
        return;
    } else if (e.key === 'Escape') {
        e.preventDefault();
        ocultarDropdown(dropdown);
        return;
    }
    
    // Actualizar selección visual
    items.forEach((item, index) => {
        if (index === selectedIndex) {
            item.classList.add('selected');
            item.style.background = 'linear-gradient(135deg, #d4af37, #b8941f)';
            item.style.color = 'white';
        } else {
            item.classList.remove('selected');
            item.style.background = 'white';
            item.style.color = '';
        }
    });
}

// Configurar event listeners adicionales
function configurarEventListeners() {
    // Interceptar cambios exitosos para actualizar base de datos
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        return originalFetch.apply(this, args)
            .then(response => {
                if (response.ok && args[0] === window.location.href) {
                    // Actualizar base de datos después de cambios exitosos
                    setTimeout(() => {
                        extraerClientesDeTabla();
                    }, 1000);
                }
                return response;
            });
    };
    
    // Limpiar formularios cuando se resetean
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'l') {
            e.preventDefault();
            limpiarDatosCliente('rango');
            limpiarDatosCliente('multiple');
        }
    });
}

// Función para exportar/importar base de datos de clientes
function exportarClientesDatabase() {
    const data = JSON.stringify(Array.from(clientesDatabase.entries()));
    console.log('📊 Base de datos de clientes:', data);
    return data;
}

function importarClientesDatabase(data) {
    try {
        const entries = JSON.parse(data);
        clientesDatabase = new Map(entries);
        console.log('📥 Base de datos importada:', clientesDatabase.size, 'clientes');
    } catch (e) {
        console.error('❌ Error importando base de datos:', e);
    }
}

// Función de estadísticas de clientes
function mostrarEstadisticasClientes() {
    const stats = {
        totalClientes: clientesDatabase.size,
        clienteMasFrecuente: null,
        promedioBoletosCliente: 0,
        totalBoletos: 0
    };
    
    let maxBoletos = 0;
    clientesDatabase.forEach((cliente) => {
        stats.totalBoletos += cliente.totalBoletos;
        if (cliente.totalBoletos > maxBoletos) {
            maxBoletos = cliente.totalBoletos;
            stats.clienteMasFrecuente = cliente;
        }
    });
    
    stats.promedioBoletosCliente = stats.totalClientes > 0 ? 
        (stats.totalBoletos / stats.totalClientes).toFixed(1) : 0;
    
    console.log('📈 Estadísticas de clientes:', stats);
    return stats;
}

// CSS adicional para sugerencias
const styleSheet = document.createElement('style');
styleSheet.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
    
    .sugerencias-dropdown {
        -webkit-overflow-scrolling: touch;
    }
    
    .sugerencias-dropdown::-webkit-scrollbar {
        width: 6px;
    }
    
    .sugerencias-dropdown::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .sugerencias-dropdown::-webkit-scrollbar-thumb {
        background: #d4af37;
        border-radius: 3px;
    }
    
    .sugerencia-item:last-child {
        border-bottom: none;
    }
    
    @media (max-width: 768px) {
        .sugerencias-dropdown {
            font-size: 0.85rem;
            max-height: 150px;
        }
        
        .sugerencia-item {
            padding: 0.6rem 0.8rem !important;
        }
        
        .modal-content {
            padding: 1.5rem !important;
            margin: 0.5rem !important;
        }
    }
`;
document.head.appendChild(styleSheet);

console.log('🚀 Sistema de autocompletado de clientes cargado completamente');

// ====== FUNCIONES ADICIONALES Y MEJORAS ======

// Función para detectar clientes duplicados por teléfono
function detectarClientesDuplicados() {
    const telefonosVistos = new Set();
    const duplicados = [];
    
    clientesDatabase.forEach((cliente, key) => {
        if (telefonosVistos.has(cliente.telefono)) {
            duplicados.push(cliente);
        } else {
            telefonosVistos.add(cliente.telefono);
        }
    });
    
    if (duplicados.length > 0) {
        console.warn('⚠️ Clientes duplicados detectados:', duplicados);
        mostrarAlertaDuplicados(duplicados);
    }
    
    return duplicados;
}

// Mostrar alerta de clientes duplicados
function mostrarAlertaDuplicados(duplicados) {
    const mensaje = `Se detectaron ${duplicados.length} posibles clientes duplicados. ¿Deseas revisar?`;
    
    if (confirm(mensaje)) {
        mostrarModalDuplicados(duplicados);
    }
}

// Modal para gestionar duplicados
function mostrarModalDuplicados(duplicados) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 10001;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        backdrop-filter: blur(5px);
    `;
    
    const content = document.createElement('div');
    content.style.cssText = `
        background: white;
        border-radius: 16px;
        padding: 2rem;
        width: 100%;
        max-width: 600px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    `;
    
    content.innerHTML = `
        <h3 style="margin: 0 0 1.5rem 0; color: #ff9800; text-align: center;">⚠️ Clientes Duplicados Detectados</h3>
        <div id="listaDuplicados"></div>
        <div style="margin-top: 1.5rem; text-align: center;">
            <button onclick="this.closest('.modal-overlay').remove(); document.body.style.overflow = '';" 
                    style="padding: 0.8rem 2rem; background: #d4af37; color: white; border: none; border-radius: 8px; cursor: pointer;">
                Cerrar
            </button>
        </div>
    `;
    
    const listaDuplicados = content.querySelector('#listaDuplicados');
    duplicados.forEach(cliente => {
        const item = document.createElement('div');
        item.style.cssText = `
            padding: 1rem;
            border: 1px solid #ff9800;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            background: #fff3e0;
        `;
        item.innerHTML = `
            <strong>${cliente.nombre}</strong> - ${cliente.telefono}<br>
            <small>Total boletos: ${cliente.totalBoletos}</small>
        `;
        listaDuplicados.appendChild(item);
    });
    
    modal.appendChild(content);
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
}

// Función para buscar cliente por boleto específico
function buscarClientePorBoleto(numeroBoleto) {
    const filas = document.querySelectorAll('.boletos-table tbody tr');
    
    for (let fila of filas) {
        const numeroCell = fila.querySelector('td:nth-child(2)');
        const nombreCell = fila.querySelector('td:nth-child(4)');
        const telefonoCell = fila.querySelector('td:nth-child(5)');
        
        if (numeroCell && numeroCell.textContent.includes(numeroBoleto.toString())) {
            const nombre = nombreCell?.textContent.trim();
            const telefono = telefonoCell?.textContent.trim();
            
            if (nombre && telefono && nombre !== '-' && telefono !== '-') {
                return {
                    numero: numeroBoleto,
                    nombre: nombre,
                    telefono: telefono
                };
            }
        }
    }
    
    return null;
}

// Función de búsqueda rápida de cliente
function busquedaRapidaCliente() {
    const query = prompt('🔍 Buscar cliente por:\n1. Nombre\n2. Teléfono\n3. Número de boleto\n\nEscribe tu búsqueda:');
    
    if (!query) return;
    
    const resultados = [];
    
    // Buscar por nombre
    clientesDatabase.forEach((cliente) => {
        if (cliente.nombre.toLowerCase().includes(query.toLowerCase())) {
            resultados.push({ tipo: 'nombre', cliente });
        }
    });
    
    // Buscar por teléfono
    clientesDatabase.forEach((cliente) => {
        if (cliente.telefono.includes(query)) {
            resultados.push({ tipo: 'telefono', cliente });
        }
    });
    
    // Buscar por número de boleto
    if (!isNaN(query)) {
        const clientePorBoleto = buscarClientePorBoleto(parseInt(query));
        if (clientePorBoleto) {
            resultados.push({ tipo: 'boleto', cliente: clientePorBoleto });
        }
    }
    
    if (resultados.length === 0) {
        alert('❌ No se encontraron resultados para: ' + query);
    } else {
        mostrarResultadosBusqueda(resultados, query);
    }
}

// Mostrar resultados de búsqueda
function mostrarResultadosBusqueda(resultados, query) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 10001;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        backdrop-filter: blur(5px);
    `;
    
    const content = document.createElement('div');
    content.style.cssText = `
        background: white;
        border-radius: 16px;
        padding: 2rem;
        width: 100%;
        max-width: 500px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    `;
    
    content.innerHTML = `
        <h3 style="margin: 0 0 1.5rem 0; color: #1b5e20; text-align: center;">
            🔍 Resultados para: "${query}"
        </h3>
        <div style="margin-bottom: 1rem; text-align: center; color: #666;">
            ${resultados.length} resultado(s) encontrado(s)
        </div>
        <div id="listaResultados"></div>
        <div style="margin-top: 1.5rem; text-align: center;">
            <button onclick="this.closest('.modal-overlay').remove(); document.body.style.overflow = '';" 
                    style="padding: 0.8rem 2rem; background: #d4af37; color: white; border: none; border-radius: 8px; cursor: pointer;">
                Cerrar
            </button>
        </div>
    `;
    
    const listaResultados = content.querySelector('#listaResultados');
    resultados.forEach((resultado, index) => {
        const { tipo, cliente } = resultado;
        const item = document.createElement('div');
        item.style.cssText = `
            padding: 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        `;
        
        const tipoIconos = {
            nombre: '👤',
            telefono: '📞',
            boleto: '🎫'
        };
        
        const tipoTexto = {
            nombre: 'Por nombre',
            telefono: 'Por teléfono', 
            boleto: 'Por boleto'
        };
        
        item.innerHTML = `
            <div>
                <div style="font-weight: 600; color: #1b5e20; margin-bottom: 0.25rem;">
                    ${tipoIconos[tipo]} ${cliente.nombre}
                </div>
                <div style="font-size: 0.85rem; color: #666; margin-bottom: 0.25rem;">
                    ${cliente.telefono}
                </div>
                <div style="font-size: 0.75rem; color: #999;">
                    ${tipoTexto[tipo]}
                </div>
            </div>
            <div style="text-align: center;">
                ${cliente.totalBoletos ? `
                    <div style="background: linear-gradient(135deg, #d4af37, #b8941f); color: white; padding: 0.3rem 0.6rem; border-radius: 15px; font-size: 0.8rem; font-weight: bold;">
                        ${cliente.totalBoletos} boletos
                    </div>
                ` : `
                    <div style="background: linear-gradient(135deg, #17a2b8, #138496); color: white; padding: 0.3rem 0.6rem; border-radius: 15px; font-size: 0.8rem; font-weight: bold;">
                        Boleto actual
                    </div>
                `}
            </div>
        `;
        
        item.addEventListener('click', function() {
            // Autocompletar con este cliente
            const inputNombre = document.getElementById('nombre_rango') || document.getElementById('nombre_multiple');
            if (inputNombre) {
                seleccionarCliente(cliente, inputNombre);
            }
            // Cerrar modal
            modal.remove();
            document.body.style.overflow = '';
        });
        
        item.addEventListener('mouseenter', function() {
            this.style.background = 'linear-gradient(135deg, #f8f9fa, #e9ecef)';
            this.style.borderColor = '#d4af37';
            this.style.transform = 'translateY(-2px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.background = 'white';
            this.style.borderColor = '#e0e0e0';
            this.style.transform = 'translateY(0)';
        });
        
        listaResultados.appendChild(item);
    });
    
    modal.appendChild(content);
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
}

// Función para agregar cliente nuevo manualmente
function agregarClienteManual() {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 10001;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        backdrop-filter: blur(5px);
    `;
    
    const content = document.createElement('div');
    content.style.cssText = `
        background: white;
        border-radius: 16px;
        padding: 2rem;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    `;
    
    content.innerHTML = `
        <h3 style="margin: 0 0 1.5rem 0; color: #1b5e20; text-align: center;">
            👤 Agregar Cliente Nuevo
        </h3>
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Nombre completo:</label>
            <input type="text" id="nuevoNombre" placeholder="Nombre del cliente" 
                   style="width: 100%; padding: 0.8rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem;">
        </div>
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Teléfono:</label>
            <input type="tel" id="nuevoTelefono" placeholder="Número de teléfono" 
                   style="width: 100%; padding: 0.8rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem;">
        </div>
        <div style="display: flex; gap: 1rem;">
            <button id="cancelarNuevo" style="flex: 1; padding: 0.8rem; border: 1px solid #ddd; background: #f5f5f5; color: #333; border-radius: 8px; cursor: pointer;">
                Cancelar
            </button>
            <button id="guardarNuevo" style="flex: 1; padding: 0.8rem; border: none; background: #1b5e20; color: white; border-radius: 8px; cursor: pointer;">
                Agregar
            </button>
        </div>
    `;
    
    modal.appendChild(content);
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    // Focus en primer input
    setTimeout(() => {
        content.querySelector('#nuevoNombre').focus();
    }, 100);
    
    const cerrarModal = () => {
        modal.remove();
        document.body.style.overflow = '';
    };
    
    content.querySelector('#cancelarNuevo').onclick = cerrarModal;
    
    content.querySelector('#guardarNuevo').onclick = () => {
        const nombre = content.querySelector('#nuevoNombre').value.trim();
        const telefono = content.querySelector('#nuevoTelefono').value.trim();
        
        if (!nombre || !telefono) {
            alert('❌ Por favor completa todos los campos');
            return;
        }
        
        // Verificar si ya existe
        const clienteKey = nombre.toLowerCase();
        if (clientesDatabase.has(clienteKey)) {
            alert('⚠️ Ya existe un cliente con ese nombre');
            return;
        }
        
        // Agregar a la base de datos
        const nuevoCliente = {
            nombre: nombre,
            telefono: telefono,
            ultimaCompra: new Date(),
            totalBoletos: 0 // Se incrementará cuando haga compras
        };
        
        clientesDatabase.set(clienteKey, nuevoCliente);
        
        // Autocompletar en el formulario actual
        const inputNombre = document.getElementById('nombre_rango') || document.getElementById('nombre_multiple');
        if (inputNombre) {
            seleccionarCliente(nuevoCliente, inputNombre);
        }
        
        cerrarModal();
        
        if (typeof mostrarAlertaMovil !== 'undefined' && esMobile && esMobile()) {
            mostrarAlertaMovil('✅ Cliente agregado correctamente', 'success');
        } else {
            alert('✅ Cliente agregado correctamente');
        }
    };
    
    // Permitir guardar con Enter
    content.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            content.querySelector('#guardarNuevo').click();
        }
    });
    
    modal.onclick = (e) => {
        if (e.target === modal) {
            cerrarModal();
        }
    };
}

// Función para crear botón de herramientas avanzadas
function crearBotonHerramientasAvanzadas() {
    const controlGroups = document.querySelectorAll('.control-group');
    const ultimoGroup = controlGroups[controlGroups.length - 1];
    
    if (ultimoGroup) {
        const botonHerramientas = document.createElement('button');
        botonHerramientas.type = 'button';
        botonHerramientas.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.5rem;
            box-shadow: 0 8px 25px rgba(23, 162, 184, 0.4);
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        `;
        botonHerramientas.innerHTML = '🔧';
        botonHerramientas.title = 'Herramientas avanzadas de clientes';
        
        botonHerramientas.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
            this.style.boxShadow = '0 12px 35px rgba(23, 162, 184, 0.6)';
        });
        
        botonHerramientas.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.boxShadow = '0 8px 25px rgba(23, 162, 184, 0.4)';
        });
        
        botonHerramientas.addEventListener('click', mostrarMenuHerramientas);
        
        document.body.appendChild(botonHerramientas);
    }
}

// Mostrar menú de herramientas
function mostrarMenuHerramientas() {
    const menu = document.createElement('div');
    menu.style.cssText = `
        position: fixed;
        bottom: 90px;
        right: 20px;
        background: white;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        z-index: 1001;
        border: 2px solid #17a2b8;
        min-width: 200px;
    `;
    
    const opciones = [
        { emoji: '🔍', texto: 'Buscar Cliente', funcion: busquedaRapidaCliente },
        { emoji: '👤', texto: 'Agregar Cliente', funcion: agregarClienteManual },
        { emoji: '📊', texto: 'Estadísticas', funcion: () => {
            const stats = mostrarEstadisticasClientes();
            alert(`📊 Estadísticas de Clientes:\n\n` +
                  `👥 Total clientes: ${stats.totalClientes}\n` +
                  `🎫 Total boletos vendidos: ${stats.totalBoletos}\n` +
                  `📈 Promedio boletos/cliente: ${stats.promedioBoletosCliente}\n` +
                  `🥇 Cliente más frecuente: ${stats.clienteMasFrecuente?.nombre || 'N/A'}`);
        }},
        { emoji: '⚠️', texto: 'Detectar Duplicados', funcion: detectarClientesDuplicados },
        { emoji: '💾', texto: 'Exportar Datos', funcion: () => {
            const data = exportarClientesDatabase();
            navigator.clipboard.writeText(data).then(() => {
                alert('📋 Datos copiados al portapapeles');
            });
        }}
    ];
    
    opciones.forEach(opcion => {
        const item = document.createElement('div');
        item.style.cssText = `
            padding: 0.75rem;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        `;
        item.innerHTML = `<span style="font-size: 1.2rem;">${opcion.emoji}</span> ${opcion.texto}`;
        
        item.addEventListener('mouseenter', function() {
            this.style.background = 'linear-gradient(135deg, #f8f9fa, #e9ecef)';
            this.style.transform = 'translateX(5px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.background = 'transparent';
            this.style.transform = 'translateX(0)';
        });
        
        item.addEventListener('click', () => {
            opcion.funcion();
            menu.remove();
        });
        
        menu.appendChild(item);
    });
    
    // Botón cerrar
    const cerrar = document.createElement('div');
    cerrar.style.cssText = `
        padding: 0.5rem;
        text-align: center;
        border-top: 1px solid #e0e0e0;
        margin-top: 0.5rem;
        cursor: pointer;
        color: #666;
        font-size: 0.9rem;
    `;
    cerrar.textContent = '✕ Cerrar';
    cerrar.onclick = () => menu.remove();
    menu.appendChild(cerrar);
    
    document.body.appendChild(menu);
    
    // Cerrar al hacer click fuera
    setTimeout(() => {
        document.addEventListener('click', function cerrarMenu(e) {
            if (!menu.contains(e.target)) {
                menu.remove();
                document.removeEventListener('click', cerrarMenu);
            }
        });
    }, 100);
}

// Mejorar la función original de cambio de estado para actualizar base de datos
const cambiarEstadoOriginal = window.cambiarEstado;
window.cambiarEstado = function(boletoId, nuevoEstado) {
    // Capturar datos antes del cambio
    const fila = document.querySelector(`input[value="${boletoId}"]`)?.closest('tr');
    let clienteActual = null;
    
    if (fila) {
        const nombreCell = fila.querySelector('td:nth-child(4)');
        const telefonoCell = fila.querySelector('td:nth-child(5)');
        
        if (nombreCell && telefonoCell) {
            const nombre = nombreCell.textContent.trim();
            const telefono = telefonoCell.textContent.trim();
            
            if (nombre !== '-' && telefono !== '-') {
                clienteActual = { nombre, telefono };
            }
        }
    }
    
    // Si hay cliente seleccionado globalmente, usar esos datos
    if (clienteSeleccionado && (nuevoEstado === 'apartado' || nuevoEstado === 'pagado')) {
        if (typeof ejecutarCambioEstado !== 'undefined') {
            ejecutarCambioEstado(boletoId, nuevoEstado, clienteSeleccionado.nombre, clienteSeleccionado.telefono);
        } else {
            // Fallback a función original
            cambiarEstadoOriginal(boletoId, nuevoEstado);
        }
        return;
    }
    
    // Llamar función original
    if (cambiarEstadoOriginal) {
        cambiarEstadoOriginal(boletoId, nuevoEstado);
    }
};

// Inicializar herramientas cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        crearBotonHerramientasAvanzadas();
        
        // Detectar duplicados automáticamente después de cargar
        setTimeout(detectarClientesDuplicados, 2000);
    }, 1000);
});

console.log('✨ Funciones avanzadas de autocompletado cargadas');
    </script>
</body>
</html>