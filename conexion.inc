<?php
//CONEXIÓN DB 
session_start();
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'vuelaseguro';
$link = mysqli_connect($host, $username, $password, $database) or die ("Error al conectar a la base de datos: " . mysqli_connect_error());
$db = mysqli_select_db($link, $database) or die ("Error al seleccionar la base de datos: " . mysqli_error($link));
mysqli_set_charset($link, 'utf8'); // para problemas con acentos
?>