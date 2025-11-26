<?php
function subir_imagen_base64($donde, $foto, $id_foto, $extesion)
{
	// Identifica si la ruta es relativa
	if (substr($donde, 0, 3) == '../') {
		$donde = __DIR__ . '/'.$donde;
	}
	$ruta = $donde;
	$control_index = 'of';

	if (!file_exists($ruta)) {
		mkdir($ruta, 0777, true);
		$control_index = 'on';
	}
	/*if($control_index=='on'){
	 $donde_html="../edither_media/index.html";	
			 if (! copy($donde_html, $ruta."/index.html")){  
   }
}	*/

	$id_f = rand(10, 5000);
	$id_foto .= $id_f;

	$ruta = $donde . $id_foto . "." . $extesion;

	// Guardar imagen
    $file = fopen($ruta, 'wb');
    if ($file === false) {
        throw new Exception("No se pudo abrir el archivo: $ruta");
    }
    fwrite($file, $foto);
    fclose($file);

	return $id_f;
}

?>