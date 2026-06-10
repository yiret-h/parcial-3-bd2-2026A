<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

$mensaje = "";
$lista_mascotas = [];
$lista_vacunas  = [];

try {
    // Obtener lista de mascotas
    $lista_mascotas = $conexion->query("
        SELECT m.id_mascota, m.nombre AS nombre_mascota, d.nombre AS nombre_dueño, e.nombre AS nombre_especie 
        FROM mascota m 
        INNER JOIN dueño d ON m.id_dueño = d.id_dueño 
        INNER JOIN especie e ON m.id_especie = e.id_especie
        ORDER BY m.nombre
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener lista de vacunas
    $lista_vacunas = $conexion->query("SELECT id_vacuna, nombre, descripcion FROM vacuna ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = "<div class='alert error'>Error al cargar datos: " . $e->getMessage() . "</div>";
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_mascota       = (int)$_POST['id_mascota'];
    $id_vacuna        = (int)$_POST['id_vacuna'];
    $fecha_aplicacion = $_POST['fecha_aplicacion'];
    $proxima_dosis    = !empty($_POST['proxima_dosis']) ? $_POST['proxima_dosis'] : null;

    // Convertir el formato 'T' de datetime-local a espacio para compatibilidad segura con MySQL
    if ($proxima_dosis) {
        $proxima_dosis = str_replace('T', ' ', $proxima_dosis);
    }

    try {
        $sql = "INSERT INTO carnet_vacunacion (id_mascota, id_vacuna, fecha_aplicacion, proxima_dosis) 
                VALUES (:id_m, :id_v, :fecha_app, :prox)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_m'      => $id_mascota,
            ':id_v'      => $id_vacuna,
            ':fecha_app' => $fecha_aplicacion,
            ':prox'      => $proxima_dosis
        ]);
        
        header("Location: vacunas.php");
        exit();
    } catch(PDOException $e) {
        $mensaje = "<div class='alert error'>Error al registrar la vacuna: " . $e->getMessage() . "</div>";
    }
}

// Preseleccionar mascota si viene de otra página
$id_mascota_pre = isset($_GET['id_mascota']) ? (int)$_GET['id_mascota'] : 0;

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario    = $_SESSION['rol'];
$titulo_pagina  = 'Registrar Vacuna';
$pagina_activa  = 'vacunas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Vacuna - Huellitas</title>
    <link rel="stylesheet" href="huellitas-shared.css?v=4">
    <link rel="stylesheet" href="huellitas-layout.css?v=4">
    <style>
        .page-body { padding: 35px 40px; }
        .form-card { border-radius: 12px; padding: 35px; max-width: 600px; margin: 0 auto; background: var(--bg-card); }
        .form-card h3 { font-family: 'Nunito', sans-serif; font-size: 20px; font-weight: 800; margin-bottom: 22px; padding-bottom: 14px; border-bottom: 2px solid var(--color-border); color: var(--color-text); }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 7px; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: .3px; color: var(--color-muted); }
        .form-group input, .form-group select { width: 100%; padding: 11px 14px; border: 2px solid var(--color-border); border-radius: 8px; font-size: 14px; outline: none; transition: border-color .2s; background: var(--input-bg); color: var(--color-text); }
        .btn-row { display: flex; gap: 10px; margin-top: 24px; flex-wrap: wrap; justify-content: space-between; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 11px 20px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; cursor: pointer; border: none; transition: all .2s; flex: 1; }
        .btn-primary { background: #0c83a7; color: #fff; flex: 0.6; }
        .btn-primary:hover { background: #085e79; }
        .btn-secondary { background: var(--input-bg); color: var(--color-text); border: 1px solid var(--color-border); flex: 0.3; }
        .btn-secondary:hover { background: var(--color-hover); }
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
            <h3>💉 Registrar Aplicación de Vacuna</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Paciente (Mascota)</label>
                    <select name="id_mascota" required>
                        <option value="">-- Selecciona el paciente --</option>
                        <?php foreach($lista_mascotas as $m): ?>
                            <option value="<?= $m['id_mascota'] ?>" <?= $m['id_mascota'] == $id_mascota_pre ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['nombre_mascota']) ?> (<?= htmlspecialchars($m['nombre_especie']) ?>) - Dueño: <?= htmlspecialchars($m['nombre_dueño']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Vacuna Aplicada</label>
                    <select name="id_vacuna" required>
                        <option value="">-- Selecciona la vacuna --</option>
                        <?php foreach($lista_vacunas as $v): ?>
                            <option value="<?= $v['id_vacuna'] ?>" title="<?= htmlspecialchars($v['descripcion']) ?>">
                                <?= htmlspecialchars($v['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Fecha de Aplicación</label>
                    <input type="date" name="fecha_aplicacion" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label>Fecha del Próximo Refuerzo (Opcional)</label>
                    <input type="datetime-local" name="proxima_dosis">
                </div>

                <div class="btn-row">
                    <a href="vacunas.php" class="btn btn-secondary">← Cancelar</a>
                    <button type="submit" class="btn btn-primary">✔ Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="huellitas-shared.js"></script>
</body>
</html>
