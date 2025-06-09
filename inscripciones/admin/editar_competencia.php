<?php
// admin/editar_competencia.php
// Permite al administrador editar una competencia existente

session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/../conexion.php';

date_default_timezone_set('America/Santiago');

// Obtener ID de la competencia
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    exit('ID de competencia inválido');
}

$errors = [];

// Si se envió el formulario (POST), procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_evento = trim($_POST['nombre_evento'] ?? '');
    $fecha_inicio  = trim($_POST['fecha_inicio'] ?? '');
    $zona          = trim($_POST['zona'] ?? '');

    // Validaciones básicas
    if ($nombre_evento === '') {
        $errors[] = 'El nombre del evento es obligatorio.';
    }
    if ($fecha_inicio === '') {
        $errors[] = 'La fecha de inicio es obligatoria.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) {
        $errors[] = 'Formato de fecha inválido. Use AAAA-MM-DD.';
    }
    if ($zona === '') {
        $errors[] = 'La zona es obligatoria.';
    }

    if (empty($errors)) {
        // Actualizar en la base de datos
        $stmt = $pdo->prepare(
            "UPDATE competencias
               SET nombre_evento = :nombre,
                   fecha_inicio  = :fecha,
                   zona          = :zona
             WHERE id = :id"
        );
        $stmt->execute([
            ':nombre' => $nombre_evento,
            ':fecha'  => $fecha_inicio,
            ':zona'   => $zona,
            ':id'     => $id
        ]);

        // Redirigir al dashboard o a la vista de competencia
        header("Location: ver_competencia.php?id={$id}");
        exit;
    }
}

// Si es GET o hay errores, cargar datos actuales
$stmt = $pdo->prepare("SELECT nombre_evento, fecha_inicio, zona FROM competencias WHERE id = ?");
$stmt->execute([$id]);
$comp = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$comp) {
    exit('Competencia no encontrada.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Competencia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
  <div class="container bg-white p-4 rounded shadow">
    <h2>Editar Competencia</h2>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" class="row g-3">
      <div class="col-12">
        <label for="nombre_evento" class="form-label">Nombre del Evento</label>
        <input type="text" name="nombre_evento" id="nombre_evento"
               class="form-control"
               value="<?= htmlspecialchars($_POST['nombre_evento'] ?? $comp['nombre_evento']) ?>" required>
      </div>
      <div class="col-md-6">
        <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
        <input type="date" name="fecha_inicio" id="fecha_inicio"
               class="form-control"
               value="<?= htmlspecialchars($_POST['fecha_inicio'] ?? $comp['fecha_inicio']) ?>" required>
      </div>
      <div class="col-md-6">
        <label for="zona" class="form-label">Zona</label>
        <input type="text" name="zona" id="zona"
               class="form-control"
               value="<?= htmlspecialchars($_POST['zona'] ?? $comp['zona']) ?>" required>
      </div>
      
      <div class="col-12">
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="dashboard.php" class="btn btn-secondary ms-2">Cancelar</a>
      </div>
    </form>
  </div>
</body>
</html>
