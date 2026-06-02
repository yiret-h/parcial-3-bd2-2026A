<?php
require_once 'config/conexion.php';
$stmt = $conexion->query("SELECT id_cita AS id, motivo_previo AS title, fecha_hora AS start FROM cita");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>