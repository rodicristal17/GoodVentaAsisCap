<?php
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include("classTable.php");
include_once("producto_riesgo_financiero_helper.php");
require_once("interconsulta_seguimiento_paciente_helper.php");
require_once("tratamiento_laboratorio_integracion_helper.php");

$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
function ObtenerDatos($operacion)
{
	
   $user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = mb_convert_encoding((string)($navegador), 'ISO-8859-1', 'UTF-8');
$resp=verificar_navegador($user,$navegador,$pass);
if($resp!="ok"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}

 


if($operacion=="buscarVistaConsulta")
{	 
	$Paciente=$_POST['Paciente'];
    $Paciente = mb_convert_encoding((string)($Paciente), 'ISO-8859-1', 'UTF-8');
	$local= (isset($_POST['local']) ? mb_convert_encoding((string)($_POST['local']), 'ISO-8859-1', 'UTF-8') : "");
	$num_factura= (isset($_POST['num_factura']) ? mb_convert_encoding((string)($_POST['num_factura']), 'ISO-8859-1', 'UTF-8') : "");
	buscarVistaConsulta($Paciente,$local,$num_factura);
}	
 
if($operacion=="buscarDetalleCompradoConsulta")
{	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8'); 
	buscarDetalleCompradoConsulta($cod_venta);
}	
 
if($operacion=="buscarHistorialConsulta")
{	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8'); 
	buscarHistorialConsulta($cod_venta);
}

if($operacion=="guardarPorcentajeProgreso")
{	
	$id_detalle_tratamientoConsulta=$_POST['id_detalle_tratamientoConsulta'];
    $id_detalle_tratamientoConsulta = mb_convert_encoding((string)($id_detalle_tratamientoConsulta), 'ISO-8859-1', 'UTF-8');
	$porcentaje=$_POST['porcentaje'];
    $porcentaje = mb_convert_encoding((string)($porcentaje), 'ISO-8859-1', 'UTF-8'); 
	$cod_agendaFK=$_POST['cod_agendaFK'];
    $cod_agendaFK = mb_convert_encoding((string)($cod_agendaFK), 'ISO-8859-1', 'UTF-8'); 
	$cod_venta=isset($_POST['cod_venta']) ? $_POST['cod_venta'] : "";
    $cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8'); 
	$observacion=isset($_POST['observacion']) ? $_POST['observacion'] : "";
    $observacion = mb_convert_encoding((string)($observacion), 'ISO-8859-1', 'UTF-8'); 
	guardarPorcentajeProgreso($id_detalle_tratamientoConsulta,$porcentaje,$cod_agendaFK,$cod_venta,$observacion);
}

	
if($operacion=="historialConsulta")
{	
	$fecha1=$_POST['fecha1'];
    $fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
	$fecha2=$_POST['fecha2'];
    $fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
	$fechafiltro=$_POST['fechafiltro'];
    $fechafiltro = mb_convert_encoding((string)($fechafiltro), 'ISO-8859-1', 'UTF-8'); 
	$documento=$_POST['documento'];
    $documento = mb_convert_encoding((string)($documento), 'ISO-8859-1', 'UTF-8');
	$paciente=$_POST['paciente'];
    $paciente = mb_convert_encoding((string)($paciente), 'ISO-8859-1', 'UTF-8');
	$especialista=$_POST['especialista'];
    $especialista = mb_convert_encoding((string)($especialista), 'ISO-8859-1', 'UTF-8');
	$local=$_POST['local'];
    $local = mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');
	$selectespecialista=$_POST['selectespecialista'];
    $selectespecialista = mb_convert_encoding((string)($selectespecialista), 'ISO-8859-1', 'UTF-8');


	historialConsulta($fecha1,$fecha2,$fechafiltro,$documento,$paciente,$especialista,$local,$selectespecialista);
}	


if($operacion=="nuevo" || $operacion=="editar" )
{	
	$cod_consulta=$_POST['cod_consulta'];
    $cod_consulta = mb_convert_encoding((string)($cod_consulta), 'ISO-8859-1', 'UTF-8'); 
	
	$motivo=$_POST['motivo'];
    $motivo = mb_convert_encoding((string)($motivo), 'ISO-8859-1', 'UTF-8'); 
	
	$diagnostico=$_POST['diagnostico'];
    $diagnostico = mb_convert_encoding((string)($diagnostico), 'ISO-8859-1', 'UTF-8'); 
	
	$prxtrabajo=$_POST['prxtrabajo'];
    $prxtrabajo = mb_convert_encoding((string)($prxtrabajo), 'ISO-8859-1', 'UTF-8'); 
	
	$trabajoreali=$_POST['trabajoreali'];
    $trabajoreali = mb_convert_encoding((string)($trabajoreali), 'ISO-8859-1', 'UTF-8'); 
	
	$fecha=$_POST['fecha'];
    $fecha = mb_convert_encoding((string)($fecha), 'ISO-8859-1', 'UTF-8'); 
	
	$cod_estecialista=isset($_POST['cod_estecialista']) ? $_POST['cod_estecialista'] : "";
    $cod_estecialista = mb_convert_encoding((string)($cod_estecialista), 'ISO-8859-1', 'UTF-8'); 
    $cod_estecialista = $user;
	
	$cod_agendamiento=$_POST['cod_agendamiento'];
    $cod_agendamiento = mb_convert_encoding((string)($cod_agendamiento), 'ISO-8859-1', 'UTF-8'); 
	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8'); 
	
	$cod_clienteFK=$_POST['cod_clienteConsulta'];
    $cod_clienteFK = mb_convert_encoding((string)($cod_clienteFK), 'ISO-8859-1', 'UTF-8'); 
	
	$apodo=$_POST['apodo'];
    $apodo = mb_convert_encoding((string)($apodo), 'ISO-8859-1', 'UTF-8'); 

	$cod_detalle_tratamiento=isset($_POST['cod_detalle_tratamiento']) ? $_POST['cod_detalle_tratamiento'] : "";
    $cod_detalle_tratamiento = mb_convert_encoding((string)($cod_detalle_tratamiento), 'ISO-8859-1', 'UTF-8'); 

	$avance_tratamiento=isset($_POST['avance_tratamiento']) ? $_POST['avance_tratamiento'] : "0";
    $avance_tratamiento = mb_convert_encoding((string)($avance_tratamiento), 'ISO-8859-1', 'UTF-8'); 
	
	abm($cod_consulta,$motivo,$diagnostico,$prxtrabajo,$trabajoreali,$fecha,$cod_estecialista,$cod_agendamiento,$cod_venta,$cod_clienteFK,$apodo,$operacion,$cod_detalle_tratamiento,$avance_tratamiento);
}	



if($operacion=="agregar_observacion_consulta" )
{	
	$cod_cliente=$_POST['cod_clienteConsulta'];
    $cod_cliente = mb_convert_encoding((string)($cod_cliente), 'ISO-8859-1', 'UTF-8'); 
	
	$descripcion=$_POST['descripcion'];
    $descripcion = mb_convert_encoding((string)($descripcion), 'ISO-8859-1', 'UTF-8'); 
	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8'); 
	
	$user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
	
	agregar_observacion_consulta($cod_cliente,$descripcion,$cod_venta,$user);
}	

if($operacion=="buscar_observacion_consulta" )
{	
	$cod_cliente=$_POST['cod_clienteConsulta'];
    $cod_cliente = mb_convert_encoding((string)($cod_cliente), 'ISO-8859-1', 'UTF-8'); 
	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8'); 
	
	
	
	buscar_observacion_consulta($cod_cliente,$cod_venta);
}	


if($operacion=="vercuotasatrazadas")
{	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8'); 
	vercuotasatrazadas($cod_venta);
}


if($operacion=="actualizarApodo")
{	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8'); 
	
	$apodo=$_POST['apodo'];
    $apodo = mb_convert_encoding((string)($apodo), 'ISO-8859-1', 'UTF-8'); 
	actualizarApodo($cod_venta,$apodo);
}




if($operacion=="verEvolucion")
{	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8'); 
 
	verEvolucion($cod_venta);
}

if($operacion=="buscarTratamientosAgenda")
{	
	$id_agenda=$_POST['id_agenda'];
    $id_agenda = mb_convert_encoding((string)($id_agenda), 'ISO-8859-1', 'UTF-8');
	$buscar= (isset($_POST['buscar']) ? mb_convert_encoding((string)($_POST['buscar']), 'ISO-8859-1', 'UTF-8') : "");
	buscarTratamientosAgenda($id_agenda,$buscar);
}

if($operacion=="vincularTratamientoAgenda")
{	
	$id_agenda=$_POST['id_agenda'];
    $id_agenda = mb_convert_encoding((string)($id_agenda), 'ISO-8859-1', 'UTF-8');
	$cod_detalle=$_POST['cod_detalle'];
    $cod_detalle = mb_convert_encoding((string)($cod_detalle), 'ISO-8859-1', 'UTF-8');
	vincularTratamientoAgenda($id_agenda,$cod_detalle);
}

if($operacion=="obtenerContextoAgendaConsulta")
{
	$id_agenda=isset($_POST['id_agenda']) ? $_POST['id_agenda'] : "";
    $id_agenda = mb_convert_encoding((string)($id_agenda), 'ISO-8859-1', 'UTF-8');
	obtenerContextoAgendaConsulta($id_agenda);
}

if($operacion=="crearPlanDefinitivoDesdeSugerido")
{
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8');
	crearPlanDefinitivoDesdeSugeridoConsulta($cod_venta,$user);
}

if($operacion=="guardarBorradorPlanDefinitivo")
{
	$plan_id=$_POST['plan_id'];
    $plan_id = mb_convert_encoding((string)($plan_id), 'ISO-8859-1', 'UTF-8');
	$motivo=isset($_POST['motivo']) ? mb_convert_encoding((string)($_POST['motivo']), 'ISO-8859-1', 'UTF-8') : "";
	guardarBorradorPlanDefinitivoConsulta($plan_id,$motivo,$user);
}

if($operacion=="confirmarPlanDefinitivo")
{
	$plan_id=$_POST['plan_id'];
    $plan_id = mb_convert_encoding((string)($plan_id), 'ISO-8859-1', 'UTF-8');
	confirmarPlanDefinitivoConsulta($plan_id,$user);
}

if($operacion=="moverItemPlanDefinitivo")
{
	$plan_id=$_POST['plan_id'];
    $plan_id = mb_convert_encoding((string)($plan_id), 'ISO-8859-1', 'UTF-8');
	$item_id=$_POST['item_id'];
    $item_id = mb_convert_encoding((string)($item_id), 'ISO-8859-1', 'UTF-8');
	$direccion=$_POST['direccion'];
    $direccion = mb_convert_encoding((string)($direccion), 'ISO-8859-1', 'UTF-8');
	$motivo=isset($_POST['motivo']) ? mb_convert_encoding((string)($_POST['motivo']), 'ISO-8859-1', 'UTF-8') : "";
	moverItemPlanDefinitivoConsulta($plan_id,$item_id,$direccion,$motivo,$user);
}

if($operacion=="guardarOrdenPlanDefinitivo")
{
	$plan_id=$_POST['plan_id'];
    $plan_id = mb_convert_encoding((string)($plan_id), 'ISO-8859-1', 'UTF-8');
	$orden_ids=isset($_POST['orden_ids']) ? mb_convert_encoding((string)($_POST['orden_ids']), 'ISO-8859-1', 'UTF-8') : "";
	$motivo=isset($_POST['motivo']) ? mb_convert_encoding((string)($_POST['motivo']), 'ISO-8859-1', 'UTF-8') : "";
	guardarOrdenPlanDefinitivoConsulta($plan_id,$orden_ids,$motivo,$user);
}

if($operacion=="actualizarObservacionItemPlanDefinitivo")
{
	$plan_id=$_POST['plan_id'];
    $plan_id = mb_convert_encoding((string)($plan_id), 'ISO-8859-1', 'UTF-8');
	$item_id=$_POST['item_id'];
    $item_id = mb_convert_encoding((string)($item_id), 'ISO-8859-1', 'UTF-8');
	$observacion=isset($_POST['observacion']) ? mb_convert_encoding((string)($_POST['observacion']), 'ISO-8859-1', 'UTF-8') : "";
	$motivo=isset($_POST['motivo']) ? mb_convert_encoding((string)($_POST['motivo']), 'ISO-8859-1', 'UTF-8') : "";
	actualizarObservacionItemPlanDefinitivoConsulta($plan_id,$item_id,$observacion,$motivo,$user);
}

if($operacion=="quitarItemPlanDefinitivo")
{
	$plan_id=$_POST['plan_id'];
    $plan_id = mb_convert_encoding((string)($plan_id), 'ISO-8859-1', 'UTF-8');
	$item_id=$_POST['item_id'];
    $item_id = mb_convert_encoding((string)($item_id), 'ISO-8859-1', 'UTF-8');
	$motivo=isset($_POST['motivo']) ? mb_convert_encoding((string)($_POST['motivo']), 'ISO-8859-1', 'UTF-8') : "";
	quitarItemPlanDefinitivoConsulta($plan_id,$item_id,$motivo,$user);
}

if($operacion=="buscarVentasAnexablesPlanDefinitivo")
{
	$plan_id=$_POST['plan_id'];
    $plan_id = mb_convert_encoding((string)($plan_id), 'ISO-8859-1', 'UTF-8');
	buscarVentasAnexablesPlanDefinitivoConsulta($plan_id);
}

if($operacion=="anexarTratamientosPlanDefinitivo")
{
	$plan_id=$_POST['plan_id'];
    $plan_id = mb_convert_encoding((string)($plan_id), 'ISO-8859-1', 'UTF-8');
	$detalle_ids=isset($_POST['detalle_ids']) ? mb_convert_encoding((string)($_POST['detalle_ids']), 'ISO-8859-1', 'UTF-8') : "";
	$venta_ids=isset($_POST['venta_ids']) ? mb_convert_encoding((string)($_POST['venta_ids']), 'ISO-8859-1', 'UTF-8') : "";
	$motivo=isset($_POST['motivo']) ? mb_convert_encoding((string)($_POST['motivo']), 'ISO-8859-1', 'UTF-8') : "";
	anexarTratamientosPlanDefinitivoConsulta($plan_id,$detalle_ids,$motivo,$user,$venta_ids);
}

if($operacion=="obtenerHistorialPlanDefinitivo")
{
	$plan_id=$_POST['plan_id'];
    $plan_id = mb_convert_encoding((string)($plan_id), 'ISO-8859-1', 'UTF-8');
	obtenerHistorialPlanDefinitivoConsulta($plan_id);
}

if($operacion=="buscarPlanesMadreCliente")
{
	$cod_cliente=isset($_POST['cod_cliente']) ? $_POST['cod_cliente'] : "";
    $cod_cliente = mb_convert_encoding((string)($cod_cliente), 'ISO-8859-1', 'UTF-8');
	buscarPlanesMadreClienteConsulta($cod_cliente);
}

if($operacion=="asignarVentaPlanMadre")
{
	$cod_venta=isset($_POST['cod_venta']) ? $_POST['cod_venta'] : "";
    $cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8');
	$plan_id=isset($_POST['plan_id']) ? $_POST['plan_id'] : "";
    $plan_id = mb_convert_encoding((string)($plan_id), 'ISO-8859-1', 'UTF-8');
	$modo=isset($_POST['modo']) ? $_POST['modo'] : "";
    $modo = mb_convert_encoding((string)($modo), 'ISO-8859-1', 'UTF-8');
	$apodo=isset($_POST['apodo']) ? $_POST['apodo'] : "";
	asignarVentaPlanMadreConsulta($cod_venta,$plan_id,$modo,$apodo,$user);
}


}


function buscarTratamientosAgenda($id_agenda,$buscar)
{
$mysqli=conectar_al_servidor();

if($id_agenda==""){
	$informacion =array("1" => "camposvacio","2" => "");
	echo json_encode($informacion);	
	exit;
}

$id_agenda = mysqli_real_escape_string($mysqli,$id_agenda);
$buscar = mysqli_real_escape_string($mysqli,$buscar);
$filtroBuscar = "";
if($buscar!=""){
	$filtroBuscar = " and concat(pr.nombre_producto,' ',IFNULL(dtv.descripcion,''),' ',IFNULL(vt.num_factura,'')) like '%".$buscar."%' ";
}

$selectRiesgo = ProductoRiesgoFinancieroSelectSql($mysqli, "pr");
$sql= "SELECT 
		dtv.cod_detalle,
		dtv.descripcion,
		dtv.cantidad_detalle,
		dtv.progreso_porcentaje,
		dtv.estado,
		pr.nombre_producto,
		".$selectRiesgo.",
		vt.cod_venta,
		vt.num_factura,
        vt.apodo,
		a.cod_detalle_ventaFK,
        (SELECT nombre_persona FROM persona WHERE cod_persona = vt.cod_clienteFK) AS nombre_cliente
	from agenda a
	inner join venta vt on vt.cod_clienteFK = a.id_paciente
	inner join detalle_venta dtv on dtv.cod_ventaFK = vt.cod_venta
	inner join producto pr on pr.cod_producto = dtv.cod_productoFK
	where a.id_agenda = '$id_agenda'
	and IFNULL(dtv.estado,'') <> 'eliminado'
	".$filtroBuscar."
	order by vt.cod_venta desc, dtv.cod_detalle desc
	limit 150";

$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$pagina="";

if ($valor>0)
{
while ($row= mysqli_fetch_assoc($result))
{  
	$cod_detalle = mb_convert_encoding((string)($row['cod_detalle']), 'UTF-8', 'ISO-8859-1');
	$descripcion = mb_convert_encoding((string)($row['descripcion']), 'UTF-8', 'ISO-8859-1');
	$progreso_porcentaje = mb_convert_encoding((string)($row['progreso_porcentaje']), 'UTF-8', 'ISO-8859-1');
	$nombre_producto = mb_convert_encoding((string)($row['nombre_producto']), 'UTF-8', 'ISO-8859-1');
	$num_factura = mb_convert_encoding((string)($row['num_factura']), 'UTF-8', 'ISO-8859-1');
	$apodo = mb_convert_encoding((string)($row['apodo']), 'UTF-8', 'ISO-8859-1');
	$nombre_cliente = mb_convert_encoding((string)($row['nombre_cliente']), 'UTF-8', 'ISO-8859-1');
	$cod_detalle_ventaFK = mb_convert_encoding((string)($row['cod_detalle_ventaFK']), 'UTF-8', 'ISO-8859-1');
	$nivel_riesgo_financiero = isset($row['nivel_riesgo_financiero']) ? $row['nivel_riesgo_financiero'] : 1;
	$badge_riesgo_financiero = ProductoRiesgoFinancieroBadgeHtml($nivel_riesgo_financiero, "tratamiento-agenda-risk");
	$seleccionado = ($cod_detalle_ventaFK == $cod_detalle) ? " tratamiento-agenda-card--activo" : "";
	$detalle = trim($nombre_producto." ".$descripcion);
	$detalleAttr = htmlspecialchars($detalle, ENT_QUOTES, 'UTF-8');

	$pagina .= "
	<div class='tratamiento-agenda-card".$seleccionado."' onclick='seleccionarTratamientoAgenda(this)'
        style= 'background:linear-gradient(90deg, rgba(76,175,80,.28) 0%, rgba(76,175,80,.28) $progreso_porcentaje%, #f2f2f2 $progreso_porcentaje%, #f2f2f2 100%);'
		data-id='".$cod_detalle."'
		data-nombre='".$detalleAttr."'>
		<div class='tratamiento-agenda-card__top'>
			<strong>".$nombre_cliente.($apodo == '' ? "" : " ($apodo)")."</strong>
            <br>
			<span>".$detalleAttr."</span>
		</div>
		<div class='tratamiento-agenda-card__meta'>
			<span>Num. venta: ".htmlspecialchars($num_factura, ENT_QUOTES, 'UTF-8')."</span>
			<span class='tratamiento-agenda-card__badges'>".$badge_riesgo_financiero."<span class='tratamiento-agenda-progreso'>".htmlspecialchars($progreso_porcentaje, ENT_QUOTES, 'UTF-8')."%</span></span>
		</div>
	</div>";
}
}else{
	$pagina = "<div class='tratamiento-agenda-vacio'>No se encontraron tratamientos para este paciente.</div>";
}

$informacion =array("1" => "exito","2" => $pagina,"3" => $valor);
echo json_encode($informacion);	
exit;
}

function vincularTratamientoAgenda($id_agenda,$cod_detalle)
{
$mysqli=conectar_al_servidor();

if($id_agenda=="" || $cod_detalle==""){
	$informacion =array("1" => "camposvacio");
	echo json_encode($informacion);	
	exit;
}

$consultaVerificar = "SELECT dtv.cod_detalle
	FROM agenda a
	inner join venta vt on vt.cod_clienteFK = a.id_paciente
	inner join detalle_venta dtv on dtv.cod_ventaFK = vt.cod_venta
	WHERE a.id_agenda = ? and dtv.cod_detalle = ?
	LIMIT 1";
$stmtVerificar = $mysqli->prepare($consultaVerificar);
$stmtVerificar->bind_param('ss', $id_agenda, $cod_detalle);

if (!$stmtVerificar->execute()) {
	echo telar_trigger_error('The query execution failed; MySQL said ('.$stmtVerificar->errno.') '.$stmtVerificar->error, E_USER_ERROR);
	exit;
}

$resultVerificar = $stmtVerificar->get_result();
if(mysqli_num_rows($resultVerificar)==0){
	$informacion =array("1" => "error","mensaje" => "El tratamiento no pertenece al paciente del agendamiento");
	echo json_encode($informacion);	
	exit;
}

$consulta1 = "UPDATE agenda SET cod_detalle_ventaFK = ? WHERE id_agenda = ? LIMIT 1";
$stmt1 = $mysqli->prepare($consulta1);
$stmt1->bind_param('ss', $cod_detalle, $id_agenda);

if (!$stmt1->execute()) {
	echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
	exit;
}

$informacion = array("1" => "exito");
mysqli_close($mysqli);
echo json_encode($informacion);
exit;
}

function normalizarFechaAgendaConsulta($fecha)
{
	$fecha = trim((string)$fecha);
	if ($fecha == "" || $fecha == "0000-00-00" || $fecha == "0000-00-00 00:00:00") {
		return "";
	}
	$timestamp = strtotime($fecha);
	return $timestamp ? date("Y-m-d", $timestamp) : "";
}

function formatearHoraAgendaConsulta($hora)
{
	$hora = trim((string)$hora);
	if ($hora == "" || $hora == "00:00:00") { return ""; }
	$timestamp = strtotime($hora);
	return $timestamp ? date("H:i", $timestamp) : substr($hora, 0, 5);
}

function registrarComentarioAgendaConsulta($mysqli,$id_agenda,$comentario)
{
	$id_agenda = trim((string)$id_agenda);
	if ($id_agenda == "" || !ctype_digit($id_agenda) || trim((string)$comentario) == "") { return false; }
	if (!tablaExisteConsulta($mysqli,"comentarios_agenda")) { return false; }
	$comentarioDb = mb_convert_encoding((string)$comentario, 'ISO-8859-1', 'UTF-8');
	$stmt = $mysqli->prepare("INSERT INTO comentarios_agenda (comentario, cod_agendaFK) VALUES (?, ?)");
	if (!$stmt) { return false; }
	$stmt->bind_param("ss", $comentarioDb, $id_agenda);
	return $stmt->execute();
}

function obtenerContextoAgendaClinicaConsulta($mysqli,$id_agenda)
{
	$id_agenda = trim((string)$id_agenda);
	if ($id_agenda == "" || !ctype_digit($id_agenda) || !tablaExisteConsulta($mysqli,"agenda")) {
		return array("existe" => false, "agenda" => null, "tratamientos" => array(), "ids" => array());
	}
	$sqlAgenda = "SELECT a.id_agenda, a.id_paciente, a.id_profesional, a.id_consultorio, a.fecha, a.hora_inicio, a.hora_fin,
		a.estado, a.motivo, a.cod_ventaFK, a.cod_detalle_ventaFK,
		p.nombre_persona AS paciente_nombre,
		up.nombre_persona AS profesional_nombre
		FROM agenda a
		LEFT JOIN persona p ON p.cod_persona = a.id_paciente
		LEFT JOIN persona up ON up.cod_persona = a.id_profesional
		WHERE a.id_agenda = ?
		LIMIT 1";
	$stmtAgenda = $mysqli->prepare($sqlAgenda);
	if (!$stmtAgenda) {
		return array("existe" => false, "agenda" => null, "tratamientos" => array(), "ids" => array());
	}
	$stmtAgenda->bind_param("s", $id_agenda);
	if (!$stmtAgenda->execute()) {
		return array("existe" => false, "agenda" => null, "tratamientos" => array(), "ids" => array());
	}
	$resultAgenda = $stmtAgenda->get_result();
	if (!($agenda = mysqli_fetch_assoc($resultAgenda))) {
		return array("existe" => false, "agenda" => null, "tratamientos" => array(), "ids" => array());
	}
	$agenda["paciente_nombre"] = mb_convert_encoding((string)$agenda["paciente_nombre"], 'UTF-8', 'ISO-8859-1');
	$agenda["profesional_nombre"] = mb_convert_encoding((string)$agenda["profesional_nombre"], 'UTF-8', 'ISO-8859-1');
	$agenda["motivo"] = mb_convert_encoding((string)$agenda["motivo"], 'UTF-8', 'ISO-8859-1');

	$tratamientos = array();
	$ids = array();
	$selectRiesgo = ProductoRiesgoFinancieroSelectSql($mysqli, "pr");
	if (tablaExisteConsulta($mysqli,"agenda_tratamientos")) {
		$sqlTratamientos = "SELECT at.cod_detalle_ventaFK, at.cod_ventaFK, at.estado,
				pr.nombre_producto, dtv.progreso_porcentaje, ".$selectRiesgo."
			FROM agenda_tratamientos at
			INNER JOIN detalle_venta dtv ON dtv.cod_detalle = at.cod_detalle_ventaFK
			INNER JOIN producto pr ON pr.cod_producto = dtv.cod_productoFK
			WHERE at.id_agenda = ?
			AND IFNULL(at.estado,'') <> 'cancelado'
			".ProductoClinicoWhereSqlConsulta("pr")."
			ORDER BY at.id ASC";
		$stmtTratamientos = $mysqli->prepare($sqlTratamientos);
		if ($stmtTratamientos) {
			$stmtTratamientos->bind_param("s", $id_agenda);
			if ($stmtTratamientos->execute()) {
				$resultTratamientos = $stmtTratamientos->get_result();
				while ($row = mysqli_fetch_assoc($resultTratamientos)) {
					$detalleId = (string)$row["cod_detalle_ventaFK"];
					if ($detalleId == "" || isset($ids[$detalleId])) { continue; }
					$ids[$detalleId] = true;
					$tratamientos[] = array(
						"id" => $detalleId,
						"venta_id" => (string)$row["cod_ventaFK"],
						"nombre" => mb_convert_encoding((string)$row["nombre_producto"], 'UTF-8', 'ISO-8859-1'),
						"estado_agenda" => mb_convert_encoding((string)$row["estado"], 'UTF-8', 'ISO-8859-1'),
						"avance" => normalizarPorcentajePlanTratamientoConsulta($row["progreso_porcentaje"]),
						"riesgo" => ProductoRiesgoFinancieroNormalizar($row["nivel_riesgo_financiero"])
					);
				}
			}
		}
	}
	$detallePrincipal = trim((string)$agenda["cod_detalle_ventaFK"]);
	if ($detallePrincipal != "" && ctype_digit($detallePrincipal) && !isset($ids[$detallePrincipal])) {
		$sqlPrincipal = "SELECT dtv.cod_detalle, dtv.cod_ventaFK, pr.nombre_producto, dtv.progreso_porcentaje, ".$selectRiesgo."
			FROM detalle_venta dtv
			INNER JOIN producto pr ON pr.cod_producto = dtv.cod_productoFK
			WHERE dtv.cod_detalle = ?
			".ProductoClinicoWhereSqlConsulta("pr")."
			LIMIT 1";
		$stmtPrincipal = $mysqli->prepare($sqlPrincipal);
		if ($stmtPrincipal) {
			$stmtPrincipal->bind_param("s", $detallePrincipal);
			if ($stmtPrincipal->execute()) {
				$resultPrincipal = $stmtPrincipal->get_result();
				if ($row = mysqli_fetch_assoc($resultPrincipal)) {
					$ids[$detallePrincipal] = true;
					$tratamientos[] = array(
						"id" => $detallePrincipal,
						"venta_id" => (string)$row["cod_ventaFK"],
						"nombre" => mb_convert_encoding((string)$row["nombre_producto"], 'UTF-8', 'ISO-8859-1'),
						"estado_agenda" => "previsto",
						"avance" => normalizarPorcentajePlanTratamientoConsulta($row["progreso_porcentaje"]),
						"riesgo" => ProductoRiesgoFinancieroNormalizar($row["nivel_riesgo_financiero"])
					);
				}
			}
		}
	}
	return array(
		"existe" => true,
		"agenda" => $agenda,
		"tratamientos" => $tratamientos,
		"ids" => array_keys($ids)
	);
}

function obtenerContextoAgendaConsulta($id_agenda)
{
	$mysqli = conectar_al_servidor();
	$contexto = obtenerContextoAgendaClinicaConsulta($mysqli,$id_agenda);
	if (!$contexto["existe"]) {
		mysqli_close($mysqli);
		responderConsultaJson("exito","", array("agenda" => array("existe" => false), "tratamientos" => array(), "ids" => array()));
	}
	$agenda = $contexto["agenda"];
	$salidaAgenda = array(
		"existe" => true,
		"id_agenda" => (string)$agenda["id_agenda"],
		"id_paciente" => (string)$agenda["id_paciente"],
		"fecha" => normalizarFechaAgendaConsulta($agenda["fecha"]),
		"hora_inicio" => formatearHoraAgendaConsulta($agenda["hora_inicio"]),
		"hora_fin" => formatearHoraAgendaConsulta($agenda["hora_fin"]),
		"estado" => (string)$agenda["estado"],
		"paciente" => (string)$agenda["paciente_nombre"],
		"profesional" => (string)$agenda["profesional_nombre"],
		"motivo" => (string)$agenda["motivo"]
	);
	mysqli_close($mysqli);
	responderConsultaJson("exito","", array(
		"agenda" => $salidaAgenda,
		"tratamientos" => $contexto["tratamientos"],
		"ids" => $contexto["ids"]
	));
}

function evaluarTratamientoAgendaConsulta($mysqli,$cod_agendamiento,$cod_detalle_tratamiento)
{
	$contexto = obtenerContextoAgendaClinicaConsulta($mysqli,$cod_agendamiento);
	if (!$contexto["existe"]) {
		return array("tiene_agenda" => false, "requiere_imprevisto" => false, "motivo" => "sin_agenda", "contexto" => $contexto);
	}
	$ids = $contexto["ids"];
	if (count($ids) == 0) {
		return array("tiene_agenda" => true, "requiere_imprevisto" => true, "motivo" => "agenda_sin_tratamiento", "contexto" => $contexto);
	}
	$seleccion = (string)$cod_detalle_tratamiento;
	if (!in_array($seleccion, $ids)) {
		return array("tiene_agenda" => true, "requiere_imprevisto" => true, "motivo" => "tratamiento_distinto", "contexto" => $contexto);
	}
	return array("tiene_agenda" => true, "requiere_imprevisto" => false, "motivo" => "coincide", "contexto" => $contexto);
}

function crearAgendamientoImprevistoConsulta($mysqli,$evaluacionAgenda,$detallePlan,$cod_clienteFK,$cod_estecialista,$fecha,$cod_consulta,$user)
{
	if (empty($evaluacionAgenda["requiere_imprevisto"])) {
		return array("ok" => true, "id_agenda" => "", "creado" => false);
	}
	$contexto = isset($evaluacionAgenda["contexto"]) ? $evaluacionAgenda["contexto"] : array("existe" => false, "agenda" => null, "tratamientos" => array(), "ids" => array());
	$agenda = (!empty($contexto["existe"]) && isset($contexto["agenda"])) ? $contexto["agenda"] : null;
	$idPaciente = $agenda && trim((string)$agenda["id_paciente"]) != "" ? (string)$agenda["id_paciente"] : (string)$cod_clienteFK;
	$idProfesional = $agenda && trim((string)$agenda["id_profesional"]) != "" ? (string)$agenda["id_profesional"] : (string)$cod_estecialista;
	$idConsultorio = $agenda ? (string)$agenda["id_consultorio"] : null;
	$fechaAgenda = $agenda && normalizarFechaAgendaConsulta($agenda["fecha"]) != "" ? normalizarFechaAgendaConsulta($agenda["fecha"]) : normalizarFechaAgendaConsulta($fecha);
	if ($fechaAgenda == "") { $fechaAgenda = date("Y-m-d"); }
	$horaInicio = $agenda && formatearHoraAgendaConsulta($agenda["hora_inicio"]) != "" ? (string)$agenda["hora_inicio"] : date("H:i:s");
	$horaFin = $agenda && formatearHoraAgendaConsulta($agenda["hora_fin"]) != "" ? (string)$agenda["hora_fin"] : date("H:i:s", strtotime("+30 minutes"));
	$codVenta = isset($detallePlan["venta_id"]) ? (string)$detallePlan["venta_id"] : "";
	$codDetalle = isset($detallePlan["cod_detalle"]) ? (string)$detallePlan["cod_detalle"] : "";
	$nombreRealizado = isset($detallePlan["nombre_producto"]) ? (string)$detallePlan["nombre_producto"] : "Tratamiento";
	$tratamientosAgenda = array();
	if (isset($contexto["tratamientos"])) {
		foreach ($contexto["tratamientos"] as $tratamientoAgenda) {
			if (isset($tratamientoAgenda["nombre"])) {
				$tratamientosAgenda[] = (string)$tratamientoAgenda["nombre"];
			}
		}
	}
	$textoPlanificado = count($tratamientosAgenda) > 0 ? implode(", ", $tratamientosAgenda) : "Sin tratamiento vinculado";
	$motivo = "IMPREVISTO: tratamiento realizado fuera de la planificacion original. Tratamiento agendado: ".$textoPlanificado.". Tratamiento realizado: ".$nombreRealizado.". Consulta clinica #".$cod_consulta.".";
	$motivoDb = mb_convert_encoding($motivo, 'ISO-8859-1', 'UTF-8');
	$estado = "ATENDIDO";

	$stmt = $mysqli->prepare("INSERT INTO agenda
		(id_paciente, id_profesional, id_consultorio, fecha, hora_inicio, hora_fin, estado, motivo, creado_por, creado_en, cod_ventaFK, cod_detalle_ventaFK)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
	if (!$stmt) {
		return array("ok" => false, "mensaje" => "No se pudo preparar el agendamiento imprevisto.");
	}
	$stmt->bind_param("sssssssssss", $idPaciente, $idProfesional, $idConsultorio, $fechaAgenda, $horaInicio, $horaFin, $estado, $motivoDb, $user, $codVenta, $codDetalle);
	if (!$stmt->execute()) {
		return array("ok" => false, "mensaje" => "No se pudo crear el agendamiento imprevisto.");
	}
	$idAgendaNueva = (string)$mysqli->insert_id;
	if (tablaExisteConsulta($mysqli,"agenda_tratamientos")) {
		$estadoTratamiento = "realizado";
		$stmtTrat = $mysqli->prepare("INSERT INTO agenda_tratamientos
			(id_agenda, cod_ventaFK, cod_detalle_ventaFK, estado, creado_por, creado_en, realizado_por, realizado_en)
			VALUES (?, ?, ?, ?, ?, NOW(), ?, NOW())
			ON DUPLICATE KEY UPDATE estado='realizado', realizado_por=VALUES(realizado_por), realizado_en=NOW()");
		if ($stmtTrat) {
			$stmtTrat->bind_param("ssssss", $idAgendaNueva, $codVenta, $codDetalle, $estadoTratamiento, $user, $user);
			$stmtTrat->execute();
		}
	}
	registrarComentarioAgendaConsulta($mysqli,$idAgendaNueva,"@{0}: @{".$user."} creo este agendamiento imprevisto desde consulta clinica #".$cod_consulta.". ".$motivo);
	if ($agenda && trim((string)$agenda["id_agenda"]) != "") {
		registrarComentarioAgendaConsulta($mysqli,(string)$agenda["id_agenda"],"@{0}: @{".$user."} registro una consulta con tratamiento distinto al planificado. Se creo agendamiento imprevisto #".$idAgendaNueva." para trazabilidad.");
	}
	return array("ok" => true, "id_agenda" => $idAgendaNueva, "creado" => true, "motivo" => $evaluacionAgenda["motivo"]);
}

function  verEvolucion($cod_venta)
{
$mysqli=conectar_al_servidor();

$tienePorcentajeAnterior = columnaExisteConsulta($mysqli,"evoluciontratamiento","porcentaje_anterior");
$tieneObservacion = columnaExisteConsulta($mysqli,"evoluciontratamiento","observacion");
$selectExtra = "";
if ($tienePorcentajeAnterior) {
	$selectExtra .= ", porcentaje_anterior";
}
if ($tieneObservacion) {
	$selectExtra .= ", observacion";
}

$sql= "SELECT nro,(select nombre_persona from persona where cod_persona=cod_usuraioFK) as usuario, fecha ".$selectExtra."
FROM evoluciontratamiento
WHERE cod_detalle_venta = ?
ORDER BY fecha DESC, cod_evoluciontratamiento DESC
LIMIT 20";

 
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $cod_venta);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$pagina="";

if ($valor>0)
{
$pagina .= "<div class='tratamiento-evolucion-lista'>";
while ($valor= mysqli_fetch_assoc($result))
{  

$nro = mb_convert_encoding((string)($valor['nro']), 'UTF-8', 'ISO-8859-1');   
$usuario = mb_convert_encoding((string)($valor['usuario']), 'UTF-8', 'ISO-8859-1');   
$fecha = mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');   
 $porcentajeAnterior = ($tienePorcentajeAnterior && isset($valor['porcentaje_anterior'])) ? mb_convert_encoding((string)($valor['porcentaje_anterior']), 'UTF-8', 'ISO-8859-1') : "";
 $observacion = ($tieneObservacion && isset($valor['observacion'])) ? mb_convert_encoding((string)($valor['observacion']), 'UTF-8', 'ISO-8859-1') : "";
 $fechaMostrar = $fecha != "" ? date("d/m/Y H:i", strtotime($fecha)) : "";
 $descripcion = $porcentajeAnterior !== "" ? htmlspecialchars($porcentajeAnterior, ENT_QUOTES, 'UTF-8')."% &rarr; ".htmlspecialchars($nro, ENT_QUOTES, 'UTF-8')."%" : htmlspecialchars($nro, ENT_QUOTES, 'UTF-8')."%";
 $pagina .= "<article class='tratamiento-evolucion-item'>"
	."<strong>".$descripcion."</strong>"
	."<span>".htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8')." &middot; ".htmlspecialchars($fechaMostrar, ENT_QUOTES, 'UTF-8')."</span>"
	.($observacion != "" ? "<p>".htmlspecialchars($observacion, ENT_QUOTES, 'UTF-8')."</p>" : "")
	."</article>";
}
$pagina .= "</div>";
} else {
	$pagina = "<div class='tratamiento-evolucion-vacio'>Sin evoluciones registradas todav&iacute;a.</div>";
}
 
$informacion =array("1" => "exito","2" => $pagina );
echo json_encode($informacion);	
exit;
}



function actualizarApodo($cod_venta,$apodo)
{
     $mysqli = conectar_al_servidor();
 
	$consulta1 = "UPDATE venta SET apodo = '$apodo' WHERE cod_venta = '$cod_venta'";

    $stmt1 = $mysqli->prepare($consulta1);
    

    if (!$stmt1->execute()) {
        echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
        exit;
    }
 
    $informacion = array("1" => "exito");
    mysqli_close($mysqli);
    echo json_encode($informacion);
    exit;
}


function columnaExisteConsulta($mysqli,$tabla,$columna)
{
	$stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
	if (!$stmt) { return false; }
	$stmt->bind_param("ss", $tabla, $columna);
	if (!$stmt->execute()) { return false; }
	$result = $stmt->get_result();
	$row = mysqli_fetch_assoc($result);
	return $row && (int)$row["total"] > 0;
}

function tablaExisteConsulta($mysqli,$tabla)
{
	$stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
	if (!$stmt) { return false; }
	$stmt->bind_param("s", $tabla);
	if (!$stmt->execute()) { return false; }
	$result = $stmt->get_result();
	$row = mysqli_fetch_assoc($result);
	return $row && (int)$row["total"] > 0;
}

function responderConsultaJson($estado,$mensaje="",$extra=array())
{
	$informacion = array("1" => $estado, "mensaje" => $mensaje);
	foreach ($extra as $clave => $valor) {
		$informacion[$clave] = $valor;
	}
	echo json_encode($informacion);
	exit;
}

function guardarPorcentajeProgreso($id_detalle_tratamientoConsulta,$porcentaje,$cod_agendaFK,$cod_venta,$observacion)
{
    $mysqli = conectar_al_servidor();
	$user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');

	$id_detalle_tratamientoConsulta = trim((string)$id_detalle_tratamientoConsulta);
	$cod_venta = trim((string)$cod_venta);
	$porcentaje = str_replace("%", "", trim((string)$porcentaje));
	$porcentaje = str_replace(",", ".", $porcentaje);
	if (!ctype_digit($id_detalle_tratamientoConsulta)) {
		responderConsultaJson("camposvacio","No se pudo identificar el tratamiento a evolucionar.");
	}
	if (!is_numeric($porcentaje)) {
		responderConsultaJson("camposvacio","El porcentaje de progreso no es valido.");
	}
	$porcentaje = (int)round((float)$porcentaje);
	if ($porcentaje < 0) { $porcentaje = 0; }
	if ($porcentaje > 100) { $porcentaje = 100; }
	if (!$mysqli->begin_transaction()) {
		mysqli_close($mysqli);
		responderConsultaJson("error","No se pudo iniciar el guardado seguro de la evolucion.");
	}

	$sqlDetalle = "SELECT dtv.cod_detalle, dtv.cod_ventaFK, dtv.progreso_porcentaje, dtv.estado, dtv.estado_tratamiento, pr.nombre_producto
		FROM detalle_venta dtv
		INNER JOIN producto pr ON pr.cod_producto = dtv.cod_productoFK
		WHERE dtv.cod_detalle = ?";
	if ($cod_venta != "") {
		$sqlDetalle .= " AND dtv.cod_ventaFK = ?";
	}
	$sqlDetalle .= " LIMIT 1 FOR UPDATE";
	$stmtDetalle = $mysqli->prepare($sqlDetalle);
	if (!$stmtDetalle) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		responderConsultaJson("error","No se pudo preparar la validacion del tratamiento.");
	}
	if ($cod_venta != "") {
		$stmtDetalle->bind_param("ss", $id_detalle_tratamientoConsulta, $cod_venta);
	} else {
		$stmtDetalle->bind_param("s", $id_detalle_tratamientoConsulta);
	}
	if (!$stmtDetalle->execute()) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		responderConsultaJson("error","No se pudo validar el tratamiento.");
	}
	$resultDetalle = $stmtDetalle->get_result();
	if (!($detalle = mysqli_fetch_assoc($resultDetalle))) {
		$stmtDetalle->close();
		$mysqli->rollback();
		mysqli_close($mysqli);
		responderConsultaJson("error","El tratamiento no pertenece a la venta o paciente activo.");
	}
	$stmtDetalle->close();
	$porcentajeAnterior = normalizarPorcentajePlanTratamientoConsulta($detalle["progreso_porcentaje"]);

	$stmtUpdate = $mysqli->prepare("UPDATE detalle_venta SET progreso_porcentaje = ? WHERE cod_detalle = ? LIMIT 1");
	if (!$stmtUpdate) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		responderConsultaJson("error","No se pudo preparar el guardado del progreso.");
	}
	$stmtUpdate->bind_param("is", $porcentaje, $id_detalle_tratamientoConsulta);
	if (!$stmtUpdate->execute()) {
		$stmtUpdate->close();
		$mysqli->rollback();
		mysqli_close($mysqli);
		responderConsultaJson("error","No se pudo guardar el progreso del tratamiento.");
	}
	$stmtUpdate->close();

	$tienePorcentajeAnterior = columnaExisteConsulta($mysqli,"evoluciontratamiento","porcentaje_anterior");
	$tieneObservacion = columnaExisteConsulta($mysqli,"evoluciontratamiento","observacion");
	$codAgendaValor = trim((string)$cod_agendaFK);
	$codAgendaValor = ctype_digit($codAgendaValor) ? (int)$codAgendaValor : null;
	if ($tienePorcentajeAnterior && $tieneObservacion) {
		$stmtEvolucion = $mysqli->prepare("INSERT INTO evoluciontratamiento (cod_detalle_venta,cod_usuraioFK,nro,fecha,cod_agendaFK,porcentaje_anterior,observacion) VALUES (?,?,?,NOW(),?,?,?)");
		if (!$stmtEvolucion) {
			$mysqli->rollback();
			mysqli_close($mysqli);
			responderConsultaJson("error","No se pudo preparar el historial de evolucion.");
		}
		$stmtEvolucion->bind_param("ssiiis", $id_detalle_tratamientoConsulta, $user, $porcentaje, $codAgendaValor, $porcentajeAnterior, $observacion);
	} else {
		$stmtEvolucion = $mysqli->prepare("INSERT INTO evoluciontratamiento (cod_detalle_venta,cod_usuraioFK,nro,fecha,cod_agendaFK) VALUES (?,?,?,NOW(),?)");
		if (!$stmtEvolucion) {
			$mysqli->rollback();
			mysqli_close($mysqli);
			responderConsultaJson("error","No se pudo preparar el historial de evolucion.");
		}
		$stmtEvolucion->bind_param("ssii", $id_detalle_tratamientoConsulta, $user, $porcentaje, $codAgendaValor);
	}
	if (!$stmtEvolucion->execute()) {
		$stmtEvolucion->close();
		$mysqli->rollback();
		mysqli_close($mysqli);
		responderConsultaJson("error","No se pudo registrar el historial de evolucion.");
	}
	$codEvolucion = intval($stmtEvolucion->insert_id);
	$stmtEvolucion->close();
	if (!$mysqli->commit()) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		responderConsultaJson("error","No se pudo confirmar la evolucion del tratamiento.");
	}
	$contextoLaboratorio = tratamientoLaboratorioContextoEvolucion(
		$mysqli,
		$id_detalle_tratamientoConsulta,
		isset($detalle["cod_ventaFK"]) ? $detalle["cod_ventaFK"] : $cod_venta,
		"evolucion_rapida",
		null,
		$codEvolucion
	);

	$estadoClase = normalizarEstadoPlanTratamientoConsulta($detalle["estado"], $detalle["estado_tratamiento"], $porcentaje);
	$estadoTexto = textoEstadoPlanTratamientoConsulta($estadoClase);
    $informacion = array(
		"1" => "exito",
		"porcentaje_anterior" => $porcentajeAnterior,
		"porcentaje_nuevo" => $porcentaje,
		"estado_clase" => $estadoClase,
		"estado_texto" => $estadoTexto,
		"cod_evolucion" => $codEvolucion,
		"laboratorio" => $contextoLaboratorio
	);
    mysqli_close($mysqli);
    echo json_encode($informacion);
    exit;
}

function asegurarConsultaTratamientoColumnaConsulta($mysqli)
{
	if (columnaExisteConsulta($mysqli,"consulta","cod_detalle_ventaFK")) {
		return true;
	}
	$stmt = $mysqli->prepare("ALTER TABLE consulta ADD COLUMN cod_detalle_ventaFK INT NULL AFTER cod_clienteFK");
	return ($stmt && $stmt->execute());
}

function obtenerTratamientoPlanMadreRegistroConsulta($mysqli,$cod_detalle_tratamiento,$cod_venta)
{
	$cod_detalle_tratamiento = trim((string)$cod_detalle_tratamiento);
	$cod_venta = trim((string)$cod_venta);
	if ($cod_detalle_tratamiento == "" || !ctype_digit($cod_detalle_tratamiento) || $cod_venta == "") {
		return null;
	}
	$contexto = obtenerContextoPlanDefinitivoConsulta($mysqli,$cod_venta);
	if (!$contexto) {
		return null;
	}
	$plan = obtenerPlanMadreAsignadoVentaRowConsulta($mysqli,$contexto["paciente_id"],$contexto["cedula"],$cod_venta);
	if (!$plan) {
		return null;
	}

	$sql = "SELECT pi.id AS plan_item_id, pi.plan_definitivo_id, pi.detalle_venta_id, pi.venta_id,
			dtv.cod_detalle, dtv.progreso_porcentaje, dtv.estado, dtv.estado_tratamiento,
			pr.nombre_producto
		FROM plan_definitivo_tratamiento_items pi
		INNER JOIN detalle_venta dtv ON dtv.cod_detalle = pi.detalle_venta_id
		INNER JOIN producto pr ON pr.cod_producto = dtv.cod_productoFK
		WHERE pi.plan_definitivo_id = ?
		AND pi.detalle_venta_id = ?
		AND pi.activo = 1
		AND IFNULL(dtv.estado,'') <> 'eliminado'
		".ProductoClinicoWhereSqlConsulta("pr")."
		LIMIT 1";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		return null;
	}
	$planId = (string)$plan["id"];
	$stmt->bind_param("ss", $planId, $cod_detalle_tratamiento);
	if (!$stmt->execute()) {
		return null;
	}
	$result = $stmt->get_result();
	if (!($row = mysqli_fetch_assoc($result))) {
		return null;
	}
	$row["nombre_producto"] = mb_convert_encoding((string)$row["nombre_producto"], 'UTF-8', 'ISO-8859-1');
	$row["plan_madre"] = $plan;
	return $row;
}

function registrarEvolucionTratamientoConsultaRegistro($mysqli,$detalle,$user,$porcentaje,$cod_agendamiento,$observacion)
{
	$idDetalle = (string)$detalle["cod_detalle"];
	$porcentaje = normalizarPorcentajePlanTratamientoConsulta($porcentaje);
	$porcentajeAnterior = normalizarPorcentajePlanTratamientoConsulta($detalle["progreso_porcentaje"]);

	$stmtUpdate = $mysqli->prepare("UPDATE detalle_venta SET progreso_porcentaje = ? WHERE cod_detalle = ? LIMIT 1");
	if (!$stmtUpdate) {
		return array("ok" => false, "mensaje" => "No se pudo preparar el guardado del avance.");
	}
	$stmtUpdate->bind_param("is", $porcentaje, $idDetalle);
	if (!$stmtUpdate->execute()) {
		return array("ok" => false, "mensaje" => "No se pudo guardar el avance del tratamiento.");
	}

	$tienePorcentajeAnterior = columnaExisteConsulta($mysqli,"evoluciontratamiento","porcentaje_anterior");
	$tieneObservacion = columnaExisteConsulta($mysqli,"evoluciontratamiento","observacion");
	$codAgendaValor = trim((string)$cod_agendamiento);
	$codAgendaValor = ctype_digit($codAgendaValor) ? (int)$codAgendaValor : null;
	if ($tienePorcentajeAnterior && $tieneObservacion) {
		$stmtEvolucion = $mysqli->prepare("INSERT INTO evoluciontratamiento (cod_detalle_venta,cod_usuraioFK,nro,fecha,cod_agendaFK,porcentaje_anterior,observacion) VALUES (?,?,?,NOW(),?,?,?)");
		if (!$stmtEvolucion) {
			return array("ok" => false, "mensaje" => "No se pudo preparar el historial de evolucion.");
		}
		$stmtEvolucion->bind_param("ssiiis", $idDetalle, $user, $porcentaje, $codAgendaValor, $porcentajeAnterior, $observacion);
	} else {
		$stmtEvolucion = $mysqli->prepare("INSERT INTO evoluciontratamiento (cod_detalle_venta,cod_usuraioFK,nro,fecha,cod_agendaFK) VALUES (?,?,?,NOW(),?)");
		if (!$stmtEvolucion) {
			return array("ok" => false, "mensaje" => "No se pudo preparar el historial de evolucion.");
		}
		$stmtEvolucion->bind_param("ssii", $idDetalle, $user, $porcentaje, $codAgendaValor);
	}
	if (!$stmtEvolucion->execute()) {
		return array("ok" => false, "mensaje" => "No se pudo registrar la evolucion del tratamiento.");
	}
	$codEvolucion = intval($stmtEvolucion->insert_id);
	$stmtEvolucion->close();
	return array("ok" => true, "porcentaje_anterior" => $porcentajeAnterior, "porcentaje_nuevo" => $porcentaje, "cod_evolucion" => $codEvolucion);
}


function vercuotasatrazadas($cod_venta){
	

$mysqli = conectar_al_servidor();

// Fecha actual
$hoy = new DateTime(date("Y-m-d"));

$sql = "
    SELECT 
        c.idcredito,
        c.fechapago,
        c.Monto,
        IFNULL(SUM(p.Monto), 0) AS total_pagado
    FROM credito c
    LEFT JOIN pago p ON p.cod_creditoFK = c.idcredito
    WHERE c.cod_venta = ?
    GROUP BY c.idcredito
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $cod_venta);
$stmt->execute();
$result = $stmt->get_result();

$total_cuotas_faltantes = 0;
$total_dias_atraso = 0;
$total_cuotas = 0;
$pagina="";
while ($row = $result->fetch_assoc()) {
    $fecha_cuota = new DateTime($row['fechapago']);
    $monto_cuota = $row['Monto'];
    $pagado = $row['total_pagado'];
    $idcredito = $row['idcredito'];

    $total_cuotas++;

    // Si la cuota no está completamente pagada y ya venció
    if ($pagado < $monto_cuota && $fecha_cuota < $hoy) {
        $total_cuotas_faltantes++;

        // Días de atraso desde el vencimiento
        $dias_atraso = $fecha_cuota->diff($hoy)->days;
		if($total_dias_atraso=="0"){
			 $total_dias_atraso= $dias_atraso;
		}
       

    }
}
if($total_dias_atraso!="0"){
$pagina.="Total cuotas impagas vencidas: $total_cuotas_faltantes ";
$pagina.=" y un total de $total_dias_atraso Días de atraso";
}


$stmt->close();
$mysqli->close();

	$informacion = array("1" => "exito", "2" => $pagina);
    echo json_encode($informacion);
    exit;
	
}
 

function abm($cod_consulta,$motivo,$diagnostico,$prxtrabajo,$trabajoreali,$fecha,$cod_estecialista,$cod_agendamiento,$cod_venta,$cod_clienteFK,$apodo,$operacion,$cod_detalle_tratamiento,$avance_tratamiento)
{
    if (trim((string)$trabajoreali) == "") {
        responderConsultaJson("camposvacio","Debe registrar la evolucion del tratamiento realizado.");
    }
    $cod_detalle_tratamiento = trim((string)$cod_detalle_tratamiento);
    if ($cod_detalle_tratamiento == "" || !ctype_digit($cod_detalle_tratamiento)) {
        responderConsultaJson("camposvacio","Seleccione el tratamiento realizado del plan madre.");
    }

    $mysqli = conectar_al_servidor();
	if (!asegurarConsultaTratamientoColumnaConsulta($mysqli)) {
		mysqli_close($mysqli);
		responderConsultaJson("error","No se pudo preparar el vinculo entre consulta y tratamiento.");
	}
	$detallePlan = obtenerTratamientoPlanMadreRegistroConsulta($mysqli,$cod_detalle_tratamiento,$cod_venta);
	if (!$detallePlan) {
		mysqli_close($mysqli);
		responderConsultaJson("error","Seleccione un tratamiento activo que pertenezca al plan madre.");
	}
	$evaluacionAgenda = evaluarTratamientoAgendaConsulta($mysqli,$cod_agendamiento,$cod_detalle_tratamiento);
	$avance_tratamiento = normalizarPorcentajePlanTratamientoConsulta($avance_tratamiento);
	$mysqli->autocommit(false);
	$codEvolucion = null;

    if ($operacion == "nuevo") {
        $consulta1 = "INSERT INTO consulta (
            cod_ventaFK, fecha, cod_usuarioFK, cod_agendamientoFK, estado,
            trabajo_realizado, proximo_trabajo, motivoconsulta, diagnostico, cod_clienteFK, cod_detalle_ventaFK) VALUES (?, ?, ?, ?, 'Activo', ?, ?, ?, ?, ?, ?)";

        $stmt1 = $mysqli->prepare($consulta1);
		if (!$stmt1) {
			$mysqli->rollback();
			mysqli_close($mysqli);
			responderConsultaJson("error","No se pudo preparar el registro clinico.");
		}
        $ss = 'ssssssssss';
        $stmt1->bind_param($ss, $cod_venta, $fecha, $cod_estecialista, $cod_agendamiento, $trabajoreali, $prxtrabajo, $motivo, $diagnostico, $cod_clienteFK, $cod_detalle_tratamiento);
    }

    if ($operacion == "editar") {
        $consulta1 = "UPDATE consulta SET
            cod_ventaFK = ?, fecha = ?, cod_usuarioFK = ?, cod_agendamientoFK = ?, 
            trabajo_realizado = ?, proximo_trabajo = ?, motivoconsulta = ?, diagnostico = ?, cod_clienteFK = ?, cod_detalle_ventaFK = ?
            WHERE cod_consulta = ?";
        
        $stmt1 = $mysqli->prepare($consulta1);
		if (!$stmt1) {
			$mysqli->rollback();
			mysqli_close($mysqli);
			responderConsultaJson("error","No se pudo preparar la actualizacion del registro clinico.");
		}
        $ss = 'sssssssssss';
        $stmt1->bind_param($ss, $cod_venta, $fecha, $cod_estecialista, $cod_agendamiento, 
                                $trabajoreali, $prxtrabajo, $motivo, $diagnostico, $cod_clienteFK, $cod_detalle_tratamiento, $cod_consulta);
    }

	if (!isset($stmt1)) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		responderConsultaJson("error","Operacion de registro clinico no valida.");
	}

    if (!$stmt1->execute()) {
		$mysqli->rollback();
		mysqli_close($mysqli);
        responderConsultaJson("error","No se pudo guardar el registro clinico.");
    }

    if ($operacion == "nuevo") {
        $cod_consulta = $mysqli->insert_id;
		$imprevisto = crearAgendamientoImprevistoConsulta($mysqli,$evaluacionAgenda,$detallePlan,$cod_clienteFK,$cod_estecialista,$fecha,$cod_consulta,$cod_estecialista);
		if (!$imprevisto["ok"]) {
			$mysqli->rollback();
			mysqli_close($mysqli);
			responderConsultaJson("error",$imprevisto["mensaje"]);
		}
		$agendaEvolucion = !empty($imprevisto["creado"]) ? $imprevisto["id_agenda"] : $cod_agendamiento;
		if (!empty($imprevisto["creado"])) {
			$stmtConsultaAgenda = $mysqli->prepare("UPDATE consulta SET cod_agendamientoFK = ? WHERE cod_consulta = ? LIMIT 1");
			if (!$stmtConsultaAgenda) {
				$mysqli->rollback();
				mysqli_close($mysqli);
				responderConsultaJson("error","No se pudo vincular la consulta con el agendamiento imprevisto.");
			}
			$stmtConsultaAgenda->bind_param("ss", $agendaEvolucion, $cod_consulta);
			if (!$stmtConsultaAgenda->execute()) {
				$mysqli->rollback();
				mysqli_close($mysqli);
				responderConsultaJson("error","No se pudo actualizar la referencia del agendamiento imprevisto.");
			}
		}
		$evolucion = registrarEvolucionTratamientoConsultaRegistro($mysqli,$detallePlan,$cod_estecialista,$avance_tratamiento,$agendaEvolucion,$trabajoreali);
		if (!$evolucion["ok"]) {
			$mysqli->rollback();
			mysqli_close($mysqli);
			responderConsultaJson("error",$evolucion["mensaje"]);
		}
		$codEvolucion = isset($evolucion["cod_evolucion"]) ? intval($evolucion["cod_evolucion"]) : null;
    } else {
		$imprevisto = array("ok" => true, "id_agenda" => "", "creado" => false);
    }

	$stmtVenta = $mysqli->prepare("UPDATE venta SET apodo = ? WHERE cod_venta = ?");
	if (!$stmtVenta) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		responderConsultaJson("error","No se pudo preparar la actualizacion del beneficiario.");
	}
	$stmtVenta->bind_param("ss", $apodo, $cod_venta);
    if (!$stmtVenta->execute()) {
		$mysqli->rollback();
		mysqli_close($mysqli);
        responderConsultaJson("error","No se pudo actualizar el beneficiario de la venta.");
    }
	if (!$mysqli->commit()) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		responderConsultaJson("error","No se pudo confirmar el registro clinico.");
	}
	$contextoLaboratorio = tratamientoLaboratorioContextoEvolucion(
		$mysqli,
		$cod_detalle_tratamiento,
		$cod_venta,
		$operacion == "nuevo" ? "consulta_nueva" : "consulta_editada",
		$cod_consulta,
		$codEvolucion
	);
    $informacion = array(
		"1" => "exito",
		"2" => $cod_consulta,
		"agenda_imprevista_id" => isset($imprevisto["id_agenda"]) ? (string)$imprevisto["id_agenda"] : "",
		"agenda_imprevista_creada" => !empty($imprevisto["creado"]) ? "1" : "0",
		"agenda_actualizar_original" => !empty($imprevisto["creado"]) ? "0" : "1",
		"agenda_validacion" => isset($evaluacionAgenda["motivo"]) ? (string)$evaluacionAgenda["motivo"] : "",
		"cod_evolucion" => $codEvolucion,
		"laboratorio" => $contextoLaboratorio
	);
    mysqli_close($mysqli);
    echo json_encode($informacion);
    exit;
}

function agregar_observacion_consulta($cod_cliente,$descripcion,$cod_venta,$user)
{
    if ($cod_cliente == "" || $descripcion == "" ) {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);    
        exit;
    }
	
	// Crear el objeto DateTime con la zona horaria de Paraguay
$paraguayTime = new DateTime("now", new DateTimeZone("America/Asuncion"));

// Obtener el string para guardar en base de datos (formato DATETIME)
$fechaHora = $paraguayTime->format("Y-m-d H:i:s");

    $mysqli = conectar_al_servidor();


    $consulta1 = "INSERT INTO detalle_observacion_consulta (descripcion,cod_clienteFK,cod_venta,cod_usuarioFK,fecha_hora) VALUES ('$descripcion','$cod_cliente','$cod_venta','$user','$fechaHora')";

    $stmt1 = $mysqli->prepare($consulta1);
    

    if (!$stmt1->execute()) {
        echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
        exit;
    }

   

    mysqli_close($mysqli);
	$informacion =array("1" => "exito" );
	echo json_encode($informacion);	
	exit;
}

function  buscar_observacion_consulta($cod_clienteFK,$cod_ventaFK)
{
$mysqli=conectar_al_servidor();

$sql= "SELECT descripcion,(select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuario, fecha_hora FROM detalle_observacion_consulta WHERE cod_clienteFK = '$cod_clienteFK' and cod_venta = '$cod_ventaFK'";

 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$pagina="";
$comentarios = array();

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$descripcion = mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');   
$usuario = mb_convert_encoding((string)($valor['usuario']), 'UTF-8', 'ISO-8859-1');   
$fecha_hora = mb_convert_encoding((string)($valor['fecha_hora']), 'UTF-8', 'ISO-8859-1');   

$comentarios[] = array(
    "descripcion" => $descripcion,
    "usuario" => $usuario,
    "fecha_hora" => $fecha_hora
);
 
}
}

if (count($comentarios) > 0) {
    $totalComentarios = count($comentarios);
    $limiteComentarios = 4;
    $primerVisible = max(0, $totalComentarios - $limiteComentarios);
    $textoCantidad = $totalComentarios == 1 ? "1 comentario interno" : $totalComentarios." comentarios internos";
    $toggleId = "consultaComentariosToggle";

    $pagina .= "<div class='consulta-comments-feed'>";

    if ($totalComentarios > $limiteComentarios) {
        $pagina .= "<input type='checkbox' class='consulta-comments-toggle' id='".$toggleId."'>";
    }

    $pagina .= "<div class='consulta-comments-feed__toolbar'><span>".$textoCantidad."</span>";

    if ($totalComentarios > $limiteComentarios) {
        $pagina .= "<label class='consulta-comments-toggle-label' for='".$toggleId."'><span class='consulta-comments-toggle-label__more'>Ver todos</span><span class='consulta-comments-toggle-label__less'>Ver menos</span></label>";
    }

    $pagina .= "</div>";

    foreach ($comentarios as $indiceComentario => $comentario) {
        $usuarioComentario = trim($comentario["usuario"]) != "" ? $comentario["usuario"] : "Equipo";
        $partesNombreComentario = preg_split('/\s+/', trim($usuarioComentario));
        $inicialesComentario = "";

        foreach ($partesNombreComentario as $parteNombreComentario) {
            if ($parteNombreComentario != "") {
                $inicialesComentario .= mb_substr($parteNombreComentario, 0, 1, 'UTF-8');
            }

            if (mb_strlen($inicialesComentario, 'UTF-8') >= 2) {
                break;
            }
        }

        if ($inicialesComentario == "") {
            $inicialesComentario = "E";
        }

        $inicialesComentario = mb_strtoupper($inicialesComentario, 'UTF-8');
        $fechaComentario = $comentario["fecha_hora"];
        $horaComentario = "";
        $timestampComentario = strtotime($comentario["fecha_hora"]);

        if ($timestampComentario !== false) {
            $fechaComentario = date("d/m/Y", $timestampComentario);
            $horaComentario = date("H:i", $timestampComentario);
        }

        $fechaHoraComentario = htmlspecialchars($fechaComentario, ENT_QUOTES, 'UTF-8').($horaComentario != "" ? " &middot; ".htmlspecialchars($horaComentario, ENT_QUOTES, 'UTF-8') : "");
        $claseExtra = ($totalComentarios > $limiteComentarios && $indiceComentario < $primerVisible) ? " consulta-comment-item--extra" : "";

        $pagina .= "<article class='consulta-comment-item".$claseExtra."'>"
            ."<div class='consulta-comment-item__avatar' aria-hidden='true'>".htmlspecialchars($inicialesComentario, ENT_QUOTES, 'UTF-8')."</div>"
            ."<div class='consulta-comment-item__content'>"
            ."<div class='consulta-comment-item__header'>"
            ."<strong>".htmlspecialchars($usuarioComentario, ENT_QUOTES, 'UTF-8')."</strong>"
            ."<span>".$fechaHoraComentario."</span>"
            ."</div>"
            ."<div class='consulta-comment-item__bubble'>"
            ."<div class='consulta-comment-item__text'>".nl2br(htmlspecialchars($comentario["descripcion"], ENT_QUOTES, 'UTF-8'))."</div>"
            ."</div>"
            ."</div>"
            ."</article>";
    }

    $pagina .= "</div>";
} else {
    $pagina = "<div class='consulta-comments-empty'>No hay comentarios internos todav&iacute;a.</div>";
}
 
$informacion =array("1" => "exito","2" => $pagina );
echo json_encode($informacion);	
exit;
}



function obtenerEvolucionesTratamientoHistorialConsulta($mysqli,$cod_detalle,$cod_agendamiento,$cod_usuario,$fecha_consulta)
{
	$cod_detalle = trim((string)$cod_detalle);
	if ($cod_detalle == "" || !ctype_digit($cod_detalle) || !tablaExisteConsulta($mysqli,"evoluciontratamiento")) {
		return array("items" => array(), "actual" => null);
	}

	$tienePorcentajeAnterior = columnaExisteConsulta($mysqli,"evoluciontratamiento","porcentaje_anterior");
	$tieneObservacion = columnaExisteConsulta($mysqli,"evoluciontratamiento","observacion");
	$selectExtra = "";
	if ($tienePorcentajeAnterior) { $selectExtra .= ", et.porcentaje_anterior"; }
	if ($tieneObservacion) { $selectExtra .= ", et.observacion"; }

	$sql = "SELECT et.cod_evoluciontratamiento, et.cod_detalle_venta, et.cod_usuraioFK, et.nro, et.fecha, et.cod_agendaFK,
			p.nombre_persona AS profesional ".$selectExtra."
		FROM evoluciontratamiento et
		LEFT JOIN persona p ON p.cod_persona = et.cod_usuraioFK
		WHERE et.cod_detalle_venta = ?
		ORDER BY et.fecha ASC, et.cod_evoluciontratamiento ASC
		LIMIT 30";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) { return array("items" => array(), "actual" => null); }
	$stmt->bind_param("s", $cod_detalle);
	if (!$stmt->execute()) { return array("items" => array(), "actual" => null); }
	$result = $stmt->get_result();
	$items = array();
	$actual = null;
	$anteriorInferido = 0;
	$cod_agendamiento = trim((string)$cod_agendamiento);
	$cod_usuario = trim((string)$cod_usuario);
	$fechaConsultaDia = "";
	$tsConsulta = strtotime((string)$fecha_consulta);
	if ($tsConsulta !== false) {
		$fechaConsultaDia = date("Y-m-d", $tsConsulta);
	}

	while ($row = mysqli_fetch_assoc($result)) {
		$nuevo = normalizarPorcentajePlanTratamientoConsulta($row["nro"]);
		$anterior = $tienePorcentajeAnterior && isset($row["porcentaje_anterior"])
			? normalizarPorcentajePlanTratamientoConsulta($row["porcentaje_anterior"])
			: $anteriorInferido;
		$profesional = mb_convert_encoding((string)$row["profesional"], 'UTF-8', 'ISO-8859-1');
		if (trim($profesional) == "") { $profesional = "Profesional"; }
		$observacion = $tieneObservacion && isset($row["observacion"]) ? mb_convert_encoding((string)$row["observacion"], 'UTF-8', 'ISO-8859-1') : "";
		$fecha = (string)$row["fecha"];
		$fechaDia = "";
		$fechaMostrar = "";
		$ts = strtotime($fecha);
		if ($ts !== false) {
			$fechaDia = date("Y-m-d", $ts);
			$fechaMostrar = date("d/m/Y", $ts);
		}
		$item = array(
			"id" => (string)$row["cod_evoluciontratamiento"],
			"usuario_id" => (string)$row["cod_usuraioFK"],
			"profesional" => $profesional,
			"anterior" => $anterior,
			"nuevo" => $nuevo,
			"delta" => max(0, $nuevo - $anterior),
			"fecha" => $fecha,
			"fecha_dia" => $fechaDia,
			"fecha_mostrar" => $fechaMostrar,
			"agenda_id" => (string)$row["cod_agendaFK"],
			"observacion" => $observacion
		);
		$items[] = $item;
		if ($cod_agendamiento != "" && (string)$row["cod_agendaFK"] == $cod_agendamiento) {
			$actual = $item;
		} elseif ($actual === null && $cod_usuario != "" && $fechaConsultaDia != "" && (string)$row["cod_usuraioFK"] == $cod_usuario && $fechaDia == $fechaConsultaDia) {
			$actual = $item;
		}
		$anteriorInferido = $nuevo;
	}
	if ($actual === null && count($items) > 0) {
		$actual = $items[count($items) - 1];
	}
	return array("items" => $items, "actual" => $actual);
}

function renderizarEvolucionTratamientoHistorialConsulta($evoluciones)
{
	$items = isset($evoluciones["items"]) ? $evoluciones["items"] : array();
	$actual = isset($evoluciones["actual"]) ? $evoluciones["actual"] : null;
	if (!$actual && count($items) == 0) { return ""; }
	if (!$actual && count($items) > 0) { $actual = $items[count($items) - 1]; }

	$anterior = isset($actual["anterior"]) ? (int)$actual["anterior"] : 0;
	$nuevo = isset($actual["nuevo"]) ? (int)$actual["nuevo"] : 0;
	$delta = isset($actual["delta"]) ? (int)$actual["delta"] : max(0, $nuevo - $anterior);
	$estado = $nuevo >= 100 ? "Finalizado" : ($nuevo > 0 ? "En proceso" : "Pendiente");
	$estadoClase = $nuevo >= 100 ? "completado" : ($nuevo > 0 ? "proceso" : "pendiente");
	$profesionalActual = isset($actual["profesional"]) ? $actual["profesional"] : "Profesional";
	$fechaActual = isset($actual["fecha_mostrar"]) ? $actual["fecha_mostrar"] : "";
	$styleBarra = "--avance-anterior: ".$anterior."%; --avance-actual: ".$nuevo."%;";

	$html = "<div class='consulta-treatment-evolution consulta-treatment-evolution--".$estadoClase."'>"
		."<div class='consulta-treatment-evolution__head'>"
			."<div><span>Avance registrado</span><strong>".$anterior."% &rarr; ".$nuevo."%</strong></div>"
			."<em>".$estado."</em>"
		."</div>"
		."<div class='consulta-treatment-progress' style='".$styleBarra."'>"
			."<div class='consulta-treatment-progress__bar' aria-hidden='true'></div>"
			."<div class='consulta-treatment-progress__info'>"
				."<span>".htmlspecialchars($profesionalActual, ENT_QUOTES, 'UTF-8').($fechaActual != "" ? " &middot; ".htmlspecialchars($fechaActual, ENT_QUOTES, 'UTF-8') : "")."</span>"
				."<b>".($delta > 0 ? "+".$delta."% en esta consulta" : "Sin aumento de porcentaje")."</b>"
			."</div>"
		."</div>";

	if (count($items) > 0) {
		$html .= "<div class='consulta-treatment-evolution__steps'>";
		foreach ($items as $item) {
			$esActual = $actual && isset($actual["id"]) && $item["id"] == $actual["id"];
			$claseActual = $esActual ? " is-current" : "";
			$claseFinal = ((int)$item["nuevo"] >= 100) ? " is-finished" : "";
			$html .= "<span class='consulta-treatment-evolution-step".$claseActual.$claseFinal."'>"
				."<b>".(int)$item["nuevo"]."%</b>"
				."<strong>".htmlspecialchars($item["profesional"], ENT_QUOTES, 'UTF-8')."</strong>"
				."<small>".htmlspecialchars($item["fecha_mostrar"], ENT_QUOTES, 'UTF-8')."</small>"
				."</span>";
		}
		$html .= "</div>";
	}
	$html .= "</div>";
	return $html;
}



function  buscarHistorialConsulta($cod_venta)
{
$mysqli=conectar_al_servidor();

$tieneVinculoTratamiento = asegurarConsultaTratamientoColumnaConsulta($mysqli);
$selectTratamiento = "'' AS cod_detalle_ventaFK, '' AS tratamiento_realizado_nombre";
$joinTratamiento = "";
if ($tieneVinculoTratamiento) {
	$selectTratamiento = "c.cod_detalle_ventaFK, pr.nombre_producto AS tratamiento_realizado_nombre";
	$joinTratamiento = " LEFT JOIN detalle_venta dtv ON dtv.cod_detalle = c.cod_detalle_ventaFK
		LEFT JOIN producto pr ON pr.cod_producto = dtv.cod_productoFK ";
}

$sql= "SELECT c.cod_consulta, c.cod_ventaFK, c.fecha, c.cod_usuarioFK, c.cod_agendamientoFK, c.estado,
 c.trabajo_realizado, c.proximo_trabajo, c.motivoconsulta, c.diagnostico,
 ".$selectTratamiento.",
 (select nombre_persona from persona where cod_persona=c.cod_usuarioFK) as especialista
 FROM consulta c
 ".$joinTratamiento."
 WHERE c.cod_ventaFK = ?
 ORDER BY c.cod_consulta DESC";

 
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $cod_venta);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";
$pagina="";

if ($valor>0)
{
$indiceConsultaHistorial = 0;
while ($valor= mysqli_fetch_assoc($result))
{  

$cod_consulta = mb_convert_encoding((string)($valor['cod_consulta']), 'UTF-8', 'ISO-8859-1');   
$fecha = mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');          
$cod_usuario_consulta = mb_convert_encoding((string)($valor['cod_usuarioFK']), 'UTF-8', 'ISO-8859-1');
$cod_agendamiento_consulta = mb_convert_encoding((string)($valor['cod_agendamientoFK']), 'UTF-8', 'ISO-8859-1');
$trabajo_realizado = mb_convert_encoding((string)($valor['trabajo_realizado']), 'UTF-8', 'ISO-8859-1');          
$proximo_trabajo = mb_convert_encoding((string)($valor['proximo_trabajo']), 'UTF-8', 'ISO-8859-1');  
$motivoconsulta = mb_convert_encoding((string)($valor['motivoconsulta']), 'UTF-8', 'ISO-8859-1');  
$diagnostico = mb_convert_encoding((string)($valor['diagnostico']), 'UTF-8', 'ISO-8859-1');  
$especialista = mb_convert_encoding((string)($valor['especialista']), 'UTF-8', 'ISO-8859-1');  
$cod_detalle_ventaFK = mb_convert_encoding((string)($valor['cod_detalle_ventaFK']), 'UTF-8', 'ISO-8859-1');  
$tratamiento_realizado_nombre = mb_convert_encoding((string)($valor['tratamiento_realizado_nombre']), 'UTF-8', 'ISO-8859-1');  

$especialistaMostrar = trim($especialista) != "" ? $especialista : "Profesional";
$partesEspecialista = preg_split('/\s+/', trim($especialistaMostrar));
$inicialesEspecialista = "";

foreach ($partesEspecialista as $parteEspecialista) {
    if ($parteEspecialista != "") {
        $inicialesEspecialista .= mb_substr($parteEspecialista, 0, 1, 'UTF-8');
    }

    if (mb_strlen($inicialesEspecialista, 'UTF-8') >= 2) {
        break;
    }
}

if ($inicialesEspecialista == "") {
    $inicialesEspecialista = "P";
}

$inicialesEspecialista = mb_strtoupper($inicialesEspecialista, 'UTF-8');
$fechaConsulta = $fecha;
$horaConsulta = "";
$timestampConsulta = strtotime($fecha);

if ($timestampConsulta !== false) {
    $fechaConsulta = date("d/m/Y", $timestampConsulta);
    if (date("H:i", $timestampConsulta) != "00:00") {
        $horaConsulta = date("H:i", $timestampConsulta);
    }
}

$codConsultaHtml = htmlspecialchars($cod_consulta, ENT_QUOTES, 'UTF-8');
$fechaDataHtml = htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8');
$especialistaDataHtml = htmlspecialchars($especialista, ENT_QUOTES, 'UTF-8');
$trabajoDataHtml = htmlspecialchars($trabajo_realizado, ENT_QUOTES, 'UTF-8');
$proximoDataHtml = htmlspecialchars($proximo_trabajo, ENT_QUOTES, 'UTF-8');
$motivoDataHtml = htmlspecialchars($motivoconsulta, ENT_QUOTES, 'UTF-8');
$diagnosticoDataHtml = htmlspecialchars($diagnostico, ENT_QUOTES, 'UTF-8');
$tratamientoDataHtml = htmlspecialchars($tratamiento_realizado_nombre, ENT_QUOTES, 'UTF-8');
$detalleTratamientoDataHtml = htmlspecialchars($cod_detalle_ventaFK, ENT_QUOTES, 'UTF-8');
$fechaHoraConsultaHtml = htmlspecialchars($fechaConsulta, ENT_QUOTES, 'UTF-8').($horaConsulta != "" ? " &middot; ".htmlspecialchars($horaConsulta, ENT_QUOTES, 'UTF-8') : "");
$motivoVisible = trim($motivoconsulta) != "" ? $motivoconsulta : "Sin motivo registrado";
$tratamientoVisible = trim($tratamiento_realizado_nombre) != "" ? $tratamiento_realizado_nombre : "Sin tratamiento vinculado";
$trabajoVisible = trim($trabajo_realizado) != "" ? $trabajo_realizado : "Sin registro cargado";
$proximoVisible = trim($proximo_trabajo) != "" ? $proximo_trabajo : "Sin registro cargado";
$zonaVisible = trim($diagnostico) != "" ? $diagnostico : "Sin zona de trabajo registrada";
$evolucionTratamientoHtml = "";
if (trim($cod_detalle_ventaFK) != "") {
	$evolucionesTratamiento = obtenerEvolucionesTratamientoHistorialConsulta($mysqli,$cod_detalle_ventaFK,$cod_agendamiento_consulta,$cod_usuario_consulta,$fecha);
	$evolucionTratamientoHtml = renderizarEvolucionTratamientoHistorialConsulta($evolucionesTratamiento);
}
$claseConsultaReciente = $indiceConsultaHistorial == 0 ? " consulta-history-item--latest" : " consulta-history-item--compact";

$pagina .= "
<div 
 onclick='abrirModal(this)'  
  role='button' tabindex='0'
  aria-label='Ver consulta número $cod_consulta' 
  class='tarjeta-consulta consulta-item consulta-history-item".$claseConsultaReciente."'
  data-codconsulta='".$codConsultaHtml."'
  data-fecha='".$fechaDataHtml."'
  data-especialista='".$especialistaDataHtml."'
  data-trabajo='".$trabajoDataHtml."'
  data-proximo='".$proximoDataHtml."'
  data-motivo='".$motivoDataHtml."'
  data-diagnostico='".$diagnosticoDataHtml."'
  data-tratamiento='".$tratamientoDataHtml."'
  data-detalletratamiento='".$detalleTratamientoDataHtml."'
>
  <div class='consulta-history-item__rail'>
    <div class='consulta-history-item__avatar' aria-hidden='true'>".htmlspecialchars($inicialesEspecialista, ENT_QUOTES, 'UTF-8')."</div>
  </div>
  <div class='consulta-history-item__content'>
    <div class='consulta-history-item__header'>
      <div class='consulta-history-item__identity'>
        <span class='consulta-history-item__number'>Consulta N&deg; ".$codConsultaHtml."</span>
        <strong>".htmlspecialchars($especialistaMostrar, ENT_QUOTES, 'UTF-8')."</strong>
        <span>Especialista</span>
      </div>
      <time class='consulta-history-item__date'>".$fechaHoraConsultaHtml."</time>
      <h3 style='display:none;'>Consulta Nº $cod_consulta</h3>
    </div>
    <div class='consulta-history-item__main'>
      <span>Tratamiento realizado</span>
      <strong>".nl2br(htmlspecialchars($tratamientoVisible, ENT_QUOTES, 'UTF-8'))."</strong>
      <p>".nl2br(htmlspecialchars($trabajoVisible, ENT_QUOTES, 'UTF-8'))."</p>
    </div>
    ".$evolucionTratamientoHtml."
    <div class='consulta-history-item__meta'>
      <span><b>Motivo</b>".nl2br(htmlspecialchars($motivoVisible, ENT_QUOTES, 'UTF-8'))."</span>
      <span><b>Zona</b>".nl2br(htmlspecialchars($zonaVisible, ENT_QUOTES, 'UTF-8'))."</span>
      <span><b>Pr&oacute;xima</b>".nl2br(htmlspecialchars($proximoVisible, ENT_QUOTES, 'UTF-8'))."</span>
    </div>
    <div class='consulta-history-item__actions'>
      <button type='button' class='consulta-history-item__recetario' onclick='event.stopPropagation(); abrirRecetarioIndicacionesDesdeConsultaId(\"".$codConsultaHtml."\")'>Receta / indicaciones</button>
    </div>
  </div>
</div>
";
$indiceConsultaHistorial++;
 
}
}
 
$informacion =array("1" => "exito","2" => $pagina );
echo json_encode($informacion);	
exit;
}

function responderPlanDefinitivoConsulta($estado,$mensaje="",$extra=array())
{
	$informacion = array("1" => $estado, "mensaje" => $mensaje);
	foreach ($extra as $clave => $valor) {
		$informacion[$clave] = $valor;
	}
	echo json_encode($informacion);
	exit;
}

function actualizarSeguimientoPacientePlanMadreConsulta($cod_venta,$user)
{
	if (!function_exists("seguimientoPacienteAsegurarHiloPorVenta")) {
		return array("ok" => false, "motivo" => "helper_no_disponible");
	}
	try {
		return seguimientoPacienteAsegurarHiloPorVenta($cod_venta, $user, "plan_madre");
	} catch (Throwable $e) {
		return array("ok" => false, "motivo" => "error_no_bloqueante", "mensaje" => $e->getMessage());
	}
}

function planDefinitivoTablasDisponiblesConsulta($mysqli)
{
	$tablas = array("plan_definitivo_tratamiento", "plan_definitivo_tratamiento_items", "plan_definitivo_tratamiento_historial");
	foreach ($tablas as $tabla) {
		$stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
		if (!$stmt) { return false; }
		$stmt->bind_param("s", $tabla);
		if (!$stmt->execute()) { return false; }
		$result = $stmt->get_result();
		$row = mysqli_fetch_assoc($result);
		if (!$row || (int)$row["total"] == 0) {
			return false;
		}
	}
	return true;
}

function obtenerRolUsuarioPlanDefinitivoConsulta($mysqli,$user)
{
	$rol = "";
	$stmt = $mysqli->prepare("SELECT tipo FROM usuario WHERE cod_usuario = ? LIMIT 1");
	if ($stmt) {
		$stmt->bind_param("s", $user);
		if ($stmt->execute()) {
			$result = $stmt->get_result();
			if ($row = mysqli_fetch_assoc($result)) {
				$rol = isset($row["tipo"]) ? $row["tipo"] : "";
			}
		}
	}
	return $rol;
}

function obtenerNombreUsuarioPlanDefinitivoConsulta($mysqli,$user)
{
	$nombre = "Usuario";
	$stmt = $mysqli->prepare("SELECT nombre_persona FROM persona WHERE cod_persona = ? LIMIT 1");
	if ($stmt) {
		$stmt->bind_param("s", $user);
		if ($stmt->execute()) {
			$result = $stmt->get_result();
			if ($row = mysqli_fetch_assoc($result)) {
				$nombre = mb_convert_encoding((string)$row["nombre_persona"], 'UTF-8', 'ISO-8859-1');
			}
		}
	}
	return $nombre;
}

function obtenerContextoPlanDefinitivoConsulta($mysqli,$cod_venta)
{
	$sql = "SELECT vt.cod_venta, vt.cod_clienteFK, vt.apodo, vt.fecha_venta, vt.num_factura,
		cl.ci_cliente, cl.rut_cliente, p.nombre_persona
		FROM venta vt
		INNER JOIN cliente cl ON cl.cod_cliente = vt.cod_clienteFK
		INNER JOIN persona p ON p.cod_persona = cl.cod_cliente
		WHERE vt.cod_venta = ?
		LIMIT 1";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) { return null; }
	$stmt->bind_param("s", $cod_venta);
	if (!$stmt->execute()) { return null; }
	$result = $stmt->get_result();
	if (!($row = mysqli_fetch_assoc($result))) { return null; }
	$ventaBaseDefinitiva = (string)$row["cod_venta"];
	return array(
		"venta_base_id" => $ventaBaseDefinitiva,
		"venta_actual_id" => (string)$row["cod_venta"],
		"paciente_id" => (string)$row["cod_clienteFK"],
		"cedula" => mb_convert_encoding((string)$row["ci_cliente"], 'UTF-8', 'ISO-8859-1'),
		"rut_cliente" => mb_convert_encoding((string)$row["rut_cliente"], 'UTF-8', 'ISO-8859-1'),
		"paciente" => mb_convert_encoding((string)$row["nombre_persona"], 'UTF-8', 'ISO-8859-1'),
		"apodo" => mb_convert_encoding((string)$row["apodo"], 'UTF-8', 'ISO-8859-1'),
		"num_factura" => mb_convert_encoding((string)$row["num_factura"], 'UTF-8', 'ISO-8859-1'),
		"fecha_venta" => (string)$row["fecha_venta"]
	);
}

function obtenerPlanDefinitivoActivoConsulta($mysqli,$venta_base_id)
{
	$sql = "SELECT pd.*, (SELECT nombre_persona FROM persona WHERE cod_persona = pd.doctor_cabecera_id LIMIT 1) AS doctor_nombre,
		(SELECT nombre_persona FROM persona WHERE cod_persona = pd.actualizado_por LIMIT 1) AS actualizado_nombre
		FROM plan_definitivo_tratamiento pd
		WHERE pd.venta_base_id = ? AND pd.activo = 1
		ORDER BY pd.id DESC
		LIMIT 1";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) { return null; }
	$stmt->bind_param("s", $venta_base_id);
	if (!$stmt->execute()) { return null; }
	$result = $stmt->get_result();
	if (!($row = mysqli_fetch_assoc($result))) { return null; }
	$row["doctor_nombre"] = mb_convert_encoding((string)$row["doctor_nombre"], 'UTF-8', 'ISO-8859-1');
	$row["actualizado_nombre"] = mb_convert_encoding((string)$row["actualizado_nombre"], 'UTF-8', 'ISO-8859-1');
	return $row;
}

function obtenerPlanDefinitivoActivoPacienteConsulta($mysqli,$paciente_id,$cedula)
{
	$paciente_id = trim((string)$paciente_id);
	$cedula = trim((string)$cedula);
	if ($paciente_id == "" && $cedula == "") { return null; }
	$sql = "SELECT pd.*, (SELECT nombre_persona FROM persona WHERE cod_persona = pd.doctor_cabecera_id LIMIT 1) AS doctor_nombre,
		(SELECT nombre_persona FROM persona WHERE cod_persona = pd.actualizado_por LIMIT 1) AS actualizado_nombre
		FROM plan_definitivo_tratamiento pd
		LEFT JOIN venta vb ON vb.cod_venta = pd.venta_base_id
		WHERE pd.activo = 1 AND (pd.paciente_id = ? OR pd.cedula = ?)
		ORDER BY
			CASE WHEN vb.cod_venta IS NULL THEN 1 ELSE 0 END,
			vb.fecha_venta ASC,
			vb.cod_venta ASC,
			CASE
				WHEN pd.estado = 'definido' THEN 0
				WHEN pd.estado = 'modificado' THEN 1
				WHEN pd.estado = 'pendiente_validacion' THEN 2
				WHEN pd.estado = 'borrador' THEN 3
				ELSE 4
			END,
			pd.id DESC
		LIMIT 1";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) { return null; }
	$stmt->bind_param("ss", $paciente_id, $cedula);
	if (!$stmt->execute()) { return null; }
	$result = $stmt->get_result();
	if (!($row = mysqli_fetch_assoc($result))) { return null; }
	$row["doctor_nombre"] = mb_convert_encoding((string)$row["doctor_nombre"], 'UTF-8', 'ISO-8859-1');
	$row["actualizado_nombre"] = mb_convert_encoding((string)$row["actualizado_nombre"], 'UTF-8', 'ISO-8859-1');
	return $row;
}

function obtenerPlanDefinitivoPorIdConsulta($mysqli,$plan_id)
{
	$sql = "SELECT * FROM plan_definitivo_tratamiento WHERE id = ? AND activo = 1 LIMIT 1";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) { return null; }
	$stmt->bind_param("s", $plan_id);
	if (!$stmt->execute()) { return null; }
	$result = $stmt->get_result();
	if (!($row = mysqli_fetch_assoc($result))) { return null; }
	return $row;
}

function obtenerDatosPacientePlanMadreConsulta($mysqli,$cod_cliente)
{
	$cod_cliente = trim((string)$cod_cliente);
	if ($cod_cliente == "") { return null; }
	$sql = "SELECT cl.cod_cliente, cl.ci_cliente, cl.rut_cliente, p.nombre_persona
		FROM cliente cl
		INNER JOIN persona p ON p.cod_persona = cl.cod_cliente
		WHERE cl.cod_cliente = ?
		LIMIT 1";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) { return null; }
	$stmt->bind_param("s", $cod_cliente);
	if (!$stmt->execute()) { return null; }
	$result = $stmt->get_result();
	if (!($row = mysqli_fetch_assoc($result))) { return null; }
	return array(
		"paciente_id" => (string)$row["cod_cliente"],
		"cedula" => mb_convert_encoding((string)$row["ci_cliente"], 'UTF-8', 'ISO-8859-1'),
		"rut_cliente" => mb_convert_encoding((string)$row["rut_cliente"], 'UTF-8', 'ISO-8859-1'),
		"paciente" => mb_convert_encoding((string)$row["nombre_persona"], 'UTF-8', 'ISO-8859-1')
	);
}

function obtenerPlanesMadrePacienteConsulta($mysqli,$paciente_id,$cedula)
{
	$paciente_id = trim((string)$paciente_id);
	$cedula = trim((string)$cedula);
	if ($paciente_id == "" && $cedula == "") { return array(); }
	$sql = "SELECT pd.*,
		vb.apodo AS apodo_base,
		vb.num_factura AS venta_base_numero,
		vb.fecha_venta AS venta_base_fecha,
		((SELECT COUNT(DISTINCT pi.venta_id) FROM plan_definitivo_tratamiento_items pi WHERE pi.plan_definitivo_id = pd.id AND pi.activo = 1)
			+ CASE WHEN pd.venta_base_id IS NOT NULL AND pd.venta_base_id <> ''
				AND NOT EXISTS (SELECT 1 FROM plan_definitivo_tratamiento_items pi_base WHERE pi_base.plan_definitivo_id = pd.id AND pi_base.venta_id = pd.venta_base_id AND pi_base.activo = 1 LIMIT 1)
				THEN 1 ELSE 0 END) AS ventas_asignadas,
		(SELECT COUNT(*) FROM plan_definitivo_tratamiento_items pi WHERE pi.plan_definitivo_id = pd.id AND pi.activo = 1) AS tratamientos_asignados
		FROM plan_definitivo_tratamiento pd
		LEFT JOIN venta vb ON vb.cod_venta = pd.venta_base_id
		WHERE pd.activo = 1 AND (pd.paciente_id = ? OR pd.cedula = ?)
		ORDER BY pd.fecha_creacion ASC, pd.id ASC";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) { return array(); }
	$stmt->bind_param("ss", $paciente_id, $cedula);
	if (!$stmt->execute()) { return array(); }
	$result = $stmt->get_result();
	$planes = array();
	$numero = 1;
	while ($row = mysqli_fetch_assoc($result)) {
		$apodo = mb_convert_encoding((string)$row["apodo_base"], 'UTF-8', 'ISO-8859-1');
		if (trim($apodo) == "") { $apodo = "Sin beneficiario"; }
		$row["plan_madre_numero"] = $numero;
		$row["plan_madre_apodo"] = $apodo;
		$row["plan_madre_label"] = "Plan madre #".$numero." - ".$apodo;
		$row["venta_base_numero"] = mb_convert_encoding((string)$row["venta_base_numero"], 'UTF-8', 'ISO-8859-1');
		$row["ventas_asignadas"] = (int)$row["ventas_asignadas"];
		$row["tratamientos_asignados"] = (int)$row["tratamientos_asignados"];
		$planes[] = $row;
		$numero++;
	}
	return $planes;
}

function buscarPlanMadreEnListaConsulta($planes,$plan_id)
{
	foreach ($planes as $plan) {
		if ((string)$plan["id"] == (string)$plan_id) {
			return $plan;
		}
	}
	return null;
}

function obtenerPlanMadreAsignadoVentaConsulta($mysqli,$paciente_id,$cedula,$cod_venta)
{
	$cod_venta = trim((string)$cod_venta);
	if ($cod_venta == "") { return null; }
	$sql = "SELECT pd.id, COUNT(pi.id) AS items_venta
		FROM plan_definitivo_tratamiento pd
		INNER JOIN plan_definitivo_tratamiento_items pi ON pi.plan_definitivo_id = pd.id AND pi.activo = 1
		WHERE pd.activo = 1
		AND pi.venta_id = ?
		AND (pd.paciente_id = ? OR pd.cedula = ?)
		GROUP BY pd.id
		ORDER BY pd.fecha_creacion ASC, pd.id ASC
		LIMIT 1";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) { return null; }
	$stmt->bind_param("sss", $cod_venta, $paciente_id, $cedula);
	if (!$stmt->execute()) { return null; }
	$result = $stmt->get_result();
	if (!($row = mysqli_fetch_assoc($result))) {
		$stmtBase = $mysqli->prepare("SELECT pd.id, 0 AS items_venta
			FROM plan_definitivo_tratamiento pd
			WHERE pd.activo = 1
			AND pd.venta_base_id = ?
			AND (pd.paciente_id = ? OR pd.cedula = ?)
			ORDER BY pd.fecha_creacion ASC, pd.id ASC
			LIMIT 1");
		if (!$stmtBase) { return null; }
		$stmtBase->bind_param("sss", $cod_venta, $paciente_id, $cedula);
		if (!$stmtBase->execute()) { return null; }
		$resultBase = $stmtBase->get_result();
		if (!($row = mysqli_fetch_assoc($resultBase))) { return null; }
	}
	$planes = obtenerPlanesMadrePacienteConsulta($mysqli,$paciente_id,$cedula);
	$plan = buscarPlanMadreEnListaConsulta($planes,$row["id"]);
	if (!$plan) { return null; }
	$plan["items_venta"] = (int)$row["items_venta"];
	return $plan;
}

function obtenerPlanesMadreAsignadosVentasConsulta($mysqli,$registros)
{
	$ventasPorPaciente = array();
	foreach ($registros as $registro) {
		$codVenta = isset($registro["cod_venta"]) ? trim((string)$registro["cod_venta"]) : "";
		if ($codVenta == "" || !ctype_digit($codVenta)) {
			continue;
		}
		$pacienteId = isset($registro["cod_cliente"]) ? trim((string)$registro["cod_cliente"]) : "";
		$cedula = isset($registro["ci_cliente"]) ? trim((string)$registro["ci_cliente"]) : "";
		$clavePaciente = $pacienteId."|".$cedula;
		if (!isset($ventasPorPaciente[$clavePaciente])) {
			$ventasPorPaciente[$clavePaciente] = array(
				"paciente_id" => $pacienteId,
				"cedula" => $cedula,
				"ventas" => array()
			);
		}
		$ventasPorPaciente[$clavePaciente]["ventas"][(string)((int)$codVenta)] = (int)$codVenta;
	}

	$planesPorVenta = array();
	foreach ($ventasPorPaciente as $grupo) {
		$ventasIds = array_values($grupo["ventas"]);
		if (count($ventasIds) == 0) {
			continue;
		}
		$pacienteId = $grupo["paciente_id"];
		$cedula = $grupo["cedula"];
		$planesPaciente = obtenerPlanesMadrePacienteConsulta($mysqli,$pacienteId,$cedula);
		if (count($planesPaciente) == 0) {
			continue;
		}
		$planesPorId = array();
		foreach ($planesPaciente as $planPaciente) {
			$planesPorId[(string)$planPaciente["id"]] = $planPaciente;
		}

		$ventasSql = implode(",", $ventasIds);
		$sqlItems = "SELECT pi.venta_id, pd.id, COUNT(pi.id) AS items_venta
			FROM plan_definitivo_tratamiento pd
			INNER JOIN plan_definitivo_tratamiento_items pi ON pi.plan_definitivo_id = pd.id AND pi.activo = 1
			WHERE pd.activo = 1
			AND pi.venta_id IN (".$ventasSql.")
			AND (pd.paciente_id = ? OR pd.cedula = ?)
			GROUP BY pi.venta_id, pd.id
			ORDER BY pi.venta_id ASC, pd.fecha_creacion ASC, pd.id ASC";
		$stmtItems = $mysqli->prepare($sqlItems);
		if ($stmtItems) {
			$stmtItems->bind_param("ss", $pacienteId, $cedula);
			if ($stmtItems->execute()) {
				$resultItems = $stmtItems->get_result();
				while ($rowItem = mysqli_fetch_assoc($resultItems)) {
					$ventaId = (string)((int)$rowItem["venta_id"]);
					if (isset($planesPorVenta[$ventaId])) {
						continue;
					}
					$planId = (string)$rowItem["id"];
					if (!isset($planesPorId[$planId])) {
						continue;
					}
					$plan = $planesPorId[$planId];
					$plan["items_venta"] = (int)$rowItem["items_venta"];
					$planesPorVenta[$ventaId] = $plan;
				}
			}
		}

		$ventasBasePendientes = array();
		foreach ($ventasIds as $ventaId) {
			if (!isset($planesPorVenta[(string)$ventaId])) {
				$ventasBasePendientes[] = $ventaId;
			}
		}
		if (count($ventasBasePendientes) == 0) {
			continue;
		}
		$sqlBase = "SELECT pd.venta_base_id AS venta_id, pd.id, 0 AS items_venta
			FROM plan_definitivo_tratamiento pd
			WHERE pd.activo = 1
			AND pd.venta_base_id IN (".implode(",", $ventasBasePendientes).")
			AND (pd.paciente_id = ? OR pd.cedula = ?)
			ORDER BY pd.venta_base_id ASC, pd.fecha_creacion ASC, pd.id ASC";
		$stmtBase = $mysqli->prepare($sqlBase);
		if (!$stmtBase) {
			continue;
		}
		$stmtBase->bind_param("ss", $pacienteId, $cedula);
		if (!$stmtBase->execute()) {
			continue;
		}
		$resultBase = $stmtBase->get_result();
		while ($rowBase = mysqli_fetch_assoc($resultBase)) {
			$ventaId = (string)((int)$rowBase["venta_id"]);
			if (isset($planesPorVenta[$ventaId])) {
				continue;
			}
			$planId = (string)$rowBase["id"];
			if (!isset($planesPorId[$planId])) {
				continue;
			}
			$plan = $planesPorId[$planId];
			$plan["items_venta"] = (int)$rowBase["items_venta"];
			$planesPorVenta[$ventaId] = $plan;
		}
	}
	return $planesPorVenta;
}

function obtenerPlanMadreAsignadoVentaRowConsulta($mysqli,$paciente_id,$cedula,$cod_venta)
{
	$info = obtenerPlanMadreAsignadoVentaConsulta($mysqli,$paciente_id,$cedula,$cod_venta);
	if (!$info) { return null; }
	$plan = obtenerPlanDefinitivoPorIdConsulta($mysqli,$info["id"]);
	if (!$plan) { return null; }
	foreach ($info as $clave => $valor) {
		$plan[$clave] = $valor;
	}
	$doctor = "";
	$actualizado = "";
	$stmt = $mysqli->prepare("SELECT
		(SELECT nombre_persona FROM persona WHERE cod_persona = ? LIMIT 1) AS doctor_nombre,
		(SELECT nombre_persona FROM persona WHERE cod_persona = ? LIMIT 1) AS actualizado_nombre");
	if ($stmt) {
		$stmt->bind_param("ss", $plan["doctor_cabecera_id"], $plan["actualizado_por"]);
		if ($stmt->execute()) {
			$row = mysqli_fetch_assoc($stmt->get_result());
			if ($row) {
				$doctor = mb_convert_encoding((string)$row["doctor_nombre"], 'UTF-8', 'ISO-8859-1');
				$actualizado = mb_convert_encoding((string)$row["actualizado_nombre"], 'UTF-8', 'ISO-8859-1');
			}
		}
	}
	$plan["doctor_nombre"] = $doctor;
	$plan["actualizado_nombre"] = $actualizado;
	return $plan;
}

function normalizarVentaBasePlanDefinitivoConsulta($mysqli,&$plan,$ventaBaseDefinitiva)
{
	$ventaBaseDefinitiva = trim((string)$ventaBaseDefinitiva);
	if (!$plan || $ventaBaseDefinitiva == "" || (string)$plan["venta_base_id"] == $ventaBaseDefinitiva) {
		return;
	}
	$planId = (string)$plan["id"];
	$stmtPlan = $mysqli->prepare("UPDATE plan_definitivo_tratamiento SET venta_base_id = ? WHERE id = ? AND activo = 1 LIMIT 1");
	if ($stmtPlan) {
		$stmtPlan->bind_param("ss", $ventaBaseDefinitiva, $planId);
		$stmtPlan->execute();
	}
	$stmtItems = $mysqli->prepare("UPDATE plan_definitivo_tratamiento_items SET origen = CASE WHEN venta_id = ? THEN 'plan_principal' ELSE 'venta_anexada' END WHERE plan_definitivo_id = ? AND activo = 1");
	if ($stmtItems) {
		$stmtItems->bind_param("ss", $ventaBaseDefinitiva, $planId);
		$stmtItems->execute();
	}
	$plan["venta_base_id"] = $ventaBaseDefinitiva;
}

function obtenerFechaVentaBasePlanDefinitivoConsulta($mysqli,$ventaBaseId)
{
	$ventaBaseId = trim((string)$ventaBaseId);
	if ($ventaBaseId == "") { return ""; }
	$stmt = $mysqli->prepare("SELECT fecha_venta FROM venta WHERE cod_venta = ? LIMIT 1");
	if (!$stmt) { return ""; }
	$stmt->bind_param("s", $ventaBaseId);
	if (!$stmt->execute()) { return ""; }
	$result = $stmt->get_result();
	if ($row = mysqli_fetch_assoc($result)) {
		return (string)$row["fecha_venta"];
	}
	return "";
}

function obtenerFechaVentaMasAntiguaItemsPlanConsulta($mysqli,$planId)
{
	$planId = trim((string)$planId);
	if ($planId == "") { return ""; }
	$stmt = $mysqli->prepare("SELECT MIN(v.fecha_venta) AS fecha_venta FROM plan_definitivo_tratamiento_items i INNER JOIN venta v ON v.cod_venta = i.venta_id WHERE i.plan_definitivo_id = ? AND i.activo = 1");
	if (!$stmt) { return ""; }
	$stmt->bind_param("s", $planId);
	if (!$stmt->execute()) { return ""; }
	$result = $stmt->get_result();
	if ($row = mysqli_fetch_assoc($result)) {
		return (string)$row["fecha_venta"];
	}
	return "";
}

function registrarHistorialPlanDefinitivoConsulta($mysqli,$plan_id,$version,$accion,$descripcion,$valor_anterior,$valor_nuevo,$motivo,$usuario_id,$rol)
{
	$accion = mb_convert_encoding((string)$accion, 'ISO-8859-1', 'UTF-8');
	$descripcion = mb_convert_encoding((string)$descripcion, 'ISO-8859-1', 'UTF-8');
	$valor_anterior = mb_convert_encoding((string)$valor_anterior, 'ISO-8859-1', 'UTF-8');
	$valor_nuevo = mb_convert_encoding((string)$valor_nuevo, 'ISO-8859-1', 'UTF-8');
	$motivo = mb_convert_encoding((string)$motivo, 'ISO-8859-1', 'UTF-8');
	$rol = mb_convert_encoding((string)$rol, 'ISO-8859-1', 'UTF-8');
	$sql = "INSERT INTO plan_definitivo_tratamiento_historial
		(plan_definitivo_id, version, accion, descripcion, valor_anterior, valor_nuevo, motivo, usuario_id, rol, fecha_hora)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) { return false; }
	$stmt->bind_param("sisssssss", $plan_id, $version, $accion, $descripcion, $valor_anterior, $valor_nuevo, $motivo, $usuario_id, $rol);
	return $stmt->execute();
}

function planDefinitivoRequiereMotivoConsulta($plan)
{
	$estado = strtolower(trim((string)$plan["estado"]));
	return ($estado == "definido" || $estado == "modificado");
}

function versionarCambioPlanDefinitivoConsulta($mysqli,$plan,$motivo,$user)
{
	$version = (int)$plan["version_actual"];
	if (planDefinitivoRequiereMotivoConsulta($plan)) {
		if (trim((string)$motivo) == "") {
			return array("ok" => false, "mensaje" => "Debe indicar el motivo de modificacion del plan madre.");
		}
		$version++;
		$estado = "modificado";
		$stmt = $mysqli->prepare("UPDATE plan_definitivo_tratamiento SET estado = ?, version_actual = ?, fecha_actualizacion = NOW(), actualizado_por = ? WHERE id = ? LIMIT 1");
		if (!$stmt) {
			return array("ok" => false, "mensaje" => "No se pudo preparar la actualizacion del plan.");
		}
		$stmt->bind_param("siss", $estado, $version, $user, $plan["id"]);
		if (!$stmt->execute()) {
			return array("ok" => false, "mensaje" => "No se pudo versionar el plan madre.");
		}
		return array("ok" => true, "version" => $version, "estado" => $estado);
	}
	$stmt = $mysqli->prepare("UPDATE plan_definitivo_tratamiento SET fecha_actualizacion = NOW(), actualizado_por = ? WHERE id = ? LIMIT 1");
	if ($stmt) {
		$stmt->bind_param("ss", $user, $plan["id"]);
		$stmt->execute();
	}
	return array("ok" => true, "version" => $version, "estado" => $plan["estado"]);
}

function ProductoTemporalidadSelectSqlConsulta($mysqli, $alias = "pr")
{
	$campos = array(
		"usa_temporalidad" => "0",
		"temporalidad_tipo" => "''",
		"temporalidad_intervalo_recomendado" => "20",
		"temporalidad_intervalo_minimo" => "15",
		"temporalidad_intervalo_maximo" => "40",
		"temporalidad_sesiones_estimadas" => "NULL",
		"temporalidad_duracion_sillon" => "NULL",
		"temporalidad_observacion" => "''"
	);
	$partes = array();
	foreach ($campos as $campo => $default) {
		$partes[] = (columnaExisteConsulta($mysqli, "producto", $campo) ? "IFNULL(".$alias.".".$campo.", ".$default.")" : $default)." AS ".$campo;
	}
	return implode(", ", $partes);
}

function FechaPlanificadaTratamientoSelectSqlConsulta($mysqli, $detalleAlias = "dtv")
{
	if (!tablaExisteConsulta($mysqli, "agenda") || !columnaExisteConsulta($mysqli, "agenda", "fecha") || !columnaExisteConsulta($mysqli, "agenda", "cod_detalle_ventaFK")) {
		return "NULL AS fecha_planificada";
	}
	$condicionDetalle = "a.cod_detalle_ventaFK = ".$detalleAlias.".cod_detalle";
	if (tablaExisteConsulta($mysqli, "agenda_tratamientos") && columnaExisteConsulta($mysqli, "agenda_tratamientos", "cod_detalle_ventaFK")) {
		$condicionEstadoTratamiento = columnaExisteConsulta($mysqli, "agenda_tratamientos", "estado") ? " AND IFNULL(at.estado,'') <> 'cancelado'" : "";
		$condicionDetalle = "(".$condicionDetalle." OR EXISTS (SELECT 1 FROM agenda_tratamientos at WHERE at.id_agenda = a.id_agenda AND at.cod_detalle_ventaFK = ".$detalleAlias.".cod_detalle".$condicionEstadoTratamiento."))";
	}
	$condicionEstado = columnaExisteConsulta($mysqli, "agenda", "estado") ? " AND UPPER(IFNULL(a.estado,'')) NOT IN ('CANCELADO','CANCELADA','ANULADO','ANULADA')" : "";
	return "(SELECT MIN(a.fecha) FROM agenda a WHERE ".$condicionDetalle.$condicionEstado.") AS fecha_planificada";
}

function FechaEvolucionTratamientoSelectSqlConsulta($mysqli, $detalleAlias = "dtv")
{
	if (!tablaExisteConsulta($mysqli, "evoluciontratamiento") || !columnaExisteConsulta($mysqli, "evoluciontratamiento", "fecha") || !columnaExisteConsulta($mysqli, "evoluciontratamiento", "cod_detalle_venta")) {
		return "NULL AS fecha_inicio_tratamiento, NULL AS fecha_ultima_evolucion";
	}
	return "(SELECT MIN(et.fecha) FROM evoluciontratamiento et WHERE et.cod_detalle_venta = ".$detalleAlias.".cod_detalle) AS fecha_inicio_tratamiento,
		(SELECT MAX(et.fecha) FROM evoluciontratamiento et WHERE et.cod_detalle_venta = ".$detalleAlias.".cod_detalle) AS fecha_ultima_evolucion";
}

function ProductoClinicoWhereSqlConsulta($productoAlias = "pr")
{
	$alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$productoAlias);
	if ($alias == "") { $alias = "pr"; }
	return " AND UPPER(TRIM(".$alias.".nombre_producto)) NOT IN ('CREDITO','INTERES','GASTO ADMINISTRATIVO','CORRETAJE')";
}

/**
 * Mantiene en Consulta la misma regla efectiva del modulo de laboratorio:
 * el producto puede sobrescribir la configuracion heredada de su categoria.
 */
function ProductoLaboratorioSelectSqlConsulta($mysqli, $productoAlias = "pr", $categoriaAlias = "ca")
{
	$productoAlias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$productoAlias);
	$categoriaAlias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$categoriaAlias);
	if ($productoAlias == "") { $productoAlias = "pr"; }
	if ($categoriaAlias == "") { $categoriaAlias = "ca"; }
	$productoRequiere = columnaExisteConsulta($mysqli, "producto", "requiere_laboratorio")
		? $productoAlias.".requiere_laboratorio" : "NULL";
	$categoriaRequiere = columnaExisteConsulta($mysqli, "categoria", "requiere_laboratorio")
		? $categoriaAlias.".requiere_laboratorio" : "0";
	return "COALESCE(".$categoriaAlias.".descripcion,'') AS categoria_laboratorio, "
		."COALESCE(".$productoRequiere.",".$categoriaRequiere.",0) AS requiere_laboratorio_efectivo";
}

/**
 * Expone solamente la existencia de un seguimiento para rotular correctamente
 * la accion inicial. El detalle completo sigue consultandose bajo demanda y
 * con las autorizaciones del modulo de laboratorio.
 */
function EstadoLaboratorioSelectSqlConsulta($mysqli, $detalleAlias = "dtv")
{
	$detalleAlias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$detalleAlias);
	if ($detalleAlias == "") { $detalleAlias = "dtv"; }
	$trabajoActivo = tablaExisteConsulta($mysqli, "trabajo_laboratorio")
		? "EXISTS(SELECT 1 FROM trabajo_laboratorio tla WHERE tla.cod_detalle_activo_unico=".$detalleAlias.".cod_detalle)"
		: "0";
	$antecedenteHistorico = tablaExisteConsulta($mysqli, "trabajo_laboratorio_historico")
		? "EXISTS(SELECT 1 FROM trabajo_laboratorio_historico tlh WHERE tlh.cod_detalle_ventaFK=".$detalleAlias.".cod_detalle)"
		: "0";
	return $trabajoActivo." AS tiene_trabajo_laboratorio_activo, "
		.$antecedenteHistorico." AS tiene_antecedente_laboratorio_historico";
}

function EsCategoriaProtesisLaboratorioConsulta($categoria, $requiereLaboratorio)
{
	if ((int)$requiereLaboratorio !== 1) { return false; }
	$texto = trim((string)$categoria);
	if ($texto == "") { return false; }
	if (!mb_check_encoding($texto, 'UTF-8')) {
		$texto = mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
	}
	$texto = mb_strtoupper($texto, 'UTF-8');
	$texto = strtr($texto, array(
		'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U'
	));
	return $texto === "PROTESIS";
}

function obtenerTratamientosVentaPlanConsulta($mysqli,$cod_venta)
{
	$cod_venta = mysqli_real_escape_string($mysqli, $cod_venta);
	$selectRiesgo = ProductoRiesgoFinancieroSelectSql($mysqli, "pr");
	$selectTemporalidad = ProductoTemporalidadSelectSqlConsulta($mysqli, "pr");
	$selectFechaPlanificada = FechaPlanificadaTratamientoSelectSqlConsulta($mysqli, "dtv");
	$selectFechaEvolucion = FechaEvolucionTratamientoSelectSqlConsulta($mysqli, "dtv");
	$selectLaboratorio = ProductoLaboratorioSelectSqlConsulta($mysqli, "pr", "ca");
	$selectEstadoLaboratorio = EstadoLaboratorioSelectSqlConsulta($mysqli, "dtv");
	$selectAlcanceOdontologico = columnaExisteConsulta($mysqli, "producto", "alcance_odontologico")
		? "pr.alcance_odontologico"
		: "'no_requiere' AS alcance_odontologico";
	$sql= "select dtv.descripcion, pr.cod_producto, dtv.cantidad_detalle, pr.nombre_producto, pr.precio_producto, vt.fecha_venta,
		dtv.cod_detalle, dtv.cod_ventaFK as venta_id, estado_tratamiento, progreso_porcentaje, dtv.estado, ".$selectAlcanceOdontologico.", ".$selectLaboratorio.", ".$selectEstadoLaboratorio.", ".$selectRiesgo.", ".$selectTemporalidad.", ".$selectFechaPlanificada.", ".$selectFechaEvolucion."
		from producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
		inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
		left join categoria ca on ca.cod_categoria=pr.cod_categoriaFK
		where dtv.cod_ventaFK='$cod_venta'
		".ProductoClinicoWhereSqlConsulta("pr")."
		order by dtv.cod_detalle asc";

	$stmt = $mysqli->prepare($sql);
	if (!$stmt || !$stmt->execute()) {
		echo telar_trigger_error('The query execution failed; MySQL said ('.$mysqli->errno.') '.$mysqli->error, E_USER_ERROR);
		exit;
	}

	$result = $stmt->get_result();
	$tratamientos = array();
	$ordenNatural = 0;
	while ($valor = mysqli_fetch_assoc($result)) {
		$descripcion = mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
		$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');
		$cod_detalle = mb_convert_encoding((string)($valor['cod_detalle']), 'UTF-8', 'ISO-8859-1');
		$cod_producto = mb_convert_encoding((string)($valor['cod_producto']), 'UTF-8', 'ISO-8859-1');
		$cantidad_detalle = mb_convert_encoding((string)($valor['cantidad_detalle']), 'UTF-8', 'ISO-8859-1');
		$estado_tratamiento = mb_convert_encoding((string)($valor['estado_tratamiento']), 'UTF-8', 'ISO-8859-1');
		$progreso_porcentaje = mb_convert_encoding((string)($valor['progreso_porcentaje']), 'UTF-8', 'ISO-8859-1');
		$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		$nivel_riesgo_financiero = isset($valor['nivel_riesgo_financiero']) ? $valor['nivel_riesgo_financiero'] : 1;
		$precio_producto = isset($valor['precio_producto']) ? (float)$valor['precio_producto'] : 0;
		$progreso_numero = normalizarPorcentajePlanTratamientoConsulta($progreso_porcentaje);
		$estadoClase = normalizarEstadoPlanTratamientoConsulta($estado, $estado_tratamiento, $progreso_numero);
		$estadoTexto = textoEstadoPlanTratamientoConsulta($estadoClase);
		$grupoPlan = grupoPlanTratamientoConsulta($estadoClase, $progreso_numero);
		$nivel_riesgo_financiero = ProductoRiesgoFinancieroNormalizar($nivel_riesgo_financiero);
		$alcance_odontologico = isset($valor["alcance_odontologico"]) ? mb_convert_encoding((string)$valor["alcance_odontologico"], 'UTF-8', 'ISO-8859-1') : "no_requiere";
		$temporalidad_tipo = isset($valor["temporalidad_tipo"]) ? mb_convert_encoding((string)$valor["temporalidad_tipo"], 'UTF-8', 'ISO-8859-1') : "";
		$temporalidad_observacion = isset($valor["temporalidad_observacion"]) ? mb_convert_encoding((string)$valor["temporalidad_observacion"], 'UTF-8', 'ISO-8859-1') : "";
		$categoria_laboratorio = isset($valor["categoria_laboratorio"]) ? mb_convert_encoding((string)$valor["categoria_laboratorio"], 'UTF-8', 'ISO-8859-1') : "";
		$requiere_laboratorio = isset($valor["requiere_laboratorio_efectivo"]) ? (int)$valor["requiere_laboratorio_efectivo"] : 0;
		$ubicacion_odontograma = obtenerUbicacionOdontogramaDetalleConsulta($mysqli, $cod_detalle, $alcance_odontologico);
		$ordenNatural++;

		$tratamientos[] = array(
			"descripcion" => $descripcion,
			"nombre_producto" => $nombre_producto,
			"cod_detalle" => $cod_detalle,
			"cod_producto" => $cod_producto,
			"venta_id" => (string)$valor["venta_id"],
			"fecha_venta" => isset($valor["fecha_venta"]) ? (string)$valor["fecha_venta"] : "",
			"cantidad_detalle" => $cantidad_detalle,
			"estado_tratamiento" => $estado_tratamiento,
			"estado" => $estado,
			"estado_clase" => $estadoClase,
			"estado_texto" => $estadoTexto,
			"avance" => $progreso_numero,
			"nivel_riesgo_financiero" => $nivel_riesgo_financiero,
			"alcance_odontologico" => $alcance_odontologico,
			"ubicacion_odontograma" => $ubicacion_odontograma,
			"categoria_laboratorio" => $categoria_laboratorio,
			"requiere_laboratorio" => $requiere_laboratorio,
			"es_protesis_laboratorio" => EsCategoriaProtesisLaboratorioConsulta($categoria_laboratorio, $requiere_laboratorio),
			"tiene_trabajo_laboratorio_activo" => !empty($valor["tiene_trabajo_laboratorio_activo"]),
			"tiene_antecedente_laboratorio_historico" => !empty($valor["tiene_antecedente_laboratorio_historico"]),
			"riesgo_orden" => $nivel_riesgo_financiero,
			"precio_producto" => $precio_producto,
			"usa_temporalidad" => isset($valor["usa_temporalidad"]) ? (int)$valor["usa_temporalidad"] : 0,
			"temporalidad_tipo" => $temporalidad_tipo,
			"temporalidad_intervalo_recomendado" => isset($valor["temporalidad_intervalo_recomendado"]) ? (int)$valor["temporalidad_intervalo_recomendado"] : 20,
			"temporalidad_intervalo_minimo" => isset($valor["temporalidad_intervalo_minimo"]) ? (int)$valor["temporalidad_intervalo_minimo"] : 15,
			"temporalidad_intervalo_maximo" => isset($valor["temporalidad_intervalo_maximo"]) ? (int)$valor["temporalidad_intervalo_maximo"] : 40,
			"temporalidad_sesiones_estimadas" => isset($valor["temporalidad_sesiones_estimadas"]) ? (int)$valor["temporalidad_sesiones_estimadas"] : 0,
			"temporalidad_duracion_sillon" => isset($valor["temporalidad_duracion_sillon"]) ? (int)$valor["temporalidad_duracion_sillon"] : 0,
			"temporalidad_observacion" => $temporalidad_observacion,
			"fecha_planificada" => isset($valor["fecha_planificada"]) ? (string)$valor["fecha_planificada"] : "",
			"fecha_inicio_tratamiento" => isset($valor["fecha_inicio_tratamiento"]) ? (string)$valor["fecha_inicio_tratamiento"] : "",
			"fecha_ultima_evolucion" => isset($valor["fecha_ultima_evolucion"]) ? (string)$valor["fecha_ultima_evolucion"] : "",
			"orden_natural" => $ordenNatural,
			"grupo_plan" => $grupoPlan
		);
	}
	return $tratamientos;
}

function aplanarGruposPlanSugeridoConsulta($gruposPlan)
{
	$items = array();
	foreach (array("continuar", "siguientes", "finalizados") as $grupo) {
		if (!isset($gruposPlan[$grupo])) { continue; }
		foreach ($gruposPlan[$grupo] as $tratamiento) {
			$items[] = $tratamiento;
		}
	}
	return $items;
}

function nivelToValue($nivel)
{
	$texto = strtolower(trim((string)$nivel));
	if ($texto == "" || $texto == "sin nivel" || $texto == "null") {
		return 0;
	}
	if (is_numeric($texto)) {
		$valor = (int)$texto;
		return ($valor >= 1 && $valor <= 5) ? $valor : 0;
	}
	if (preg_match('/n\s*([1-5])/', $texto, $m)) {
		return (int)$m[1];
	}
	if (preg_match('/\b([1-5])\b/', $texto, $m)) {
		return (int)$m[1];
	}
	return 0;
}

function planRiskItemValueConsulta($item)
{
	$nivel = isset($item["nivel_riesgo_financiero"]) ? $item["nivel_riesgo_financiero"] : (isset($item["nivel_riesgo_snapshot"]) ? $item["nivel_riesgo_snapshot"] : "");
	return nivelToValue($nivel);
}

function planRiskOrdenConsulta($item)
{
	if (isset($item["orden"])) { return (float)$item["orden"]; }
	if (isset($item["orden_natural"])) { return (float)$item["orden_natural"]; }
	if (isset($item["cod_detalle"])) { return (float)$item["cod_detalle"]; }
	if (isset($item["detalle_venta_id"])) { return (float)$item["detalle_venta_id"]; }
	return 0;
}

function normalizarFechaPlanConsulta($fecha)
{
	$fecha = trim((string)$fecha);
	if ($fecha == "" || $fecha == "0000-00-00" || $fecha == "0000-00-00 00:00:00") {
		return "";
	}
	if (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})/', $fecha, $m)) {
		$fecha = $m[3]."-".$m[2]."-".$m[1];
	}
	$timestamp = strtotime($fecha);
	return $timestamp ? date("Y-m-d", $timestamp) : "";
}

function sumarDiasFechaPlanConsulta($fecha,$dias)
{
	$fecha = normalizarFechaPlanConsulta($fecha);
	if ($fecha == "") { $fecha = date("Y-m-d"); }
	$dias = (int)$dias;
	$timestamp = strtotime($fecha." ".($dias >= 0 ? "+" : "").$dias." days");
	return $timestamp ? date("Y-m-d", $timestamp) : $fecha;
}

function diasEntreFechasPlanConsulta($desde,$hasta)
{
	$desde = normalizarFechaPlanConsulta($desde);
	$hasta = normalizarFechaPlanConsulta($hasta);
	if ($desde == "" || $hasta == "") { return 0; }
	try {
		$d1 = new DateTime($desde);
		$d2 = new DateTime($hasta);
		return (int)$d1->diff($d2)->format("%r%a");
	} catch (Exception $e) {
		return 0;
	}
}

function formatearFechaPlanConsulta($fecha)
{
	$fecha = normalizarFechaPlanConsulta($fecha);
	return $fecha != "" ? date("d/m/Y", strtotime($fecha)) : "";
}

function nombreMesCortoPlanConsulta($mes)
{
	$mes = (int)$mes;
	$meses = array(
		1 => "Ene",
		2 => "Feb",
		3 => "Mar",
		4 => "Abr",
		5 => "May",
		6 => "Jun",
		7 => "Jul",
		8 => "Ago",
		9 => "Set",
		10 => "Oct",
		11 => "Nov",
		12 => "Dic"
	);
	return isset($meses[$mes]) ? $meses[$mes] : "";
}

function formatearMesPlanConsulta($fecha)
{
	$fecha = normalizarFechaPlanConsulta($fecha);
	if ($fecha == "") { return ""; }
	$timestamp = strtotime($fecha);
	if (!$timestamp) { return ""; }
	return nombreMesCortoPlanConsulta((int)date("n", $timestamp))." ".date("Y", $timestamp);
}

function ticksMesesPlanRiskConsulta($fechaBase,$maxDays)
{
	$fechaBase = normalizarFechaPlanConsulta($fechaBase);
	$maxDays = max(0, (int)$maxDays);
	if ($fechaBase == "") {
		$ticks = array();
		$salto = $maxDays > 240 ? 60 : 30;
		for ($dia = 0; $dia <= $maxDays; $dia += $salto) {
			$ticks[] = array("dia" => $dia, "mes" => "Mes ".(int)round($dia / 30), "detalle" => "D".$dia);
		}
		if (count($ticks) == 0 || end($ticks)["dia"] != $maxDays) {
			$ticks[] = array("dia" => $maxDays, "mes" => "Mes ".(int)round($maxDays / 30), "detalle" => "D".$maxDays);
		}
		return $ticks;
	}
	try {
		$base = new DateTime($fechaBase);
		$fin = clone $base;
		$fin->modify("+".$maxDays." days");
		$saltoMeses = $maxDays > 420 ? 3 : ($maxDays > 210 ? 2 : 1);
		$ticks = array();
		$cursor = clone $base;
		while ($cursor <= $fin) {
			$dia = max(0, (int)$base->diff($cursor)->format("%a"));
			$ticks[] = array(
				"dia" => $dia,
				"mes" => formatearMesPlanConsulta($cursor->format("Y-m-d")),
				"detalle" => "D".$dia." ".$cursor->format("d/m")
			);
			$cursor->modify("+".$saltoMeses." month");
		}
		if (count($ticks) == 0 || end($ticks)["dia"] < $maxDays) {
			$ticks[] = array(
				"dia" => $maxDays,
				"mes" => formatearMesPlanConsulta($fin->format("Y-m-d")),
				"detalle" => "D".$maxDays." ".$fin->format("d/m")
			);
		}
		return $ticks;
	} catch (Exception $e) {
		return array(array("dia" => 0, "mes" => "Mes 0", "detalle" => "D0"), array("dia" => $maxDays, "mes" => "Mes ".(int)round($maxDays / 30), "detalle" => "D".$maxDays));
	}
}

function ventasDesdeItemsPlanConsulta($items)
{
	$ventas = array();
	foreach ($items as $item) {
		$venta = "";
		if (isset($item["venta_id"])) { $venta = (string)$item["venta_id"]; }
		elseif (isset($item["cod_ventaFK"])) { $venta = (string)$item["cod_ventaFK"]; }
		elseif (isset($item["cod_venta"])) { $venta = (string)$item["cod_venta"]; }
		$venta = preg_replace('/[^0-9]/', '', $venta);
		if ($venta != "") {
			$ventas[$venta] = (int)$venta;
		}
	}
	return array_values($ventas);
}

function formatoMontoCortoPlanRiskConsulta($monto)
{
	$monto = (float)$monto;
	if ($monto >= 1000000) {
		$valor = $monto / 1000000;
		return rtrim(rtrim(number_format($valor, 1, ",", "."), "0"), ",")."M";
	}
	if ($monto >= 1000) {
		return number_format(round($monto / 1000), 0, ",", ".")."k";
	}
	return number_format($monto, 0, ",", ".");
}

function obtenerPagosMensualesPlanConsulta($mysqli,$ventas,$fechaBase)
{
	if (!tablaExisteConsulta($mysqli, "pago") || !tablaExisteConsulta($mysqli, "credito")) {
		return array();
	}
	$ventasLimpias = array();
	foreach ($ventas as $venta) {
		$venta = (int)$venta;
		if ($venta > 0) { $ventasLimpias[$venta] = $venta; }
	}
	if (count($ventasLimpias) == 0) {
		return array();
	}
	$fechaBase = normalizarFechaPlanConsulta($fechaBase);
	$listaVentas = implode(",", array_values($ventasLimpias));
	$campoVentaPago = columnaExisteConsulta($mysqli, "pago", "cod_venta_fk") ? "NULLIF(p.cod_venta_fk,0)" : "NULL";
	$campoTipoPago = columnaExisteConsulta($mysqli, "pago", "tipo") ? "p.tipo" : "''";
	$sql = "
		SELECT
			DATE_FORMAT(p.Fecha, '%Y-%m-01') AS mes_fecha,
			SUM(IFNULL(p.Monto,0)) AS monto_pagado,
			COUNT(DISTINCT p.cod_creditoFK) AS cuotas_pagadas,
			COUNT(*) AS pagos_realizados,
			GROUP_CONCAT(DISTINCT COALESCE(".$campoVentaPago.", cr.cod_venta) ORDER BY COALESCE(".$campoVentaPago.", cr.cod_venta) SEPARATOR ', ') AS ventas,
			SUM(CASE WHEN ".$campoTipoPago." = 'Interes' THEN IFNULL(p.Monto,0) ELSE 0 END) AS monto_interes
		FROM pago p
		LEFT JOIN credito cr ON cr.idcredito = p.cod_creditoFK
		WHERE COALESCE(".$campoVentaPago.", cr.cod_venta) IN (".$listaVentas.")
			AND IFNULL(p.Monto,0) > 0
			AND p.Fecha IS NOT NULL
			AND p.Fecha >= '1000-01-01'
		GROUP BY DATE_FORMAT(p.Fecha, '%Y-%m-01')
		ORDER BY mes_fecha ASC";
	$result = $mysqli->query($sql);
	if (!$result) {
		return array();
	}
	$pagos = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$mesFecha = normalizarFechaPlanConsulta($row["mes_fecha"]);
		if ($mesFecha == "") { continue; }
		$dayOffset = $fechaBase != "" ? max(0, diasEntreFechasPlanConsulta($fechaBase,$mesFecha)) : 0;
		$monto = isset($row["monto_pagado"]) ? (float)$row["monto_pagado"] : 0;
		$interes = isset($row["monto_interes"]) ? (float)$row["monto_interes"] : 0;
		$pagos[] = array(
			"fecha" => $mesFecha,
			"mes" => formatearMesPlanConsulta($mesFecha),
			"dayOffset" => $dayOffset,
			"monto" => $monto,
			"montoTexto" => number_format($monto, 0, ",", ".")." Gs.",
			"montoCorto" => formatoMontoCortoPlanRiskConsulta($monto),
			"montoInteres" => $interes,
			"cuotas" => isset($row["cuotas_pagadas"]) ? (int)$row["cuotas_pagadas"] : 0,
			"pagos" => isset($row["pagos_realizados"]) ? (int)$row["pagos_realizados"] : 0,
			"ventas" => isset($row["ventas"]) ? (string)$row["ventas"] : ""
		);
	}
	return $pagos;
}

function etiquetaTemporalidadTipoPlanConsulta($tipo)
{
	$tipo = strtolower(trim((string)$tipo));
	if ($tipo == "evolutivo") { return "Evolutivo"; }
	if ($tipo == "recurrente") { return "Recurrente"; }
	if ($tipo == "control_periodico") { return "Control periodico"; }
	if ($tipo == "unico") { return "Unico"; }
	return "Sin configurar";
}

function enteroTemporalidadPlanConsulta($item,$campo,$default)
{
	if (!isset($item[$campo]) || trim((string)$item[$campo]) == "" || !is_numeric($item[$campo])) {
		return (int)$default;
	}
	return (int)$item[$campo];
}

function obtenerTemporalidadItemPlanConsulta($item)
{
	$configurada = isset($item["usa_temporalidad"]) && (int)$item["usa_temporalidad"] == 1;
	$intervalo = enteroTemporalidadPlanConsulta($item, "temporalidad_intervalo_recomendado", 20);
	$minimo = enteroTemporalidadPlanConsulta($item, "temporalidad_intervalo_minimo", 15);
	$maximo = enteroTemporalidadPlanConsulta($item, "temporalidad_intervalo_maximo", 40);
	if ($intervalo <= 0) { $intervalo = 20; }
	if ($minimo <= 0) { $minimo = max(1, $intervalo - 5); }
	if ($maximo <= 0) { $maximo = max($intervalo, $minimo + 20); }
	if ($maximo < $minimo) { $maximo = $minimo; }
	$tipo = isset($item["temporalidad_tipo"]) ? (string)$item["temporalidad_tipo"] : "";
	$observacion = isset($item["temporalidad_observacion"]) ? (string)$item["temporalidad_observacion"] : "";
	return array(
		"configurada" => $configurada,
		"tipo" => $tipo,
		"tipo_label" => etiquetaTemporalidadTipoPlanConsulta($tipo),
		"intervalo" => $intervalo,
		"minimo" => $minimo,
		"maximo" => $maximo,
		"sesiones" => enteroTemporalidadPlanConsulta($item, "temporalidad_sesiones_estimadas", 0),
		"duracion" => enteroTemporalidadPlanConsulta($item, "temporalidad_duracion_sillon", 0),
		"observacion" => $observacion
	);
}

function detalleIdPlanRiskConsulta($item)
{
	if (isset($item["cod_detalle"])) { return (string)$item["cod_detalle"]; }
	if (isset($item["detalle_venta_id"])) { return (string)$item["detalle_venta_id"]; }
	return "";
}

function fechaInicioCurvaPlanConsulta($items,$fechaInicio)
{
	$base = normalizarFechaPlanConsulta($fechaInicio);
	if ($base == "") { $base = date("Y-m-d"); }
	return $base;
}

function estadoTemporalPlanConsulta($temporalidad,$fechaPlanificada,$paso,$diasDesdeAnterior,$fechaReal = "")
{
	if (!$temporalidad["configurada"]) { return array("label" => "Sin configuracion", "clase" => "sin-configuracion"); }
	if ($fechaReal != "") {
		if ($paso <= 1) { return array("label" => "Inicio real", "clase" => "real"); }
		if ($diasDesdeAnterior < $temporalidad["minimo"]) { return array("label" => "Adelantado", "clase" => "adelantado"); }
		if ($diasDesdeAnterior > $temporalidad["maximo"]) { return array("label" => "Atrasado", "clase" => "atrasado"); }
		return array("label" => "Evolucionado", "clase" => "real");
	}
	if ($fechaPlanificada == "") { return array("label" => "Proyectado", "clase" => "proyectado"); }
	if ($paso <= 1) { return array("label" => "Inicio", "clase" => "inicio"); }
	if ($diasDesdeAnterior < $temporalidad["minimo"]) { return array("label" => "Adelantado", "clase" => "adelantado"); }
	if ($diasDesdeAnterior > $temporalidad["maximo"]) { return array("label" => "Atrasado", "clase" => "atrasado"); }
	return array("label" => "En tiempo", "clase" => "en-tiempo");
}

function buildAutomaticSuggestionCurve($items,$fechaInicio = "")
{
	$ordenados = $items;
	usort($ordenados, function($a, $b) {
		$riesgoA = planRiskItemValueConsulta($a);
		$riesgoB = planRiskItemValueConsulta($b);
		$riesgoA = $riesgoA > 0 ? $riesgoA : 99;
		$riesgoB = $riesgoB > 0 ? $riesgoB : 99;
		if ($riesgoA != $riesgoB) {
			return $riesgoA < $riesgoB ? -1 : 1;
		}
		$ordenA = planRiskOrdenConsulta($a);
		$ordenB = planRiskOrdenConsulta($b);
		if ($ordenA == $ordenB) { return 0; }
		return $ordenA < $ordenB ? -1 : 1;
	});
	return buildTreatmentRiskCurve($ordenados,$fechaInicio,false);
}

function planRiskNombreTratamientoConsulta($item)
{
	if (isset($item["nombre_producto_actual"]) && trim((string)$item["nombre_producto_actual"]) != "") { return (string)$item["nombre_producto_actual"]; }
	if (isset($item["nombre_producto"]) && trim((string)$item["nombre_producto"]) != "") { return (string)$item["nombre_producto"]; }
	if (isset($item["nombre_tratamiento_snapshot"]) && trim((string)$item["nombre_tratamiento_snapshot"]) != "") { return (string)$item["nombre_tratamiento_snapshot"]; }
	return "Tratamiento";
}

function planRiskVentaOrigenConsulta($item)
{
	$venta = isset($item["venta_id"]) ? "#".(string)$item["venta_id"] : "";
	$origen = "";
	if (isset($item["origen"])) {
		$origen = (string)$item["origen"] == "venta_anexada" ? "Anexada" : "Principal";
	}
	if ($venta == "") { return $origen != "" ? $origen : "Sin venta"; }
	return $origen != "" ? $venta." ".$origen : $venta;
}

function planRiskMontoConsulta($item)
{
	if (isset($item["precio_producto"]) && is_numeric($item["precio_producto"])) { return (float)$item["precio_producto"]; }
	if (isset($item["monto"]) && is_numeric($item["monto"])) { return (float)$item["monto"]; }
	return 0;
}

function buildTreatmentRiskCurve($items,$fechaInicio = "",$usarFechasReales = true)
{
	$puntos = array();
	$paso = 1;
	$fechaBase = fechaInicioCurvaPlanConsulta($items,$fechaInicio);
	$fechaSugerida = $fechaBase;
	$fechaAnteriorGrafico = "";
	$intervaloAnterior = 0;
	foreach ($items as $item) {
		$valor = planRiskItemValueConsulta($item);
		$monto = planRiskMontoConsulta($item);
		$temporalidad = obtenerTemporalidadItemPlanConsulta($item);
		if ($paso > 1) {
			$fechaSugerida = sumarDiasFechaPlanConsulta($fechaSugerida, $intervaloAnterior > 0 ? $intervaloAnterior : 20);
		}
		$fechaPlanificada = $usarFechasReales && isset($item["fecha_planificada"]) ? normalizarFechaPlanConsulta($item["fecha_planificada"]) : "";
		$fechaInicioTratamiento = $usarFechasReales && isset($item["fecha_inicio_tratamiento"]) ? normalizarFechaPlanConsulta($item["fecha_inicio_tratamiento"]) : "";
		$fechaUltimaEvolucion = $usarFechasReales && isset($item["fecha_ultima_evolucion"]) ? normalizarFechaPlanConsulta($item["fecha_ultima_evolucion"]) : "";
		$fechaReal = $fechaUltimaEvolucion != "" ? $fechaUltimaEvolucion : $fechaInicioTratamiento;
		$fechaOrigenClase = "proyectado";
		$fechaOrigenTexto = "Proyectado";
		if ($fechaReal != "") {
			$fechaGrafico = $fechaReal;
			$fechaOrigenClase = "real";
			$fechaOrigenTexto = "Evolucion real";
		} elseif ($fechaPlanificada != "") {
			$fechaGrafico = $fechaPlanificada;
			$fechaOrigenClase = "agenda";
			$fechaOrigenTexto = "Agenda";
		} else {
			$fechaGrafico = $fechaSugerida;
		}
		$diasDesdeAnterior = $fechaAnteriorGrafico != "" ? diasEntreFechasPlanConsulta($fechaAnteriorGrafico, $fechaGrafico) : 0;
		$estadoTemporal = estadoTemporalPlanConsulta($temporalidad,$fechaPlanificada,$paso,$diasDesdeAnterior,$fechaReal);
		$dayOffset = max(0, diasEntreFechasPlanConsulta($fechaBase,$fechaGrafico));
		$puntos[] = array(
			"paso" => $paso,
			"detalleId" => detalleIdPlanRiskConsulta($item),
			"tratamiento" => planRiskNombreTratamientoConsulta($item),
			"nivelLabel" => $valor > 0 ? ProductoRiesgoFinancieroTexto($valor) : "Sin nivel",
			"nivelValue" => $valor,
			"estado" => isset($item["estado_texto"]) ? (string)$item["estado_texto"] : "",
			"ventaOrigen" => planRiskVentaOrigenConsulta($item),
			"monto" => $monto,
			"montoTexto" => $monto > 0 ? number_format($monto, 0, ",", ".")." Gs." : "",
			"fechaBase" => $fechaBase,
			"fechaSugerida" => $fechaSugerida,
			"fechaPlanificada" => $fechaPlanificada,
			"fechaInicioTratamiento" => $fechaInicioTratamiento,
			"fechaUltimaEvolucion" => $fechaUltimaEvolucion,
			"fechaGrafico" => $fechaGrafico,
			"fechaGraficoTexto" => formatearFechaPlanConsulta($fechaGrafico),
			"fechaOrigenClase" => $fechaOrigenClase,
			"fechaOrigenTexto" => $fechaOrigenTexto,
			"dayOffset" => $dayOffset,
			"intervaloRecomendadoDias" => $temporalidad["intervalo"],
			"intervaloMinimoDias" => $temporalidad["minimo"],
			"intervaloMaximoDias" => $temporalidad["maximo"],
			"diasDesdeAnterior" => $diasDesdeAnterior,
			"estadoTemporal" => $estadoTemporal["label"],
			"estadoTemporalClase" => $estadoTemporal["clase"],
			"usaTemporalidad" => $temporalidad["configurada"] ? 1 : 0,
			"temporalidadTipo" => $temporalidad["tipo_label"],
			"temporalidadObservacion" => $temporalidad["observacion"]
		);
		$fechaAnteriorGrafico = $fechaGrafico;
		$intervaloAnterior = $temporalidad["intervalo"];
		$paso++;
	}
	return $puntos;
}

function promedioPlanRiskConsulta($valores)
{
	$limpios = array();
	foreach ($valores as $valor) {
		if ($valor > 0) { $limpios[] = $valor; }
	}
	if (count($limpios) == 0) { return 0; }
	return array_sum($limpios) / count($limpios);
}

function analyzeRiskProgression($curve)
{
	if (count($curve) == 0) {
		return array("inicio" => "Sin datos", "inicio_clase" => "neutral", "progresion" => "Sin datos", "progresion_clase" => "neutral");
	}
	$valores = array();
	foreach ($curve as $punto) {
		if ((int)$punto["nivelValue"] > 0) { $valores[] = (int)$punto["nivelValue"]; }
	}
	if (count($valores) == 0) {
		return array("inicio" => "Sin nivel", "inicio_clase" => "medio", "progresion" => "Sin nivel", "progresion_clase" => "medio");
	}
	$primero = $valores[0];
	$inicio = $primero <= 2 ? "Bajo" : ($primero == 3 ? "Medio" : "Alto");
	$inicioClase = $primero <= 2 ? "bajo" : ($primero == 3 ? "medio" : "alto");
	foreach ($curve as $punto) {
		$nivel = (int)$punto["nivelValue"];
		$dias = isset($punto["dayOffset"]) ? (int)$punto["dayOffset"] : (($punto["paso"] - 1) * 20);
		if ($nivel >= 5 && $dias <= 30) {
			return array("inicio" => $inicio, "inicio_clase" => $inicioClase, "progresion" => "Riesgo alto adelantado", "progresion_clase" => "alto");
		}
		if ($nivel >= 4 && $dias <= 15) {
			return array("inicio" => $inicio, "inicio_clase" => $inicioClase, "progresion" => "Riesgo alto adelantado", "progresion_clase" => "alto");
		}
	}
	$cantidad = count($valores);
	$mitad = max(1, (int)ceil($cantidad / 2));
	$primeros = array_slice($valores, 0, $mitad);
	$ultimos = array_slice($valores, -$mitad);
	$promedioInicio = promedioPlanRiskConsulta($primeros);
	$promedioFinal = promedioPlanRiskConsulta($ultimos);
	$tieneAltoTemprano = false;
	for ($i = 0; $i < min(2, $cantidad); $i++) {
		if ($valores[$i] >= 4) { $tieneAltoTemprano = true; }
	}
	if ($cantidad > 1 && $promedioInicio > ($promedioFinal + 0.6)) {
		$progresion = "Invertida";
		$progresionClase = "alto";
	} elseif ($tieneAltoTemprano || $promedioInicio >= 3.5) {
		$progresion = "Acelerada";
		$progresionClase = "medio";
	} else {
		$progresion = "Equilibrada";
		$progresionClase = "bajo";
	}
	return array("inicio" => $inicio, "inicio_clase" => $inicioClase, "progresion" => $progresion, "progresion_clase" => $progresionClase);
}

function analyzeTemporalDensity($curve)
{
	if (count($curve) == 0) {
		return array("label" => "Sin datos", "clase" => "neutral", "warning" => "");
	}
	$dias = array();
	foreach ($curve as $punto) {
		$dias[] = isset($punto["dayOffset"]) ? (int)$punto["dayOffset"] : 0;
	}
	sort($dias);
	for ($i = 0; $i < count($dias); $i++) {
		$totalVentana = 0;
		for ($j = $i; $j < count($dias); $j++) {
			if (($dias[$j] - $dias[$i]) <= 15) {
				$totalVentana++;
			}
		}
		if ($totalVentana > 2) {
			return array("label" => "Alta frecuencia", "clase" => "alto", "warning" => "Alta frecuencia de atenciones: este paciente tiene mas de 2 visitas en 15 dias.");
		}
	}
	return array("label" => "Normal", "clase" => "bajo", "warning" => "");
}

function analyzeTemporalidadPlanConsulta($curve)
{
	if (count($curve) == 0) {
		return array("label" => "Sin datos", "clase" => "neutral");
	}
	$sinConfig = 0;
	$adelantados = 0;
	$atrasados = 0;
	foreach ($curve as $punto) {
		if (empty($punto["usaTemporalidad"])) { $sinConfig++; }
		$estado = isset($punto["estadoTemporalClase"]) ? (string)$punto["estadoTemporalClase"] : "";
		if ($estado == "adelantado") { $adelantados++; }
		if ($estado == "atrasado") { $atrasados++; }
	}
	if ($sinConfig > 0) { return array("label" => "Sin configuracion completa", "clase" => "medio"); }
	if ($adelantados > 0) { return array("label" => "Muy concentrada", "clase" => "alto"); }
	if ($atrasados > 0) { return array("label" => "Con atrasos", "clase" => "medio"); }
	return array("label" => "Correcta", "clase" => "bajo");
}

function calculatePlanDeviation($actualCurve, $referenceCurve)
{
	$total = min(count($actualCurve), count($referenceCurve));
	if ($total == 0) {
		return array("label" => "Sin referencia", "clase" => "neutral", "valor" => null);
	}
	$suma = 0;
	$comparados = 0;
	for ($i = 0; $i < $total; $i++) {
		$actual = (int)$actualCurve[$i]["nivelValue"];
		$referencia = (int)$referenceCurve[$i]["nivelValue"];
		if ($actual <= 0 || $referencia <= 0) { continue; }
		$desvioRiesgo = abs($actual - $referencia);
		$diaActual = isset($actualCurve[$i]["dayOffset"]) ? (int)$actualCurve[$i]["dayOffset"] : (($i + 1) * 20);
		$diaReferencia = isset($referenceCurve[$i]["dayOffset"]) ? (int)$referenceCurve[$i]["dayOffset"] : (($i + 1) * 20);
		$desvioTemporal = min(2, abs($diaActual - $diaReferencia) / 30);
		$suma += ($desvioRiesgo + $desvioTemporal);
		$comparados++;
	}
	if ($comparados == 0) {
		return array("label" => "Sin nivel", "clase" => "neutral", "valor" => null);
	}
	$promedio = $suma / $comparados;
	if ($promedio <= 0.7) { return array("label" => "Bajo", "clase" => "bajo", "valor" => $promedio); }
	if ($promedio <= 1.5) { return array("label" => "Medio", "clase" => "medio", "valor" => $promedio); }
	return array("label" => "Alto", "clase" => "alto", "valor" => $promedio);
}

function planRiskTooltipConsulta($punto)
{
	$partes = array(
		"Paso ".$punto["paso"],
		$punto["tratamiento"],
		"Nivel: ".$punto["nivelLabel"],
		"Origen fecha: ".$punto["fechaOrigenTexto"],
		"Inicio real: ".(formatearFechaPlanConsulta($punto["fechaInicioTratamiento"]) != "" ? formatearFechaPlanConsulta($punto["fechaInicioTratamiento"]) : "Sin evolucion"),
		"Ultima evolucion: ".(formatearFechaPlanConsulta($punto["fechaUltimaEvolucion"]) != "" ? formatearFechaPlanConsulta($punto["fechaUltimaEvolucion"]) : "Sin evolucion"),
		"Fecha sugerida: ".(formatearFechaPlanConsulta($punto["fechaSugerida"]) != "" ? formatearFechaPlanConsulta($punto["fechaSugerida"]) : "Sin fecha"),
		"Fecha planificada: ".(formatearFechaPlanConsulta($punto["fechaPlanificada"]) != "" ? formatearFechaPlanConsulta($punto["fechaPlanificada"]) : "Sin agenda"),
		"Intervalo recomendado: ".$punto["intervaloRecomendadoDias"]." dias",
		"Dias desde anterior: ".$punto["diasDesdeAnterior"],
		"Estado temporal: ".$punto["estadoTemporal"],
		"Estado: ".($punto["estado"] != "" ? $punto["estado"] : "Sin estado"),
		"Venta: ".$punto["ventaOrigen"]
	);
	if ($punto["montoTexto"] != "") {
		$partes[] = "Monto: ".$punto["montoTexto"];
	}
	return htmlspecialchars(implode("\n", $partes), ENT_QUOTES, "UTF-8");
}

function planRiskNivelNombreConsulta($nivel)
{
	$nivel = (int)$nivel;
	if ($nivel == 1) { return "N1 Bajo"; }
	if ($nivel == 2) { return "N2 Moderado"; }
	if ($nivel == 3) { return "N3 Controlado"; }
	if ($nivel == 4) { return "N4 Alto"; }
	if ($nivel == 5) { return "N5 Critico"; }
	return "Sin nivel";
}

function planRiskPointFlagsConsulta($punto)
{
	$flags = array();
	$nivel = isset($punto["nivelValue"]) ? (int)$punto["nivelValue"] : 0;
	$dias = isset($punto["dayOffset"]) ? (int)$punto["dayOffset"] : 0;
	if (($nivel >= 5 && $dias <= 30) || ($nivel >= 4 && $dias <= 15)) {
		$flags[] = "alto_temprano";
	}
	if (isset($punto["estadoTemporalClase"]) && $punto["estadoTemporalClase"] == "adelantado") {
		$flags[] = "adelantado";
	}
	if ($nivel <= 0) {
		$flags[] = "sin_nivel";
	}
	return $flags;
}

function planRiskSmoothPathConsulta($coords)
{
	if (count($coords) == 0) { return ""; }
	$path = "M ".$coords[0]["x"]." ".$coords[0]["y"];
	for ($i = 1; $i < count($coords); $i++) {
		$prev = $coords[$i - 1];
		$actual = $coords[$i];
		$midX = round(($prev["x"] + $actual["x"]) / 2, 2);
		$path .= " C ".$midX." ".$prev["y"].", ".$midX." ".$actual["y"].", ".$actual["x"]." ".$actual["y"];
	}
	return $path;
}

function renderPlanRiskChartPathConsulta($curve, $maxDays, $width, $height, $left, $top, $right, $bottom, $tipo)
{
	$plotWidth = $width - $left - $right;
	$plotHeight = $height - $top - $bottom;
	$coords = array();
	$markers = "";
	$count = count($curve);
	for ($i = 0; $i < $count; $i++) {
		$punto = $curve[$i];
		$valor = (int)$punto["nivelValue"];
		$dia = isset($punto["dayOffset"]) ? (int)$punto["dayOffset"] : (($punto["paso"] - 1) * 20);
		$x = $left + (($maxDays <= 0) ? ($plotWidth / 2) : ($dia * ($plotWidth / $maxDays)));
		$y = $top + ((5 - max(0, min(5, $valor))) * ($plotHeight / 5));
		$coords[] = array("x" => round($x, 2), "y" => round($y, 2));
		$claseNivel = $valor > 0 ? " nivel-".$valor : " is-missing";
		$claseFecha = isset($punto["fechaOrigenClase"]) ? " fecha-".preg_replace('/[^a-z0-9_-]/', '', strtolower((string)$punto["fechaOrigenClase"])) : "";
		$label = $valor > 0 ? "N".$valor : "?";
		$flags = $tipo == "actual" ? planRiskPointFlagsConsulta($punto) : array();
		$flagClass = "";
		foreach ($flags as $flag) {
			$flagClass .= " is-".preg_replace('/[^a-z0-9_-]/', '', strtolower((string)$flag));
		}
		$mostrarLabel = ($tipo == "actual" && ($i == 0 || $i == ($count - 1) || count($flags) > 0));
		$labelTexto = $mostrarLabel ? "<text class='plan-risk-point-label' x='".round($x, 2)."' y='".round($y - 10, 2)."'>".$label."</text>" : "";
		$alertaTexto = "";
		if (in_array("alto_temprano", $flags)) {
			$alertaTexto = "<text class='plan-risk-alert-label' x='".round($x, 2)."' y='".round($y - 23, 2)."'>Alerta</text>";
		}
		$markers .= "<g class='plan-risk-point plan-risk-point--".$tipo.$claseFecha.$claseNivel.$flagClass."' tabindex='0'><title>".planRiskTooltipConsulta($punto)."</title><circle cx='".round($x, 2)."' cy='".round($y, 2)."' r='".($tipo == "referencia" ? "4" : "5")."'></circle>".$labelTexto.$alertaTexto."</g>";
	}
	$path = count($coords) > 1 ? "<path class='plan-risk-line plan-risk-line--".$tipo."' d='".planRiskSmoothPathConsulta($coords)."'></path>" : "";
	return $path.$markers;
}

function planRiskPagoTooltipConsulta($pago)
{
	$partes = array(
		"Pagos por mes",
		"Mes: ".$pago["mes"],
		"Monto abonado: ".$pago["montoTexto"],
		"Cuotas con pago: ".$pago["cuotas"],
		"Pagos registrados: ".$pago["pagos"]
	);
	if ((float)$pago["montoInteres"] > 0) {
		$partes[] = "Interes incluido: ".number_format((float)$pago["montoInteres"], 0, ",", ".")." Gs.";
	}
	if (trim((string)$pago["ventas"]) != "") {
		$partes[] = "Ventas: ".$pago["ventas"];
	}
	return htmlspecialchars(implode("\n", $partes), ENT_QUOTES, "UTF-8");
}

function renderPlanRiskPaymentBarsConsulta($pagosMensuales, $maxDays, $width, $height, $left, $top, $right, $bottom)
{
	if (count($pagosMensuales) == 0 || $maxDays <= 0) {
		return "";
	}
	$plotWidth = $width - $left - $right;
	$plotHeight = $height - $top - $bottom;
	$yBase = $top + $plotHeight;
	$barMaxHeight = max(34, min(74, $plotHeight * 0.36));
	$maxMonto = 0;
	foreach ($pagosMensuales as $pago) {
		$maxMonto = max($maxMonto, isset($pago["monto"]) ? (float)$pago["monto"] : 0);
	}
	if ($maxMonto <= 0) {
		return "";
	}
	$barWidth = max(18, min(42, $plotWidth / max(5, count($pagosMensuales) * 1.45)));
	$html = "<g class='plan-risk-payments-layer'>";
	$html .= "<text class='plan-risk-payment-scale-label' x='".($width - $right)."' y='".($top + 11)."'>Max pago ".htmlspecialchars(formatoMontoCortoPlanRiskConsulta($maxMonto), ENT_QUOTES, "UTF-8")."</text>";
	foreach ($pagosMensuales as $pago) {
		$monto = isset($pago["monto"]) ? (float)$pago["monto"] : 0;
		if ($monto <= 0) { continue; }
		$dia = isset($pago["dayOffset"]) ? (int)$pago["dayOffset"] : 0;
		$dia = max(0, min($maxDays, $dia));
		$x = $left + ($dia * ($plotWidth / $maxDays));
		$barHeight = max(5, ($monto / $maxMonto) * $barMaxHeight);
		$y = $yBase - $barHeight;
		$xRect = $x - ($barWidth / 2);
		$cuotas = isset($pago["cuotas"]) ? (int)$pago["cuotas"] : 0;
		$labelCuotas = $cuotas == 1 ? "1 cuota" : $cuotas." cuotas";
		$html .= "<g class='plan-risk-payment-bar' tabindex='0'><title>".planRiskPagoTooltipConsulta($pago)."</title>";
		$html .= "<rect x='".round($xRect, 2)."' y='".round($y, 2)."' width='".round($barWidth, 2)."' height='".round($barHeight, 2)."' rx='5'></rect>";
		$html .= "<text class='plan-risk-payment-label' x='".round($x, 2)."' y='".round(max($top + 18, $y - 5), 2)."'>".htmlspecialchars($pago["montoCorto"], ENT_QUOTES, "UTF-8")."</text>";
		if ($barWidth >= 24) {
			$html .= "<text class='plan-risk-payment-count' x='".round($x, 2)."' y='".round($yBase - 7, 2)."'>".htmlspecialchars($labelCuotas, ENT_QUOTES, "UTF-8")."</text>";
		}
		$html .= "</g>";
	}
	$html .= "</g>";
	return $html;
}

function renderPlanRiskChart($actualCurve, $referenceCurve = array(), $modo = "definitivo", $pagosMensuales = array())
{
	$mostrarReferencia = ($modo == "definitivo" && count($referenceCurve) > 0);
	$referenciaAnalisis = $mostrarReferencia ? $referenceCurve : $actualCurve;
	$analisis = analyzeRiskProgression($actualCurve);
	$temporalidadAnalisis = analyzeTemporalidadPlanConsulta($actualCurve);
	$frecuenciaAnalisis = analyzeTemporalDensity($actualCurve);
	$desvio = calculatePlanDeviation($actualCurve, $referenciaAnalisis);
	$sinNivel = false;
	foreach ($actualCurve as $punto) {
		if ((int)$punto["nivelValue"] <= 0) { $sinNivel = true; break; }
	}
	$tituloCurvaActual = $modo == "definitivo" ? "Plan madre" : "Sugerencia autom&aacute;tica";
	$maxPuntos = max(1, count($actualCurve), count($referenceCurve));
	$maxDays = 0;
	$fechaBaseGrafico = "";
	foreach (array_merge($actualCurve, $referenceCurve) as $punto) {
		if ($fechaBaseGrafico == "" && isset($punto["fechaBase"])) { $fechaBaseGrafico = $punto["fechaBase"]; }
		$maxDays = max($maxDays, isset($punto["dayOffset"]) ? (int)$punto["dayOffset"] : 0);
	}
	foreach ($pagosMensuales as $pagoMensual) {
		$maxDays = max($maxDays, isset($pagoMensual["dayOffset"]) ? (int)$pagoMensual["dayOffset"] : 0);
	}
	if ($maxDays <= 0) { $maxDays = max(20, ($maxPuntos - 1) * 20); }
	$width = max(680, min(1500, ($maxDays * 8) + 120));
	$height = 250;
	$left = 92;
	$right = 32;
	$top = 22;
	$bottom = 50;
	$plotWidth = $width - $left - $right;
	$plotHeight = $height - $top - $bottom;
	$svg = "";
	if (count($actualCurve) > 0) {
		$svg .= "<svg class='plan-risk-svg' width='".$width."' height='".$height."' viewBox='0 0 ".$width." ".$height."' role='img' aria-label='Resumen grafico de riesgo financiero y temporalidad'>";
		for ($nivel = 1; $nivel <= 5; $nivel++) {
			$y = $top + ((5 - $nivel) * ($plotHeight / 5));
			$svg .= "<line class='plan-risk-grid' x1='".$left."' y1='".round($y, 2)."' x2='".($width - $right)."' y2='".round($y, 2)."'></line>";
			$svg .= "<text class='plan-risk-axis-label' x='10' y='".round($y + 4, 2)."'>".htmlspecialchars(planRiskNivelNombreConsulta($nivel), ENT_QUOTES, "UTF-8")."</text>";
		}
		$svg .= "<line class='plan-risk-axis' x1='".$left."' y1='".$top."' x2='".$left."' y2='".($top + $plotHeight)."'></line>";
		$svg .= "<line class='plan-risk-axis' x1='".$left."' y1='".($top + $plotHeight)."' x2='".($width - $right)."' y2='".($top + $plotHeight)."'></line>";
		$ticks = ticksMesesPlanRiskConsulta($fechaBaseGrafico,$maxDays);
		foreach ($ticks as $tick) {
			$dia = isset($tick["dia"]) ? (int)$tick["dia"] : 0;
			if ($dia < 0 || $dia > $maxDays) { continue; }
			$x = $left + ($dia * ($plotWidth / $maxDays));
			$svg .= "<line class='plan-risk-tick' x1='".round($x, 2)."' y1='".($top + $plotHeight)."' x2='".round($x, 2)."' y2='".($top + $plotHeight + 5)."'></line>";
			$svg .= "<text class='plan-risk-month-label' x='".round($x, 2)."' y='".($height - 24)."'>".htmlspecialchars($tick["mes"], ENT_QUOTES, "UTF-8")."</text>";
			$svg .= "<text class='plan-risk-date-label' x='".round($x, 2)."' y='".($height - 8)."'>".htmlspecialchars($tick["detalle"], ENT_QUOTES, "UTF-8")."</text>";
		}
		$svg .= renderPlanRiskPaymentBarsConsulta($pagosMensuales, $maxDays, $width, $height, $left, $top, $right, $bottom);
		if ($mostrarReferencia) {
			$svg .= renderPlanRiskChartPathConsulta($referenceCurve, $maxDays, $width, $height, $left, $top, $right, $bottom, "referencia");
		}
		$svg .= renderPlanRiskChartPathConsulta($actualCurve, $maxDays, $width, $height, $left, $top, $right, $bottom, "actual");
		$svg .= "</svg>";
	}
	$desvioDetalle = $desvio["valor"] === null ? "" : " <small>".number_format($desvio["valor"], 1, ".", "")."</small>";
	$warnings = "";
	if ($sinNivel) {
		$warnings .= "<div class='plan-risk-warning plan-risk-warning--nivel'>Hay tratamientos sin nivel de riesgo cargado.</div>";
	}
	if ($frecuenciaAnalisis["warning"] != "") {
		$warnings .= "<div class='plan-risk-warning plan-risk-warning--frecuencia'>".$frecuenciaAnalisis["warning"]."</div>";
	}
	if ($analisis["progresion"] == "Riesgo alto adelantado") {
		$warnings .= "<div class='plan-risk-warning plan-risk-warning--alto-temprano'>Se detect&oacute; un N4 o N5 demasiado temprano en la ruta.</div>";
	}
	if ($modo == "definitivo" && !$mostrarReferencia) {
		$warnings .= "<div class='plan-risk-warning plan-risk-warning--referencia'>No se encontr&oacute; curva de referencia autom&aacute;tica.</div>";
	}
	$fechaInicioPlanTexto = $fechaBaseGrafico != "" ? formatearFechaPlanConsulta($fechaBaseGrafico) : "";
	$fechaInicioDetalle = $fechaInicioPlanTexto != "" ? " <small>".$fechaInicioPlanTexto."</small>" : "";
	$cuerpoGrafico = count($actualCurve) == 0
		? "<div class='plan-risk-empty'>No hay tratamientos suficientes para generar el gr&aacute;fico.</div>"
		: "<div class='plan-risk-chart-scroll'>".$svg."</div>";
	return "
<section class='plan-risk-card plan-risk-card--".$modo."'>
	<div class='plan-risk-card__head'>
		<div>
			<strong>Resumen gr&aacute;fico de la ruta</strong>
			<span>Proyecci&oacute;n del riesgo financiero seg&uacute;n orden y temporalidad del plan</span>
		</div>
		<div class='plan-risk-legend'>
			<span class='plan-risk-legend__actual'>Ruta realizada / ".$tituloCurvaActual."</span>
			".($mostrarReferencia ? "<span class='plan-risk-legend__referencia'>Ruta esperada / sugerida</span>" : "")."
			".(count($pagosMensuales) > 0 ? "<span class='plan-risk-legend__pagos'>Monto abonado mensual</span>" : "")."
			<span class='plan-risk-legend__alerta'>Punto con alerta</span>
		</div>
	</div>
	<div class='plan-risk-indicators'>
		<span class='plan-risk-chip plan-risk-chip--".$analisis["inicio_clase"]."'><b>Inicio venta base</b>".$analisis["inicio"].$fechaInicioDetalle."</span>
		<span class='plan-risk-chip plan-risk-chip--".$analisis["progresion_clase"]."'><b>Progresi&oacute;n</b>".$analisis["progresion"]."</span>
		<span class='plan-risk-chip plan-risk-chip--".$temporalidadAnalisis["clase"]."'><b>Temporalidad</b>".$temporalidadAnalisis["label"]."</span>
		<span class='plan-risk-chip plan-risk-chip--".$frecuenciaAnalisis["clase"]."'><b>Frecuencia</b>".$frecuenciaAnalisis["label"]."</span>
		<span class='plan-risk-chip plan-risk-chip--".$desvio["clase"]."'><b>Desv&iacute;o vs sugerencia</b>".$desvio["label"].$desvioDetalle."</span>
	</div>
	".$cuerpoGrafico."
	".$warnings."
</section>";
}

 
function normalizarPorcentajePlanTratamientoConsulta($valor)
{
	$valor = str_replace("%", "", trim((string)$valor));
	$valor = str_replace(",", ".", $valor);
	$valor = is_numeric($valor) ? (int)round((float)$valor) : 0;
	if ($valor < 0) { return 0; }
	if ($valor > 100) { return 100; }
	return $valor;
}

function textoContienePlanTratamientoConsulta($texto, $opciones)
{
	$texto = strtolower(trim((string)$texto));
	foreach ($opciones as $opcion) {
		if (strpos($texto, $opcion) !== false) {
			return true;
		}
	}
	return false;
}

function normalizarEstadoPlanTratamientoConsulta($estado, $estado_tratamiento, $avance)
{
	$texto = strtolower(trim((string)$estado." ".(string)$estado_tratamiento));
	if ($avance >= 100 || textoContienePlanTratamientoConsulta($texto, array("completado", "finalizado", "finalizada"))) {
		return "completado";
	}
	if (textoContienePlanTratamientoConsulta($texto, array("eliminado", "anulado", "cancelado", "inactivo"))) {
		return "cancelado";
	}
	if (($avance > 0 && $avance < 100) || textoContienePlanTratamientoConsulta($texto, array("proceso", "iniciado", "iniciada"))) {
		return "proceso";
	}
	return "pendiente";
}

function textoEstadoPlanTratamientoConsulta($estado)
{
	if ($estado == "completado") { return "Completado"; }
	if ($estado == "proceso") { return "En proceso"; }
	if ($estado == "cancelado") { return "Anulado"; }
	return "Pendiente";
}

function grupoPlanTratamientoConsulta($estado, $avance)
{
	if ($estado == "completado" || $estado == "cancelado" || $avance >= 100) {
		return "finalizados";
	}
	if ($estado == "proceso" || ($avance > 0 && $avance < 100)) {
		return "continuar";
	}
	return "siguientes";
}

function compararNumeroPlanTratamientoConsulta($a, $b, $campo, $direccion = "asc")
{
	$valorA = isset($a[$campo]) ? (float)$a[$campo] : 0;
	$valorB = isset($b[$campo]) ? (float)$b[$campo] : 0;
	if ($valorA == $valorB) {
		return 0;
	}
	$resultado = $valorA < $valorB ? -1 : 1;
	return $direccion == "desc" ? ($resultado * -1) : $resultado;
}

function compararTextoPlanTratamientoConsulta($a, $b, $campo)
{
	$valorA = isset($a[$campo]) ? strtolower((string)$a[$campo]) : "";
	$valorB = isset($b[$campo]) ? strtolower((string)$b[$campo]) : "";
	return strcmp($valorA, $valorB);
}

function ordenarGrupoPlanTratamientoConsulta(&$grupo, $tipo)
{
	usort($grupo, function($a, $b) use ($tipo) {
		if ($tipo == "continuar") {
			return compararNumeroPlanTratamientoConsulta($a, $b, "riesgo_orden")
				?: compararNumeroPlanTratamientoConsulta($a, $b, "orden_natural")
				?: compararNumeroPlanTratamientoConsulta($a, $b, "avance", "desc")
				?: compararTextoPlanTratamientoConsulta($a, $b, "nombre_producto");
		}
		if ($tipo == "siguientes") {
			return compararNumeroPlanTratamientoConsulta($a, $b, "riesgo_orden")
				?: compararNumeroPlanTratamientoConsulta($a, $b, "orden_natural")
				?: compararNumeroPlanTratamientoConsulta($a, $b, "precio_producto")
				?: compararTextoPlanTratamientoConsulta($a, $b, "nombre_producto");
		}
		return compararNumeroPlanTratamientoConsulta($a, $b, "orden_natural")
			?: compararTextoPlanTratamientoConsulta($a, $b, "nombre_producto");
	});
}

function ordenarTratamientosPlanSugerido($tratamientos)
{
	$grupos = array(
		"continuar" => array(),
		"siguientes" => array(),
		"finalizados" => array()
	);
	foreach ($tratamientos as $tratamiento) {
		$grupo = isset($tratamiento["grupo_plan"]) ? $tratamiento["grupo_plan"] : "siguientes";
		if (!isset($grupos[$grupo])) {
			$grupo = "siguientes";
		}
		$grupos[$grupo][] = $tratamiento;
	}
	ordenarGrupoPlanTratamientoConsulta($grupos["continuar"], "continuar");
	ordenarGrupoPlanTratamientoConsulta($grupos["siguientes"], "siguientes");
	ordenarGrupoPlanTratamientoConsulta($grupos["finalizados"], "finalizados");
	return $grupos;
}

function ayudaCardPlanTratamientoConsulta($tratamiento)
{
	if ($tratamiento["grupo_plan"] == "continuar") {
		return "Ya iniciado, conviene continuarlo.";
	}
	if ($tratamiento["grupo_plan"] == "finalizados") {
		return $tratamiento["estado_clase"] == "cancelado" ? "Tratamiento no activo." : "Tratamiento ya finalizado.";
	}
	return "Siguiente sugerido segun riesgo y orden natural.";
}

function odontogramaTablasDisponiblesConsulta($mysqli)
{
	$res = $mysqli->query("SHOW TABLES LIKE 'odontograma_tratamiento_links'");
	return $res && $res->num_rows > 0;
}

function textoSuperficieOdontogramaConsulta($superficie)
{
	$mapa = array(
		"mesial" => "Mesial",
		"distal" => "Distal",
		"vestibular" => "Vestibular",
		"lingual_palatina" => "Lingual / Palatina",
		"oclusal_incisal" => "Oclusal / Incisal"
	);
	return isset($mapa[$superficie]) ? $mapa[$superficie] : $superficie;
}

function normalizarArcadaOdontogramaConsulta($arcada)
{
	$arcada = strtolower(str_replace(" ", "_", trim((string)$arcada)));
	if ($arcada == "superior_e_inferior" || $arcada == "superior_inferior" || $arcada == "ambas_arcadas") {
		return "ambas";
	}
	return $arcada;
}

function textoArcadaOdontogramaConsulta($arcada)
{
	$arcada = normalizarArcadaOdontogramaConsulta($arcada);
	if ($arcada == "ambas") { return "Arcada superior e inferior"; }
	if ($arcada == "superior") { return "Arcada superior"; }
	if ($arcada == "inferior") { return "Arcada inferior"; }
	return "Arcada ".str_replace("_", " ", $arcada);
}

function textoAlcanceOdontogramaConsulta($alcance)
{
	$mapa = array(
		"no_requiere" => "No requiere odontograma",
		"boca_completa" => "Boca completa",
		"arcada" => "Arcada",
		"cuadrante" => "Cuadrante",
		"pieza_dental" => "Pieza dental",
		"pieza_superficie" => "Pieza + superficie",
		"piezas_multiples" => "Varias piezas"
	);
	$alcance = strtolower(trim((string)$alcance));
	return isset($mapa[$alcance]) ? $mapa[$alcance] : "Pieza dental";
}

function piezasUbicacionOdontogramaConsulta($link)
{
	$piezas = array();
	if (!$link || !isset($link["piezas_json"]) || trim((string)$link["piezas_json"]) == "") {
		return $piezas;
	}
	$dec = json_decode($link["piezas_json"], true);
	if (is_array($dec)) {
		foreach ($dec as $pieza) {
			$pieza = trim((string)$pieza);
			if ($pieza != "" && !in_array($pieza, $piezas, true)) {
				$piezas[] = $pieza;
			}
		}
	}
	return $piezas;
}

function textoUbicacionOdontogramaConsulta($link)
{
	if (!$link) { return ""; }
	if ((int)$link["boca_completa"] == 1) { return "Boca completa"; }
	if (trim((string)$link["arcada"]) != "") { return textoArcadaOdontogramaConsulta($link["arcada"]); }
	if (trim((string)$link["cuadrante"]) != "") { return "Cuadrante ".str_replace("_", " ", $link["cuadrante"]); }
	$piezas = piezasUbicacionOdontogramaConsulta($link);
	if (count($piezas) > 0) { return "Piezas ".implode(", ", $piezas); }
	$texto = trim((string)$link["pieza"]) != "" ? "Pieza ".$link["pieza"] : "";
	$superficies = array();
	if (trim((string)$link["superficies_json"]) != "") {
		$dec = json_decode($link["superficies_json"], true);
		if (is_array($dec)) {
			foreach ($dec as $sup) {
				$superficies[] = textoSuperficieOdontogramaConsulta($sup);
			}
		}
	}
	if (count($superficies) > 0) {
		$texto .= " &middot; ".implode(", ", $superficies);
	}
	return trim($texto);
}

function baseUbicacionOdontogramaConsulta($alcance, $falta)
{
	$alcance = strtolower(trim((string)$alcance));
	return array(
		"texto" => "",
		"falta" => $falta,
		"requiere" => ($alcance != "" && $alcance != "no_requiere"),
		"alcance" => $alcance,
		"pieza" => "",
		"piezas_json" => "",
		"superficies_json" => "",
		"arcada" => "",
		"cuadrante" => "",
		"boca_completa" => 0
	);
}

function obtenerUbicacionOdontogramaDetalleConsulta($mysqli, $detalleId, $alcance)
{
	$alcance = strtolower(trim((string)$alcance));
	$requiere = ($alcance != "" && $alcance != "no_requiere");
	if (!odontogramaTablasDisponiblesConsulta($mysqli)) {
		return baseUbicacionOdontogramaConsulta($alcance, false);
	}
	$selectPiezasJson = columnaExisteConsulta($mysqli, "odontograma_tratamiento_links", "piezas_json") ? "piezas_json" : "'' AS piezas_json";
	$stmt = $mysqli->prepare("SELECT pieza, ".$selectPiezasJson.", superficies_json, arcada, cuadrante, boca_completa FROM odontograma_tratamiento_links WHERE detalle_venta_id = ? AND activo = 1 ORDER BY id DESC LIMIT 1");
	if (!$stmt) {
		return baseUbicacionOdontogramaConsulta($alcance, $requiere);
	}
	$stmt->bind_param("s", $detalleId);
	if (!$stmt->execute()) {
		return baseUbicacionOdontogramaConsulta($alcance, $requiere);
	}
	$row = $stmt->get_result()->fetch_assoc();
	if (!$row) {
		$base = baseUbicacionOdontogramaConsulta($alcance, $requiere);
		$base["texto"] = $requiere ? "Falta ubicar en odontograma" : "";
		return $base;
	}
	$texto = textoUbicacionOdontogramaConsulta($row);
	$row["texto"] = $texto != "" ? $texto : "Falta ubicar en odontograma";
	$row["falta"] = false;
	$row["requiere"] = $requiere;
	$row["alcance"] = $alcance;
	return $row;
}

function tipoUbicacionVisualOdontogramaConsulta($ubicacion)
{
	if (!is_array($ubicacion) || !empty($ubicacion["falta"])) { return "pendiente"; }
	if ((int)$ubicacion["boca_completa"] == 1) { return "boca-completa"; }
	if (count(piezasUbicacionOdontogramaConsulta($ubicacion)) > 0) { return "piezas-multiples"; }
	if (trim((string)$ubicacion["arcada"]) != "") {
		$arcada = normalizarArcadaOdontogramaConsulta($ubicacion["arcada"]);
		if ($arcada == "superior") { return "arcada-superior"; }
		if ($arcada == "inferior") { return "arcada-inferior"; }
		return "ambas-arcadas";
	}
	if (trim((string)$ubicacion["pieza"]) != "") { return "pieza"; }
	if (trim((string)$ubicacion["cuadrante"]) != "") { return "ambas-arcadas"; }
	return "pendiente";
}

function primeraSuperficieUbicacionOdontogramaConsulta($ubicacion)
{
	if (!is_array($ubicacion)) { return ""; }
	if (trim((string)$ubicacion["superficies_json"]) != "") {
		$dec = json_decode($ubicacion["superficies_json"], true);
		if (is_array($dec) && count($dec) > 0) {
			return (string)$dec[0];
		}
	}
	return "";
}

function iconoUbicacionOdontogramaConsulta($tipo, $pieza)
{
	$piezaHtml = htmlspecialchars((string)$pieza, ENT_QUOTES, "UTF-8");
	if ($tipo == "pieza") {
		return "<span class='odontograma-location-icon odontograma-icon-pieza mini-diente-perfil' aria-hidden='true'><svg viewBox='0 0 44 44'><path class='icon-tooth-root' d='M17 23 C18 28 19 35 22 40 C25 35 26 28 27 23 C24 25 20 25 17 23 Z'></path><path class='icon-tooth-crown' d='M12 9 C16 3 28 3 32 9 C34 16 30 23 24 26 C22 24 20 24 18 26 C12 23 10 16 12 9 Z'></path></svg>".($piezaHtml != "" ? "<b>".$piezaHtml."</b>" : "")."</span>";
	}
	if ($tipo == "arcada-superior") {
		return "<span class='odontograma-location-icon odontograma-icon-arcada-superior' aria-hidden='true'><svg viewBox='0 0 44 44'><path d='M8 28 C13 14 31 14 36 28'></path><circle cx='14' cy='27' r='2.5'></circle><circle cx='22' cy='23' r='2.5'></circle><circle cx='30' cy='27' r='2.5'></circle></svg></span>";
	}
	if ($tipo == "arcada-inferior") {
		return "<span class='odontograma-location-icon odontograma-icon-arcada-inferior' aria-hidden='true'><svg viewBox='0 0 44 44'><path d='M8 16 C13 30 31 30 36 16'></path><circle cx='14' cy='17' r='2.5'></circle><circle cx='22' cy='21' r='2.5'></circle><circle cx='30' cy='17' r='2.5'></circle></svg></span>";
	}
	if ($tipo == "ambas-arcadas") {
		return "<span class='odontograma-location-icon odontograma-icon-ambas-arcadas' aria-hidden='true'><svg viewBox='0 0 44 44'><path d='M9 20 C14 9 30 9 35 20'></path><path d='M9 24 C14 35 30 35 35 24'></path><circle cx='16' cy='20' r='2'></circle><circle cx='22' cy='17' r='2'></circle><circle cx='28' cy='20' r='2'></circle><circle cx='16' cy='24' r='2'></circle><circle cx='22' cy='27' r='2'></circle><circle cx='28' cy='24' r='2'></circle></svg></span>";
	}
	if ($tipo == "boca-completa") {
		return "<span class='odontograma-location-icon odontograma-icon-boca-completa' aria-hidden='true'><svg viewBox='0 0 44 44'><path d='M7 22 C10 8 34 8 37 22 C34 36 10 36 7 22 Z'></path><path d='M12 20 C17 15 27 15 32 20'></path><path d='M12 24 C17 29 27 29 32 24'></path><path d='M22 15 L22 29'></path></svg></span>";
	}
	if ($tipo == "piezas-multiples") {
		return "<span class='odontograma-location-icon odontograma-icon-piezas-multiples' aria-hidden='true'><svg viewBox='0 0 44 44'><circle cx='14' cy='16' r='5'></circle><circle cx='28' cy='16' r='5'></circle><circle cx='21' cy='29' r='5'></circle><path d='M14 21 L21 24'></path><path d='M28 21 L21 24'></path></svg></span>";
	}
	return "<span class='odontograma-location-icon odontograma-icon-pendiente' aria-hidden='true'><svg viewBox='0 0 44 44'><circle cx='22' cy='22' r='13'></circle><path d='M22 14 L22 30'></path><path d='M14 22 L30 22'></path></svg></span>";
}

function renderizarUbicacionOdontogramaConsulta($ubicacion, $claseBase)
{
	if (!is_array($ubicacion) || trim((string)$ubicacion["texto"]) == "") {
		return "";
	}
	$tipo = tipoUbicacionVisualOdontogramaConsulta($ubicacion);
	$falta = !empty($ubicacion["falta"]);
	$texto = $falta ? "Ubicaci&oacute;n pendiente" : htmlspecialchars((string)$ubicacion["texto"], ENT_QUOTES, "UTF-8");
	$detalle = $falta ? "Requiere: ".htmlspecialchars(textoAlcanceOdontogramaConsulta($ubicacion["alcance"]), ENT_QUOTES, "UTF-8") : "";
	if ($tipo == "boca-completa" && !$falta) { $detalle = "Aplica a todos los dientes"; }
	if ($tipo == "piezas-multiples" && !$falta) { $detalle = "Seleccion multiple"; }
	if (($tipo == "arcada-superior" || $tipo == "arcada-inferior" || $tipo == "ambas-arcadas") && !$falta) { $detalle = "Ubicaci&oacute;n general"; }
	$clase = $claseBase." tratamiento-ubicacion-visual tratamiento-ubicacion-".$tipo.($falta ? " ".$claseBase."--falta tratamiento-ubicacion-pendiente" : " tratamiento-ubicacion-completa");
	$contenido = iconoUbicacionOdontogramaConsulta($tipo, isset($ubicacion["pieza"]) ? $ubicacion["pieza"] : "")."<span class='tratamiento-ubicacion-texto'><b>".$texto."</b>".($detalle != "" ? "<small>".$detalle."</small>" : "")."</span>";
	if ($falta) {
		return "<span class='".$clase."'>".$contenido."</span>";
	}
	$pieza = htmlspecialchars((string)$ubicacion["pieza"], ENT_QUOTES, "UTF-8");
	$arcada = htmlspecialchars((string)$ubicacion["arcada"], ENT_QUOTES, "UTF-8");
	$cuadrante = htmlspecialchars((string)$ubicacion["cuadrante"], ENT_QUOTES, "UTF-8");
	$boca = ((int)$ubicacion["boca_completa"] == 1) ? "1" : "";
	$superficie = htmlspecialchars(primeraSuperficieUbicacionOdontogramaConsulta($ubicacion), ENT_QUOTES, "UTF-8");
	$piezas = htmlspecialchars(implode(",", piezasUbicacionOdontogramaConsulta($ubicacion)), ENT_QUOTES, "UTF-8");
	$onclick = "event.stopPropagation(); if (typeof odontogramaEnfocarUbicacionFicha == 'function') { odontogramaEnfocarUbicacionFicha('".$pieza."','".$arcada."','".$cuadrante."','".$boca."','".$superficie."','".$piezas."'); }";
	return "<button type='button' class='".$clase."' title='Ver ubicaci&oacute;n en odontograma' onclick=\"".$onclick."\">".$contenido."</button>";
}

function resumenTemporalDesdePuntoPlanConsulta($punto)
{
	return array(
		"intervalo" => isset($punto["intervaloRecomendadoDias"]) ? (int)$punto["intervaloRecomendadoDias"] : 20,
		"fecha_sugerida" => isset($punto["fechaSugerida"]) ? (string)$punto["fechaSugerida"] : "",
		"fecha_planificada" => isset($punto["fechaPlanificada"]) ? (string)$punto["fechaPlanificada"] : "",
		"fecha_inicio_tratamiento" => isset($punto["fechaInicioTratamiento"]) ? (string)$punto["fechaInicioTratamiento"] : "",
		"fecha_ultima_evolucion" => isset($punto["fechaUltimaEvolucion"]) ? (string)$punto["fechaUltimaEvolucion"] : "",
		"fecha_origen" => isset($punto["fechaOrigenTexto"]) ? (string)$punto["fechaOrigenTexto"] : "Proyectado",
		"fecha_origen_clase" => isset($punto["fechaOrigenClase"]) ? (string)$punto["fechaOrigenClase"] : "proyectado",
		"dias_desde_anterior" => isset($punto["diasDesdeAnterior"]) ? (int)$punto["diasDesdeAnterior"] : 0,
		"estado" => isset($punto["estadoTemporal"]) ? (string)$punto["estadoTemporal"] : "Proyectado",
		"estado_clase" => isset($punto["estadoTemporalClase"]) ? (string)$punto["estadoTemporalClase"] : "proyectado",
		"configurada" => !empty($punto["usaTemporalidad"]),
		"tipo" => isset($punto["temporalidadTipo"]) ? (string)$punto["temporalidadTipo"] : "Sin configurar"
	);
}

function asignarResumenTemporalItemsPlanConsulta(&$items,$curve)
{
	$mapa = array();
	foreach ($curve as $punto) {
		if (isset($punto["detalleId"]) && trim((string)$punto["detalleId"]) != "") {
			$mapa[(string)$punto["detalleId"]] = resumenTemporalDesdePuntoPlanConsulta($punto);
		}
	}
	for ($i = 0; $i < count($items); $i++) {
		$id = detalleIdPlanRiskConsulta($items[$i]);
		if ($id != "" && isset($mapa[$id])) {
			$items[$i]["temporalidad_resumen"] = $mapa[$id];
		} elseif (isset($curve[$i])) {
			$items[$i]["temporalidad_resumen"] = resumenTemporalDesdePuntoPlanConsulta($curve[$i]);
		}
	}
}

function asignarResumenTemporalGruposPlanConsulta(&$gruposPlan,$curve)
{
	$mapa = array();
	foreach ($curve as $punto) {
		if (isset($punto["detalleId"]) && trim((string)$punto["detalleId"]) != "") {
			$mapa[(string)$punto["detalleId"]] = resumenTemporalDesdePuntoPlanConsulta($punto);
		}
	}
	foreach ($gruposPlan as $grupo => &$items) {
		for ($i = 0; $i < count($items); $i++) {
			$id = detalleIdPlanRiskConsulta($items[$i]);
			if ($id != "" && isset($mapa[$id])) {
				$items[$i]["temporalidad_resumen"] = $mapa[$id];
			}
		}
	}
	unset($items);
}

function renderTemporalidadTratamientoConsulta($item,$claseBase)
{
	$resumen = isset($item["temporalidad_resumen"]) && is_array($item["temporalidad_resumen"]) ? $item["temporalidad_resumen"] : null;
	if (!$resumen) {
		$temporalidad = obtenerTemporalidadItemPlanConsulta($item);
		$resumen = array(
			"intervalo" => $temporalidad["intervalo"],
			"fecha_sugerida" => "",
			"fecha_planificada" => isset($item["fecha_planificada"]) ? normalizarFechaPlanConsulta($item["fecha_planificada"]) : "",
			"fecha_inicio_tratamiento" => isset($item["fecha_inicio_tratamiento"]) ? normalizarFechaPlanConsulta($item["fecha_inicio_tratamiento"]) : "",
			"fecha_ultima_evolucion" => isset($item["fecha_ultima_evolucion"]) ? normalizarFechaPlanConsulta($item["fecha_ultima_evolucion"]) : "",
			"fecha_origen" => "Proyectado",
			"fecha_origen_clase" => "proyectado",
			"dias_desde_anterior" => 0,
			"estado" => $temporalidad["configurada"] ? "Proyectado" : "Sin configuracion",
			"estado_clase" => $temporalidad["configurada"] ? "proyectado" : "sin-configuracion",
			"configurada" => $temporalidad["configurada"],
			"tipo" => $temporalidad["tipo_label"]
		);
	}
	$chips = array();
	$chips[] = "<span class='tratamiento-temporalidad-chip'>Cada ".(int)$resumen["intervalo"]." dias</span>";
	if (normalizarFechaPlanConsulta($resumen["fecha_sugerida"]) != "") {
		$chips[] = "<span class='tratamiento-temporalidad-chip'>Sugerida ".htmlspecialchars(formatearFechaPlanConsulta($resumen["fecha_sugerida"]), ENT_QUOTES, "UTF-8")."</span>";
	}
	if (normalizarFechaPlanConsulta($resumen["fecha_planificada"]) != "") {
		$chips[] = "<span class='tratamiento-temporalidad-chip tratamiento-temporalidad-chip--agenda'>Agenda ".htmlspecialchars(formatearFechaPlanConsulta($resumen["fecha_planificada"]), ENT_QUOTES, "UTF-8")."</span>";
	}
	if (normalizarFechaPlanConsulta($resumen["fecha_inicio_tratamiento"]) != "") {
		$chips[] = "<span class='tratamiento-temporalidad-chip tratamiento-temporalidad-chip--real'>Inicio real ".htmlspecialchars(formatearFechaPlanConsulta($resumen["fecha_inicio_tratamiento"]), ENT_QUOTES, "UTF-8")."</span>";
	}
	if (normalizarFechaPlanConsulta($resumen["fecha_ultima_evolucion"]) != "") {
		$chips[] = "<span class='tratamiento-temporalidad-chip tratamiento-temporalidad-chip--real'>Evolucion ".htmlspecialchars(formatearFechaPlanConsulta($resumen["fecha_ultima_evolucion"]), ENT_QUOTES, "UTF-8")."</span>";
	}
	if ((int)$resumen["dias_desde_anterior"] > 0) {
		$chips[] = "<span class='tratamiento-temporalidad-chip'>D+".(int)$resumen["dias_desde_anterior"]." desde anterior</span>";
	}
	$estadoClase = htmlspecialchars((string)$resumen["estado_clase"], ENT_QUOTES, "UTF-8");
	$chips[] = "<span class='tratamiento-temporalidad-chip tratamiento-temporalidad-chip--".$estadoClase."'>".htmlspecialchars((string)$resumen["estado"], ENT_QUOTES, "UTF-8")."</span>";
	if (empty($resumen["configurada"])) {
		$chips[] = "<span class='tratamiento-temporalidad-chip tratamiento-temporalidad-chip--sin-configuracion'>Sin temporalidad configurada</span>";
	}
	return "<div class='".$claseBase." tratamiento-temporalidad-chips'>".implode("", $chips)."</div>";
}

function atributosLaboratorioTratamientoConsulta($item, $ubicacion, $codProducto, $alcance)
{
	if (empty($item["es_protesis_laboratorio"])) { return ""; }
	$categoria = isset($item["categoria_laboratorio"]) ? $item["categoria_laboratorio"] : "PROTESIS";
	$cantidad = isset($item["cantidad_detalle_laboratorio"])
		? $item["cantidad_detalle_laboratorio"]
		: (isset($item["cantidad_detalle"]) ? $item["cantidad_detalle"] : 1);
	$cantidadNumero = (float)str_replace(",", ".", (string)$cantidad);
	$requiereRegularizacion = abs($cantidadNumero - 1.0) > 0.0001;
	return " data-tratamiento-laboratorio='1'"
		." data-laboratorio-ubicacion-falta='".(!empty($ubicacion["falta"]) ? "1" : "0")."'"
		." data-laboratorio-trabajo-activo='".(!empty($item["tiene_trabajo_laboratorio_activo"]) ? "1" : "0")."'"
		." data-laboratorio-antecedente-historico='".(!empty($item["tiene_antecedente_laboratorio_historico"]) ? "1" : "0")."'"
		." data-laboratorio-regularizacion='".($requiereRegularizacion ? "1" : "0")."'"
		." data-tratamiento-cantidad='".htmlspecialchars((string)$cantidad, ENT_QUOTES, "UTF-8")."'"
		." data-tratamiento-producto='".htmlspecialchars((string)$codProducto, ENT_QUOTES, "UTF-8")."'"
		." data-tratamiento-alcance='".htmlspecialchars((string)$alcance, ENT_QUOTES, "UTF-8")."'"
		." data-tratamiento-categoria='".htmlspecialchars((string)$categoria, ENT_QUOTES, "UTF-8")."'";
}

function renderizarAccionLaboratorioTratamientoConsulta($item, $ubicacion, $modo = "accion")
{
	if (empty($item["es_protesis_laboratorio"])) { return ""; }
	$faltaUbicacion = !empty($ubicacion["falta"]);
	$cantidad = isset($item["cantidad_detalle_laboratorio"])
		? $item["cantidad_detalle_laboratorio"]
		: (isset($item["cantidad_detalle"]) ? $item["cantidad_detalle"] : 1);
	$cantidadNumero = (float)str_replace(",", ".", (string)$cantidad);
	$requiereRegularizacion = abs($cantidadNumero - 1.0) > 0.0001;
	if ($modo === "microhilo" && !empty($item["tiene_trabajo_laboratorio_activo"])) {
		return "<aside class='consulta-laboratorio-microhilo-slot' data-laboratorio-mini-hilo-slot>"
			."<section class='consulta-laboratorio-microhilo is-loading' data-laboratorio-mini-hilo "
			."data-laboratorio-mini-hilo-estado='cargando' aria-busy='true' aria-live='polite'>"
			."<span class='consulta-laboratorio-microhilo__loader' aria-hidden='true'></span>"
			."<span>Consultando hilo del trabajo...</span>"
			."</section></aside>";
	}
	$resumen = "";
	if (!empty($item["tiene_trabajo_laboratorio_activo"])) {
		$texto = "Abrir trabajo de laboratorio";
	} elseif (!empty($item["tiene_antecedente_laboratorio_historico"])) {
		$texto = "Ver antecedente de laboratorio";
	} elseif ($requiereRegularizacion) {
		$cantidadTexto = formatearCantidadTratamientoConsulta($cantidad);
		$esCantidadAgrupada = $cantidadNumero >= 2
			&& $cantidadNumero <= 32
			&& abs($cantidadNumero - round($cantidadNumero)) < 0.0001;
		$texto = $esCantidadAgrupada
			? "Designar ".intval(round($cantidadNumero))." trabajos"
			: "Regularizar para laboratorio";
		$resumen = $esCantidadAgrupada
			? $cantidadTexto." trabajos &middot; Un selector por trabajo"
			: $cantidadTexto." unidades registradas &middot; Administraci&oacute;n";
	} else {
		$texto = $faltaUbicacion ? "Asignar ubicaci&oacute;n para iniciar" : "Preparar trabajo de laboratorio";
	}
	return "<div class='consulta-laboratorio-card-action'>"
		."<button type='button' class='consulta-laboratorio-card-action__button' data-tratamiento-laboratorio-accion "
		."onclick='event.stopPropagation(); tratamientoLaboratorioClinicoAbrirDesdeTarjeta(this)' "
		."aria-label='".strip_tags($texto)."'>"
		."<i class='fa-solid fa-microscope' aria-hidden='true'></i>"
		."<span data-tratamiento-laboratorio-accion-texto>".$texto."</span>"
		."</button>"
		."<span class='consulta-laboratorio-card-action__resumen".($requiereRegularizacion ? " is-regularization" : "")."' data-tratamiento-laboratorio-resumen aria-live='polite'".($resumen == "" ? " hidden" : "").">".$resumen."</span>"
		."</div>";
}

function renderizarItemPlanTratamientoConsulta($tratamiento, $numero, &$styleName)
{
	$styleName = CargarStyleTable($styleName);
	$nombre = htmlspecialchars($tratamiento["nombre_producto"], ENT_QUOTES, "UTF-8");
	$nombreJs = htmlspecialchars(json_encode((string)$tratamiento["nombre_producto"]), ENT_QUOTES, "UTF-8");
	$descripcion = htmlspecialchars($tratamiento["descripcion"], ENT_QUOTES, "UTF-8");
	$ayuda = htmlspecialchars(ayudaCardPlanTratamientoConsulta($tratamiento), ENT_QUOTES, "UTF-8");
	$estadoClase = $tratamiento["estado_clase"];
	$estadoTexto = htmlspecialchars($tratamiento["estado_texto"], ENT_QUOTES, "UTF-8");
	$avance = (int)$tratamiento["avance"];
	$cod_detalle = htmlspecialchars($tratamiento["cod_detalle"], ENT_QUOTES, "UTF-8");
	$venta_id = htmlspecialchars((string)$tratamiento["venta_id"], ENT_QUOTES, "UTF-8");
	$cod_producto = htmlspecialchars((string)$tratamiento["cod_producto"], ENT_QUOTES, "UTF-8");
	$alcance_odontologico = htmlspecialchars((string)$tratamiento["alcance_odontologico"], ENT_QUOTES, "UTF-8");
	$badge_riesgo_financiero = ProductoRiesgoFinancieroBadgeHtml($tratamiento["nivel_riesgo_financiero"], "consulta-treatment-risk");
	$descripcionHtml = $descripcion != "" ? "<span class='plan-tratamientos-descripcion'>".$descripcion."</span>" : "";
	$ubicacionHtml = renderizarUbicacionOdontogramaConsulta($tratamiento["ubicacion_odontograma"], "plan-tratamientos-ubicacion");
	$temporalidadHtml = renderTemporalidadTratamientoConsulta($tratamiento, "plan-tratamientos-temporalidad");
	$esLaboratorio = !empty($tratamiento["es_protesis_laboratorio"]);
	$atributosLaboratorio = atributosLaboratorioTratamientoConsulta($tratamiento, $tratamiento["ubicacion_odontograma"], $tratamiento["cod_producto"], $tratamiento["alcance_odontologico"]);
	$accionLaboratorioHtml = renderizarAccionLaboratorioTratamientoConsulta($tratamiento, $tratamiento["ubicacion_odontograma"]);
	$asignarUbicacionHtml = (!empty($tratamiento["ubicacion_odontograma"]["falta"]) && !$esLaboratorio)
		? "<button type='button' class='odontograma-plan-ubicar-btn' onclick='event.stopPropagation(); odontogramaAsignarTratamientoFicha(\"".$cod_detalle."\",\"".$venta_id."\",\"".$cod_producto."\",".$nombreJs.",\"".$alcance_odontologico."\")'>Asignar ubicaci&oacute;n</button>"
		: "";

return "
<table class='$styleName consulta-treatment-row plan-tratamientos-card plan-tratamientos-card--".$tratamiento["grupo_plan"]." consulta-treatment-row--$estadoClase' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatostrConsultaTratamiento(this)' data-detalle-tratamiento='".$cod_detalle."' data-tratamiento-venta='".$venta_id."' data-tratamiento-nombre='".$nombre."' data-tratamiento-estado='".$estadoTexto."' data-tratamiento-estado-clase='".$estadoClase."' data-tratamiento-avance='".$avance."'".$atributosLaboratorio.">
<td class='consulta-treatment-row__qty plan-tratamientos-numero' style='width:5%;text-aling:center'>".$numero."</td>
<td class='consulta-treatment-row__name plan-tratamientos-info' style='width:60%'>
<strong>".$nombre."</strong>
<span class='plan-tratamientos-ayuda-card'>".$ayuda."</span>
".$descripcionHtml."
<div class='consulta-treatment-location-actions'>
".$ubicacionHtml."
".$asignarUbicacionHtml."
".$accionLaboratorioHtml."
</div>
".$temporalidadHtml."
</td>
<td class='consulta-treatment-row__progress plan-tratamientos-badges' style='width:25%;text-align: center;'>
".$badge_riesgo_financiero."
<span class='consulta-treatment-status consulta-treatment-status--$estadoClase'>$estadoTexto</span>
<span class='consulta-treatment-percent plan-tratamientos-progreso'>$avance%</span>
</td>
<td id='td_datos_1' style='Display:none'>".$avance."</td>
<td id='td_id_1' style='display:none'>".$cod_detalle."</td>
</tr>
</table>";
}

function renderizarSeccionPlanTratamientoConsulta($titulo, $ayuda, $tratamientos, $vacio, $grupo, &$styleName)
{
	$pagina = "
<section class='plan-tratamientos-seccion plan-tratamientos-seccion--".$grupo."' data-plan-seccion='".$grupo."'>
	<div class='plan-tratamientos-seccion-head'>
		<div>
			<strong>".$titulo."</strong>
			<span>".$ayuda."</span>
		</div>
		<em>".count($tratamientos)."</em>
	</div>
	<div class='plan-tratamientos-lista'>";
	if (count($tratamientos) == 0) {
		$pagina .= "<div class='plan-tratamientos-vacio'>".$vacio."</div>";
	} else {
		$numero = 1;
		foreach ($tratamientos as $tratamiento) {
			$pagina .= renderizarItemPlanTratamientoConsulta($tratamiento, $numero, $styleName);
			$numero++;
		}
	}
	$pagina .= "
	</div>
</section>";
	return $pagina;
}

function textoEstadoPlanDefinitivoConsulta($estado)
{
	$estado = strtolower(trim((string)$estado));
	if ($estado == "pendiente_validacion") { return "Pendiente de validaci&oacute;n cl&iacute;nica"; }
	if ($estado == "definido") { return "Definido"; }
	if ($estado == "modificado") { return "Modificado"; }
	if ($estado == "borrador") { return "Borrador"; }
	return "Sin definir";
}

function etiquetaEstadoPlanDefinitivoConsulta($estado,$version)
{
	$estado = strtolower(trim((string)$estado));
	$version = (int)$version;
	if ($estado == "pendiente_validacion") { return "Vigente &middot; Pendiente de validaci&oacute;n cl&iacute;nica"; }
	if ($estado == "borrador") { return "Borrador"; }
	if ($estado == "modificado") { return "Modificado &middot; Versi&oacute;n ".$version; }
	if ($estado == "definido") { return "Definido &middot; Ruta vigente"; }
	return "Sin definir";
}

function etiquetaTabPlanDefinitivoConsulta($estado,$version,$existe)
{
	if (!$existe) { return "Plan madre &middot; Pendiente"; }
	$estado = strtolower(trim((string)$estado));
	$version = (int)$version;
	if ($estado == "pendiente_validacion") { return "Plan madre &middot; Pendiente validaci&oacute;n"; }
	if ($estado == "borrador") { return "Plan madre &middot; Borrador"; }
	if ($estado == "modificado") { return "Plan madre &middot; Versi&oacute;n ".$version; }
	return "Plan madre &middot; Asignado";
}

function obtenerItemsPlanDefinitivoConsulta($mysqli,$plan_id)
{
	$selectRiesgo = ProductoRiesgoFinancieroSelectSql($mysqli, "pr");
	$selectTemporalidad = ProductoTemporalidadSelectSqlConsulta($mysqli, "pr");
	$selectFechaPlanificada = FechaPlanificadaTratamientoSelectSqlConsulta($mysqli, "dtv");
	$selectFechaEvolucion = FechaEvolucionTratamientoSelectSqlConsulta($mysqli, "dtv");
	$selectLaboratorio = ProductoLaboratorioSelectSqlConsulta($mysqli, "pr", "ca");
	$selectEstadoLaboratorio = EstadoLaboratorioSelectSqlConsulta($mysqli, "dtv");
	$selectAlcanceOdontologico = columnaExisteConsulta($mysqli, "producto", "alcance_odontologico")
		? "pr.alcance_odontologico"
		: "'no_requiere' AS alcance_odontologico";
	$sql = "SELECT i.*, vt.num_factura, vt.apodo, vt.fecha_venta, vt.cod_clienteFK,
		dtv.estado, dtv.estado_tratamiento, dtv.progreso_porcentaje, dtv.cantidad_detalle AS cantidad_detalle_laboratorio,
		COALESCE(pr.nombre_producto, i.nombre_tratamiento_snapshot) AS nombre_producto_actual,
		pr.precio_producto,
		".$selectAlcanceOdontologico.", ".$selectLaboratorio.", ".$selectEstadoLaboratorio.", ".$selectRiesgo.", ".$selectTemporalidad.", ".$selectFechaPlanificada.", ".$selectFechaEvolucion."
		FROM plan_definitivo_tratamiento_items i
		INNER JOIN detalle_venta dtv ON dtv.cod_detalle = i.detalle_venta_id
		INNER JOIN venta vt ON vt.cod_venta = i.venta_id
		LEFT JOIN producto pr ON pr.cod_producto = dtv.cod_productoFK
		LEFT JOIN categoria ca ON ca.cod_categoria = pr.cod_categoriaFK
		WHERE i.plan_definitivo_id = ? AND i.activo = 1
		".ProductoClinicoWhereSqlConsulta("pr")."
		ORDER BY i.orden ASC, i.id ASC";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) { return array(); }
	$stmt->bind_param("s", $plan_id);
	if (!$stmt->execute()) { return array(); }
	$result = $stmt->get_result();
	$items = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$avance = normalizarPorcentajePlanTratamientoConsulta($row["progreso_porcentaje"]);
		$estadoClase = normalizarEstadoPlanTratamientoConsulta($row["estado"], $row["estado_tratamiento"], $avance);
		$row["estado_clase"] = $estadoClase;
		$row["estado_texto"] = textoEstadoPlanTratamientoConsulta($estadoClase);
		$row["avance"] = $avance;
		$row["nombre_producto_actual"] = mb_convert_encoding((string)$row["nombre_producto_actual"], 'UTF-8', 'ISO-8859-1');
		$row["nombre_tratamiento_snapshot"] = mb_convert_encoding((string)$row["nombre_tratamiento_snapshot"], 'UTF-8', 'ISO-8859-1');
		$row["observacion_clinica"] = mb_convert_encoding((string)$row["observacion_clinica"], 'UTF-8', 'ISO-8859-1');
		$row["etapa"] = mb_convert_encoding((string)$row["etapa"], 'UTF-8', 'ISO-8859-1');
		$row["apodo"] = mb_convert_encoding((string)$row["apodo"], 'UTF-8', 'ISO-8859-1');
		$row["num_factura"] = mb_convert_encoding((string)$row["num_factura"], 'UTF-8', 'ISO-8859-1');
		$row["nivel_riesgo_financiero"] = ProductoRiesgoFinancieroNormalizar(isset($row["nivel_riesgo_financiero"]) ? $row["nivel_riesgo_financiero"] : $row["nivel_riesgo_snapshot"]);
		$row["alcance_odontologico"] = isset($row["alcance_odontologico"]) ? $row["alcance_odontologico"] : "no_requiere";
		$row["categoria_laboratorio"] = isset($row["categoria_laboratorio"]) ? mb_convert_encoding((string)$row["categoria_laboratorio"], 'UTF-8', 'ISO-8859-1') : "";
		$row["requiere_laboratorio"] = isset($row["requiere_laboratorio_efectivo"]) ? (int)$row["requiere_laboratorio_efectivo"] : 0;
		$row["es_protesis_laboratorio"] = EsCategoriaProtesisLaboratorioConsulta($row["categoria_laboratorio"], $row["requiere_laboratorio"]);
		$row["tiene_trabajo_laboratorio_activo"] = !empty($row["tiene_trabajo_laboratorio_activo"]);
		$row["tiene_antecedente_laboratorio_historico"] = !empty($row["tiene_antecedente_laboratorio_historico"]);
		$row["precio_producto"] = isset($row["precio_producto"]) ? (float)$row["precio_producto"] : 0;
		$row["usa_temporalidad"] = isset($row["usa_temporalidad"]) ? (int)$row["usa_temporalidad"] : 0;
		$row["temporalidad_tipo"] = isset($row["temporalidad_tipo"]) ? mb_convert_encoding((string)$row["temporalidad_tipo"], 'UTF-8', 'ISO-8859-1') : "";
		$row["temporalidad_intervalo_recomendado"] = isset($row["temporalidad_intervalo_recomendado"]) ? (int)$row["temporalidad_intervalo_recomendado"] : 20;
		$row["temporalidad_intervalo_minimo"] = isset($row["temporalidad_intervalo_minimo"]) ? (int)$row["temporalidad_intervalo_minimo"] : 15;
		$row["temporalidad_intervalo_maximo"] = isset($row["temporalidad_intervalo_maximo"]) ? (int)$row["temporalidad_intervalo_maximo"] : 40;
		$row["temporalidad_sesiones_estimadas"] = isset($row["temporalidad_sesiones_estimadas"]) ? (int)$row["temporalidad_sesiones_estimadas"] : 0;
		$row["temporalidad_duracion_sillon"] = isset($row["temporalidad_duracion_sillon"]) ? (int)$row["temporalidad_duracion_sillon"] : 0;
		$row["temporalidad_observacion"] = isset($row["temporalidad_observacion"]) ? mb_convert_encoding((string)$row["temporalidad_observacion"], 'UTF-8', 'ISO-8859-1') : "";
		$row["fecha_planificada"] = isset($row["fecha_planificada"]) ? (string)$row["fecha_planificada"] : "";
		$row["fecha_inicio_tratamiento"] = isset($row["fecha_inicio_tratamiento"]) ? (string)$row["fecha_inicio_tratamiento"] : "";
		$row["fecha_ultima_evolucion"] = isset($row["fecha_ultima_evolucion"]) ? (string)$row["fecha_ultima_evolucion"] : "";
		$row["ubicacion_odontograma"] = obtenerUbicacionOdontogramaDetalleConsulta($mysqli, $row["detalle_venta_id"], $row["alcance_odontologico"]);
		$items[] = $row;
	}
	return $items;
}

function renderizarItemPlanDefinitivoConsulta($item,$numero,$plan_id,$editable)
{
	$itemId = htmlspecialchars((string)$item["id"], ENT_QUOTES, "UTF-8");
	$planId = htmlspecialchars((string)$plan_id, ENT_QUOTES, "UTF-8");
	$detalleOdontograma = htmlspecialchars((string)$item["detalle_venta_id"], ENT_QUOTES, "UTF-8");
	$nombre = trim((string)$item["nombre_producto_actual"]) != "" ? $item["nombre_producto_actual"] : $item["nombre_tratamiento_snapshot"];
	$nombreHtml = htmlspecialchars($nombre, ENT_QUOTES, "UTF-8");
	$nombreJs = htmlspecialchars(json_encode((string)$nombre), ENT_QUOTES, "UTF-8");
	$venta = htmlspecialchars((string)$item["venta_id"], ENT_QUOTES, "UTF-8");
	$ventaVisible = trim((string)$item["num_factura"]) != "" ? (string)$item["num_factura"] : (string)$item["venta_id"];
	$ventaVisibleHtml = htmlspecialchars($ventaVisible, ENT_QUOTES, "UTF-8");
	$producto = htmlspecialchars((string)$item["producto_id"], ENT_QUOTES, "UTF-8");
	$alcanceOdontologico = htmlspecialchars((string)$item["alcance_odontologico"], ENT_QUOTES, "UTF-8");
	$origen = (string)$item["origen"] == "venta_anexada" ? "Venta anexada" : "Plan principal";
	$origenHtml = htmlspecialchars($origen, ENT_QUOTES, "UTF-8");
	$estadoClase = htmlspecialchars((string)$item["estado_clase"], ENT_QUOTES, "UTF-8");
	$estadoTexto = htmlspecialchars((string)$item["estado_texto"], ENT_QUOTES, "UTF-8");
	$avance = (int)$item["avance"];
	$observacion = trim((string)$item["observacion_clinica"]);
	$observacionHtml = $observacion != "" ? "<p class='plan-definitivo-observacion'><strong>Observaci&oacute;n:</strong> ".nl2br(htmlspecialchars($observacion, ENT_QUOTES, "UTF-8"))."</p>" : "";
	$etapa = trim((string)$item["etapa"]);
	$etapaHtml = $etapa != "" ? "<span class='plan-definitivo-etapa'>".htmlspecialchars($etapa, ENT_QUOTES, "UTF-8")."</span>" : "";
	$badge_riesgo_financiero = ProductoRiesgoFinancieroBadgeHtml($item["nivel_riesgo_financiero"], "consulta-treatment-risk");
	$riesgoValor = ProductoRiesgoFinancieroNormalizar($item["nivel_riesgo_financiero"]);
	$riesgoTexto = ProductoRiesgoFinancieroTexto($riesgoValor);
	$editableClass = $editable ? "" : " plan-definitivo-readonly-item";
	$finalizadoClass = ($estadoClase == "completado" || $estadoClase == "cancelado") ? " plan-ruta-finalizado" : "";
	$nodoTexto = ($estadoClase == "completado") ? "&#10003;" : $numero;
	$nodoTitulo = ($estadoClase == "completado") ? "Paso ".$numero." completado" : "Paso ".$numero;
	$ubicacionHtml = renderizarUbicacionOdontogramaConsulta($item["ubicacion_odontograma"], "plan-definitivo-ubicacion");
	$temporalidadHtml = renderTemporalidadTratamientoConsulta($item, "plan-definitivo-temporalidad");
	$esLaboratorio = !empty($item["es_protesis_laboratorio"]);
	$atributosLaboratorio = atributosLaboratorioTratamientoConsulta($item, $item["ubicacion_odontograma"], $item["producto_id"], $item["alcance_odontologico"]);
	$laboratorioActivo = $esLaboratorio && !empty($item["tiene_trabajo_laboratorio_activo"]);
	$accionLaboratorioHtml = $laboratorioActivo
		? "" : renderizarAccionLaboratorioTratamientoConsulta($item, $item["ubicacion_odontograma"]);
	$microHiloLaboratorioHtml = $laboratorioActivo
		? renderizarAccionLaboratorioTratamientoConsulta($item, $item["ubicacion_odontograma"], "microhilo")
		: ($esLaboratorio
			? "<aside class='consulta-laboratorio-microhilo-slot' data-laboratorio-mini-hilo-slot hidden></aside>"
			: "");
	$claseMicroHiloLaboratorio = $laboratorioActivo ? " is-laboratorio-microhilo" : "";
	$asignarUbicacionHtml = (!empty($item["ubicacion_odontograma"]["falta"]) && !$esLaboratorio)
		? "<button type='button' class='odontograma-plan-ubicar-btn' onclick='event.stopPropagation(); odontogramaAsignarTratamientoFicha(\"".$detalleOdontograma."\",\"".$venta."\",\"".$producto."\",".$nombreJs.",\"".$alcanceOdontologico."\")'>Asignar ubicaci&oacute;n</button>"
		: "";

	return "
<article class='plan-definitivo-item plan-ruta-item plan-definitivo-item--".$estadoClase.$editableClass.$finalizadoClass."' role='listitem' onclick='obtenerDatosPlanDefinitivoTratamientoConsulta(event,this)' data-plan-id='".$planId."' data-plan-item='".$itemId."' data-plan-numero='".$numero."' data-detalle-odontograma='".$detalleOdontograma."' data-detalle-tratamiento='".$detalleOdontograma."' data-tratamiento-venta='".$venta."' data-tratamiento-venta-visible='".$ventaVisibleHtml."' data-tratamiento-origen='".$origenHtml."' data-tratamiento-riesgo='".$riesgoValor."' data-tratamiento-riesgo-texto='".htmlspecialchars($riesgoTexto, ENT_QUOTES, "UTF-8")."' data-tratamiento-nombre='".$nombreHtml."' data-tratamiento-estado='".$estadoTexto."' data-tratamiento-estado-clase='".$estadoClase."' data-tratamiento-avance='".$avance."' data-observacion='".htmlspecialchars($observacion, ENT_QUOTES, "UTF-8")."'".$atributosLaboratorio.">
	<div class='plan-ruta-nodo' title='".$nodoTitulo."'><span>".$nodoTexto."</span></div>
	<div class='plan-definitivo-item__body".$claseMicroHiloLaboratorio."'>
		<div class='plan-definitivo-item__main'>
			<div class='plan-definitivo-item__top'>
				<strong>".$nombreHtml."</strong>
				<span>Paso ".$numero." de la ruta cl&iacute;nica</span>
			</div>
			<div class='plan-ruta-origen'>Venta #".$ventaVisibleHtml." &middot; ".$origenHtml."</div>
			<div class='plan-ruta-ubicacion'>".$ubicacionHtml.$asignarUbicacionHtml.$accionLaboratorioHtml."</div>
			".$temporalidadHtml."
			<div class='plan-definitivo-item__badges'>
				".$badge_riesgo_financiero."
				<span class='consulta-treatment-status consulta-treatment-status--".$estadoClase."'>".$estadoTexto."</span>
				<span class='consulta-treatment-percent'>".$avance."%</span>
				".$etapaHtml."
			</div>
			".$observacionHtml."
		</div>
		".$microHiloLaboratorioHtml."
	</div>
	<div class='plan-definitivo-item__actions plan-definitivo-edit-only'>
		<button type='button' class='plan-definitivo-icon-btn plan-definitivo-order-btn' title='Subir' onpointerdown='event.stopPropagation()' onclick='moverItemPlanDefinitivoConsulta(event,\"".$planId."\",\"".$itemId."\",-1)'>&uarr;</button>
		<button type='button' class='plan-definitivo-icon-btn plan-definitivo-order-btn' title='Bajar' onpointerdown='event.stopPropagation()' onclick='moverItemPlanDefinitivoConsulta(event,\"".$planId."\",\"".$itemId."\",1)'>&darr;</button>
		<button type='button' class='plan-definitivo-icon-btn' title='Editar observacion' onpointerdown='event.stopPropagation()' onclick='editarObservacionItemPlanDefinitivoConsulta(event,\"".$planId."\",\"".$itemId."\")'>&hellip;</button>
		<button type='button' class='plan-definitivo-icon-btn plan-definitivo-icon-btn--remove' title='Quitar de esta ruta' onpointerdown='event.stopPropagation()' onclick='quitarItemPlanDefinitivoConsulta(event,\"".$planId."\",\"".$itemId."\")'>x</button>
	</div>
</article>";
}

function renderizarListaPlanDefinitivoConsulta($items,$plan_id,$editable)
{
	$pagina = "<div class='plan-definitivo-lista plan-definitivo-ruta' role='list'>";
	if (count($items) == 0) {
		$pagina .= "<div class='plan-definitivo-empty-inline'>La ruta cl&iacute;nica todav&iacute;a no tiene tratamientos.</div>";
	}
	$numero = 1;
	foreach ($items as $item) {
		$pagina .= renderizarItemPlanDefinitivoConsulta($item,$numero,$plan_id,$editable);
		$numero++;
	}
	$pagina .= "</div>";
	return $pagina;
}

function renderizarPlanDefinitivoConsulta($mysqli,$cod_venta,$tratamientosSugeridos,&$metaPlanDefinitivo)
{
	$metaPlanDefinitivo = array(
		"existe" => false,
		"estado" => "sin_definir",
		"version" => 0,
		"tab_label" => "Plan madre &middot; Pendiente",
		"vista_inicial" => "sugerido"
	);
	if (!planDefinitivoTablasDisponiblesConsulta($mysqli)) {
		$metaPlanDefinitivo["tab_label"] = "Plan madre &middot; Pendiente";
		return "
<div class='plan-definitivo-panel plan-definitivo-panel--empty'>
	<div class='plan-definitivo-empty'>
		<strong>Plan madre pendiente de activar</strong>
		<span>Aplic&aacute; la actualizaci&oacute;n SQL del plan madre para habilitar esta ruta cl&iacute;nica.</span>
	</div>
</div>";
	}

	$contexto = obtenerContextoPlanDefinitivoConsulta($mysqli,$cod_venta);
	if ($contexto) {
		$metaPlanDefinitivo["venta_base_id"] = (string)$contexto["venta_base_id"];
		$metaPlanDefinitivo["venta_actual_id"] = (string)$contexto["venta_actual_id"];
	}
	$plan = $contexto ? obtenerPlanMadreAsignadoVentaRowConsulta($mysqli,$contexto["paciente_id"],$contexto["cedula"],$cod_venta) : obtenerPlanDefinitivoActivoConsulta($mysqli,$cod_venta);
	if (!$plan) {
		$ventaBaseCrear = $contexto ? (string)$contexto["venta_base_id"] : (string)$cod_venta;
		$ventaBase = htmlspecialchars($ventaBaseCrear, ENT_QUOTES, "UTF-8");
		$codCliente = $contexto ? htmlspecialchars((string)$contexto["paciente_id"], ENT_QUOTES, "UTF-8") : "";
		return "
<div class='plan-definitivo-panel plan-definitivo-panel--empty' data-plan-venta-base='".$ventaBase."'>
	<div class='plan-definitivo-header'>
		<div>
			<h4>Plan madre pendiente</h4>
			<span>Esta venta todav&iacute;a no tiene sus tratamientos anexados a un plan madre.</span>
		</div>
		<em class='plan-definitivo-status plan-definitivo-status--sin-definir'>Pendiente</em>
	</div>
	<div class='plan-definitivo-empty'>
		<strong>Anex&aacute; los tratamientos a un plan madre</strong>
		<span>Eleg&iacute; el plan madre donde se fusionan los tratamientos de esta venta, o cre&aacute; uno nuevo para otro Apodo/beneficiario.</span>
		<div class='plan-definitivo-empty__actions'>
			<button type='button' class='plan-definitivo-primary' onclick='abrirAsignarPlanMadreVentaConsulta(\"".$ventaBase."\",\"".$codCliente."\",\"\")'>Anexar a plan madre</button>
			<button type='button' class='plan-definitivo-secondary' onclick='crearPlanDefinitivoDesdeSugeridoConsulta(\"".$ventaBase."\")'>Crear plan madre desde esta venta</button>
			<button type='button' class='plan-definitivo-secondary' onclick='mostrarGuiaPlanDefinitivoConsulta()'>Gu&iacute;a r&aacute;pida</button>
		</div>
	</div>
</div>";
	}

	$planId = htmlspecialchars((string)$plan["id"], ENT_QUOTES, "UTF-8");
	$estado = strtolower((string)$plan["estado"]);
	$estadoTexto = textoEstadoPlanDefinitivoConsulta($estado);
	$estadoCompleto = etiquetaEstadoPlanDefinitivoConsulta($estado,(int)$plan["version_actual"]);
	$doctor = trim((string)$plan["doctor_nombre"]) != "" ? $plan["doctor_nombre"] : "Doctor actual";
	$doctorHtml = htmlspecialchars($doctor, ENT_QUOTES, "UTF-8");
	$version = (int)$plan["version_actual"];
	$fechaActualizacion = trim((string)$plan["fecha_actualizacion"]) != "" ? date("d/m/Y H:i", strtotime($plan["fecha_actualizacion"])) : "";
	$editable = ($estado == "borrador");
	$editableClass = $editable ? " is-editing" : "";
	$items = obtenerItemsPlanDefinitivoConsulta($mysqli,$plan["id"]);
	$metaPlanDefinitivo["existe"] = true;
	$metaPlanDefinitivo["estado"] = $estado;
	$metaPlanDefinitivo["version"] = $version;
	$metaPlanDefinitivo["venta_base_id"] = (string)$plan["venta_base_id"];
	$metaPlanDefinitivo["tab_label"] = etiquetaTabPlanDefinitivoConsulta($estado,$version,true);
	$metaPlanDefinitivo["vista_inicial"] = "definitivo";
	$esVentaBaseCentral = ((string)$plan["venta_base_id"] == (string)$cod_venta);
	$planMadreNumero = isset($plan["plan_madre_numero"]) ? (int)$plan["plan_madre_numero"] : 0;
	$planMadreApodo = isset($plan["plan_madre_apodo"]) ? trim((string)$plan["plan_madre_apodo"]) : "Sin beneficiario";
	$planMadreTitulo = $planMadreNumero > 0 ? "Plan madre #".$planMadreNumero : "Plan madre";
	$planMadreTituloHtml = htmlspecialchars($planMadreTitulo, ENT_QUOTES, "UTF-8");
	$planMadreApodoHtml = htmlspecialchars($planMadreApodo, ENT_QUOTES, "UTF-8");
	$ayudaEstado = "Esta venta pertenece a ".$planMadreTituloHtml." - ".$planMadreApodoHtml.". Cualquier modificaci&oacute;n quedar&aacute; registrada.";
	if ($estado == "borrador") {
		$ayudaEstado = "Este plan madre todav&iacute;a no fue confirmado. Pod&eacute;s continuar la edici&oacute;n y confirmar la ruta cuando est&eacute; lista.";
	}
	if ($estado == "pendiente_validacion") {
		$ayudaEstado = "Plan madre vigente generado desde agenda con sugerencia autom&aacute;tica. Puede usarse para agendar y atender, pero queda pendiente de validaci&oacute;n del jefe cl&iacute;nico.";
	}
	if ($estado == "modificado") {
		$ayudaEstado = "La ruta fue modificada con trazabilidad. El orden cl&iacute;nico se conserva seg&uacute;n la versi&oacute;n vigente.";
	}
	if (!$esVentaBaseCentral) {
		$ventaBaseCentral = htmlspecialchars((string)$plan["venta_base_id"], ENT_QUOTES, "UTF-8");
		$ayudaEstado = "Esta venta est&aacute; asignada a ".$planMadreTituloHtml." - ".$planMadreApodoHtml.", creado desde la venta #".$ventaBaseCentral.".";
	}

	$accionesLectura = "";
	if ($estado == "definido" || $estado == "modificado") {
		$accionesLectura = "<button type='button' class='plan-definitivo-secondary plan-definitivo-readonly-action' onclick='editarPlanDefinitivoConsulta(\"".$planId."\")'>Editar ruta</button>";
	}
	$accionConfirmar = "";
	if ($estado == "borrador") {
		$accionConfirmar = "<button type='button' class='plan-definitivo-primary plan-definitivo-edit-only' onclick='confirmarPlanDefinitivoConsulta(\"".$planId."\")'>Confirmar plan</button>";
	}
	if ($estado == "pendiente_validacion") {
		$accionConfirmar = "<button type='button' class='plan-definitivo-primary' onclick='confirmarPlanDefinitivoConsulta(\"".$planId."\")'>Validar plan madre</button>";
	}
	$botonConfirmarOrden = "<button type='button' class='plan-definitivo-primary plan-definitivo-edit-only plan-definitivo-save-order-btn' onclick='guardarOrdenPlanDefinitivoConsulta(\"".$planId."\")'>Confirmar orden</button>";
	$botonCancelarEdicion = "<button type='button' class='plan-definitivo-secondary plan-definitivo-edit-only' onclick='cancelarEdicionOrdenPlanDefinitivoConsulta(\"".$planId."\")'>Cancelar edici&oacute;n</button>";
	$fechaInicioPlan = obtenerFechaVentaBasePlanDefinitivoConsulta($mysqli,$plan["venta_base_id"]);
	if ($fechaInicioPlan == "") {
		$fechaInicioPlan = obtenerFechaVentaMasAntiguaItemsPlanConsulta($mysqli,$plan["id"]);
	}
	if ($fechaInicioPlan == "") {
		$fechaInicioPlan = isset($plan["fecha_creacion"]) ? $plan["fecha_creacion"] : (isset($plan["fecha_actualizacion"]) ? $plan["fecha_actualizacion"] : "");
	}
	$curvaPlanDefinitivo = buildTreatmentRiskCurve($items,$fechaInicioPlan);
	$curvaReferenciaPlan = buildAutomaticSuggestionCurve($items,$fechaInicioPlan);
	$pagosMensualesPlan = obtenerPagosMensualesPlanConsulta($mysqli, ventasDesdeItemsPlanConsulta($items), $fechaInicioPlan);
	asignarResumenTemporalItemsPlanConsulta($items,$curvaPlanDefinitivo);

	return "
<div class='plan-definitivo-panel".$editableClass."' data-plan-id='".$planId."' data-plan-estado='".htmlspecialchars($estado, ENT_QUOTES, "UTF-8")."' data-plan-label='".$planMadreTituloHtml." - ".$planMadreApodoHtml."' data-plan-orden-sugerido='N1 a N5'>
	<div class='plan-definitivo-header'>
		<div>
			<h4>".$planMadreTituloHtml." - ".$planMadreApodoHtml."</h4>
			<span>Ruta cl&iacute;nica agrupada bajo esta c&eacute;dula. Orden sugerido: N1 a N5.</span>
		</div>
		<em class='plan-definitivo-status plan-definitivo-status--".htmlspecialchars($estado, ENT_QUOTES, "UTF-8")."'>".$estadoCompleto."</em>
	</div>
	<div class='plan-definitivo-meta'>
		<span><strong>Doctor</strong>".$doctorHtml."</span>
		<span><strong>Versi&oacute;n</strong>".$version."</span>
		<span><strong>&Uacute;ltima actualizaci&oacute;n</strong>".htmlspecialchars($fechaActualizacion, ENT_QUOTES, "UTF-8")."</span>
	</div>
	<div class='plan-definitivo-context'>".$ayudaEstado."</div>
	<div class='plan-definitivo-order-notice plan-definitivo-edit-only' data-order-notice='1'>
		<strong>Orden en edici&oacute;n</strong>
		<span>Us&aacute; las flechas para acomodar la ruta. El historial se registra al presionar Confirmar orden.</span>
	</div>
	<div class='plan-definitivo-actions'>
		".$accionesLectura."
		".$botonConfirmarOrden."
		".$botonCancelarEdicion."
		<button type='button' class='plan-definitivo-secondary plan-definitivo-edit-only' onclick='abrirAnexarTratamientosPlanDefinitivoConsulta(\"".$planId."\")'>Anexar tratamientos</button>
		".$accionConfirmar."
		<button type='button' class='plan-definitivo-secondary' onclick='verHistorialPlanDefinitivoConsulta(\"".$planId."\")'>Ver historial</button>
	</div>
	".renderizarListaPlanDefinitivoConsulta($items,$plan["id"],$editable)."
	".renderPlanRiskChart($curvaPlanDefinitivo,$curvaReferenciaPlan,"definitivo",$pagosMensualesPlan)."
</div>";
}

function renderizarTabsPlanesConsulta($planSugeridoHtml,$planDefinitivoHtml,$metaPlanDefinitivo)
{
	$vistaInicial = (!empty($metaPlanDefinitivo["existe"])) ? "definitivo" : "sugerido";
	$labelDefinitivo = isset($metaPlanDefinitivo["tab_label"]) ? $metaPlanDefinitivo["tab_label"] : "Plan madre &middot; Pendiente";
	$activoDefinitivo = $vistaInicial == "definitivo";
	$activoSugerido = !$activoDefinitivo;
	$ariaDefinitivo = $activoDefinitivo ? "true" : "false";
	$ariaSugerido = $activoSugerido ? "true" : "false";
	return "
<div class='consulta-plan-tabs' data-consulta-plan-tabs data-vista-inicial='".$vistaInicial."' data-plan-definitivo-existe='".(!empty($metaPlanDefinitivo["existe"]) ? "1" : "0")."'>
	<div class='consulta-plan-tabs__nav' role='tablist' aria-label='Plan de tratamientos'>
		<button type='button' id='consultaPlanDefinitivoTab' class='consulta-plan-tab consulta-plan-tab--definitivo".($activoDefinitivo ? " is-active" : "")."' role='tab' aria-selected='".$ariaDefinitivo."' aria-controls='consultaPlanDefinitivoPanel' data-plan-tab-button='definitivo' onclick='cambiarTabPlanConsulta(this,\"definitivo\")'><span class='consulta-plan-tab__icon' aria-hidden='true'>&#10003;</span><span>".$labelDefinitivo."</span></button>
		<button type='button' id='consultaPlanSugeridoTab' class='consulta-plan-tab consulta-plan-tab--sugerido".($activoSugerido ? " is-active" : "")."' role='tab' aria-selected='".$ariaSugerido."' aria-controls='consultaPlanSugeridoPanel' data-plan-tab-button='sugerido' onclick='cambiarTabPlanConsulta(this,\"sugerido\")'><span class='consulta-plan-tab__icon' aria-hidden='true'>&#10022;</span><span>Sugerencia autom&aacute;tica</span></button>
	</div>
	<div id='consultaPlanDefinitivoPanel' class='consulta-plan-tabs__panel".($activoDefinitivo ? " is-active" : "")."' role='tabpanel' aria-labelledby='consultaPlanDefinitivoTab' data-plan-tab='definitivo'>".$planDefinitivoHtml."</div>
	<div id='consultaPlanSugeridoPanel' class='consulta-plan-tabs__panel".($activoSugerido ? " is-active" : "")."' role='tabpanel' aria-labelledby='consultaPlanSugeridoTab' data-plan-tab='sugerido'>".$planSugeridoHtml."</div>
</div>";
}

function crearPlanDefinitivoDesdeSugeridoConsulta($cod_venta,$user)
{
	$mysqli=conectar_al_servidor();
	if (!planDefinitivoTablasDisponiblesConsulta($mysqli)) {
		responderPlanDefinitivoConsulta("error","Debe aplicar la actualizacion SQL del plan madre.");
	}
	$contexto = obtenerContextoPlanDefinitivoConsulta($mysqli,$cod_venta);
	if (!$contexto) {
		responderPlanDefinitivoConsulta("error","No se pudo identificar la venta base del paciente.");
	}
	$ventaBaseDefinitiva = (string)$cod_venta;
	$tratamientos = obtenerTratamientosVentaPlanConsulta($mysqli,$ventaBaseDefinitiva);
	if (count($tratamientos) == 0) {
		responderPlanDefinitivoConsulta("camposvacio","No hay tratamientos para crear el plan madre.");
	}
	$itemsOrdenados = aplanarGruposPlanSugeridoConsulta(ordenarTratamientosPlanSugerido($tratamientos));
	$cedula = mb_convert_encoding((string)$contexto["cedula"], 'ISO-8859-1', 'UTF-8');
	$paciente_id = $contexto["paciente_id"];
	$estado = "borrador";
	$version = 1;
	$activo = 1;
	$rol = obtenerRolUsuarioPlanDefinitivoConsulta($mysqli,$user);

	$mysqli->autocommit(false);
	$stmt = $mysqli->prepare("INSERT INTO plan_definitivo_tratamiento
		(cedula, paciente_id, venta_base_id, doctor_cabecera_id, estado, version_actual, fecha_creacion, creado_por, fecha_actualizacion, actualizado_por, activo)
		VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, NOW(), ?, ?)");
	if (!$stmt) {
		$mysqli->rollback();
		responderPlanDefinitivoConsulta("error","No se pudo preparar la creacion del plan madre.");
	}
	$stmt->bind_param("sssssisii", $cedula, $paciente_id, $ventaBaseDefinitiva, $user, $estado, $version, $user, $user, $activo);
	if (!$stmt->execute()) {
		$mysqli->rollback();
		responderPlanDefinitivoConsulta("error","No se pudo crear el plan madre.");
	}
	$plan_id = $mysqli->insert_id;
	$stmtItem = $mysqli->prepare("INSERT INTO plan_definitivo_tratamiento_items
		(plan_definitivo_id, venta_id, detalle_venta_id, producto_id, nombre_tratamiento_snapshot, nivel_riesgo_snapshot, orden, origen, activo, fecha_agregado, agregado_por)
		VALUES (?, ?, ?, ?, ?, ?, ?, 'plan_principal', 1, NOW(), ?)");
	if (!$stmtItem) {
		$mysqli->rollback();
		responderPlanDefinitivoConsulta("error","No se pudo preparar el detalle del plan madre.");
	}
	$orden = 1;
	foreach ($itemsOrdenados as $tratamiento) {
		$ventaItem = (string)$tratamiento["venta_id"];
		$detalleItem = (string)$tratamiento["cod_detalle"];
		$productoItem = (string)$tratamiento["cod_producto"];
		$nombre = mb_convert_encoding((string)$tratamiento["nombre_producto"], 'ISO-8859-1', 'UTF-8');
		$nivel = (int)$tratamiento["nivel_riesgo_financiero"];
		$stmtItem->bind_param("sssssiis", $plan_id, $ventaItem, $detalleItem, $productoItem, $nombre, $nivel, $orden, $user);
		if (!$stmtItem->execute()) {
			$mysqli->rollback();
			responderPlanDefinitivoConsulta("error","No se pudieron copiar los tratamientos sugeridos.");
		}
		$orden++;
	}
	registrarHistorialPlanDefinitivoConsulta($mysqli,$plan_id,1,"creacion","Se creo el plan madre.", "", "Estado: Borrador", "", $user, $rol);
	registrarHistorialPlanDefinitivoConsulta($mysqli,$plan_id,1,"creacion_desde_plan_sugerido","Se creo el plan madre desde esta venta.", "", count($itemsOrdenados)." tratamientos copiados", "", $user, $rol);
	$mysqli->commit();
	responderPlanDefinitivoConsulta("exito","Plan madre creado como borrador.", array("plan_id" => $plan_id));
}

function guardarBorradorPlanDefinitivoConsulta($plan_id,$motivo,$user)
{
	$mysqli=conectar_al_servidor();
	if (!planDefinitivoTablasDisponiblesConsulta($mysqli)) {
		responderPlanDefinitivoConsulta("error","Debe aplicar la actualizacion SQL del plan madre.");
	}
	$plan = obtenerPlanDefinitivoPorIdConsulta($mysqli,$plan_id);
	if (!$plan) {
		responderPlanDefinitivoConsulta("error","No se encontro el plan madre.");
	}
	$rol = obtenerRolUsuarioPlanDefinitivoConsulta($mysqli,$user);
	$versionado = versionarCambioPlanDefinitivoConsulta($mysqli,$plan,$motivo,$user);
	if (!$versionado["ok"]) {
		responderPlanDefinitivoConsulta("camposvacio",$versionado["mensaje"]);
	}
	registrarHistorialPlanDefinitivoConsulta($mysqli,$plan_id,$versionado["version"],"guardado_borrador","Se guardo el plan madre como borrador.", "", "Borrador guardado", $motivo, $user, $rol);
	responderPlanDefinitivoConsulta("exito","Borrador guardado.");
}

function confirmarPlanDefinitivoConsulta($plan_id,$user)
{
	$mysqli=conectar_al_servidor();
	if (!planDefinitivoTablasDisponiblesConsulta($mysqli)) {
		responderPlanDefinitivoConsulta("error","Debe aplicar la actualizacion SQL del plan madre.");
	}
	$plan = obtenerPlanDefinitivoPorIdConsulta($mysqli,$plan_id);
	if (!$plan) {
		responderPlanDefinitivoConsulta("error","No se encontro el plan madre.");
	}
	$estadoAnterior = $plan["estado"];
	$version = (int)$plan["version_actual"];
	if ($version < 1) { $version = 1; }
	$estado = "definido";
	$stmt = $mysqli->prepare("UPDATE plan_definitivo_tratamiento
		SET estado = ?,
			version_actual = ?,
			doctor_cabecera_id = IF(doctor_cabecera_id IS NULL OR doctor_cabecera_id = 0, ?, doctor_cabecera_id),
			fecha_actualizacion = NOW(),
			actualizado_por = ?
		WHERE id = ?
		LIMIT 1");
	if (!$stmt) {
		responderPlanDefinitivoConsulta("error","No se pudo preparar la confirmacion.");
	}
	$stmt->bind_param("sisss", $estado, $version, $user, $user, $plan_id);
	if (!$stmt->execute()) {
		responderPlanDefinitivoConsulta("error","No se pudo confirmar el plan madre.");
	}
	$rol = obtenerRolUsuarioPlanDefinitivoConsulta($mysqli,$user);
	registrarHistorialPlanDefinitivoConsulta($mysqli,$plan_id,$version,"confirmacion","Se confirmo el plan madre.", "Estado: ".$estadoAnterior, "Estado: Definido", "", $user, $rol);
	responderPlanDefinitivoConsulta("exito","Plan madre confirmado.");
}

function obtenerItemPlanDefinitivoConsulta($mysqli,$plan_id,$item_id)
{
	$sql = "SELECT * FROM plan_definitivo_tratamiento_items WHERE id = ? AND plan_definitivo_id = ? AND activo = 1 LIMIT 1";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) { return null; }
	$stmt->bind_param("ss", $item_id, $plan_id);
	if (!$stmt->execute()) { return null; }
	$result = $stmt->get_result();
	if (!($row = mysqli_fetch_assoc($result))) { return null; }
	$row["nombre_tratamiento_snapshot"] = mb_convert_encoding((string)$row["nombre_tratamiento_snapshot"], 'UTF-8', 'ISO-8859-1');
	$row["observacion_clinica"] = mb_convert_encoding((string)$row["observacion_clinica"], 'UTF-8', 'ISO-8859-1');
	return $row;
}

function moverItemPlanDefinitivoConsulta($plan_id,$item_id,$direccion,$motivo,$user)
{
	$mysqli=conectar_al_servidor();
	if (!planDefinitivoTablasDisponiblesConsulta($mysqli)) {
		responderPlanDefinitivoConsulta("error","Debe aplicar la actualizacion SQL del plan madre.");
	}
	$plan = obtenerPlanDefinitivoPorIdConsulta($mysqli,$plan_id);
	$item = obtenerItemPlanDefinitivoConsulta($mysqli,$plan_id,$item_id);
	if (!$plan || !$item) {
		responderPlanDefinitivoConsulta("error","No se encontro el item del plan madre.");
	}
	$direccion = (int)$direccion;
	$comparador = $direccion < 0 ? "<" : ">";
	$ordenSql = $direccion < 0 ? "DESC" : "ASC";
	$sql = "SELECT * FROM plan_definitivo_tratamiento_items
		WHERE plan_definitivo_id = ? AND activo = 1 AND orden ".$comparador." ?
		ORDER BY orden ".$ordenSql.", id ".$ordenSql." LIMIT 1";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		responderPlanDefinitivoConsulta("error","No se pudo preparar el cambio de orden.");
	}
	$stmt->bind_param("si", $plan_id, $item["orden"]);
	if (!$stmt->execute()) {
		responderPlanDefinitivoConsulta("error","No se pudo buscar la posicion destino.");
	}
	$result = $stmt->get_result();
	if (!($otro = mysqli_fetch_assoc($result))) {
		responderPlanDefinitivoConsulta("exito","El tratamiento ya esta en el limite de la ruta.");
	}
	$rol = obtenerRolUsuarioPlanDefinitivoConsulta($mysqli,$user);
	$versionado = versionarCambioPlanDefinitivoConsulta($mysqli,$plan,$motivo,$user);
	if (!$versionado["ok"]) {
		responderPlanDefinitivoConsulta("camposvacio",$versionado["mensaje"]);
	}
	$stmtUp = $mysqli->prepare("UPDATE plan_definitivo_tratamiento_items SET orden = ? WHERE id = ? LIMIT 1");
	if (!$stmtUp) {
		responderPlanDefinitivoConsulta("error","No se pudo preparar el reordenamiento.");
	}
	$ordenItem = (int)$item["orden"];
	$ordenOtro = (int)$otro["orden"];
	$stmtUp->bind_param("is", $ordenOtro, $item_id);
	$stmtUp->execute();
	$stmtUp->bind_param("is", $ordenItem, $otro["id"]);
	$stmtUp->execute();
	$descripcion = "Se cambio el orden de ".$item["nombre_tratamiento_snapshot"].".";
	registrarHistorialPlanDefinitivoConsulta($mysqli,$plan_id,$versionado["version"],"cambio_orden",$descripcion,"Orden ".$ordenItem,"Orden ".$ordenOtro,$motivo,$user,$rol);
	responderPlanDefinitivoConsulta("exito","Orden actualizado.");
}

function normalizarOrdenIdsPlanDefinitivoConsulta($orden_ids)
{
	$partes = explode(",", (string)$orden_ids);
	$ids = array();
	$vistos = array();
	foreach ($partes as $id) {
		$id = trim((string)$id);
		if ($id == "" || isset($vistos[$id])) {
			continue;
		}
		$vistos[$id] = true;
		$ids[] = $id;
	}
	return $ids;
}

function resumenOrdenPlanDefinitivoConsulta($items)
{
	$lineas = array();
	$posicion = 1;
	foreach ($items as $item) {
		$nombre = isset($item["nombre_utf8"]) ? $item["nombre_utf8"] : (isset($item["nombre_tratamiento_snapshot"]) ? $item["nombre_tratamiento_snapshot"] : "Tratamiento");
		$lineas[] = "Paso ".$posicion.": ".$nombre;
		$posicion++;
	}
	return implode("\n", $lineas);
}

function guardarOrdenPlanDefinitivoConsulta($plan_id,$orden_ids,$motivo,$user)
{
	$mysqli=conectar_al_servidor();
	if (!planDefinitivoTablasDisponiblesConsulta($mysqli)) {
		responderPlanDefinitivoConsulta("error","Debe aplicar la actualizacion SQL del plan madre.");
	}
	$plan = obtenerPlanDefinitivoPorIdConsulta($mysqli,$plan_id);
	if (!$plan) {
		responderPlanDefinitivoConsulta("error","No se encontro el plan madre.");
	}
	$idsOrdenados = normalizarOrdenIdsPlanDefinitivoConsulta($orden_ids);
	if (count($idsOrdenados) == 0) {
		responderPlanDefinitivoConsulta("camposvacio","No se recibio el orden del plan madre.");
	}

	$stmt = $mysqli->prepare("SELECT id, nombre_tratamiento_snapshot, orden FROM plan_definitivo_tratamiento_items WHERE plan_definitivo_id = ? AND activo = 1 ORDER BY orden ASC, id ASC");
	if (!$stmt) {
		responderPlanDefinitivoConsulta("error","No se pudo preparar la lectura del orden actual.");
	}
	$stmt->bind_param("s", $plan_id);
	if (!$stmt->execute()) {
		responderPlanDefinitivoConsulta("error","No se pudo consultar el orden actual.");
	}
	$result = $stmt->get_result();
	$itemsActuales = array();
	$itemsPorId = array();
	$idsActuales = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$id = (string)$row["id"];
		$row["nombre_utf8"] = mb_convert_encoding((string)$row["nombre_tratamiento_snapshot"], 'UTF-8', 'ISO-8859-1');
		$itemsActuales[] = $row;
		$itemsPorId[$id] = $row;
		$idsActuales[] = $id;
	}

	if (count($idsOrdenados) != count($idsActuales)) {
		responderPlanDefinitivoConsulta("camposvacio","El orden recibido no coincide con los tratamientos activos del plan madre.");
	}
	foreach ($idsOrdenados as $idOrdenado) {
		if (!isset($itemsPorId[$idOrdenado])) {
			responderPlanDefinitivoConsulta("camposvacio","El orden incluye un tratamiento que ya no pertenece al plan madre.");
		}
	}

	$sinCambios = true;
	for ($i = 0; $i < count($idsActuales); $i++) {
		if ((string)$idsActuales[$i] != (string)$idsOrdenados[$i]) {
			$sinCambios = false;
			break;
		}
	}
	if ($sinCambios) {
		responderPlanDefinitivoConsulta("exito","No habia cambios de orden para guardar.");
	}

	$itemsNuevos = array();
	$posicionAnterior = array();
	foreach ($itemsActuales as $indice => $itemActual) {
		$posicionAnterior[(string)$itemActual["id"]] = $indice + 1;
	}
	foreach ($idsOrdenados as $idOrdenado) {
		$itemsNuevos[] = $itemsPorId[$idOrdenado];
	}

	$movimientos = array();
	foreach ($idsOrdenados as $indiceNuevo => $idOrdenado) {
		$anterior = isset($posicionAnterior[$idOrdenado]) ? (int)$posicionAnterior[$idOrdenado] : 0;
		$nuevo = $indiceNuevo + 1;
		if ($anterior != $nuevo) {
			$nombreMovido = isset($itemsPorId[$idOrdenado]["nombre_utf8"]) ? $itemsPorId[$idOrdenado]["nombre_utf8"] : "Tratamiento";
			$movimientos[] = $nombreMovido." del paso ".$anterior." al paso ".$nuevo;
		}
	}

	$rol = obtenerRolUsuarioPlanDefinitivoConsulta($mysqli,$user);
	$mysqli->autocommit(false);
	$versionado = versionarCambioPlanDefinitivoConsulta($mysqli,$plan,$motivo,$user);
	if (!$versionado["ok"]) {
		$mysqli->rollback();
		responderPlanDefinitivoConsulta("camposvacio",$versionado["mensaje"]);
	}

	$stmtUpdate = $mysqli->prepare("UPDATE plan_definitivo_tratamiento_items SET orden = ? WHERE id = ? AND plan_definitivo_id = ? LIMIT 1");
	if (!$stmtUpdate) {
		$mysqli->rollback();
		responderPlanDefinitivoConsulta("error","No se pudo preparar el guardado del orden.");
	}
	foreach ($idsOrdenados as $indice => $idOrdenado) {
		$nuevoOrden = $indice + 1;
		$stmtUpdate->bind_param("iss", $nuevoOrden, $idOrdenado, $plan_id);
		if (!$stmtUpdate->execute()) {
			$mysqli->rollback();
			responderPlanDefinitivoConsulta("error","No se pudo guardar el nuevo orden del plan madre.");
		}
	}

	$antes = resumenOrdenPlanDefinitivoConsulta($itemsActuales);
	$despues = resumenOrdenPlanDefinitivoConsulta($itemsNuevos);
	$descripcion = count($movimientos) > 0
		? "Se actualizo el orden clinico del plan madre: ".implode("; ", $movimientos)."."
		: "Se actualizo el orden clinico del plan madre.";
	if (!registrarHistorialPlanDefinitivoConsulta($mysqli,$plan_id,$versionado["version"],"cambio_orden",$descripcion,$antes,$despues,$motivo,$user,$rol)) {
		$mysqli->rollback();
		responderPlanDefinitivoConsulta("error","No se pudo registrar el historial del cambio de orden.");
	}

	$mysqli->commit();
	responderPlanDefinitivoConsulta("exito","Orden del plan madre confirmado.");
}

function actualizarObservacionItemPlanDefinitivoConsulta($plan_id,$item_id,$observacion,$motivo,$user)
{
	$mysqli=conectar_al_servidor();
	if (!planDefinitivoTablasDisponiblesConsulta($mysqli)) {
		responderPlanDefinitivoConsulta("error","Debe aplicar la actualizacion SQL del plan madre.");
	}
	$plan = obtenerPlanDefinitivoPorIdConsulta($mysqli,$plan_id);
	$item = obtenerItemPlanDefinitivoConsulta($mysqli,$plan_id,$item_id);
	if (!$plan || !$item) {
		responderPlanDefinitivoConsulta("error","No se encontro el item del plan madre.");
	}
	$rol = obtenerRolUsuarioPlanDefinitivoConsulta($mysqli,$user);
	$versionado = versionarCambioPlanDefinitivoConsulta($mysqli,$plan,$motivo,$user);
	if (!$versionado["ok"]) {
		responderPlanDefinitivoConsulta("camposvacio",$versionado["mensaje"]);
	}
	$observacionDb = mb_convert_encoding((string)$observacion, 'ISO-8859-1', 'UTF-8');
	$stmt = $mysqli->prepare("UPDATE plan_definitivo_tratamiento_items SET observacion_clinica = ? WHERE id = ? AND plan_definitivo_id = ? LIMIT 1");
	if (!$stmt) {
		responderPlanDefinitivoConsulta("error","No se pudo preparar la observacion.");
	}
	$stmt->bind_param("sss", $observacionDb, $item_id, $plan_id);
	if (!$stmt->execute()) {
		responderPlanDefinitivoConsulta("error","No se pudo guardar la observacion.");
	}
	$descripcion = "Se modifico la observacion clinica de ".$item["nombre_tratamiento_snapshot"].".";
	registrarHistorialPlanDefinitivoConsulta($mysqli,$plan_id,$versionado["version"],"observacion_modificada",$descripcion,$item["observacion_clinica"],$observacion,$motivo,$user,$rol);
	responderPlanDefinitivoConsulta("exito","Observacion guardada.");
}

function quitarItemPlanDefinitivoConsulta($plan_id,$item_id,$motivo,$user)
{
	$mysqli=conectar_al_servidor();
	if (!planDefinitivoTablasDisponiblesConsulta($mysqli)) {
		responderPlanDefinitivoConsulta("error","Debe aplicar la actualizacion SQL del plan madre.");
	}
	$plan = obtenerPlanDefinitivoPorIdConsulta($mysqli,$plan_id);
	$item = obtenerItemPlanDefinitivoConsulta($mysqli,$plan_id,$item_id);
	if (!$plan || !$item) {
		responderPlanDefinitivoConsulta("error","No se encontro el item del plan madre.");
	}
	$rol = obtenerRolUsuarioPlanDefinitivoConsulta($mysqli,$user);
	$versionado = versionarCambioPlanDefinitivoConsulta($mysqli,$plan,$motivo,$user);
	if (!$versionado["ok"]) {
		responderPlanDefinitivoConsulta("camposvacio",$versionado["mensaje"]);
	}
	$stmt = $mysqli->prepare("UPDATE plan_definitivo_tratamiento_items SET activo = 0 WHERE id = ? AND plan_definitivo_id = ? LIMIT 1");
	if (!$stmt) {
		responderPlanDefinitivoConsulta("error","No se pudo preparar la baja del item.");
	}
	$stmt->bind_param("ss", $item_id, $plan_id);
	if (!$stmt->execute()) {
		responderPlanDefinitivoConsulta("error","No se pudo quitar el tratamiento de la ruta.");
	}
	$descripcion = "Se quito ".$item["nombre_tratamiento_snapshot"]." de esta ruta.";
	registrarHistorialPlanDefinitivoConsulta($mysqli,$plan_id,$versionado["version"],"tratamiento_quitado",$descripcion,"Incluido en ruta","Quitado de ruta",$motivo,$user,$rol);
	responderPlanDefinitivoConsulta("exito","Tratamiento quitado solo de la ruta.");
}

function buscarVentasAnexablesPlanDefinitivoConsulta($plan_id)
{
	$mysqli=conectar_al_servidor();
	if (!planDefinitivoTablasDisponiblesConsulta($mysqli)) {
		responderPlanDefinitivoConsulta("error","Debe aplicar la actualizacion SQL del plan madre.");
	}
	$plan = obtenerPlanDefinitivoPorIdConsulta($mysqli,$plan_id);
	if (!$plan) {
		responderPlanDefinitivoConsulta("error","No se encontro el plan madre.");
	}
	$cedula = $plan["cedula"];
	$paciente_id = $plan["paciente_id"];
	$selectRiesgo = ProductoRiesgoFinancieroSelectSql($mysqli, "pr");
	$sql = "SELECT vt.cod_venta, vt.fecha_venta, vt.num_factura, vt.apodo, vt.cod_clienteFK,
		p.nombre_persona, cl.ci_cliente,
		dtv.cod_detalle, dtv.descripcion, dtv.estado, dtv.estado_tratamiento, dtv.progreso_porcentaje,
		pr.cod_producto, pr.nombre_producto, ".$selectRiesgo.",
		(SELECT COUNT(*) FROM plan_definitivo_tratamiento_items pi WHERE pi.plan_definitivo_id = ? AND pi.detalle_venta_id = dtv.cod_detalle AND pi.activo = 1) AS ya_incluido
		FROM venta vt
		INNER JOIN cliente cl ON cl.cod_cliente = vt.cod_clienteFK
		INNER JOIN persona p ON p.cod_persona = cl.cod_cliente
		INNER JOIN detalle_venta dtv ON dtv.cod_ventaFK = vt.cod_venta
		INNER JOIN producto pr ON pr.cod_producto = dtv.cod_productoFK
		WHERE (vt.cod_clienteFK = ? OR cl.ci_cliente = ?)
		AND IFNULL(dtv.estado,'') <> 'eliminado'
		AND IFNULL((SELECT COUNT(*) FROM cancelaciones c WHERE c.cod_venta = vt.cod_venta),0)=0
		".ProductoClinicoWhereSqlConsulta("pr")."
		ORDER BY vt.cod_venta DESC, nivel_riesgo_financiero ASC, dtv.cod_detalle ASC
		LIMIT 300";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		responderPlanDefinitivoConsulta("error","No se pudo preparar la busqueda de ventas anexables.");
	}
	$stmt->bind_param("sss", $plan_id, $paciente_id, $cedula);
	if (!$stmt->execute()) {
		responderPlanDefinitivoConsulta("error","No se pudieron consultar ventas anexables.");
	}
	$result = $stmt->get_result();
	$ventas = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$ventaId = (string)$row["cod_venta"];
		if (!isset($ventas[$ventaId])) {
			$origen = ($ventaId == (string)$plan["venta_base_id"]) ? "Plan principal" : "Venta separada";
			$ventas[$ventaId] = array(
				"cod_venta" => $ventaId,
				"fecha_venta" => $row["fecha_venta"],
				"num_factura" => mb_convert_encoding((string)$row["num_factura"], 'UTF-8', 'ISO-8859-1'),
				"apodo" => mb_convert_encoding((string)$row["apodo"], 'UTF-8', 'ISO-8859-1'),
				"paciente" => mb_convert_encoding((string)$row["nombre_persona"], 'UTF-8', 'ISO-8859-1'),
				"ci_cliente" => mb_convert_encoding((string)$row["ci_cliente"], 'UTF-8', 'ISO-8859-1'),
				"origen" => $origen,
				"total_items" => 0,
				"incluidos" => 0,
				"items" => array()
			);
		}
		$avance = normalizarPorcentajePlanTratamientoConsulta($row["progreso_porcentaje"]);
		$estadoClase = normalizarEstadoPlanTratamientoConsulta($row["estado"], $row["estado_tratamiento"], $avance);
		$incluido = (int)$row["ya_incluido"] > 0;
		$ventas[$ventaId]["total_items"]++;
		if ($incluido) {
			$ventas[$ventaId]["incluidos"]++;
		}
		$ventas[$ventaId]["items"][] = array(
			"cod_detalle" => $row["cod_detalle"],
			"nombre_producto" => mb_convert_encoding((string)$row["nombre_producto"], 'UTF-8', 'ISO-8859-1'),
			"nivel_riesgo_financiero" => ProductoRiesgoFinancieroNormalizar($row["nivel_riesgo_financiero"]),
			"estado_texto" => textoEstadoPlanTratamientoConsulta($estadoClase),
			"ya_incluido" => $incluido ? 1 : 0
		);
	}
	$pagina = "<div class='plan-definitivo-anexar-lista'>";
	if (count($ventas) == 0) {
		$pagina .= "<div class='plan-definitivo-empty-inline'>No se encontraron ventas asociadas a la misma c&eacute;dula/paciente.</div>";
	}
	foreach ($ventas as $venta) {
		$fecha = $venta["fecha_venta"] != "" ? date("d/m/Y", strtotime($venta["fecha_venta"])) : "";
		$alias = trim($venta["apodo"]) != "" ? $venta["apodo"] : $venta["paciente"];
		$totalItems = (int)$venta["total_items"];
		$incluidos = (int)$venta["incluidos"];
		$pendientes = $totalItems - $incluidos;
		if ($pendientes < 0) { $pendientes = 0; }
		$ventaCompleta = ($totalItems > 0 && $pendientes == 0);
		$ventaParcial = ($incluidos > 0 && $pendientes > 0);
		$estadoVenta = $ventaCompleta ? "Ya incluida" : ($ventaParcial ? "Completar venta" : "Disponible");
		$detalleEstado = $ventaCompleta
			? $totalItems." tratamientos ya incluidos"
			: ($ventaParcial ? $incluidos." incluidos / ".$pendientes." por anexar" : $totalItems." tratamientos disponibles");
		$pagina .= "<section class='plan-definitivo-anexar-venta'>"
			."<label class='plan-definitivo-anexar-venta-selector".($ventaCompleta ? " is-included" : "").($ventaParcial ? " is-partial" : "")."'>"
			."<span class='plan-definitivo-anexar-check'>"
			."<input type='checkbox' value='".htmlspecialchars($venta["cod_venta"], ENT_QUOTES, "UTF-8")."' ".($ventaCompleta ? "disabled" : "").">"
			."<span class='plan-definitivo-anexar-check__box' aria-hidden='true'></span>"
			."</span>"
			."<span class='plan-definitivo-anexar-main'>"
			."<strong>Venta #".htmlspecialchars($venta["cod_venta"], ENT_QUOTES, "UTF-8")."</strong>"
			."<small>Paciente/Alias: ".htmlspecialchars($alias, ENT_QUOTES, "UTF-8")." &middot; Fecha: ".htmlspecialchars($fecha, ENT_QUOTES, "UTF-8")." &middot; ".htmlspecialchars($venta["origen"], ENT_QUOTES, "UTF-8")."</small>"
			."</span>"
			."<span class='plan-definitivo-anexar-summary'>"
			."<em>".htmlspecialchars($detalleEstado, ENT_QUOTES, "UTF-8")."</em>"
			."<b>".htmlspecialchars($estadoVenta, ENT_QUOTES, "UTF-8")."</b>"
			."</span>"
			."</label>";
		foreach ($venta["items"] as $item) {
			$incluido = $item["ya_incluido"] > 0;
			$badge = ProductoRiesgoFinancieroBadgeHtml($item["nivel_riesgo_financiero"], "consulta-treatment-risk");
			$pagina .= "<div class='plan-definitivo-anexar-item".($incluido ? " is-included" : "")."'>"
				."<span>".htmlspecialchars($item["nombre_producto"], ENT_QUOTES, "UTF-8")."</span>"
				.$badge
				."<em>".htmlspecialchars($item["estado_texto"], ENT_QUOTES, "UTF-8")."</em>"
				.($incluido ? "<b>Ya incluido</b>" : "")
				."</div>";
		}
		$pagina .= "</section>";
	}
	$pagina .= "</div>";
	responderPlanDefinitivoConsulta("exito","", array("2" => $pagina));
}

function normalizarIdsSeparadosPlanConsulta($ids)
{
	$salida = array();
	foreach (explode(",", (string)$ids) as $id) {
		$id = trim($id);
		if ($id != "" && ctype_digit($id) && !in_array($id, $salida)) {
			$salida[] = $id;
		}
	}
	return $salida;
}

function anexarTratamientosPlanDefinitivoConsulta($plan_id,$detalle_ids,$motivo,$user,$venta_ids="")
{
	$mysqli=conectar_al_servidor();
	if (!planDefinitivoTablasDisponiblesConsulta($mysqli)) {
		responderPlanDefinitivoConsulta("error","Debe aplicar la actualizacion SQL del plan madre.");
	}
	$plan = obtenerPlanDefinitivoPorIdConsulta($mysqli,$plan_id);
	if (!$plan) {
		responderPlanDefinitivoConsulta("error","No se encontro el plan madre.");
	}
	$ventas = normalizarIdsSeparadosPlanConsulta($venta_ids);
	$detalleIds = normalizarIdsSeparadosPlanConsulta($detalle_ids);
	if (count($ventas) == 0 && count($detalleIds) > 0) {
		$stmtVentaDetalle = $mysqli->prepare("SELECT dtv.cod_ventaFK
			FROM detalle_venta dtv
			INNER JOIN venta vt ON vt.cod_venta = dtv.cod_ventaFK
			INNER JOIN cliente cl ON cl.cod_cliente = vt.cod_clienteFK
			INNER JOIN producto pr ON pr.cod_producto = dtv.cod_productoFK
			WHERE dtv.cod_detalle = ?
			AND (vt.cod_clienteFK = ? OR cl.ci_cliente = ?)
			AND IFNULL(dtv.estado,'') <> 'eliminado'
			AND IFNULL((SELECT COUNT(*) FROM cancelaciones c WHERE c.cod_venta = vt.cod_venta),0)=0
			".ProductoClinicoWhereSqlConsulta("pr")."
			LIMIT 1");
		if (!$stmtVentaDetalle) {
			responderPlanDefinitivoConsulta("error","No se pudo preparar la validacion de ventas.");
		}
		foreach ($detalleIds as $detalle_id) {
			$stmtVentaDetalle->bind_param("sss", $detalle_id, $plan["paciente_id"], $plan["cedula"]);
			$stmtVentaDetalle->execute();
			$resultVentaDetalle = $stmtVentaDetalle->get_result();
			if ($rowVentaDetalle = mysqli_fetch_assoc($resultVentaDetalle)) {
				$ventaDetectada = (string)$rowVentaDetalle["cod_ventaFK"];
				if ($ventaDetectada != "" && !in_array($ventaDetectada, $ventas)) {
					$ventas[] = $ventaDetectada;
				}
			}
		}
	}
	if (count($ventas) == 0) {
		responderPlanDefinitivoConsulta("camposvacio","Seleccione al menos una venta para anexar.");
	}
	$rol = obtenerRolUsuarioPlanDefinitivoConsulta($mysqli,$user);
	$mysqli->autocommit(false);
	$versionado = versionarCambioPlanDefinitivoConsulta($mysqli,$plan,$motivo,$user);
	if (!$versionado["ok"]) {
		$mysqli->rollback();
		responderPlanDefinitivoConsulta("camposvacio",$versionado["mensaje"]);
	}
	$agregados = 0;
	$leidos = 0;
	$ventasProcesadas = array();
	foreach ($ventas as $venta_id) {
		$resultado = anexarVentaCompletaPlanMadreConsulta($mysqli,$plan,$venta_id,$user,$motivo,((string)$plan["venta_base_id"] == (string)$venta_id));
		if (!$resultado["ok"]) {
			$mysqli->rollback();
			responderPlanDefinitivoConsulta("error",$resultado["mensaje"]);
		}
		if ((int)$resultado["leidos"] > 0) {
			$ventasProcesadas[] = $venta_id;
			$leidos += (int)$resultado["leidos"];
			$agregados += (int)$resultado["agregados"];
		}
	}
	if ($leidos == 0) {
		$mysqli->rollback();
		responderPlanDefinitivoConsulta("camposvacio","Las ventas seleccionadas no tienen tratamientos activos para anexar.");
	}
	if (count($ventasProcesadas) > 0) {
		registrarHistorialPlanDefinitivoConsulta(
			$mysqli,
			$plan_id,
			$versionado["version"],
			"venta_asignada",
			"Se anexaron o completaron ventas completas en el plan madre.",
			"",
			"Ventas #".implode(", #", $ventasProcesadas)." - ".$agregados." tratamientos agregados",
			$motivo,
			$user,
			$rol
		);
	}
	$mysqli->commit();
	if ($agregados == 0) {
		responderPlanDefinitivoConsulta("exito","Las ventas seleccionadas ya estaban completas en el plan madre.", array("agregados" => 0, "ventas" => count($ventasProcesadas)));
	}
	responderPlanDefinitivoConsulta("exito","Ventas anexadas o completadas: ".count($ventasProcesadas).". Tratamientos agregados: ".$agregados.".", array("agregados" => $agregados, "ventas" => count($ventasProcesadas)));
}

function buscarPlanesMadreClienteConsulta($cod_cliente)
{
	$mysqli=conectar_al_servidor();
	if (!planDefinitivoTablasDisponiblesConsulta($mysqli)) {
		responderPlanDefinitivoConsulta("error","Debe aplicar la actualizacion SQL del plan madre.");
	}
	$paciente = obtenerDatosPacientePlanMadreConsulta($mysqli,$cod_cliente);
	if (!$paciente) {
		responderPlanDefinitivoConsulta("camposvacio","Seleccione un cliente valido.");
	}
	$planes = obtenerPlanesMadrePacienteConsulta($mysqli,$paciente["paciente_id"],$paciente["cedula"]);
	$salida = array();
	foreach ($planes as $plan) {
		$salida[] = array(
			"id" => (string)$plan["id"],
			"numero" => (int)$plan["plan_madre_numero"],
			"apodo" => (string)$plan["plan_madre_apodo"],
			"label" => (string)$plan["plan_madre_label"],
			"estado" => textoEstadoPlanDefinitivoConsulta($plan["estado"]),
			"version" => (int)$plan["version_actual"],
			"venta_base_id" => (string)$plan["venta_base_id"],
			"ventas_asignadas" => (int)$plan["ventas_asignadas"],
			"tratamientos_asignados" => (int)$plan["tratamientos_asignados"]
		);
	}
	responderPlanDefinitivoConsulta("exito","", array(
		"planes" => $salida,
		"paciente" => $paciente["paciente"],
		"cedula" => $paciente["cedula"]
	));
}

function actualizarApodoVentaPlanMadreConsulta($mysqli,$cod_venta,$apodo,$soloSiVacio=true)
{
	$apodo = trim((string)$apodo);
	if ($cod_venta == "" || $apodo == "") { return; }
	$apodoDb = mb_convert_encoding($apodo, 'ISO-8859-1', 'UTF-8');
	if ($soloSiVacio) {
		$stmt = $mysqli->prepare("UPDATE venta SET apodo = ? WHERE cod_venta = ? AND IFNULL(apodo,'') = '' LIMIT 1");
	} else {
		$stmt = $mysqli->prepare("UPDATE venta SET apodo = ? WHERE cod_venta = ? LIMIT 1");
	}
	if ($stmt) {
		$stmt->bind_param("ss", $apodoDb, $cod_venta);
		$stmt->execute();
	}
}

function anexarVentaCompletaPlanMadreConsulta($mysqli,$plan,$cod_venta,$user,$motivo,$origenBase)
{
	$planId = (string)$plan["id"];
	$selectRiesgo = ProductoRiesgoFinancieroSelectSql($mysqli, "pr");
	$stmtOrden = $mysqli->prepare("SELECT COALESCE(MAX(orden),0) AS max_orden FROM plan_definitivo_tratamiento_items WHERE plan_definitivo_id = ? AND activo = 1");
	if (!$stmtOrden) { return array("ok" => false, "mensaje" => "No se pudo preparar el orden del plan madre."); }
	$stmtOrden->bind_param("s", $planId);
	$stmtOrden->execute();
	$rowOrden = mysqli_fetch_assoc($stmtOrden->get_result());
	$orden = (int)$rowOrden["max_orden"];
	$sql = "SELECT dtv.cod_detalle, dtv.cod_ventaFK, dtv.cod_productoFK, pr.nombre_producto, ".$selectRiesgo."
		FROM detalle_venta dtv
		INNER JOIN venta vt ON vt.cod_venta = dtv.cod_ventaFK
		INNER JOIN cliente cl ON cl.cod_cliente = vt.cod_clienteFK
		INNER JOIN producto pr ON pr.cod_producto = dtv.cod_productoFK
		WHERE dtv.cod_ventaFK = ?
		AND (vt.cod_clienteFK = ? OR cl.ci_cliente = ?)
		AND IFNULL(dtv.estado,'') <> 'eliminado'
		AND IFNULL((SELECT COUNT(*) FROM cancelaciones c WHERE c.cod_venta = vt.cod_venta),0)=0
		".ProductoClinicoWhereSqlConsulta("pr")."
		ORDER BY nivel_riesgo_financiero ASC, dtv.cod_detalle ASC";
	$stmtDato = $mysqli->prepare($sql);
	$stmtExiste = $mysqli->prepare("SELECT COUNT(*) AS total FROM plan_definitivo_tratamiento_items WHERE plan_definitivo_id = ? AND detalle_venta_id = ? AND activo = 1");
	$stmtDesactivarOtros = $mysqli->prepare("UPDATE plan_definitivo_tratamiento_items SET activo = 0 WHERE detalle_venta_id = ? AND plan_definitivo_id <> ? AND activo = 1");
	$stmtInsert = $mysqli->prepare("INSERT INTO plan_definitivo_tratamiento_items
		(plan_definitivo_id, venta_id, detalle_venta_id, producto_id, nombre_tratamiento_snapshot, nivel_riesgo_snapshot, orden, origen, activo, fecha_agregado, agregado_por)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), ?)");
	if (!$stmtDato || !$stmtExiste || !$stmtDesactivarOtros || !$stmtInsert) {
		return array("ok" => false, "mensaje" => "No se pudo preparar la anexion al plan madre.");
	}
	$stmtDato->bind_param("sss", $cod_venta, $plan["paciente_id"], $plan["cedula"]);
	if (!$stmtDato->execute()) {
		return array("ok" => false, "mensaje" => "No se pudieron leer los tratamientos de la venta.");
	}
	$result = $stmtDato->get_result();
	$agregados = 0;
	$leidos = 0;
	$origen = $origenBase ? "plan_principal" : "venta_anexada";
	while ($row = mysqli_fetch_assoc($result)) {
		$leidos++;
		$detalleItem = (string)$row["cod_detalle"];
		$stmtDesactivarOtros->bind_param("ss", $detalleItem, $planId);
		$stmtDesactivarOtros->execute();
		$stmtExiste->bind_param("ss", $planId, $detalleItem);
		$stmtExiste->execute();
		$rowExiste = mysqli_fetch_assoc($stmtExiste->get_result());
		if ((int)$rowExiste["total"] > 0) {
			continue;
		}
		$orden++;
		$nombreUtf8 = mb_convert_encoding((string)$row["nombre_producto"], 'UTF-8', 'ISO-8859-1');
		$nombreDb = mb_convert_encoding((string)$nombreUtf8, 'ISO-8859-1', 'UTF-8');
		$nivel = ProductoRiesgoFinancieroNormalizar($row["nivel_riesgo_financiero"]);
		$ventaItem = (string)$row["cod_ventaFK"];
		$productoItem = (string)$row["cod_productoFK"];
		$stmtInsert->bind_param("sssssiiss", $planId, $ventaItem, $detalleItem, $productoItem, $nombreDb, $nivel, $orden, $origen, $user);
		if ($stmtInsert->execute()) {
			$agregados++;
		}
	}
	return array("ok" => true, "agregados" => $agregados, "leidos" => $leidos);
}

function desactivarPlanesMadreVaciosConsulta($mysqli,$paciente_id,$cedula,$planActivoId,$user)
{
	$stmt = $mysqli->prepare("UPDATE plan_definitivo_tratamiento pd
		SET pd.activo = 0, pd.fecha_actualizacion = NOW(), pd.actualizado_por = ?
		WHERE pd.activo = 1
		AND pd.id <> ?
		AND (pd.paciente_id = ? OR pd.cedula = ?)
		AND NOT EXISTS (
			SELECT 1
			FROM plan_definitivo_tratamiento_items pi
			WHERE pi.plan_definitivo_id = pd.id
			AND pi.activo = 1
			LIMIT 1
		)");
	if ($stmt) {
		$stmt->bind_param("ssss", $user, $planActivoId, $paciente_id, $cedula);
		$stmt->execute();
	}
}

function crearPlanMadreDesdeVentaConsulta($mysqli,$contexto,$apodo,$user)
{
	$cod_venta = (string)$contexto["venta_actual_id"];
	if (trim((string)$apodo) != "") {
		actualizarApodoVentaPlanMadreConsulta($mysqli,$cod_venta,$apodo,false);
	}
	$cedula = mb_convert_encoding((string)$contexto["cedula"], 'ISO-8859-1', 'UTF-8');
	$paciente_id = $contexto["paciente_id"];
	$estado = "borrador";
	$version = 1;
	$activo = 1;
	$stmt = $mysqli->prepare("INSERT INTO plan_definitivo_tratamiento
		(cedula, paciente_id, venta_base_id, doctor_cabecera_id, estado, version_actual, fecha_creacion, creado_por, fecha_actualizacion, actualizado_por, activo)
		VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, NOW(), ?, ?)");
	if (!$stmt) {
		return array("ok" => false, "mensaje" => "No se pudo preparar la creacion del plan madre.");
	}
	$stmt->bind_param("sssssisii", $cedula, $paciente_id, $cod_venta, $user, $estado, $version, $user, $user, $activo);
	if (!$stmt->execute()) {
		return array("ok" => false, "mensaje" => "No se pudo crear el plan madre.");
	}
	$planId = $mysqli->insert_id;
	$plan = obtenerPlanDefinitivoPorIdConsulta($mysqli,$planId);
	$resultado = anexarVentaCompletaPlanMadreConsulta($mysqli,$plan,$cod_venta,$user,"Creacion de plan madre desde venta #".$cod_venta,true);
	if (!$resultado["ok"]) { return $resultado; }
	if ((int)$resultado["leidos"] == 0) {
		return array("ok" => false, "mensaje" => "La venta no tiene tratamientos activos para anexar al plan madre.");
	}
	desactivarPlanesMadreVaciosConsulta($mysqli,$paciente_id,$contexto["cedula"],$planId,$user);
	$rol = obtenerRolUsuarioPlanDefinitivoConsulta($mysqli,$user);
	$alias = trim((string)$apodo) != "" ? $apodo : "Sin beneficiario";
	registrarHistorialPlanDefinitivoConsulta($mysqli,$planId,1,"creacion","Se creo el plan madre ".$alias." desde venta #".$cod_venta.".","","".$resultado["agregados"]." tratamientos anexados","",$user,$rol);
	return array("ok" => true, "plan_id" => $planId, "agregados" => $resultado["agregados"]);
}

function asignarVentaPlanMadreConsulta($cod_venta,$plan_id,$modo,$apodo,$user)
{
	$mysqli=conectar_al_servidor();
	if (!planDefinitivoTablasDisponiblesConsulta($mysqli)) {
		responderPlanDefinitivoConsulta("error","Debe aplicar la actualizacion SQL del plan madre.");
	}
	$modo = strtolower(trim((string)$modo));
	if ($modo == "pendiente") {
		responderPlanDefinitivoConsulta("exito","La venta quedo pendiente de anexar a un plan madre.");
	}
	$contexto = obtenerContextoPlanDefinitivoConsulta($mysqli,$cod_venta);
	if (!$contexto) {
		responderPlanDefinitivoConsulta("error","No se pudo identificar la venta.");
	}
	$mysqli->autocommit(false);
	if ($modo == "nuevo") {
		$resultadoNuevo = crearPlanMadreDesdeVentaConsulta($mysqli,$contexto,$apodo,$user);
		if (!$resultadoNuevo["ok"]) {
			$mysqli->rollback();
			responderPlanDefinitivoConsulta("error",$resultadoNuevo["mensaje"]);
		}
		$mysqli->commit();
		$seguimientoPaciente = actualizarSeguimientoPacientePlanMadreConsulta($cod_venta,$user);
		responderPlanDefinitivoConsulta("exito","Nuevo plan madre creado. Tratamientos anexados: ".$resultadoNuevo["agregados"].".", array("plan_id" => $resultadoNuevo["plan_id"], "agregados" => $resultadoNuevo["agregados"], "seguimiento_paciente" => $seguimientoPaciente));
	}
	$plan = obtenerPlanDefinitivoPorIdConsulta($mysqli,$plan_id);
	if (!$plan) {
		$mysqli->rollback();
		responderPlanDefinitivoConsulta("error","No se encontro el plan madre seleccionado.");
	}
	if ((string)$plan["paciente_id"] != (string)$contexto["paciente_id"] && trim((string)$plan["cedula"]) != trim((string)$contexto["cedula"])) {
		$mysqli->rollback();
		responderPlanDefinitivoConsulta("error","El plan madre no corresponde a esta cedula.");
	}
	$planes = obtenerPlanesMadrePacienteConsulta($mysqli,$contexto["paciente_id"],$contexto["cedula"]);
	$planInfo = buscarPlanMadreEnListaConsulta($planes,$plan_id);
	$aliasPlan = $planInfo ? $planInfo["plan_madre_apodo"] : "";
	if (trim((string)$apodo) != "") {
		actualizarApodoVentaPlanMadreConsulta($mysqli,$cod_venta,$apodo,true);
	} elseif (trim((string)$aliasPlan) != "" && $aliasPlan != "Sin beneficiario") {
		actualizarApodoVentaPlanMadreConsulta($mysqli,$cod_venta,$aliasPlan,true);
	}
	$motivo = "Anexion de tratamientos de venta #".$cod_venta." al plan madre.";
	$rol = obtenerRolUsuarioPlanDefinitivoConsulta($mysqli,$user);
	$versionado = versionarCambioPlanDefinitivoConsulta($mysqli,$plan,$motivo,$user);
	if (!$versionado["ok"]) {
		$mysqli->rollback();
		responderPlanDefinitivoConsulta("camposvacio",$versionado["mensaje"]);
	}
	$resultado = anexarVentaCompletaPlanMadreConsulta($mysqli,$plan,$cod_venta,$user,$motivo,((string)$plan["venta_base_id"] == (string)$cod_venta));
	if (!$resultado["ok"]) {
		$mysqli->rollback();
		responderPlanDefinitivoConsulta("error",$resultado["mensaje"]);
	}
	if ((int)$resultado["leidos"] == 0) {
		$mysqli->rollback();
		responderPlanDefinitivoConsulta("camposvacio","La venta no tiene tratamientos activos para anexar al plan madre.");
	}
	desactivarPlanesMadreVaciosConsulta($mysqli,$contexto["paciente_id"],$contexto["cedula"],$plan_id,$user);
	registrarHistorialPlanDefinitivoConsulta($mysqli,$plan_id,$versionado["version"],"venta_asignada","Se anexaron tratamientos de la venta #".$cod_venta." al plan madre.","","".$resultado["agregados"]." tratamientos anexados",$motivo,$user,$rol);
	$mysqli->commit();
	$seguimientoPaciente = actualizarSeguimientoPacientePlanMadreConsulta($cod_venta,$user);
	$mensajeAnexo = (int)$resultado["agregados"] > 0 ? "Tratamientos anexados al plan madre: ".$resultado["agregados"]."." : "Esta venta ya estaba anexada a ese plan madre.";
	responderPlanDefinitivoConsulta("exito",$mensajeAnexo, array("plan_id" => $plan_id, "agregados" => (int)$resultado["agregados"], "seguimiento_paciente" => $seguimientoPaciente));
}

function etiquetaAccionHistorialPlanDefinitivoConsulta($accion)
{
	$accion = (string)$accion;
	$mapa = array(
		"creacion" => "Creacion del plan",
		"creacion_desde_plan_sugerido" => "Creacion desde sugerencia",
		"guardado_borrador" => "Borrador guardado",
		"confirmacion" => "Plan confirmado",
		"cambio_orden" => "Cambio de orden",
		"observacion_modificada" => "Observacion modificada",
		"tratamiento_quitado" => "Tratamiento quitado",
		"tratamiento_anexado" => "Tratamiento anexado",
		"venta_asignada" => "Tratamientos anexados a plan madre"
	);
	if (isset($mapa[$accion])) {
		return $mapa[$accion];
	}
	$texto = trim(ucwords(str_replace("_", " ", $accion)));
	return $texto != "" ? $texto : "Accion registrada";
}

function renderValorHistorialPlanDefinitivoConsulta($titulo,$valor)
{
	$valor = mb_convert_encoding((string)$valor, 'UTF-8', 'ISO-8859-1');
	if (trim($valor) == "") {
		return "";
	}
	return "<div class='plan-definitivo-historial-valor'>"
		."<b>".htmlspecialchars($titulo, ENT_QUOTES, "UTF-8")."</b>"
		."<span>".nl2br(htmlspecialchars($valor, ENT_QUOTES, "UTF-8"))."</span>"
		."</div>";
}

function obtenerHistorialPlanDefinitivoConsulta($plan_id)
{
	$mysqli=conectar_al_servidor();
	if (!planDefinitivoTablasDisponiblesConsulta($mysqli)) {
		responderPlanDefinitivoConsulta("error","Debe aplicar la actualizacion SQL del plan madre.");
	}
	$sql = "SELECT h.*, p.nombre_persona
		FROM plan_definitivo_tratamiento_historial h
		LEFT JOIN persona p ON p.cod_persona = h.usuario_id
		WHERE h.plan_definitivo_id = ?
		ORDER BY h.fecha_hora DESC, h.id DESC";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		responderPlanDefinitivoConsulta("error","No se pudo preparar el historial.");
	}
	$stmt->bind_param("s", $plan_id);
	if (!$stmt->execute()) {
		responderPlanDefinitivoConsulta("error","No se pudo consultar el historial.");
	}
	$result = $stmt->get_result();
	$pagina = "<div class='plan-definitivo-historial-lista'>";
	if (mysqli_num_rows($result) == 0) {
		$pagina .= "<div class='plan-definitivo-empty-inline'>Sin historial registrado todav&iacute;a.</div>";
	}
	while ($row = mysqli_fetch_assoc($result)) {
		$fecha = $row["fecha_hora"] != "" ? date("d/m/Y H:i", strtotime($row["fecha_hora"])) : "";
		$usuario = mb_convert_encoding((string)$row["nombre_persona"], 'UTF-8', 'ISO-8859-1');
		if (trim($usuario) == "") { $usuario = "Usuario"; }
		$descripcion = mb_convert_encoding((string)$row["descripcion"], 'UTF-8', 'ISO-8859-1');
		$motivo = mb_convert_encoding((string)$row["motivo"], 'UTF-8', 'ISO-8859-1');
		$accion = mb_convert_encoding((string)$row["accion"], 'UTF-8', 'ISO-8859-1');
		$rol = mb_convert_encoding((string)$row["rol"], 'UTF-8', 'ISO-8859-1');
		$usuarioRol = htmlspecialchars($usuario, ENT_QUOTES, "UTF-8").($rol != "" ? " &middot; ".htmlspecialchars($rol, ENT_QUOTES, "UTF-8") : "");
		$valorAnterior = renderValorHistorialPlanDefinitivoConsulta("Antes", $row["valor_anterior"]);
		$valorNuevo = renderValorHistorialPlanDefinitivoConsulta("Despues", $row["valor_nuevo"]);
		$cambios = ($valorAnterior != "" || $valorNuevo != "")
			? "<div class='plan-definitivo-historial-cambio'>".$valorAnterior.$valorNuevo."</div>"
			: "";
		$pagina .= "<article class='plan-definitivo-historial-item'>"
			."<div class='plan-definitivo-historial-top'>"
				."<time>".htmlspecialchars($fecha, ENT_QUOTES, "UTF-8")."</time>"
				."<em>Versi&oacute;n ".htmlspecialchars((string)$row["version"], ENT_QUOTES, "UTF-8")."</em>"
			."</div>"
			."<span class='plan-definitivo-historial-accion'>".htmlspecialchars(etiquetaAccionHistorialPlanDefinitivoConsulta($accion), ENT_QUOTES, "UTF-8")."</span>"
			."<strong>".$usuarioRol."</strong>"
			."<span class='plan-definitivo-historial-desc'>".htmlspecialchars($descripcion, ENT_QUOTES, "UTF-8")."</span>"
			.$cambios
			.($motivo != "" ? "<p class='plan-definitivo-historial-motivo'>Motivo: ".htmlspecialchars($motivo, ENT_QUOTES, "UTF-8")."</p>" : "")
			."</article>";
	}
	$pagina .= "</div>";
	responderPlanDefinitivoConsulta("exito","", array("2" => $pagina));
}

function  buscarDetalleCompradoConsulta($cod_venta)
{
$mysqli=conectar_al_servidor();
$cod_venta = mysqli_real_escape_string($mysqli, $cod_venta);
$styleName="tableRegistroSearch";
$tratamientos = obtenerTratamientosVentaPlanConsulta($mysqli,$cod_venta);
$fechaBaseSugerida = "";
if (count($tratamientos) > 0 && isset($tratamientos[0]["fecha_venta"])) {
	$fechaBaseSugerida = (string)$tratamientos[0]["fecha_venta"];
}
if ($fechaBaseSugerida == "") {
	$fechaBaseSugerida = obtenerFechaVentaBasePlanDefinitivoConsulta($mysqli,$cod_venta);
}

$gruposPlan = ordenarTratamientosPlanSugerido($tratamientos);
$curvaSugerida = buildAutomaticSuggestionCurve($tratamientos,$fechaBaseSugerida);
$pagosMensualesSugeridos = obtenerPagosMensualesPlanConsulta($mysqli, ventasDesdeItemsPlanConsulta($tratamientos), $fechaBaseSugerida);
asignarResumenTemporalGruposPlanConsulta($gruposPlan,$curvaSugerida);
$metaPlanDefinitivo = array();
$planDefinitivoHtml = renderizarPlanDefinitivoConsulta($mysqli,$cod_venta,$tratamientos,$metaPlanDefinitivo);
$ventaBaseCrear = isset($metaPlanDefinitivo["venta_base_id"]) && trim((string)$metaPlanDefinitivo["venta_base_id"]) != "" ? $metaPlanDefinitivo["venta_base_id"] : $cod_venta;
$ventaBase = htmlspecialchars((string)$ventaBaseCrear, ENT_QUOTES, "UTF-8");
$avisoSugeridoHtml = "";
if (!empty($metaPlanDefinitivo["existe"])) {
	$avisoSugeridoHtml = "
	<div class='plan-sugerido-aviso-secundario'>
		<span>Esta es una referencia calculada autom&aacute;ticamente. La ruta cl&iacute;nica vigente se encuentra en el Plan madre.</span>
		<button type='button' class='plan-definitivo-secondary' onclick='volverARutaVigentePlanConsulta(this)'>Volver a la ruta vigente</button>
	</div>";
} else {
	$avisoSugeridoHtml = "
	<div class='plan-sugerido-aviso-secundario plan-sugerido-aviso-secundario--inicio'>
		<span>Us&aacute; esta sugerencia como punto de partida para crear la ruta cl&iacute;nica.</span>
		<button type='button' class='plan-definitivo-primary' onclick='crearPlanDefinitivoDesdeSugeridoConsulta(\"".$ventaBase."\")'>Crear plan madre</button>
	</div>";
}
$planSugeridoHtml = "
<div class='plan-tratamientos-panel plan-sugerido-wrapper' data-plan-vista='plan_sugerido'>
	<div class='plan-tratamientos-header plan-sugerido-header'>
		<div>
			<strong>Sugerencia autom&aacute;tica</strong>
			<p class='plan-tratamientos-ayuda'>Referencia calculada por el sistema seg&uacute;n estado y riesgo financiero.</p>
			<div class='plan-tratamientos-chips'>
				<span>En proceso primero</span>
				<span>Pendientes N1 - N5</span>
				<span>Finalizados al final</span>
			</div>
		</div>
		<label class='plan-tratamientos-vista'>
			<span>Vista</span>
			<select onchange='cambiarVistaPlanTratamientosConsulta(this)'>
				<option value='plan_sugerido' selected>Sugerencia autom&aacute;tica</option>
				<option value='todos'>Todos</option>
				<option value='continuar'>En proceso</option>
				<option value='siguientes'>Pendientes</option>
				<option value='finalizados'>Finalizados</option>
			</select>
		</label>
	</div>
	".$avisoSugeridoHtml."
	<div class='plan-tratamientos-contenido'>";
$planSugeridoHtml .= renderizarSeccionPlanTratamientoConsulta("Continuar", "Ya iniciados, conviene continuarlos.", $gruposPlan["continuar"], "No hay tratamientos en proceso.", "continuar", $styleName);
$planSugeridoHtml .= renderizarSeccionPlanTratamientoConsulta("Siguientes sugeridos", "Ordenado por riesgo y orden natural.", $gruposPlan["siguientes"], "No hay tratamientos pendientes.", "siguientes", $styleName);
$planSugeridoHtml .= renderizarSeccionPlanTratamientoConsulta("Finalizados", "Tratamientos que ya no cuentan como prioridad activa.", $gruposPlan["finalizados"], "No hay tratamientos finalizados.", "finalizados", $styleName);
$planSugeridoHtml .= renderPlanRiskChart($curvaSugerida,array(),"sugerencia",$pagosMensualesSugeridos);
$planSugeridoHtml .= "
	</div>
</div>";

$pagina = renderizarTabsPlanesConsulta($planSugeridoHtml,$planDefinitivoHtml,$metaPlanDefinitivo);
  
$informacion =array("1" => "exito","2" => $pagina );
echo json_encode($informacion);	
exit;
}

function buscarConsulta($Paciente,$local,$num_factura) {
    $mysqli=conectar_al_servidor();
	$pagina='';

	$Paciente = trim((string)$Paciente);
	$local = trim((string)$local);
	$num_factura = trim((string)$num_factura);
	$pacienteSql = mysqli_real_escape_string($mysqli,$Paciente);
	$localSql = mysqli_real_escape_string($mysqli,$local);
	$numFacturaSql = mysqli_real_escape_string($mysqli,$num_factura);
	 
    $sqlFiltro= "";
	$sqlJoinBusquedaPaciente = "";
	if($local!=""){
		$sqlFiltro.=" and vt.cod_local='".$localSql."' ";
	}
	
	if($Paciente!=""){
		$pacienteSoloNumeros = preg_replace('/[^0-9]/','',$Paciente);
		$esDocumentoOTelefonoExacto = ($pacienteSoloNumeros === $Paciente && strlen($Paciente) >= 5);
		if ($esDocumentoOTelefonoExacto) {
			$sqlJoinBusquedaPaciente=" inner join (
				select cl_busqueda.cod_cliente from cliente cl_busqueda where cl_busqueda.ci_cliente='".$pacienteSql."'
				union
				select cl_busqueda.cod_cliente from cliente cl_busqueda where cl_busqueda.rut_cliente='".$pacienteSql."'
				union
				select p_busqueda.cod_persona as cod_cliente from persona p_busqueda where p_busqueda.telefono='".$pacienteSql."'
			) busqueda_paciente on busqueda_paciente.cod_cliente=vt.cod_clienteFK ";
		} else {
			$sqlFiltro.=" and (cl.ci_cliente like '%".$pacienteSql."%' or cl.rut_cliente like '%".$pacienteSql."%' or p.nombre_persona like '%".$pacienteSql."%' or p.telefono like '%".$pacienteSql."%') ";
		}
	}

    if($num_factura!=""){
		$sqlFiltro.=" and vt.num_factura='".$numFacturaSql."' ";
	}

	$planesMadreDisponibles = planDefinitivoTablasDisponiblesConsulta($mysqli);
	$condicionTratamientoClinicoPorcentaje = ProductoClinicoWhereSqlConsulta("pr_por");
	$condicionTratamientoClinicoTotal = ProductoClinicoWhereSqlConsulta("pr_tot");
	$planDefinitivoSelect = ",
    '' as plan_definitivo_id,
    '' as plan_definitivo_venta_base_id,
    '' as plan_definitivo_estado,
	0 as plan_definitivo_version,
    0 as plan_definitivo_items_venta";
	
    $sql= "Select  nombre_persona as paciente,cl.ci_cliente,cl.cod_cliente,num_factura,cod_venta,apodo, vt.cod_local as cod_local_venta,
    (select sum(dtv_por.progreso_porcentaje) from detalle_venta dtv_por inner join producto pr_por on pr_por.cod_producto=dtv_por.cod_productoFK where dtv_por.cod_ventaFK=vt.cod_venta".$condicionTratamientoClinicoPorcentaje.") as porcentaje , 
    (select Nombre from local where cod_local=vt.cod_local) as nombre_local , 
    (select count(*) from detalle_venta dtv_tot inner join producto pr_tot on pr_tot.cod_producto=dtv_tot.cod_productoFK where dtv_tot.cod_ventaFK=vt.cod_venta".$condicionTratamientoClinicoTotal.") as totalporcentaje
    ".$planDefinitivoSelect."
    from venta vt ".$sqlJoinBusquedaPaciente." inner join cliente cl on vt.cod_clienteFK=cl.cod_cliente
    inner join persona p on cl.cod_cliente=p.cod_persona
    where cl.estado = 'Activo' and not exists (select 1 from cancelaciones ca where ca.cod_venta=vt.cod_venta)".$sqlFiltro." limit 100;";
  
    $stmt = $mysqli->prepare($sql);
    if ( ! $stmt->execute()) {
        echo "Error";
        exit;
    }
 
	$result = $stmt->get_result();
    $valor= mysqli_num_rows($result);
    $nroRegistro= $valor;
    $registros=array();
 
    if ($valor>0) {
        while ($valor= mysqli_fetch_assoc($result)) {
            $num_factura=mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');
            $ci_cliente=mb_convert_encoding((string)($valor['ci_cliente']), 'UTF-8', 'ISO-8859-1'); 
            $paciente=mb_convert_encoding((string)($valor['paciente']), 'UTF-8', 'ISO-8859-1');
            $cod_cliente=mb_convert_encoding((string)($valor['cod_cliente']), 'UTF-8', 'ISO-8859-1');
            $decripcion=''; 
            $cod_venta=mb_convert_encoding((string)($valor['cod_venta']), 'UTF-8', 'ISO-8859-1');
            $apodo=mb_convert_encoding((string)($valor['apodo']), 'UTF-8', 'ISO-8859-1');
            $cod_local_venta=mb_convert_encoding((string)($valor['cod_local_venta']), 'UTF-8', 'ISO-8859-1');
            $nombre_local=mb_convert_encoding((string)($valor['nombre_local']), 'UTF-8', 'ISO-8859-1');
            $porcentaje = $valor['porcentaje'];
            $totalporcentaje = $valor['totalporcentaje'];

            $registros[] = array(
                "num_factura" => $num_factura,
                "ci_cliente" => $ci_cliente,
                "paciente" => $paciente,
                "cod_cliente" => $cod_cliente,
                "decripcion" => $decripcion,
                "cod_venta" => $cod_venta,
                "apodo" => $apodo,
                "cod_local_venta" => $cod_local_venta,
                "nombre_local" => $nombre_local,
                "porcentaje" => $porcentaje,
                "totalporcentaje" => $totalporcentaje,
                "plan_definitivo_id" => "",
                "plan_definitivo_venta_base_id" => "",
                "plan_definitivo_estado" => "",
                "plan_definitivo_version" => 0,
                "plan_definitivo_items_venta" => 0,
                "plan_madre_numero" => 0,
                "plan_madre_apodo" => "",
                "plan_madre_label" => "",
            );
        }
    }

    if ($planesMadreDisponibles && count($registros) > 0) {
        $planesPorVenta = obtenerPlanesMadreAsignadosVentasConsulta($mysqli,$registros);
        foreach ($registros as $indiceRegistro => $registro) {
            $codVentaPlan = isset($registro["cod_venta"]) ? (string)((int)$registro["cod_venta"]) : "";
            if ($codVentaPlan == "" || !isset($planesPorVenta[$codVentaPlan])) {
                continue;
            }
            $planMadreInfo = $planesPorVenta[$codVentaPlan];
            $registros[$indiceRegistro]["plan_definitivo_id"] = mb_convert_encoding((string)($planMadreInfo['id']), 'UTF-8', 'ISO-8859-1');
            $registros[$indiceRegistro]["plan_definitivo_venta_base_id"] = mb_convert_encoding((string)($planMadreInfo['venta_base_id']), 'UTF-8', 'ISO-8859-1');
            $registros[$indiceRegistro]["plan_definitivo_estado"] = mb_convert_encoding((string)($planMadreInfo['estado']), 'UTF-8', 'ISO-8859-1');
            $registros[$indiceRegistro]["plan_definitivo_version"] = (int)$planMadreInfo['version_actual'];
            $registros[$indiceRegistro]["plan_definitivo_items_venta"] = (int)$planMadreInfo['items_venta'];
            $registros[$indiceRegistro]["plan_madre_numero"] = (int)$planMadreInfo['plan_madre_numero'];
            $registros[$indiceRegistro]["plan_madre_apodo"] = mb_convert_encoding((string)($planMadreInfo['plan_madre_apodo']), 'UTF-8', 'ISO-8859-1');
            $registros[$indiceRegistro]["plan_madre_label"] = mb_convert_encoding((string)($planMadreInfo['plan_madre_label']), 'UTF-8', 'ISO-8859-1');
        }
    }
    mysqli_close($mysqli);

    return $registros;
}

function buscarSelectorTratamiento($Paciente,$local,$num_factura) {
    $registros = buscarConsulta($Paciente,$local,$num_factura);
    
	$pagina='';
    foreach ($registros as $valor) {
        
    }
}

function resumenTratamientoVacioConsulta()
{
    return array(
        "html" => "",
        "pendientes" => 0,
        "proceso" => 0,
        "completados" => 0,
        "cancelados" => 0,
        "activos" => 0
    );
}

function finalizarResumenTratamientoConsulta($resumen)
{
    if (trim((string)$resumen["html"]) == "") {
        $resumen["html"] = "<ul class='clinical-treatment-list'><li class='clinical-treatment-empty'>Sin tratamientos registrados</li></ul>";
        return $resumen;
    }
    $resumen["html"] = "<ul class='clinical-treatment-list'>".$resumen["html"]."</ul>";
    return $resumen;
}

function agregarTratamientoAlResumenConsulta(&$resumen,$row)
{
    $nombre_producto = mb_convert_encoding((string)($row['nombre_producto']), 'UTF-8', 'ISO-8859-1');
    $cantidad_detalle = isset($row['cantidad_detalle']) ? formatearCantidadTratamientoConsulta($row['cantidad_detalle']) : "1";
    $estado = mb_convert_encoding((string)($row['estado']), 'UTF-8', 'ISO-8859-1');
    $progreso_porcentaje = mb_convert_encoding((string)($row['progreso_porcentaje']), 'UTF-8', 'ISO-8859-1');
    $progreso_porcentaje = max(0, min(100, (int)$progreso_porcentaje));
    $nombre_producto_html = htmlspecialchars($nombre_producto, ENT_QUOTES, 'UTF-8');
    $cantidad_detalle_html = htmlspecialchars($cantidad_detalle, ENT_QUOTES, 'UTF-8');
    $nivel_riesgo_financiero = isset($row['nivel_riesgo_financiero']) ? $row['nivel_riesgo_financiero'] : 1;
    $badge_riesgo_financiero = ProductoRiesgoFinancieroBadgeHtml($nivel_riesgo_financiero, "clinical-treatment-risk");
    $estado_normalizado = strtolower(trim($estado));
    $estado_clase = "pendiente";
    $estado_texto = "Pendiente";

    if ($estado_normalizado == "eliminado" || $estado_normalizado == "anulado" || $estado_normalizado == "cancelado") {
        $estado_clase = "cancelado";
        $estado_texto = "Anulado";
        $resumen["cancelados"]++;
    } elseif ($progreso_porcentaje >= 100) {
        $estado_clase = "completado";
        $estado_texto = "Completado";
        $resumen["completados"]++;
        $resumen["activos"]++;
    } elseif ($progreso_porcentaje > 0) {
        $estado_clase = "proceso";
        $estado_texto = "En proceso";
        $resumen["proceso"]++;
        $resumen["activos"]++;
    } else {
        $resumen["pendientes"]++;
        $resumen["activos"]++;
    }

    $resumen["html"] .= "
            <li class='clinical-treatment-item clinical-treatment-item--".$estado_clase."'>
                <span class='clinical-treatment-name'>".$nombre_producto_html."</span>
                <span class='clinical-treatment-quantity' title='Cantidad comprada'>Cant. ".$cantidad_detalle_html."</span>
                ".$badge_riesgo_financiero."
                <span class='clinical-treatment-status'>".$estado_texto."</span>
                <span class='clinical-treatment-progress'>".$progreso_porcentaje."%</span>
            </li>";
}

function formatearCantidadTratamientoConsulta($cantidad)
{
    $cantidadNumero = (float)str_replace(",", ".", (string)$cantidad);
    $cantidadTexto = number_format($cantidadNumero, 2, ",", ".");
    return rtrim(rtrim($cantidadTexto, "0"), ",");
}

function detalleTratamientoDatos($buscar) {
    $mysqli = conectar_al_servidor();
    $buscar = mysqli_real_escape_string($mysqli, $buscar);

    $selectRiesgo = ProductoRiesgoFinancieroSelectSql($mysqli, "pr");
    $sql = "SELECT pr.cod_producto, pr.nombre_producto, dtv.cantidad_detalle, dtv.estado, dtv.progreso_porcentaje, ".$selectRiesgo." 
            FROM producto pr 
            INNER JOIN detalle_venta dtv ON dtv.cod_productoFK = pr.cod_producto
            WHERE dtv.cod_ventaFK = '$buscar'
            ".ProductoClinicoWhereSqlConsulta("pr");

    $stmt = $mysqli->prepare($sql);
    if (!$stmt->execute()) {
        telar_trigger_error('Query error: '.$stmt->error, E_USER_ERROR);
        exit;
    }

    $result = $stmt->get_result();
    $resumen = resumenTratamientoVacioConsulta();

    while ($row = mysqli_fetch_assoc($result)) {
        agregarTratamientoAlResumenConsulta($resumen,$row);
    }
    mysqli_close($mysqli);

    return finalizarResumenTratamientoConsulta($resumen);
}

function detalleTratamientosDatosPorVentaConsulta($ventas)
{
    $ventasIds = array();
    foreach ($ventas as $venta) {
        $venta = trim((string)$venta);
        if ($venta != "" && ctype_digit($venta)) {
            $ventasIds[(string)((int)$venta)] = (int)$venta;
        }
    }

    $resumenes = array();
    foreach ($ventasIds as $ventaId) {
        $resumenes[(string)$ventaId] = resumenTratamientoVacioConsulta();
    }
    if (count($ventasIds) == 0) {
        return $resumenes;
    }

    $mysqli = conectar_al_servidor();
    $selectRiesgo = ProductoRiesgoFinancieroSelectSql($mysqli, "pr");
    $sql = "SELECT dtv.cod_ventaFK, pr.cod_producto, pr.nombre_producto, dtv.cantidad_detalle, dtv.estado, dtv.progreso_porcentaje, ".$selectRiesgo."
            FROM detalle_venta dtv
            INNER JOIN producto pr ON pr.cod_producto = dtv.cod_productoFK
            WHERE dtv.cod_ventaFK IN (".implode(",", $ventasIds).")
            ".ProductoClinicoWhereSqlConsulta("pr")."
            ORDER BY dtv.cod_ventaFK ASC, dtv.cod_detalle ASC";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt || !$stmt->execute()) {
        telar_trigger_error('Query error: '.$mysqli->error, E_USER_ERROR);
        exit;
    }

    $result = $stmt->get_result();
    while ($row = mysqli_fetch_assoc($result)) {
        $ventaId = (string)((int)$row["cod_ventaFK"]);
        if (!isset($resumenes[$ventaId])) {
            $resumenes[$ventaId] = resumenTratamientoVacioConsulta();
        }
        agregarTratamientoAlResumenConsulta($resumenes[$ventaId],$row);
    }
    mysqli_close($mysqli);

    foreach ($resumenes as $ventaId => $resumen) {
        $resumenes[$ventaId] = finalizarResumenTratamientoConsulta($resumen);
    }
    return $resumenes;
}

function buscarVistaConsulta($Paciente,$local,$num_factura) {
    $registros = buscarConsulta($Paciente,$local,$num_factura);
    
	$pagina='';
    $gruposPaciente = array();
    $ordenEstado = array("proceso" => 1, "pendiente" => 2, "completado" => 3, "cancelado" => 4);
    $ventasConsulta = array();
    foreach ($registros as $registroDetalle) {
        if (isset($registroDetalle["cod_venta"]) && trim((string)$registroDetalle["cod_venta"]) != "") {
            $ventasConsulta[] = $registroDetalle["cod_venta"];
        }
    }
    $detallesTratamientoPorVenta = detalleTratamientosDatosPorVentaConsulta($ventasConsulta);

    foreach ($registros as $indiceRegistro => $valor) {
        $num_factura= $valor['num_factura'];
        $ci_cliente= $valor['ci_cliente'];
        $paciente= $valor['paciente'];
        $cod_cliente= $valor['cod_cliente'];
        $cod_venta= $valor['cod_venta'];
        $apodo= $valor['apodo'];
        $nombre_local= $valor['nombre_local'];
        $planDefinitivoId = isset($valor['plan_definitivo_id']) ? $valor['plan_definitivo_id'] : "";
        $planDefinitivoVentaBaseId = isset($valor['plan_definitivo_venta_base_id']) ? $valor['plan_definitivo_venta_base_id'] : "";
        $planDefinitivoEstado = isset($valor['plan_definitivo_estado']) ? $valor['plan_definitivo_estado'] : "";
        $planDefinitivoVersion = isset($valor['plan_definitivo_version']) ? (int)$valor['plan_definitivo_version'] : 0;
        $planDefinitivoItemsVenta = isset($valor['plan_definitivo_items_venta']) ? (int)$valor['plan_definitivo_items_venta'] : 0;
        $planMadreNumero = isset($valor['plan_madre_numero']) ? (int)$valor['plan_madre_numero'] : 0;
        $planMadreApodo = isset($valor['plan_madre_apodo']) ? $valor['plan_madre_apodo'] : "";
        $planMadreLabel = isset($valor['plan_madre_label']) ? $valor['plan_madre_label'] : "";
        $porcentaje = $valor['porcentaje'];
        $totalporcentaje = $valor['totalporcentaje'];
        $totalporcentaje = $totalporcentaje * 100;

        if ($totalporcentaje > 0) {
            $resultadoPorcentaje = round(($porcentaje / $totalporcentaje) * 100);
        } else {
            $resultadoPorcentaje = 0;
        }

        $estadoClase = "pendiente";
        $estadoTexto = "Pendiente";
        if ($resultadoPorcentaje >= 100) {
            $estadoClase = "completado";
            $estadoTexto = "Completado";
        } elseif ($resultadoPorcentaje > 0) {
            $estadoClase = "proceso";
            $estadoTexto = "En proceso";
        }

        $codVentaDetalleKey = ctype_digit((string)$cod_venta) ? (string)((int)$cod_venta) : (string)$cod_venta;
        $detalleTratamiento = isset($detallesTratamientoPorVenta[$codVentaDetalleKey])
            ? $detallesTratamientoPorVenta[$codVentaDetalleKey]
            : finalizarResumenTratamientoConsulta(resumenTratamientoVacioConsulta());
        $tratamientosActivos = isset($detalleTratamiento["activos"]) ? (int)$detalleTratamiento["activos"] : 0;
        $totalTratamientosRegistro = $detalleTratamiento["pendientes"] + $detalleTratamiento["proceso"] + $detalleTratamiento["completados"] + $detalleTratamiento["cancelados"];
        $claveGrupo = $cod_cliente != "" ? $cod_cliente : $ci_cliente."_".$paciente;

        if (!isset($gruposPaciente[$claveGrupo])) {
            $pacienteHtml = htmlspecialchars($paciente, ENT_QUOTES, 'UTF-8');
            $apodoHtml = $apodo != "" ? " <span class='clinical-patient-alias'>(".htmlspecialchars($apodo, ENT_QUOTES, 'UTF-8').")</span>" : "";
            $gruposPaciente[$claveGrupo] = array(
                "paciente_html" => $pacienteHtml.$apodoHtml,
                "paciente_oculto" => htmlspecialchars($paciente.($apodo != "" ? " (".$apodo.")" : ""), ENT_QUOTES, 'UTF-8'),
                "ci_cliente" => htmlspecialchars($ci_cliente, ENT_QUOTES, 'UTF-8'),
                "apodo" => htmlspecialchars($apodo, ENT_QUOTES, 'UTF-8'),
                "pendientes" => 0,
                "proceso" => 0,
                "completados" => 0,
                "plan_definitivo_id" => "",
                "plan_definitivo_venta_base_id" => "",
                "plan_definitivo_base_titulo" => "",
                "plan_definitivo_estado" => "",
                "plan_definitivo_version" => 0,
                "planes_madre" => array(),
                "plan_grupos" => array(),
                "registros" => array()
            );
        }

        $gruposPaciente[$claveGrupo]["pendientes"] += $detalleTratamiento["pendientes"];
        $gruposPaciente[$claveGrupo]["proceso"] += $detalleTratamiento["proceso"];
        $gruposPaciente[$claveGrupo]["completados"] += $detalleTratamiento["completados"];

        $numFacturaHtml = htmlspecialchars($num_factura, ENT_QUOTES, 'UTF-8');
        $codVentaHtml = htmlspecialchars($cod_venta, ENT_QUOTES, 'UTF-8');
        $codLocalVentaHtml = htmlspecialchars($valor['cod_local_venta'], ENT_QUOTES, 'UTF-8');
        $localHtml = htmlspecialchars($nombre_local, ENT_QUOTES, 'UTF-8');
        $ciHidden = htmlspecialchars($ci_cliente, ENT_QUOTES, 'UTF-8');
        $tituloRegistro = $num_factura != "" ? "Venta #".$numFacturaHtml : "C&oacute;digo venta #".$codVentaHtml;
        $codigoVisible = $num_factura != "" ? $numFacturaHtml : $codVentaHtml;
        $apodoActualHtml = htmlspecialchars($apodo, ENT_QUOTES, 'UTF-8');
        $apodoBadgeHtml = trim($apodo) != "" ? "<span class='clinical-card__nickname'>Apodo/beneficiario: ".$apodoActualHtml."</span>" : "";
        $pacienteOcultoRegistro = htmlspecialchars($paciente.($apodo != "" ? " (".$apodo.")" : ""), ENT_QUOTES, 'UTF-8');
        $tienePlanDefinitivo = trim((string)$planDefinitivoId) != "";
        $esVentaBasePlanDefinitivo = $tienePlanDefinitivo && ((string)$planDefinitivoVentaBaseId == (string)$cod_venta);
        $ventaIncluidaPlanDefinitivo = $tienePlanDefinitivo && ($planDefinitivoItemsVenta > 0 || $esVentaBasePlanDefinitivo);
        $tratamientosConteoPlan = $ventaIncluidaPlanDefinitivo ? (int)$planDefinitivoItemsVenta : $totalTratamientosRegistro;
        $estadoPlanDefinitivoTexto = textoEstadoPlanDefinitivoConsulta($planDefinitivoEstado);
        $planDefinitivoCardClass = $ventaIncluidaPlanDefinitivo ? " clinical-record-card--plan-asignado" : " clinical-record-card--plan-pendiente";
        $planDefinitivoMarkerHtml = "";
        $planAsignarBtnHtml = "";
        $planGrupoKey = "pendiente";
        $planGrupoOrden = 9999;
        $planGrupoClase = "pendiente";
        $planGrupoTitulo = "Pendiente de anexar";
        $planGrupoSubtitulo = "Ventas cuyos tratamientos todav&iacute;a no forman parte de un plan madre.";
        $planGrupoDetalle = "Estas ventas deben anexarse a un plan madre antes de quedar dentro de una ruta cl&iacute;nica activa.";
        $planGrupoBeneficiario = "";
        $planGrupoNumero = "!";
        if ($ventaIncluidaPlanDefinitivo) {
            $beneficiarioPlanMadre = trim($planMadreApodo) != "" ? $planMadreApodo : "Sin beneficiario";
            $labelPlanMadre = trim($planMadreLabel) != "" ? $planMadreLabel : ("Plan madre #".$planMadreNumero);
            $labelPlanMadreHtml = htmlspecialchars($labelPlanMadre, ENT_QUOTES, 'UTF-8');
            $planDefinitivoMarkerHtml = "<span class='clinical-plan-mini-marker clinical-plan-mini-marker--base' title='Tratamientos anexados a un plan madre'><span aria-hidden='true'>".$planMadreNumero."</span>Anexado a ".htmlspecialchars($labelPlanMadre, ENT_QUOTES, 'UTF-8')."</span>";
            $gruposPaciente[$claveGrupo]["planes_madre"][(string)$planDefinitivoId] = array(
                "numero" => $planMadreNumero,
                "apodo" => htmlspecialchars($planMadreApodo, ENT_QUOTES, 'UTF-8'),
                "label" => $labelPlanMadreHtml
            );
            $planGrupoKey = "plan_".$planDefinitivoId;
            $planGrupoOrden = $planMadreNumero > 0 ? $planMadreNumero : 500;
            $planGrupoClase = "asignado";
            $planGrupoTitulo = $labelPlanMadreHtml;
            $planGrupoSubtitulo = "Plan integral de tratamiento";
            $planGrupoDetalle = "Plan madre activo: tratamientos fusionados bajo este plan.";
            $planGrupoBeneficiario = "Apodo/beneficiario: ".htmlspecialchars($beneficiarioPlanMadre, ENT_QUOTES, 'UTF-8');
            $planGrupoNumero = $planMadreNumero > 0 ? (string)$planMadreNumero : "#";
        } else {
            if ($tratamientosActivos <= 0) {
                $planGrupoKey = "sin_activos";
                $planGrupoOrden = 10000;
                $planGrupoClase = "inactivo";
                $planGrupoTitulo = "Sin tratamientos activos";
                $planGrupoSubtitulo = "Ventas anuladas o sin tratamientos disponibles para fusionar.";
                $planGrupoDetalle = "Estas ventas quedan visibles como historial, pero no se anexan a un plan madre activo.";
                $planGrupoNumero = "-";
                $planDefinitivoMarkerHtml = "<span class='clinical-plan-mini-marker clinical-plan-mini-marker--inactive' title='No hay tratamientos activos para fusionar al plan madre'><span aria-hidden='true'>!</span>Sin tratamientos activos</span>";
                $planAsignarBtnHtml = "<span class='clinical-plan-assign-note'>No anexable</span>";
            } else {
                $planDefinitivoMarkerHtml = "<span class='clinical-plan-mini-marker clinical-plan-mini-marker--pending' title='Tratamientos pendientes de anexar a un plan madre'><span aria-hidden='true'>!</span>Pendiente de anexar</span>";
                $planAsignarBtnHtml = "<button type='button' class='clinical-plan-assign-btn' onclick='event.stopPropagation(); abrirAsignarPlanMadreVentaConsulta(\"".$codVentaHtml."\",\"".htmlspecialchars($cod_cliente, ENT_QUOTES, 'UTF-8')."\",\"".$apodoActualHtml."\")'>Anexar a plan madre</button>";
            }
        }
        if (!isset($gruposPaciente[$claveGrupo]["plan_grupos"][$planGrupoKey])) {
            $gruposPaciente[$claveGrupo]["plan_grupos"][$planGrupoKey] = array(
                "orden" => $planGrupoOrden,
                "clase" => $planGrupoClase,
                "titulo" => $planGrupoTitulo,
                "subtitulo" => $planGrupoSubtitulo,
                "detalle" => $planGrupoDetalle,
                "beneficiario" => $planGrupoBeneficiario,
                "numero" => $planGrupoNumero,
                "venta_base_id" => htmlspecialchars((string)$planDefinitivoVentaBaseId, ENT_QUOTES, 'UTF-8'),
                "ventas" => 0,
                "tratamientos" => 0,
                "base" => null,
                "registros" => array()
            );
        }
        $gruposPaciente[$claveGrupo]["plan_grupos"][$planGrupoKey]["ventas"]++;
        $gruposPaciente[$claveGrupo]["plan_grupos"][$planGrupoKey]["tratamientos"] += $tratamientosConteoPlan;
        if ($tienePlanDefinitivo && $gruposPaciente[$claveGrupo]["plan_definitivo_id"] == "") {
            $gruposPaciente[$claveGrupo]["plan_definitivo_id"] = htmlspecialchars((string)$planDefinitivoId, ENT_QUOTES, 'UTF-8');
            $gruposPaciente[$claveGrupo]["plan_definitivo_venta_base_id"] = htmlspecialchars((string)$planDefinitivoVentaBaseId, ENT_QUOTES, 'UTF-8');
            $gruposPaciente[$claveGrupo]["plan_definitivo_base_titulo"] = "Venta #".htmlspecialchars((string)$planDefinitivoVentaBaseId, ENT_QUOTES, 'UTF-8');
            $gruposPaciente[$claveGrupo]["plan_definitivo_estado"] = htmlspecialchars($estadoPlanDefinitivoTexto, ENT_QUOTES, 'UTF-8');
            $gruposPaciente[$claveGrupo]["plan_definitivo_version"] = $planDefinitivoVersion;
        }
        if ($esVentaBasePlanDefinitivo) {
            $gruposPaciente[$claveGrupo]["plan_definitivo_base_titulo"] = $tituloRegistro;
        }

        $datosOcultosRegistroHtml = "
          <div style='display:none;'>
            <span id='td_datos_1'>".$pacienteOcultoRegistro."</span>
            <span id='td_datos_2'>".$ciHidden."</span>
            <span id='td_datos_3'>".$numFacturaHtml."</span>
            <span id='td_datos_4'></span>
            <span id='td_datos_5'>".$codVentaHtml."</span>
            <span id='td_datos_6'>".htmlspecialchars($cod_cliente, ENT_QUOTES, 'UTF-8')."</span>
            <span id='td_datos_7'>".$apodoActualHtml."</span>
            <span id='td_datos_8'>".$localHtml."</span>
            <span id='td_datos_9'>".$codLocalVentaHtml."</span>
          </div>";
        $eyebrowRegistro = "Registro cl&iacute;nico";
        if ($ventaIncluidaPlanDefinitivo) {
            $eyebrowRegistro = $esVentaBasePlanDefinitivo ? "Venta base del plan madre" : "Plan anexado";
        }
        $rolPlanRegistroClass = "";
        if ($ventaIncluidaPlanDefinitivo) {
            $rolPlanRegistroClass = $esVentaBasePlanDefinitivo ? " clinical-record-card--plan-madre-base" : " clinical-record-card--plan-hijo";
        }

        $registroVista = array(
            "orden" => $ordenEstado[$estadoClase],
            "indice" => $indiceRegistro,
            "es_base" => $esVentaBasePlanDefinitivo,
            "titulo" => $tituloRegistro,
            "estado_clase" => $estadoClase,
            "estado_texto" => $estadoTexto,
            "porcentaje" => $resultadoPorcentaje,
            "tratamientos_count" => $tratamientosConteoPlan,
            "datos_html" => $datosOcultosRegistroHtml,
            "html" => "
        <div class='tarjeta-paciente clinical-record-card clinical-record-card--".$estadoClase.$planDefinitivoCardClass.$rolPlanRegistroClass."' onclick='ObtenerdatosAbmConsulta(this)' onkeyup='if(event.keyCode==13||event.keyCode==32){ObtenerdatosAbmConsulta(this)}' role='button' tabindex='0' title='Abrir historial cl&iacute;nico' aria-label='Abrir historial cl&iacute;nico del paciente'>
          <div class='clinical-record-card__header'>
            <div>
              <span class='clinical-record-card__eyebrow'>".$eyebrowRegistro."</span>
              <div class='clinical-record-card__title-row'>
                <h3>".$tituloRegistro."</h3>
                ".$apodoBadgeHtml."
                ".$planDefinitivoMarkerHtml."
              </div>
            </div>
            <div class='clinical-progress-badge clinical-progress-badge--".$estadoClase."'>
              <strong>".$resultadoPorcentaje."%</strong>
              <span>".$estadoTexto."</span>
            </div>
          </div>

          <div class='clinical-record-meta'>
            <span><strong>Local</strong>".$localHtml."</span>
          </div>

          <div class='clinical-treatment-block'>
            <strong>Tratamientos</strong>
            ".$detalleTratamiento["html"]."
          </div>

          <span class='clinical-record-action'>Ver evoluci&oacute;n</span>
          ".$planAsignarBtnHtml."

          ".$datosOcultosRegistroHtml."
        </div>"
        );
        $gruposPaciente[$claveGrupo]["registros"][] = $registroVista;
        $gruposPaciente[$claveGrupo]["plan_grupos"][$planGrupoKey]["registros"][] = $registroVista;
        if ($esVentaBasePlanDefinitivo) {
            $gruposPaciente[$claveGrupo]["plan_grupos"][$planGrupoKey]["base"] = $registroVista;
        }
    }

    foreach ($gruposPaciente as $grupo) {
        $cantidadRegistros = count($grupo["registros"]);
        $textoRegistros = $cantidadRegistros == 1 ? "1 registro encontrado" : $cantidadRegistros." registros encontrados";
        $registrosHtml = "";

        $planGrupos = $grupo["plan_grupos"];
        uasort($planGrupos, function($a, $b) {
            if ($a["orden"] == $b["orden"]) {
                return strcmp($a["titulo"], $b["titulo"]);
            }
            return ($a["orden"] < $b["orden"]) ? -1 : 1;
        });
        foreach ($planGrupos as $planGrupo) {
            usort($planGrupo["registros"], function($a, $b) {
                if ($a["orden"] == $b["orden"]) {
                    if ($a["indice"] == $b["indice"]) {
                        return 0;
                    }
                    return ($a["indice"] < $b["indice"]) ? -1 : 1;
                }
                return ($a["orden"] < $b["orden"]) ? -1 : 1;
            });
            $ventasPlanTexto = $planGrupo["ventas"] == 1 ? "1 venta" : $planGrupo["ventas"]." ventas";
            $tratamientosPlanTexto = $planGrupo["tratamientos"] == 1 ? "1 tratamiento" : $planGrupo["tratamientos"]." tratamientos";
            $registrosPlanHtml = "";
            $esGrupoPlanMadre = $planGrupo["clase"] == "asignado";
            $registroBasePlan = ($esGrupoPlanMadre && isset($planGrupo["base"]) && is_array($planGrupo["base"])) ? $planGrupo["base"] : null;
            if ($esGrupoPlanMadre) {
                $registrosBaseHtml = "";
                $registrosAnexadosHtml = "";
                if ($registroBasePlan !== null) {
                    $tratamientosBaseTexto = ((int)$registroBasePlan["tratamientos_count"] == 1) ? "1 tratamiento" : ((int)$registroBasePlan["tratamientos_count"])." tratamientos";
                    $registrosBaseHtml = "
            <div class='clinical-plan-subgroup-label clinical-plan-subgroup-label--base'>
              <strong>Tratamientos de la venta base</strong>
              <span>".$registroBasePlan["titulo"]." &middot; ".$tratamientosBaseTexto."</span>
            </div>
            ".$registroBasePlan["html"];
                }
                foreach ($planGrupo["registros"] as $registro) {
                    if (!empty($registro["es_base"])) {
                        continue;
                    }
                    $registrosAnexadosHtml .= $registro["html"];
                }
                $registrosPlanHtml = $registrosBaseHtml;
                if (trim($registrosAnexadosHtml) != "") {
                    $registrosPlanHtml .= "
            <div class='clinical-plan-subgroup-label clinical-plan-subgroup-label--anexadas'>
              <strong>Ventas anexadas</strong>
              <span>Tratamientos agregados desde otras ventas</span>
            </div>
            ".$registrosAnexadosHtml;
                }
                if (trim($registrosPlanHtml) == "") {
                    $registrosPlanHtml = "<div class='clinical-plan-empty-children'>Sin ventas anexadas adicionales.</div>";
                }
            } else {
                foreach ($planGrupo["registros"] as $registro) {
                    $registrosPlanHtml .= $registro["html"];
                }
            }

            $planHeaderHtml = "";
            if ($esGrupoPlanMadre) {
                $registroActivoPlan = $registroBasePlan;
                if ($registroActivoPlan === null && isset($planGrupo["registros"][0])) {
                    $registroActivoPlan = $planGrupo["registros"][0];
                }
                $ventaMadreTitulo = $registroBasePlan !== null ? $registroBasePlan["titulo"] : "Venta base #".$planGrupo["venta_base_id"];
                if (trim((string)$planGrupo["venta_base_id"]) == "" && $registroBasePlan === null) {
                    $ventaMadreTitulo = "Venta base pendiente";
                }
                $tratamientosBasePlan = ($registroBasePlan !== null && isset($registroBasePlan["tratamientos_count"])) ? (int)$registroBasePlan["tratamientos_count"] : 0;
                $tratamientosAnexadosPlan = max(0, (int)$planGrupo["tratamientos"] - $tratamientosBasePlan);
                $detalleContenidoPlan = $planGrupo["subtitulo"];
                if ($tratamientosBasePlan > 0 || $tratamientosAnexadosPlan > 0) {
                    $detalleContenidoPlan = "Venta base: ".$tratamientosBasePlan." tratamientos &middot; Anexados: ".$tratamientosAnexadosPlan;
                }
                $datosPlanActivoHtml = "";
                $atributosPlanActivo = "";
                $clasePlanActivo = "";
                $accionPlanActivo = "<span class='clinical-plan-row-action'>Abrir plan madre</span>";
                if ($registroActivoPlan !== null) {
                    $datosPlanActivoHtml = $registroActivoPlan["datos_html"];
                    $atributosPlanActivo = " onclick='ObtenerdatosAbmConsulta(this)' onkeyup='if(event.keyCode==13||event.keyCode==32){ObtenerdatosAbmConsulta(this)}' role='button' tabindex='0' title='Abrir plan madre' aria-label='Abrir plan madre'";
                } else {
                    $clasePlanActivo = " clinical-plan-master-row--readonly";
                    $accionPlanActivo = "<span class='clinical-plan-row-action clinical-plan-row-action--muted'>Venta base no visible</span>";
                }
                $planHeaderHtml = "
            <div class='tarjeta-paciente clinical-plan-master-row".$clasePlanActivo."'".$atributosPlanActivo.">
              <span class='clinical-plan-tree-icon clinical-plan-tree-icon--madre' aria-hidden='true'>".$planGrupo["numero"]."</span>
              <span class='clinical-plan-master-row__kind'>Plan madre</span>
              <strong>".$ventaMadreTitulo."</strong>
              <span class='clinical-plan-master-row__beneficiary'>".$planGrupo["beneficiario"]."</span>
              <span class='clinical-plan-master-row__name'>".$detalleContenidoPlan."</span>
              <em>".$ventasPlanTexto." / ".$tratamientosPlanTexto."</em>
              ".$accionPlanActivo."
              ".$datosPlanActivoHtml."
            </div>";
            } else {
                $planHeaderHtml = "
            <div class='clinical-plan-section__header'>
              <span class='clinical-plan-section__badge' aria-hidden='true'>".$planGrupo["numero"]."</span>
              <div>
                <strong>".$planGrupo["titulo"]."</strong>
                <small>".$planGrupo["subtitulo"]."</small>
              </div>
              <em>".$ventasPlanTexto." / ".$tratamientosPlanTexto."</em>
            </div>";
            }
            $registrosHtml .= "
          <section class='clinical-plan-section clinical-plan-section--".$planGrupo["clase"]."'>
            ".$planHeaderHtml."
            <div class='clinical-records-grid'>".$registrosPlanHtml."</div>
          </section>";
        }

        $planDefinitivoResumenHtml = "";
        if (!empty($grupo["planes_madre"])) {
            $chipsPlanesMadre = "";
            foreach ($grupo["planes_madre"] as $planGrupo) {
                $chipsPlanesMadre .= "<span class='clinical-patient-plan-chip'><b>#".$planGrupo["numero"]."</b>".$planGrupo["apodo"]."</span>";
            }
            $planDefinitivoResumenHtml = "
              <div class='clinical-patient-plan-banner'>
                <span class='clinical-patient-plan-banner__icon' aria-hidden='true'>&#10003;</span>
                <div>
                  <strong>Planes madre asignados</strong>
                  <small>Ventas agrupadas por Apodo/beneficiario de esta c&eacute;dula.</small>
                  <div class='clinical-patient-plan-chips'>".$chipsPlanesMadre."</div>
                </div>
              </div>";
        } else {
            $planDefinitivoResumenHtml = "
              <div class='clinical-patient-plan-banner clinical-patient-plan-banner--pending'>
                <span class='clinical-patient-plan-banner__icon' aria-hidden='true'>!</span>
                <div>
                  <strong>Sin ventas asignadas a plan madre</strong>
                  <small>Las ventas pendientes pueden asignarse a un plan madre existente o crear uno nuevo.</small>
                </div>
              </div>";
        }
        $claseResumenPaciente = "clinical-patient-summary-card".(!empty($grupo["planes_madre"]) ? " clinical-patient-summary-card--has-plan" : "");

        $pagina .= "
        <section class='clinical-patient-result-group'>
          <div class='".$claseResumenPaciente."'>
            <div class='clinical-patient-summary-card__main'>
              <p>Paciente encontrado</p>
              <h3>".$grupo["paciente_html"]."</h3>
              <div class='clinical-patient-summary-card__meta'>
                <span><strong>CI / Documento</strong>".$grupo["ci_cliente"]."</span>
                <span><strong>Registros</strong>".$cantidadRegistros."</span>
              </div>
            </div>
            ".$planDefinitivoResumenHtml."
            <div class='clinical-patient-summary-card__stats'>
              <span><strong>".$grupo["pendientes"]."</strong><small>Pendientes</small></span>
              <span><strong>".$grupo["proceso"]."</strong><small>En proceso</small></span>
              <span><strong>".$grupo["completados"]."</strong><small>Completados</small></span>
            </div>
          </div>

          <div class='clinical-records-header'>
            <div>
              <p>".$textoRegistros." para ".$grupo["paciente_html"]."</p>
              <h3>Planes madre y ventas</h3>
            </div>
          </div>

          <div class='clinical-plan-hierarchy'>
            ".$registrosHtml."
          </div>
        </section>";
    }

    $informacion =array("1" => "exito","2" => $pagina);
    echo json_encode($informacion);	
    exit;

    foreach ($registros as $valor) {
        $num_factura= $valor['num_factura'];
        $ci_cliente= $valor['ci_cliente'];
        $paciente= $valor['paciente'];
        $cod_cliente= $valor['cod_cliente'];
        $cod_venta= $valor['cod_venta'];
        $apodo= $valor['apodo'];
        $nombre_local= $valor['nombre_local'];
        
        $porcentaje = $valor['porcentaje'];
        $totalporcentaje = $valor['totalporcentaje'];
        $totalporcentaje = $totalporcentaje * 100;

        if ($totalporcentaje > 0) {
            $resultadoPorcentaje = round(($porcentaje / $totalporcentaje) * 100);
        } else {
            $resultadoPorcentaje = 0; // Evitar división por cero
        }
        
        $descripcion= detalleTratamiento($cod_venta);

        if($apodo != ''){
            $paciente = $paciente." <b style='color:#8BC34A' >($apodo)</b>";
        }
        $color=" #e53935; ";	
        if($resultadoPorcentaje=="100"){
            $color=" #8bc34a; ";
        }			 
                $pagina .= "
        <div class='tarjeta-paciente' onclick='ObtenerdatosAbmConsulta(this)' onkeyup='if(event.keyCode==13||event.keyCode==32){ObtenerdatosAbmConsulta(this)}' role='button' tabindex='0' title='Abrir historial clínico' aria-label='Abrir historial clínico del paciente' style='
          position: relative; /* Necesario para posicionar el círculo */
          border: 1px solid #ddd;
          border-radius: 8px;
          margin: 10px 0;
          height: auto;
          padding: 15px;
          box-shadow: 0 2px 6px rgba(0,0,0,0.1);
          font-family: Arial, sans-serif;
        '>
          <!-- Círculo del porcentaje -->
          <div style='
            position: absolute;
            top: 5px;
            right: 5px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: $color /* Rojo */
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
          '>
            $resultadoPorcentaje %
          </div>
        
          <h3 style='
            margin-top:0;
            margin-bottom:10px;
            font-size: 16px;
            color: #333;
          '>Paciente</h3>
          
          <p><strong>Nombre:</strong> $paciente</p>
          <p><strong>CI:</strong> $ci_cliente</p>
          <p><strong>Código venta:</strong> $num_factura</p>
          <p><strong>Local:</strong> $nombre_local</p>
        
          <div style='
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
          '>
            <strong>Tratamientos:</strong>
            $descripcion
          </div>
          
          <!-- Datos ocultos -->
          <div style='display:none;'>
            <span id='td_datos_1'>$paciente</span>
            <span id='td_datos_2'>$ci_cliente</span>
            <span id='td_datos_3'>$num_factura</span>
            <span id='td_datos_4'></span> 
            <span id='td_datos_5'>$cod_venta</span> 
            <span id='td_datos_6'>$cod_cliente</span> 
            <span id='td_datos_7'>$apodo</span> 
          </div>
        </div>";
    }

    $informacion =array("1" => "exito","2" => $pagina);
    echo json_encode($informacion);	
    exit;
}
 
function detalleTratamiento($buscar) {
    $mysqli = conectar_al_servidor();

    $sql = "SELECT pr.cod_producto, pr.nombre_producto, dtv.estado, dtv.progreso_porcentaje 
            FROM producto pr 
            INNER JOIN detalle_venta dtv ON dtv.cod_productoFK = pr.cod_producto
            WHERE dtv.cod_ventaFK = '$buscar'";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt->execute()) {
        telar_trigger_error('Query error: '.$stmt->error, E_USER_ERROR);
        exit;
    }

    $result = $stmt->get_result();
    $valor = mysqli_num_rows($result);

    $html = "<ul style='list-style-type:none; padding:0; margin:0;'>";

    if ($valor > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $nombre_producto = mb_convert_encoding((string)($row['nombre_producto']), 'UTF-8', 'ISO-8859-1');
            $estado = mb_convert_encoding((string)($row['estado']), 'UTF-8', 'ISO-8859-1');
            $progreso_porcentaje = mb_convert_encoding((string)($row['progreso_porcentaje']), 'UTF-8', 'ISO-8859-1');
            $progreso_porcentaje = max(0, min(100, (int)$progreso_porcentaje));
            $nombre_producto_html = htmlspecialchars($nombre_producto, ENT_QUOTES, 'UTF-8');
            
            $html .= "
            <li style='
                background:linear-gradient(90deg, rgba(76,175,80,.28) 0%, rgba(76,175,80,.28) ".$progreso_porcentaje."%, #f2f2f2 ".$progreso_porcentaje."%, #f2f2f2 100%);
                margin-bottom:4px;
                padding:5px 10px;
                border-radius:4px;
                font-size:13px;
                border:1px solid #e0e0e0;"
                .($estado == 'eliminado' ? 'text-decoration: line-through;' : '').
            "'>
            ".$nombre_producto_html." <span style='float:right;font-weight:bold;'>".$progreso_porcentaje."%</span>
            </li>";
        }
    } else {
        $html .= "<li style='color:#999'>Sin tratamientos registrados</li>";
    }
    $html .= "</ul>";

    return $html;
}

function historialConsulta($fecha1,$fecha2,$fechafiltro,$documento,$paciente,$especialista,$local,$selectespecialista)
{
$mysqli=conectar_al_servidor();

$condicionfechas = '';
if($fecha1!=''){
	$condicionfechas = " and fecha between '$fecha1' and '$fecha2'";
}

$condicionfechafiltro = '';
if($fechafiltro!=''){
	$condicionfechafiltro = " and fecha = '$fechafiltro'";
}

$condicionLocal="";
if($local!=""){
$condicionLocal="and (SELECT cod_local FROM local WHERE cod_local = (SELECT cod_local FROM venta WHERE cod_venta = cod_ventaFK)) = '".$local."' ";
}

$condicionpaciente="";
if($paciente!=""){
$condicionpaciente=" and (select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$paciente."%' ";
}

$condicionespecialista="";
if($especialista!=""){
$condicionespecialista=" and (select nombre_persona from persona where cod_persona=cod_usuarioFK) like '%".$especialista."%' ";
}

$condicionselectespecialista="";
if($selectespecialista!=""){
$condicionselectespecialista=" and cod_usuarioFK = '".$selectespecialista."' ";
}

$condiciondocumento="";
if($documento!=""){
$condiciondocumento=" and (select ci_cliente from cliente where cod_cliente=cod_clienteFK) = '$documento' ";
}

$sql= "SELECT cod_consulta, cod_ventaFK, fecha, cod_usuarioFK, cod_agendamientoFK, estado, trabajo_realizado,
proximo_trabajo, motivoconsulta, diagnostico, cod_clienteFK,
(SELECT nombre FROM local WHERE cod_local = (SELECT cod_local FROM venta WHERE cod_venta = cod_ventaFK)) as local,
(select nombre_persona from persona where cod_persona=cod_usuarioFK) as especialista,
(select nombre_persona from persona where cod_persona=cod_clienteFK) as cliente,
(select ci_cliente from cliente where cod_cliente=cod_clienteFK) as ci
FROM consulta where estado= 'Activo' ".$condicionLocal.$condicionpaciente.$condicionespecialista.$condicionfechas.$condicionfechafiltro.$condiciondocumento.$condicionselectespecialista." order by cod_consulta desc limit 100 "; 	

// echo $sql;
// exit;


$stmt = $mysqli->prepare($sql);
$pagina = "";   
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
 $styleName="tableRegistroSearch";
$escapeHistorialConsulta = function($texto) {
	return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
};
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$cod_consulta = mb_convert_encoding((string)($valor['cod_consulta']), 'UTF-8', 'ISO-8859-1');
$cod_ventaFK = mb_convert_encoding((string)($valor['cod_ventaFK']), 'UTF-8', 'ISO-8859-1');
$fecha = mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
$cod_usuarioFK = mb_convert_encoding((string)($valor['cod_usuarioFK']), 'UTF-8', 'ISO-8859-1');
$cod_agendamientoFK = mb_convert_encoding((string)($valor['cod_agendamientoFK']), 'UTF-8', 'ISO-8859-1');
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
$trabajo_realizado = mb_convert_encoding((string)($valor['trabajo_realizado']), 'UTF-8', 'ISO-8859-1');
$proximo_trabajo = mb_convert_encoding((string)($valor['proximo_trabajo']), 'UTF-8', 'ISO-8859-1');
$motivoconsulta = mb_convert_encoding((string)($valor['motivoconsulta']), 'UTF-8', 'ISO-8859-1');
$diagnostico = mb_convert_encoding((string)($valor['diagnostico']), 'UTF-8', 'ISO-8859-1');
$cod_clienteFK = mb_convert_encoding((string)($valor['cod_clienteFK']), 'UTF-8', 'ISO-8859-1');
$local = mb_convert_encoding((string)($valor['local']), 'UTF-8', 'ISO-8859-1');
$especialista = mb_convert_encoding((string)($valor['especialista']), 'UTF-8', 'ISO-8859-1');
$cliente = mb_convert_encoding((string)($valor['cliente']), 'UTF-8', 'ISO-8859-1');
$ci = mb_convert_encoding((string)($valor['ci']), 'UTF-8', 'ISO-8859-1');

$especialistaNombre = trim($especialista);
$especialistaCargo = "";
if (preg_match('/\(([^)]*)\)\s*$/u', $especialista, $coincidenciaCargo)) {
	$especialistaCargo = trim($coincidenciaCargo[1]);
	$especialistaNombre = trim(preg_replace('/\s*\([^)]*\)\s*$/u', '', $especialista));
}
if ($especialistaNombre == "") {
	$especialistaNombre = "Sin especialista";
}

$nombreParaIniciales = trim(preg_replace('/\s+/', ' ', $especialistaNombre));
$partesNombre = $nombreParaIniciales != "" ? preg_split('/\s+/', $nombreParaIniciales) : array("Sin", "Especialista");
$primeraInicial = mb_substr($partesNombre[0], 0, 1, 'UTF-8');
$ultimaInicial = count($partesNombre) > 1 ? mb_substr($partesNombre[count($partesNombre) - 1], 0, 1, 'UTF-8') : "";
$iniciales = mb_strtoupper($primeraInicial.$ultimaInicial, 'UTF-8');
if ($iniciales == "") {
	$iniciales = "SE";
}

$fechaMostrar = trim($fecha) != "" ? $fecha : "Sin fecha";
$ciMostrar = trim($ci) != "" ? $ci : "Sin CI";
$clienteMostrar = trim($cliente) != "" ? $cliente : "Sin paciente";
$motivoMostrar = trim($motivoconsulta) != "" ? $motivoconsulta : "Sin motivo";
$zonaMostrar = trim($diagnostico) != "" ? $diagnostico : "Sin dato";
$trabajoMostrar = trim($trabajo_realizado) != "" ? $trabajo_realizado : "Sin dato";
$proximoMostrar = trim($proximo_trabajo) != "" ? $proximo_trabajo : "Sin dato";
$localMostrar = trim($local) != "" ? $local : "Sin local";
$cargoMostrar = trim($especialistaCargo) != "" ? $especialistaCargo : "Especialista";
$incompleta = (trim($motivoconsulta) == "" || trim($diagnostico) == "" || trim($trabajo_realizado) == "" || trim($proximo_trabajo) == "") ? "1" : "0";

$pagina.="
<article class='consulta-audit-row' data-especialista='".$escapeHistorialConsulta($especialistaNombre)."' data-incompleta='".$incompleta."'>
<div class='consulta-audit-cell consulta-audit-date' title='".$escapeHistorialConsulta($fechaMostrar)."'>".$escapeHistorialConsulta($fechaMostrar)."</div>
<div class='consulta-audit-cell consulta-audit-ci' title='".$escapeHistorialConsulta($ciMostrar)."'>".$escapeHistorialConsulta($ciMostrar)."</div>
<div class='consulta-audit-cell consulta-audit-patient' title='".$escapeHistorialConsulta($clienteMostrar)."'><strong>".$escapeHistorialConsulta($clienteMostrar)."</strong></div>
<div class='consulta-audit-cell consulta-audit-specialist' title='".$escapeHistorialConsulta($especialistaNombre." - ".$cargoMostrar)."'>
<span class='consulta-audit-avatar'>".$escapeHistorialConsulta($iniciales)."</span>
<span class='consulta-audit-specialist-info'><strong>".$escapeHistorialConsulta($especialistaNombre)."</strong><span class='consulta-audit-badge consulta-audit-badge--role'>".$escapeHistorialConsulta($cargoMostrar)."</span></span>
</div>
<div class='consulta-audit-cell consulta-audit-reason'><span class='consulta-audit-badge consulta-audit-badge--reason' title='".$escapeHistorialConsulta($motivoMostrar)."'>".$escapeHistorialConsulta($motivoMostrar)."</span></div>
<div class='consulta-audit-cell consulta-audit-zone consulta-audit-ellipsis' title='".$escapeHistorialConsulta($zonaMostrar)."'>".$escapeHistorialConsulta($zonaMostrar)."</div>
<div class='consulta-audit-cell consulta-audit-work consulta-audit-ellipsis' title='".$escapeHistorialConsulta($trabajoMostrar)."'>".$escapeHistorialConsulta($trabajoMostrar)."</div>
<div class='consulta-audit-cell consulta-audit-next consulta-audit-ellipsis' title='".$escapeHistorialConsulta($proximoMostrar)."'>".$escapeHistorialConsulta($proximoMostrar)."</div>
<div class='consulta-audit-cell consulta-audit-local'><span class='consulta-audit-badge consulta-audit-badge--local' title='".$escapeHistorialConsulta($localMostrar)."'>".$escapeHistorialConsulta($localMostrar)."</span></div>
</article>";


}
} else {
	$pagina = "<div class='consulta-audit-empty'>No se encontraron consultas con los filtros seleccionados.</div>";
}


$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'));
echo json_encode($informacion);	
exit;
}




 
ObtenerDatos($operacion);
 
?>
