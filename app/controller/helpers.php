<?php
/** Utilidades compartidas por los controladores de la 3ra entrega. */

function vm_sesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function vm_usuario_id(): int
{
    vm_sesion();
    return (int)($_SESSION['id'] ?? 0);
}

function vm_tipo_usuario(): string
{
    vm_sesion();
    return (string)($_SESSION['tipo_usuario'] ?? '');
}

function vm_h($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function vm_json(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function vm_redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Token CSRF por sesión. */
function vm_csrf(): string
{
    vm_sesion();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function vm_csrf_ok(?string $token): bool
{
    vm_sesion();
    return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}

/** Porcentaje de comisión vigente (parámetro global que edita el administrador). */
function vm_comision_pct(mysqli $db): float
{
    $r = $db->query("SELECT valor FROM configuracion WHERE clave = 'comision_porcentaje'");
    $fila = $r ? $r->fetch_assoc() : null;
    return $fila ? (float)$fila['valor'] : 10.0;
}

/**
 * Calcula el resumen de una carga en curso (duración, energía, monto y reparto).
 * Lo usan tanto la pantalla de checkout (vista previa) como el cobro real, así ambos coinciden.
 * Devuelve null si la reserva no existe, no es del usuario o ya no está en curso.
 */
function vm_resumen_reserva(mysqli $db, int $reservaId, int $uid, bool $bloquear = false): ?array
{
    require_once __DIR__ . '/../model/calculos.php';
    $sql = "SELECT r.id, r.cargador_id, r.vehiculo_id, c.tipoConector, c.potenciaKilowatts, c.precioKwh, c.precioHora,
                   p.id_usuario AS propietario_id, p.direccion,
                   GREATEST(1, CEIL(TIMESTAMPDIFF(SECOND, COALESCE(r.inicio_real, NOW()), NOW()) / 60)) AS minutos
              FROM reservas r
              JOIN cargadores c ON c.id = r.cargador_id
              JOIN puntos_carga p ON p.id = c.idPuntoCarga
             WHERE r.id = ? AND r.usuario_id = ? AND r.estado IN ('en_curso','pendiente')"
         . ($bloquear ? ' FOR UPDATE' : '');
    $st = $db->prepare($sql);
    $st->bind_param('ii', $reservaId, $uid);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$r) {
        return null;
    }

    $pMax = 0.0;
    $cap = null;
    if ($r['vehiculo_id']) {
        $sv = $db->prepare("SELECT capacidad_kwh, potencia_max_kw FROM vehiculos WHERE id = ?");
        $sv->bind_param('i', $r['vehiculo_id']);
        $sv->execute();
        if ($v = $sv->get_result()->fetch_assoc()) {
            $pMax = (float)$v['potencia_max_kw'];
            $cap = (float)$v['capacidad_kwh'];
        }
        $sv->close();
    }

    $minutos = (int)$r['minutos'];
    $kwh = kwhEntregados($minutos, (float)$r['potenciaKilowatts'], $pMax, $cap);
    $total = calcularMonto((float)$r['precioKwh'], (float)$r['precioHora'], $kwh, $minutos);
    $pct = vm_comision_pct($db);
    $reparto = repartirMonto($total, $pct);

    return $r + [
        'minutos' => $minutos, 'kwh' => $kwh, 'comision_pct' => $pct,
        'total' => $reparto['total'], 'comision' => $reparto['comision'], 'monto_propietario' => $reparto['propietario'],
    ];
}
