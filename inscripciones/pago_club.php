<?php
session_start();
if (!isset($_SESSION['club_id'])) {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/conexion.php';
$club_id        = $_SESSION['club_id'];
$competencia_id = isset($_GET['competencia_id']) ? (int)$_GET['competencia_id'] : 0;

if ($competencia_id <= 0) {
    header("Location: panel_club.php");
    exit;
}

// 1) Recuperar pago y datos de competencia
$stmt = $pdo->prepare("
  SELECT p.*, c.nombre_evento
    FROM pagos_club p
    JOIN competencias c ON p.competencia_id = c.id
   WHERE p.club_id        = :club
     AND p.competencia_id = :comp
   LIMIT 1
");
$stmt->execute([
  ':club' => $club_id,
  ':comp' => $competencia_id
]);
$pago = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pago) {
    header("Location: generar_pago.php?competencia_id={$competencia_id}");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pago de <?= htmlspecialchars($pago['nombre_evento']) ?></title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .box {
      max-width: 500px;
      margin: 2rem auto;
      padding: 1.5rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      background: #fafafa;
    }
    .box h2 { margin-top:0; }
    .box p { margin:.5rem 0; }
    .box input[type=file] { margin:1rem 0; width:100%; }
    .btn {
      display:block; width:100%; padding:.75rem;
      background:#c8102e; color:#fff; text-align:center;
      border:none; border-radius:5px; cursor:pointer;
      font-size:1rem;
    }
    .btn:disabled { background:#999; cursor:default; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../templates/header.php'; ?>
  <main class="container">
    <div class="box">
      <h2>Pago de “<?= htmlspecialchars($pago['nombre_evento']) ?>”</h2>
      <p><strong>Generado:</strong>
        <?= date('d/m/Y H:i', strtotime($pago['fecha_generacion'])) ?></p>
      <p><strong>Deportistas:</strong>
        <?= htmlspecialchars($pago['total_inscripciones']) ?></p>
      <p><strong>Monto:</strong>
        $<?= number_format($pago['monto_total'],0,',','.') ?></p>
      <p><strong>Estado:</strong>
        <?= ucfirst($pago['estado']) ?></p>
        <?php if ($pago['estado'] === 'pendiente'): ?>
        <div class="alert alert-info" style="max-width:500px; margin:1rem auto; padding:1rem; background:#e7f3fe; color:#31708f; border:1px solid #bce8f1; border-radius:4px;">
            <p><strong>Recuerda:</strong> antes de subir el comprobante, <a href="ver_inscripciones.php" style="color:#0b78e3; text-decoration:underline;">revisa que todos los deportistas estén correctamente inscritos</a>.</p>
        </div>

        <form method="POST" enctype="multipart/form-data" action="subir_comprobante.php">
            <!-- resto del formulario -->
        </form>
        <?php endif; ?>  
      <?php if ($pago['estado'] === 'pendiente'): ?>
        <form method="POST" enctype="multipart/form-data" action="subir_comprobante.php">
          <input type="hidden" name="pago_id"          value="<?= $pago['id'] ?>">
          <input type="hidden" name="competencia_id"  value="<?= $pago['competencia_id'] ?>">
          <label for="comprobante">Comprobante (PDF/Imagen):</label>
          <input type="file" name="comprobante" id="comprobante"
                 accept=".pdf,image/*" required>
          <button type="submit" class="btn">Subir y Marcar como Pagado</button>
        </form>
      <?php else: ?>
        <p><strong>Comprobante subido:</strong></p>
        <a class="btn"
           href="https://drive.google.com/uc?id=<?= htmlspecialchars($pago['comprobante_drive_id']) ?>&export=download"
           target="_blank">
          Descargar comprobante
        </a>
      <?php endif; ?>
      <div style="text-align:center; margin-top:1.5rem;">
  <a href="panel_club.php" class="btn btn-outline">
    ← Volver al Panel
  </a>
</div>
    </div>
  </main>
  <?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
