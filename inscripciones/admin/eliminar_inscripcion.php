<?php
// admin/eliminar_inscripcion.php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/../conexion.php';

// 1) Validar ID
$id = isset($_GET['id']) && is_numeric($_GET['id'])
    ? (int)$_GET['id']
    : 0;
if (!$id) {
    exit('ID de inscripción inválido.');
}

// 2) Cargar datos para mostrar
$stmt = $pdo->prepare("
    SELECT 
      i.competencia_id,
      d.nombre_completo,
      c.nombre_evento,
      i.modalidad,
      i.nivel,
      i.subnivel,
      i.categoria
    FROM inscripciones i
    JOIN deportistas d  ON i.deportista_id  = d.id
    JOIN competencias c ON i.competencia_id = c.id
    WHERE i.id = ?
");
$stmt->execute([$id]);
$insc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$insc) {
    exit('Inscripción no encontrada.');
}

// 3) Si confirma (POST), borrar y volver a la lista
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $del = $pdo->prepare("DELETE FROM inscripciones WHERE id = ?");
    $del->execute([$id]);
    // flash message opcional
    $_SESSION['flash_success'] = "Inscripción de <strong>"
        . htmlspecialchars($insc['nombre_completo'])
        . "</strong> eliminada correctamente.";
    header("Location: ver_competencia.php?id={$insc['competencia_id']}");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Eliminar Inscripción #<?= $id ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
  <div class="container bg-white p-4 rounded shadow">
    <h2>Eliminar Inscripción</h2>
    <p>¿Estás seguro que deseas <strong>eliminar</strong> esta inscripción?</p>
    <ul>
      <li><strong>Competencia:</strong> <?= htmlspecialchars($insc['nombre_evento']) ?></li>
      <li><strong>Deportista:</strong> <?= htmlspecialchars($insc['nombre_completo']) ?></li>
      <li><strong>Modalidad:</strong> <?= htmlspecialchars($insc['modalidad']) ?></li>
      <li><strong>Nivel/Subnivel:</strong> <?= htmlspecialchars($insc['nivel']) ?> <?= $insc['subnivel']?("/".htmlspecialchars($insc['subnivel'])):"" ?></li>
      <li><strong>Categoría:</strong> <?= htmlspecialchars($insc['categoria']) ?></li>
    </ul>
    <form method="POST" class="d-inline">
      <button type="submit" class="btn btn-danger">Sí, eliminar</button>
    </form>
    <a href="ver_competencia.php?id=<?= $insc['competencia_id'] ?>" class="btn btn-secondary ms-2">
      No, volver
    </a>
  </div>
</body>
</html>
