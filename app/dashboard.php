<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - Clínica Veterinaria</title>
    <style>
        /* Diseño de la interfaz con menú lateral fijo */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f4f7f6;
            display: flex;
        }

        /* Estilos del menú lateral */
        .sidebar {
            width: 260px;
            height: 100vh;
            background-color: #006805;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 25px;
            text-align: center;
            background-color: #074e0b;
            border-bottom: 1px solid #074e0b;
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #e4ffea;
        }

        .user-info {
            padding: 15px 25px;
            background-color: #0a5f0e;
            font-size: 14px;
            border-bottom: 1px solid #0a5f0e;
        }

        .user-info span {
            display: block;
            color: #ffffff;
        }

        .user-info .rol {
            color: #eeff00;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-menu li a {
            display: block;
            padding: 15px 25px;
            color: #ecf0f1;
            text-decoration: none;
            font-size: 15px;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar-menu li a:hover {
            background-color: #3d8d58;
            border-left-color: #227446;
            padding-left: 30px;
        }

        .btn-logout {
            background-color: #075521;
            text-align: center;
            font-weight: bold;
        }

        .btn-logout:hover {
            background-color: #e74c3c !important;
            border-left-color: #e74c3c !important;
        }

        /* Estilos del contenedor de contenido principal */
        .main-content {
            margin-left: 260px; /* Deja el espacio para que no lo tape el menú */
            padding: 40px;
            width: calc(100% - 260px);
            box-sizing: border-box;
        }

        .welcome-card {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            margin-bottom: 30px;
        }

        .welcome-card h1 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }

        .welcome-card p {
            color: #7f8c8d;
            margin: 0;
        }

        /* Tarjetas informativas de acceso rápido */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            border-top: 4px solid #3498db;
        }

        .card h4 {
            margin: 0 0 10px 0;
            color: #7f8c8d;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }

        .card p {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Huellitas</h3>
        </div>
        
        <div class="user-info">
            <span>Bienvenido, <b><?= htmlspecialchars($nombre_usuario) ?></b></span>
            <div class="rol"><?= htmlspecialchars($rol_usuario) ?></div>
        </div>

        <ul class="sidebar-menu">
            <li><a href="dashboard.php">Inicio</a></li>
            <li><a href="dueños.php">Dueños y Mascotas</a></li>
            <li><a href="citas.php">Agenda / Citas</a></li>
            <li><a href="consultas.php">Consultas Médicas</a></li>
            <li><a href="vacunas.php">Carnet de Vacunación</a></li>
            <li><a href="tratamientos.php">Tratamientos</a></li>
            <li><a href="medicamentos.php">Catálogo Medicamentos</a></li>
            <li><a href="reportes.php">Reportes y Estadísticas</a></li>
            <li><a href="logout.php" class="btn-logout">Cerrar Sesión</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="welcome-card">
            <h1>Panel de Control</h1>
            <p>Sistema de gestión clínica para el control de pacientes, historiales médicos y agendas de la veterinaria.</p>
        </div>

        <div class="dashboard-grid">
            <div class="card" style="border-top-color: #22773c;">
                <h4>Mascotas Activas</h4>
                <p>Módulo de Pacientes</p>
            </div>
            <div class="card" style="border-top-color: #024e22;">
                <h4>Citas para Hoy</h4>
                <p>Calendario</p>
            </div>
            <div class="card" style="border-top-color: #01771b;">
                <h4>Consultas Realizadas</h4>
                <p>Historial Clínico</p>
            </div>
        </div>
    </div>

</body>
</html>