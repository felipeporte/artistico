<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión a BD
$dsn = 'mysql:host=localhost;dbname=inscripciones;charset=utf8mb4';
$dbUser = 'inscrip';
$dbPass = 'Maiteam';
$pdo = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Obtener categorías y modalidades
$list = $pdo->query("
    SELECT DISTINCT categoria, modalidad
      FROM competidores
     ORDER BY categoria, modalidad
")->fetchAll(PDO::FETCH_ASSOC);
$cats = array_unique(array_column($list, 'categoria'));
$mods = array_unique(array_column($list, 'modalidad'));

// Leer filtros
$filterCat = $_GET['categoria'] ?? '';
$filterMod = $_GET['modalidad'] ?? '';

// Preparar consulta con filtros
$sql = "
    SELECT nombre, club, categoria, modalidad, pie, grupo, orden_salida
      FROM competidores
";
$where = []; $params = [];
if ($filterCat) { $where[] = 'categoria = ?';   $params[] = $filterCat; }
if ($filterMod) { $where[] = 'modalidad = ?';   $params[] = $filterMod; }
if ($where)      { $sql .= ' WHERE '. implode(' AND ', $where); }
$sql .= ' ORDER BY categoria, modalidad, orden_salida';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por categoría y modalidad
$grouped = [];
foreach ($rows as $r) {
    $grouped[$r['categoria']][$r['modalidad']][] = $r;
}

// **Insertar aquí el reorder** 
$categoryOrder = ['Tots','Mini','Espoir','Cadete','Youth','Junior','Senior'];
uksort($grouped, function($a, $b) use ($categoryOrder) {
    $posA = array_search($a, $categoryOrder);
    $posB = array_search($b, $categoryOrder);
    $posA = ($posA === false) ? PHP_INT_MAX : $posA;
    $posB = ($posB === false) ? PHP_INT_MAX : $posB;
    return $posA <=> $posB;
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Resultados – Copa de Figura</title>
  <link rel="stylesheet" href="../css/style.css">
  <script src="/js/main.js"></script>
</head>
<body>
  <!-- Header compartido -->
  <?php include $_SERVER['DOCUMENT_ROOT'].'/templates/header.php';?>

  <main>
    <section id="resultados" class="features">
      <div class="container">
        <h2>Resultados Completos - Copa de Figura</h2>

        <form method="get" class="filters">
          <select name="categoria">
            <option value="">-- Todas las Categorías --</option>
            <?php foreach($cats as $c): 
              $sel = ($c === $filterCat) ? ' selected' : '';
            ?>
              <option value="<?=htmlspecialchars($c)?>"<?=$sel?>>
                <?=htmlspecialchars($c)?>
              </option>
            <?php endforeach; ?>
          </select>

          <select name="modalidad">
            <option value="">-- Todas las Modalidades --</option>
            <?php foreach($mods as $m):
              $sel = ($m === $filterMod) ? ' selected' : '';
            ?>
              <option value="<?=htmlspecialchars($m)?>"<?=$sel?>>
                <?=htmlspecialchars($m)?>
              </option>
            <?php endforeach; ?>
          </select>

          <button type="submit" class="btn">Filtrar</button>
        </form>

        <div class="accordion">
          <?php foreach($grouped as $cat => $modalidades): ?>
            <details>
              <summary><?=htmlspecialchars($cat)?></summary>
              <div class="modalidad">
                <?php foreach($modalidades as $mod => $items): 
                  // El pie y grupo son constantes dentro de este grupo
                  $first = $items[0];
                  $pie   = htmlspecialchars($first['pie']);
                  $grupo = htmlspecialchars($first['grupo']);
                ?>
                  <details>
                    <summary>
                      <?=htmlspecialchars($mod)?> 
                      (Pie: <?=$pie?>, Grupo: <?=$grupo?>)
                    </summary>
                    <table>
                      <thead>
                        <tr>
                          <th>Nombre</th>
                          <th>Club</th>
                          <th>Orden</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($items as $r): ?>
                          <tr>
                            <td><?=htmlspecialchars($r['club'])?></td>
                            <td><?=htmlspecialchars($r['nombre'])?></td>
                            <td><?=htmlspecialchars($r['orden_salida'])?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </details>
                <?php endforeach; ?>
              </div>
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
