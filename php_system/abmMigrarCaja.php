<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

include("buscar_nivel.php");
require("conexion.php");
include("verificar_navegador.php");
include("classTable.php");
include('quitarseparadormiles.php');

function verificar($operacion)
{
	
 $user=$_POST['useru'];
    $user = utf8_decode($user);	
if($user!=""){

	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = utf8_decode($navegador);
$resp=verificar_navegador($user,$navegador,$pass);
if($resp!="ok" && $operacion!="buscaroption"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}
}


//CONTROL DE ACCESO



	
if($operacion=="nuevo" || $operacion=="editar")
{
	
	$cod_MigrarCaja=$_POST['cod_MigrarCaja'];
$cod_MigrarCaja = utf8_decode($cod_MigrarCaja);

	$monto=$_POST['monto'];
$monto = quitarseparadormiles($monto);

	$obs=$_POST['obs'];
$obs = utf8_decode($obs);

	$usu_RecibirFK=$_POST['usu_RecibirFK'];
$usu_RecibirFK = utf8_decode($usu_RecibirFK);

	$estado=$_POST['estado'];
$estado = utf8_decode($estado);	

	$user=$_POST['useru'];
$user = utf8_decode($user);	

	$cod_cajaApertura=$_POST['cod_cajaApertura'];
$cod_cajaApertura = utf8_decode($cod_cajaApertura);	


	abm($cod_MigrarCaja,$monto,$obs,$usu_RecibirFK,$estado,$user,$cod_cajaApertura,$operacion);

}




if($operacion=="buscar")
{
	$fecha=$_POST['fecha'];
$fecha = utf8_decode($fecha);
$recibe=$_POST['recibe'];
$recibe = utf8_decode($recibe);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);

$user=$_POST['useru'];
$user = utf8_decode($user);	
	buscar($fecha,$recibe,$user,$estado);

}	


if($operacion=="buscaroptionUsu")
{

	buscaroptionUsu();

}	




if($operacion=="BuscarAbmCajaEscritorio")
{

$user=$_POST['useru'];
$user = utf8_decode($user);	
	BuscarAbmCajaEscritorio( $user,"Activo");

}	


if($operacion=="nuevoCajaEscritorio")
{

$idabm=$_POST['idabm'];
$idabm = utf8_decode($idabm);	

$codApertura=$_POST['codApertura'];
$codApertura = utf8_decode($codApertura);	
	nuevoCajaEscritorio( $idabm,$codApertura);

}	




if($operacion=="buscarInforme")
{
	$fecha=$_POST['fecha'];
$fecha = utf8_decode($fecha);
$recibe=$_POST['recibe'];
$recibe = utf8_decode($recibe);
$usuario=$_POST['usuario'];
$usuario = utf8_decode($usuario);

$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);

$tipo=$_POST['tipo'];
$tipo = utf8_decode($tipo);	
	buscarInforme($fecha,$recibe,$tipo,$usuario,$fecha1,$fecha2);

}	
 

}


function buscarInforme($fecha,$recibe,$tipo,$usuario,$fecha1,$fecha2)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
	 	$tipotrx= $tipo;
		
		if($tipo=="CAJA SISTEMA"){
			
			$condicionFiltrofecha="";
			if($fecha!=""){
				$condicionFiltrofecha=" and fecha ='".$fecha."'";
			}
			
			$condicionfecha="";
			if($fecha1!="" && $fecha2!=""){
				$condicionfecha=" and fecha between '".$fecha1."' and '".$fecha2."' ";
			}
			
			$condicionusuario="";
			if($usuario!=""){
				$condicionusuario=" and (select nombre_persona from persona where cod_persona=cod_UsuEnviaFK) like '%".$usuario."%' ";
			}

			$condicionrecibe="";
			if($recibe!=""){
				$condicionrecibe=" and (select nombre_persona from persona where cod_persona=cod_usuRecibeFK)  like '%".$recibe."%'";
			}
				
			$sql= "Select idmigrar_caja, obs, fecha, monto, cod_caja_desdeFK, cod_caja_hastaFK, estado, tipo, cod_usuRecibeFK, cod_UsuEnviaFK ,
				(select nombre_persona from persona where cod_persona=cod_usuRecibeFK) as usuarioRecibe  ,
				(select nombre_persona from persona where cod_persona=cod_UsuEnviaFK) as usuarioEnvia from migrar_caja where idmigrar_caja!='' ".$condicionFiltrofecha.$condicionfecha.$condicionusuario.$condicionrecibe." order by idmigrar_caja desc limit 500 ";
			
		}else{
			
			$condicionFiltrofecha="";
			if($fecha!=""){
				$condicionFiltrofecha=" and fechacierre ='".$fecha."'";
			}
			
			$condicionfecha="";
			if($fecha1!="" && $fecha2!=""){
				$condicionfecha=" and fechacierre between '".$fecha1."' and '".$fecha2."' ";
			}
			
			$condicionusuario="";
			if($usuario!=""){
				$condicionusuario=" and (Select nombre_persona from persona where cod_persona=cod_cobrador) like '%".$usuario."%' ";
			}

			$condicionrecibe="";
			if($recibe!=""){
				$condicionrecibe=" and (select (select nombre_persona from persona where cod_persona=codusuarioap)  from arqueocaja where idarqueocaja=cod_AperturaCajaFK )  like '%".$recibe."%'";
			}
			
			
			$sql= "Select idaperturacajaapp as idmigrar_caja, 
						  fechacierre as fecha,  concat('CAJA APP','-',(select (select nombre_persona from persona where cod_persona=codusuarioap)  from arqueocaja where idarqueocaja=cod_AperturaCajaFK )) as obs,
						  IFNULL(montocierre,0) as monto,
						  estado,
						 (Select nombre_persona from persona where cod_persona=cod_cobrador) as usuarioEnvia,
						(select (select nombre_persona from persona where cod_persona=codusuarioap)  from arqueocaja where idarqueocaja=cod_AperturaCajaFK ) as usuarioRecibe
				from aperturacajaapp ap where  estado='Cerrado'  ".$condicionFiltrofecha.$condicionfecha.$condicionusuario.$condicionrecibe."   order by idaperturacajaapp desc limit 500  ";
			
		}
		 
   // echo($sql);
   // exit; 
   $stmt = $mysqli->prepare($sql); 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $styleName="tableRegistroSearch";
 
 $totalMonto=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {	  
		  
		      $idmigrar_caja=$valor['idmigrar_caja'];
		  	  $obs=utf8_encode($valor['obs']);
		  	  $fecha=utf8_encode($valor['fecha']);
			  
			  $monto=$valor['monto']; 
			  
			  $estado=utf8_encode($valor['estado']);
			  
			  $usuarioRecibe=utf8_encode($valor['usuarioRecibe']);
			  $usuarioEnvia=utf8_encode($valor['usuarioEnvia']);
   	 
		 $totalMonto= $totalMonto + $monto; 
		 
			  $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'    >
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idmigrar_caja."</td>
<td  id='td_datos_1' style='width:10%'>".$fecha."</td>
<td  id='td_datos_2' style='width:10%'>".number_format($monto,'0',',','.')."</td>
<td  id='td_datos_3' style='width:25%'>".$obs."</td>
<td  id='td_datos_4' style='width:15%'>".$usuarioEnvia."</td>
<td  id='td_datos_4' style='width:10%'>".$tipotrx."</td>
<td  id='td_datos_4' style='width:15%'>".$usuarioRecibe."</td>
<td  id='td_datos_6' style='width:10%'>".$estado."</td>
</tr>
</table>";			  
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro ,"4" => number_format($totalMonto,'0',',','.'));
echo json_encode($informacion);	
exit;


}




function nuevoCajaEscritorio( $idabm,$codApertura)
{


$mysqli=conectar_al_servidor();

$consulta1="Update migrar_caja set  cod_caja_hastaFK='".$codApertura."' where idmigrar_caja='".$idabm."'";	
$stmt1 = $mysqli->prepare($consulta1);

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}

 
function BuscarAbmCajaEscritorio( $user,$estado)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
		$sql= "Select idmigrar_caja, obs, fecha, monto, cod_caja_desdeFK, cod_caja_hastaFK, estado, tipo, cod_usuRecibeFK, cod_UsuEnviaFK ,
				(select nombre_persona from persona where cod_persona=cod_usuRecibeFK) as usuarioRecibe  ,
				(select nombre_persona from persona where cod_persona=cod_UsuEnviaFK) as usuarioEnvia from migrar_caja where estado=? and cod_usuRecibeFK='".$user."' and cod_caja_hastaFK='0'   order by idmigrar_caja desc limit 50";
		
   // echo($sql);
   // exit;
   
   $stmt = $mysqli->prepare($sql);
  	$s='s';
$stmt->bind_param($s,$estado);

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
		  
		      $idmigrar_caja=$valor['idmigrar_caja'];
		  	  $obs=utf8_encode($valor['obs']);
		  	  $fecha=utf8_encode($valor['fecha']);
			  
			  $monto=$valor['monto'];
		  	  $cod_caja_desdeFK=utf8_encode($valor['cod_caja_desdeFK']);
		  	  $cod_caja_hastaFK=utf8_encode($valor['cod_caja_hastaFK']);
			  $estado=utf8_encode($valor['estado']);
			  $tipo=utf8_encode($valor['tipo']);
			  $cod_usuRecibeFK=utf8_encode($valor['cod_usuRecibeFK']);
			  $cod_UsuEnviaFK=utf8_encode($valor['cod_UsuEnviaFK']);
			  
			  $usuarioRecibe=utf8_encode($valor['usuarioRecibe']);
			  $usuarioEnvia=utf8_encode($valor['usuarioEnvia']);
			  
 
		  	 
			  $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='ObtenerdatosAbmCajaEscritorio(this)'  >
<td id='td_id' style='display:none'>".$idmigrar_caja."</td>
<td  id='td_datos_1' style='width:20%'>".$fecha."</td>
<td  id='td_datos_2' style='width:25%'>".$usuarioEnvia."</td>
<td  id='td_datos_3' style='width:25%'>".$obs."</td>
<td  id='td_datos_4' style='width:20%'>".number_format($monto,'0',',','.')."</td>
</tr>
</table>";			  
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}

 
function abm($cod_MigrarCaja,$monto,$obs,$usu_RecibirFK,$estado,$user,$cod_cajaApertura,$operacion)
{
	
	
if($monto==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor();

if($operacion=="nuevo")
{


$consulta1="Insert into migrar_caja (  obs, fecha, monto, cod_caja_desdeFK, cod_caja_hastaFK, estado, tipo, cod_usuRecibeFK, cod_UsuEnviaFK)
values(?,now(),?,?,0,?,'Enviar',?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssss';
$stmt1->bind_param($ss,$obs,$monto,$cod_cajaApertura,$estado,$usu_RecibirFK,$user);


}


if($operacion=="editar")
{

$consulta1="Update migrar_caja set obs=?, fecha=now(), monto=?,    estado=?,  cod_usuRecibeFK=?, cod_UsuEnviaFK=? where idmigrar_caja=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssss';
$stmt1->bind_param($ss,$obs,$monto ,$estado, $usu_RecibirFK,$user,$cod_MigrarCaja); 

}



if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}

function buscar($fecha,$recibe,$user,$estado)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 	$condicionfecha="";
if($fecha!=""){
	$condicionfecha=" and fecha ='".$fecha."'";
}
$condicionrecibe="";
if($recibe!=""){
	$condicionrecibe=" and (select nombre_persona from persona where cod_persona=cod_usuRecibeFK)  like '%".$recibe."%'";
}
		$sql= "Select idmigrar_caja, obs, fecha, monto, cod_caja_desdeFK, cod_caja_hastaFK, estado, tipo, cod_usuRecibeFK, cod_UsuEnviaFK ,
				(select nombre_persona from persona where cod_persona=cod_usuRecibeFK) as usuarioRecibe  ,
				(select nombre_persona from persona where cod_persona=cod_UsuEnviaFK) as usuarioEnvia from migrar_caja where estado=? and cod_UsuEnviaFK='".$user."' ".$condicionfecha.$condicionrecibe." order by idmigrar_caja desc limit 50";
		
   // echo($sql);
   // exit;
   
   $stmt = $mysqli->prepare($sql);
  	$s='s';
$stmt->bind_param($s,$estado);

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
		  
		      $idmigrar_caja=$valor['idmigrar_caja'];
		  	  $obs=utf8_encode($valor['obs']);
		  	  $fecha=utf8_encode($valor['fecha']);
			  
			  $monto=$valor['monto'];
		  	  $cod_caja_desdeFK=utf8_encode($valor['cod_caja_desdeFK']);
		  	  $cod_caja_hastaFK=utf8_encode($valor['cod_caja_hastaFK']);
			  $estado=utf8_encode($valor['estado']);
			  $tipo=utf8_encode($valor['tipo']);
			  $cod_usuRecibeFK=utf8_encode($valor['cod_usuRecibeFK']);
			  $cod_UsuEnviaFK=utf8_encode($valor['cod_UsuEnviaFK']);
			  
			  $usuarioRecibe=utf8_encode($valor['usuarioRecibe']);
			  $usuarioEnvia=utf8_encode($valor['usuarioEnvia']);
			  
$Evento="";
if($cod_caja_hastaFK=="0" ){
	$Evento="onclick='obtenerdatosabmMigrarCaja(this)'";
}else{
	$Evento=" ";
}  	  	 
		  	 
			  $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' $Evento  >
<td id='td_id' style='width:10%; background-color: #efeded;color:red'>".$idmigrar_caja."</td>
<td  id='td_datos_1' style='width:20%'>".$fecha."</td>
<td  id='td_datos_2' style='width:20%'>".number_format($monto,'0',',','.')."</td>
<td  id='td_datos_3' style='width:30%'>".$obs."</td>
<td  id='td_datos_4' style='width:20%'>".$usuarioRecibe."</td>
<td  id='td_datos_5' style='display:none'>".$cod_usuRecibeFK."</td>
<td  id='td_datos_6' style='display:none'>".$estado."</td>
</tr>
</table>";			  
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}

function buscaroptionUsu()
{
	 
		$sql= "Select * from persona inner join usuario u on cod_persona=cod_usuario inner join local l on cod_localFK=cod_local where u.estado='Activo' and l.estado='Activo' ";
	 	
	$mysqli=conectar_al_servidor();
	
		
		 $pagina="";  

   
   
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
		  
		  
		      $cod_usuario=$valor['cod_usuario'];
		  	  $nombre_persona=utf8_encode($valor['nombre_persona']);
		  	  $estado=utf8_encode($valor['estado']);
		  	 
		  	 
			    	
			  $pagina.="<option  value='$cod_usuario' >".$nombre_persona."</option>";   
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}




verificar($operacion);
?>