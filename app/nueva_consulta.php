<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

$mensaje = "";

// 1. Cargar CITAS PENDIENTES (Para el flujo Normal)
$citas_pendientes = [];
try {
    $sql_citas = "SELECT c.id_cita, c.id_mascota, c.id_veterinario, c.motivo_previo, m.nombre AS nombre_mascota, d.nombre AS nombre_dueño, v.nombre AS nombre_vet 
                  FROM cita c 
                  INNER JOIN mascota m ON c.id_mascota = m.id_mascota 
                  INNER JOIN dueño d ON m.id_dueño = d.id_dueño
                  INNER JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                  WHERE c.estado = 'Pendiente' ORDER BY c.fecha_hora ASC";
    $citas_pendientes = $conexion->query($sql_citas)->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {}

// 2. Cargar TODAS LAS MASCOTAS y VETERINARIOS (Para el flujo de Urgencia)
$lista_mascotas = $conexion->query("SELECT m.id_mascota, m.nombre AS nombre_mascota, d.nombre AS nombre_dueño FROM mascota m INNER JOIN dueño d ON m.id_dueño = d.id_dueño ORDER BY m.nombre")->fetchAll(PDO::FETCH_ASSOC);
$lista_veterinarios = $conexion->query("SELECT id_veterinario, nombre FROM veterinario ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// 3. Cargar Catálogo de Vacunas
$lista_vacunas = $conexion->query("SELECT id_vacuna, nombre FROM vacuna ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
// ... después de cargar vacunas ($lista_vacunas), añade esto:
$lista_meds = $conexion->query("SELECT id_medicamento, nombre FROM medicamento")->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_ingreso = $_POST['tipo_ingreso'];
    $fecha = $_POST['fecha'];
    $diagnostico = trim($_POST['diagnostico']);
    $observaciones = trim($_POST['observaciones']);
    
    // Variables que dependen de si fue normal o urgencia
    $id_mascota = ""; $id_veterinario = ""; $motivo = ""; $id_cita_completar = null;

    if ($tipo_ingreso == "Normal") {
        $partes_cita = explode('|', $_POST['cita_seleccionada']); // Separamos los datos ocultos
        $id_cita_completar = $partes_cita[0];
        $id_mascota = $partes_cita[1];
        $id_veterinario = $partes_cita[2];
        $motivo = $_POST['motivo_auto']; // El texto que se autollenó
    } else {
        $id_mascota = $_POST['id_mascota_urgencia'];
        $id_veterinario = $_POST['id_veterinario_urgencia'];
        $motivo = "URGENCIA: " . trim($_POST['motivo_urgencia']);
    }

    try {
        $conexion->beginTransaction(); // Iniciamos una transacción segura

        // A. Guardar la Consulta
        $sql_c = "INSERT INTO consulta (fecha, motivo, diagnostico, observaciones, id_mascota, id_veterinario) VALUES (:f, :m, :d, :o, :id_m, :id_v)";
        $stmt_c = $conexion->prepare($sql_c);
        $stmt_c->execute([':f'=>$fecha, ':m'=>$motivo, ':d'=>$diagnostico, ':o'=>$observaciones, ':id_m'=>$id_mascota, ':id_v'=>$id_veterinario]);
        $id_consulta = $conexion->lastInsertId();

        // B. Guardar Tratamiento y Medicamentos si se proporcionó descripción
        $tratamiento_desc = trim($_POST['tratamiento_desc'] ?? '');
        $tratamiento_dias = (int)($_POST['tratamiento_dias'] ?? 1);
        
        if (!empty($tratamiento_desc)) {
            $sql_t = "INSERT INTO tratamiento (id_consulta, descripcion, duracion_dias) VALUES (:id_c, :desc, :dias)";
            $stmt_t = $conexion->prepare($sql_t);
            $stmt_t->execute([':id_c' => $id_consulta, ':desc' => $tratamiento_desc, ':dias' => $tratamiento_dias]);
            $id_tratamiento = $conexion->lastInsertId();
            
            // Si hay medicamentos seleccionados
            if (!empty($_POST['medicamentos']) && is_array($_POST['medicamentos'])) {
                $sql_dt = "INSERT INTO detalle_tratamiento (id_tratamiento, id_medicamento) VALUES (?, ?)";
                $stmt_dt = $conexion->prepare($sql_dt);
                
                // Opcional: Descontar stock
                $sql_stock = "UPDATE medicamento SET stock = stock - 1 WHERE id_medicamento = ? AND stock > 0";
                $stmt_stock = $conexion->prepare($sql_stock);

                foreach ($_POST['medicamentos'] as $id_med) {
                    $stmt_dt->execute([$id_tratamiento, $id_med]);
                    $stmt_stock->execute([$id_med]);
                }
            }
        }

        // C. Si venía de una cita, actualizar su estado a Completada
        if ($id_cita_completar) {
            $stmt_upd = $conexion->prepare("UPDATE cita SET estado = 'Completada' WHERE id_cita = ?");
            $stmt_upd->execute([$id_cita_completar]);
        }

        // D. Si el doctor aplicó vacuna, registrarla en el carnet
        if (isset($_POST['aplico_vacuna']) && $_POST['aplico_vacuna'] == 'SI' && !empty($_POST['id_vacuna'])) {
            $id_vacuna = $_POST['id_vacuna'];
            $prox_dosis = !empty($_POST['proxima_dosis']) ? $_POST['proxima_dosis'] : null;
            
            $sql_v = "INSERT INTO carnet_vacunacion (id_mascota, id_vacuna, fecha_aplicacion, proxima_dosis) VALUES (?, ?, ?, ?)";
            $conexion->prepare($sql_v)->execute([$id_mascota, $id_vacuna, $fecha, $prox_dosis]);
        }

        $conexion->commit(); // Confirmamos que todo se guardó
        header("Location: consultas.php");
        exit();
        
    } catch(PDOException $e) {
        $conexion->rollBack(); // Si algo falla, deshacemos todo para evitar errores
        $mensaje = "<div class='alert error'>Error en el proceso: " . $e->getMessage() . "</div>";
    }
}

$nombre_usuario = $_SESSION['usuario']; $rol_usuario = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Consulta - Huellitas</title>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="huellitas-shared.css?v=4"><link rel="stylesheet" href="huellitas-layout.css?v=4">
    <style>
        .page-body { padding: 35px 40px; }
        .form-card { border-radius: 12px; padding: 35px; max-width: 750px; margin: 0 auto; background: var(--bg-card); }
        .form-card h3 { font-family:'Nunito',sans-serif; font-size: 20px; font-weight: 800; border-bottom: 2px solid var(--color-border); padding-bottom: 10px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 700; font-size: 12px; text-transform: uppercase; margin-bottom: 6px; color: var(--color-muted); }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 2px solid var(--color-border); border-radius: 8px; background: var(--input-bg); color: var(--color-text); }
        
        /* Estilos del Toggle Normal/Urgencia */
        .toggle-container { display: flex; gap: 20px; margin-bottom: 25px; background: var(--input-bg); padding: 15px; border-radius: 8px; border: 1px solid var(--color-border); }
        .radio-label { display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: bold; cursor: pointer; color: var(--color-text); }
        .radio-label input { width: 18px; height: 18px; cursor: pointer; }
        
        .seccion-dinamica { display: none; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 2px dashed var(--color-border); }
        .seccion-dinamica.activa { display: block; }
        .seccion-normal { background: rgba(34, 119, 60, 0.05); border-color: #22773c; }
        .seccion-urgencia { background: rgba(231, 76, 60, 0.05); border-color: #e74c3c; }
        
        .vacuna-box { background: rgba(243, 156, 18, 0.1); border: 2px solid #f39c12; padding: 15px; border-radius: 8px; margin-top: 20px; }

        .btn-submit { background-color: #22773c; color: white; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; font-size: 15px; margin-bottom: 10px;}
        .select2-container--default .select2-selection--single { background: var(--input-bg); border: 2px solid var(--color-border); border-radius: 8px; height: 40px; display: flex; align-items: center; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--color-text); }
        .select2-dropdown { background: var(--bg-card); border: 2px solid var(--color-border); }
        .select2-search__field { background: var(--input-bg); color: var(--color-text); }
        .select2-results__option { color: var(--color-text); }
        .select2-container--default .select2-selection--multiple { background: var(--input-bg); border: 2px solid var(--color-border); border-radius: 8px; min-height: 40px; }
        .select2-container--default .select2-selection--multiple .select2-selection__choice { background: #34495e; color: #fff; border: none; padding: 4px 8px; border-radius: 4px; margin-top: 6px; }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove { color: #fff; margin-right: 5px; cursor: pointer; font-weight: bold; border: none; }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover { color: #f39c12; background: transparent; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <?php include 'topbar.php'; ?>
    <div class="page-body">
        <?= $mensaje ?>
        <div class="form-card">
            <h3>&#129658; Registro de Consulta Médica</h3>
            
            <form method="POST">
                
                <div class="form-group">
                    <label>Fecha de la Consulta:</label>
                    <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="toggle-container">
                    <span style="font-weight:bold; margin-right:10px; color: var(--color-text);">Tipo de Ingreso:</span>
                    <label class="radio-label">
                        <input type="radio" name="tipo_ingreso" value="Normal" checked onchange="cambiarModo('Normal')"> Cita Programada
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="tipo_ingreso" value="Urgencia" onchange="cambiarModo('Urgencia')"> Urgencia Médica
                    </label>
                </div>

                <div id="bloque-normal" class="seccion-dinamica seccion-normal activa">
                    <h4 style="margin-bottom:15px; color:#22773c;">&#9989; Seleccionar paciente en sala de espera</h4>
                    <div class="form-group">
                        <label>Selecciona la Cita:</label>
                        <select name="cita_seleccionada" id="select-citas" class="buscador-select" style="width:100%" onchange="autocompletarMotivo()">
                            <option value="">-- Elige la cita de la agenda --</option>
                            <?php foreach($citas_pendientes as $c): 
                                // Empaquetamos ID Cita, ID Mascota e ID Vet en el value
                                $val = $c['id_cita'].'|'.$c['id_mascota'].'|'.$c['id_veterinario'];
                            ?>
                                <option value="<?= $val ?>" data-motivo="<?= htmlspecialchars($c['motivo_previo']) ?>">
                                    Paciente: <?= htmlspecialchars($c['nombre_mascota']) ?> (Dr. <?= htmlspecialchars($c['nombre_vet']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Motivo agendado (Automático):</label>
                        <input type="text" id="motivo_auto" name="motivo_auto" readonly style="background:var(--bg-main); font-weight:bold;">
                    </div>
                </div>

                <div id="bloque-urgencia" class="seccion-dinamica seccion-urgencia">
                    <h4 style="margin-bottom:15px; color:#e74c3c;">&#128680; Ingreso por Urgencias</h4>
                    <div class="form-group">
                        <label>Paciente:</label>
                        <select name="id_mascota_urgencia" class="buscador-select" style="width:100%">
                            <option value="">-- Buscar mascota en el sistema --</option>
                            <?php foreach($lista_mascotas as $m): ?>
                                <option value="<?= $m['id_mascota'] ?>"><?= htmlspecialchars($m['nombre_mascota']) ?> (Dueño: <?= htmlspecialchars($m['nombre_dueño']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Médico de Turno:</label>
                        <select name="id_veterinario_urgencia" class="buscador-select" style="width:100%">
                            <?php foreach($lista_veterinarios as $v): ?>
                                <option value="<?= $v['id_veterinario'] ?>"><?= htmlspecialchars($v['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Motivo de la Urgencia:</label>
                        <textarea name="motivo_urgencia" placeholder="Describa el motivo de ingreso rápido..."></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label>Diagnóstico del Médico:</label>
                    <textarea name="diagnostico" required placeholder="Gastroenteritis aguda, paciente sano, etc..."></textarea>
                </div>
                <div class="form-group">
                    <label>Observaciones y Recomendaciones:</label>
                    <textarea name="observaciones" placeholder="Recetar dieta blanda, reposo..."></textarea>
                </div>

                <div class="vacuna-box">
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="color:#d68910; font-size:14px; display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="aplico_vacuna" value="SI" id="check-vacuna" style="width:18px;height:18px;cursor:pointer;" onchange="toggleVacuna()">
                            ¿Se aplicó alguna vacuna durante esta consulta?
                        </label>
                    </div>
                    <div id="detalles-vacuna" style="display:none; margin-top:15px; padding-top:15px; border-top:1px dashed #f39c12;">
                        <div class="form-group">
                            <label>Selecciona la vacuna del inventario:</label>
                            <select name="id_vacuna" class="buscador-select" style="width:100%">
                                <option value="">-- Elige la vacuna --</option>
                                <?php foreach($lista_vacunas as $v): ?>
                                    <option value="<?= $v['id_vacuna'] ?>"><?= htmlspecialchars($v['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Fecha y Hora del próximo refuerzo (Opcional):</label>
                            <input type="datetime-local" name="proxima_dosis">
                        </div>
                    </div>
                </div>
<div class="vacuna-box" style="border-color: #9b59b6; background: rgba(155, 89, 182, 0.1);">
    <h4 style="color:#9b59b6; margin-bottom:15px;">&#128138; Tratamiento y Medicación</h4>
    
    <div class="form-group">
        <label>Tratamiento / Plan de acción:</label>
        <textarea name="tratamiento_desc" placeholder="Describa el tratamiento a seguir..."></textarea>
    </div>

    <div class="form-group">
        <label>Duración (días):</label>
        <input type="number" name="tratamiento_dias" value="1" min="1">
    </div>

    <div class="form-group">
        <label>Medicamentos necesarios (Selección múltiple):</label>
        <select name="medicamentos[]" class="buscador-select" multiple style="width:100%">
            <?php foreach($lista_meds as $med): ?>
                <option value="<?= $med['id_medicamento'] ?>"><?= htmlspecialchars($med['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <small style="color:var(--color-muted);">* Mantén presionada la tecla Ctrl para seleccionar varios.</small>
    </div>
</div>
                <div style="margin-top: 25px;">
                    <button type="submit" class="btn-submit">&#128190; Guardar Historial Clínico</button>
                    <a href="consultas.php" style="display:block; text-align:center; color:var(--color-text); font-weight:bold; text-decoration:none; margin-top:10px;">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="huellitas-shared.js"></script>
<script>
    $(document).ready(function() { $('.buscador-select').select2(); });

    function cambiarModo(modo) {
        if(modo === 'Normal') {
            document.getElementById('bloque-normal').classList.add('activa');
            document.getElementById('bloque-urgencia').classList.remove('activa');
        } else {
            document.getElementById('bloque-urgencia').classList.add('activa');
            document.getElementById('bloque-normal').classList.remove('activa');
        }
    }

    function autocompletarMotivo() {
        var select = document.getElementById('select-citas');
        var motivo = select.options[select.selectedIndex].getAttribute('data-motivo');
        document.getElementById('motivo_auto').value = motivo || '';
        
        // Si el motivo incluye la palabra vacuna, marcamos el checkbox automáticamente por comodidad
        if(motivo && motivo.toLowerCase().includes('vacuna')) {
            document.getElementById('check-vacuna').checked = true;
            toggleVacuna();
        }
    }

    function toggleVacuna() {
        var check = document.getElementById('check-vacuna');
        document.getElementById('detalles-vacuna').style.display = check.checked ? 'block' : 'none';
    }
</script>
</body>
</html>