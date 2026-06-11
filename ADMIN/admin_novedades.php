<?php
$link = null;
include_once('../conexion.inc');
if (!$link) {
    die("Error de conexión a la base de datos.");
}

// Verificar si el usuario es admin
if (!isset($_SESSION['tipoUsuario']) || $_SESSION['tipoUsuario'] != 'admin') {
    header("Location: ../INDEX/index.php");
    exit();
}
if (!empty($_POST['crear']) || !empty($_POST['editar']) || !empty($_POST['eliminar'])) {
    if (!empty($_POST['crear'])) {
        if (empty($_POST['titulo']) || empty($_POST['descripcion']) || empty($_POST['fecha_expiracion']) || empty($_POST['tipo_novedad'])) {
            $mensaje = "Por favor, complete todos los campos para crear la novedad.";
        } elseif (strtotime($_POST['fecha_expiracion']) < strtotime(date('Y-m-d'))) {
            $mensaje = "La fecha de expiración no puede ser anterior a la fecha actual.";
        } elseif (strtotime($_POST['fecha_expiracion']) < strtotime($_POST['fecha_publicacion'])) {
            $mensaje = "La fecha de expiración no puede ser anterior a la fecha de publicación.";
        } elseif (strlen($_POST['titulo']) > 50) {
            $mensaje = "El título no puede tener más de 50 caracteres.";
         }
        else {
            $titulo = $_POST['titulo'];
            $descripcion = $_POST['descripcion'];
            $fecha_publicacion = date('Y-m-d');
            $fecha_expiracion = $_POST['fecha_expiracion'];
            $tipo_novedad = $_POST['tipo_novedad'];
            $sql = "INSERT INTO novedades (TituloNovedad, textoNovedad, fechaPublicacionNovedad, fechaExpiracionNovedad, tipoNovedad) VALUES ('$titulo', '$descripcion', '$fecha_publicacion', '$fecha_expiracion', '$tipo_novedad')";
            $result = mysqli_query($link, $sql);
        }
    }

    if (!empty($_POST['editar'])) {
        $id = $_POST['id'];
        $query = "SELECT * FROM novedades WHERE codNovedad=$id";
        $result = mysqli_query($link, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            if (empty($_POST['titulo']) || empty($_POST['descripcion']) || empty($_POST['fecha_expiracion']) || empty($_POST['tipo_novedad'])) {
                $mensaje = "Por favor, complete todos los campos para editar la novedad.";
            } elseif (strtotime($_POST['fecha_expiracion']) < strtotime(date('Y-m-d'))) {
                $mensaje = "La fecha de expiración no puede ser anterior a la fecha actual.";
            } elseif (strtotime($_POST['fecha_expiracion']) < strtotime($_POST['fecha_publicacion'])) {
                $mensaje = "La fecha de expiración no puede ser anterior a la fecha de publicación.";
            } elseif (strlen($_POST['titulo']) > 50) {
                $mensaje = "El título no puede tener más de 50 caracteres.";
                } else {
                $titulo = $_POST['titulo'];
                $descripcion = $_POST['descripcion'];
                $fecha_publicacion = $_POST['fecha_publicacion'];
                $fecha_expiracion = $_POST['fecha_expiracion'];
                $tipo_novedad = $_POST['tipo_novedad'];
                $sql = "UPDATE novedades SET TituloNovedad='$titulo', textoNovedad='$descripcion', fechaPublicacionNovedad='$fecha_publicacion', fechaExpiracionNovedad='$fecha_expiracion', tipoNovedad='$tipo_novedad' WHERE codNovedad=$id";
                $result = mysqli_query($link, $sql);
            }
        } else {
            $mensaje = "No se encontró la novedad a editar.";
        }
    }

    if (!empty($_POST['eliminar'])) {
        $id = $_POST['id'];
        $sql = "SELECT * FROM novedades WHERE codNovedad=$id";
        $result = mysqli_query($link, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $novedad = mysqli_fetch_assoc($result);
            if ($novedad['tipoNovedad'] == 'promocion') {
                $sql_delete_promocion = "DELETE FROM promociones WHERE codNovedad=$id";
                mysqli_query($link, $sql_delete_promocion);
            }
            $sql_delete_novedad = "DELETE FROM novedades WHERE codNovedad=$id";
            mysqli_query($link, $sql_delete_novedad);
        } else {
            $mensaje = "No se encontró la novedad a eliminar.";
        }
    }
}
?>
