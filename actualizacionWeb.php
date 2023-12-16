<?php
ini_set('max_execution_time', 600);

header ('Content-type: text/html; charset=utf-8');

include_once 'includes/db.php';
include_once 'includes/file.php';
include_once 'includes/inmueble.php';
include_once 'includes/junta_condominio.php';
include_once 'includes/propietario.php';
include_once 'includes/propiedades.php';
include_once 'includes/factura.php';

define("ENCODING", 'UTF-8');

$db = new db();

$tablas = [
    "factura_detalle", 
    "facturas", 
    "propiedades", 
    "propietarios", 
    "junta_condominio", 
    "inmueble", 
    "inmueble_deuda_confidencial",
    "movimiento_caja",
    "cancelacion_gastos"
];

if (isset($_GET['codinm'])) {
    $codinm = $_GET['codinm'];
    $db->exec_query("delete from factura_detalle where id_factura in (select numero_factura from facturas wher id_inmueble='".$codinm."')");
    $db->exec_query("delete from facturas where id_inmueble='".$codinm."'");
    $db->exec_query("delete from propietarios where cedula in (select cedula from propiedades where id_inmueble='".$codinm."')");
    $db->exec_query("delete from junta_condominio where id_inmueble='".$codinm."'");
    $db->exec_query("delete from propiedades where id_inmueble='".$codinm."'");
    $db->exec_query("delete from inmueble where id='".$codinm."'");
    $db->exec_query("delete from inmueble_deuda_confidencial where id_inmueble='".$codinm."'");
    $db->exec_query("delete from movimiento_caja where id_inmueble='".$codinm."'");
    $db->exec_query("delete from cancelacion_gastos where id_inmueble='".$codinm."'");
    $mensaje = "Actualización inmueble ".$codinm."<br>";
} else {
    $mensaje = "Proceso de Actualización Ejecutado<br />";
    foreach ($tablas as $tabla) {
        $r = $db->exec_query("truncate table " . $tabla);
        echo "limpiar tabla: " .$tabla."<br />";
    }
}

$archivo = ACTUALIZ . ARCHIVO_INMUEBLE;
if(file_exists($archivo)) {

    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    $inmueble = new inmueble();
    $mensaje.= "procesar archivo inmueble (".count($lineas).")<br />";
    echo $mensaje;
    
    foreach ($lineas as $linea) {
        
        $registro = explode("\t", $linea);
        if ($registro[0] != "") {
            
            $registro = [
                'id'                => $registro[0],            
                'nombre_inmueble'   => $registro[1],
                'deuda'             => $registro[2],
                'fondo_reserva'     => $registro[3],
                'beneficiario'      => $registro[4],
                'banco'             => '',
                'numero_cuenta'     => '',
                'supervision'       => '0',
                'RIF'               => $registro[5],
                'meses_mora'        => $registro[6],
                'porc_mora'         => $registro[7],
                'facturacion_usd'   => $registro[8],
                'tasa_cambio'       => $registro[9],
                'redondea_usd'      => $registro[10]
            ];
            
            $r = $inmueble->insertar($registro);
            
            if($r["suceed"]==FALSE){
                echo ARCHIVO_INMUEBLE."<br/>".$r['stats']['errno']." ".'<br/>'.$r['query'].'<br/>';
            }   
        }
    }

}


$archivo = ACTUALIZ . ARCHIVO_CUENTAS;
if (file_exists($archivo)) {

    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    $inmueble = new inmueble();
    $mensaje.= "actulizando cuentas inmuebles (" . count($lineas) . ")<br />";
    echo "actualizando cuentas inmuebles (" . count($lineas) . ")<br />";
    
    foreach ($lineas as $linea) {
        $id = '';
        $registro = explode("\t", $linea);
        
        if ($registro[0] != "") {
            $id=$registro[0];
            $registro = Array(
                "numero_cuenta" => $registro[1],
                "banco" => $registro[2]);
    
    
            $r = $inmueble->actualizar("'".$id."'",$registro);
            if ($r["suceed"] == FALSE) {
                //echo ARCHIVO_INMUEBLE."<br />".$r['stats']['errno']."<br />".$r['stats']['error'];
                echo $r['query'];
                die();
            }
    
        }
    }
}


$archivo = ACTUALIZ . ARCHIVO_JUNTA_CONDOMINIO;
if(file_exists($archivo)) {

    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    $junta_condominio = new junta_condominio();
    echo "procesar archivo Junta Condominio (".count($lineas).")<br />";
    $mensaje.= "procesar archivo Junta Condominio (".count($lineas).")<br />";
    foreach ($lineas as $linea) {
    
        $registro = explode("\t", $linea);
        
        if ($registro[0] != "") {
            $registro = Array("id_cargo" => $registro[1],
                "id_inmueble" => $registro[0],
                "cedula" => $registro[2]);
            $r = $junta_condominio->insertar($registro);
            
            if($r["suceed"]==FALSE){
                echo ARCHIVO_JUNTA_CONDOMINIO."<br />".$r['stats']['errno']."<br />".$r['stats']['error'];
            }
        }
    }

}


$archivo = ACTUALIZ . ARCHIVO_PROPIETARIOS;
if(file_exists($archivo)) {

    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    $propietario = new propietario();
    echo "procesar archivo Propietarios (".count($lineas).")<br />";
    $mensaje.= "procesar archivo Propietarios (".count($lineas).")<br />";
    foreach ($lineas as $linea) {
    
        $registro = explode("\t", $linea);
    
        if ($registro[0] != "") {
            
           $registro = [
                'nombre'            => mb_convert_encoding($registro[0], ENCODING),
                'clave'             => $registro[1],
                'email'             => $registro[2],
                'cedula'            => $registro[3],
                'telefono1'         => $registro[4],
                'telefono2'         => $registro[5],
                'telefono3'         => $registro[6],
                'direccion'         => mb_convert_encoding($registro[7], ENCODING),
                'recibos'           => $registro[8],
                'email_alternativo' => $registro[9]
           ];
           
           $r = $propietario->insertar($registro);
           
           if($r["suceed"]==FALSE){
                echo "<b>Archivo Propietario: ".$archivo.' - '.$r['stats']['errno']."-".$r['stats']['error']."</b>".'<br/>'.$r['query'].'<br/>';
                die($r['query']);
            }
        }
    }

}


$archivo = ACTUALIZ . ARCHIVO_PROPIEDADES;
if(file_exists($archivo)) {

    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    $propiedades = new propiedades();
    echo "procesar archivo Propiedades (".count($lineas).")<br />";
    $mensaje.= "procesar archivo Propiedades (".count($lineas).")<br />";
    foreach ($lineas as $linea) {
    
        $registro = explode("\t", $linea);
    
        if ($registro[0] != "") {
            
            $registro = [
                'cedula'            => $registro[0],
                'id_inmueble'       => $registro[1],
                'apto'              => $registro[2],
                'alicuota'          => $registro[3],
                'meses_pendiente'   => $registro[4],
                'deuda_total'       => $registro[5],
                'deuda_usd'         => str_replace("\r", "", $registro[6])
            ];
            
            $r = $propiedades->insertar($registro);
            if($r["suceed"]==FALSE){
                echo "<b>Archivo Propiedades: ".$r['stats']['errno']."-".$r['stats']['error']."</b><br />".'<br/>'.$r['query'].'<br/>';
            }
        }
    
    }

}


$archivo = ACTUALIZ . ARCHIVO_FACTURA;
if(file_exists($archivo)) {

    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    $facturas = new factura();
    echo "procesar archivo Facturas (".count($lineas).")<br />";
    $mensaje.= "procesar archivo Facturas (".count($lineas).")<br />";
    foreach ($lineas as $linea) {
        
        $registro = explode("\t", $linea);
        
        if ($registro[0] != "") {
            
            $registro = [
                'id_inmueble'       => $registro[0],
                'apto'              => $registro[1],
                'numero_factura'    => $registro[2],
                'periodo'           => $registro[3],
                'facturado'         => $registro[4],
                'abonado'           => $registro[5],
                'fecha'             => $registro[6],
                'facturado_usd'     => $registro[7]
            ];
            
            $r = $facturas->insertar($registro);
                    
            if(!$r["suceed"]){
                echo($r['stats']['errno']."-".$r['stats']['error'].'<br/>'.$r['query'].'<br/>');
            }
        }
    
    }

}


$archivo = ACTUALIZ . ARCHIVO_FACTURA_DETALLE;
if(file_exists($archivo)==true) {

    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo Detalle Factura (".count($lineas).")<br />";
    $mensaje.="procesar archivo Detalle Factura (".count($lineas).")<br />";
    foreach ($lineas as $linea) {

        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = Array(
                'id_factura'    => $registro[0],
                'detalle'       =>mb_convert_encoding($registro[1], ENCODING),
                'codigo_gasto'  => $registro[2],
                'comun'         => $registro[3],
                'monto'         => str_replace("\r","",$registro[4])
                    );
            
            $r = $facturas->insertar_detalle_factura($registro);
            
            if($r["suceed"]==FALSE){
                die($r['stats']['errno']."-".$r['stats']['error'].'<br/>'.$r['query'].'<br/>');
            }
        }
    }

    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo Detalle Factura (".count($lineas).")<br />";
    $mensaje.="procesar archivo Detalle Factura (".count($lineas).")<br />";
    foreach ($lineas as $linea) {

        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = Array(
                'id_factura'    => $registro[0],
                'detalle'       =>mb_convert_encoding($registro[1], ENCODING),
                'codigo_gasto'  => $registro[2],
                'comun'         => $registro[3],
                'monto'         => str_replace("\r","",$registro[4])
                    );
            
            $r = $facturas->insertar_detalle_factura($registro);
            
            if($r["suceed"]==FALSE){
                die($r['stats']['errno']."-".$r['stats']['error'].'<br/>'.$r['query'].'<br/>');
            }
        }
    }

}


$archivo = ACTUALIZ . ARCHIVO_MOVIMIENTO_CAJA;
if(file_exists($archivo)) {
    
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo movimiento caja (".count($lineas).")<br />";
    $mensaje.="procesar archivo movimiento caja (".count($lineas).")<br />";
    $pago = new pago();
    foreach ($lineas as $linea) {
    
        $registro = explode("\t", $linea);
    
        if ($registro[0] != "") {
    
            $registro = [
                'numero_recibo'     => $registro[0],
                'fecha_movimiento'  => $registro[1],
                'forma_pago'        => mb_convert_encoding($registro[2], ENCODING),
                'monto'             => $registro[3],
                'cuenta'            => mb_convert_encoding($registro[4], ENCODING),
                'descripcion'       => mb_convert_encoding($registro[5], ENCODING),
                'id_inmueble'       => $registro[6],
                'id_apto'           => str_replace("\r","",$registro[7])
            ];
    
            $r = $pago->insertarMovimientoCaja($registro);
    
    
            if ($r["suceed"] == FALSE) {
                die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
    
}


$archivo = ACTUALIZ . ARCHIVO_EDO_CTA_INM;
if(file_exists($archivo)) {

    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo estado de cuenta inmueble (".count($lineas).")<br />";
    $mensaje.="procesar archivo estado de cuenta inmueble (".count($lineas).")<br />";
    foreach ($lineas as $linea) {
    
        $registro = explode("\t", $linea);
    
        if ($registro[0] != "") {
    
            $registro = [
                'id_inmueble'   => $registro[0],
                'apto'          => $registro[1],
                'propietario'   => mb_convert_encoding($registro[2], ENCODING),
                'recibos'       => $registro[3],
                'deuda'         => $registro[4],
                'deuda_usd'     => str_replace("\r", "", $registro[5]
                )
            ];
    
            $r = $inmueble->insertarEstadoDeCuentaInmueble($registro);
    
            if ($r["suceed"] == FALSE) {
                die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
    
}


if (GRAFICO_FACTURACION == 1) {
    $archivo = ACTUALIZ . "FACTURACION_MENSUAL.txt";
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo grafico facturacion mensual (" . count($lineas) . ")<br />";
    $mensaje.="procesar archivo grafico facturación mensual (" . count($lineas) . ")<br />";
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = Array(
                "id_inmueble" => $registro[0],
                "periodo" => $registro[1],
                "facturado" => $registro[2]
            );

            $r = $inmueble->insertarFacturacionMensual($registro);

            if ($r["suceed"] == FALSE) {
                die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
}

if (GRAFICO_COBRANZA == 1) {
    $archivo = ACTUALIZ . "COBRANZA_MENSUAL.txt";
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo grafico cobranza mensual (" . count($lineas) . ")<br />";
    $mensaje.="procesar archivo grafico cobranza mensual (" . count($lineas) . ")<br />";
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = Array(
                "id_inmueble" => $registro[0],
                "periodo" => $registro[1],
                "monto" => $registro[2]
            );

            $r = $inmueble->insertarCobranzaMensual($registro);

            if ($r["suceed"] == FALSE) {
                die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
}


$archivo = ACTUALIZ . "CANCELACION_GASTOS.txt";
if(file_exists($archivo)) {

    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo cancelacion de gastos (".count($lineas).")<br />";
    $mensaje.="procesar archivo cancelacion de gastos (".count($lineas).")<br />";
    $pago = new pago();
    foreach ($lineas as $linea) {
    
        $registro = explode("\t", $linea);
    
        if ($registro[0] != "") {
    
            $registro = [
                "fecha_movimiento" => $registro[0],
                "monto" => $registro[1],
                "descripcion" => mb_convert_encoding($registro[2], ENCODING),
                "id_inmueble" => $registro[3],
                "id_apto" => $registro[4],
                "periodo" => $registro[5],
                "numero_factura" => str_replace("\r","",$registro[6])            
            ];
    
            $r = $pago->insertarCancelacionDeGastos($registro);
    
    
            if ($r["suceed"] == FALSE) {
                die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    
    }

}

$fecha = JFILE::read(ACTUALIZ."ACTUALIZACION.txt");
echo "****FIN DEL PROCESO DE ACTUALIZACION****<br />";
echo "Información actualizada al: ".$fecha."<br/>";
$mail = new mailto();
$r = $mail->enviar_email("Actualización Sac en Línea ".$fecha,$mensaje, "", 'administradorasac@gmail.com',"");
        
if ($r=="") {
    echo "Email de confirmación enviado con éxito<br />";
} else {
    echo "Falló el envio del emailo de ejecución del proceso<br />";
}
echo "Cierre esta ventana para finalizar.";