<?php
date_default_timezone_set('America/Santiago');
// Mismo deadline que en index.php
$deadline = strtotime('2025-04-24 23:59:59');
if(time() > $deadline) {
  echo '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><title>Inscripciones Cerradas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{margin:0;padding:0;display:flex;align-items:center;justify-content:center;
      height:100vh;font-family:Arial,sans-serif;background:#f4f4f4;}
    .msg{background:#fff;padding:30px;border-radius:8px;
      box-shadow:0 4px 12px rgba(0,0,0,0.1);text-align:center;}
    .msg h1{color:#BD1E2D;margin-bottom:16px;}
  </style>
</head>
<body>
  <div class="msg">
    <h1>Inscripciones Cerradas</h1>
    <p>El tiempo de inscripciones ha finalizado.</p>
  </div>
</body>
</html>';
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Inscripción Competencia</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      margin:0; padding:0;
      display:flex; justify-content:center; align-items:center;
      min-height:100vh;
      background: linear-gradient(135deg, #eaeaea, #fddde6);
      font-family: Arial, sans-serif;
    }
    .card {
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      width: 90%;
      max-width: 500px;
    }
    .card h1 {
      margin-top:0;
      margin-bottom:20px;
      color: #BD1E2D;
      text-align: center;
      font-size: 1.5em;
    }
    .card label {
      display: block;
      margin-top: 12px;
      font-weight: bold;
      color: #333;
    }
    .card input[type="text"],
    .card input[type="number"],
    .card select {
      width: 100%;
      padding: 8px;
      margin-top: 4px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
      font-size: 1em;
    }
    .modalidades {
      margin-top: 12px;
    }
    .modalidades legend {
      font-weight: bold;
      color: #333;
    }
    .modalidades label {
      display: inline-block;
      width: 48%;
      margin-bottom: 8px;
      font-weight: normal;
    }
    .submit-btn {
      margin-top: 20px;
      width: 100%;
      padding: 12px;
      background: #BD1E2D;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 1em;
      cursor: pointer;
    }
    .submit-btn:hover {
      background: #9a1a23;
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>Ficha de Inscripción</h1>
    <form action="procesa.php" method="post">
      <label>Club
        <input type="text" name="club" required>
      </label>

      <label>Nombre completo
        <input type="text" name="nombre" required>
      </label>

      <label>Edad
        <input type="number" name="edad" min="3" max="100" required>
      </label>

      <label>Categoría
        <select name="categoria" required>
          <option value="">– selecciona –</option>
          <option value="Escuela D">Escuela D</option>
          <option value="Escuela C">Escuela C</option>
          <option value="Eficiencia Básica">Eficiencia Básica</option>
          <option value="Eficiencia Intermedia">Eficiencia Intermedia</option>
          <option value="Eficiencia Avanzada">Eficiencia Avanzada</option>
          <option value="Internacional">Internacional</option>
        </select>
      </label>

      <label>Campeonato
        <select name="campeonato" required>
          <option value="">– selecciona –</option>
          <option value="1er Ranking Copa Figuras">1er Ranking Copa Figura</option>
        </select>
      </label>

      <fieldset class="modalidades">
        <legend>Modalidades </legend>
        <label><input type="checkbox" name="modalidades[]" value="Figuras"> Figura</label>
      <!--   <label><input type="checkbox" name="modalidades[]" value="Free Skating"> Free Skating</label>
        <label><input type="checkbox" name="modalidades[]" value="Solo Dance"> Solo Dance</label>
        <label><input type="checkbox" name="modalidades[]" value="Parejas"> Parejas</label> -->
      </fieldset>

      <button type="submit" class="submit-btn">Enviar inscripción</button>
    </form>
  </div>
</body>
</html>
