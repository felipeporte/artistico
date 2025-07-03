<?php
// admin/documentos.php

require $_SERVER['DOCUMENT_ROOT'] . '/inscripciones/conexion.php';


// 1) Obtener el ID del evento por GET
if (empty($_GET['evento_id'])) {
  exit('Parámetro evento_id ausente');
}
$evento_id = (int)$_GET['evento_id'];

// 2) Cargar datos del evento
$stmt = $pdo->prepare("SELECT nombre, slug FROM competencias_index WHERE id = ?");
$stmt->execute([$evento_id]);
$evento = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$evento) {
  exit('Evento no encontrado.');
}

// 3) Cargar documentos existentes
$stmt2 = $pdo->prepare("
  SELECT id, titulo, descripcion, tipo, ruta, fecha_subida
  FROM competencia_documentos
  WHERE evento_id = ?
  ORDER BY fecha_subida DESC
");
$stmt2->execute([$evento_id]);
$docs = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'template/header.php'; ?>
</head>

<body>
  <?php include 'template/menu.php'; ?>

  <div class="container py-4">
  <h1>Documentos de “<?= htmlspecialchars($evento['nombre']) ?>”</h1>
  <a href="eventos.php" class="btn btn-sm btn-secondary mb-3">&larr; Volver a Eventos</a>
 
    <!-- Listado de documentos -->
    <h2>Archivos subidos</h2>
    <?php if (empty($docs)): ?>
      <div class="alert alert-info">No hay documentos aún.</div>
    <?php else: ?>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Título</th>
            <th>Tipo</th>
            <th>Subido</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($docs as $d): ?>
            <tr>
              <td><?= htmlspecialchars($d['titulo']) ?></td>
              <td><?= htmlspecialchars($d['tipo']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($d['fecha_subida'])) ?></td>
              <td>
                <a href="<?= htmlspecialchars($d['ruta']) ?>" target="_blank"
                  class="btn btn-sm btn-primary">Ver</a>
                <a href="documento_delete.php?id=<?= $d['id'] ?>&evento_id=<?= $evento_id ?>"
                  class="btn btn-sm btn-danger"
                  onclick="return confirm('¿Eliminar este documento?')">Borrar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <!-- Formulario de subida -->
    <h2 class="mt-5">Subir nuevo documento</h2>
    <form action="documento_upload.php" method="post" enctype="multipart/form-data"
      class="row g-3">
      <input type="hidden" name="evento_id" value="<?= $evento_id ?>">
      <div class="col-md-4">
        <label class="form-label">Título</label>
        <input name="titulo" type="text" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Tipo</label>
        <select name="tipo" class="form-select" required>
          <option value="programacion">Programación</option>
          <option value="reglamento">Reglamento</option>
          <option value="sorteo">Sorteo</option>
          <option value="resultado">Resultado</option>
          <option value="comunicado">Comunicado</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Archivo (PDF)</label>
        <input name="archivo" type="file" accept="application/pdf"
          class="form-control" required>
      </div>
      <div class="col-12">
        <button class="btn btn-success">Subir Documento</button>
      </div>
    </form>
  </div>
</body>

</html>