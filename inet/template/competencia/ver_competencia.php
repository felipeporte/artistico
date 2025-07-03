<?php

// /competencia/ver_competencia.php

include './../../../inscripciones/conexion.php';

// 1) Leer & validar ID de competencia
$id = isset($_GET['id']) && is_numeric($_GET['id'])
    ? (int)$_GET['id']
    : 0;
if (!$id) {
    exit('ID inválido de competencia.');
}

// 2) Obtener todas las competencias
$stmt1 = $pdo->query("SELECT id, nombre_evento, fecha_inicio, zona FROM competencias ORDER BY fecha_inicio DESC");
$competencias = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// 3) Traer datos de la competencia
$stmt = $pdo->prepare("SELECT nombre_evento, zona FROM competencias WHERE id = ?");
$stmt->execute([$id]);
$comp = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$comp) {
    exit('Competencia no encontrada.');
}
// 4) Traer inscripciones
$stmt = $pdo->prepare("
    SELECT
      i.id,
      i.deportista_id,
      c.id AS club_id,
      c.nombre_club,
      d.nombre_completo,
      d.genero,
      i.modalidad,
      i.nivel,
      i.subnivel,
      i.categoria
    FROM inscripciones i
    JOIN deportistas d ON i.deportista_id = d.id
    JOIN clubs c       ON d.club_id        = c.id
    WHERE i.competencia_id = ?
    ORDER BY d.genero, i.modalidad, i.categoria 
");
$stmt->execute([$id]);
$inscritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5) Calcular totales (solo para vista HTML)
if (!$exportCsv) {
    $stmtCounts = $pdo->prepare("
      SELECT 
        COUNT(*) AS total_ins,
        COUNT(DISTINCT deportista_id) AS total_deportistas
      FROM inscripciones
      WHERE competencia_id = ?
    ");
    $stmtCounts->execute([$id]);
    $counts = $stmtCounts->fetch(PDO::FETCH_ASSOC);
}
// 6) traer monto total del evento 

$stmt = $pdo->prepare("
SELECT SUM(monto_total) AS total_recaudado
FROM pagos_club
WHERE competencia_id = ?
");
$stmt->execute([$id]);
$monto_total = $stmt->fetch(PDO::FETCH_ASSOC);

// 7) clubes sin pago

$stmt = $pdo->prepare("
  SELECT DISTINCT c.id, c.nombre_club
  FROM clubs c
  JOIN deportistas d ON d.club_id = c.id
  JOIN inscripciones i ON i.deportista_id = d.id
  WHERE i.competencia_id = ?
    AND c.id NOT IN (
      SELECT DISTINCT club_id
      FROM pagos_club
      WHERE competencia_id = ?
        AND estado = 'pagado'
    )
  ORDER BY c.nombre_club
");
$stmt->execute([$id, $id]);
$clubes_no_pagados = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<script>";
  echo "console.log('Variable PHP: " . $clubes_no_pagados['nombre_club'] . "');";
  echo "</script>"
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php include './../header.php'; ?>
</head>
<body>
<!-- Menu -->
<?php include './../menu.php'; ?>



<div class="container mb-3">
    <div class="row title mt-5">
        <div class="col-10">
                        <h2><?= htmlspecialchars($comp['nombre_evento']) ?></h2>
                        <small class="text-muted">(<?= htmlspecialchars($comp['zona']) ?>)</small>
        </div>           
        <div class="col-2">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <!-- <button class="btn btn-primary me-md-2" type="button"><i class="fa-solid fa-left-long"></i> Volver</button> -->
                <a class="btn btn-primary" href="/inet/competencias.php" role="button"><i class="fa-solid fa-left-long"></i> Volver</a>
            </div>
        </div>
    </div>
</div>
<div class="container-xl">
    <?php if (!empty($inscritos)): ?>
    <div class="row">
        <div class="col-sm-4"> 
                <div class="card" >
                    <div class="card-body">
                        <h5 class="card-title text-center">Total Inscritos</h5>
                        <h2 class="amount"><?= $counts['total_ins'] ?></h2>
                        <p class="card-text text-center">Corresponde al total de inscrito para todas las modalidades.</p>
                    </div>
                </div>
        </div>        
        <div class="col-sm-4">
                <div class="card" >
                    <div class="card-body">
                        <h5 class="card-title text-center">Deportistas únicos</h5>
                        <h2 class="amount"><?= $counts['total_deportistas'] ?></h2>
                        <p class="card-text text-center">Corresponde a la cantidad de deportistas que competiran.</p>
                    </div>
                </div>  
        </div>
        <div class="col-sm-4">
                <div class="card" >
                    <div class="card-body">
                        <h5 class="card-title text-center">Total Recaudado</h5>
                        <h3 class="amount"><?= '$' . number_format($monto_total['total_recaudado'] ?? 0, 0, ',', '.'); ?></h3>
                        <p class="card-text text-center">Total de inscripcion pagadas y enviada comprobante.</p>
                    </div>
                </div>  
        </div>                    
    </div>
    <div class="row mt-3">
        <div class="accordion" id="accordionpagos">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        <strong>Club pendiente de subir comprobante.</strong> 
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionpagos">
                <div class="accordion-body">
                        <?php if (empty($clubes_no_pagados)): ?>
                        <div class="alert alert-success" role="alert">
                        No existen clubes con comprobante pendiente. 🎉
                        </div>
                    <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($clubes_no_pagados as $club): ?>
                        <li class="list-group-item"><?= htmlspecialchars($club['nombre_club']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                </div>
            </div>
        
        
        </div>
    </div>

    <div class="table-responsive">                        
    <table id="example" class="table table-striped table-bordered ">
        <thead class="table-light">
          <tr>
            <th>Club</th>
            <th>Deportista</th>
            <th>Género</th>
            <th>Modalidad</th>
            <th>Nivel</th>
            <th>Subnivel</th>
            <th>Categoría</th>
            <th>Pago</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
            <?php foreach ($inscritos as $row): 
                $genero = $row['genero'] === 'M'   ? 'Masculino'
                        : ($row['genero'] === 'F' ? 'Femenino'
                                                    : htmlspecialchars($row['genero']));
                // Verificar si el club tiene pago confirmado
                $stmtPago = $pdo->prepare("SELECT estado FROM pagos_club WHERE club_id = ? AND competencia_id = ?");
                $stmtPago->execute([$row['club_id'], $id]);
                $pago = $stmtPago->fetch(PDO::FETCH_ASSOC);
                $iconoPago = ($pago && $pago['estado'] === 'pagado') ? '✅' : '❌';
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['nombre_club']) ?></td>
                    <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                    <td><?= $genero ?></td>
                    <td><?= htmlspecialchars(ucfirst($row['modalidad'])) ?></td>
                    <td><?= htmlspecialchars($row['nivel']) ?></td>
                    <td><?= htmlspecialchars($row['subnivel'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['categoria']) ?></td>
                    <td style="text-align: center;"><?= $iconoPago ?></td>
                    <td>
                    <a href="editar_inscripcion.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                      <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a href="eliminar_inscripcion.php?id=<?= $row['id'] ?>" 
                        class="btn btn-sm btn-danger ms-1"
                        onclick="return confirm('¿Eliminar esta inscripción?');">
                       <i class="fa-solid fa-trash-can"></i>
                    </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    <?php else: ?>
      <p>No hay inscripciones para esta competencia.</p>
    <?php endif; ?>
    </table>
    </div>
</div>
<script> 

var table = new DataTable('#example', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json',
    },
});
</script>
<style>
.amount {
  font-weight: bold;
  background: linear-gradient(to right top,rgb(223, 101, 101),rgb(235, 108, 108));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  padding: 10px;
  text-align: center;
}
  .card-info {
  display: flex;
  flex-direction: column;
  justify-content: space-evenly;
}

</style>
