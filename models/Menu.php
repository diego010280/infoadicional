<?php
require_once 'config/database.php';

class Menu {
	private $conn;
	
	public function __construct() {
		$database = new Database();
		$this->conn = $database->getConnection();
	}
	
	public function getUserMenu($id, $modulo) {
		$query = "SELECT DISTINCT(objetos.objeto_id) IdObjeto, objetos.objeto_descrip, permiso_ejec, objetos.objeto_link
			FROM segusuario.autorizacion
			JOIN segusuario.perfiles ON perfiles.perfil_id = autorizacion.autorizacion_perfil_id
			JOIN segusuario.objetos ON objetos.objeto_modulo_id = perfiles.perfil_modulo_id
			JOIN segusuario.permisos ON permisos.permiso_perfil_id = perfiles.perfil_id
				AND permisos.permiso_objeto_id = objetos.objeto_id
			WHERE autorizacion.autorizacion_usuario_id = ?
			AND perfiles.perfil_modulo_id = ?
			AND objetos.objeto_activo = 1
			AND objetos.objeto_titulo = 1
			AND (objetos.objeto_padre IS NULL or objetos.objeto_padre = 0)
			AND objetos.objeto_vercomo IN (1,3)
			GROUP BY IdObjeto";
		
		$stmt = $this->conn->prepare($query);
		$stmt->bind_param('ii', $id, $modulo);
		$stmt->execute();
		$opciones = $stmt->get_result();
		
		$query = "SELECT DISTINCT(objetos.objeto_id) IdObjeto, objetos.objeto_link, objetos.objeto_descrip
			FROM segusuario.autorizacion
			JOIN segusuario.perfiles ON autorizacion.autorizacion_perfil_id = perfiles.perfil_id
			JOIN segusuario.permisos ON perfiles.perfil_id = permisos.permiso_perfil_id
			JOIN segusuario.objetos ON permisos.permiso_objeto_id = objetos.objeto_id
			WHERE autorizacion.autorizacion_usuario_id = ?
			AND perfiles.perfil_modulo_id = ?
			AND permisos.permiso_ejec = 1
			AND objetos.objeto_activo = 1
			AND objetos.objeto_titulo = 1
			AND objetos.objeto_padre = ?
			AND objetos.objeto_vercomo IN (1,3)";

		$stmt = $this->conn->prepare($query);
		
		$menus = array();
		
		while ($opcion = $opciones->fetch_assoc()) {
			
			$items = array();
			
			$stmt->bind_param('iii', $id, $modulo, $opcion['IdObjeto']);
			$stmt->execute();
			$itemsMenu = $stmt->get_result();
			
			while ($itemMenu = $itemsMenu->fetch_assoc()) {
				$items[] = ['name' => $itemMenu['objeto_link'], 'descrip' => $itemMenu['objeto_descrip']];
			}
						
			$menus[] = ['name' => $opcion['objeto_link'],
						'descrip' => $opcion['objeto_descrip'],
						'items' => $items];
		}
		return $menus;
	}
}
?>