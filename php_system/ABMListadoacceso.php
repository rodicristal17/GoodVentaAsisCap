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





	
if($funt=="editarnombreacceso" )
{
	
	
	$idlistadodeacceso=$_POST['idabm'];
    $idlistadodeacceso = utf8_decode($idlistadodeacceso);
	$nombre=$_POST['nombre'];
    $nombre = utf8_decode($nombre);
	editarnombreacceso($idlistadodeacceso,$nombre,$funt);

}
if($funt=="editarnombreformulario" )
{
	
	
	$idlistadodeacceso=$_POST['idabm'];
    $idlistadodeacceso = utf8_decode($idlistadodeacceso);
	$nombre=$_POST['nombre'];
    $nombre = utf8_decode($nombre);
	editarnombreformulario($idlistadodeacceso,$nombre,$funt);

}
if($funt=="editarorden" )
{
	
	
	$idlistadodeacceso=$_POST['idabm'];
    $idlistadodeacceso = utf8_decode($idlistadodeacceso);
	$orden=$_POST['orden'];
    $orden = utf8_decode($orden);
	editarorden($idlistadodeacceso,$orden);

}

if($funt=="addAcceso" )
{
	
	
	$codigo=$_POST['codigo'];
    $codigo = utf8_decode($codigo);
	$nombre=$_POST['nombre'];
    $nombre = utf8_decode($nombre);
	$acceso=$_POST['acceso'];
    $acceso = utf8_decode($acceso);
	$orden=$_POST['orden'];
    $orden = utf8_decode($orden);
	$formulario=$_POST['formulario'];
    $formulario = utf8_decode($formulario);
	addAcceso($codigo,$nombre,$acceso,$orden,$formulario);

}

if($funt=="buscar")
{
$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
buscar($buscar);

}
	

}

function addAcceso($codigo,$nombre,$acceso,$orden,$formulario)
{
	
	if($nombre=="" ){
$informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
	}

$mysqli=conectar_al_servidor();
$consulta="insert into listadodeacceso (nro,codigo,formulario, nombre,accion,tipo) values (?,?,?,?,?,'Administrativo')";	
$stmt = $mysqli->prepare($consulta);
$ss='sssss';
$stmt->bind_param($ss,$orden,$codigo,$formulario,$nombre,$acceso); 
       
	
if ( ! $stmt->execute() ) {
	$informacion =array("1" => "error");
	echo json_encode($informacion);	
	exit;
}
$idlistadodeacceso=buscarultimaidacceso($formulario);
 buscaraccesosaddListNiveles($idlistadodeacceso,$acceso);
 buscarusuariosaccesos($idlistadodeacceso);
mysqli_close($mysqli); 
 $informacion =array("1" => "exito");
 echo json_encode($informacion);	
 exit;
	
}

function buscarultimaidacceso($formulario)
{
	$mysqli=conectar_al_servidor();
	 $idlistadodeacceso='';
	
		$sql= "Select idlistadodeacceso
        from listadodeacceso where formulario='$formulario' and tipo='Administrativo' order by idlistadodeacceso desc limit 1 ";
		 
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
	  }
	  
 }
 
  mysqli_close($mysqli); 
return $idlistadodeacceso;

}

function buscaraccesosaddListNiveles($idlistadodeacceso,$accion)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	
		$sql= "Select cod_niveles
        from listado_niveles where tipo='Administrativo' and estado='Activo' order by cod_niveles asc";
		 
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
	   insertaracceso($idlistadodeacceso,$cod_niveles,$accion);
	  }	  
 }
 
  mysqli_close($mysqli); 

}

function insertaracceso($idlistadodeacceso,$cod_nivelesfk,$accion)
{
	$mysqli=conectar_al_servidor();
    $consulta="insert into detallesniveles (cod_nivelesfk, idlistadodeacceso,accion) values (?,?,?)";	
	
     $stmt = $mysqli->prepare($consulta);
    $ss='sss';
    $stmt->bind_param($ss,$cod_nivelesfk,$idlistadodeacceso,$accion);  
	
if ( ! $stmt->execute() ) {
	$informacion =array("1" => "error");
	echo json_encode($informacion);	
	exit;
}

 mysqli_close($mysqli); 
	
}

function buscarusuariosaccesos($idlistadodeacceso)
{
	$mysqli=conectar_al_servidor();	
	$sql= "Select cod_usuario, Acceso
    from usuario where estado='Activo'";
     

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
			  
	 $cod_usuario=$valor['cod_usuario'];
	 $Acceso=$valor['Acceso'];			  
	 generadordeaccesos($Acceso,$cod_usuario,$idlistadodeacceso);
		
	  }
 } 
  mysqli_close($mysqli); 

}

function generadordeaccesos($cod_nivelesFk,$usuarios_idusario,$idlistadodeacceso)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 // EliminarAccesos($usuarios_idusario);
		$sql= "Select dts.iddetallesniveles,lta.nro,lta.formulario,lta.codigo,lta.nombre,dts.accion ,lta.idlistadodeacceso
        from listado_niveles lts inner join detallesniveles dts on dts.cod_nivelesfk=lts.cod_niveles 
		inner join listadodeacceso lta on lta.idlistadodeacceso=dts.idlistadodeacceso
		where cod_nivelesfk='".$cod_nivelesFk."' and  dts.idlistadodeacceso='".$idlistadodeacceso."' order by lta.nro asc";
		
	 
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
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		  
		     $iddetallesniveles=$valor['iddetallesniveles'];
		  	  $nro=utf8_encode($valor['nro']);
		  	  $formulario=utf8_encode($valor['formulario']);
		  	  $codigo=utf8_encode($valor['codigo']);
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $accion=utf8_encode($valor['accion']);
		  	  $idlistadodeacceso=utf8_encode($valor['idlistadodeacceso']);		  	 
			  guardaraccesouser($idlistadodeacceso,$accion,$usuarios_idusario);
			    	 
		  	
			  
			  
	  }
	  
 }
 
  mysqli_close($mysqli); 


}

function guardaraccesouser($idlistadodeaccesoFK,$accion,$usuarios_idusario){

	$mysqli=conectar_al_servidor();
	
	
	
				$consulta= "Select count(*) from accesosuser where idlistadodeaccesoFK='$idlistadodeaccesoFK'  and tipo='Administrativo' and usuarios_idusario='$usuarios_idusario'  ";
	
	
		$stmt = $mysqli->prepare($consulta);

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
if($valor==0){
	$consulta="INSERT INTO accesosuser (idlistadodeaccesoFK,tipo,usuarios_idusario,accion) VALUES ('$idlistadodeaccesoFK','Administrativo','$usuarios_idusario','$accion')";	

	$stmt = $mysqli->prepare($consulta);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

}

	 mysqli_close($mysqli); 
	
}

function EliminarAccesos($usuarios_idusario){
	
	$mysqli=conectar_al_servidor();
	$consulta="Delete from accesosuser where usuarios_idusario='$usuarios_idusario' and  tipo='Administrativo'";	
	$stmt = $mysqli->prepare($consulta);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
	
	 mysqli_close($mysqli); 
}



function editarnombreacceso($idlistadodeacceso,$nombre,$funt)
{
	
	if($nombre=="" ){
$informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
	}

	$mysqli=conectar_al_servidor();

    $consulta="Update listadodeacceso set nombre=upper(?) where idlistadodeacceso=?";	
	$stmt = $mysqli->prepare($consulta);
    $ss='ss';        
    $stmt->bind_param($ss,$nombre,$idlistadodeacceso); 
       
	
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

function editarorden($idlistadodeacceso,$orden)
{
	
	if($orden=="" ){
$informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
	}

	$mysqli=conectar_al_servidor();

    $consulta="Update listadodeacceso set orden=? where idlistadodeacceso=?";	
	$stmt = $mysqli->prepare($consulta);
    $ss='ss';        
    $stmt->bind_param($ss,$orden,$idlistadodeacceso); 
       
	
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

function editarnombreformulario($formulario,$nombre,$funt)
{
	
	if($nombre=="" ){
$informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
	}

	$mysqli=conectar_al_servidor();

    $consulta="Update listadodeacceso set formulario=upper(?) where formulario=?";	
	$stmt = $mysqli->prepare($consulta);
    $ss='ss';        
    $stmt->bind_param($ss,$nombre,$formulario); 
       
	
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

function buscar($buscar)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	
		$sql= "Select idlistadodeacceso,nro,formulario,codigo,nombre,accion,tipo,orden
        from listadodeacceso where concat(nombre,' ',formulario) like ?  and tipo='Administrativo' order by nro asc,orden asc ";
		 
   $stmt = $mysqli->prepare($sql);
  	$s='s';
$buscar1="%".$buscar."%";
//$buscar="".$buscar."";
$stmt->bind_param($s,$buscar1);

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
		  
		  
		  
		      $idlistadodeacceso=$valor['idlistadodeacceso'];
		  	  $nro=utf8_encode($valor['nro']);
		  	  $formulario=utf8_encode($valor['formulario']);
		  	  $codigo=utf8_encode($valor['codigo']);
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $accion=utf8_encode($valor['accion']);
		  	  $tipo=utf8_encode($valor['tipo']);
		  	  $orden=utf8_encode($valor['orden']);
			  $tituloPagina="";
		  	  if($controltitulo==""){
				   $tituloPagina="<p class='ptituloZ'>".$formulario."</p>"  ;
				  $controltitulo=$formulario;
			  }
			  if($controltitulo!=$formulario){
				 $tituloPagina="<p class='ptituloZ'>".$formulario."</p>"  ;
				 $controltitulo=$formulario;
			  }
		  	$orden="<input onkeyup='if(event.keyCode === 13){guardarordenlistadoacceso(this)}'  value='$orden' style='width: 80px;text-align: center;' type='text'  class='input3' id='$idlistadodeacceso'  />";
			
			  $styleName=CargarStyleTable($styleName);
			  $pagina.=$tituloPagina."<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
			  <tr id='tbSelecRegistro' onclick='ObtenerdatosAbmListaAccesos(this)'>
			  <td id='td_id' style='display:none;'>".$idlistadodeacceso."</td>
			   <td  style='width:10%' >".$orden."</td>
			   <td  id='td_datos_1' style='width:90%;text-align:left;padding-left:10px' >".$nombre."</td>
			  <td  id='td_datos_2' style='display:none' >".$formulario."</td>
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

		$sql= "Select cod_listafacultad,nombre,estado
        from listafacultad where  estado='Activo'  order by nombre asc";
		 $pagina="";
   $stmt = $mysqli->prepare($sql);
  
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
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		  
		      $cod_listafacultad=$valor['cod_listafacultad'];
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  
		  	
		  	$pagina.="<option id='$cod_listafacultad' value='$nombre'  ></option>";
		
			    	 
		  	
			  
			  
	  }
	  
 }
 
  mysqli_close($mysqli); 
$informacion =array("1" => "exito","2" => $pagina,"3"=> $totalresouesta);
echo json_encode($informacion);	
exit;


}


verificar($funt);
?>