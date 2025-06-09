<?php
// registrar_deportista.php  (o en tu panel_club.php)
session_start();
if (!isset($_SESSION['club_id'])) {
    header("Location: login.php");
    exit;
}

// 1. Conectar a la BD
require __DIR__ . '/conexion.php'; // Debe definir $pdo (PDO)

// 2. Recuperar el club actual
$club_id = $_SESSION['club_id'];

// 3. Consultar los datos del club
$stmt = $pdo->prepare("
    SELECT 
      rut, 
      zona, 
      nombre_presidente AS presidente, 
      email_responsable 
    FROM clubs 
    WHERE id = :id
");
$stmt->execute([':id' => $club_id]);
$club = $stmt->fetch(PDO::FETCH_ASSOC);

// 4. Si no existe, redirigir o mostrar mensaje
if (!$club) {
    echo "Club no encontrado.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel del Club</title>
  <link rel="stylesheet" href="../css/style.css">
  <script src="../js/main.js"></script>
</head>
<body>
  <?php include __DIR__ . '/../templates/header.php'; ?>

  <main class="login">
    <section class="hero">
      <div class="container">
        <h1>Bienvenido, <?= htmlspecialchars($_SESSION['club_nombre']); ?>!</h1>
        
        <!-- 5. Mostrar datos del club -->
        <div class="datos-club" style="margin: 1.5rem 0; text-align: center;">
          <h2>Datos de tu club</h2>
          <ul style="list-style: none; padding: 0; line-height: 1.6;">
            <li><strong>RUT:</strong> <?= htmlspecialchars($club['rut']); ?></li>
            <li><strong>Zona:</strong> <?= htmlspecialchars($club['zona']); ?></li>
            <li><strong>Presidente:</strong> <?= htmlspecialchars($club['presidente']); ?></li>
            <li><strong>Email:</strong> <?= htmlspecialchars($club['email_responsable']); ?></li>
          </ul>
        </div>

        <div class="panel-opciones" >
          <a href="registrar_deportista.php" class="btn">Registrar Deportista</a>
          <a href="inscribir.php" class="btn">Inscribir a Competencia</a>
          <a href="ver_inscripciones.php" class="btn">Ver Inscripciones</a>
          <a href="logout.php" class="btn btn-outline">Cerrar sesión</a>
        </div>
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
