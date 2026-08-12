<?php

ob_start();
ini_set('display_errors', '0');
date_default_timezone_set('America/Asuncion');

require('conexion.php');
include('verificar_navegador.php');
include('buscar_nivel.php');

define('RPS_PERMISO', 'REGULARIZARPAGOSSALTEADOS');
define('RPS_REVERTIR', 'REVERTIRREGULARIZACIONPAGOSSALTEADOS');

function rps_json($data) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function rps_param($name, $default = '') { return isset($_POST[$name]) ? $_POST[$name] : $default; }
function rps_db($value) { return mb_convert_encoding((string)$value, 'ISO-8859-1', 'UTF-8'); }
function rps_user() {
    $user = rps_db(rps_param('useru'));
    $pass = str_replace('=', '+', (string)rps_param('passu'));
    $browser = rps_db(rps_param('navegador'));
    if ($user === '' || $pass === '' || $browser === '' || verificar_navegador($user, $browser, $pass) !== 'ok') {
        rps_json(array('1' => 'UI', '2' => 'Sesion invalida'));
    }
    return (int)$user;
}
function rps_fail($message, $extra = array()) { rps_json(array_merge(array('1' => 'error', '2' => $message), $extra)); }
function rps_query($db, $sql) {
    $result = $db->query($sql);
    if ($result === false) { throw new Exception($db->error); }
    return $result;
}
function rps_tables_exist($db) {
    $r = rps_query($db, "SELECT COUNT(*) n FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('regularizacion_pago_salteado','regularizacion_pago_salteado_detalle','regularizacion_pago_salteado_reversion')");
    return (int)$r->fetch_assoc()['n'] === 3;
}
function rps_payment_data($row) {
    $fields = array('idPago','Fecha','Monto','cod_creditoFK','cod_cobradorFK','Tipo','hora','cod_venta_fk','comision','lat','lot','nrofactura','titulocuota','tipopago','codApertura','codCaja','descripcion','codAperturaApp','cod_tipoPagoFK','nroventa','anulado','num_comprobante','fecha_facturado');
    $data = array(); foreach ($fields as $field) { $data[$field] = $row[$field]; }
    return $data;
}
function rps_plan($db, $sale, $lock) {
    $suffix = $lock ? ' FOR UPDATE' : '';
    $active = rps_query($db, "SELECT r.id_regularizacion,r.fecha_hora FROM regularizacion_pago_salteado r LEFT JOIN regularizacion_pago_salteado_reversion rv ON rv.id_regularizacionFK=r.id_regularizacion WHERE r.cod_venta=$sale AND rv.id_reversion IS NULL ORDER BY r.id_regularizacion DESC LIMIT 1");
    if ($row = $active->fetch_assoc()) { return array('blocked' => 'Esta venta ya tiene una regularizacion activa.', 'regularizacion' => (int)$row['id_regularizacion']); }
    $credits = array(); $numeric = array(); $deliveries = array(); $lastPaid = 0;
    $res = rps_query($db, "SELECT cr.*,CAST(SUBSTRING_INDEX(TRIM(cr.plazo),'/',1) AS UNSIGNED) nro,IFNULL(SUM(CASE WHEN p.Tipo='Pago Cuota' THEN p.Monto ELSE 0 END),0) pagado FROM credito cr LEFT JOIN pago p ON p.cod_creditoFK=cr.idcredito WHERE cr.cod_venta=$sale AND UPPER(IFNULL(cr.Esado,''))<>'INACTIVO' GROUP BY cr.idcredito ORDER BY nro,cr.idcredito".$suffix);
    while ($c = $res->fetch_assoc()) {
        $isDelivery = strtoupper(trim((string)$c['plazo'])) === 'ENTREGA' || strtoupper(trim((string)$c['tipo'])) === 'ENTREGA';
        if ($isDelivery) { $deliveries[(int)$c['idcredito']] = $c; continue; }
        if (!preg_match('/^[0-9]+(\/[0-9]+)?$/', trim((string)$c['plazo']))) { continue; }
        $c['debe'] = max(0, (int)$c['Monto'] - (int)$c['descuento']);
        $c['saldo_capital'] = max(0, $c['debe'] - (int)$c['pagado']);
        $numeric[(int)$c['idcredito']] = $c;
        if ($c['debe'] > 0 && (int)$c['pagado'] >= $c['debe']) { $lastPaid = max($lastPaid, (int)$c['nro']); }
        if ((int)$c['totalinteres'] !== 0 || (int)$c['deudaInteres'] !== 0) { return array('blocked' => 'La venta posee interes distinto de cero y no puede regularizarse con esta herramienta.'); }
    }
    $destinations = array(); $needed = 0;
    foreach ($numeric as $c) {
        if ((int)$c['nro'] < $lastPaid && (int)$c['saldo_capital'] > 0) {
            $destinations[] = array('credito' => (int)$c['idcredito'], 'cuota' => (int)$c['nro'], 'plazo' => $c['plazo'], 'saldo' => (int)$c['saldo_capital']);
            $needed += (int)$c['saldo_capital'];
        }
    }
    usort($destinations, function ($a, $b) { return $a['cuota'] - $b['cuota']; });
    if ($needed <= 0) { return array('blocked' => 'La venta ya no tiene cuotas salteadas de capital.'); }
    $sourceIds = array(); foreach ($deliveries as $id => $unused) { $sourceIds[] = $id; }
    foreach ($numeric as $id => $c) { if ((int)$c['nro'] > (int)$destinations[0]['cuota']) { $sourceIds[] = $id; } }
    if (!$sourceIds) { return array('blocked' => 'No hay pagos de entrega ni de cuotas posteriores disponibles.'); }
    $ids = implode(',', array_map('intval', $sourceIds));
    $payments = array();
    $res = rps_query($db, "SELECT p.*,CASE WHEN UPPER(TRIM(IFNULL(cr.plazo,'')))='ENTREGA' OR UPPER(TRIM(IFNULL(cr.tipo,'')))='ENTREGA' THEN 0 ELSE CAST(SUBSTRING_INDEX(TRIM(cr.plazo),'/',1) AS UNSIGNED) END orden_cuota,CASE WHEN EXISTS(SELECT 1 FROM pago_transferencia_conciliacion pt WHERE pt.cod_pagoFK=p.idPago) OR EXISTS(SELECT 1 FROM ueno_movimiento_pago up WHERE up.cod_pagoFK=p.idPago) THEN 1 ELSE 0 END vinculado FROM pago p INNER JOIN credito cr ON cr.idcredito=p.cod_creditoFK WHERE p.cod_venta_fk=$sale AND p.Tipo='Pago Cuota' AND IFNULL(p.Monto,0)>0 AND p.cod_creditoFK IN ($ids) ORDER BY (orden_cuota=0) DESC,orden_cuota DESC,p.idPago ASC".$suffix);
    while ($p = $res->fetch_assoc()) {
        if ((int)$p['vinculado'] === 1) { return array('blocked' => 'Hay pagos conciliados con transferencia o UENO. Deben desvincularse por su flujo original antes de regularizar.', 'pago_bloqueado' => (int)$p['idPago']); }
        $payments[] = $p;
    }
    $available = 0; foreach ($payments as $p) { $available += (int)$p['Monto']; }
    if ($available < $needed) { return array('blocked' => 'Los pagos disponibles no alcanzan para completar todas las cuotas salteadas.', 'necesario' => $needed, 'disponible' => $available); }
    $allocations = array(); $di = 0; $remainingDest = (int)$destinations[0]['saldo']; $applied = 0;
    foreach ($payments as $p) {
        if ($applied >= $needed) { break; }
        $left = (int)$p['Monto']; $chunks = array();
        while ($left > 0 && $di < count($destinations)) {
            $amount = min($left, $remainingDest);
            $chunks[] = array('credito' => $destinations[$di]['credito'], 'cuota' => $destinations[$di]['cuota'], 'monto' => $amount);
            $left -= $amount; $remainingDest -= $amount; $applied += $amount;
            if ($remainingDest === 0) { $di++; if ($di < count($destinations)) { $remainingDest = (int)$destinations[$di]['saldo']; } }
        }
        if ($left > 0) { $chunks[] = array('credito' => (int)$p['cod_creditoFK'], 'cuota' => (int)$p['orden_cuota'], 'monto' => $left, 'remanente' => 1); }
        $allocations[] = array('payment' => $p, 'chunks' => $chunks);
    }
    $hashParts = array($sale, $needed);
    foreach ($allocations as $a) { $hashParts[] = $a['payment']['idPago'].':'.$a['payment']['cod_creditoFK'].':'.$a['payment']['Monto']; foreach ($a['chunks'] as $c) { $hashParts[] = $c['credito'].':'.$c['monto']; } }
    return array('needed' => $needed, 'available' => $available, 'destinations' => $destinations, 'allocations' => $allocations, 'hash' => sha1(implode('|', $hashParts)));
}
function rps_public_plan($plan, $sale) {
    if (isset($plan['blocked'])) { $plan['1'] = 'error'; $plan['2'] = $plan['blocked']; return $plan; }
    $sources = array(); foreach ($plan['allocations'] as $a) { $p = $a['payment']; $sources[] = array('pago' => (int)$p['idPago'], 'origen' => (int)$p['orden_cuota'] === 0 ? 'Entrega' : 'Cuota '.$p['orden_cuota'], 'monto' => (int)$p['Monto'], 'destinos' => $a['chunks']); }
    return array('1' => 'exito', 'venta' => $sale, 'monto' => $plan['needed'], 'disponible' => $plan['available'], 'destinos' => $plan['destinations'], 'origenes' => $sources, 'huella' => $plan['hash']);
}
function rps_insert_clone($db, $data, $credit, $amount) {
    $columns = array('Fecha','Monto','cod_creditoFK','cod_cobradorFK','Tipo','hora','cod_venta_fk','comision','lat','lot','nrofactura','titulocuota','tipopago','codApertura','codCaja','descripcion','codAperturaApp','cod_tipoPagoFK','nroventa','anulado','num_comprobante','fecha_facturado');
    $values = array(); foreach ($columns as $c) { $v = $c === 'Monto' ? $amount : ($c === 'cod_creditoFK' ? $credit : $data[$c]); $values[] = $v === null ? 'NULL' : "'".$db->real_escape_string((string)$v)."'"; }
    rps_query($db, 'INSERT INTO pago (`'.implode('`,`', $columns).'`) VALUES ('.implode(',', $values).')');
    return (int)$db->insert_id;
}

$user = rps_user();
$db = conectar_al_servidor();
if ($db->connect_errno) { rps_fail('No se pudo conectar a la base de datos.'); }
if (!rps_tables_exist($db)) { rps_fail('Falta ejecutar la actualizacion de historial y regularizacion.'); }
$operation = strtolower(trim((string)rps_param('funt', 'preview')));
$sale = (int)rps_param('venta', 0);
if ($sale <= 0) { rps_fail('Venta invalida.'); }

if ($operation === 'revertir') {
    if (controldeaccesoacasas($user, RPS_REVERTIR, " u.accion='SI' ") != 1) { rps_json(array('1' => 'sinpermiso', '2' => 'No tiene permiso para revertir regularizaciones.')); }
    $id = (int)rps_param('regularizacion', 0);
    $db->autocommit(false);
    try {
        $h = rps_query($db, "SELECT r.* FROM regularizacion_pago_salteado r LEFT JOIN regularizacion_pago_salteado_reversion rv ON rv.id_regularizacionFK=r.id_regularizacion WHERE r.id_regularizacion=$id AND r.cod_venta=$sale AND rv.id_reversion IS NULL FOR UPDATE");
        if (!$h->fetch_assoc()) { throw new Exception('La regularizacion no existe o ya fue revertida.'); }
        $res = rps_query($db, "SELECT * FROM regularizacion_pago_salteado_detalle WHERE id_regularizacionFK=$id ORDER BY id_detalle");
        $originals = array(); $created = array();
        while ($d = $res->fetch_assoc()) { $originals[(int)$d['id_pago_original']] = json_decode($d['datos_originales'], true); if ($d['id_pago_creado'] !== null) { $created[] = (int)$d['id_pago_creado']; } }
        foreach ($created as $pid) { $check = rps_query($db, "SELECT COUNT(*) n FROM pago WHERE idPago=$pid FOR UPDATE")->fetch_assoc(); if ((int)$check['n'] !== 1) { throw new Exception('Un pago creado fue modificado o eliminado; no se puede revertir automaticamente.'); } rps_query($db, "DELETE FROM pago WHERE idPago=$pid"); }
        foreach ($originals as $pid => $data) {
            $check = rps_query($db, "SELECT COUNT(*) n FROM pago WHERE idPago=$pid FOR UPDATE")->fetch_assoc(); if ((int)$check['n'] !== 1) { throw new Exception('Un pago original fue modificado o eliminado; no se puede revertir automaticamente.'); }
            rps_query($db, "UPDATE pago SET cod_creditoFK=".(int)$data['cod_creditoFK'].",Monto=".(int)$data['Monto']." WHERE idPago=$pid");
        }
        rps_query($db, "INSERT INTO regularizacion_pago_salteado_reversion(id_regularizacionFK,usuario_id) VALUES($id,$user)");
        $db->commit(); rps_json(array('1' => 'exito', '2' => 'Regularizacion revertida exactamente.', 'regularizacion' => $id));
    } catch (Exception $e) { $db->rollback(); error_log('RPS revertir: '.$e->getMessage()); rps_fail($e->getMessage()); }
}

if (controldeaccesoacasas($user, RPS_PERMISO, " u.accion='SI' ") != 1) { rps_json(array('1' => 'sinpermiso', '2' => 'No tiene permiso para regularizar pagos salteados.')); }
if ($operation === 'preview') {
    try { rps_json(rps_public_plan(rps_plan($db, $sale, false), $sale)); } catch (Exception $e) { error_log('RPS preview: '.$e->getMessage()); rps_fail('No se pudo preparar la vista previa.'); }
}
if ($operation !== 'aplicar') { rps_fail('Operacion invalida.'); }
$expected = trim((string)rps_param('huella'));
$db->autocommit(false);
try {
    $plan = rps_plan($db, $sale, true);
    if (isset($plan['blocked'])) { throw new Exception($plan['blocked']); }
    if ($expected === '' || !hash_equals($plan['hash'], $expected)) { throw new Exception('Los pagos cambiaron desde la vista previa. Vuelva a revisar antes de aplicar.'); }
    $createdExpected = 0; foreach ($plan['allocations'] as $a) { $createdExpected += max(0, count($a['chunks']) - 1); }
    rps_query($db, "INSERT INTO regularizacion_pago_salteado(usuario_id,cod_venta,monto_reasignado,pagos_origen,pagos_creados,huella_previa) VALUES($user,$sale,".(int)$plan['needed'].",".count($plan['allocations']).",$createdExpected,'".$db->real_escape_string($plan['hash'])."')");
    $rid = (int)$db->insert_id; $createdCount = 0;
    foreach ($plan['allocations'] as $allocation) {
        $p = $allocation['payment']; $original = rps_payment_data($p); $json = $db->real_escape_string(json_encode($original, JSON_UNESCAPED_UNICODE));
        foreach ($allocation['chunks'] as $index => $chunk) {
            $createdId = null;
            if ($index === 0) { rps_query($db, "UPDATE pago SET cod_creditoFK=".(int)$chunk['credito'].",Monto=".(int)$chunk['monto']." WHERE idPago=".(int)$p['idPago']); }
            else { $createdId = rps_insert_clone($db, $original, (int)$chunk['credito'], (int)$chunk['monto']); $createdCount++; }
            rps_query($db, "INSERT INTO regularizacion_pago_salteado_detalle(id_regularizacionFK,id_pago_original,id_pago_creado,cod_credito_origen,cod_credito_destino,monto_original,monto_aplicado,datos_originales) VALUES($rid,".(int)$p['idPago'].",".($createdId === null ? 'NULL' : $createdId).",".(int)$original['cod_creditoFK'].",".(int)$chunk['credito'].",".(int)$original['Monto'].",".(int)$chunk['monto'].",'$json')");
        }
    }
    if ($createdCount !== $createdExpected) { throw new Exception('No coincidio la cantidad de pagos divididos.'); }
    $db->commit(); rps_json(array('1' => 'exito', '2' => 'Pagos regularizados correctamente.', 'regularizacion' => $rid, 'monto' => (int)$plan['needed']));
} catch (Exception $e) { $db->rollback(); error_log('RPS aplicar: '.$e->getMessage()); rps_fail($e->getMessage()); }
