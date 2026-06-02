<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

if (!isset($_GET['id'])) { header("Location: dueños.php"); exit(); }
$id_mascota = (int)$_GET['id'];

// Datos de la mascota
$mascota = null;
try {
    $sql = "SELECT m.*, r.nombre AS nombre_raza, e.nombre AS nombre_especie, d.nombre AS nombre_dueño, d.telefono AS tel_dueño, d.email AS email_dueño
            FROM mascota m
            INNER JOIN raza r ON m.id_raza = r.id_raza
            INNER JOIN especie e ON r.id_especie = e.id_especie
            INNER JOIN dueño d ON m.id_dueño = d.id_dueño
            WHERE m.id_mascota = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id_mascota]);
    $mascota = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {}

if (!$mascota) { header("Location: dueños.php"); exit(); }

// Consultas
$consultas = [];
try {
    $stmt = $conexion->prepare("SELECT c.*, v.nombre AS nombre_vet FROM consulta c INNER JOIN veterinario v ON c.id_veterinario=v.id_veterinario WHERE c.id_mascota=:id ORDER BY c.fecha DESC");
    $stmt->execute([':id' => $id_mascota]);
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {}

// Próximas citas
$citas = [];
try {
    $stmt = $conexion->prepare("SELECT c.*, v.nombre AS nombre_vet FROM cita c INNER JOIN veterinario v ON c.id_veterinario=v.id_veterinario WHERE c.id_mascota=:id AND c.fecha_hora >= NOW() ORDER BY c.fecha_hora ASC LIMIT 5");
    $stmt->execute([':id' => $id_mascota]);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {}

// Vacunas
$vacunas = [];
try {
    $stmt = $conexion->prepare("SELECT cv.*, v.nombre AS nombre_vacuna, v.descripcion FROM carnet_vacunacion cv INNER JOIN vacuna v ON cv.id_vacuna=v.id_vacuna WHERE cv.id_mascota=:id ORDER BY cv.fecha_aplicacion DESC");
    $stmt->execute([':id' => $id_mascota]);
    $vacunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {}

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario    = $_SESSION['rol'];
$edad = "";
if (!empty($mascota['fecha_nacimiento'])) {
    $diff = (new DateTime())->diff(new DateTime($mascota['fecha_nacimiento']));
    $edad = $diff->y > 0 ? $diff->y . ' año(s) y ' . $diff->m . ' mes(es)' : $diff->m . ' mes(es)';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($mascota['nombre']) ?> - Huellitas</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Lato:wght@300;400;700&display=swap');
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Lato',sans-serif; background:#f0f4f1; display:flex; min-height:100vh; }

        .sidebar { width:260px; min-height:100vh; background:linear-gradient(180deg,#043a07 0%,#006805 60%,#0a7a0f 100%); color:white; position:fixed; top:0;left:0; display:flex; flex-direction:column; box-shadow:3px 0 15px rgba(0,0,0,.15); z-index:100; }
        .sidebar-header { padding:28px 25px 20px; text-align:center; background-color:rgba(0,0,0,.25); border-bottom:1px solid rgba(255,255,255,.1); }
        .sidebar-header h3 { font-family:'Nunito',sans-serif; font-size:26px; font-weight:800; color:#e4ffea; }
        .sidebar-header p  { font-size:11px; color:#a5d6a7; margin-top:4px; text-transform:uppercase; letter-spacing:2px; }
        .sidebar-menu { list-style:none; padding:12px 0; flex-grow:1; }
        .sidebar-menu li a { display:flex; align-items:center; gap:10px; padding:13px 25px; color:#d4edda; text-decoration:none; font-size:14.5px; font-weight:600; transition:all .25s; border-left:4px solid transparent; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color:rgba(255,255,255,.12); border-left-color:#7ddb8e; padding-left:32px; color:#fff; }
        .sidebar-menu li a .icon { font-size:17px; }
        .sidebar-divider { height:1px; background:rgba(255,255,255,.1); margin:8px 20px; }
        .btn-logout { margin-top:auto; }
        .btn-logout a { display:flex; align-items:center; gap:10px; padding:14px 25px; color:#ffcdd2; text-decoration:none; font-size:14px; font-weight:600; border-top:1px solid rgba(255,255,255,.1); transition:all .25s; }
        .btn-logout a:hover { background-color:#c0392b; color:#fff; }

        .main-content { margin-left:260px; width:calc(100% - 260px); display:flex; flex-direction:column; }
        .top-header { display:flex; justify-content:space-between; align-items:center; background:#fff; padding:18px 40px; box-shadow:0 2px 8px rgba(0,0,0,.06); position:sticky; top:0; z-index:50; }
        .top-header h2 { font-family:'Nunito',sans-serif; font-size:22px; font-weight:800; color:#2c3e50; }
        .user-greeting { display:flex; align-items:center; gap:12px; color:#555; font-size:14px; }
        .user-avatar { width:38px; height:38px; background:linear-gradient(135deg,#006805,#3d8d58); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:700; font-size:16px; }
        .badge-rol { background:#e8f5e9; color:#2e7d32; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; text-transform:uppercase; }

        .page-body { padding:30px 40px; }

        /* Hero del perfil */
        .perfil-hero {
            background:linear-gradient(135deg,#006805 0%,#3dba4e 100%);
            border-radius:14px;
            padding:28px 32px;
            color:white;
            display:flex;
            align-items:center;
            gap:24px;
            margin-bottom:28px;
            position:relative;
            overflow:hidden;
        }
        .perfil-hero::after { content:'🐾'; position:absolute; right:24px; font-size:90px; opacity:.12; top:50%; transform:translateY(-50%); }
        .avatar-mascota { width:80px; height:80px; background:rgba(255,255,255,.2); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:44px; flex-shrink:0; }
        .perfil-info h1 { font-family:'Nunito',sans-serif; font-size:28px; font-weight:800; }
        .perfil-info .meta { display:flex; gap:16px; margin-top:6px; flex-wrap:wrap; }
        .perfil-info .meta span { font-size:13px; opacity:.9; background:rgba(0,0,0,.15); padding:3px 10px; border-radius:10px; }
        .perfil-hero .actions { margin-left:auto; display:flex; flex-direction:column; gap:8px; z-index:1; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:8px; text-decoration:none; font-weight:700; font-size:13px; cursor:pointer; border:none; transition:all .2s; font-family:'Lato',sans-serif; }
        .btn-white  { background:#fff; color:#006805; }
        .btn-white:hover  { background:#e8f5e9; }
        .btn-red    { background:#e74c3c; color:#fff; }
        .btn-red:hover    { background:#c0392b; }

        /* Secciones */
        .sections-grid { display:grid; grid-template-columns:1fr 1fr; gap:22px; }
        .section-card { background:#fff; border-radius:12px; padding:22px; box-shadow:0 2px 10px rgba(0,0,0,.05); }
        .section-card h4 { font-family:'Nunito',sans-serif; font-size:16px; font-weight:800; color:#2c3e50; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
        .section-card.full { grid-column:1 / -1; }

        /* Info básica */
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .info-item label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:#7f8c8d; font-weight:700; display:block; margin-bottom:3px; }
        .info-item span { font-size:14px; font-weight:600; color:#2c3e50; }

        /* Tabla compacta */
        .mini-table { width:100%; border-collapse:collapse; }
        .mini-table th { background:#f0faf2; color:#2e7d32; text-align:left; padding:9px 12px; font-size:11px; text-transform:uppercase; letter-spacing:.4px; border-bottom:2px solid #d4edda; }
        .mini-table td { padding:10px 12px; border-bottom:1px solid #f0f0f0; font-size:13px; color:#2c3e50; }
        .mini-table tr:last-child td { border-bottom:none; }
        .mini-table tr:hover td { background:#f9fbf9; }

        .badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:700; }
        .badge-Pendiente  { background:#fff3cd; color:#856404; }
        .badge-Completada { background:#d4edda; color:#155724; }
        .badge-prox { background:#d1ecf1; color:#0c5460; }

        .fecha-prox { color:#d35400; font-weight:700; background:#fdebd0; padding:3px 8px; border-radius:5px; font-size:12px; }
        .empty-msg { color:#7f8c8d; font-size:13px; text-align:center; padding:20px; }

        /* PDF print */
        @media print {
            .sidebar, .top-header, .btn, .actions { display:none !important; }
            body { background:white; }
            .main-content { margin-left:0; width:100%; }
            .page-body { padding:10px; }
            .perfil-hero { background:#006805 !important; -webkit-print-color-adjust:exact; }
            .section-card { box-shadow:none; border:1px solid #ddd; }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header">
        <h3>Huellitas</h3>
        <p>Clínica Veterinaria</p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><span class="icon">🏠</span> Inicio</a></li>
        <div class="sidebar-divider"></div>
        <li><a href="dueños.php" class="active"><span class="icon">🐾</span> Dueños y Mascotas</a></li>
        <li><a href="citas.php"><span class="icon">📅</span> Agenda / Citas</a></li>
        <li><a href="consultas.php"><span class="icon">🩺</span> Consultas Médicas</a></li>
        <div class="sidebar-divider"></div>
        <li><a href="tratamientos.php"><span class="icon">💊</span> Tratamientos</a></li>
        <li><a href="medicamentos.php"><span class="icon">🧪</span> Catálogo Medicamentos</a></li>
        <li><a href="reportes.php"><span class="icon">📊</span> Reportes y Estadísticas</a></li>
    </ul>
    <div class="btn-logout"><a href="logout.php"><span class="icon">🚪</span> Cerrar Sesión</a></div>
</div>

<div class="main-content">
    <div class="top-header">
        <h2>← <a href="dueños.php" style="color:#22773c;text-decoration:none;">Directorio</a> / <?= htmlspecialchars($mascota['nombre']) ?></h2>
        <div class="user-greeting">
            <div class="user-avatar"><?= strtoupper(substr($nombre_usuario,0,1)) ?></div>
            <div>Bienvenido, <strong><?= htmlspecialchars($nombre_usuario) ?></strong></div>
            <span class="badge-rol"><?= htmlspecialchars($rol_usuario) ?></span>
        </div>
    </div>

    <div class="page-body">

        <!-- Hero -->
        <div class="perfil-hero">
            <div class="avatar-mascota">
                <?= $mascota['nombre_especie'] === 'Gato' ? '🐱' : ($mascota['nombre_especie'] === 'Perro' ? '🐶' : '🐾') ?>
            </div>
            <div class="perfil-info">
                <h1><?= htmlspecialchars($mascota['nombre']) ?></h1>
                <div class="meta">
                    <span><?= htmlspecialchars($mascota['nombre_especie']) ?> — <?= htmlspecialchars($mascota['nombre_raza']) ?></span>
                    <span><?= htmlspecialchars($mascota['sexo']) ?></span>
                    <?php if($edad): ?><span>🎂 <?= $edad ?></span><?php endif; ?>
                    <span>Dueño: <?= htmlspecialchars($mascota['nombre_dueño']) ?></span>
                </div>
            </div>
            <div class="actions">
                <button onclick="window.print()" class="btn btn-white">📄 Descargar Carnet PDF</button>
                <a href="nueva_cita.php" class="btn btn-white">📅 Agendar Cita</a>
                <a href="nueva_consulta.php" class="btn btn-red">🩺 Nueva Consulta</a>
            </div>
        </div>

        <div class="sections-grid">
            <!-- Info básica -->
            <div class="section-card">
                <h4>📋 Información del Dueño</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Dueño</label>
                        <span><?= htmlspecialchars($mascota['nombre_dueño']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Teléfono</label>
                        <span><?= htmlspecialchars($mascota['tel_dueño']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Correo</label>
                        <span><?= htmlspecialchars($mascota['email_dueño'] ?: 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Fecha de Nacimiento</label>
                        <span><?= !empty($mascota['fecha_nacimiento']) ? date('d/m/Y', strtotime($mascota['fecha_nacimiento'])) : 'No registrada' ?></span>
                    </div>
                </div>
            </div>

            <!-- Próximas citas -->
            <div class="section-card">
                <h4>📅 Próximas Citas</h4>
                <?php if(count($citas) > 0): ?>
                <table class="mini-table">
                    <thead><tr><th>Fecha</th><th>Motivo</th><th>Veterinario</th><th>Estado</th></tr></thead>
                    <tbody>
                    <?php foreach($citas as $cita): ?>
                        <tr>
                            <td><strong><?= date('d/m/Y H:i', strtotime($cita['fecha_hora'])) ?></strong></td>
                            <td><?= htmlspecialchars(substr($cita['motivo_previo'],0,30)) ?>…</td>
                            <td><?= htmlspecialchars($cita['nombre_vet']) ?></td>
                            <td><span class="badge badge-<?= $cita['estado'] ?>"><?= $cita['estado'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="empty-msg">No hay citas próximas agendadas.</p>
                <?php endif; ?>
            </div>

            <!-- Vacunas -->
            <div class="section-card full">
                <h4>💉 Carnet de Vacunación</h4>
                <?php if(count($vacunas) > 0): ?>
                <table class="mini-table">
                    <thead><tr><th>Fecha Aplicación</th><th>Vacuna</th><th>Descripción</th><th>Próxima Dosis</th></tr></thead>
                    <tbody>
                    <?php foreach($vacunas as $v): ?>
                        <tr>
                            <td><strong><?= date('d/m/Y', strtotime($v['fecha_aplicacion'])) ?></strong></td>
                            <td><?= htmlspecialchars($v['nombre_vacuna']) ?></td>
                            <td><?= htmlspecialchars(substr($v['descripcion'] ?? '',0,60)) ?></td>
                            <td>
                                <?php if(!empty($v['proxima_dosis'])): ?>
                                    <span class="fecha-prox"><?= date('d/m/Y', strtotime($v['proxima_dosis'])) ?></span>
                                <?php else: ?>
                                    <span style="color:#95a5a6;font-size:12px;">Única dosis</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="empty-msg">No hay vacunas registradas para esta mascota.</p>
                <?php endif; ?>
            </div>

            <!-- Historial de consultas -->
            <div class="section-card full">
                <h4>🩺 Historial de Consultas</h4>
                <?php if(count($consultas) > 0): ?>
                <table class="mini-table">
                    <thead><tr><th>Fecha</th><th>Motivo</th><th>Diagnóstico</th><th>Observaciones</th><th>Veterinario</th></tr></thead>
                    <tbody>
                    <?php foreach($consultas as $con): ?>
                        <tr>
                            <td><strong><?= date('d/m/Y', strtotime($con['fecha'])) ?></strong></td>
                            <td><?= htmlspecialchars($con['motivo']) ?></td>
                            <td><?= htmlspecialchars($con['diagnostico']) ?></td>
                            <td><?= htmlspecialchars($con['observaciones'] ?: '—') ?></td>
                            <td><?= htmlspecialchars($con['nombre_vet']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="empty-msg">No hay consultas registradas para esta mascota.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>