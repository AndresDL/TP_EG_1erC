<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test INSERT Promociones</title>
</head>
<body>
<h2>Test directo de INSERT en promociones</h2>

<?php
// Conexión directa, sin includes
$link = mysqli_connect('localhost', 'root', '', 'vuelaseguro');

if (!$link) {
    die("<p style='color:red'>ERROR CONEXION: " . mysqli_connect_error() . "</p>");
}
echo "<p style='color:green'>✅ Conexión OK</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $desc      = mysqli_real_escape_string($link, $_POST['desc']);
    $descuento = (float)$_POST['descuento'];
    $vigencia  = $_POST['vigencia'];
    $codAero   = (int)$_POST['codAero'];

    $sql = "INSERT INTO promociones (descripcionPromocion, descuentoPromocion, codAerolinea, estadoPromocion, imagenPromocion, vigenciaPromocion, codCEO)
            VALUES ('$desc', $descuento, $codAero, 'pendiente', '', '$vigencia', 2)";

    echo "<p><strong>SQL ejecutado:</strong><br><code>" . htmlspecialchars($sql) . "</code></p>";

    if (mysqli_query($link, $sql)) {
        echo "<p style='color:green; font-size:1.3rem'>✅ INSERT OK — ID: " . mysqli_insert_id($link) . "</p>";
    } else {
        echo "<p style='color:red'>❌ ERROR SQL: " . mysqli_error($link) . "</p>";
    }
}

// Mostrar registros actuales
$res = mysqli_query($link, "SELECT * FROM promociones");
echo "<h3>Registros en tabla promociones:</h3>";
if (mysqli_num_rows($res) === 0) {
    echo "<p>La tabla está vacía.</p>";
} else {
    echo "<table border='1' cellpadding='6'><tr><th>ID</th><th>Descripción</th><th>Descuento</th><th>Estado</th><th>Vigencia</th><th>codCEO</th></tr>";
    while ($r = mysqli_fetch_assoc($res)) {
        echo "<tr>
            <td>{$r['codPromocion']}</td>
            <td>{$r['descripcionPromocion']}</td>
            <td>{$r['descuentoPromocion']}</td>
            <td>{$r['estadoPromocion']}</td>
            <td>{$r['vigenciaPromocion']}</td>
            <td>{$r['codCEO']}</td>
        </tr>";
    }
    echo "</table>";
}
?>

<hr>
<h3>Formulario de prueba</h3>
<form method="POST">
    <p>Descripción: <input type="text" name="desc" value="Promo de prueba" required></p>
    <p>Descuento %: <input type="number" name="descuento" value="20" required></p>
    <p>Vigencia: <input type="date" name="vigencia" value="2026-12-31" required></p>
    <p>codAerolinea: <input type="number" name="codAero" value="1" required></p>
    <p><button type="submit">Insertar</button></p>
</form>

</body>
</html>