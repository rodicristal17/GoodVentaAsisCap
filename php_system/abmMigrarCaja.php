<?php

$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');

include("buscar_nivel.php");
require("conexion.php");
require_once("solicitud_eliminado_helper.php");
include("verificar_navegador.php");
include("classTable.php");
include('quitarseparadormiles.php');

function verificar($operacion)
{
	
 $user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');	
if($user!=""){

	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = mb_convert_encoding((string)($navegador), 'ISO-8859-1', 'UTF-8');
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
$cod_MigrarCaja = mb_convert_encoding((string)($cod_MigrarCaja), 'ISO-8859-1', 'UTF-8');

	$monto=$_POST['monto'];
$monto = quitarseparadormiles($monto);

	$obs=$_POST['obs'];
$obs = mb_convert_encoding((string)($obs), 'ISO-8859-1', 'UTF-8');

	$usu_RecibirFK=$_POST['usu_RecibirFK'];
$usu_RecibirFK = mb_convert_encoding((string)($usu_RecibirFK), 'ISO-8859-1', 'UTF-8');

	$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');	

	$user=$_POST['useru'];
$user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');	

	$cod_cajaApertura=$_POST['cod_cajaApertura'];
$cod_cajaApertura = mb_convert_encoding((string)($cod_cajaApertura), 'ISO-8859-1', 'UTF-8');	


if($operacion=="editar")
{
	registrarSolicitudEliminacionGenerica(
		"migrar_caja",
		"idmigrar_caja",
		$cod_MigrarCaja,
		"Solicitud automatica por edicion de migracion de caja.",
		$user,
		"archivo: abmMigrarCaja.php | funcion: verificar | funt: editar | idmigrar_caja: ".$cod_MigrarCaja." | monto: ".$monto." | usu_RecibirFK: ".$usu_RecibirFK." | estado: ".$estado." | cod_cajaApertura: ".$cod_cajaApertura
	);
}

	abm($cod_MigrarCaja,$monto,$obs,$usu_RecibirFK,$estado,$user,$cod_cajaApertura,$operacion);

}




if($operacion=="buscar")
{
	$fecha=$_POST['fecha'];
$fecha = mb_convert_encoding((string)($fecha), 'ISO-8859-1', 'UTF-8');
$recibe=$_POST['recibe'];
$recibe = mb_convert_encoding((string)($recibe), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');

$user=$_POST['useru'];
$user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');	
	buscar($fecha,$recibe,$user,$estado);

}	


if($operacion=="buscaroptionUsu")
{

	buscaroptionUsu();

}	




if($operacion=="BuscarAbmCajaEscritorio")
{

$user=$_POST['useru'];
$user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');	
	BuscarAbmCajaEscritorio( $user,"Activo");

}	


if($operacion=="nuevoCajaEscritorio")
{

$idabm=$_POST['idabm'];
$idabm = mb_convert_encoding((string)($idabm), 'ISO-8859-1', 'UTF-8');	

$codApertura=$_POST['codApertura'];
$codApertura = mb_convert_encoding((string)($codApertura), 'ISO-8859-1', 'UTF-8');	
	registrarSolicitudEliminacionGenerica(
		"migrar_caja",
		"idmigrar_caja",
		$idabm,
		"Solicitud automatica por asignacion de caja destino.",
		$user,
		"archivo: abmMigrarCaja.php | funcion: verificar | funt: nuevoCajaEscritorio | idmigrar_caja: ".$idabm." | codApertura: ".$codApertura
	);
	nuevoCajaEscritorio( $idabm,$codApertura);

}	




if($operacion=="buscarInforme")
{
	$fecha=$_POST['fecha'];
$fecha = mb_convert_encoding((string)($fecha), 'ISO-8859-1', 'UTF-8');
$recibe=$_POST['recibe'];
$recibe = mb_convert_encoding((string)($recibe), 'ISO-8859-1', 'UTF-8');
$usuario=$_POST['usuario'];
$usuario = mb_convert_encoding((string)($usuario), 'ISO-8859-1', 'UTF-8');

$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');

$tipo=$_POST['tipo'];
$tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');	
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
		  	  $obs=mb_convert_encoding((string)($valor['obs']), 'UTF-8', 'ISO-8859-1');
		  	  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
			  
			  $monto=$valor['monto']; 
			  
			  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
			  
			  $usuarioRecibe=mb_convert_encoding((string)($valor['usuarioRecibe']), 'UTF-8', 'ISO-8859-1');
			  $usuarioEnvia=mb_convert_encoding((string)($valor['usuarioEnvia']), 'UTF-8', 'ISO-8859-1');
   	 
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
		  	  $obs=mb_convert_encoding((string)($valor['obs']), 'UTF-8', 'ISO-8859-1');
		  	  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
			  
			  $monto=$valor['monto'];
		  	  $cod_caja_desdeFK=mb_convert_encoding((string)($valor['cod_caja_desdeFK']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_caja_hastaFK=mb_convert_encoding((string)($valor['cod_caja_hastaFK']), 'UTF-8', 'ISO-8859-1');
			  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
			  $tipo=mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');
			  $cod_usuRecibeFK=mb_convert_encoding((string)($valor['cod_usuRecibeFK']), 'UTF-8', 'ISO-8859-1');
			  $cod_UsuEnviaFK=mb_convert_encoding((string)($valor['cod_UsuEnviaFK']), 'UTF-8', 'ISO-8859-1');
			  
			  $usuarioRecibe=mb_convert_encoding((string)($valor['usuarioRecibe']), 'UTF-8', 'ISO-8859-1');
			  $usuarioEnvia=mb_convert_encoding((string)($valor['usuarioEnvia']), 'UTF-8', 'ISO-8859-1');
			  
 
		  	 
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
		  	  $obs=mb_convert_encoding((string)($valor['obs']), 'UTF-8', 'ISO-8859-1');
		  	  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
			  
			  $monto=$valor['monto'];
		  	  $cod_caja_desdeFK=mb_convert_encoding((string)($valor['cod_caja_desdeFK']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_caja_hastaFK=mb_convert_encoding((string)($valor['cod_caja_hastaFK']), 'UTF-8', 'ISO-8859-1');
			  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
			  $tipo=mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');
			  $cod_usuRecibeFK=mb_convert_encoding((string)($valor['cod_usuRecibeFK']), 'UTF-8', 'ISO-8859-1');
			  $cod_UsuEnviaFK=mb_convert_encoding((string)($valor['cod_UsuEnviaFK']), 'UTF-8', 'ISO-8859-1');
			  
			  $usuarioRecibe=mb_convert_encoding((string)($valor['usuarioRecibe']), 'UTF-8', 'ISO-8859-1');
			  $usuarioEnvia=mb_convert_encoding((string)($valor['usuarioEnvia']), 'UTF-8', 'ISO-8859-1');
			  
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
	 
		$sql= "Select * from persona inner join usuario u on cod_persona=cod_usuario inner join local l on cod_localFK=cod_local where u.estado='Activo' and u.tipo='ADMINISTRATIVO' and l.estado='Activo' ";
	 	
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
		  	  $nombre_persona=mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	 
		  	 
			    	
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
