<?php
// Iniciamos el motor de sesiones de PHP (siempre debe ir en la primera línea)
session_start();

// Si el usuario ya había iniciado sesión antes, lo enviamos directo al panel principal
if (isset($_SESSION['usuario'])) {
    header("Location:dashboard.php");
    exit();
}
require_once 'config/conexion.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        // Buscamos al usuario en la base de datos
        $sql = "SELECT id_usuario, username, password_hash, rol FROM usuario WHERE username = :username AND estado = 'Activo'";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':username' => $username]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificamos si existe y si la contraseña coincide con el hash encriptado
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            // ¡Credenciales correctas! Guardamos los datos en la sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['usuario'] = $usuario['username'];
            $_SESSION['rol'] = $usuario['rol'];
            
            // Lo redirigimos a la página principal del sistema
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    } catch(PDOException $e) {
        $error = "Error de conexión: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Clínica Veterinaria Huellitas</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&family=Lato:wght@400;700&display=swap');

        body {
            margin: 0;
            padding: 0;
            font-family: 'Lato', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
            background-color: #064a5f; /* Color de respaldo */
        }

        /* ── FONDO ANIMADO CROSSFADE ── */
        .bg-layer {
            position: absolute;
            top: -5%; left: -5%;
            width: 110%; height: 110%;
            background-size: cover;
            background-position: center;
            animation: moverFondo 30s linear infinite alternate;
            z-index: -2;
            opacity: 0;
            transition: opacity 2.5s ease-in-out;
        }
        .bg-layer.active {
            opacity: 1;
        }

        /* Capa superpuesta azul oscura para que el texto blanco resalte */
        .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(3, 28, 38, 0.85) 0%, rgba(12, 131, 167, 0.65) 100%);
            z-index: -1;
        }

        @keyframes moverFondo {
            0%   { transform: scale(1) translate(0, 0); }
            100% { transform: scale(1.05) translate(-1.5%, -1.5%); }
        }

        /* ── CAJA TRANSPARENTE AZUL CLARITO (Glassmorphism) ── */
        .login-container {
            /* Azul cielo/clarito con mucha transparencia */
            background: rgba(135, 206, 235, 0.12);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 45px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
            width: 100%;
            max-width: 360px;
            text-align: center;
            /* Borde blanco semi-transparente para simular el borde del cristal */
            border: 1px solid rgba(255, 255, 255, 0.25);
            z-index: 1;
        }

        /* Logo con imagen */
        .login-icon {
            width: 85px;
            height: 85px;
            margin: 0 auto 10px auto;
            /* Sombra ligera para que resalte */
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }
        
        .login-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .login-container h2 {
            font-family: 'Nunito', sans-serif;
            color: #ffffff;
            margin: 0 0 30px 0;
            font-weight: 800;
            font-size: 28px;
            letter-spacing: 1px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .input-group {
            margin-bottom: 22px;
            text-align: left;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #e0f2f7; /* Blanco azulado */
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Inputs transparentes */
        .input-group input {
            width: 100%;
            padding: 14px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 10px;
            box-sizing: border-box;
            outline: none;
            transition: all 0.3s;
            font-size: 15px;
            font-family: 'Lato', sans-serif;
            background: rgba(255, 255, 255, 0.1); /* Relleno casi transparente */
            color: #ffffff; /* Texto blanco al escribir */
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .input-group input:focus {
            border-color: #ffffff;
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
        }

        /* Botón inverso (Blanco con letras azules) */
        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: #ffffff;
            color: #0c83a7;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-login:hover {
            background-color: #e0f2f7;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        .error-msg {
            color: #ffffff;
            font-size: 13px;
            margin-bottom: 20px;
            background-color: rgba(231, 76, 60, 0.8);
            padding: 12px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.3);
            font-weight: bold;
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body>

    <div class="bg-layer active" id="bg-layer-1"></div>
    <div class="bg-layer" id="bg-layer-2"></div>
    <div class="overlay"></div>

    <div class="login-container">
        <div class="login-icon">
            <img src="imagenes/paw_logo.png" alt="Huellitas Logo">
        </div>
        <h2>Huellitas</h2>
        
        <?php if(!empty($error)): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required autocomplete="off" placeholder="Ingresa tu usuario">
            </div>
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-login">Ingresar al Sistema</button>
        </form>
    </div>

    <script>
        // Array de imágenes de mascotas/veterinaria para el fondo
        // Nota: Asegúrate de descargar y guardar las imágenes de los loros y perritos en tu carpeta "imagenes"
        const imagenesFondo = [
            "imagenes/v.jpg",
            "imagenes/mascotas/perritos.jpg",
            "imagenes/mascotas/loros1.jpg",
            "imagenes/mascotas/loros2.jpg"
        ];
        
        let indiceActual = 0;
        let layerActiva = 1;
        const layer1 = document.getElementById('bg-layer-1');
        const layer2 = document.getElementById('bg-layer-2');

        // Precargar imágenes en segundo plano para evitar parpadeos blancos
        imagenesFondo.forEach(src => {
            const img = new Image();
            img.src = src;
        });

        // Inicializar la primera capa
        layer1.style.backgroundImage = `url('${imagenesFondo[0]}')`;

        // Cambiar la imagen automáticamente cada 4 segundos (crossfade)
        setInterval(() => {
            indiceActual = (indiceActual + 1) % imagenesFondo.length;
            const nuevaImagen = `url('${imagenesFondo[indiceActual]}')`;
            
            if (layerActiva === 1) {
                layer2.style.backgroundImage = nuevaImagen;
                layer2.classList.add('active');
                layer1.classList.remove('active');
                layerActiva = 2;
            } else {
                layer1.style.backgroundImage = nuevaImagen;
                layer1.classList.add('active');
                layer2.classList.remove('active');
                layerActiva = 1;
            }
        }, 4000);
    </script>
</body>
</html>