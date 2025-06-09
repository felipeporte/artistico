<?php
date_default_timezone_set('America/Santiago');
// Fecha límite: último segundo del 24 de abril de 2025
$deadline = strtotime('2025-04-24 23:59:59');
$now      = time();
$open     = $now <= $deadline;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Inscripciones Competencia 2025</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #eaeaea, #fddde6);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }
    .container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      width: 90%;
      max-width: 800px;
      justify-content: center;
    }
    .card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 24px;
      flex: 1 1 280px;
      text-align: center;
    }
    .card h2 {
      margin-top: 0;
      margin-bottom: 16px;
      color: #BD1E2D;
      font-size: 1.4em;
    }
    .btn {
      display: inline-block;
      margin: 8px 0;
      padding: 12px 20px;
      background: #BD1E2D;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-size: 1em;
    }
    .btn:hover {
      background: #9a1a23;
    }
    .btn.disabled {
      background:#ccc; cursor:not-allowed; opacity:0.6;
      pointer-events:none;
    }
    .link {
      display: block;
      margin-top: 12px;
      color: #007BFF;
      text-decoration: none;
      font-size: 0.9em;
      border-bottom: 2px solid #007BFF;
      padding-bottom: 2px;
    }
    .link:hover {
      color: #0056b3;
      border-bottom-color: #0056b3;
    }
  </style>
</head>
<body>
  <div class="container">

    <!-- Tarjeta de Inscripción -->
    <div class="card">
      <h2>Inscripciones</h2>
      <?php if($open): ?>
        <a href="formulario.php" class="btn">Ir al Formulario</a>
      <?php else: ?>
        <a class="btn disabled">Inscripciones cerradas</a>
      <?php endif; ?>
     <!--  <a href="listado.php" class="link">Ver listado de inscripciones</a> -->
    </div>

    <!-- Tarjeta de Recursos -->
    <div class="card">
      <h2>1er Copa Figura - 2025 </h2>
      <a href="com1-1copafigura.pdf" class="btn" target="_blank">
        Descargar documento oficial (PDF)
      </a>
      <a href="https://meet.google.com/ycc-qxxk-wyx" class="link" target="_blank">
        Sorteo de la competencia<br><strong>25/04/2025</strong>
      </a>
    </div>

  </div>
</body>
</html>
