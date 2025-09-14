<?php
require_once '../config.php';
requireAdmin();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mensaje = '';
$error = '';

if ($_POST && isset($_POST['crear_rifa'])) {
    $nombre = trim($_POST['nombre']);
    $fecha_entrega = $_POST['fecha_entrega'];
    $total_boletos = (int)$_POST['total_boletos'];
    $precio_boleto = (float)$_POST['precio_boleto'];
    $descripcion_premio = trim($_POST['descripcion_premio']);
    
    if (empty($nombre) || empty($fecha_entrega) || $total_boletos <= 0 || $precio_boleto <= 0) {
        $error = 'Todos los campos son obligatorios y deben tener valores válidos';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Verificar y crear columnas si no existen
            $columnas_necesarias = [
                'descripcion_premio' => 'TEXT NULL',
                'imagen_premio' => 'VARCHAR(255) NULL'
            ];
            
            foreach ($columnas_necesarias as $columna => $tipo) {
                $check_stmt = $pdo->prepare("SHOW COLUMNS FROM rifas LIKE ?");
                $check_stmt->execute([$columna]);
                if (!$check_stmt->fetch()) {
                    try {
                        $pdo->exec("ALTER TABLE rifas ADD COLUMN $columna $tipo");
                    } catch (Exception $alter_error) {
                        // Si no puede crear la columna, continuar sin ella
                    }
                }
            }
            
            // Verificar qué columnas existen ahora
            $desc_exists = false;
            $img_exists = false;
            
            $check_desc = $pdo->prepare("SHOW COLUMNS FROM rifas LIKE 'descripcion_premio'");
            $check_desc->execute();
            $desc_exists = (bool)$check_desc->fetch();
            
            $check_img = $pdo->prepare("SHOW COLUMNS FROM rifas LIKE 'imagen_premio'");
            $check_img->execute();
            $img_exists = (bool)$check_img->fetch();
            
            // Manejar la subida de imagen solo si la columna existe
            $imagen_nombre = null;
            if ($img_exists && isset($_FILES['imagen_premio']) && $_FILES['imagen_premio']['error'] == 0) {
                $archivo = $_FILES['imagen_premio'];
                $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($extension, $extensiones_permitidas)) {
                    // Verificar tamaño (máximo 5MB)
                    if ($archivo['size'] <= 5 * 1024 * 1024) {
                        // Crear directorio si no existe
                        $upload_dir = '../uploads/rifas/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        // Generar nombre único para la imagen
                        $imagen_nombre = 'rifa_' . uniqid() . '.' . $extension;
                        $ruta_destino = $upload_dir . $imagen_nombre;
                        
                        // Mover archivo
                        if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                            throw new Exception('Error al subir la imagen');
                        }
                        
                        // Redimensionar imagen si es muy grande
                        redimensionarImagen($ruta_destino, 800, 600);
                        
                    } else {
                        throw new Exception('La imagen es muy grande. Máximo 5MB');
                    }
                } else {
                    throw new Exception('Formato de imagen no válido. Solo JPG, PNG, GIF, WEBP');
                }
            }
            
            // Construir consulta INSERT según las columnas disponibles
            if ($desc_exists && $img_exists) {
                $stmt = $pdo->prepare("INSERT INTO rifas (nombre, fecha_entrega, total_boletos, precio_boleto, descripcion_premio, imagen_premio) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $fecha_entrega, $total_boletos, $precio_boleto, $descripcion_premio, $imagen_nombre]);
            } elseif ($desc_exists) {
                $stmt = $pdo->prepare("INSERT INTO rifas (nombre, fecha_entrega, total_boletos, precio_boleto, descripcion_premio) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $fecha_entrega, $total_boletos, $precio_boleto, $descripcion_premio]);
            } elseif ($img_exists) {
                $stmt = $pdo->prepare("INSERT INTO rifas (nombre, fecha_entrega, total_boletos, precio_boleto, imagen_premio) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $fecha_entrega, $total_boletos, $precio_boleto, $imagen_nombre]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO rifas (nombre, fecha_entrega, total_boletos, precio_boleto) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $fecha_entrega, $total_boletos, $precio_boleto]);
            }
            
            $rifa_id = $pdo->lastInsertId();
            
            // Crear combos default
            $combos_default = [
                [1, $precio_boleto],
                [5, $precio_boleto * 5],
                [10, $precio_boleto * 10],
                [20, $precio_boleto * 20],
                [30, $precio_boleto * 30],
                [40, $precio_boleto * 40],
                [50, $precio_boleto * 50],
                [100, $precio_boleto * 100],
                [150, $precio_boleto * 150],
                [200, $precio_boleto * 200],
                [300, $precio_boleto * 300],
                [400, $precio_boleto * 400],
                [500, $precio_boleto * 500],
                [1000, $precio_boleto * 1000]
            ];
            
            foreach ($combos_default as $combo) {
                if ($combo[0] <= $total_boletos) {
                    $stmt = $pdo->prepare("INSERT INTO combos (rifa_id, cantidad_boletos, precio) VALUES (?, ?, ?)");
                    $stmt->execute([$rifa_id, $combo[0], $combo[1]]);
                }
            }
            
            // Crear boletos
            for ($i = 1; $i <= $total_boletos; $i++) {
                $stmt = $pdo->prepare("INSERT INTO boletos (rifa_id, numero_boleto) VALUES (?, ?)");
                $stmt->execute([$rifa_id, $i]);
            }
            
            $pdo->commit();
            
            $mensaje_extra = '';
            if (!$desc_exists) $mensaje_extra .= ' (Nota: La descripción no se guardó porque la columna no existe)';
            if (!$img_exists) $mensaje_extra .= ' (Nota: La imagen no se guardó porque la columna no existe)';
            
            $mensaje = 'Rifa creada exitosamente con ' . $total_boletos . ' boletos' . ($imagen_nombre && $img_exists ? ' e imagen subida' : '') . $mensaje_extra;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            // Eliminar imagen si se subió pero falló la transacción
            if ($imagen_nombre && file_exists('../uploads/rifas/' . $imagen_nombre)) {
                unlink('../uploads/rifas/' . $imagen_nombre);
            }
            $error = 'Error al crear la rifa: ' . $e->getMessage();
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
    
    // Calcular nuevas dimensiones manteniendo proporción
    $ratio = min($max_width / $width, $max_height / $height);
    if ($ratio >= 1) return true; // No redimensionar si ya es pequeña
    
    $new_width = round($width * $ratio);
    $new_height = round($height * $ratio);
    
    // Crear imagen desde archivo
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
    
    // Crear nueva imagen redimensionada
    $dest = imagecreatetruecolor($new_width, $new_height);
    
    // Preservar transparencia para PNG y GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
        imagefill($dest, 0, 0, $transparent);
    }
    
    imagecopyresampled($dest, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Guardar imagen redimensionada
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nueva Rifa - Panel Administrativo</title>
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

/* ====== FORM STYLING ====== */
.form-grid {
    display: grid;
    gap: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    position: relative;
    animation: slideInLeft 0.6s ease-out;
}

.form-group:nth-child(1) { animation-delay: 0.2s; }
.form-group:nth-child(2) { animation-delay: 0.3s; }
.form-group:nth-child(3) { animation-delay: 0.4s; }
.form-group:nth-child(4) { animation-delay: 0.5s; }
.form-group:nth-child(5) { animation-delay: 0.6s; }

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

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

/* ====== IMAGE UPLOAD STYLING ====== */
.image-upload {
    border: 2px dashed #d4af37;
    border-radius: 16px;
    padding: 2.5rem;
    text-align: center;
    background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    cursor: pointer;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.image-upload::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 40%, rgba(212, 175, 55, 0.1) 50%, transparent 60%);
    transform: translateX(-100%);
    transition: transform 0.6s;
}

.image-upload:hover::before {
    transform: translateX(100%);
}

.image-upload:hover {
    background: linear-gradient(135deg, #f0f8ff 0%, #e8f4f8 100%);
    border-color: #b8941f;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.15);
}

.image-upload.dragover {
    background: linear-gradient(135deg, #e8f5e8 0%, #d4f2d4 100%);
    border-color: #4caf50;
    transform: scale(1.02);
}

.upload-icon {
    font-size: 3rem;
    color: #d4af37;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    animation: pulse 2s infinite;
}

.upload-text {
    color: #666;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.upload-text strong {
    color: #1b5e20;
    font-weight: 700;
}

.file-input {
    display: none;
}

.upload-btn {
    background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
    position: relative;
    overflow: hidden;
}

.upload-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s;
}

.upload-btn:hover::before {
    left: 100%;
}

.upload-btn:hover {
    background: linear-gradient(135deg, #b8941f 0%, #996f1a 100%);
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
}

.image-preview {
    display: none;
    margin-top: 1rem;
    text-align: center;
    animation: fadeInUp 0.5s ease-out;
}

.preview-img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border: 3px solid #d4af37;
    transition: all 0.3s ease;
}

.preview-img:hover {
    transform: scale(1.05);
    box-shadow: 0 12px 35px rgba(212, 175, 55, 0.3);
}

.remove-image {
    background: linear-gradient(135deg, #c62828 0%, #d32f2f 100%);
    color: white;
    border: none;
    padding: 0.8rem 1.5rem;
    border-radius: 25px;
    cursor: pointer;
    margin-top: 1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(198, 40, 40, 0.3);
}

.remove-image:hover {
    background: linear-gradient(135deg, #d32f2f 0%, #f44336 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(198, 40, 40, 0.4);
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

/* ====== INFO BOXES ====== */
.info-box {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border: 1px solid #2196f3;
    border-radius: 16px;
    padding: 2rem;
    margin: 2rem 0;
    position: relative;
    box-shadow: 0 4px 20px rgba(33, 150, 243, 0.1);
    animation: slideInLeft 0.6s ease-out 0.7s both;
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
    font-weight: 500;
}

/* ====== COMBOS PREVIEW ====== */
.combos-preview {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 16px;
    padding: 2rem;
    margin-top: 2rem;
    border: 1px solid #e9ecef;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    position: relative;
    overflow: hidden;
    animation: slideInLeft 0.6s ease-out 0.8s both;
}

.combos-preview::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4af37, #1b5e20);
}

.combos-preview h4 {
    color: #1b5e20;
    margin-bottom: 1rem;
    font-weight: 700;
    font-size: 1.1rem;
}

.combos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.combo-item {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    font-size: 0.9rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.combo-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #d4af37, #1b5e20);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
}

.combo-item:hover::before {
    transform: scaleX(1);
}

.combo-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #d4af37;
}

.combo-item div:first-child {
    font-weight: 800;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    color: #333;
}

.combo-item div:last-child {
    color: #1b5e20;
    font-weight: 700;
    font-size: 1rem;
}

/* ====== ACTIONS ====== */
.actions {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    margin-top: 3rem;
    flex-wrap: wrap;
    animation: fadeInUp 0.6s ease-out 0.9s both;
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
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
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
    
    .info-box {
        padding: 1.5rem;
        margin: 1.5rem 0;
    }
    
    .combos-grid {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 0.8rem;
    }
    
    .image-upload {
        padding: 2rem;
        min-height: 180px;
    }
    
    .upload-icon {
        font-size: 2.5rem;
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
    
    .combos-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .image-upload {
        padding: 1.5rem;
        min-height: 160px;
    }
    
    .upload-icon {
        font-size: 2rem;
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
    
    .combos-preview {
        page-break-inside: avoid;
    }
}
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>➕ Crear Nueva Rifa</h1>
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
            <h2 class="form-title">📋 Información de la Rifa</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre">Nombre de la Rifa:</label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Ejemplo: Rifa Especial Febrero 2025">
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion_premio">Descripción del Premio:</label>
                        <textarea id="descripcion_premio" name="descripcion_premio" placeholder="Describe el premio que se puede ganar (ej: iPhone 15 Pro Max, $50,000 en efectivo, etc.)" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagen_premio">Imagen del Premio:</label>
                        <div class="image-upload" id="imageUpload">
                            <div class="upload-icon">📷</div>
                            <div class="upload-text">
                                <strong>Haz clic para subir una imagen</strong><br>
                                o arrastra y suelta aquí<br>
                                <small>JPG, PNG, GIF, WEBP (máx. 5MB)</small>
                            </div>
                            <button type="button" class="upload-btn" onclick="document.getElementById('imagen_premio').click()">
                                Seleccionar Imagen
                            </button>
                            <input type="file" id="imagen_premio" name="imagen_premio" class="file-input" accept="image/*">
                        </div>
                        <div class="image-preview" id="imagePreview">
                            <img id="previewImg" class="preview-img" alt="Vista previa">
                            <br>
                            <button type="button" class="remove-image" onclick="removeImage()">Eliminar Imagen</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_entrega">Fecha de Entrega de Premios:</label>
                        <input type="date" id="fecha_entrega" name="fecha_entrega" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="total_boletos">Total de Boletos:</label>
                            <select id="total_boletos" name="total_boletos" onchange="actualizarCombos()">
                                <option value="999" selected>999 boletos</option>
                                <option value="1999">1,999 boletos</option>
                                <option value="4999">4,999 boletos</option>
                                <option value="9999">9,999 boletos</option>
                                <option value="99999">99,999 boletos</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="precio_boleto">Precio por Boleto:</label>
                            <input type="number" id="precio_boleto" name="precio_boleto" step="0.01" min="0.01" value="1.00" onchange="actualizarCombos()">
                        </div>
                    </div>
                </div>
                
                <div class="info-box">
                    <h4>ℹ️ Información Importante:</h4>
                    <ul style="margin-left: 1.5rem;">
                        <li>La imagen se redimensionará automáticamente para optimizar la carga</li>
                        <li>Se crearán automáticamente todos los boletos numerados del 1 al número total</li>
                        <li>Se generarán combos predefinidos basados en el precio por boleto</li>
                        <li>La rifa estará activa inmediatamente después de crearla</li>
                        <li>Podrás gestionar los boletos desde el panel de administración</li>
                    </ul>
                </div>
                
                <div class="combos-preview">
                    <h4 style="color: #1b5e20; margin-bottom: 0.5rem;">📦 Vista Previa de Combos:</h4>
                    <div class="combos-grid" id="combos-preview">
                    </div>
                </div>
                
                <div class="actions">
                    <button type="submit" name="crear_rifa" class="btn btn-primary">✅ Crear Rifa</button>
                    <a href="index.php" class="btn btn-secondary">❌ Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Manejar subida de imagen
        const imageUpload = document.getElementById('imageUpload');
        const imageInput = document.getElementById('imagen_premio');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        
        // Drag and drop
        imageUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageUpload.classList.add('dragover');
        });
        
        imageUpload.addEventListener('dragleave', () => {
            imageUpload.classList.remove('dragover');
        });
        
        imageUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            imageUpload.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                imageInput.files = files;
                handleImageSelect();
            }
        });
        
        // Click en área de upload
        imageUpload.addEventListener('click', (e) => {
            if (e.target !== imageUpload.querySelector('.upload-btn')) {
                imageInput.click();
            }
        });
        
        // Cambio en input de archivo
        imageInput.addEventListener('change', handleImageSelect);
        
        function handleImageSelect() {
            const file = imageInput.files[0];
            if (file) {
                // Validar tipo
                if (!file.type.startsWith('image/')) {
                    alert('Por favor selecciona solo archivos de imagen');
                    return;
                }
                
                // Validar tamaño (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('La imagen es muy grande. Máximo 5MB');
                    return;
                }
                
                // Mostrar preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImg.src = e.target.result;
                    imageUpload.style.display = 'none';
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
        
        function removeImage() {
            imageInput.value = '';
            imageUpload.style.display = 'block';
            imagePreview.style.display = 'none';
            previewImg.src = '';
        }
        
        function actualizarCombos() {
            const totalBoletos = parseInt(document.getElementById('total_boletos').value);
            const precioBoleto = parseFloat(document.getElementById('precio_boleto').value);
            const combosContainer = document.getElementById('combos-preview');
            
            const combosDefault = [1, 5, 10, 20, 30, 40, 50, 100, 150, 200, 300, 400, 500, 1000];
            
            combosContainer.innerHTML = '';
            
            combosDefault.forEach(cantidad => {
                if (cantidad <= totalBoletos) {
                    const precio = (cantidad * precioBoleto).toFixed(2);
                    const comboDiv = document.createElement('div');
                    comboDiv.className = 'combo-item';
                    comboDiv.innerHTML = `
                        <div style="font-weight: bold;">${cantidad}</div>
                        <div style="color: #1b5e20;">$${precio}</div>
                    `;
                    combosContainer.appendChild(comboDiv);
                }
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            actualizarCombos();
            
            const fechaInput = document.getElementById('fecha_entrega');
            const hoy = new Date();
            const manana = new Date(hoy);
            manana.setDate(hoy.getDate() + 1);
            fechaInput.value = manana.toISOString().split('T')[0];
        });
    </script>
</body>
</html>