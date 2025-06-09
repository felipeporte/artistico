<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Selectivo Panamericano – Federación Chilena de Patinaje</title>
  <!-- Reutiliza tus estilos -->
  <link rel="stylesheet" href="/css/style.css">
  <script src="/js/main.js"></script>
</head>
<body>
  <!-- Header compartido -->
  <?php include $_SERVER['DOCUMENT_ROOT'].'/templates/header.php';?>

  <main>
    <!-- Hero -->
    <section class="hero">
      <div class="container">
        <h1>Selectivo Panamericano</h1>
        <p>Del 22/05/2025 al 25/05/2025 · Estadio Nacional</p>
      </div>
    </section>

    <!-- Enlaces a subpáginas -->
    <section class="features">
      <div class="container">
        <article class="card">
          <h2>Comunicado</h2>
          <p>2º RANKING Y SELECTIVO PANAMERICANO NACIONES</p>
          <a href="../anexos/SelectivoPanamericano.pdf" target="_blank" class="btn">Ver PDF</a>
        </article>
        <article class="card">
          <h2>Intrucciones Cuenta</h2>
          <p>Intructivo de como crear contraseña para inscripcion.</p>
          <a href="../anexos/CrearContraseña.pdf" class="btn" target="_blank">Ver PDF</a>
        </article>
        <article class="card">
          <h2>Entrenamiento Oficial</h2>
          <p>Programacion de prueba de pista (entrenamiento oficia)</p>
          <a href="1selectivo-entrenamiento.php" class="btn" >Ver</a>
        </article>
        <article class="card">
          <h2>Programación Oficial</h2>
          <p>Horarios oficiales de la competencia.</p>
          <a href="../anexos/ProgramacionSelectivo.pdf" class="btn" target="_blank">Ver PDF</a>
        </article>
        
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
