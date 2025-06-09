<?php
$dsn  = 'mysql:host=localhost;dbname=inscripciones;charset=utf8mb4';
$user = 'inscrip';
$pass = 'Maiteam';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo 'Error de conexión: ' . $e->getMessage();
    exit;
}
// 1) PHP en hora de Santiago
date_default_timezone_set('America/Santiago');

// 2) MySQL en hora de Santiago
$pdo->exec("SET time_zone = '-04:00'");
?>
