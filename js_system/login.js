
 window.onload=function()
		{		
			if (typeof history.pushState === "function") {
        history.pushState("jibberish", null, null);
        window.onpopstate = function () {
            history.pushState('newjibberish', null, null);
				evento_atras();
			//volver_atras_pagina()
            // Handle the back (or forward) buttons here
            // Will NOT handle refresh, use onbeforeunload for this.
        };
    }  else {
        var ignoreHashChange = true;
        window.onhashchange = function () {
            if (!ignoreHashChange) {
                ignoreHashChange = true;
                window.location.hash = Math.random();
				//evento_atras();
				//volver_atras_pagina()
                // Detect and redirect change here
                // Works in older FF and IE9
                // * it does mess with your hash symbol (anchor?) pound sign
                // delimiter on the end of the URL
            }
            else {
                ignoreHashChange = false;   
            }
        };
    }
buscarabmCasaOption()	
}

function verCerrarIngresar(){
	document.getElementById('divAcceso1').style.display='none'
	document.getElementById('divAcceso2').style.display=''
	document.getElementById('inpt_user').value=''
	document.getElementById('inpt_pass').value=''
	
}
function buscarabmCasaOption(){
 
 

	 
	document.getElementById("inptlocaluser").innerHTML="";
	
		 	
				 var datos = new FormData();
					  datos.append("useru" , "")
			 datos.append("passu" , "")
			 datos.append("navegador" , "")
			 datos.append("funt" , "buscaroptionlogin")
		
	 $.ajax({
			
			data: datos,
			 cache:false,
			contentType: false,
			processData: false,
			url: "/GoodVentaAsisCap/php_system/abmcasa.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			
		 if (Respuesta=="UI")
			{
		
			  ir_a_login()
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta=="NI")
			{
		
			
					ver_vetana_informativa("NO PUEDES REALIZAR LA ACCIÓN - BUSCAR CLIENTE")
						return false;
					


			} 
			if (Respuesta == "exito")
			{
				
				
				
		  var datos_buscados=datos[2];
		 
			document.getElementById("inptlocaluser").innerHTML=datos_buscados
				buscarCajaOption()

	  
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}
function buscarCajaOption(){
	 
	document.getElementById("inptcajauser").innerHTML="";
	var codLocal=document.getElementById("inptlocaluser").value;
		 	
				 var datos = new FormData();
					  datos.append("useru" , "")
			 datos.append("passu" , "")
			 datos.append("navegador" , "")
			 datos.append("cod_local" , codLocal)
			 datos.append("funt" , "buscaroptionlogin")
		
	 $.ajax({
			
			data: datos,
			 cache:false,
			contentType: false,
			processData: false,
			url: "/GoodVentaAsisCap/php_system/abmCaja.php",
			type:"post",
			beforeSend: function(){			
			
			
			},
				error: function(jqXHR, textstatus, errorThrowm){
	
			},
			success: function(responseText)
			{
	
			var Respuesta=responseText;
     console.log(Respuesta)
			try{
				var datos = $.parseJSON(Respuesta); 
          Respuesta=datos["1"];  
			
		 if (Respuesta=="UI")
			{
		
			  ir_a_login()
				ver_vetana_informativa("USUARIO INCORRECTO VUELVA A INICIAR SESION...")
						return false;
					


			} 
			if (Respuesta=="NI")
			{
		
			
					ver_vetana_informativa("NO PUEDES REALIZAR LA ACCIÓN - BUSCAR CLIENTE")
						return false;
					


			} 
			if (Respuesta == "exito")
			{
				
				
				
		  var datos_buscados=datos[2];
		 
			document.getElementById("inptcajauser").innerHTML=datos_buscados
			

	  
			}
			}catch(error)
				{
					
				}
			}
			});
	
	
}


function verificardatos(){
	var user=document.getElementById('inpt_user').value
	var pass=document.getElementById('inpt_pass').value
	var inptlocaluser=document.getElementById('inptlocaluser').value
	 if(user==""){
	  ver_vetana_informativa("FALTO INGRESAR EL USUARIO","#")
	  return false;
  }
  if(pass==""){
	  ver_vetana_informativa("FALTO INGRESAR LA CONTRASEÑA","#")
	  return false;
	  
  }
  if(inptlocaluser==""){
	  ver_vetana_informativa("FALTO SELECCIONAR EL LOCAL","#")
	  return false;
	  
  }
	entrar_al_sistema(user,pass,inptlocaluser);
}

function entrar_al_sistema(datos1,datos2,datos3){
	
	ver_cerrar_ventana_cargando(1);
	var navegador=obtener_navegor_en_uso()
	 var datos = new FormData();
			
			 datos.append("user" , datos1)
			 datos.append("pass" , datos2)
			 datos.append("local" , datos3)
			 datos.append("navegador" , navegador)
			var OpAjax= $.ajax({
			
			data: datos,
			url: "/GoodVentaAsisCap/php_system/login.php",
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
			  	 
			Respuesta=responseText;	
			
				console.log(Respuesta)
			var datos = $.parseJSON(Respuesta);
				Respuesta = datos["1"];
	
		 if (Respuesta=="UI")
			{
			
			ver_vetana_informativa("USURIO O CONTRASEÑA INCORRECTA...","#")
			
			ver_cerrar_ventana_cargando(2);
				
						return false;
					

			}
			
			if (Respuesta!="error")
			{
		
		
         var p=datos["1"];  
         var u=datos["2"]; 
var caja=document.getElementById("inptcajauser").value;
	localStorage.setItem("saludo"+u, "si");	
  window.location="/GoodVentaAsisCap/system/inicio.html?p="+p+"&q="+u+"&c="+caja;
				
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
	 function ver_cerrar_ventana_cargando(d){
	if(d=="1"){
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
	 
