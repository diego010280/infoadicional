<?php
require_once 'config/database.php';

class Cuenta {
	private $conn;
	
	public function __construct() {
		$database = new Database();
		$this->conn = $database->getConnection();
	}
	
	public function getPerfil($id) {
		$query = 'SELECT usuario_apellido, usuario_nombre, usuario_avatar FROM segusuario.usuarios WHERE usuario_id = ?';
		$stmt = $this->conn->prepare($query);
		$stmt->bind_param('i', $id);
		$stmt->execute();
		return $stmt->get_result()->fetch_assoc();
	}

}
?>