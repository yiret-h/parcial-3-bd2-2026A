<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }
require_once 'config/conexion.php';

$mensaje = "";

try {
    $conexion->exec("CREATE TABLE IF NOT EXISTS medicamento (
        id_medicamento INT NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(120) NOT NULL,
        principio_activo VARCHAR(100),
        categoria VARCHAR(60),
        presentacion VARCHAR(80),
        stock INT DEFAULT 0,
        unidad VARCHAR(30) DEFAULT 'unidades',
        descripcion TEXT,
        PRIMARY KEY (id_medicamento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch(PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear') {
    try {
        $sql = "INSERT INTO medicamento (nombre, principio_activo, categoria, presentacion, stock, unidad, descripcion)
                VALUES (:nom, :pa, :cat, :pres, :stock, :unidad, :desc)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nom'   => trim($_POST['nombre']),
            ':pa'    => trim($_POST['principio_activo']),
            ':cat'   => trim($_POST['categoria']),
            ':pres'  => trim($_POST['presentacion']),
            ':stock' => (int)$_POST['stock'],
            ':unidad'=> trim($_POST['unidad']),
            ':desc'  => trim($_POST['descripcion']),
        ]);
        $mensaje = "<div class='alert success'>&#9989; Medicamento agregado al catálogo.</div>";
    } catch(PDOException $e) {
        $mensaje = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'ajustar') {
    try {
        $stmt = $conexion->prepare("UPDATE medicamento SET stock = stock + :delta WHERE id_medicamento = :id");
        $stmt->execute([':delta' => (int)$_POST['delta'], ':id' => (int)$_POST['id_medicamento']]);
        $mensaje = "<div class='alert success'>&#9989; Stock actualizado.</div>";
    } catch(PDOException $e) {}
}

$medicamentos = [];
try {
    $medicamentos = $conexion->query("SELECT * FROM medicamento ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {}

$categorias = ['Antibiótico','Antiparasitario','Antiinflamatorio','Analgésico','Vitamina/Suplemento','Vacuna','Dermatológico','Digestivo','Otro'];

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario    = $_SESSION['rol'];
$titulo_pagina  = 'Catálogo de Medicamentos';
$pagina_activa  = 'medicamentos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Medicamentos - Huellitas</title>
    <link rel="stylesheet" href="huellitas-shared.css?v=4">
    <link rel="stylesheet" href="huellitas-layout.css?v=4">
    <style>
        .page-body { padding:30px 40px; }
        .layout { display:grid; grid-template-columns:340px 1fr; gap:24px; align-items:start; }

        .form-card { padding:24px; position:sticky; top:90px; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; margin-bottom:5px; font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:.3px; }
        .form-group input, .form-group select, .form-group textarea { width:100%; padding:9px 12px; border-radius:7px; font-size:13.5px; border: 2px solid var(--color-border); background: var(--input-bg); color: var(--color-text); }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
        .btn-submit { width:100%; padding:11px; background:var(--color-accent); color:#fff; border:none; border-radius:8px; font-weight:700; font-size:15px; cursor:pointer; transition:background .2s; }
        .btn-submit:hover { filter: brightness(0.9); }

        .toolbar { display:flex; align-items:center; gap:12px; margin-bottom:18px; flex-wrap:wrap; }
        .search-box { display:flex; align-items:center; border:2px solid var(--color-border); border-radius:8px; padding:0 12px; gap:8px; flex:1; background: var(--input-bg); }
        .search-box input { border:none; outline:none; padding:9px 0; font-size:13.5px; width:100%; background:transparent; color: var(--color-text); }
        .filter-cat { padding:9px 12px; border:2px solid var(--color-border); border-radius:8px; font-size:13px; outline:none; cursor:pointer; background: var(--input-bg); color: var(--color-text); }

        .med-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(260px,1fr)); gap:14px; }
        .med-card { border-radius:10px; padding:18px; border-top:4px solid var(--color-accent); }
        .med-card.bajo-stock { border-top-color:#e74c3c; }
        
        .cat-badge { display:inline-block; background:#e8f8f5; color:#1abc9c; padding:2px 9px; border-radius:10px; font-size:11px; font-weight:700; margin-bottom:10px; }
        .med-card.bajo-stock .cat-badge { background:#fadbd8; color:#e74c3c; }
        .pres-row { font-size:12.5px; margin-bottom:10px; }
        .stock-row { display:flex; align-items:center; gap:8px; margin-bottom:10px; }
        .stock-num { font-family:'Nunito',sans-serif; font-size:22px; font-weight:800; }
        .stock-label { font-size:11px; font-weight:700; text-transform:uppercase; color: var(--color-muted); }
        .stock-bajo { color:#e74c3c; }
        
        .stock-controls { display:flex; gap:6px; }
        .stock-btn { flex:1; padding:6px; border:2px solid var(--color-border); border-radius:6px; background:var(--input-bg); color: var(--color-text); cursor:pointer; font-size:14px; font-weight:700; transition: all .15s; }
        .stock-btn:hover { background:var(--color-hover); }

        .alert { padding:13px; border-radius:8px; font-weight:700; margin-bottom:18px; }
        .success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .error   { background:#fadbd8; color:#c0392b; border:1px solid #e74c3c; }
        .empty-msg { text-align:center; padding:50px; color:var(--color-muted); border-radius:10px; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <?php include 'topbar.php'; ?>

    <div class="page-body">
        <?= $mensaje ?>
        <div class="layout">
            <div class="form-card">
                <h4>&#129514; Agregar Medicamento</h4>
                <form method="POST">
                    <input type="hidden" name="accion" value="crear">
                    <div class="form-group">
                        <label>Nombre Comercial</label>
                        <input type="text" name="nombre" required placeholder="Ej: Amoxivet">
                    </div>
                    <div class="form-group">
                        <label>Principio Activo</label>
                        <input type="text" name="principio_activo" placeholder="Ej: Amoxicilina">
                    </div>
                    <div class="form-group">
                        <label>Categoría</label>
                        <select name="categoria">
                            <option value="">-- Selecciona --</option>
                            <?php foreach($categorias as $cat): ?>
                                <option><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Presentación</label>
                            <input type="text" name="presentacion" placeholder="Ej: 250mg tableta">
                        </div>
                        <div class="form-group">
                            <label>Stock inicial</label>
                            <input type="number" name="stock" min="0" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Unidad de medida</label>
                        <input type="text" name="unidad" placeholder="Ej: tabletas, frascos" value="unidades">
                    </div>
                    <div class="form-group">
                        <label>Descripción / Uso</label>
                        <textarea name="descripcion" placeholder="Para qué sirve..."></textarea>
                    </div>
                    <button type="submit" class="btn-submit">+ Agregar al Catálogo</button>
                </form>
            </div>

            <div>
                <div class="toolbar">
                    <div class="search-box">
                        <span>&#128269;</span>
                        <input type="text" id="buscador" placeholder="Buscar medicamento…" oninput="filtrar()">
                    </div>
                    <select class="filter-cat" id="filtro-cat" onchange="filtrar()">
                        <option value="">Todas las categorías</option>
                        <?php foreach($categorias as $cat): ?>
                            <option><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if(count($medicamentos) > 0): ?>
                <div class="med-grid" id="grid">
                    <?php foreach($medicamentos as $m):
                        $bajo = $m['stock'] <= 5;
                    ?>
                    <div class="med-card <?= $bajo?'bajo-stock':'' ?>"
                         data-buscar="<?= strtolower($m['nombre'].' '.$m['principio_activo'].' '.$m['categoria'].' '.$m['descripcion']) ?>"
                         data-cat="<?= $m['categoria'] ?>">
                        <div class="med-nombre"><?= htmlspecialchars($m['nombre']) ?></div>
                        <div class="med-pa"><?= htmlspecialchars($m['principio_activo']) ?></div>
                        <div><span class="cat-badge"><?= htmlspecialchars($m['categoria'] ?: 'Sin categoría') ?></span></div>
                        <div class="pres-row">&#128230; <?= htmlspecialchars($m['presentacion'] ?: 'N/A') ?></div>
                        <div class="stock-row">
                            <div>
                                <div class="stock-num <?= $bajo?'stock-bajo':'' ?>"><?= $m['stock'] ?></div>
                                <div class="stock-label"><?= htmlspecialchars($m['unidad']) ?> <?= $bajo?'&#9888; Stock bajo':'' ?></div>
                            </div>
                        </div>
                        <div class="stock-controls">
                            <form method="POST" style="display:contents">
                                <input type="hidden" name="accion" value="ajustar">
                                <input type="hidden" name="id_medicamento" value="<?= $m['id_medicamento'] ?>">
                                <input type="hidden" name="delta" value="-1">
                                <button class="stock-btn" type="submit" <?= $m['stock']<=0?'disabled':'' ?>>- Usar</button>
                            </form>
                            <form method="POST" style="display:contents">
                                <input type="hidden" name="accion" value="ajustar">
                                <input type="hidden" name="id_medicamento" value="<?= $m['id_medicamento'] ?>">
                                <input type="hidden" name="delta" value="1">
                                <button class="stock-btn" type="submit">+ Reponer</button>
                            </form>
                        </div>
                        <?php if(!empty($m['descripcion'])): ?>
                            <div style="font-size:12px;color:var(--color-muted);margin-top:8px;border-top:1px solid var(--color-border);padding-top:8px;"><?= htmlspecialchars(substr($m['descripcion'],0,80)) ?>…</div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <div class="empty-msg">&#129514; El catálogo está vacío. Agrega el primer medicamento.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src="huellitas-shared.js"></script>
<script>
function filtrar() {
    const texto = document.getElementById('buscador').value.toLowerCase();
    const cat   = document.getElementById('filtro-cat').value.toLowerCase();
    document.querySelectorAll('.med-card').forEach(c => {
        const matchTxt = c.dataset.buscar.includes(texto);
        const matchCat = !cat || (c.dataset.cat || '').toLowerCase() === cat;
        c.style.display = (matchTxt && matchCat) ? '' : 'none';
    });
}
</script>
</body>
</html>