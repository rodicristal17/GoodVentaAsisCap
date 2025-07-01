
function ExploradorExcelMalla(){	
controlExploracion="1"
$("input[name=file_1]").click();

}

let selectedFile;
console.log(window.XLSX);
document.getElementById('file_1').addEventListener("change", (event) => {
   
	
		  selectedFile = event.target.files[0];
    JsonExcel=null;
	datosexcel=null;
	if(controlExploracion=="1"){
	var control=readFileexcelcontrol()
	if(control==false){
		cancelarCarga()
	}else{
		convertiraJson()
	}

	}
	

	
})

let data=[{
    "name":"jayanth",
    "data":"scd",
    "abc":"sdef"
}]
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
		if(controlExploracion=="1"){
			//document.getElementById("lblTiempoCarga1").innerHTML="Tiempo aprox. : "+t+" Min."
		}
		
	}else{
		t=Number(t).toFixed(1);
		if(controlExploracion=="1"){
			//document.getElementById("lblTiempoCarga1").innerHTML="Tiempo aprox. : "+t+" Seg."
		}
		
	}
	
	

}
reader.readAsDataURL(file);
}

var datosexcel;
var JsonExcel;
var counterexcelCarga;
function convertiraJson(){
	 verCerrarEfectoCargando("1")
	 document.getElementById("lbltitulomensaje_b").innerHTML="Preparando Archivo (1/3)...";
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
		 document.getElementById("lbltitulomensaje_b").innerHTML="Obteniendo Datos (2/3)...";
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
 verCerrarEfectoCargando("2")	 
var file=$("input[id=file_1]")[0].files[0];
var filename= file.name;
var tamanho = file.size;


var reader = new FileReader();
reader.onload = function(e){
	
	var mb=(Number(tamanho)/1048576).toFixed(2);
	mb=mb.toString().replace('.',',')
	if(controlExploracion=="1"){
	  document.getElementById("inptNombreRegistroImportarMalla").value=filename
	  document.getElementById("inptTamanhoRegistroImportarMalla").value=separadordemilesnumero(mb)+" MB"
     document.getElementById("inptNroTotalRegistroImportarMalla").value=separadordemilesnumero(datosexcel.length)
	 document.getElementById("divImportarMallaCurricular").style.display=""
	}
	
	 var tiempo = 1;
		clearInterval(counterexcelCarga);
			 counterexcelCarga=setInterval(timer,1000);
			function timer(){
			tiempo = tiempo -1 ;
			if (tiempo==0){	
	if(controlExploracion=="1"){
	
	}
	 clearInterval(counterexcelCarga);
	 	}
		}

}
reader.readAsDataURL(file);
}

function cancelarCarga(){
	JsonExcel=null;
	datosexcel=null;
	 var input=document.getElementById("file_1");
      input.value = ''
}