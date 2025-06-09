<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO competencias (nombre_evento, fecha_inicio, fecha_fin, zona, modalidades, niveles, es_clasificatorio)
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nombre_evento'],
        $_POST['fecha_inicio'],
        $_POST['fecha_fin'],
        $_POST['zona'],
        json_encode($_POST['modalidades'] ?? []),
        json_encode($_POST['niveles'] ?? []),
        isset($_POST['clasificatorio']) ? 1 : 0
    ]);
    header('Location: crear_competencia.php#ok');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear competencia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
<div class="container bg-white p-4 rounded shadow">
  <h2>Crear competencia</h2>
  <form method="POST" class="needs-validation" novalidate>
    <div class="mb-3">
      <label class="form-label">Nombre</label>
      <input type="text" name="nombre_evento" class="form-control" required>
      <div class="invalid-feedback">Por favor ingrese un nombre.</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Fecha inicio</label>
      <input type="date" name="fecha_inicio" class="form-control" required>
      <div class="invalid-feedback">Seleccione una fecha de inicio.</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Fecha fin</label>
      <input type="date" name="fecha_fin" class="form-control" required>
      <div class="invalid-feedback">Seleccione una fecha de término.</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Zona</label>
<select name="zona" class="form-select" required>
        <option value="">Seleccione una zona</option>
  <option value="CENTRO">CENTRO</option>
  <option value="NORTE">NORTE</option>
  <option value="COSTA">COSTA</option>
  <option value="NORCOSTA">NORCOSTA</option>
  <option value="SUR">SUR</option>
  <option value="TODAS">TODAS</option>
</select>
    </div>
    <div class="mb-3">
      <label class="form-label">Modalidades</label><br>
      <?php
      $modalidades = ['freeskating','figuras','solo_dance','inline','pairs','couple_dance','show','quartets','precision'];
      foreach ($modalidades as $mod) {
          echo '<div class="form-check form-check-inline">';
          echo '<input class="form-check-input" type="checkbox" name="modalidades[]" value="' . $mod . '">';
          echo '<label class="form-check-label">' . ucfirst(str_replace('_', ' ', $mod)) . '</label>';
          echo '</div>';
      }
      ?>
    </div>
    <div class="mb-3">
      <label class="form-label">Niveles</label><br>
      <?php
      $niveles = ['formativo','escuela','promocional','internacional'];
      foreach ($niveles as $niv) {
          echo '<div class="form-check form-check-inline">';
          echo '<input class="form-check-input" type="checkbox" name="niveles[]" value="' . $niv . '">';
          echo '<label class="form-check-label">' . ucfirst($niv) . '</label>';
          echo '</div>';
      }
      ?>
    </div>
    <div class="mb-3 form-check">
      <input class="form-check-input" type="checkbox" name="clasificatorio">
      <label class="form-check-label">¿Clasificatorio?</label>
    </div>
    <button type="submit" class="btn btn-primary">Crear</button>
  </form>
</div>
<script>
// Validación visual de Bootstrap
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>
<script>
// Mostrar toast de éxito si fue insertado
if (window.location.hash === "#ok") {
  const toast = document.createElement("div");
  toast.className = "toast align-items-center text-white bg-success border-0 position-fixed bottom-0 end-0 m-3 show";
  toast.setAttribute("role", "alert");
  toast.innerHTML = `
    <div class="d-flex justify-content-between align-items-center w-100">
      <div class="toast-body">
        Competencia creada correctamente.
        <a href='dashboard.php' class='text-white fw-bold ms-2 text-decoration-underline'>Ir al panel</a>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
    </div>`;
  document.body.appendChild(toast);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
