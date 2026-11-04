<?php
/**
 * Reservas / inicio de carga.
 *   POST accion=iniciar  (JSON, cabecera X-CSRF-Token)   -> reserva el cargador y empieza la carga
 *   POST accion=cancelar (formulario, campo csrf)         -> cancela sin cobro y libera el cargador
 * Estados de una reserva: en_curso -> finalizada (pagada en el checkout) | cancelada
 */
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../model/conexion.php';
require_once __DIR__ . '/../model/calculos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    vm_redirect('/app/view/content/reservas.php');
}
$uid = vm_usuario_id();
if (!$uid) {
    vm_json(['ok' => false, 'error' => 'auth'], 401);
}

$esJson = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
$in = $esJson ? (json_decode(file_get_contents('php://input'), true) ?: []) : $_POST;
$token = $esJson ? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null) : ($_POST['csrf'] ?? null);
if (!vm_csrf_ok($token)) {
    vm_json(['ok' => false, 'error' => 'csrf'], 403);
}
$accion = (string)($in['accion'] ?? '');

/* ---------------- INICIAR CARGA ---------------- */
if ($accion === 'iniciar') {
    $cargadorId = trim((string)($in['cargador_id'] ?? ''));
    $vehiculoId = (int)($in['vehiculo_id'] ?? 0);
    $soc = isset($in['soc']) && is_numeric($in['soc']) ? (int)$in['soc'] : -1;
    if ($cargadorId === '' || $vehiculoId <= 0 || $soc < 0 || $soc > 99) {
        vm_json(['ok' => false, 'error' => 'datos'], 422);
    }

    $conexion->begin_transaction();
    try {
        // Bloqueo de la fila del cargador: evita que dos usuarios reserven a la vez (concurrencia)
        $st = $conexion->prepare(
            "SELECT c.estadoUso, c.estadoOperativo, c.potenciaKilowatts, p.id_usuario,
                    (SELECT COUNT(*) FROM reservas r WHERE r.cargador_id = c.id AND r.estado IN ('en_curso','pendiente')) AS activas
               FROM cargadores c JOIN puntos_carga p ON p.id = c.idPuntoCarga
              WHERE c.id = ? FOR UPDATE"
        );
        $st->bind_param('s', $cargadorId);
        $st->execute();
        $c = $st->get_result()->fetch_assoc();
        $st->close();

        $error = null;
        if (!$c) {
            $error = 'no_existe';
        } elseif ((int)$c['id_usuario'] <= 0) {
            $error = 'publico';            // los cargadores públicos importados no se alquilan
        } elseif ((int)$c['id_usuario'] === $uid) {
            $error = 'propio';
        } elseif (stripos($c['estadoOperativo'], 'fuera') !== false) {
            $error = 'fuera_de_servicio';
        } elseif ((int)$c['activas'] > 0 || stripos($c['estadoUso'], 'uso') !== false) {
            $error = 'ocupado';
        }

        $veh = null;
        if (!$error) {
            $sv = $conexion->prepare("SELECT capacidad_kwh, potencia_max_kw FROM vehiculos WHERE id = ? AND usuario_id = ?");
            $sv->bind_param('ii', $vehiculoId, $uid);
            $sv->execute();
            $veh = $sv->get_result()->fetch_assoc();
            $sv->close();
            if (!$veh) {
                $error = 'vehiculo';
            }
        }
        if (!$error) {
            $sa = $conexion->prepare("SELECT COUNT(*) FROM reservas WHERE usuario_id = ? AND estado IN ('en_curso','pendiente')");
            $sa->bind_param('i', $uid);
            $sa->execute();
            $sa->bind_result($propias);
            $sa->fetch();
            $sa->close();
            if ($propias > 0) {
                $error = 'ya_tiene_carga';
            }
        }
        if ($error) {
            $conexion->rollback();
            vm_json(['ok' => false, 'error' => $error], 409);
        }

        $st = $conexion->prepare(
            "INSERT INTO reservas (cargador_id, usuario_id, fecha, hora_inicio, estado, vehiculo_id, soc_inicial, inicio_real)
             VALUES (?, ?, CURDATE(), CURTIME(), 'en_curso', ?, ?, NOW())"
        );
        $st->bind_param('siii', $cargadorId, $uid, $vehiculoId, $soc);
        $st->execute();
        $reservaId = $conexion->insert_id;
        $st->close();

        $t = tiempoCargaMinutos((float)$veh['capacidad_kwh'], $soc, 100, (float)$c['potenciaKilowatts'], (float)$veh['potencia_max_kw']);
        $min = $t === null ? null : (int)ceil($t);
        $st = $conexion->prepare(
            "UPDATE cargadores SET estadoUso = 'En uso',
                    libre_estimado = IF(? IS NULL, NULL, DATE_ADD(NOW(), INTERVAL ? MINUTE))
              WHERE id = ?"
        );
        $st->bind_param('iis', $min, $min, $cargadorId);
        $st->execute();
        $st->close();

        $conexion->commit();
        vm_json(['ok' => true, 'reserva_id' => $reservaId, 'minutos_estimados' => $min]);
    } catch (Throwable $e) {
        $conexion->rollback();
        vm_json(['ok' => false, 'error' => 'servidor'], 500);
    }
}

/* ---------------- CANCELAR ---------------- */
if ($accion === 'cancelar') {
    $reservaId = (int)($in['reserva_id'] ?? 0);
    $conexion->begin_transaction();
    try {
        $st = $conexion->prepare(
            "SELECT cargador_id FROM reservas
              WHERE id = ? AND usuario_id = ? AND estado IN ('en_curso','pendiente') FOR UPDATE"
        );
        $st->bind_param('ii', $reservaId, $uid);
        $st->execute();
        $r = $st->get_result()->fetch_assoc();
        $st->close();
        if ($r) {
            $st = $conexion->prepare("UPDATE reservas SET estado = 'cancelada', fin_real = NOW(), hora_fin = CURTIME() WHERE id = ?");
            $st->bind_param('i', $reservaId);
            $st->execute();
            $st->close();
            $st = $conexion->prepare("UPDATE cargadores SET estadoUso = 'Disponible', libre_estimado = NULL WHERE id = ?");
            $st->bind_param('s', $r['cargador_id']);
            $st->execute();
            $st->close();
        }
        $conexion->commit();
    } catch (Throwable $e) {
        $conexion->rollback();
    }
    vm_redirect('/app/view/content/reservas.php');
}

vm_json(['ok' => false, 'error' => 'accion'], 400);
