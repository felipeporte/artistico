<?php
include 'conexion.php';

$id = $_GET['id'] ?? null;
$anio_competencia = 2025;

function calcularCategoriaPorEdad($fecha_nacimiento, $anio_competencia) {
    $fecha_limite = new DateTime("$anio_competencia-12-31");
    $nacimiento = new DateTime($fecha_nacimiento);
    $edad = $nacimiento->diff($fecha_limite)->y;

    if ($edad >= 5  && $edad <=  6) return "Pre-novato";
    if ($edad >= 6  && $edad <=  7) return "Novato";
    if ($edad >= 8 && $edad <= 9) return "Tots";
    if ($edad == 10 || $edad == 11) return "Minis";
    if ($edad == 12 || $edad == 13) return "Espoir";
    if ($edad == 14 || $edad == 15) return "Cadet";
    if ($edad == 16) return "Youth";
    if ($edad == 17 || $edad == 18) return "Junior";
    if ($edad >= 19) return "Senior";
    return "Sin categoría";
}

if ($id) {
    $stmt = $pdo->prepare("SELECT fecha_nacimiento FROM deportistas WHERE id = ?");
    $stmt->execute([$id]);
    $fecha = $stmt->fetchColumn();

    if ($fecha) {
        echo calcularCategoriaPorEdad($fecha, $anio_competencia);
        exit;
    }
}

echo "Sin datos";
