
var userid="1";
var passuser="NDE4MQ++";
var navegador="NoDefinido"
function obtener_datos_user(){
	
	userid=buscar_datos_url_usuario('q');
	passuser=buscar_datos_url_usuario('p');
	navegador=obtener_navegor_en_uso();
	
	if(userid==""){
		 document.cookie="user=;max-age=86400;path=/";
               document.cookie="pass=;max-age=86400;path=/";
    ir_a_login()
	}
	if(passuser==""){
			 document.cookie="user=;max-age=86400;path=/";
               document.cookie="pass=;max-age=86400;path=/";
		        ir_a_login()
	}
}
function ir_a_login(){
	 window.location="/GoodVentaAsisCap/system/login.html";

}


		function obtener_datos(datos)
		{
		
   var loc = datos;
   if (loc.indexOf('?')>0)
   {
   
   var getstring = loc.split('?')[1];
   var GET= getstring.split('&');
   var get ={};
  
   for (var i =0, l = GET.length; i < l;i++)
   {
   var tmp = GET[i].split('=');
   get[tmp[0]]= unescape(decodeURI(tmp[1]));
   
   }
   return get;
   }
	}
	function buscar_datos_url_usuario(datos){
		
		try{
			valores = document.location.href;
		 valores = obtener_datos(valores);
		 var  datos=valores[datos];
		 return datos;
		}catch(error){
		return "";
		}
		 
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
		 return null;
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
	 