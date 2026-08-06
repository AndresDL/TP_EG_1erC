<?php
session_start();
$link = null;
include_once('../conexion.inc');

if (!$link) {
    die("Error de conexión a la base de datos.");
}

// Verificar si el usuario es admin
if (!isset($_SESSION['tipoUsuario'] ) || $_SESSION['tipoUsuario'] != 'admin') {
    header("Location: ../INDEX/index.php");
    exit();
}

$mensaje = "";
$tipo_mensaje = "danger"; 

// ─── LÓGICA DE PROCESAMIENTO (CRUD) ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['crear'])) {
        if (empty($_POST['titulo']) || empty($_POST['descripcion']) || empty($_POST['fecha_expiracion']) || empty($_POST['tipo_novedad'])) {
            $mensaje = "Por favor, complete todos los campos para crear la novedad.";
        } elseif (strtotime($_POST['fecha_expiracion']) < strtotime(date('Y-m-d'))) {
            $mensaje = "La fecha de expiración no puede ser anterior a la fecha actual.";
        } elseif (strlen($_POST['titulo']) > 50) {
            $mensaje = "El título no puede tener más de 50 caracteres.";
        } else {
            $titulo = mysqli_real_escape_string($link, $_POST['titulo']);
            $descripcion = mysqli_real_escape_string($link, $_POST['descripcion']);
            $fecha_publicacion = date('Y-m-d');
            $fecha_expiracion = $_POST['fecha_expiracion'];
            $tipo_novedad = $_POST['tipo_novedad']; // 'Alerta', 'Importante', 'Informativa'
            
            $sql = "INSERT INTO novedades (TituloNovedad, textoNovedad, fechaPublicacionNovedad, fechaExpiracionNovedad, tipoNovedad) 
                    VALUES ('$titulo', '$descripcion', '$fecha_publicacion', '$fecha_expiracion', '$tipo_novedad')";
            
            if(mysqli_query($link, $sql)){
                $mensaje = "Novedad creada exitosamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al crear la novedad: " . mysqli_error($link);
            }
        }
    }

    if (!empty($_POST['editar'])) {
        $id = (int)$_POST['id'];
        if (empty($_POST['titulo']) || empty($_POST['descripcion']) || empty($_POST['fecha_expiracion']) || empty($_POST['tipo_novedad'])) {
            $mensaje = "Por favor, complete todos los campos para editar la novedad.";
        } elseif (strlen($_POST['titulo']) > 50) {
            $mensaje = "El título no puede tener más de 50 caracteres.";
        } else {
            $titulo = mysqli_real_escape_string($link, $_POST['titulo']);
            $descripcion = mysqli_real_escape_string($link, $_POST['descripcion']);
            $fecha_publicacion = $_POST['fecha_publicacion'];
            $fecha_expiracion = $_POST['fecha_expiracion'];
            $tipo_novedad = $_POST['tipo_novedad'];
            
            $sql = "UPDATE novedades SET TituloNovedad='$titulo', textoNovedad='$descripcion', 
                    fechaPublicacionNovedad='$fecha_publicacion', fechaExpiracionNovedad='$fecha_expiracion', tipoNovedad='$tipo_novedad' 
                    WHERE codNovedad=$id";
            
            if(mysqli_query($link, $sql)){
                $mensaje = "Novedad actualizada exitosamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al actualizar la novedad: " . mysqli_error($link);
            }
        }
    }

    if (!empty($_POST['eliminar'])) {
        $id = (int)$_POST['id'];
        $sql_delete = "DELETE FROM novedades WHERE codNovedad=$id";
        if(mysqli_query($link, $sql_delete)){
            $mensaje = "Novedad eliminada exitosamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al eliminar la novedad: " . mysqli_error($link);
        }
    }
}

// ─── OBTENER TODAS LAS NOVEDADES ─────────────────────────────────────────────
$query_novedades = "SELECT * FROM novedades ORDER BY fechaPublicacionNovedad DESC";
$resultado_novedades = mysqli_query($link, $query_novedades);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Novedades - Admin VuelaSeguro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="../INDEX/index.php">VuelaSeguro Admin</a>
        <div class="d-flex">
            <span class="navbar-text me-3">
                Hola, <?= htmlspecialchars($_SESSION['usuario']['nombreUsuario']) ?>
            </span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">Salir</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Novedades</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="bi bi-plus-circle me-1"></i> Crear Novedad
        </button>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Publicación</th>
                            <th>Expiración</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($resultado_novedades) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($resultado_novedades)): ?>
                                <?php 
                                    $hoy = date('Y-m-d');
                                    $vigente = ($row['fechaExpiracionNovedad'] >= $hoy);
                                ?>
                                <tr>
                                    <td><?= $row['codNovedad'] ?></td>
                                    <td class="text-start"><?= htmlspecialchars($row['TituloNovedad']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $row['tipoNovedad'] == 'Alerta' ? 'warning text-dark' : ($row['tipoNovedad'] == 'Importante' ? 'primary' : 'success') ?>">
                                            <?= $row['tipoNovedad'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($row['fechaPublicacionNovedad'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['fechaExpiracionNovedad'])) ?></td>
                                    <td>
                                        <?php if ($vigente): ?>
                                            <span class="badge bg-success">Vigente</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Expirada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $row['codNovedad'] ?>" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar<?= $row['codNovedad'] ?>" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <div class="modal fade text-start" id="modalEditar<?= $row['codNovedad'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST" action="">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">Modificar Novedad #<?= $row['codNovedad'] ?></h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $row['codNovedad'] ?>">
                                                    <input type="hidden" name="fecha_publicacion" value="<?= $row['fechaPublicacionNovedad'] ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Título</label>
                                                        <input type="text" class="form-control" name="titulo" value="<?= htmlspecialchars($row['TituloNovedad']) ?>" maxlength="50" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Tipo de Novedad</label>
                                                        <select class="form-select" name="tipo_novedad" required>
                                                            <option value="Informativa" <?= $row['tipoNovedad'] == 'Informativa' ? 'selected' : '' ?>>Informativa</option>
                                                            <option value="Importante" <?= $row['tipoNovedad'] == 'Importante' ? 'selected' : '' ?>>Importante</option>
                                                            <option value="Alerta" <?= $row['tipoNovedad'] == 'Alerta' ? 'selected' : '' ?>>Alerta</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Descripción</label>
                                                        <textarea class="form-control" name="descripcion" rows="4" required><?= htmlspecialchars($row['textoNovedad']) ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Fecha de Expiración</label>
                                                        <input type="date" class="form-control" name="fecha_expiracion" value="<?= $row['fechaExpiracionNovedad'] ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <input type="submit" name="editar" class="btn btn-primary" value="Guardar Cambios">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="modal fade text-start" id="modalEliminar<?= $row['codNovedad'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST" action="">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Eliminar Novedad</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $row['codNovedad'] ?>">
                                                    <p>¿Estás seguro que deseas eliminar la novedad <strong>"<?= htmlspecialchars($row['TituloNovedad']) ?>"</strong>?</p>
                                                    <p class="text-danger small">Esta acción no se puede deshacer.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <input type="submit" name="eliminar" class="btn btn-danger" value="Eliminar">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">No hay novedades registradas en el sistema.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Crear Nueva Novedad</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Título (máx 50 carac.)</label>
                        <input type="text" class="form-control" name="titulo" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Novedad</label>
                        <select class="form-select" name="tipo_novedad" required>
                            <option value="" disabled selected>Seleccione un tipo...</option>
                            <option value="Informativa">Informativa</option>
                            <option value="Importante">Importante</option>
                            <option value="Alerta">Alerta</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de Expiración</label>
                        <input type="date" class="form-control" name="fecha_expiracion" min="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <input type="submit" name="crear" class="btn btn-success" value="Crear Novedad">
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php 
if($link) {
    mysqli_close($link);
}
?>