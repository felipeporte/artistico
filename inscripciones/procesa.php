<?php
// procesa.php
// 1. Conexión
$dsn  = 'mysql:host=localhost;dbname=inscripciones;charset=utf8mb4';
$user = 'inscrip'; $pass = 'Maiteam';
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

// 2. Captura y validación
$club       = trim($_POST['club'] ?? '');
$nombre     = trim($_POST['nombre'] ?? '');
$edad       = (int)($_POST['edad'] ?? 0);
$categoria  = $_POST['categoria'] ?? '';
$campeonato = trim($_POST['campeonato'] ?? '');
$mods       = $_POST['modalidades'] ?? [];
if (!$club||!$nombre||!$edad||!$categoria||!$campeonato||count($mods)<1||count($mods)>3) {
  die('Datos inválidos. Vuelve y revisa.');
}

// 3. Precio
$cant = count($mods);
$precio = $cant===1?20000:($cant===2?45000:($cant===3?55000:0));

// 4. Inserción
$sql = "INSERT INTO fichas (club,nombre,edad,categoria,campeonato,modalidades,cantmod,precio)
        VALUES (:club,:nombre,:edad,:cat,:camp,:mods,:cant,:precio)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  ':club'=>$club,':nombre'=>$nombre,':edad'=>$edad,
  ':cat'=>$categoria,':camp'=>$campeonato,
  ':mods'=>implode(',',$mods),':cant'=>$cant,':precio'=>$precio
]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>¡Gracias!</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{
      margin:0; padding:0;
      display:flex; justify-content:center; align-items:center;
      min-height:100vh;
      background: linear-gradient(135deg, #eaeaea, #fddde6);
      font-family: Arial, sans-serif;
    }
    .card{
      background:#fff; padding:30px;
      border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1);
      text-align:center; width:90%; max-width:400px;
    }
    .card h1{ color:#BD1E2D; margin-top:0; }
    .card p{ margin:10px 0; color:#333; }
    .card .btn{
      display:inline-block; margin-top:20px;
      padding:10px 20px; background:#BD1E2D;
      color:#fff; text-decoration:none; border-radius:6px;
    }
    .card .btn:hover{ background:#9a1a23; }
  </style>
</head>
<body>
  <div class="card">
    <h1>¡Inscripción recibida!</h1>
    <p><strong><?=htmlspecialchars($nombre)?></strong></p>
    <p>Campeonato: <?=htmlspecialchars($campeonato)?></p>
    <p>Modalidades: <?=htmlspecialchars(implode(', ',$mods))?></p>
    <p>Precio: <strong> $<?=number_format($precio,0,',','.')?> </strong></p>
    <p>Fecha: <?=date('Y-m-d H:i:s')?></p>
    <a href="formulario.php" class="btn">Registrar otro</a>
  </div>
</body>
</html>
