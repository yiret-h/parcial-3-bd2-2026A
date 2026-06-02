<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear') {
    try {
        $sql = "INSERT INTO tratamiento (id_consulta, descripcion, duracion_dias) VALUES (:id_c, :desc, :dias)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id_c'=>(int)$_POST['id_consulta'], ':desc'=>trim($_POST['descripcion']), ':dias'=>(int)$_POST['duracion_dias']]);
        $mensaje = "<div class='alert success'>✅ Tratamiento registrado correctamente.</div>";
    } catch(PDOException $e) {
        $mensaje = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
    }
}

$lista_consultas = [];
try {
    $sql = "SELECT c.id_consulta, c.fecha, c.diagnostico, m.nombre AS nombre_mascota, d.nombre AS nombre_dueño
            FROM consulta c
            INNER JOIN mascota m ON c.id_mascota=m.id_mascota
            INNER JOIN dueño d ON m.id_dueño=d.id_dueño
            ORDER BY c.fecha DESC";
    $lista_consultas = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {}

$tratamientos = [];
try {
    $sql = "SELECT t.id_tratamiento, t.descripcion, t.duracion_dias, t.id_consulta,
                   c.fecha AS fecha_consulta, c.diagnostico,
                   m.nombre AS nombre_mascota, d.nombre AS nombre_dueño
            FROM tratamiento t
            INNER JOIN consulta c ON t.id_consulta=c.id_consulta
            INNER JOIN mascota m ON c.id_mascota=m.id_mascota
            INNER JOIN dueño d ON m.id_dueño=d.id_dueño
            ORDER BY c.fecha DESC";
    $tratamientos = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {}

function estadoTrat($fecha, $dias) {
    $fin = (new DateTime($fecha))->modify("+$dias days");
    return $fin >= new DateTime() ? 'Activo' : 'Completado';
}
function diasRestantes($fecha, $dias) {
    $fin = (new DateTime($fecha))->modify("+$dias days");
    $hoy = new DateTime();
    if ($fin < $hoy) return 0;
    return (int)$hoy->diff($fin)->days;
}

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario    = $_SESSION['rol'];
$titulo_pagina  = 'Tratamientos';
$pagina_activa  = 'tratamientos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tratamientos - Huellitas</title>
    <link rel="stylesheet" href="huellitas-shared.css">
    <link rel="stylesheet" href="huellitas-layout.css">
    <style>
        .page-body { padding:30px 40px; }
        .layout { display:grid; grid-template-columns:360px 1fr; gap:24px; align-items:start; }

        .form-card { padding:24px; position:sticky; top:90px; }
        .form-group { margin-bottom:15px; }
        .form-group label { display:block; margin-bottom:6px; font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:.3px; }
        .form-group select, .form-group input, .form-group textarea { width:100%; padding:10px 12px; border:2px solid var(--color-border); border-radius:8px; outline:none; background: var(--input-bg); color: var(--color-text); }
        .form-group textarea { min-height:80px; resize:vertical; }
        .btn-submit { width:100%; padding:12px; background:#9b59b6; color:#fff; border:none; border-radius:8px; font-weight:700; font-size:15px; cursor:pointer; }
        .btn-submit:hover { filter: brightness(0.9); }

        .toolbar { display:flex; align-items:center; gap:12px; margin-bottom:18px; }
        .search-box { display:flex; align-items:center; border:2px solid var(--color-border); border-radius:8px; padding:0 12px; gap:8px; flex:1; background: var(--input-bg); }
        .search-box input { border:none; outline:none; padding:9px 0; width:100%; background:transparent; color: var(--color-text); }

        .trat-card { border-radius:10px; padding:18px 20px; margin-bottom:12px; border-left:5px solid #9b59b6; display:grid; grid-template-columns:1fr auto; gap:10px; align-items:start; }
        .trat-card.completado { border-left-color: var(--color-border); opacity:.8; }
        .trat-meta { font-size:12.5px; margin-bottom:8px; }
        .trat-desc { font-size:13.5px; margin-bottom:8px; }
        
        .progress-bar { height:6px; background:var(--color-border); border-radius:3px; overflow:hidden; }
        .progress-fill { height:100%; background:linear-gradient(90deg,#9b59b6,#d7bde2); border-radius:3px; transition:width .4s; }
        .progress-fill.done { background:#bdc3c7; }
        
        .badge { display:inline-block; padding:3px 10px; border-radius:10px; font-size:11px; font-weight:700; }
        .badge-activo { background:#e8daef; color:#6c3483; }
        .badge-completado { background:#eaecee; color:#7f8c8d; }
        .dias-left { font-size:12px; color:#9b59b6; font-weight:700; margin-top:4px; }

        .alert { padding:13px; border-radius:8px; font-weight:700; margin-bottom:18px; }
        .success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .error   { background:#fadbd8; color:#c0392b; border:1px solid #e74c3c; }
        .empty-msg { text-align:center; padding:40px; color:var(--color-muted); border-radius:10px; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <?php include 'topbar.php'; ?>

    <div class="page-body">
        <?= $mensaje ?>
        <div class="layout">
            <div class="form-card">
                <h4>💊 Registrar Tratamiento</h4>
                <form method="POST">
                    <input type="hidden" name="accion" value="crear">
                    <div class="form-group">
                        <label>Consulta asociada</label>
                        <select name="id_consulta" required>
                            <option value="">-- Selecciona la consulta --</option>
                            <?php foreach($lista_consultas as $con): ?>
                                <option value="<?= $con['id_consulta'] ?>">
                                    <?= date('d/m/Y', strtotime($con['fecha_consulta'] ?? $con['fecha'])) ?> — <?= htmlspecialchars($con['nombre_mascota']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Descripción del tratamiento</label>
                        <textarea name="descripcion" required placeholder="Ej: Amoxicilina..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Duración (días)</label>
                        <input type="number" name="duracion_dias" min="1" max="365" required>
                    </div>
                    <button type="submit" class="btn-submit">💾 Guardar Tratamiento</button>
                </form>
            </div>

            <div>
                <div class="toolbar">
                    <div class="search-box">
                        <span>🔍</span>
                        <input type="text" id="buscador" placeholder="Buscar paciente, diagnóstico…" oninput="filtrar()">
                    </div>
                </div>

                <?php if(count($tratamientos) > 0): ?>
                    <?php foreach($tratamientos as $t):
                        $estado = estadoTrat($t['fecha_consulta'], $t['duracion_dias']);
                        $restantes = diasRestantes($t['fecha_consulta'], $t['duracion_dias']);
                        $pct = $estado === 'Activo' ? max(0, round(($t['duracion_dias'] - $restantes) / $t['duracion_dias'] * 100)) : 100;
                    ?>
                    <div class="trat-card <?= $estado === 'Completado' ? 'completado' : '' ?>"
                         data-buscar="<?= strtolower($t['nombre_mascota'].' '.$t['nombre_dueño'].' '.$t['diagnostico'].' '.$t['descripcion']) ?>">
                        <div>
                            <h5>🐾 <?= htmlspecialchars($t['nombre_mascota']) ?>
                                <span style="font-size:12px;color:var(--color-muted);font-weight:400;">/ <?= htmlspecialchars($t['nombre_dueño']) ?></span>
                            </h5>
                            <div class="trat-meta">Consulta: <?= date('d/m/Y', strtotime($t['fecha_consulta'])) ?> — <?= htmlspecialchars(substr($t['diagnostico'],0,50)) ?>…</div>
                            <div class="trat-desc"><?= htmlspecialchars($t['descripcion']) ?></div>
                            <div class="progress-bar">
                                <div class="progress-fill <?= $estado==='Completado'?'done':'' ?>" style="width:<?= $pct ?>%"></div>
                            </div>
                            <?php if($estado === 'Activo'): ?>
                                <div class="dias-left">⏳ <?= $restantes ?> día(s) restantes</div>
                            <?php else: ?>
                                <div class="dias-left" style="color:var(--color-muted);">✅ Completado</div>
                            <?php endif; ?>
                        </div>
                        <span class="badge badge-<?= strtolower($estado) ?>"><?= $estado ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-msg">💊 No hay tratamientos registrados aún.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src="huellitas-shared.js"></script>
<script>
function filtrar() {
    const t = document.getElementById('buscador').value.toLowerCase();
    document.querySelectorAll('.trat-card').forEach(c => {
        c.style.display = c.dataset.buscar.includes(t) ? '' : 'none';
    });
}
</script>
</body>
</html>