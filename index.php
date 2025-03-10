<?php
require_once 'core/Router.php';
require_once 'controllers/goOut.php';
require_once 'controllers/mainController.php';
require_once 'models/Autorizaciones.php';
require_once 'config/tiempo.php';

$goout = new goOut();

Router::page('extension');

$action = Router::page('action');

switch ($action) {
	case 'logout':
		$goout->logout();
		break;
	case 'salir':
		$goout->salir();
		break;
	default:
		Router::route($action);
}
?>