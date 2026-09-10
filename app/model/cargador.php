<?php

class Cargador {
    public string $id;
    public int $idPuntoCarga;
    public float $potenciaKilowatts;
    public string $tipoConector;
    public string $tipoCargador;
    public string $estadoUso;
    public string $estadoOperativo;
    public float $precioKwh;
    public float $precioHora;

    public function __construct(
        string $id,
        int $idPuntoCarga,
        float $potenciaKilowatts,
        string $tipoConector,
        string $tipoCargador,
        string $estadoUso,
        string $estadoOperativo,
        float $precioKwh,
        float $precioHora
    ) {
        $this->id = $id;
        $this->idPuntoCarga = $idPuntoCarga;
        $this->potenciaKilowatts = $potenciaKilowatts;
        $this->tipoConector = $tipoConector;
        $this->tipoCargador = $tipoCargador;
        $this->estadoUso = $estadoUso;
        $this->estadoOperativo = $estadoOperativo;
        $this->precioKwh = $precioKwh;
        $this->precioHora = $precioHora;
    }

    public function publicar(): bool {
        // Lógica para publicar el cargador individual
        return true;
    }

    public function editar(): bool {
        // Lógica para editar el cargador
        return true;
    }

    public function borrar(): bool {
        // Lógica para eliminar el cargador
        return true;
    }

    public function pausar(): bool {
        // Lógica para pausar el cargador
        return true;
    }

    public function reanudar(): bool {
        // Lógica para reanudar el cargador
        return true;
    }
}