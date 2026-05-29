<?php
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include("classTable.php");

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
	guardarPorcentajeProgreso($id_detalle_tratamientoConsulta,$porcentaje,$cod_agendaFK);
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
	
	$cod_estecialista=$_POST['cod_estecialista'];
    $cod_estecialista = mb_convert_encoding((string)($cod_estecialista), 'ISO-8859-1', 'UTF-8'); 
	
	$cod_agendamiento=$_POST['cod_agendamiento'];
    $cod_agendamiento = mb_convert_encoding((string)($cod_agendamiento), 'ISO-8859-1', 'UTF-8'); 
	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8'); 
	
	$cod_clienteFK=$_POST['cod_clienteConsulta'];
    $cod_clienteFK = mb_convert_encoding((string)($cod_clienteFK), 'ISO-8859-1', 'UTF-8'); 
	
	$apodo=$_POST['apodo'];
    $apodo = mb_convert_encoding((string)($apodo), 'ISO-8859-1', 'UTF-8'); 
	
	abm($cod_consulta,$motivo,$diagnostico,$prxtrabajo,$trabajoreali,$fecha,$cod_estecialista,$cod_agendamiento,$cod_venta,$cod_clienteFK,$apodo,$operacion);
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

$sql= "SELECT 
		dtv.cod_detalle,
		dtv.descripcion,
		dtv.cantidad_detalle,
		dtv.progreso_porcentaje,
		dtv.estado,
		pr.nombre_producto,
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
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
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
			<span>Progreso: ".htmlspecialchars($progreso_porcentaje, ENT_QUOTES, 'UTF-8')."%</span>
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
	echo trigger_error('The query execution failed; MySQL said ('.$stmtVerificar->errno.') '.$stmtVerificar->error, E_USER_ERROR);
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
	echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
	exit;
}

$informacion = array("1" => "exito");
mysqli_close($mysqli);
echo json_encode($informacion);
exit;
}

function  verEvolucion($cod_venta)
{
$mysqli=conectar_al_servidor();

$sql= "SELECT nro,(select nombre_persona from persona where cod_persona=cod_usuraioFK) as usuario, fecha FROM evoluciontratamiento WHERE cod_detalle_venta = '$cod_venta'";

 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";
$pagina="";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$nro = mb_convert_encoding((string)($valor['nro']), 'UTF-8', 'ISO-8859-1');   
$usuario = mb_convert_encoding((string)($valor['usuario']), 'UTF-8', 'ISO-8859-1');   
$fecha = mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');   
 
 
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<style>
.timeline {
  position: relative;
  margin: 2px 0;
  padding-left: 5px;
  border-left: 3px solid #4a90e2;
}
.timeline-item {
  position: relative;
  margin-bottom: 2px;
}
.timeline-item::before {
  content: '';
  position: absolute;
  left: -8px;
  top: 4px;
  width: 14px;
  height: 14px;
  background-color: #4a90e2;
  border-radius: 50%;
}
.timeline-content {
  background-color: #f9f9f9;
  padding: 5px 7px;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}
.timeline-content .description {
  font-weight: bold;
  margin-bottom: 2px;
}
.timeline-content .meta {
  font-size: 12px;
  color: #666;
  border-top: 1px solid #ddd;
  margin-top: 2px;
  padding-top: 2px;
}
</style>

<div class='timeline'>
  <div class='timeline-item'>
    <div class='timeline-content'>
      <div class='description'>
         ".htmlspecialchars($nro)." %
      </div>
      <div class='meta'>
       ".htmlspecialchars($usuario)." - ".htmlspecialchars($fecha)."
      </div>
    </div>
  </div>
 
</div>

"; 
 
}
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
        echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
        exit;
    }
 
    $informacion = array("1" => "exito");
    mysqli_close($mysqli);
    echo json_encode($informacion);
    exit;
}


function guardarPorcentajeProgreso($id_detalle_tratamientoConsulta,$porcentaje,$cod_agendaFK)
{
     $mysqli = conectar_al_servidor();
 
	$consulta1 = "UPDATE detalle_venta SET progreso_porcentaje = '$porcentaje' WHERE cod_detalle = '$id_detalle_tratamientoConsulta'";

    $stmt1 = $mysqli->prepare($consulta1);
    

    if (!$stmt1->execute()) {
        echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
        exit;
    }
	
	$user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
	
	$consulta1 = "insert into evoluciontratamiento (cod_detalle_venta,cod_usuraioFK,nro,fecha,cod_agendaFK)VALUES('$id_detalle_tratamientoConsulta',$user,'$porcentaje',now(),'$cod_agendaFK') ";

    $stmt1 = $mysqli->prepare($consulta1);
    

    if (!$stmt1->execute()) {
        echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
        exit;
    }
		
 
    $informacion = array("1" => "exito");
    mysqli_close($mysqli);
    echo json_encode($informacion);
    exit;
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
 

function abm($cod_consulta,$motivo,$diagnostico,$prxtrabajo,$trabajoreali,$fecha,$cod_estecialista,$cod_agendamiento,$cod_venta,$cod_clienteFK,$apodo,$operacion)
{
    if ($trabajoreali == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);    
        exit;
    }

    $mysqli = conectar_al_servidor();

    if ($operacion == "nuevo") {
        $consulta1 = "INSERT INTO consulta (
            cod_ventaFK, fecha, cod_usuarioFK, cod_agendamientoFK, estado,
            trabajo_realizado, proximo_trabajo, motivoconsulta, diagnostico,cod_clienteFK) VALUES (?, ?, ?, ?, 'Activo', ?, ?, ?, ?,?)";

        $stmt1 = $mysqli->prepare($consulta1);
        $ss = 'sssssssss';
        $stmt1->bind_param($ss, $cod_venta, $fecha, $cod_estecialista, $cod_agendamiento, $trabajoreali, $prxtrabajo, $motivo, $diagnostico,$cod_clienteFK);
    }

    if ($operacion == "editar") {
        $consulta1 = "UPDATE consulta SET
            cod_ventaFK = ?, fecha = ?, cod_usuarioFK = ?, cod_agendamientoFK = ?, 
            trabajo_realizado = ?, proximo_trabajo = ?, motivoconsulta = ?, diagnostico = ?,cod_clienteFK = ?
            WHERE cod_consulta = ?";
        
        $stmt1 = $mysqli->prepare($consulta1);
        $ss = 'ssssssssss';
        $stmt1->bind_param($ss, $cod_venta, $fecha, $cod_estecialista, $cod_agendamiento, 
                                $trabajoreali, $prxtrabajo, $motivo, $diagnostico, $cod_clienteFK, $cod_consulta);
    }

    if (!$stmt1->execute()) {
        echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
        exit;
    }



	$consulta1 = "UPDATE venta SET apodo = '$apodo' WHERE cod_venta = '$cod_venta'";

    $stmt1 = $mysqli->prepare($consulta1);
    

    if (!$stmt1->execute()) {
        echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
        exit;
    }
	
	
    // Obtener el ID insertado si es nuevo
    if ($operacion == "nuevo") {
        $cod_consulta = $mysqli->insert_id;
        $informacion = array("1" => "exito", "2" => $cod_consulta);
    } else {
        $informacion = array("1" => "exito", "2" => $cod_consulta);
    }

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
        echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
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
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";
$pagina="";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$descripcion = mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');   
$usuario = mb_convert_encoding((string)($valor['usuario']), 'UTF-8', 'ISO-8859-1');   
$fecha_hora = mb_convert_encoding((string)($valor['fecha_hora']), 'UTF-8', 'ISO-8859-1');   
 
 
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<style>
.timeline {
  position: relative;
  margin: 2px 0;
  padding-left: 5px;
  border-left: 3px solid #4a90e2;
}
.timeline-item {
  position: relative;
  margin-bottom: 2px;
}
.timeline-item::before {
  content: '';
  position: absolute;
  left: -8px;
  top: 4px;
  width: 14px;
  height: 14px;
  background-color: #4a90e2;
  border-radius: 50%;
}
.timeline-content {
  background-color: #f9f9f9;
  padding: 5px 7px;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}
.timeline-content .description {
  font-weight: bold;
  margin-bottom: 2px;
}
.timeline-content .meta {
  font-size: 12px;
  color: #666;
  border-top: 1px solid #ddd;
  margin-top: 2px;
  padding-top: 2px;
}
</style>

<div class='timeline'>
  <div class='timeline-item'>
    <div class='timeline-content'>
      <div class='description'>
         ".htmlspecialchars($descripcion)."
      </div>
      <div class='meta'>
       ".htmlspecialchars($usuario)." - ".htmlspecialchars($fecha_hora)."
      </div>
    </div>
  </div>
 
</div>

"; 
 
}
}
 
$informacion =array("1" => "exito","2" => $pagina );
echo json_encode($informacion);	
exit;
}



function  buscarHistorialConsulta($cod_venta)
{
$mysqli=conectar_al_servidor();

$sql= "select cod_consulta ,cod_ventaFK ,fecha ,cod_usuarioFK ,cod_agendamientoFK ,estado ,trabajo_realizado ,proximo_trabajo,motivoconsulta,diagnostico,(select nombre_persona from persona where cod_persona=cod_usuarioFK) as especialista
 from  consulta  
where  cod_ventaFK='$cod_venta' order by cod_consulta desc";

 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";
$pagina="";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$cod_consulta = mb_convert_encoding((string)($valor['cod_consulta']), 'UTF-8', 'ISO-8859-1');   
$fecha = mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');          
$trabajo_realizado = mb_convert_encoding((string)($valor['trabajo_realizado']), 'UTF-8', 'ISO-8859-1');          
$proximo_trabajo = mb_convert_encoding((string)($valor['proximo_trabajo']), 'UTF-8', 'ISO-8859-1');  
$motivoconsulta = mb_convert_encoding((string)($valor['motivoconsulta']), 'UTF-8', 'ISO-8859-1');  
$diagnostico = mb_convert_encoding((string)($valor['diagnostico']), 'UTF-8', 'ISO-8859-1');  
$especialista = mb_convert_encoding((string)($valor['especialista']), 'UTF-8', 'ISO-8859-1');  
 
$pagina .= "
<div 
 onclick='abrirModal(this)'  
  role='button' tabindex='0'
  aria-label='Ver consulta número $cod_consulta' 
  class='tarjeta-consulta consulta-item'
  data-codconsulta='$cod_consulta'
  data-fecha='$fecha'
  data-especialista='$especialista'
  data-trabajo='$trabajo_realizado'
  data-proximo='$proximo_trabajo'
  data-motivo='$motivoconsulta'
  data-diagnostico='$diagnostico'
>
  <span class='fecha'>$fecha</span>
  <div class='consulta-header'>
    <h3 style='display:none;'>Consulta Nº $cod_consulta</h3>
  </div>
  <div class='consulta-body'>
    <p><strong>Doc.:</strong> $especialista</p>
    <p><strong>Tr R.:</strong> $trabajo_realizado</p>
    <p><strong>Prx Tr:</strong> $proximo_trabajo</p>
  </div>
</div>
";
 
}
}
 
$informacion =array("1" => "exito","2" => $pagina );
echo json_encode($informacion);	
exit;
}

 
function  buscarDetalleCompradoConsulta($cod_venta)
{
$mysqli=conectar_al_servidor();

$sql= "select dtv.descripcion , pr.cod_producto,dtv.cantidad_detalle,pr.nombre_producto,dtv.cod_detalle ,estado_tratamiento,progreso_porcentaje, dtv.estado
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where dtv.cod_ventaFK='$cod_venta'";

 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";
$pagina="";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$descripcion = mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');   
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$cod_detalle = mb_convert_encoding((string)($valor['cod_detalle']), 'UTF-8', 'ISO-8859-1');          
$cantidad_detalle = mb_convert_encoding((string)($valor['cantidad_detalle']), 'UTF-8', 'ISO-8859-1');  
$estado_tratamiento = mb_convert_encoding((string)($valor['estado_tratamiento']), 'UTF-8', 'ISO-8859-1'); 
$progreso_porcentaje = mb_convert_encoding((string)($valor['progreso_porcentaje']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 

$Style='';
$estadoClase = 'pendiente';
$estadoTexto = 'Pendiente';
$progreso_numero = max(0, min(100, (int)$progreso_porcentaje));
if ($estado == "eliminado" || $estado == "anulado" || $estado == "cancelado") {
	$estadoClase = 'cancelado';
	$estadoTexto = 'Anulado';
} elseif ($progreso_numero >= 100) {
	$estadoClase = 'completado';
	$estadoTexto = 'Completado';
} elseif ($progreso_numero > 0) {
	$estadoClase = 'proceso';
	$estadoTexto = 'En proceso';
}

// $descripcionDetalleVenta=buscardescripcionDetalleVenta($cod_detalle);
 $styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName consulta-treatment-row consulta-treatment-row--$estadoClase' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatostrConsultaTratamiento(this)' $Style> 
<td class='consulta-treatment-row__qty' style='width:5%;text-aling:center'>".number_format($cantidad_detalle,'0',',','.')."</td>
<td class='consulta-treatment-row__name' style='width:60%'><strong>$nombre_producto</strong><span>$descripcion</span></td> 
<td class='consulta-treatment-row__progress' style='width:25%;text-align: center;'>
<span class='consulta-treatment-status consulta-treatment-status--$estadoClase'>$estadoTexto</span>
<span class='consulta-treatment-percent'>$progreso_numero%</span>
</td> 
<td id='td_datos_1' style='Display:none'>$progreso_porcentaje </td> 
<td id='td_id_1' style='display:none'> $cod_detalle </td> 
</tr>
</table>";
 
}
}
  
$informacion =array("1" => "exito","2" => $pagina );
echo json_encode($informacion);	
exit;
}

function buscarConsulta($Paciente,$local,$num_factura) {
    $mysqli=conectar_al_servidor();
	$pagina='';
	 
    $sqlFiltro= "";
	if($local!=""){
		$sqlFiltro=" and  cod_local='".$local."' ";
	}
	
	if($Paciente!=""){
		$sqlFiltro=" and  concat(cl.ci_cliente,' ',cl.rut_cliente ,' ',p.nombre_persona )   like '%".$Paciente."%' ";
	}

    if($num_factura!=""){
		$sqlFiltro=" and num_factura like '%".$num_factura."%' ";
	}
	
    $sql= "Select  nombre_persona as paciente,cl.ci_cliente,cl.cod_cliente,num_factura,cod_venta,apodo , 
    (select sum(progreso_porcentaje) from detalle_venta where cod_ventaFK=cod_venta) as porcentaje , 
    (select Nombre from local where cod_local=vt.cod_local) as nombre_local , 
    (select count(*) from detalle_venta where cod_ventaFK=cod_venta) as totalporcentaje
    from venta vt inner join cliente cl on cod_clienteFK=cod_cliente
    inner join persona p on cod_cliente=cod_persona
    where cl.estado = 'Activo' and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0".$sqlFiltro." limit 100;";
  
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
                "nombre_local" => $nombre_local,
                "porcentaje" => $porcentaje,
                "totalporcentaje" => $totalporcentaje,
            );
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

function detalleTratamientoDatos($buscar) {
    $mysqli = conectar_al_servidor();

    $sql = "SELECT pr.cod_producto, pr.nombre_producto, dtv.estado, dtv.progreso_porcentaje 
            FROM producto pr 
            INNER JOIN detalle_venta dtv ON dtv.cod_productoFK = pr.cod_producto
            WHERE dtv.cod_ventaFK = '$buscar'";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt->execute()) {
        trigger_error('Query error: '.$stmt->error, E_USER_ERROR);
        exit;
    }

    $result = $stmt->get_result();
    $valor = mysqli_num_rows($result);
    $pendientes = 0;
    $proceso = 0;
    $completados = 0;
    $cancelados = 0;
    $html = "<ul class='clinical-treatment-list'>";

    if ($valor > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $nombre_producto = mb_convert_encoding((string)($row['nombre_producto']), 'UTF-8', 'ISO-8859-1');
            $estado = mb_convert_encoding((string)($row['estado']), 'UTF-8', 'ISO-8859-1');
            $progreso_porcentaje = mb_convert_encoding((string)($row['progreso_porcentaje']), 'UTF-8', 'ISO-8859-1');
            $progreso_porcentaje = max(0, min(100, (int)$progreso_porcentaje));
            $nombre_producto_html = htmlspecialchars($nombre_producto, ENT_QUOTES, 'UTF-8');
            $estado_normalizado = strtolower(trim($estado));
            $estado_clase = "pendiente";
            $estado_texto = "Pendiente";

            if ($estado_normalizado == "eliminado" || $estado_normalizado == "anulado" || $estado_normalizado == "cancelado") {
                $estado_clase = "cancelado";
                $estado_texto = "Anulado";
                $cancelados++;
            } elseif ($progreso_porcentaje >= 100) {
                $estado_clase = "completado";
                $estado_texto = "Completado";
                $completados++;
            } elseif ($progreso_porcentaje > 0) {
                $estado_clase = "proceso";
                $estado_texto = "En proceso";
                $proceso++;
            } else {
                $pendientes++;
            }

            $html .= "
            <li class='clinical-treatment-item clinical-treatment-item--".$estado_clase."'>
                <span class='clinical-treatment-name'>".$nombre_producto_html."</span>
                <span class='clinical-treatment-status'>".$estado_texto."</span>
                <span class='clinical-treatment-progress'>".$progreso_porcentaje."%</span>
            </li>";
        }
    } else {
        $html .= "<li class='clinical-treatment-empty'>Sin tratamientos registrados</li>";
    }
    $html .= "</ul>";

    return array(
        "html" => $html,
        "pendientes" => $pendientes,
        "proceso" => $proceso,
        "completados" => $completados,
        "cancelados" => $cancelados
    );
}

function buscarVistaConsulta($Paciente,$local,$num_factura) {
    $registros = buscarConsulta($Paciente,$local,$num_factura);
    
	$pagina='';
    $gruposPaciente = array();
    $ordenEstado = array("proceso" => 1, "pendiente" => 2, "completado" => 3, "cancelado" => 4);

    foreach ($registros as $indiceRegistro => $valor) {
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

        $detalleTratamiento = detalleTratamientoDatos($cod_venta);
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
                "registros" => array()
            );
        }

        $gruposPaciente[$claveGrupo]["pendientes"] += $detalleTratamiento["pendientes"];
        $gruposPaciente[$claveGrupo]["proceso"] += $detalleTratamiento["proceso"];
        $gruposPaciente[$claveGrupo]["completados"] += $detalleTratamiento["completados"];

        $numFacturaHtml = htmlspecialchars($num_factura, ENT_QUOTES, 'UTF-8');
        $codVentaHtml = htmlspecialchars($cod_venta, ENT_QUOTES, 'UTF-8');
        $localHtml = htmlspecialchars($nombre_local, ENT_QUOTES, 'UTF-8');
        $ciHidden = htmlspecialchars($ci_cliente, ENT_QUOTES, 'UTF-8');
        $tituloRegistro = $num_factura != "" ? "Venta #".$numFacturaHtml : "C&oacute;digo venta #".$codVentaHtml;
        $codigoVisible = $num_factura != "" ? $numFacturaHtml : $codVentaHtml;
        $apodoActualHtml = htmlspecialchars($apodo, ENT_QUOTES, 'UTF-8');
        $apodoBadgeHtml = trim($apodo) != "" ? "<span class='clinical-card__nickname'>Apodo: ".$apodoActualHtml."</span>" : "";
        $pacienteOcultoRegistro = htmlspecialchars($paciente.($apodo != "" ? " (".$apodo.")" : ""), ENT_QUOTES, 'UTF-8');

        $gruposPaciente[$claveGrupo]["registros"][] = array(
            "orden" => $ordenEstado[$estadoClase],
            "indice" => $indiceRegistro,
            "html" => "
        <div class='tarjeta-paciente clinical-record-card clinical-record-card--".$estadoClase."' onclick='ObtenerdatosAbmConsulta(this)' onkeyup='if(event.keyCode==13||event.keyCode==32){ObtenerdatosAbmConsulta(this)}' role='button' tabindex='0' title='Abrir historial cl&iacute;nico' aria-label='Abrir historial cl&iacute;nico del paciente'>
          <div class='clinical-record-card__header'>
            <div>
              <span class='clinical-record-card__eyebrow'>Registro cl&iacute;nico</span>
              <div class='clinical-record-card__title-row'>
                <h3>".$tituloRegistro."</h3>
                ".$apodoBadgeHtml."
              </div>
            </div>
            <div class='clinical-progress-badge clinical-progress-badge--".$estadoClase."'>
              <strong>".$resultadoPorcentaje."%</strong>
              <span>".$estadoTexto."</span>
            </div>
          </div>

          <div class='clinical-record-meta'>
            <span><strong>C&oacute;digo venta</strong>".$codigoVisible."</span>
            <span><strong>Local</strong>".$localHtml."</span>
          </div>

          <div class='clinical-treatment-block'>
            <strong>Tratamientos</strong>
            ".$detalleTratamiento["html"]."
          </div>

          <span class='clinical-record-action'>Ver evoluci&oacute;n</span>

          <div style='display:none;'>
            <span id='td_datos_1'>".$pacienteOcultoRegistro."</span>
            <span id='td_datos_2'>".$ciHidden."</span>
            <span id='td_datos_3'>".$numFacturaHtml."</span>
            <span id='td_datos_4'></span> 
            <span id='td_datos_5'>".$codVentaHtml."</span> 
            <span id='td_datos_6'>".htmlspecialchars($cod_cliente, ENT_QUOTES, 'UTF-8')."</span> 
            <span id='td_datos_7'>".$apodoActualHtml."</span> 
          </div>
        </div>"
        );
    }

    foreach ($gruposPaciente as $grupo) {
        usort($grupo["registros"], function($a, $b) {
            if ($a["orden"] == $b["orden"]) {
                if ($a["indice"] == $b["indice"]) {
                    return 0;
                }
                return ($a["indice"] < $b["indice"]) ? -1 : 1;
            }
            return ($a["orden"] < $b["orden"]) ? -1 : 1;
        });

        $cantidadRegistros = count($grupo["registros"]);
        $textoRegistros = $cantidadRegistros == 1 ? "1 registro encontrado" : $cantidadRegistros." registros encontrados";
        $registrosHtml = "";

        foreach ($grupo["registros"] as $registro) {
            $registrosHtml .= $registro["html"];
        }

        $pagina .= "
        <section class='clinical-patient-result-group'>
          <div class='clinical-patient-summary-card'>
            <div class='clinical-patient-summary-card__main'>
              <p>Paciente encontrado</p>
              <h3>".$grupo["paciente_html"]."</h3>
              <div class='clinical-patient-summary-card__meta'>
                <span><strong>CI / Documento</strong>".$grupo["ci_cliente"]."</span>
                <span><strong>Registros</strong>".$cantidadRegistros."</span>
              </div>
            </div>
            <div class='clinical-patient-summary-card__stats'>
              <span><strong>".$grupo["pendientes"]."</strong><small>Pendientes</small></span>
              <span><strong>".$grupo["proceso"]."</strong><small>En proceso</small></span>
              <span><strong>".$grupo["completados"]."</strong><small>Completados</small></span>
            </div>
          </div>

          <div class='clinical-records-header'>
            <div>
              <p>".$textoRegistros." para ".$grupo["paciente_html"]."</p>
              <h3>Tratamientos y registros</h3>
            </div>
          </div>

          <div class='clinical-records-grid'>
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
        trigger_error('Query error: '.$stmt->error, E_USER_ERROR);
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
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
 $styleName="tableRegistroSearch";
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


$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' >
<td  id='' style='width:10%'>".$fecha."</td>
<td  id='' style='width:5%'>".$ci."</td>
<td  id='' style='width:10%'>".$cliente."</td>
<td  id='' style='width:10%'>".$especialista."</td>
<td  id='' style='width:15%'>".$motivoconsulta."</td>
<td  id='' style='width:15%'>".$diagnostico."</td>
<td  id='' style='width:15%'>".$trabajo_realizado."</td>
<td  id='' style='width:15%'>".$proximo_trabajo."</td>
<td  id='' style='width:5%'>".$local."</td>
</tr>
</table>";


}
}


$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'));
echo json_encode($informacion);	
exit;
}




 
ObtenerDatos($operacion);
 
?>
