<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $documento = trim($_POST['documento']);
    $nombre    = trim($_POST['nombre']);
    $telefono  = trim($_POST['telefono']);
    $email     = trim($_POST['email']);
    $direccion = trim($_POST['direccion']);

    try {
        $sql = "INSERT INTO dueño (documento_identidad, nombre, telefono, email, direccion)
                VALUES (:doc, :nombre, :telefono, :email, :direccion)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':doc'=>$documento, ':nombre'=>$nombre, ':telefono'=>$telefono, ':email'=>$email, ':direccion'=>$direccion]);

        header("Location: dueños.php");
        exit();
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) {
            $mensaje = "<div class='alert error'>Ya existe un dueño con ese documento de identidad.</div>";
        } else {
            $mensaje = "<div class='alert error'>Error al registrar: " . $e->getMessage() . "</div>";
        }
    }
}

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario    = $_SESSION['rol'];
$titulo_pagina  = 'Nuevo Dueño';
$pagina_activa  = 'dueños';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Dueño - Huellitas</title>
    <link rel="stylesheet" href="huellitas-shared.css">
    <link rel="stylesheet" href="huellitas-layout.css">
    <style>
        .page-body { padding: 35px 40px; }
        .form-card { border-radius: 12px; padding: 35px; max-width: 580px; margin: 0 auto; }
        .form-card h3 { font-family: 'Nunito', sans-serif; font-size: 20px; font-weight: 800; margin-bottom: 22px; padding-bottom: 14px; border-bottom: 2px solid var(--color-border); }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 7px; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: .3px; }
        .form-group input { width: 100%; padding: 11px 14px; border: 2px solid var(--color-border); border-radius: 8px; font-size: 14px; outline: none; transition: border-color .2s; }
        .btn-row { display: flex; gap: 10px; margin-top: 24px; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 11px 20px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; cursor: pointer; border: none; transition: all .2s; flex: 1; }
        .btn-primary { background: #22773c; color: #fff; }
        .btn-primary:hover { background: #024e22; }
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
            <h3>👤 Datos del Nuevo Dueño</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Documento de Identidad</label>
                    <input type="text" name="documento" required autocomplete="off" placeholder="Ej: 1234567890">
                </div>
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre" required autocomplete="off" placeholder="Ej: Carlos Pérez Gómez">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" required autocomplete="off" placeholder="Ej: 3001234567">
                </div>
                <div class="form-group">
                    <label>Correo Electrónico (Opcional)</label>
                    <input type="email" name="email" autocomplete="off" placeholder="correo@ejemplo.com">
                </div>
                <div class="form-group">
                    <label>Dirección (Opcional)</label>
                    <input type="text" name="direccion" autocomplete="off" placeholder="Ej: Calle 45 # 12-30">
                </div>
                <div class="btn-row">
                    <button type="submit" class="btn btn-primary">💾 Guardar Dueño</button>
                    <a href="nueva_mascota.php" class="btn btn-orange">🐶 Nueva Mascota</a>
                    <a href="dueños.php" class="btn btn-secondary">← Volver</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="huellitas-shared.js"></script>
</body>
</html>