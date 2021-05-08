<?php

include_once '../../includes/constants.php';
include_once "../../includes/usuario.php";

usuario::esUsuarioLogueado();
$accion = isset($_GET['accion']) ? $_GET['accion'] : "";
$session = $_SESSION;
switch ($accion) {
    
    // <editor-fold defaultstate="collapsed" desc="salir">
    case "salir":
        
        $user_logout = new usuario();
        $user_logout->logout();
        break; // </editor-fold>
        
    // <editor-fold defaultstate="collapsed" desc="guardar">
    case "guardar":
        $pago = new pago();
        $data = $_POST;
        if (count($data) > 0) {
            unset($data['procesar']);
            $exito = $pago->registrarPago($data);
            $exito['facturas'] = $_POST['facturas'];
            
        } else {
            header("location:".URL_INTRANET."/caja");
            return;
        }
        
        echo $twig->render('intranet/caja/index.html.twig', array("session" => $session,
            "resultado" => $exito,
            "accion" => "registrar"
        ));
        break;
    // </editor-fold>
        
    // <editor-fold defaultstate="collapsed" desc="consultar">
    case "consultar":
        if (isset($_POST['inmueble']) && isset($_POST['apto'])) {
            $facturas = new factura();
            $factura = $facturas->estadoDeCuenta($_POST['inmueble'], $_POST['apto']);


            if ($factura['suceed'] == true) {

                for ($index = 0; $index < count($factura['data']); $index++) {


                    $filename = "../avisos/" . $factura['data'][$index]['numero_factura'] . ".pdf";
                    $factura['data'][$index]['aviso'] = file_exists($filename);
                
                    
                }

                $cuenta = Array("inmueble" => $_POST['inmueble'],
                    "apto" => $_POST['apto'],
                    "recibos" => $factura['data']);
            }
        }
        echo $twig->render('intranet/caja/index.html.twig', array(
            "session" => $session,
            "cuenta" => $cuenta
        ));
        break; // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="default">
    default :
        echo $twig->render('intranet/caja/index.html.twig', array(
            "session" => $session
        ));
        break; // </editor-fold>

}
?>