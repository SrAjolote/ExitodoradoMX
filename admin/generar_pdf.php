<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['telefono']) || !isset($_GET['rifa'])) {
    header('Location: consultar_boletos.php');
    exit();
}

$telefono = $_GET['telefono'];
$rifa_id = (int)$_GET['rifa'];

$stmt = $pdo->prepare("
    SELECT b.numero_boleto, r.nombre as rifa_nombre, r.fecha_entrega, r.total_boletos, b.nombre_cliente
    FROM boletos b 
    JOIN rifas r ON b.rifa_id = r.id 
    WHERE b.telefono_cliente = ? AND b.rifa_id = ? AND b.estado = 'pagado'
    ORDER BY b.numero_boleto ASC
");
$stmt->execute([$telefono, $rifa_id]);
$boletos = $stmt->fetchAll();

if (empty($boletos)) {
    header('Location: consultar_boletos.php');
    exit();
}

$rifa_nombre = $boletos[0]['rifa_nombre'];
$fecha_entrega = $boletos[0]['fecha_entrega'];
$total_boletos = $boletos[0]['total_boletos'];
$nombre_cliente = $boletos[0]['nombre_cliente'] ?? 'Cliente';
$digitos = strlen((string)$total_boletos);

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Boletos - ' . htmlspecialchars($rifa_nombre) . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #fdf6e3;
        }
        
        .header {
            text-align: center;
            background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header h2 {
            margin: 10px 0 0 0;
            font-size: 18px;
            opacity: 0.9;
        }
        
        .info-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 2px solid #d4af37;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            font-weight: bold;
            color: #1b5e20;
        }
        
        .info-value {
            color: #333;
        }
        
        .boletos-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            border: 2px solid #d4af37;
        }
        
        .boletos-title {
            text-align: center;
            color: #d4af37;
            font-size: 20px;
            margin-bottom: 20px;
            border-bottom: 2px solid #d4af37;
            padding-bottom: 10px;
        }
        
        .boletos-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 10px;
            margin: 20px 0;
        }
        
        .boleto-item {
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
            color: white;
            padding: 12px 8px;
            text-align: center;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(27, 94, 32, 0.3);
        }
        
        .total-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
            border: 2px solid #1b5e20;
        }
        
        .total-boletos {
            font-size: 18px;
            font-weight: bold;
            color: #1b5e20;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background: #1b5e20;
            color: white;
            border-radius: 10px;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        .fecha-generacion {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎰 ÉXITO DORADO MX 🎰</h1>
        <h2>Comprobante de Boletos</h2>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Rifa:</span>
            <span class="info-value">' . htmlspecialchars($rifa_nombre) . '</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cliente:</span>
            <span class="info-value">' . htmlspecialchars($nombre_cliente) . '</span>
        </div>
        <div class="info-row">
            <span class="info-label">Teléfono:</span>
            <span class="info-value">' . htmlspecialchars($telefono) . '</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha de entrega de premios:</span>
            <span class="info-value">' . date('d/m/Y', strtotime($fecha_entrega)) . '</span>
        </div>
    </div>
    
    <div class="boletos-section">
        <h3 class="boletos-title">TUS BOLETOS PAGADOS</h3>
        <div class="boletos-grid">';

foreach ($boletos as $boleto) {
    $numero_formateado = str_pad($boleto['numero_boleto'], $digitos, '0', STR_PAD_LEFT);
    $html .= '<div class="boleto-item">' . $numero_formateado . '</div>';
}

$html .= '
        </div>
        
        <div class="total-section">
            <div class="total-boletos">Total de boletos: ' . count($boletos) . '</div>
        </div>
    </div>
    
    <div class="footer">
        <p><strong>¡Mucha suerte en el sorteo!</strong></p>
        <p>WhatsApp: +52 1 81 1763 4009</p>
        <p>www.exitodoradomx.com</p>
    </div>
    
    <div class="fecha-generacion">
        Comprobante generado el ' . date('d/m/Y H:i:s') . '
    </div>
</body>
</html>';

$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'Boletos_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $rifa_nombre) . '_' . date('Y-m-d') . '.pdf';

$dompdf->stream($filename, array('Attachment' => 1));
?>