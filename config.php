<?php
$host = 'localhost';
$dbname = 'u257831247_rifas';
$username = 'u257831247_rifas';
$password = '2415691611+Juan';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

session_start();

function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: admin/login.php');
        exit();
    }
}

function formatoPeso($cantidad) {
    return '$' . number_format($cantidad, 2) . ' MXN';
}

function generarMensajeWhatsApp($boletos_apartados, $boletos_no_apartados, $nombre_cliente, $monto_total, $rifa_nombre) {
    $mensaje = "Hola, Aparte boletos de la rifa!!\n";
    $mensaje .= formatoPeso($monto_total) . "!!\n";
    $mensaje .= "————————————\n";
    $mensaje .= count($boletos_apartados) . " BOLETOS:\n";
    $mensaje .= implode(', ', $boletos_apartados) . "\n\n";
    
    if (!empty($boletos_no_apartados)) {
        $mensaje .= "*Boletos NO apartados:*\n";
        $mensaje .= implode(', ', $boletos_no_apartados) . "\n\n";
    }
    
    $mensaje .= "Nombre: " . $nombre_cliente . "\n\n";
    $mensaje .= "FAVOR DE MANDAR TU PAGO AQUI A ESTA LINEA ÚNICAMENTE.. DE LO CONTRARIO PODRIA SALIR A LA VENTA TU BOLETO...\n";
    $mensaje .= "SORTEO " . $rifa_nombre . ", " . formatoPeso($monto_total) . " SE JUEGA CON TRIS CLASICO DE LN [fecha y hora] TRANSMISIÓN EN VIVO EN LA PAGINA DE FACEBOOK\n\n";
    $mensaje .= "1 BOLETO POR \$1\n5 BOLETOS POR \$5\n10 BOLETOS POR \$10\n20 BOLETOS POR \$20\n30 BOLETOS POR \$30\n40 BOLETOS POR \$40\n50 BOLETOS POR \$50\n100 BOLETOS POR \$100\n150 BOLETOS POR \$150\n200 BOLETOS POR \$200\n300 BOLETOS POR \$300\n400 BOLETOS POR \$400\n500 BOLETOS POR \$500\n1,000 BOLETOS POR \$1,000\n";
    $mensaje .= "————————————\n";
    $mensaje .= "*CUENTAS DE PAGO AQUÍ:* https://exitodoradomx.com/pagos\n\n";
    $mensaje .= "Celular: 8117634009\n\n";
    $mensaje .= "El siguiente paso es enviar foto del comprobante de pago por aquí";
    
    return $mensaje;
}
?>