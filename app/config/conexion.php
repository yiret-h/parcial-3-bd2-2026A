<?php
$host = 'localhost';
$dbname = 'clinica_veterinaria';
$username = 'admin_vet'; 
$password = '1096203233'; 
try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>