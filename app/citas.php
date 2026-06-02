<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

$sql = "SELECT c.id_cita, c.fecha_hora, c.motivo_previo, c.estado,
               m.nombre AS nombre_mascota, d.nombre AS nombre_dueño, v.nombre AS nombre_vet
        FROM cita c
        INNER JOIN mascota m ON c.id_mascota = m.id_mascota
        INNER JOIN dueño d ON m.id_dueño = d.id_dueño
        INNER JOIN veterinario v ON c.id_veterinario = v.id_veterinario
        ORDER BY c.fecha_hora ASC";
$stmt = $conexion->query($sql);
$lista_citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario    = $_SESSION['rol'];
$titulo_pagina  = 'Agenda / Citas';
$pagina_activa  = 'citas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de Citas - Huellitas</title>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <link rel="stylesheet" href="huellitas-shared.css">
    <link rel="stylesheet" href="huellitas-layout.css">
    <style>
        .page-body { padding:30px 40px; }
        .split-layout { display:grid; grid-template-columns: 1fr 1fr; gap:24px; align-items:start; }

        .calendar-card { border-radius:12px; padding: 16px 18px 18px; }
        .calendar-card h4 { font-family:'Nunito',sans-serif; font-size:16px; font-weight:800; margin-bottom:10px; }

        .fc .fc-toolbar-title { font-family:'Nunito',sans-serif; font-size:17px; font-weight:800; color: var(--color-text); }
        .fc .fc-button-primary { background-color: var(--color-accent) !important; border-color: var(--color-accent) !important; font-size:12px !important; }
        .fc .fc-button-primary:hover { filter: brightness(0.9); }
        .fc .fc-daygrid-day:hover { background-color: var(--color-hover) !important; cursor:pointer; }
        .fc .fc-daygrid-day.fc-day-selected { background-color: var(--color-hover) !important; }
        .fc-event { cursor:pointer; border-radius:4px !important; font-size:11px !important; border: none; }
        .fc-theme-standard td, .fc-theme-standard th { border-color: var(--color-border); }
        .fc .fc-col-header-cell-cushion, .fc .fc-daygrid-day-number { color: var(--color-text); }

        .table-panel { border-radius:12px; padding:20px; min-height:300px; }
        .table-panel h4 { font-family:'Nunito',sans-serif; font-size:16px; font-weight:800; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
        .table-panel h4 .day-label { background: var(--color-accent); color:#fff; padding:3px 10px; border-radius:6px; font-size:13px; }

        .toolbar { display:flex; justify-content:flex-end; margin-bottom:20px; }
        .btn-orange { background:#f39c12; color:#fff; padding:10px 18px; border-radius:8px; text-decoration:none; font-weight:700; font-size:14px; transition:all .2s; }
        .btn-orange:hover { background:#d68910; }

      table { width: 100%; border-collapse: collapse; }
        th { padding: 14px 16px; background-color: var(--table-header, #22773c); color: white; text-align: left; text-transform: uppercase; letter-spacing: .4px; font-size: 12px; }
        td { padding: 14px 16px; border-bottom: 1px solid var(--color-border); font-size: 13.5px; color: var(--color-text); }
        tr:hover td { background-color: var(--color-hover); }
        
        .badge-estado { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:700; text-transform:uppercase; }
        .badge-Pendiente  { background:#fff3cd; color:#856404; }
        .badge-Completada { background:#d4edda; color:#155724; }
        .badge-Cancelada  { background:#f8d7da; color:#721c24; }

        .empty-day { text-align:center; padding:40px; color: var(--color-muted); }
        .empty-day .emoji { font-size:40px; display:block; margin-bottom:10px; }

        @media (max-width: 900px) { .split-layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <?php include 'topbar.php'; ?>

    <div class="page-body">
        <div class="toolbar">
            <a href="nueva_cita.php" class="btn-orange">📅 + Programar Cita</a>
        </div>

        <div class="split-layout">
            <div class="calendar-card">
                <h4>📅 Calendario</h4>
                <div id="calendar"></div>
            </div>

            <div class="table-panel" id="tabla-panel">
                <h4>
                    Citas del día
                    <span class="day-label" id="day-label">Selecciona un día</span>
                </h4>
                <div id="tabla-contenido">
                    <div class="empty-day">
                        Haz clic en un día del calendario para ver sus citas.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="huellitas-shared.js"></script>
<script>
const todasLasCitas = <?php
    $citas_js = array_map(function($c) {
        return [
            'id'       => $c['id_cita'],
            'title'    => $c['nombre_mascota'] . ' — ' . $c['motivo_previo'],
            'start'    => $c['fecha_hora'],
            'fecha'    => substr($c['fecha_hora'], 0, 10),
            'hora'     => substr($c['fecha_hora'], 11, 5),
            'mascota'  => $c['nombre_mascota'],
            'dueño'    => $c['nombre_dueño'],
            'motivo'   => $c['motivo_previo'],
            'estado'   => $c['estado'],
            'vet'      => $c['nombre_vet'],
            'color'    => $c['estado'] === 'Completada' ? '#27ae60' : ($c['estado'] === 'Cancelada' ? '#e74c3c' : '#f39c12'),
        ];
    }, $lista_citas);
    echo json_encode($citas_js);
?>;

document.addEventListener('DOMContentLoaded', function () {
    const cal = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        locale: 'es',
        height: 'auto',
        events: todasLasCitas,
        headerToolbar: { left:'prev,next today', center:'title', right:'' },
        dateClick: function(info) {
            mostrarCitasDia(info.dateStr);
        },
        eventClick: function(info) {
            mostrarCitasDia(info.event.startStr.slice(0,10));
        }
    });
    cal.render();
});

function mostrarCitasDia(fecha) {
    const citasDelDia = todasLasCitas.filter(c => c.fecha === fecha);
    document.getElementById('day-label').textContent = formatFecha(fecha);

    if (citasDelDia.length === 0) {
        document.getElementById('tabla-contenido').innerHTML = `
            <div class="empty-day">
                <span class="emoji">📭</span>
                No hay citas para este día.
            </div>`;
        return;
    }

    let html = `<table>
        <thead><tr>
            <th>Hora</th><th>Paciente</th><th>Dueño</th><th>Motivo</th><th>Veterinario</th><th>Estado</th>
        </tr></thead><tbody>`;

    citasDelDia.forEach(c => {
        html += `<tr>
            <td><strong>${c.hora}</strong></td>
            <td>${escHtml(c.mascota)}</td>
            <td>${escHtml(c.dueño)}</td>
            <td>${escHtml(c.motivo)}</td>
            <td>${escHtml(c.vet)}</td>
            <td><span class="badge-estado badge-${c.estado}">${c.estado}</span></td>
        </tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('tabla-contenido').innerHTML = html;
}

function formatFecha(str) {
    const [y,m,d] = str.split('-');
    const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    return `${d} ${meses[parseInt(m)-1]} ${y}`;
}
function escHtml(s) {
    const d = document.createElement('div'); d.textContent = s; return d.innerHTML;
}
</script>
</body>
</html>