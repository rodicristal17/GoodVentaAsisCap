<?php


$funt = $_POST['funt'];
$funt = utf8_decode($funt);

//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include("classTable.php");
include('quitarseparadormiles.php');
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


	
if($funt=="nuevo" || $funt=="editar")
{
	
$idAbmCheque=$_POST['idAbmCheque'];
    $idAbmCheque = utf8_decode($idAbmCheque);
	
$fechaemi=$_POST['fechaemi'];
    $fechaemi = utf8_decode($fechaemi);

$fechaven=$_POST['fechaven'];
    $fechaven = utf8_decode($fechaven);

$nroCheque=$_POST['nroCheque'];
    $nroCheque = utf8_decode($nroCheque);

$orden=$_POST['orden'];
    $orden = utf8_decode($orden);

$concepto=$_POST['concepto'];
    $concepto = utf8_decode($concepto);

$importe=$_POST['importe'];
    $importe = quitarseparadormiles($importe);

$banco=$_POST['banco'];
    $banco = utf8_decode($banco);
	
$estado=$_POST['estado'];
    $estado = utf8_decode($estado);
	
$pagado=$_POST['pagado'];
    $pagado = utf8_decode($pagado);

	abm($idAbmCheque,$pagado,$fechaemi,$fechaven,$nroCheque,$orden,$concepto,$importe,$banco,$estado,$funt);
	


}

if($funt=="buscar")
{
	$fechaEmi=$_POST['fechaEmi'];
$fechaEmi = utf8_decode($fechaEmi);
	$NroCheque=$_POST['NroCheque'];
$NroCheque = utf8_decode($NroCheque);
	$fechaven=$_POST['fechaven'];
$fechaven = utf8_decode($fechaven);
	$orden=$_POST['orden'];
$orden = utf8_decode($orden);
	$concepto=$_POST['concepto'];
$concepto = utf8_decode($concepto);
	$pago=$_POST['pago'];
$pago = utf8_decode($pago);
	$banco=$_POST['banco'];
$banco = utf8_decode($banco);
	$Fecha1=$_POST['Fecha1'];
$Fecha1 = utf8_decode($Fecha1);
	$Fecha2=$_POST['Fecha2'];
$Fecha2 = utf8_decode($Fecha2);
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
		  	  $fecemi=utf8_encode($valor['fecemi']);
		  	  $nroche=utf8_encode($valor['nroche']);
			  $fecven=$valor['fecven'];
		  	  $orden=utf8_encode($valor['orden']);
		  	  $concep=utf8_encode($valor['concep']);
			  $importe=$valor['importe'];
		  	  $pagado=utf8_encode($valor['pagado']);
		  	  $cod_bancoFK=utf8_encode($valor['cod_bancoFK']);
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
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  // $Estado=utf8_encode($valor['Estado']);
		  	 
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