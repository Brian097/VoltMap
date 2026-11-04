<?php

class Administrador extends Usuario {
    public function __construct($id, $email, $nombre, $contraseña, $seudonimo, $fotoPerfil, $estado) {
        parent::__construct($id, $email, $nombre, $contraseña, $seudonimo, $fotoPerfil, $estado);
    }

    public function promoverUsuario() {
    }

    public function consultarEstadistica() {
    }

    public function configurarComision() {
    }

    public function Registrar($conexion){}

}

?>