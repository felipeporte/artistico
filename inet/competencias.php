<?php

// Panel de administración: listar competencias 

include './../inscripciones/conexion.php';

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
    <?php include 'template/header.php'; ?>
</head>
<body>
<!-- Menu -->
<?php include 'template/menu.php'; ?>

 <!-- Sección Competencias -->
  <div class="container">
  <div class="mb-5 mt-5">
    <h4>Competencias</h4>
    <ul class="list-group mb-2">
      <?php foreach ($competencias as $c): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <?= htmlspecialchars($c['nombre_evento']) ?>
            <small class="text-muted">(<?= htmlspecialchars($c['zona']) ?> - <?= htmlspecialchars($c['fecha_inicio']) ?>)</small>
          </span>
          <div>
            <a href="template/competencia/ver_competencia.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Ver Inscritos</a>
            <a href="editar_competencia.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary">Editar</a>
            <a href="eliminar_competencia.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar competencia?');">Eliminar</a>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
    <a href="crear_competencia.php" class="btn btn-success">+ Nueva Competencia</a>
  </div>
      </div>