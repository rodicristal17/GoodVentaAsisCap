/*Este archivo se utiliza en app.php*/
window.onload=function()

		{
			
		var usuario=buscar_este_cookie('user');
		 var pass=buscar_este_cookie('pass');
		
		
	   if(usuario=="nll" || usuario=="null" || usuario=="invitado" || usuario=="" || usuario==undefined || usuario=="undefined"){
	window.location="/GoodVentaElim/app/login.html";
			
		 }

		 	entrar_al_sistema(usuario,pass)
	
			
				
		       
			   
			   
			
		}
		
		 //buscar cookies
	 function buscar_este_cookie(name)
	 {
		 var nameEQ=name+"=";
		 var ca= document.cookie.split(";");
		 for(var i=0;i<ca.length;i++)
		 {
			 var c =ca[i];
			 while(c.charAt(0)==' ')c=c.substring(1,c.length);
			 if(c.indexOf(nameEQ)==0){
				 return decodeURIComponent (c.substring(nameEQ.length,c.length));
			 }
		 }
		 return "invitado";
	 }
	 

function entrar_al_sistema(datos1,datos2){
	var navegador= obtener_navegor_en_uso();
	ver_cerrar_ventana_cargando(1);
	var datos = new FormData();
	         datos.append("user" , datos1)
			 datos.append("pass" , datos2)
			 datos.append("navegador" , navegador)
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaElim/php/buscar_datos_usuario.php",
			type:"post",
	        cache:false,
			contentType: false,
			processData: false,
				error: function(jqXHR, textstatus, errorThrowm){
					 alertmensaje("ERROR DE CONECCIÓN")
	ver_cerrar_ventana_cargando(2);
					 return false;
			},
			success: function(responseText)
			{
			  	 	ver_cerrar_ventana_cargando(2);
			Respuesta=responseText;	
			
				console.log(Respuesta)
	var datos = $.parseJSON(Respuesta); 
	Respuesta=datos[1];
	try {
		 if (Respuesta=="UI")
			{
		
			document.cookie="user=;max-age=86400;path=/";
               document.cookie="pass=;max-age=86400;path=/";
				 window.location="/GoodVentaElim/app/login.html";
						return false;
					


			}
			
			if (Respuesta!="error")
			{
		
		
       window.location="/GoodVentaElim/app/inicio.html?p="+datos2+"&q="+datos1;
					


			}
			else
			{
			
	
					 alertmensaje("LO SENTIMOS HA OCURRIDO UN ERROR")


			}
			
			
		 
			 }catch(error){
					
					alertmensaje("LO SENTIMOS HA OCURRIDO UN ERROR")
					
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
		 
		document.getElementById('div_cargando_info').innerHTML=imgCargandoA
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
}

function ocultarmensaje(){

	document.getElementById('capa_informativa').innerHTML=""
	document.getElementById('capa_informativa').style.display='none'
}
	 
