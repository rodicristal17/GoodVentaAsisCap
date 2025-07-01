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



	
if($operacion=="nuevo" || $operacion=="editar")
{
	
	
	$Cod_Agendamiento=$_POST['idabm'];
    $Cod_Agendamiento = utf8_decode($Cod_Agendamiento);
	$Cod_PacienteFK=$_POST['idPaciente'];
    $Cod_PacienteFK = utf8_decode($Cod_PacienteFK); 
	$obs=$_POST['obs'];
    $obs = utf8_decode($obs);
    $FechaRecepcion=$_POST['FechaRecepcion'];
    $FechaRecepcion = utf8_decode($FechaRecepcion);
    $FechaConsulta=$_POST['FechaConsulta'];
    $FechaConsulta = utf8_decode($FechaConsulta);  
	
	$useru=$_POST['useru'];
    $useru = utf8_decode($useru);
	
	$MedicoFK=$_POST['MedicoFK'];
    $MedicoFK = utf8_decode($MedicoFK);
    
    
	abm($MedicoFK,$useru,$Cod_Agendamiento,$Cod_PacienteFK,$FechaRecepcion,$FechaConsulta,$obs,$operacion);

}
 
 
  if($operacion=="obtenerPacientes"){

 	obtenerPacientes();
 }
 
 
 
if($operacion=="buscardatosdeAgendamientoBuscador")
{
	$paciente=$_POST['paciente'];
    $paciente = utf8_decode($paciente);
	$medico=$_POST['medico'];
    $medico = utf8_decode($medico);
	$fecha=$_POST['fecha'];
    $fecha = utf8_decode($fecha);
	buscardatosdeAgendamientoBuscador($paciente,$medico,$fecha);

}



if($operacion=="EliminarAgendamiento")
{
	$cod_agen=$_POST['cod_agen'];
    $cod_agen = utf8_decode($cod_agen);
	$estado=$_POST['estado'];
    $estado = utf8_decode($estado);
	CambiarEstadoAgendamiento($cod_agen,$estado);

}




}



function CambiarEstadoAgendamiento($cod_agen,$estado)
{
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
		  	  $num_factura=utf8_encode($valor['num_factura']);
		  	  $medico=utf8_encode($valor['medico']);
		  	  $paciente=utf8_encode($valor['paciente']); 		  	 
		  	  $fecha_con=utf8_encode($valor['fecha_con']); 		  	 
		  	  $ci_cliente=utf8_encode($valor['ci_cliente']); 		  	 
		  	  $estado=utf8_encode($valor['estado']); 		  	 
			  
		  	 
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
		  	  $nombre_persona=utf8_encode($valor['nombre_persona']); 
		  	  $num_factura=utf8_encode($valor['num_factura']); 
		  	  $ci=utf8_encode($valor['ci']); 
		  	 		  	 
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