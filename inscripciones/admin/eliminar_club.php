<?php
// admin/eliminar_club.php
// Elimina un club y todos sus datos relacionados (inscripciones, deportistas, pagos_club)

session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/../conexion.php';
date_default_timezone_set('America/Santiago');

// 1) Leer ID del club a eliminar
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    exit('ID de club inválido');
}

try {
    // 2) Iniciar transacción
    $pdo->beginTransaction();

    // 3) Eliminar inscripciones de deportistas de este club
    $stmt = $pdo->prepare(
        "DELETE i
           FROM inscripciones i
           JOIN deportistas d ON i.deportista_id = d.id
          WHERE d.club_id = :club"
    );
    $stmt->execute([':club' => $id]);

    // 4) Eliminar pagos asociados a este club
    $stmt = $pdo->prepare("DELETE FROM pagos_club WHERE club_id = :club");
    $stmt->execute([':club' => $id]);

    // 5) Eliminar deportistas del club
    $stmt = $pdo->prepare("DELETE FROM deportistas WHERE club_id = :club");
    $stmt->execute([':club' => $id]);

    // 6) Eliminar el club
    $stmt = $pdo->prepare("DELETE FROM clubs WHERE id = :club");
    $stmt->execute([':club' => $id]);

    // 7) Confirmar transacción
    $pdo->commit();

    // 8) Redirigir al dashboard con mensaje
    header('Location: dashboard.php?msg=Club+eliminado+correctamente');
    exit;

} catch (Exception $e) {
    // Deshacer transacción en caso de error
    $pdo->rollBack();
    echo '<div class="alert alert-danger m-3">'
       . '<strong>Error al eliminar club:</strong> ' . htmlspecialchars($e->getMessage())
       . '</div>';
    echo '<div class="m-3"><a href="dashboard.php" class="btn btn-secondary">← Volver al Panel</a></div>';
    exit;
}
