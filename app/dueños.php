<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/conexion.php';

$lista_dueños = [];
$error_db = "";

try {
   
    $sql = "SELECT d.id_dueño, d.documento_identidad, d.nombre, d.telefono, d.email, 
                   GROUP_CONCAT(m.nombre SEPARATOR ', ') AS lista_mascotas
            FROM dueño d
            LEFT JOIN mascota m ON d.id_dueño = m.id_dueño
            GROUP BY d.id_dueño
            ORDER BY d.nombre ASC";
            
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $lista_dueños = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_db = "Error al conectar con la tabla: " . $e->getMessage();
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
        /* Estilos del Dashboard */
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
        .btn-logout:hover { background-color: #e74c3c !important; border-left-color: #e74c3c !important; }

        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); box-sizing: border-box; }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h2 { margin: 0; color: #2c3e50; }
        .btn-nuevo { background-color: #f39c12; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; transition: 0.3s; }
        .btn-nuevo:hover { background-color: #d68910; }
        
        .table-container { background-color: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #22773c; color: white; text-align: left; padding: 15px; font-size: 14px; }
        td { padding: 15px; border-bottom: 1px solid #ecf0f1; color: #2c3e50; font-size: 14px; }
        tr:hover { background-color: #f9fbf9; }
        .btn-accion { background-color: #3498db; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; }
        .btn-accion:hover { background-color: #2980b9; }
        .alerta-error { background-color: #fadbd8; color: #c0392b; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #e74c3c; font-weight: bold; }
        .mascota-tag { background-color: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 13px; border: 1px solid #c8e6c9; }
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
            <h2>Directorio de Clientes</h2>
            <a href="nuevo_dueño.php" class="btn-nuevo">+ Añadir Nuevo Dueño</a>
        </div>

        <?php if(!empty($error_db)): ?>
            <div class="alerta-error"><?= $error_db ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Nombre del Dueño</th>
                        <th>Teléfono</th>
                        <th>Mascotas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($lista_dueños) > 0): ?>
                        <?php foreach ($lista_dueños as $dueño): ?>
                            <tr>
                                <td><?= htmlspecialchars($dueño['documento_identidad']) ?></td>
                                <td><strong><?= htmlspecialchars($dueño['nombre']) ?></strong></td>
                                <td><?= htmlspecialchars($dueño['telefono']) ?></td>
                                <td>
                                    <?php if (!empty($dueño['lista_mascotas'])): ?>
                                        <span class="mascota-tag"><?= htmlspecialchars($dueño['lista_mascotas']) ?></span>
                                    <?php else: ?>
                                        <em style="color:#7f8c8d;">Sin registrar</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="nueva_mascota.php?id_dueño=<?= $dueño['id_dueño'] ?>" class="btn-accion">+ Añadir Mascota</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: #7f8c8d;">
                                No hay clientes registrados aún. Haz clic en "Añadir Nuevo Dueño" para comenzar.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>