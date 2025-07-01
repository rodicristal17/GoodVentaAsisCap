<?php
function quitarseparadormiles($nro){
	$nro=str_replace('.','',$nro);
	$nro=str_replace(',','.',$nro);
	return $nro;
}
?>