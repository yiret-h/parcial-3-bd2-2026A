<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

// ── Estadísticas generales ──────────────────────────────────────────────────
$stats = [];

try {
    // Total dueños
    $stats['total_dueños'] = (int)$conexion->query("SELECT COUNT(*) FROM dueño")->fetchColumn();

    // Total mascotas
    $stats['total_mascotas'] = (int)$conexion->query("SELECT COUNT(*) FROM mascota")->fetchColumn();

    // Total consultas
    $stats['total_consultas'] = (int)$conexion->query("SELECT COUNT(*) FROM consulta")->fetchColumn();

    // Citas pendientes
    $stats['citas_pendientes'] = (int)$conexion->query("SELECT COUNT(*) FROM cita WHERE estado='Pendiente'")->fetchColumn();

    // Citas este mes
    $stats['citas_mes'] = (int)$conexion->query(
        "SELECT COUNT(*) FROM cita WHERE MONTH(fecha_hora)=MONTH(NOW()) AND YEAR(fecha_hora)=YEAR(NOW())"
    )->fetchColumn();

    // Consultas este mes
    $stats['consultas_mes'] = (int)$conexion->query(
        "SELECT COUNT(*) FROM consulta WHERE MONTH(fecha)=MONTH(NOW()) AND YEAR(fecha)=YEAR(NOW())"
    )->fetchColumn();

    // Vacunas aplicadas
    $stats['vacunas'] = (int)$conexion->query("SELECT COUNT(*) FROM carnet_vacunacion")->fetchColumn();

    // Tratamientos activos
    $stats['tratamientos'] = (int)$conexion->query("SELECT COUNT(*) FROM tratamiento")->fetchColumn();

} catch(PDOException $e) { $stats = array_fill_keys(['total_dueños','total_mascotas','total_consultas','citas_pendientes','citas_mes','consultas_mes','vacunas','tratamientos'],0); }

// ── Consultas por mes (últimos 6 meses) ────────────────────────────────────
$consultas_por_mes = [];
try {
    $rows = $conexion->query(
        "SELECT DATE_FORMAT(fecha,'%Y-%m') AS mes, COUNT(*) AS total
         FROM consulta
         WHERE fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
         GROUP BY mes ORDER BY mes ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $r) $consultas_por_mes[$r['mes']] = (int)$r['total'];
} catch(PDOException $e) {}

// ── Citas por estado ────────────────────────────────────────────────────────
$citas_estado = ['Pendiente'=>0,'Completada'=>0,'Cancelada'=>0];
try {
    $rows = $conexion->query("SELECT estado, COUNT(*) AS total FROM cita GROUP BY estado")->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $r) $citas_estado[$r['estado']] = (int)$r['total'];
} catch(PDOException $e) {}

// ── Mascotas por especie ────────────────────────────────────────────────────
$mascotas_especie = [];
try {
    $rows = $conexion->query(
        "SELECT e.nombre AS especie, COUNT(*) AS total
         FROM mascota m
         INNER JOIN raza r ON m.id_raza=r.id_raza
         INNER JOIN especie e ON r.id_especie=e.id_especie
         GROUP BY especie ORDER BY total DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $r) $mascotas_especie[$r['especie']] = (int)$r['total'];
} catch(PDOException $e) {}

// ── Top veterinarios por consultas ─────────────────────────────────────────
$top_vets = [];
try {
    $top_vets = $conexion->query(
        "SELECT v.nombre, COUNT(*) AS total
         FROM consulta c INNER JOIN veterinario v ON c.id_veterinario=v.id_veterinario
         GROUP BY v.id_veterinario ORDER BY total DESC LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {}

// ── Próximas vacunas vencidas ──────────────────────────────────────────────
$vacunas_prox = [];
try {
    $vacunas_prox = $conexion->query(
        "SELECT cv.proxima_dosis, v.nombre AS nombre_vacuna, m.nombre AS nombre_mascota, d.nombre AS nombre_dueño
         FROM carnet_vacunacion cv
         INNER JOIN vacuna v  ON cv.id_vacuna=v.id_vacuna
         INNER JOIN mascota m ON cv.id_mascota=m.id_mascota
         INNER JOIN dueño d   ON m.id_dueño=d.id_dueño
         WHERE cv.proxima_dosis IS NOT NULL AND cv.proxima_dosis >= CURDATE()
         ORDER BY cv.proxima_dosis ASC LIMIT 8"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {}

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario    = $_SESSION['rol'];
$titulo_pagina  = 'Reportes y Estadísticas';
$pagina_activa  = 'reportes';

// Preparar arrays JS
$meses_labels = array_keys($consultas_por_mes);
$meses_data   = array_values($consultas_por_mes);
// Formatear labels a nombres amigables
$meses_nombres = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
$meses_labels_fmt = array_map(function($m) use($meses_nombres) {
    [$y,$mo] = explode('-',$m);
    return ($meses_nombres[$mo] ?? $mo) . ' ' . substr($y,2);
}, $meses_labels);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Huellitas</title>
    <link rel="stylesheet" href="huellitas-shared.css?v=4">
    <link rel="stylesheet" href="huellitas-layout.css?v=4">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* ── KPI cards ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .kpi-card {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 20px 18px;
            box-shadow: var(--shadow-card);
            border-top: 4px solid #22773c;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .kpi-card.orange { border-top-color: #f39c12; }
        .kpi-card.blue   { border-top-color: #3498db; }
        .kpi-card.purple { border-top-color: #9b59b6; }
        .kpi-card.teal   { border-top-color: #1abc9c; }
        .kpi-card.red    { border-top-color: #e74c3c; }

        .kpi-icon  { font-size: 26px; line-height: 1; margin-bottom: 6px; }
        .kpi-value {
            font-family: 'Nunito', sans-serif;
            font-size: 32px; font-weight: 800;
            color: var(--color-text);
            line-height: 1;
        }
        .kpi-label { font-size: 12px; color: var(--color-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
        .kpi-sub   { font-size: 11px; color: var(--color-accent); font-weight: 700; margin-top: 4px; }

        /* ── Charts grid ── */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        .charts-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .chart-card {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 20px 22px;
            box-shadow: var(--shadow-card);
        }
        .chart-card h4 {
            font-family: 'Nunito', sans-serif;
            font-size: 15px; font-weight: 800;
            color: var(--color-text);
            margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }
        .chart-wrap { position: relative; height: 220px; }

        /* ── Tablas de reportes ── */
        .section-title {
            font-family: 'Nunito', sans-serif;
            font-size: 12px; font-weight: 800;
            color: var(--color-muted);
            text-transform: uppercase; letter-spacing: 1px;
            margin-bottom: 14px; margin-top: 8px;
        }

        .report-table-card {
            background: var(--bg-card);
            border-radius: 12px;
            box-shadow: var(--shadow-card);
            overflow: hidden;
            margin-bottom: 22px;
        }
        .report-table-card h4 {
            font-family: 'Nunito', sans-serif;
            font-size: 15px; font-weight: 800;
            color: var(--color-text);
            padding: 16px 20px 12px;
            border-bottom: 1px solid var(--color-border);
            display: flex; align-items: center; gap: 8px;
        }
        table { width:100%; border-collapse:collapse; }
        th { background: var(--table-header,#22773c); color:white; text-align:left; padding:11px 16px; font-size:11.5px; text-transform:uppercase; letter-spacing:.4px; }
        td { padding:12px 16px; border-bottom:1px solid var(--color-border); color:var(--color-text); font-size:13.5px; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:var(--color-hover); }

        .bar-inline {
            display: flex; align-items: center; gap: 10px;
        }
        .bar-track {
            flex: 1; height: 8px; background: var(--color-border);
            border-radius: 4px; overflow: hidden;
        }
        .bar-fill { height: 100%; background: linear-gradient(90deg,#22773c,#3dba4e); border-radius: 4px; }
        .bar-count { font-weight: 800; font-size: 13px; color: var(--color-text); min-width: 24px; text-align: right; }

        .fecha-prox { color:#d35400; font-weight:700; background:#fdebd0; padding:3px 8px; border-radius:5px; font-size:12px; }
        .fecha-ok   { color:#155724; font-weight:700; background:#d4edda; padding:3px 8px; border-radius:5px; font-size:12px; }

        .empty-state { text-align:center; padding:30px; color:var(--color-muted); font-size:13px; }

        .toolbar-right { display:flex; justify-content:flex-end; margin-bottom:18px; }

        @media (max-width:960px) {
            .charts-grid, .charts-grid-3 { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <?php include 'topbar.php'; ?>

    <div class="page-body">

        <!-- Botón imprimir -->
        <div class="toolbar-right">
            <button onclick="window.print()" class="btn btn-orange" style="background:#34495e;">🖨️ Imprimir Reporte</button>
        </div>

        <!-- ── KPIs ── -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon">&#128100;</div>
                <div class="kpi-value"><?= $stats['total_dueños'] ?></div>
                <div class="kpi-label">Clientes registrados</div>
            </div>
            <div class="kpi-card teal">
                <div class="kpi-icon">&#128062;</div>
                <div class="kpi-value"><?= $stats['total_mascotas'] ?></div>
                <div class="kpi-label">Mascotas registradas</div>
            </div>
            <div class="kpi-card blue">
                <div class="kpi-icon">&#129658;</div>
                <div class="kpi-value"><?= $stats['total_consultas'] ?></div>
                <div class="kpi-label">Consultas totales</div>
                <div class="kpi-sub">+<?= $stats['consultas_mes'] ?> este mes</div>
            </div>
            <div class="kpi-card orange">
                <div class="kpi-icon">&#128197;</div>
                <div class="kpi-value"><?= $stats['citas_pendientes'] ?></div>
                <div class="kpi-label">Citas pendientes</div>
                <div class="kpi-sub"><?= $stats['citas_mes'] ?> agendadas este mes</div>
            </div>
            <div class="kpi-card purple">
                <div class="kpi-icon">&#128137;</div>
                <div class="kpi-value"><?= $stats['vacunas'] ?></div>
                <div class="kpi-label">Vacunas aplicadas</div>
            </div>
            <div class="kpi-card red">
                <div class="kpi-icon">&#128138;</div>
                <div class="kpi-value"><?= $stats['tratamientos'] ?></div>
                <div class="kpi-label">Tratamientos registrados</div>
            </div>
        </div>

        <!-- ── Gráficas fila 1 ── -->
        <div class="charts-grid">
            <!-- Consultas por mes -->
            <div class="chart-card">
                <h4>📈 Consultas por Mes (últimos 6 meses)</h4>
                <div class="chart-wrap">
                    <canvas id="chartConsultas"></canvas>
                </div>
            </div>

            <!-- Citas por estado -->
            <div class="chart-card">
                <h4>&#128202; Estado de Citas</h4>
                <div class="chart-wrap">
                    <canvas id="chartEstados"></canvas>
                </div>
            </div>
        </div>

        <!-- ── Gráficas fila 2 ── -->
        <div class="charts-grid">
            <!-- Mascotas por especie -->
            <div class="chart-card">
                <h4>&#128062; Mascotas por Especie</h4>
                <div class="chart-wrap">
                    <canvas id="chartEspecies"></canvas>
                </div>
            </div>

            <!-- Top veterinarios -->
            <div class="chart-card">
                <h4>👨‍⚕️ Top Veterinarios por Consultas</h4>
                <?php if(count($top_vets) > 0):
                    $max_vet = max(array_column($top_vets,'total'));
                ?>
                <div style="margin-top:8px;">
                    <?php foreach($top_vets as $v): ?>
                    <div style="margin-bottom:14px;">
                        <div style="font-size:13px;font-weight:700;color:var(--color-text);margin-bottom:5px;">
                            <?= htmlspecialchars($v['nombre']) ?>
                        </div>
                        <div class="bar-inline">
                            <div class="bar-track">
                                <div class="bar-fill" style="width:<?= $max_vet>0?round($v['total']/$max_vet*100):0 ?>%"></div>
                            </div>
                            <div class="bar-count"><?= $v['total'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <div class="empty-state">No hay datos aún.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Tabla: próximas dosis de vacunas ── -->
        <p class="section-title">&#128203; Alertas y Seguimiento</p>
        <div class="report-table-card">
            <h4>&#128137; Próximas Dosis de Vacunas</h4>
            <?php if(count($vacunas_prox) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Dueño</th>
                        <th>Vacuna</th>
                        <th>Próxima Dosis</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($vacunas_prox as $vac):
                    $dias = (new DateTime())->diff(new DateTime($vac['proxima_dosis']))->days;
                    $urgente = $dias <= 14;
                ?>
                <tr>
                    <td><strong>&#128062; <?= htmlspecialchars($vac['nombre_mascota']) ?></strong></td>
                    <td><?= htmlspecialchars($vac['nombre_dueño']) ?></td>
                    <td><?= htmlspecialchars($vac['nombre_vacuna']) ?></td>
                    <td><?= date('d/m/Y', strtotime($vac['proxima_dosis'])) ?></td>
                    <td>
                        <?php if($urgente): ?>
                            <span class="fecha-prox">&#9888; En <?= $dias ?> día(s)</span>
                        <?php else: ?>
                            <span class="fecha-ok">&#9989; En <?= $dias ?> días</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state">No hay dosis próximas registradas.</div>
            <?php endif; ?>
        </div>

    </div><!-- /page-body -->
</div><!-- /main-content -->

<script src="huellitas-shared.js"></script>
<script>
// ── Colores adaptativos (dark mode) ─────────────────────────────────────────
function isDark() { return document.body.classList.contains('dark-mode'); }
function gridColor() { return isDark() ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.07)'; }
function textColor()  { return isDark() ? '#d4edda' : '#2c3e50'; }

// ── Chart: Consultas por mes ─────────────────────────────────────────────────
const ctxC = document.getElementById('chartConsultas').getContext('2d');
const chartConsultas = new Chart(ctxC, {
    type: 'bar',
    data: {
        labels: <?= json_encode($meses_labels_fmt) ?>,
        datasets: [{
            label: 'Consultas',
            data: <?= json_encode($meses_data) ?>,
            backgroundColor: 'rgba(34,119,60,0.75)',
            borderColor: '#22773c',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: gridColor() }, ticks: { color: textColor(), font:{size:11} } },
            y: { grid: { color: gridColor() }, ticks: { color: textColor(), stepSize: 1 }, beginAtZero: true }
        }
    }
});

// ── Chart: Citas por estado ──────────────────────────────────────────────────
const ctxE = document.getElementById('chartEstados').getContext('2d');
const chartEstados = new Chart(ctxE, {
    type: 'doughnut',
    data: {
        labels: ['Pendientes','Completadas','Canceladas'],
        datasets: [{
            data: [
                <?= $citas_estado['Pendiente'] ?>,
                <?= $citas_estado['Completada'] ?>,
                <?= $citas_estado['Cancelada'] ?>
            ],
            backgroundColor: ['#f39c12','#27ae60','#e74c3c'],
            borderWidth: 0,
            hoverOffset: 6
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { color: textColor(), font:{size:12}, padding:14 }
            }
        },
        cutout: '62%'
    }
});

// ── Chart: Mascotas por especie ──────────────────────────────────────────────
const ctxEsp = document.getElementById('chartEspecies').getContext('2d');
const coloresPastel = ['#22773c','#3498db','#f39c12','#9b59b6','#1abc9c','#e74c3c'];
const chartEspecies = new Chart(ctxEsp, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($mascotas_especie)) ?>,
        datasets: [{
            label: 'Mascotas',
            data: <?= json_encode(array_values($mascotas_especie)) ?>,
            backgroundColor: coloresPastel,
            borderRadius: 6,
            borderWidth: 0,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: gridColor() }, ticks: { color: textColor(), stepSize:1 }, beginAtZero: true },
            y: { grid: { display: false }, ticks: { color: textColor(), font:{size:12} } }
        }
    }
});

// Actualizar colores al cambiar modo oscuro
document.getElementById('darkModeToggle')?.addEventListener('click', () => {
    setTimeout(() => {
        chartConsultas.options.scales.x.grid.color = gridColor();
        chartConsultas.options.scales.y.grid.color = gridColor();
        chartConsultas.options.scales.x.ticks.color = textColor();
        chartConsultas.options.scales.y.ticks.color = textColor();
        chartConsultas.update();

        chartEspecies.options.scales.x.grid.color = gridColor();
        chartEspecies.options.scales.x.ticks.color = textColor();
        chartEspecies.options.scales.y.ticks.color = textColor();
        chartEspecies.update();

        chartEstados.options.plugins.legend.labels.color = textColor();
        chartEstados.update();
    }, 350);
});
</script>
</body>
</html>