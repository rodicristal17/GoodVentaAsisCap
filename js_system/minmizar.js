window.addEventListener("keydown", function (event) {
//console.log(event.keyCode)
  
 // if (event.ctrlKey &&  event.which === 83) {
                // alert("You pressed Ctrl + s");
                // event.preventDefault();
            
	
		// document.getElementById("divSegundoPlano").style.display=""	 
  // }

  if (event.ctrlKey &&  event.which === 38) {
        
 verventanasminizados()		 
                event.preventDefault();	
				return false
		
  }
  if (event.which === 27) {
        minimizartodaventanaabierto2()
		 
                event.preventDefault();	
				return false
		
  }
  if (event.ctrlKey &&  event.which === 83) {
               
                event.preventDefault();
            
	
  }

   
},false);


function verventanasminizados(){
	var pagina=""
	var control=1;
	
	if($("div[id=divMinimizadoCargarCompras1]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarAbmCompra()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/compra.png' /><br/><label class='pTitulo17'  >Cargar Compras</label></button>"
	control=control+1;
	}

	if($("div[id=divMinimizadoCuentasCobrar1]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarCuentasACobrar()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/cuantasacobra.png' /><br/><label class='pTitulo17'  >Cuentas a Cobrar</label></button>"
	control=control+1;
	}

	if($("div[id=divMinimizadoCobrosRealizados1]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarInformeArqueo()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/arqueo.png' /><br/><label class='pTitulo17'  >Cobros Ralizados</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoExpedienteCliente1]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarInformeExpedientes()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/expedientes.png' /><br/><label class='pTitulo17'  >Expediente del Cliente</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoHistorialVenta1]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarHistorialVenta()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/dinero.png' /><br/><label class='pTitulo17'  >Historial de Ventas</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoListadoProducto1]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarAbmProducto()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/producto.png' /><br/><label class='pTitulo17'  >Listado de productos</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoNuevaVenta1]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarAbmVenta()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/venta.png' /><br/><label class='pTitulo17'  >Facturación</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoCargarSueldo]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarAbmSueldo()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/sueldo.png' /><br/><label class='pTitulo17'  >Cargar Sueldo</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoCatalago]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarInformeCatalogo()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/impresora.png' /><br/><label class='pTitulo17'  >Catalogo</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoClientesInactivo]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='vercerrarclientesinactivos(1)' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/hibernacion.png' /><br/><label class='pTitulo17'  >Clientes Inactivos</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoCuentasPagar]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarInformeCuentasAPagar(1)' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/cuentasApagar.png' /><br/><label class='pTitulo17'  >Cuentas a Pagar</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoConsultarCaja]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarInformeConsultaCaja(1)' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/informedecaja.png' /><br/><label class='pTitulo17'  >Consulta de Cajas</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoEgresoIngreso]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarAbmGasto()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/gastos.png' /><br/><label class='pTitulo17'  >Ingreso / Egreso</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoHistorialCompra]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarHistorialCompra()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/historialcompra.png' /><br/><label class='pTitulo17'  >Ingreso / Egreso</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoImprimirCodBarra]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarInformeCodBarra()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/codigobarras.png' /><br/><label class='pTitulo17'  >Cod. de Barra</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoInformeCambios]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarInformeDevoluciones()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/report.png' /><br/><label class='pTitulo17'  >Informe de Cambios</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoInformeGeneralCuentas]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarCuentasACobrarInforme()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/report.png' /><br/><label class='pTitulo17'  >Informe General de Cuentas</label></button>"
	control=control+1;
	}
	
	if($("div[id=divMinimizadoInformeEvaluacion]").is(':visible')){
	pagina+="<button id='btnMini_"+control+"' onclick='verCerrarInformeDeEvaluacion()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/report.png' /><br/><label class='pTitulo17'  >Informe de Evaluacion</label></button>"
	control=control+1;
	}	
	if($("div[id=divMinimizadoInformeInventario]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarInformeInventario()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/report.png' /><br/><label class='pTitulo17'  >Informe de Inventario</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoInformeGananciaPorVenta]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarInformeGananciasVentas()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/report.png' /><br/><label class='pTitulo17'  >Informe de Ganancia</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoProductoComprado]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarInformeProductosComprados()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/report.png' /><br/><label class='pTitulo17'  >Productos Comprados</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoProductoVendido]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarInformeProductosVendidos()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/report.png' /><br/><label class='pTitulo17'  >Productos Vendidos</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoVentaCancelada]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarInformeVentasCanceladas()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/report.png' /><br/><label class='pTitulo17'  >Ventas Canceladas</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoComisionCobrador]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarInformeComisionCobrador()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/report.png' /><br/><label class='pTitulo17'  >Comisión Cobrador</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoComisionVendedor]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarInformeComisionVendedor()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/report.png' /><br/><label class='pTitulo17'  >Comisión Vendedor</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoListadoDeLocales]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarAbmCasa()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/casa.png' /><br/><label class='pTitulo17'  >Listado de Locales</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoListadoZona]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarAbmZona()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/zona.png' /><br/><label class='pTitulo17'  >Listado de Zonas</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoListadoCobradores]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarAbmCobrador()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/cobrador.png' /><br/><label class='pTitulo17'  >Listado de Cobradores</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoListadoCliente]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarAbmClientes()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/cliente.png' /><br/><label class='pTitulo17'  >Listado de Clientes</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoListadoCaja]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarAbmCaja()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/caja.png' /><br/><label class='pTitulo17'  >Listado de Caja</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoListadoProveedor]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarAbmProveedor()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/proveedores.png' /><br/><label class='pTitulo17'  >Listado de Proveedores</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoListadoVendedor]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarAbmVendedor()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/vendedor.png' /><br/><label class='pTitulo17'  >Listado de Vendedores</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoNroFactura]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarFrmNroFactura()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/numero.png' /><br/><label class='pTitulo17'  >Listado de Cajas</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoProductoEnGarantia]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarInformeProductoEnGarantia()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/garantia.png' /><br/><label class='pTitulo17'  >Productos en Garantía</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoSolicitudes]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarSolitudProducto()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/listadodecarga.png' /><br/><label class='pTitulo17'  >Solicitudes</label></button>"
	control=control+1;
	}
	if($("div[id=divMinimizadoUsuarios]").is(':visible')){
	pagina+="<button  id='btnMini_"+control+"'onclick='verCerrarAbmUsuarios()' class='buttonMinimizado'><img class='imgIconoMenuMinimizado' src='/GoodVentaAsisCap/iconos/usuarios.png' /><br/><label class='pTitulo17'  >Usuarios</label></button>"
	control=control+1;
	}
	if(pagina!=""){
		 document.getElementById("divSegundoPlano").style.display=""
	document.getElementById("divVentanaminizado").innerHTML=pagina
	document.getElementById("btnMini_1").focus()
	
	}
}

function finalizarventanasminizados(){
	var pagina=""
	var control=1;
	if($("div[id=divMinimizadoCargarFacturas2]").is(':visible')){
	verCerrarFrmCargarFacturas()
	}
	if($("div[id=divMinimizadoCobranzas2]").is(':visible')){
	verCerrarFrmCobrarAranceles()
	}
	if($("div[id=divMinimizadoDeudasAnteriores2]").is(':visible')){
	verCerrarFrmDeudasAlumnos()
	}
	if($("div[id=divMinimizadoListadoDeFilial]").is(':visible')){
	verCerrarFrmAbmFilial()
	}
	if($("div[id=divMinimizadoDeCarreras]").is(':visible')){
	verCerrarFrmListaCarrera()
	}
	if($("div[id=divMinimizadoListadodeFacultad]").is(':visible')){
	verCerrarFrmListaFacultad()
	}
	if($("div[id=divMinimizadoListadoDeCatedras]").is(':visible')){
	verCerrarFrmListaCatedra()
	}
	if($("div[id=divMinimizadoMallaCurricular]").is(':visible')){
	verCerrarFrmAsignarMalla()
	}
	if($("div[id=divMinimizadoCarrera]").is(':visible')){
	verCerrarFrmAsignarCarrera()
	}
	if($("div[id=divMinimizadoNroFactura]").is(':visible')){
	verCerrarFrmAsignarFacturaNro()
	}
	if($("div[id=divMinimizadoListadoAranceles]").is(':visible')){
	verCerrarFrmListaAranceles()
	}
	if($("div[id=divMinimizadoAranceles]").is(':visible')){
	verCerrarFrmAsignarArancel()
	}
	if($("div[id=divMinimizadoFacturasCargadas]").is(':visible')){
	verCerrarFrmReportFacturasCargadas()
	}
	if($("div[id=divMinimizadoFacturaHabilitadas]").is(':visible')){
	verCerrarFrmReportNroFacturasHabilitadas()
	}
	if($("div[id=divMinimizadoBalanceGeneral]").is(':visible')){
	verCerrarFrmReportBalanceGeneral()
	}
	if($("div[id=divMinimizadoListadoAlumnos]").is(':visible')){
	verCerrarFrmAbmAlumnos()
	}
	if($("div[id=divMinimizadoMatriculacionAlumnos]").is(':visible')){
	verCerrarFrmAsignarAlumnos()
	}
	if($("div[id=divMinimizadoAlumnoMatriculado]").is(':visible')){
	verCerrarFrmReportAlumnosMatriculados()
	}	
	if($("div[id=divMinimizadoUsuario]").is(':visible')){
	verCerrarFrmUsuarios()
	}
	
		 document.getElementById("divSegundoPlano").style.display="non"
	document.getElementById("divVentanaminizado").innerHTML=""
	
	
}


function minimizartodaventanaabierto(){
	if(document.getElementById("divAbmCargarFacturas").style.display==""){
		MinimizarVentanaCargarFactura()
	}
	if(document.getElementById("divAbmCobrarAranceles").style.display==""){
		MinimizarVentanaCobranzas()
	}
	if(document.getElementById("divDeudasAlumnos").style.display==""){
		MinimizarVentanaDeudasAnteriores()
	}
	if(document.getElementById("divCambiarMisDatosPersonales").style.display==""){
		verCerrarMisDatos("2")
	}
	if(document.getElementById("divAbmAbmFilial").style.display==""){
		MinimizarVentanaListadoFilial()
	}
	if(document.getElementById("divAbmListaCarrera").style.display==""){
		MinimizarVentanaListadoCarrera()
	}
	if(document.getElementById("divAbmListaFacultad").style.display==""){
		MinimizarVentanaListadoFacultad()
	}
	if(document.getElementById("divAbmListaCatedra").style.display==""){
		MinimizarVentanaListadoCatedras()
	}
	if(document.getElementById("divAbmAsignarMalla").style.display==""){
		MinimizarVentanaMallaCurricular()
	}
	if(document.getElementById("divAbmAsignarCarrera").style.display==""){
		MinimizarVentanaAsignarCarrer()
	}
	if(document.getElementById("divAbmAsignarFacturaNro").style.display==""){
	MinimizarVentanaNroFactura()
	}
	if(document.getElementById("divAbmListaAranceles").style.display==""){
	MinimizarVentanaListadoArancel()
	}
	if(document.getElementById("divAbmAsignarArancel").style.display==""){
	MinimizarVentanaAsignarArancel()
	}
	if(document.getElementById("divReportFacturasCargadas").style.display==""){
MinimizarVentanaFacturasCargadas()
	}
	if(document.getElementById("divReportNroFacturasHabilitadas").style.display==""){
MinimizarVentanaFacturasHabilitadas()
	}
	
	if(document.getElementById("divReportBalance").style.display==""){
MinimizarVentanaBalancelGeneral()
	}
	if(document.getElementById("divAbmAbmAlumnos").style.display==""){
MinimizarVentanaAlumno()
	}
	if(document.getElementById("divAbmMatricularAlumnos").style.display==""){
MinimizarVentanaMatricularAlumnos()
	}
	if(document.getElementById("divReportAlumMatri").style.display==""){
MinimizarVentanaReportMatriculados()
}
if(document.getElementById("divAbmUsuario").style.display==""){
MinimizarVentanaUsuario()
}

}

function minimizartodaventanaabierto2(){
	if(document.getElementById("divSegundoPlano").style.display==""){
		document.getElementById("divSegundoPlano").style.display="none"
		return
	}
	if(document.getElementById("divVueltoVentaAContado").style.display==""){
		document.getElementById("divVueltoVentaAContado").style.display="none"
		return
	}
	
	if(document.getElementById("divAbmCompra").style.display==""){
		minizarventaCompras()
		return
	}
	if(document.getElementById("divCuentasAcobrar").style.display==""){
		minimizarcuentascobrar()
		return
	}
	if(document.getElementById("divArqueo").style.display==""){
	minimizarArqueo()
		return
	}
	if(document.getElementById("divInfExpediente").style.display==""){
		minimizarexpedientecliente()
		return
	}
	if(document.getElementById("divHistorialVenta").style.display==""){
		minimizarhistorialventa()
		return
	}
	if(document.getElementById("divAbmProducto").style.display==""){
		minimizarabmproductos()
		return
	}
	if(document.getElementById("divAbmVenta").style.display==""){
		minimizarventa("1")
		return
	}
	if(document.getElementById("divAbmCompra").style.display==""){
		minizarventaCompras("1")
		return
	}
	if(document.getElementById("divAbmSueldo").style.display==""){
	minimizarsueldos("1")
		return
	}
	if(document.getElementById("divInformeCatalago").style.display==""){
		minimizarcatalogo()
		return
	}
	if(document.getElementById("divClientesInactivos").style.display==""){
	minimizarclientesinactivo()
	return
	}
	if(document.getElementById("divCuentasAPagar").style.display==""){
	minimizarcuentaspagar()
	return
	}
	if(document.getElementById("divConsultaCaja").style.display==""){
	minimizarconsultacaja()
	return
	}
	if(document.getElementById("divReportFacturasCargadas").style.display==""){
MinimizarVentanaFacturasCargadas()
return
	}
	if(document.getElementById("divReportNroFacturasHabilitadas").style.display==""){
MinimizarVentanaFacturasHabilitadas()
return
	}
	
	if(document.getElementById("divReportBalance").style.display==""){
MinimizarVentanaBalancelGeneral()
return
	}
	if(document.getElementById("divAbmAbmAlumnos").style.display==""){
MinimizarVentanaAlumno()
return
	}
	if(document.getElementById("divAbmMatricularAlumnos").style.display==""){
MinimizarVentanaMatricularAlumnos()
return
	}
	if(document.getElementById("divReportAlumMatri").style.display==""){
MinimizarVentanaReportMatriculados()
return
}
if(document.getElementById("divAbmUsuario").style.display==""){
MinimizarVentanaUsuario()
return
}

}

function guardarDatosCtrlS(){
	return
if(document.getElementById("divAbmCargarFacturas2").style.display==""){
VerificarDatosCargaFactura()
return
}

if(document.getElementById("divAbmDeudasAlumnos2").style.display==""){
VerificarDatosDeudasAlumnos()
return
}
if(document.getElementById("divAbmAbmFilial2").style.display==""){
VerificarDatosAbmFilial()
return
}
if(document.getElementById("divAbmListaCarrera2").style.display==""){
VerificarDatosListaCarrera()()
return
}

}














