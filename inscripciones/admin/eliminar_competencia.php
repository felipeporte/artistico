<?php
// admin/eliminar_competencia.php
// Elimina una competencia y sus datos relacionados (inscripciones, niveles_competencia, pagos_club)

session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/../conexion.php';
date_default_timezone_set('America/Santiago');

// Leer ID de competencia
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    exit('ID de competencia inválido.');
}

try {
    // Iniciar transacción
    $pdo->beginTransaction();

    // 1) Eliminar inscripciones
    $stmt = $pdo->prepare("DELETE FROM inscripciones WHERE competencia_id = ?");
    $stmt->execute([$id]);

    // 2) Eliminar niveles_competencia
    $stmt = $pdo->prepare("DELETE FROM niveles_competencia WHERE competencia_id = ?");
    $stmt->execute([$id]);

    // 3) Eliminar pagos asociados
    $stmt = $pdo->prepare("DELETE FROM pagos_club WHERE competencia_id = ?");
    $stmt->execute([$id]);

    // 4) Eliminar la competencia
    $stmt = $pdo->prepare("DELETE FROM competencias WHERE id = ?");
    $stmt->execute([$id]);

    // Confirmar
    $pdo->commit();

    // Redirigir con mensaje
    header('Location: dashboard.php?msg=Competencia+eliminada+correctamente');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    echo '<div class="alert alert-danger m-3">'
       . '<strong>Error al eliminar competencia:</strong> ' . htmlspecialchars($e->getMessage())
       . '</div>';
    echo '<div class="m-3"><a href="dashboard.php" class="btn btn-secondary">← Volver al Panel</a></div>';
    exit;
}
