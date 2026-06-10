<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

$mensaje = "";
$lista_mascotas = [];
$lista_veterinarios = [];

try {
    $sql_mascotas = "SELECT m.id_mascota, m.nombre AS nombre_mascota, d.nombre AS nombre_dueño FROM mascota m INNER JOIN dueño d ON m.id_dueño = d.id_dueño ORDER BY d.nombre ASC, m.nombre ASC";
    $lista_mascotas = $conexion->query($sql_mascotas)->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) { $mensaje .= "<div class='alert error'>Error: " . $e->getMessage() . "</div>"; }

try {
    $sql_vet = "SELECT id_veterinario, nombre, especialidad FROM veterinario ORDER BY especialidad ASC, nombre ASC";
    $lista_veterinarios = $conexion->query($sql_vet)->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) { $mensaje .= "<div class='alert error'>Error: " . $e->getMessage() . "</div>"; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_mascota = $_POST['id_mascota'];
    $fecha_hora = str_replace('T', ' ', $_POST['fecha_hora']); 
    $tipo_cita = $_POST['tipo_cita'];
    $detalles = trim($_POST['detalles_motivo']);
    $id_veterinario = $_POST['id_veterinario'];
    
    // Unimos el tipo general con los detalles para guardarlo en la base de datos
    $motivo_previo = $tipo_cita . ($detalles != "" ? " - " . $detalles : "");

    try {
        $sql = "INSERT INTO cita (id_mascota, fecha_hora, motivo_previo, estado, id_veterinario) VALUES (:id_m, :fecha, :motivo, 'Pendiente', :id_v)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id_m' => $id_mascota, ':fecha' => $fecha_hora, ':motivo' => $motivo_previo, ':id_v' => $id_veterinario]);
        header("Location: citas.php");
        exit();
    } catch(PDOException $e) {
        $mensaje = "<div class='alert error'>Error al programar: " . $e->getMessage() . "</div>";
    }
}

$nombre_usuario = $_SESSION['usuario']; $rol_usuario = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Programar Cita - Huellitas</title>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="huellitas-shared.css?v=4"><link rel="stylesheet" href="huellitas-layout.css?v=4">
    <style>
        .page-body { padding: 35px 40px; }
        .form-card { border-radius: 12px; padding: 35px; max-width: 600px; margin: 0 auto; }
        .form-card h3 { font-family: 'Nunito', sans-serif; font-size: 20px; font-weight: 800; margin-bottom: 22px; padding-bottom: 14px; border-bottom: 2px solid var(--color-border); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 700; font-size: 13px; text-transform: uppercase; color: var(--color-muted); }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 11px 14px; border: 2px solid var(--color-border); border-radius: 8px; font-size: 14px; background: var(--input-bg); color: var(--color-text); }
        .btn-submit { background-color: #f39c12; color: white; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; font-size: 15px; margin-bottom: 10px;}
        .btn-volver { display: block; text-align: center; background-color: #34495e; color: white; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: bold; }
        .select2-container--default .select2-selection--single { background: var(--input-bg); border: 2px solid var(--color-border); border-radius: 8px; height: 44px; display: flex; align-items: center; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--color-text); }
        .select2-dropdown { background: var(--bg-card); border: 2px solid var(--color-border); }
        .select2-search__field { background: var(--input-bg); color: var(--color-text); }
        .select2-results__option { color: var(--color-text); }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <?php include 'topbar.php'; ?>
    <div class="page-body">
        <?= $mensaje ?>
        <div class="form-card">
            <h3>&#128197; Programar Nueva Cita</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Paciente (Mascota):</label>
                    <select name="id_mascota" class="buscador-select" required>
                        <option value="">-- Buscar paciente --</option>
                        <?php foreach($lista_mascotas as $m): ?>
                            <option value="<?= $m['id_mascota'] ?>"><?= htmlspecialchars($m['nombre_mascota']) ?> (Dueño: <?= htmlspecialchars($m['nombre_dueño']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Día y Hora:</label>
                    <input type="datetime-local" name="fecha_hora" required>
                </div>
                <div class="form-group">
                    <label>Tipo de Cita (Motivo General):</label>
                    <select name="tipo_cita" required style="border-color: #f39c12; border-width: 2px;">
                        <option value="Consulta General">&#129658; Consulta General</option>
                        <option value="Vacunación">&#128137; Vacunación</option>
                        <option value="Desparasitación">&#128138; Desparasitación</option>
                        <option value="Revisión/Control">&#128203; Revisión / Control</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Detalles adicionales (Opcional):</label>
                    <textarea name="detalles_motivo" placeholder="Ej: Trae su propio carnet, el dueño reporta decaimiento..."></textarea>
                </div>
                <div class="form-group">
                    <label>Médico Tratante:</label>
                    <select name="id_veterinario" class="buscador-select" required>
                        <option value="">-- Buscar médico --</option>
                        <?php foreach($lista_veterinarios as $v): ?>
                            <option value="<?= $v['id_veterinario'] ?>"><?= htmlspecialchars($v['nombre']) ?> (<?= htmlspecialchars($v['especialidad']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Guardar en Agenda</button>
                <a href="citas.php" class="btn-volver">Volver a la Agenda</a>
            </form>
        </div>
    </div>
</div>
<script src="huellitas-shared.js"></script>
<script>$(document).ready(function() { $('.buscador-select').select2({width: '100%'}); });</script>
</body>
</html>