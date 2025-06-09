<?php
session_start();
$clave_federacion = 'comisiontecnica2025!';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['clave'] === $clave_federacion) {
        $_SESSION['admin'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Clave incorrecta";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ingreso administrador</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
<div class="container form-container bg-white p-4 rounded shadow">
  <h2>Ingreso administrador</h2>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Clave</label>
      <input type="password" name="clave" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Entrar</button>
  </form>
  <?php if (isset($error)) echo "<div class='alert alert-danger mt-3'>$error</div>"; ?>
</div>
</body>
</html>
