<?php
require_once __DIR__ . '/Cargador.php';

class PuntoCarga {
    public int $id;
    public bool $visible;
    public string $tipoUsuario;
    public float $latitud;
    public float $longitud;
    public string $direccion;
    public string $ciudadYDepartamento;
    /** @var Cargador[] */
    public array $cargadores = [];

    public function __construct(
        int $id,
        bool $visible,
        string $tipoUsuario,
        float $latitud,
        float $longitud,
        string $direccion,
        string $ciudadYDepartamento,
        array $cargadores = []
    ) {
        $this->id = $id;
        $this->visible = $visible;
        $this->tipoUsuario = $tipoUsuario;
        $this->latitud = $latitud;
        $this->longitud = $longitud;
        $this->direccion = $direccion;
        $this->ciudadYDepartamento = $ciudadYDepartamento;
        $this->cargadores = $cargadores;
    }

    public function estadisticas(): array {
        // Lógica para obtener estadísticas del punto de carga
        return [];
    }

    public function publicar(): bool {
        // Lógica para publicar el punto completo
        return true;
    }

    public function editar(): bool {
        // Lógica para editar el punto de carga
        return true;
    }

    public function borrar(): bool {
        // Lógica para borrar el punto de carga y sus conectores
        return true;
    }

    public function pausar(): bool {
        // Lógica para pausar el punto de carga
        return true;
    }

    public function reanudar(): bool {
        // Lógica para reanudar el punto de carga
        return true;
    }
}