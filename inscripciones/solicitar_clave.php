<?php
include __DIR__ . '/conexion.php';
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/enviar_mail.php';

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['identificador']); // puede ser rut o correo

    $stmt = $pdo->prepare("SELECT id, email_responsable FROM clubs WHERE (email_responsable = ? OR rut = ?) AND activo = 1");
    $stmt->execute([$input, $input]);
    $club = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($club) {
        $token = bin2hex(random_bytes(32));
        $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $pdo->prepare("UPDATE clubs SET token_recuperacion = ?, token_expira = ? WHERE id = ?")
            ->execute([$token, $expira, $club['id']]);

        $link = "https://artistico.cl/inscripciones/cambiar_clave.php?token=$token";
        $resultado = enviarCorreoRecuperacion($club['email_responsable'], $link);

        $mensaje = ($resultado === true)
            ? "Se envió un correo a: {$club['email_responsable']}"
            : "Error al enviar: $resultado";
    } else {
        $mensaje = "No se encontró un club con ese RUT o correo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear o recuperar clave</title>
  <link rel="stylesheet" href="../css/style.css">
  <script src="../js/main.js"></script>
</head>
<body>
  <?php include __DIR__ . '/../templates/header.php'; ?>
  <main class="login">
    <section class="hero">
      <div class="container">
        <h1>Crear o recuperar clave</h1>
        <p>Ingrese el correo o RUT del club para generar un enlace de recuperación.</p>
        <form method="POST" class="form-login">
          <label for="identificador">Correo o RUT</label>
          <input type="text" name="identificador" id="identificador" required>

          <button type="submit" class="btn">Enviar enlace</button>
        </form>
        <?php if ($mensaje) echo "<p class='mensaje'>$mensaje</p>"; ?>
      </div>
    </section>
  </main>
  <footer class="site-footer">
    <div class="container">
      <p>© 2025 Federación Chilena de Patinaje. Todos los derechos reservados.</p>
    </div>
  </footer>
</body>
</html>
