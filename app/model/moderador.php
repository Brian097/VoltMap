<?php

class Moderador extends Usuario {
    public function __construct($id, $email, $nombre, $contraseña, $seudonimo, $fotoPerfil, $estado) {
        parent::__construct($id, $email, $nombre, $contraseña, $seudonimo, $fotoPerfil, $estado);
    }

    public function validarReporte() {
        // Lógica para validar reporte
    }

    public function calificarReporte() {
        // Lógica para calificar reporte
    }

    public function Registrar($conexion){

    }
    
    

}

?>