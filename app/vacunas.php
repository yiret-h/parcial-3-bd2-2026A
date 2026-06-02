<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

$lista_carnets = [];
$error_db = "";

try {
    $sql = "SELECT cv.id_carnet, cv.fecha_aplicacion, cv.proxima_dosis, 
                   v.nombre AS nombre_vacuna, 
                   m.nombre AS nombre_mascota, 
                   d.nombre AS nombre_dueño
            FROM carnet_vacunacion cv
            INNER JOIN vacuna v ON cv.id_vacuna = v.id_vacuna
            INNER JOIN mascota m ON cv.id_mascota = m.id_mascota
            INNER JOIN dueño d ON m.id_dueño = d.id_dueño
            ORDER BY cv.fecha_aplicacion DESC";
            
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $lista_carnets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_db = "Error al cargar el carnet de vacunación: " . $e->getMessage();
}

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario    = $_SESSION['rol'];
$titulo_pagina  = 'Historial de Vacunación';
$pagina_activa  = 'vacunas'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carnet de Vacunación - Huellitas</title>
    <link rel="stylesheet" href="huellitas-shared.css">
    <link rel="stylesheet" href="huellitas-layout.css">
    <style>
        .page-body { padding: 40px; }
        .page-header { display: flex; justify-content: flex-end; align-items: center; margin-bottom: 30px; gap: 10px; }
        .btn-nuevo { background-color: #f39c12; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; transition: 0.3s; }
        .btn-nuevo:hover { background-color: #d68910; }
        .btn-pdf { background-color: #e74c3c; color: white; padding: 10px 20px; border-radius: 6px; border: none; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 15px; }
        .btn-pdf:hover { background-color: #c0392b; }
        
        .table-container { border-radius: 8px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        .alerta-error { background-color: #fadbd8; color: #c0392b; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        .fecha-proxima { color: #d35400; font-weight: bold; background-color: #fdebd0; padding: 4px 8px; border-radius: 4px; font-size: 12px; }

        @media print {
            body { background-color: white; }
            .sidebar, .top-header, .page-header { display: none !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            .table-container { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <?php include 'topbar.php'; ?>

    <div class="page-body">
        <div class="page-header">
            <a href="nueva_vacuna.php" class="btn-nuevo">+ Registrar Vacuna</a>
            <button onclick="window.print()" class="btn-pdf">📄 Descargar PDF</button>
        </div>
        
        <?php if(!empty($error_db)): ?>
            <div class="alerta-error"><?= $error_db ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Fecha Aplicación</th>
                        <th>Paciente (Mascota)</th>
                        <th>Dueño</th>
                        <th>Vacuna Aplicada</th>
                        <th>Próxima Dosis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($lista_carnets) > 0): ?>
                        <?php foreach ($lista_carnets as $registro): ?>
                            <tr>
                                <td><strong><?= date("d/m/Y", strtotime($registro['fecha_aplicacion'])) ?></strong></td>
                                <td><?= htmlspecialchars($registro['nombre_mascota']) ?></td>
                                <td><?= htmlspecialchars($registro['nombre_dueño']) ?></td>
                                <td><?= htmlspecialchars($registro['nombre_vacuna']) ?></td>
                                <td>
                                    <?php if (!empty($registro['proxima_dosis'])): ?>
                                        <span class="fecha-proxima">
                                            <?= date("d/m/Y", strtotime($registro['proxima_dosis'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--color-muted); font-size: 13px;">No requiere / Única dosis</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: var(--color-muted);">
                                No hay registros de vacunación. Haz clic en "Registrar Vacuna" para iniciar.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="huellitas-shared.js"></script>
</body>
</html>