<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.zoho.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'comisiontecnica@artistico.cl';
    $mail->Password   = 'pimQyNkw49SU';  // tu contraseña de aplicación
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    $mail->setFrom('comisiontecnica@artistico.cl', 'Soporte Artistico');
    $mail->addAddress('felipe.porte@pesaschile.cl'); // cambia esto por tu correo personal

    $mail->isHTML(true);
    $mail->Subject = 'Prueba de correo';
    $mail->Body    = '<p>Este es un <strong>correo de prueba</strong> enviado con PHPMailer y Zoho.</p>';

    $mail->send();
    echo "Correo enviado con éxito ✔️";
} catch (Exception $e) {
    echo "Error al enviar: {$mail->ErrorInfo}";
}
