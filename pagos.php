<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Métodos de Pago - ÉXITO DORADO MX</title>
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
        
        /* Header profesional con logo */
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
            width: 60px;
            height: 60px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
            border: 2px solid #d4af37;
        }
        
        .brand-info h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: #2c2c2c;
            margin-bottom: 0.2rem;
        }
        
        .brand-info p {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }
        
        .security-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #4caf50, #2e7d32);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        
        .page-title {
            background: white;
            margin: 2rem auto;
            max-width: 1200px;
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
        }
        
        .page-title h2 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #2c2c2c;
            margin-bottom: 0.5rem;
        }
        
        .page-title p {
            font-size: 1.1rem;
            color: #666;
            font-weight: 500;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Alert de seguridad */
        .security-alert {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border: 1px solid #d4af37;
            color: #856404;
            padding: 1.5rem;
            border-radius: 15px;
            margin: 2rem 0;
            text-align: center;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .security-alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #d4af37, #f4e794, #d4af37);
        }
        
        .security-alert i {
            font-size: 1.2rem;
            margin-right: 0.5rem;
        }
        
        .metodos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        
        /* Cards profesionales */
        .metodo-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .metodo-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d4af37, #f4e794, #d4af37);
        }
        
        .metodo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        
        .metodo-title {
            color: #2c2c2c;
            font-size: 1.6rem;
            margin-bottom: 1.5rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            font-weight: 700;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f5f5f5;
        }
        
        .metodo-title i {
            color: #d4af37;
            font-size: 1.8rem;
        }
        
        /* Información bancaria profesional */
        .banco-info {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 15px;
            padding: 2rem;
            margin: 1.5rem 0;
            position: relative;
        }
        
        .banco-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
        }
        
        .banco-logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #d4af37, #f4e794);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .banco-header h4 {
            color: #2c2c2c;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .info-row:hover {
            border-color: #d4af37;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.1);
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-label i {
            color: #d4af37;
        }
        
        .info-value {
            color: #2c2c2c;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .numero-cuenta {
            background: #f8f9fa;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            font-size: 1rem;
            letter-spacing: 1px;
        }
        
        /* Botón de copiar moderno */
        .btn-copy {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-copy:hover {
            background: linear-gradient(135deg, #20c997, #17a2b8);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        /* Instrucciones claras */
        .instrucciones {
            background: white;
            border: 1px solid #e9ecef;
            border-left: 4px solid #17a2b8;
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .instrucciones h4 {
            color: #17a2b8;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .instrucciones ol {
            counter-reset: step-counter;
            list-style: none;
            margin-left: 0;
        }
        
        .instrucciones li {
            counter-increment: step-counter;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #17a2b8;
            position: relative;
            padding-left: 4rem;
        }
        
        .instrucciones li::before {
            content: counter(step-counter);
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: #17a2b8;
            color: white;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        /* Sección de contacto confiable */
        .contacto {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            margin: 3rem 0;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
            position: relative;
        }
        
        .contacto::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #25d366, #128c7e, #25d366);
        }
        
        .contacto h3 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #2c2c2c;
            font-weight: 800;
        }
        
        .contacto p {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
        }
        
        .whatsapp-btn {
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: white;
            padding: 1.5rem 3rem;
            border-radius: 15px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            margin: 1rem;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.3);
        }
        
        .whatsapp-btn:hover {
            background: linear-gradient(135deg, #128c7e, #075e54);
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(37, 211, 102, 0.4);
        }
        
        .telefono-info {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
            display: inline-block;
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
        }
        
        .volver a:hover {
            background: #d4af37;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
        }
        
        /* Badges de confianza */
        .trust-badges {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }
        
        .trust-badge {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #495057;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .trust-badge i {
            color: #28a745;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header {
                padding: 1rem 0;
            }
            
            .header-content {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            
            .logo {
                width: 40px;
                height: 40px;
            }
            
            .brand-info h1 {
                font-size: 1.3rem;
            }
            
            .brand-info p {
                font-size: 0.8rem;
            }
            
            .security-badge {
                font-size: 0.7rem;
                padding: 0.3rem 0.6rem;
            }
            
            .page-title {
                margin: 1rem auto;
                padding: 1.5rem;
            }
            
            .page-title h2 {
                font-size: 1.8rem;
            }
            
            .page-title p {
                font-size: 1rem;
            }
            
            .metodos-grid {
                grid-template-columns: 1fr;
            }
            
            .info-row {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .metodo-card {
                padding: 1.5rem;
            }
            
            .whatsapp-btn {
                padding: 1rem 2rem;
                font-size: 1rem;
            }
            
            .contacto {
                padding: 2rem;
            }
            
            .trust-badges {
                flex-direction: column;
                align-items: center;
            }
        }
        
        /* Animaciones suaves */
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
        
        .metodo-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .metodo-card:nth-child(2) {
            animation-delay: 0.1s;
        }
        
        .metodo-card:nth-child(3) {
            animation-delay: 0.2s;
        }
        
        /* ====== RESPONSIVIDAD COMPLETA ====== */

/* Breakpoints definidos */
/* xs: 0-479px (móviles pequeños) */
/* sm: 480-767px (móviles grandes) */
/* md: 768-1023px (tablets) */
/* lg: 1024-1199px (tablets grandes/laptops pequeñas) */
/* xl: 1200px+ (desktop) */

/* ====== MÓVILES EXTRA PEQUEÑOS (0-479px) ====== */
@media (max-width: 479px) {
    /* Contenedor principal */
    .container {
        padding: 10px;
    }
    
    /* Header ultra compacto */
    .header {
        padding: 0.5rem 0;
    }
    
    .header-content {
        padding: 0 10px;
        gap: 0.3rem;
    }
    
    .logo {
        width: 35px;
        height: 35px;
    }
    
    .brand-info h1 {
        font-size: 1.1rem;
        line-height: 1.2;
    }
    
    .brand-info p {
        font-size: 0.7rem;
    }
    
    .security-badge {
        font-size: 0.6rem;
        padding: 0.2rem 0.5rem;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    /* Título de página */
    .page-title {
        margin: 0.5rem auto;
        padding: 1rem;
        border-radius: 15px;
    }
    
    .page-title h2 {
        font-size: 1.4rem;
        line-height: 1.3;
    }
    
    .page-title p {
        font-size: 0.9rem;
    }
    
    /* Grid de métodos - una sola columna */
    .metodos-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
        margin: 1.5rem 0;
    }
    
    /* Cards más compactas */
    .metodo-card {
        padding: 1rem;
        border-radius: 15px;
    }
    
    .metodo-title {
        font-size: 1.2rem;
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
    
    .metodo-title i {
        font-size: 1.5rem;
    }
    
    /* Información bancaria más compacta */
    .banco-info {
        padding: 1rem;
        margin: 1rem 0;
        border-radius: 10px;
    }
    
    .banco-header {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .banco-logo {
        width: 40px;
        height: 40px;
        font-size: 1rem;
        margin: 0 auto;
    }
    
    .banco-header h4 {
        font-size: 1.1rem;
        margin: 0;
    }
    
    /* Filas de información apiladas */
    .info-row {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
        padding: 0.8rem;
        margin-bottom: 0.8rem;
    }
    
    .info-label {
        font-size: 0.9rem;
        justify-content: center;
    }
    
    .info-value {
        flex-direction: column;
        gap: 0.5rem;
        align-items: center;
    }
    
    .numero-cuenta {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        word-break: break-all;
        text-align: center;
        min-width: 100%;
        box-sizing: border-box;
    }
    
    .btn-copy {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
        width: 100%;
        justify-content: center;
    }
    
    /* Instrucciones más compactas */
    .instrucciones {
        padding: 1rem;
        margin: 1.5rem 0;
        border-radius: 10px;
    }
    
    .instrucciones h4 {
        font-size: 1.1rem;
        margin-bottom: 1rem;
        text-align: center;
        flex-direction: column;
        gap: 0.3rem;
    }
    
    .instrucciones li {
        padding: 0.8rem;
        padding-left: 3rem;
        margin-bottom: 0.8rem;
        font-size: 0.9rem;
        line-height: 1.5;
    }
    
    .instrucciones li::before {
        width: 1.5rem;
        height: 1.5rem;
        font-size: 0.8rem;
        left: 0.8rem;
    }
    
    /* Contacto más compacto */
    .contacto {
        padding: 1.5rem;
        margin: 2rem 0;
        border-radius: 15px;
    }
    
    .contacto h3 {
        font-size: 1.4rem;
        margin-bottom: 0.8rem;
    }
    
    .contacto p {
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }
    
    .whatsapp-btn {
        padding: 1rem;
        font-size: 0.9rem;
        width: 100%;
        justify-content: center;
        margin: 0.5rem 0;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .telefono-info {
        padding: 0.8rem;
        font-size: 0.9rem;
        width: 100%;
        box-sizing: border-box;
    }
    
    /* Badges de confianza apilados */
    .trust-badges {
        flex-direction: column;
        gap: 0.8rem;
        margin: 1.5rem 0;
    }
    
    .trust-badge {
        padding: 0.8rem 1rem;
        font-size: 0.8rem;
        justify-content: center;
        text-align: center;
    }
    
    /* Alerta de seguridad */
    .security-alert {
        padding: 1rem;
        margin: 1.5rem 0;
        font-size: 0.9rem;
        border-radius: 10px;
    }
    
    /* Botón volver */
    .volver {
        margin: 2rem 0;
    }
    
    .volver a {
        padding: 0.8rem 1.5rem;
        font-size: 0.9rem;
        border-radius: 10px;
    }
}

/* ====== MÓVILES GRANDES (480-767px) ====== */
@media (min-width: 480px) and (max-width: 767px) {
    .container {
        padding: 15px;
    }
    
    .header-content {
        padding: 0 15px;
    }
    
    .logo {
        width: 45px;
        height: 45px;
    }
    
    .brand-info h1 {
        font-size: 1.4rem;
    }
    
    .brand-info p {
        font-size: 0.85rem;
    }
    
    .page-title h2 {
        font-size: 2rem;
    }
    
    .metodo-card {
        padding: 1.5rem;
    }
    
    .banco-header {
        flex-direction: row;
        text-align: left;
    }
    
    .banco-logo {
        margin: 0;
    }
    
    .info-row {
        flex-direction: row;
        text-align: left;
    }
    
    .info-value {
        flex-direction: row;
        align-items: center;
    }
    
    .numero-cuenta {
        font-size: 0.9rem;
        min-width: auto;
    }
    
    .btn-copy {
        width: auto;
    }
    
    .whatsapp-btn {
        flex-direction: row;
        width: auto;
        display: inline-flex;
    }
    
    .trust-badges {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
    }
}

/* ====== TABLETS (768-1023px) ====== */
@media (min-width: 768px) and (max-width: 1023px) {
    .container {
        padding: 20px;
    }
    
    .header-content {
        flex-direction: row;
        text-align: left;
    }
    
    .metodos-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .metodo-card {
        padding: 2rem;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .banco-info {
        padding: 1.5rem;
    }
    
    .info-row {
        flex-direction: row;
        justify-content: space-between;
    }
    
    .contacto {
        padding: 2.5rem;
        max-width: 600px;
        margin: 3rem auto;
    }
    
    .page-title {
        max-width: 800px;
    }
    
    .trust-badges {
        flex-direction: row;
        justify-content: center;
    }
}

/* ====== TABLETS GRANDES / LAPTOPS PEQUEÑAS (1024-1199px) ====== */
@media (min-width: 1024px) and (max-width: 1199px) {
    .metodos-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }
    
    .metodo-card {
        padding: 2rem;
    }
    
    .container {
        max-width: 1000px;
    }
    
    .page-title {
        max-width: 1000px;
    }
}

/* ====== MEJORAS DE USABILIDAD MÓVIL ====== */

/* Mejor legibilidad en pantallas pequeñas */
@media (max-width: 767px) {
    /* Textos más legibles */
    body {
        font-size: 16px; /* Evita zoom automático en iOS */
        -webkit-text-size-adjust: 100%;
    }
    
    /* Elementos táctiles más grandes */
    .btn-copy,
    .whatsapp-btn,
    .volver a {
        min-height: 44px; /* Tamaño mínimo recomendado para elementos táctiles */
        min-width: 44px;
    }
    
    /* Espaciado mejorado para táctil */
    .info-row {
        margin-bottom: 1rem;
    }
    
    /* Scroll horizontal prevención */
    * {
        max-width: 100%;
        box-sizing: border-box;
    }
    
    /* Números de cuenta que no se rompan mal */
    .numero-cuenta {
        word-break: break-all;
        hyphens: auto;
        -webkit-hyphens: auto;
        -moz-hyphens: auto;
    }
}

/* ====== NAVEGACIÓN CON TECLADO MEJORADA ====== */
@media (max-width: 767px) {
    .keyboard-navigation .btn-copy:focus,
    .keyboard-navigation .whatsapp-btn:focus,
    .keyboard-navigation .volver a:focus {
        outline: 3px solid #d4af37;
        outline-offset: 3px;
        box-shadow: 0 0 0 1px white, 0 0 0 4px #d4af37;
    }
}

/* ====== ORIENTACIÓN DE PANTALLA ====== */

/* Modo paisaje en móviles */
@media (max-width: 767px) and (orientation: landscape) {
    .header {
        padding: 0.5rem 0;
    }
    
    .page-title {
        padding: 1rem;
        margin: 1rem auto;
    }
    
    .page-title h2 {
        font-size: 1.8rem;
    }
    
    .metodo-card {
        padding: 1.5rem;
    }
    
    .contacto {
        padding: 2rem;
    }
    
    .whatsapp-btn {
        padding: 0.8rem 1.5rem;
    }
}

/* ====== OPTIMIZACIONES DE RENDIMIENTO ====== */

/* Reducir animaciones en dispositivos de bajo rendimiento */
@media (max-width: 767px) {
    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
    
    /* Optimización de scroll */
    .metodo-card,
    .banco-info,
    .contacto {
        will-change: auto;
    }
}

/* ====== MEJORAS PARA ACCESIBILIDAD ====== */

/* Contraste mejorado en pantallas pequeñas */
@media (max-width: 767px) {
    .info-label {
        color: #333;
        font-weight: 700;
    }
    
    .numero-cuenta {
        color: #000;
        background: #fff;
        border: 2px solid #ddd;
    }
    
    .btn-copy {
        font-weight: 700;
    }
}

/* ====== UTILIDADES RESPONSIVAS ====== */

/* Ocultar elementos en móviles si es necesario */
.mobile-hidden {
    @media (max-width: 767px) {
        display: none !important;
    }
}

/* Mostrar solo en móviles */
.mobile-only {
    display: none;
    @media (max-width: 767px) {
        display: block !important;
    }
}

/* Espaciado responsivo */
.responsive-spacing {
    @media (max-width: 479px) {
        margin: 1rem 0;
    }
    
    @media (min-width: 480px) and (max-width: 767px) {
        margin: 1.5rem 0;
    }
    
    @media (min-width: 768px) {
        margin: 2rem 0;
    }
}

/* ====== SOPORTE PARA NOTCH DE IPHONES ====== */
@media (max-width: 767px) {
    .header {
        padding-top: env(safe-area-inset-top, 1rem);
    }
    
    .container {
        padding-left: env(safe-area-inset-left, 10px);
        padding-right: env(safe-area-inset-right, 10px);
    }
}

/* ====== FIX PARA ZOOM EN IOS ====== */
@media (max-width: 767px) {
    /* Prevenir zoom en inputs en iOS */
    input[type="text"],
    input[type="tel"],
    input[type="email"],
    textarea {
        font-size: 16px !important;
    }
    
    /* Mejorar el viewport en dispositivos iOS */
    body {
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
    }
}

/* ====== HOVER STATES SOLO PARA DISPOSITIVOS CON HOVER ====== */
@media (hover: hover) and (pointer: fine) {
    .metodo-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }
    
    .btn-copy:hover {
        background: linear-gradient(135deg, #20c997, #17a2b8);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }
    
    .whatsapp-btn:hover {
        background: linear-gradient(135deg, #128c7e, #075e54);
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(37, 211, 102, 0.4);
    }
}

/* ====== ESTADOS DE ACTIVE PARA DISPOSITIVOS TÁCTILES ====== */
@media (hover: none) and (pointer: coarse) {
    .btn-copy:active {
        background: linear-gradient(135deg, #20c997, #17a2b8);
        transform: scale(0.95);
    }
    
    .whatsapp-btn:active {
        background: linear-gradient(135deg, #128c7e, #075e54);
        transform: scale(0.98);
    }
    
    .metodo-card:active {
        transform: scale(0.98);
    }
}}
a.logo-section,
a.logo-section:link,
a.logo-section:visited,
a.logo-section:hover,
a.logo-section:active,
a.logo-section:focus {
    text-decoration: none !important;
    color: inherit !important;
    outline: none !important;
}

a.logo-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

a.logo-section:hover {
    transform: translateY(-2px);
}

a.logo-section:hover .logo {
    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.5);
    transform: scale(1.05);
}

a.logo-section:hover .brand-info h1 {
    color: #d4af37 !important;
}

a.logo-section:hover .brand-info p {
    color: #b8941f !important;
}

/* Forzar que los elementos internos no tengan decoración */
a.logo-section * {
    text-decoration: none !important;
}

a.logo-section .brand-info h1,
a.logo-section .brand-info p {
    margin: 0;
    transition: color 0.3s ease;
    text-decoration: none !important;
}
    </style>
</head>
<body>
   <header class="header">
    <div class="header-content">
        <a href="index.php" class="logo-section">
            <img src="logo.jpg" alt="ÉXITO DORADO MX" class="logo">
            <div class="brand-info">
                <h1>ÉXITO DORADO MX</h1>
                <p>Rifas Confiables y Seguras</p>
            </div>
        </a>
        <div class="security-badge">
            <i class="fas fa-shield-alt"></i>
            Pagos Seguros
        </div>
    </div>
</header>
    
    <div class="page-title">
        <h2><i class="fas fa-credit-card"></i> Métodos de Pago</h2>
        <p>Realiza tu pago de forma segura y confiable</p>
    </div>
    
    <div class="container">
        <div class="security-alert">
            <i class="fas fa-info-circle"></i>
            <strong>Importante:</strong> Todos nuestros métodos de pago son seguros y verificados. Envía tu comprobante por WhatsApp para confirmar tu participación.
        </div>
        
        <div class="trust-badges">
            <div class="trust-badge">
                <i class="fas fa-lock"></i>
                Transacciones Seguras
            </div>
            <div class="trust-badge">
                <i class="fas fa-clock"></i>
                Confirmación en 2 Horas
            </div>
            <div class="trust-badge">
                <i class="fas fa-headset"></i>
                Soporte 24/7
            </div>
        </div>
        
        <div class="metodos-grid">
            <div class="metodo-card">
                <h3 class="metodo-title">
                    <i class="fas fa-university"></i>
                    Transferencia Bancaria
                </h3>
                
                <div class="banco-info">
                    <div class="banco-header">
                        <div class="banco-logo">NU</div>
                        <h4>Nu Mexico</h4>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-user"></i> Titular:
                        </span>
                        <span class="info-value">
                            <span class="numero-cuenta">Juan Pablo Gonzalez Garza</span>
                            <button class="btn-copy" onclick="copiarTexto('Juan Pablo Gonzalez Garza')">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-barcode"></i> CLABE:
                        </span>
                        <span class="info-value">
                            <span class="numero-cuenta">638180010117746788</span>
                            <button class="btn-copy" onclick="copiarTexto('638180010117746788')">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </span>
                    </div>
                </div>
                
                <div class="banco-info">
                    <div class="banco-header">
                        <div class="banco-logo">BC</div>
                        <h4>BanCoppel</h4>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-user"></i> Titular:
                        </span>
                        <span class="info-value">
                            <span class="numero-cuenta">Juan Pablo Gonzalez Garza</span>
                            <button class="btn-copy" onclick="copiarTexto('Juan Pablo Gonzalez Garza')">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-credit-card"></i> Tarjeta:
                        </span>
                        <span class="info-value">
                            <span class="numero-cuenta">4169 1608 2033 2691</span>
                            <button class="btn-copy" onclick="copiarTexto('4169160820332691')">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-barcode"></i> CLABE:
                        </span>
                        <span class="info-value">
                            <span class="numero-cuenta">137580104156839317</span>
                            <button class="btn-copy" onclick="copiarTexto('137580104156839317')">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="metodo-card">
                <h3 class="metodo-title">
                    <i class="fas fa-mobile-alt"></i>
                    Tarjeta y Tiendas de Conveniencia
                </h3>
                
                <div class="banco-info">
                    <div class="banco-header">
                        <div class="banco-logo"><i class="fas fa-credit-card"></i></div>
                        <h4>Pago Directo con Tarjeta</h4>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-hashtag"></i> Número:
                        </span>
                        <span class="info-value">
                            <span class="numero-cuenta">4169 1608 2033 2691</span>
                            <button class="btn-copy" onclick="copiarTexto('4169160820332691')">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-user"></i> Titular:
                        </span>
                        <span class="info-value">
                            <span class="numero-cuenta">Juan Pablo Gonzalez Garza</span>
                            <button class="btn-copy" onclick="copiarTexto('Juan Pablo Gonzalez Garza')">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </span>
                    </div>
                </div>

                <div class="banco-info">
                    <div class="banco-header">
                        <div class="banco-logo"><i class="fas fa-store"></i></div>
                        <h4>Oxxo y Tiendas de Conveniencia</h4>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-credit-card"></i> Tarjeta:
                        </span>
                        <span class="info-value">
                            <span class="numero-cuenta">4169 1608 2033 2691</span>
                            <button class="btn-copy" onclick="copiarTexto('4169160820332691')">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-shopping-cart"></i> Disponible en:
                        </span>
                        <span class="info-value">
                            <span style="color: #666; font-family: Inter;">Oxxo, 7-Eleven, Farmacias del Ahorro</span>
                            
                        </span>
                    </div>
                </div>
                
                <div class="instrucciones">
                    <h4><i class="fas fa-list-ol"></i> Instrucciones para tiendas:</h4>
                    <ol>
                        <li>Dirígete a cualquier Oxxo, 7-Eleven o Farmacia del Ahorro</li>
                        <li>Menciona que realizarás un depósito a tarjeta BanCoppel</li>
                        <li>Proporciona el número de tarjeta: <strong>4169 1608 2033 2691</strong></li>
                        <li>Especifica el monto exacto de tu compra</li>
                        <li>Conserva tu ticket de pago</li>
                        <li>Envía la foto del ticket CON TU NOMBRE por WhatsApp</li>
                    </ol>
                </div>
            </div>
        </div>
        
        <div class="instrucciones">
            <h4><i class="fas fa-check-circle"></i> Proceso de confirmación de pago:</h4>
            <ol>
                <li><strong>Realiza tu pago</strong> usando cualquiera de los métodos seguros disponibles</li>
                <li><strong>Fotografía tu comprobante</strong> asegurándote de que se vea claramente</li>
                <li><strong>Envía la imagen por WhatsApp</strong> al número oficial de contacto</li>
                <li><strong>Incluye tu información</strong>: nombre completo y números de boletos comprados</li>
                <li><strong>Recibe confirmación</strong> en un máximo de 2 horas en horario comercial</li>
            </ol>
        </div>
        
        <div class="contacto">
            <h3><i class="fas fa-whatsapp"></i> Confirma tu Pago</h3>
            <p>Envía tu comprobante de pago por WhatsApp para confirmar tu participación en la rifa</p>
            
            <a href="https://wa.me/5218180946816" class="whatsapp-btn" target="_blank">
                <i class="fab fa-whatsapp"></i>
                Enviar Comprobante por WhatsApp
            </a>
            
            <div class="telefono-info">
                <i class="fas fa-phone"></i>
                <strong>+52 1 81 8094 6816</strong>
            </div>
        </div>
        
        <div class="volver">
            <a href="index.php">
                <i class="fas fa-arrow-left"></i>
                Volver al inicio
            </a>
        </div>
    </div>
    
    <script>
        function copiarTexto(texto) {
            navigator.clipboard.writeText(texto).then(function() {
                // Crear notificación elegante
                const notification = document.createElement('div');
                notification.innerHTML = `
                    <i class="fas fa-check-circle"></i>
                    <span>Texto copiado correctamente</span>
                `;
                notification.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #28a745;
                    color: white;
                    padding: 1rem 1.5rem;
                    border-radius: 10px;
                    z-index: 10000;
                    box-shadow: 0 4px 20px rgba(40, 167, 69, 0.3);
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    font-weight: 600;
                    animation: slideInRight 0.3s ease-out;
                `;
                
                document.body.appendChild(notification);
                
                // Vibración en dispositivos móviles
                if (navigator.vibrate) {
                    navigator.vibrate(100);
                }
                
                setTimeout(() => {
                    notification.style.animation = 'slideOutRight 0.3s ease-out';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.remove();
                        }
                    }, 300);
                }, 2500);
                
            }).catch(function(err) {
                // Fallback para navegadores antiguos
                const textArea = document.createElement('textarea');
                textArea.value = texto;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    // Mostrar alerta simple si no hay soporte para clipboard
                    alert('✅ Texto copiado: ' + texto);
                } catch (err) {
                    // Si todo falla, mostrar el texto para copiado manual
                    prompt('Copia este texto manualmente:', texto);
                }
                
                document.body.removeChild(textArea);
            });
        }
        
        // Animaciones CSS adicionales
        const style = document.createElement('style');
        style.textContent = `
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
            
            /* Efecto de hover para las cards */
            .metodo-card {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            /* Efecto de focus para inputs */
            .numero-cuenta:focus {
                outline: 2px solid #d4af37;
                outline-offset: 2px;
            }
            
            /* Animación de carga para los badges */
            .trust-badge {
                animation: fadeInUp 0.6s ease-out;
            }
            
            .trust-badge:nth-child(2) {
                animation-delay: 0.1s;
            }
            
            .trust-badge:nth-child(3) {
                animation-delay: 0.2s;
            }
            
            /* Efecto de pulso para el botón de WhatsApp */
            .whatsapp-btn {
                position: relative;
                overflow: hidden;
            }
            
            .whatsapp-btn::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: translate(-50%, -50%);
                transition: width 0.6s, height 0.6s;
            }
            
            .whatsapp-btn:active::after {
                width: 300px;
                height: 300px;
            }
        `;
        document.head.appendChild(style);
        
        // Efecto de parallax suave para el fondo
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.1;
            document.body.style.backgroundPosition = `center ${rate}px`;
        });
        
        // Animación de entrada progresiva
        document.addEventListener('DOMContentLoaded', function() {
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
            document.querySelectorAll('.metodo-card, .instrucciones, .contacto').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
        
        // Mejorar accesibilidad con focus visible
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });
        
        document.addEventListener('mousedown', function() {
            document.body.classList.remove('keyboard-navigation');
        });
    </script>
    
    <style>
        /* Estilos adicionales para accesibilidad */
        .keyboard-navigation .btn-copy:focus,
        .keyboard-navigation .whatsapp-btn:focus,
        .keyboard-navigation .volver a:focus {
            outline: 3px solid #d4af37;
            outline-offset: 2px;
        }
        
        /* Mejoras para modo oscuro del sistema */
        @media (prefers-color-scheme: dark) {
            .metodo-card,
            .banco-info,
            .info-row,
            .instrucciones,
            .contacto {
                border-color: rgba(212, 175, 55, 0.3);
            }
        }
        
        /* Animación de carga inicial */
        .header {
            animation: slideDown 0.8s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-100%);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Efecto de brillo en hover para elementos importantes */
        .numero-cuenta:hover {
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
        }
        
        /* Transiciones suaves para todos los elementos interactivos */
        * {
            scroll-behavior: smooth;
        }
    </style>
</body>
</html>