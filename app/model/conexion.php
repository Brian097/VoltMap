<?php

if(file_exists(__DIR__."/../../config/server.php")){
		require_once __DIR__."/../../config/server.php";
} else {
    echo "ERROR AL LEER config/server.php";
}

$conexion = new mysqli(DB_SERVER, DB_USER, DB_PASSW, DB_NAME);

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

