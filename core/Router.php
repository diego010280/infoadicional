<?php
class Router {
    public static $allowed = array();

    public function permisos($pagina) {
		if (isset($_SESSION['logged'], $_SESSION['moduleid'])) {
			
			$consulta = false;
			$alta = false;
			$baja = false;
			$modifica = false;
			
			$autorizaciones = new Autorizaciones();
			$resultados = $autorizaciones->getAuthorization($pagina);
			
			if ($resultados->num_rows > 0) {
			
				while ($resultado = $resultados->fetch_assoc()) {
					if ($resultado['permiso_ejec'] == 1) $consulta = true; else break;
					if ($resultado['permiso_alta'] == 1) $alta = true;
					if ($resultado['permiso_baja'] == 1) $baja = true;
					if ($resultado['permiso_mod'] == 1)  $modifica = true;
				}
				
				if (!$consulta) {
					header('Location: /acceso/login?access=private');
					exit;
				}
				self::$allowed['abmConsulta'] = $consulta;
				self::$allowed['abmAlta'] = $alta;
				self::$allowed['abmBaja'] = $baja;
				self::$allowed['abmModifica'] = $modifica;
			
			} else {
				header('Location: /acceso/login?access=private');
				exit;
			}
			
		} else {
			header('Location: /acceso/login?access=private');
			exit;
		}
	}
	
	public function ctrlTime() {
		if (empty($_SESSION['logged']) or !is_numeric($_SESSION['moduleid']) or ($_SESSION['moduleid'] < 1)) {

			header('Location:../acceso/login?access=private');
			exit;

		} else {
			if ($_SESSION['start'] < time() - Tiempo::$tiempoespera) {
				
				$_SESSION = array();
				session_destroy();
				
				header('Location: ../acceso/login?access=timeout');
				exit;
				
			}
			$_SESSION['start'] = time();
		}
	}
	
	public static function page($operation) {
		switch ($operation) {
			case 'extension':
				$uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
				$path = parse_url($uri, PHP_URL_PATH);
			
				if (pathinfo($path, PATHINFO_EXTENSION) !== '') {
					echo 'El archivo no existe';
					exit; 
				}
				break;
			case 'scriptname':
				return ($_GET['page'] ?? 'oodd').'.php';
			case 'action':
				return $_GET['action'] ?? '';
		}
	}
	
	public static function route($action) {
		$controller = new mainController();
		$page = self::page('scriptname');
		self::permisos($page);
		self::ctrlTime();
		
		switch ($action) {
			case 'menuPerfil':
				$controller->menuPerfil();
				break;
			case 'search':
				$controller->searchResults(self::$allowed, $page, $_POST);
				break;
			case 'filtro':
				$controller->searchResults(self::$allowed, $page);
				break;
			case 'crud':
				$controller->crud(self::$allowed, $page, $_POST);
				break;
			case 'save':
				$controller->save($page, $_POST);
				break;
			default:
				$controller->inicio(self::$allowed);
		}
	}

}
?>