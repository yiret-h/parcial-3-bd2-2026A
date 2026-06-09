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
    <link rel="stylesheet" href="huellitas-shared.css?v=4">
    <link rel="stylesheet" href="huellitas-layout.css?v=4">
    <style>
        .page-body { padding:30px 40px; }
        .split-layout { display:grid; grid-template-columns: 1fr 1fr; gap:24px; align-items:start; }

        .calendar-card { border-radius:16px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); background: var(--bg-card); border-top: 4px solid #0c83a7;}
        .calendar-card h4 { font-family:'Nunito',sans-serif; font-size:18px; font-weight:800; margin-bottom:15px; color: #0c83a7; }

        /* Quitar bordes de la tabla base de FullCalendar */
        .fc-theme-standard td, .fc-theme-standard th, .fc-scrollgrid { border: none !important; }
        
        /* Cabeceras de los días (Lun, Mar, Mie...) */
        .fc .fc-col-header-cell { padding-bottom: 15px; }
        .fc .fc-col-header-cell-cushion { 
            color: #0c83a7; font-weight: 800; text-transform: uppercase; font-size: 13px; 
            background: rgba(12, 131, 167, 0.1); padding: 5px 15px; border-radius: 20px;
        }

        /* Marcos de los días (Cuadros redondeados) */
        .fc .fc-daygrid-day-frame {
            border: 1px solid var(--color-border);
            border-radius: 12px;
            margin: 4px; /* Separación entre cuadros */
            min-height: 80px;
            transition: all 0.2s ease;
            background: var(--bg-main);
        }
        .fc .fc-daygrid-day:hover .fc-daygrid-day-frame { 
            background-color: var(--color-hover); 
            border-color: #0c83a7;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(12, 131, 167, 0.15);
            cursor: pointer;
        }
        .fc .fc-day-today .fc-daygrid-day-frame {
            background: rgba(12, 131, 167, 0.05);
            border: 2px solid #0c83a7;
        }
        .fc .fc-daygrid-day-number { color: var(--color-text); font-weight: 800; padding: 8px; }
        
        /* Eventos (puntitos o pastillas) */
        .fc-event { 
            cursor:pointer; border-radius:10px !important; font-size:11px !important; 
            border: none; padding: 3px 6px; margin: 2px 4px !important;
            font-weight: 700; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: white !important; text-align: center;
        }
        .fc .fc-daygrid-event-dot { display: none; } /* Ocultar el puntito nativo */

        /* Barra superior y botones */
        .fc .fc-toolbar-title { font-family:'Nunito',sans-serif; font-size:22px; font-weight:800; color: #0c83a7; text-transform: capitalize; }
        .fc .fc-button-primary { 
            background-color: #0c83a7 !important; border: none !important; 
            font-size:13px !important; font-weight: 800 !important; border-radius: 8px !important; padding: 8px 16px !important;
            box-shadow: 0 4px 6px rgba(12, 131, 167, 0.2); text-transform: uppercase;
        }
        .fc .fc-button-primary:hover { background-color: #085e79 !important; transform: translateY(-1px); }
        .fc .fc-button-active { background-color: #064a5f !important; }

        /* Ajustes modo oscuro para el calendario */
        body.dark-mode .fc .fc-col-header-cell-cushion { color: #48dbfb; background: rgba(72, 219, 251, 0.15); }
        body.dark-mode .fc .fc-daygrid-day-frame { border-color: #1a4a5a; background: #0b1f26; }
        body.dark-mode .fc .fc-daygrid-day:hover .fc-daygrid-day-frame { border-color: #48dbfb; background: #0c3b4a; }
        body.dark-mode .fc .fc-day-today .fc-daygrid-day-frame { border-color: #48dbfb; background: rgba(72, 219, 251, 0.1); }
        body.dark-mode .fc .fc-toolbar-title { color: #48dbfb; }

        .table-panel { border-radius:16px; padding:25px; min-height:300px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); background: var(--bg-card); border-top: 4px solid #f39c12; overflow-x: auto;}
        .table-panel h4 { font-family:'Nunito',sans-serif; font-size:18px; font-weight:800; margin-bottom:18px; display:flex; align-items:center; gap:8px; color: #f39c12; }
        .table-panel h4 .day-label { background: #fdebd0; color:#d35400; padding:4px 12px; border-radius:20px; font-size:13px; font-weight: 800; }
        body.dark-mode .table-panel h4 .day-label { background: #4a2a11; color: #f39c12; }

        .toolbar { display:flex; justify-content:flex-end; margin-bottom:20px; }
        .btn-orange { background:#f39c12; color:#fff; padding:12px 20px; border-radius:8px; text-decoration:none; font-weight:800; font-size:14px; transition:all .2s; box-shadow: 0 4px 10px rgba(243, 156, 18, 0.3); }
        .btn-orange:hover { background:#d68910; transform: translateY(-2px); }

        .table-panel table { width: 100%; border-collapse: collapse; min-width: 400px;}
        .table-panel th { padding: 14px 16px; background-color: #f39c12; color: white; text-align: left; text-transform: uppercase; letter-spacing: .4px; font-size: 12px; border-radius: 4px; }
        .table-panel td { padding: 14px 16px; border-bottom: 1px solid var(--color-border); font-size: 13.5px; color: var(--color-text); }
        .table-panel tr:hover td { background-color: var(--color-hover); }
        
        .badge-estado { display:inline-block; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:800; text-transform:uppercase; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .badge-Pendiente  { background:#fff3cd; color:#856404; }
        .badge-Completada { background:#d4edda; color:#155724; }
        .badge-Cancelada  { background:#f8d7da; color:#721c24; }

        .empty-day { text-align:center; padding:50px 20px; color: var(--color-muted); font-size: 15px; font-weight: 600; }
        .empty-day .emoji { font-size:50px; display:block; margin-bottom:15px; opacity: 0.7; }

        .split-layout { display:grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap:24px; align-items:start; }
        @media (max-width: 900px) { .split-layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <?php include 'topbar.php'; ?>

    <div class="page-body">
        <div class="toolbar">
            <a href="nueva_cita.php" class="btn-orange">&#128197; + Programar Cita</a>
        </div>

        <div class="split-layout">
            <div class="calendar-card">
                <h4>&#128197; Calendario</h4>
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
            'hora'     => date("g:i A", strtotime($c['fecha_hora'])),
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
        eventDisplay: 'block', /* ¡Esta es la clave para que las citas con hora tengan fondo sólido! */
        eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short', omitZeroMinute: false }, /* Formato 12 horas (AM/PM) */
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