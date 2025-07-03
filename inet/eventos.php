<?php
// admin/eventos.php
require $_SERVER['DOCUMENT_ROOT'] .'/inscripciones/conexion.php'; 
// TODO: Verifica sesión de admin

// 1) Procesamiento Create/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = $_POST['id']           ?? null;
    $nombre    = trim($_POST['nombre']  ?? '');
    $slug      = trim($_POST['slug']    ?? '');
    $fi        = $_POST['fecha_inicio'] ?? '';
    $ff        = $_POST['fecha_fin']    ?? '';
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $activo    = isset($_POST['activo']) ? 1 : 0;

    if ($nombre && $slug && $fi && $ff && $ubicacion) {
        if ($id) {
            // UPDATE
            $sql = "UPDATE competencias_index
                      SET nombre=?, slug=?, fecha_inicio=?, fecha_fin=?, ubicacion=?, activo=?
                    WHERE id=?";
            $pdo->prepare($sql)
                ->execute([$nombre,$slug,$fi,$ff,$ubicacion,$activo,$id]);
        } else {
            // INSERT
            $sql = "INSERT INTO competencias_index
                      (nombre,slug,fecha_inicio,fecha_fin,ubicacion,activo)
                    VALUES (?,?,?,?,?,?)";
            $pdo->prepare($sql)
                ->execute([$nombre,$slug,$fi,$ff,$ubicacion,$activo]);
        }
        header('Location: eventos.php');
        exit;
    } else {
        $error = 'Todos los campos son obligatorios.';
    }
}

// 2) Listado de eventos
$stmt = $pdo->query("SELECT * FROM competencias_index ORDER BY fecha_inicio");
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include 'template/header.php'; ?>
</head>
<body>
  <?php include 'template/menu.php'; ?>

  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>Eventos</h1>
      <button id="btn-new" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#eventModal">
        + Nuevo Evento
      </button>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Nombre</th><th>Fechas</th><th>Ubicación</th><th>Activo</th><th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($eventos as $ev): ?>
        <tr>
          <td><?=htmlspecialchars($ev['nombre'])?></td>
          <td>
            <?=date('d/m/Y',strtotime($ev['fecha_inicio']))?> –
            <?=date('d/m/Y',strtotime($ev['fecha_fin']))?>
          </td>
          <td><?=htmlspecialchars($ev['ubicacion'])?></td>
          <td><?= $ev['activo'] ? '✅' : '❌' ?></td>
          <td class="text-nowrap">
            <button class="btn btn-sm btn-primary btn-edit"
                    data-id="<?=$ev['id']?>"
                    data-nombre="<?=htmlspecialchars($ev['nombre'])?>"
                    data-slug="<?=htmlspecialchars($ev['slug'])?>"
                    data-fi="<?=$ev['fecha_inicio']?>"
                    data-ff="<?=$ev['fecha_fin']?>"
                    data-ubicacion="<?=htmlspecialchars($ev['ubicacion'])?>"
                    data-activo="<?=$ev['activo']?>"
                    data-bs-toggle="modal" data-bs-target="#eventModal">
              Editar
            </button>
            <a href="competencia_delete.php?id=<?=$ev['id']?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('¿Eliminar evento?')">Borrar</a>
            <a href="documentos.php?evento_id=<?=$ev['id']?>"
               class="btn btn-sm btn-secondary">Docs</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Modal de Crear/Editar -->
  <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="post" class="modal-form">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Nuevo Evento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" id="event-id">
            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input name="nombre" id="event-nombre" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Slug (URL)</label>
              <input name="slug" id="event-slug" class="form-control" required>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" id="event-fi" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Fecha Fin</label>
                <input type="date" name="fecha_fin" id="event-ff" class="form-control" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Ubicación</label>
              <input name="ubicacion" id="event-ubicacion" class="form-control" required>
            </div>
            <div class="form-check">
              <input type="checkbox" name="activo" id="event-activo" class="form-check-input">
              <label class="form-check-label" for="event-activo">Mostrar en página pública</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Script para manejar el modal -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const modal = new bootstrap.Modal(document.getElementById('eventModal'));
      const title = document.getElementById('modalTitle');
      const formFields = {
        id:        document.getElementById('event-id'),
        nombre:    document.getElementById('event-nombre'),
        slug:      document.getElementById('event-slug'),
        fi:        document.getElementById('event-fi'),
        ff:        document.getElementById('event-ff'),
        ubicacion: document.getElementById('event-ubicacion'),
        activo:    document.getElementById('event-activo'),
      };

      // Nuevo evento
      document.getElementById('btn-new').addEventListener('click', () => {
        title.textContent = 'Nuevo Evento';
        formFields.id.value = '';
        formFields.nombre.value = '';
        formFields.slug.value = '';
        formFields.fi.value = '';
        formFields.ff.value = '';
        formFields.ubicacion.value = '';
        formFields.activo.checked = true;
      });

      // Editar evento
      document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
          title.textContent = 'Editar Evento';
          formFields.id.value        = btn.dataset.id;
          formFields.nombre.value    = btn.dataset.nombre;
          formFields.slug.value      = btn.dataset.slug;
          formFields.fi.value        = btn.dataset.fi;
          formFields.ff.value        = btn.dataset.ff;
          formFields.ubicacion.value = btn.dataset.ubicacion;
          formFields.activo.checked  = btn.dataset.activo === '1';
        });
      });
    });
  </script>
  <!-- Asegúrate de tener bootstrap.bundle.min.js incluido en template/header o aquí -->
</body>
</html>
