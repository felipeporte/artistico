<?php
/**
 * resultados.php
 *
 * Lee el JSON de resultados generado, permite filtrar por categoría
 * y muestra un accordion con tablas de posiciones.
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ruta al JSON con los resultados
$jsonFile = __DIR__ . '/resultados.json';
if (!file_exists($jsonFile)) {
    die('Archivo de resultados no encontrado.');
}
$data = json_decode(file_get_contents($jsonFile), true);
if (!is_array($data)) {
    die('Formato JSON inválido.');
}

// Extraer categorías únicas y ordenar
$categories = [];
foreach ($data as $r) {
    $categories[$r['category']] = true;
}
$categories = array_keys($categories);
sort($categories, SORT_STRING);

// Filtro de categoría desde GET
$filterCat = $_GET['categoria'] ?? '';

// Agrupar resultados por categoría
$grouped = [];
foreach ($data as $r) {
    if ($filterCat && $r['category'] !== $filterCat) {
        continue;
    }
    $grouped[$r['category']][] = $r;
}
ksort($grouped, SORT_STRING);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Resultados – Copa de Figura</title>
  <link rel="stylesheet" href="../css/style.css">
  <script src="/js/main.js"></script>
  <style>
    .filters { margin-bottom: 1em; }
    .accordion details { margin-bottom: .5em; }
    table { border-collapse: collapse; width: 100%; margin-top: .5em; }
    th, td { border: 1px solid #eee; padding: 6px; }
    th { background: #eee; }
  </style>
</head>
<body>
  <!-- Header compartido -->
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php'; ?>

  <main>
    <section id="resultados" class="features">
      <div class="container">
        <h2>Resultados Copa de Figura</h2>
        <h1> Todos lo resultados (Drive). <br> <a href="https://drive.google.com/drive/folders/1tAkCsZFmDLHULgAkaTMmU7JVXCHWWoW7?usp=drive_link" target="_blank" class="btn" >Ver Carpeta</a></h1>
        <form method="get" class="filters">
          <select name="categoria">
            <option value="">-- Todas las Categorías --</option>
            <?php foreach ($categories as $c):
              $sel = ($c === $filterCat) ? ' selected' : '';
            ?>
              <option value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>"<?= $sel ?>>
                <?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn">Filtrar</button>
        </form>

        <div class="accordion">
          <?php foreach ($grouped as $cat => $items): ?>
            <details<?= ($filterCat === $cat) ? ' open' : '' ?> >
              <summary><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></summary>
              <table>
                <thead>
                  <tr>
                    <th>Pos.</th>
                    <th>Nombre</th>
                    <th>Club</th>
                    <th>Puntos</th>
<!--                     <th>M.V.</th>
                    <th>Cód.</th> -->
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($items as $r): ?>
                    <tr>
                      <td><?= $r['position'] ?></td>
                      <td><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars($r['club'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= $r['points'] ?></td>
                     <!--  <td><?= $r['mv'] ?></td> -->
                      <!-- <td><?= htmlspecialchars($r['pattern'] ?? '', ENT_QUOTES, 'UTF-8') ?></td> -->
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </details>
          <?php endforeach; ?>
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
