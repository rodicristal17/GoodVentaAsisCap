<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include("classTable.php");
function verificar($operacion)
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


	
if($operacion=="nuevo" || $operacion=="editar")
{
	
	
	$idvendedor=$_POST['idvendedor'];
$idvendedor = utf8_decode($idvendedor);
$nombre=$_POST['nombre'];
$nombre = utf8_decode($nombre);
	$nrotelef=$_POST['nrotelef'];
$nrotelef = utf8_decode($nrotelef);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
$cod_localfk=$_POST['cod_localfk'];
$cod_localfk = utf8_decode($cod_localfk);
	abm($nombre,$nrotelef,$estado,$idvendedor,$cod_localfk,$operacion);

}

if($operacion=="buscar")
{
	$codigo=$_POST['codigo'];
$codigo = utf8_decode($codigo);
$vendedor=$_POST['vendedor'];
$vendedor = utf8_decode($vendedor);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
	

}
buscar($codigo,$vendedor,$estado,$cod_local);
}	
if($operacion=="buscarselect")
{
buscarselect();
}	
if($operacion=="buscarvista")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	$codlocal=$_POST['codlocal'];
$codlocal = utf8_decode($codlocal);
if($codlocal==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$codlocal=buscarlocaluser($user);
	}
	

}
	buscarvista($buscar,$codlocal);

}

if( $operacion=="editarAcceso")
{
	
	
	$idMetas=$_POST['idMetas'];
    $idMetas = utf8_decode($idMetas);
	$nro=$_POST['nro'];
    $nro = utf8_decode($nro);    
	abmAccesoMetas($nro,$idMetas,$operacion);

}

if($operacion=="buscarVendedor")
{
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
	buscarLoteamientoVendedor($cod_local);

}	



if($operacion=="buscarMetas")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
	$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$local=$_POST['local'];
$local = utf8_decode($local);
buscarMetas($fecha1,$fecha2,$local);

}

}

function abm($nombre,$nrotelef,$estado,$idvendedor,$cod_localfk,$operacion)
{
	
	
if($nombre==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor();

if($operacion=="nuevo") 
{


$consulta1="Insert into vendedor (nombre,nrotelef,estado,cod_localfk)
values(?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$nombre,$nrotelef,$estado,$cod_localfk);


}


if($operacion=="editar")
{

$consulta1="Update vendedor set nombre=?,nrotelef=?,estado=?,cod_localfk=? where idvendedor=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssss';
$stmt1->bind_param($ss,$nombre,$nrotelef,$estado,$cod_localfk,$idvendedor); 

}




if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}

function buscar($codigo,$vendedor,$estado,$cod_local)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	$condicioncodigo="";
if($codigo!=""){
	$condicioncodigo=" and idvendedor ='".$codigo."'";
}
$condicionvendedor="";
if($vendedor!=""){
	$condicionvendedor=" and nombre  like '%".$vendedor."%'";
}
$condicionlocal="";
if($cod_local!=""){
	$condicionlocal=" and cod_localfk = '".$cod_local."'";
}
	 
		$sql= "Select idvendedor, nombre, nrotelef, estado, cod_localfk,
		(select Nombre from local where cod_local=cod_localfk limit 1 ) as local
		from vendedor where estado=? ".$condicioncodigo.$condicionvendedor.$condicionlocal;
		
   
   
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
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idvendedor=$valor['idvendedor'];
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $nrotelef=utf8_encode($valor['nrotelef']);
		  	  $estado=utf8_encode($valor['estado']);
		  	  $local=utf8_encode($valor['local']);
		  	 
		  	 
			    	 
		  	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmVendedor(this)'>
<td id='td_id' style='width:10%; background-color: #efeded;color:red'>".$idvendedor."</td>
<td  id='td_datos_1' style='width:20%'>".$nombre."</td>
<td  id='td_datos_2' style='width:20%'>".$nrotelef."</td>
<td  id='td_datos_4' style='width:20%'>".$local."</td>
<td  id='td_datos_3' style='display:none'>".$estado."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}

function buscarselect()
{
	$mysqli=conectar_al_servidor();
	$pagina='';
	$pagina.="<option  value='' >SELECCIONAR</option>";   
	$sql= "Select idvendedor, nombre, nrotelef, estado, cod_localfk,
	(select Nombre from local where cod_local=cod_localfk limit 1 ) as local
	from vendedor where estado='Activo' ";	   
   $stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);

 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idvendedor=$valor['idvendedor'];
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $nrotelef=utf8_encode($valor['nrotelef']);
		  	  $estado=utf8_encode($valor['estado']);
		  	  $local=utf8_encode($valor['local']);
		  	 
		  	 
			    	 
		  	  $pagina.="<option  value='$idvendedor' >".$nombre."</option>";   
			  
			  
	  }
 }
 
 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;


}

function buscarvista($buscar,$codlocal)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 $condicionlocal="";
if($codlocal!=""){
	$condicionlocal=" and cod_localfk = '".$codlocal."'";
}
		$sql= "Select * from vendedor where nombre like ?  and estado='Activo' ".$condicionlocal;
		
   
   
   $stmt = $mysqli->prepare($sql);
  	$s='s';
$buscar="%".$buscar."%";
//$buscar="".$buscar."";
$stmt->bind_param($s,$buscar);

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
		  
		  $idvendedor=$valor['idvendedor'];
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $nrotelef=utf8_encode($valor['nrotelef']);
		  	  $estado=utf8_encode($valor['estado']);
		  	 
		  	 
			    	 
		  	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='obtenerdatosvistavendedor(this)'>
<td id='td_id' style='width:10%; background-color: #efeded;color:red'>".$idvendedor."</td>
<td  id='td_datos_1' style='width:45%'>".$nombre."</td>
<td  id='td_datos_2' style='width:45%'>".$nrotelef."</td>
<td  id='td_datos_3' style='display:none'>".$estado."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}




function buscarLoteamientoVendedor($cod_local)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	

$condicionlocal="";
if($cod_local!=""){
	$condicionlocal=" and cod_localfk = '".$cod_local."'";
}
	 
		$sql= "Select idvendedor, nombre, nrotelef, estado, cod_localfk,
		(select Nombre from local where cod_local=cod_localfk limit 1 ) as local
		from vendedor where estado!='' ".$condicionlocal;
		
 
   
   $stmt = $mysqli->prepare($sql);
  	
 $styleName="tableRegistroSearch";
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
		  
		  
		  
		      $idvendedor=$valor['idvendedor'];
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $estado=utf8_encode($valor['estado']);
			 
			  $AccionContador=buscarAccionContador($idvendedor);
			  if($AccionContador=="0"){
				  abmAccesoMetasPHP($idvendedor,"0","nuevoAcceso");
			  }
			   $Accion=buscarAccion($idvendedor);
		  	 $NroMetas=$Accion[0];
			 $NroMetas=number_format($NroMetas,'0',',','.');
			 $idMetas=$Accion[1];
			 $Style="style=' text-align: center;
				background-color: cadetblue;
				color: white;'";
				$inputcheck="<input  name='".$idMetas."' class='inputText' $Style value='$NroMetas'  type='text' onkeyup='separadordemiles(this); if(event.keyCode == 13){abmaccesoMetas(this)}'  />";
			  $styleName=CargarStyleTable($styleName);
			 	 
			  $pagina.="
			  <table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
			  <tr id='tbSelecRegistro' '>
			  <td id='td_id' style='width:5%'>".$idvendedor."</td>
			  <td id='td_datos_1'style='width:70%' class='tdRegistroSearch' >".$nombre."</td>
			   <td  id='td_datos_2' style='width:25%'>".$inputcheck."</td>
			  </tr>
			  </table>";
			    	 
		  	
			  
			  
	  }
 }
 
 
  $informacion =array("1" => "exito","2" => $pagina,"3"=> $totalresouesta);
echo json_encode($informacion);	
exit;


}




function buscarAccion($Usuario)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select idMetas, nro, Estado, Cod_vendedorFK
        from metas where Cod_vendedorFK='".$Usuario."' limit 1 ";
		

   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$accion="NO";
$idDetalleZona=0;
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 $nro="0";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		      $nro=$valor['nro']; 
			  $idMetas=$valor['idMetas']; 			  
	  }
 }
 $Resultado[0]= $nro;
 $Resultado[1]= $idMetas;

 return $Resultado;	



}



function buscarAccionContador($Vendedor)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select count(*) as zona  
        from metas where Cod_vendedorFK='".$Vendedor."'   limit 1 ";
		
		
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$accion=0;
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		      $accion=$valor['zona']; 			  
	  }
 }

 return $accion;	

}


function abmAccesoMetas($nro,$idMetas,$funt)
{
	
	$mysqli=conectar_al_servidor();

	if($funt=="editarAcceso")
	{
               
    $consulta="update metas set nro='".$nro."' where  idMetas='".$idMetas."' ";	

	$stmt = $mysqli->prepare($consulta);
       

	}
	
if ( ! $stmt->execute() ) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

	
}



function abmAccesoMetasPHP($Cod_vendedorFK,$nro,$funt)
{
	
	$mysqli=conectar_al_servidor();


	if($funt=="nuevoAcceso")
	{
	   
    $consulta="insert into metas (  Cod_vendedorFK, nro, Estado) values (?,?,'Activo')";	
     $stmt = $mysqli->prepare($consulta);
    $ss='ss';
    $stmt->bind_param($ss,$Cod_vendedorFK,$nro); 
        
 	}

	
if ( ! $stmt->execute() ) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


	
}






function buscarMetas($fecha1,$fecha2,$codlocal)
{
	
	$mysqli=conectar_al_servidor();
	$condicionNroCompra="";
	 $tabla="";
	
	$condicionfecha="";
	if($fecha1!="" || $fecha2!=""){
		$condicionfecha="  and fecha_venta between '".$fecha1."' and '".$fecha2."' ";
	}
	
	$condicionLocal="";
	if($codlocal!=""){
		$condicionLocal=" and cod_localfk='$codlocal' ";
	}

		
		$sql= "Select idvendedor, nombre, nrotelef, v.estado, cod_localfk,
		(select Nombre from local where cod_local=cod_localfk limit 1 ) as local
		from vendedor inner join venta v on  Vendedor1=idvendedor where IFNULL((Select count(fecha) from cancelaciones where cod_venta=v.cod_venta limit 1),0)=0 ".$condicionfecha.$condicionLocal."  group by idvendedor asc ";
	  	  
	
		    
		  	
		  	 
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
  $styleName="tableRegistroSearch";
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idvendedor=$valor['idvendedor'];
		      $nombre=utf8_encode($valor['nombre']);
		  	  $nrotelef=utf8_encode($valor['nrotelef']);
		  	  $local=utf8_encode($valor['local']);
			  
			  $Accion=buscarAccion($idvendedor);
		  	 $NroMetas=$Accion[0];
			 $NroMetas2=$Accion[0];
			 $NroMetas=number_format($NroMetas,'0',',','.');
			 
			 $totalcredito=buscarTotalVentaVendedorCredito($idvendedor,$fecha1,$fecha2,$codlocal);
			 $totalcontado=buscarTotalVentaVendedorContado($idvendedor,$fecha1,$fecha2,$codlocal);
			
			 $totalVentas=$totalcredito+$totalcontado;
		  	 
			 $TVenta2=$totalVentas;
				
			 $totalVentaResultado=$totalVentaResultado + $totalVentas;
			 $Diferencia=  $TVenta2 -  $NroMetas2 ;
			 if($TVenta2==0 || $TVenta2==""){
				 $TVenta2=1;
			 }
			 
			 if($NroMetas2==0 || $NroMetas2==""){
				 $NroMetas2=1;
			 }
			 
			 
			 $efectividad = ( $TVenta2 * 100 )/ $NroMetas2 ;
		  	$styleName=CargarStyleTable($styleName);
		  	   $tabla.="
				<table class='$styleName'  border='1' cellspacing='1' cellpadding='5'>
				<tr id='tbSelecRegistro' >
				<td  id='td_datos_1' style='width:30%; text-align:center'>".$nombre."</td>
				<td  id='td_datos_3' style='width:10%;text-align:center'>".$local."</td>
				<td  id='td_datos_3' style='width:10%;text-align:center'>".$NroMetas."</td>
				<td  id='td_datos_3' style='width:10%;text-align:center'>".number_format($totalcredito,'0',',','.')."</td>
				<td  id='td_datos_3' style='width:10%;text-align:center'>".number_format($totalcontado,'0',',','.')."</td>
				<td  id='td_datos_3' style='width:10%;text-align:center'>".number_format($totalVentas,'0',',','.')."</td>
				<td  id='td_datos_3' style='width:10%;text-align:center'>".number_format($Diferencia,'0',',','.')."</td>
				<td  id='td_datos_3' style='width:10%;text-align:center'>".number_format($efectividad,'0',',','.')."</td>
				</tr>
				</table>";
		
			  
			  
	  }
 }
  $TotalVenta2=buscarTotalVenta($fecha1,$fecha2,$codlocal);
$informacion =array("1" => "exito","2" => $tabla ,"3"=> number_format($totalVentaResultado,'0',',','.'),"4"=> number_format($TotalVenta2,'0',',','.'));
echo json_encode($informacion);	
exit;
}



function buscarTotalVentaVendedorCredito($Vendedor,$fecha1,$fecha2,$local)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
	 
		$condicionLocal="";
	if($local!=""){
		$condicionLocal=" and  vt.cod_local='$local' ";
	}
	
	 	$condicionfecha="";
	if($fecha1!="" || $fecha2!=""){
		$condicionfecha=" and fecha_venta between '".$fecha1."' and '".$fecha2."' ";
	}
	
		$sql= "Select sum(total_venta) as total_venta  
        from venta vt where  vt.TipoVenta='CREDITO'  and Vendedor1='".$Vendedor."' ".$condicionfecha.$condicionLocal."   and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0   limit 1 ";
	
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$total_venta=0;
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		      $total_venta=$valor['total_venta']; 			  
	  }
 }

 return $total_venta;	

}


function buscarTotalVentaVendedorContado($Vendedor,$fecha1,$fecha2,$local)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
	 
		$condicionLocal="";
	if($local!=""){
		$condicionLocal=" and  vt.cod_local='$local' ";
	}
	
	 	$condicionfecha="";
	if($fecha1!="" || $fecha2!=""){
		$condicionfecha=" and fecha_venta between '".$fecha1."' and '".$fecha2."' ";
	}
	
		$sql= "Select sum(total_venta) as total_venta  
        from venta vt where  vt.TipoVenta='CONTADO'  and Vendedor1='".$Vendedor."' ".$condicionfecha.$condicionLocal."   and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0   limit 1 ";
	
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$total_venta=0;
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		      $total_venta=$valor['total_venta']; 			  
	  }
 }

 return $total_venta;	

}




function buscarTotalVenta($fecha1,$fecha2,$local)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
	 	$condicionfecha="";
	if($fecha1!="" || $fecha2!=""){
		$condicionfecha=" and fecha_venta between '".$fecha1."' and '".$fecha2."' ";
	}
	
		$condicionLocal="";
	if($local!=""){
		$condicionLocal=" and  vt.cod_local='$local' ";
	}
	
		$sql= "Select sum(total_venta) as total_venta  
        from venta vt where  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0    ".$condicionLocal.$condicionfecha."   limit 1 ";
	
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$total_venta=0;
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		      $total_venta=$valor['total_venta']; 			  
	  }
 }

 return $total_venta;	

}


verificar($operacion);
?>