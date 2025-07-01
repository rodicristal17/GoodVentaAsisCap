<?php
$funt = $_POST['funt'];
$funt = utf8_decode($funt);

//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include('quitarseparadormiles.php');
include("classTable.php");


function verificar($funt)
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
    $cod_niveles = utf8_decode($cod_niveles);
	$nombre=$_POST['nombre'];
    $nombre = utf8_decode($nombre);
	$estado=$_POST['estado'];
    $estado = utf8_decode($estado);
	abm($cod_niveles,$nombre,$estado,$funt);

}

if($funt=="buscar")
{
$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
buscar($buscar,$estado);

}

if($funt=="buscardetalles")
{
$idAbmListaNiveles=$_POST['idAbmListaNiveles'];
$idAbmListaNiveles = utf8_decode($idAbmListaNiveles);
$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
buscardetalles($idAbmListaNiveles,$buscar);

}

if($funt=="editaracceso")
{
$iddetallesniveles=$_POST['idabm'];
$iddetallesniveles = utf8_decode($iddetallesniveles);
$acciones=$_POST['acciones'];
$acciones = utf8_decode($acciones);
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


    mysqli_close($mysqli); 

    $informacion =array("1" => "exito");
    echo json_encode($informacion);	
    exit;
	
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
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		  
		      $cod_niveles=$valor['cod_niveles'];
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $estado=utf8_encode($valor['estado']);
		  	
		  	
			  $styleName=CargarStyleTable($styleName);
			  $pagina.="<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
			  <tr id='tbSelecRegistro' onclick='ObtenerdatosAbmListaNiveles(this)'>
			  <td id='td_id' style='display:none;'>".$cod_niveles."</td>
			   <td  id='td_datos_1' style='width:60%' >".$nombre."</td>
			  <td  id='td_datos_2' style='display:none' >".$estado."</td>
			  </tr>
			  </table>";
			    	 
		  	
			  
			  
	  }
	  
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
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		  
		     $iddetallesniveles=$valor['iddetallesniveles'];
		  	  $nro=utf8_encode($valor['nro']);
		  	  $formulario=utf8_encode($valor['formulario']);
		  	  $codigo=utf8_encode($valor['codigo']);
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $accion=utf8_encode($valor['accion']);
		  	 
			   $tituloacceso="";
			 if($controltitulo!=$formulario){
				   $tituloacceso="<p class='ptituloZ'>".$formulario."</p>";
				   $controltitulo=$formulario;
			 }
		  	 $inputcheck="<input id='".$iddetallesniveles."' type='checkbox' onclick='abmaccesolistanivel(this)'  />";
			 if($accion=="SI"){
			$inputcheck="<input id='".$iddetallesniveles."' type='checkbox'  checked onclick='abmaccesolistanivel(this)' />";
          		
			 }
			    	 
$styleName=CargarStyleTable($styleName);		  	  
$pagina.=$tituloacceso."
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
 <tr id='tbSelecRegistro' >
<td  id='td_datos_7' style='width:70%;text-align:left;padding-left:10px' >".$nombre."</td>
<td id='td_datos_2' style='width:20%'>".$inputcheck."</td>
</tr>
</table>";
			    	 
		  	
			  
			  
	  }
	  
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
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $tipo=utf8_encode($valor['tipo']);
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