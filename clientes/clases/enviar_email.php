<?php

use PHPMailer\PHPMailer\{PHPMailer, SMTP, Exception};


require '../phpmailer/src/PHPMailer/PHPMailer.php';
require '../phpmailer/src/PHPMailer/SMTP.php';
require '../phpmailer/src/PHPMailer/Exception.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; //SMTP ::DEBUG_OFF;
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'jireh.bussines@gmail.com';                     //SMTP username
    $mail->Password   = 'emailsecret';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('jireh.bussines@gmail.com', 'COMIDA JIREH');
    $mail->addAddress('jireh.bussines@gamil.com', 'JIREH User');     //Add a recipient



    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Detalles de su compra';
    
    $cuerpo = '<h4>Gracias por su compra</h4>';
    $cuerpo .= '<p>El ID de su compra es </b>'.$idTransaccion .'</b></p>';

    $mail->Body    = utf8_decode($cuerpo);
    $mail->AltBody = 'Le agregamos los detalles de su compra.';

    $mail->setLanguage('es', '../phpmailer/language/phpmailer.lang-es.php');

    $mail->send();
} catch (Exception $e) {
    echo "Error al enviar el correo electronico de la compra: {$mail->ErrorInfo}";
}
