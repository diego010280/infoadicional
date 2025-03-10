<?php
require_once 'config/database.php';

class Autorizaciones {
	private $conn;
	
	public function __construct() {
		$database = new Database();
		$this->conn = $database->getConnection();
	}
	
	public function getAuthorization($pagina) {
		$pgname = basename($pagina);
		
		$query = 'SELECT permisos.permiso_ejec, permisos.permiso_alta,
			permisos.permiso_baja, permisos.permiso_mod, perfiles.perfil_EsAdm,
			autorizacion.autorizacion_organismos_id AS Aut_idorg, autorizacion_izq_der.GEN_Organismos_Izquierdo AS Aut_izq,
			autorizacion_izq_der.GEN_Organismos_Derecho AS Aut_der, autorizacion_izq_der.GEN_Organismos_Descripcion AS Desc_depend,
			objetos.objeto_id, perfiles.perfil_id, autorizacion_nivel
			FROM segusuario.autorizacion
			JOIN segusuario.perfiles ON perfiles.perfil_id = autorizacion.autorizacion_perfil_id
			JOIN segusuario.permisos ON permisos.permiso_perfil_id = perfiles.perfil_id
			JOIN segusuario.objetos ON objetos.objeto_id = permisos.permiso_objeto_id
			JOIN segusuario.autorizacion_izq_der ON autorizacion_izq_der.Organismo_id = autorizacion.autorizacion_organismos_id
			WHERE autorizacion.autorizacion_usuario_id = ?
			AND perfiles.perfil_activo = 1
			AND objetos.objeto_link LIKE ?
			AND objetos.objeto_modulo_id = ?
			ORDER BY autorizacion_izq_der.GEN_Organismos_Izquierdo';
		
		$stmt = $this->conn->prepare($query);
        // Enlazar parámetros (s = string, i = integer)
		$stmt->bind_param('isi', $_SESSION['usuario_id'], $pgname, $_SESSION['moduleid']);
		$stmt->execute();
		
		return $stmt->get_result();
	}
}