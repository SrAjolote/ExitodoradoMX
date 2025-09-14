<?php
require_once 'config.php';

$boletos_usuario = [];
$boleto_consultado = null;
$telefono = '';
$numero_boleto = '';
$tipo_consulta = '';

if ($_POST) {
    $tipo_consulta = $_POST['tipo_consulta'] ?? '';
    
    if ($tipo_consulta === 'telefono' && isset($_POST['telefono'])) {
        $telefono = trim($_POST['telefono']);
        if (!empty($telefono)) {
            // Modificado: incluir tanto boletos pagados como apartados
            $stmt = $pdo->prepare("
                SELECT b.*, r.nombre as rifa_nombre, r.fecha_entrega 
                FROM boletos b 
                JOIN rifas r ON b.rifa_id = r.id 
                WHERE b.telefono_cliente = ? AND (b.estado = 'pagado' OR b.estado = 'apartado')
                ORDER BY b.estado ASC, r.fecha_entrega ASC, b.numero_boleto ASC
            ");
            $stmt->execute([$telefono]);
            $boletos_usuario = $stmt->fetchAll();
        }
    } elseif ($tipo_consulta === 'boleto' && isset($_POST['numero_boleto'])) {
        $numero_boleto = trim($_POST['numero_boleto']);
        if (!empty($numero_boleto)) {
            $stmt = $pdo->prepare("
                SELECT b.*, r.nombre as rifa_nombre, r.fecha_entrega, r.id as rifa_id
                FROM boletos b 
                JOIN rifas r ON b.rifa_id = r.id 
                WHERE b.numero_boleto = ?
            ");
            $stmt->execute([$numero_boleto]);
            $boleto_consultado = $stmt->fetch();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Mis Boletos - Éxito Dorado MX</title>
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
            background: linear-gradient(135deg, #f5e6d3 0%, #e8d5c4 25%, #dcc5b0 50%, #d0b59c 75%, #c4a588 100%);
            color: #2c2c2c;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        /* Header profesional */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
            border: 2px solid #d4af37;
        }
        
        .brand-info h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #2c2c2c;
            margin-bottom: 0.2rem;
        }
        
        .brand-info p {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }
        
        .back-link {
            color: #2c2c2c;
            text-decoration: none;
            padding: 0.8rem 1.5rem;
            border: 2px solid #d4af37;
            border-radius: 15px;
            transition: all 0.3s ease;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .back-link:hover {
            background: #d4af37;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
        }
        
        .page-title {
            background: white;
            margin: 2rem auto;
            max-width: 900px;
            padding: 3rem 2rem;
            border-radius: 25px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .page-title::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d4af37, #f4e794, #d4af37);
        }
        
        .page-title h2 {
            font-size: 2.8rem;
            font-weight: 800;
            color: #2c2c2c;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }
        
        .page-title p {
            font-size: 1.2rem;
            color: #666;
            font-weight: 500;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Formulario de consulta mejorado */
        .consulta-form {
            background: white;
            border-radius: 25px;
            padding: 3rem;
            margin: 2rem 0;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .consulta-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #17a2b8, #20c997, #17a2b8);
        }
        
        .form-title {
            color: #2c2c2c;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        /* Pestañas */
        .tabs-container {
            margin-bottom: 2rem;
        }
        
        .tabs {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .tab-btn {
            background: white;
            border: 2px solid #e9ecef;
            color: #6c757d;
            padding: 1rem 2rem;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .tab-btn.active {
            background: linear-gradient(135deg, #17a2b8, #20c997);
            border-color: #17a2b8;
            color: white;
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        }
        
        .tab-btn:hover:not(.active) {
            border-color: #17a2b8;
            color: #17a2b8;
            transform: translateY(-2px);
        }
        
        .consulta-tab {
            display: none;
        }
        
        .consulta-tab.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 2rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 1rem;
            color: #2c2c2c;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: white;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
            transform: translateY(-2px);
        }
        
        .form-group input::placeholder {
            color: #999;
            font-weight: 400;
        }
        
        /* Botón de consulta espectacular */
        .btn-primary {
            background: linear-gradient(135deg, #17a2b8, #20c997);
            color: white;
            padding: 1.8rem 2rem;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-size: 1.2rem;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 10px 30px rgba(23, 162, 184, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: all 0.5s;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #138496, #17a2b8);
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(23, 162, 184, 0.4);
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:active {
            transform: translateY(-1px) scale(0.98);
        }
        
        /* Información de ayuda */
        .help-info {
            background: rgba(23, 162, 184, 0.1);
            border: 1px solid rgba(23, 162, 184, 0.3);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .help-info i {
            color: #17a2b8;
            font-size: 1.2rem;
            margin-top: 0.2rem;
        }
        
        .help-text {
            color: #0c5460;
            font-weight: 500;
            line-height: 1.5;
        }
        
        /* Resultados mejorados */
        .resultados {
            margin-top: 3rem;
        }
        
        .stats-summary {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.2);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .stats-summary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #28a745, #20c997, #28a745);
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 1rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .stats-number.pagados {
            color: #28a745;
        }
        
        .stats-number.apartados {
            color: #ffc107;
        }
        
        .stats-number.total {
            color: #17a2b8;
        }
        
        .stats-label {
            font-size: 1rem;
            color: #666;
            font-weight: 600;
        }
        
        /* Grupos de rifas mejorados */
        .rifa-group {
            background: white;
            border-radius: 25px;
            padding: 2.5rem;
            margin: 2rem 0;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .rifa-group::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #28a745, #20c997, #28a745);
        }
        
        .rifa-group:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        
        .rifa-title {
            color: #2c2c2c;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .fecha-entrega {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 1rem 2rem;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        /* Sección de estado de boletos */
        .estado-section {
            margin: 2rem 0;
        }
        
        .estado-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .estado-title.pagados {
            color: #28a745;
        }
        
        .estado-title.apartados {
            color: #ffc107;
        }
        
        /* Grid de boletos mejorado */
        .boletos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            gap: 12px;
            margin: 1rem 0;
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid #e9ecef;
        }
        
        .boletos-grid.pagados {
            background: rgba(40, 167, 69, 0.05);
        }
        
        .boletos-grid.apartados {
            background: rgba(255, 193, 7, 0.05);
        }
        
        .boleto-item {
            padding: 1rem;
            text-align: center;
            border-radius: 12px;
            font-weight: 800;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        
        .boleto-pagado {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .boleto-apartado {
            background: linear-gradient(135deg, #ffc107, #ffb300);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }
        
        .boleto-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: all 0.5s;
        }
        
        .boleto-item:hover {
            transform: translateY(-3px) scale(1.05);
        }
        
        .boleto-pagado:hover {
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }
        
        .boleto-apartado:hover {
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.4);
        }
        
        .boleto-item:hover::before {
            left: 100%;
        }
        
        /* Acciones de la rifa */
        .rifa-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }
        
        .btn-action {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        
        .btn-action:hover {
            background: linear-gradient(135deg, #495057, #343a40);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
        }
        
        /* Información del boleto individual */
        .boleto-detalle {
            background: white;
            border-radius: 25px;
            padding: 2.5rem;
            margin: 2rem 0;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .boleto-detalle::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d4af37, #f4e794, #d4af37);
        }
        
        .boleto-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .boleto-header h3 {
            font-size: 2rem;
            font-weight: 800;
            color: #2c2c2c;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .boleto-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 15px;
            border: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }
        
        .info-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .info-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #d4af37, #f4e794);
            color: white;
        }
        
        .info-icon.estado-disponible {
            background: linear-gradient(135deg, #6c757d, #495057);
        }
        
        .info-icon.estado-apartado {
            background: linear-gradient(135deg, #ffc107, #ffb300);
        }
        
        .info-icon.estado-pagado {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        .info-content {
            flex: 1;
        }
        
        .info-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }
        
        .info-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c2c2c;
        }
        
        .info-value.estado-disponible {
            color: #6c757d;
        }
        
        .info-value.estado-apartado {
            color: #ffc107;
        }
        
        .info-value.estado-pagado {
            color: #28a745;
        }
        
        .boleto-disponible-action {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            margin-top: 2rem;
        }
        
        .boleto-disponible-action p {
            color: #155724;
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }
        
        /* Sin boletos */
        .no-boletos {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 25px;
            margin: 2rem 0;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .no-boletos::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ffc107, #ffb300, #ffc107);
        }
        
        .no-boletos-icon {
            font-size: 4rem;
            color: #ffc107;
            margin-bottom: 1.5rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-15px); }
            60% { transform: translateY(-8px); }
        }
        
        .no-boletos h3 {
            color: #2c2c2c;
            font-size: 2rem;
            margin-bottom: 1rem;
            font-weight: 800;
        }
        
        .no-boletos p {
            color: #666;
            font-size: 1.1rem;
            margin: 0.8rem 0;
            line-height: 1.6;
        }
        
        .no-boletos-actions {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Navegación */
        .volver {
            text-align: center;
            margin: 3rem 0;
        }
        
        .volver a {
            color: #2c2c2c;
            text-decoration: none;
            padding: 1rem 2rem;
            border: 2px solid #d4af37;
            border-radius: 15px;
            transition: all 0.3s ease;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .volver a:hover {
            background: #d4af37;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .page-title {
                padding: 2rem 1.5rem;
                margin: 1rem auto;
            }
            
            .page-title h2 {
                font-size: 2.2rem;
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .consulta-form {
                padding: 2rem 1.5rem;
            }
            
            .tabs {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .tab-btn {
                padding: 0.8rem 1.5rem;
                font-size: 0.9rem;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .boletos-grid {
                grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
                gap: 10px;
                padding: 1.5rem;
            }
            
            .boleto-item {
                padding: 0.8rem;
                font-size: 0.9rem;
            }
            
            .rifa-group {
                padding: 2rem 1.5rem;
            }
            
            .rifa-title {
                font-size: 1.5rem;
                flex-direction: column;
            }
            
            .rifa-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-action {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
            
            .boleto-info-grid {
                grid-template-columns: 1fr;
            }
            
            .boleto-header h3 {
                font-size: 1.6rem;
                flex-direction: column;
            }
        }
        
        /* Animaciones de entrada */
        .consulta-form,
        .rifa-group,
        .boleto-detalle,
        .no-boletos {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .rifa-group:nth-child(2) { animation-delay: 0.1s; }
        .rifa-group:nth-child(3) { animation-delay: 0.2s; }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        a.logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none !important;
            color: inherit !important;
            transition: all 0.3s ease;
        }

        a.logo-section:hover,
        a.logo-section:visited,
        a.logo-section:active,
        a.logo-section:focus {
            text-decoration: none !important;
            color: inherit !important;
            outline: none !important;
        }

        a.logo-section:hover {
            transform: translateY(-2px);
        }

        a.logo-section:hover .logo {
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.5);
            transform: scale(1.05);
        }

        a.logo-section:hover .brand-info h1 {
            color: #d4af37;
        }

        /* Asegurar que los elementos internos no tengan decoración */
        a.logo-section * {
            text-decoration: none !important;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo-section">
                <img src="logo.jpg" alt="Éxito Dorado MX" class="logo">
                <div class="brand-info">
                    <h1>ÉXITO DORADO MX</h1>
                    <p>Consulta de Boletos</p>
                </div>
            </a>
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Volver al inicio
            </a>
        </div>
    </header>
    
    <div class="page-title">
        <h2>
            <i class="fas fa-ticket-alt"></i>
            Consultar Boletos
        </h2>
        <p>Verifica tus boletos pagados y apartados, o consulta el estado de cualquier boleto</p>
    </div>
    
    <div class="container">
        <div class="consulta-form">
            <h3 class="form-title">
                <i class="fas fa-search"></i>
                Consulta tus boletos
            </h3>
            
            <!-- Pestañas de selección -->
            <div class="tabs-container">
                <div class="tabs">
                    <button type="button" class="tab-btn <?php echo ($tipo_consulta !== 'boleto') ? 'active' : ''; ?>" onclick="cambiarTab('telefono')">
                        <i class="fas fa-phone"></i>
                        Por Teléfono
                    </button>
                    <button type="button" class="tab-btn <?php echo ($tipo_consulta === 'boleto') ? 'active' : ''; ?>" onclick="cambiarTab('boleto')">
                        <i class="fas fa-ticket-alt"></i>
                        Por Número de Boleto
                    </button>
                </div>
            </div>
            
            <!-- Formulario por teléfono -->
            <form method="POST" id="consultaFormTelefono" class="consulta-tab <?php echo ($tipo_consulta !== 'boleto') ? 'active' : ''; ?>">
                <input type="hidden" name="tipo_consulta" value="telefono">
                <div class="form-group">
                    <label for="telefono">
                        <i class="fas fa-phone"></i>
                        Número de teléfono:
                    </label>
                    <input type="tel" 
                           id="telefono" 
                           name="telefono" 
                           value="<?php echo $tipo_consulta === 'telefono' ? htmlspecialchars($telefono) : ''; ?>" 
                           placeholder="Ejemplo: 81 8094 6816" 
                           required>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-search"></i>
                    Consultar mis Boletos
                </button>
            </form>
            
            <!-- Formulario por número de boleto -->
            <form method="POST" id="consultaFormBoleto" class="consulta-tab <?php echo ($tipo_consulta === 'boleto') ? 'active' : ''; ?>">
                <input type="hidden" name="tipo_consulta" value="boleto">
                <div class="form-group">
                    <label for="numero_boleto">
                        <i class="fas fa-ticket-alt"></i>
                        Número de boleto:
                    </label>
                    <input type="number" 
                           id="numero_boleto" 
                           name="numero_boleto" 
                           value="<?php echo $tipo_consulta === 'boleto' ? htmlspecialchars($numero_boleto) : ''; ?>" 
                           placeholder="Ejemplo: 1234" 
                           min="1"
                           required>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-search"></i>
                    Consultar Boleto
                </button>
            </form>
            
            <div class="help-info">
                <i class="fas fa-info-circle"></i>
                <div class="help-text">
                    <strong>Consulta por teléfono:</strong> Muestra todos tus boletos pagados y apartados.<br>
                    <strong>Consulta por boleto:</strong> Muestra el estado específico de un boleto y sus datos.
                </div>
            </div>
        </div>
        
        <?php if ($_POST): ?>
            <div class="resultados">
                <?php if ($tipo_consulta === 'telefono'): ?>
                    <!-- Resultados por teléfono -->
                    <?php if (empty($boletos_usuario)): ?>
                        <div class="no-boletos">
                            <div class="no-boletos-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <h3>No se encontraron boletos</h3>
                            <p>No hay boletos pagados o apartados registrados con este número de teléfono.</p>
                            <p>Si has realizado una compra o apartado recientemente, verifica que:</p>
                            <ul style="text-align: left; display: inline-block; margin: 1rem 0;">
                                <li>El número de teléfono esté correcto</li>
                                <li>Tu pago haya sido confirmado o el boleto apartado</li>
                                <li>El boleto esté marcado como "pagado" o "apartado"</li>
                            </ul>
                            
                            <div class="no-boletos-actions">
                                <a href="https://wa.me/5218180946816" target="_blank" class="btn-action" style="background: linear-gradient(135deg, #25d366, #128c7e);">
                                    <i class="fab fa-whatsapp"></i>
                                    Contactar Soporte
                                </a>
                                <a href="index.php" class="btn-action">
                                    <i class="fas fa-home"></i>
                                    Ir al Inicio
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php
                        $rifas_agrupadas = [];
                        $total_boletos = 0;
                        $total_pagados = 0;
                        $total_apartados = 0;
                        
                        foreach ($boletos_usuario as $boleto) {
                            $rifas_agrupadas[$boleto['rifa_id']]['info'] = [
                                'nombre' => $boleto['rifa_nombre'],
                                'fecha_entrega' => $boleto['fecha_entrega']
                            ];
                            
                            if ($boleto['estado'] === 'pagado') {
                                $rifas_agrupadas[$boleto['rifa_id']]['pagados'][] = $boleto['numero_boleto'];
                                $total_pagados++;
                            } elseif ($boleto['estado'] === 'apartado') {
                                $rifas_agrupadas[$boleto['rifa_id']]['apartados'][] = $boleto['numero_boleto'];
                                $total_apartados++;
                            }
                            $total_boletos++;
                        }
                        ?>
                        
                        <div class="stats-summary">
                            <div class="stats-container">
                                <?php if ($total_pagados > 0): ?>
                                <div class="stat-item">
                                    <div class="stats-number pagados"><?php echo $total_pagados; ?></div>
                                    <div class="stats-label">
                                        <i class="fas fa-check-circle"></i>
                                        Boletos Pagados
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($total_apartados > 0): ?>
                                <div class="stat-item">
                                    <div class="stats-number apartados"><?php echo $total_apartados; ?></div>
                                    <div class="stats-label">
                                        <i class="fas fa-clock"></i>
                                        Boletos Apartados
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="stat-item">
                                    <div class="stats-number total"><?php echo $total_boletos; ?></div>
                                    <div class="stats-label">
                                        <i class="fas fa-ticket-alt"></i>
                                        Total de Boletos
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php foreach ($rifas_agrupadas as $rifa_id => $datos): ?>
                            <div class="rifa-group">
                                <h3 class="rifa-title">
                                    <i class="fas fa-trophy"></i>
                                    <?php echo htmlspecialchars($datos['info']['nombre']); ?>
                                </h3>
                                
                                <div class="fecha-entrega">
                                    <i class="fas fa-calendar-alt"></i>
                                    Entrega de premios: <?php echo date('d/m/Y', strtotime($datos['info']['fecha_entrega'])); ?>
                                </div>
                                
                                <?php if (!empty($datos['pagados'])): ?>
                                <div class="estado-section">
                                    <div class="estado-title pagados">
                                        <i class="fas fa-check-circle"></i>
                                        Tus boletos pagados (<?php echo count($datos['pagados']); ?> boletos):
                                    </div>
                                    
                                    <div class="boletos-grid pagados">
                                        <?php foreach ($datos['pagados'] as $numero): ?>
                                            <div class="boleto-item boleto-pagado"><?php echo str_pad($numero, 4, '0', STR_PAD_LEFT); ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($datos['apartados'])): ?>
                                <div class="estado-section">
                                    <div class="estado-title apartados">
                                        <i class="fas fa-clock"></i>
                                        Tus boletos apartados (<?php echo count($datos['apartados']); ?> boletos):
                                    </div>
                                    
                                    <div class="boletos-grid apartados">
                                        <?php foreach ($datos['apartados'] as $numero): ?>
                                            <div class="boleto-item boleto-apartado"><?php echo str_pad($numero, 4, '0', STR_PAD_LEFT); ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="help-info" style="margin-top: 1rem; background: rgba(255, 193, 7, 0.1); border-color: rgba(255, 193, 7, 0.3);">
                                        <i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i>
                                        <div class="help-text" style="color: #856404;">
                                            <strong>Boletos apartados:</strong> Estos boletos están reservados temporalmente. Contacta para confirmar el pago antes de que expire el apartado.
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="rifa-actions">
                                    <a href="https://wa.me/5218180946816?text=Hola, consulto sobre mis boletos de la rifa: <?php echo urlencode($datos['info']['nombre']); ?>" target="_blank" class="btn-action" style="background: linear-gradient(135deg, #25d366, #128c7e);">
                                        <i class="fab fa-whatsapp"></i>
                                        Contactar sobre esta Rifa
                                    </a>
                                    <?php if (!empty($datos['apartados'])): ?>
                                    <a href="https://wa.me/5218180946816?text=Hola, quiero pagar mis boletos apartados de la rifa: <?php echo urlencode($datos['info']['nombre']); ?>" target="_blank" class="btn-action" style="background: linear-gradient(135deg, #ffc107, #ffb300);">
                                        <i class="fas fa-credit-card"></i>
                                        Pagar Boletos Apartados
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                <?php elseif ($tipo_consulta === 'boleto'): ?>
                    <!-- Resultados por número de boleto -->
                    <?php if (!$boleto_consultado): ?>
                        <div class="no-boletos">
                            <div class="no-boletos-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <h3>Boleto no encontrado</h3>
                            <p>No se encontró el boleto número <strong><?php echo htmlspecialchars($numero_boleto); ?></strong></p>
                            <p>Verifica que el número de boleto esté correcto.</p>
                            
                            <div class="no-boletos-actions">
                                <a href="https://wa.me/5218180946816" target="_blank" class="btn-action" style="background: linear-gradient(135deg, #25d366, #128c7e);">
                                    <i class="fab fa-whatsapp"></i>
                                    Contactar Soporte
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="boleto-detalle">
                            <div class="boleto-header">
                                <h3>
                                    <i class="fas fa-ticket-alt"></i>
                                    Información del Boleto #<?php echo str_pad($boleto_consultado['numero_boleto'], 4, '0', STR_PAD_LEFT); ?>
                                </h3>
                            </div>
                            
                            <div class="boleto-info-grid">
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Rifa</div>
                                        <div class="info-value"><?php echo htmlspecialchars($boleto_consultado['rifa_nombre']); ?></div>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-icon estado-<?php echo $boleto_consultado['estado']; ?>">
                                        <?php if ($boleto_consultado['estado'] === 'disponible'): ?>
                                            <i class="fas fa-circle"></i>
                                        <?php elseif ($boleto_consultado['estado'] === 'apartado'): ?>
                                            <i class="fas fa-clock"></i>
                                        <?php elseif ($boleto_consultado['estado'] === 'pagado'): ?>
                                            <i class="fas fa-check-circle"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Estado</div>
                                        <div class="info-value estado-<?php echo $boleto_consultado['estado']; ?>">
                                            <?php 
                                            switch($boleto_consultado['estado']) {
                                                case 'disponible': echo 'Disponible'; break;
                                                case 'apartado': echo 'Apartado'; break;
                                                case 'pagado': echo 'Pagado'; break;
                                                default: echo ucfirst($boleto_consultado['estado']);
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($boleto_consultado['estado'] !== 'disponible' && !empty($boleto_consultado['nombre_cliente'])): ?>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Cliente</div>
                                        <div class="info-value"><?php echo htmlspecialchars($boleto_consultado['nombre_cliente']); ?></div>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Teléfono</div>
                                        <div class="info-value"><?php echo htmlspecialchars($boleto_consultado['telefono_cliente']); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Fecha de Entrega</div>
                                        <div class="info-value"><?php echo date('d/m/Y', strtotime($boleto_consultado['fecha_entrega'])); ?></div>
                                    </div>
                                </div>
                                
                                <?php if ($boleto_consultado['estado'] === 'apartado' && !empty($boleto_consultado['fecha_apartado'])): ?>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Fecha de Apartado</div>
                                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($boleto_consultado['fecha_apartado'])); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($boleto_consultado['estado'] === 'disponible'): ?>
                            <div class="boleto-disponible-action">
                                <p><i class="fas fa-info-circle"></i> Este boleto está disponible para compra</p>
                                <a href="comprar.php?rifa=<?php echo $boleto_consultado['rifa_id']; ?>" class="btn-action" style="background: linear-gradient(135deg, #28a745, #20c997);">
                                    <i class="fas fa-shopping-cart"></i>
                                    Comprar este Boleto
                                </a>
                            </div>
                            <?php elseif ($boleto_consultado['estado'] === 'apartado'): ?>
                            <div class="boleto-disponible-action" style="background: rgba(255, 193, 7, 0.1); border-color: rgba(255, 193, 7, 0.3);">
                                <p style="color: #856404;"><i class="fas fa-exclamation-triangle"></i> Este boleto está apartado temporalmente</p>
                                <a href="https://wa.me/5218180946816?text=Hola, quiero pagar el boleto apartado #<?php echo $boleto_consultado['numero_boleto']; ?> de la rifa: <?php echo urlencode($boleto_consultado['rifa_nombre']); ?>" target="_blank" class="btn-action" style="background: linear-gradient(135deg, #ffc107, #ffb300);">
                                    <i class="fas fa-credit-card"></i>
                                    Pagar este Boleto
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="volver">
            <a href="index.php">
                <i class="fas fa-arrow-left"></i>
                Volver al inicio
            </a>
        </div>
    </div>
    
    <script>
        // Función para cambiar entre pestañas
        function cambiarTab(tipo) {
            // Cambiar pestañas activas
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.consulta-tab').forEach(tab => tab.classList.remove('active'));
            
            // Activar pestaña seleccionada
            event.target.classList.add('active');
            document.getElementById('consultaForm' + (tipo === 'telefono' ? 'Telefono' : 'Boleto')).classList.add('active');
        }
        
        // Validación del número de teléfono
        document.getElementById('telefono').addEventListener('input', function(e) {
            let valor = e.target.value.replace(/\D/g, ''); // Solo números
            
            // Formato automático para números mexicanos
            if (valor.length >= 10) {
                if (valor.startsWith('52')) {
                    // Ya tiene código de país
                    valor = '+52 ' + valor.substring(2);
                } else if (valor.length === 10) {
                    // Número local, agregar código de país
                    valor = '' + valor;
                }
            }
            
            e.target.value = valor;
        });
        
        // Efectos visuales mejorados
        document.addEventListener('DOMContentLoaded', function() {
            // Animar entrada de boletos
            const boletos = document.querySelectorAll('.boleto-item');
            boletos.forEach((boleto, index) => {
                boleto.style.opacity = '0';
                boleto.style.transform = 'translateY(20px)';
                boleto.style.transition = 'all 0.3s ease';
                
                setTimeout(() => {
                    boleto.style.opacity = '1';
                    boleto.style.transform = 'translateY(0)';
                }, index * 50);
            });
            
            // Efectos hover para boletos
            boletos.forEach(boleto => {
                boleto.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.05)';
                });
                
                boleto.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Scroll suave al resultado
            <?php if ($_POST): ?>
            const resultados = document.querySelector('.resultados');
            if (resultados) {
                setTimeout(() => {
                    resultados.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                }, 100);
            }
            <?php endif; ?>
        });
        
        // CSS adicional para animaciones
        const style = document.createElement('style');
        style.textContent = `
            /* Efecto de shine para las cards */
            .rifa-group:hover::after {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
                animation: shine 0.6s ease-out;
                pointer-events: none;
            }
            
            @keyframes shine {
                to {
                    left: 100%;
                }
            }
            
            /* Efecto de pulso para botones activos */
            .btn-primary:active,
            .btn-action:active {
                transform: translateY(-1px) scale(0.98) !important;
            }
            
            /* Mejoras para el input focus */
            .form-group input:focus {
                animation: focusGlow 0.3s ease-out;
            }
            
            @keyframes focusGlow {
                0% {
                    box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.4);
                }
                100% {
                    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>