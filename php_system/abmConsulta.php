<?php
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include("classTable.php");

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);
function ObtenerDatos($operacion)
{
	
   $user=$_POST['useru'];
    $user = utf8_decode($user);
	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = utf8_decode($navegador);
$resp=verificar_navegador($user,$navegador,$pass);
if($resp!="ok"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}

 


if($operacion=="buscarVistaConsulta")
{	 
	$Paciente=$_POST['Paciente'];
    $Paciente = utf8_decode($Paciente);
	$local=$_POST['local'];
    $local = utf8_decode($local);
	buscarVistaConsulta($Paciente,$local);
}	
 
if($operacion=="buscarDetalleCompradoConsulta")
{	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = utf8_decode($cod_venta); 
	buscarDetalleCompradoConsulta($cod_venta);
}	
 
if($operacion=="buscarHistorialConsulta")
{	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = utf8_decode($cod_venta); 
	buscarHistorialConsulta($cod_venta);
}

if($operacion=="guardarPorcentajeProgreso")
{	
	$id_detalle_tratamientoConsulta=$_POST['id_detalle_tratamientoConsulta'];
    $id_detalle_tratamientoConsulta = utf8_decode($id_detalle_tratamientoConsulta);
	$porcentaje=$_POST['porcentaje'];
    $porcentaje = utf8_decode($porcentaje); 
	guardarPorcentajeProgreso($id_detalle_tratamientoConsulta,$porcentaje);
}

	
if($operacion=="historialConsulta")
{	
	$fecha1=$_POST['fecha1'];
    $fecha1 = utf8_decode($fecha1);
	$fecha2=$_POST['fecha2'];
    $fecha2 = utf8_decode($fecha2);
	$fechafiltro=$_POST['fechafiltro'];
    $fechafiltro = utf8_decode($fechafiltro); 
	$documento=$_POST['documento'];
    $documento = utf8_decode($documento);
	$paciente=$_POST['paciente'];
    $paciente = utf8_decode($paciente);
	$especialista=$_POST['especialista'];
    $especialista = utf8_decode($especialista);
	$local=$_POST['local'];
    $local = utf8_decode($local);
	$selectespecialista=$_POST['selectespecialista'];
    $selectespecialista = utf8_decode($selectespecialista);


	historialConsulta($fecha1,$fecha2,$fechafiltro,$documento,$paciente,$especialista,$local,$selectespecialista);
}	


if($operacion=="nuevo" || $operacion=="editar" )
{	
	$cod_consulta=$_POST['cod_consulta'];
    $cod_consulta = utf8_decode($cod_consulta); 
	
	$motivo=$_POST['motivo'];
    $motivo = utf8_decode($motivo); 
	
	$diagnostico=$_POST['diagnostico'];
    $diagnostico = utf8_decode($diagnostico); 
	
	$prxtrabajo=$_POST['prxtrabajo'];
    $prxtrabajo = utf8_decode($prxtrabajo); 
	
	$trabajoreali=$_POST['trabajoreali'];
    $trabajoreali = utf8_decode($trabajoreali); 
	
	$fecha=$_POST['fecha'];
    $fecha = utf8_decode($fecha); 
	
	$cod_estecialista=$_POST['cod_estecialista'];
    $cod_estecialista = utf8_decode($cod_estecialista); 
	
	$cod_agendamiento=$_POST['cod_agendamiento'];
    $cod_agendamiento = utf8_decode($cod_agendamiento); 
	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = utf8_decode($cod_venta); 
	
	$cod_clienteFK=$_POST['cod_clienteConsulta'];
    $cod_clienteFK = utf8_decode($cod_clienteFK); 
	
	$apodo=$_POST['apodo'];
    $apodo = utf8_decode($apodo); 
	
	abm($cod_consulta,$motivo,$diagnostico,$prxtrabajo,$trabajoreali,$fecha,$cod_estecialista,$cod_agendamiento,$cod_venta,$cod_clienteFK,$apodo,$operacion);
}	



if($operacion=="agregar_observacion_consulta" )
{	
	$cod_cliente=$_POST['cod_clienteConsulta'];
    $cod_cliente = utf8_decode($cod_cliente); 
	
	$descripcion=$_POST['descripcion'];
    $descripcion = utf8_decode($descripcion); 
	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = utf8_decode($cod_venta); 
	
	$user=$_POST['useru'];
    $user = utf8_decode($user);
	
	agregar_observacion_consulta($cod_cliente,$descripcion,$cod_venta,$user);
}	

if($operacion=="buscar_observacion_consulta" )
{	
	$cod_cliente=$_POST['cod_clienteConsulta'];
    $cod_cliente = utf8_decode($cod_cliente); 
	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = utf8_decode($cod_venta); 
	
	
	
	buscar_observacion_consulta($cod_cliente,$cod_venta);
}	


if($operacion=="vercuotasatrazadas")
{	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = utf8_decode($cod_venta); 
	vercuotasatrazadas($cod_venta);
}


if($operacion=="actualizarApodo")
{	
	$cod_venta=$_POST['cod_venta'];
    $cod_venta = utf8_decode($cod_venta); 
	
	$apodo=$_POST['apodo'];
    $apodo = utf8_decode($apodo); 
	actualizarApodo($cod_venta,$apodo);
}


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


function guardarPorcentajeProgreso($id_detalle_tratamientoConsulta,$porcentaje)
{
     $mysqli = conectar_al_servidor();
 
	$consulta1 = "UPDATE detalle_venta SET progreso_porcentaje = '$porcentaje' WHERE cod_detalle = '$id_detalle_tratamientoConsulta'";

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

$descripcion = utf8_encode($valor['descripcion']);   
$usuario = utf8_encode($valor['usuario']);   
$fecha_hora = utf8_encode($valor['fecha_hora']);   
 
 
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

$cod_consulta = utf8_encode($valor['cod_consulta']);   
$fecha = utf8_encode($valor['fecha']);          
$trabajo_realizado = utf8_encode($valor['trabajo_realizado']);          
$proximo_trabajo = utf8_encode($valor['proximo_trabajo']);  
$motivoconsulta = utf8_encode($valor['motivoconsulta']);  
$diagnostico = utf8_encode($valor['diagnostico']);  
$especialista = utf8_encode($valor['especialista']);  
 
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

$sql= "select dtv.descripcion , pr.cod_producto,dtv.cantidad_detalle,pr.nombre_producto,dtv.cod_detalle ,estado_tratamiento,progreso_porcentaje
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

$descripcion = utf8_encode($valor['descripcion']);   
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$cod_detalle = utf8_encode($valor['cod_detalle']);          
$cantidad_detalle = utf8_encode($valor['cantidad_detalle']);  
$estado_tratamiento = utf8_encode($valor['estado_tratamiento']); 
$progreso_porcentaje = utf8_encode($valor['progreso_porcentaje']); 

$Style='';
if($estado_tratamiento!=""){
	$Style=" style=' background-color: #8BC34A; color:#ffffff;' ";
}

// $descripcionDetalleVenta=buscardescripcionDetalleVenta($cod_detalle);
 $styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatostrConsultaTratamiento(this)'> 
<td  style='width:20%;text-aling:center'>".number_format($cantidad_detalle,'0',',','.')."</td>
<td  style='width:60%'>$nombre_producto   $descripcion </td> 
<td   style='width:20%;text-align: center;'> <span style='
            background: linear-gradient(135deg, #4CAF50, #81C784);
    color: #fff;
    padding: 8px 3px;
    border-radius: 7px;
    font-weight: bold;
    font-size: 12px;
    display: inline-block;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
    '>  $progreso_porcentaje % </span></td> 
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

 

 function buscarVistaConsulta($Paciente,$local)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
 
	
		$condicionlocal="";
	if($local!=""){
		$condicionlocal=" and  cod_local='".$local."' ";
	}
	
	$condicionPaciente="";
	if($Paciente!=""){
		$condicionPaciente=" and  concat(cl.ci_cliente,' ',cl.rut_cliente ,' ',p.nombre_persona )   like '%".$Paciente."%' ";
	}
	
	
		$sql= "Select  nombre_persona as paciente,cl.ci_cliente,cl.cod_cliente,num_factura,cod_venta,apodo , 
		(select sum(progreso_porcentaje) from detalle_venta where cod_ventaFK=cod_venta) as porcentaje , 
		(select count(*) from detalle_venta where cod_ventaFK=cod_venta) as totalporcentaje
		from venta vt inner join cliente cl on cod_clienteFK=cod_cliente
		inner join persona p on cod_cliente=cod_persona
		  where cl.estado = 'Activo' and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0".$condicionPaciente.$condicionlocal." limit 100;";


   
   $stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $contador=0;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		   
		  
		  	  $num_factura=utf8_encode($valor['num_factura']);
		  	  $ci_cliente=utf8_encode($valor['ci_cliente']); 
			  $paciente=utf8_encode($valor['paciente']);
			  $cod_cliente=utf8_encode($valor['cod_cliente']);
			   $decripcion=''; 
			   $cod_venta=utf8_encode($valor['cod_venta']);
			   $apodo=utf8_encode($valor['apodo']);
			   
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
<div class='tarjeta-paciente' onclick='ObtenerdatosAbmConsulta(this)' style='
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
  '>DATOS PACIENTE</h3>
  
  <p><strong>Nombre:</strong> $paciente</p>
  <p><strong>CI:</strong> $ci_cliente</p>
  <p><strong>Código venta:</strong> $num_factura</p>

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
 }
 
 		
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;


}
 
function detalleTratamiento($buscar) {
    $mysqli = conectar_al_servidor();

    $sql = "SELECT pr.cod_producto, pr.nombre_producto 
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
            $nombre_producto = utf8_encode($row['nombre_producto']);
            $html .= "
            <li style='
                background-color:#f2f2f2;
                margin-bottom:4px;
                padding:5px 10px;
                border-radius:4px;
                font-size:13px;
            '>
            $nombre_producto
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


$cod_consulta = utf8_encode($valor['cod_consulta']);
$cod_ventaFK = utf8_encode($valor['cod_ventaFK']);
$fecha = utf8_encode($valor['fecha']);
$cod_usuarioFK = utf8_encode($valor['cod_usuarioFK']);
$cod_agendamientoFK = utf8_encode($valor['cod_agendamientoFK']);
$estado = utf8_encode($valor['estado']);
$trabajo_realizado = utf8_encode($valor['trabajo_realizado']);
$proximo_trabajo = utf8_encode($valor['proximo_trabajo']);
$motivoconsulta = utf8_encode($valor['motivoconsulta']);
$diagnostico = utf8_encode($valor['diagnostico']);
$cod_clienteFK = utf8_encode($valor['cod_clienteFK']);
$local = utf8_encode($valor['local']);
$especialista = utf8_encode($valor['especialista']);
$cliente = utf8_encode($valor['cliente']);
$ci = utf8_encode($valor['ci']);


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