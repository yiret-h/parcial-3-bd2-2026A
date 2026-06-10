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

// Subir foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    $dir = 'imagenes/mascotas/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $filename = $id_mascota . '_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $filename)) {
            try {
                $stmt = $conexion->prepare("INSERT INTO fotografia_mascota (ruta_archivo, id_mascota) VALUES (:ruta, :id_mascota)");
                $stmt->execute([':ruta' => $filename, ':id_mascota' => $id_mascota]);
            } catch(PDOException $e) {}
        }
    }
    header("Location: perfil_mascota.php?id=$id_mascota");
    exit();
}

// Eliminar foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_foto'])) {
    $foto_to_delete = basename($_POST['delete_foto']);
    $file_path = "imagenes/mascotas/" . $foto_to_delete;
    if (strpos($foto_to_delete, $id_mascota . "_") === 0 && file_exists($file_path)) {
        if (unlink($file_path)) {
            try {
                $stmt = $conexion->prepare("DELETE FROM fotografia_mascota WHERE ruta_archivo = :ruta");
                $stmt->execute([':ruta' => $foto_to_delete]);
            } catch(PDOException $e) {}
        }
    }
    header("Location: perfil_mascota.php?id=$id_mascota");
    exit();
}

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

// Leer fotos de la mascota
$fotos = glob("imagenes/mascotas/{$id_mascota}_*.*");
if (!$fotos) $fotos = [];

$fotos_js = array_map(function($f) { return str_replace('\\', '/', $f); }, $fotos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($mascota['nombre']) ?> - Huellitas</title>
    <link rel="stylesheet" href="huellitas-shared.css?v=4">
    <link rel="stylesheet" href="huellitas-layout.css?v=4">
    <style>
        /* Estilos específicos de perfil_mascota.php en AZUL */
        .page-body { padding:30px 40px; }
        
        .perfil-hero {
            background:linear-gradient(135deg,#0c83a7 0%,#34aed4 100%);
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
        .perfil-hero::after, .perfil-hero::before {
            content: '';
            position: absolute;
            background-image: url('imagenes/paw_logo.png');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.12;
            z-index: 0;
        }
        .perfil-hero::after { right: 20px; top: -15px; width: 140px; height: 140px; transform: rotate(15deg); }
        .perfil-hero::before { right: 100px; top: 30px; width: 90px; height: 90px; transform: rotate(30deg); }
        .avatar-mascota { width:80px; height:80px; background:rgba(255,255,255,.2); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:44px; flex-shrink:0; position:relative; z-index:1; }
        .perfil-info h1 { font-family:'Nunito',sans-serif; font-size:28px; font-weight:800; }
        .perfil-info .meta { display:flex; gap:16px; margin-top:6px; flex-wrap:wrap; }
        .perfil-info .meta span { font-size:13px; opacity:.9; background:rgba(0,0,0,.15); padding:3px 10px; border-radius:10px; }
        .perfil-hero .actions { margin-left:auto; display:flex; flex-direction:column; gap:8px; z-index:1; }
        
        /* Secciones y Grid */
        .sections-grid { display:grid; grid-template-columns:1fr 1fr; gap:22px; }
        .section-card { background:var(--bg-card); border-radius:12px; padding:22px; box-shadow:var(--shadow-card); }
        .section-card h4 { font-family:'Nunito',sans-serif; font-size:16px; font-weight:800; color:var(--color-text); margin-bottom:16px; display:flex; align-items:center; gap:8px; justify-content: space-between; }
        .section-card.full { grid-column:1 / -1; }
        
        /* Info básica */
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .info-item label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:var(--color-muted); font-weight:700; display:block; margin-bottom:3px; }
        .info-item span { font-size:14px; font-weight:600; color:var(--color-text); }

        /* Mini Album */
        .mini-album-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(80px, 1fr)); gap:10px; margin-top:10px;}
        .album-photo-container { position: relative; width: 100%; aspect-ratio: 1; transition:transform 0.2s; cursor:pointer; }
        .album-photo-container:hover { transform:scale(1.05); }
        .album-photo-img { width:100%; height:100%; border-radius:10px; background-size:cover; background-position:center; }
        .album-photo { width:100%; aspect-ratio: 1; background:rgba(12,131,167,0.1); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:30px; border:2px dashed #0c83a7; color:#0c83a7; cursor:pointer; transition:transform 0.2s;}
        .album-photo:hover { transform:scale(1.05); background:rgba(12,131,167,0.2); }
        
        /* Tabla compacta */
        .mini-table { width:100%; border-collapse:collapse; }
        .mini-table th { background:var(--table-header); color:#fff; text-align:left; padding:9px 12px; font-size:11px; text-transform:uppercase; letter-spacing:.4px; }
        .mini-table td { padding:10px 12px; border-bottom:1px solid var(--color-border); font-size:13px; color:var(--color-text); }
        .mini-table tr:hover td { background:var(--color-hover); }

        .badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:700; }
        .badge-Pendiente  { background:#fff3cd; color:#856404; }
        .badge-Completada { background:#d4edda; color:#155724; }
        .fecha-prox { color:#d35400; font-weight:700; background:#fdebd0; padding:3px 8px; border-radius:5px; font-size:12px; }
        .empty-msg { color:var(--color-muted); font-size:13px; text-align:center; padding:20px; }
        
        body.dark-mode .album-photo { border-color: var(--color-accent); color: var(--color-accent); background: rgba(52, 174, 212, 0.1); }
        body.dark-mode .album-photo:hover { background: rgba(52, 174, 212, 0.2); }
        body.dark-mode .perfil-hero { background: linear-gradient(135deg, #095870 0%, #0c83a7 100%); }
        body.dark-mode .badge-Pendiente { background: #4a3607; color: #fce392; }
        body.dark-mode .badge-Completada { background: #133a1e; color: #a2e0b1; }
        body.dark-mode .fecha-prox { background: #4a2006; color: #eebb99; }

        .btn-print { background:#f39c12; color:#fff; border:none; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:bold; cursor:pointer; display:inline-flex; align-items:center; gap:5px;}
        .btn-print:hover { background:#d68910; }

        /* Plantillas de Impresión */
        @media print {
            body { background: white !important; color: black !important; }
            .sidebar, .top-header, .perfil-hero .actions, .btn-print, .mini-album-grid form { display:none !important; }
            .main-content { margin:0 !important; width:100% !important; padding:0 !important;}
            .page-body { padding:0 !important; }
            .section-card { box-shadow:none !important; border:1px solid #ccc !important; page-break-inside: avoid; }
            .perfil-hero { background: #0c83a7 !important; -webkit-print-color-adjust: exact; color: white !important; }
            
            /* Ocultar secciones no deseadas según la clase en body */
            body.print-vacunas .section-card:not(.card-vacunas) { display: none !important; }
            body.print-consultas .section-card:not(.card-consultas) { display: none !important; }
            body.print-vacunas .perfil-hero, body.print-consultas .perfil-hero { display: flex !important; }
            
            /* Encabezado de clínica visible al imprimir */
            .print-header { display:block !important; text-align:center; margin-bottom:20px; border-bottom:2px solid #0c83a7; padding-bottom:10px;}
            .print-header h1 { color:#0c83a7; font-size:24px; margin:0;}
            .print-header p { font-size:12px; color:#555;}
        }
        @media screen {
            .print-header { display: none; }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <?php include 'topbar.php'; ?>

    <div class="page-body">
        
        <!-- Print Header -->
        <div class="print-header">
            <h1>Huellitas - Clínica Veterinaria</h1>
            <p>Reporte Oficial del Paciente</p>
        </div>

        <!-- Botón Volver -->
        <div style="margin-bottom: 20px;">
            <a href="dueños.php" style="background: var(--bg-card); border: 1px solid var(--color-border); padding: 8px 16px; border-radius: 8px; text-decoration: none; color: var(--color-text); font-weight: 800; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s;">
                <span style="font-size: 16px;">←</span> Volver al Directorio
            </a>
        </div>

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
                <a href="nueva_cita.php" class="btn btn-primary" style="background: white; color: #0c83a7;">📅 Agendar Cita</a>
                <a href="nueva_consulta.php" class="btn btn-primary" style="background: #e74c3c;">🩺 Nueva Consulta</a>
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
                </div>
            </div>
            
            <!-- Mini Álbum -->
            <div class="section-card">
                <h4>📸 Mini Álbum</h4>
                <div class="mini-album-grid">
                    <?php foreach($fotos_js as $index => $foto_url): ?>
                        <div class="album-photo-container">
                            <div class="album-photo-img" onclick="openLightbox(<?= $index ?>)" style="background-image:url('<?= htmlspecialchars($foto_url) ?>');"></div>
                            <form method="POST" style="position: absolute; top: 4px; right: 4px; margin: 0; padding: 0;">
                                <input type="hidden" name="delete_foto" value="<?= htmlspecialchars(basename($foto_url)) ?>">
                                <button type="submit" onclick="return confirm('¿Estás seguro de que deseas eliminar esta foto?');" style="background: rgba(231,76,60,0.9); color: white; border: none; border-radius: 50%; width: 22px; height: 22px; font-size: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.3);" title="Eliminar foto">✕</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                    <?php if(count($fotos) == 0): ?>
                        <div class="album-photo" title="Foto inicial"><?= $mascota['nombre_especie'] === 'Gato' ? '🐱' : ($mascota['nombre_especie'] === 'Perro' ? '🐶' : '🐾') ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" id="form-foto" style="display:inline;">
                        <label class="album-photo" title="Agregar Foto" style="cursor:pointer; display:flex; align-items:center; justify-content:center; margin:0; position:relative;">
                            +
                            <input type="file" name="foto" style="position:absolute; width:100%; height:100%; opacity:0; cursor:pointer;" onchange="document.getElementById('form-foto').submit();" accept="image/*">
                        </label>
                    </form>
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
            <div class="section-card full card-vacunas">
                <h4>
                    <span>💉 Carnet de Vacunación</span>
                    <a href="imprimir_carnet.php?id=<?= $id_mascota ?>" target="_blank" class="btn-print" style="text-decoration:none;">🖨️ Imprimir Carnet (Nuevo Diseño)</a>
                </h4>
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
            <div class="section-card full card-consultas">
                <h4>
                    <span>🩺 Historial Médico</span>
                    <button class="btn-print" onclick="imprimirSeccion('consultas')">🖨️ Imprimir Historial</button>
                </h4>
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

<!-- Lightbox Modal para el Mini Álbum -->
<div id="lightbox" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,20,30,0.85); backdrop-filter: blur(5px); z-index:9999; align-items:center; justify-content:center; flex-direction:column;">
    <div style="position: relative; max-width: 90%; display: flex; align-items: center; justify-content: center;">
        <button onclick="prevImage(event)" style="position: absolute; left: 10px; background: rgba(0,0,0,0.6); border: none; color: white; font-size: 24px; cursor: pointer; z-index: 10000; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.8)'" onmouseout="this.style.background='rgba(0,0,0,0.6)'">&#10094;</button>
        <img id="lightbox-img" src="" style="min-width:350px; min-height:350px; width:auto; height:auto; max-width:90vw; max-height:80vh; object-fit:cover; border-radius:12px; box-shadow:0 15px 40px rgba(0,0,0,0.5); border: 3px solid white; cursor: pointer;" onclick="document.getElementById('lightbox').style.display='none'">
        <button onclick="nextImage(event)" style="position: absolute; right: 10px; background: rgba(0,0,0,0.6); border: none; color: white; font-size: 24px; cursor: pointer; z-index: 10000; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.8)'" onmouseout="this.style.background='rgba(0,0,0,0.6)'">&#10095;</button>
    </div>
    <p style="color: white; font-family: 'Nunito', sans-serif; margin-top: 15px; font-weight: 800; opacity: 0.8; cursor: pointer;" onclick="document.getElementById('lightbox').style.display='none'">Haz clic en la imagen o en el fondo para cerrar</p>
</div>

<script>
const fotosAlbum = <?= json_encode($fotos_js ?? []) ?>;
let currentIndex = 0;

function openLightbox(index) {
    if (fotosAlbum.length === 0) return;
    currentIndex = index;
    updateLightboxImage();
    document.getElementById('lightbox').style.display = 'flex';
}

function updateLightboxImage() {
    document.getElementById('lightbox-img').src = fotosAlbum[currentIndex];
}

function prevImage(e) {
    e.stopPropagation();
    currentIndex = (currentIndex > 0) ? currentIndex - 1 : fotosAlbum.length - 1;
    updateLightboxImage();
}

function nextImage(e) {
    e.stopPropagation();
    currentIndex = (currentIndex < fotosAlbum.length - 1) ? currentIndex + 1 : 0;
    updateLightboxImage();
}

// Cerrar haciendo clic en el fondo
document.getElementById('lightbox').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
    }
});

function imprimirSeccion(tipo) {
    // tipo puede ser 'vacunas' o 'consultas'
    document.body.classList.add('print-' + tipo);
    window.print();
    document.body.classList.remove('print-' + tipo);
}
</script>
<script src="huellitas-shared.js"></script>
</body>
</html>