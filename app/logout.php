<?php
session_start();
session_unset(); // Borra las variables de sesión
session_destroy(); // Destruye la sesión por completo

// Redirige al login de inmediato
header("Location: index.php");
exit();