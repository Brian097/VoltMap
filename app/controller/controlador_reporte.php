<?php
/**
 * POST (JSON) -> registra un reporte de la comunidad sobre un cargador.
 * Cuerpo: { cargador_id, tipo: operativo|ocupacion|fila, valor, lat, lng, vehiculo_id?, soc? }
 * Cabecera: X-CSRF-Token
 */
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../model/conexion.php';
require_once __DIR__ . '/../model/calculos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    vm_json(['ok' => false, 'error' => 'metodo'], 405);
}
$uid = vm_usuario_id();
if (!$uid) {
    vm_json(['ok' => false, 'error' => 'auth'], 401);
}
if (!vm_csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    vm_json(['ok' => false, 'error' => 'csrf'], 403);
}

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$cargadorId = trim((string)($in['cargador_id'] ?? ''));
$tipo  = (string)($in['tipo'] ?? '');
$valor = trim((string)($in['valor'] ?? ''));

$permitidos = [
    'operativo' => ['funcionando', 'fuera_de_servicio'],
    'ocupacion' => ['en_uso', 'disponible'],
];
if ($cargadorId === '' || !in_array($tipo, ['operativo', 'ocupacion', 'fila'], true)) {
    vm_json(['ok' => false, 'error' => 'datos'], 422);
}
if ($tipo === 'fila') {
    if (!ctype_digit($valor) || (int)$valor > 20) {
        vm_json(['ok' => false, 'error' => 'fila_invalida'], 422);
    }
} elseif (!in_array($valor, $permitidos[$tipo], true)) {
    vm_json(['ok' => false, 'error' => 'valor_invalido'], 422);
}

// 1) El cargador existe y obtenemos sus coordenadas
$st = $conexion->prepare(
    "SELECT c.id, c.potenciaKilowatts, p.latitud, p.longitud
       FROM cargadores c JOIN puntos_carga p ON p.id = c.idPuntoCarga
      WHERE c.id = ?"
);
$st->bind_param('s', $cargadorId);
$st->execute();
$cargador = $st->get_result()->fetch_assoc();
$st->close();
if (!$cargador) {
    vm_json(['ok' => false, 'error' => 'no_existe'], 404);
}

// 2) Solo puede reportar quien está en el punto de carga (radio configurable en .env; 0 = desactivado)
$radio = (float)($_ENV['REPORTE_RADIO_METROS'] ?? 300);
if ($radio > 0) {
    if (!isset($in['lat'], $in['lng']) || !is_numeric($in['lat']) || !is_numeric($in['lng'])) {
        vm_json(['ok' => false, 'error' => 'sin_ubicacion'], 403);
    }
    $dist = distanciaMetros((float)$in['lat'], (float)$in['lng'], (float)$cargador['latitud'], (float)$cargador['longitud']);
    if ($dist > $radio) {
        vm_json(['ok' => false, 'error' => 'lejos', 'distancia_m' => (int)round($dist), 'radio_m' => (int)$radio], 403);
    }
}

// 3) Anti-spam: un mismo usuario no repite el mismo tipo de reporte en 60 s
$st = $conexion->prepare(
    "SELECT COUNT(*) FROM reportes
      WHERE cargador_id = ? AND usuario_id = ? AND tipo = ? AND fecha > (NOW() - INTERVAL 60 SECOND)"
);
$st->bind_param('sis', $cargadorId, $uid, $tipo);
$st->execute();
$st->bind_result($recientes);
$st->fetch();
$st->close();
if ($recientes > 0) {
    vm_json(['ok' => false, 'error' => 'demasiado_rapido'], 429);
}

// 4) No se puede marcar "disponible" un cargador con una carga en curso reservada en VoltMap
if ($tipo === 'ocupacion' && $valor === 'disponible') {
    $st = $conexion->prepare("SELECT COUNT(*) FROM reservas WHERE cargador_id = ? AND estado IN ('en_curso','pendiente')");
    $st->bind_param('s', $cargadorId);
    $st->execute();
    $st->bind_result($activas);
    $st->fetch();
    $st->close();
    if ($activas > 0) {
        vm_json(['ok' => false, 'error' => 'reservado'], 409);
    }
}

// 5) Aplicar el reporte
$conexion->begin_transaction();
try {
    $st = $conexion->prepare("INSERT INTO reportes (cargador_id, usuario_id, tipo, valor) VALUES (?, ?, ?, ?)");
    $st->bind_param('siss', $cargadorId, $uid, $tipo, $valor);
    $st->execute();
    $st->close();

    if ($tipo === 'operativo') {
        $nuevo = $valor === 'funcionando' ? 'Operativo' : 'Fuera de servicio';
        $st = $conexion->prepare("UPDATE cargadores SET estadoOperativo = ?, ultimo_reporte = NOW() WHERE id = ?");
        $st->bind_param('ss', $nuevo, $cargadorId);
    } elseif ($tipo === 'fila') {
        $n = (int)$valor;
        $st = $conexion->prepare("UPDATE cargadores SET fila_espera = ?, ultimo_reporte = NOW() WHERE id = ?");
        $st->bind_param('is', $n, $cargadorId);
    } elseif ($valor === 'disponible') {
        $st = $conexion->prepare("UPDATE cargadores SET estadoUso = 'Disponible', libre_estimado = NULL, ultimo_reporte = NOW() WHERE id = ?");
        $st->bind_param('s', $cargadorId);
    } else { // en_uso: estimación predictiva con el modelo físico si informan vehículo y % de batería
        $minutos = null;
        $vehiculoId = (int)($in['vehiculo_id'] ?? 0);
        if ($vehiculoId > 0 && isset($in['soc']) && is_numeric($in['soc'])) {
            $sv = $conexion->prepare("SELECT capacidad_kwh, potencia_max_kw FROM vehiculos WHERE id = ? AND usuario_id = ?");
            $sv->bind_param('ii', $vehiculoId, $uid);
            $sv->execute();
            $veh = $sv->get_result()->fetch_assoc();
            $sv->close();
            if ($veh) {
                $t = tiempoCargaMinutos((float)$veh['capacidad_kwh'], (float)$in['soc'], 100,
                                        (float)$cargador['potenciaKilowatts'], (float)$veh['potencia_max_kw']);
                $minutos = $t === null ? null : (int)ceil($t);
            }
        }
        $st = $conexion->prepare(
            // libre_estimado va ANTES de estadoUso: MySQL evalúa los SET de izquierda a derecha.
            // Si el cargador ya estaba en uso y este reporte no trae estimación, se conserva la anterior.
            "UPDATE cargadores SET
                    libre_estimado = IF(? IS NULL, IF(estadoUso = 'En uso', libre_estimado, NULL), DATE_ADD(NOW(), INTERVAL ? MINUTE)),
                    estadoUso = 'En uso',
                    ultimo_reporte = NOW()
              WHERE id = ?"
        );
        $st->bind_param('iis', $minutos, $minutos, $cargadorId);
    }
    $st->execute();
    $st->close();
    $conexion->commit();
} catch (Throwable $e) {
    $conexion->rollback();
    vm_json(['ok' => false, 'error' => 'servidor'], 500);
}

vm_json(['ok' => true]);
