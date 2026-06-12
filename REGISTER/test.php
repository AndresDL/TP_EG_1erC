<?php

    include('../conexion.php');
    $qryRegister = 'INSERT INTO usuarios VALUES (NULL,'".$_POST["nombreUsuario"]')'
    $qry = mysqli_query($link,'SELECT * FROM usuarios');
    $arr = mysqli_fetch_array($qry);
    echo $_POST['nombre'], $arr['nombreUsuario'];


?>
