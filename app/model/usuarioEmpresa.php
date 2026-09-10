<?php

class UsuarioEmpresa extends Usuario {
    private $rut;

    public function __construct($id, $email, $nombre, $contraseña, $seudonimo, $fotoPerfil, $estado, $rut) {
        parent::__construct($id, $email, $nombre, $contraseña, $seudonimo, $fotoPerfil, $estado);
        $this->rut = $rut;
    }

    // Getters y Setters
    public function getRut() { return $this->rut; }
    public function setRut($rut) { $this->rut = $rut; }

    public function publicarCargador() {
        // Lógica para publicar cargador
    }

    public function gestionarCargadores() {
        // Lógica para gestionar cargadores
    }

    public function consultarEstadistica() {
        // Lógica para consultar estadística
    }

    public function Registrar($conexion) {
        $tipo = 'empresa';
        $stmt = $conexion->prepare("INSERT INTO usuarios (tipo_usuario, nombre, correo, password, seudonimo, foto_perfil, estado, rut) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $tipo, $this->nombre, $this->email, $this->contraseña, $this->seudonimo, $this->fotoPerfil, $this->estado, $this->rut);
        return $stmt;
    }
}

?>