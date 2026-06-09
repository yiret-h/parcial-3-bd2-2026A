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
    <link rel="stylesheet" href="huellitas-shared.css?v=4">
    <link rel="stylesheet" href="huellitas-layout.css?v=4">
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
                <span>&#128269;</span>
                <input type="text" id="buscador" placeholder="Buscar por paciente, dueño, diagnóstico…" oninput="filtrar()">
            </div>
            <span class="resultado-count" id="contador"><?= count($lista_consultas) ?> consulta(s)</span>
            <a href="nueva_consulta.php" class="btn-primary">&#129658; + Nueva Consulta</a>
        </div>

        <?php if (count($lista_consultas) > 0): ?>
        <div class="consultas-lista" id="lista">
            <?php
            $fecha_actual = "";
            $meses = ['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio',
                      '07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];
            foreach ($lista_consultas as $index => $c):
                $fecha_partes = explode('-', $c['fecha']);
                $label_fecha  = $fecha_partes[2] . ' de ' . ($meses[$fecha_partes[1]] ?? $fecha_partes[1]) . ' de ' . $fecha_partes[0];
                if ($fecha_actual !== $c['fecha']):
                    if ($fecha_actual !== "") echo '</div>'; 
                    $fecha_actual = $c['fecha'];
            ?>
                <div class="fecha-grupo">
                    <h5>&#128197; <?= $label_fecha ?></h5>
            <?php endif; ?>

            <div class="consulta-card" onclick="abrirModalConsulta(<?= $index ?>)"
                 data-buscar="<?= strtolower(htmlspecialchars($c['nombre_mascota'].' '.$c['nombre_dueño'].' '.$c['motivo'].' '.$c['diagnostico'].' '.$c['nombre_veterinario'])) ?>">
                <div>
                    <div class="card-hora"><?= date('d/m', strtotime($c['fecha'])) ?></div>
                    <div class="card-motivo"><?= $c['fecha'] ?></div>
                </div>
                <div>
                    <div class="card-label">Paciente</div>
                    <div class="card-val">&#128062; <?= htmlspecialchars($c['nombre_mascota']) ?></div>
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
            <span class="emoji">&#129658;</span>
            No hay consultas registradas aún. ¡Registra la primera!
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Detalle de Consulta -->
<div id="modalConsulta" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,20,30,0.6); backdrop-filter: blur(4px); z-index:9999; align-items:center; justify-content:center;" onclick="cerrarModalConsultaBg(event)">
    <div style="background: var(--bg-card, #ffffff); width: 90%; max-width: 550px; border-radius: 14px; box-shadow: 0 15px 40px rgba(0,0,0,0.3); overflow: hidden; animation: modalIn 0.3s ease;">
        <div style="background: linear-gradient(135deg, #0c83a7 0%, #34aed4 100%); padding: 20px 24px; color: white; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-family: 'Nunito', sans-serif; font-size: 20px; display: flex; align-items: center; gap: 10px;">🩺 Detalles de la Consulta</h3>
            <button onclick="cerrarModalConsulta()" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; opacity: 0.8; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">✕</button>
        </div>
        <div style="padding: 24px; display: flex; flex-direction: column; gap: 16px; max-height: 70vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-border); padding-bottom: 12px;">
                <div>
                    <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Fecha</span>
                    <div id="modal-fecha" style="font-weight: 700; color: #3498db; font-size: 16px;"></div>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Veterinario</span>
                    <div id="modal-vet" style="font-weight: 600; color: var(--color-text);"></div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; border-bottom: 1px solid var(--color-border); padding-bottom: 12px;">
                <div>
                    <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Paciente</span>
                    <div id="modal-paciente" style="font-weight: 600; color: var(--color-text); font-size: 15px;">🐾 <span></span></div>
                </div>
                <div>
                    <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Dueño</span>
                    <div id="modal-dueno" style="font-weight: 600; color: var(--color-text); font-size: 15px;"></div>
                </div>
            </div>
            <div>
                <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Motivo</span>
                <div id="modal-motivo" style="font-size: 15px; margin-top: 4px; color: var(--color-text);"></div>
            </div>
            <div>
                <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Diagnóstico</span>
                <div id="modal-diagnostico" style="font-size: 15px; margin-top: 4px; color: #4caf6a; font-weight: 700;"></div>
            </div>
            <div style="background: rgba(12,131,167,0.05); padding: 16px; border-radius: 10px; border: 1px solid rgba(12,131,167,0.15); margin-top: 8px;">
                <span style="font-size: 11px; color: #0c83a7; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Observaciones</span>
                <div id="modal-obs" style="font-size: 14px; margin-top: 6px; color: var(--color-text); line-height: 1.5;"></div>
            </div>
        </div>
        <div style="padding: 16px 24px; text-align: right; border-top: 1px solid var(--color-border); background: rgba(0,0,0,0.02);">
            <button onclick="cerrarModalConsulta()" style="background: #0c83a7; color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 14px; transition: background 0.2s;" onmouseover="this.style.background='#096885'" onmouseout="this.style.background='#0c83a7'">Cerrar</button>
        </div>
    </div>
</div>

<style>
@keyframes modalIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.consulta-card { cursor: pointer; }
</style>

<script src="huellitas-shared.js"></script>
<script>
const datosConsultas = <?= json_encode($lista_consultas) ?>;

function abrirModalConsulta(index) {
    const c = datosConsultas[index];
    if (!c) return;

    // Formatear la fecha a dd/mm/yyyy
    const partesFecha = c.fecha.split('-');
    const fechaFormat = partesFecha[2] + '/' + partesFecha[1] + '/' + partesFecha[0];

    document.getElementById('modal-fecha').textContent = fechaFormat;
    document.getElementById('modal-vet').textContent = '👨‍⚕️ ' + c.nombre_veterinario;
    document.querySelector('#modal-paciente span').textContent = c.nombre_mascota;
    document.getElementById('modal-dueno').textContent = c.nombre_dueño;
    document.getElementById('modal-motivo').textContent = c.motivo;
    document.getElementById('modal-diagnostico').textContent = c.diagnostico;
    document.getElementById('modal-obs').textContent = c.observaciones || 'Sin observaciones adicionales.';

    document.getElementById('modalConsulta').style.display = 'flex';
}

function cerrarModalConsulta() {
    document.getElementById('modalConsulta').style.display = 'none';
}

function cerrarModalConsultaBg(e) {
    if (e.target.id === 'modalConsulta') {
        cerrarModalConsulta();
    }
}

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