<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);
include_once('quitarseparadormiles.php');
include_once("buscar_nivel.php");
require_once("conexion.php");
include_once("verificar_navegador.php");
include_once("classTable.php");

function verificar($operacion)
{
	
 $user=$_POST['useru'];
    $user = utf8_decode($user);
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




if($operacion=="nuevo" || $operacion=="editar")
{
$idarqueocaja=$_POST['idarqueocaja'];
$idarqueocaja = utf8_decode($idarqueocaja);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$montoapertura=$_POST['montoapertura'];
$montoapertura = quitarseparadormiles($montoapertura);
$montocierre=$_POST['montocierre'];
$montocierre = quitarseparadormiles($montocierre);
$fechaapertura=$_POST['fechaapertura'];
$fechacierre=$_POST['fechacierre'];
$caja_idcaja=$_POST['caja_idcaja'];
$caja_idcaja = utf8_decode($caja_idcaja);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
$codusuarioap=$_POST['codusuarioap'];
$codusuarioap = utf8_decode($codusuarioap);
$codusuarioce = $user;
abm($idarqueocaja,$cod_local,$caja_idcaja,$montoapertura,$montocierre,$fechaapertura,$fechacierre,$estado,$codusuarioap,$codusuarioce,$operacion);

}

if($operacion=="controldecaja")
{
$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$Usuario=$_POST['Usuario'];
$Usuario = utf8_decode($Usuario);

controldecaja($buscar,$cod_local,$Usuario);

}

if($operacion=="buscar_recaudo_opciones_pago")
{
$idArqeoFk1=$_POST['idArqeoFk1'];
$idArqeoFk1 = utf8_decode($idArqeoFk1);

buscar_recaudo_opciones_pago($idArqeoFk1);

}	

if($operacion=="buscarmoviemientocaja")
{
$idArqeoFk=$_POST['idArqeoFk'];
$idArqeoFk = utf8_decode($idArqeoFk);
buscarmoviemientocaja($idArqeoFk);

}	

if($operacion=="buscarvista")
{
$caja=$_POST['caja'];
$caja = utf8_decode($caja);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
$local=$_POST['local'];
$local = utf8_decode($local);
$fechaapertura=$_POST['fechaapertura'];
$fechaapertura = utf8_decode($fechaapertura);
$fechafin=$_POST['fechafin'];
$fechafin = utf8_decode($fechafin);
$usuario=$_POST['usuario'];
$usuario = utf8_decode($usuario);
$lote = $_POST['lote'];
$lote = utf8_decode($lote);

$informacion = buscarvista($fechaapertura,$fechafin,$caja,$estado,$local,$usuario,$lote);
echo json_encode($informacion);	
exit;
}

if($operacion=="buscarcajaapp")
{
$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$cobrador=$_POST['cobrador'];
$cobrador = utf8_decode($cobrador);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
buscarcajaapp($fecha1,$fecha2,$cobrador,$estado);

}	

}

function abm($idarqueocaja,$cod_local,$caja_idcaja,$montoapertura,$montocierre,$fechaapertura,$fechacierre,$estado,$codusuarioap,$codusuarioce,$operacion)
{
	
	
$mysqli=conectar_al_servidor();

if($operacion=="nuevo")
{

$lote="LOTE ".obternerLote($cod_local,$codusuarioap);

$consulta1="Insert into arqueocaja (cod_local,caja_idcaja,montoapertura,fechaapertura,estado,codusuarioap,montocierre,lote)
values(?,?,?,?,?,?,'0',?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssss';
$stmt1->bind_param($ss,$cod_local,$caja_idcaja,$montoapertura,$fechaapertura,$estado,$codusuarioap,$lote);


}


if($operacion=="editar")
{
	// Verificar si tiene moviemientos pendientes de autorizacion
	$sql= "SELECT * FROM gastos WHERE estado='solicitado' AND cod_usuario= $codusuarioce";
	$stmt = $mysqli->prepare($sql);
	if ( ! $stmt->execute()) {
		echo "Error";
		exit;
	}
	$result = $stmt->get_result();
 	$valor= mysqli_num_rows($result);
	if ($valor > 0) {
		echo json_encode(array("1" => "error", "2" => "No se puede cerrar la caja", "3" => "Existen $valor Egresos / Ingresos que necesitan aprobacion."));
		exit;
	}

	// Obtiene los montos en cada tipo de moneda
	$cant500= $_POST['cant500'];
	$cant500= utf8_decode($cant500);
	$cant1000= $_POST['cant1000'];
	$cant1000= utf8_decode($cant1000);
	$cant2000= $_POST['cant2000'];
	$cant2000= utf8_decode($cant2000);
	$cant5000= $_POST['cant5000'];
	$cant5000= utf8_decode($cant5000);
	$cant10000= $_POST['cant10000'];
	$cant10000= utf8_decode($cant10000);
	$cant20000= $_POST['cant20000'];
	$cant20000= utf8_decode($cant20000);
	$cant50000= $_POST['cant50000'];
	$cant50000= utf8_decode($cant50000);
	$cant100000= $_POST['cant100000'];
	$cant100000= utf8_decode($cant100000);
$lote=obternerLoteEdit($idarqueocaja);
$consulta1="Update arqueocaja set codusuarioce=?,montocierre=?,fechacierre=?,estado='Cerrado',cant500= ?,cant1000= ?,cant2000= ?,cant5000= ?,cant10000= ?,cant20000= ?,cant50000= ?,cant100000= ? where idarqueocaja=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssssssss';
$stmt1->bind_param($ss,$codusuarioce,$montocierre,$fechacierre,$cant500,$cant1000,$cant2000,$cant5000,$cant10000,$cant20000,$cant50000,$cant100000,$idarqueocaja); 

}

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
$informacion =array("1" => "exito","2" => $lote);
echo json_encode($informacion);	
exit;
	
}

function obternerLoteEdit($cod)
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select lote 	from arqueocaja where idarqueocaja='$cod'  ";
		

   
   
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $lote= 0;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $lote=$valor['lote'];
		  	  
		  	 			  
	  } 
	
 
 }
 
 return $lote;
 
 

}



function obternerLote($cod_local,$user)
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select count(*) as contador
		from arqueocaja where  cod_local='$cod_local' and codusuarioap='$user'  ";
		

   
   
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $contador= 0;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $contador=$valor['contador'];
		  	  
		  	 			  
	  } 
	
 
 }
 
 return $contador;
 
 

}




function controldecaja($buscar,$cod_local,$user)
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select idarqueocaja, caja_idcaja, montoapertura, montocierre, fechaapertura, fechacierre, estado, codusuarioap, codusuarioce,
		(Select nombre_persona from persona where cod_persona=codusuarioap) as usuarioap
		from arqueocaja where caja_idcaja='$buscar' and estado='Activo' and cod_local='$cod_local' and codusuarioap='$user' ";
		 $pagina="";  

   
   
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalRecaudado= 0;
 
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idarqueocaja=$valor['idarqueocaja'];
		  	  $caja_idcaja=utf8_encode($valor['caja_idcaja']);
		  	  $montoapertura=utf8_encode($valor['montoapertura']);
		  	  $montocierre=utf8_encode($valor['montocierre']);
		  	  $fechaapertura=utf8_encode($valor['fechaapertura']);
		  	  $fechacierre=utf8_encode($valor['fechacierre']);
		  	  $estado=utf8_encode($valor['estado']);
		  	  $codusuarioap=utf8_encode($valor['codusuarioap']);
		  	  $codusuarioce=utf8_encode($valor['codusuarioce']);
		  	  $usuarioap=utf8_encode($valor['usuarioap']);
		  	  $totalRecaudado=ObtenerTotalCaja($idarqueocaja,$montoapertura);
		  	 			  
	  }
	  
	  $informacion =array("1" => "exito","2" =>"1","3"=>$idarqueocaja,"4"=>$caja_idcaja,"5"=>  number_format($montoapertura,'0',',','.')
	  ,"6"=>  number_format($montocierre,'0',',','.'),"7"=>$fechaapertura,"8"=>$fechacierre,"9"=>$estado,"10"=>  number_format($totalRecaudado,'0',',','.')
	  ,"11"=>$codusuarioap ,"12"=>$usuarioap);
echo json_encode($informacion);	
exit;
 }else{
	 $totalRecaudado=obternerultimacajauser($cod_local,$user,$buscar);
	$informacion =array("1" => "exito","2" =>"0","3"=> number_format($totalRecaudado,'0',',','.'));
echo json_encode($informacion);	
exit;
 
 }
  
}

function obternerultimacajauser($cod_local,$user,$buscar)
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select idarqueocaja,montoapertura
		from arqueocaja where caja_idcaja='$buscar' and cod_local='$cod_local' and codusuarioap='$user' order by  idarqueocaja desc limit 1";
		

   
   
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalRecaudado= 0;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idarqueocaja=$valor['idarqueocaja'];
		  	  $montoapertura=utf8_encode($valor['montoapertura']);
		  	  $totalRecaudado=ObtenerTotalCaja($idarqueocaja,$montoapertura);
		  	 			  
	  } 
	
 
 }
 
 return $totalRecaudado;
 
 

}


/*Buscar Registro*/
function buscarvista($fechaapertura,$fechafin,$caja,$estado,$local,$usuario,$lote)
{
$sqlFiltro= "";
	
$mysqli=conectar_al_servidor();

if ($fechaapertura != "") {
    $sqlFiltro.= " AND DATE_FORMAT(fechaapertura, '%Y-%m-%d') >= '$fechaapertura'";
}

if ($fechafin != "") {
    $sqlFiltro.= " AND DATE_FORMAT(fechacierre, '%Y-%m-%d') <= '$fechafin'";
}

if($caja!=""){
$sqlFiltro.=" and (Select cajanro from caja l where l.idcaja=caja_idcaja) like '%".$caja."%'";	
}

if($estado!=""){
$sqlFiltro.=" and estado='$estado' ";	
}

if($local!=""){
$sqlFiltro.=" and ap.cod_local='$local' ";	
}

if($usuario!=""){
$sqlFiltro.=" and (Select nombre_persona from persona where cod_persona=codusuarioap) like '%".$usuario."%'";		
}

if ($lote != "") {
    $sqlFiltro.= " AND lote like '%$lote'";
}

$sql= "Select idarqueocaja, caja_idcaja, montoapertura, montocierre, fechaapertura, fechacierre, estado, codusuarioap, codusuarioce,cod_local,
(Select cajanro from caja l where l.idcaja=caja_idcaja) as cajanro,
(ifnull((Select sum(Monto) from pago where codApertura=idarqueocaja),0)) as cobros,
(ifnull((Select sum(monto) from gastos where codApertura=idarqueocaja and tipo='Egreso'),0)) as egreso,
(ifnull((Select sum(monto) from gastos where codApertura=idarqueocaja and tipo='Ingreso'),0)) as ingreso,
(Select cajanro from caja l where l.idcaja=caja_idcaja) as cajanro,lote,
(Select nombre_persona from persona where cod_persona=codusuarioap) as usuarioap,
(Select nombre_persona from persona where cod_persona=codusuarioce) as usuariocie,
ap.cant500, ap.cant1000, ap.cant2000, ap.cant5000, ap.cant10000, ap.cant20000, ap.cant50000, ap.cant100000,
(Select Nombre from local l where l.cod_local=ap.cod_local) as nombrelocal
from arqueocaja ap where  estado!='Cancelado' ".$sqlFiltro." order by idarqueocaja desc limit 100  ";

$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

$Totaldiferencia = 0;
$TotalApertura = 0;
$TotalCierre = 0;

$TotalIngreso = 0;
$TotalEgreso = 0;
$TotalCobros = 0;

$registros = array();

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$lote = utf8_encode($valor['lote']); 
$idarqueocaja = utf8_encode($valor['idarqueocaja']); 
$caja_idcaja = utf8_encode($valor['caja_idcaja']);          
$montoapertura = utf8_encode($valor['montoapertura']);          
$montocierre = utf8_encode($valor['montocierre']); 
$fechaapertura = utf8_encode($valor['fechaapertura']); 
$fechacierre = utf8_encode($valor['fechacierre']); 
$estado = utf8_encode($valor['estado']); 
$codusuarioap = utf8_encode($valor['codusuarioap']); 
$codusuarioce = utf8_encode($valor['codusuarioce']); 
$cod_local = utf8_encode($valor['cod_local']); 
$nombrelocal = utf8_encode($valor['nombrelocal']); 
$cajanro = utf8_encode($valor['cajanro']); 
$usuarioap = utf8_encode($valor['usuarioap']); 
$usuariocie = utf8_encode($valor['usuariocie']); 

$cant500 = utf8_encode($valor['cant500']);
if ($cant500 == "") {$cant500=0;}
$cant1000 = utf8_encode($valor['cant1000']);
if ($cant1000 == "") {$cant1000=0;}
$cant2000 = utf8_encode($valor['cant2000']);
if ($cant2000 == "") {$cant2000=0;}
$cant5000 = utf8_encode($valor['cant5000']);
if ($cant5000 == "") {$cant5000=0;}
$cant10000 = utf8_encode($valor['cant10000']);
if ($cant10000 == "") {$cant10000=0;}
$cant20000 = utf8_encode($valor['cant20000']);
if ($cant20000 == "") {$cant20000=0;}
$cant50000 = utf8_encode($valor['cant50000']);
if ($cant50000 == "") {$cant50000=0;}
$cant100000 = utf8_encode($valor['cant100000']);
if ($cant100000 == "") {$cant100000=0;}

$cobros = utf8_encode($valor['cobros']); 
$egreso = utf8_encode($valor['egreso']); 
$ingreso = utf8_encode($valor['ingreso']); 

$TotalIngreso =  $TotalIngreso +$ingreso ;
$TotalEgreso =$TotalEgreso +$egreso ;
$TotalCobros = $TotalCobros + $cobros;

$TotalApertura += $montoapertura;
$TotalCierre += $montocierre;

$diferencia = ($TotalCobros + $TotalIngreso) - $TotalEgreso;
$diferencia = abs($diferencia);

$fechaapertura2 = date("d-m-Y H:i:s", strtotime($fechaapertura));
$fechacierre2="";
if($fechacierre!=""){
	$fechacierre2 = date("d-m-Y H:i:s", strtotime($fechacierre));
}

	    	  $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosaperturacierrecaja(this)'>
<td id='td_id_1' style='display:none'>".$idarqueocaja."</td>
<td id='td_id_2' style='display:none'>".$caja_idcaja."</td>
<td id='td_id_3' style='display:none'>".$codusuarioap."</td>
<td id='td_id_4' style='display:none'>".$codusuarioce."</td>
<td id='td_id_5' style='display:none'>".$cod_local."</td>
<td id='td_datos_2' style='display:none'>".$nombrelocal."</td>
<td id='td_datos_10' style='width:10%'>".$lote."</td>
<td id='td_datos_1' style='width:10%'>".$cajanro."</td>
<td id='td_datos_9' style='width:10%'>".$estado."</td>
<td id='td_datos_3' style='width:10%'>".$fechaapertura."</td>
<td id='td_datos_4' style='width:10%'>".$fechacierre."</td>
<td id='td_datos_7' style='width:10%'>".number_format($montoapertura,'0',',','.')."</td>
<td id='td_datos_8' style='width:10%'>".number_format($montocierre,'0',',','.')."</td>
<td id='td_datos_5' style='width:15%'>".$usuarioap."</td>
<td id='td_datos_2' style='width:10%'>".$nombrelocal."</td>
<td id='td_datos_6' style='display:none'>".$usuariocie."</td>
<td id='td_datos_11' style='display: none;'>$cant500</td>
<td id='td_datos_12' style='display: none;'>$cant1000</td>
<td id='td_datos_13' style='display: none;'>$cant2000</td>
<td id='td_datos_14' style='display: none;'>$cant5000</td>
<td id='td_datos_15' style='display: none;'>$cant10000</td>
<td id='td_datos_16' style='display: none;'>$cant20000</td>
<td id='td_datos_17' style='display: none;'>$cant50000</td>
<td id='td_datos_18' style='display: none;'>$cant100000</td>
</tr>
</table>";

$registros[]= array(
	'lote' => utf8_encode($valor['lote']),
	'idarqueocaja' => utf8_encode($valor['idarqueocaja']),
	'caja_idcaja' => utf8_encode($valor['caja_idcaja']),
	'montoapertura' => utf8_encode($valor['montoapertura']),
	'montocierre' => utf8_encode($valor['montocierre']),
	'fechaapertura' => utf8_encode($valor['fechaapertura']),
	'fechacierre' => utf8_encode($valor['fechacierre']),
	'estado' => utf8_encode($valor['estado']),
	'codusuarioap' => utf8_encode($valor['codusuarioap']),
	'codusuarioce' => utf8_encode($valor['codusuarioce']),
	'cod_local' => utf8_encode($valor['cod_local']),
	'nombrelocal' => utf8_encode($valor['nombrelocal']),
	'cajanro' => utf8_encode($valor['cajanro']),
	'usuarioap' => utf8_encode($valor['usuarioap']),
	'usuariocie' => utf8_encode($valor['usuariocie']),
	'diferencia' => $diferencia,
);
}
}


$Totaldiferencia = ($TotalCobros + $TotalIngreso) - $TotalEgreso;

return array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4"=>number_format($Totaldiferencia,'0',',','.'),"5"=>number_format($TotalApertura,'0',',','.'),"6"=>number_format($TotalCierre,'0',',','.'),"7"=>number_format($TotalIngreso,'0',',','.'),"8"=>number_format($TotalEgreso,'0',',','.'),"9"=>number_format($TotalCobros,'0',',','.'), "10" => $registros);
}

function buscarcajaapp($fecha1,$fecha2,$cobrador,$estado)
{
	
$mysqli=conectar_al_servidor();

$condicionFechas="";
if($fecha1!="" && $fecha2!=""){
$condicionFechas="and fechaapertura>='$fecha1' and fechaapertura <='$fecha2' ";	
}
$condicionCobrador="";
if($cobrador!=""){
	$condicionCobrador=" and (Select nombre_persona from persona where cod_persona=cod_cobrador) like '%".$cobrador."%' ";
}
$condicionestado="";
if($estado!=""){
	$condicionestado=" and estado='".$estado."' ";
}


$sql= "Select idaperturacajaapp, fechaapertura, fechacierre, estado, IFNULL(montocierre,0) as montocierre, cod_cobrador,
(Select nombre_persona from persona where cod_persona=cod_cobrador) as usuario
from aperturacajaapp ap where  estado!='Cancelado' ".$condicionFechas.$condicionCobrador.$condicionestado." order by idaperturacajaapp desc limit 100  ";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$idaperturacajaapp = utf8_encode($valor['idaperturacajaapp']); 
$fechaapertura = utf8_encode($valor['fechaapertura']);          
$fechacierre = utf8_encode($valor['fechacierre']);          
$estado = utf8_encode($valor['estado']); 
$montocierre = utf8_encode($valor['montocierre']); 
$cod_cobrador = utf8_encode($valor['cod_cobrador']); 
$usuario = utf8_encode($valor['usuario']); 


	    	 $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosaperturacierrecajaapp(this)'>
<td id='td_id_1' style='display:none'>".$idaperturacajaapp."</td>
<td id='td_datos_1' style='width:10%'>".$usuario."</td>
<td id='td_datos_9' style='width:10%'>".$fechaapertura."</td>
<td id='td_datos_3' style='width:10%'>".$fechacierre."</td>
<td id='td_datos_7' style='width:10%'>".$montocierre."</td>
<td id='td_datos_5' style='width:10%'>".$estado."</td>
</tr>
</table>";


}
}


$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function ObtenerTotalCaja($idArqeoFk,$montoInicio)
{
$mysqli=conectar_al_servidor();

$sql= "select  sum(pg.Monto) as Monto
 from  pago pg 
 where pg.Monto>0 and pg.codApertura='$idArqeoFk' ";	
$Pagos = "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
          
$m = $valor['Monto'];          
$Pagos=$Pagos+$m;

	    	 


}
}

$sql= "Select monto
		from gastos g where codApertura='$idArqeoFk' and estado='Activo' and tipo='Egreso'";	
$MontoEgresos = "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
          
$m = $valor['monto'];          
$MontoEgresos=$MontoEgresos+$m;

	    	 


}
}


$sql= "Select monto
		from gastos g where codApertura='$idArqeoFk' and estado='Activo' and tipo='Ingreso'";	
$MontoIngreso= "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
          
$m = $valor['monto'];          
$MontoIngreso=$MontoIngreso+$m;

	    	 


}
}

 

$datosdeCajaRecibir=datosdeCajaRecibir($idArqeoFk);
$datosdeCajaEnviado=datosdeCajaEnviado($idArqeoFk);



$totalIngreso=$MontoIngreso+$Pagos+$montoInicio + $datosdeCajaRecibir[1];
$Monto=$totalIngreso-($MontoEgresos  + $datosdeCajaEnviado[1]);
 

return $Monto;
}





/*Buscar */
function datosdeCajaEnviado($idArqeoFk)
{
$mysqli=conectar_al_servidor();
 
	
$sql= "select idmigrar_caja, obs, fecha, monto, cod_caja_desdeFK, cod_caja_hastaFK, estado, tipo, cod_usuRecibeFK, cod_UsuEnviaFK , 
				(select nombre_persona from persona where cod_persona=cod_usuRecibeFK) as usuarioRecibe  ,
				(select nombre_persona from persona where cod_persona=cod_UsuEnviaFK) as usuarioEnvia from  migrar_caja  where cod_caja_desdeFK='$idArqeoFk' ";	


 $pagina="";
 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalCaja=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$monto = utf8_encode($valor['monto']); 
$usuarioRecibe = utf8_encode($valor['usuarioRecibe']); 

$totalCaja= $totalCaja + $monto ;
	$styleName=CargarStyleTable($styleName);
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:30%;text-align:left;padding:5px;line-height: 18px;' >".$usuarioRecibe."</td>
<td id='' style='width:20%'>". number_format($monto,'0',',','.')." </td>
<td id='' style='width:20%'> </td>
</tr>
</table>
";




}
}
   
$datos[0]=$pagina;
$datos[1]=$totalCaja;
return $datos;
}



/*Buscar */
function datosdeCajaRecibir($idArqeoFk)
{
$mysqli=conectar_al_servidor();
 
	
$sql= "select idmigrar_caja, obs, fecha, monto, cod_caja_desdeFK, cod_caja_hastaFK, estado, tipo, cod_usuRecibeFK, cod_UsuEnviaFK , 
				(select nombre_persona from persona where cod_persona=cod_usuRecibeFK) as usuarioRecibe  ,
				(select nombre_persona from persona where cod_persona=cod_UsuEnviaFK) as usuarioEnvia from  migrar_caja  where cod_caja_hastaFK='$idArqeoFk' ";	


 $pagina="";
 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalCaja=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$monto = utf8_encode($valor['monto']); 
$usuarioEnvia = utf8_encode($valor['usuarioEnvia']); 

$totalCaja= $totalCaja + $monto ;
	$styleName=CargarStyleTable($styleName);
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:30%;text-align:left;padding:5px;line-height: 18px;' >".$usuarioEnvia."</td>
<td id='' style='width:20%'>". number_format($monto,'0',',','.')." </td>
<td id='' style='width:20%'> </td>
</tr>
</table>
";




}
}
   
$datos[0]=$pagina;
$datos[1]=$totalCaja;
return $datos;
}






function buscarmoviemientocaja($idArqeoFk)
{
$mysqli=conectar_al_servidor();

$sql= "select Monto,tipo,cod_venta_fk,descripcion,
(Select Nombre from local l where l.cod_local=pg.codCaja) as nombrelocal
 from  pago pg 
 where pg.Monto>0 and pg.codApertura='$idArqeoFk' ";
$totalPagado = "0";   
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
          

$Monto = utf8_encode($valor['Monto']); 
$nombrelocal = utf8_encode($valor['nombrelocal']); 
$cod_venta_fk = utf8_encode($valor['cod_venta_fk']); 
$tipo = utf8_encode($valor['tipo']); 
$descripcion = utf8_encode($valor['descripcion']); 


$totalPagado=$totalPagado+$Monto;
if($descripcion=="ventas"){
	$descripcion=buscar_detalles_venta($cod_venta_fk);
}
	$pagina.="
<table class='tableTicket' border='0' cellspacing='0' cellpadding='0'>
<tr >
<td id='' style='width:75%;text-align:left;padding:5px;line-height: 18px;' >".$descripcion."</td>
<td id='' style='width:25%'>". number_format($Monto,'0',',','.')."</td>
</tr>
</table>
";


	    	 


}
}

$datosdeCajaRecibir=datosdeCajaRecibir($idArqeoFk);
$datosdeCajaEnviado=datosdeCajaEnviado($idArqeoFk);


$montoapertura=Obtenermontoapertura($idArqeoFk);

$datosdeEgresos=datosdeEgresos($idArqeoFk);
$datosdeIngreso=datosdeIngreso($idArqeoFk); 
$totalPagado=($totalPagado+$datosdeIngreso[0]+$montoapertura + $datosdeCajaRecibir[1])-($datosdeEgresos[0] + $datosdeCajaEnviado[1] );
 $informacion =array("1" => "exito","2" =>  number_format($totalPagado,'0',',','.'),"3"=> $pagina);
echo json_encode($informacion);	
exit;
}
function buscar_recaudo_opciones_pago($idArqeoFk)
{
$mysqli=conectar_al_servidor();

$sql= "SELECT * FROM tipopago;";

$pagina = "<table style='width:98%'><tr>";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

$arrayIds = ["inptTotalEfectivoConsultaCaja","inptTotalTarjetaConsultaCaja","inptTotalDebitoConsultaCaja","inptTotalTransferenciaConsultaCaja","inptTotalBilleteraConsultaCaja","inptTotalGiroMovilConsultaCaja"];
$contador = 0;
$TotalEfectivo = 0;
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
          

$cod_tipoPago = utf8_encode($valor['cod_tipoPago']); 
$nombre = utf8_encode($valor['nombre']); 

$totalMonto = buscar_total_opcion_pago($idArqeoFk,$cod_tipoPago);


	$pagina.="
<td style='width:10%;text-align:left'>
<p class='pTituloC' >".$nombre.":</p>
<input class='inputTextDisable' id='".$arrayIds[$contador]."' type='text' disabled
style='width:95%;' value='".number_format($totalMonto,'0',',','.')."' />
</td>
";

if($nombre!="EFECTIVO"){
	$TotalEfectivo += $totalMonto;
}

$contador++;

}
}

$pagina.= "</tr>
</table>";   

$informacion =array("1" => "exito","2"=> $pagina,"3"=> number_format($TotalEfectivo,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function buscar_total_opcion_pago($idArqeoFk,$idtipopago)
{
$mysqli=conectar_al_servidor();
 

	
$sql= "select  IFNULL(Monto,0) as Monto
 from  pago pg
 where pg.Monto>0 and pg.codApertura='$idArqeoFk' and pg.cod_tipoPagoFK='$idtipopago' ";	

 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalTipoPago=0;

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{
$totalTipoPago+= utf8_encode($valor['Monto']); 
}
}
  
 mysqli_close($mysqli);
return $totalTipoPago;
}




/*Buscar */
function buscar_detalles_venta($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.nombre_producto,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,dtv.detalleproducto
 from
 venta vt inner join detalle_venta dtv on vt.cod_venta=dtv.cod_ventaFK 
 inner join producto pr on pr.cod_producto=dtv.cod_productoFK
 where vt.cod_venta='$buscar' ";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$a=1;
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$nombre_producto = utf8_encode($valor['nombre_producto']);       
$cantidad_detalle = utf8_encode($valor['cantidad_detalle']);       
$detalleproducto = utf8_encode($valor['detalleproducto']);       
$subtotal = utf8_decode($valor['subtotal']);      
if($pagina==""){
	$pagina.=$a.") &nbsp".$nombre_producto.",&nbsp&nbsp".number_format($cantidad_detalle,'2',',','.')."(".$detalleproducto.")";	
	}else{
		$pagina.="<br>".$a.") &nbsp".$nombre_producto.",&nbsp&nbsp".number_format($cantidad_detalle,'2',',','.')."(".$detalleproducto.")";	
	}


}
}

return $pagina;
}


function datosdeEgresos($idArqeoFk)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
		$sql= "Select monto
		from gastos g where codApertura='$idArqeoFk' and estado='Activo' and tipo='Egreso' ";
		
   
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalGasto=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      
		  	  $monto=utf8_encode($valor['monto']);
		  	 $totalGasto=$totalGasto+$monto;
		  	 
	
			    	 
		  	  
			  
			  
	  }
 }


 $datos[0]= $totalGasto;
 return $datos;
}

function datosdeIngreso($idArqeoFk)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	
		$sql= "Select monto
		from gastos g where codApertura='$idArqeoFk' and estado='Activo' and tipo='Ingreso' ";
		
   
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalGasto=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		   
		  	  $monto=utf8_encode($valor['monto']);
		  	 $totalGasto=$totalGasto+$monto;
		  	 	 
		
			    	 
		  	  
			  
			  
	  }
 }

 $datos[0]= $totalGasto;
 return $datos;
}

function Obtenermontoapertura($idArqeoFk)
{
$mysqli=conectar_al_servidor();

$sql= "Select montoapertura
from arqueocaja  where idarqueocaja='$idArqeoFk'  ";
$montoapertura = "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
          
$montoapertura = utf8_encode($valor['montoapertura']);          

	    	 


}
}

return $montoapertura;
}



verificar($operacion);
?>