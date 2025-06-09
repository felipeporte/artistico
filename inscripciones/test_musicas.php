<?php
// admin/subir_archivos.php
session_start();
if (!isset($_SESSION['club_id'])) {
    header('Location: ../login.php');
    exit;
}
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/conexion.php';
require __DIR__ . '/certifica/google-drive-client.php';

$club_id = $_SESSION['club_id'];
$folderId = '1Hs1ZbmMrsiqUH2lqrnQE91PcwvJDSYJD'; // tu carpeta en Drive

// 1) Si llega un POST con inscribir_id y archivo, lo procesamos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $insc_id = (int)($_POST['insc_id'] ?? 0);
    $tipo    = $_POST['tipo'] ?? ''; // 'musica' o 'hoja'
    if ($insc_id && in_array($tipo, ['musica','hoja']) && !empty($_FILES['archivo']['tmp_name'])) {
        // Obtener datos de la inscripción (para el nombre de archivo)
        $stmt = $pdo->prepare("
          SELECT d.nombre_completo, i.modalidad
            FROM inscripciones i
            JOIN deportistas d ON i.deportista_id = d.id
           WHERE i.id = :id
        ");
        $stmt->execute([':id'=>$insc_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            // Generar nombre slugificado
            function slug($t){ $t=iconv('UTF-8','ASCII//TRANSLIT',$t); $t=strtolower($t); $t=preg_replace('/[^a-z0-9]+/','_',$t); return trim($t,'_'); }
            $base = slug($row['nombre_completo']) . '-' . slug($row['modalidad']);
            $ext  = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
            $name = ($tipo==='musica' ? 'musica_'.$base : 'hoja_'.$base) . '.' . $ext;

            // Subida a Drive
            $fileMeta = new Google_Service_Drive_DriveFile([
              'name'    => $name,
              'parents' => [$folderId],
            ]);
            $upload = $service->files->create($fileMeta, [
              'data'       => file_get_contents($_FILES['archivo']['tmp_name']),
              'mimeType'   => $_FILES['archivo']['type'],
              'uploadType' => 'multipart'
            ]);
            $driveId = $upload->id;

            // Actualizar DB
            if ($tipo==='musica') {
                $upd = $pdo->prepare("UPDATE inscripciones SET musica_drive_id = ? WHERE id = ?");
            } else {
                $upd = $pdo->prepare("UPDATE inscripciones SET hoja_elementos_drive_id = ? WHERE id = ?");
            }
            $upd->execute([$driveId, $insc_id]);
            $_SESSION['flash'] = "Archivo subido correctamente.";
        }
    }
    header("Location: subir_archivos.php");
    exit;
}

// 2) Recuperar todas las inscripciones de este club
$stmt = $pdo->prepare("
  SELECT 
    i.id,
    d.nombre_completo,
    i.competencia_id,
    c.nombre_evento,
    i.modalidad,
    i.nivel,
    i.subnivel,
    i.categoria,
    i.hoja_elementos_drive_id,
    i.musica_drive_id
  FROM inscripciones i
  JOIN deportistas d  ON i.deportista_id = d.id
  JOIN competencias c ON i.competencia_id = c.id
  WHERE d.club_id = ?
  ORDER BY c.fecha_inicio DESC, d.nombre_completo
");
$stmt->execute([$club_id]);
$insc = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Subir Archivos – Club</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
<div class="container bg-white p-4 rounded shadow">
  <h2>Subir Música / Hoja de Elementos</h2>
  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-success"><?= $_SESSION['flash'] ?></div>
    <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Competencia</th>
        <th>Deportista</th>
        <th>Modalidad</th>
        <th>Nivel</th>
        <th>Categoría</th>
        <th>Hoja</th>
        <th>Música</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($insc as $row): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['nombre_evento']) ?></td>
        <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
        <td><?= htmlspecialchars($row['modalidad']) ?></td>
        <td><?= htmlspecialchars($row['nivel']) ?> <?= $row['subnivel']?"/".htmlspecialchars($row['subnivel']):"" ?></td>
        <td><?= htmlspecialchars($row['categoria']) ?></td>

        <!-- Hoja de Elementos -->
        <td>
          <?php if ($row['hoja_elementos_drive_id']): ?>
            <a href="https://drive.google.com/uc?id=<?= $row['hoja_elementos_drive_id'] ?>&export=download" target="_blank">Ver PDF</a>
          <?php else: ?>
            <form method="POST" enctype="multipart/form-data" class="d-flex">
              <input type="hidden" name="insc_id" value="<?= $row['id'] ?>">
              <input type="hidden" name="tipo"    value="hoja">
              <input type="file" name="archivo" accept="application/pdf" required class="form-control form-control-sm me-2">
              <button class="btn btn-sm btn-outline-primary">Subir</button>
            </form>
          <?php endif; ?>
        </td>

        <!-- Música -->
        <td>
          <?php if ($row['musica_drive_id']): ?>
            <a href="https://drive.google.com/uc?id=<?= $row['musica_drive_id'] ?>&export=download" target="_blank">Ver música</a>
          <?php else: ?>
            <form method="POST" enctype="multipart/form-data" class="d-flex">
              <input type="hidden" name="insc_id" value="<?= $row['id'] ?>">
              <input type="hidden" name="tipo"    value="musica">
              <input type="file" name="archivo" accept="audio/*" required class="form-control form-control-sm me-2">
              <button class="btn btn-sm btn-outline-success">Subir</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <a href="/panel_club.php" class="btn btn-secondary">← Volver al Panel</a>
</div>
</body>
</html>
