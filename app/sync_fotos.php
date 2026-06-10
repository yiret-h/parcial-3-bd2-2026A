<?php
require_once 'config/conexion.php';

$dir = 'imagenes/mascotas/';
if (!is_dir($dir)) {
    echo "Dir not found";
    exit;
}

$fotos = glob($dir . '*.*');
$count = 0;
foreach ($fotos as $foto) {
    $filename = basename($foto);
    // Extraer id_mascota (ej: "1_123456.jpg")
    if (preg_match('/^(\d+)_/', $filename, $matches)) {
        $id_mascota = $matches[1];
        
        // Comprobar si ya existe en la DB
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM fotografia_mascota WHERE ruta_archivo = :ruta");
        $stmt->execute([':ruta' => $filename]);
        if ($stmt->fetchColumn() == 0) {
            $insert = $conexion->prepare("INSERT INTO fotografia_mascota (ruta_archivo, id_mascota) VALUES (:ruta, :id)");
            $insert->execute([':ruta' => $filename, ':id' => $id_mascota]);
            $count++;
        }
    }
}
echo "Sincronizadas $count fotos a la base de datos.";
