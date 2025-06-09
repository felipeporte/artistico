<?php
// admin/editar_club.php
// Permite al administrador editar la información de un club

session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/../conexion.php';
date_default_timezone_set('America/Santiago');

// 1) Leer ID del club a editar
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    exit('ID de club inválido');
}

$errors = [];

// 2) Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_club       = trim($_POST['nombre_club'] ?? '');
    $rut                = trim($_POST['rut'] ?? '');
    $zona               = trim($_POST['zona'] ?? '');
    $presidente         = trim($_POST['nombre_presidente'] ?? '');
    $email_responsable  = trim($_POST['email_responsable'] ?? '');

    // Validaciones
    if ($nombre_club === '') {
        $errors[] = 'El nombre del club es obligatorio.';
    }
    if ($rut === '') {
        $errors[] = 'El RUT es obligatorio.';
    }
    if ($zona === '') {
        $errors[] = 'La zona es obligatoria.';
    }
    if ($presidente === '') {
        $errors[] = 'El nombre del presidente es obligatorio.';
    }
    if ($email_responsable === '') {
        $errors[] = 'El email de contacto es obligatorio.';
    } elseif (!filter_var($email_responsable, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email de contacto no tiene un formato válido.';
    }

    // Si no hay errores, actualizar
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "UPDATE clubs
                SET nombre_club       = :nombre,
                    rut               = :rut,
                    zona              = :zona,
                    nombre_presidente = :pres,
                    email_responsable = :email
              WHERE id = :id"
        );
        $stmt->execute([
            ':nombre' => $nombre_club,
            ':rut'    => $rut,
            ':zona'   => $zona,
            ':pres'   => $presidente,
            ':email'  => $email_responsable,
            ':id'     => $id
        ]);
        header('Location: dashboard.php?msg=Club+actualizado+correctamente');
        exit;
    }
}

// 3) Si es GET o hubo errores, cargar datos actuales
$stmt = $pdo->prepare(
    "SELECT nombre_club, rut, zona, nombre_presidente, email_responsable
       FROM clubs
      WHERE id = ?"
);
$stmt->execute([$id]);
$club = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$club) {
    exit('Club no encontrado.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Club</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
  <div class="container bg-white p-4 rounded shadow">
    <h2>Editar Club</h2>

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
      <div class="col-md-6">
        <label for="nombre_club" class="form-label">Nombre del Club</label>
        <input type="text" name="nombre_club" id="nombre_club" class="form-control"
               value="<?= htmlspecialchars($_POST['nombre_club'] ?? $club['nombre_club']) ?>" required>
      </div>
      <div class="col-md-6">
        <label for="rut" class="form-label">RUT</label>
        <input type="text" name="rut" id="rut" class="form-control"
               value="<?= htmlspecialchars($_POST['rut'] ?? $club['rut']) ?>" required>
      </div>
      <div class="col-md-6">
        <label for="zona" class="form-label">Zona</label>
        <input type="text" name="zona" id="zona" class="form-control"
               value="<?= htmlspecialchars($_POST['zona'] ?? $club['zona']) ?>" required>
      </div>
      <div class="col-md-6">
        <label for="nombre_presidente" class="form-label">Presidente</label>
        <input type="text" name="nombre_presidente" id="nombre_presidente" class="form-control"
               value="<?= htmlspecialchars($_POST['nombre_presidente'] ?? $club['nombre_presidente']) ?>" required>
      </div>
      <div class="col-12">
        <label for="email_responsable" class="form-label">Email de Contacto</label>
        <input type="email" name="email_responsable" id="email_responsable" class="form-control"
               value="<?= htmlspecialchars($_POST['email_responsable'] ?? $club['email_responsable']) ?>" required>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="dashboard.php" class="btn btn-secondary ms-2">Cancelar</a>
      </div>
    </form>
  </div>
</body>
</html>
