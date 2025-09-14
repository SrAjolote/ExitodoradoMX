<?php
// Obtener la fecha actual para mostrar la última actualización
$fecha_actualizacion = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviso de Privacidad - Éxito Dorado MX | Protección de Datos Personales</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Aviso de Privacidad de Éxito Dorado MX. Conoce cómo protegemos y manejamos tus datos personales en nuestra plataforma de rifas.">
    <meta name="keywords" content="aviso de privacidad, protección de datos, Éxito Dorado MX, LFPDPPP, datos personales">
    <meta name="author" content="Éxito Dorado MX">
    <meta name="robots" content="index, follow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="logo.jpg">
    <link rel="apple-touch-icon" href="logo.jpg">
    
    <!-- Theme Color -->
    <meta name="theme-color" content="#d4af37">
    
    <!-- Fuentes e iconos -->
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
            line-height: 1.7;
            min-height: 100vh;
            overflow-x: hidden;
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
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }
        
        .logo-section:hover {
            transform: translateY(-2px);
            text-decoration: none;
            color: inherit;
        }
        
        .logo-img {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
            border: 2px solid #d4af37;
        }
        
        .brand-text {
            color: #2c2c2c;
            font-size: 1.3rem;
            font-weight: 800;
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
            background: rgba(212, 175, 55, 0.1);
        }
        
        .back-link:hover {
            background: #d4af37;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
        }
        
        /* Container principal */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 20px;
        }
        
        /* Header de página */
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 3rem 2rem;
            background: white;
            border-radius: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d4af37, #f4e794, #d4af37);
        }
        
        .page-title {
            font-size: 3rem;
            color: #2c2c2c;
            margin-bottom: 1rem;
            font-weight: 800;
            background: linear-gradient(135deg, #d4af37, #2c2c2c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .page-subtitle {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .update-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 20px;
            font-weight: 700;
            display: inline-block;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }
        
        /* Contenido principal */
        .privacy-content {
            background: white;
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .privacy-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #17a2b8, #20c997, #17a2b8);
        }
        
        /* Secciones */
        .section {
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .section-title {
            font-size: 1.8rem;
            color: #2c2c2c;
            margin-bottom: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem 0;
            border-left: 4px solid #d4af37;
            padding-left: 1rem;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), transparent);
        }
        
        .section-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #444;
        }
        
        .section-content p {
            margin-bottom: 1.5rem;
        }
        
        .section-content strong {
            color: #2c2c2c;
            font-weight: 700;
        }
        
        /* Listas */
        .section-content ul,
        .section-content ol {
            margin: 1.5rem 0;
            padding-left: 2rem;
        }
        
        .section-content li {
            margin-bottom: 0.8rem;
            position: relative;
        }
        
        .section-content ul li::marker {
            color: #d4af37;
        }
        
        /* Highlights especiales */
        .highlight-box {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(244, 231, 148, 0.1));
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            border-left: 4px solid #d4af37;
        }
        
        .highlight-box .highlight-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c2c2c;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .important-box {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(248, 215, 218, 0.3));
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            border-left: 4px solid #dc3545;
        }
        
        .contact-box {
            background: linear-gradient(135deg, rgba(23, 162, 184, 0.1), rgba(187, 229, 236, 0.3));
            border: 1px solid rgba(23, 162, 184, 0.3);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: center;
            border-left: 4px solid #17a2b8;
        }
        
        .contact-box .contact-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2c2c2c;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .contact-info {
            font-size: 1.1rem;
            margin: 0.8rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 600;
        }
        
        .whatsapp-link {
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: white;
            padding: 1rem 2rem;
            border-radius: 15px;
            text-decoration: none;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.3);
        }
        
        .whatsapp-link:hover {
            background: linear-gradient(135deg, #128c7e, #075e54);
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(37, 211, 102, 0.4);
            text-decoration: none;
            color: white;
        }
        
        /* Tabla de derechos */
        .rights-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .rights-table th {
            background: linear-gradient(135deg, #d4af37, #f4e794);
            color: white;
            padding: 1.5rem;
            text-align: left;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .rights-table td {
            padding: 1.5rem;
            border-bottom: 1px solid #f8f9fa;
            vertical-align: top;
        }
        
        .rights-table tr:last-child td {
            border-bottom: none;
        }
        
        .rights-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, #2c2c2c, #1a1a1a);
            color: white;
            padding: 3rem 0 2rem;
            text-align: center;
            margin-top: 4rem;
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
            font-size: 2rem;
            margin-bottom: 1rem;
            font-weight: 800;
            background: linear-gradient(135deg, #d4af37, #f4e794);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .footer-info {
            font-size: 1rem;
            margin: 0.8rem 0;
            color: #e9ecef;
            font-weight: 500;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 1rem 20px;
            }
            
            .logo-img {
                width: 40px;
                height: 40px;
            }
            
            .brand-text {
                font-size: 1.1rem;
            }
            
            .back-link {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }
            
            .container {
                padding: 1rem 15px;
            }
            
            .page-header {
                padding: 2rem 1.5rem;
                margin-bottom: 2rem;
                border-radius: 20px;
            }
            
            .page-title {
                font-size: 2.2rem;
                margin-bottom: 0.8rem;
            }
            
            .page-subtitle {
                font-size: 1rem;
                margin-bottom: 1rem;
            }
            
            .update-badge {
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
            }
            
            .privacy-content {
                padding: 2rem 1.5rem;
                border-radius: 20px;
            }
            
            .section-title {
                font-size: 1.5rem;
                margin-bottom: 1rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
                padding: 0.8rem 0;
                padding-left: 0.8rem;
            }
            
            .section-content {
                font-size: 1rem;
            }
            
            .highlight-box,
            .important-box,
            .contact-box {
                padding: 1.5rem;
                margin: 1.5rem 0;
                border-radius: 12px;
            }
            
            .contact-title {
                font-size: 1.2rem;
                flex-direction: column;
                gap: 0.3rem;
            }
            
            .contact-info {
                font-size: 1rem;
                flex-direction: column;
                gap: 0.3rem;
            }
            
            .whatsapp-link {
                padding: 0.8rem 1.5rem;
                font-size: 0.95rem;
            }
            
            .rights-table {
                font-size: 0.9rem;
                border-radius: 12px;
            }
            
            .rights-table th,
            .rights-table td {
                padding: 1rem;
            }
            
            .rights-table th {
                font-size: 1rem;
            }
            
            .footer {
                padding: 2rem 0 1.5rem;
                margin-top: 3rem;
            }
            
            .footer-title {
                font-size: 1.6rem;
            }
            
            .footer-info {
                font-size: 0.9rem;
                margin: 0.6rem 0;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 1rem 10px;
            }
            
            .page-header {
                padding: 1.5rem 1rem;
                border-radius: 15px;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
            
            .privacy-content {
                padding: 1.5rem 1rem;
                border-radius: 15px;
            }
            
            .section-title {
                font-size: 1.3rem;
                padding-left: 0.6rem;
            }
            
            .section-content {
                font-size: 0.95rem;
                line-height: 1.6;
            }
            
            .section-content ul,
            .section-content ol {
                padding-left: 1.5rem;
            }
            
            .highlight-box,
            .important-box,
            .contact-box {
                padding: 1.2rem;
                border-radius: 10px;
            }
            
            .rights-table th,
            .rights-table td {
                padding: 0.8rem;
                font-size: 0.85rem;
            }
            
            .whatsapp-link {
                padding: 0.7rem 1.2rem;
                font-size: 0.9rem;
            }
        }
        
        /* Animaciones */
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
        
        .page-header,
        .privacy-content,
        .section {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .section:nth-child(2) { animation-delay: 0.1s; }
        .section:nth-child(3) { animation-delay: 0.2s; }
        .section:nth-child(4) { animation-delay: 0.3s; }
        
        /* Scroll suave */
        html {
            scroll-behavior: smooth;
        }
        
        /* Mejoras de accesibilidad */
        .section-title:focus,
        .whatsapp-link:focus,
        .back-link:focus {
            outline: 3px solid #d4af37;
            outline-offset: 2px;
        }
        
        /* Print styles */
        @media print {
            .header,
            .footer,
            .whatsapp-link {
                display: none !important;
            }
            
            body {
                background: white;
                color: black;
            }
            
            .page-header,
            .privacy-content {
                box-shadow: none;
                border: 1px solid #ccc;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo-section">
                <img src="logo.jpg" alt="Éxito Dorado MX" class="logo-img">
                <div class="brand-text">ÉXITO DORADO MX</div>
            </a>
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Volver al Inicio
            </a>
        </nav>
    </header>

    <!-- Container principal -->
    <div class="container">
        <!-- Header de la página -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-shield-alt"></i>
                Aviso de Privacidad
            </h1>
            <p class="page-subtitle">
                En Éxito Dorado MX protegemos tus datos personales con los más altos estándares de seguridad
            </p>
            <div class="update-badge">
                <i class="fas fa-calendar-check"></i>
                Última actualización: <?php echo $fecha_actualizacion; ?>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="privacy-content">
            <!-- Sección 1: Identidad del Responsable -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-building"></i>
                    Identidad del Responsable
                </h2>
                <div class="section-content">
                    <p><strong>Éxito Dorado MX</strong> (en adelante "nosotros", "nuestro" o la "Empresa"), con domicilio en México, es el responsable del tratamiento de sus datos personales conforme a la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP).</p>
                    
                    <div class="highlight-box">
                        <div class="highlight-title">
                            <i class="fas fa-info-circle"></i>
                            Información de Contacto
                        </div>
                        <p><strong>Razón Social:</strong> Éxito Dorado MX</p>
                        <p><strong>Giro Comercial:</strong> Plataforma de rifas y sorteos en línea</p>
                        <p><strong>Sitio Web:</strong> https://exitodoradomx.com</p>
                        <p><strong>WhatsApp:</strong> +52 1 81 8094 6816</p>
                    </div>
                </div>
            </div>

            <!-- Sección 2: Datos que Recabamos -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-database"></i>
                    Datos Personales que Recabamos
                </h2>
                <div class="section-content">
                    <p>Para brindarle nuestros servicios de rifas y sorteos, recabamos los siguientes tipos de datos personales:</p>
                    
                    <h3 style="color: #d4af37; margin: 1.5rem 0 1rem 0; font-weight: 700;">Datos de Identificación:</h3>
                    <ul>
                        <li>Nombre completo</li>
                        <li>Número de teléfono</li>
                        <li>Dirección de correo electrónico (cuando aplique)</li>
                    </ul>
                    
                    <h3 style="color: #d4af37; margin: 1.5rem 0 1rem 0; font-weight: 700;">Datos de Participación:</h3>
                    <ul>
                        <li>Números de boletos adquiridos</li>
                        <li>Fecha y hora de compra</li>
                        <li>Método de pago utilizado</li>
                        <li>Historial de participaciones</li>
                    </ul>
                    
                    <h3 style="color: #d4af37; margin: 1.5rem 0 1rem 0; font-weight: 700;">Datos Técnicos:</h3>
                    <ul>
                        <li>Dirección IP</li>
                        <li>Tipo de dispositivo y navegador</li>
                        <li>Cookies y tecnologías similares</li>
                        <li>Fecha y hora de acceso a nuestros servicios</li>
                    </ul>
                </div>
            </div>

            <!-- Sección 3: Finalidades del Tratamiento -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-bullseye"></i>
                    Finalidades del Tratamiento
                </h2>
                <div class="section-content">
                    <p>Sus datos personales serán utilizados para las siguientes finalidades:</p>
                    
                    <h3 style="color: #28a745; margin: 1.5rem 0 1rem 0; font-weight: 700;">Finalidades Primarias (Necesarias):</h3>
                    <ol>
                        <li>Procesar su participación en rifas y sorteos</li>
                        <li>Generar y entregar comprobantes de participación</li>
                        <li>Contactarlo para confirmar su participación</li>
                        <li>Realizar el sorteo y determinar ganadores</li>
                        <li>Entregar premios a los ganadores</li>
                        <li>Cumplir con obligaciones legales y fiscales</li>
                        <li>Brindar atención al cliente y soporte técnico</li>
                    </ol>
                    
                    <h3 style="color: #17a2b8; margin: 1.5rem 0 1rem 0; font-weight: 700;">Finalidades Secundarias (Opcionales):</h3>
                    <ol>
                        <li>Envío de promociones y nuevas rifas disponibles</li>
                        <li>Estudios de mercado y mejora de servicios</li>
                        <li>Marketing directo y publicidad personalizada</li>
                        <li>Programas de lealtad y beneficios para clientes</li>
                    </ol>
                    
                    <div class="important-box">
                        <p><strong><i class="fas fa-exclamation-triangle"></i> Importante:</strong> Para las finalidades secundarias, podrá manifestar su negativa u oposición en cualquier momento sin que ello afecte su participación en nuestros servicios principales.</p>
                    </div>
                </div>
            </div>

            <!-- Sección 4: Derechos ARCO -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-user-shield"></i>
                    Sus Derechos como Titular
                </h2>
                <div class="section-content">
                    <p>Usted tiene derecho a conocer qué datos personales tenemos de usted, para qué los utilizamos y las condiciones del uso que les damos (<strong>Derechos ARCO</strong>):</p>
                    
                    <table class="rights-table">
                        <thead>
                            <tr>
                                <th>Derecho</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Acceso</strong></td>
                                <td>Conocer qué datos personales tenemos de usted y los detalles del tratamiento</td>
                            </tr>
                            <tr>
                                <td><strong>Rectificación</strong></td>
                                <td>Solicitar la corrección de datos inexactos o incompletos</td>
                            </tr>
                            <tr>
                                <td><strong>Cancelación</strong></td>
                                <td>Solicitar que eliminemos sus datos cuando considere que no se requieren</td>
                            </tr>
                            <tr>
                                <td><strong>Oposición</strong></td>
                                <td>Oponerse al tratamiento de sus datos para finalidades específicas</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="highlight-box">
                        <div class="highlight-title">
                            <i class="fas fa-clipboard-list"></i>
                            Cómo Ejercer sus Derechos
                        </div>
                        <p>Para ejercer cualquiera de sus derechos ARCO, deberá:</p>
                        <ol>
                            <li>Contactarnos a través de WhatsApp: <strong>+52 1 81 8094 6816</strong></li>
                            <li>Proporcionar su nombre completo y número de teléfono</li>
                            <li>Especificar claramente qué derecho desea ejercer</li>
                            <li>En caso de rectificación, indicar las modificaciones a realizar</li>
                        </ol>
                        <p><strong>Tiempo de respuesta:</strong> 20 días hábiles máximo</p>
                    </div>
                </div>
            </div>

            <!-- Sección 5: Transferencias -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-exchange-alt"></i>
                    Transferencias de Datos
                </h2>
                <div class="section-content">
                    <p>Sus datos personales podrán ser transferidos y tratados dentro y fuera del país, por personas distintas a esta empresa. En ese sentido, su información puede ser compartida con:</p>
                    
                    <ul>
                        <li><strong>Proveedores de servicios de pago:</strong> Para procesar transacciones y pagos de manera segura</li>
                        <li><strong>Prestadores de servicios tecnológicos:</strong> Para el mantenimiento y operación de nuestra plataforma</li>
                        <li><strong>Autoridades competentes:</strong> Cuando sea requerido por ley o resolución judicial</li>
                        <li><strong>Socios comerciales:</strong> Para el cumplimiento de los sorteos y entrega de premios</li>
                    </ul>
                    
                    <div class="important-box">
                        <p><strong><i class="fas fa-shield-alt"></i> Protección:</strong> Todas las transferencias se realizan bajo estrictos acuerdos de confidencialidad y niveles de protección equivalentes a los establecidos en este aviso de privacidad.</p>
                    </div>
                </div>
            </div>

            <!-- Sección 6: Medidas de Seguridad -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-lock"></i>
                    Medidas de Seguridad
                </h2>
                <div class="section-content">
                    <p>Éxito Dorado MX ha implementado medidas de seguridad administrativas, técnicas y físicas para proteger sus datos personales contra daño, pérdida, alteración, destrucción o uso no autorizado:</p>
                    
                    <h3 style="color: #d4af37; margin: 1.5rem 0 1rem 0; font-weight: 700;">Medidas Técnicas:</h3>
                    <ul>
                        <li>Encriptación de datos sensibles</li>
                        <li>Certificados SSL en todas las comunicaciones</li>
                        <li>Firewalls y sistemas de detección de intrusiones</li>
                        <li>Respaldos seguros y redundantes</li>
                        <li>Monitoreo continuo de seguridad</li>
                    </ul>
                    
                    <h3 style="color: #d4af37; margin: 1.5rem 0 1rem 0; font-weight: 700;">Medidas Administrativas:</h3>
                    <ul>
                        <li>Políticas internas de protección de datos</li>
                        <li>Capacitación regular del personal</li>
                        <li>Acuerdos de confidencialidad</li>
                        <li>Procedimientos de respuesta a incidentes</li>
                        <li>Auditorías periódicas de seguridad</li>
                    </ul>
                </div>
            </div>

            <!-- Sección 7: Uso de Cookies -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-cookie-bite"></i>
                    Uso de Cookies y Tecnologías Similares
                </h2>
                <div class="section-content">
                    <p>Nuestro sitio web utiliza cookies y tecnologías similares para mejorar su experiencia de usuario y proporcionar servicios personalizados.</p>
                    
                    <h3 style="color: #17a2b8; margin: 1.5rem 0 1rem 0; font-weight: 700;">Tipos de Cookies que Utilizamos:</h3>
                    <ul>
                        <li><strong>Cookies Técnicas:</strong> Necesarias para el funcionamiento básico del sitio</li>
                        <li><strong>Cookies de Rendimiento:</strong> Para analizar y mejorar el rendimiento del sitio</li>
                        <li><strong>Cookies de Funcionalidad:</strong> Para recordar sus preferencias y personalizar su experiencia</li>
                        <li><strong>Cookies Analíticas:</strong> Para entender cómo los usuarios interactúan con nuestro sitio</li>
                    </ul>
                    
                    <div class="highlight-box">
                        <div class="highlight-title">
                            <i class="fas fa-cog"></i>
                            Control de Cookies
                        </div>
                        <p>Puede deshabilitar las cookies en la configuración de su navegador. Sin embargo, esto puede afectar la funcionalidad de nuestros servicios.</p>
                    </div>
                </div>
            </div>

            <!-- Sección 8: Conservación de Datos -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-clock"></i>
                    Tiempo de Conservación
                </h2>
                <div class="section-content">
                    <p>Sus datos personales serán conservados durante el tiempo necesario para cumplir con las finalidades para las que fueron recabados y durante los plazos legalmente exigidos:</p>
                    
                    <ul>
                        <li><strong>Datos de participación:</strong> 5 años posteriores a la última participación</li>
                        <li><strong>Datos de ganadores:</strong> 10 años para cumplir con obligaciones fiscales</li>
                        <li><strong>Datos de comunicación:</strong> 2 años desde el último contacto</li>
                        <li><strong>Datos técnicos:</strong> 12 meses para análisis y mejoras</li>
                    </ul>
                    
                    <p>Transcurridos los plazos mencionados, procederemos a la eliminación segura de sus datos personales, salvo que exista impedimento legal o judicial para hacerlo.</p>
                </div>
            </div>

            <!-- Sección 9: Menores de Edad -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-child"></i>
                    Protección de Menores de Edad
                </h2>
                <div class="section-content">
                    <div class="important-box">
                        <p><strong><i class="fas fa-exclamation-circle"></i> Restricción de Edad:</strong> Nuestros servicios están dirigidos exclusivamente a personas mayores de 18 años. No recopilamos intencionalmente datos personales de menores de edad.</p>
                    </div>
                    
                    <p>Si detectamos que hemos recabado datos de un menor de edad sin el consentimiento apropiado, procederemos inmediatamente a:</p>
                    <ul>
                        <li>Eliminar toda la información del menor</li>
                        <li>Cancelar cualquier participación activa</li>
                        <li>Contactar a los padres o tutores legales</li>
                        <li>Implementar medidas adicionales de verificación</li>
                    </ul>
                </div>
            </div>

            <!-- Sección 10: Modificaciones -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-edit"></i>
                    Modificaciones al Aviso de Privacidad
                </h2>
                <div class="section-content">
                    <p>Este aviso de privacidad puede sufrir modificaciones, cambios o actualizaciones derivadas de nuevos requerimientos legales, necesidades operativas o mejoras en nuestros servicios.</p>
                    
                    <div class="highlight-box">
                        <div class="highlight-title">
                            <i class="fas fa-bell"></i>
                            Notificación de Cambios
                        </div>
                        <p>Le notificaremos sobre cualquier modificación mediante:</p>
                        <ul>
                            <li>Publicación en nuestro sitio web: <strong>https://exitodoradomx.com</strong></li>
                            <li>Mensaje directo por WhatsApp cuando sea aplicable</li>
                            <li>Aviso prominente en nuestra plataforma</li>
                        </ul>
                        <p><strong>Fecha de última actualización:</strong> <?php echo $fecha_actualizacion; ?></p>
                    </div>
                </div>
            </div>

            <!-- Sección 11: Negativa al Tratamiento -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-times-circle"></i>
                    Negativa al Tratamiento de Datos
                </h2>
                <div class="section-content">
                    <p>Usted puede manifestar su negativa para el tratamiento de sus datos personales para las finalidades secundarias. Para ello, deberá comunicarse con nosotros especificando claramente a qué finalidades se opone.</p>
                    
                    <div class="important-box">
                        <p><strong><i class="fas fa-info-circle"></i> Importante:</strong> La negativa para el tratamiento de datos para finalidades primarias puede tener como consecuencia que no podamos proporcionarle algunos servicios que solicita, ya que son necesarios para el cumplimiento de nuestras obligaciones contractuales.</p>
                    </div>
                </div>
            </div>

            <!-- Sección 12: Contacto -->
            <div class="section">
                <div class="contact-box">
                    <div class="contact-title">
                        <i class="fas fa-headset"></i>
                        Contacto para Protección de Datos
                    </div>
                    <p>Si tiene alguna pregunta sobre este Aviso de Privacidad o desea ejercer sus derechos, contáctenos:</p>
                    
                    <div class="contact-info">
                        <i class="fas fa-globe"></i>
                        Sitio Web: https://exitodoradomx.com
                    </div>
                    
                    <div class="contact-info">
                        <i class="fab fa-whatsapp"></i>
                        WhatsApp: +52 1 81 8094 6816
                    </div>
                    
                    <div class="contact-info">
                        <i class="fas fa-clock"></i>
                        Horario de Atención: 24/7
                    </div>
                    
                    <a href="https://wa.me/5218180946816?text=Hola%2C%20tengo%20una%20consulta%20sobre%20el%20Aviso%20de%20Privacidad" target="_blank" class="whatsapp-link">
                        <i class="fab fa-whatsapp"></i>
                        Contactar por WhatsApp
                    </a>
                </div>
            </div>

            <!-- Sección 13: Marco Legal -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-gavel"></i>
                    Marco Legal Aplicable
                </h2>
                <div class="section-content">
                    <p>Este Aviso de Privacidad se rige conforme a la legislación mexicana vigente, particularmente:</p>
                    
                    <ul>
                        <li><strong>Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP)</strong></li>
                        <li><strong>Reglamento de la Ley Federal de Protección de Datos Personales en Posesión de los Particulares</strong></li>
                        <li><strong>Lineamientos del Instituto Nacional de Transparencia, Acceso a la Información y Protección de Datos Personales (INAI)</strong></li>
                        <li><strong>Código de Comercio</strong></li>
                        <li><strong>Ley Federal del Consumidor</strong></li>
                    </ul>
                    
                    <div class="highlight-box">
                        <div class="highlight-title">
                            <i class="fas fa-balance-scale"></i>
                            Jurisdicción
                        </div>
                        <p>Para la interpretación y cumplimiento del presente Aviso de Privacidad, las partes se someten a la jurisdicción de los tribunales competentes de México, renunciando expresamente a cualquier otro fuero que pudiera corresponderles por razón de sus domicilios presentes o futuros.</p>
                    </div>
                </div>
            </div>

            <!-- Sección 14: Compromiso -->
            <div class="section">
                <div class="highlight-box">
                    <div class="highlight-title">
                        <i class="fas fa-handshake"></i>
                        Nuestro Compromiso con su Privacidad
                    </div>
                    <p>En <strong>Éxito Dorado MX</strong> nos comprometemos a:</p>
                    <ul>
                        <li>Proteger sus datos personales con los más altos estándares de seguridad</li>
                        <li>Ser transparentes sobre el uso que damos a su información</li>
                        <li>Respetar sus derechos como titular de datos personales</li>
                        <li>Cumplir con todas las disposiciones legales aplicables</li>
                        <li>Mejorar continuamente nuestras prácticas de protección de datos</li>
                        <li>Brindar atención oportuna a sus solicitudes y consultas</li>
                    </ul>
                    <p style="text-align: center; margin-top: 2rem; font-weight: 700; color: #d4af37; font-size: 1.2rem;">
                        <i class="fas fa-shield-alt"></i>
                        Su confianza es nuestro mayor compromiso
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <h3 class="footer-title">ÉXITO DORADO MX</h3>
            <p class="footer-info">
                <i class="fas fa-shield-alt"></i>
                Protegemos tu privacidad con los más altos estándares de seguridad
            </p>
            <p class="footer-info">
                <i class="fab fa-whatsapp"></i>
                WhatsApp: +52 1 81 8094 6816
            </p>
            <p class="footer-info">
                <i class="fas fa-globe"></i>
                https://exitodoradomx.com
            </p>
            <p class="footer-info">
                <i class="fas fa-calendar-alt"></i>
                Última actualización: <?php echo $fecha_actualizacion; ?>
            </p>
            
            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.2);">
                <p class="footer-info">
                    <i class="fas fa-copyright"></i>
                    2025 Éxito Dorado MX - Todos los derechos reservados
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll para enlaces internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const headerHeight = document.querySelector('.header').offsetHeight;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerHeight - 20;
                    
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Animación de entrada para secciones
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

        // Aplicar animación a las secciones
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.section').forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(el);
            });

            // Efecto de escritura para el título principal
            const title = document.querySelector('.page-title');
            if (title && window.innerWidth > 768) {
                title.style.overflow = 'hidden';
                title.style.borderRight = '3px solid #d4af37';
                title.style.whiteSpace = 'nowrap';
                title.style.animation = 'typing 3s steps(30, end), blink-caret 0.75s step-end infinite';
                
                // Remover el efecto después de la animación
                setTimeout(() => {
                    title.style.borderRight = 'none';
                    title.style.whiteSpace = 'normal';
                    title.style.overflow = 'visible';
                }, 4000);
            }
        });

        // Efecto parallax sutil
        window.addEventListener('scroll', function() {
            if (window.innerWidth > 768) {
                const scrolled = window.pageYOffset;
                const pageHeader = document.querySelector('.page-header');
                if (pageHeader) {
                    const rate = scrolled * -0.2;
                    pageHeader.style.transform = `translateY(${rate}px)`;
                }
            }
        });

        // Contador de caracteres para dispositivos móviles (mejora UX)
        function addReadingTime() {
            const content = document.querySelector('.privacy-content');
            const text = content.textContent || content.innerText || '';
            const wordCount = text.trim().split(/\s+/).length;
            const readingTime = Math.ceil(wordCount / 200); // 200 palabras por minuto promedio
            
            const badge = document.querySelector('.update-badge');
            badge.innerHTML += ` | <i class="fas fa-clock"></i> ${readingTime} min de lectura`;
        }

        // Agregar tiempo de lectura
        document.addEventListener('DOMContentLoaded', addReadingTime);

        // Agregar CSS para las animaciones de typing
        const style = document.createElement('style');
        style.textContent = `
            @keyframes typing {
                from { width: 0 }
                to { width: 100% }
            }
            
            @keyframes blink-caret {
                from, to { border-color: transparent }
                50% { border-color: #d4af37 }
            }
            
            /* Efecto de hover mejorado para enlaces */
            .whatsapp-link {
                position: relative;
                overflow: hidden;
            }
            
            .whatsapp-link::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
                transition: left 0.6s;
            }
            
            .whatsapp-link:hover::before {
                left: 100%;
            }
            
            /* Efecto de hover para las cajas destacadas */
            .highlight-box:hover,
            .important-box:hover,
            .contact-box:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
                transition: all 0.3s ease;
            }
            
            /* Mejora de accesibilidad */
            .section-title:focus {
                outline: 3px solid #d4af37;
                outline-offset: 2px;
                border-radius: 4px;
            }
            
            /* Loading placeholder para imágenes */
            .logo-img {
                background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                background-size: 200% 100%;
            }
            
            .logo-img:not([src]) {
                animation: loading 1.5s infinite;
            }
            
            @keyframes loading {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }
        `;
        document.head.appendChild(style);

        // Función para imprimir la página
        function printPage() {
            window.print();
        }

        // Agregar botón de imprimir (opcional)
        function addPrintButton() {
            const printBtn = document.createElement('button');
            printBtn.innerHTML = '<i class="fas fa-print"></i> Imprimir';
            printBtn.className = 'btn-print';
            printBtn.onclick = printPage;
            printBtn.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #d4af37;
                color: white;
                border: none;
                padding: 1rem;
                border-radius: 50px;
                cursor: pointer;
                box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
                font-weight: 600;
                z-index: 1000;
                transition: all 0.3s ease;
            `;
            
            printBtn.addEventListener('mouseenter', function() {
                this.style.background = '#b8941f';
                this.style.transform = 'translateY(-2px)';
            });
            
            printBtn.addEventListener('mouseleave', function() {
                this.style.background = '#d4af37';
                this.style.transform = 'translateY(0)';
            });
            
            // Solo mostrar en desktop
            if (window.innerWidth > 768) {
                document.body.appendChild(printBtn);
            }
        }

        // Agregar botón de imprimir después de cargar
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(addPrintButton, 2000);
        });
    </script>
</body>
</html>