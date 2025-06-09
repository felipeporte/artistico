<?php
session_start();
if (!isset($_SESSION['club_id'])) {
    http_response_code(403);
    echo "No autorizado.";
    exit;
}

include __DIR__ . '/conexion.php';

$club_id = $_SESSION['club_id'];

$stmt = $pdo->prepare("SELECT nombre_completo, rut, DATE_FORMAT(fecha_nacimiento, '%d-%m-%Y') AS fecha_nacimiento_fmt, genero FROM deportistas WHERE club_id = ? ORDER BY nombre_completo");
$stmt->execute([$club_id]);
$deportistas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($deportistas)) {
    echo "<p>No hay deportistas registrados.</p>";
    exit;
}

echo "<table class='tabla-deportistas'>";
echo "<thead><tr><th>Nombre</th><th>RUT</th><th>Nacimiento</th><th>Género</th></tr></thead><tbody>";
foreach ($deportistas as $d) {
    echo "<tr>
            <td>{$d['nombre_completo']}</td>
            <td>{$d['rut']}</td>
            <td>{$d['fecha_nacimiento_fmt']}</td>
            <td>" . ($d['genero'] === 'F' ? 'Femenino' : 'Masculino') . "</td>
          </tr>";
}
echo "</tbody></table>";
?>
