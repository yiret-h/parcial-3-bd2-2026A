<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

$mensaje     = "";
$lista_dueños = [];
$lista_razas  = [];

try {
    // Obtener lista de dueños para el select
    $lista_dueños = $conexion->query("SELECT id_dueño, documento_identidad, nombre FROM dueño ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener lista de razas agrupadas o con el nombre de la especie
    $lista_razas = $conexion->query(
        "SELECT r.id_raza, r.nombre AS nombre_raza, e.nombre AS nombre_especie 
         FROM raza r 
         INNER JOIN especie e ON r.id_especie = e.id_especie 
         ORDER BY e.nombre, r.nombre"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = "<div class='alert error'>Error al cargar datos: " . $e->getMessage() . "</div>";
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_dueño_f       = (int)$_POST['id_dueño'];
    $nombre_mascota   = trim($_POST['nombre']);
    $id_raza          = (int)$_POST['id_raza'];
    $sexo             = $_POST['sexo'];
    $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;

    try {
        $sql = "INSERT INTO mascota (nombre, id_raza, sexo, fecha_nacimiento, id_dueño) 
                VALUES (:nombre, :id_raza, :sexo, :fecha_nac, :id_dueno)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nombre'    => $nombre_mascota,
            ':id_raza'   => $id_raza,
            ':sexo'      => $sexo,
            ':fecha_nac' => $fecha_nacimiento,
            ':id_dueno'  => $id_dueño_f
        ]);
        
        header("Location: dueños.php");
        exit();
    } catch(PDOException $e) {
        $mensaje = "<div class='alert error'>Error al guardar: " . $e->getMessage() . "</div>";
    }
}

// ID preseleccionado si viene de dueños.php
$id_dueño_pre = isset($_GET['id_dueño']) ? (int)$_GET['id_dueño'] : 0;


$nombre_usuario = $_SESSION['usuario'];
$rol_usuario    = $_SESSION['rol'];
$titulo_pagina  = 'Nueva Mascota';
$pagina_activa  = 'dueños';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Mascota - Huellitas</title>
    <link rel="stylesheet" href="huellitas-shared.css?v=4">
    <link rel="stylesheet" href="huellitas-layout.css?v=4">
    <style>
        .page-body { padding: 35px 40px; }
        .form-card { border-radius: 12px; padding: 35px; max-width: 580px; margin: 0 auto; }
        .form-card h3 { font-family: 'Nunito', sans-serif; font-size: 20px; font-weight: 800; margin-bottom: 22px; padding-bottom: 14px; border-bottom: 2px solid var(--color-border); }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 7px; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: .3px; }
        .form-group input, .form-group select { width: 100%; padding: 11px 14px; border: 2px solid var(--color-border); border-radius: 8px; font-size: 14px; outline: none; transition: border-color .2s; }
        .btn-row { display: flex; gap: 10px; margin-top: 24px; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 11px 20px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; cursor: pointer; border: none; transition: all .2s; flex: 1; }
        .btn-primary { background: #0c83a7; color: #fff; }
        .btn-primary:hover { background: #085e79; }
        .btn-secondary { background: #ecf0f1; color: #555; }
        .btn-secondary:hover { background: #bdc3c7; }
        .btn-orange { background: #f39c12; color: #fff; }
        .btn-orange:hover { background: #d68910; }
        .alert { padding: 14px; margin-bottom: 18px; border-radius: 8px; font-weight: 700; }
        .error { background: #fadbd8; color: #c0392b; border: 1px solid #e74c3c; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <?php include 'topbar.php'; ?>

    <div class="page-body">
        <?= $mensaje ?>
        <div class="form-card">
            <h3>&#128054; Datos de la Nueva Mascota</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Dueño</label>
                    <select name="id_dueño" required>
                        <option value="">-- Selecciona el dueño --</option>
                        <?php foreach($lista_dueños as $d): ?>
                            <option value="<?= $d['id_dueño'] ?>" <?= $d['id_dueño'] == $id_dueño_pre ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['nombre']) ?> (CC: <?= htmlspecialchars($d['documento_identidad']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nombre de la Mascota</label>
                    <input type="text" name="nombre" required autocomplete="off" placeholder="Ej: Firulais">
                </div>
                <div class="form-group">
                    <label>Especie y Raza</label>
                    <select name="id_raza" required>
                        <option value="">-- Selecciona especie y raza --</option>
                        <?php foreach($lista_razas as $r): ?>
                            <option value="<?= $r['id_raza'] ?>"><?= htmlspecialchars($r['nombre_especie']) ?> — <?= htmlspecialchars($r['nombre_raza']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Sexo</label>
                    <select name="sexo" required>           
                        <option value="Macho">Macho</option>
                        <option value="Hembra">Hembra</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fecha de Nacimiento (Aproximada)</label>
                    <input type="date" name="fecha_nacimiento">
                </div>
                <div class="btn-row" style="justify-content: space-between;">
                    <a href="dueños.php" class="btn btn-secondary" style="flex:0.3; text-align:center;">← Volver</a>
                    <button type="submit" class="btn btn-primary" style="flex:0.6; background:#0c83a7; color:white; border:none; text-align:center;">&#10004; Finalizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="huellitas-shared.js"></script>
</body>
</html>