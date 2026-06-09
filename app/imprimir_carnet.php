<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

if (!isset($_GET['id'])) { echo "ID no proporcionado"; exit(); }
$id_mascota = (int)$_GET['id'];

// Obtener datos
$sql = "SELECT m.*, r.nombre AS nombre_raza, d.nombre AS nombre_dueño, d.telefono AS tel_dueño, d.direccion AS dir_dueño
        FROM mascota m
        INNER JOIN raza r ON m.id_raza = r.id_raza
        INNER JOIN dueño d ON m.id_dueño = d.id_dueño
        WHERE m.id_mascota = :id";
$stmt = $conexion->prepare($sql);
$stmt->execute([':id' => $id_mascota]);
$mascota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mascota) { echo "Mascota no encontrada"; exit(); }

// Vacunas
$stmt_v = $conexion->prepare("SELECT v.nombre AS nombre_vacuna, cv.fecha_aplicacion FROM carnet_vacunacion cv INNER JOIN vacuna v ON cv.id_vacuna=v.id_vacuna WHERE cv.id_mascota=:id ORDER BY cv.fecha_aplicacion ASC");
$stmt_v->execute([':id' => $id_mascota]);
$vacunas = $stmt_v->fetchAll(PDO::FETCH_ASSOC);

$edad = "";
if (!empty($mascota['fecha_nacimiento'])) {
    $diff = (new DateTime())->diff(new DateTime($mascota['fecha_nacimiento']));
    $edad = $diff->y > 0 ? $diff->y . ' años' : $diff->m . ' meses';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carnet de Vacunación - <?= htmlspecialchars($mascota['nombre']) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@600;800&display=swap');

        /* Reset y configuración de página */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Nunito', sans-serif; background: #555; display: flex; flex-direction: column; align-items: center; padding: 20px; gap: 20px;}
        
        @page {
            size: A4 landscape; /* Para que se imprima horizontalmente */
            margin: 0; 
        }

        /* Estructura de la hoja A4 */
        .sheet {
            width: 297mm;
            height: 210mm;
            background: white;
            position: relative;
            display: flex;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            overflow: hidden;
            background-color: #f6fcfc;
            /* Patrón de huellitas de fondo sutil */
            background-image: radial-gradient(#d4f1f4 15%, transparent 16%), radial-gradient(#d4f1f4 15%, transparent 16%);
            background-size: 60px 60px;
            background-position: 0 0, 30px 30px;
        }

        /* Cada mitad de la hoja (página del folleto) */
        .page-half {
            width: 50%;
            height: 100%;
            padding: 40px;
            position: relative;
        }
        
        /* Línea divisoria central para doblar */
        .sheet::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            border-left: 1px dashed #b2e6e9;
        }

        /* ESTILOS: CARA EXTERIOR (Hoja 1) */
        .back-cover {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #f39c12;
            font-family: 'Fredoka One', cursive;
        }
        .back-cover .logo-heart { font-size: 80px; margin-bottom: 10px; }
        .back-cover h2 { font-size: 32px; letter-spacing: 2px; margin-bottom: 40px;}
        .back-cover p { font-size: 20px; margin-bottom: 20px; letter-spacing: 1px; color: #e67e22; }

        .front-cover {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end; /* Para empujar la tarjeta hacia abajo */
            padding-bottom: 50px;
        }
        .front-cover .logo-small { position: absolute; top: 40px; text-align: center; color: #f39c12; font-family: 'Fredoka One', cursive;}
        .front-cover .logo-small .icon { font-size: 40px; }
        .front-cover .logo-small h3 { font-size: 18px; }

        /* Ilustración de perritos (Placeholder o Imagen) */
        .dogs-illustration {
            width: 80%;
            height: 180px;
            background: url('https://cdn-icons-png.flaticon.com/512/3047/3047928.png') no-repeat center bottom;
            background-size: contain;
            margin-bottom: -20px;
            z-index: 2;
            position: relative;
        }

        /* Tarjeta de información principal */
        .info-card {
            width: 100%;
            background: white;
            border: 5px solid #00d2d3;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            z-index: 1;
        }
        .info-card h1 {
            font-family: 'Fredoka One', cursive;
            color: #f39c12;
            text-align: center;
            font-size: 28px;
            line-height: 1.1;
            margin-bottom: 30px;
            text-transform: uppercase;
        }
        .info-row {
            display: flex;
            align-items: flex-end;
            margin-bottom: 20px;
            gap: 10px;
        }
        .info-label {
            font-weight: 800;
            color: #e67e22;
            font-size: 16px;
            white-space: nowrap;
        }
        .info-value {
            flex-grow: 1;
            border-bottom: 2px solid #b2e6e9;
            color: #2c3e50;
            font-size: 18px;
            font-weight: 600;
            padding: 0 10px 2px;
        }

        /* ESTILOS: CARA INTERIOR (Hoja 2) */
        .table-page { padding: 40px 30px; }
        .table-page h2 {
            font-family: 'Fredoka One', cursive;
            color: #f39c12;
            text-align: center;
            font-size: 26px;
            margin-bottom: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .table-page h2 span { border-bottom: 4px solid #f39c12; padding-bottom: 4px; }
        
        .carnet-table { width: 100%; border-collapse: collapse; background: white; }
        .carnet-table th {
            background: #48dbfb;
            color: white;
            font-family: 'Fredoka One', cursive;
            font-size: 16px;
            padding: 12px;
            letter-spacing: 1px;
            text-align: left;
            border: 2px solid #48dbfb;
        }
        .carnet-table td {
            border: 2px solid #48dbfb;
            padding: 15px 12px;
            height: 45px; /* Para asegurar el espacio de escritura manual */
            font-size: 15px;
            color: #2c3e50;
            font-weight: 600;
        }

        /* Botón de imprimir (oculto en papel) */
        .print-btn {
            position: fixed; top: 20px; right: 20px;
            background: #e74c3c; color: white; border: none; padding: 15px 25px;
            border-radius: 30px; font-size: 18px; font-weight: bold; cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3); z-index: 1000;
        }
        .print-btn:hover { background: #c0392b; }

        @media print {
            body { background: white; padding: 0; }
            .sheet { box-shadow: none; }
            .print-btn { display: none; }
            /* Forzar el fondo a imprimirse */
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()">🖨️ Imprimir Carnet</button>

    <!-- HOJA 1: CARA EXTERIOR (Contraportada y Portada) -->
    <div class="sheet">
        <!-- Contraportada (Izquierda al abrir el PDF plano) -->
        <div class="page-half back-cover">
            <div class="logo-heart">🐾</div>
            <h2>huellitas</h2>
            <p>cra 36,cl152 #33-89</p>
            <p>3144019612</p>
        </div>
        
        <!-- Portada (Derecha al abrir el PDF plano) -->
        <div class="page-half front-cover">
            <div class="logo-small">
                <div class="icon">🐾</div>
                <h3>huellitas</h3>
            </div>
            
            <div class="dogs-illustration"></div>
            
            <div class="info-card">
                <h1>CARNET DE<br>VACUNACIÓN</h1>
                
                <div class="info-row">
                    <span class="info-label">Nombre de tu mascota:</span>
                    <span class="info-value"><?= htmlspecialchars($mascota['nombre']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Raza:</span>
                    <span class="info-value"><?= htmlspecialchars($mascota['nombre_raza']) ?></span>
                </div>
                <div style="display:flex; gap: 20px;">
                    <div class="info-row" style="flex:1;">
                        <span class="info-label">Edad</span>
                        <span class="info-value text-center"><?= htmlspecialchars($edad) ?></span>
                    </div>
                    <div class="info-row" style="flex:1.5;">
                        <span class="info-label">Teléfono</span>
                        <span class="info-value"><?= htmlspecialchars($mascota['tel_dueño']) ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Dirección</span>
                    <span class="info-value"><?= htmlspecialchars($mascota['dir_dueño'] ?? '') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dueño</span>
                    <span class="info-value"><?= htmlspecialchars($mascota['nombre_dueño']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- HOJA 2: CARA INTERIOR (Tablas) -->
    <div class="sheet" style="page-break-before: always;">
        <!-- Lado Izquierdo: Vacunas -->
        <div class="page-half table-page">
            <h2><span>VACUNACIÓN</span></h2>
            <table class="carnet-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">FECHA</th>
                        <th>VACUNA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Mostrar vacunas registradas y rellenar con filas vacías hasta tener al menos 8
                    $max_rows = max(8, count($vacunas) + 2);
                    for($i=0; $i<$max_rows; $i++): 
                    ?>
                    <tr>
                        <td><?= isset($vacunas[$i]) ? date('d/m/Y', strtotime($vacunas[$i]['fecha_aplicacion'])) : '' ?></td>
                        <td><?= isset($vacunas[$i]) ? htmlspecialchars($vacunas[$i]['nombre_vacuna']) : '' ?></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Lado Derecho: Desparasitación -->
        <div class="page-half table-page">
            <h2><span>DESPARASITACIÓN</span></h2>
            <table class="carnet-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">FECHA</th>
                        <th>PRODUCTO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for($i=0; $i<$max_rows; $i++): ?>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
