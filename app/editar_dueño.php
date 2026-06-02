<?php
session_start();

// Validar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/conexion.php';

// Verificar que los datos vengan por POST (cuando se envía el formulario)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recibir y limpiar los datos enviados desde el modal
    $id_dueño  = (int)$_POST['id_dueño'];
    $documento = trim($_POST['documento']);
    $nombre    = trim($_POST['nombre']);
    $telefono  = trim($_POST['telefono']);
    $email     = trim($_POST['email']);
    $direccion = trim($_POST['direccion']);

    try {
        // Preparar la consulta UPDATE
        $sql = "UPDATE dueño 
                SET documento_identidad = :doc, 
                    nombre = :nombre, 
                    telefono = :telefono, 
                    email = :email, 
                    direccion = :direccion 
                WHERE id_dueño = :id";
        
        $stmt = $conexion->prepare($sql);
        
        // Ejecutar la consulta con los datos seguros
        $stmt->execute([
            ':doc'       => $documento,
            ':nombre'    => $nombre,
            ':telefono'  => $telefono,
            ':email'     => $email,
            ':direccion' => $direccion,
            ':id'        => $id_dueño
        ]);

        // Redirigir de vuelta a la página principal de dueños para que se cierre el modal
        header("Location: dueños.php");
        exit();

    } catch(PDOException $e) {
        // Si hay un error (ej. documento duplicado), lo mostramos temporalmente
        echo "Error al actualizar los datos: " . $e->getMessage();
        echo "<br><a href='dueños.php'>Volver</a>";
        exit();
    }
} else {
    // Si alguien intenta entrar a este archivo directamente por la URL, lo devolvemos
    header("Location: dueños.php");
    exit();
}
?>