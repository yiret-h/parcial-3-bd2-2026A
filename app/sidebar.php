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
        <li><a href="dashboard.php"    <?= $pagina_activa==='dashboard'   ?'class="active"':'' ?>><span class="icon">&#127968;</span> Inicio</a></li>
        <div class="sidebar-divider"></div>
        <li><a href="dueños.php"       <?= $pagina_activa==='dueños'      ?'class="active"':'' ?>><span class="icon">&#128062;</span> Dueños y Mascotas</a></li>
        <li><a href="citas.php"        <?= $pagina_activa==='citas'       ?'class="active"':'' ?>><span class="icon">&#128197;</span> Agenda / Citas</a></li>
        <li><a href="consultas.php"    <?= $pagina_activa==='consultas'   ?'class="active"':'' ?>><span class="icon">&#129658;</span> Consultas Médicas</a></li>
        <li><a href="reportes.php"     <?= $pagina_activa==='reportes'    ?'class="active"':'' ?>><span class="icon">&#128202;</span> Reportes y Estadísticas</a></li>
    </ul>
</div>