<?php

    
    include('../conexion.php');

    $hashedPassword = password_hash($_POST['clave'], PASSWORD_BCRYPT);

    $stmt = mysqli_prepare($link, 
    'INSERT INTO usuarios VALUES (NULL, ?, ?, "USUARIO", ?, ?)');

    mysqli_stmt_bind_param($stmt, 'ssss',
    $_POST['nombre'],
    $hashedPassword,
    $_POST['email'],
    $_POST['telefono']
    );

    mysqli_stmt_execute($stmt);
    
    mysqli_stmt_close($stmt);   


?>