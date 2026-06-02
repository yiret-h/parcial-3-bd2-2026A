<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

$lista_consultas = [];
$error_db = "";

try {
    $sql = "SELECT c.id_consulta, c.fecha, c.motivo, c.diagnostico, c.observaciones,
                   m.nombre AS nombre_mascota, d.nombre AS nombre_dueño, v.nombre AS nombre_veterinario
            FROM consulta c
            INNER JOIN mascota m ON c.id_mascota = m.id_mascota
            INNER JOIN dueño d ON m.id_dueño = d.id_dueño
            INNER JOIN veterinario v ON c.id_veterinario = v.id_veterinario
            ORDER BY c.fecha DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $lista_consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_db = "Error al cargar las consultas: " . $e->getMessage();
}

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario    = $_SESSION['rol'];
$titulo_pagina  = 'Consultas Médicas';
$pagina_activa  = 'consultas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultas Médicas - Huellitas</title>
    <link rel="stylesheet" href="huellitas-shared.css">
    <link rel="stylesheet" href="huellitas-layout.css">
    <style>
        .page-body { padding:30px 40px; }

        .toolbar { display:flex; align-items:center; gap:12px; margin-bottom:20px; flex-wrap:wrap; }
        .search-box { display:flex; align-items:center; border-radius:8px; padding:0 14px; gap:8px; flex:1; min-width:240px; max-width:420px; border: 2px solid var(--color-border); }
        .search-box input { border:none; outline:none; padding:10px 0; font-size:14px; width:100%; background:transparent; color: var(--color-text); }
        
        .btn-primary { background:var(--color-accent); color:#fff; padding:10px 18px; border-radius:8px; text-decoration:none; font-weight:700; font-size:14px; }
        .btn-primary:hover { filter: brightness(0.9); }

        .consultas-lista { display:flex; flex-direction:column; gap:14px; }

        .fecha-grupo h5 { font-family:'Nunito',sans-serif; font-size:13px; font-weight:800; color:var(--color-muted); text-transform:uppercase; letter-spacing:.6px; padding: 8px 0 6px; border-bottom: 1px solid var(--color-border); margin-bottom: 10px; }

        .consulta-card { border-radius:10px; padding:18px 22px; display:grid; grid-template-columns: 90px 1fr 1fr 1fr 1fr; gap:12px; align-items:center; border-left: 4px solid #3498db; transition: transform .15s; }
        .consulta-card:hover { transform:translateX(3px); }
        
        .card-hora { font-family:'Nunito',sans-serif; font-size:22px; font-weight:800; color:#3498db; line-height:1; }
        .card-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:3px; }
        .card-motivo { font-size:13px; margin-top:2px; }
        .card-diag { font-size:13px; font-weight:600; color: #4caf6a; }

        .alerta-error { background:#fadbd8; color:#c0392b; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #e74c3c; font-weight:700; }
        .empty-state { text-align:center; padding:60px; color:var(--color-muted); border-radius:10px; }
        .empty-state .emoji { font-size:48px; display:block; margin-bottom:12px; }

        .resultado-count { font-size:13px; color:var(--color-muted); margin-left:auto; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <?php include 'topbar.php'; ?>

    <div class="page-body">
        <?php if(!empty($error_db)): ?>
            <div class="alerta-error"><?= $error_db ?></div>
        <?php endif; ?>

        <div class="toolbar">
            <div class="search-box">
                <span>🔍</span>
                <input type="text" id="buscador" placeholder="Buscar por paciente, dueño, diagnóstico…" oninput="filtrar()">
            </div>
            <span class="resultado-count" id="contador"><?= count($lista_consultas) ?> consulta(s)</span>
            <a href="nueva_consulta.php" class="btn-primary">🩺 + Nueva Consulta</a>
        </div>

        <?php if (count($lista_consultas) > 0): ?>
        <div class="consultas-lista" id="lista">
            <?php
            $fecha_actual = "";
            $meses = ['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio',
                      '07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];
            foreach ($lista_consultas as $c):
                $fecha_partes = explode('-', $c['fecha']);
                $label_fecha  = $fecha_partes[2] . ' de ' . ($meses[$fecha_partes[1]] ?? $fecha_partes[1]) . ' de ' . $fecha_partes[0];
                if ($fecha_actual !== $c['fecha']):
                    if ($fecha_actual !== "") echo '</div>'; 
                    $fecha_actual = $c['fecha'];
            ?>
                <div class="fecha-grupo">
                    <h5>📅 <?= $label_fecha ?></h5>
            <?php endif; ?>

            <div class="consulta-card"
                 data-buscar="<?= strtolower(htmlspecialchars($c['nombre_mascota'].' '.$c['nombre_dueño'].' '.$c['motivo'].' '.$c['diagnostico'].' '.$c['nombre_veterinario'])) ?>">
                <div>
                    <div class="card-hora"><?= date('d/m', strtotime($c['fecha'])) ?></div>
                    <div class="card-motivo"><?= $c['fecha'] ?></div>
                </div>
                <div>
                    <div class="card-label">Paciente</div>
                    <div class="card-val">🐾 <?= htmlspecialchars($c['nombre_mascota']) ?></div>
                    <div class="card-motivo">Dueño: <?= htmlspecialchars($c['nombre_dueño']) ?></div>
                </div>
                <div>
                    <div class="card-label">Motivo</div>
                    <div class="card-val"><?= htmlspecialchars(substr($c['motivo'],0,60)) ?><?= strlen($c['motivo'])>60?'…':'' ?></div>
                </div>
                <div>
                    <div class="card-label">Diagnóstico</div>
                    <div class="card-diag"><?= htmlspecialchars(substr($c['diagnostico'],0,60)) ?><?= strlen($c['diagnostico'])>60?'…':'' ?></div>
                </div>
                <div>
                    <div class="card-label">Médico</div>
                    <div class="card-val">👨‍⚕️ <?= htmlspecialchars($c['nombre_veterinario']) ?></div>
                </div>
            </div>

            <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <span class="emoji">🩺</span>
            No hay consultas registradas aún. ¡Registra la primera!
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="huellitas-shared.js"></script>
<script>
function filtrar() {
    const texto = document.getElementById('buscador').value.toLowerCase().trim();
    const tarjetas = document.querySelectorAll('.consulta-card');
    let visible = 0;
    tarjetas.forEach(t => {
        const match = t.dataset.buscar.includes(texto);
        t.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.querySelectorAll('.fecha-grupo').forEach(g => {
        const hayVisible = [...g.querySelectorAll('.consulta-card')].some(c => c.style.display !== 'none');
        g.style.display = hayVisible ? '' : 'none';
    });
    document.getElementById('contador').textContent = visible + ' consulta(s)';
}
</script>
</body>
</html>