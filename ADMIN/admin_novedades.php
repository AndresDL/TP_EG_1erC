<?php
session_start();
require_once('../conexion.php');

// Verificar si el usuario es admin
if (!isset($_SESSION['tipoUsuario']) || $_SESSION['tipoUsuario'] != 'admin') {
    header("Location: ../INDEX/index.php");
    exit();
}
if(!empty($_POST['crear']) || !empty($_POST['editar']) || !empty($_POST['eliminar'])){
    if(!empty($_POST['crear'])){
        $titulo = $_POST['titulo'];
        $descripcion = $_POST['descripcion'];
        $fecha = $_POST['fecha'];
        $sql = "INSERT INTO novedades (titulo, descripcion, fecha) VALUES ('$titulo', '$descripcion', '$fecha')";
        mysqli_query($link, $sql);
    }
    if(!empty($_POST['editar'])){
        $id = $_POST['id'];
        $titulo = $_POST['titulo'];
        $descripcion = $_POST['descripcion'];
        $fecha = $_POST['fecha'];
        $sql = "UPDATE novedades SET titulo='$titulo', descripcion='$descripcion', fecha='$fecha' WHERE id=$id";
        mysqli_query($link, $sql);
    }
    if(!empty($_POST['eliminar'])){
        $id = $_POST['id'];
        $sql = "DELETE FROM novedades WHERE id=$id";
        mysqli_query($link, $sql);
    }
}