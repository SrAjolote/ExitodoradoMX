<?php
require_once '../config.php';

requireAdmin();

$rifa_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$rifa_id) {
    header('Location: index.php');
    exit();
}

$rifa_stmt = $pdo->prepare("SELECT * FROM rifas WHERE id = ?");
$rifa_stmt->execute([$rifa_id]);
$rifa = $rifa_stmt->fetch();

if (!$rifa) {
    header('Location: index.php');
    exit();
}

$mensaje = '';
$error = '';

if ($_POST && isset($_POST['actualizar_rifa'])) {
    $nombre = trim($_POST['nombre']);
    $fecha_entrega = $_POST['fecha_entrega'];
    $descripcion_premio = trim($_POST['descripcion_premio']);
    $activa = isset($_POST['activa']) ? 1 : 0;
    
    if (empty($nombre) || empty($fecha_entrega)) {
        $error = 'El nombre y la fecha de entrega son obligatorios';
    } else {
        try {
            // Manejar imagen si se subió una nueva
            $imagen_actual = $rifa['imagen_premio'];
            
            if (isset($_FILES['imagen_premio']) && $_FILES['imagen_premio']['error'] == 0) {
                $archivo = $_FILES['imagen_premio'];
                $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($extension, $extensiones_permitidas)) {
                    if ($archivo['size'] <= 5 * 1024 * 1024) {
                        $upload_dir = '../uploads/rifas/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        // Eliminar imagen anterior si existe
                        if (!empty($imagen_actual) && file_exists($upload_dir . $imagen_actual)) {
                            unlink($upload_dir . $imagen_actual);
                        }
                        
                        $imagen_nueva = 'rifa_' . uniqid() . '.' . $extension;
                        $ruta_destino = $upload_dir . $imagen_nueva;
                        
                        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                            // Redimensionar imagen
                            redimensionarImagen($ruta_destino, 800, 600);
                            $imagen_actual = $imagen_nueva;
                        }
                    } else {
                        throw new Exception('La imagen es muy grande. Máximo 5MB');
                    }
                } else {
                    throw new Exception('Formato de imagen no válido');
                }
            }
            
            // Debug: Verificar que la tabla y columnas existen
            $debug_stmt = $pdo->prepare("SHOW COLUMNS FROM rifas LIKE 'descripcion_premio'");
            $debug_stmt->execute();
            $column_exists = $debug_stmt->fetch();
            
            if (!$column_exists) {
                throw new Exception('La columna descripcion_premio no existe en la tabla rifas');
            }
            
            $stmt = $pdo->prepare("UPDATE rifas SET nombre = ?, descripcion_premio = ?, fecha_entrega = ?, activa = ?, imagen_premio = ? WHERE id = ?");
            $result = $stmt->execute([$nombre, $descripcion_premio, $fecha_entrega, $activa, $imagen_actual, $rifa_id]);
            
            if (!$result) {
                throw new Exception('Error en la consulta SQL: ' . implode(', ', $stmt->errorInfo()));
            }
            
            $mensaje = 'Rifa actualizada exitosamente';
            
            // Actualizar datos locales
            $rifa['nombre'] = $nombre;
            $rifa['descripcion_premio'] = $descripcion_premio;
            $rifa['fecha_entrega'] = $fecha_entrega;
            $rifa['activa'] = $activa;
            $rifa['imagen_premio'] = $imagen_actual;
            
        } catch (Exception $e) {
            $error = 'Error al actualizar la rifa: ' . $e->getMessage();
        }
    }
}

// Función para redimensionar imágenes
function redimensionarImagen($ruta, $max_width, $max_height) {
    $info = getimagesize($ruta);
    if (!$info) return false;
    
    $width = $info[0];
    $height = $info[1];
    $type = $info[2];
    
    $ratio = min($max_width / $width, $max_height / $height);
    if ($ratio >= 1) return true;
    
    $new_width = round($width * $ratio);
    $new_height = round($height * $ratio);
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($ruta);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($ruta);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($ruta);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($ruta);
            break;
        default:
            return false;
    }
    
    $dest = imagecreatetruecolor($new_width, $new_height);
    
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
        imagefill($dest, 0, 0, $transparent);
    }
    
    imagecopyresampled($dest, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($dest, $ruta, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($dest, $ruta, 8);
            break;
        case IMAGETYPE_GIF:
            imagegif($dest, $ruta);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($dest, $ruta, 85);
            break;
    }
    
    imagedestroy($source);
    imagedestroy($dest);
    return true;
}

$stats_stmt = $pdo->prepare("SELECT estado, COUNT(*) as total FROM boletos WHERE rifa_id = ? GROUP BY estado");
$stats_stmt->execute([$rifa_id]);
$stats = [];
while ($row = $stats_stmt->fetch()) {
    $stats[$row['estado']] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Rifa - Panel Administrativo</title>
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
    overflow-x: hidden;
}

/* ====== HEADER ====== */
.header {
    background: linear-gradient(135deg, #d4af37 0%, #b8941f 50%, #996f1a 100%);
    color: white;
    padding: 2rem 0;
    text-align: center;
    box-shadow: 0 8px 32px rgba(212, 175, 55, 0.4);
    position: relative;
    overflow: hidden;
}

.header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="70" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="70" cy="30" r="1.5" fill="rgba(255,255,255,0.1)"/></svg>');
    animation: floatPattern 20s linear infinite;
}

@keyframes floatPattern {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100px); }
}

.header .container {
    position: relative;
    z-index: 1;
}

.header h1 {
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    font-weight: 800;
    margin-bottom: 0.5rem;
    text-shadow: 0 4px 8px rgba(0,0,0,0.3);
    animation: slideInDown 0.8s ease-out;
}

.header p {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    opacity: 0.9;
    font-weight: 500;
    animation: slideInUp 0.8s ease-out 0.2s both;
}

/* ====== CONTAINER ====== */
.container {
    max-width: 900px;
    margin: 0 auto;
    padding: 1.5rem;
}

/* ====== ALERTAS ====== */
.alert {
    padding: 1.5rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    animation: slideInLeft 0.6s ease-out;
    position: relative;
    overflow: hidden;
}

.alert::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: currentColor;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    border: 1px solid #28a745;
}

.alert-success::after {
    content: '✅';
    font-size: 1.2rem;
}

.alert-error {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
    border: 1px solid #dc3545;
}

.alert-error::after {
    content: '❌';
    font-size: 1.2rem;
}

/* ====== FORM CONTAINER ====== */
.form-container {
    background: white;
    border-radius: 24px;
    padding: 3rem;
    margin: 2rem 0;
    box-shadow: 0 20px 60px rgba(0,0,0,0.12);
    border: 1px solid rgba(212, 175, 55, 0.2);
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.8s ease-out;
}

.form-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #d4af37, #1b5e20, #d4af37);
}

.form-title {
    color: #1b5e20;
    font-size: clamp(1.4rem, 3vw, 1.8rem);
    font-weight: 800;
    margin-bottom: 2rem;
    text-align: center;
    position: relative;
    padding-bottom: 1rem;
}

.form-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: linear-gradient(90deg, #d4af37, #1b5e20);
    border-radius: 2px;
}

/* ====== STATS GRID ====== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 16px;
    padding: 2rem 1.5rem;
    text-align: center;
    border: 1px solid #e9ecef;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4af37, #1b5e20);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
}

.stat-card:hover::before {
    transform: scaleX(1);
}

.stat-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    border-color: #d4af37;
}

.stat-number {
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    font-weight: 900;
    margin-bottom: 0.8rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    animation: countUp 1s ease-out;
}

.stat-card div:last-child {
    color: #666;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-disponible { color: #6c757d; }
.stat-apartado { color: #ff9800; }
.stat-pagado { color: #28a745; }

/* ====== FORM STYLING ====== */
.form-grid {
    display: grid;
    gap: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    position: relative;
}

.form-group label {
    margin-bottom: 0.8rem;
    color: #1b5e20;
    font-weight: 700;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: color 0.3s ease;
}

.form-group label::before {
    content: '●';
    color: #d4af37;
    font-size: 0.8rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 1.2rem 1.5rem;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    font-family: inherit;
    background: white;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #d4af37;
    box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.15);
    transform: translateY(-2px);
}

.form-group input:hover,
.form-group select:hover,
.form-group textarea:hover {
    border-color: #d4af37;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.1);
}

.form-group textarea {
    min-height: 120px;
    resize: vertical;
    font-family: inherit;
    line-height: 1.6;
}

.form-group input[type="file"] {
    padding: 1rem;
    background: #f8f9fa;
    border-style: dashed;
    cursor: pointer;
}

.form-group input[type="file"]:hover {
    background: #e9ecef;
}

.form-group small {
    margin-top: 0.5rem;
    color: #6c757d;
    font-size: 0.85rem;
    font-style: italic;
}

/* ====== CHECKBOX STYLING ====== */
.checkbox-group {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.checkbox-group:hover {
    background: #e9ecef;
    border-color: #d4af37;
}

.checkbox-group input[type="checkbox"] {
    width: 20px;
    height: 20px;
    margin: 0;
    accent-color: #d4af37;
    cursor: pointer;
    transform: scale(1.2);
}

.checkbox-group label {
    margin: 0;
    cursor: pointer;
    font-weight: 600;
    color: #1b5e20;
}

.checkbox-group label::before {
    display: none;
}

/* ====== BOTONES ====== */
.btn {
    padding: 1.2rem 2.5rem;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-align: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    min-width: 160px;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s;
}

.btn:hover::before {
    left: 100%;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.25);
}

.btn:active {
    transform: translateY(-1px);
}

.btn-primary {
    background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%);
}

.btn-secondary {
    background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
    color: white;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #b8941f 0%, #996f1a 100%);
}

.btn-danger {
    background: linear-gradient(135deg, #c62828 0%, #d32f2f 100%);
    color: white;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #d32f2f 0%, #f44336 100%);
}

/* ====== INFO BOXES ====== */
.info-box {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border: 1px solid #2196f3;
    border-radius: 16px;
    padding: 2rem;
    margin: 2rem 0;
    position: relative;
    box-shadow: 0 4px 20px rgba(33, 150, 243, 0.1);
}

.info-box::before {
    content: 'ℹ️';
    position: absolute;
    top: -12px;
    left: 20px;
    background: #2196f3;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}

.info-box h4 {
    color: #1976d2;
    margin-bottom: 1rem;
    font-size: 1.2rem;
    font-weight: 700;
}

.info-box ul {
    margin-left: 2rem;
    line-height: 1.8;
}

.info-box li {
    margin-bottom: 0.5rem;
    color: #1565c0;
}

.warning-box {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    border: 1px solid #ffc107;
    color: #856404;
    padding: 2rem;
    border-radius: 16px;
    margin: 2rem 0;
    position: relative;
    box-shadow: 0 4px 20px rgba(255, 193, 7, 0.1);
    font-weight: 600;
}

.warning-box::before {
    content: '⚠️';
    position: absolute;
    top: -12px;
    left: 20px;
    background: #ffc107;
    color: #856404;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}

/* ====== IMAGEN ACTUAL ====== */
.current-image-container {
    margin: 1rem 0;
    text-align: center;
}

.current-image-container img {
    max-width: 250px;
    max-height: 200px;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border: 3px solid #d4af37;
    transition: all 0.3s ease;
}

.current-image-container img:hover {
    transform: scale(1.05);
    box-shadow: 0 12px 35px rgba(212, 175, 55, 0.3);
}

.no-image-placeholder {
    color: #6c757d;
    font-style: italic;
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 12px;
    border: 2px dashed #dee2e6;
    text-align: center;
    font-size: 1.1rem;
}

/* ====== ACTIONS ====== */
.actions {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    margin-top: 3rem;
    flex-wrap: wrap;
}

/* ====== ANIMACIONES ====== */
@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes countUp {
    from {
        opacity: 0;
        transform: scale(0.5);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

/* ====== SCROLLBAR PERSONALIZADO ====== */
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
    background: linear-gradient(135deg, #b8941f, #996f1a);
}

/* ====== RESPONSIVE DESIGN ====== */
@media (max-width: 1024px) {
    .container {
        padding: 1rem;
    }
    
    .form-container {
        padding: 2rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 1rem;
    }
}

@media (max-width: 768px) {
    .header {
        padding: 1.5rem 0;
    }
    
    .container {
        padding: 0.75rem;
    }
    
    .form-container {
        padding: 1.5rem;
        margin: 1rem 0;
        border-radius: 16px;
    }
    
    .form-title {
        font-size: 1.4rem;
        margin-bottom: 1.5rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        padding: 1.5rem 1rem;
    }
    
    .stat-number {
        font-size: 1.6rem;
        margin-bottom: 0.5rem;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 1rem;
        font-size: 0.95rem;
    }
    
    .btn {
        padding: 1rem 1.5rem;
        font-size: 0.9rem;
        min-width: 140px;
    }
    
    .actions {
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .info-box,
    .warning-box {
        padding: 1.5rem;
        margin: 1.5rem 0;
    }
    
    .current-image-container img {
        max-width: 200px;
        max-height: 150px;
    }
    
    .alert {
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }
}

@media (max-width: 480px) {
    .header {
        padding: 1rem 0;
    }
    
    .container {
        padding: 0.5rem;
    }
    
    .form-container {
        padding: 1rem;
        margin: 0.5rem 0;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 0.8rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .form-grid {
        gap: 1.5rem;
    }
    
    .form-group label {
        font-size: 0.9rem;
        margin-bottom: 0.6rem;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 0.8rem;
        font-size: 0.9rem;
    }
    
    .btn {
        padding: 0.8rem 1.2rem;
        font-size: 0.85rem;
        min-width: 120px;
    }
    
    .current-image-container img {
        max-width: 150px;
        max-height: 120px;
    }
}

/* ====== MEJORAS DE ACCESIBILIDAD ====== */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* ====== ESTADOS DE FOCUS MEJORADOS ====== */
.btn:focus,
input:focus,
select:focus,
textarea:focus {
    outline: 3px solid rgba(212, 175, 55, 0.5);
    outline-offset: 2px;
}

/* ====== MEJORAS PARA IMPRESIÓN ====== */
@media print {
    .header,
    .actions,
    .btn {
        display: none !important;
    }
    
    .form-container {
        box-shadow: none;
        border: 1px solid #ccc;
        page-break-inside: avoid;
        background: white;
    }
    
    .stats-grid {
        page-break-inside: avoid;
    }
}

/* ====== EFECTOS ADICIONALES ====== */
.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }
.stat-card:nth-child(5) { animation-delay: 0.5s; }

.form-group:nth-child(1) { animation: slideInLeft 0.6s ease-out 0.2s both; }
.form-group:nth-child(2) { animation: slideInLeft 0.6s ease-out 0.3s both; }
.form-group:nth-child(3) { animation: slideInLeft 0.6s ease-out 0.4s both; }
.form-group:nth-child(4) { animation: slideInLeft 0.6s ease-out 0.5s both; }
.form-group:nth-child(5) { animation: slideInLeft 0.6s ease-out 0.6s both; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>✏️ Editar Rifa</h1>
            <p>Panel Administrativo - Éxito Dorado MX</p>
        </div>
    </div>
    
    <div class="container">
        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <h2 class="form-title">📊 Estadísticas Actuales</h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number stat-disponible"><?php echo $stats['disponible'] ?? 0; ?></div>
                    <div>Disponibles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number stat-apartado"><?php echo $stats['apartado'] ?? 0; ?></div>
                    <div>Apartados</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number stat-pagado"><?php echo $stats['pagado'] ?? 0; ?></div>
                    <div>Pagados</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo array_sum($stats); ?></div>
                    <div>Total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($rifa['total_boletos']); ?></div>
                    <div>Boletos Creados</div>
                </div>
            </div>
            
            <h2 class="form-title">📝 Editar Información</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre">Nombre de la Rifa:</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($rifa['nombre']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion_premio">Descripción del Premio:</label>
                        <textarea id="descripcion_premio" name="descripcion_premio" placeholder="Describe el premio"><?php echo htmlspecialchars($rifa['descripcion_premio'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Imagen Actual del Premio:</label>
                        <?php if (!empty($rifa['imagen_premio']) && file_exists('../uploads/rifas/' . $rifa['imagen_premio'])): ?>
                            <div style="margin: 1rem 0;">
                                <img src="../uploads/rifas/<?php echo htmlspecialchars($rifa['imagen_premio']); ?>" 
                                     alt="Imagen actual" style="max-width: 200px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            </div>
                        <?php else: ?>
                            <p style="color: #666; font-style: italic;">No hay imagen actual</p>
                        <?php endif; ?>
                        
                        <label for="imagen_premio" style="margin-top: 1rem;">Cambiar Imagen (opcional):</label>
                        <input type="file" id="imagen_premio" name="imagen_premio" accept="image/*">
                        <small style="color: #666;">JPG, PNG, GIF, WEBP (máx. 5MB)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_entrega">Fecha de Entrega de Premios:</label>
                        <input type="date" id="fecha_entrega" name="fecha_entrega" value="<?php echo $rifa['fecha_entrega']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Estado de la Rifa:</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="activa" name="activa" <?php echo $rifa['activa'] ? 'checked' : ''; ?>>
                            <label for="activa">Rifa activa (visible para los usuarios)</label>
                        </div>
                    </div>
                </div>
                
                <div class="info-box">
                    <h4>ℹ️ Información de solo lectura:</h4>
                    <ul style="margin-left: 1.5rem;">
                        <li><strong>Total de boletos:</strong> <?php echo number_format($rifa['total_boletos']); ?></li>
                        <li><strong>Precio por boleto:</strong> $<?php echo number_format($rifa['precio_boleto'], 2); ?> MXN</li>
                        <li><strong>Fecha de creación:</strong> <?php echo date('d/m/Y H:i', strtotime($rifa['fecha_creacion'])); ?></li>
                    </ul>
                </div>
                
                <?php if (($stats['apartado'] ?? 0) > 0 || ($stats['pagado'] ?? 0) > 0): ?>
                <div class="warning-box">
                    ⚠️ <strong>Precaución:</strong> Esta rifa ya tiene boletos apartados o pagados. Ten cuidado al desactivarla.
                </div>
                <?php endif; ?>
                
                <div class="actions">
                    <button type="submit" name="actualizar_rifa" class="btn btn-primary">✅ Guardar Cambios</button>
                    <a href="gestionar_boletos.php?rifa=<?php echo $rifa_id; ?>" class="btn btn-secondary">🎫 Gestionar Boletos</a>
                    <a href="index.php" class="btn btn-secondary">❌ Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>