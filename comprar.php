<?php
require_once 'config.php';

$rifa_id = isset($_GET['rifa']) ? (int)$_GET['rifa'] : 0;

if (!$rifa_id) {
    header('Location: index.php');
    exit();
}

$rifa_stmt = $pdo->prepare("SELECT * FROM rifas WHERE id = ? AND activa = 1");
$rifa_stmt->execute([$rifa_id]);
$rifa = $rifa_stmt->fetch();

if (!$rifa) {
    header('Location: index.php');
    exit();
}

// Obtener boletos y su estado
$boletos_stmt = $pdo->prepare("SELECT numero_boleto, estado FROM boletos WHERE rifa_id = ? ORDER BY numero_boleto ASC");
$boletos_stmt->execute([$rifa_id]);
$boletos = $boletos_stmt->fetchAll();

// Crear array para fácil acceso
$boletos_estado = [];
foreach ($boletos as $boleto) {
    $boletos_estado[$boleto['numero_boleto']] = $boleto['estado'];
}

// Obtener combos predefinidos
$combos_stmt = $pdo->prepare("SELECT * FROM combos WHERE rifa_id = ? ORDER BY cantidad_boletos ASC");
$combos_stmt->execute([$rifa_id]);
$combos = $combos_stmt->fetchAll();

// Procesar compra
$mensaje = '';
$error = '';
$boletos_comprados = [];

if ($_POST && isset($_POST['comprar'])) {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos'] ?? '');
    $telefono = trim($_POST['telefono']);
    $estado = trim($_POST['estado'] ?? '');
    $boletos_seleccionados = isset($_POST['boletos_seleccionados']) ? json_decode($_POST['boletos_seleccionados'], true) : [];
    
    if (empty($nombre) || empty($telefono)) {
        $error = 'Nombre y teléfono son obligatorios';
    } elseif (empty($apellidos)) {
        $error = 'Los apellidos son obligatorios';
    } elseif (empty($estado)) {
        $error = 'El estado es obligatorio';
    } elseif (empty($boletos_seleccionados)) {
        $error = 'Debes seleccionar al menos un boleto';
    } else {
        try {
            $pdo->beginTransaction();
            
            $boletos_procesados = [];
            $total_a_pagar = 0;
            
            // Verificar boletos específicos ya seleccionados
            foreach ($boletos_seleccionados as $numero_boleto) {
                $numero_boleto = (int)$numero_boleto;
                
                $stmt = $pdo->prepare("SELECT id FROM boletos WHERE rifa_id = ? AND numero_boleto = ? AND estado = 'disponible' FOR UPDATE");
                $stmt->execute([$rifa_id, $numero_boleto]);
                $boleto = $stmt->fetch();
                
                if (!$boleto) {
                    throw new Exception("El boleto $numero_boleto ya no está disponible");
                }
                
                $boletos_procesados[] = [
                    'id' => $boleto['id'],
                    'numero' => $numero_boleto
                ];
                $total_a_pagar += $rifa['precio_boleto'];
            }
            
            // Apartar boletos con información completa
            $nombre_completo = trim($nombre . ' ' . $apellidos);
            foreach ($boletos_procesados as $boleto_data) {
                $stmt = $pdo->prepare("UPDATE boletos SET estado = 'apartado', nombre_cliente = ?, telefono_cliente = ?, estado_cliente = ?, fecha_apartado = NOW() WHERE id = ?");
                $stmt->execute([$nombre_completo, $telefono, $estado, $boleto_data['id']]);
            }
            
            $pdo->commit();
            
            // Preparar datos para mostrar
            $boletos_comprados = array_column($boletos_procesados, 'numero');
            $mensaje = 'success';
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

$digitos = strlen((string)$rifa['total_boletos']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Boletos - <?php echo htmlspecialchars($rifa['nombre']); ?> | Éxito Dorado MX</title>
    
    <!-- Variables PHP para JavaScript -->
    <script>
    window.precioBoleto = <?php echo $rifa['precio_boleto']; ?>;
    window.totalBoletosRifa = <?php echo $rifa['total_boletos']; ?>;
    window.digitos = <?php echo $digitos; ?>;
    console.log('💰 Precio real del boleto cargado:', window.precioBoleto);
    </script>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Aquí puedes agregar tu CSS -->
    <style>
/* ====== RESET Y BASE ====== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: linear-gradient(135deg, #fdf6e3 0%, #f5f1e3 100%);
    color: #333;
    line-height: 1.6;
    min-height: 100vh;
}

/* ====== HEADER ====== */
.header {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 100;
    border-bottom: 3px solid #d4af37;
}

.navbar {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
}

.logo-img {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
    border: 2px solid #d4af37;
}

.brand-text {
    font-size: 1.3rem;
    font-weight: 800;
    color: #2c2c2c;
}

.back-link {
    color: #2c2c2c;
    text-decoration: none;
    padding: 0.5rem 1rem;
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
}

/* ====== CONTAINER ====== */
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

/* ====== WIDGET MÓVIL FLOTANTE ====== */
.mobile-floating-info {
    display: none;
    position: fixed;
    top: auto;
    bottom: 20px;
    left: 15px;
    right: 15px;
    background: white;
    border-radius: 20px;
    padding: 1rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    border: 2px solid #d4af37;
    backdrop-filter: blur(10px);
    animation: slideUpMobile 0.3s ease-out;
}

/* ====== BOTÓN DE MÁQUINA DE LA SUERTE MÓVIL ====== */
.mobile-suerte-btn {
    display: none;
    position: fixed;
    top: auto;
    bottom: 90px;
    right: 20px;
    z-index: 1001;
    animation: pulseSuccess 2s ease-in-out infinite;
}

/* Mostrar solo en móvil */
@media (max-width: 768px) {
    .mobile-suerte-btn {
        display: block;
    }
}

/* Ocultar en desktop */
@media (min-width: 769px) {
    .mobile-suerte-btn {
        display: none !important;
    }
    
    .modal-combos-mobile {
        display: none !important;
    }
}

.btn-suerte-movil {
    background: linear-gradient(135deg, #d4af37, #f4e794, #b8941f, #d4af37);
    background-size: 300% 300%;
    animation: gradientShift 3s ease infinite, floating 2s ease-in-out infinite;
    color: white;
    border: none;
    padding: 1rem 1.5rem;
    border-radius: 25px;
    font-weight: 800;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    min-width: 80px;
    text-align: center;
}

.btn-suerte-movil:hover {
    transform: scale(1.1) translateY(-5px);
    box-shadow: 0 15px 35px rgba(212, 175, 55, 0.6);
}

.btn-suerte-movil i {
    font-size: 1.5rem;
    animation: spin 2s linear infinite;
}

.btn-suerte-movil span {
    font-size: 0.7rem;
    line-height: 1;
}

/* ====== MODAL DE COMBOS MÓVIL ====== */
.modal-combos-mobile {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(212, 175, 55, 0.2);
    backdrop-filter: blur(10px);
}

.modal-combos-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-radius: 25px 25px 0 0;
    padding: 2rem 1.5rem;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.3);
    animation: slideUpFromBottom 0.4s ease-out;
    scroll-behavior: smooth;
}

.modal-combos-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.modal-combos-header h3 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #2c2c2c;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-combos-close {
    background: #f8f9fa;
    border: none;
    font-size: 1.2rem;
    color: #666;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.3s ease;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-combos-close:hover {
    background: #d4af37;
    color: white;
    transform: scale(1.1);
}

.combos-descripcion {
    text-align: center;
    color: #1b5e20;
    font-size: 1rem;
    margin-bottom: 2rem;
    font-weight: 600;
}

.combos-mobile-lista {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.combo-mobile-item {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border: 2px solid #e9ecef;
    border-radius: 20px;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.combo-mobile-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.1), transparent);
    transition: left 0.6s ease;
}

.combo-mobile-item:hover::before {
    left: 100%;
}

.combo-mobile-item:hover {
    border-color: #d4af37;
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
}

.combo-mobile-info {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.combo-mobile-cantidad {
    font-weight: 800;
    color: #2c2c2c;
    font-size: 1.1rem;
}

.combo-mobile-precio {
    font-size: 1rem;
    color: #1b5e20;
    font-weight: 700;
}

.combo-mobile-descuento {
    font-size: 0.9rem;
    color: #d4af37;
    font-weight: 700;
}

.combo-mobile-btn {
    background: linear-gradient(135deg, #d4af37, #f4e794);
    color: white;
    padding: 0.8rem 1.2rem;
    border-radius: 15px;
    font-weight: 700;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 120px;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
}

/* ====== COMBO PERSONALIZADO ====== */
.combo-personalizado {
    background: linear-gradient(135deg, #fdf6e3, #f5f1e3);
    border: 2px solid #d4af37;
    border-radius: 20px;
    padding: 1.5rem;
    margin-top: 1rem;
}

.combo-personalizado h4 {
    color: #1b5e20;
    font-weight: 800;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.cantidad-personalizada-input {
    display: flex;
    gap: 1rem;
    align-items: stretch;
}

.cantidad-personalizada-input input {
    flex: 1;
    padding: 1rem;
    border: 2px solid #d4af37;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    text-align: center;
    background: #fdf6e3;
    color: #1b5e20;
}

.cantidad-personalizada-input input:focus {
    outline: none;
    border-color: #b8941f;
    box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.25);
    background: white;
}

.btn-personalizado {
    background: linear-gradient(135deg, #d4af37, #b8941f);
    color: white;
    border: none;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    min-width: 120px;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
}

.btn-personalizado:hover {
    background: linear-gradient(135deg, #b8941f, #996f1a);
    transform: translateY(-2px);
}

.btn-personalizado:active {
    background: linear-gradient(135deg, #996f1a, #7a5c15);
}

.floating-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.floating-row {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    flex: 1;
}

.floating-label {
    font-size: 0.75rem;
    color: #666;
    font-weight: 600;
}

.floating-value {
    font-size: 1.1rem;
    font-weight: 800;
    color: #28a745;
}

.floating-total {
    font-size: 1.2rem;
    font-weight: 800;
    color: #d4af37;
}

.floating-comprar-btn {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    padding: 0.8rem 1.5rem;
    border-radius: 15px;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 100px;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.floating-comprar-btn:hover {
    background: linear-gradient(135deg, #20c997, #17a2b8);
    transform: translateY(-2px);
}

.floating-comprar-btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* ====== ALERTAS ====== */
.alert-error {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    border: 1px solid #dc3545;
    color: #721c24;
    padding: 1.5rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    font-weight: 600;
    box-shadow: 0 8px 25px rgba(220, 53, 69, 0.2);
}

/* ====== RIFA HEADER ====== */
.rifa-header {
    background: white;
    border-radius: 24px;
    padding: 3rem;
    margin-bottom: 2rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.12);
    border: 1px solid rgba(212, 175, 55, 0.2);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.rifa-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #d4af37, #1b5e20, #d4af37);
}

.rifa-image-large {
    width: 200px;
    height: 200px;
    margin: 0 auto 2rem;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    background: linear-gradient(135deg, #d4af37, #f4e794);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #d4af37;
}

.rifa-image-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.rifa-image-large .default-icon {
    font-size: 5rem;
    color: white;
}

.rifa-title {
    font-size: clamp(2rem, 4vw, 2.8rem);
    color: #2c2c2c;
    margin-bottom: 1rem;
    font-weight: 800;
}

.rifa-prize {
    background: linear-gradient(135deg, #d4af37, #f4e794);
    color: white;
    padding: 2rem;
    border-radius: 20px;
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 2rem;
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
}

/* ====== PROGRESS BAR ====== */
.progress-section {
    margin-top: 1.5rem;
    background: white;
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid rgba(212, 175, 55, 0.2);
}

.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c2c2c;
}

.progress-bar-container {
    position: relative;
    background: #f8f9fa;
    border-radius: 10px;
    height: 20px;
    overflow: hidden;
    border: 1px solid #e9ecef;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.progress-bar {
    height: 100%;
    background: linear-gradient(135deg, #d4af37, #f4e794);
    border-radius: 9px;
    transition: width 0.8s ease;
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-weight: 700;
    font-size: 0.8rem;
    color: #2c2c2c;
    z-index: 1;
}

/* ====== LAYOUT PRINCIPAL ====== */
.compra-section {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 2rem;
    margin-top: 2rem;
}

/* ====== SELECCIÓN DE BOLETOS ====== */
.seleccion-boletos {
    background: white;
    border-radius: 24px;
    padding: 3rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.12);
    border: 1px solid rgba(212, 175, 55, 0.2);
    position: relative;
    overflow: hidden;
}

.seleccion-boletos::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #d4af37, #1b5e20, #d4af37);
}

.section-title {
    font-size: clamp(1.6rem, 3vw, 2rem);
    color: #1b5e20;
    margin-bottom: 2rem;
    text-align: center;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

/* ====== CONTROLES ====== */
.controls-section {
    margin: 2rem 0;
    text-align: center;
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #495057);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 700;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #495057, #343a40);
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(108, 117, 125, 0.4);
}

/* ====== LEYENDA ====== */
.legend {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    margin: 2rem 0;
    flex-wrap: wrap;
    padding: 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 16px;
    border: 1px solid #e9ecef;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    font-size: 0.95rem;
    font-weight: 600;
    color: #495057;
}

.legend-color {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    border: 2px solid;
}

/* ====== SEARCH BOX ====== */
.search-box {
    display: flex;
    gap: 1rem;
    margin: 1.5rem 0;
    align-items: center;
}

.search-input {
    flex: 1;
    padding: 1.2rem 1.5rem;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    font-family: inherit;
    background: white;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #17a2b8;
    box-shadow: 0 0 0 4px rgba(23, 162, 184, 0.15);
}

.btn-buscar {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    padding: 1.2rem 2rem;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 700;
    transition: all 0.3s ease;
    box-shadow: 0 6px 20px rgba(23, 162, 184, 0.3);
}

.btn-buscar:hover {
    background: linear-gradient(135deg, #138496, #117a8b);
    transform: translateY(-3px);
}

/* ====== GRID DE BOLETOS ====== */
.combos-title {
    color: #2c2c2c;
    margin-bottom: 1.5rem;
    font-size: 1.3rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.boletos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(65px, 1fr));
    gap: 10px;
    max-height: 450px;
    overflow-y: auto;
    padding: 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 16px;
    border: 1px solid #e9ecef;
    scroll-behavior: smooth;
}

.boleto-item {
    width: 65px;
    height: 65px;
    border: 2px solid #ddd;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-weight: 700;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    background: white;
    user-select: none;
    touch-action: manipulation;
}

.boleto-disponible {
    border-color: #28a745;
    color: #155724;
    background: white;
}

.boleto-disponible:hover {
    background: #d4edda;
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    z-index: 2;
}

.boleto-seleccionado {
    background: linear-gradient(135deg, #d4af37, #f4e794);
    color: white;
    border-color: #b8941f;
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
    z-index: 3;
}

.boleto-apartado {
    background: #fff3e0;
    color: #e65100;
    border-color: #ffcc02;
    cursor: not-allowed;
    opacity: 0.7;
}

.boleto-pagado {
    background: linear-gradient(135deg, #ff4757, #ff3742);
    color: white;
    border-color: #ff3742;
    cursor: not-allowed;
    opacity: 0.9;
    font-weight: 800;
    box-shadow: 0 4px 15px rgba(255, 71, 87, 0.3);
}

/* ====== SIDEBAR ====== */
.sidebar {
    background: white;
    border-radius: 24px;
    padding: 3rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.12);
    border: 1px solid rgba(212, 175, 55, 0.2);
    height: fit-content;
    position: sticky;
    top: 120px;
    overflow: hidden;
}

.sidebar::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #28a745, #20c997, #28a745);
}

/* ====== COMBOS COMPACTOS ====== */
.combos-compactos {
    margin-bottom: 2rem;
    border: 1px solid #e9ecef;
    border-radius: 16px;
    overflow: hidden;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.combos-header {
    padding: 1.2rem 1.5rem;
    background: linear-gradient(135deg, #d4af37, #f4e794);
    color: white;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 700;
    transition: all 0.3s ease;
}

.combos-header:hover {
    background: linear-gradient(135deg, #b8941f, #d4af37);
}

.combos-dropdown {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    background: white;
}

.combos-dropdown.open {
    max-height: 300px;
}

.combo-item {
    padding: 1.2rem 1.5rem;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.combo-item:hover {
    background: #f8f9fa;
}

.combo-info {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.combo-cantidad-compacto {
    font-weight: 800;
    color: #2c2c2c;
    font-size: 1rem;
}

.combo-precio-compacto {
    font-size: 0.9rem;
    color: #666;
    font-weight: 600;
}

.combo-descuento {
    font-size: 0.8rem;
    color: #28a745;
    font-weight: 700;
}

.combo-btn-compacto {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    padding: 0.6rem 1rem;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 80px;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.combo-btn-compacto:hover {
    background: linear-gradient(135deg, #20c997, #17a2b8);
    transform: translateY(-2px);
}

/* ====== INFORMACIÓN COMPACTA ====== */
.seleccionados-info-compacta {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 1.5rem;
    border-radius: 12px;
    margin: 1rem 0;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    position: sticky;
    top: 10px;
    z-index: 10;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.8rem;
    padding: 0.8rem;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.info-row:last-child {
    margin-bottom: 0;
    background: linear-gradient(135deg, #d4af37, #f4e794);
    color: white;
    font-weight: 800;
    font-size: 1.1rem;
}

.info-label-compacta {
    font-weight: 600;
    color: #495057;
}

.info-value-compacta {
    font-weight: 700;
    color: #28a745;
    font-size: 1.1rem;
}

/* ====== LISTA DE BOLETOS SELECCIONADOS ====== */
.boletos-seleccionados-lista {
    max-height: 150px;
    overflow-y: auto;
    background: white;
    padding: 1rem;
    border-radius: 12px;
    margin-top: 1rem;
    border: 1px solid #e9ecef;
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #d4af37;
    font-size: 0.9rem;
    line-height: 1.6;
    scroll-behavior: smooth;
}

/* ====== BOTÓN COMPRAR ====== */
.btn-comprar {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 1.8rem 2rem;
    border: none;
    border-radius: 15px;
    font-size: 1.2rem;
    font-weight: 800;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
}

.btn-comprar:hover {
    background: linear-gradient(135deg, #20c997, #17a2b8);
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(40, 167, 69, 0.4);
}

.btn-comprar:disabled {
    background: #6c757d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* ====== INFO CARD ====== */
.info-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 2rem;
    border-radius: 16px;
    text-align: center;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    margin: 1.5rem 0;
}

.info-number {
    font-size: 2.2rem;
    font-weight: 800;
    color: #d4af37;
    margin-bottom: 0.5rem;
}

.info-label {
    color: #666;
    font-weight: 600;
    font-size: 1rem;
}

/* ====== INFORMACIÓN IMPORTANTE ====== */
.importante-info {
    margin-top: 1.5rem;
    padding: 1.5rem;
    background: rgba(255, 193, 7, 0.1);
    border: 1px solid rgba(255, 193, 7, 0.3);
    border-radius: 15px;
    font-size: 0.9rem;
    color: #856404;
    font-weight: 600;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

/* ====== MODALES ====== */
.modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(212, 175, 55, 0.15);
    backdrop-filter: blur(10px);
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 25px;
    padding: 3rem;
    text-align: center;
    max-width: 650px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
    scroll-behavior: smooth;
}

.ruleta-gif {
    width: 320px;
    height: 320px;
    border-radius: 50%;
    box-shadow: 0 15px 40px rgba(212, 175, 55, 0.4);
    border: 4px solid #d4af37;
}

.boletos-ganados {
    background: linear-gradient(135deg, #d4af37, #f4e794);
    color: white;
    padding: 2.5rem;
    border-radius: 20px;
    margin: 2rem 0;
    box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3);
}

.boletos-lista {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
    margin-top: 1.5rem;
}

.boleto-ganado {
    background: white;
    color: #d4af37;
    padding: 1.2rem;
    border-radius: 12px;
    font-weight: 800;
    font-size: 1.3rem;
    min-width: 90px;
    text-align: center;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

/* ====== MODAL DE PAGO ====== */
.modal-pago {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(212, 175, 55, 0.15);
    backdrop-filter: blur(10px);
}

.modal-pago-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 25px;
    padding: 3rem;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
    scroll-behavior: smooth;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.modal-title {
    font-size: 2rem;
    font-weight: 800;
    color: #2c2c2c;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #666;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.3s ease;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    background: #f8f9fa;
    color: #dc3545;
}

.resumen-compra {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    border: 1px solid #e9ecef;
}

.resumen-titulo {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2c2c2c;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.boletos-resumen {
    background: white;
    padding: 1rem;
    border-radius: 12px;
    margin: 1rem 0;
    border: 1px solid #e9ecef;
    max-height: 120px;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #d4af37;
    font-size: 0.9rem;
    line-height: 1.6;
    scroll-behavior: smooth;
}

/* ====== FORMULARIO ====== */
.form-pago {
    margin-bottom: 2rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.form-group-completo {
    margin-bottom: 1.5rem;
}

.form-group-completo label {
    display: block;
    margin-bottom: 0.8rem;
    color: #1b5e20;
    font-weight: 700;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-group-completo input, 
.form-group-completo select {
    width: 100%;
    padding: 1.2rem 1.5rem;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    font-family: inherit;
    background: white;
    transition: all 0.3s ease;
}

.form-group-completo input:focus,
.form-group-completo select:focus {
    outline: none;
    border-color: #28a745;
    box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.15);
}

.btn-confirmar-compra {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 1.8rem 2rem;
    border: none;
    border-radius: 15px;
    font-size: 1.2rem;
    font-weight: 800;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
}

.btn-confirmar-compra:hover {
    background: linear-gradient(135deg, #20c997, #17a2b8);
    transform: translateY(-3px);
}

.btn-confirmar-compra:disabled {
    background: #6c757d;
    cursor: not-allowed;
    transform: none;
}

.importante-modal {
    margin-top: 1.5rem;
    padding: 1.5rem;
    background: rgba(255, 193, 7, 0.1);
    border: 1px solid rgba(255, 193, 7, 0.3);
    border-radius: 15px;
    font-size: 0.9rem;
    color: #856404;
    font-weight: 600;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

/* ====== SCROLLBAR PERSONALIZADO ====== */
::-webkit-scrollbar {
    width: 12px;
}

::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #d4af37, #b8941f);
    border-radius: 10px;
    border: 2px solid #f8f9fa;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #b8941f, #996f1a);
}

/* ====== ANIMACIONES ====== */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.8);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes slideUpMobile {
    from {
        opacity: 0;
        transform: translateY(100px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideUpFromBottom {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

@keyframes floating {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

@keyframes pulseSuccess {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes confetti-fall {
    0% {
        transform: translateY(-100vh) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
    }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@keyframes fadeOut {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-20px);
    }
}

/* ====== ESTADOS DE CARGA ====== */
.btn-confirmar-compra.loading {
    background: #6c757d !important;
    cursor: wait !important;
    position: relative;
}

.btn-confirmar-compra.loading::after {
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

/* ====== BOLETOS DESTACADOS ====== */
.boleto-destacado {
    position: relative;
    z-index: 10;
    animation: pulse 1s ease-in-out 3;
    border-color: #17a2b8 !important;
    box-shadow: 0 0 15px #17a2b8 !important;
}

.boleto-destacado::after {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, #17a2b8, transparent, #17a2b8);
    border-radius: 14px;
    z-index: -1;
    animation: rotateBorder 2s linear infinite;
}

@keyframes rotateBorder {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.confetti {
    position: fixed;
    width: 10px;
    height: 10px;
    background: #d4af37;
    animation: confetti-fall 3s linear infinite;
    pointer-events: none;
    z-index: 10000;
}

/* ====== RESPONSIVE DESIGN ====== */
@media (max-width: 1200px) {
    .container {
        padding: 1rem;
    }
    
    .compra-section {
        grid-template-columns: 1fr 350px;
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    /* Mostrar elementos móviles */
    .mobile-floating-info {
        display: none; /* Se mostrará con JS cuando haya selección */
    }
    
    .mobile-suerte-btn {
        display: block;
    }
    
    /* Ocultar sidebar en móvil */
    .sidebar {
        display: none;
    }
    
    /* Ajustar espacio inferior para los widgets flotantes */
    .container {
        padding-bottom: 150px;
    }
    
    .header {
        padding: 0.8rem 0;
    }
    
    .navbar {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
        padding: 0 15px;
    }
    
    .logo-img {
        width: 35px;
        height: 35px;
    }
    
    .brand-text {
        font-size: 1rem;
    }
    
    .back-link {
        padding: 0.4rem 0.8rem;
        font-size: 0.9rem;
    }
    
    .container {
        padding: 10px 10px 120px 10px;
    }
    
    .rifa-header {
        padding: 2rem 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .rifa-image-large {
        width: 150px;
        height: 150px;
        margin-bottom: 1.5rem;
    }
    
    .rifa-title {
        font-size: 1.8rem;
        margin-bottom: 0.8rem;
    }
    
    .rifa-prize {
        padding: 1.5rem;
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
    }
    
    .progress-info {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
        font-size: 0.85rem;
    }
    
    .progress-bar-container {
        height: 18px;
    }
    
    .progress-text {
        font-size: 0.75rem;
    }
    
    .compra-section {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .seleccion-boletos {
        padding: 2rem 1.5rem;
    }
    
    .section-title {
        font-size: 1.6rem;
        margin-bottom: 1.5rem;
    }
    
    .controls-section {
        flex-direction: column;
        gap: 0.8rem;
    }
    
    .btn-secondary {
        padding: 0.8rem 1.5rem;
        font-size: 0.9rem;
        width: 100%;
    }
    
    .search-box {
        flex-direction: column;
        gap: 0.8rem;
    }
    
    .search-input {
        padding: 0.8rem;
        font-size: 0.9rem;
    }
    
    .btn-buscar {
        width: 100%;
        padding: 0.8rem;
        font-size: 0.9rem;
    }
    
    .legend {
        gap: 1rem;
        padding: 1rem;
        margin: 1.5rem 0;
    }
    
    .legend-item {
        font-size: 0.85rem;
        gap: 0.6rem;
    }
    
    .legend-color {
        width: 20px;
        height: 20px;
    }
    
    .boletos-grid {
        grid-template-columns: repeat(5, 1fr);
        gap: 8px;
        max-height: 400px;
        padding: 1rem;
        margin: 0 -0.5rem;
    }
    
    .boleto-item {
        width: 100%;
        height: 50px;
        font-size: 0.8rem;
        border-radius: 8px;
        min-height: 50px;
    }
    
    .modal-content,
    .modal-pago-content {
        width: 95%;
        padding: 2rem 1.5rem;
        max-height: 90vh;
        border-radius: 20px;
    }
    
    .modal-title {
        font-size: 1.5rem;
    }
    
    .resumen-compra {
        padding: 1.2rem;
    }
    
    .btn-confirmar-compra {
        padding: 1.3rem;
        font-size: 1rem;
    }
    
    .ruleta-gif {
        width: 200px;
        height: 200px;
    }
    
    .boleto-ganado {
        padding: 0.8rem;
        font-size: 1rem;
        min-width: 60px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 8px 8px 120px 8px;
    }
    
    .mobile-floating-info {
        left: 10px;
        right: 10px;
        bottom: 15px;
        padding: 0.8rem;
    }
    
    .floating-content {
        gap: 0.8rem;
    }
    
    .floating-comprar-btn {
        padding: 0.7rem 1.2rem;
        font-size: 0.85rem;
        min-width: 90px;
    }
    
    .boletos-grid {
        gap: 6px;
        padding: 0.8rem;
        margin: 0 -0.3rem;
        max-height: 350px;
    }
    
    .boleto-item {
        height: 45px;
        font-size: 0.75rem;
        border-radius: 6px;
        min-height: 45px;
    }
    
    .rifa-header {
        padding: 1.5rem 1rem;
    }
    
    .rifa-image-large {
        width: 120px;
        height: 120px;
    }
    
    .rifa-title {
        font-size: 1.5rem;
    }
    
    .seleccion-boletos {
        padding: 1.5rem 1rem;
    }
    
    .section-title {
        font-size: 1.4rem;
    }
}
/* ====== INFORMACIÓN COMPACTA FIJA EN MODAL ====== */
.modal-pago-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 25px;
    padding: 3rem;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
    scroll-behavior: smooth;
}

/* Hacer que la información compacta se quede fija */
.seleccionados-info-compacta.fija {
    position: sticky;
    top: 0;
    z-index: 100;
    background: white;
    margin: -3rem -3rem 1.5rem -3rem; /* Extender hasta los bordes */
    padding: 2rem 3rem 1.5rem 3rem;
    border-radius: 25px 25px 0 0;
    border-bottom: 2px solid #e9ecef;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Responsive para móvil */
@media (max-width: 768px) {
    .modal-pago-content {
        padding: 2rem 1.5rem;
    }
    
    .seleccionados-info-compacta.fija {
        margin: -2rem -1.5rem 1rem -1.5rem;
        padding: 1.5rem 1.5rem 1rem 1.5rem;
        border-radius: 20px 20px 0 0;
    }
}

@media (max-width: 480px) {
    .modal-pago-content {
        padding: 1.5rem 1rem;
    }
    
    .seleccionados-info-compacta.fija {
        margin: -1.5rem -1rem 1rem -1rem;
        padding: 1rem;
        border-radius: 20px 20px 0 0;
    }
}

    </style>
</head>

<body>
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo-section">
                <img src="logo.jpg" alt="Éxito Dorado MX" class="logo-img">
                <div class="brand-text">ÉXITO DORADO MX</div>
            </a>
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Volver a Rifas
            </a>
        </nav>
    </header>
    
    <div class="container">
        <!-- Alertas de error -->
        <?php if ($error): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Header de la rifa -->
        <div class="rifa-header">
            <div class="rifa-image-large">
                <?php if (!empty($rifa['imagen_premio']) && file_exists("uploads/rifas/" . $rifa['imagen_premio'])): ?>
                    <img src="uploads/rifas/<?php echo htmlspecialchars($rifa['imagen_premio']); ?>" 
                         alt="<?php echo htmlspecialchars($rifa['nombre']); ?>">
                <?php else: ?>
                    <div class="default-icon"><i class="fas fa-gift"></i></div>
                <?php endif; ?>
            </div>
            
            <h1 class="rifa-title"><?php echo htmlspecialchars($rifa['nombre']); ?></h1>
            
            <?php if (!empty($rifa['descripcion_premio'])): ?>
                <div class="rifa-prize">
                    <i class="fas fa-trophy"></i>
                    <?php echo htmlspecialchars($rifa['descripcion_premio']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Barra de progreso -->
            <?php 
            $total_boletos = $rifa['total_boletos'];
            $disponibles = count(array_filter($boletos_estado, function($estado) { return $estado === 'disponible'; }));
            $vendidos = $total_boletos - $disponibles;
            $porcentaje_vendido = ($vendidos / $total_boletos) * 100;
            ?>
            
            <div class="progress-section">
                <div class="progress-info">
                    <span><strong><?php echo number_format($vendidos); ?></strong> vendidos de <strong><?php echo number_format($total_boletos); ?></strong></span>
                    <span style="color: #28a745;"><strong><?php echo number_format($disponibles); ?></strong> disponibles</span>
                </div>
                
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?php echo number_format($porcentaje_vendido, 1); ?>%"></div>
                    <div class="progress-text">
                        <?php echo number_format($porcentaje_vendido, 1); ?>% vendido
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Widget móvil flotante -->
        <div class="mobile-floating-info" id="mobileFloatingInfo" style="display: none;">
            <div class="floating-content">
                <div class="floating-row">
                    <span class="floating-label">Seleccionados:</span>
                    <span class="floating-value" id="mobileSeleccionados">0</span>
                </div>
                <div class="floating-row">
                    <span class="floating-label">Total:</span>
                    <span class="floating-total" id="mobileTotalPrecio">$0.00</span>
                </div>
                <button class="floating-comprar-btn" id="mobileComprarBtn" onclick="abrirModalPago()" disabled>
                    <i class="fas fa-shopping-cart"></i>
                    Comprar
                </button>
            </div>
        </div>
        
        <!-- Botón de máquina de la suerte móvil -->
        <div class="mobile-suerte-btn" id="mobileSuerteBtn" style="display: none;">
            <button class="btn-suerte-movil" onclick="mostrarCombosMobiles()">
                <i class="fas fa-dice"></i>
                <span>Máquina de la Suerte</span>
            </button>
        </div>
        
        <!-- Modal de combos móvil -->
        <div id="modalCombosMobile" class="modal-combos-mobile" style="display: none;">
            <div class="modal-combos-content">
                <div class="modal-combos-header">
                    <h3><i class="fas fa-bolt"></i> Máquina de la Suerte</h3>
                    <button class="modal-combos-close" onclick="cerrarCombosMobiles()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <p class="combos-descripcion">¡Deja que la suerte elija tus boletos!</p>
                
                <div class="combos-mobile-lista">
                    <?php foreach ($combos as $combo): ?>
                    <div class="combo-mobile-item" onclick="seleccionarComboMobile(<?php echo $combo['cantidad_boletos']; ?>)">
                        <div class="combo-mobile-info">
                            <div class="combo-mobile-cantidad">🎲 <?php echo $combo['cantidad_boletos']; ?> boletos</div>
                            <div class="combo-mobile-precio">💰 $<?php echo number_format($combo['precio'], 2); ?></div>
                            <?php if ($combo['precio'] < ($combo['cantidad_boletos'] * $rifa['precio_boleto'])): ?>
                                <?php $descuento = (($combo['cantidad_boletos'] * $rifa['precio_boleto']) - $combo['precio']); ?>
                                <div class="combo-mobile-descuento">🎁 Ahorras $<?php echo number_format($descuento, 2); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="combo-mobile-btn">
                            <i class="fas fa-dice"></i>
                            Probar Suerte
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="combo-personalizado">
                    <h4><i class="fas fa-magic"></i> Cantidad Personalizada</h4>
                    <div class="cantidad-personalizada-input">
                        <input type="number" id="cantidadPersonalizada" min="1" max="<?php echo $rifa['total_boletos']; ?>" placeholder="¿Cuántos boletos?">
                        <button onclick="seleccionarComboPersonalizado()" class="btn-personalizado">
                            <i class="fas fa-star"></i>
                            ¡A la Suerte!
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sección principal de compra -->
        <div class="compra-section">
            <!-- Selección de boletos -->
            <div class="seleccion-boletos">
                <h2 class="section-title">
                    <i class="fas fa-bullseye"></i>
                    Selecciona Tus Boletos
                </h2>
                
                <!-- Controles de selección -->
                <div class="controls-section">
                    <button class="btn-secondary" onclick="seleccionarTodos()">
                        <i class="fas fa-check-double"></i>
                        Seleccionar Todos
                    </button>
                    <button class="btn-secondary" onclick="limpiarSeleccion()">
                        <i class="fas fa-eraser"></i>
                        Limpiar Selección
                    </button>
                </div>
                
                <!-- Leyenda de estados -->
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: white; border-color: #28a745;"></div>
                        <span>Disponible</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: linear-gradient(135deg, #d4af37, #f4e794); border-color: #b8941f;"></div>
                        <span>Seleccionado</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #fff3e0; border-color: #ffcc02;"></div>
                        <span>Apartado</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: linear-gradient(135deg, #ff4757, #ff3742); border-color: #ff3742;"></div>
                        <span>Pagado</span>
                    </div>
                </div>
                
                <!-- Buscador de números -->
                <div class="search-box">
                    <input type="text" 
                           class="search-input" 
                           id="searchInput" 
                           placeholder="Buscar números: 1, 5-10, 123..."
                           onkeypress="if(event.key==='Enter') buscarNumeros()">
                    
                    <button class="btn-buscar" onclick="buscarNumeros()">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
                
                <!-- Selección manual de boletos -->
                <div class="seleccion-manual">
                    <h3 class="combos-title">
                        <i class="fas fa-ticket-alt"></i>
                        Selección Manual
                    </h3>
                    <div class="boletos-grid" id="boletosGrid">
                        <?php for ($i = 1; $i <= $rifa['total_boletos']; $i++): ?>
                            <?php 
                            $numero_formateado = str_pad($i, $digitos, '0', STR_PAD_LEFT);
                            $estado = $boletos_estado[$i] ?? 'disponible';
                            $clase_estado = 'boleto-' . $estado;
                            ?>
                            <div class="boleto-item <?php echo $clase_estado; ?>" 
                                 data-numero="<?php echo $i; ?>" 
                                 data-estado="<?php echo $estado; ?>"
                                 onclick="toggleBoleto(<?php echo $i; ?>)">
                                <?php echo $numero_formateado; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="sidebar">
                <h2 class="section-title">
                    <i class="fas fa-shopping-cart"></i>
                    Tu Compra
                </h2>
                
                <!-- Combos rápidos compactos -->
                <div class="combos-compactos">
                    <div class="combos-header" onclick="toggleCombos()">
                        <span><i class="fas fa-bolt"></i> Combos Rápidos</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="combos-dropdown" id="combosDropdown">
                        <?php foreach ($combos as $combo): ?>
                        <div class="combo-item" onclick="seleccionarCombo(<?php echo $combo['cantidad_boletos']; ?>)">
                            <div class="combo-info">
                                <div class="combo-cantidad-compacto"><?php echo $combo['cantidad_boletos']; ?> boletos</div>
                                <div class="combo-precio-compacto">$<?php echo number_format($combo['precio'], 2); ?></div>
                                <?php if ($combo['precio'] < ($combo['cantidad_boletos'] * $rifa['precio_boleto'])): ?>
                                    <?php $descuento = (($combo['cantidad_boletos'] * $rifa['precio_boleto']) - $combo['precio']); ?>
                                    <div class="combo-descuento">Ahorras $<?php echo number_format($descuento, 2); ?></div>
                                <?php endif; ?>
                            </div>
                            <button class="combo-btn-compacto">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Información de selección compacta -->
                <div class="seleccionados-info-compacta">
                    <div class="info-row">
                        <span class="info-label-compacta">Boletos seleccionados:</span>
                        <span class="info-value-compacta" id="totalSeleccionados">0</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label-compacta">Precio por boleto:</span>
                        <span class="info-value-compacta">$<?php echo number_format($rifa['precio_boleto'], 2); ?></span>
                    </div>
                    <div class="info-row">
                        <span>TOTAL A PAGAR:</span>
                        <span id="totalPrecio">$0.00</span>
                    </div>
                </div>
                
                <!-- Lista de boletos seleccionados -->
                <div class="boletos-seleccionados-lista" id="boletosSeleccionadosLista" style="display: none;">
                    <strong>Boletos seleccionados:</strong>
                    <div id="listaNumeros"></div>
                </div>
                
                <!-- Botón de comprar -->
                <button type="button" class="btn-comprar" id="btnAbrirPago" onclick="abrirModalPago()" disabled>
                    <i class="fas fa-shopping-cart"></i>
                    Comprar Boletos
                </button>
                
                <!-- Información de fecha de entrega -->
                <div class="info-card">
                    <div class="info-number"><?php echo date('d/m/Y', strtotime($rifa['fecha_entrega'])); ?></div>
                    <div class="info-label">Fecha de Entrega</div>
                </div>
                
                <!-- Información importante -->
                <div class="importante-info">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Importante:</strong> Al completar la compra, tus boletos quedarán apartados. Recibirás la información de pago por WhatsApp.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Ruleta -->
    <div id="ruletaModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2 id="modalTitle" style="color: #d4af37; font-size: 2.2rem; margin-bottom: 1rem; font-weight: 800;">
                <i class="fas fa-dice"></i> ¡La Ruleta Está Girando!
            </h2>
            
            <div class="ruleta-container">
                <img src="ruleta.gif" alt="Ruleta de la Suerte" class="ruleta-gif" id="ruletaGif">
            </div>
            
            <div class="boletos-ganados" id="boletosGanados" style="display: none;">
                <h3 style="margin-bottom: 1rem; font-size: 1.5rem; font-weight: 800;">
                    <i class="fas fa-ticket-alt"></i> Tus Boletos de la Suerte
                </h3>
                <div class="boletos-lista" id="boletosGanadosLista"></div>
                
                <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(255,255,255,0.2); border-radius: 15px;">
                    <p style="font-size: 1.1rem; margin-bottom: 1rem;">
                        <i class="fas fa-bullseye"></i> ¡La ruleta ha elegido tus números!
                    </p>
                    <p style="font-weight: bold; font-size: 1.2rem;">¿Te gustan estos boletos? 🤔</p>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem; flex-wrap: wrap;">
                    <button onclick="confirmarSeleccionCombo()" style="background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 1rem 2rem; border: none; border-radius: 15px; font-weight: bold; cursor: pointer; font-size: 1.1rem; min-width: 160px; transition: all 0.3s ease;">
                        <i class="fas fa-check"></i> Confirmar Boletos
                    </button>
                    <button onclick="volverAGirar()" style="background: linear-gradient(135deg, #ff9800, #f57c00); color: white; padding: 1rem 2rem; border: none; border-radius: 15px; font-weight: bold; cursor: pointer; font-size: 1.1rem; min-width: 160px; transition: all 0.3s ease;">
                        <i class="fas fa-redo"></i> Girar de Nuevo
                    </button>
                    <button onclick="cerrarModal()" style="background: linear-gradient(135deg, #6c757d, #495057); color: white; padding: 1rem 2rem; border: none; border-radius: 15px; font-weight: bold; cursor: pointer; font-size: 1.1rem; min-width: 160px; transition: all 0.3s ease;">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
   <!-- Modal de Pago -->
<div id="modalPago" class="modal-pago" style="display: none;">
    <div class="modal-pago-content">
        <!-- HEADER FIJO (no baja) -->
        <div class="modal-header-con-info">
            <div class="modal-header">
                <h2 class="modal-title">
                    <i class="fas fa-credit-card"></i>
                    Confirmar Compra
                </h2>
                <button class="modal-close" onclick="cerrarModalPago()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Información compacta de selección - ESTA PARTE NO BAJA -->
            <div class="seleccionados-info-compacta fija">
                <div class="info-row">
                    <span class="info-label-compacta">Boletos seleccionados:</span>
                    <span class="info-value-compacta" id="resumenCantidadCompacta">0</span>
                </div>
                <div class="info-row">
                    <span class="info-label-compacta">Precio por boleto:</span>
                    <span class="info-value-compacta">$<?php echo number_format($rifa['precio_boleto'], 2); ?></span>
                </div>
                <div class="info-row">
                    <span>TOTAL A PAGAR:</span>
                    <span id="resumenTotalCompacto">$0.00</span>
                </div>
            </div>
        </div>
        
        <!-- CONTENIDO QUE SÍ HACE SCROLL -->
        <div class="modal-contenido-scroll">
            <div class="resumen-compra">
                <h3 class="resumen-titulo">
                    <i class="fas fa-ticket-alt"></i>
                    Tus números seleccionados
                </h3>
                
                <div class="boletos-resumen" id="boletosResumen" style="display: none;">
                    <div id="numerosResumen"></div>
                </div>
            </div>
            
            <form class="form-pago" id="formPagoModal" method="POST">
                <div class="form-row">
                    <div class="form-group-completo">
                        <label for="nombreModal">
                            <i class="fas fa-user"></i>
                            Nombre(s):
                        </label>
                        <input type="text" id="nombreModal" name="nombre" required placeholder="Ej: Juan Carlos">
                    </div>
                    
                    <div class="form-group-completo">
                        <label for="apellidosModal">
                            <i class="fas fa-user-tag"></i>
                            Apellidos:
                        </label>
                        <input type="text" id="apellidosModal" name="apellidos" required placeholder="Ej: García López">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group-completo">
                        <label for="telefonoModal">
                            <i class="fas fa-phone"></i>
                            Teléfono:
                        </label>
                        <input type="tel" id="telefonoModal" name="telefono" required placeholder="81 8094 6816">
                    </div>
                    
                    <div class="form-group-completo">
                        <label for="estadoModal">
                            <i class="fas fa-map-marker-alt"></i>
                            Estado:
                        </label>
                        <select id="estadoModal" name="estado" required>
                            <option value="">Selecciona tu estado</option>
                            <option value="Aguascalientes">Aguascalientes</option>
                            <option value="Baja California">Baja California</option>
                            <option value="Baja California Sur">Baja California Sur</option>
                            <option value="Campeche">Campeche</option>
                            <option value="Chiapas">Chiapas</option>
                            <option value="Chihuahua">Chihuahua</option>
                            <option value="Ciudad de México">Ciudad de México</option>
                            <option value="Coahuila">Coahuila</option>
                            <option value="Colima">Colima</option>
                            <option value="Durango">Durango</option>
                            <option value="Estado de México">Estado de México</option>
                            <option value="Guanajuato">Guanajuato</option>
                            <option value="Guerrero">Guerrero</option>
                            <option value="Hidalgo">Hidalgo</option>
                            <option value="Jalisco">Jalisco</option>
                            <option value="Michoacán">Michoacán</option>
                            <option value="Morelos">Morelos</option>
                            <option value="Nayarit">Nayarit</option>
                            <option value="Nuevo León">Nuevo León</option>
                            <option value="Oaxaca">Oaxaca</option>
                            <option value="Puebla">Puebla</option>
                            <option value="Querétaro">Querétaro</option>
                            <option value="Quintana Roo">Quintana Roo</option>
                            <option value="San Luis Potosí">San Luis Potosí</option>
                            <option value="Sinaloa">Sinaloa</option>
                            <option value="Sonora">Sonora</option>
                            <option value="Tabasco">Tabasco</option>
                            <option value="Tamaulipas">Tamaulipas</option>
                            <option value="Tlaxcala">Tlaxcala</option>
                            <option value="Veracruz">Veracruz</option>
                            <option value="Yucatán">Yucatán</option>
                            <option value="Zacatecas">Zacatecas</option>
                        </select>
                    </div>
                </div>
                
                <input type="hidden" name="boletos_seleccionados" id="boletosModalInput">
                <input type="hidden" name="comprar" value="1">
                
                <button type="submit" name="comprar" class="btn-confirmar-compra" id="btnConfirmarCompra">
                    <i class="fas fa-check-circle"></i>
                    Confirmar y Apartar Boletos
                </button>
            </form>
            
            <div class="importante-modal">
                <i class="fas fa-whatsapp"></i>
                <div>
                    Después de confirmar, te enviaremos la información de pago por WhatsApp para completar tu compra.
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Aquí puedes agregar tu JavaScript -->
    <script>
// ====== CONFIGURACIÓN INICIAL ======
let boletosSeleccionados = [];
let boletosTemporales = [];
let cantidadComboActual = 0;
let isSelecting = false;

// Obtener datos de PHP
const precioBoleto = window.precioBoleto;
const totalBoletosRifa = window.totalBoletosRifa;
const digitos = window.digitos;

console.log('🎯 Datos cargados:', { precioBoleto, totalBoletosRifa, digitos });

// ====== FUNCIONES PRINCIPALES ======
function toggleBoleto(numero) {
    if (isSelecting) return;
    
    isSelecting = true;
    setTimeout(() => isSelecting = false, 200);
    
    const boleto = document.querySelector(`[data-numero="${numero}"]`);
    if (!boleto) {
        console.error('Boleto no encontrado:', numero);
        return;
    }
    
    const estado = boleto.dataset.estado;
    
    if (estado !== 'disponible') {
        mostrarAlerta('Este boleto no está disponible', 'error');
        return;
    }
    
    const index = boletosSeleccionados.indexOf(numero);
    
    if (index > -1) {
        // Deseleccionar
        boletosSeleccionados.splice(index, 1);
        boleto.classList.remove('boleto-seleccionado');
        boleto.classList.add('boleto-disponible');
    } else {
        // Seleccionar
        boletosSeleccionados.push(numero);
        boleto.classList.remove('boleto-disponible');
        boleto.classList.add('boleto-seleccionado');
    }
    
    actualizarResumen();
}

function actualizarResumen() {
    const total = boletosSeleccionados.length;
    const precio = total * precioBoleto;
    
    console.log('📊 Actualizando:', { total, precio, precioBoleto });
    
    // Desktop
    const totalEl = document.getElementById('totalSeleccionados');
    const precioEl = document.getElementById('totalPrecio');
    const btnComprar = document.getElementById('btnAbrirPago');
    
    if (totalEl) totalEl.textContent = total;
    if (precioEl) precioEl.textContent = `$${precio.toFixed(2)}`;
    if (btnComprar) {
        btnComprar.disabled = total === 0;
        btnComprar.style.background = total > 0 ? 'linear-gradient(135deg, #28a745, #20c997)' : '#6c757d';
    }
    
    // Móvil
    const mobileTotal = document.getElementById('mobileSeleccionados');
    const mobilePrecio = document.getElementById('mobileTotalPrecio');
    const mobileBtn = document.getElementById('mobileComprarBtn');
    const mobileWidget = document.getElementById('mobileFloatingInfo');
    const mobileSuerteBtn = document.getElementById('mobileSuerteBtn');
    
    if (mobileTotal) mobileTotal.textContent = total;
    if (mobilePrecio) mobilePrecio.textContent = `$${precio.toFixed(2)}`;
    if (mobileBtn) {
        mobileBtn.disabled = total === 0;
        mobileBtn.style.background = total > 0 ? 'linear-gradient(135deg, #28a745, #20c997)' : '#6c757d';
    }
    
    // Gestión de widget móvil
    if (esMobile()) {
        if (mobileSuerteBtn) mobileSuerteBtn.style.display = 'block';
        
        if (mobileWidget) {
            if (total > 0) {
                mobileWidget.style.display = 'block';
                if (mobileSuerteBtn) mobileSuerteBtn.style.bottom = '160px';
            } else {
                mobileWidget.style.display = 'none';
                if (mobileSuerteBtn) mobileSuerteBtn.style.bottom = '90px';
            }
        }
    }
    
    // Lista de boletos seleccionados
    const lista = document.getElementById('listaNumeros');
    const container = document.getElementById('boletosSeleccionadosLista');
    
    if (total > 0 && lista && container) {
        container.style.display = 'block';
        const numeros = boletosSeleccionados
            .sort((a, b) => a - b)
            .map(num => String(num).padStart(digitos, '0'))
            .join(', ');
        lista.textContent = numeros;
    } else if (container) {
        container.style.display = 'none';
    }
}

function seleccionarTodos() {
    limpiarSeleccion();
    
    const disponibles = document.querySelectorAll('.boleto-disponible');
    if (disponibles.length === 0) {
        mostrarAlerta('No hay boletos disponibles', 'info');
        return;
    }
    
    if (disponibles.length > 100 && !confirm(`¿Estás seguro de seleccionar ${disponibles.length} boletos?`)) {
        return;
    }
    
    disponibles.forEach(boleto => {
        const numero = parseInt(boleto.dataset.numero);
        boletosSeleccionados.push(numero);
        boleto.classList.remove('boleto-disponible');
        boleto.classList.add('boleto-seleccionado');
    });
    
    actualizarResumen();
    mostrarAlerta(`${disponibles.length} boletos seleccionados`, 'success');
}

function limpiarSeleccion() {
    boletosSeleccionados.forEach(numero => {
        const boleto = document.querySelector(`[data-numero="${numero}"]`);
        if (boleto) {
            boleto.classList.remove('boleto-seleccionado');
            boleto.classList.add('boleto-disponible');
        }
    });
    
    boletosSeleccionados = [];
    actualizarResumen();
    mostrarAlerta('Selección eliminada', 'info');
}

// ====== FUNCIONES DE COMBOS ======
function seleccionarCombo(cantidad) {
    const disponibles = Array.from(document.querySelectorAll('.boleto-disponible'))
        .map(el => parseInt(el.dataset.numero))
        .filter(num => !boletosSeleccionados.includes(num));
    
    if (disponibles.length < cantidad) {
        mostrarAlerta(`Solo hay ${disponibles.length} boletos disponibles`, 'error');
        return;
    }
    
    cantidadComboActual = cantidad;
    mostrarRuletaParaCombo(cantidad, disponibles);
}

function toggleCombos() {
    const dropdown = document.getElementById('combosDropdown');
    if (!dropdown) return;
    
    dropdown.classList.toggle('open');
    
    const icon = dropdown.previousElementSibling?.querySelector('i:last-child');
    if (icon) {
        icon.style.transform = dropdown.classList.contains('open') ? 'rotate(180deg)' : 'rotate(0deg)';
    }
}

// ====== FUNCIONES MÓVILES ======
function mostrarCombosMobiles() {
    const modal = document.getElementById('modalCombosMobile');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        const input = document.getElementById('cantidadPersonalizada');
        if (input) input.value = '';
    }
}

function cerrarCombosMobiles() {
    const modal = document.getElementById('modalCombosMobile');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function seleccionarComboMobile(cantidad) {
    cerrarCombosMobiles();
    setTimeout(() => seleccionarCombo(cantidad), 300);
}

function seleccionarComboPersonalizado() {
    const input = document.getElementById('cantidadPersonalizada');
    if (!input) return;
    
    const cantidad = parseInt(input.value);
    if (!cantidad || cantidad < 1) {
        mostrarAlerta('Ingresa una cantidad válida', 'error');
        return;
    }
    
    if (cantidad > totalBoletosRifa) {
        mostrarAlerta(`La cantidad máxima es ${totalBoletosRifa}`, 'error');
        return;
    }
    
    cerrarCombosMobiles();
    setTimeout(() => seleccionarCombo(cantidad), 300);
}

// ====== FUNCIONES DE RULETA ======
function mostrarRuletaParaCombo(cantidad, disponibles) {
    const modal = document.getElementById('ruletaModal');
    const boletosGanados = document.getElementById('boletosGanados');
    const boletosLista = document.getElementById('boletosGanadosLista');
    const modalTitle = document.getElementById('modalTitle');
    
    if (!modal || !boletosGanados || !boletosLista || !modalTitle) {
        mostrarAlerta('Error: Modal no encontrado', 'error');
        return;
    }
    
    // Limpiar y mostrar modal
    boletosLista.innerHTML = '';
    boletosGanados.style.display = 'none';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    modalTitle.innerHTML = '<i class="fas fa-dice"></i> ¡La Ruleta Está Girando!';
    modalTitle.style.animation = 'pulse 1s infinite';
    
    // Crear confetti
    crearConfetti();
    
    // Generar números aleatorios
    const boletosAleatorios = [];
    const disponiblesCopia = [...disponibles];
    
    for (let i = 0; i < cantidad && disponiblesCopia.length > 0; i++) {
        const indice = Math.floor(Math.random() * disponiblesCopia.length);
        const numero = disponiblesCopia.splice(indice, 1)[0];
        boletosAleatorios.push(numero);
    }
    
    boletosTemporales = [...boletosAleatorios];
    
    // Mostrar resultados después de 3 segundos
    setTimeout(() => {
        modalTitle.innerHTML = '<i class="fas fa-star"></i> ¡Estos Son Tus Boletos de la Suerte!';
        modalTitle.style.animation = '';
        boletosGanados.style.display = 'block';
        
        boletosAleatorios.forEach((numero, index) => {
            setTimeout(() => {
                const div = document.createElement('div');
                div.className = 'boleto-ganado';
                div.textContent = String(numero).padStart(digitos, '0');
                
                div.style.opacity = '0';
                div.style.transform = 'scale(0) rotate(180deg)';
                div.style.transition = 'all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
                
                boletosLista.appendChild(div);
                
                setTimeout(() => {
                    div.style.opacity = '1';
                    div.style.transform = 'scale(1) rotate(0deg)';
                }, 50);
                
                if (navigator.vibrate && esMobile()) {
                    navigator.vibrate(50);
                }
            }, index * 300);
        });
    }, 3000);
}

function confirmarSeleccionCombo() {
    boletosTemporales.forEach(numero => {
        if (!boletosSeleccionados.includes(numero)) {
            const boleto = document.querySelector(`[data-numero="${numero}"]`);
            if (boleto && boleto.dataset.estado === 'disponible') {
                boletosSeleccionados.push(numero);
                boleto.classList.remove('boleto-disponible');
                boleto.classList.add('boleto-seleccionado');
            }
        }
    });
    
    actualizarResumen();
    cerrarModal();
    mostrarAlerta(`${boletosTemporales.length} boletos agregados exitosamente`, 'success');
    
    boletosTemporales = [];
    cantidadComboActual = 0;
}

function volverAGirar() {
    const disponibles = Array.from(document.querySelectorAll('.boleto-disponible'))
        .map(el => parseInt(el.dataset.numero))
        .filter(num => !boletosSeleccionados.includes(num));
    
    if (disponibles.length < cantidadComboActual) {
        mostrarAlerta(`Solo hay ${disponibles.length} boletos disponibles`, 'error');
        return;
    }
    
    mostrarRuletaParaCombo(cantidadComboActual, disponibles);
}

function cerrarModal() {
    const modal = document.getElementById('ruletaModal');
    if (modal) {
        modal.style.display = 'none';
    }
    document.body.style.overflow = '';
    boletosTemporales = [];
    cantidadComboActual = 0;
}

// ====== FUNCIONES DE BÚSQUEDA ======
function buscarNumeros() {
    const input = document.getElementById('searchInput');
    if (!input) return;
    
    const valor = input.value.trim();
    if (!valor) {
        mostrarAlerta('Ingresa números para buscar', 'info');
        return;
    }
    
    const numeros = [];
    const partes = valor.split(',');
    
    partes.forEach(parte => {
        parte = parte.trim();
        
        if (parte.includes('-')) {
            const [inicio, fin] = parte.split('-').map(n => parseInt(n));
            if (!isNaN(inicio) && !isNaN(fin)) {
                for (let i = Math.min(inicio, fin); i <= Math.max(inicio, fin); i++) {
                    if (i >= 1 && i <= totalBoletosRifa) {
                        numeros.push(i);
                    }
                }
            }
        } else {
            const num = parseInt(parte);
            if (!isNaN(num) && num >= 1 && num <= totalBoletosRifa) {
                numeros.push(num);
            }
        }
    });
    
    // Limpiar destacados anteriores
    document.querySelectorAll('.boleto-destacado').forEach(b => {
        b.classList.remove('boleto-destacado');
    });
    
    if (numeros.length > 0) {
        numeros.forEach((numero, index) => {
            setTimeout(() => {
                const boleto = document.querySelector(`[data-numero="${numero}"]`);
                if (boleto) {
                    boleto.classList.add('boleto-destacado');
                    if (index === 0) {
                        boleto.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            }, index * 100);
        });
        
        mostrarAlerta(`Encontrados ${numeros.length} números`, 'success');
        input.value = '';
        
        setTimeout(() => {
            document.querySelectorAll('.boleto-destacado').forEach(b => {
                b.classList.remove('boleto-destacado');
            });
        }, 5000);
    } else {
        mostrarAlerta('No se encontraron números válidos', 'error');
    }
}

// ====== FUNCIONES DE MODAL DE PAGO ======
function abrirModalPago() {
    if (boletosSeleccionados.length === 0) {
        mostrarAlerta('Debes seleccionar al menos un boleto', 'error');
        return;
    }
    
    const precio = boletosSeleccionados.length * precioBoleto;
    
    // Actualizar información compacta
    const resumenCantidad = document.getElementById('resumenCantidadCompacta');
    const resumenTotal = document.getElementById('resumenTotalCompacto');
    
    if (resumenCantidad) resumenCantidad.textContent = boletosSeleccionados.length;
    if (resumenTotal) resumenTotal.textContent = `$${precio.toFixed(2)}`;
    
    // Mostrar boletos seleccionados
    const boletosContainer = document.getElementById('boletosResumen');
    const numerosContainer = document.getElementById('numerosResumen');
    
    if (boletosContainer && numerosContainer) {
        boletosContainer.style.display = 'block';
        const numeros = boletosSeleccionados
            .sort((a, b) => a - b)
            .map(num => String(num).padStart(digitos, '0'))
            .join(', ');
        numerosContainer.textContent = numeros;
    }
    
    // Limpiar formulario
    const campos = ['nombreModal', 'apellidosModal', 'telefonoModal', 'estadoModal'];
    campos.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) campo.value = '';
    });
    
    // Actualizar campo oculto
    const boletosInput = document.getElementById('boletosModalInput');
    if (boletosInput) {
        boletosInput.value = JSON.stringify(boletosSeleccionados);
    }
    
    // Mostrar modal
    const modal = document.getElementById('modalPago');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            const nombreModal = document.getElementById('nombreModal');
            if (nombreModal) nombreModal.focus();
        }, 100);
    }
}

function cerrarModalPago() {
    const modal = document.getElementById('modalPago');
    if (modal) {
        modal.style.display = 'none';
    }
    document.body.style.overflow = '';
}

// ====== FUNCIONES DE VALIDACIÓN ======
function validarFormularioModalCompleto() {
    const nombreModal = document.getElementById('nombreModal');
    const apellidosModal = document.getElementById('apellidosModal');
    const telefonoModal = document.getElementById('telefonoModal');
    const estadoModal = document.getElementById('estadoModal');
    const btnConfirmar = document.getElementById('btnConfirmarCompra');
    
    if (!nombreModal || !apellidosModal || !telefonoModal || !estadoModal || !btnConfirmar) return;
    
    const tieneNombre = nombreModal.value.trim().length >= 2;
    const tieneApellidos = apellidosModal.value.trim().length >= 2;
    const tieneTelefono = telefonoModal.value.trim().length >= 10;
    const tieneEstado = estadoModal.value.trim().length > 0;
    const tieneBoletos = boletosSeleccionados.length > 0;
    
    const formularioCompleto = tieneNombre && tieneApellidos && tieneTelefono && tieneEstado && tieneBoletos;
    
    btnConfirmar.disabled = !formularioCompleto || btnConfirmar.classList.contains('loading');
    btnConfirmar.style.background = formularioCompleto ? 'linear-gradient(135deg, #28a745, #20c997)' : '#6c757d';
}

function validarTelefono(input) {
    input.value = input.value.replace(/[^0-9+\-\s()]/g, '');
    validarFormularioModalCompleto();
}

function validarNombre(input) {
    input.value = input.value.replace(/[^a-zA-ZáéíóúñÁÉÍÓÚÑ\s]/g, '');
    validarFormularioModalCompleto();
}

// ====== FUNCIONES DE ÉXITO Y WHATSAPP ======
function mostrarModalExitoMejorado(datos) {
    console.log('🎉 Mostrando modal de éxito:', datos);
    
    const numeroWhatsApp = "8180946816";
    const mensaje = `*>> NUEVA COMPRA DE BOLETOS <<*

* ES IMPORTANTE MANDAR EL COMPROBANTE DE PAGO *

*- Rifa:* ${datos.rifaNombre}
*- Cliente:* ${datos.nombreCompleto}
*- Estado:* ${datos.estado}
*- Teléfono:* ${datos.telefono}
*- Boletos:* ${datos.boletosFormateados}
*- Total:* ${datos.totalPagar}

Realizar el pago aquí:
https://exitodoradomx.com/pagos.php`;

    const urlWhatsApp = `https://wa.me/${numeroWhatsApp}?text=${encodeURIComponent(mensaje)}`;
    
    // Crear modal
    const modalHTML = `
        <div class="modal-exito-whatsapp" id="modalExitoWhatsApp" style="
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(212, 175, 55, 0.15); backdrop-filter: blur(10px);
            z-index: 10001; display: flex; align-items: center; justify-content: center;
            padding: 1rem;
        ">
            <div class="modal-exito-content" style="
                background: white; border-radius: 25px; padding: 3rem;
                max-width: 650px; width: 100%; text-align: center;
                box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
                max-height: 90vh; overflow-y: auto;
            ">
                <div style="
                    width: 100px; height: 100px; background: linear-gradient(135deg, #28a745, #20c997);
                    border-radius: 50%; display: flex; align-items: center; justify-content: center;
                    margin: 0 auto 2rem; animation: pulse 2s infinite;
                ">
                    <i class="fas fa-check" style="font-size: 3rem; color: white;"></i>
                </div>
                
                <h2 style="font-size: 2.5rem; font-weight: 800; color: #2c2c2c; margin-bottom: 1rem;">
                    ¡Compra Exitosa!
                </h2>
                <p style="font-size: 1.2rem; color: #666; margin-bottom: 1rem;">
                    Hola <strong>${datos.nombreCompleto}</strong>
                </p>
                <p style="font-size: 1rem; color: #666; margin-bottom: 2rem;">
                    Tus boletos han sido apartados correctamente
                </p>
                
                <div style="
                    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
                    padding: 2rem; border-radius: 20px; margin: 2rem 0;
                    border: 2px solid #d4af37; box-shadow: 0 8px 25px rgba(212, 175, 55, 0.2);
                ">
                    <div style="font-size: 1.4rem; font-weight: 800; color: #d4af37; margin-bottom: 1rem;">
                        <i class="fas fa-ticket-alt"></i> Tus Números de la Suerte
                    </div>
                    <div style="
                        font-family: 'Courier New', monospace; font-size: 1.3rem;
                        font-weight: 800; color: #2c2c2c; background: white;
                        padding: 1.5rem; border-radius: 15px; border: 1px solid #e9ecef;
                        word-break: break-all; line-height: 1.8;
                    ">
                        ${datos.boletosFormateados}
                    </div>
                </div>
                
                <div style="
                    background: linear-gradient(135deg, #e8f5e8, #f1f8e9);
                    padding: 1.5rem; border-radius: 15px; margin: 1.5rem 0;
                    border: 2px solid #4caf50; display: flex; align-items: center;
                    gap: 1rem; justify-content: center;
                ">
                    <i class="fas fa-map-marker-alt" style="font-size: 1.5rem; color: #2e7d32;"></i>
                    <div style="color: #2e7d32; font-weight: 700; font-size: 1rem;">
                        Estado: ${datos.estado}
                    </div>
                </div>
                
                <p style="margin: 1.5rem 0; font-size: 1.1rem; color: #666; font-weight: 600;">
                    Ahora envía un mensaje por WhatsApp para confirmar tu compra y recibir información de pago
                </p>
                
                <div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap; margin: 2rem 0;">
                    <a href="${urlWhatsApp}" target="_blank" style="
                        background: linear-gradient(135deg, #25d366, #128c7e);
                        color: white; padding: 1.5rem 3rem; border-radius: 20px;
                        font-weight: 800; text-decoration: none; font-size: 1.3rem;
                        display: inline-flex; align-items: center; gap: 1rem;
                        min-width: 250px; justify-content: center;
                        transition: all 0.3s ease; text-transform: uppercase;
                        letter-spacing: 0.5px; box-shadow: 0 12px 30px rgba(37, 211, 102, 0.4);
                    " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 40px rgba(37, 211, 102, 0.6)'" 
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 12px 30px rgba(37, 211, 102, 0.4)'">
                        <i class="fab fa-whatsapp" style="font-size: 1.8rem;"></i>
                        Enviar WhatsApp
                    </a>
                    <button onclick="cerrarModalExitoMejorado(); window.location.reload();" style="
                        background: linear-gradient(135deg, #6c757d, #495057);
                        color: white; padding: 1.2rem 2.5rem; border: none;
                        border-radius: 15px; font-weight: 700; cursor: pointer;
                        font-size: 1.1rem; min-width: 200px;
                        transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
                    " onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                        Continuar
                    </button>
                </div>
                
                <div style="
                    background: linear-gradient(135deg, #e3f2fd, #f0f7ff);
                    padding: 1.5rem; border-radius: 15px; margin-top: 2rem;
                    border: 2px solid #2196f3; display: flex; align-items: center;
                    justify-content: center; gap: 1rem;
                ">
                    <i class="fas fa-phone" style="color: #1976d2; font-size: 1.3rem;"></i>
                    <div style="font-size: 1.2rem; font-weight: 800; color: #1976d2;">
                        +52 1 81 8094 6816
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    crearConfetti();
}

function cerrarModalExitoMejorado() {
    const modal = document.getElementById('modalExitoWhatsApp');
    if (modal) {
        modal.style.animation = 'fadeOut 0.4s ease-out';
        setTimeout(() => modal.remove(), 400);
    }
    document.body.style.overflow = '';
}

// ====== UTILIDADES ======
function mostrarAlerta(mensaje, tipo = 'info') {
    const colores = {
        error: 'linear-gradient(135deg, #dc3545, #c82333)',
        success: 'linear-gradient(135deg, #28a745, #20c997)',
        info: 'linear-gradient(135deg, #17a2b8, #138496)',
        warning: 'linear-gradient(135deg, #ffc107, #e0a800)'
    };
    
    const iconos = {
        error: 'exclamation-triangle',
        success: 'check-circle',
        info: 'info-circle',
        warning: 'exclamation-circle'
    };
    
    const alert = document.createElement('div');
    const esMobileDevice = esMobile();
    
    if (esMobileDevice) {
        alert.style.cssText = `
            position: fixed; top: 10px; left: 10px; right: 10px;
            background: ${colores[tipo]}; color: white; padding: 1rem;
            border-radius: 12px; z-index: 10001; box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            font-weight: 600; text-align: center; font-size: 0.9rem;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        `;
    } else {
        alert.style.cssText = `
            position: fixed; top: 20px; right: 20px;
            background: ${colores[tipo]}; color: white; padding: 1rem 1.5rem;
            border-radius: 15px; z-index: 10001; box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            font-weight: 600; max-width: 350px; display: flex; align-items: center; gap: 0.5rem;
        `;
    }
    
    alert.innerHTML = `<i class="fas fa-${iconos[tipo]}"></i> ${mensaje}`;
    document.body.appendChild(alert);
    
    const timeout = esMobileDevice ? 3000 : 4000;
    setTimeout(() => {
        if (alert.parentNode) alert.remove();
    }, timeout);
}

function crearConfetti() {
    const colores = ['#d4af37', '#f4e794', '#b8941f', '#28a745', '#20c997', '#17a2b8', '#6f42c1', '#e83e8c', '#fd7e14'];
    
    for (let i = 0; i < 80; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.style.cssText = `
                position: fixed; 
                width: ${Math.random() * 12 + 6}px; 
                height: ${Math.random() * 12 + 6}px;
                background: ${colores[Math.floor(Math.random() * colores.length)]};
                left: ${Math.random() * 100}%; 
                top: -20px; 
                border-radius: ${Math.random() > 0.5 ? '50%' : '0'};
                pointer-events: none; 
                z-index: 10000;
                animation: confetti-fall ${Math.random() * 3 + 2}s linear forwards;
                animation-delay: ${Math.random() * 2}s;
                transform: rotate(${Math.random() * 360}deg);
            `;
            
            document.body.appendChild(confetti);
            
            setTimeout(() => {
                if (confetti.parentNode) confetti.remove();
            }, 6000);
        }, i * 40);
    }
}

function esMobile() {
    return window.innerWidth <= 768 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

function esDispositivoTactil() {
    return 'ontouchstart' in window || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;
}

function obtenerBoletosDisponibles() {
    return Array.from(document.querySelectorAll('.boleto-disponible'))
        .map(el => parseInt(el.dataset.numero))
        .filter(num => !isNaN(num))
        .sort((a, b) => a - b);
}

// ====== CONFIGURACIÓN DE EVENTOS ======
function configurarFormularioModal() {
    const formPagoModal = document.getElementById('formPagoModal');
    if (formPagoModal) {
        formPagoModal.addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombreModal')?.value.trim();
            const apellidos = document.getElementById('apellidosModal')?.value.trim();
            const telefono = document.getElementById('telefonoModal')?.value.trim();
            const estado = document.getElementById('estadoModal')?.value.trim();
            
            if (!nombre || nombre.length < 2) {
                e.preventDefault();
                mostrarAlerta('El nombre debe tener al menos 2 caracteres', 'error');
                return;
            }
            
            if (!apellidos || apellidos.length < 2) {
                e.preventDefault();
                mostrarAlerta('Los apellidos deben tener al menos 2 caracteres', 'error');
                return;
            }
            
            if (!telefono || telefono.length < 10) {
                e.preventDefault();
                mostrarAlerta('El teléfono debe tener al menos 10 dígitos', 'error');
                return;
            }
            
            if (!estado) {
                e.preventDefault();
                mostrarAlerta('Debes seleccionar tu estado', 'error');
                return;
            }
            
            if (boletosSeleccionados.length === 0) {
                e.preventDefault();
                mostrarAlerta('Debes seleccionar al menos un boleto', 'error');
                return;
            }
            
            // Mostrar estado de carga
            const btn = document.getElementById('btnConfirmarCompra');
            if (btn) {
                btn.classList.add('loading');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            }
            
            setTimeout(() => formPagoModal.style.pointerEvents = 'none', 100);
        });
    }
}

function configurarValidacionTiempoReal() {
    const campos = ['nombreModal', 'apellidosModal', 'telefonoModal', 'estadoModal'];
    
    campos.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) {
            campo.addEventListener('input', validarFormularioModalCompleto);
            campo.addEventListener('change', validarFormularioModalCompleto);
        }
    });
    
    // Validación específica para nombres
    const nombreModal = document.getElementById('nombreModal');
    const apellidosModal = document.getElementById('apellidosModal');
    
    if (nombreModal) {
        nombreModal.addEventListener('input', function() { validarNombre(this); });
        nombreModal.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' || e.key === 'Delete' || e.key === 'Tab' || e.key === 'ArrowLeft' || e.key === 'ArrowRight') return;
            if (/[0-9]/.test(e.key)) e.preventDefault();
        });
    }
    
    if (apellidosModal) {
        apellidosModal.addEventListener('input', function() { validarNombre(this); });
        apellidosModal.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' || e.key === 'Delete' || e.key === 'Tab' || e.key === 'ArrowLeft' || e.key === 'ArrowRight') return;
            if (/[0-9]/.test(e.key)) e.preventDefault();
        });
    }
    
    // Validación para teléfono
    const telefonoModal = document.getElementById('telefonoModal');
    if (telefonoModal) {
        telefonoModal.addEventListener('input', function() { validarTelefono(this); });
        telefonoModal.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' || e.key === 'Delete' || e.key === 'Tab' || e.key === 'ArrowLeft' || e.key === 'ArrowRight' || /[0-9+\-\s()]/.test(e.key)) return;
            e.preventDefault();
        });
    }
}

// ====== INICIALIZACIÓN ======
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Sistema iniciado');
    console.log('💰 Precio por boleto:', precioBoleto);
    
    // Configurar formulario y validaciones
    configurarFormularioModal();
    configurarValidacionTiempoReal();
    
    // Mostrar botón móvil si es necesario
    const mobileSuerteBtn = document.getElementById('mobileSuerteBtn');
    if (mobileSuerteBtn && esMobile()) {
        mobileSuerteBtn.style.display = 'block';
    }
    
    // Configurar eventos de clic fuera de modales
    window.addEventListener('click', function(event) {
        const modalPago = document.getElementById('modalPago');
        const ruletaModal = document.getElementById('ruletaModal');
        const modalCombosMobile = document.getElementById('modalCombosMobile');
        
        if (event.target === modalPago) cerrarModalPago();
        if (event.target === ruletaModal) cerrarModal();
        if (event.target === modalCombosMobile) cerrarCombosMobiles();
    });
    
    // Configurar búsqueda con Enter
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarNumeros();
            }
        });
        
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' || e.key === 'Delete' || e.key === 'Tab' || e.key === 'ArrowLeft' || e.key === 'ArrowRight' || e.key === 'Enter') return;
            if (!/[0-9,\-\s]/.test(e.key)) e.preventDefault();
        });
    }
    
    // Configurar input personalizado
    const inputPersonalizado = document.getElementById('cantidadPersonalizada');
    if (inputPersonalizado) {
        inputPersonalizado.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                seleccionarComboPersonalizado();
            }
        });
        
        inputPersonalizado.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' || e.key === 'Delete' || e.key === 'Tab' || e.key === 'ArrowLeft' || e.key === 'ArrowRight' || e.key === 'Enter') return;
            if (!/[0-9]/.test(e.key)) e.preventDefault();
        });
    }
    
    // Configurar teclas rápidas para desktop
    if (!esMobile()) {
        document.addEventListener('keydown', function(e) {
            // Ctrl + A para seleccionar todos
            if (e.ctrlKey && e.key === 'a') {
                e.preventDefault();
                seleccionarTodos();
            }
            
            // Escape para limpiar selección
            if (e.key === 'Escape') {
                limpiarSeleccion();
            }
            
            // Enter para abrir modal de pago
            if (e.key === 'Enter' && !e.ctrlKey && !e.altKey && boletosSeleccionados.length > 0) {
                const activeElement = document.activeElement;
                if (!activeElement || activeElement.tagName !== 'INPUT') {
                    e.preventDefault();
                    abrirModalPago();
                }
            }
        });
    }
    
    actualizarResumen();
});

// ====== FUNCIONES GLOBALES PARA HTML ======
window.toggleBoleto = toggleBoleto;
window.seleccionarTodos = seleccionarTodos;
window.limpiarSeleccion = limpiarSeleccion;
window.buscarNumeros = buscarNumeros;
window.abrirModalPago = abrirModalPago;
window.cerrarModalPago = cerrarModalPago;
window.seleccionarCombo = seleccionarCombo;
window.toggleCombos = toggleCombos;
window.mostrarCombosMobiles = mostrarCombosMobiles;
window.cerrarCombosMobiles = cerrarCombosMobiles;
window.seleccionarComboMobile = seleccionarComboMobile;
window.seleccionarComboPersonalizado = seleccionarComboPersonalizado;
window.confirmarSeleccionCombo = confirmarSeleccionCombo;
window.volverAGirar = volverAGirar;
window.cerrarModal = cerrarModal;
window.mostrarModalExitoMejorado = mostrarModalExitoMejorado;
window.cerrarModalExitoMejorado = cerrarModalExitoMejorado;

console.log('🚀 JavaScript cargado correctamente');
    </script>

    <!-- Script para mostrar modal de éxito después de compra -->
    <?php if ($mensaje === 'success'): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const datosCompra = {
            rifaNombre: '<?php echo addslashes($rifa['nombre']); ?>',
            nombreCompleto: '<?php echo addslashes(trim($nombre . ' ' . $apellidos)); ?>',
            estado: '<?php echo addslashes($estado); ?>',
            telefono: '<?php echo addslashes($telefono); ?>',
            boletosFormateados: '<?php echo implode(', ', array_map(function($n) use ($digitos) { return str_pad($n, $digitos, '0', STR_PAD_LEFT); }, $boletos_comprados)); ?>',
            totalPagar: '$<?php echo number_format(count($boletos_comprados) * $rifa['precio_boleto'], 2); ?>'
        };
        
        // Solo si existe la función mostrarModalExitoMejorado
        if (typeof mostrarModalExitoMejorado === 'function') {
            mostrarModalExitoMejorado(datosCompra);
        } else {
            alert('¡Compra exitosa! Te contactaremos por WhatsApp');
        }
    });
    </script>
    <?php endif; ?>

</body>
</html>