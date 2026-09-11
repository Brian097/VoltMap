<?php

abstract class Usuario {
    protected $id;
    protected $email;
    protected $nombre;
    protected $contraseña;
    protected $seudonimo;
    protected $fotoPerfil;
    protected $estado;

    public function __construct($id, $email, $nombre, $contraseña, $seudonimo, $fotoPerfil, $estado) {
        $this->id = $id;
        $this->email = $email;
        $this->nombre = $nombre;
        $this->contraseña = $contraseña;
        $this->seudonimo = $seudonimo;
        $this->fotoPerfil = $fotoPerfil;
        $this->estado = $estado;
    }

    // Getters y Setters básicos
    public function getEmail() { return $this->email; }
    public function getNombre() { return $this->nombre; }
    public function getContraseña() { return $this->contraseña; }

    abstract public function Registrar($conexion);
}
?>