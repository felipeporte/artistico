<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Selectivo Panamericano – Federación Chilena de Patinaje</title>
  <!-- Reutiliza tus estilos -->
  <link rel="stylesheet" href="/css/style.css">
  <script src="/js/main.js"></script>

  <style>
    .accordion { margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 8px; overflow: hidden; }
    .accordion summary {
      background: #1e73be; color: white; padding: 1rem;
      font-weight: bold; cursor: pointer;
    }
    .accordion table {
      width: 100%; border-collapse: collapse;
    }
    .accordion th, .accordion td {
      border: 1px solid #ddd; padding: 0.75rem; text-align: left;
    }
    .accordion tbody tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    @media screen and (max-width: 768px) {
      .accordion table, .accordion thead, .accordion tbody, .accordion th, .accordion td, .accordion tr {
        display: block;
      }
      .accordion tr {
        margin-bottom: 1rem;
        background: #fff;
        border: 1px solid #ddd;
        padding: 0.5rem;
        border-radius: 6px;
      }
      .accordion td::before {
        content: attr(data-label);
        font-weight: bold;
        display: block;
        margin-bottom: 4px;
        color: #333;
      }
    }
  </style>
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



    <!-- Entrenamientos Oficiales -->
    <?php
    require '../inscripciones/conexion.php';
    setlocale(LC_TIME, 'es_CL.UTF-8'); // Para servidores en Chile

    $competencia_id = 5;
    $stmt = $pdo->prepare("SELECT fecha, hora, clubes, cantidad_deportistas, duracion FROM entrenamientos WHERE competencia_id = ? ORDER BY fecha, hora");
    $stmt->execute([$competencia_id]);
    $entrenamientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $por_fecha = [];
    foreach ($entrenamientos as $e) {
      $f = $e['fecha'];
      $por_fecha[$f][] = $e;
    }
    ?>

    <section class="form-login">
      <h2>Entrenamientos Oficiales</h2>

      <?php foreach ($por_fecha as $fecha => $bloque): ?>
        <details class="accordion" open>
          <summary><?= ucfirst(strftime("%A %d %B %Y", strtotime($fecha))) ?></summary>
          <table class="tabla">
            <thead>
              <tr>
                <th>Hora</th>
                <th>Clubes</th>
                <th># Deportistas</th>
                <th>Duración</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bloque as $e): ?>
                <tr>
                  <td data-label="Hora"><?= $e['hora'] ?></td>
                  <td data-label="Clubes"><?= htmlspecialchars($e['clubes']) ?></td>
                  <td data-label="Deportistas"><?= $e['cantidad_deportistas'] ?? '-' ?></td>
                  <td data-label="Duración"><?= $e['duracion'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </details>
      <?php endforeach; ?>
    </section>

  </main>

  <footer class="site-footer">
    <div class="container">
      <p>© 2025 Federación Chilena de Patinaje. Todos los derechos reservados.</p>
    </div>
  </footer>
</body>
</html>
