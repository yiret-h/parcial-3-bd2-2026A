<?php
// includes/sidebar.php
// Variables esperadas: $pagina_activa (ej: 'dashboard', 'dueños', 'citas', etc.)
$pagina_activa = $pagina_activa ?? '';
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3>Huellitas</h3>
        <p>Clínica Veterinaria</p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"    <?= $pagina_activa==='dashboard'   ?'class="active"':'' ?>><span class="icon">🏠</span> Inicio</a></li>
        <div class="sidebar-divider"></div>
        <li><a href="dueños.php"       <?= $pagina_activa==='dueños'      ?'class="active"':'' ?>><span class="icon">🐾</span> Dueños y Mascotas</a></li>
        <li><a href="citas.php"        <?= $pagina_activa==='citas'       ?'class="active"':'' ?>><span class="icon">📅</span> Agenda / Citas</a></li>
        <li><a href="consultas.php"    <?= $pagina_activa==='consultas'   ?'class="active"':'' ?>><span class="icon">🩺</span> Consultas Médicas</a></li>
        <div class="sidebar-divider"></div>
        <li><a href="tratamientos.php" <?= $pagina_activa==='tratamientos'?'class="active"':'' ?>><span class="icon">💊</span> Tratamientos</a></li>
        <li><a href="medicamentos.php" <?= $pagina_activa==='medicamentos'?'class="active"':'' ?>><span class="icon">🧪</span> Catálogo Medicamentos</a></li>
        <li><a href="reportes.php"     <?= $pagina_activa==='reportes'    ?'class="active"':'' ?>><span class="icon">📊</span> Reportes y Estadísticas</a></li>
    </ul>
</div>