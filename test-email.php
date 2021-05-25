<?php

include './includes/constants.php';
include './includes/mailto.php';
// define("SMTP_SERVER","a2plcpnl0361.prod.iad2.secureserver.net");
// define("PORT",465);
// define("USER_MAIL","pagoscondominio@administradorasac.com");
// define("PASS_MAIL","10537439");
// define("SMTP",2);
$mail = new mailto();
echo("enviando......<br>");
$r = $mail->enviar_email("Prueba envío correo SAC", "Este es un mensaje de prueba", '', "ticket.soporte21@gmail.com", "Edgar Messia");
echo("enviado<br>");                    
if ($r=='') {
    echo(".- Mensaje enviado a Ok!\n");
} else {
    echo(".- Mensaje enviado a Fallo\n");
}