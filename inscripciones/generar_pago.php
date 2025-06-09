<?php
// generar_pago.php

session_start();
if (!isset($_SESSION['club_id'])) {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/conexion.php';
$club_id        = $_SESSION['club_id'];
$competencia_id = isset($_GET['competencia_id']) ? (int)$_GET['competencia_id'] : 0;

if ($competencia_id <= 0) {
    header("Location: panel_club.php?error=Debe+elegir+una+competencia");
    exit;
}

// 0) Verificar si ya existe un pago para este club+competencia
$stmt = $pdo->prepare("
  SELECT id, estado 
    FROM pagos_club 
   WHERE club_id = :club 
     AND competencia_id = :comp
");
$stmt->execute([
  ':club' => $club_id,
  ':comp' => $competencia_id
]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);
if ($existing && $existing['estado'] === 'pagado') {
    // Ya está pagado: no regeneramos, vamos a la vista
    header("Location: pago_club.php?competencia_id={$competencia_id}");
    exit;
}

// 1) Agrupar inscripciones por deportista en ESTA competencia
$stmt = $pdo->prepare("
  SELECT 
    i.deportista_id,
    SUM(CASE WHEN i.modalidad NOT IN ('show','quartets') THEN 1 ELSE 0 END) AS cnt_regular,
    SUM(CASE WHEN i.modalidad     IN ('show','quartets') THEN 1 ELSE 0 END) AS cnt_group
  FROM inscripciones i
  JOIN deportistas d ON i.deportista_id = d.id
  WHERE d.club_id        = :club
    AND i.competencia_id = :comp
  GROUP BY i.deportista_id
");
$stmt->execute([
  ':club' => $club_id,
  ':comp' => $competencia_id
]);
$grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2) Definir tarifas
$tarifa       = [1 => 20000, 2 => 35000, 3 => 45000];
$tarifa_group = 15000;

// 3) Calcular monto total
$monto_total = 0;
foreach ($grupos as $g) {
    // modalidades regulares
    $n = $g['cnt_regular'];
    if ($n >= 3)        $monto_total += $tarifa[3];
    elseif ($n >= 1)    $monto_total += $tarifa[$n];
    // show/quartets
    $monto_total += $g['cnt_group'] * $tarifa_group;
}
$total_inscripciones = count($grupos);

// 4) Insertar o actualizar sólo si es nuevo o estaba pendiente
if (!$existing) {
    // no existía: creamos
    $stmt = $pdo->prepare("
      INSERT INTO pagos_club
        (club_id, competencia_id, total_inscripciones, monto_total, estado, fecha_generacion)
      VALUES
        (:club, :comp, :tot, :monto, 'pendiente', NOW())
    ");
    $stmt->execute([
      ':club'  => $club_id,
      ':comp'  => $competencia_id,
      ':tot'   => $total_inscripciones,
      ':monto' => $monto_total
    ]);
} else {
    // existía y estaba pendiente: actualizamos monto y fecha
    $stmt = $pdo->prepare("
      UPDATE pagos_club
         SET total_inscripciones = :tot,
             monto_total         = :monto,
             fecha_generacion    = NOW()
       WHERE id = :id
    ");
    $stmt->execute([
      ':tot'   => $total_inscripciones,
      ':monto' => $monto_total,
      ':id'    => $existing['id']
    ]);
}

// 5) Redirigir a la vista del pago
header("Location: pago_club.php?competencia_id={$competencia_id}");
exit;
