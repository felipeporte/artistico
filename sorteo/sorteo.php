<?php
// sorteo.php – Copa de Figura: sorteo con revisión, edición y estilo mejorado
require 'auth.php';
session_start();
// Si no está logueado, redirigir a index
if (empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}
// 1. Conexión a la base de datos
$dsn  = 'mysql:host=localhost;dbname=inscripciones;charset=utf8mb4';
$user = 'inscrip';
$pass = 'Maiteam';
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// 2. Configuración de cantidad de grupos por modalidad|categoría
$gruposConfig = [
    "Escuela D|Tots"            => 1,
    "Escuela D|Mini"            => 2,
    "Escuela D|Espoir"          => 2,
    "Escuela D|Cadete"          => 2,
    "Escuela D|Youth"           => 2,
    "Escuela D|Junior"          => 2,
    "Escuela D|Senior"          => 2,
    "Escuela C|Tots"            => 2,
    "Escuela C|Mini"            => 2,
    "Escuela C|Espoir"          => 2,
    "Escuela C|Cadete"          => 2,
    "Escuela C|Youth"           => 2,
    "Escuela C|Junior"          => 2,
    "Escuela C|Senior"          => 2,
    "Eficiencia Básica|Tots"    => 2,
    "Eficiencia Básica|Mini"    => 1,
    "Eficiencia Básica|Espoir"  => 2,
    "Eficiencia Básica|Cadete"  => 2,
    "Eficiencia Básica|Youth"   => 2,
    "Eficiencia Básica|Junior"  => 2,
    "Eficiencia Básica|Senior"  => 2,
    "Eficiencia Intermedia|Tots"=> 2,
    "Eficiencia Intermedia|Mini"=> 2,
    "Eficiencia Intermedia|Espoir"=> 2,
    "Eficiencia Intermedia|Cadete"=> 2,
    "Eficiencia Intermedia|Youth"=> 2,
    "Eficiencia Intermedia|Junior"=> 2,
    "Eficiencia Intermedia|Senior"=> 2,
    "Eficiencia Avanzado|Tots"   => 2,
    "Eficiencia Avanzado|Mini"   => 2,
    "Eficiencia Avanzado|Espoir" => 2,
    "Eficiencia Avanzado|Cadete" => 3,
    "Eficiencia Avanzado|Youth"  => 3,
    "Eficiencia Avanzado|Junior" => 3,
    "Eficiencia Avanzado|Senior" => 3,
    "Internacional|Tots"         => 2,
    "Internacional|Mini"         => 2,
    "Internacional|Espoir"       => 3,
    "Internacional|Cadete"       => 4,
    "Internacional|Youth"        => 4,
    "Internacional|Junior"       => 4,
    "Internacional|Senior"       => 4
];

// 3. Orden personalizado de categorías
$catOrder = ['Novato','Tots','Minis','Espoir','Cadet','Youth','Junior','Senior'];

// 4. Obtener lista de categoría|modalidad y agrupar
$rows = $pdo->query(
    "SELECT DISTINCT categoria, modalidad FROM competidores"
)->fetchAll(PDO::FETCH_ASSOC);
$comboMap = [];
foreach ($rows as $r) {
    $comboMap[$r['categoria']][] = $r['modalidad'];
}

// 5. Editar competidor
$editMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'editar') {
    $id   = (int)($_POST['id'] ?? 0);
    $nmod = $_POST['nmodalidad'] ?? '';
    $ncat = $_POST['ncategoria'] ?? '';
    if ($id && $nmod && $ncat) {
        $stmt = $pdo->prepare("UPDATE competidores SET modalidad = ?, categoria = ? WHERE id = ?");
        $stmt->execute([$nmod, $ncat, $id]);
        $editMsg = "Competidor #$id actualizado a $nmod - $ncat.";
    }
}

// 6. Leer selección de combo (categoria|modalidad)
$groupParam = $_GET['group'] ?? '';
$selCategoria = '';
$selModalidad = '';
if ($groupParam) {
    list($selCategoria, $selModalidad) = explode('|', $groupParam, 2);
}

// 7. Sortear según fase
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phase'])) {
    $phase = $_POST['phase'];
    if ($selCategoria && $selModalidad) {
        $stmt = $pdo->prepare(
            "SELECT id FROM competidores WHERE categoria = ? AND modalidad = ?"
        );
        $stmt->execute([$selCategoria, $selModalidad]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if ($phase === 'pie') {
            $pie = rand(0,1) ? 'A' : 'B';
            foreach ($ids as $id) {
                $pdo->prepare("UPDATE competidores SET pie = ? WHERE id = ?")->execute([$pie, $id]);
            }
            $msg = "Pie asignado ($pie) a $selCategoria - $selModalidad.";
        } elseif ($phase === 'grupo') {
            // Clave invertida para buscar en configuración: modalidad|categoría
            $cfgKey = "$selModalidad|$selCategoria";
            $total = $gruposConfig[$cfgKey] ?? 1;
            $g = rand(1, $total);
            foreach ($ids as $id) {
                $pdo->prepare("UPDATE competidores SET grupo = ? WHERE id = ?")->execute([$g, $id]);
            }
            $msg = "Grupo asignado ($g) a $selCategoria - $selModalidad.";
            $total = $gruposConfig[$cfgKey] ?? 1;
            $g = rand(1, $total);
            foreach ($ids as $id) {
                $pdo->prepare("UPDATE competidores SET grupo = ? WHERE id = ?")->execute([$g, $id]);
            }
            $msg = "Grupo asignado ($g) a $selCategoria - $selModalidad.";
        } elseif ($phase === 'orden') {
            shuffle($ids);
            foreach ($ids as $i => $id) {
                $pdo->prepare("UPDATE competidores SET orden_salida = ? WHERE id = ?")->execute([$i+1, $id]);
            }
            $msg = "Orden de salida definido para $selCategoria - $selModalidad.";
        }
    }
}

// 8. Obtener competidores para mostrar
$competidores = [];
if ($selCategoria && $selModalidad) {
    $stmt = $pdo->prepare(
        "SELECT id, nombre, club, pie, grupo, orden_salida
         FROM competidores
         WHERE categoria = ? AND modalidad = ?
         ORDER BY nombre"
    );
    $stmt->execute([$selCategoria, $selModalidad]);
    $competidores = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sorteo Liga - Costa</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen;
      background: #f0f2f5;
      margin: 0;
      padding: 20px;
      display: flex;
      justify-content: center;
    }
    .container {
      background: #ffffff;
      max-width: 1400px;
      width: 100%;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      padding: 20px;
    }
    h1, h2 {
      text-align: center;
      color: #333;
    }
    .message {
      background: #e6ffed;
      border: 1px solid #b7f5c9;
      padding: 10px;
      border-radius: 4px;
      color: #2d6a4f;
      margin-bottom: 20px;
    }
    .section {
      margin-bottom: 30px;
    }
    form {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 10px;
    }
    select, button {
      padding: 8px 12px;
      border-radius: 4px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }
    button {
      background: #4a90e2;
      color: #fff;
      border: none;
      cursor: pointer;
      transition: background 0.3s;
    }
    button:hover {
      background: #357ab8;
    }
    .sorteo-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 10px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
    }
    th {
      background: #f7f7f7;
      position: sticky;
      top: 0;
    }
    tbody tr:nth-child(even) {
      background: #fafafa;
    }
    tbody tr:hover {
      background: #f1f1f1;
    }
    .actions form {
      display: inline-block;
      margin: 0;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Sorteo Liga Costa</h1>

    <?php if ($editMsg) { ?><div class="message"><?php echo htmlspecialchars($editMsg); ?></div><?php } ?>
    <?php if ($msg)     { ?><div class="message"><?php echo htmlspecialchars($msg); ?></div><?php } ?>

    <div class="section">
      <h2>Seleccionar categoría y modalidad</h2>
      <form method="get">
        <select name="group">
          <option value="">-- Elija --</option>
          <?php
          foreach ($catOrder as $cat) {
              if (isset($comboMap[$cat])) {
                  foreach ($comboMap[$cat] as $mod) {
                      $val = $cat . '|' . $mod;
                      $sel = ($val === $groupParam) ? ' selected' : '';
                      echo '<option value="' . htmlspecialchars($val) . '"' . $sel . '>'
                         . htmlspecialchars("$cat - $mod") . '</option>';
                  }
              }
          }
          ?>
        </select>
        <button type="submit">Cargar</button>
      </form>
    </div>

    <?php if ($competidores) { ?>
      <div class="section">
        <h2> Revisar o editar competidoras de <?php echo htmlspecialchars(' (' . $selCategoria . ' - ' . $selModalidad . ')'); ?></h2>
        <table>
          <thead>
            <tr><th>ID</th><th>Club</th><th>Nombre</th><!-- <th>Pie</th><th>Grupo</th> --><th>Orden</th><th>Modificar</th></tr>
          </thead>
          <tbody>
            <?php foreach ($competidores as $c) { ?>
              <tr>
                <td><?php echo $c['id']; ?></td>
                <td><?php echo htmlspecialchars($c['club']); ?></td>
                <td><?php echo htmlspecialchars($c['nombre']); ?></td>

                <!-- <td><?php echo htmlspecialchars($c['pie']); ?></td>
                <td><?php echo htmlspecialchars($c['grupo']); ?></td> -->
                <td><?php echo htmlspecialchars($c['orden_salida']); ?></td>
                <td class="actions">
                  <form method="post" action="?group=<?php echo urlencode($groupParam); ?>">
                    <input type="hidden" name="action" value="editar">
                    <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                    <select name="ncategoria">
                      <?php foreach ($catOrder as $oc) {
                          echo '<option>' . htmlspecialchars($oc) . '</option>';
                      } ?>
                    </select>
                    <select name="nmodalidad">
                      <?php if (isset($comboMap[$selCategoria])) {
                          foreach ($comboMap[$selCategoria] as $ocm) {
                              echo '<option>' . htmlspecialchars($ocm) . '</option>';
                          }
                      } ?>
                    </select>
                    <button>Guardar</button>
                  </form>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <div class="section">
        <h2>Botones de Sorteo </h2>
        <div class="sorteo-buttons">
          <?php foreach ([/* 'pie' => 'Sortear Pie (A/B)', 'grupo' => 'Sortear Grupo',  */'orden' => 'Sortear Orden'] as $k => $l) { ?>
            <form method="post" action="?group=<?php echo urlencode($groupParam); ?>">
              <input type="hidden" name="phase" value="<?php echo $k; ?>">
              <button><?php echo htmlspecialchars($l); ?></button>
            </form>
          <?php } ?>
        </div>
      </div>
    <?php } ?>

    <p style="text-align:center;"><a href="ver_resultados.php">Ver resultados completos</a></p>
  </div>
</body>
</html>
