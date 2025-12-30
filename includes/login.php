<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Fondo con imagen */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('../assets/img/fondos/fondo.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -1;
        }

        /* Overlay oscuro sutil para mejor contraste */
        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: -1;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            animation: slideIn 0.6s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            color: white;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.5);
        }

        .error-message {
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: white;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
        }

        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            backdrop-filter: blur(5px);
        }

        .form-group input:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.1);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        
.submit-btn {
    width: 100%;
    padding: 16px;
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    margin-top: 10px;
}

.submit-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    box-shadow: 0 8px 25px rgba(255, 255, 255, 0.1);
}

.submit-btn:active {
    transform: translateY(0);
    background: rgba(255, 255, 255, 0.1);
}

        .footer-text {
            text-align: center;
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        }

        .footer-text a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        /* Iconos decorativos con símbolos Unicode */
        .icon-email::before {
            content: '✉';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
            font-size: 18px;
        }

        .icon-lock::before {
            content: '🔒';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            filter: grayscale(0.3) brightness(1.2);
        }

        /* Animación de carga para el botón */
        .submit-btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        
     .submit-btn.loading::after {
    content: '';
    display: inline-block;
    width: 14px;
    height: 14px;
    margin-left: 10px;
    border: 2px solid white;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 40px 30px;
                margin: 20px;
            }

            .login-header h2 {
                font-size: 26px;
            }
        }

        /* Efecto de partículas decorativas */
        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            pointer-events: none;
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; animation-duration: 3s; }
        .particle:nth-child(2) { left: 30%; animation-delay: 1s; animation-duration: 4s; }
        .particle:nth-child(3) { left: 50%; animation-delay: 2s; animation-duration: 3.5s; }
        .particle:nth-child(4) { left: 70%; animation-delay: 0.5s; animation-duration: 4.5s; }
        .particle:nth-child(5) { left: 90%; animation-delay: 1.5s; animation-duration: 3.8s; }
    </style>
</head>
<body>
    <!-- Partículas decorativas -->
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>

    <div class="login-container">
        <div class="login-header">
            <h2>Bienvenido</h2>
            <p>Ingresa tus credenciales para continuar</p>
        </div>

        <?php

        /*
        session_start();


        */


        if (!empty($_SESSION['error'])) {
            echo '<div class="error-message">';
            echo '⚠️ ' . htmlspecialchars($_SESSION['error']);
            echo '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="usuarios_login.php" method="POST" id="loginForm">
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <div class="input-wrapper icon-email">
                    <input 
                        type="email" 
                        id="correo" 
                        name="correo" 
                        placeholder="tu@correo.com"
                        required
                        autocomplete="email"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <div class="input-wrapper icon-lock">
                    <input 
                        type="password" 
                        id="contrasena" 
                        name="contrasena" 
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                </div>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                Iniciar Sesión
            </button>
        </form>

      <div class="footer-text">
    ¿Has olvidado tu contraseña? <a href="#">Comuníquese con el administrador del sistema</a>
</div>



    </div>

    <script>
        // Animación del botón al enviar
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.textContent = 'Ingresando';
        });

        // Efecto de enfoque suave
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
                this.parentElement.style.transition = 'transform 0.2s ease';
            });
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>