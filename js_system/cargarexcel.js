


function ExploradorExcel(){	
controlExploracion="2"
$("input[name=file_1]").click();

}

let selectedFile;
console.log(window.XLSX);
document.getElementById('file_1').addEventListener("change", (event) => {
   
	
		  selectedFile = event.target.files[0];
    JsonExcel=null;
	datosexcel=null;
	if(controlExploracion=="2"){
	var control=readFileexcelcontrol()
	if(control==false){
		cancelarCarga()
	}else{
		convertiraJson()
	}
	}else{
		readFilecsv();
	}
	

	
})

let data=[{
    "name":"jayanth",
    "data":"scd",
    "abc":"sdef"
}]

var datosexcel;
var JsonExcel;
var counterexcelCarga;
function convertiraJson(){
	 document.getElementById("divDatosArchivo").style.display='none'
	 document.getElementById("divEfectoCargando0Archivo").style.display='none'
	 document.getElementById("divEfectoCargando3Archivo").style.display='none'
	 document.getElementById("divEfectoCargando5Archivo").style.display='none'
	 document.getElementById("divEfectoCargando1Archivo").style.display=''
	document.getElementById("divProgresocarga2").style.width='10%'
	document.getElementById("lblTituloCargando1Excel").innerHTML='Preparando Archivo (1/3)...'
	var tiempo = 1;
		clearInterval(counterexcelCarga);
			 counterexcelCarga=setInterval(timer,1000);
			function timer(){
			tiempo = tiempo -1 ;
			if (tiempo==0){	
		
	 	
	 
	 
	
	
    XLSX.utils.json_to_sheet(data, 'out.xlsx');
    if(selectedFile){
        let fileReader = new FileReader();
        fileReader.readAsBinaryString(selectedFile);
		document.getElementById("divProgresocarga2").style.width='70%'
		document.getElementById("lblTituloCargando1Excel").innerHTML='Obteniendo Datos (2/3)...'
        fileReader.onload = (event)=>{		
         let data = event.target.result;
         let workbook = XLSX.read(data,{type:"binary"});
      
        // console.log(workbook.Strings);
         //console.log(workbook.Strings.length);
	
         workbook.SheetNames.forEach(sheet => {
              let rowObject = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[sheet]);
              
			
           JsonExcel= JSON.stringify(rowObject,undefined,4)
		  // document.getElementById("TdExcelImportado").innerHTML=imgCargandoA
		      datosexcel = JSON.parse(JsonExcel);  
//			  procesardatos(datosexcel)

 //CargarJsonTable()
 
   

               readFileexcel();
         });
        }
    

}


clearInterval(counterexcelCarga);
	 	}
		}

}

 function readFileexcel(){		
var file=$("input[id=file_1]")[0].files[0];
var filename= file.name;
var tamanho = file.size;


var reader = new FileReader();
reader.onload = function(e){
	
	var mb=(Number(tamanho)/1048576).toFixed(2);
	mb=mb.toString().replace('.',',')
	document.getElementById("inptNombreArchivo").value=filename
	document.getElementById("inptTamanhoArchivo").value=separadordemilesnumero(mb)+" MB"
     document.getElementById("inptNroFilasArchivo").value=separadordemilesnumero(datosexcel.length)
	 document.getElementById("divProgresocarga2").style.width='98%'
	 document.getElementById("lblTituloCargando1Excel").innerHTML='Finalizando (3/3)...'
	 var tiempo = 1;
		clearInterval(counterexcelCarga);
			 counterexcelCarga=setInterval(timer,1000);
			function timer(){
			tiempo = tiempo -1 ;
			if (tiempo==0){	
	 document.getElementById("divDatosArchivo").style.display=''
	 document.getElementById("divEfectoCargando1Archivo").style.display='none'
	 document.getElementById("divEfectoCargando0Archivo").style.display='none'
	 document.getElementById("divEfectoCargando3Archivo").style.display='none'
	 document.getElementById("divEfectoCargando5Archivo").style.display='none'
	 clearInterval(counterexcelCarga);
	 	}
		}

}
reader.readAsDataURL(file);
}

function readFileexcelcontrol(){		
var file=$("input[id=file_1]")[0].files[0];
var filename= file.name;
var tamanho = file.size;
if (tamanho > 5000000){
alertmensaje("LA FOTO NO PUEDE EXCEDER LOS 5Mb")
return false
}
file_extension=filename.substring(filename.lastIndexOf('.')+1).toLowerCase();
if ((file_extension=="csv") || (file_extension=="xlsx") ){
}else{
alertmensaje("El archivo seleccionado no corresponde a un  .xlsx o .csv")
return false;
}
var reader = new FileReader();
reader.onload = function(e){
	
	
	var mb=(Number(tamanho)/1048576).toFixed(2);
	if(mb<1){
		t=4;
	}else{	
		var t=((Number(mb)*9)/1.43).toFixed(2)
		t=t+2;
		}
	
	
	if(t>59){
		t=(t/59).toFixed(1);
		document.getElementById("lblTiempoCarga1").innerHTML="Tiempo aprox. : "+t+" Min."
	}else{
		t=Number(t).toFixed(1);
		document.getElementById("lblTiempoCarga1").innerHTML="Tiempo aprox. : "+t+" Seg."
	}
	
	

}
reader.readAsDataURL(file);
}

function cancelarCarga(){
	JsonExcel=null;
	datosexcel=null;
		 document.getElementById("divDatosArchivo").style.display='none'
	 document.getElementById("divEfectoCargando1Archivo").style.display='none'
	 document.getElementById("divEfectoCargando3Archivo").style.display='none'
	 document.getElementById("divEfectoCargando5Archivo").style.display='none'
	 document.getElementById("divEfectoCargando0Archivo").style.display=''
	 var input=document.getElementById("file_1");
      input.value = ''
}


function CargarJsonTable() {


	
	var datos = new FormData();


	datos.append("json", JsonExcel)
	datos.append("TotalRegistro", datosexcel.length)
	var OpAjax = $.ajax({

		data: datos,
		url: "/SysAcademico/php/ObtenerTablaJSon.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus, errorThrowm) {
			ver_vetana_informativa("ERROR DE CONECCIÓN")
	
			return false;
		},
		success: function (responseText) {

			Respuesta = responseText;

			console.log(Respuesta)
			try {

				var datos = $.parseJSON(Respuesta);
				
  document.getElementById("TdExcelImportado").innerHTML=datos[2]
  document.getElementById("lblNroRegistroTdImportacion").innerHTML="Registros encontrados "+datos[3]
  document.getElementById("btnSincronizar").style.display=""
				
			

			} catch (error) {

				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR")

			}


		}
	});
}

function cancelarInsercion(){
	controlInsercion=false
	verResumenGuardado()
	 var input=document.getElementById("file_1");
      input.value = ''
}
var cantidarInseEnPausa=0;
function PausarInsercion(d){
	if(d.value=="Pausar"){
d.value="Continuar"
	controlInsercion=false
	cantidarInseEnPausa=registrocargado;
	}else{
		
			controlInsercion=true
		d.value="Pausar"
		registrocargado=cantidarInseEnPausa;
		procesardatos(datosexcel)
	}
}

   var counterexcelTimer
	function TimerExcelGuardo(){
		
		TiempoTranscurrido = 0;
		var segundo = 0;
		var minuto = 0;
		var hora = 0;
		clearInterval(counterexcelTimer);
			 counterexcelTimer=setInterval(timer,1000);
			function timer(){
				if(controlInsercion==true){
					
			segundo = Number(segundo) +1 ;
			
			if (segundo>59){	
			
			segundo=0;
			minuto=Number(minuto)+1;
			
			}
			
			if (minuto>59){	
			
			minuto=0;
			hora=Number(hora)+1;
			
			}
			
			if (hora>23){	
			
			
			hora=0;
			
			}
			
	if(hora<10){
	  horat="0"+hora
  } else{
	   horat=hora
  }
  if(segundo<10){
	  segundot="0"+segundo
  }else{
	  segundot=segundo
  }
  if(minuto<10){
	  minutot="0"+minuto
  }else{
	  minutot=minuto
  }
		

   horaImprimible = horat + " : " + minutot + " : " + segundot
TiempoTranscurrido=TiempoTranscurrido+1;
    document.getElementById("lblTimerGuardando1Excel").innerHTML = horaImprimible
		
			
				}
			
			}
		
 
   
 

  
}
var TiempoTranscurrido=0;
var controlInsercion=true;
var registrocargado=0;
function procesardatos(data){
	 var pagina=""	
		 var cargando=registrocargado+1;
		 var totalregistro=data.length-1;
      
		 for (var i = registrocargado ; i < cargando ; i++) {
		 
		
			
		 
					
			  var filiar=data[i]["FILIAL"]
			   var anho=data[i]["ANHO"]
			    var FacturaNro=data[i]["FACTURA"]
			    var ControlFactura=data[i]["CONTROLFACTURA"]
			    var Fecha=data[i]["FECHA"]
				
			  var nombreanterior=data[i]["NOMBREORIGINAL"]
			  var apellidoanterior=data[i]["APELLIDOORIGINAL"]
			  var cianterior=data[i]["CIORIGINAL"]
			  
			  var Nombre=data[i]["NOMBREDEPURADO"]
			  var Apellido=data[i]["APELLIDODEPURADO"]
			  var CI=data[i]["CIDEPURADO"]
			  
			  var Carrera=data[i]["CARRERA"]
			  var Curso=data[i]["CURSO"]
			   var SutTotal=data[i]["SUBTOTAL"]
			  var Concepto1=data[i]["CONCEPTO1"]
			  var Concepto2=data[i]["CONCEPTO2"]
			 
			 
           	 abmimportar(controlInsercion,registrocargado,totalregistro,filiar, FacturaNro,ControlFactura, Fecha, anho, Nombre, Apellido, CI, Carrera, Curso, Concepto1, Concepto2, SutTotal,nombreanterior,apellidoanterior, cianterior) 
             registrocargado=registrocargado+1;
		
		
			  }
}



	 