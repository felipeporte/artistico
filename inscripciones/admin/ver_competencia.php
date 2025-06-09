<?php
// admin/ver_competencia.php

session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/../conexion.php';

// 1) Leer & validar ID de competencia
$id = isset($_GET['id']) && is_numeric($_GET['id'])
    ? (int)$_GET['id']
    : 0;
if (!$id) {
    exit('ID inválido de competencia.');
}

// 2) Chequear si piden exportar CSV
$exportCsv = isset($_GET['export']) && $_GET['export'] === 'csv';

// 3) Traer datos de la competencia
$stmt = $pdo->prepare("SELECT nombre_evento, zona FROM competencias WHERE id = ?");
$stmt->execute([$id]);
$comp = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$comp) {
    exit('Competencia no encontrada.');
}

// 4) Traer inscripciones
$stmt = $pdo->prepare("
    SELECT
      i.id,
      i.deportista_id,
      c.id AS club_id,
      c.nombre_club,
      d.nombre_completo,
      d.genero,
      i.modalidad,
      i.nivel,
      i.subnivel,
      i.categoria
    FROM inscripciones i
    JOIN deportistas d ON i.deportista_id = d.id
    JOIN clubs c       ON d.club_id        = c.id
    WHERE i.competencia_id = ?
    ORDER BY d.genero, i.modalidad, i.categoria 
");
$stmt->execute([$id]);
$inscritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5) Calcular totales (solo para vista HTML)
if (!$exportCsv) {
    $stmtCounts = $pdo->prepare("
      SELECT 
        COUNT(*) AS total_ins,
        COUNT(DISTINCT deportista_id) AS total_deportistas
      FROM inscripciones
      WHERE competencia_id = ?
    ");
    $stmtCounts->execute([$id]);
    $counts = $stmtCounts->fetch(PDO::FETCH_ASSOC);
}

// 6) Si piden CSV, lo generamos y salimos
if ($exportCsv) {
    header('Content-Type: text/csv; charset=UTF-8');
    header("Content-Disposition: attachment; filename=\"inscripciones_competencia_{$id}.csv\"");
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Club','Deportista','Género','Modalidad','Nivel','Subnivel','Categoría'], ';');
    foreach ($inscritos as $row) {
        $genero = $row['genero'] === 'M' ? 'Masculino'
                : ($row['genero'] === 'F' ? 'Femenino' : $row['genero']);
        fputcsv($out, [
            $row['nombre_club'],
            $row['nombre_completo'],
            $genero,
            $row['modalidad'],
            $row['nivel'],
            $row['subnivel'] ?: '-',
            $row['categoria'],
        ], ';');
    }
    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Inscritos – <?= htmlspecialchars($comp['nombre_evento']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
  <div class="container bg-white p-4 rounded shadow">
    <h2 class="mb-3">
      Inscritos: <?= htmlspecialchars($comp['nombre_evento']) ?>
      <small class="text-muted">(<?= htmlspecialchars($comp['zona']) ?>)</small>
    </h2>

    <?php if (!empty($inscritos)): ?>
      <div class="mb-3">
        <span class="me-4"><strong>Total inscripciones:</strong> <?= $counts['total_ins'] ?></span>
        <span><strong>Deportistas únicos:</strong> <?= $counts['total_deportistas'] ?></span>
      </div>
      <table class="table table-striped table-bordered">
        <thead class="table-light">
          <tr>
            <th>Club</th>
            <th>Deportista</th>
            <th>Género</th>
            <th>Modalidad</th>
            <th>Nivel</th>
            <th>Subnivel</th>
            <th>Categoría</th>
            <th>Pago</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($inscritos as $row): 
          $genero = $row['genero'] === 'M'   ? 'Masculino'
                  : ($row['genero'] === 'F' ? 'Femenino'
                                            : htmlspecialchars($row['genero']));
          // Verificar si el club tiene pago confirmado
          $stmtPago = $pdo->prepare("SELECT estado FROM pagos_club WHERE club_id = ? AND competencia_id = ?");
          $stmtPago->execute([$row['club_id'], $id]);
          $pago = $stmtPago->fetch(PDO::FETCH_ASSOC);
          $iconoPago = ($pago && $pago['estado'] === 'pagado') ? '✅' : '❌';
        ?>
          <tr>
            <td><?= htmlspecialchars($row['nombre_club']) ?></td>
            <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
            <td><?= $genero ?></td>
            <td><?= htmlspecialchars(ucfirst($row['modalidad'])) ?></td>
            <td><?= htmlspecialchars($row['nivel']) ?></td>
            <td><?= htmlspecialchars($row['subnivel'] ?: '-') ?></td>
            <td><?= htmlspecialchars($row['categoria']) ?></td>
            <td style="text-align: center;"><?= $iconoPago ?></td>
            <td>
              <a href="editar_inscripcion.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                Editar
              </a>
              <a href="eliminar_inscripcion.php?id=<?= $row['id'] ?>" 
                 class="btn btn-sm btn-danger ms-1"
                 onclick="return confirm('¿Eliminar esta inscripción?');">
                Eliminar
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No hay inscripciones para esta competencia.</p>
    <?php endif; ?>

    <div class="mt-4 d-flex">
      <a href="dashboard.php" class="btn btn-secondary">← Volver al Panel</a>
      <a href="ver_competencia.php?id=<?= $id ?>&export=csv" 
         class="btn btn-success ms-auto">
        📥 Descargar CSV
      </a>
    </div>
  </div>
</body>
</html>
