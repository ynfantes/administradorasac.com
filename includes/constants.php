<?php
// <editor-fold defaultstate="collapsed" desc="configuracion regional">
date_default_timezone_set("America/La_Paz");
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="init">
$debug = false;
$sistema = "/";
$email_error = false;
$mostrar_error = true;

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Cheqeuo servidor">

if ($_SERVER['SERVER_NAME'] == "www.administradorasac.com" | $_SERVER['SERVER_NAME'] == "administradorasac.com") {
    $user = "sac_root";
    $password = "dmn+str";
    $db = "administradora_sac";
    $email_error = true;
    $mostrar_error = true;
    $debug = FALSE;
    //define("HOST", "200.74.207.8");
} else {
//    $user = "root";
//    $password = "";
//    $db = "grupvenc_valoriza2";
    $sistema = "/administradorasac.com/";
    $user = "root";
    $password = "";
    $db = "administradora_sac";
}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Acceso a la BD">
define("HOST", "localhost");
define("USER", $user);
define("PASSWORD", $password);
define("DB", $db);
// </editor-fold>
//<editor-fold defaultstate="collapsed" desc="configuracion de ficheros del sistema">
define("SISTEMA", $sistema);
define("EMAIL_ERROR", $email_error);
define("EMAIL_CONTACTO", "ynfantes@gmail.com");
define("EMAIL_TITULO", "error");
define("MOSTRAR_ERROR", $mostrar_error);
define("DEBUG", $debug);

define("TITULO", "Administradora SAC");
/**
 * para las urls
 */
define("ROOT", 'http://' . $_SERVER['SERVER_NAME'] . SISTEMA);
define("URL_SISTEMA", ROOT . "enlinea");
define("URL_INTRANET", ROOT . "intranet");
/**
 * para los includes
 */
if($_SERVER['DOCUMENT_ROOT']=="") {
    define("SERVER_ROOT", substr($_SERVER['SCRIPT_FILENAME'], 0, -strlen($_SERVER['SCRIPT_NAME'])) . SISTEMA);
} else {
    define("SERVER_ROOT", $_SERVER['DOCUMENT_ROOT'] . SISTEMA);
}

/*set_include_path(SERVER_ROOT . "/site/");*/
define("TEMPLATE", SERVER_ROOT . "/template/");
define("mailPHP",0);
define("sendMail",1);
define("SMTP",2);
//</editor-fold>

////<editor-fold defaultstate="collapsed" desc="Twig">
//include_once '../includes/twig/lib/Twig/Autoloader.php';
//include_once '../includes/extensiones.php';

include_once dirname(dirname(dirname(__FILE__))).'/framework/twig/lib/Twig/Autoloader.php';
include_once SERVER_ROOT . 'includes/extensiones.php';
Twig_Autoloader::register();

//$loader = new Twig_Loader_Filesystem('../template');
$loader = new Twig_Loader_Filesystem(SERVER_ROOT . 'template');
$twig = new Twig_Environment($loader, array(
            'debug' => TRUE,
            'cache' => SERVER_ROOT . 'cache',
            'cache' => true,
            "auto_reload" => TRUE)
);
if (isset($_SESSION))
    $twig->addGlobal("session", $_SESSION);

$twig->addExtension(new extensiones());
$twig->addExtension(new Twig_Extension_Debug());

//</editor-fold>
//<editor-fold defaultstate="collapsed" desc="autoload">

function __autoload($clase) {
    include_once SERVER_ROOT . "includes/" . $clase . ".php";
}

spl_autoload_register("__autoload", false);
//</editor-fold>
//<editor-fold defaultstate="collapsed" desc="cerrar sesión">
if (isset($_GET['logout']) && $_GET['logout'] == true) {
    $user_logout = new propietario();
    $user_logout->logout();
}
//</editor-fold>
define("NOMBRE_APLICACION","Sac en Línea");
define("ACTUALIZ","data/");
define("ARCHIVO_INMUEBLE","INMUEBLE.txt");
define("ARCHIVO_CUENTAS","CUENTAS.txt");
define("ARCHIVO_FACTURA","FACTURA.txt");
define("ARCHIVO_FACTURA_DETALLE","FACTURA_DETALLE.txt");
define("ARCHIVO_JUNTA_CONDOMINIO","JUNTA_CONDOMINIO.txt");
define("ARCHIVO_PROPIEDADES","PROPIEDADES.txt");
define("ARCHIVO_PROPIETARIOS","PROPIETARIOS.txt");
define("ARCHIVO_EDO_CTA_INM","EDO_CUENTA_INMUEBLE.txt");
define("ARCHIVO_CUENTAS_DE_FONDO","CUENTAS_FONDO.txt");
define("ARCHIVO_MOVIMIENTOS_DE_FONDO","MOVIMIENTO_FONDO.txt");
define("ARCHIVO_ACTUALIZACION","ACTUALIZACION.txt");
define("ARCHIVO_MOVIMIENTO_CAJA","MOVIMIENTO_CAJA.txt");
define("SMTP_SERVER","mail.administradorasac.com");
define("PORT",25);
define("USER_MAIL","pagoscondominio@administradorasac.com");
define("PASS_MAIL","10537439");
define("MESES_COBRANZA","3");
define("GRAFICO_FACTURACION",1);
define("GRAFICO_COBRANZA",1);
define("DEMO",0);
define("RECIBO_GENERAL",0);
define("GRUPOS",0);