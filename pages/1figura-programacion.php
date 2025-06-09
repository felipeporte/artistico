<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Programación – Copa de Figuras</title>
  <link rel="stylesheet" href="../css/style.css">
  
</head>
<body>
  <!-- Header -->
  <?php include $_SERVER['DOCUMENT_ROOT'].'/templates/header.php'; ?>

  <main>
    <!-- Hero -->
    <section class="hero">
      <div class="container">
        <h1>Programación Oficial</h1>
        <p>1º Copa de Figuras – Estadio Nacional</p>
        <p><a href="../anexos/figura1-programacion.pdf" target="_blank" class="btn">Ver PDF</a></p>
      </div>
    </section>

    <!-- Tabs & Contenido -->
    <section class="features">
      <div class="container">
        <div class="tabs">
          <button class="tab-button active" data-day="viernes">Viernes 2/05<br> (prueba de pista)</button>
          <button class="tab-button" data-day="sabado">Sábado 3/05</button>
          <button class="tab-button" data-day="domingo">Domingo 4/05</button>
        </div>

        <div class="tab-contents">
          <!-- Viernes -->
          <div class="tab-content" id="viernes">
            <ul>
              <li>15:00 – FAM, Jardín del Mar, SDA, Peñalolén, Maiteam (8 deportistas) – 0:30</li>
              <li>15:30 – Los Vilos, USACH (8 deportistas) – 0:30</li>
              <li>16:00 – Everton (8 deportistas) – 0:30</li>
              <li>16:30 – Cristal, Salesianos (8 deportistas) – 0:30</li>
              <li>17:00 – U. de Chile (9 deportistas) – 0:30</li>
              <li>17:30 – Illari, Huasco (8 deportistas) – 0:30</li>
              <li>18:00 – Huasco (9 deportistas) – 0:30</li>
              <li>18:30 – Águilas, Wings and Skates (7 deportistas) – 0:30</li>
              <li>19:00 – Precision Center, Copiapó G1 (8 deportistas) – 0:30</li>
              <li>19:30 – Amancay, Copiapó G2 (8 deportistas) – 0:30</li>
              <li>20:00 – Roller King, Team Nazasport (8 deportistas) – 0:30</li>
              <li>20:30 – <strong>Fin Entrenamientos Oficiales</strong></li>
            </ul>
          </div>

          <!-- Sábado -->
          <div class="tab-content hidden" id="sabado">
            <ul>
              <li>08:30 – Figuras Senior Ladies Intermediate (3 deportistas) – 0:45</li>
              <li>09:15 – Figuras Junior Ladies Intermediate (7 deportistas) – 1:45</li>
              <li>11:00 – Break – 0:15</li>
              <li>11:15 – Figuras Youth Ladies Basic (5 deportistas) – 1:15</li>
              <li>12:30 – Figuras Senior Ladies Basic (3 deportistas) – 0:45</li>
              <li>13:15 – Receso Almuerzo & Premiación Jornada AM – 1:00</li>
              <li>14:15 – Figuras Espoir Ladies Basic (3 deportistas) – 0:45</li>
              <li>15:00 – Figuras Espoir Ladies Avanzada (2 deportistas) – 0:30</li>
              <li>15:30 – Figuras Junior Ladies Avanzada (2 deportistas) – 0:30</li>
              <li>16:00 – Figuras Cadet Ladies Avanzada (3 deportistas) – 0:45</li>
              <li>16:45 – Figuras Cadet Ladies Internacional (1 deportista) – 0:20</li>
              <li>17:05 – Break & Premiación Jornada PM – 0:15</li>
              <li>17:20 – Figuras Youth Ladies Internacional (5 deportistas) – 1:40</li>
              <li>19:00 – Figuras Junior Ladies Internacional (3 deportistas) – 0:45</li>
              <li>19:45 – Figuras Senior Ladies Internacional (4 deportistas) – 1:00</li>
              <li>20:45 – <strong>Fin y Premiación Jornada PM</strong></li>
            </ul>
          </div>

          <!-- Domingo -->
          <div class="tab-content hidden" id="domingo">
            <ul>
              <li>08:30 – Figuras Senior Ladies Escuela D (3 deportistas) – 0:36</li>
              <li>09:06 – Figuras Senior Ladies Escuela C (2 deportistas) – 0:30</li>
              <li>09:36 – Figuras Cadet Ladies Escuela D (8 deportistas) – 1:36</li>
              <li>11:12 – Break – 0:15</li>
              <li>11:30 – Figuras Youth Ladies Escuela D (4 deportistas) – 0:48</li>
              <li>12:18 – Figuras Cadet Ladies Escuela C (3 deportistas) – 0:45</li>
              <li>13:00 – Receso Almuerzo & Premiación Jornada AM – 1:00</li>
              <li>14:00 – Figuras Youth Ladies Escuela C (2 deportistas) – 0:30</li>
              <li>14:30 – Figuras Mini Ladies Escuela D (7 deportistas) – 1:24</li>
              <li>15:54 – Figuras Espoir Ladies Escuela D (6 deportistas) – 1:12</li>
              <li>17:32 – Break & Premiación Jornada PM – 0:15</li>
              <li>17:47 – Figuras Mini Ladies Escuela C (6 deportistas) – 1:30</li>
              <li>19:17 – Figuras Tots Ladies Basic (1 deportista) – 0:12</li>
              <li>19:30 – Figuras Tots Ladies Escuela C (2 deportistas) – 0:24</li>
              <li>19:54 – Figuras Mini Ladies Avanzada (1 deportista) – 0:12</li>
              <li>20:06 – Figuras Espoir Ladies Escuela C (2 deportistas) – 0:30</li>
              <li>20:36 – <strong>Fin y Premiación Jornada PM</strong></li>
            </ul>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>© 2025 Federación Chilena de Patinaje. Todos los derechos reservados.</p>
    </div>
  </footer>

  <script src="../js/main.js"></script>
</body>
</html>
