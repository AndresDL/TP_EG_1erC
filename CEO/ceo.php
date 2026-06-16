<?php
// Usamos la ruta absoluta directa al htdocs de XAMPP para no errarle jamás
include_once($_SERVER['DOCUMENT_ROOT'] . '/TP_EG_1erC/VUELOS/conexion.php');

// Validamos que la conexión se haya realizado con la variable $link de tu archivo
if (!isset($link) || !$link) {
    die("Error de conexión interno del sistema.");
}

// Estructura de sesión obligatoria para simular el rol de CEO
if (!isset($_SESSION['usuario'])) {
    $_SESSION['usuario'] = [
        'nombreUsuario' => 'Mateo',
        'tipoUsuario' => 'CEO',
        'codAerolinea' => 1
    ];
}

$codAerolineaCEO = $_SESSION['usuario']['codAerolinea'];
$mensaje = "";
$tipo_mensaje = "danger"; 

// ─── LÓGICA DE PROCESAMIENTO (ALTA, MODIFICACIÓN Y BAJA) ──────────────────────
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. CREAR VUELO
    if (!empty($_POST['crear'])) {
        if (empty($_POST['origen']) || empty($_POST['destino']) || empty($_POST['fecha']) || empty($_POST['hora']) || empty($_POST['precio']) || empty($_POST['asientos'])) {
            $mensaje = "Por favor, complete todos los campos para crear el vuelo.";
        } elseif (strtotime($_POST['fecha']) < strtotime(date('Y-m-d'))) {
            $mensaje = "La fecha del vuelo no puede ser anterior a la fecha actual.";
        } else {
            $origen = mysqli_real_escape_string($link, $_POST['origen']);
            $destino = mysqli_real_escape_string($link, $_POST['destino']);
            $fecha = $_POST['fecha'];
            $hora = $_POST['hora'];
            $precio = (float)$_POST['precio'];
            $asientos = (int)$_POST['asientos'];

            $sql = "INSERT INTO VUELOS (origenVuelo, destinoVuelo, fechaSalidaVuelo, horaSalidaVuelo, precioVuelo, asientosDisponibles, codAerolinea) 
                    VALUES ('$origen', '$destino', '$fecha', '$hora', '$precio', '$asientos', $codAerolineaCEO)";
            
            if(mysqli_query($link, $sql)){
                $mensaje = "Vuelo creado exitosamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al crear el vuelo: " . mysqli_error($link);
            }
        }
    }

    // 2. EDITAR VUELO
    if (!empty($_POST['editar'])) {
        $id = (int)$_POST['id'];
        if (empty($_POST['origen']) || empty($_POST['destino']) || empty($_POST['fecha']) || empty($_POST['hora']) || empty($_POST['precio']) || empty($_POST['asientos'])) {
            $mensaje = "Por favor, complete todos los campos.";
        } else {
            $origen = mysqli_real_escape_string($link, $_POST['origen']);
            $destino = mysqli_real_escape_string($link, $_POST['destino']);
            $fecha = $_POST['fecha'];
            $hora = $_POST['hora'];
            $precio = (float)$_POST['precio'];
            $asientos = (int)$_POST['asientos'];

            $sql = "UPDATE VUELOS SET origenVuelo='$origen', destinoVuelo='$destino', 
                    fechaSalidaVuelo='$fecha', horaSalidaVuelo='$hora', precioVuelo='$precio', asientosDisponibles='$asientos' 
                    WHERE codVuelo=$id AND codAerolinea=$codAerolineaCEO";
            
            if(mysqli_query($link, $sql)){
                $mensaje = "Vuelo actualizado exitosamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al actualizar el vuelo: " . mysqli_error($link);
            }
        }
    }

    // 3. ELIMINAR VUELO
    if (!empty($_POST['eliminar'])) {
        $id = (int)$_POST['id'];
        $sql_delete = "DELETE FROM VUELOS WHERE codVuelo=$id AND codAerolinea=$codAerolineaCEO";

        if(mysqli_query($link, $sql_delete)){
            $mensaje = "Vuelo eliminado exitosamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al eliminar el vuelo: " . mysqli_error($link);
        }
    }
}

// Carga los vuelos de la base de datos
$query_vuelos = "SELECT * FROM VUELOS WHERE codAerolinea = $codAerolineaCEO ORDER BY fechaSalidaVuelo ASC";
$resultado_vuelos = mysqli_query($link, $query_vuelos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vuelos - Panel CEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">VuelaSeguro Admin</a>
        <div class="d-flex">
            <span class="navbar-text me-3 text-white">
                Hola, <strong><?= htmlspecialchars($_SESSION['usuario']['nombreUsuario']) ?></strong>
            </span>
        </div>
    </div>
</nav>

<div class="container" style="min-height: 75vh;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Vuelos (Panel CEO)</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="bi bi-plus-circle me-1"></i> Cargar Nuevo Vuelo
        </button>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-5">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Precio</th>
                            <th>Asientos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado_vuelos && mysqli_num_rows($resultado_vuelos) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($resultado_vuelos)): ?>
                                <?php $horaLimpia = date("H:i", strtotime($row['horaSalidaVuelo'])); ?>
                                <tr>
                                    <td>#<?= $row['codVuelo'] ?></td>
                                    <td><?= htmlspecialchars($row['origenVuelo']) ?></td>
                                    <td><?= htmlspecialchars($row['destinoVuelo']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['fechaSalidaVuelo'])) ?></td>
                                    <td><?= $horaLimpia ?></td>
                                    <td class="fw-bold text-success">$<?= number_format($row['precioVuelo'], 0, ',', '.') ?></td>
                                    <td><?= $row['asientosDisponibles'] ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $row['codVuelo'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar<?= $row['codVuelo'] ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <div class="modal fade text-start" id="modalEditar<?= $row['codVuelo'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST" action="">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">Modificar Vuelo #<?= $row['codVuelo'] ?></h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $row['codVuelo'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Origen</label>
                                                        <input type="text" class="form-control" name="origen" value="<?= htmlspecialchars($row['origenVuelo']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Destino</label>
                                                        <input type="text" class="form-control" name="destino" value="<?= htmlspecialchars($row['destinoVuelo']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Fecha</label>
                                                        <input type="date" class="form-control" name="fecha" value="<?= $row['fechaSalidaVuelo'] ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Hora</label>
                                                        <input type="time" class="form-control" name="hora" value="<?= $row['horaSalidaVuelo'] ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Precio ($)</label>
                                                        <input type="number" class="form-control" name="precio" value="<?= $row['precioVuelo'] ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Asientos Disponibles</label>
                                                        <input type="number" class="form-control" name="asientos" value="<?= $row['asientosDisponibles'] ?>" required>
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

                                <div class="modal fade text-start" id="modalEliminar<?= $row['codVuelo'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST" action="">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Eliminar Vuelo</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $row['codVuelo'] ?>">
                                                    <p>¿Estás seguro que deseas eliminar el vuelo con destino a <strong>"<?= htmlspecialchars($row['destinoVuelo']) ?>"</strong>?</p>
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
                                <td colspan="8" class="text-center py-4">No hay vuelos registrados para esta aerolínea.</td>
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
                    <h5 class="modal-title">Crear Nuevo Vuelo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Origen</label>
                        <input type="text" class="form-control" name="origen" placeholder="Ej: Rosario" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Destino</label>
                        <input type="text" class="form-control" name="destino" placeholder="Ej: Madrid" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de Salida</label>
                        <input type="date" class="form-control" name="fecha" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hora de Salida</label>
                        <input type="time" class="form-control" name="hora" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio ($)</label>
                        <input type="number" class="form-control" name="precio" placeholder="Ej: 85000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Asientos Disponibles</label>
                        <input type="number" class="form-control" name="asientos" placeholder="Ej: 150" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <input type="submit" name="crear" class="btn btn-success" value="Crear Vuelo">
                </div>
            </div>
        </form>
    </div>
</div>

<footer class="bg-dark text-white text-center py-3 mt-auto">
    <div class="container">
        <p class="m-0 small">© 2026 VuelaSeguro. Todos los derechos reservados.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
if(isset($link) && $link) {
    mysqli_close($link);
}
?>