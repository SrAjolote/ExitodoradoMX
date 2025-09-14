<?php
require_once 'config.php';

$rifas_stmt = $pdo->query("SELECT * FROM rifas WHERE activa = 1 ORDER BY fecha_entrega ASC");
$rifas = $rifas_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
    
<head>
<meta name="description" content="Participa en rifas legales y gana premios reales como autos, dinero y más. ¡Éxito Dorado te premia cada semana!">
<meta name="keywords" content="rifas legales, rifas en línea, rifas México, ganar premios, exito dorado">
<meta name="author" content="Éxito Dorado">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="canonical" href="https://exitodoradomx.com/" />

<!-- Open Graph para compartir en redes -->
<meta property="og:title" content="Éxito Dorado | Rifas en Línea con Premios Reales">
<meta property="og:description" content="Participa, gana y celebra. Rifas legales con premios garantizados.">
<meta property="og:image" content="https://exitodoradomx.com/assets/logo-og.jpg">
<meta property="og:url" content="https://exitodoradomx.com/">
<meta property="og:type" content="website">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Rifas con Éxito Dorado">
<meta name="twitter:description" content="Gana dinero, autos y más en rifas 100% legales en México.">
<meta name="twitter:image" content="https://exitodoradomx.com/assets/logo-og.jpg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éxito Dorado MX - Rifas de la Suerte</title>
    
    <!-- AÑADIR ESTAS LÍNEAS PARA EL FAVICON -->
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="logo.jpg">
    <link rel="apple-touch-icon" href="logo.jpg">
    <link rel="apple-touch-icon" sizes="152x152" href="logo.jpg">
    <link rel="apple-touch-icon" sizes="180x180" href="logo.jpg">
    <link rel="apple-touch-icon" sizes="167x167" href="logo.jpg">
    
    <!-- Para Android Chrome -->
    <meta name="theme-color" content="#d4af37">
    <meta name="msapplication-TileColor" content="#d4af37">
    <meta name="msapplication-TileImage" content="logo.jpg">
    
    <!-- Resto de tu código -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éxito Dorado MX - Rifas de la Suerte</title>
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
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid #d4af37;
        }
        
        .navbar {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo-img {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
            border: 2px solid #d4af37;
        }
        
        .brand-text {
            color: #2c2c2c;
            font-size: 1.5rem;
            font-weight: 800;
        }
        
        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        
        .nav-links a {
            color: #2c2c2c;
            text-decoration: none;
            padding: 0.8rem 1.5rem;
            border-radius: 15px;
            transition: all 0.3s ease;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
        }
        
        .nav-links a:hover {
            background: #d4af37;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
        }
        
        /* Hero Section espectacular */
        .hero {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(184, 148, 31, 0.1));
            padding: 6rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="gradient"><stop offset="0%" stop-color="rgba(212,175,55,0.1)"/><stop offset="100%" stop-color="transparent"/></radialGradient></defs><circle cx="50" cy="50" r="50" fill="url(%23gradient)"/></svg>') repeat;
            background-size: 200px 200px;
            animation: float 20s ease-in-out infinite;
            pointer-events: none;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .hero-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }
        
        .hero-badge {
            background: linear-gradient(135deg, #d4af37, #f4e794);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 2rem;
            display: inline-block;
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .hero-title {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #d4af37, #2c2c2c, #d4af37);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: 1.4rem;
            margin-bottom: 3rem;
            color: #666;
            font-weight: 500;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #d4af37, #f4e794);
            color: white;
            padding: 1.5rem 3rem;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3);
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
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 40px rgba(212, 175, 55, 0.4);
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-secondary {
            background: white;
            color: #d4af37;
            padding: 1.5rem 3rem;
            border: 2px solid #d4af37;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .btn-secondary:hover {
            background: #d4af37;
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(212, 175, 55, 0.4);
        }
        
        /* Features Section */
        .features {
            padding: 6rem 0;
            background: white;
            position: relative;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .section-title {
            text-align: center;
            font-size: 3rem;
            color: #2c2c2c;
            margin-bottom: 1rem;
            font-weight: 800;
        }
        
        .section-subtitle {
            text-align: center;
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 4rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2.5rem;
        }
        
        .feature-card {
            text-align: center;
            padding: 3rem 2rem;
            background: white;
            border-radius: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d4af37, #f4e794, #d4af37);
        }
        
        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        
        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #d4af37, #f4e794);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .feature-title {
            font-size: 1.5rem;
            color: #2c2c2c;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .feature-description {
            color: #666;
            line-height: 1.6;
            font-size: 1rem;
        }
        
        /* Rifas Section */
        .rifas-section {
            padding: 6rem 0;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }
        
        .rifas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 2.5rem;
            margin-top: 3rem;
        }
        
        .rifa-card {
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(212, 175, 55, 0.2);
            position: relative;
        }
        
        .rifa-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #28a745, #20c997, #28a745);
        }
        
        .rifa-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.2);
        }
        
        .rifa-image {
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, #d4af37, #f4e794);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        
        .rifa-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.3s ease;
        }
        
        .rifa-image:hover img {
            transform: scale(1.1);
        }
        
        .rifa-image .default-icon {
            font-size: 4.5rem;
            color: white;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        
        .image-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(40, 167, 69, 0.9);
            color: white;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .rifa-content {
            padding: 2.5rem;
        }
        
        .rifa-title {
            font-size: 1.6rem;
            color: #2c2c2c;
            margin-bottom: 1.5rem;
            font-weight: 700;
            line-height: 1.3;
        }
        
        .rifa-description {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #1976d2;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            border: 1px solid #90caf9;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .rifa-date {
            background: #f8f9fa;
            color: #495057;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #d4af37;
            font-weight: 600;
        }
        
        .rifa-condition {
            background: rgba(255, 193, 7, 0.1);
            color: #856404;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            font-size: 0.95rem;
            border: 1px solid rgba(255, 193, 7, 0.3);
            font-weight: 600;
        }
        
        .btn-participate {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 1.5rem 2rem;
            border: none;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            display: block;
            text-align: center;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-participate::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: all 0.5s;
        }
        
        .btn-participate:hover {
            background: linear-gradient(135deg, #20c997, #17a2b8);
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(40, 167, 69, 0.4);
        }
        
        .btn-participate:hover::before {
            left: 100%;
        }
        
        /* Quick Links */
        .quick-links {
            background: white;
            padding: 6rem 0;
        }
        
        .quick-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
            margin-top: 3rem;
        }
        
        .quick-link {
            background: white;
            padding: 3rem 2rem;
            border-radius: 25px;
            text-align: center;
            text-decoration: none;
            color: #2c2c2c;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(212, 175, 55, 0.2);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }
        
        .quick-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6c757d, #495057, #6c757d);
        }
        
        .quick-link:hover {
            border-color: #d4af37;
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }
        
        .quick-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: #d4af37;
        }
        
        .quick-link h3 {
            font-size: 1.4rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .quick-link p {
            color: #666;
            line-height: 1.6;
        }
        
        /* No rifas */
        .no-rifas {
            text-align: center;
            padding: 5rem 2rem;
            background: white;
            border-radius: 25px;
            margin: 3rem 0;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
        }
        
        .no-rifas h3 {
            color: #d4af37;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            font-weight: 800;
        }
        
        .no-rifas p {
            font-size: 1.3rem;
            color: #666;
            margin: 1.5rem 0;
            font-weight: 500;
        }
        
        .no-rifas-icon {
            font-size: 5rem;
            margin: 2rem 0;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            60% { transform: translateY(-10px); }
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, #2c2c2c, #1a1a1a);
            color: white;
            padding: 4rem 0 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d4af37, #f4e794, #d4af37);
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .footer-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 800;
            background: linear-gradient(135deg, #d4af37, #f4e794);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .contact-info {
            font-size: 1.2rem;
            margin: 1rem 0;
            color: #e9ecef;
            font-weight: 500;
        }
        
        .footer-divider {
            margin: 3rem 0 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
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
            background-color: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }
        
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90%;
            max-height: 90%;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.5);
            border: 2px solid #d4af37;
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
            background: rgba(0, 0, 0, 0.7);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 2001;
            backdrop-filter: blur(10px);
        }
        
        .modal-close:hover {
            background: rgba(212, 175, 55, 0.9);
            transform: scale(1.1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 1rem 20px;
            }
            
            .logo-img {
                width: 50px;
                height: 50px;
            }
            
            .brand-text {
                font-size: 1.3rem;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }
            
            .nav-links a {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }
            
            .hero {
                padding: 4rem 0;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-primary,
            .btn-secondary {
                padding: 1.2rem 2rem;
                font-size: 1rem;
                width: 100%;
                max-width: 300px;
            }
            
            .section-title {
                font-size: 2.2rem;
            }
            
            .rifas-grid {
                grid-template-columns: 1fr;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-grid {
                grid-template-columns: 1fr;
            }
            
            .feature-card,
            .quick-link {
                padding: 2rem 1.5rem;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Animaciones de entrada */
        .feature-card,
        .rifa-card,
        .quick-link {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .feature-card:nth-child(2) { animation-delay: 0.1s; }
        .feature-card:nth-child(3) { animation-delay: 0.2s; }
        .feature-card:nth-child(4) { animation-delay: 0.3s; }
        
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
        .logo-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
}

.logo-section:hover {
    transform: translateY(-2px);
    text-decoration: none;
    color: inherit;
}

.logo-section:hover .logo-img {
    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.5);
    transform: scale(1.05);
}

.logo-section:hover .brand-text {
    color: #d4af37;
}
/* ====== FIX RESPONSIVO ACCESO RÁPIDO - SOLO LAYOUT ====== */

/* Mejorar responsividad sin cambiar colores */
@media (max-width: 1200px) {
    .quick-grid {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
    }
}

@media (max-width: 768px) {
    .quick-links {
        padding: 4rem 0;
    }
    
    .quick-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        max-width: 500px;
        margin: 2rem auto 0;
    }
    
    .quick-link {
        padding: 2.5rem 2rem;
        border-radius: 20px;
        min-height: auto;
    }
    
    .quick-link h3 {
        font-size: 1.3rem;
        margin-bottom: 1rem;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    
    .quick-link p {
        font-size: 0.95rem;
        line-height: 1.5;
    }
    
    .quick-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }
}

@media (max-width: 600px) {
    .quick-grid {
        max-width: 100%;
        margin: 1.5rem 0 0;
    }
    
    .quick-link {
        margin: 0 10px;
    }
}

@media (max-width: 480px) {
    .quick-links {
        padding: 3rem 0;
    }
    
    .container {
        padding: 0 15px;
    }
    
    .quick-grid {
        gap: 1.2rem;
        margin-top: 1.5rem;
    }
    
    .quick-link {
        padding: 2rem 1.5rem;
        border-radius: 18px;
        margin: 0 5px;
    }
    
    .quick-link h3 {
        font-size: 1.2rem;
        margin-bottom: 0.8rem;
        white-space: normal;
        overflow: visible;
        text-overflow: clip;
        line-height: 1.2;
        word-break: break-word;
        hyphens: auto;
        -webkit-hyphens: auto;
        -moz-hyphens: auto;
    }
    
    .quick-link p {
        font-size: 0.9rem;
        line-height: 1.4;
    }
    
    .quick-icon {
        font-size: 2.2rem;
        margin-bottom: 0.8rem;
    }
}

@media (max-width: 374px) {
    .quick-link {
        padding: 1.8rem 1.2rem;
        margin: 0 2px;
    }
    
    .quick-link h3 {
        font-size: 1.1rem;
        margin-bottom: 0.6rem;
        line-height: 1.1;
    }
    
    .quick-link p {
        font-size: 0.85rem;
        line-height: 1.3;
    }
    
    .quick-icon {
        font-size: 2rem;
        margin-bottom: 0.6rem;
    }
}

/* Orientación landscape en móviles */
@media (max-width: 768px) and (orientation: landscape) {
    .quick-links {
        padding: 2.5rem 0;
    }
    
    .quick-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        max-width: 100%;
    }
    
    .quick-link {
        padding: 1.5rem 1rem;
        min-height: 180px;
    }
    
    .quick-link h3 {
        font-size: 1rem;
        margin-bottom: 0.5rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .quick-link p {
        font-size: 0.8rem;
        line-height: 1.3;
    }
    
    .quick-icon {
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
    }
}

/* Mejoras específicas para texto largo */
.quick-link h3 {
    overflow-wrap: break-word;
    word-wrap: break-word;
}

/* Flexbox interno para centrado perfecto */
@media (max-width: 768px) {
    .quick-link {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }
    
    .quick-link p {
        flex-grow: 0;
        margin: 0;
    }
}

/* Touch feedback mejorado para móviles */
@media (max-width: 768px) {
    .quick-link:active {
        transform: translateY(-3px) scale(0.98);
        transition: transform 0.1s ease;
    }
}

/* Contenedor seguro para evitar overflow */
@media (max-width: 767px) {
    .quick-links .container {
        overflow: hidden;
        padding-left: max(15px, env(safe-area-inset-left));
        padding-right: max(15px, env(safe-area-inset-right));
    }
    
    .quick-grid {
        width: 100%;
        box-sizing: border-box;
    }
    
    .quick-link {
        width: 100%;
        box-sizing: border-box;
        max-width: 100%;
    }
}

/* Mejoras para títulos específicos que se cortan */
@media (max-width: 600px) {
    /* Para "Métodos de Pago" */
    .quick-link:nth-child(2) h3 {
        font-size: 1.15rem;
    }
    
    /* Para "Soporte WhatsApp" */
    .quick-link:nth-child(3) h3 {
        font-size: 1.15rem;
    }
}

@media (max-width: 400px) {
    /* Reducir aún más en pantallas muy pequeñas */
    .quick-link h3 {
        font-size: 1rem !important;
        line-height: 1.1;
        margin-bottom: 0.5rem;
    }
}

/* Grid fallback para navegadores sin soporte */
@supports not (display: grid) {
    .quick-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .quick-link {
        flex: 0 1 300px;
        margin: 1rem;
    }
    
    @media (max-width: 768px) {
        .quick-link {
            flex: 1 1 100%;
            max-width: 500px;
        }
    }
}

/* Optimización para tablets en modo portrait */
@media (min-width: 769px) and (max-width: 1024px) {
    .quick-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        max-width: 800px;
        margin: 2rem auto 0;
    }
    
    .quick-link {
        padding: 2.5rem 1.8rem;
    }
    
    .quick-link h3 {
        font-size: 1.25rem;
    }
}

/* Mejorar el espaciado en contenedores */
@media (max-width: 480px) {
    .section-title {
        font-size: 2rem;
        margin-bottom: 0.8rem;
        padding: 0 20px;
        text-align: center;
    }
    
    .section-subtitle {
        font-size: 1rem;
        margin-bottom: 1.5rem;
        padding: 0 20px;
        text-align: center;
    }
}

/* Ajustes para dispositivos con notch */
@media (max-width: 768px) {
    .quick-links {
        padding-left: env(safe-area-inset-left);
        padding-right: env(safe-area-inset-right);
    }
}

/* Prevenir que el texto se rompa de manera fea */
.quick-link h3 {
    text-rendering: optimizeLegibility;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Asegurar que los iconos se mantengan centrados */
@media (max-width: 768px) {
    .quick-icon {
        display: block;
        text-align: center;
        width: 100%;
    }
}
    </style>
</head>
<body>
<!-- REEMPLAZAR TODO EL HEADER EXISTENTE CON ESTE CÓDIGO -->
<header class="header">
    <nav class="navbar">
        <a href="index.php" class="logo-section">
            <img src="logo.jpg" alt="Éxito Dorado MX" class="logo-img">
            <div class="brand-text">ÉXITO DORADO MX</div>
        </a>
        
        <!-- Botón hamburguesa para móvil -->
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <div class="nav-links" id="navLinks">
            <a href="#rifas">
                <i class="fas fa-dice"></i>
                Rifas
            </a>
            <a href="consultar_boletos.php">
                <i class="fas fa-ticket-alt"></i>
                Mis Boletos
            </a>
            <a href="pagos.php">
                <i class="fas fa-credit-card"></i>
                Pagar
            </a>
            <a href="https://wa.me/5218180946816" target="_blank">
                <i class="fab fa-whatsapp"></i>
                WhatsApp
            </a>
        </div>
    </nav>
</header>

<!-- AGREGAR ESTE CSS AL FINAL DEL <style> EXISTENTE -->
<style>
/* ====== HEADER MÓVIL CON TOGGLE ====== */

/* Botón hamburguesa */
.mobile-menu-toggle {
    display: none;
    flex-direction: column;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
    gap: 4px;
    transition: all 0.3s ease;
    z-index: 1001;
}

.mobile-menu-toggle span {
    width: 25px;
    height: 3px;
    background: #2c2c2c;
    border-radius: 2px;
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    transform-origin: center;
}

.mobile-menu-toggle:hover span {
    background: #d4af37;
}

/* Animación del botón hamburguesa cuando está activo */
.mobile-menu-toggle.active span:nth-child(1) {
    transform: rotate(45deg) translate(6px, 6px);
}

.mobile-menu-toggle.active span:nth-child(2) {
    opacity: 0;
    transform: translateX(20px);
}

.mobile-menu-toggle.active span:nth-child(3) {
    transform: rotate(-45deg) translate(6px, -6px);
}

/* Responsive para móviles */
@media (max-width: 768px) {
    .navbar {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 20px;
        position: relative;
    }
    
    /* Mostrar botón hamburguesa en móvil */
    .mobile-menu-toggle {
        display: flex;
    }
    
    /* Menú de navegación en móvil */
    .nav-links {
        position: fixed;
        top: 0;
        right: -100%;
        width: 280px;
        height: 100vh;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 249, 250, 0.98));
        backdrop-filter: blur(20px);
        flex-direction: column;
        justify-content: flex-start;
        align-items: stretch;
        gap: 0;
        padding: 100px 0 50px 0;
        transition: right 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
        border-left: 1px solid rgba(212, 175, 55, 0.3);
        z-index: 1000;
    }
    
    /* Menú abierto */
    .nav-links.active {
        right: 0;
    }
    
    /* Enlaces en el menú móvil */
    .nav-links a {
        background: transparent;
        border: none;
        border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 0;
        padding: 1.5rem 2rem;
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c2c2c;
        transition: all 0.3s ease;
        transform: translateX(50px);
        opacity: 0;
        animation: slideInRight 0.3s ease-out forwards;
    }
    
    /* Delay para animación de cada enlace */
    .nav-links.active a:nth-child(1) { animation-delay: 0.1s; }
    .nav-links.active a:nth-child(2) { animation-delay: 0.2s; }
    .nav-links.active a:nth-child(3) { animation-delay: 0.3s; }
    .nav-links.active a:nth-child(4) { animation-delay: 0.4s; }
    
    .nav-links a:hover {
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(244, 231, 148, 0.1));
        color: #d4af37;
        transform: translateX(10px);
        border-left: 4px solid #d4af37;
    }
    
    .nav-links a:last-child {
        border-bottom: none;
    }
    
    /* Overlay para cerrar el menú */
    .nav-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .nav-overlay.active {
        display: block;
        opacity: 1;
    }
    
    /* Ajustar logo en móvil */
    .logo-img {
        width: 45px;
        height: 45px;
    }
    
    .brand-text {
        font-size: 1.2rem;
    }
}

/* Animaciones */
@keyframes slideInRight {
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Para tablets */
@media (min-width: 769px) and (max-width: 1024px) {
    .nav-links {
        gap: 1rem;
    }
    
    .nav-links a {
        padding: 0.7rem 1.2rem;
        font-size: 0.95rem;
    }
}

/* Efectos adicionales */
@media (max-width: 768px) {
    /* Efecto de blur en el contenido cuando el menú está abierto */
    body.menu-open .hero,
    body.menu-open .rifas-section,
    body.menu-open .features,
    body.menu-open .quick-links,
    body.menu-open .footer {
        filter: blur(3px);
        transition: filter 0.3s ease;
    }
    
    /* Prevenir scroll cuando el menú está abierto */
    body.menu-open {
        overflow: hidden;
    }
    
    /* Efecto de bounce en el botón hamburguesa */
    .mobile-menu-toggle:active {
        transform: scale(0.9);
    }
    
    /* Gradiente sutil en el menú móvil */
    .nav-links::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100%;
        height: 80px;
        background: linear-gradient(180deg, rgba(212, 175, 55, 0.1), transparent);
        pointer-events: none;
    }
    
    /* Sombra en el header cuando el menú está abierto */
    .header.menu-open {
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.2);
        z-index: 1002;
    }
}

/* Indicador visual en el botón hamburguesa */
@media (max-width: 768px) {
    .mobile-menu-toggle::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 40px;
        height: 40px;
        border: 2px solid transparent;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: all 0.3s ease;
    }
    
    .mobile-menu-toggle:hover::after {
        border-color: rgba(212, 175, 55, 0.3);
        background: rgba(212, 175, 55, 0.1);
    }
    
    .mobile-menu-toggle.active::after {
        border-color: #d4af37;
        background: rgba(212, 175, 55, 0.2);
        transform: translate(-50%, -50%) rotate(180deg);
    }
}
/* ====== RESPONSIVIDAD COMPLETA PARA MÓVILES ====== */

/* ===== BREAKPOINTS DEFINIDOS ===== */
/* xs: 0-374px (móviles muy pequeños) */
/* sm: 375-479px (móviles pequeños) */
/* md: 480-767px (móviles grandes) */
/* lg: 768px+ (tablets y desktop) */

/* ===== MÓVILES MUY PEQUEÑOS (0-374px) ===== */
@media (max-width: 374px) {
    /* Contenedores principales */
    .container {
        padding: 0 10px;
    }
    
    /* Header ultra compacto */
    .navbar {
        padding: 0.8rem 15px;
    }
    
    .logo-img {
        width: 35px;
        height: 35px;
    }
    
    .brand-text {
        font-size: 1rem;
        font-weight: 700;
    }
    
    /* Hero section compacta */
    .hero {
        padding: 3rem 0;
    }
    
    .hero-content {
        padding: 0 15px;
    }
    
    .hero-badge {
        padding: 0.6rem 1.5rem;
        font-size: 0.8rem;
        margin-bottom: 1.5rem;
    }
    
    .hero-title {
        font-size: 2rem;
        line-height: 1.1;
        margin-bottom: 1rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
        margin-bottom: 2rem;
    }
    
    .hero-buttons {
        gap: 1rem;
    }
    
    .btn-primary,
    .btn-secondary {
        padding: 1rem 1.5rem;
        font-size: 0.9rem;
        width: 100%;
        max-width: none;
    }
    
    /* Secciones más compactas */
    .features,
    .rifas-section,
    .quick-links {
        padding: 3rem 0;
    }
    
    .section-title {
        font-size: 1.8rem;
        margin-bottom: 0.8rem;
    }
    
    .section-subtitle {
        font-size: 1rem;
        margin-bottom: 2rem;
    }
    
    /* Cards de rifas ultra compactas */
    .rifas-grid {
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .rifa-card {
        border-radius: 15px;
    }
    
    .rifa-image {
        height: 180px;
    }
    
    .rifa-image .default-icon {
        font-size: 3rem;
    }
    
    .rifa-content {
        padding: 1.5rem;
    }
    
    .rifa-title {
        font-size: 1.3rem;
        margin-bottom: 1rem;
    }
    
    .rifa-description,
    .rifa-date,
    .rifa-condition {
        padding: 1rem;
        margin-bottom: 1rem;
        font-size: 0.85rem;
    }
    
    .btn-participate {
        padding: 1.2rem 1.5rem;
        font-size: 1rem;
    }
    
    /* Features compactas */
    .features-grid {
        gap: 1.5rem;
    }
    
    .feature-card {
        padding: 2rem 1.5rem;
        border-radius: 15px;
    }
    
    .feature-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }
    
    .feature-title {
        font-size: 1.2rem;
        margin-bottom: 0.8rem;
    }
    
    .feature-description {
        font-size: 0.9rem;
    }
    
    /* Quick links compactas */
    .quick-grid {
        gap: 1.5rem;
    }
    
    .quick-link {
        padding: 2rem 1.5rem;
        border-radius: 15px;
    }
    
    .quick-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }
    
    .quick-link h3 {
        font-size: 1.2rem;
        margin-bottom: 0.8rem;
    }
    
    .quick-link p {
        font-size: 0.9rem;
    }
    
    /* Footer compacto */
    .footer {
        padding: 3rem 0 1.5rem;
    }
    
    .footer-title {
        font-size: 1.8rem;
        margin-bottom: 0.8rem;
    }
    
    .contact-info {
        font-size: 1rem;
        margin: 0.8rem 0;
    }
    
    /* Modal compacto */
    .modal-content {
        max-width: 95%;
        max-height: 85%;
        border-radius: 15px;
    }
    
    .modal-close {
        top: 10px;
        right: 15px;
        width: 40px;
        height: 40px;
        font-size: 1.5rem;
    }
    
    /* No rifas compacto */
    .no-rifas {
        padding: 3rem 1.5rem;
        margin: 2rem 0;
        border-radius: 15px;
    }
    
    .no-rifas h3 {
        font-size: 1.8rem;
        margin-bottom: 1rem;
    }
    
    .no-rifas p {
        font-size: 1.1rem;
        margin: 1rem 0;
    }
    
    .no-rifas-icon {
        font-size: 3.5rem;
        margin: 1.5rem 0;
    }
}

/* ===== MÓVILES PEQUEÑOS (375-479px) ===== */
@media (min-width: 375px) and (max-width: 479px) {
    .navbar {
        padding: 0.9rem 20px;
    }
    
    .logo-img {
        width: 40px;
        height: 40px;
    }
    
    .brand-text {
        font-size: 1.1rem;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .btn-primary,
    .btn-secondary {
        padding: 1.2rem 2rem;
        font-size: 1rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .rifa-image {
        height: 200px;
    }
    
    .rifa-content {
        padding: 2rem;
    }
    
    .rifa-title {
        font-size: 1.4rem;
    }
    
    .feature-card,
    .quick-link {
        padding: 2.5rem 2rem;
    }
}

/* ===== MÓVILES GRANDES (480-767px) ===== */
@media (min-width: 480px) and (max-width: 767px) {
    .navbar {
        padding: 1rem 20px;
    }
    
    .hero {
        padding: 4rem 0;
    }
    
    .hero-title {
        font-size: 3rem;
    }
    
    .hero-subtitle {
        font-size: 1.2rem;
    }
    
    .btn-primary,
    .btn-secondary {
        padding: 1.3rem 2.5rem;
        font-size: 1.1rem;
        width: auto;
        max-width: 280px;
    }
    
    .hero-buttons {
        flex-direction: row;
        justify-content: center;
        gap: 1rem;
    }
    
    .section-title {
        font-size: 2.5rem;
    }
    
    .features,
    .rifas-section,
    .quick-links {
        padding: 4rem 0;
    }
    
    .rifas-grid {
        grid-template-columns: 1fr;
        max-width: 500px;
        margin: 2rem auto 0;
    }
    
    .rifa-image {
        height: 220px;
    }
    
    .features-grid,
    .quick-grid {
        grid-template-columns: 1fr;
        max-width: 500px;
        margin: 0 auto;
        gap: 2rem;
    }
}

/* ===== MEJORAS UNIVERSALES PARA MÓVILES (0-767px) ===== */
@media (max-width: 767px) {
    /* Reset de espaciado para móviles */
    * {
        -webkit-tap-highlight-color: transparent;
    }
    
    /* Mejorar la experiencia táctil */
    .btn-primary,
    .btn-secondary,
    .btn-participate,
    .quick-link,
    .nav-links a {
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        touch-action: manipulation;
    }
    
    /* Texto más legible */
    body {
        font-size: 16px;
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
    }
    
    /* Evitar zoom en inputs */
    input, select, textarea {
        font-size: 16px;
    }
    
    /* Mejorar contraste */
    .hero-subtitle,
    .section-subtitle,
    .feature-description,
    .quick-link p {
        color: #555;
    }
    
    /* Espaciado mejorado */
    .hero-content,
    .container {
        padding-left: max(20px, env(safe-area-inset-left));
        padding-right: max(20px, env(safe-area-inset-right));
    }
    
    /* Header con safe area */
    .header {
        padding-top: env(safe-area-inset-top);
    }
    
    /* Animaciones más suaves en móvil */
    .rifa-card:hover,
    .feature-card:hover,
    .quick-link:hover {
        transform: translateY(-5px) scale(1.01);
    }
    
    /* Scrollbar personalizada */
    ::-webkit-scrollbar {
        width: 4px;
    }
    
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    
    ::-webkit-scrollbar-thumb {
        background: rgba(212, 175, 55, 0.5);
        border-radius: 2px;
    }
    
    /* Mejorar legibilidad de texto */
    .rifa-description,
    .rifa-date,
    .rifa-condition {
        line-height: 1.5;
    }
    
    /* Botones más accesibles */
    .btn-primary:active,
    .btn-secondary:active,
    .btn-participate:active {
        transform: scale(0.98);
        transition: transform 0.1s ease;
    }
    
    /* Mejorar modal en móvil */
    .modal {
        padding: 20px;
    }
    
    .modal-content {
        border-radius: 20px;
        overflow: hidden;
    }
    
    /* Footer responsive */
    .footer-content {
        padding: 0 20px;
    }
    
    .footer-divider {
        margin: 2rem 0 1rem;
        padding-top: 1.5rem;
    }
    
    /* Image badges más visibles */
    .image-badge {
        top: 10px;
        right: 10px;
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
    }
    
    /* Mejor spacing entre secciones */
    .features {
        margin-top: 0;
    }
    
    .rifas-section {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    }
    
    /* Hero más atractivo en móvil */
    .hero::before {
        background-size: 150px 150px;
    }
    
    /* Cards con mejor jerarquía visual */
    .rifa-card::before,
    .feature-card::before {
        height: 3px;
    }
    
    /* Mejorar no-rifas en móvil */
    .no-rifas {
        text-align: center;
    }
    
    .no-rifas .btn-primary {
        margin-top: 1rem;
        width: 100%;
        max-width: 280px;
    }
}

/* ===== LANDSCAPE MODE EN MÓVILES ===== */
@media (max-width: 767px) and (orientation: landscape) {
    .hero {
        padding: 2rem 0;
    }
    
    .hero-title {
        font-size: 2rem;
        margin-bottom: 0.8rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .hero-buttons {
        gap: 1rem;
    }
    
    .btn-primary,
    .btn-secondary {
        padding: 0.8rem 1.5rem;
        font-size: 0.9rem;
    }
    
    .features,
    .rifas-section,
    .quick-links {
        padding: 2rem 0;
    }
    
    .section-title {
        font-size: 1.8rem;
        margin-bottom: 0.8rem;
    }
    
    .section-subtitle {
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
}

/* ===== OPTIMIZACIONES DE RENDIMIENTO ===== */
@media (max-width: 767px) {
    /* Reducir motion en dispositivos lentos */
    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
    
    /* Optimizar will-change */
    .rifa-card,
    .feature-card,
    .quick-link {
        will-change: transform;
    }
    
    .rifa-card:hover,
    .feature-card:hover,
    .quick-link:hover {
        will-change: auto;
    }
}

/* ===== DARK MODE SUPPORT ===== */
@media (max-width: 767px) and (prefers-color-scheme: dark) {
    .hero-subtitle,
    .section-subtitle,
    .feature-description,
    .quick-link p {
        color: #ccc;
    }
    
    .rifa-card,
    .feature-card,
    .quick-link {
        border-color: rgba(212, 175, 55, 0.3);
    }
}

/* ===== LOADING STATES PARA MÓVIL ===== */
@media (max-width: 767px) {
    /* Skeleton loading para imágenes */
    .rifa-image:empty {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    
    /* Estados de loading para botones */
    .btn-primary.loading,
    .btn-secondary.loading,
    .btn-participate.loading {
        position: relative;
        color: transparent;
    }
    
    .btn-primary.loading::after,
    .btn-secondary.loading::after,
    .btn-participate.loading::after {
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

/* ===== HIGH DPI DISPLAYS ===== */
@media (max-width: 767px) and (-webkit-min-device-pixel-ratio: 2), 
       (max-width: 767px) and (min-resolution: 192dpi) {
    /* Mejorar calidad visual en pantallas retina */
    .logo-img {
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }
    
    /* Bordes más nítidos */
    .rifa-card,
    .feature-card,
    .quick-link,
    .btn-primary,
    .btn-secondary {
        border-width: 0.5px;
    }
}

/* ===== ACCESIBILIDAD MEJORADA ===== */
@media (max-width: 767px) {
    /* Focus visible mejorado */
    .btn-primary:focus,
    .btn-secondary:focus,
    .btn-participate:focus,
    .quick-link:focus,
    .nav-links a:focus {
        outline: 3px solid #d4af37;
        outline-offset: 2px;
    }
    
    /* Mejor contraste para texto */
    .rifa-title,
    .feature-title,
    .quick-link h3 {
        color: #1a1a1a;
    }
    
    /* Skip link para navegación con teclado */
    .skip-link {
        position: absolute;
        top: -40px;
        left: 6px;
        background: #d4af37;
        color: white;
        padding: 8px;
        text-decoration: none;
        border-radius: 4px;
        z-index: 1000;
    }
    
    .skip-link:focus {
        top: 6px;
    }
}

/* ===== PRINT STYLES ===== */
@media print {
    .header,
    .nav-links,
    .mobile-menu-toggle,
    .hero-buttons,
    .btn-primary,
    .btn-secondary,
    .btn-participate,
    .quick-links,
    .footer {
        display: none !important;
    }
    
    .hero {
        padding: 1rem 0;
    }
    
    .hero-title {
        color: #000 !important;
        font-size: 2rem;
    }
    
    .rifa-card,
    .feature-card {
        break-inside: avoid;
        margin-bottom: 1rem;
    }
}
/* ====== FIX PARA ELIMINAR SCROLL HORIZONTAL ====== */

/* Contenedor principal que previene overflow horizontal */
html, body {
    overflow-x: hidden;
    max-width: 100vw;
    position: relative;
}

/* Asegurar que ningún elemento se salga */
* {
    max-width: 100%;
    box-sizing: border-box;
}

/* Fix específicos para elementos que causan overflow */
@media (max-width: 767px) {
    /* Hero section contenida */
    .hero {
        overflow: hidden;
        width: 100%;
    }
    
    .hero::before {
        width: 100%;
        max-width: 100%;
    }
    
    /* Contenedores principales */
    .container,
    .hero-content,
    .footer-content {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }
    
    /* Cards que no se salgan */
    .rifa-card,
    .feature-card,
    .quick-link {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    /* Grids contenidos */
    .rifas-grid,
    .features-grid,
    .quick-grid {
        width: 100%;
        overflow: hidden;
    }
    
    /* Hero title sin desbordamiento */
    .hero-title {
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
    }
    
    /* Botones contenidos */
    .hero-buttons {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }
    
    .btn-primary,
    .btn-secondary {
        max-width: 100%;
        box-sizing: border-box;
    }
    
    /* Navbar sin overflow */
    .navbar {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }
    
    /* Texto que no se desborde */
    .section-title,
    .section-subtitle,
    .rifa-title,
    .feature-title {
        word-wrap: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
    }
    
    /* Modal contenido */
    .modal-content {
        max-width: calc(100vw - 40px);
        box-sizing: border-box;
    }
    
    /* Nav links móvil sin problemas */
    .nav-links {
        max-width: 280px;
        box-sizing: border-box;
    }
}

/* Viewport específico para móviles */
@media (max-width: 767px) {
    /* Asegurar que el viewport sea respetado */
    .header,
    .hero,
    .features,
    .rifas-section,
    .quick-links,
    .footer {
        width: 100vw;
        max-width: 100vw;
        overflow-x: hidden;
    }
    
    /* Padding que respete el viewport */
    .container {
        padding-left: min(20px, 5vw);
        padding-right: min(20px, 5vw);
    }
    
    /* Text que no cause overflow */
    p, h1, h2, h3, h4, h5, h6 {
        word-break: break-word;
        hyphens: auto;
    }
}

/* Fix para elementos específicos problemáticos */
@media (max-width: 767px) {
    /* Gradientes que no se salgan */
    body {
        background-attachment: fixed;
        background-size: cover;
        background-repeat: no-repeat;
    }
    
    /* Animaciones contenidas */
    @keyframes float {
        0%, 100% { 
            transform: translateY(0) rotate(0deg);
        }
        50% { 
            transform: translateY(-10px) rotate(180deg);
        }
    }
    
    /* Cards hover sin salirse */
    .rifa-card:hover,
    .feature-card:hover,
    .quick-link:hover {
        transform: translateY(-5px);
        /* Removido scale para evitar overflow */
    }
}

/* Debugging: remover después de verificar */
/* 
@media (max-width: 767px) {
    * {
        border: 1px solid red !important;
    }
}
*/
/* ====== FIX PARA MENÚ HAMBURGUESA ====== */

/* Quitar overflow-x SOLO del nav-links cuando está cerrado */
@media (max-width: 768px) {
    /* Permitir que el menú se muestre fuera del viewport */
    .nav-links {
        max-width: 280px;
        box-sizing: border-box;
        /* REMOVER overflow: hidden */
    }
    
    /* Asegurar que el menú funcione correctamente */
    .nav-links.active {
        right: 0 !important;
        overflow: visible;
    }
    
    /* Contenedor del navbar puede tener overflow en X cuando el menú está abierto */
    .navbar {
        width: 100%;
        max-width: 100%;
        overflow: visible; /* Cambiar de hidden a visible */
    }
    
    /* Header debe permitir que el menú se vea */
    .header {
        overflow: visible; /* Cambiar de hidden a visible */
    }
    
    /* Solo prevenir overflow horizontal en el contenido principal */
    .hero,
    .features,
    .rifas-section,
    .quick-links,
    .footer {
        overflow-x: hidden;
    }
}

/* Mantener el overflow-x hidden solo en elementos que lo necesitan */
@media (max-width: 767px) {
    /* HTML y body siguen con overflow-x hidden */
    html {
        overflow-x: hidden;
    }
    
    body {
        overflow-x: hidden;
        /* Pero permitir que el menú se muestre */
    }
    
    /* Cuando el menú está abierto, permitir scroll en X temporalmente */
    body.menu-open {
        overflow-x: visible;
    }
}
/* ====== BOTONES ALREDEDOR DEL TÍTULO ====== */

/* Contenedor para el título y botones */
.hero-title-container {
    position: relative !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 2rem !important;
    margin-bottom: 1.5rem !important;
}

/* El título en el centro */
.hero-title-container .hero-title {
    margin: 0 !important;
    flex-shrink: 0 !important;
}

/* Botón izquierdo */
.btn-left {
    position: absolute !important;
    left: -200px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    padding: 1rem 2rem !important;
    font-size: 1rem !important;
    border-radius: 15px !important;
    white-space: nowrap !important;
}

/* Botón derecho */
.btn-right {
    position: absolute !important;
    right: -200px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    padding: 1rem 2rem !important;
    font-size: 1rem !important;
    border-radius: 15px !important;
    white-space: nowrap !important;
}

/* Ocultar los botones originales de abajo */
.hero-buttons {
    display: none !important;
}

/* Para pantallas medianas */
@media (max-width: 1400px) {
    .btn-left {
        left: -150px !important;
        padding: 0.8rem 1.5rem !important;
        font-size: 0.9rem !important;
    }
    
    .btn-right {
        right: -150px !important;
        padding: 0.8rem 1.5rem !important;
        font-size: 0.9rem !important;
    }
}

/* Para tablets */
@media (max-width: 1200px) {
    .btn-left {
        left: -100px !important;
        padding: 0.7rem 1.2rem !important;
        font-size: 0.8rem !important;
    }
    
    .btn-right {
        right: -100px !important;
        padding: 0.7rem 1.2rem !important;
        font-size: 0.8rem !important;
    }
}

/* Para móviles - mover los botones abajo del título */
@media (max-width: 768px) {
    .hero-title-container {
        flex-direction: column !important;
        gap: 1.5rem !important;
    }
    
    .btn-left,
    .btn-right {
        position: relative !important;
        left: auto !important;
        right: auto !important;
        top: auto !important;
        transform: none !important;
        padding: 1rem 2rem !important;
        font-size: 1rem !important;
        width: 100% !important;
        max-width: 280px !important;
    }
    
    /* Mostrar los botones originales en móvil si prefieres */
    .hero-buttons {
        display: flex !important;
        flex-direction: column !important;
        gap: 1.5rem !important;
        align-items: center !important;
    }
    
    .btn-left,
    .btn-right {
        display: none !important;
    }
}
/* ====== AJUSTES PARA BOTONES MÁS ANCHOS Y SECCIÓN MENOS ALTA ====== */

/* 1. Reducir altura de la sección hero */
.hero {
    padding: 3rem 0 !important; /* Era 6rem, ahora 3rem */
    min-height: 70vh !important; /* Reducir altura mínima */
}

/* 2. Hacer los botones más anchos */
.btn-left,
.btn-right {
    padding: 1.2rem 3rem !important; /* Más ancho (era 1rem 2rem) */
    font-size: 1.1rem !important; /* Texto un poco más grande */
    min-width: 180px !important; /* Ancho mínimo */
    border-radius: 20px !important; /* Bordes más redondeados */
}

/* Para pantallas medianas */
@media (max-width: 1400px) {
    .btn-left {
        left: -180px !important; /* Ajustar posición para botones más anchos */
        padding: 1rem 2.5rem !important;
    }
    
    .btn-right {
        right: -180px !important; /* Ajustar posición para botones más anchos */
        padding: 1rem 2.5rem !important;
    }
}

/* Para tablets */
@media (max-width: 1200px) {
    .btn-left {
        left: -120px !important;
        padding: 0.9rem 2rem !important;
        min-width: 150px !important;
    }
    
    .btn-right {
        right: -120px !important;
        padding: 0.9rem 2rem !important;
        min-width: 150px !important;
    }
}

/* Ajustar espaciado del hero-content */
.hero-content {
    padding: 0 2rem !important;
}

/* Reducir margen del título */
.hero-title-container {
    margin-bottom: 1rem !important; /* Era 1.5rem */
}

/* Reducir margen del subtítulo */
.hero-subtitle {
    margin-bottom: 2rem !important; /* Era 3rem */
}

/* Para móviles mantener el tamaño normal */
@media (max-width: 768px) {
    .hero {
        padding: 2rem 0 !important; /* Aún más compacto en móvil */
        min-height: 60vh !important;
    }
    
    .btn-left,
    .btn-right {
        padding: 1rem 2rem !important;
        min-width: auto !important;
        width: 100% !important;
        max-width: 280px !important;
    }
}
/* ====== HERO SECTION AÚN MÁS BAJA ====== */

/* Hacer la sección hero súper compacta */
.hero {
    padding: 2rem 0 !important; /* Aún menos altura (era 6rem originalmente) */
    min-height: 50vh !important; /* Altura mínima más baja */
}

/* Reducir todos los espacios internos */
.hero-badge {
    margin-bottom: 1.5rem !important; /* Era 2rem */
    padding: 0.6rem 1.5rem !important; /* Más compacto */
    font-size: 1.2rem !important; /* Texto más pequeño */
}

.hero-title-container {
    margin-bottom: 0.8rem !important; /* Era 1.5rem */
}

.hero-title {
    font-size: clamp(2rem, 6vw, 3.5rem) !important; /* Título más pequeño */
    margin-bottom: 0 !important;
}

.hero-subtitle {
    font-size: 1.2rem !important; /* Era 1.4rem */
    margin-bottom: 1.5rem !important; /* Era 3rem */
}

/* Para móviles aún más compacto */
@media (max-width: 768px) {
    .hero {
        padding: 1.5rem 0 !important; /* Súper compacto en móvil */
        min-height: 40vh !important;
    }
    
    .hero-badge {
        margin-bottom: 1rem !important;
        padding: 0.5rem 1.2rem !important;
        font-size: 0.8rem !important;
    }
    
    .hero-title {
        font-size: 2rem !important;
    }
    
    .hero-subtitle {
        font-size: 1rem !important;
        margin-bottom: 1rem !important;
    }
}
/* ====== BOTONES FLOTANTES DE REDES SOCIALES ====== */

/* Contenedor principal de botones flotantes */
.floating-social {
    position: fixed;
    right: 25px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1500;
    display: flex;
    flex-direction: column;
    gap: 15px;
    animation: floatUpDown 3s ease-in-out infinite;
}

/* Botón individual */
.floating-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: white;
    font-size: 1.8rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
}

/* Botón Facebook */
.floating-btn.facebook {
    background: linear-gradient(135deg, #1877f2, #42a5f5);
}

.floating-btn.facebook:hover {
    background: linear-gradient(135deg, #166fe5, #1976d2);
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 15px 40px rgba(24, 119, 242, 0.4);
}

/* Botón Instagram */
.floating-btn.instagram {
    background: linear-gradient(135deg, #405de6, #5851db, #833ab4, #c13584, #e1306c, #fd1d1d, #f56040, #f77737, #fcaf45, #ffdc80);
}

.floating-btn.instagram:hover {
    background: linear-gradient(135deg, #833ab4, #fd1d1d);
    transform: scale(1.1) rotate(-5deg);
    box-shadow: 0 15px 40px rgba(225, 48, 108, 0.4);
}

/* Efecto de brillo */
.floating-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.6s;
}

.floating-btn:hover::before {
    left: 100%;
}

/* Animación de flotación */
@keyframes floatUpDown {
    0%, 100% {
        transform: translateY(-50%);
    }
    50% {
        transform: translateY(calc(-50% - 10px));
    }
}

/* Efecto de pulso alternado */
.floating-btn:nth-child(1) {
    animation-delay: 0s;
}

.floating-btn:nth-child(2) {
    animation-delay: 1.5s;
}

.floating-btn {
    animation: floatUpDown 3s ease-in-out infinite, pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    50% {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25), 0 0 20px rgba(255, 255, 255, 0.3);
    }
}

/* Responsivo para móviles */
@media (max-width: 768px) {
    .floating-social {
        right: 15px;
        gap: 12px;
    }
    
    .floating-btn {
        width: 50px;
        height: 50px;
        font-size: 1.4rem;
    }
}

@media (max-width: 480px) {
    .floating-social {
        right: 10px;
        top: auto;
        bottom: 100px;
        transform: none;
    }
    
    .floating-btn {
        width: 45px;
        height: 45px;
        font-size: 1.2rem;
    }
}

/* Ocultar en landscape móvil para no interferir */
@media (max-width: 768px) and (orientation: landscape) {
    .floating-social {
        display: none;
    }
}

/* Tooltips */
.floating-btn::after {
    content: attr(data-tooltip);
    position: absolute;
    right: 70px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    pointer-events: none;
}

.floating-btn:hover::after {
    opacity: 1;
    visibility: visible;
    right: 75px;
}

/* Flecha del tooltip */
.floating-btn::before {
    content: '';
    position: absolute;
    right: 65px;
    top: 50%;
    transform: translateY(-50%);
    border-left: 6px solid rgba(0, 0, 0, 0.8);
    border-top: 6px solid transparent;
    border-bottom: 6px solid transparent;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.floating-btn:hover::before {
    opacity: 1;
    visibility: visible;
    right: 69px;
}

/* Ocultar tooltips en móvil */
@media (max-width: 768px) {
    .floating-btn::after,
    .floating-btn::before {
        display: none;
    }
}
/* Partículas de fondo */
.particles-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: -1;
    overflow: hidden;
}

.particle {
    position: absolute;
    background: linear-gradient(45deg, #d4af37, #f4e794);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
    opacity: 0.7;
}

@keyframes float {
    0%, 100% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
    10% { opacity: 0.7; }
    90% { opacity: 0.7; }
    100% { transform: translateY(-100px) rotate(360deg); opacity: 0; }
}

</style>

<!-- AGREGAR ESTE JAVASCRIPT AL FINAL, ANTES DE </body> -->
<script>
// Función para toggle del menú móvil
function toggleMobileMenu() {
    const navLinks = document.getElementById('navLinks');
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const header = document.querySelector('.header');
    const body = document.body;
    
    // Toggle clases
    navLinks.classList.toggle('active');
    menuToggle.classList.toggle('active');
    header.classList.toggle('menu-open');
    body.classList.toggle('menu-open');
    
    // Crear/remover overlay
    let overlay = document.querySelector('.nav-overlay');
    
    if (navLinks.classList.contains('active')) {
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'nav-overlay';
            overlay.onclick = closeMobileMenu;
            document.body.appendChild(overlay);
        }
        setTimeout(() => overlay.classList.add('active'), 10);
    } else {
        if (overlay) {
            overlay.classList.remove('active');
            setTimeout(() => overlay.remove(), 300);
        }
    }
}

// Función para cerrar el menú móvil
function closeMobileMenu() {
    const navLinks = document.getElementById('navLinks');
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const header = document.querySelector('.header');
    const body = document.body;
    const overlay = document.querySelector('.nav-overlay');
    
    navLinks.classList.remove('active');
    menuToggle.classList.remove('active');
    header.classList.remove('menu-open');
    body.classList.remove('menu-open');
    
    if (overlay) {
        overlay.classList.remove('active');
        setTimeout(() => overlay.remove(), 300);
    }
}

// Cerrar menú al hacer clic en un enlace
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-links a');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Solo cerrar en móvil
            if (window.innerWidth <= 768) {
                closeMobileMenu();
            }
        });
    });
    
    // Cerrar menú al redimensionar ventana
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeMobileMenu();
        }
    });
    
    // Cerrar menú con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMobileMenu();
        }
    });
});

// Efecto suave para el scroll cuando se hace clic en enlaces internos
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            // Cerrar menú móvil si está abierto
            closeMobileMenu();
            
            // Scroll suave con offset para el header sticky
            const headerHeight = document.querySelector('.header').offsetHeight;
            const elementPosition = target.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerHeight;
            
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }
    });
});
</script>
    
   <!-- Hero -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">
            <i class="fas fa-star"></i>
            ¡La Mejor Plataforma de Rifas de México!
        </div>
        
        <!-- NUEVO CONTENEDOR PARA TÍTULO Y BOTONES A LOS LADOS -->
        <div class="hero-title-container">
            <a href="#rifas" class="btn-primary btn-left">
                <i class="fas fa-dice"></i>
                Ver Rifas
            </a>
            
            <h1 class="hero-title">¡Tu Suerte Te Espera!</h1>
            
            <a href="consultar_boletos.php" class="btn-secondary btn-right">
                <i class="fas fa-ticket-alt"></i>
                Mis Boletos
            </a>
        </div>
        
        <p class="hero-subtitle">Participa en las mejores rifas de México y gana premios increíbles que pueden cambiar tu vida para siempre</p>
        
        <!-- BOTONES ORIGINALES (se ocultan en desktop, se muestran en móvil) -->
        <div class="hero-buttons">
            <a href="#rifas" class="btn-primary">
                <i class="fas fa-dice"></i>
                Ver Rifas Disponibles
            </a>
            <a href="consultar_boletos.php" class="btn-secondary">
                <i class="fas fa-ticket-alt"></i>
                Consultar Boletos
            </a>
        </div>
    </div>
</section>
    <!-- Rifas -->
    <section class="rifas-section" id="rifas">
        <div class="container">
            <h2 class="section-title">
<i class="fa-brands fa-font-awesome"></i>
                Rifas Activas
                <i class="fa-brands fa-font-awesome"></i>
            </h2>
            <p class="section-subtitle">Descubre nuestras rifas disponibles y elige la que más te guste. ¡La suerte está esperándote!</p>
            
            <?php if (!empty($rifas)): ?>
            <div class="rifas-grid">
                <?php foreach ($rifas as $rifa): ?>
                    <?php
                    // Verificar si la rifa tiene imagen
                    $tiene_imagen = false;
                    $ruta_imagen = '';
                    
                    if (isset($rifa['imagen_premio']) && !empty($rifa['imagen_premio'])) {
                        $ruta_imagen = 'uploads/rifas/' . $rifa['imagen_premio'];
                        $tiene_imagen = @file_exists($ruta_imagen);
                    }
                    ?>
                    
                <div class="rifa-card">
                    <div class="rifa-image" <?php echo $tiene_imagen ? "onclick=\"openModal('" . htmlspecialchars($ruta_imagen) . "')\"" : ''; ?>>
                        <?php if ($tiene_imagen): ?>
                            <img src="<?php echo htmlspecialchars($ruta_imagen); ?>" 
                                 alt="<?php echo htmlspecialchars($rifa['nombre']); ?>"
                                 loading="lazy">
                            <div class="image-badge">
                                <i class="fas fa-search-plus"></i>
                                Ver imagen
                            </div>
                        <?php else: ?>
                            <div class="default-icon">
                                <i class="fas fa-gift"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="rifa-content">
                        <h3 class="rifa-title"><?php echo htmlspecialchars($rifa['nombre']); ?></h3>
                                                <a href="comprar.php?rifa=<?php echo $rifa['id']; ?>" class="btn-participate">
                            <i class="fas fa-ticket-alt"></i>
                            ¡Participar Ahora!
                        </a>
                       
                        
                        <div class="rifa-date">
                            <i class="fas fa-calendar-alt"></i>
                            <strong>Entrega de premios:</strong> <?php echo date('d/m/Y', strtotime($rifa['fecha_entrega'])); ?>
                        </div>
                        
                        <div class="rifa-condition">
                            <i class="fas fa-info-circle"></i>
                            <strong>Importante:</strong> Venta condicionada al 85% de la venta de boletos. Si no se alcanza ese porcentaje, la fecha de la rifa sera modificada.
                        </div>
                         <?php if (isset($rifa['descripcion_premio']) && !empty($rifa['descripcion_premio'])): ?>
                            <div class="rifa-description">
                                <i class="fas fa-trophy"></i>
                                <strong>Premio:</strong> <?php echo htmlspecialchars($rifa['descripcion_premio']); ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-rifas">
                <h3>
                    <i class="fas fa-clock"></i>
                    Próximamente Nuevas Rifas
                </h3>
                <p>Estamos preparando rifas increíbles con premios espectaculares</p>
                <div class="no-rifas-icon">
                    <i class="fas fa-clover"></i>
                </div>
                <a href="https://wa.me/5218180946816" target="_blank" class="btn-primary">
                    <i class="fab fa-whatsapp"></i>
                    Síguenos en WhatsApp
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Features -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">¿Por qué elegir Éxito Dorado MX?</h2>
            <p class="section-subtitle">Somos la plataforma de rifas más confiable y segura de México, con miles de ganadores satisfechos</p>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="feature-title">Súper Rápido</h3>
                    <p class="feature-description">Compra tus boletos en segundos desde cualquier dispositivo. Proceso simple y sin complicaciones.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h3 class="feature-title">Grandes Premios</h3>
                    <p class="feature-description">Premios espectaculares que pueden cambiar tu vida para siempre. ¡Desde electrónicos hasta casas!</p>
                </div>
            </div>
        </div>
    </section>
    
    
    <!-- Quick Links -->
    <section class="quick-links">
        <div class="container">
            <h2 class="section-title">Acceso Rápido</h2>
            <p class="section-subtitle">Todo lo que necesitas al alcance de un clic</p>
            <div class="quick-grid">
                <a href="consultar_boletos.php" class="quick-link">
                    <div class="quick-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <h3>Consultar Boletos</h3>
                    <p>Revisa tus boletos comprados y descarga comprobantes de participación</p>
                </a>
                <a href="pagos.php" class="quick-link">
                    <div class="quick-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3>Métodos de Pago</h3>
                    <p>Conoce todas las formas de pago disponibles: transferencias, tarjetas y tiendas</p>
                </a>
                <a href="https://wa.me/5218180946816" class="quick-link" target="_blank">
                    <div class="quick-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h3>Soporte WhatsApp</h3>
                    <p>Contacto directo para dudas, soporte y confirmación de pagos 24/7</p>
                </a>
            </div>
        </div>
    </section>
    
  <!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <h3 class="footer-title">ÉXITO DORADO MX</h3>
        <p class="contact-info">
            <i class="fas fa-star"></i>
            La suerte siempre está de tu lado
            <i class="fas fa-star"></i>
        </p>
        <p class="contact-info">
            <i class="fab fa-whatsapp"></i>
            WhatsApp: +52 1 81 8094 6816
        </p>
        <p class="contact-info">
            <i class="fas fa-headset"></i>
            Atención al cliente 24/7
        </p>
        <p class="contact-info">
            <i class="fas fa-shield-alt"></i>
            Rifas 100% seguras y transparentes
        </p>
        
        <!-- Enlaces legales -->
        <div class="footer-links">
            <a href="aviso-privacidad.php" class="privacy-link">
                <i class="fas fa-shield-alt"></i>
                Aviso de Privacidad
            </a>
        </div>
        
        <div class="footer-divider">
            <p>
                <i class="fas fa-copyright"></i>
                2025 Éxito Dorado MX - Todos los derechos reservados
            </p>
        </div>
    </div>
</footer>

<!-- CSS adicional para el enlace de privacidad -->
<style>
/* Estilos para los enlaces del footer */
.footer-links {
    margin: 2rem 0;
    text-align: center;
}

.privacy-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #e9ecef;
    text-decoration: none;
    padding: 0.8rem 1.5rem;
    border: 2px solid rgba(212, 175, 55, 0.3);
    border-radius: 15px;
    background: rgba(212, 175, 55, 0.1);
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 0.95rem;
    backdrop-filter: blur(10px);
}

.privacy-link:hover {
    color: white;
    background: linear-gradient(135deg, #d4af37, #f4e794);
    border-color: #d4af37;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
    text-decoration: none;
}

.privacy-link:active {
    transform: translateY(0) scale(0.98);
}

/* Responsive para móviles */
@media (max-width: 768px) {
    .footer-links {
        margin: 1.5rem 0;
    }
    
    .privacy-link {
        padding: 0.7rem 1.2rem;
        font-size: 0.9rem;
        width: 100%;
        max-width: 250px;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .privacy-link {
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
    }
}

/* Efecto de brillo */
.privacy-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s;
    border-radius: 15px;
}

.privacy-link {
    position: relative;
    overflow: hidden;
}

.privacy-link:hover::before {
    left: 100%;
}

/* Variante con múltiples enlaces legales (por si quieres agregar más) */
.footer-links-multiple {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin: 2rem 0;
}

.footer-links-multiple .privacy-link {
    flex: 0 0 auto;
    min-width: 180px;
}

@media (max-width: 768px) {
    .footer-links-multiple {
        flex-direction: column;
        align-items: center;
        gap: 0.8rem;
    }
    
    .footer-links-multiple .privacy-link {
        width: 100%;
        max-width: 250px;
    }
}
</style>
    <!-- Modal para mostrar imagen completa -->
    <div id="imageModal" class="modal" onclick="closeModal()">
        <span class="modal-close" onclick="closeModal()">
            <i class="fas fa-times"></i>
        </span>
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
            
            // Efecto de aparición
            modal.style.animation = 'fadeIn 0.3s ease-out';
        }
        
        function closeModal() {
            const modal = document.getElementById('imageModal');
            modal.style.animation = 'fadeOut 0.3s ease-out';
            
            setTimeout(() => {
                modal.style.display = 'none';
                // Restaurar scroll del body
                document.body.style.overflow = 'auto';
            }, 300);
        }
        
        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
        
        // Scroll suave para enlaces internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Animación de entrada para elementos cuando aparecen en viewport
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observar elementos para animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.feature-card, .rifa-card, .quick-link').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
            
            // Efecto de parallax sutil para el hero
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const hero = document.querySelector('.hero');
                if (hero) {
                    const rate = scrolled * -0.5;
                    hero.style.transform = `translateY(${rate}px)`;
                }
            });
        });
        
        // Agregar CSS adicional para animaciones
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
            
            /* Efecto de hover mejorado para botones */
            .btn-primary:active,
            .btn-secondary:active,
            .btn-participate:active {
                transform: translateY(-2px) scale(0.98) !important;
            }
            
            /* Efecto de brillo para las cards */
            .rifa-card:hover::after {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
                animation: shine 0.6s ease-out;
                pointer-events: none;
            }
            
            @keyframes shine {
                to {
                    left: 100%;
                }
            }
            
            /* Mejoras para el modal */
            .modal-content {
                animation: zoomIn 0.3s ease-out;
            }
            
            @keyframes zoomIn {
                from {
                    opacity: 0;
                    transform: translate(-50%, -50%) scale(0.8);
                }
                to {
                    opacity: 1;
                    transform: translate(-50%, -50%) scale(1);
                }
            }
            
            /* Efecto de typing para el hero title */
            .hero-title {
                overflow: hidden;
                white-space: nowrap;
                animation: typing 3s steps(40, end), blink-caret 0.75s step-end infinite;
                border-right: 3px solid #d4af37;
            }
            
            @keyframes typing {
                from { width: 0 }
                to { width: 100% }
            }
            
            @keyframes blink-caret {
                from, to { border-color: transparent }
                50% { border-color: #d4af37 }
            }
            
            /* Responsive adjustments */
            @media (max-width: 768px) {
                .hero-title {
                    white-space: normal;
                    border-right: none;
                    animation: fadeInUp 1s ease-out;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Efecto adicional para botones flotantes
document.addEventListener('DOMContentLoaded', function() {
    const floatingBtns = document.querySelectorAll('.floating-btn');
    
    floatingBtns.forEach((btn, index) => {
        // Animación de aparición escalonada
        btn.style.opacity = '0';
        btn.style.transform = 'translateX(100px)';
        
        setTimeout(() => {
            btn.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            btn.style.opacity = '1';
            btn.style.transform = 'translateX(0)';
        }, 500 + (index * 200));
    });
    
    // Ocultar/mostrar botones al hacer scroll
    let lastScrollTop = 0;
    const floatingContainer = document.querySelector('.floating-social');
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > lastScrollTop && scrollTop > 200) {
            // Scrolling down - hide buttons
            floatingContainer.style.transform = 'translateX(100px) translateY(-50%)';
            floatingContainer.style.opacity = '0.3';
        } else {
            // Scrolling up - show buttons
            floatingContainer.style.transform = 'translateX(0) translateY(-50%)';
            floatingContainer.style.opacity = '1';
        }
        
        lastScrollTop = scrollTop;
    });
});
// Efectos de sonido
function playSound(soundId) {
    const audio = document.getElementById(soundId);
    if (audio) {
        audio.currentTime = 0;
        audio.play().catch(e => console.log('Audio play failed:', e));
    }
}

// Añadir sonidos a botones
document.querySelectorAll('.btn-primary, .btn-secondary, .btn-participate').forEach(btn => {
    btn.addEventListener('click', () => playSound('successSound'));
    btn.addEventListener('mouseenter', () => playSound('hoverSound'));
});
    </script>
    <!-- Partículas mágicas -->
<div class="particles-container" id="particles"></div>

<script>
// Crear partículas
function createParticles() {
    const container = document.getElementById('particles');
    
    setInterval(() => {
        const particle = document.createElement('div');
        particle.className = 'particle';
        
        const size = Math.random() * 6 + 2;
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDuration = (Math.random() * 3 + 4) + 's';
        particle.style.animationDelay = Math.random() * 2 + 's';
        
        container.appendChild(particle);
        
        setTimeout(() => {
            particle.remove();
        }, 8000);
    }, 300);
}

createParticles();
// Efecto de confeti
function createConfetti() {
    const colors = ['#f43f5e', '#10b981', '#3b82f6', '#8b5cf6', '#f59e0b'];
    
    for (let i = 0; i < 50; i++) {
        const confetti = document.createElement('div');
        confetti.style.position = 'fixed';
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.top = '-10px';
        confetti.style.width = Math.random() * 10 + 5 + 'px';
        confetti.style.height = confetti.style.width;
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.borderRadius = '50%';
        confetti.style.pointerEvents = 'none';
        confetti.style.zIndex = '2000';
        confetti.style.animation = `confettiFall ${Math.random() * 3 + 2}s linear forwards`;
        
        document.body.appendChild(confetti);
        
        setTimeout(() => confetti.remove(), 5000);
    }
}

// CSS para el confeti
const confettiStyle = document.createElement('style');
confettiStyle.textContent = `
    @keyframes confettiFall {
        to {
            transform: translateY(100vh) rotate(720deg);
            opacity: 0;
        }
    }
`;
document.head.appendChild(confettiStyle);

// Añadir confeti a botones de participar
document.querySelectorAll('.btn-participate').forEach(btn => {
    btn.addEventListener('click', createConfetti);
});
</script>

    <!-- Audio effects -->
<audio id="successSound" preload="auto">
    <source src="data:audio/wav;base64,UklGRvIGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU4GAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMZBSWo4/hpUBELRKGk8sB2KAQggtH23XBAG" type="audio/wav">
</audio>

<audio id="hoverSound" preload="auto">
    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YVoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmMZBSWo4/hpUBELRKGk8sB2KAQggtH23XBAG=" type="audio/wav">
</audio>
    <!-- Botones flotantes de redes sociales -->
<div class="floating-social">
    <a href="https://www.facebook.com/share/1DusMS1U2S/" 
       target="_blank" 
       class="floating-btn facebook"
       data-tooltip="Síguenos en Facebook">
        <i class="fab fa-facebook-f"></i>
    </a>
    
    <a href="https://www.instagram.com/exitodoradomx?igsh=MWZ6OXJreThoeG1sOA==" 
       target="_blank" 
       class="floating-btn instagram"
       data-tooltip="Síguenos en Instagram">
        <i class="fab fa-instagram"></i>
    </a>
</div>
</body>
</html>
           