<?php

    $link = null;

    include_once('../conexion.inc');

    if (!$link) {
        die("Error de conexión a la base de datos.");
    };

   session_destroy();

   header('Location: ../INDEX/index.php');

?>