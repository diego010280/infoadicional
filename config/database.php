<?php
session_status()=== PHP_SESSION_NONE ? session_start():null;

class Database {
    private $host = '192.168.0.199';
    private $username= 'root';
    private $password = 'KTdFHbEbKEcVWz4U';
    private $dbname = 'adi';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
            $this->conn->set_charset('utf8');
        } catch (Exception $e) {
            echo 'Fallo la conexion: '.$e->getMessage();
        }
        return $this->conn;
    }
    

}
?>