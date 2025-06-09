<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // ajusta si estás en otra ruta

function enviarCorreoRecuperacion($correo, $link) {
    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP de Zoho
        $mail->isSMTP();
        $mail->Host       = 'smtp.zoho.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'comisiontecnica@artistico.cl';
        $mail->Password   = 'pimQyNkw49SU'; // tu contraseña de aplicación
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        // Correo del remitente
        $mail->setFrom('comisiontecnica@artistico.cl', 'Soporte Artistico');
        $mail->addAddress($correo);

        // Contenido del mensaje
        $mail->isHTML(true);
        $mail->Subject = 'Crea o recupera tu clave de acceso';
        $mail->Body = "
            <p>Hola,</p>
            <p>Has solicitado crear o recuperar tu clave de acceso al sistema de inscripciones.</p>
            <p>Haz clic en el siguiente enlace para continuar:</p>
            <p><a href='$link'>$link</a></p>
            <p>Este enlace expirará en 1 hora.</p>
            <br>
            <p>Saludos,<br>Comisión Técnica - Federación de Patinaje</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
