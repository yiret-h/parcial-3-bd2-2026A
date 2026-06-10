<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario    = $_SESSION['rol'];
$titulo_pagina  = 'Panel de Control';
$pagina_activa  = 'dashboard';

// Consultar próximas citas (Próximos 7 días)
$citas_proximas = [];
try {
    $sql_citas = "SELECT c.fecha_hora, m.nombre AS mascota, d.nombre AS dueño, c.motivo_previo 
                  FROM cita c
                  INNER JOIN mascota m ON c.id_mascota = m.id_mascota
                  INNER JOIN dueño d ON m.id_dueño = d.id_dueño
                  WHERE DATE(c.fecha_hora) >= CURDATE() AND DATE(c.fecha_hora) <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND c.estado = 'Pendiente'
                  ORDER BY c.fecha_hora ASC";
    $stmt = $conexion->query($sql_citas);
    $citas_proximas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {}

// Consultar próximas vacunas (Próximos 30 días)
$vacunas_proximas = [];
try {
    $sql_vacunas = "SELECT cv.proxima_dosis, m.nombre AS mascota, d.nombre AS dueño, v.nombre AS vacuna
                    FROM carnet_vacunacion cv
                    INNER JOIN mascota m ON cv.id_mascota = m.id_mascota
                    INNER JOIN dueño d ON m.id_dueño = d.id_dueño
                    INNER JOIN vacuna v ON cv.id_vacuna = v.id_vacuna
                    WHERE cv.proxima_dosis >= CURDATE() AND cv.proxima_dosis <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                    ORDER BY cv.proxima_dosis ASC";
    $stmt = $conexion->query($sql_vacunas);
    $vacunas_proximas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {}

// Contar alertas urgentes (hoy o mañana)
$alertas_hoy = 0;
$alertas_manana = 0;
$hoy = date('Y-m-d');
$manana = date('Y-m-d', strtotime('+1 day'));

foreach ($citas_proximas as $c) {
    $fecha = date('Y-m-d', strtotime($c['fecha_hora']));
    if ($fecha == $hoy) $alertas_hoy++;
    elseif ($fecha == $manana) $alertas_manana++;
}
foreach ($vacunas_proximas as $v) {
    if (!empty($v['proxima_dosis'])) {
        $fecha = date('Y-m-d', strtotime($v['proxima_dosis']));
        if ($fecha == $hoy) $alertas_hoy++;
        elseif ($fecha == $manana) $alertas_manana++;
    }
}

$mostrar_alerta = false;
$mensaje_alerta = "";
if (($alertas_hoy > 0 || $alertas_manana > 0) && !isset($_SESSION['welcome_alert_shown'])) {
    $mostrar_alerta = true;
    $_SESSION['welcome_alert_shown'] = true;
    
    if ($alertas_hoy > 0 && $alertas_manana > 0) {
        $mensaje_alerta = "Tienes <b>{$alertas_hoy}</b> recordatorio(s) para hoy y <b>{$alertas_manana}</b> para mañana.";
    } elseif ($alertas_hoy > 0) {
        $mensaje_alerta = "Tienes <b>{$alertas_hoy}</b> recordatorio(s) para hoy.";
    } elseif ($alertas_manana > 0) {
        $mensaje_alerta = "Tienes <b>{$alertas_manana}</b> recordatorio(s) para mañana.";
    }
}
// Pasar todos los eventos a Javascript para notificaciones en tiempo real
$eventos_js = [];
foreach ($citas_proximas as $c) {
    $eventos_js[] = [
        'tipo' => 'Cita',
        'paciente' => $c['mascota'],
        'datetime' => $c['fecha_hora']
    ];
}
foreach ($vacunas_proximas as $v) {
    if (!empty($v['proxima_dosis'])) {
        // Asegurarse de que tenga formato completo para JS (incluso si la BD aún es DATE)
        $dt = (strlen($v['proxima_dosis']) <= 10) ? $v['proxima_dosis'] . ' 00:00:00' : $v['proxima_dosis'];
        $eventos_js[] = [
            'tipo' => 'Vacuna',
            'paciente' => $v['mascota'],
            'vacuna' => $v['vacuna'],
            'datetime' => $dt
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Huellitas</title>
    <link rel="stylesheet" href="huellitas-shared.css?v=4">
    <link rel="stylesheet" href="huellitas-layout.css?v=4">
    <style>
        /* ── Welcome banner ── */
        .welcome-block {
            background: linear-gradient(135deg, #0c83a7 0%, #064a5f 100%);
            color: #ffffff !important;
            padding: 35px 40px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(7, 58, 73, 0.25);
            position: relative;
            overflow: hidden;
        }
        .welcome-block h2, .welcome-block p { color: #ffffff !important; position: relative; z-index: 2; margin: 0; }
        .welcome-block p { margin-top: 8px; font-size: 15px; opacity: 0.9; }

        /* Huellitas decorativas de fondo */
        .welcome-block::after, .welcome-block::before {
            content: '';
            position: absolute;
            background-image: url('imagenes/paw_logo.png');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.12; 
            z-index: 1;
        }
        .welcome-block::after { right: 15px; top: -15px; width: 120px; height: 120px; transform: rotate(20deg); }
        .welcome-block::before { right: 90px; top: 50px; width: 80px; height: 80px; transform: rotate(35deg); }

        .section-label { font-family:'Nunito',sans-serif; font-size:12px; font-weight:800; color:var(--color-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:14px; margin-top:30px; }

        /* ── Quick access cards ── */
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px,1fr)); gap: 18px; margin-bottom: 10px; }
        .card { background: var(--bg-card); border-radius: 12px; padding: 24px 22px; text-decoration: none; display: flex; flex-direction: column; gap: 8px; box-shadow: var(--shadow-card); border-top: 4px solid #2f89a0; transition: transform .25s, box-shadow .25s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 12px 28px rgba(0,0,0,.13); }
        .card-icon { font-size: 30px; line-height:1; }
        .card h3 { font-family:'Nunito',sans-serif; font-size:16px; font-weight:800; color:var(--color-text); }
        .card p   { font-size:13px; color:var(--color-muted); line-height:1.5; }
        .card-arrow { margin-top:auto; font-size:12px; color:var(--color-muted); font-weight:700; }
        .card.orange { border-top-color:#f39c12; }
        .card.blue   { border-top-color:#1500FF; }
        .card.red    { border-top-color:#e74c3c; }

        /* ── Tablas de Recordatorios ── */
        .reminders-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        @media (max-width: 900px) { .reminders-grid { grid-template-columns: 1fr; } }
        
        .reminder-card { background: var(--bg-card); border-radius: 14px; padding: 24px; box-shadow: var(--shadow-card); border: 1px solid var(--color-border); }
        .reminder-card h4 { font-family: 'Nunito', sans-serif; font-size: 16px; font-weight: 800; color: var(--color-text); margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid var(--color-border); display:flex; align-items:center; gap:8px;}
        
        .reminder-table { width: 100%; border-collapse: collapse; }
        .reminder-table th { background: #0c83a7; color: white; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; border-radius: 4px; }
        .reminder-table td { padding: 12px; border-bottom: 1px solid var(--color-border); font-size: 13px; color: var(--color-text); }
        .reminder-table tr:hover td { background-color: rgba(12, 131, 167, 0.05); }

        .badge-soon { background: #fdebd0; color: #d35400; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; display:inline-block; }
        .badge-today { background: #fadbd8; color: #c0392b; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; display:inline-block; }

        body.dark-mode .reminder-table th { background: #0c3b4a; color: #48dbfb; }
        body.dark-mode .reminder-card { border-color: #1a4a5a; }
        body.dark-mode .reminder-card h4 { color: #48dbfb; border-color: #1a4a5a; }
        body.dark-mode .reminder-table tr:hover td { background-color: rgba(72, 219, 251, 0.05); }
    </style>
</head>
<body>
<?php $pagina_activa='dashboard'; include 'sidebar.php'; ?>

<div class="main-content">
    <?php include 'topbar.php'; ?>

    <div class="page-body">
        <div class="welcome-block">
            <h2>¡Hola, <?= htmlspecialchars($nombre_usuario) ?>! </h2>
            <p>Bienvenido al sistema de gestión de Clínica Veterinaria Huellitas.</p>
        </div>

        <p class="section-label">Acceso Rápido</p>
        <div class="cards-grid">
            <a href="dueños.php" class="card">
                <div class="card-icon">🐾</div>
                <h3>Dueños y Mascotas</h3>
                <p>Gestiona el directorio de clientes y sus mascotas.</p>
                <div class="card-arrow">Ver directorio →</div>
            </a>
            <a href="citas.php" class="card orange">
                <div class="card-icon">📅</div>
                <h3>Agenda / Citas</h3>
                <p>Consulta el calendario y las citas programadas.</p>
                <div class="card-arrow">Ver agenda →</div>
            </a>
            <a href="consultas.php" class="card blue">
                <div class="card-icon">🩺</div>
                <h3>Historial Clínico</h3>
                <p>Revisa y registra consultas médicas y diagnósticos.</p>
                <div class="card-arrow">Ver historial →</div>
            </a>
            <a href="reportes.php" class="card red">
                <div class="card-icon">📊</div>
                <h3>Reportes y Estadísticas</h3>
                <p>Visualiza métricas e informes del desempeño.</p>
                <div class="card-arrow">Ver reportes →</div>
            </a>
        </div>

        <p class="section-label">Recordatorios (Alertas de Seguimiento)</p>
        <div class="reminders-grid">
            <!-- Citas Próximas -->
            <div class="reminder-card">
                <h4>📅 Próximas Citas (7 días)</h4>
                <?php if (count($citas_proximas) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="reminder-table">
                        <thead><tr><th>Paciente</th><th>Dueño</th><th>Fecha y Hora</th><th>Estado</th></tr></thead>
                        <tbody>
                            <?php foreach($citas_proximas as $c): 
                                $fecha_cita = date('Y-m-d', strtotime($c['fecha_hora']));
                                $hora_cita = date('g:i A', strtotime($c['fecha_hora']));
                                $dias = (strtotime($fecha_cita) - strtotime($hoy)) / 86400;
                                $clase_badge = ($dias <= 1) ? 'badge-today' : 'badge-soon';
                                $texto_badge = ($dias == 0) ? 'HOY' : (($dias == 1) ? 'Mañana' : "En $dias día(s)");
                            ?>
                            <tr onclick="showResumenCita('<?= htmlspecialchars(addslashes($c['mascota'])) ?>', '<?= htmlspecialchars(addslashes($c['dueño'])) ?>', '<?= date('d/m/Y', strtotime($c['fecha_hora'])) ?> <?= $hora_cita ?>', '<?= htmlspecialchars(addslashes($c['motivo_previo'])) ?>')" style="cursor: pointer;" title="Ver resumen">
                                <td><strong><?= htmlspecialchars($c['mascota']) ?></strong></td>
                                <td><?= htmlspecialchars($c['dueño']) ?></td>
                                <td><?= date('d/m/Y', strtotime($c['fecha_hora'])) ?> <span style="color:var(--color-muted); font-size:11px;"><?= $hora_cita ?></span></td>
                                <td><span class="<?= $clase_badge ?>"><?= $texto_badge ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p style="color: var(--color-muted); font-size: 14px; text-align: center; padding: 20px;">No hay citas agendadas para los próximos 7 días.</p>
                <?php endif; ?>
            </div>

            <!-- Vacunas Próximas -->
            <div class="reminder-card">
                <h4>💉 Próximas Vacunas (30 días)</h4>
                <?php if (count($vacunas_proximas) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="reminder-table">
                        <thead><tr><th>Paciente</th><th>Dueño</th><th>Vacuna</th><th>Fecha y Hora</th><th>Estado</th></tr></thead>
                        <tbody>
                            <?php foreach($vacunas_proximas as $v): 
                                $fecha_vac = date('Y-m-d', strtotime($v['proxima_dosis']));
                                $hora_vac = (strlen($v['proxima_dosis']) > 10 && substr($v['proxima_dosis'], 11, 5) != '00:00') ? date('g:i A', strtotime($v['proxima_dosis'])) : 'Aviso todo el día';
                                $dias = (strtotime($fecha_vac) - strtotime($hoy)) / 86400;
                                $clase_badge = ($dias <= 1) ? 'badge-today' : 'badge-soon';
                                $texto_badge = ($dias == 0) ? 'HOY' : (($dias == 1) ? 'Mañana' : "En $dias día(s)");
                            ?>
                            <tr onclick="showResumenVacuna('<?= htmlspecialchars(addslashes($v['mascota'])) ?>', '<?= htmlspecialchars(addslashes($v['dueño'])) ?>', '<?= htmlspecialchars(addslashes($v['vacuna'])) ?>', '<?= date('d/m/Y', strtotime($v['proxima_dosis'])) ?> <?= $hora_vac ?>')" style="cursor: pointer;" title="Ver resumen">
                                <td><strong><?= htmlspecialchars($v['mascota']) ?></strong></td>
                                <td><?= htmlspecialchars($v['dueño']) ?></td>
                                <td><?= htmlspecialchars($v['vacuna']) ?></td>
                                <td><?= date('d/m/Y', strtotime($v['proxima_dosis'])) ?> <span style="color:var(--color-muted); font-size:11px;"><?= $hora_vac ?></span></td>
                                <td><span class="<?= $clase_badge ?>"><?= $texto_badge ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p style="color: var(--color-muted); font-size: 14px; text-align: center; padding: 20px;">No hay vacunas programadas para los próximos 30 días.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script src="huellitas-shared.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const eventosPendientes = <?php echo json_encode($eventos_js); ?>;
const notified1h = new Set();
const notifiedNow = new Set();

function checkRealtimeReminders() {
    const ahora = new Date();
    eventosPendientes.forEach(ev => {
        if (!ev.datetime) return;
        
        // Parsear fecha y hora para evitar problemas de zona horaria de JS
        const [datePart, timePart] = ev.datetime.split(' ');
        const [y, m, d] = datePart.split('-');
        const [H, M, S] = timePart.split(':');
        const eventTime = new Date(y, m-1, d, H, M, S);
        
        const diffMs = eventTime - ahora;
        const diffMins = Math.floor(diffMs / 60000);
        const eventId = ev.tipo + '-' + ev.paciente + '-' + ev.datetime;
        
        // Notificar exactamente 1 hora antes (Rango de 50 a 60 minutos antes)
        if (diffMins <= 60 && diffMins > 50 && !notified1h.has(eventId)) {
            notified1h.add(eventId);
            Swal.fire({
                title: '⏰ ¡En 1 hora!',
                html: `<b>${ev.tipo}</b> de <b>${ev.paciente}</b><br>Hora: ${eventTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`,
                icon: 'info', toast: true, position: 'bottom-end', showConfirmButton: false, timer: 10000,
                background: document.body.classList.contains('dark-mode') ? '#0b1f26' : '#ffffff',
                color: document.body.classList.contains('dark-mode') ? '#ffffff' : '#333333'
            });
        }
        
        // Notificar en la hora exacta (Rango de -5 a 5 minutos)
        if (diffMins <= 5 && diffMins >= -5 && !notifiedNow.has(eventId)) {
            notifiedNow.add(eventId);
            Swal.fire({
                title: '🔔 ¡Es la hora!',
                html: `<b>${ev.tipo}</b> de <b>${ev.paciente}</b> es en este momento.`,
                icon: 'warning', iconColor: '#e74c3c', toast: true, position: 'bottom-end', showConfirmButton: false, timer: 12000,
                background: document.body.classList.contains('dark-mode') ? '#0b1f26' : '#ffffff',
                color: document.body.classList.contains('dark-mode') ? '#ffffff' : '#333333'
            });
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    <?php if ($mostrar_alerta): ?>
    // Notificación general de bienvenida
    Swal.fire({
        title: '¡Atención!',
        html: '<?= $mensaje_alerta ?><br><br>Revisa la sección de recordatorios en el panel.',
        icon: 'warning', iconColor: '#f39c12', confirmButtonColor: '#0c83a7', confirmButtonText: 'Entendido, revisar',
        toast: true, position: 'top-end', showConfirmButton: false, timer: 8000, timerProgressBar: true,
        background: document.body.classList.contains('dark-mode') ? '#0b1f26' : '#ffffff',
        color: document.body.classList.contains('dark-mode') ? '#ffffff' : '#333333'
    });
    <?php endif; ?>

    window.cerrarModalResumen = function() {
        document.getElementById('modalResumen').style.display = 'none';
    };

    window.showResumenCita = function(mascota, dueño, fecha, motivo) {
        document.getElementById('modal-title').innerHTML = '&#128197; Detalles de la Cita';
        document.getElementById('modal-fecha').textContent = fecha;
        document.getElementById('modal-paciente').innerHTML = '🐾 <span>' + mascota + '</span>';
        document.getElementById('modal-dueno').textContent = dueño;
        document.getElementById('label-motivo').textContent = 'Motivo de la cita';
        document.getElementById('modal-motivo').textContent = motivo;
        document.getElementById('btn-ir').textContent = 'Ir a Citas';
        document.getElementById('btn-ir').style.background = '#f39c12';
        document.getElementById('btn-ir').onclick = () => window.location.href = 'citas.php';
        document.getElementById('modalResumen').style.display = 'flex';
    };

    window.showResumenVacuna = function(mascota, dueño, vacuna, fecha) {
        document.getElementById('modal-title').innerHTML = '&#128137; Detalles de Vacunación';
        document.getElementById('modal-fecha').textContent = fecha;
        document.getElementById('modal-paciente').innerHTML = '🐾 <span>' + mascota + '</span>';
        document.getElementById('modal-dueno').textContent = dueño;
        document.getElementById('label-motivo').textContent = 'Vacuna a aplicar';
        document.getElementById('modal-motivo').textContent = vacuna;
        document.getElementById('btn-ir').textContent = 'Ir a Consultas';
        document.getElementById('btn-ir').style.background = '#1500FF';
        document.getElementById('btn-ir').onclick = () => window.location.href = 'consultas.php';
        document.getElementById('modalResumen').style.display = 'flex';
    };

    // Iniciar el reloj comprobador de citas y vacunas (Revisa cada 30 segundos)
    checkRealtimeReminders();
    setInterval(checkRealtimeReminders, 30000);
});
</script>

<style>
@keyframes modalIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- Modal Detalles -->
<div id="modalResumen" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,20,30,0.6); backdrop-filter: blur(4px); z-index:9999; align-items:center; justify-content:center;" onclick="if(event.target.id === 'modalResumen') cerrarModalResumen()">
    <div style="background: var(--bg-card, #ffffff); width: 90%; max-width: 550px; border-radius: 14px; box-shadow: 0 15px 40px rgba(0,0,0,0.3); overflow: hidden; animation: modalIn 0.3s ease;">
        <div style="background: linear-gradient(135deg, #0c83a7 0%, #34aed4 100%); padding: 20px 24px; color: white; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="modal-title" style="margin: 0; font-family: 'Nunito', sans-serif; font-size: 20px; display: flex; align-items: center; gap: 10px;">🩺 Detalles</h3>
            <button onclick="cerrarModalResumen()" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; opacity: 0.8; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">✕</button>
        </div>
        <div style="padding: 24px; display: flex; flex-direction: column; gap: 16px; max-height: 70vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-border); padding-bottom: 12px;">
                <div>
                    <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Fecha programada</span>
                    <div id="modal-fecha" style="font-weight: 700; color: #3498db; font-size: 16px;"></div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; border-bottom: 1px solid var(--color-border); padding-bottom: 12px;">
                <div>
                    <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Paciente</span>
                    <div id="modal-paciente" style="font-weight: 600; color: var(--color-text); font-size: 15px;"></div>
                </div>
                <div>
                    <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Dueño</span>
                    <div id="modal-dueno" style="font-weight: 600; color: var(--color-text); font-size: 15px;"></div>
                </div>
            </div>
            <div>
                <span id="label-motivo" style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Motivo</span>
                <div id="modal-motivo" style="font-size: 15px; margin-top: 4px; color: var(--color-text);"></div>
            </div>
        </div>
        <div style="padding: 16px 24px; text-align: right; border-top: 1px solid var(--color-border); background: rgba(0,0,0,0.02); display: flex; justify-content: flex-end; gap: 10px;">
            <button onclick="cerrarModalResumen()" style="background: transparent; color: var(--color-text); border: 1px solid var(--color-border); padding: 10px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 14px; transition: background 0.2s;" onmouseover="this.style.background='var(--color-hover)'" onmouseout="this.style.background='transparent'">Cerrar</button>
            <button id="btn-ir" style="background: #0c83a7; color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 14px; transition: filter 0.2s;" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='none'">Ir a...</button>
        </div>
    </div>
</div>

</body>
</html>