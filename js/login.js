function verificardatos(){
	var user=document.getElementById('inpt_user').value
	var pass=document.getElementById('inpt_pass').value
	 if(user==""){
	  ver_vetana_informativa("FALTO INGRESAR EL USUARIO","#")
	  return false;
  }
  if(pass==""){
	  ver_vetana_informativa("FALTO INGRESAR LA CONTRASEÑA","#")
	  return false;
	  
  }
	entrar_al_sistema(user,pass);
}

function entrar_al_sistema(datos1,datos2){
	
	ver_cerrar_ventana_cargando(1);
	var navegador=obtener_navegor_en_uso()
	 var datos = new FormData();
			
			 datos.append("user" , datos1)
			 datos.append("pass" , datos2)
			 datos.append("navegador" , navegador)
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/login.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
					 ver_vetana_informativa("ERROR DE CONECCIÓN","#")
	ver_cerrar_ventana_cargando(2);
					 return false;
			},
			success: function(responseText)
			{
			  	 	ver_cerrar_ventana_cargando(2);
			Respuesta=responseText;	
			
				console.log(Respuesta)
	
		 if (Respuesta=="UI")
			{
		
			
				ver_vetana_informativa("USURIO O CONTRASEÑA INCORRECTA...","#")
						return false;
					


			}
			
			if (Respuesta!="error")
			{
		
		var datos = $.parseJSON(Respuesta); 
         var p=datos["1"];  
         var u=datos["2"];  
		 document.cookie="user="+u+";max-age=86400;path=/";
               document.cookie="pass="+p+";max-age=86400;path=/";
  window.location="/GoodVentaElim/app/inicio.html?p="+p+"&q="+u;
				
				document.getElementById('inpt_user').value=""
	     document.getElementById('inpt_pass').value=""
				
					
					


			}
			else
			{
			
	
					 ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR","#")


			}
			
			
		 
					
			}
			});
			
	
	
}

 /*Obtener navegador en uso*/
	 function obtener_navegor_en_uso()
	 {
	 	var navegador =navigator.userAgent;
		var na ;
		if((na=navegador.indexOf('MSIE'))!==-1)
		{
		  navegador = "explorer";
		}else{
		if((na=navegador.indexOf('OPERA'))!==-1)
		{
		  navegador = "opera";
		}else{
		if((na=navegador.indexOf('Chrome'))!==-1)
		{
		  navegador = "chrome";
		}else{
		if((na=navegador.indexOf('Firefox'))!==-1)
		{
		  navegador = "Firefox";
		}else{
		navegador ="otros";
		}
	       }
		   }
		   }
		return navegador ;   
	 }
	 var imgCargandoA="<img src='/GoodVentaElim/iconos/cargando.gif' style='width:30px' />"
	 function ver_cerrar_ventana_cargando(d){
	if(d=="1"){
		 
		
		document.getElementById('lbltitulomensaje_b').innerHTML="CARGANDO..."
		document.getElementById('div_principal_info_carga').style.display=''
	}else{
		document.getElementById('div_principal_info_carga').style.display='none'
	}
}
	
function ver_vetana_informativa(titulo,id_c){
	var pagina_informativa="<div class='div_info_3' title='click para cerrar' onclick='cerrar_ventanas(this)' id='"+id_c+"' name='ventanas_infos'>"+
	"<table style='width:100%;height:100%;padding:15px'>"+
	"<tr>"+
	"<td >"+
	"<label class='label_info_a'>"+titulo+"</label>"+
	"</td>"+
	"</tr>"+
	"</table>"+
	"</div>"
	document.getElementById('capa_informativa').innerHTML=pagina_informativa
	document.getElementById('capa_informativa').style.display=''
	$("div[id=capa_informativa]").fadeOut(1500)
}

function ocultarmensaje(){

	document.getElementById('capa_informativa').innerHTML=""
	document.getElementById('capa_informativa').style.display='none'
}
	 
