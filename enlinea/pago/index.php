<?php
include_once '../../includes/constants.php';
$propietario = new propietario();
if (!isset($_GET['id'])) {    
    $propietario->esPropietarioLogueado();
    $session = $_SESSION;
}
$bitacora = new bitacora();

$accion = isset($_GET['accion']) ? $_GET['accion'] : "listar";


switch ($accion) {
    
    // <editor-fold defaultstate="collapsed" desc="cancelacion">
    case "cancelacion":
        $titulo = $_GET['id'] . ".pdf";
        $content = 'Content-type: application/pdf';
        $url = ROOT . "cancelacion.gastos/" . $_GET['id'] . ".pdf";
        header('Content-Disposition: attachment; filename="' . $titulo . '"');
        header($content);
        readfile($url,false);
        break;
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="listarRecibosCancelados">
    case "listarRecibosCancelados":
        $propiedad = new propiedades();
        $inmuebles = new inmueble();
        $pagos = new pago();

        $propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);
        $cuenta = Array();

        if ($propiedades['suceed'] == true) {

            foreach ($propiedades['data'] as $propiedad) {

                $inmueble = $inmuebles->ver($propiedad['id_inmueble']);
                $pago = $pagos->listarCancelacionDeGastos($propiedad['id_inmueble'], $propiedad['apto']);
                
                if ($pago['suceed'] == true) {
                    $bitacora->insertar(Array(
                        "id_sesion"=>$session['id_sesion'],
                        "id_accion"=> 12,
                        "descripcion"=>count($pago['data'])." recibos(s) registrado(s).",
                    ));
                    for ($index = 0; $index < count($pago['data']); $index++) {
                        
                        $filename = "../../cancelacion.gastos/" . $pago['data'][$index]['numero_factura'] . ".pdf";
                        $pago['data'][$index]['recibo'] = file_exists($filename);
                        
                    }
                    
                    $cuenta[] = Array("inmueble" => $inmueble['data'][0],
                        "propiedades" => $propiedad,
                        "cuentas" => $pago['data']);
                }
            }
        }
        
        echo $twig->render('enlinea/pago/cancelacion.gastos.html.twig', array("session" => $session,
            "cuentas" => $cuenta));


        break; 
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="ver">
    case "ver":
        $propiedad = new propiedades();
        $inmuebles = new inmueble();
        $pagos = new pago();

        $propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);
        $cuenta = Array();
        
        if ($propiedades['suceed'] == true) {
            
            foreach ($propiedades['data'] as $propiedad) {
                
                $legal = $propiedad['meses_pendiente'] > MESES_COBRANZA;
                
                $inmueble = $inmuebles->ver($propiedad['id_inmueble']);
                $pago = $pagos->listarPagosProcesados($propiedad['id_inmueble'], $propiedad['apto'], 5);
                
                if ($pago['suceed'] == true) {
                    $bitacora->insertar(Array(
                        "id_sesion"=>$session['id_sesion'],
                        "id_accion"=> 12,
                        "descripcion"=>count($pago['data'])." recibos(s) registrado(s).",
                    ));
                    for ($index = 0; $index < count($pago['data']); $index++) {
                        if (RECIBO_GENERAL==1) {
                            $filename = "../../cancelacion.gastos/" . $pago['data'][$index]['n_recibo'] . ".pdf";
                            $pago['data'][$index]['recibo'] = file_exists($filename);
                        } else {
                            $filename = "../../cancelacion.gastos/" . $pago['data'][$index]['id_factura'] . ".pdf";
                            $pago['data'][$index]['recibo'] = file_exists($filename);
                        }
                    }
                    $cuenta[] = Array("inmueble" => $inmueble['data'][0],
                        "propiedades" => $propiedad,
                        "cuentas" => $pago['data'],
                        "legal"=>$legal
                            );
                }
            }
        }

        echo $twig->render('enlinea/pago/cancelacion.html.twig', array("session" => $session,
            "cuentas" => $cuenta));


        break; // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="guardar">
    case "guardar":
        $pago = new pago();
        
        $data = $_POST;
        
        if (isset($session['usuario']['directorio'])) {
            $p = $propietario->obtenerPropietario( $data['id_inmueble'][0],  $data['id_apto'][0]);
            foreach ($p as $i) {
                
                $celular = str_replace("(", "", $data['telefono']);
                $celular = str_replace(")", "", $celular);
                $celular = str_replace("-", "", $celular);
                $celular = str_replace(" ", "", $celular);
                $propietario->actualizar($i['id'], Array('email'=>$data['email'],'telefono3'=>$celular,'modificado'=>1)); 
            }
        } else {    
            $celular = str_replace("(", "", $data['telefono']);
            $celular = str_replace(")", "", $celular);
            $celular = str_replace("-", "", $celular);
            $celular = str_replace(" ", "", $celular);
            $datos_contacto = Array('email'=>$data['email'],'telefono3'=>$celular,'modificado'=>1);
            $propietario->actualizar($session['usuario']['id'], $datos_contacto);
            $_SESSION['usuario']['email']=$data['email'];
            $_SESSION['usuario']['telefono3']=$celular;
        }
        if (isset($_FILES['soporte'])) {
            $file = explode(".",$_FILES['soporte']['name']);
            $extension = strtolower(end($file));
            $data['soporte']=$data['tipo_pago'].$data['numero_documento'].'_'.$data['id_inmueble'][0].'_'.$data['id_apto'][0].'.'.$extension;
            $tempFile = $_FILES['soporte']['tmp_name'];
            $mainFile = $data['soporte'];
            move_uploaded_file($tempFile,"soportes/".$mainFile);
        }
        if (count($data) > 0) {
            unset($data['registrar']);
            $data['fecha']=date("Y-m-d H:i:00 ", time());
            $data['id_sesion']=$session['id_sesion'];
            $exito = $pago->registrarPago($data);
            
            $bitacora->insertar(Array(
                "id_sesion"=>$session['id_sesion'],
                "id_accion"=> 9,
                "descripcion"=>$data['numero_documento']." >".$exito['mensaje'],
            ));
        } else {
            header("location:" . URL_SISTEMA . "/pago/registrar");
            return;
        }
        
        echo json_encode($exito);
        
        break;
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="registrar, listar">
        case "registrar":
        case "listar":
        default :
        $propiedad = new propiedades();
        $facturas = new factura();
        $inmuebles = new inmueble();
        $resultado = Array();
        if ($accion == 'guardar') {
            $resultado = $exito;
        }
        
        $propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);

        $cuenta = Array();
        
        $bitacora->insertar(Array(
            "id_sesion"=>$session['id_sesion'] ,
            "id_accion"=> 8 ,
            "descripcion"=>'Inicio del proceso' ,
        ));
        
        if ($propiedades['suceed'] == true) {

            foreach ($propiedades['data'] as $propiedad) {

            $inmueble = $inmuebles->ver($propiedad['id_inmueble']);
            $factura = $facturas->estadoDeCuenta($propiedad['id_inmueble'], $propiedad['apto']);
            
                if ($factura['suceed'] == true) {
                    
                    if ($propiedad['meses_pendiente'] < $inmueble['data'][0]["meses_mora"] && $inmueble['data'][0]["meses_mora"] > 0) {
                    
                        for ($index = 0; $index < count($factura['data']); $index++) {
                            $filename = "../avisos/" . $factura['data'][$index]['numero_factura'] . ".pdf";
                            
                            $factura['data'][$index]['aviso'] = file_exists($filename);
//                            $r = pago::facturaPendientePorProcesar($factura['data'][$index]['periodo'], $factura['data'][$index]['id_inmueble'], $factura['data'][$index]['apto']);
//                            if ($r['suceed'] && count($r['data'])>0) {
//                                $factura['data'][$index]['pagado'] = 1;
//                                $factura['data'][$index]['pagado_detalle']= "<i class='fa fa-calendar-o'></i> ".
//                                        Misc::date_format($r['data'][0]['fecha'])."<br>".
//                                        strtoupper($r['data'][0]['tipo_pago']." - Ref: ".
//                                                $r['data'][0]['numero_documento']."<br>Monto: ".
//                                                number_format($r['data'][0]['monto'],2,",","."));
//                            } else {
                                $factura['data'][$index]['pagado'] = 0;
                                $factura['data'][$index]['pagado_detalle']='';
//                            }
                        }
                    }
                    
                    $cuenta[] = Array(
                            "inmueble" => $inmueble['data'][0],
                            "propiedades" => $propiedad,
                            "cuentas" => $factura['data'],
                            "resultado" => $resultado
                            );
                }
            }
        }
        
        echo $twig->render('enlinea/pago/formulario.html.twig', array("session" => $session,
        "cuentas" => $cuenta,
        "accion" => $accion,
        "usuario"=>$session['usuario'],
        "propiedades"=>$propiedades['data']
        ));
        break; 
// </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="listaPagoDetalle">
    case "listaPagosDetalle":
        $pagos = new pago();
        $pago_detalle = $pagos->detalleTodosPagosPendientes();
        if ($pago_detalle['suceed'] && count($pago_detalle['data']) > 0) {
            echo "id_pago,id_inmueble,id_apto,monto,id_factura<br>";
            foreach ($pago_detalle['data'] as $value) {
                echo $value['id_pago'] . ",";
                echo $value['id_inmueble'] . ",";
                echo $value['id_apto'] . ",";
                echo $value['monto'] * 100 . ",";
                echo $value['id_factura'];
                echo "<br>";
            }
        }
        break; 
// </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="listaPagosMaestros">
    case "listaPagosMaestros":
        $pagos = new pago();
        $pagos_maestro = $pagos->listarPagosPendientes();

        if ($pagos_maestro['suceed'] && count($pagos_maestro['data']) > 0) {
            echo "id,fecha,tipo_pago,numero_documento,fecha_documento,monto,banco_origen,";
            echo "banco_destino,numero_cuenta,estatus,email,enviado,telefono<br>";
            foreach ($pagos_maestro['data'] as $pago) {
                echo $pago['id'] . ",";
                echo Misc::date_format($pago['fecha']) . ",";
                echo strtoupper($pago['tipo_pago']) . ","; 
               echo $pago["numero_documento"] . ",";
                echo Misc::date_format($pago["fecha_documento"]) . ",";
                echo $pago["monto"] * 100 . ",";
                echo $pago["banco_origen"] . ",";
                echo $pago["banco_destino"] . ",";
                echo str_replace("-", "", "#".$pago["numero_cuenta"]) . ",";
                echo strtoupper($pago["estatus"]) . ",";
                echo $pago["email"] . ",";
                echo $pago["enviado"] . ",";
                echo $pago["telefono"];
                echo "<br>";
            }
        }
        break; 
// </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="listaPagosPendientes">
    case "listarPagosPendientes":
        $pagos = new pago();
        $fecha = $_GET['id']=='sac'?null:$_GET['id'];
        $codigo_inmueble = null;
        if (isset($_GET['codigo_inmueble'])) {
            $codigo_inmueble = $_GET['codigo_inmueble'];
        }
        $pagos_maestro = $pagos->listarPagosPendientes($fecha, $codigo_inmueble);
        
        if ($pagos_maestro['suceed'] && count($pagos_maestro['data']) > 0) {
            
            foreach ($pagos_maestro['data'] as $pago) {
                //$usuario = $pagos->obtenerUsuarioPago($pago["numero_documento"]);
                
                $pago_detalle = $pagos->detallePagoPendiente($pago['id']);
                
                if ($pago_detalle['suceed'] && count($pago_detalle['data'] > 0)) {
                    $enviado = $pago["enviado"] == 0 ? "False" : "True";
                    echo "|" . $pago['id'] . "|";
                    echo Misc::date_format($pago['fecha']) . "|";
                    echo strtoupper($pago['tipo_pago']) . "|";
                    echo $pago["numero_documento"] . "|";
                    echo Misc::date_format($pago["fecha_documento"]) . "|";
                    echo Misc::number_format($pago["monto"]) . "|";
                    echo $pago["banco_origen"] . "|";
                    echo $pago["banco_destino"] . "|";
                    echo $pago["numero_cuenta"] . "|";
                    echo strtoupper($pago["estatus"]) . "|";
                    echo $pago["email"] . "|";
                    echo $enviado . "|";
                    echo $pago["telefono"] . "|";
                    echo $pago["id_sesion"]."|";
                    echo $pago["numero_cedula_titular"]."|";
                    // --
                    foreach ($pago_detalle['data'] as $value) {
                        echo $value['id_inmueble'] . "|";
                        echo $value['id_apto'] . "|";
                        echo Misc::number_format($value['monto']) . "|";
                        echo $value['id_factura'] . "|";
                        echo $value['periodo'] . "|";
                    }
                    echo "<br>";
                }
            }
        } else {
            echo "0";
        }
        break; // </editor-fold>
        
    // <editor-fold defaultstate="collapsed" desc="Pagos procesados general">
    case "listarPagosProcesadosGeneral":
        $pagos = new pago();
        $desde = null;
        $hasta = null;
        $codigo_inmueble = null;
        if (isset($_GET['desde'])) {
            $desde = $_GET['desde'];
        }
        if (isset($_GET['hasta'])) {
            $hasta = $_GET['hasta'];
        }
        if (isset($_GET['inmueble'])) {
            $codigo_inmueble = $_GET['inmueble'];
        }


        $pagos_maestro = $pagos->listarPagosProcesadosRangoFechas($desde, $hasta, $codigo_inmueble);


        if ($pagos_maestro['suceed'] && count($pagos_maestro['data']) > 0) {


            foreach ($pagos_maestro['data'] as $pago) {

                $pago_detalle = $pagos->detallePagoPendiente($pago['id']);


                if ($pago_detalle['suceed'] && count($pago_detalle['data'] > 0)) {
                    $enviado = $pago["enviado"] == 0 ? "False" : "True";
                    echo "|" . $pago['id'] . "|";
                    echo Misc::date_format($pago['fecha']) . "|";
                    echo strtoupper($pago['tipo_pago']) . "|";
                    echo $pago["numero_documento"] . "|";
                    echo Misc::date_format($pago["fecha_documento"]) . "|";
                    echo Misc::number_format($pago["monto"]) . "|";
                    echo $pago["banco_origen"] . "|";
                    echo $pago["banco_destino"] . "|";
                    echo $pago["numero_cuenta"] . "|";
                    echo strtoupper($pago["estatus"]) . "|";
                    echo $pago["email"] . "|";
                    echo $enviado . "|";
                    echo $pago["telefono"] . "|";
                    // --
                    foreach ($pago_detalle['data'] as $value) {
                        echo $value['id_inmueble'] . "|";
                        echo $value['id_apto'] . "|";
                        echo Misc::number_format($value['monto']) . "|";
                        echo $value['id_factura'] . "|";
                        echo $value['periodo'] . "|";
                    }
                    echo "<br>";
                }
            }
        } else {
            echo "0";
        }
        break; 
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="confirmar">
    case "confirmar":

        $pago = new pago();
        $id = $_GET['id'];
        $estatus = $_GET['estatus'];
        $recibo = isset($_GET['recibo'])? $_GET['recibo']:null;
        $motivo = isset($_GET['motivo']) ? $_GET['motivo']:null;
        
        $r = $pago->procesarPago($id, $estatus,$recibo,$motivo);
        echo $r;
        break;
    
    // </editor-fold>
   
    // <editor-fold defaultstate="collapsed" desc="reenviar email registro pago">
    case "reenviarEmailRegistroPago":
        $pago = new pago();
        $id = $_GET['id'];
        $pago->enviarEmailPagoRegistrado($id);
        break; 
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="historico de pagos">
    case "historico":
        $propiedad = new propiedades();
        $pagos = new pago();
        $inmuebles = new inmueble();


        $propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);


        $historico = Array();
        if ($propiedades['suceed'] == true) {


            foreach ($propiedades['data'] as $propiedad) {
                $bitacora->insertar(Array(
                    "id_sesion" => $session['id_sesion'],
                    "id_accion" => 16,
                    "descripcion" => $propiedad['id_inmueble'] . " - " . $propiedad['apto'],
                ));

                $inmueble = $inmuebles->ver($propiedad['id_inmueble']);

                $p = $pagos->estadoDeCuenta($propiedad['id_inmueble'], $propiedad['apto']);
                
                if ($p['suceed'] == true) {

                    $historico[] = Array(
                        "inmueble" => $inmueble['data'][0],
                        "propiedad" => $propiedad,
                        "pagos" => $p['data']
                    );
                }
            }
        }
        echo $twig->render('enlinea/pago/historico.html.twig', array(
            "session" => $session,
            "propiedades" => $propiedad,
            "historicos" => $historico
        ));
        break; 
    // </editor-fold>
        
    // <editor-fold defaultstate="collapsed" desc="actualizar factura">
    case "actualizar_factura":


        if (isset($_GET['inmueble']) && isset($_GET['apto']) && isset($_GET['id']) && isset($_GET['monto'])) {


            $pagos = new pago();
            $r = $pagos->actualizarFactura($_GET['inmueble'], $_GET['apto'], $_GET['id'], $_GET['monto']);


            if (!$r['stats']['error'] == '') {
                echo $r['stats']['error'];
            }

        
        }
        break; 
    // </editor-fold>

}