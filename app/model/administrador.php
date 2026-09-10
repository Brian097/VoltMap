<?php

class Administrador extends Usuario {
    public function __construct($id, $email, $nombre, $contraseña, $seudonimo, $fotoPerfil, $estado) {
        parent::__construct($id, $email, $nombre, $contraseña, $seudonimo, $fotoPerfil, $estado);
    }

    public function promoverUsuario() {
        // Lógica para promover usuario
    }

    public function consultarEstadistica() {
        // Lógica para consultar estadística
    }

    public function configurarComision() {
        // Lógica para configurar comisión
    }

    public function Registrar($conexion){}

}

?>