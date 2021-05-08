<?php
include_once 'includes/constants.php';

//echo $twig->render('mantenimiento.html.twig');
//die();
$propietario = new propietario();

$result = array();
$cedula = '';
$password = '';

if (isset($_POST['login'])) {
    
    $cedula = $_POST['cedula'];
    $password = $_POST['password'];
    $result = $propietario->login($cedula, $password, 0);
    echo json_encode($result);
    //die();
//    if ($result['suceed']) {
//        
//        if ($_SESSION['status'] == 'logueado') {
//            header("location:" . URL_SISTEMA );
//        }
//    }
    
} else {
    if (isset($_POST['cedula'])) {
        $result = $propietario->recuperarContraSena($_POST['cedula']);
        echo json_encode($result);
    } else {
        echo $twig->render('index2.html.twig', array("mensaje" => $result,"cedula"=>$cedula,"password"=>$password));
    }
}

