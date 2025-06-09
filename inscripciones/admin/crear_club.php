<?php
// admin/crear_club.php
// Permite al administrador crear un nuevo club

session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/../conexion.php';
date_default_timezone_set('America/Santiago');

$errors = [];

// Procesar formulario al enviar (POST)
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

    // Si no hay errores, insertar nuevo club
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO clubs
             (nombre_club, rut, zona, nombre_presidente, email_responsable)
             VALUES
             (:nombre, :rut, :zona, :pres, :email)"
        );
        $stmt->execute([
            ':nombre' => $nombre_club,
            ':rut'    => $rut,
            ':zona'   => $zona,
            ':pres'   => $presidente,
            ':email'  => $email_responsable,
        ]);

        // Redirigir al dashboard con mensaje de éxito
        header('Location: dashboard.php?msg=Club+creado+correctamente');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Crear Club</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
  <div class="container bg-white p-4 rounded shadow">
    <h2>Crear Nuevo Club</h2>

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
               value="<?= htmlspecialchars($_POST['nombre_club'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label for="rut" class="form-label">RUT</label>
        <input type="text" name="rut" id="rut" class="form-control"
               value="<?= htmlspecialchars($_POST['rut'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label for="zona" class="form-label">Zona</label>
        <input type="text" name="zona" id="zona" class="form-control"
               value="<?= htmlspecialchars($_POST['zona'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label for="nombre_presidente" class="form-label">Presidente</label>
        <input type="text" name="nombre_presidente" id="nombre_presidente" class="form-control"
               value="<?= htmlspecialchars($_POST['nombre_presidente'] ?? '') ?>" required>
      </div>
      <div class="col-12">
        <label for="email_responsable" class="form-label">Email de Contacto</label>
        <input type="email" name="email_responsable" id="email_responsable" class="form-control"
               value="<?= htmlspecialchars($_POST['email_responsable'] ?? '') ?>" required>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary">Crear Club</button>
        <a href="dashboard.php" class="btn btn-secondary ms-2">Cancelar</a>
      </div>
    </form>
  </div>
</body>
</html>
