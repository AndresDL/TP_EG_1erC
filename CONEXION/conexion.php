<?php

$host     = "localhost";
$usuario  = "root";       // Cambiar si tu usuario de MySQL es distinto
$clave    = "";           // Cambiar si tenés contraseña
$base     = "vuelaseguro";

$conn = new mysqli($host, $usuario, $clave, $base);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>