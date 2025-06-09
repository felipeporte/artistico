<?php
// admin/editar_inscripcion.php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
date_default_timezone_set('America/Santiago');
include __DIR__ . '/../conexion.php';

$id = isset($_GET['id']) && is_numeric($_GET['id'])
    ? (int)$_GET['id']
    : 0;
if (!$id) {
    exit('ID de inscripción inválido.');
}

// Funciones para categoría
function calcularCategoriaPorEdad($fecha_nac, $anio) {
    $limite = new DateTime("$anio-12-31");
    $n      = new DateTime($fecha_nac);
    $edad   = $n->diff($limite)->y;
    if ($edad >= 8  && $edad <= 9)   return "Tots";
    if ($edad == 10 || $edad == 11)   return "Minis";
    if ($edad == 12 || $edad == 13)   return "Espoir";
    if ($edad == 14 || $edad == 15)   return "Cadet";
    if ($edad == 16)                  return "Youth";
    if ($edad == 17 || $edad == 18)   return "Junior";
    if ($edad >= 19)                  return "Senior";
    return null;
}

// 1) Cargar inscripción
$stmt = $pdo->prepare("
  SELECT i.*, c.fecha_inicio, c.nombre_evento, c.niveles, c.modalidades
    FROM inscripciones i
    JOIN competencias c ON i.competencia_id = c.id
   WHERE i.id = ?
");
$stmt->execute([$id]);
$insc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$insc) {
    exit('Inscripción no encontrada.');
}

// Año de competencia para categoría
$anio_comp = (new DateTime($insc['fecha_inicio']))->format('Y');

// 2) Si viene POST, procesar actualización
$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer y sanitizar
    $dep_id  = (int)$_POST['deportista_id'];
    $modal   = trim($_POST['modalidad']);
    $nivelId = (int)$_POST['nivel_id'];
    $sub     = $_POST['subnivel'] !== '' ? trim($_POST['subnivel']) : null;

    // Validar fecha de nacimiento y categoría
    $stmt = $pdo->prepare("SELECT fecha_nacimiento FROM deportistas WHERE id = ?");
    $stmt->execute([$dep_id]);
    $fecha_nac = $stmt->fetchColumn();
    if (!$fecha_nac) {
        $errors[] = "No se encontró fecha de nacimiento del deportista.";
    } else {
        $cat = calcularCategoriaPorEdad($fecha_nac, $anio_comp);
        if (!$cat) {
            $errors[] = "El deportista no cumple la edad mínima para inscribirse.";
        }
    }

    // Validar duplicado: mismo deportista/competencia/modalidad distinto ID
    $stmt = $pdo->prepare("
      SELECT COUNT(*) FROM inscripciones
       WHERE competencia_id = :comp
         AND deportista_id  = :dep
         AND modalidad      = :mod
         AND id <> :this
    ");
    $stmt->execute([
      ':comp' => $insc['competencia_id'],
      ':dep'  => $dep_id,
      ':mod'  => $modal,
      ':this' => $id
    ]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Este deportista ya está inscrito en esa modalidad para esta competencia.";
    }

    // Si todo OK, actualizar
    if (empty($errors)) {
        $stmt = $pdo->prepare("
          UPDATE inscripciones
             SET deportista_id = :dep,
                 modalidad     = :mod,
                 nivel         = :niv,
                 subnivel      = :sub,
                 categoria     = :cat
           WHERE id = :id
        ");
        $stmt->execute([
          ':dep' => $dep_id,
          ':mod' => $modal,
          ':niv' => $_POST['nivel_text'] ?? $insc['nivel'],
          ':sub' => $sub,
          ':cat' => $cat,
          ':id'  => $id
        ]);
        $success = "Inscripción actualizada correctamente.";
        // refrescar datos
        header("Location: editar_inscripcion.php?id={$id}&ok=1");
        exit;
    }
}

// 3) Cargar lista de deportistas
$deportistas = $pdo
  ->query("SELECT id, nombre_completo FROM deportistas ORDER BY nombre_completo")
  ->fetchAll(PDO::FETCH_ASSOC);

// Pasar JSON a arrays para JS
$mods   = json_decode($insc['modalidades'], true);
$nivs   = json_decode($insc['niveles'],     true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Inscripción #<?= $id ?> – <?= htmlspecialchars($insc['nombre_evento']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .container { max-width: 800px; margin-top:2rem; }
  </style>
</head>
<body class="bg-light">
  <div class="container bg-white p-4 rounded shadow">
    <h2>Editar Inscripción #<?= $id ?></h2>
    <p><strong>Competencia:</strong> <?= htmlspecialchars($insc['nombre_evento']) ?></p>

    <?php if ($success || isset($_GET['ok'])): ?>
      <div class="alert alert-success"><?= $success ?: 'Actualizado correctamente.' ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <ul>
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="mb-3">
        <label class="form-label">Deportista</label>
        <select name="deportista_id" class="form-select" required>
          <?php foreach ($deportistas as $d): ?>
            <option value="<?= $d['id'] ?>"
              <?= $d['id']==$insc['deportista_id']?'selected':'' ?>>
              <?= htmlspecialchars($d['nombre_completo']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Modalidad</label>
        <select id="modalidad" name="modalidad" class="form-select" required></select>
      </div>

      <div class="mb-3">
        <label class="form-label">Nivel / Subnivel</label>
        <select id="nivel_subnivel" name="nivel_id" class="form-select" required></select>
        <input type="hidden" name="nivel_text" id="nivel_text">
      </div>

      <button type="submit" class="btn btn-primary">Guardar cambios</button>
      <a href="dashboard.php" class="btn btn-secondary ms-2">← Volver al Panel</a>
    </form>
  </div>

  <script>
  (function(){
    const mods = <?= json_encode($mods) ?>,
          niveaux = <?= json_encode($nivs) ?>,
          selMod = document.getElementById('modalidad'),
          selNS  = document.getElementById('nivel_subnivel'),
          currentModal = "<?= $insc['modalidad'] ?>",
          currentNivId = <?= (int)$insc['nivel_id'] ?>;

    // 1) Poblamos modalidades
    selMod.innerHTML = '';
    mods.forEach(m=>{
      const o = document.createElement('option');
      o.value = m;
      o.textContent = m.charAt(0).toUpperCase()+m.slice(1).replace('_',' ');
      if (m===currentModal) o.selected = true;
      selMod.appendChild(o);
    });

    // 2) Poblamos niveles/subniveles segun modalidad
    function fillLevels(){
      const mod = selMod.value;
      selNS.innerHTML = '';
      // fetch combinaciones desde servidor?
      fetch('../get_competencia_info.php?id=<?= $insc['competencia_id'] ?>')
        .then(r=>r.json())
        .then(data=>{
          data.niveles
            .filter(x=>x.modalidad===mod)
            .forEach(x=>{
              const o=document.createElement('option');
              o.value = x.id;
              o.textContent = x.nivel + (x.subnivel? ' – '+x.subnivel:'');
              if (x.id===currentNivId) o.selected=true;
              o.dataset.text = x.nivel; 
              selNS.appendChild(o);
            });
          // guardar nivel_text
          const sel = selNS.querySelector('option:checked');
          document.getElementById('nivel_text').value = sel?sel.dataset.text:'';
        });
    }
    selMod.addEventListener('change', fillLevels);
    selNS.addEventListener('change', ()=>{
      const sel = selNS.querySelector('option:checked');
      document.getElementById('nivel_text').value = sel? sel.dataset.text : '';
    });

    // init
    fillLevels();
  })();
  </script>
</body>
</html>
