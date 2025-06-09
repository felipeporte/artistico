<?php
include __DIR__ . '/conexion.php';

$token = $_GET['token'] ?? null;
$mensaje = '';
$mostrar_formulario = false;

if ($token) {
    $stmt = $pdo->prepare("SELECT id, token_expira FROM clubs WHERE token_recuperacion = ?");
    $stmt->execute([$token]);
    $club = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($club && strtotime($club['token_expira']) > time()) {
        $mostrar_formulario = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nueva = $_POST['clave'] ?? '';
            $confirmar = $_POST['confirmar'] ?? '';

            if ($nueva === $confirmar && strlen($nueva) >= 6) {
                $hash = hash('sha256', $nueva);
                $stmt = $pdo->prepare("UPDATE clubs SET contraseña_hash = ?, token_recuperacion = NULL, token_expira = NULL WHERE id = ?");
                $stmt->execute([$hash, $club['id']]);
                $mensaje = "Clave actualizada correctamente. Redirigiendo al login...";
header("refresh:3;url=login.php");
                $mostrar_formulario = false;
            } else {
                $mensaje = "Las contraseñas no coinciden o son muy cortas.";
            }
        }
    } else {
        $mensaje = "El enlace ha expirado o no es válido.";
    }
} else {
    $mensaje = "Token no proporcionado.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cambiar clave</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <?php include __DIR__ . '/../templates/header.php'; ?>
  <main class="login">
    <section class="hero">
      <div class="container">
        <h1>Crear nueva clave</h1>
        <?php if ($mensaje): ?>
            <p class='mensaje'><?= $mensaje ?></p>
  <?php if (!$mostrar_formulario): ?>
    <p><a href="login.php" class="btn">Ir al login ahora</a></p>
  <?php endif; ?>
<?php endif; ?>

        <?php if ($mostrar_formulario): ?>
        <form method="POST" class="form-login">
          <label for="clave">Nueva clave</label>
          <input type="password" id="clave" name="clave" required>

          <label for="confirmar">Confirmar clave</label>
          <input type="password" id="confirmar" name="confirmar" required>

          <button type="submit" class="btn">Actualizar</button>
        </form>
        <?php endif; ?>
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
