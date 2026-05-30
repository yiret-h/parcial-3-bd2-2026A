<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/conexion.php';

$mensaje = "";

// 1. Traemos la lista de dueños para el menú desplegable
try {
    $sql_dueños = "SELECT id_dueño, nombre, documento_identidad FROM dueño ORDER BY nombre ASC";
    $stmt_dueños = $conexion->query($sql_dueños);
    $lista_dueños = $stmt_dueños->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensaje = "<div class='alerta-error'>Error al cargar dueños: " . $e->getMessage() . "</div>";
}

// 2. Traemos la lista de razas junto con su especie para el menú desplegable
try {
    // Usamos JOIN para unir la tabla raza con la tabla especie
    $sql_razas = "SELECT r.id_raza, r.nombre AS nombre_raza, e.nombre AS nombre_especie 
                  FROM raza r 
                  INNER JOIN especie e ON r.id_especie = e.id_especie 
                  ORDER BY e.nombre ASC, r.nombre ASC";
    $stmt_razas = $conexion->query($sql_razas);
    $lista_razas = $stmt_razas->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensaje = "<div class='alerta-error'>Error al cargar razas: " . $e->getMessage() . "</div>";
}

// 3. Si el usuario envía el formulario para guardar la mascota
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_dueño = $_POST['id_dueño'];
    $nombre_mascota = trim($_POST['nombre']);
    $id_raza = $_POST['id_raza'];
    $sexo = $_POST['sexo'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];

    try {
      
        $sql_insert = "INSERT INTO mascota (id_dueño, nombre, id_raza, sexo, fecha_nacimiento) 
                       VALUES (:id_dueno, :nombre, :id_raza, :sexo, :fecha_nacimiento)";
        
        $stmt = $conexion->prepare($sql_insert);
        $stmt->execute([
            ':id_dueno' => $id_dueño, 
            ':nombre' => $nombre_mascota,
            ':id_raza' => $id_raza,
            ':sexo' => $sexo,
            ':fecha_nacimiento' => empty($fecha_nacimiento) ? null : $fecha_nacimiento
        ]);

        $mensaje = "<div class='alert success'>¡Mascota registrada correctamente!</div>";
    } catch(PDOException $e) {
        $mensaje = "<div class='alert error'>Error al registrar: " . $e->getMessage() . "</div>";
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
    <title>Añadir Mascota - Huellitas</title>
    <style>
        /* Reutilizamos los mismos estilos verdes */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background-color: #f4f7f6; display: flex; }
        .sidebar { width: 260px; height: 100vh; background-color: #006805; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar-header { padding: 25px; text-align: center; background-color: #074e0b; border-bottom: 1px solid #074e0b; }
        .sidebar-header h3 { margin: 0; font-size: 20px; font-weight: 600; color: #e4ffea; }
        .user-info { padding: 15px 25px; background-color: #0a5f0e; font-size: 14px; border-bottom: 1px solid #0a5f0e; }
        .user-info span { display: block; color: #ffffff; }
        .user-info .rol { color: #eeff00; font-weight: bold; font-size: 12px; text-transform: uppercase; margin-top: 3px; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex-grow: 1; overflow-y: auto; }
        .sidebar-menu li a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; font-size: 15px; transition: all 0.3s; border-left: 4px solid transparent; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: #3d8d58; border-left-color: #227446; padding-left: 30px; }
        .btn-logout { background-color: #075521; text-align: center; font-weight: bold; }
        
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); box-sizing: border-box; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { margin: 0; color: #2c3e50; }
        
        .form-container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 600; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 6px; box-sizing: border-box; outline: none; transition: 0.3s; font-family: inherit; }
        .form-group input:focus, .form-group select:focus { border-color: #22773c; }
        .btn-submit { background-color: #22773c; color: white; padding: 12px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; width: 100%; }
        .btn-submit:hover { background-color: #024e22; }
        .btn-volver { display: block; text-align: center; background-color: #34495e; color: white; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 10px; transition: 0.3s; }
        .btn-volver:hover { background-color: #2c3e50; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 6px; font-weight: bold; }
        .success { background-color: #e4ffea; color: #074e0b; border: 1px solid #3d8d58; }
        .error, .alerta-error { background-color: #fadbd8; color: #c0392b; border: 1px solid #e74c3c; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header"><h3>Huellitas</h3></div>
        <div class="user-info">
            <span>Bienvenido, <b><?= htmlspecialchars($nombre_usuario) ?></b></span>
            <div class="rol"><?= htmlspecialchars($rol_usuario) ?></div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">Inicio</a></li>
            <li><a href="dueños.php" class="active">Dueños y Mascotas</a></li>
            <li><a href="citas.php">Agenda / Citas</a></li>
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
            <h2>Registrar Nueva Mascota</h2>
        </div>

        <?= $mensaje ?>

        <div class="form-container">
            <form method="POST" action="">
                
                <div class="form-group">
                    <label for="id_dueño">Seleccionar Dueño:</label>
                    <select id="id_dueño" name="id_dueño" required>
                        <option value="">-- Elige un dueño de la lista --</option>
                        <?php foreach($lista_dueños as $dueño): ?>
                            <option value="<?= $dueño['id_dueño'] ?>">
                                <?= htmlspecialchars($dueño['nombre']) ?> (CC: <?= htmlspecialchars($dueño['documento_identidad']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre de la Mascota:</label>
                    <input type="text" id="nombre" name="nombre" required autocomplete="off" placeholder="Ej: Firulais">
                </div>

                <div class="form-group">
                    <label for="id_raza">Especie y Raza:</label>
                    <select id="id_raza" name="id_raza" required>
                        <option value="">-- Elige la especie y raza --</option>
                        <?php foreach($lista_razas as $raza): ?>
                            <option value="<?= $raza['id_raza'] ?>">
                                <?= htmlspecialchars($raza['nombre_especie']) ?> - <?= htmlspecialchars($raza['nombre_raza']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sexo">Sexo:</label>
                    <select id="sexo" name="sexo" required>
                        <option value="Macho">Macho</option>
                        <option value="Hembra">Hembra</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de Nacimiento (Aproximada):</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento">
                </div>

                <button type="submit" class="btn-submit">Guardar Mascota</button>
                <a href="dueños.php" class="btn-volver">Volver al Directorio</a>
            </form>
        </div>
    </div>

</body>
</html>