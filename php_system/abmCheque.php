<?php


$funt = $_POST['funt'];
$funt = mb_convert_encoding((string)($funt), 'ISO-8859-1', 'UTF-8');

//cargar achivos importantes
require("conexion.php");
require_once("solicitud_eliminado_helper.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include("classTable.php");
include('quitarseparadormiles.php');
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


	
if($funt=="nuevo" || $funt=="editar")
{
	
$idAbmCheque=$_POST['idAbmCheque'];
    $idAbmCheque = mb_convert_encoding((string)($idAbmCheque), 'ISO-8859-1', 'UTF-8');
	
$fechaemi=$_POST['fechaemi'];
    $fechaemi = mb_convert_encoding((string)($fechaemi), 'ISO-8859-1', 'UTF-8');

$fechaven=$_POST['fechaven'];
    $fechaven = mb_convert_encoding((string)($fechaven), 'ISO-8859-1', 'UTF-8');

$nroCheque=$_POST['nroCheque'];
    $nroCheque = mb_convert_encoding((string)($nroCheque), 'ISO-8859-1', 'UTF-8');

$orden=$_POST['orden'];
    $orden = mb_convert_encoding((string)($orden), 'ISO-8859-1', 'UTF-8');

$concepto=$_POST['concepto'];
    $concepto = mb_convert_encoding((string)($concepto), 'ISO-8859-1', 'UTF-8');

$importe=$_POST['importe'];
    $importe = quitarseparadormiles($importe);

$banco=$_POST['banco'];
    $banco = mb_convert_encoding((string)($banco), 'ISO-8859-1', 'UTF-8');
	
$estado=$_POST['estado'];
    $estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	
$pagado=$_POST['pagado'];
    $pagado = mb_convert_encoding((string)($pagado), 'ISO-8859-1', 'UTF-8');

	abm($idAbmCheque,$pagado,$fechaemi,$fechaven,$nroCheque,$orden,$concepto,$importe,$banco,$estado,$funt);
	


}

if($funt=="buscar")
{
	$fechaEmi=$_POST['fechaEmi'];
$fechaEmi = mb_convert_encoding((string)($fechaEmi), 'ISO-8859-1', 'UTF-8');
	$NroCheque=$_POST['NroCheque'];
$NroCheque = mb_convert_encoding((string)($NroCheque), 'ISO-8859-1', 'UTF-8');
	$fechaven=$_POST['fechaven'];
$fechaven = mb_convert_encoding((string)($fechaven), 'ISO-8859-1', 'UTF-8');
	$orden=$_POST['orden'];
$orden = mb_convert_encoding((string)($orden), 'ISO-8859-1', 'UTF-8');
	$concepto=$_POST['concepto'];
$concepto = mb_convert_encoding((string)($concepto), 'ISO-8859-1', 'UTF-8');
	$pago=$_POST['pago'];
$pago = mb_convert_encoding((string)($pago), 'ISO-8859-1', 'UTF-8');
	$banco=$_POST['banco'];
$banco = mb_convert_encoding((string)($banco), 'ISO-8859-1', 'UTF-8');
	$Fecha1=$_POST['Fecha1'];
$Fecha1 = mb_convert_encoding((string)($Fecha1), 'ISO-8859-1', 'UTF-8');
	$Fecha2=$_POST['Fecha2'];
$Fecha2 = mb_convert_encoding((string)($Fecha2), 'ISO-8859-1', 'UTF-8');
	buscar($fechaEmi,$NroCheque,$fechaven,$orden,$concepto,$pago,$banco,$Fecha1,$Fecha2);
	
}	

if($funt=="buscarOption")
{

	buscarOption();

}	


}

function abm($idAbmCheque,$pagado,$fechaemi,$fechaven,$nroCheque,$orden,$concepto,$importe,$banco,$estado,$funt)
{
	
	if($importe=="" ){
$informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
	}

	$mysqli=conectar_al_servidor();

if($pagado=="PENDIENTE"){
	$pagado="0";
}else{
	$pagado="1";
}

	if($funt=="nuevo")
	{
	
	
    
    $consulta="insert into cheque ( fecemi, nroche, fecven, orden, concep, importe, pagado, cod_bancoFK,estado) values (?,?,?,upper(?),upper(?),?,?,?,?)";	
     $stmt = $mysqli->prepare($consulta);
    $ss='sssssssss';
    $stmt->bind_param($ss,$fechaemi,$nroCheque,$fechaven,$orden,$concepto,$importe,$pagado,$banco,$estado); 
        
 
	}
	if($funt=="editar")
	{
    
    $consulta="Update cheque set fecemi='$fechaemi', nroche='$nroCheque', fecven='$fechaven', orden=upper('$orden'), concep=upper('$concepto'), importe=$importe, pagado=$pagado, cod_bancoFK=$banco,estado='$estado' where idcheque=$idAbmCheque";	

	$stmt = $mysqli->prepare($consulta);
	
	// echo($consulta);
	// exit;

       
	}
	
if ( ! $stmt->execute() ) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}
function buscar($fechaEmi,$NroCheque,$fechaven,$orden,$concepto,$pago,$banco,$Fecha1,$Fecha2)
{
	$mysqli=conectar_al_servidor();
	
		 $condicionfechaEmi=" ";
		if($fechaEmi!=""){
			$condicionfechaEmi=" and fecemi='$fechaEmi'  "; 
		 }
		 
		 $condicionNroCheque="";
		 if($NroCheque!=""){
			$condicionNroCheque=" and nroche='$NroCheque' "; 
		 }
		 
		 $condicionfechaven="";
		 if($fechaven!=""){
			$condicionfechaven=" and fecven='$fechaven' "; 
		 }		 
		 
		 $condicionorden="";
		 if($orden!=""){
			$condicionorden=" and orden like '%$orden%' "; 
		 }
		 
		 $condicionconcepto="";
		 if($concepto!=""){
			$condicionconcepto=" and concep like '%$concepto%' "; 
		 }
		 
		 $condicionpago="";
		 if($pago!=""){
			 if($pago=="PAGADO"){
				 $condicionpago=" and pagado='1' ";
			 }else{
				 $condicionpago=" and pagado='0' ";
			 }
			 
		 }
		 $condicionbanco="";
		 if($banco!=""){
			$condicionbanco=" and cod_bancoFK = '".$banco."' "; 
		 }
		 $condicionrangofechas="";
		 if($Fecha1!="" && $Fecha2!="" ){
			$condicionrangofechas=" and fecven between '$Fecha1' and '$Fecha2' "; 
		 }
	
	
	 $pagina='';
		$sql= "Select idcheque, fecemi, nroche, fecven, orden, concep, importe, pagado, cod_bancoFK , estado,
		(select nombre from banco where cod_bancoFK=idbanco) as banco
        from cheque where  estado='Activo' ".$condicionfechaEmi.$condicionNroCheque.$condicionfechaven.$condicionorden.$condicionconcepto.$condicionpago.$condicionbanco.$condicionrangofechas." order by fecven desc limit 500 ";
		
		// echo($pago);
		// exit;

   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 $styleName="tableRegistroSearch";
 
 $totalImporte=0;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		      $idcheque=$valor['idcheque'];
		  	  $fecemi=mb_convert_encoding((string)($valor['fecemi']), 'UTF-8', 'ISO-8859-1');
		  	  $nroche=mb_convert_encoding((string)($valor['nroche']), 'UTF-8', 'ISO-8859-1');
			  $fecven=$valor['fecven'];
		  	  $orden=mb_convert_encoding((string)($valor['orden']), 'UTF-8', 'ISO-8859-1');
		  	  $concep=mb_convert_encoding((string)($valor['concep']), 'UTF-8', 'ISO-8859-1');
			  $importe=$valor['importe'];
		  	  $pagado=mb_convert_encoding((string)($valor['pagado']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_bancoFK=mb_convert_encoding((string)($valor['cod_bancoFK']), 'UTF-8', 'ISO-8859-1');
			  $banco=$valor['banco'];
			  $estado=$valor['estado'];
	if($pagado=="0"){
		$pagado="PENDIENTE";
	}else{
		$pagado="PAGADO";
	}

if($pagado=="PENDIENTE"){
	 $totalImporte= $totalImporte + $importe;
}	
	
		  	 $styleName=CargarStyleTable($styleName);
			  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='ObtenerdatosAbmCheque(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idcheque."</td>
<td  id='td_datos_1' style='width:10%'>".$fecemi."</td>
<td  id='td_datos_2' style='width:10%'>".$nroche."</td>
<td  id='td_datos_3' style='width:10%'>".$fecven."</td>
<td  id='td_datos_4' style='width:15%'>".$orden."</td>
<td  id='td_datos_5' style='width:15%'>".$concep."</td>
<td  id='td_datos_6' style='width:10%'>". number_format($importe,'0',',','.')."</td>
<td  id='td_datos_7' style='width:10%'>".$pagado."</td>
<td  id='td_datos_8' style='width:15%'>".$banco."</td>
<td  id='td_datos_9' style='display:none'>".$cod_bancoFK."</td>
<td  id='td_datos_10' style='display:none'>".$estado."</td>
</tr>
</table>";
			    	 
		  	
			  
			  
	  }
 }
 
  mysqli_close($mysqli);
  $informacion =array("1" => "exito","2" => $pagina,"3"=> $totalresouesta,"4"=> number_format($totalImporte,'0',',','.'));
echo json_encode($informacion);	
exit;


}
function buscarOption()
{
	$mysqli=conectar_al_servidor();
	 $pagina="<option value='' >TODOS</option>";  
		$sql= "Select idbanco,nombre,estado
        from banco where estado='Activo' order by nombre asc ";
		   
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
		   
		  
		      $idbanco=$valor['idbanco'];
		  	  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
		  	  // $Estado=mb_convert_encoding((string)($valor['Estado']), 'UTF-8', 'ISO-8859-1');
		  	 
			    $pagina.="<option value='$idbanco' >$nombre</option>";
		  	 
	  
	  }
 }
 
  mysqli_close($mysqli);
  $informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;


}






verificar($funt);
?>
