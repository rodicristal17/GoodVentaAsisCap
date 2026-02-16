<?php
$funt = $_POST['funt'];
//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
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




	
if($funt=="editar")
{

$acciones=$_POST['acciones'];
$acciones = mb_convert_encoding((string)($acciones), 'ISO-8859-1', 'UTF-8');
$idAbmUsuario=$_POST['idAbmUsuario'];
$idAbmUsuario = mb_convert_encoding((string)($idAbmUsuario), 'ISO-8859-1', 'UTF-8');
$idabm=$_POST['idabm'];
$idabm = mb_convert_encoding((string)($idabm), 'ISO-8859-1', 'UTF-8');
abm($acciones,$idabm,$funt,$idAbmUsuario);
}

if($funt=="buscar")
{
$buscador=$_POST['buscador'];
$buscador = mb_convert_encoding((string)($buscador), 'ISO-8859-1', 'UTF-8');
$buscar=$_POST['buscar'];
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
buscar($buscar,$buscador);
}

}

function abm($acciones,$idabm,$funt,$user)
{
	
if( $idabm=="" ){
$informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
}

	$mysqli=conectar_al_servidor();

	if($funt=="editar")
	{
	$consulta="Update accesosuser set accion=? where idaccesosUser=?";	

	$stmt = $mysqli->prepare($consulta);


$ss='ss';

$stmt->bind_param($ss,$acciones,$idabm); 

	}
	
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$porcentaje=ObtenerPorcentaje($user);
  $informacion =array("1" => "exito","2"=>$porcentaje);
echo json_encode($informacion);	
exit;


	
	
	
	
}

function ObtenerPorcentaje($buscar)
{
	$mysqli=conectar_al_servidor();
	$sql= "Select lta.nro,lta.formulario,lta.codigo,lta.nombre,acus.idaccesosUser,acus.accion,acus.usuarios_idusario,lta.formulario
	from accesosuser acus inner join listadodeacceso lta on lta.idlistadodeacceso=acus.idlistadodeaccesoFK
	where usuarios_idusario = ? and acus.tipo='Administrativo' order by lta.nro asc,lta.orden asc";
		
   $nrodeactivos=0;
   $totalactivos=0;
 
   
   $stmt = $mysqli->prepare($sql);
  	$s='s';

$stmt->bind_param($s,$buscar);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalactivos=$valor;
 $controltitulo="";
 

 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  $accion=mb_convert_encoding((string)($valor['accion']), 'UTF-8', 'ISO-8859-1');
		   if($accion=="SI"){
			  $nrodeactivos=$nrodeactivos+1;	
          	
			 }
          		
			 
			    	 		  
	  }
 }
  mysqli_close($mysqli);
 
  $nrodeactivos=($nrodeactivos*100)/$totalactivos;
  return number_format($nrodeactivos,'0',',','.');


}
function buscar($buscar,$buscador)
{
	$mysqli=conectar_al_servidor();
	 $pagina1='';
	 $pagina2='';
	 $pagina3='';
		$sql= "Select lta.nro,lta.formulario,lta.codigo,lta.nombre,acus.idaccesosUser,acus.accion,acus.usuarios_idusario,lta.formulario
		from accesosuser acus inner join listadodeacceso lta on lta.idlistadodeacceso=acus.idlistadodeaccesoFK
		where usuarios_idusario = ? and acus.tipo='Administrativo' and concat(lta.nombre,' ',lta.formulario) like '%".$buscador."%' order by lta.nro asc,lta.orden asc";
		
   $nrodeactivos=0;
   $totalactivos=0;
 
   
   $stmt = $mysqli->prepare($sql);
  	$s='s';

$stmt->bind_param($s,$buscar);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalactivos=$valor;
 $controltitulo="";
$styleName="tableRegistroSearch";

 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idaccesosUser=$valor['idaccesosUser'];
			  $accion=mb_convert_encoding((string)($valor['accion']), 'UTF-8', 'ISO-8859-1');
			  $usuarios_idusario=mb_convert_encoding((string)($valor['usuarios_idusario']), 'UTF-8', 'ISO-8859-1');
			  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
			  $codigo=mb_convert_encoding((string)($valor['codigo']), 'UTF-8', 'ISO-8859-1');
			  $formulario=mb_convert_encoding((string)($valor['formulario']), 'UTF-8', 'ISO-8859-1');
			  $tituloacceso="";
			 if($controltitulo!=$formulario){
				   $tituloacceso="<p class='ptituloZ'>".$formulario."</p>";
				   $controltitulo=$formulario;
			 }
		  	 $inputcheck="<input id='".$idaccesosUser."' type='checkbox' onclick='abmacceso(this)'  />";
			 if($accion=="SI"){
			$inputcheck="<input id='".$idaccesosUser."' type='checkbox'  checked onclick='abmacceso(this)' />";
            $nrodeactivos=$nrodeactivos+1;			
			 }
			    	 
$styleName=CargarStyleTable($styleName);		  	  
$pagina1.=$tituloacceso."
 <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
 <tr id='tbSelecRegistro' >
<td  id='td_datos_7' style='width:70%'>".$nombre."</td>
<td id='td_datos_2' style='width:20%'>".$inputcheck."</td>
</tr>
</table>";
	 
			  
	  }
 }
  mysqli_close($mysqli);
 
  $nrodeactivos=($nrodeactivos*100)/$totalactivos;
 
  $informacion =array("1" => 'exito',"2" => $pagina1,"3"=>number_format($nrodeactivos,'0',',','.'));
echo json_encode($informacion);	
exit;


}


verificar($funt);
?>