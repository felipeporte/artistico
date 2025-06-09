<?php
session_start();
if (!isset($_SESSION['club_id'])) {
    header("Location: login.php");
    exit;
}
include __DIR__ . '/conexion.php';

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre_completo'] ?? '';
    $rut = $_POST['rut'] ?? '';
    $fecha = $_POST['fecha_nacimiento'] ?? '';
    $genero = $_POST['genero'] ?? '';

    if ($nombre && $fecha && $rut && $genero) {
        $stmt = $pdo->prepare("INSERT INTO deportistas (club_id, rut, nombre_completo, fecha_nacimiento, genero) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['club_id'], $rut, $nombre, $fecha, $genero]);
        $mensaje = "Deportista registrado correctamente.";
    } else {
        $mensaje = "Todos los campos son obligatorios.";
    } 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Deportista</title>
  <link rel="stylesheet" href="../css/style.css">
  <script src="../js/main.js"></script>
  <style>
    .form-login-wrapper {
      display: flex;
      justify-content: center;
    }
    .form-login {
      width: 100%;
      max-width: 600px;
    }
    .form-login .form-group {
      display: flex;
      align-items: center;
      margin-bottom: 1rem;
    }
    .form-login .form-group label {
      flex: 0 0 150px;
      text-align: right;
      margin-right: 1rem;
    }
    .form-login .form-group .form-control {
      flex: 1;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../templates/header.php'; ?>
  <main class="login">
    <section class="hero">
      <div class="container">
        <h1 class="mb-4 text-center">Registrar nuevo deportista</h1>
        <?php if ($mensaje): ?>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      const toast = document.createElement('div');
      toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed bottom-0 end-0 m-3 show';
      toast.setAttribute('role', 'alert');
      toast.innerHTML = `
        <div class="d-flex">
          <div class="toast-body"><?= $mensaje ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>`;
      document.body.appendChild(toast);
    });
  </script>
<?php endif; ?>
        <div class="form-login-wrapper">
          <form method="POST" class="form-login needs-validation card p-4 shadow bg-white rounded" novalidate>
            <div class="form-group">
              <label for="nombre">Nombre completo</label>
              <input type="text" id="nombre" name="nombre_completo" required class="form-control">
            </div>

            <div class="form-group">
              <label for="rut">RUT</label>
              <input type="text" id="rut" name="rut" required class="form-control">
            </div>

            <div class="form-group">
              <label for="fecha">Fecha de nacimiento</label>
              <input type="date" id="fecha" name="fecha_nacimiento" required class="form-control">
            </div>

            <div class="form-group">
              <label for="genero">Género</label>
              <select id="genero" name="genero" class="form-control" required>
                <option value="">Seleccione</option>
                <option value="F">Femenino</option>
                <option value="M">Masculino</option>
              </select>
            </div>

            <div class="d-grid mt-3">
              <button type="submit" class="btn btn-primary">Registrar</button>
            </div>
          </form>
        </div>
        <p class="text-center mt-3"><a href="panel_club.php">Volver al panel</a></p>
      </div>
      <hr>
      <h2 class="text-center">Deportistas del club</h2>
      <div id="lista-deportistas" class="container">Cargando...</div>
      <script>
        document.addEventListener("DOMContentLoaded", () => {
          fetch("cargar_deportistas.php")
            .then(res => res.text())
            .then(html => document.getElementById("lista-deportistas").innerHTML = html)
            .catch(err => document.getElementById("lista-deportistas").innerText = "Error al cargar lista de deportistas.");
        });
      </script>
    </section>
  </main>
  <footer class="site-footer">
    <div class="container">
      <p>© 2025 Federación Chilena de Patinaje. Todos los derechos reservados.</p>
    </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Bootstrap validation
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
</body>
</html>
