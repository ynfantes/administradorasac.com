<?php
header ('Content-type: text/html; charset=utf-8');
include_once '../includes/constants.php';
usuario::esUsuarioLogueado();
$session = $_SESSION;
$id_inmueble = isset($_GET['inmueble']) ? $_GET['inmueble']:"";
$apto = isset($_GET['apto']) ? $_GET['apto']:"";
$propiedad = new propiedades();
$bitacora = new bitacora();
$propiedades = $propiedad->inmueblePorPropietario($session['usuario']['cedula']);
$ultimo_pago = array();
$aut=false;
if ($propiedades['suceed']) {
    $inmueble = new inmueble();
    $factura = new factura();
    $pago = new pago();
    foreach ($propiedades['data'] as $p) {
         
        if ($id_inmueble == $p['id_inmueble']) {
            $bitacora->insertar(Array(
                "id_sesion" => $session['id_sesion'],
                "id_accion" => 17,
                "descripcion" => $id_inmueble." - ".$apto,
            ));
            $inmueble = $inmueble->ver($id_inmueble);
            if ($inmueble['suceed'] && count($inmueble['data'])>0) {
                $inm = $inmueble['data'][0];
            }
            $periodo = $factura->ultimoPeriodoFacturado($id_inmueble);
            $pagos = $pago->estadoDeCuenta($id_inmueble, $apto);
            //var_dump($pagos);
            if ($pagos['suceed'] && count($pagos['data'])>0) {
                $ultimo_pago= $pagos['row'];
            }
            //var_dump($ultimo_pago);
            $aut = true;
            $fecha = getDate();
            $mes = Array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
            $fecha = $fecha["mday"]." de ".$mes[$fecha["mon"]]." de ".$fecha["year"];
            break;
        }
    }
}
if (!$aut) {
    die("Está tratando de ver una información que no está asociada a su cuenta de condominio.");
}
?>
<page>
<br /><br /><br /><br />
<img src="../images/logo-sac-solvencia.jpg" height="108" width="194" />
<br /><br /><br />
<br /><br /><br />
<div style="font-size: 140%; font-weight: bold; text-align: center;">SOLVENCIA DE CONDOMINIO</div>
<br /><br /><br /><br />
<p align="justify" style="line-height: 200%;">
Por medio de la presente, hacemos constar que el señor, <span style="font-weight: bold">
<?php echo $session['usuario']['nombre']?></span>,
propietario del Apartamento <span style="font-weight: bold"><?php echo $apto ?></span> 
en el condominio <span style="font-weight: bold"><?php echo $inm['nombre_inmueble'] ?></span>, 
se encuentra solvente de pago de condominio correpondiente al inmueble antes identificado.</p>
<p align="justify" style="line-height: 200%;">
    Ultima facturación emitida mes de <span style="font-weight: bold">
    <?php echo Misc::date_periodo_format($periodo) ?></span>.
    <?php        if (count($ultimo_pago)>0) { ?>
    Ultima cancelación realizada por el propietario mes
    <span style="font-weight: bold">
    <?php echo Misc::date_periodo_format($periodo)." (".Misc::date_format($ultimo_pago['fecha_movimiento']).")" ?></span>.
    <?php } ?>
</p>
<p align="justify" style="line-height: 200%;">Solvencia sujeta a la conformación
    ente el Banco del último pago realizado.</p>
<p align="justify" style="line-height: 200%;">
Constancia que expedimos a socilicitud de la parte interesada, en Caracas, en fecha 
    <?php echo $fecha ?></p><br /><br /><br /><br /><br /><br /><br /><br />
<div style="text-align: center;">
POR LA ADMINISTRADORA<br />
<br />
<br><br><br>
</div>
<page_footer style="text-align: center; font-size: 10px;">
<span style="font-size: 8px">
Para que esta solvencia de pago tenga validez, debe poseer la firma del presidente
con su respectivo sello húmedo, de la Junta De Condominio.</span>
<hr style="background-color: #000; height: 1px;" />
Av. Caurimare, Centro Comercial Caroní. Módulo A Piso 1 OFC.A-1. Colinas de Bello
Monte. Teléfonos:(0212)7519991 / 37.64/ 67.61 Fax:(0212)753.8111 - Venezuela
</page_footer>
</page>