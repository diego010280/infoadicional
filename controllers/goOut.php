<?php
class goOut {
	public function logout() {
        session_status() === PHP_SESSION_NONE ? session_start() : null;
		
		$_SESSION = array();
		session_destroy();

		header('Location: /acceso/login');
		exit;
    }
	
	public function salir() {
		session_status() === PHP_SESSION_NONE ? session_start() : null;
		
		if ($_SESSION['start'] < time() - Tiempo::$tiempoespera) {
			
			$_SESSION = array();
			session_destroy();

			header('Location: /acceso/login?access=timeout');
			exit;
				
		} else {
			header('Location: /acceso/segacceso/checks');
			exit;
		}
	}
}