<?php
$archivo='';
ob_start();

include(dirname(__FILE__).'/solvenciaDeCondominio.php');
$content = ob_get_clean();

require_once('../includes/html2pdf/html2pdf.class.php');

try
{
    $html2pdf = new HTML2PDF('P', 'Letter', 'fr',true,'UTF-8',array(20, 10, 20, 10));
//    $html2pdf->setModeDebug();
    $html2pdf->pdf->SetDisplayMode('fullpage');
    $html2pdf->setDefaultFont('arial');
    $html2pdf->writeHTML($content);
    $html2pdf->Output('SolvenciaDeCondominio.pdf');
}

catch(HTML2PDF_exception $e) {
    echo $e;
    exit;
}
