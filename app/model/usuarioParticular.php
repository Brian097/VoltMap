<?php
class UsuarioParticular extends Usuario {
    private $cedulaldentidad;
    private $fotoCedula;

    public function __construct($id, $email, $nombre, $contraseña, $seudonimo, $fotoPerfil, $estado, $cedulaldentidad, $fotoCedula) {
        parent::__construct($id, $email, $nombre, $contraseña, $seudonimo, $fotoPerfil, $estado);
        $this->cedulaldentidad = $cedulaldentidad;
        $this->fotoCedula = $fotoCedula;
    }

    // Getters
    public function getCedulaldentidad() { return $this->cedulaldentidad; }
    public function getFotoCedula() { return $this->fotoCedula; }

    // Setters
    public function setCedulaldentidad($cedulaldentidad) { $this->cedulaldentidad = $cedulaldentidad; }
    public function setFotoCedula($fotoCedula) { $this->fotoCedula = $fotoCedula; }

    public function agregarVehiculo() {
        // Lógica para agregar vehículo
    }

    public function reservar() {
        // Lógica para reservar
    }

    public function cancelarReserva() {
        // Lógica para cancelar reserva
    }

    public function calificar() {
        // Lógica para calificar
    }

    public function Registrar($conexion) {
        $tipo = 'particular';
        $stmt = $conexion->prepare("INSERT INTO usuarios (tipo_usuario, nombre, correo, password, seudonimo, foto_perfil, estado, cedula_identidad, foto_cedula) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        // Corregido aquí: de $this->cedulaIdentidad a $this->cedulaldentidad
        $stmt->bind_param("sssssssss", $tipo, $this->nombre, $this->email, $this->contraseña, $this->seudonimo, $this->fotoPerfil, $this->estado, $this->cedulaldentidad, $this->fotoCedula);
        return $stmt;
    }
}
?>