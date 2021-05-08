<?php
$message="Contacto Administradora Sac<br/>";
$message.=$_POST["email"]."<br>";
$message.=$_POST["name"]."<br>";
$message.=$_POST["phone"]."<br>";
$message.=$_POST["message"];
$message = utf8_decode(stripslashes($message));
$headers = "Content-Language:es-ve\n";
$headers .= "Content-Type: text/html; charset=iso-8859-1\n";
$headers .= "bcc:ynfantes@gmail.com\n";

$subject = "Contacto administradorasac.com";
$headers .= 'From: Contacto <info@administradorasac.com>'."\r\n".'Reply-To:'.$_POST["email"]."\r\n" ;
$email = "sistemas@administradorasac.com";

//$email = isset($_POST['token']) ? "info@sistemavaloriza.com" : "ynfantes@gmail.com";
if ($_POST["email"]!='' && $_POST["name"]!='' && $_POST["phone"]!='' && $_POST["message"]!='') {
    if (mail($email,$subject,$message,$headers)) {
        echo "Mensaje enviado con éxito!\r\nLo estaremos contactando a la brevedad. Gracias por contactarnos.";
    } else {
        echo "¡Ups! Ocurrió un error al tratar de enviar el mensaje</strong>
        Inténtelo nuevamente. Gracias por contactarnos.";    
    }
}
