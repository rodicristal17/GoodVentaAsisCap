<?php
$funt = $_POST['funt'];
$funt = mb_convert_encoding((string)($funt), 'ISO-8859-1', 'UTF-8');

//cargar achivos importantes
require("conexion.php");
require_once("solicitud_eliminado_helper.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include('quitarseparadormiles.php');
include("classTable.php");


function verificar($funt)
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


	
	//CONTROL DE ACCESO
// if($funt=="nuevo"){

	// buscarnivel($user,"FACULTAD"," anhadir='SI' ");
// }
// if($funt=="editar" || $funt=="eliminar"){
	
	// buscarnivel($user,"FACULTAD"," modificar='SI' ");
// }
// if($funt=="buscar"){

	// buscarnivel($user,"FACULTAD"," buscar='SI' ");
// }





	
if($funt=="nuevo" || $funt=="editar")
{
	
	
	$cod_niveles=$_POST['idabm'];
    $cod_niveles = mb_convert_encoding((string)($cod_niveles), 'ISO-8859-1', 'UTF-8');
	$nombre=$_POST['nombre'];
    $nombre = mb_convert_encoding((string)($nombre), 'ISO-8859-1', 'UTF-8');
	$estado=$_POST['estado'];
    $estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	abm($cod_niveles,$nombre,$estado,$funt);

}

if($funt=="buscar")
{
$buscar=$_POST['buscar'];
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
buscar($buscar,$estado);

}

if($funt=="buscardetalles")
{
$idAbmListaNiveles=$_POST['idAbmListaNiveles'];
$idAbmListaNiveles = mb_convert_encoding((string)($idAbmListaNiveles), 'ISO-8859-1', 'UTF-8');
$buscar=$_POST['buscar'];
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
buscardetalles($idAbmListaNiveles,$buscar);

}

if($funt=="editaracceso")
{
$iddetallesniveles=$_POST['idabm'];
$iddetallesniveles = mb_convert_encoding((string)($iddetallesniveles), 'ISO-8859-1', 'UTF-8');
$acciones=$_POST['acciones'];
$acciones = mb_convert_encoding((string)($acciones), 'ISO-8859-1', 'UTF-8');
editaracceso($iddetallesniveles,$acciones);

}

if($funt=="buscarSelect")
{

buscarSelect();

}





	

}

function abm($cod_niveles,$nombre,$estado,$funt)
{
	
	if($nombre=="" ){
$informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
	}

	$mysqli=conectar_al_servidor();

	if($funt=="nuevo")
	{
				$consulta= "Select count(*) from listado_niveles where nombre=? and tipo='Administrativo' ";
	
	
		$stmt = $mysqli->prepare($consulta);
$ss='s';
$stmt->bind_param($ss,$nombre); 


if ( ! $stmt->execute()) {
	$informacion =array("1" => "error");
	echo json_encode($informacion);	
	exit;
}

$valor = 0;
$stmt->bind_result($valor);
while ($stmt->fetch()) { 
   
	 $valor =$valor;
}

if($valor>0)
{
	$informacion =array("1" => "EX");
	echo json_encode($informacion);	
	exit;
}   
	}
	
	if($funt=="nuevo")
	{
	
    $consulta="insert into listado_niveles (nombre, estado,tipo) values (upper(?),?,'Administrativo')";	
     $stmt = $mysqli->prepare($consulta);
    $ss='ss';
    $stmt->bind_param($ss,$nombre,$estado); 
 
	}
	
	if($funt=="editar")
	{
		if (solicitudEliminadoEsEstadoInactivo($estado)) {
			$user = solicitudEliminadoValorPost('useru', '0');
			$respuesta = registrarSolicitudEliminacionGenerica(
				'listado_niveles',
				'cod_niveles',
				$cod_niveles,
				'Solicitud de eliminacion de nivel.',
				$user,
				'Nivel: '.$nombre
			);
			echo json_encode($respuesta);
			exit;
		}
        
    $consulta="Update listado_niveles set nombre=upper(?),  estado=?  where cod_niveles=?";	
	$stmt = $mysqli->prepare($consulta);
    $ss='sss';        
    $stmt->bind_param($ss,$nombre,$estado,$cod_niveles); 
       
	}
	
if ( ! $stmt->execute() ) {
	$informacion =array("1" => "error");
	echo json_encode($informacion);	
	exit;
}
if($funt=="nuevo")
	{
  $cod_nivelesfk=buscarultimaid();
   buscaracceso($cod_nivelesfk);
	}

    mysqli_close($mysqli); 

    $informacion =array("1" => "exito");
    echo json_encode($informacion);	
    exit;
	
}



function editaracceso($iddetallesniveles,$acciones)
{
	


	$mysqli=conectar_al_servidor();        
    $consulta="Update detallesniveles set accion=?  where iddetallesniveles=? ";	
	$stmt = $mysqli->prepare($consulta);
    $ss='ss';        
    $stmt->bind_param($ss,$acciones,$iddetallesniveles);      

	
if ( ! $stmt->execute() ) {
	$informacion =array("1" => "error");
	echo json_encode($informacion);	
	exit;
}

sincronizarAccesoUsuariosNivel($mysqli, $iddetallesniveles, $acciones);

    mysqli_close($mysqli); 

    $informacion =array("1" => "exito");
    echo json_encode($informacion);	
    exit;
	
}

function sincronizarAccesoUsuariosNivel($mysqli, $iddetallesniveles, $acciones)
{
	$sqlDatos = "SELECT cod_nivelesfk, idlistadodeacceso FROM detallesniveles WHERE iddetallesniveles = ? LIMIT 1";
	$stmtDatos = $mysqli->prepare($sqlDatos);
	if (!$stmtDatos) {
		return;
	}
	$s='s';
	$stmtDatos->bind_param($s,$iddetallesniveles);
	if (!$stmtDatos->execute()) {
		$stmtDatos->close();
		return;
	}
	$result = $stmtDatos->get_result();
	$row = $result->fetch_assoc();
	$stmtDatos->close();
	if (!$row) {
		return;
	}

	$codNiveles = $row['cod_nivelesfk'];
	$idListadoAcceso = $row['idlistadodeacceso'];

	$sqlUpdate = "UPDATE accesosuser acus
		INNER JOIN usuario us ON us.cod_usuario = acus.usuarios_idusario
		SET acus.accion = ?
		WHERE us.Acceso = ?
			AND acus.idlistadodeaccesoFK = ?
			AND acus.tipo = 'Administrativo'";
	$stmtUpdate = $mysqli->prepare($sqlUpdate);
	if ($stmtUpdate) {
		$sss='sss';
		$stmtUpdate->bind_param($sss,$acciones,$codNiveles,$idListadoAcceso);
		$stmtUpdate->execute();
		$stmtUpdate->close();
	}

	$sqlInsert = "INSERT INTO accesosuser (idlistadodeaccesoFK, tipo, usuarios_idusario, accion)
		SELECT ?, 'Administrativo', us.cod_usuario, ?
		FROM usuario us
		LEFT JOIN accesosuser acus ON acus.idlistadodeaccesoFK = ?
			AND acus.tipo = 'Administrativo'
			AND acus.usuarios_idusario = us.cod_usuario
		WHERE us.Acceso = ?
			AND us.estado = 'Activo'
			AND acus.idaccesosUser IS NULL";
	$stmtInsert = $mysqli->prepare($sqlInsert);
	if ($stmtInsert) {
		$ssss='ssss';
		$stmtInsert->bind_param($ssss,$idListadoAcceso,$acciones,$idListadoAcceso,$codNiveles);
		$stmtInsert->execute();
		$stmtInsert->close();
	}
}


function buscarultimaid()
{
	$mysqli=conectar_al_servidor();
	 $cod_niveles='';
	
		$sql= "Select cod_niveles from listado_niveles where tipo='Administrativo' order by cod_niveles desc limit 1 ";
		 
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
		  
		      $cod_niveles=$valor['cod_niveles'];
	  
	  }
	  
 } 
return $cod_niveles;
}

function buscaracceso($cod_nivelesfk)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	
		$sql= "Select idlistadodeacceso
        from listadodeacceso where tipo='Administrativo' order by idlistadodeacceso asc";
		 
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
	   $idlistadodeacceso=$valor['idlistadodeacceso']; 	 			  
	   insertaracceso($idlistadodeacceso,$cod_nivelesfk)  ;
	  }	  
 }
 
  mysqli_close($mysqli); 

}

function insertaracceso($idlistadodeacceso,$cod_nivelesfk)
{
	$mysqli=conectar_al_servidor();
    $consulta="insert into detallesniveles (cod_nivelesfk, idlistadodeacceso,accion) values (?,?,'SI')";	
     $stmt = $mysqli->prepare($consulta);
    $ss='ss';
    $stmt->bind_param($ss,$cod_nivelesfk,$idlistadodeacceso);  
	
if ( ! $stmt->execute() ) {
	$informacion =array("1" => "error");
	echo json_encode($informacion);	
	exit;
}

 mysqli_close($mysqli); 
	
}


function buscar($buscar,$estado)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	
		$sql= "Select cod_niveles,nombre,estado 
        from listado_niveles where nombre like ? and estado=? and tipo='Administrativo'  order by nombre asc";
		 
   $stmt = $mysqli->prepare($sql);
  	$s='ss';
$buscar1="%".$buscar."%";
//$buscar="".$buscar."";
$stmt->bind_param($s,$buscar1,$estado);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$paginaArancel="";
$controltitulo="0";
$totalArancel=-1;
$totales=0;
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 if ($valor>0)
 {
	  $pagina.="<table class='lista-niveles-main-table'>
	  <tbody>";
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		  
		      $cod_niveles=$valor['cod_niveles'];
		  	  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	  $nombreSeguro=htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
		  	  $estadoSeguro=htmlspecialchars($estado, ENT_QUOTES, 'UTF-8');
		  	  $estadoClase=$estado=="Activo" ? "is-active" : "is-inactive";
		  	
			  $pagina.="<tr id='tbSelecRegistro' class='lista-niveles-main-row ".$estadoClase."' onclick='ObtenerdatosAbmListaNiveles(this)'>
			  <td id='td_id' style='display:none;'>".$cod_niveles."</td>
			  <td id='td_datos_1' style='display:none;'>".$nombreSeguro."</td>
			  <td id='td_datos_2' style='display:none;'>".$estadoSeguro."</td>
			  <td class='lista-niveles-main-info'>
			  <span class='lista-niveles-main-name'>".$nombreSeguro."</span>
			  <span class='lista-niveles-main-meta'>Nivel administrativo #".$cod_niveles."</span>
			  </td>
			  <td class='lista-niveles-main-state'><span class='lista-niveles-state-badge'>".$estadoSeguro."</span></td>
			  </tr>";
			    	 
		  	
			  
			  
	  }
	  $pagina.="</tbody></table>";
	  
 }
 
  mysqli_close($mysqli); 
$informacion =array("1" => "exito","2" => $pagina,"3"=> $totalresouesta);
echo json_encode($informacion);	
exit;


}

function buscardetalles($idAbmListaNiveles,$buscar)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	
		$sql= "Select dts.iddetallesniveles,lta.nro,lta.formulario,lta.codigo,lta.nombre,dts.accion 
        from listado_niveles lts inner join detallesniveles dts on dts.cod_nivelesfk=lts.cod_niveles 
		inner join listadodeacceso lta on lta.idlistadodeacceso=dts.idlistadodeacceso
		where cod_nivelesfk='".$idAbmListaNiveles."' and  lta.formulario like '%".$buscar."%' order by lta.nro asc,lta.orden asc";
		 
   $stmt = $mysqli->prepare($sql);
  if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$controltitulo="";
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 
 if ($valor>0)
 {
	  $pagina.="<table class='lista-niveles-acceso-table'>
	  <tbody>";
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		  
		     $iddetallesniveles=$valor['iddetallesniveles'];
		  	  $nro=mb_convert_encoding((string)($valor['nro']), 'UTF-8', 'ISO-8859-1');
		  	  $formulario=mb_convert_encoding((string)($valor['formulario']), 'UTF-8', 'ISO-8859-1');
		  	  $codigo=mb_convert_encoding((string)($valor['codigo']), 'UTF-8', 'ISO-8859-1');
		  	  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $accion=mb_convert_encoding((string)($valor['accion']), 'UTF-8', 'ISO-8859-1');
		  	 
			  $formularioSeguro=htmlspecialchars($formulario, ENT_QUOTES, 'UTF-8');
			  $codigoSeguro=htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8');
			  $nombreSeguro=htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
			  $nroSeguro=htmlspecialchars($nro, ENT_QUOTES, 'UTF-8');
			  $estadoFila=$accion=="SI" ? "is-enabled" : "is-disabled";
			  $checked=$accion=="SI" ? " checked" : "";
			  $textoEstado=$accion=="SI" ? "Permitido" : "Bloqueado";
			  $codigoHtml=$codigoSeguro!="" ? "<span class='lista-niveles-acceso-code'>".$codigoSeguro."</span>" : "";

			   $tituloacceso="";
			 if($controltitulo!=$formulario){
				   $tituloacceso="<tr class='lista-niveles-acceso-group'>
				   <th colspan='2'>".$formularioSeguro."</th>
				   </tr>";
				   $controltitulo=$formulario;
			 }
		  	 $inputcheck="<label class='lista-niveles-switch'>
		  	 <input id='".$iddetallesniveles."' type='checkbox'".$checked." onclick='abmaccesolistanivel(this)' />
		  	 <span class='lista-niveles-switch-track'></span>
		  	 <span class='lista-niveles-switch-text'>".$textoEstado."</span>
		  	 </label>";
			    	  	  	  
$pagina.=$tituloacceso."
<tr id='tbSelecRegistro' class='lista-niveles-acceso-row ".$estadoFila."'>
<td id='td_datos_7' class='lista-niveles-acceso-info'>
<span class='lista-niveles-acceso-name'>".$nombreSeguro."</span>
<span class='lista-niveles-acceso-meta'>Nro. ".$nroSeguro.$codigoHtml."</span>
</td>
<td id='td_datos_2' class='lista-niveles-acceso-action'>".$inputcheck."</td>
</tr>";
			    	 
		  	
			  
			  
	  }
	  $pagina.="</tbody></table>";
	  
 }
 
  mysqli_close($mysqli); 
$informacion =array("1" => "exito","2" => $pagina,"3"=> $totalresouesta);
echo json_encode($informacion);	
exit;


}


function buscarSelect()
{
	$mysqli=conectar_al_servidor();

		$sql= "Select cod_niveles,nombre,estado,tipo
        from listado_niveles where estado='Activo' and (tipo='Administrativo' or tipo='Administrativo') order by nombre asc";
	
		 $pagina="<option value='' >SIN ACCESO</option>";
		 $paginaAdministrativo="<option value='' >SIN ACCESO</option>";
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
		  
		  
		  
		      $cod_niveles=$valor['cod_niveles'];
		  	  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $tipo=mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');
			  if($tipo=="Administrativo"){
		  	   $pagina.="<option value='$cod_niveles' >$nombre</option>";
			  }
			
		  	  
		  
		
			    	 
		  	
			  
			  
	  }
	  
 }
 
  mysqli_close($mysqli); 
$informacion =array("1" => "exito","2" => $pagina,"3"=> $paginaAdministrativo);
echo json_encode($informacion);	
exit;


}


verificar($funt);
?>
