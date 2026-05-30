<?php
session_start();

// Control de seguridad: Si no hay sesión, pa' fuera
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/conexion.php';

$mensaje = "";

// Si el usuario envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $documento = trim($_POST['documento']);
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $direccion = trim($_POST['direccion']);

    try {
        $sql = "INSERT INTO dueño (documento_identidad, nombre, telefono, email, direccion) 
                VALUES (:documento, :nombre, :telefono, :email, :direccion)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':documento' => $documento,
            ':nombre' => $nombre,
            ':telefono' => $telefono,
            ':email' => $email,
            ':direccion' => $direccion
        ]);

        $mensaje = "<div class='alert success'>¡Dueño registrado correctamente en el sistema!</div>";
    } catch(PDOException $e) {
        // El código 23000 en SQL significa que se violó una regla de valor único (UNIQUE)
        if ($e->getCode() == 23000) {
            $mensaje = "<div class='alert error'>Error: Ya existe un dueño registrado con ese documento de identidad.</div>";
        } else {
            $mensaje = "<div class='alert error'>Error al registrar: " . $e->getMessage() . "</div>";
        }
    }
}

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario = $_SESSION['rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dueños y Mascotas - Huellitas</title>
    <style>
        /* Reutilizamos los estilos de tu Dashboard */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f4f7f6;
            display: flex;
        }
        .sidebar {
            width: 260px;
            height: 100vh;
            background-color: #006805;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar-header {
            padding: 25px;
            text-align: center;
            background-color: #074e0b;
            border-bottom: 1px solid #074e0b;
        }
        .sidebar-header h3 { margin: 0; font-size: 20px; font-weight: 600; color: #e4ffea; }
        .user-info {
            padding: 15px 25px; background-color: #0a5f0e; font-size: 14px; border-bottom: 1px solid #0a5f0e;
        }
        .user-info span { display: block; color: #ffffff; }
        .user-info .rol { color: #eeff00; font-weight: bold; font-size: 12px; text-transform: uppercase; margin-top: 3px; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex-grow: 1; overflow-y: auto; }
        .sidebar-menu li a {
            display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; font-size: 15px; transition: all 0.3s; border-left: 4px solid transparent;
        }
        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            background-color: #3d8d58; border-left-color: #227446; padding-left: 30px;
        }
        .btn-logout { background-color: #075521; text-align: center; font-weight: bold; }
        .btn-logout:hover { background-color: #e74c3c !important; border-left-color: #e74c3c !important; }

        /* Estilos específicos para el contenido y formulario */
        .main-content {
            margin-left: 260px; padding: 40px; width: calc(100% - 260px); box-sizing: border-box;
        }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { margin: 0; color: #2c3e50; }
        
        .form-container {
            background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); max-width: 600px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 600; font-size: 14px; }
        .form-group input {
            width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 6px; box-sizing: border-box; outline: none; transition: 0.3s;
        }
        .form-group input:focus { border-color: #22773c; }
        .btn-submit {
            background-color: #22773c; color: white; padding: 12px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; width: 100%;
        }
        .btn-submit:hover { background-color: #024e22; }
        
        /* Alertas */
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 6px; font-weight: bold; }
        .success { background-color: #e4ffea; color: #074e0b; border: 1px solid #3d8d58; }
        .error { background-color: #fadbd8; color: #c0392b; border: 1px solid #e74c3c; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Huellitas</h3>
        </div>
        <div class="user-info">
            <span>Bienvenido, <b><?= htmlspecialchars($nombre_usuario) ?></b></span>
            <div class="rol"><?= htmlspecialchars($rol_usuario) ?></div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">Inicio</a></li>
            <li><a href="dueños.php" class="active">Dueños y Mascotas</a></li> <li><a href="citas.php">Agenda / Citas</a></li>
            <li><a href="consultas.php">Consultas Médicas</a></li>
            <li><a href="vacunas.php">Carnet de Vacunación</a></li>
            <li><a href="tratamientos.php">Tratamientos</a></li>
            <li><a href="medicamentos.php">Catálogo Medicamentos</a></li>
            <li><a href="reportes.php">Reportes y Estadísticas</a></li>
            <li><a href="logout.php" class="btn-logout">Cerrar Sesión</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h2>Registrar Nuevo Dueño</h2>
        </div>

        <?= $mensaje ?>
      <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="documento">Documento de Identidad:</label>
                    <input type="text" id="documento" name="documento" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre Completo:</label>
                    <input type="text" id="nombre" name="nombre" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico (Opcional):</label>
                    <input type="email" id="email" name="email" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="direccion">Dirección (Opcional):</label>
                    <input type="text" id="direccion" name="direccion" autocomplete="off">
                </div>
                
             <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-submit" style="flex: 1;">Guardar Dueño</button>
                    
                    <a href="dueños.php" style="flex: 1; text-align: center; background-color: #34495e; color: white; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; transition: 0.3s;">Ver Lista de Clientes</a>
                    
                    <a href="nueva_mascota.php" style="flex: 1; text-align: center; background-color: #f39c12; color: white; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; transition: 0.3s;">+ Añadir Mascota</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
