<?php
session_start();
include __DIR__ . '/conexion.php';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Recortar espacios al inicio y al final
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $clave = isset($_POST['clave']) ? trim($_POST['clave']) : '';

    // 2) Validar que no vengan vacíos después del trim
    if ($email === '' || $clave === '') {
        $mensaje = "Por favor completa ambos campos sin espacios adicionales.";
    } else {
        // 3) Intentar cargar el club
        $stmt = $pdo->prepare("
            SELECT id, nombre_club, contraseña_hash 
              FROM clubs 
             WHERE email_responsable = ? 
               AND activo = 1
        ");
        $stmt->execute([$email]);
        $club = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($club) {
            // 4) Verificar si tiene hash o está pendiente
            if (is_null($club['contraseña_hash'])) {
                $mensaje = "Aún no has creado tu clave. Solicítala <a href='solicitar_clave.php'>aquí</a>.";
            } elseif (hash('sha256', $clave) === $club['contraseña_hash']) {
                // 5) Login exitoso
                $_SESSION['club_id']     = $club['id'];
                $_SESSION['club_nombre'] = $club['nombre_club'];
                header("Location: panel_club.php");
                exit;
            } else {
                $mensaje = "Clave incorrecta.";
            }
        } else {
            $mensaje = "Correo no encontrado o cuenta desactivada.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ingreso Club</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <?php include __DIR__ . '/../templates/header.php'; ?>
  <main class="login">
    <section class="hero">
      <div class="container">
        <h1>Ingreso de Clubes</h1>
        <p>Ingrese sus credenciales para inscribir deportistas.</p>

        <?php if ($mensaje): ?>
          <div class="error" style="color:#721c24; background:#f8d7da; padding:1rem; border-radius:5px; margin-bottom:1rem;">
            <?= $mensaje // ya contiene HTML para el enlace ?>
          </div>
        <?php endif; ?>

        <form method="POST" class="form-login" novalidate>
          <label for="email">Correo registrado</label>
          <input 
            type="email" 
            id="email" 
            name="email" 
            required 
            value="<?= isset($email) ? htmlspecialchars($email) : '' ?>"
            placeholder="ejemplo@club.cl"
          >

          <label for="clave">Clave</label>
          <input 
            type="password" 
            id="clave" 
            name="clave" 
            required
            placeholder="Tu clave"
          >

          <button type="submit" class="btn">Entrar</button>
        </form>

        <p style="margin-top:1rem;">
          ¿Olvidaste tu clave o aún no tienes una? 
          <a href="solicitar_clave.php">Solicítala aquí</a>
        </p>
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
