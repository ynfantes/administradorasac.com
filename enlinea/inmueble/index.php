<?php
include_once '../../includes/constants.php';
include_once '../../includes/file.php';

$propietario = new propietario();

$propietario->esPropietarioLogueado();

$inmuebles = new inmueble();
$propiedad = new propiedades();
$bitacora = new bitacora();
$facturas = new factura();

$accion = isset($_GET['accion']) ? $_GET['accion'] : "listar";
$session = $_SESSION;

$cuenta = Array();
$factura = null;
$cobro = null;
$monto = 0;
$total = 0;
$promedio_facturacion = 0;

$monto_c = 0;
$total_c = 0;
$promedio_cobranza = 0;
$i = 0;
$n = 0;
$direccion_facturacion = "right";
$direccion_cobranza = "right";

//$propiedades = $propiedad->inmueblePorPropietario($_SESSION['usuario']['cedula']);
$propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);

if ($propiedades['suceed']) {
    
     for ($index = 0; $index < count($propiedades['data']); $index++) {
         $inmueble = $inmuebles->ver($propiedades['data'][$index]['id_inmueble']);
         $propiedades["data"][$index]["inmueble"] = $inmueble['data'][0];
     }
    
    $facturacion = $inmuebles->movimientoFacturacionMensual($propiedades['data'][0]['id_inmueble']);
    $cobranza = $inmuebles->movimientoCobranzaMensual($propiedades['data'][0]['id_inmueble']);
    
    if ($facturacion['suceed']) {

        foreach ($facturacion['data'] as $r) {
            $i++;
            $direccion_facturacion = $r['facturado'] > $monto ? "up" : "down";
            $monto = $r['facturado'];
            $total += $monto;
            $factura .= $factura != '' ? ',' : '';
            $factura .= (int) $r['facturado'];
        }
        if($i>0) { $promedio_facturacion = (int) ($total / $i); };
    }
    
    if ($cobranza['suceed']) {
        
        foreach ($cobranza['data'] as $c) {
            $n++;
            $direccion_cobranza = $c['monto'] > $monto_c ? "up" : "down";
            $monto_c = $c['monto'];
            $total_c += $monto_c;
            $cobro .= $cobro != '' ? ',' : '';
            $cobro .= (int)$c['monto'];
        }
        if ($n>0) {$promedio_cobranza = (int)($total_c / $n);}
        
    }
    
}

switch ($accion) {
    
    case "junta-condominio": default :
        $junta_condominio = new junta_condominio();
        $propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);
        
        $miembros = Array();

        if ($propiedades['suceed'] == true) {
            $id_inmueble = "";
            foreach ($propiedades['data'] as $propiedad) {
                
                $bitacora->insertar(Array(

                    'id_sesion'     => $session['id_sesion'],
                    'id_accion'     => 4,
                    'descripcion'   => $propiedad['id_inmueble']." - ".$propiedad['apto']

                ));
                
                if ($propiedad['id_inmueble'] != $id_inmueble) {

                    $id_inmueble    = $propiedad['id_inmueble'];
                    $inmueble       = $inmuebles->ver($id_inmueble);
                    $junta          = $junta_condominio->listarJuntaPorInmueble($id_inmueble);
                    
                        if ($junta['suceed'] && count($junta['data'])>0) {
                            
                            for ($index = 0; $index < count($junta['data']); $index++) {

                                $prop = $propietario->obtenerDatosPropietario($junta['data'][$index]['cedula']);
                                $junta['data'][$index]['propietario']=$prop['data'][0];

                            }
                            $miembros[] = Array(

                                'inmueble' => $inmueble['data'][0],
                                'miembros' => $junta['data']

                            );
                        }

                }
            }
        }
        
        echo $twig->render('enlinea/inmueble/formulario.html.twig', array("session" => $session,
            'junta'                   => $miembros,
            'movimiento_facturacion'  => $factura,
            'promedio_facturacion'    => $promedio_facturacion,
            'direccion_facturacion'   => $direccion_facturacion,
            'movimiento_cobranza'     => $cobro,
            'promedio_cobranza'       => $promedio_cobranza,
            'direccion_cobranza'      => $direccion_cobranza
        ));
        
        break; 

    case "cuenta":
        $propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);
        $id_inmueble = "";
        if ($propiedades['suceed'] == true) {
            $cuentas = Array();
            
            foreach ($propiedades['data'] as $propiedad) {
                
                $bitacora->insertar(Array(
                    "id_sesion"=>$session['id_sesion'],
                    "id_accion"=>6,
                    "descripcion"=>$propiedad['id_inmueble']." - ".$propiedad['apto'],
                ));
                $legal = false;
                if ($id_inmueble!=$propiedad['id_inmueble'] && $propiedad['id_inmueble']==$session['junta']) {
                    if ($propiedad['meses_pendiente'] > MESES_COBRANZA) {
                        $legal = true;
                    }
                    $id_inmueble=$propiedad['id_inmueble'];
                    $inm = $inmuebles->ver($propiedad['id_inmueble']);
                    $cuenta = $inmuebles->estadoDeCuenta($propiedad['id_inmueble']);
                    $cuentas[] = Array("inmueble" => $inm['data'][0], "cuenta" => $cuenta['data'],"legal"=>$legal);
                    
                }
            }
        }


        echo $twig->render('enlinea/inmueble/estado-de-cuenta.html.twig', array("session" => $session,
            "cuentas" => $cuentas,
            "movimiento_facturacion" => $factura,
            "promedio_facturacion" => $promedio_facturacion,
            "direccion_facturacion" => $direccion_facturacion,
            "movimiento_cobranza" => $cobro,
            "promedio_cobranza" => $promedio_cobranza,
            "direccion_cobranza" => $direccion_cobranza
        ));
        break; // </editor-fold>

    case "facturacionflot":
        
        $propiedades = $propiedad->inmueblePorPropietario($_SESSION['usuario']['cedula']);
        
        if ($propiedades['suceed']) {
            //foreach ($propiedades['data'] as $p) {
                $facturacion = $inmuebles->movimientoFacturacionMensual($_GET['id']);
                $rows = array();
                $table = array();
                if ($facturacion['suceed'] && count($facturacion['data'])>0) {
                    $table['label'] = $facturacion['data'][0]['nombre_inmueble'];
                    //$i = 0;
                    foreach ($facturacion['data'] as $r) {
                        //$i++;
                        $rows[] = array((string) Misc::date_periodo_format($r['periodo']), $r['facturado'] / 1000);
                    }
                }
                $table['data'] = $rows;
                $jsonTable = json_encode($table);

                echo $jsonTable;
            //}
        }
        break; 
    
    case "cobranzaflot":
        
        $propiedades = $propiedad->inmueblePorPropietario($_SESSION['usuario']['cedula']);
        
        if ($propiedades['suceed']) {
            //foreach ($propiedades['data'] as $p) {
                $cobranza = $inmuebles->movimientoCobranzaMensual($_GET['id']);
                $rows = array();
                $table = array();
                //$i = 1;
                if ($cobranza['suceed'] && count($cobranza['data'])>0) {
                    $table['label'] = $cobranza['data'][0]['nombre_inmueble'];
                    foreach ($cobranza['data'] as $r) {
                        //$i++;
                        $rows[] = array((string) Misc::date_periodo_format($r['periodo']), $r['monto'] / 1000);
                    }
                    $table['data'] = $rows;
                }
                $jsonTable = json_encode($table);

                echo $jsonTable;
            //}
        }
        break; 

    
    case "cartelera":
        $archivo = '../../' . ACTUALIZ . ARCHIVO_ACTUALIZACION;
        $fecha_actualizacion = JFile::read($archivo);
        
        $bitacora->insertar(Array(
            "id_sesion"=>$session['id_sesion'],
            "id_accion"=> 11,
            "descripcion"=>$fecha_actualizacion,
        ));
        echo $twig->render('enlinea/inmueble/cartelera.html.twig', array(
            "session" => $session,
            "propiedades" => $propiedades['data'],
            "fecha_actualizacion" => $fecha_actualizacion,
            "movimiento_facturacion" => $factura,
            "promedio_facturacion" => $promedio_facturacion,
            "direccion_facturacion" => $direccion_facturacion,
            "movimiento_cobranza" => $cobro,
            "promedio_cobranza" => $promedio_cobranza,
            "direccion_cobranza" => $direccion_cobranza,
            "inmuebles" => $propiedades['data']
        ));
        break; 
    
    case "imprimircuenta":
        break;
}
