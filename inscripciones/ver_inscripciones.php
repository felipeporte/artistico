<?php
// ver_inscripciones.php
// Muestra las inscripciones del club, con filtro opcional por competencia

// Modo debug (opcional)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['club_id'])) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('America/Santiago');
include __DIR__ . '/conexion.php';

$club_id = $_SESSION['club_id'];
// Competencia seleccionada en el filtro
$filter_comp = isset($_GET['competencia_id']) ? (int)$_GET['competencia_id'] : 0;

// 1) Cargar lista de competencias para el filtro
// Obtener zona del club
$stmtZ = $pdo->prepare("SELECT zona FROM clubs WHERE id = ?");
$stmtZ->execute([$club_id]);
$zona_club = $stmtZ->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT id, nombre_evento
       FROM competencias
      WHERE zona = :zona OR zona = 'TODAS'
      ORDER BY fecha_inicio DESC"
);
$stmt->execute([':zona' => $zona_club]);
$competencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2) Construir consulta principal
$sql = 
    "SELECT
       i.id,
       c.nombre_evento AS competencia,
       d.nombre_completo AS deportista,
       i.modalidad,
       i.nivel,
       i.subnivel,
       i.categoria,
       i.fecha_inscripcion
     FROM inscripciones i
     JOIN competencias c ON i.competencia_id = c.id
     JOIN deportistas d ON i.deportista_id = d.id
     WHERE d.club_id = :club";
$params = [':club' => $club_id];
if ($filter_comp > 0) {
    $sql .= " AND i.competencia_id = :comp";
    $params[':comp'] = $filter_comp;
}
$sql .= " ORDER BY i.modalidad DESC";

$stmt2 = $pdo->prepare($sql);
$stmt2->execute($params);
$inscripciones = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ver Inscripciones</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .filter-box {
      max-width: 400px;
      margin: 1rem auto;
      padding: .75rem;
      background: #fff;
      border-radius: 5px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }
    .filter-box label {
      display: block;
      margin-bottom: .5rem;
      font-weight: bold;
    }
    .filter-box select {
      width: 100%;
      padding: .5rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 1rem;
    }
    .table-wrapper {
      overflow-x: auto;
      margin-top: 1rem;
    }
    .responsive-table {
      width: 100%;
      border-collapse: collapse;
    }
    .responsive-table th,
    .responsive-table td {
      padding: .75rem;
      border: 1px solid #ccc;
      text-align: left;
    }
    .responsive-table thead {
      background: #f5f5f5;
    }
    @media (max-width: 768px) {
      .responsive-table thead { display: none; }
      .responsive-table tr { display: block; margin-bottom: 1rem; }
      .responsive-table td {
        display: flex;
        justify-content: space-between;
        padding: .5rem;
        border: none;
        border-bottom: 1px solid #ccc;
      }
      .responsive-table td:before {
        content: attr(data-label);
        font-weight: bold;
      }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../templates/header.php'; ?>
  <main class="container">
    <h1>Inscripciones de <?= htmlspecialchars($_SESSION['club_nombre']) ?></h1>

    <!-- Filtro por competencia -->
    <div class="filter-box">
      <form method="GET" style="display:flex; align-items:center; gap:1rem;">
        <div style="flex:1">
          <label for="competencia_id">Filtrar por torneo:</label>
          <select name="competencia_id" id="competencia_id" onchange="this.form.submit()">
            <option value="0">-- Todas las competencias --</option>
            <?php foreach ($competencias as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $filter_comp == $c['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nombre_evento']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <a href="panel_club.php" class="btn btn-outline">&larr; Volver al Panel</a>
        </div>
      </form>
    </div>

    <?php if (empty($inscripciones)): ?>
      <p>No hay inscripciones para este filtro.</p>
    <?php else: ?>
      <div class="table-wrapper">
        <table class="responsive-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Competencia</th>
              <th>Deportista</th>
              <th>Modalidad</th>
              <th>Nivel/Subnivel</th>
              <th>Categoría</th>
              <th>Fecha</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($inscripciones as $i => $row): ?>
              <?php $nivelDisplay = $row['nivel'] . (!empty($row['subnivel']) ? ' – ' . $row['subnivel'] : ''); ?>
              <tr>
                <td data-label="#"><?= $i + 1 ?></td>
                <td data-label="Competencia"><?= htmlspecialchars($row['competencia']) ?></td>
                <td data-label="Deportista"><?= htmlspecialchars($row['deportista']) ?></td>
                <td data-label="Modalidad"><?= htmlspecialchars(ucfirst($row['modalidad'])) ?></td>
                <td data-label="Nivel/Subnivel"><?= htmlspecialchars($nivelDisplay) ?></td>
                <td data-label="Categoría"><?= htmlspecialchars($row['categoria']) ?></td>
                <td data-label="Fecha"><?= date('d/m/Y H:i', strtotime($row['fecha_inscripcion'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>
  <footer class="site-footer">
    <div class="container">
      <p>© 2025 Federación Chilena de Patinaje.</p>
    </div>
  </footer>
</body>
</html>
