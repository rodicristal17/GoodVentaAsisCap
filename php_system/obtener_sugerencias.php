<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

include("buscar_nivel.php");
require("conexion.php");
include("verificar_navegador.php");
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
if($resp!="ok" && $operacion!="buscaroptionlogin"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}

 
if($operacion=="buscar")
{
 
	buscar( );

}


if($operacion=="ActualizarSugerencia")
{
 
 	
$idsugerencias=$_POST['idsugerencias'];
$idsugerencias= utf8_decode($idsugerencias);
$resolucion=$_POST['resolucion'];
$resolucion= utf8_decode($resolucion);
$estado=$_POST['estado'];
$estado= utf8_decode($estado);


	ActualizarSugerencia($idsugerencias,$resolucion,$estado);

}	



 
if($operacion=="buscarSugerencia")
{

$idsugerencias=$_POST['idsugerencias'];
$idsugerencias= utf8_decode($idsugerencias); 
	buscarSugerencia($idsugerencias);

}


 
}



 
function buscarSugerencia($idsugerencias)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
 
		$sql= "Select  fecha, estado, descripcion, cod_usuarioFK, cod_sugerencia, (select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuario from detalle_sugerencias where cod_sugerencia='".$idsugerencias."'  " ;   
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $styleName="tableRegistroSearch";
 $contador=0;
 if ($valor>0)
 {
  while ($valor= mysqli_fetch_assoc($result))
	  {
  
$fecha = utf8_encode($valor['fecha']);
$estado= utf8_encode($valor['estado']);
$descripcion = utf8_encode($valor['descripcion']);
$usuario = utf8_encode($valor['usuario']); 
 
 
$vacio="";

$pagina .= "<tr>
          <td>".$fecha."</td>
          <td>".$estado."</td>
          <td>".$descripcion."</td>
          <td>".$usuario."</td>
        </tr>";

 
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}






function ActualizarSugerencia($idsugerencias,$resolucion,$estado)
{
 
$mysqli=conectar_al_servidor();
	/*AUDITORIA*/
	date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
	 $user=$_POST['useru'];
    $user = utf8_decode($user);
 
$consulta1="Update sugerencias set resolucion='".$resolucion."',estado='".$estado."',cod_usuFK_resol='".$user."'  where idsugerencias='".$idsugerencias."'";	
$stmt1 = $mysqli->prepare($consulta1);
 
if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

$consulta1 = "INSERT INTO detalle_sugerencias (fecha, estado, descripcion, cod_usuarioFK, cod_sugerencia) 
              VALUES (NOW(), ?, ?, ?, ?)";

$stmt1 = $mysqli->prepare($consulta1);

if (!$stmt1) {
    trigger_error('Error en la preparación de la consulta: ' . $mysqli->error, E_USER_ERROR);
    exit;
}

$stmt1->bind_param("sssi", $estado, $resolucion, $user, $idsugerencias);

if (!$stmt1->execute()) {
    trigger_error('La ejecución de la consulta falló; MySQL dijo (' . $stmt1->errno . ') ' . $stmt1->error, E_USER_ERROR);
    exit;
}






 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}

 
function buscar()
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
 
		$sql= "Select idsugerencias,fecha, cedula,nombre_persona,descripcion_su,escliente, (select nombre_persona from persona where cod_persona=cod_usuarioFK_Doc) as doctor, (select nombre_persona from persona where cod_persona=cod_usuFK_resol) as UsuResolucion , cod_usuarioFK_Doc,calif,comentarioDoc,estado , resolucion from sugerencias   ORDER BY 
		FIELD(estado, 'Activo', 'Pendiente', 'En Proceso', 'Archivado', 'Resuelto') limit 100 " ; 

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
 $contador=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
 

$idsugerencias = $valor['idsugerencias'];
$fecha = utf8_encode($valor['fecha']);
$cedula = utf8_encode($valor['cedula']);
$nombre_persona = utf8_encode($valor['nombre_persona']);
$descripcion_su = utf8_encode($valor['descripcion_su']);
$escliente = utf8_encode($valor['escliente']);
$cod_usuarioFK_Doc = utf8_encode($valor['cod_usuarioFK_Doc']);
$calif = (int)utf8_encode($valor['calif']); // Calificación numérica
$comentarioDoc = utf8_encode($valor['comentarioDoc']);
$estado = utf8_encode($valor['estado']);
$doctor = utf8_encode($valor['doctor']);
$resolucion = utf8_encode($valor['resolucion']);
$UsuResolucion = utf8_encode($valor['UsuResolucion']);

if($resolucion!=""){
$resolucion = "<p><strong>Resolución:</strong> $resolucion <br><span style='color: #555;'>Por: <strong>$UsuResolucion</strong></span></p>";

}

if (in_array($estado, ["Activo", "Pendiente", "En Proceso"])) {
    $contador++;
}

$Style=" style='border-left: 5px solid #ff5722;' ";
if($estado=="Pendiente"){
	    $Style=" style='border-left: 5px solid #ffeb3b;'";
}

if($estado=="En Proceso"){
	$Style=" style='border-left: 5px solid #ffeb3b;'";
}

if($estado=="Resuelto"){
	$Style=" style='border-left: 5px solid #8BC34A;'";
}

if($estado=="Archivado"){
	$Style=" style='border-left: 5px solid #3f51b5;'";
}

$color = colorCalificacion($calif);

$badgeCliente = ($escliente === 'Cliente') ? 'badge-success' : 'badge-danger';
$colorCalifClass = ''; 
switch ($calif) {
  case 5: $colorCalifClass = 'text-success'; break;
  case 4: $colorCalifClass = 'text-info'; break;
  case 3: $colorCalifClass = 'text-warning'; break;
  case 2: $colorCalifClass = 'text-secondary'; break;
  default: $colorCalifClass = 'text-danger'; break;
}

$vacio="";

$pagina .= "
<div class='card my-3' $Style onclick=\"abrirModalEditarComentario(
  '" . htmlspecialchars($descripcion_su, ENT_QUOTES) . "',
  '" . htmlspecialchars($comentarioDoc, ENT_QUOTES) . "',
  '" . htmlspecialchars($estado, ENT_QUOTES) . "',
  '" . htmlspecialchars($idsugerencias, ENT_QUOTES) . "'
)\">
  <div class='card-header d-flex justify-content-between align-items-center'>
    <span>Sugerencia #$idsugerencias</span>
    <small class='text-secondary'>$fecha</small>
  </div>
  <div class='card-body'>
    <h5 class='card-title'>$nombre_persona <span class='badge $badgeCliente'>$escliente</span></h5>
    <h6 class='card-subtitle mb-2 text-secondary'>C.I.: $cedula</h6>
    <p class='card-text'>$descripcion_su</p>
    <p><strong>Doctor:</strong> $doctor</p>
    <p><strong>Comentario del Doctor:</strong> $comentarioDoc</p>
  </div>
  <div class='card-footer d-flex justify-content-between align-items-center'>
    <div>
      <span class='fw-bold'>Calificación:</span> 
      <span class='$colorCalifClass'>$calif / 5</span>
      " . starIcons($calif) . "
    </div>
    <div>
      <span class='fw-bold'>Estado:</span> <span class='badge badge-secondary text-uppercase'>$estado</span>
    </div>
  </div>
  $resolucion
</div>
";



 		  
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" => $contador);
echo json_encode($informacion);	
exit;


}

// Función para color según calificación
function colorCalificacion($calif) {
    if ($calif == 5) return 'success';    // Verde
    if ($calif == 4) return 'info';       // Azul claro
    if ($calif == 3) return 'warning';    // Amarillo
    if ($calif == 2) return 'secondary';  // Gris
    return 'danger';                      // Rojo para 1 o menos
}

// Función para mostrar estrellas según la calificación
function starIcons($calif) {
    $fullStar = "<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-star-fill' viewBox='0 0 16 16'>
    <path d='M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.32-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.63.282.95l-3.523 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z'/>
  </svg>";
    $emptyStar = "<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-star' viewBox='0 0 16 16'>
    <path d='M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.522-3.356c.33-.321.158-.889-.283-.95l-4.898-.696L8.465.792a.513.513 0 0 0-.927 0L5.354 5.118l-4.898.696c-.441.06-.612.629-.282.95l3.522 3.356-.83 4.73z'/>
  </svg>";
  
    $htmlStars = '';
    for ($i = 1; $i <= 5; $i++) {
        $htmlStars .= ($i <= $calif) ? $fullStar : $emptyStar;
    }
    return "<span class='ms-2 text-warning' style='vertical-align: middle;'>$htmlStars</span>";
}
 

verificar($operacion);
?>