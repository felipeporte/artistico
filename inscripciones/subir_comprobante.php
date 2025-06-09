<?php
// subir_comprobante.php

// Mostrar errores en pantalla (opcional en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['club_id'])) {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/conexion.php';
// Carga el cliente de Drive
require __DIR__ . '/certifica/google-drive-client.php';

// Leer IDs desde el formulario
$pago_id        = isset($_POST['pago_id']) ? (int)$_POST['pago_id'] : 0;
$competencia_id = isset($_POST['competencia_id']) ? (int)$_POST['competencia_id'] : 0;

if ($pago_id <= 0 || $competencia_id <= 0 || empty($_FILES['comprobante']['tmp_name'])) {
    header("Location: pago_club.php?competencia_id={$competencia_id}");
    exit;
}

// Function para generar slugs seguros para filenames
function slugify($text) {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '_', $text);
    return trim($text, '_');
}

// 1) Obtener nombre del club desde columna nombre_club
$stmt = $pdo->prepare("SELECT nombre_club FROM clubs WHERE id = ?");
$stmt->execute([$_SESSION['club_id']]);
$clubNombre = $stmt->fetchColumn() ?: 'club';

// 2) Obtener nombre de la competencia
$stmt = $pdo->prepare("SELECT nombre_evento FROM competencias WHERE id = ?");
$stmt->execute([$competencia_id]);
$competenciaNombre = $stmt->fetchColumn() ?: 'competencia';

// 3) Crear nombre de archivo
$clubSlug   = slugify($clubNombre);
$compSlug   = slugify($competenciaNombre);
$ext        = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
$uploadName = "{$clubSlug}-{$compSlug}.{$ext}";

// 4) Subir a Google Drive
$folderId = '1Hs1ZbmMrsiqUH2lqrnQE91PcwvJDSYJD';  // tu carpeta compartida

$fileMetadata = new Google_Service_Drive_DriveFile([
    'name'    => $uploadName,
    'parents' => [$folderId]
]);

$result = $service->files->create($fileMetadata, [
    'data'       => file_get_contents($_FILES['comprobante']['tmp_name']),
    'mimeType'   => $_FILES['comprobante']['type'],
    'uploadType' => 'multipart'
]);

$driveId = $result->id;

// 5) Actualizar la tabla pagos_club
$stmt = $pdo->prepare(
    "UPDATE pagos_club
       SET comprobante_drive_id = :did,
           fecha_pago           = NOW(),
           estado               = 'pagado'
     WHERE id = :pid"
);
$stmt->execute([
    ':did' => $driveId,
    ':pid' => $pago_id
]);

// 6) Redirigir de vuelta al detalle
header("Location: pago_club.php?competencia_id={$competencia_id}");
exit;
