<?php
// admin/dashboard.php
// Panel de administración: listar competencias y gestionar clubes

session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/../conexion.php';

// 1) Obtener todas las competencias
$stmt1 = $pdo->query("SELECT id, nombre_evento, fecha_inicio, zona FROM competencias ORDER BY fecha_inicio DESC");
$competencias = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// 2) Obtener todos los clubes
$stmt2 = $pdo->query("SELECT id, nombre_club, rut, zona, nombre_presidente, email_responsable FROM clubs ORDER BY nombre_club ASC");
$clubs = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Administración</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
<div class="container bg-white p-4 rounded shadow">
  <h2>Panel de Administración</h2>

  <!-- Sección Competencias -->
  <div class="mb-5">
    <h4>Competencias</h4>
    <ul class="list-group mb-2">
      <?php foreach ($competencias as $c): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <?= htmlspecialchars($c['nombre_evento']) ?>
            <small class="text-muted">(<?= htmlspecialchars($c['zona']) ?> - <?= htmlspecialchars($c['fecha_inicio']) ?>)</small>
          </span>
          <div>
            <a href="ver_competencia.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Ver Inscritos</a>
            <a href="editar_competencia.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary">Editar</a>
            <a href="eliminar_competencia.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar competencia?');">Eliminar</a>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
    <a href="crear_competencia.php" class="btn btn-success">+ Nueva Competencia</a>
  </div>

  <!-- Sección Clubes -->
  <div>
    <h4>Clubes</h4>
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>RUT</th>
          <th>Zona</th>
          <th>Presidente</th>
          <th>Email</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clubs as $club): ?>
          <tr>
            <td><?= htmlspecialchars($club['nombre_club']) ?></td>
            <td><?= htmlspecialchars($club['rut']) ?></td>
            <td><?= htmlspecialchars($club['zona']) ?></td>
            <td><?= htmlspecialchars($club['nombre_presidente']) ?></td>
            <td><?= htmlspecialchars($club['email_responsable']) ?></td>
            <td>
              <a href="editar_club.php?id=<?= $club['id'] ?>" class="btn btn-sm btn-secondary">Editar</a>
              <a href="eliminar_club.php?id=<?= $club['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar club?');">Eliminar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <a href="crear_club.php" class="btn btn-primary">+ Nuevo Club</a>
  </div>
</div>
</body>
</html>
