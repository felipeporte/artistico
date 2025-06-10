<?php
session_start();
if (!isset($_SESSION['club_id'])) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('America/Santiago');
// Definir fecha límite
$fecha_limite = strtotime("2025-06-20 6:59:59");
$fecha_actual = time();
$inscripciones_cerradas = $fecha_actual > $fecha_limite;

include __DIR__ . '/conexion.php';

$club_id          = $_SESSION['club_id'];
$anio_competencia = 2025;
$errors           = [];
$success_message  = '';

// Obtener zona del club para filtrar competencias
$stmt = $pdo->prepare("SELECT zona FROM clubs WHERE id = ?");
$stmt->execute([$club_id]);
$zona_club = $stmt->fetchColumn();

// — Funciones de cálculo de categoría y validación —
function calcularCategoriaPorEdad($fecha_nacimiento, $anio, $modalidad = '', $nivel = '', $subnivel = '') {
    $limite = new DateTime("$anio-12-31");
    $nac    = new DateTime($fecha_nacimiento);
    $edad   = $nac->diff($limite)->y;

    // Parche: si es freeskaing escuela d y edad 17 o más → categoría Senior
    if (strtolower($modalidad) === 'freeskating' && strtolower($nivel) === 'escuela' && strtolower($subnivel) === 'd' && $edad >= 17) {
        return "Todo Competidor";
    }

    
    if ($edad >= 5  && $edad <=  6) return "Pre-novato";
    if ($edad >= 6  && $edad <=  7) return "Novato";
    if ($edad >= 8  && $edad <=  9) return "Tots";
    if ($edad === 10|| $edad === 11) return "Minis";
    if ($edad === 12|| $edad === 13) return "Espoir";
    if ($edad === 14|| $edad === 15) return "Cadet";
    if ($edad === 16)                 return "Youth";
    if ($edad === 17|| $edad === 18)  return "Junior";
    if ($edad >= 19)                  return "Senior";
    return null;
}

function puedeInscribirse($pdo, $nivel_id, $fecha_nac, $anio, $modalidad = '', $nivel = '', $subnivel = '') {
    $cat = calcularCategoriaPorEdad($fecha_nac, $anio, $modalidad, $nivel, $subnivel);
    if (!$cat) return false;
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) 
          FROM nivel_categoria nc
          JOIN categorias_edad c 
            ON nc.categoria_id = c.id
         WHERE nc.nivel_id       = ?
           AND c.nombre_categoria = ?"
    );
    $stmt->execute([$nivel_id, $cat]);
    return $stmt->fetchColumn() > 0;
}

// — Procesar formulario de inscripción —
if (isset($_POST['inscribir'])) {
    $competencia_id = (int) $_POST['competencia_id'];
    $deportista_id  = (int) $_POST['deportista_id'];
    $modalidad      = $_POST['modalidad'];
    $nivel_id       = (int) $_POST['nivel_id'];

    // 1) Sacar nombre_nivel y subnivel
    $stmt = $pdo->prepare("SELECT nombre_nivel, COALESCE(subnivel,'') AS subnivel FROM niveles WHERE id = ?");
    $stmt->execute([$nivel_id]);
    $nivelData  = $stmt->fetch(PDO::FETCH_ASSOC);
    $nivel_text = $nivelData['nombre_nivel'];
    $subnivel   = $nivelData['subnivel'];

    // 2) Fecha de nacimiento
    $stmt = $pdo->prepare("SELECT fecha_nacimiento FROM deportistas WHERE id = ?");
    $stmt->execute([$deportista_id]);
    $fecha_nac = $stmt->fetchColumn();

    if (!$fecha_nac) {
        $errors[] = "No se encontró la fecha de nacimiento del deportista.";
    } else {
        $categoria = calcularCategoriaPorEdad($fecha_nac, $anio_competencia, $modalidad, $nivel_text, $subnivel);
        if (!$categoria) {
            $errors[] = "El deportista no cumple la edad mínima para inscribirse.";
        } elseif ($categoria !== "Todo Competidor" && !puedeInscribirse($pdo, $nivel_id, $fecha_nac, $anio_competencia, $modalidad, $nivel_text, $subnivel)) {
            $stmt = $pdo->prepare(
                "SELECT c.nombre_categoria
                  FROM nivel_categoria nc
                  JOIN categorias_edad c 
                    ON nc.categoria_id = c.id
                 WHERE nc.nivel_id = ?"
            );
            $stmt->execute([$nivel_id]);
            $permitidas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $errors[] = "La categoría '". htmlspecialchars($categoria) . "' no está permitida. Permitidas: " . implode(', ', $permitidas);
        }
    }

    // 3) Chequear duplicados
    $dup = $pdo->prepare(
        "SELECT COUNT(*) 
          FROM inscripciones 
         WHERE deportista_id   = :dep 
           AND competencia_id  = :comp 
           AND modalidad       = :mod"
    );
    $dup->execute([
      ':dep'  => $deportista_id,
      ':comp' => $competencia_id,
      ':mod'  => $modalidad
    ]);
    if ($dup->fetchColumn() > 0) {
        $errors[] = "Este deportista ya está inscrito en esa competencia y modalidad.";
    }

    // 4) Insertar si no hay errores
    if (empty($errors)) {
        try {
            $ins = $pdo->prepare(
                "INSERT INTO inscripciones
                  (competencia_id, deportista_id, modalidad, nivel, subnivel, categoria, fecha_inscripcion)
                VALUES
                  (:comp, :dep, :mod, :niv, :sub, :cat, NOW())"
            );
            $ins->execute([
                ':comp'=> $competencia_id,
                ':dep' => $deportista_id,
                ':mod' => $modalidad,
                ':niv' => $nivel_text,
                ':sub' => $subnivel,
                ':cat' => $categoria
            ]);
            $success_message = "Inscripción registrada correctamente.";
        } catch (PDOException $e) {
            $errors[] = "Error al guardar inscripción: " . $e->getMessage();
        }
    }
}
?>
<?php
// (Todo el código PHP original se mantiene arriba)
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inscribir a Competencia</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .form-login-wrapper {
      display: flex;
      justify-content: center;
      padding: 2rem 1rem;
    }
    .form-login {
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 600px;
    }
    .floating-label {
      position: relative;
      margin-bottom: 1.5rem;
    }
    .floating-label input,
    .floating-label select {
      width: 100%;
      padding: 1rem 0.75rem;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 5px;
      background: none;
    }
    .floating-label label {
      position: absolute;
      top: 1rem;
      left: 0.75rem;
      color: #999;
      font-size: 1rem;
      transition: all 0.2s ease;
      pointer-events: none;
    }
    .floating-label input:focus + label,
    .floating-label input:not(:placeholder-shown) + label,
    .floating-label select:focus + label,
    .floating-label select:not(:placeholder-shown) + label {
      top: -0.5rem;
      left: 0.5rem;
      font-size: 0.8rem;
      background: #fff;
      padding: 0 0.25rem;
      color: #333;
    }
    .btn {
      display: inline-block;
      padding: 0.75rem 1.5rem;
      font-size: 1rem;
      background: #1e38a6;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      width: 100%;
    }
    .btn:hover {
      background: #003f7d;
    }
    .btn-outline {
      background: transparent;
      border: 2px solid #1e38a6;
      color: #1e38a6;
      display: inline-block;
      padding: .5rem 1rem;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
    }
    .btn-outline:hover {
      background: #1e38a6;
      color: #fff;
    }
    .alert {
      max-width: 600px;
      margin: 1rem auto;
      padding: 0.75rem 1rem;
      border-radius: 5px;
    }
    .alert-success {
      background: #d4edda;
      color: #155724;
    }
    .alert-danger {
      background: #f8d7da;
      color: #721c24;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../templates/header.php'; ?>
  <main class="login">
    <section class="hero">
      <div class="container">
        <h1 class="text-center">Inscribir a Competencia – <?= htmlspecialchars($_SESSION['club_nombre']) ?></h1>

        <?php if ($success_message): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <div class="form-login-wrapper">
        <?php if ($inscripciones_cerradas): ?>
          <div style="color:red; text-align:center; margin: 20px 0;">
            <strong>El período de inscripciones ha finalizado.</strong>
          </div>
        <?php endif; ?>
          <form method="POST" class="form-login">
            <div class="floating-label">
              <select name="competencia_id" id="competencia_id" required>
                <option value="" disabled selected hidden>Seleccionar competencia</option>
                <?php
                $stmt = $pdo->prepare(
                  "SELECT id, nombre_evento
                    FROM competencias
                   WHERE (zona = ? OR zona = 'TODAS') AND fecha_inicio >= NOW()
                   ORDER BY fecha_inicio DESC"
                );
                $stmt->execute([$zona_club]);
                while ($c = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value=\"{$c['id']}\">".htmlspecialchars($c['nombre_evento'])."</option>";
                }
                ?>
              </select>
              <label for="competencia_id">Competencia</label>
            </div>

            <div class="floating-label">
              <select name="deportista_id" id="deportista_id" required>
                <option value="" disabled selected hidden>Seleccionar deportista</option>
                <?php
                $stmt = $pdo->prepare("SELECT id, nombre_completo FROM deportistas WHERE club_id = ?");
                $stmt->execute([$club_id]);
                while ($d = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value=\"{$d['id']}\">".htmlspecialchars($d['nombre_completo'])."</option>";
                }
                ?>
              </select>
              <label for="deportista_id">Deportista</label>
            </div>

            <div class="floating-label">
              <select name="modalidad" id="modalidad" required></select>
              <label for="modalidad">Modalidad</label>
            </div>

            <div class="floating-label">
              <select name="nivel_id" id="nivel_subnivel" required></select>
              <label for="nivel_subnivel">Nivel / Subnivel</label>
            </div>

            <div class="floating-label">
              <input type="text" name="categoria" id="categoria" readonly placeholder=" ">
              <label for="categoria">Categoría</label>
            </div>

            <button type="submit" name="inscribir" class="btn" <?= $inscripciones_cerradas ? 'disabled' : '' ?> >Inscribir</button>
          </form>
          <div style="text-align:center; margin-top:1rem;">
            <a href="panel_club.php" class="btn-outline">← Volver al Panel</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>© 2025 Federación Chilena de Patinaje. Todos los derechos reservados.</p>
    </div>
  </footer>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const comp = document.getElementById('competencia_id'),
          mod  = document.getElementById('modalidad'),
          ns   = document.getElementById('nivel_subnivel'),
          dep  = document.getElementById('deportista_id'),
          cat  = document.getElementById('categoria');
    let combos = [];

    comp.addEventListener('change', () => {
      fetch('get_competencia_info.php?id=' + comp.value)
        .then(r => r.json())
        .then(data => {
          combos = data.combinaciones || [];
          mod.innerHTML = '<option disabled selected hidden>Seleccionar modalidad</option>';
          (data.modalidades||[]).forEach(m => {
            const o = document.createElement('option');
            o.value = m;
            o.text  = m.charAt(0).toUpperCase()+m.slice(1).replace('_',' ');
            mod.appendChild(o);
          });
          mod.dispatchEvent(new Event('change'));
        })
        .catch(console.error);
    });

    mod.addEventListener('change', () => {
      ns.innerHTML = '<option disabled selected hidden>Seleccionar nivel / subnivel</option>';
      combos.filter(c=>c.modalidad===mod.value)
            .forEach(c=>{
        const o = document.createElement('option');
        o.value = c.nivel_id;
        o.text  = c.nivel + (c.subnivel? ' – '+c.subnivel:'');
        ns.appendChild(o);
      });
      ns.dispatchEvent(new Event('change'));
    });

    function recalcular() {
      if(!dep.value||!ns.value) return;
      fetch('obtener_categoria.php?id='+dep.value)
        .then(r=>r.text())
        .then(t=>cat.value=t)
        .catch(console.error);
    }
    dep.addEventListener('change', recalcular);
    ns.addEventListener('change', recalcular);

    comp.dispatchEvent(new Event('change'));
  });
  </script>
</body>
</html>
