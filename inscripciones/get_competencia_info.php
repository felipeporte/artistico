<?php
// get_competencia_info.php
header('Content-Type: application/json');
include __DIR__ . '/conexion.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
  SELECT 
    m.nombre    AS modalidad,
    n.id        AS nivel_id,
    n.nombre_nivel AS nivel,
    COALESCE(n.subnivel,'') AS subnivel
  FROM niveles_competencia nc
  JOIN niveles      n ON nc.nivel_id      = n.id
  JOIN modalidades  m ON n.modalidad_id   = m.id
  WHERE nc.competencia_id = :id
");
$stmt->execute([':id' => $id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Extraer lista única de modalidades
$modalidades = array_values(array_unique(array_column($rows,'modalidad')));

echo json_encode([
  'modalidades' => $modalidades,
  'combinaciones' => $rows
]);
