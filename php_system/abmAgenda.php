<?php

//cargar achivos importantes
require_once("conexion.php");
include_once("verificar_navegador.php");
include_once("buscar_nivel.php");
include_once("classTable.php");
include_once("abmCalendar.php");

function verificarAgenda($operacion)
{
	
	
	if($operacion=="buscaroption")
{

	buscaroption();

}else {	
	
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



//CONTROL DE ACCESO
if ($operacion == 'obtenerComentarios') {
	$idAgenda = $_POST['idAgenda'];
	$idAgenda = mb_convert_encoding((string)($idAgenda), 'ISO-8859-1', 'UTF-8');
	$result= obtenerComentarios(array(
		'cod_agendaFK' => $idAgenda,
	));

	if (isset($result["1"]) && $result["1"] == "error") {
		echo json_encode($result);
		exit;
	}

	$mysqliUsuarios = conectar_al_servidor();
	$mysqliUsuarios->set_charset("utf8");
    $usuariosAgenda = obtenerUsuariosAgenda($mysqliUsuarios);
	mysqli_close($mysqliUsuarios);
	$pagina="";
	foreach ($result as $comentario) {
		$textoComentario = isset($comentario["comentario"]) ? $comentario["comentario"] : "";
        if(preg_match_all('/@\{(\d+)\}\s*:\s*(.*?)(?=@\{\d+\}\s*:|$)/s', $textoComentario, $coincidencias, PREG_SET_ORDER)){
            foreach($coincidencias as $coincidencia){
                $codUsuario = $coincidencia[1];
                $nombreUsuario = isset($usuariosAgenda[$codUsuario]) ? $usuariosAgenda[$codUsuario] : "@{".$codUsuario."}";
                $contenidoMotivo = nl2br(htmlspecialchars(trim($coincidencia[2]), ENT_QUOTES, 'UTF-8'), false);

                $pagina .= '<div class="sugerencias-container" style="justify-content:flex-start;margin:0;">
                    <div class="card my-3" style="border-left:5px solid #416c8f;margin: 0px !important;margin-bottom: 7px !important;display:flex;flex-direction:column;gap:0;min-height:auto;">
                        <div class="card-header d-flex justify-content-between align-items-center" style="padding:6px 10px 4px 10px;gap:10px;min-height:auto;">
                            <span style="font-size:10pt;line-height:1.15;">'.htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8').'</span>
                        </div>
                        <div class="card-body" style="padding:4px 10px 8px 10px;">
                            <p class="card-text" style="font-size: 10pt; text-align:justify;margin:0;line-height:1.35;">'.$contenidoMotivo.'</p>
                        </div>
                    </div>
                </div>';
            }
        }
	}

	echo json_encode(array('1' => 'exito', '2' =>$result, '3' => $pagina));
	exit;
}

if ($operacion == 'crearComentario') {
	$idAgenda = $_POST['idAgenda'];
	$idAgenda = mb_convert_encoding((string)($idAgenda), 'ISO-8859-1', 'UTF-8');
	$comentario = $_POST['comentario'];
	$comentario = mb_convert_encoding((string)($comentario), 'ISO-8859-1', 'UTF-8');

	// Limpiar comentario
	$comentario= "@{".$user."}: $comentario";
	$result= crearComentario($idAgenda, $comentario);
	echo json_encode($result);
	exit;
}

if($operacion=="nuevo" || $operacion=="editar")
{
	$idAgenda=isset($_POST['idAgenda']) ? $_POST['idAgenda'] : NULL;
$idAgenda = $idAgenda !== NULL ? mb_convert_encoding((string)($idAgenda), 'ISO-8859-1', 'UTF-8') : NULL;
	$motivo=isset($_POST['motivo']) ? $_POST['motivo'] : NULL;
$motivo = $motivo !== NULL ? mb_convert_encoding((string)($motivo), 'ISO-8859-1', 'UTF-8') : NULL;
	$fechaCompromiso=isset($_POST['fechaCompromiso']) ? $_POST['fechaCompromiso'] : NULL;
$fechaCompromiso = $fechaCompromiso !== NULL ? mb_convert_encoding((string)($fechaCompromiso), 'ISO-8859-1', 'UTF-8') : NULL;
	$estado=isset($_POST['estado']) ? $_POST['estado'] : NULL;
$estado = $estado !== NULL ? mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8') : NULL;
	$Cod_cobrador=isset($_POST['Cod_cobrador']) ? $_POST['Cod_cobrador'] : NULL;
$Cod_cobrador = $Cod_cobrador !== NULL ? mb_convert_encoding((string)($Cod_cobrador), 'ISO-8859-1', 'UTF-8') : NULL;
	$cod_clienteAgenda=isset($_POST['cod_clienteAgenda']) ? $_POST['cod_clienteAgenda'] : NULL;
$cod_clienteAgenda = $cod_clienteAgenda !== NULL ? mb_convert_encoding((string)($cod_clienteAgenda), 'ISO-8859-1', 'UTF-8') : NULL;

abmAgenda($idAgenda,$motivo,$fechaCompromiso,$estado,$Cod_cobrador,$cod_clienteAgenda,$operacion);

}


if($operacion=="buscar")
{
	$fecha1=$_POST["fecha1"];
 	$fecha1=mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
	$fecha2=$_POST["fecha2"];
 	$fecha2=mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
	
	$cliente=$_POST["cliente"];
 	$cliente=mb_convert_encoding((string)($cliente), 'ISO-8859-1', 'UTF-8');
	$cobrador=$_POST["cobrador"];
 	$cobrador=mb_convert_encoding((string)($cobrador), 'ISO-8859-1', 'UTF-8');
	$estado=$_POST["estado"];
 	$estado=mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	$tipo=$_POST["tipo"];
 	$tipo=mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');
	
 	buscar($estado,$tipo,$fecha1,$fecha2,$cliente,$cobrador);

}	
if($operacion=="buscarvista")
{
	$buscar=$_POST['buscar'];
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
	buscarvista($buscar);

}
}

}

function abmAgenda($idAgenda,$motivo,$fechaCompromiso,$estado,$Cod_cobrador,$cod_clienteAgenda,$operacion)
{
	
	
if($motivo=="" || $Cod_cobrador==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

if($operacion=="nuevo")
{


$consulta1="Insert into visitascliente (fecha,Motivo,cod_clienteFK,cod_cobradorFK,fechaCompro,estado) values (NOW(),?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$stmt1->bind_param('siiss',$motivo,$cod_clienteAgenda,$Cod_cobrador,$fechaCompromiso,$estado);
}


if($operacion=="editar")
{

$parametros = array();
$atributos = "";
$ss = "";

if ($motivo !== NULL) {
	$atributos .= "Motivo=?";
	$ss .= "s";
	$parametros[] = $motivo;
}
if ($cod_clienteAgenda !== NULL) {
	$atributos .= $atributos != "" ? ",cod_clienteFK=?" : "cod_clienteFK=?";
	$ss .= "i";
	$parametros[] = $cod_clienteAgenda;
}
if ($Cod_cobrador !== NULL) {
	$atributos .= $atributos != "" ? ",cod_cobradorFK=?" : "cod_cobradorFK=?";
	$ss .= "i";
	$parametros[] = $Cod_cobrador;
}
if ($fechaCompromiso !== NULL) {
	$atributos .= $atributos != "" ? ",fechaCompro=?" : "fechaCompro=?";
	$ss .= "s";
	$parametros[] = $fechaCompromiso;
}
if ($estado !== NULL) {
	$atributos .= $atributos != "" ? ",estado=?" : "estado=?";
	$ss .= "s";
	$parametros[] = $estado;
}

if ($atributos == "") {
	$informacion =array("1" => "camposvacio");
	echo json_encode($informacion);	
	exit;
}

$parametros[] = $idAgenda;
$ss .= "i";

$consulta1="Update visitascliente set $atributos where cod_VisitasCliente=?";	
$stmt1 = $mysqli->prepare($consulta1);

$refs = array();
foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}

call_user_func_array(array($stmt1, 'bind_param'), array_merge(array($ss), $refs));

}

if (!$stmt1->execute()) {
$informacion =array("1" => "error", "mensaje" => "Error al guardar: " . $stmt1->error, "sql" => $consulta1);
echo json_encode($informacion);
exit;

}

$stmt1->close();


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}

function buscar($estado,$tipo,$fecha1,$fecha2,$cliente,$cobrador)
{
$mysqli=conectar_al_servidor();


$condicionfecha="";
if($fecha1!="" || $fecha2!=""){
	if($tipo=="compromiso" ){
		$condicionfecha=" and fechaCompro between '$fecha1' and '$fecha2' ";
	}else{
		$condicionfecha=" and fecha between '$fecha1' and '$fecha2 23:59:00' ";
}
}

$condicioncliente="";
if($cliente!=""){
	$condicioncliente=" and (select nombre_persona from persona where cod_persona = cod_clienteFK) like '%$cliente%'";
}
$condicioncobrador="";
if($cobrador!=""){
	$condicioncobrador=" and (select nombre_persona from persona where cod_persona = cod_cobradorFK) like '%$cobrador%'";
}
$condicionestado="";
if($estado!=""){
	$condicionestado=" and estado = '$estado'";
}





$sql= "select estado,fechaCompro, cod_VisitasCliente, fecha, Motivo, cod_clienteFK, cod_cobradorFK ,(select nombre_persona from persona where cod_persona = cod_cobradorFK) as cobrador , (select nombre_persona from persona where cod_persona = cod_clienteFK) as cliente , (select nombre from zona where idzona=(select idzonaFk from cliente where cod_cliente = cod_clienteFK)) as zona  from visitascliente  where cod_VisitasCliente!=''
".$condicioncliente.$condicioncobrador.$condicionestado.$condicionfecha." limit 500";

// echo($sql);
// exit;
$pagina = "";   
$stmt = $mysqli->prepare($sql);
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
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');  
$cod_clienteFK = mb_convert_encoding((string)($valor['cod_clienteFK']), 'UTF-8', 'ISO-8859-1');
$cod_VisitasCliente = mb_convert_encoding((string)($valor['cod_VisitasCliente']), 'UTF-8', 'ISO-8859-1');
$Motivo = mb_convert_encoding((string)($valor['Motivo']), 'UTF-8', 'ISO-8859-1');     
$fecha = mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1'); 
$cliente = mb_convert_encoding((string)($valor['cliente']), 'UTF-8', 'ISO-8859-1');     
$zona = mb_convert_encoding((string)($valor['zona']), 'UTF-8', 'ISO-8859-1'); 
$cobrador = mb_convert_encoding((string)($valor['cobrador']), 'UTF-8', 'ISO-8859-1');  
$fechaCompro = mb_convert_encoding((string)($valor['fechaCompro']), 'UTF-8', 'ISO-8859-1');    
  


 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmAgenda(this)'>
<td  id='td_datos_1' style='width:15%'>".$fecha."</td>
<td  id='td_datos_2' style='width:25%'>".$cliente."</td>
<td  id='td_datos_3' style='width:30%'>".$Motivo."</td>
<td  id='td_datos_4' style='width:15%'>".$cobrador."</td>
<td  id='td_datos_5' style='width:15%'>".$fechaCompro."</td>
<td  id='td_id' style='display:none'>".$cod_VisitasCliente."</td>
<td  id='td_datos_6' style='display:none'>".$estado."</td>
<td  id='td_datos_7' style='display:none'>".$cod_clienteFK."</td>
</tr>
</table>";


}
}
     mysqli_close($mysqli);
$informacion =array("1" => "exito","2" =>($pagina),"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}



function buscarvista($buscar)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select * from zona where nombre like ?  and estado='Activo' ";
		
   
   
   $stmt = $mysqli->prepare($sql);
  	$s='s';
$buscar="%".$buscar."%";
//$buscar="".$buscar."";
$stmt->bind_param($s,$buscar);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
  $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idzona=$valor['idzona'];
		  	  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	 
		  	 
			    	 $styleName=CargarStyleTable($styleName);  
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosVistaZona(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idzona."</td>
<td  id='td_datos_1' style='width:50%'>".$nombre."</td>
<td  id='td_datos_2' style='display:none'>".$estado."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}

function buscaroption()
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select * from zona where estado='Activo' ";
		 $pagina="<option  value='' >SELECCIONAR</option>";  

   
   
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idzona=$valor['idzona'];
		  	  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	 
		  	 
			    	
			  $pagina.="<option  value='$idzona' >".$nombre."</option>";   
			  
	  }
 }
 
 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}


function obtenerComentarios($filtros= array())
{
	$sqlFiltro = "";
	$parametros = array();
	$ss = "";

	foreach ($filtros as $key => $value) {
		if ($value === NULL || $value === "") {continue;}

		if ($sqlFiltro == "") {
			$sqlFiltro .= "WHERE ";
		} else {
			$sqlFiltro .= " AND ";
		}

		switch ($key) {
			default:
				$sqlFiltro .= is_numeric($value) ? "ca.$key = ?" : "ca.$key LIKE ?";
				$ss .= is_numeric($value) ? "i" : "s";
				$parametros[] = is_numeric($value) ? $value : "%".$value."%";
				break;
		}
	}

	$sql = "SELECT ca.id, ca.comentario, ca.fecha, ca.cod_agendaFK
		FROM comentarios_agenda ca
		$sqlFiltro
		ORDER BY ca.fecha ASC, ca.id ASC";

	$mysqli=conectar_al_servidor();
	$stmt = $mysqli->prepare($sql);

	if (!$stmt) {
		return array("1" => "error", "mensaje" => "Error al preparar consulta: " . $mysqli->error, "sql" => $sql);
	}

	if (count($parametros) > 0) {
		$refs = array();
		foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}
		call_user_func_array(array($stmt, 'bind_param'), array_merge(array($ss), $refs));
	}

	if (!$stmt->execute()) {
		$informacion =array("1" => "error", "mensaje" => "Error al obtener comentarios: " . $stmt->error, "sql" => $sql);
		$stmt->close();
		mysqli_close($mysqli);
		return $informacion;
	}

	$result = $stmt->get_result();
	$registros = array();
	while ($row = $result->fetch_assoc()) {
		$reg = array();
		foreach ($row as $key => $value) {
			$reg[$key]= mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
		}
		$registros[] = $reg;
	}

	$stmt->close();
	mysqli_close($mysqli);

	return $registros;
}

function crearComentario($idAgenda, $comentario)
{
	if ($idAgenda == "" || $comentario == "") {
		return array("1" => "camposvacio");
	}

	$mysqli=conectar_al_servidor();
	$sql = "INSERT INTO comentarios_agenda (comentario, cod_agendaFK) VALUES (?, ?)";
	$stmt = $mysqli->prepare($sql);

	if (!$stmt) {
		return array("1" => "error", "mensaje" => "Error al preparar consulta: " . $mysqli->error, "sql" => $sql);
	}

	$stmt->bind_param('si', $comentario, $idAgenda);

	if (!$stmt->execute()) {
		$informacion = array("1" => "error", "mensaje" => "Error al guardar comentario: " . $stmt->error, "sql" => $sql);
		$stmt->close();
		mysqli_close($mysqli);
		return $informacion;
	}

	$idComentario = $stmt->insert_id;
	$stmt->close();
	mysqli_close($mysqli);

	return array("1" => "exito", "id" => $idComentario);
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
	$operacion = $_POST['funt'];
	$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');

	verificarAgenda($operacion);
}
?>
