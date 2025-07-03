<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Comision Tecnica - Patinaje Artistico – Herramientas</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="js/main.js"></script>
</head>
<body>
    <?php include __DIR__.'/templates/header.php'; ?>
  <main>
    <section class="hero">
      <div class="container">
        <h1>Comisión Técnica de Patinaje Artístico.</h1>
        <p>Gestión de Competencias para el año 2025.</p>
      </div>
    </section>

    <section id="inscripciones" class="features">
      <div class="container">
        <!-- 1º Copa de Figuras -->
        <article class="card">
          <h2>1º Copa de Figuras</h2>
          <p><strong>02/05 – 04/05</strong> · Estadio Nacional</p>
          <a href="pages/1figura.php" class="btn">Ir a Evento</a>
        </article>

        <!-- AIS Trieste -->
        <article class="card">
          <h2>AIS Trieste</h2>
          <p><strong>12/05 – 18/05</strong> · Trieste (ITA)</p>
          <a href="pages/inscripciones.html" class="btn disabled" aria-disabled="true">No disponible</a>
        </article>

        <!-- Clasificatorio Panamericano -->
        <article class="card">
          <h2>Clasificatorio Panamericano</h2>
          <p><strong>22/05 – 25/05</strong> · Santiago</p>
          <a href="pages/1selectivo-panam.php" class="btn" >Ver Más</a>
        </article>

          <!-- Open -->
          <article class="card">
          <h2>Open Panamericano</h2>
          <p><strong>Comunicado y documentos</strong></p>
          <p><strong>26/06 – 06/07</strong> · Buenos Aires</p>
          <a href="pages/eventos/open-panam25.php" class="btn" >Ver Más</a>
        </article>

        <!-- 1º Ranking Formativo -->
        <article class="card">
          <h2>1º Ranking Formativo, Intermedio y Escuelas</h2>
          <p><strong>20/05 – 01/06</strong> · Zona Norcosta</p>
          <a href="pages/inscripciones.html" class="btn disabled" aria-disabled="true">No disponible</a>
        </article>

        <!-- Final Mundial WorldsKATE -->
        <article class="card">
          <h2>Final WorldsKATE Clasificados</h2>
          <p><strong>09/06 – 15/06</strong> · Europa</p>
          <a href="pages/inscripciones.html" class="btn disabled" aria-disabled="true">No disponible</a>
        </article>

        <!-- Y así sucesivamente... -->
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
