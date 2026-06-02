<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

$lista_dueños = [];
$error_db = "";

try {
    $sql = "SELECT d.id_dueño, d.documento_identidad, d.nombre, d.telefono, d.email, d.direccion,
                   GROUP_CONCAT(CONCAT(m.id_mascota,':',m.nombre,':',IFNULL(e.nombre,'?')) SEPARATOR '|') AS lista_mascotas
            FROM dueño d
            LEFT JOIN mascota m   ON d.id_dueño    = m.id_dueño
            LEFT JOIN raza r      ON m.id_raza      = r.id_raza
            LEFT JOIN especie e   ON r.id_especie   = e.id_especie
            GROUP BY d.id_dueño
            ORDER BY d.nombre ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $lista_dueños = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_db = "Error: " . $e->getMessage();
}

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario    = $_SESSION['rol'];
$titulo_pagina  = 'Dueños y Mascotas';
$pagina_activa  = 'dueños';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dueños y Mascotas - Huellitas</title>
    <link rel="stylesheet" href="huellitas-shared.css">
    <link rel="stylesheet" href="huellitas-layout.css">
    <style>
        /* Layout 2 columnas */
        .split-layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 22px;
            align-items: start;
        }

        /* Tabla dueños */
        .table-container { background:var(--bg-card); border-radius:10px; box-shadow:var(--shadow-card); overflow:hidden; }
        table { width:100%; border-collapse:collapse; }
        th  { background:var(--table-header,#22773c); color:white; text-align:left; padding:13px 15px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; }
        td  { padding:13px 15px; border-bottom:1px solid var(--color-border); color:var(--color-text); font-size:13.5px; }
        tr.data-row:hover td { background:var(--color-hover); }
        tr:last-child td { border-bottom:none; }

        .mascota-tag {
            display:inline-block; background:#e8f5e9; color:#2e7d32;
            padding:3px 9px; border-radius:5px; font-weight:700; font-size:12px;
            border:1px solid #c8e6c9; text-decoration:none; transition:background .2s; margin:2px;
        }
        .mascota-tag:hover { background:#c8e6c9; }
        .actions-cell { display:flex; gap:6px; flex-wrap:wrap; }

        /* Panel mascotas lado derecho */
        .pets-panel {
            background: var(--bg-card);
            border-radius: 12px;
            box-shadow: var(--shadow-card);
            padding: 20px;
            position: sticky;
            top: 80px;
            max-height: calc(100vh - 110px);
            overflow-y: auto;
        }
        .pets-panel h4 {
            font-family:'Nunito',sans-serif; font-size:16px; font-weight:800;
            color:var(--color-text); margin-bottom:14px;
            padding-bottom:10px; border-bottom:2px solid var(--color-border);
            display:flex; align-items:center; gap:8px;
        }
        .pet-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 12px; border-radius: 9px;
            text-decoration: none; transition: background .2s;
            margin-bottom: 6px;
            border: 1px solid var(--color-border);
            background: var(--bg-main);
        }
        .pet-item:hover { background: var(--color-hover); border-color: var(--color-accent); }
        .pet-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: linear-gradient(135deg,#22773c,#3dba4e);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }
        .pet-info .pet-name { font-weight:800; font-size:14px; color:var(--color-text); font-family:'Nunito',sans-serif; }
        .pet-info .pet-owner { font-size:12px; color:var(--color-muted); margin-top:1px; }
        .pet-info .pet-especie { font-size:11px; color:var(--color-accent); font-weight:700; margin-top:1px; }
        .pet-arrow { margin-left:auto; color:var(--color-muted); font-size:13px; }

        .empty-msg-panel { text-align:center; padding:30px 10px; color:var(--color-muted); font-size:13px; }

        /* Modal editar */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:200; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal { background:var(--bg-card); border-radius:14px; padding:30px; width:100%; max-width:480px; box-shadow:0 20px 60px rgba(0,0,0,.25); position:relative; }
        .modal h3 { font-family:'Nunito',sans-serif; font-size:19px; font-weight:800; color:var(--color-text); margin-bottom:20px; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; margin-bottom:5px; font-weight:700; font-size:12px; color:var(--color-muted); text-transform:uppercase; }
        .form-group input { width:100%; padding:10px 12px; border:2px solid var(--color-border); border-radius:7px; font-size:13.5px; font-family:'Lato',sans-serif; outline:none; background:var(--input-bg); color:var(--color-text); transition:border-color .2s; }
        .form-group input:focus { border-color:#22773c; }
        .modal-footer { display:flex; gap:10px; margin-top:20px; }
        .modal-close { position:absolute; top:14px; right:18px; font-size:22px; cursor:pointer; color:var(--color-muted); background:none; border:none; }
        .btn-sm { font-size:12px; padding:6px 12px; }

        .alerta-error { background:#fadbd8; color:#c0392b; padding:14px; border-radius:8px; margin-bottom:18px; border:1px solid #e74c3c; font-weight:700; }
        .empty-state  { text-align:center; padding:40px; color:var(--color-muted); }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <?php include 'topbar.php'; ?>

    <div class="page-body">
        <?php if($error_db): ?><div class="alerta-error"><?= $error_db ?></div><?php endif; ?>

        <div class="toolbar">
            <div class="search-box">
                <span>🔍</span>
                <input type="text" id="buscador" placeholder="Buscar por nombre, documento o mascota…" oninput="filtrarTabla()">
            </div>
            <a href="nuevo_dueño.php"  class="btn btn-primary">+ Añadir Dueño</a>
            <a href="nueva_mascota.php" class="btn btn-orange">🐶 Nueva Mascota</a>
        </div>

        <div class="split-layout">
            <!-- Tabla dueños -->
            <div>
                <div class="table-container">
                    <table id="tabla-dueños">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Mascotas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(count($lista_dueños)>0): ?>
                            <?php foreach($lista_dueños as $d): ?>
                            <tr class="data-row">
                                <td><?= htmlspecialchars($d['documento_identidad']) ?></td>
                                <td><strong><?= htmlspecialchars($d['nombre']) ?></strong></td>
                                <td><?= htmlspecialchars($d['telefono']) ?></td>
                                <td>
                                    <?php if(!empty($d['lista_mascotas'])): ?>
                                        <?php foreach(explode('|',$d['lista_mascotas']) as $m):
                                            [$mid,$mnombre] = explode(':',$m,2);
                                            $mnombre = explode(':',$mnombre)[0]; ?>
                                            <a href="perfil_mascota.php?id=<?= $mid ?>" class="mascota-tag">🐾 <?= htmlspecialchars($mnombre) ?></a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <em style="color:var(--color-muted)">Sin mascotas</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="nueva_mascota.php?id_dueño=<?= $d['id_dueño'] ?>" class="btn btn-blue btn-sm">+ Mascota</a>
                                        <button class="btn btn-edit btn-sm"
                                            onclick="abrirEdicion(
                                                <?= $d['id_dueño'] ?>,
                                                '<?= addslashes($d['documento_identidad']) ?>',
                                                '<?= addslashes($d['nombre']) ?>',
                                                '<?= addslashes($d['telefono']) ?>',
                                                '<?= addslashes($d['email']??'') ?>',
                                                '<?= addslashes($d['direccion']??'') ?>'
                                            )">✏️ Editar</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5"><div class="empty-state">🐾 No hay clientes aún.</div></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Panel mascotas -->
            <div class="pets-panel">
                <h4>🐾 Mascotas registradas</h4>
                <?php
                $todas_mascotas = [];
                foreach($lista_dueños as $d) {
                    if(!empty($d['lista_mascotas'])) {
                        foreach(explode('|',$d['lista_mascotas']) as $m) {
                            $partes = explode(':',$m,3);
                            if(count($partes)>=2) {
                                $todas_mascotas[] = ['id'=>$partes[0],'nombre'=>$partes[1],'especie'=>$partes[2]??'','dueño'=>$d['nombre']];
                            }
                        }
                    }
                }
                ?>
                <?php if(count($todas_mascotas)>0): ?>
                    <?php foreach($todas_mascotas as $pm):
                        $emoji = str_contains(strtolower($pm['especie']),'gato')?'🐱':(str_contains(strtolower($pm['especie']),'perro')?'🐶':'🐾');
                    ?>
                    <a href="perfil_mascota.php?id=<?= $pm['id'] ?>" class="pet-item">
                        <div class="pet-avatar"><?= $emoji ?></div>
                        <div class="pet-info">
                            <div class="pet-name"><?= htmlspecialchars($pm['nombre']) ?></div>
                            <div class="pet-owner">Dueño: <?= htmlspecialchars($pm['dueño']) ?></div>
                            <div class="pet-especie"><?= htmlspecialchars($pm['especie']) ?></div>
                        </div>
                        <div class="pet-arrow">→</div>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-msg-panel">No hay mascotas registradas aún.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Dueño -->
<div class="modal-overlay" id="modalEditar">
    <div class="modal">
        <button class="modal-close" onclick="cerrarModal()">✕</button>
        <h3>✏️ Editar Dueño</h3>
        <form method="POST" action="editar_dueño.php">
            <input type="hidden" name="id_dueño" id="edit_id">
            <div class="form-group"><label>Documento</label><input type="text" name="documento" id="edit_doc" required></div>
            <div class="form-group"><label>Nombre Completo</label><input type="text" name="nombre" id="edit_nombre" required></div>
            <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" id="edit_telefono" required></div>
            <div class="form-group"><label>Correo</label><input type="email" name="email" id="edit_email"></div>
            <div class="form-group"><label>Dirección</label><input type="text" name="direccion" id="edit_direccion"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script src="huellitas-shared.js"></script>
<script>
function filtrarTabla() {
    const t = document.getElementById('buscador').value.toLowerCase();
    document.querySelectorAll('#tabla-dueños tbody tr.data-row').forEach(f => {
        f.style.display = f.textContent.toLowerCase().includes(t) ? '' : 'none';
    });
}
function abrirEdicion(id,doc,nombre,tel,email,dir) {
    document.getElementById('edit_id').value=id;
    document.getElementById('edit_doc').value=doc;
    document.getElementById('edit_nombre').value=nombre;
    document.getElementById('edit_telefono').value=tel;
    document.getElementById('edit_email').value=email;
    document.getElementById('edit_direccion').value=dir;
    document.getElementById('modalEditar').classList.add('open');
}
function cerrarModal() { document.getElementById('modalEditar').classList.remove('open'); }
document.getElementById('modalEditar').addEventListener('click',function(e){ if(e.target===this) cerrarModal(); });
</script>
</body>
</html>