<?php
/**
 * Cálculos puros de física para la simulación de carga de los vehículos
 */

/** Eficiencia de carga η (0 < η <= 1). En el documento de Física usamos η = 1 */
const EFICIENCIA_CARGA = 1.0;

/** potencia real = la menor entre la del cargador y la que acepta el vehículo. */
function potenciaEfectivaKw(float $pCargadorKw, float $pMaxVehiculoKw): float
{
    if ($pMaxVehiculoKw <= 0) {
        return max(0.0, $pCargadorKw);
    }
    return max(0.0, min($pCargadorKw, $pMaxVehiculoKw));
}

function energiaNecesariaKwh(float $capacidadKwh, float $socInicial, float $socFinal): float
{
    $socInicial = max(0.0, min(100.0, $socInicial));
    $socFinal   = max(0.0, min(100.0, $socFinal));
    return max(0.0, ($socFinal - $socInicial) / 100 * $capacidadKwh);
}

/** tiempo de carga en minutos: t(h) = E / (P × η), t(min) = t(h) × 60. Devuelve null si P = 0. */
function tiempoCargaMinutos(
    float $capacidadKwh, float $socInicial, float $socFinal,
    float $pCargadorKw, float $pMaxVehiculoKw, float $eficiencia = EFICIENCIA_CARGA
): ?float {
    $p = potenciaEfectivaKw($pCargadorKw, $pMaxVehiculoKw);
    if ($p <= 0 || $eficiencia <= 0) {
        return null;
    }
    $e = energiaNecesariaKwh($capacidadKwh, $socInicial, $socFinal);
    return ($e / ($p * $eficiencia)) * 60;
}

/** la distancia en metros entre dos coordenadas (fórmula de Haversine). */
function distanciaMetros(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $r = 6371000.0;
    $f1 = deg2rad($lat1);
    $f2 = deg2rad($lat2);
    $df = deg2rad($lat2 - $lat1);
    $dl = deg2rad($lng2 - $lng1);
    $a = sin($df / 2) ** 2 + cos($f1) * cos($f2) * sin($dl / 2) ** 2;
    return 2 * $r * asin(min(1.0, sqrt($a)));
}

/**
 * energía entregada (kWh) en una carga simulada de $minutos.
 * Nunca supera la capacidad de la batería del vehículo (si se conoce).
 */
function kwhEntregados(float $minutos, float $pCargadorKw, float $pMaxVehiculoKw, ?float $capacidadKwh = null): float
{
    $kwh = potenciaEfectivaKw($pCargadorKw, $pMaxVehiculoKw) * ($minutos / 60);
    if ($capacidadKwh !== null && $capacidadKwh > 0) {
        $kwh = min($kwh, $capacidadKwh);
    }
    return round($kwh, 2);
}

/** Monto total: si el cargador tiene precio por kWh se usa ese; si no, el precio por hora. */
function calcularMonto(float $precioKwh, float $precioHora, float $kwh, float $minutos): float
{
    $total = $precioKwh > 0 ? $kwh * $precioKwh : ($minutos / 60) * $precioHora;
    return round(max(0.0, $total), 2);
}

/** Reparte el monto: comisión para la plataforma y resto para el propietario (suman exactamente el total). */
function repartirMonto(float $total, float $comisionPct): array
{
    $comisionPct = max(0.0, min(100.0, $comisionPct));
    $comision    = round($total * $comisionPct / 100, 2);
    return [
        'total'       => round($total, 2),
        'comision'    => $comision,
        'propietario' => round($total - $comision, 2),
    ];
}

/** Validación Luhn de número de tarjeta (solo para la simulación). */
function tarjetaValidaLuhn(string $numero): bool
{
    $n = preg_replace('/\D/', '', $numero);
    if (strlen($n) < 13 || strlen($n) > 19) {
        return false;
    }
    $suma = 0;
    $alt = false;
    for ($i = strlen($n) - 1; $i >= 0; $i--) {
        $d = (int)$n[$i];
        if ($alt) {
            $d *= 2;
            if ($d > 9) {
                $d -= 9;
            }
        }
        $suma += $d;
        $alt = !$alt;
    }
    return $suma % 10 === 0;
}

/** Vencimiento MM/AA o MM/AAAA: válido si es un mes real y no está vencido. */
function vencimientoValido(string $mmaa, ?DateTimeImmutable $hoy = null): bool
{
    if (!preg_match('#^(0[1-9]|1[0-2])\s*/\s*(\d{2}|\d{4})$#', trim($mmaa), $m)) {
        return false;
    }
    $anio = (int)$m[2];
    if ($anio < 100) {
        $anio += 2000;
    }
    $hoy = $hoy ?? new DateTimeImmutable('now');
    $finDeMes = (new DateTimeImmutable(sprintf('%04d-%02d-01', $anio, (int)$m[1])))->modify('last day of this month');
    return $finDeMes->format('Y-m-d') >= $hoy->format('Y-m-d');
}
