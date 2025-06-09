<?php
// ver_resultados.php – Mostrar resultados con filtros de categoría y modalidad
/* require 'auth.php';
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
} */

// Conexión a BD
$dsn = 'mysql:host=localhost;dbname=inscripciones;charset=utf8mb4';
$dbUser = 'inscrip';
$dbPass = 'Maiteam';
$pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

// Obtener categorías y modalidades para filtros
$list = $pdo->query("SELECT DISTINCT categoria, modalidad FROM competidores ORDER BY categoria, modalidad")->fetchAll(PDO::FETCH_ASSOC);
$cats = array_unique(array_column($list, 'categoria'));
$mods = array_unique(array_column($list, 'modalidad'));

// Leer filtros
$filterCat = $_GET['categoria'] ?? '';
$filterMod = $_GET['modalidad'] ?? '';

// Preparar consulta con filtros
$sql = "SELECT id, nombre, club, categoria, modalidad, pie, grupo, orden_salida FROM competidores";
$where = [];
$params = [];
if ($filterCat) { $where[] = 'categoria = ?'; $params[] = $filterCat; }
if ($filterMod) { $where[] = 'modalidad = ?'; $params[] = $filterMod; }
if ($where) { $sql .= ' WHERE '.implode(' AND ', $where); }
$sql .= ' ORDER BY categoria, modalidad, orden_salida';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Resultados Completos - Zona Costa</title>
  <style>
    body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen;background:#f0f2f5;margin:0;padding:20px;display:flex;justify-content:center}
    .container{background:#fff;max-width:1800px;width:100%;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);padding:20px}
    h1{text-align:center;color:#333;margin-bottom:20px}
    .filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;justify-content:center}
    select,button{padding:8px 12px;border:1px solid #ccc;border-radius:4px;font-size:1rem}
    button{background:#4a90e2;color:#fff;border:none;cursor:pointer;transition:background .3s}
    button:hover{background:#357ab8}
    table{width:100%;border-collapse:collapse}
    th,td{padding:12px;border-bottom:1px solid #ddd;text-align:center}
    th{background:#f7f7f7;position:sticky;top:0;z-index:1}
    tbody tr:nth-child(even){background:#fafafa}
    tbody tr:hover{background:#f1f1f1}
    .back-link{text-align:center;margin-top:20px}
    .back-link a{color:#4a90e2;text-decoration:none}
    .back-link a:hover{text-decoration:underline}
  </style>
</head>
<body>
  <div class="container">
    <h1>Resultados Completos - Zona Costa</h1>
    <form method="get" class="filters">
      <select name="categoria">
        <option value="">-- Todas las Categorías --</option>
        <?php foreach($cats as $c){ $sel=($c===$filterCat)?' selected':''; echo "<option value='".htmlspecialchars($c)."'{$sel}>".htmlspecialchars($c)."</option>";}?>
      </select>
      <select name="modalidad">
        <option value="">-- Todas las Modalidades --</option>
        <?php foreach($mods as $m){ $sel=($m===$filterMod)?' selected':''; echo "<option value='".htmlspecialchars($m)."'{$sel}>".htmlspecialchars($m)."</option>";}?>
      </select>
      <button type="submit">Filtrar</button>
    </form>
    <table>
      <thead>
        <tr><th>ID</th><th>Club</th><th>Nombre</th><th>Categoría</th><th>Modalidad</th><!-- <th>Pie</th><th>Grupo</th> --><th>Orden</th></tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r){?>
          <tr>
            <td><?php echo htmlspecialchars($r['id']);?></td>
                        <td><?php echo htmlspecialchars($r['club']);?></td>
            <td><?php echo htmlspecialchars($r['nombre']);?></td>

            <td><?php echo htmlspecialchars($r['categoria']);?></td>
            <td><?php echo htmlspecialchars($r['modalidad']);?></td>
<!--             <td><?php echo htmlspecialchars($r['pie']);?></td>
            <td><?php echo htmlspecialchars($r['grupo']);?></td> -->
            <td><?php echo htmlspecialchars($r['orden_salida']);?></td>
          </tr>
        <?php }?>
      </tbody>
    </table>
    <!-- <div class="back-link"><a href="sorteo.php">&larr; Volver al sorteo</a></div> -->
  </div>
</body>
</html>
