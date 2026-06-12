<?php

    include('../conexion.php');

    
    $loginQry = "SELECT emailUsuario FROM usuarios WHERE emailUsuario = ".$_GET['mail']."";

    if($stmt = mysqli_prepare($link, $loginQry)){

        mysqli_stmt_bind_param($stmt,"s", $_GET['mail']);

        mysqli_stmt_execute($stmt);

        mysqli_stmt_bind_result($stmt, $email);

        while (mysqli_stmt_fetch($stmt)){

            echo $email;

        }

        mysqli_stmt_close($stmt);

    } else {
        echo "Error en la preparación de la consulta: " . mysqli_error($link);
    }


?>