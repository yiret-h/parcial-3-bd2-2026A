<?php
// includes/topbar.php
// Variables esperadas: $titulo_pagina, $nombre_usuario, $rol_usuario
$titulo_pagina  = $titulo_pagina  ?? 'Panel';
$nombre_usuario = $nombre_usuario ?? 'Usuario';
$rol_usuario    = $rol_usuario    ?? '';
$inicial        = strtoupper(substr($nombre_usuario, 0, 1));
?>
<div class="top-header">
    <h2><?= htmlspecialchars($titulo_pagina) ?></h2>

    <div class="user-menu-wrapper">
        <div class="user-menu-trigger">
            <div class="user-avatar"><?= $inicial ?></div>
            <div class="user-menu-info">
                <div class="user-name"><?= htmlspecialchars($nombre_usuario) ?></div>
                <div class="user-sub"><?= htmlspecialchars($rol_usuario) ?></div>
            </div>
            <span class="user-caret">▼</span>
        </div>

        <div class="user-dropdown">
            <div class="dropdown-header">
                <div class="dh-name"><?= htmlspecialchars($nombre_usuario) ?></div>
                <div class="dh-rol"><?= htmlspecialchars($rol_usuario) ?></div>
            </div>

            <!-- Toggle modo nocturno -->
            <button class="dropdown-item toggle-dark" id="darkModeToggle">
                <span style="display:flex;align-items:center;gap:8px;">
                    <span class="di-icon">🌙</span> Modo nocturno
                </span>
                <div class="toggle-switch"></div>
            </button>

            <div class="dropdown-divider"></div>

            <!-- Cerrar sesión -->
            <a href="logout.php" class="dropdown-item logout-item">
                <span class="di-icon">🚪</span> Cerrar sesión
            </a>
        </div>
    </div>
</div>