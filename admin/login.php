<?php
require_once '../config.php';

$error = '';

if ($_POST && isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: index.php');
        exit();
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo - Éxito Dorado MX</title>
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #fdf6e3 0%, #f5f1e3 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

/* ====== BACKGROUND PATTERN ====== */
body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(212,175,55,0.1)"/><circle cx="80" cy="80" r="2" fill="rgba(212,175,55,0.1)"/><circle cx="40" cy="70" r="1.5" fill="rgba(212,175,55,0.1)"/><circle cx="70" cy="30" r="1.5" fill="rgba(212,175,55,0.1)"/></svg>');
    animation: floatPattern 20s linear infinite;
    z-index: 0;
}

@keyframes floatPattern {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100px); }
}

/* ====== LOGIN CONTAINER ====== */
.login-container {
    background: white;
    border-radius: 24px;
    padding: 3rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.12);
    border: 1px solid rgba(212, 175, 55, 0.2);
    width: 100%;
    max-width: 420px;
    position: relative;
    z-index: 1;
    overflow: hidden;
    animation: fadeInUp 0.8s ease-out;
}

.login-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #d4af37, #1b5e20, #d4af37);
}

.login-container::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, transparent 40%, rgba(212, 175, 55, 0.05) 50%, transparent 60%);
    transform: rotate(45deg);
    animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { opacity: 0; }
    50% { opacity: 1; }
}

/* ====== LOGO ====== */
.logo {
    text-align: center;
    margin-bottom: 2.5rem;
    animation: slideInDown 0.8s ease-out 0.2s both;
}

.logo h1 {
    color: #d4af37;
    font-size: clamp(1.8rem, 4vw, 2.2rem);
    font-weight: 800;
    margin-bottom: 0.5rem;
    text-shadow: 0 4px 8px rgba(212, 175, 55, 0.3);
    background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    position: relative;
}

.logo h1::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #d4af37, #1b5e20);
    border-radius: 2px;
}

.logo p {
    color: #666;
    font-size: 1rem;
    font-weight: 500;
    opacity: 0.8;
}

/* ====== FORM STYLING ====== */
.form-group {
    margin-bottom: 2rem;
    position: relative;
    animation: slideInLeft 0.6s ease-out;
}

.form-group:nth-child(1) { animation-delay: 0.4s; }
.form-group:nth-child(2) { animation-delay: 0.5s; }

.form-group label {
    display: block;
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

.form-group input {
    width: 100%;
    padding: 1.2rem 1.5rem;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    font-family: inherit;
    background: white;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    position: relative;
}

.form-group input:focus {
    outline: none;
    border-color: #d4af37;
    box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.15);
    transform: translateY(-2px);
}

.form-group input:hover {
    border-color: #d4af37;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.1);
}

/* ====== BUTTON ====== */
.btn-login {
    width: 100%;
    padding: 1.2rem 2rem;
    background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 6px 20px rgba(27, 94, 32, 0.3);
    position: relative;
    overflow: hidden;
    margin-top: 1rem;
    animation: slideInUp 0.6s ease-out 0.6s both;
}

.btn-login::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s;
}

.btn-login:hover::before {
    left: 100%;
}

.btn-login:hover {
    background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%);
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(27, 94, 32, 0.4);
}

.btn-login:active {
    transform: translateY(-1px);
}

/* ====== ERROR MESSAGE ====== */
.error {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
    padding: 1.2rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    border: 1px solid #dc3545;
    text-align: center;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
    position: relative;
    animation: shake 0.5s ease-in-out;
}

.error::before {
    content: '⚠️';
    margin-right: 0.5rem;
    font-size: 1.1rem;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* ====== VOLVER LINK ====== */
.volver {
    text-align: center;
    margin-top: 2.5rem;
    animation: fadeIn 0.6s ease-out 0.8s both;
}

.volver a {
    color: #1b5e20;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s ease;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    display: inline-block;
    position: relative;
}

.volver a::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #d4af37, #1b5e20);
    transition: width 0.3s ease;
}

.volver a:hover::before {
    width: 100%;
}

.volver a:hover {
    color: #d4af37;
    transform: translateY(-2px);
}

/* ====== ANIMACIONES ====== */
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

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* ====== RESPONSIVE DESIGN ====== */
@media (max-width: 768px) {
    .login-container {
        padding: 2rem;
        margin: 1rem;
        max-width: 380px;
    }
    
    .logo h1 {
        font-size: 1.8rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group input {
        padding: 1rem;
        font-size: 0.95rem;
    }
    
    .btn-login {
        padding: 1rem;
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    body {
        padding: 0.5rem;
    }
    
    .login-container {
        padding: 1.5rem;
        margin: 0.5rem;
        max-width: 100%;
        border-radius: 16px;
    }
    
    .logo {
        margin-bottom: 2rem;
    }
    
    .logo h1 {
        font-size: 1.6rem;
    }
    
    .form-group label {
        font-size: 0.9rem;
        margin-bottom: 0.6rem;
    }
    
    .form-group input {
        padding: 0.9rem;
        font-size: 0.9rem;
    }
    
    .btn-login {
        padding: 0.9rem;
        font-size: 0.95rem;
    }
    
    .volver {
        margin-top: 2rem;
    }
    
    .volver a {
        font-size: 0.9rem;
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
.btn-login:focus,
input:focus {
    outline: 3px solid rgba(212, 175, 55, 0.5);
    outline-offset: 2px;
}

/* ====== EFECTOS ADICIONALES ====== */
.login-container:hover {
    box-shadow: 0 25px 70px rgba(0,0,0,0.15);
}



/* ====== LOADING STATE ====== */
.btn-login.loading {
    position: relative;
    color: transparent;
}

.btn-login.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid transparent;
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🎰 Éxito Dorado MX</h1>
            <p>Panel Administrativo</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login">🔐 Iniciar Sesión</button>
        </form>
        
        <div class="volver">
            <a href="../index.php">← Volver al sitio principal</a>
        </div>
    </div>
</body>
</html>