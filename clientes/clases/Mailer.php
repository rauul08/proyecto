<?php	

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {

    function enviarEmail($email, $asunto, $cuerpo)
    {
        require_once '../config/config.php';    
        require '../phpmailer/src/PHPMailer.php';
        require '../phpmailer/src/SMTP.php';
        require '../phpmailer/src/Exception.php';

        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->SMTPDebug = SMTP::DEBUG_OFF;                   // Desactiva la depuración, puedes usar DEBUG_SERVER para más detalles
            $mail->isSMTP();                                      // Enviar usando SMTP
            $mail->Host       = MAIL_HOST;                        // Establece el servidor SMTP
            $mail->SMTPAuth   = true;                             // Habilita autenticación SMTP
            $mail->Username   = MAIL_USER;                        // Nombre de usuario SMTP
            $mail->Password   = MAIL_PASS;                        // Contraseña SMTP (Contraseña de Aplicación)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;   // Habilita cifrado TLS implícito
            $mail->Port       = MAIL_PORT;                        // Puerto TCP para conectarse (TLS = 587)

            // Emisor
            $mail->setFrom(MAIL_USER, 'COMIDA JIREH');
            // Receptor
            $mail->addAddress($email);                            // Añadir un destinatario

            // Contenido
            $mail->isHTML(true);                                  // Establecer formato de correo a HTML
            $mail->Subject = $asunto;
            $mail->Body    = utf8_decode($cuerpo);                // Cuerpo del correo
            $mail->setLanguage('es', './phpmailer/language/phpmailer.lang-es.php');

            // Envío del correo
            if ($mail->send()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            echo "Error al enviar el correo electrónico: {$mail->ErrorInfo}";
            return false;
        }
    }
}
