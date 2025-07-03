<?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/head.php'; ?>


  <?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php'; ?>
  <?php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  require $_SERVER['DOCUMENT_ROOT'] . '/inscripciones/conexion.php';  // tu conexión PDO a `artistico_db`

  // 1) Consulta principal
  $sql = "
  SELECT 
    ci.id, ci.nombre, ci.slug,
    ci.fecha_inicio, ci.fecha_fin, ci.ubicacion,
    cd.titulo AS doc_titulo,
    cd.ruta   AS doc_ruta
  FROM competencias_index ci
  LEFT JOIN competencia_documentos cd
    ON ci.id = cd.evento_id
  WHERE ci.activo = 1
  ORDER BY ci.fecha_inicio
";
  $stmt = $pdo->query($sql);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // 2) Agrupamos documentos por evento
  $eventos = [];
  foreach ($rows as $r) {
    $id = $r['id'];
    if (!isset($eventos[$id])) {
      $eventos[$id] = [
        'nombre'  => $r['nombre'],
        'slug'    => $r['slug'],
        'inicio'  => date('d/m/y', strtotime($r['fecha_inicio'])),
        'fin'     => date('d/m/y', strtotime($r['fecha_fin'])),
        'ubicacion' => $r['ubicacion'],
        'docs'    => []
      ];
    }
    if ($r['doc_titulo']) {
      $eventos[$id]['docs'][] = [
        'titulo' => $r['doc_titulo'],
        'ruta'   => $r['doc_ruta']
      ];
    }
  }
  ?>
  <main class="flex-grow-1">
    <section id="title">
      <div class="container text-center pt-3">
        <h1 class="fw-bold">Comisión Técnica de Patinaje Artístico.</h1>
        <p>Gestión de Competencias para el año 2025.</p>
      </div>
    </section>

    <section id="inscripciones" class="py-5">
      <div class="container">
        <div class="row g-4 align-items-stretch">
          <?php foreach ($eventos as $e): ?>
            <div class="col-lg-4 col-md-6 col-sm-6 d-flex flex-column justify-content-between text-center ">
              <article class="card shadow-sm rounded h-100">
                <div class="card-body text-center d-flex flex-column justify-content-between text-center">
                  <p class="card-text fw-semibold">
                    <?= $e['inicio'] ?> – <?= $e['fin'] ?>
                  </p>
                  <h3 class="card-title mb-5 fw-bold"><?= htmlspecialchars($e['nombre']) ?></h3>
                  <p><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($e['ubicacion']) ?></p>
                  <a href="evento.php?slug=<?= urlencode($e['slug']) ?>"
                    class="btn btn-primary">
                    Ir a Evento
                  </a>
                </div>
              </article>
            </div>

          <?php endforeach; ?>

          <?php if (empty($eventos)): ?>
            <p class="text-muted">No hay eventos activos.</p>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/footer.php'; ?>
