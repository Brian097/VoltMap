<?php
require_once __DIR__ . '/../../config/config.php';

$conexion = new mysqli($_ENV['DB_SERVER'], $_ENV['DB_USER'], $_ENV['DB_PASSW'], $_ENV['DB_NAME']);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

/*
    $conexion = new mysqli($host, $user, $pass, $db);

    const DB_SERVER="localhost"; //179.27.203.143
    const DB_NAME="prueba"; //voltmap
    const DB_USER="root"; //voltmap
    const DB_PASSW=""; //admin56237477


class conexion{
		protected function conectar(){
			$conexion = new PDO("mysql:host=".$this->server.";dbname=".$this->db,$this->user,$this->pass);
			$conexion->exec("SET CHARACTER SET utf8");
			return $conexion;
		}

		protected function ejecutarConsulta($consulta){
			$sql=$this->conectar()->prepare($consulta);
			$sql->execute();
			return $sql;
		}

    }
*/ 

?>

