<?php
// listado.php
$pdo = new PDO(
  'mysql:host=localhost;dbname=inscripciones;charset=utf8mb4',
  'inscrip','Maiteam',
  [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
);
$clubsStmt = $pdo->query("SELECT DISTINCT club FROM fichas ORDER BY club");
$clubs     = $clubsStmt->fetchAll(PDO::FETCH_COLUMN);
$filtro    = $_GET['club'] ?? '';
if ($filtro) {
  $stmt = $pdo->prepare("SELECT * FROM fichas WHERE club=:club ORDER BY fecha DESC");
  $stmt->execute([':club'=>$filtro]);
} else {
  $stmt = $pdo->query("SELECT * FROM fichas ORDER BY fecha DESC");
}
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Listado Inscripciones</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      margin:0; padding:20px;
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #eaeaea, #fddde6);
    }
    .card {
      background:#fff; padding:20px;
      border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1);
      max-width:1000px; margin:auto;
    }
    .card h1 {
      color: #BD1E2D; text-align:center; margin-top:0;
    }
    form {
      margin-bottom:15px; text-align:right;
    }
    select {
      padding:6px; border-radius:6px; border:1px solid #ccc;
    }
    table {
      width:100%; border-collapse:collapse; margin-top:10px;
    }
    th, td {
      border:1px solid #ccc; padding:8px; text-align:left;
    }
    th {
      background:#f2f2f2; color:#333;
    }
    @media(max-width:600px) {
      th,td{ font-size:0.85em; padding:6px; }
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>Listado de Inscripciones</h1>
    <form method="get">
      <label>Club:
        <select name="club" onchange="this.form.submit()">
          <option value="">-- todos --</option>
          <?php foreach($clubs as $c): ?>
            <option value="<?=htmlspecialchars($c)?>" <?= $c===$filtro?'selected':''?>>
              <?=htmlspecialchars($c)?>
            </option>
          <?php endforeach ?>
        </select>
      </label>
    </form>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Club</th><th>Nombre</th><th>Edad</th>
          <th>Categoría</th><th>Campeonato</th><th>Modalidades</th>
          <th>CantMod</th><th>Precio</th><th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($rows)): ?>
          <tr><td colspan="10" style="text-align:center">No hay registros.</td></tr>
        <?php else: foreach($rows as $r): ?>
          <tr>
            <td><?=$r['id']?></td>
            <td><?=htmlspecialchars($r['club'])?></td>
            <td><?=htmlspecialchars($r['nombre'])?></td>
            <td><?=$r['edad']?></td>
            <td><?=htmlspecialchars($r['categoria'])?></td>
            <td><?=htmlspecialchars($r['campeonato'])?></td>
            <td><?=htmlspecialchars($r['modalidades'])?></td>
            <td><?=$r['cantmod']?></td>
            <td>$<?=number_format($r['precio'],0,',','.')?></td>
            <td><?=$r['fecha']?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
