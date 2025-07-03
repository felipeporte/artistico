<?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/head.php'; ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php'; ?>
<?php
  // 1) Mostrar errores en desarrollo
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  // 2) Conexión a la base de datos
  require $_SERVER['DOCUMENT_ROOT'] .'/inscripciones/conexion.php';  // tu conexión PDO a `artistico_db`

  // 3) Obtener el slug de la URL
  if (empty($_GET['slug'])) {
      http_response_code(400);
      exit('400 Bad Request: falta el parámetro slug.');
  }
  $slug = $_GET['slug'];

  // 4) Consultar datos del evento
  $stmt = $pdo->prepare("
      SELECT id, nombre, fecha_inicio, fecha_fin, ubicacion
      FROM competencias_index
      WHERE slug = :slug
        AND activo = 1
      LIMIT 1
  ");
  $stmt->execute(['slug' => $slug]);
  $evento = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$evento) {
      http_response_code(404);
      exit('404 Not Found: evento no existe o no está activo.');
  }

  // 5) Consultar documentos asociados
  $stmt2 = $pdo->prepare("
      SELECT titulo, descripcion, tipo, ruta, fecha_subida
      FROM competencia_documentos
      WHERE evento_id = :eid
      ORDER BY FIELD(tipo,'programacion','reglamento','sorteo','resultado','comunicado'), fecha_subida
  ");
  $stmt2->execute(['eid' => $evento['id']]);
  $docs = $stmt2->fetchAll(PDO::FETCH_ASSOC);

  // 6) Formatear fechas
  $fi = date('d/m/Y', strtotime($evento['fecha_inicio']));
  $ff = date('d/m/Y', strtotime($evento['fecha_fin']));
?>

  <nav class="navbar navbar-light bg-light mb-4">
    <div class="container">
      <a class="navbar-brand" href="index.php">&larr; Volver a Eventos</a>
    </div>
  </nav>

  <main class="container">
    <header class="mb-5 text-center">
      <h1><?=htmlspecialchars($evento['nombre'])?></h1>
      <p class="text-muted">
        <strong><?= $fi ?></strong> – <strong><?= $ff ?></strong>
        &middot; <?=htmlspecialchars($evento['ubicacion'])?>
      </p>
    </header>

    <?php if (empty($docs)): ?>
      <div class="alert alert-info">Aún no hay documentos disponibles para este evento.</div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($docs as $doc): ?>
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?=htmlspecialchars($doc['titulo'])?></h5>
                <?php if (!empty($doc['descripcion'])): ?>
                  <p class="card-text"><?=htmlspecialchars($doc['descripcion'])?></p>
                <?php endif; ?>
                <p class="mt-auto text-muted small">
                  Subido: <?= date('d/m/Y H:i', strtotime($doc['fecha_subida'])) ?>
                </p>
                <a href="<?=htmlspecialchars($doc['ruta'])?>"
                   target="_blank"
                   class="btn btn-primary mt-2">
                  Ver <?=ucfirst($doc['tipo'])?>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/footer.php'; ?>
