<?php
use Spipu\Html2Pdf\Html2Pdf;

$archivo='';
ob_start();

include(dirname(__FILE__).'/solvenciaDeCondominio.php');
$content = ob_get_clean();

try
{
    $html2pdf = new Html2Pdf('P', 'Letter', 'fr',true,'UTF-8',array(20, 10, 20, 10));
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
