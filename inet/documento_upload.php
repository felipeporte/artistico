<?php
// 1) Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// admin/documento_upload.php
require $_SERVER['DOCUMENT_ROOT'] .'/inscripciones/conexion.php'; 

// 1) Recoger datos del POST
$evento_id = (int)($_POST['evento_id'] ?? 0);
$titulo    = trim($_POST['titulo'] ?? '');
$tipo      = $_POST['tipo'] ?? '';
$file      = $_FILES['archivo'] ?? null;

// 2) Validaciones básicas
if (!$evento_id || !$titulo || !$tipo || !$file) {
    exit('Faltan datos o archivo.');
}

// 3) Validar MIME type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']);
if ($mime !== 'application/pdf') {
    exit('Solo se permiten archivos PDF.');
}

// 4) Obtener slug del evento
$stmt = $pdo->prepare("SELECT slug FROM competencias_index WHERE id = ?");
$stmt->execute([$evento_id]);
$slug = $stmt->fetchColumn();
if (!$slug) {
    exit('Evento no existe.');
}

// 5) Preparar carpeta destino
$baseDir = __DIR__ . "/anexos/{$slug}/";
if (!is_dir($baseDir)) {
    mkdir($baseDir, 0755, true);
}

// 6) Generar nombre de archivo y rutas
$nombreArchivo = $tipo . '_' . time() . '.pdf';
$rutaRelativa   = "/inet/anexos/{$slug}/" . $nombreArchivo;
$rutaAbsoluta   = $baseDir . $nombreArchivo;

// 7) Mover archivo subido
if (!move_uploaded_file($file['tmp_name'], $rutaAbsoluta)) {
    exit('Error al mover el archivo.');
}

// 8) Insertar registro en la base de datos
$stmt = $pdo->prepare("
    INSERT INTO competencia_documentos
      (evento_id, titulo, descripcion, tipo, ruta)
    VALUES
      (:eid, :tit, NULL, :tip, :rut)
");
$stmt->execute([
    'eid' => $evento_id,
    'tit' => $titulo,
    'tip' => $tipo,
    'rut' => $rutaRelativa
]);

// 9) Redirigir de vuelta a la página de documentos
header("Location: documentos.php?evento_id={$evento_id}");
exit;
