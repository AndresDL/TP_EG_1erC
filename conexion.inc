<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['path' => '/']);
    session_start();
}

$host     = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$database = getenv('DB_NAME');
$port     = (int) getenv('DB_PORT');

mysqli_report(MYSQLI_REPORT_OFF);

$link = mysqli_connect($host, $username, $password, $database, $port);

if (!$link) {
    die("Error al conectar a la base de datos: " . mysqli_connect_error() . " (Code: " . mysqli_connect_errno() . ")");
}

$db = mysqli_select_db($link, $database);
if (!$db) {
    die("Error al seleccionar la base de datos: " . mysqli_error($link));
}

mysqli_set_charset($link, 'utf8');
?>
