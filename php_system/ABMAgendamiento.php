<?php
require("conexion.php");
require_once("solicitud_eliminado_helper.php");
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



	
if($operacion=="nuevo" || $operacion=="editar")
{
	
	
	$Cod_Agendamiento=$_POST['idabm'];
    $Cod_Agendamiento = mb_convert_encoding((string)($Cod_Agendamiento), 'ISO-8859-1', 'UTF-8');
	$Cod_PacienteFK=$_POST['idPaciente'];
    $Cod_PacienteFK = mb_convert_encoding((string)($Cod_PacienteFK), 'ISO-8859-1', 'UTF-8'); 
	$obs=$_POST['obs'];
    $obs = mb_convert_encoding((string)($obs), 'ISO-8859-1', 'UTF-8');
    $FechaRecepcion=$_POST['FechaRecepcion'];
    $FechaRecepcion = mb_convert_encoding((string)($FechaRecepcion), 'ISO-8859-1', 'UTF-8');
    $FechaConsulta=$_POST['FechaConsulta'];
    $FechaConsulta = mb_convert_encoding((string)($FechaConsulta), 'ISO-8859-1', 'UTF-8');  
	
	$useru=$_POST['useru'];
    $useru = mb_convert_encoding((string)($useru), 'ISO-8859-1', 'UTF-8');
	
	$MedicoFK=$_POST['MedicoFK'];
    $MedicoFK = mb_convert_encoding((string)($MedicoFK), 'ISO-8859-1', 'UTF-8');
    
    
	abm($MedicoFK,$useru,$Cod_Agendamiento,$Cod_PacienteFK,$FechaRecepcion,$FechaConsulta,$obs,$operacion);

}
 
 
  if($operacion=="obtenerPacientes"){

 	obtenerPacientes();
 }
 
 
 
if($operacion=="buscardatosdeAgendamientoBuscador")
{
	$paciente=$_POST['paciente'];
    $paciente = mb_convert_encoding((string)($paciente), 'ISO-8859-1', 'UTF-8');
	$medico=$_POST['medico'];
    $medico = mb_convert_encoding((string)($medico), 'ISO-8859-1', 'UTF-8');
	$fecha=$_POST['fecha'];
    $fecha = mb_convert_encoding((string)($fecha), 'ISO-8859-1', 'UTF-8');
	buscardatosdeAgendamientoBuscador($paciente,$medico,$fecha);

}



if($operacion=="EliminarAgendamiento")
{
	$cod_agen=$_POST['cod_agen'];
    $cod_agen = mb_convert_encoding((string)($cod_agen), 'ISO-8859-1', 'UTF-8');
	$estado=$_POST['estado'];
    $estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	CambiarEstadoAgendamiento($cod_agen,$estado);

}




}



function CambiarEstadoAgendamiento($cod_agen,$estado)
{
	if (solicitudEliminadoEsEstadoInactivo($estado)) {
		$user = solicitudEliminadoValorPost('useru', '0');
		$respuesta = registrarSolicitudEliminacionGenerica(
			'agendamiento',
			'cod_agendamiento',
			$cod_agen,
			'Solicitud de eliminacion de agendamiento.',
			$user,
			'Agendamiento: '.$cod_agen
		);
		echo json_encode($respuesta);
		exit;
	}
	$mysqli=conectar_al_servidor();

    
    $consulta="Update agendamiento set  estado='$estado' where cod_agendamiento='$cod_agen'";	

	$stmt = $mysqli->prepare($consulta);
        
	
if (!$stmt->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}




function buscardatosdeAgendamientoBuscador($paciente,$medico,$fecha)
{

	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
	 
	 	$condicionpaciente="";
if($paciente!=""){
	$condicionpaciente=" and (Select nombre_persona from persona where cod_persona=cod_clienteFK ) like '%".$paciente."%'";
}

	$condicionmedico="";
if($medico!=""){
	$condicionmedico=" and (Select nombre_persona from persona whee cod_usuarioFK=cod_persona ) like '%".$medico."%'";
}

	$condicionfecha="";
if($fecha!=""){
	$condicionfecha=" and fecha_con ='".$fecha."'";
}
	$sql= "Select ag.cod_agendamiento, vt.num_factura,cl.ci_cliente,ag.estado,
		(Select nombre_persona from persona where ag.cod_usuarioFK=cod_persona ) as medico,
		(Select  nombre_persona from persona where cod_persona=cod_clienteFK ) as paciente , fecha_con
		from agendamiento ag 
		inner join venta vt on cod_ventaFK=cod_venta 
		inner join cliente cl on cod_cliente=cod_clienteFK where ag.estado='Activo' and estadocuenta='Activo'  ".$condicionpaciente.$condicionmedico.$condicionfecha."
		order by ag.cod_agendamiento asc ";
		 
		 // echo($sql);
		 // exit;
   
   $stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		      $cod_agendamiento=$valor['cod_agendamiento'];
		  	  $num_factura=mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');
		  	  $medico=mb_convert_encoding((string)($valor['medico']), 'UTF-8', 'ISO-8859-1');
		  	  $paciente=mb_convert_encoding((string)($valor['paciente']), 'UTF-8', 'ISO-8859-1'); 		  	 
		  	  $fecha_con=mb_convert_encoding((string)($valor['fecha_con']), 'UTF-8', 'ISO-8859-1'); 		  	 
		  	  $ci_cliente=mb_convert_encoding((string)($valor['ci_cliente']), 'UTF-8', 'ISO-8859-1'); 		  	 
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 		  	 
			  
		  	 
			  $pagina.="
			  <table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
			  <tr id='tbSelecRegistro'  onclick='obtenerdatosAgendamiento(this)' >
			  <td id='td_id_1' style='width:5%'>".$cod_agendamiento."</td>  
			  <td id='td_datos_2'style='width:25%' class='tdRegistroSearch' >".$ci_cliente." / ".$paciente." / ".$num_factura."</td>
			  <td id='td_datos_3' style='width:30%' class='tdRegistroSearch'>".$medico."</td> 
			  <td id='td_datos_4' style='width:25%' class='tdRegistroSearch'>".$fecha_con."</td> 
 			  <td id='td_datos_5' style='width:15%' class='tdRegistroSearch' >".$estado."</td> 
			  </tr>
			  </table>";
			  
		 }
 }
 
 $informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;


}






function abm($MedicoFK,$useru,$Cod_Agendamiento,$Cod_PacienteFK,$FechaRecepcion,$FechaConsulta,$obs,$operacion)
{
	
	
	if($Cod_PacienteFK=="" || $FechaRecepcion==""  || $FechaConsulta==""  ){
	$informacion =array("1" => "DI" , "2" => $Cod_PacienteFK , "3" => $FechaRecepcion, "4" => $FechaConsulta );
		echo json_encode($informacion);	
		exit;
	}

	$mysqli=conectar_al_servidor();
 
	if($operacion=="nuevo")
	{
     
    $consulta="insert into agendamiento (cod_usuarioFK, cod_ventaFK, fecha_con, fecha_ag, estado, decripcion) values ('$MedicoFK','$Cod_PacienteFK','$FechaConsulta','$FechaRecepcion','Activo','$obs')";	

     $stmt = $mysqli->prepare($consulta);

	}
	if($operacion=="editar")
	{
		if (isset($Estado) && solicitudEliminadoEsEstadoInactivo($Estado)) {
			$user = solicitudEliminadoValorPost('useru', '0');
			$respuesta = registrarSolicitudEliminacionGenerica(
				'agendamiento',
				'idagendamiento',
				$Cod_Agendamiento,
				'Solicitud de eliminacion de agendamiento.',
				$user,
				'Agendamiento: '.$Cod_Agendamiento
			);
			echo json_encode($respuesta);
			exit;
		}
        
    $consulta="Update agendamiento set cod_UsuarioFK=?, Cod_PacienteFK=?, fecha=?, tipo=?, fecha_hora_ag=?, estado=?, cod_consultasFK=?, edad=?, descripcion=?,turno=? where idagendamiento=?";	

	$stmt = $mysqli->prepare($consulta);
        
    $ss='sssssssssss';
        
   $stmt->bind_param($ss,$useru,$Cod_PacienteFK,$FechaRecepcion,$tipopaciente,$FechaEntrega,$Estado,$contultaFK,$edad,$Observacion,$nroTurno,$Cod_Agendamiento); 
        
	
       
	}
	
if (!$stmt->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 

$informacion =array("1" => "exito" );
echo json_encode($informacion);	
exit;

	
	
	
}






function obtenerPacientes()
{
	$mysqli=conectar_al_servidor();
	 $pagina="";  
	
		$sql= "Select cod_venta , nombre_persona , num_factura, (select ci_cliente from cliente where cod_clienteFK=cod_cliente) as ci from venta vt inner join persona on cod_clienteFK=cod_persona where estadocuenta='Activo' and  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 order by nombre_persona asc ";
		   
   $stmt = $mysqli->prepare($sql);
  	
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}


	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		   
		  
		      $cod_venta=$valor['cod_venta'];
		  	  $nombre_persona=mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1'); 
		  	  $num_factura=mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1'); 
		  	  $ci=mb_convert_encoding((string)($valor['ci']), 'UTF-8', 'ISO-8859-1'); 
		  	 		  	 
			  $pagina.="<option id='$cod_venta'  >$ci / $nombre_persona / $num_factura</option>";
			
		  	  
	  }
 }
 
  mysqli_close($mysqli);
  $informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;


}


 
 
 
ObtenerDatos($operacion);
 
verificar($funt);
?>
