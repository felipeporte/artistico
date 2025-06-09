<?php
// panel_club.php
// Panel principal del club con datos, opciones y generación de pago

// Habilitar debug (opcional)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['club_id'])) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('America/Santiago');
include __DIR__ . '/conexion.php';

// Recuperar datos del club
$club_id = $_SESSION['club_id'];
$stmt = $pdo->prepare("SELECT rut, zona, nombre_presidente AS presidente, email_responsable, nombre_club FROM clubs WHERE id = ?");
$stmt->execute([$club_id]);
$club = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$club) {
    echo "<p style='color:red;'>Club no encontrado.</p>";
    exit;
}

// Nombre del club
$club_nombre = $club['nombre_club'];

// Cargar competencias
$stmt2 = $pdo->prepare(
    "SELECT id, nombre_evento FROM competencias WHERE zona = :zona OR zona = 'TODAS' ORDER BY fecha_inicio DESC"
);
$stmt2->execute([':zona' => $club['zona']]);
$competencias = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel del Club</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    /* Datos del club */
    .datos-club { margin: 1.5rem 0; text-align: center; }
    .datos-club ul { list-style: none; padding: 0; line-height: 1.6; }

    /* Botones del panel */
    .panel-opciones { display: flex; flex-wrap: wrap; justify-content: center; gap: 1rem; margin: 2rem 0; }
    .btn, .btn-outline {
      padding: .75rem 1.5rem;
      border-radius: 5px;
      font-size: 1rem;
      text-decoration: none;
      display: inline-block;
      min-width: 200px;
      text-align: center;
    }
    .btn { background: #c8102e; color: #fff; }
    .btn:hover { background: #a10a24; }
    .btn-outline { background: transparent; border: 2px solid #c8102e; color: #c8102e; }
    .btn-outline:hover { background: #c8102e; color: #fff; }

    /* Tarjeta de pago estilo card */
    .form-pago {
      background: #fff;
      color: #333;
      padding: 1.5rem;
      border-radius: 8px;
      margin: 2rem auto;
      max-width: 400px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .form-pago h2 {
      margin-top: 0;
      font-size: 1.25rem;
      color: #c8102e;
    }
    .form-pago label {
      display: block;
      margin-bottom: .5rem;
      font-weight: 600;
    }
    .form-pago select {
      width: 100%;
      padding: .75rem;
      margin-bottom: 1rem;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 1rem;
      background: #fff;
      color: #333;
    }
    .form-pago select:focus {
      outline: none;
      border-color: #c8102e;
      box-shadow: 0 0 0 2px rgba(30,56,166,0.2);
    }
    .form-pago .btn {
      width: 100%;
      background: #c8102e;
      color: #fff;
      font-weight: 600;
    }
    .form-pago .btn:hover {
      background: #a10a24;
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
      .panel-opciones { flex-direction: column; }
      .btn { min-width: auto; width: 100%; }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../templates/header.php'; ?>

  <main class="login">
    <section class="hero">
      <div class="container">
        <h1>Bienvenido, <?= htmlspecialchars($club_nombre) ?>!</h1>

        <!-- Datos del club -->
        <div class="datos-club">
          <h2>Datos de tu club</h2>
          <ul>
            <li><strong>RUT:</strong> <?= htmlspecialchars($club['rut']) ?></li>
            <li><strong>Zona:</strong> <?= htmlspecialchars($club['zona']) ?></li>
            <li><strong>Presidente:</strong> <?= htmlspecialchars($club['presidente']) ?></li>
            <li><strong>Email:</strong> <?= htmlspecialchars($club['email_responsable']) ?></li>
          </ul>
        </div>

        <!-- Opciones del panel -->
        <div class="panel-opciones">
          <a href="registrar_deportista.php" class="btn">Registrar Deportista</a>
          <a href="inscribir.php" class="btn">Inscribir a Competencia</a>
          <a href="ver_inscripciones.php" class="btn">Ver Inscripciones</a>
          <a href="logout.php" class="btn btn-outline">Cerrar sesión</a>
        </div>

        <!-- Bloque Generar pago estilo card -->
        <div class="form-pago">
          <h2>Generar Pago de Inscripciones</h2>
          <form action="generar_pago.php" method="GET">
            <label for="competencia_id">Selecciona el torneo:</label>
            <select name="competencia_id" id="competencia_id" required>
              <option value="" disabled selected>-- Elige competencia --</option>
              <?php foreach ($competencias as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre_evento']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Generar pago</button>
          </form>
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
