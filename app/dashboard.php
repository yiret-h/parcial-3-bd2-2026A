<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
$nombre_usuario = $_SESSION['usuario'];
$rol_usuario    = $_SESSION['rol'];
$titulo_pagina  = 'Panel de Control';
$pagina_activa  = 'dashboard';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Huellitas</title>
    <link rel="stylesheet" href="huellitas-shared.css">
    <link rel="stylesheet" href="huellitas-layout.css">
    <style>
        /* ── Welcome banner ── */
        .welcome-banner {
            background: linear-gradient(135deg,#006805 0%,#1a9e2a 50%,#3dba4e 100%);
            border-radius: 14px; padding: 28px 32px; color: white;
            margin-bottom: 26px; position: relative; overflow: hidden;
        }
        .welcome-banner::after {
            content:'🐾'; position:absolute; right:28px; top:50%;
            transform:translateY(-50%); font-size:80px; opacity:.14;
        }
        .welcome-banner h1 { font-family:'Nunito',sans-serif; font-size:24px; font-weight:800; margin-bottom:5px; }
        .welcome-banner p  { opacity:.9; font-size:14px; }

        .section-label {
            font-family:'Nunito',sans-serif; font-size:12px; font-weight:800;
            color:var(--color-muted); text-transform:uppercase; letter-spacing:1px;
            margin-bottom:14px;
        }

        /* ── Quick access cards ── */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px,1fr));
            gap: 18px; margin-bottom: 30px;
        }
        .card {
            background: var(--bg-card);
            border-radius: 12px; padding: 24px 22px;
            text-decoration: none; display: flex; flex-direction: column; gap: 8px;
            box-shadow: var(--shadow-card);
            border-top: 4px solid #22773c;
            transition: transform .25s, box-shadow .25s;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 12px 28px rgba(0,0,0,.13); }
        .card-icon { font-size: 30px; line-height:1; }
        .card h3 { font-family:'Nunito',sans-serif; font-size:16px; font-weight:800; color:var(--color-text); }
        .card p   { font-size:13px; color:var(--color-muted); line-height:1.5; }
        .card-arrow { margin-top:auto; font-size:12px; color:var(--color-muted); font-weight:700; }
        .card.orange { border-top-color:#f39c12; }
        .card.blue   { border-top-color:#3498db; }
        .card.purple { border-top-color:#9b59b6; }
        .card.teal   { border-top-color:#1abc9c; }
        .card.red    { border-top-color:#e74c3c; }
    </style>
</head>
<body>
<?php $pagina_activa='dashboard'; include 'sidebar.php'; ?>

<div class="main-content">
    <?php include 'topbar.php'; ?>

    <div class="page-body">
        <div class="welcome-banner">
            <h1>¡Hola, <?= htmlspecialchars($nombre_usuario) ?>! 👋</h1>
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
            <a href="tratamientos.php" class="card purple">
                <div class="card-icon">💊</div>
                <h3>Tratamientos</h3>
                <p>Administra los tratamientos activos y completados.</p>
                <div class="card-arrow">Ver tratamientos →</div>
            </a>
            <a href="medicamentos.php" class="card teal">
                <div class="card-icon">🧪</div>
                <h3>Catálogo de Medicamentos</h3>
                <p>Inventario de medicamentos disponibles en la clínica.</p>
                <div class="card-arrow">Ver catálogo →</div>
            </a>
            <a href="reportes.php" class="card red">
                <div class="card-icon">📊</div>
                <h3>Reportes y Estadísticas</h3>
                <p>Visualiza métricas e informes del desempeño.</p>
                <div class="card-arrow">Ver reportes →</div>
            </a>
        </div>
    </div>
</div>

<script src="huellitas-shared.js"></script>
</body>
</html>