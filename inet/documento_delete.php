<?php
// admin/documento_delete.php
require $_SERVER['DOCUMENT_ROOT'] .'/inscripciones/conexion.php'; 

// 1) Recoger parámetros
$id        = isset($_GET['id'])        ? (int)$_GET['id'] : 0;
$evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;
if (!$id || !$evento_id) {
    exit('Parámetros inválidos.');
}

// 2) Obtener información del documento
$stmt = $pdo->prepare("SELECT tipo, ruta FROM competencia_documentos WHERE id = ?");
$stmt->execute([$id]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$doc) {
    exit('Documento no encontrado.');
}

// 3) Si no es un enlace (resultado), eliminar el archivo físico
if ($doc['tipo'] !== 'resultado') {
    // Ruta absoluta
    $filePath = $_SERVER['DOCUMENT_ROOT'] . $doc['ruta'];
    if (is_file($filePath)) {
        @unlink($filePath);
    }
}

// 4) Borrar registro de la base de datos
$pdo->prepare("DELETE FROM competencia_documentos WHERE id = ?")
    ->execute([$id]);

// 5) Redirigir de vuelta al listado de documentos
header("Location: documentos.php?evento_id={$evento_id}");
exit;
