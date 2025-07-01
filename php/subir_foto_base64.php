<?php 
function subir_imagen_base64($donde,$foto,$id_foto,$extesion){
	 $ruta=$donde;
	 $control_index='of';
if (file_exists($ruta))
{
	
}else
{
	mkdir($ruta,0777,true);
	$control_index='on';
}
	/*if($control_index=='on'){
	 $donde_html="../edither_media/index.html";	
			 if (! copy($donde_html, $ruta."/index.html")){
	   
	     
   }
}	*/


	 $id_f = rand(10,5000);
	  $id_foto.=$id_f;
	
	  
 $ruta=$donde.$id_foto.".".$extesion;	
$fp = fopen($ruta, 'wb');
fwrite($fp, $foto);
fclose($fp);

 
   
 	return $id_f;
   
   
  
	

}

?>