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
    <title>Login - Clínica Veterinaria</title>
    <style>
        /* Un diseño minimalista, limpio y estético */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #88ad84;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 350px;
            text-align: center;
        }
        .login-container h2 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 600;
        }
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #7f8c8d;
            font-size: 14px;
        }
        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #bdc3c7;
            border-radius: 6px;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.3s;
        }
        .input-group input:focus {
            border-color: #3498db;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: bold;
        }
        .btn-login:hover {
            background-color: #2980b9;
        }
        .error-msg {
            color: #e74c3c;
            font-size: 14px;
            margin-bottom: 15px;
            background-color: #fadbd8;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Clínica Veterinaria</h2>
        
        <?php if(!empty($error)): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required autocomplete="off">
            </div>
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Ingresar</button>
        </form>
    </div>

</body>
</html>