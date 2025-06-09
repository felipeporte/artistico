<?php
session_start();
require 'auth.php';  // Definiciones de AUTH_USER/AUTH_PASS y manejo de sesión

// Si ya está autenticado, redirigir a sorteo
if (!empty($_SESSION['logged_in'])) {
    header('Location: sorteo.php');
    exit;
}

// Procesar intento de login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    if ($user === AUTH_USER && $pass === AUTH_PASS) {
        $_SESSION['logged_in'] = true;
        header('Location: sorteo.php');
        exit;
    } else {
        $error = 'Credenciales incorrectas.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Sorteo</title>
  <style>
    body { display: flex; justify-content: center; align-items: center;
           height: 100vh; margin: 0; background: #f0f2f5; }
    .login-box {
      background: white; padding: 20px; border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1); width: 300px;
      font-family: sans-serif;
    }
    .login-box h2 { text-align: center; margin-bottom: 20px; }
    .login-box input {
      width: 100%; padding: 8px; margin: 8px 0;
      border: 1px solid #ccc; border-radius: 4px;
    }
    .login-box button {
      width: 100%; padding: 10px; background: #4a90e2;
      color: white; border: none; border-radius: 4px;
      cursor: pointer; font-size: 1rem;
    }
    .login-box button:hover { background: #357ab8; }
    .error { color: red; font-size: 0.9rem; text-align: center; }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>Iniciar Sesión</h2>
    <?php if ($error) { echo '<div class="error">'.htmlspecialchars($error).'</div>'; } ?>
    <form method="post">
      <input type="text" name="user" placeholder="Usuario" required>
      <input type="password" name="pass" placeholder="Contraseña" required>
      <button type="submit" name="login">Entrar</button>
    </form>
  </div>
</body>
</html>
