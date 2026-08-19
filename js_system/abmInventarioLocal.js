var cod_inventarioLocal = "";
var totalregistroinformeInsumoLocal = 0;
var registrocargadoInsumoLocal = 0;
var controldebusquedadInformeInsumoLocal = false;

var fotoInventario1 = "";
var extInventario1 = "";
var fotoInventario2 = "";
var extInventario2 = "";
var fotoInventario3 = "";
var extInventario3 = "";
var fotoFacturaInventario = "";
var extFacturaInventario = "";
var fotoCompromisoInventario = "";
var extCompromisoInventario = "";

var inventarioLocalEstado = {
    inicializado: false,
    esquemaConsultado: false,
    pagina: 1,
    limite: 25,
    totalPaginas: 1,
    vista: "general",
    estructuraControl: false,
    registros: [],
    resumen: {},
    seleccionado: null,
    expandidos: {}
};

function inventarioLocalElemento(id) {
    return document.getElementById(id);
}

function inventarioLocalTexto(valor) {
    return valor === null || typeof valor === "undefined" ? "" : String(valor);
}

function inventarioLocalEscapar(valor) {
    return inventarioLocalTexto(valor)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/\"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function inventarioLocalParsearRespuesta(respuesta) {
    if (typeof respuesta === "object" && respuesta !== null) {
        return respuesta;
    }
    return $.parseJSON(respuesta);
}

function inventarioLocalMensajeError(datos, predeterminado) {
    if (datos && datos.mensaje) {
        return datos.mensaje;
    }
    if (datos && datos["2"] && typeof datos["2"] === "string") {
        return datos["2"];
    }
    return predeterminado || "No se pudo completar la operación.";
}

function inventarioLocalManejarErrorAjax(jqXHR, textstatus) {
    var datos = jqXHR && jqXHR.responseJSON ? jqXHR.responseJSON : null;
    if (!datos && jqXHR && jqXHR.responseText) {
        try {
            datos = inventarioLocalParsearRespuesta(jqXHR.responseText);
        } catch (error) {
            datos = null;
        }
    }
    if (typeof manejadordeerroresjquery === "function" && jqXHR) {
        manejadordeerroresjquery(jqXHR.status, textstatus || "error", "abmventana");
    }
    ver_vetana_informativa("No se pudo completar la operación", inventarioLocalMensajeError(datos, "Revise la conexión e intente nuevamente."), "error");
}

function inventarioLocalPeticion(accion, valores, configuracion) {
    obtener_datos_user();
    var datos = new FormData();
    datos.append("useru", userid);
    datos.append("passu", passuser);
    datos.append("navegador", navegador);
    datos.append("accion", accion);
    valores = valores || {};
    Object.keys(valores).forEach(function (clave) {
        if (Array.isArray(valores[clave])) {
            valores[clave].forEach(function (valor) {
                datos.append(clave + "[]", valor === null || typeof valor === "undefined" ? "" : valor);
            });
        } else {
            datos.append(clave, valores[clave] === null || typeof valores[clave] === "undefined" ? "" : valores[clave]);
        }
    });
    return $.ajax($.extend({
        data: datos,
        url: "../php_system/abmInventarioLocal.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false
    }, configuracion || {}));
}

function inventarioLocalFormatoNumero(valor) {
    var numero = Number(valor || 0);
    if (!isFinite(numero)) {
        numero = 0;
    }
    return Math.round(numero).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function inventarioLocalFormatoMoneda(valor) {
    return "Gs. " + inventarioLocalFormatoNumero(valor);
}

function inventarioLocalMontoSinFormato(valor) {
    var limpio = inventarioLocalTexto(valor).replace(/[^0-9]/g, "");
    return limpio === "" ? "0" : limpio;
}

function inventarioLocalFecha(valor, incluirHora) {
    var texto = inventarioLocalTexto(valor);
    if (texto === "") {
        return "—";
    }
    var partes = texto.substring(0, 10).split("-");
    if (partes.length !== 3) {
        return texto;
    }
    var salida = partes[2] + "/" + partes[1] + "/" + partes[0];
    if (incluirHora && texto.length >= 16) {
        salida += " " + texto.substring(11, 16);
    }
    return salida;
}

function inventarioLocalCodigo(valor) {
    var codigo = inventarioLocalTexto(valor);
    while (codigo.length < 3) {
        codigo = "0" + codigo;
    }
    return "ACT-" + codigo;
}

function inventarioLocalIniciales(nombre) {
    var palabras = inventarioLocalTexto(nombre).trim().split(/\s+/);
    var iniciales = "?";
    if (palabras[0]) {
        iniciales = palabras[0].charAt(0);
        if (palabras.length > 1) {
            iniciales += palabras[palabras.length - 1].charAt(0);
        }
    }
    return iniciales.toUpperCase();
}

function inventarioLocalUrlImagen(url, respaldo) {
    var valor = inventarioLocalTexto(url).trim();
    if (/^(\/|https?:\/\/)/i.test(valor)) {
        return valor;
    }
    return respaldo || "";
}

function inventarioLocalHtmlAvatar(nombre, url) {
    var imagen = inventarioLocalUrlImagen(url, "");
    if (imagen !== "") {
        return '<img class="inventario-local-avatar" src="' + inventarioLocalEscapar(imagen) + '" alt="">';
    }
    return '<span class="inventario-local-avatar-iniciales" aria-hidden="true">' + inventarioLocalEscapar(inventarioLocalIniciales(nombre)) + '</span>';
}

function inventarioLocalHtmlPersona(etiqueta, nombre, avatar, fecha) {
    var nombreVisible = inventarioLocalTexto(nombre).trim() || "Sin asignar";
    var detalle = etiqueta;
    if (fecha) {
        detalle += " · " + inventarioLocalFecha(fecha, false);
    }
    return '<div class="inventario-local-persona">' + inventarioLocalHtmlAvatar(nombreVisible, avatar)
        + '<div class="inventario-local-persona__texto"><strong title="' + inventarioLocalEscapar(nombreVisible) + '">' + inventarioLocalEscapar(nombreVisible)
        + '</strong><small>' + inventarioLocalEscapar(detalle) + '</small></div></div>';
}

function inventarioLocalHtmlEstado(estado) {
    var valor = inventarioLocalTexto(estado).toLowerCase();
    var clase = "inventario-local-etiqueta--neutro";
    var icono = "○";
    var texto = valor || "Sin estado";
    if (valor === "excelente") {
        clase = "";
        icono = "✓";
        texto = "Excelente";
    } else if (valor === "mantenimiento") {
        clase = "inventario-local-etiqueta--advertencia";
        icono = "⚒";
        texto = "Mantenimiento";
    } else if (valor === "dañado") {
        clase = "inventario-local-etiqueta--peligro";
        icono = "×";
        texto = "Dañado";
    }
    return '<span class="inventario-local-etiqueta ' + clase + '"><span aria-hidden="true">' + icono + '</span>' + inventarioLocalEscapar(texto) + '</span>';
}

function inventarioLocalHtmlVerificacion(registro) {
    if (!registro.ultima_verificacion) {
        return '<div class="inventario-local-verificacion"><span class="inventario-local-etiqueta inventario-local-etiqueta--peligro">! No verificado</span><small>Sin conteo registrado</small></div>';
    }
    var hoy = new Date();
    var hoyTexto = hoy.getFullYear() + "-" + ("0" + (hoy.getMonth() + 1)).slice(-2) + "-" + ("0" + hoy.getDate()).slice(-2);
    var diferencia = registro.diferencia_ultima_verificacion === null ? 0 : Number(registro.diferencia_ultima_verificacion);
    var vencida = registro.proxima_verificacion && registro.proxima_verificacion < hoyTexto;
    var clase = diferencia !== 0 || vencida ? "inventario-local-etiqueta--advertencia" : "";
    var texto = diferencia !== 0 ? "Con diferencia" : (vencida ? "Vencida" : "Verificado");
    return '<div class="inventario-local-verificacion"><span class="inventario-local-etiqueta ' + clase + '">✓ ' + texto + '</span><small>'
        + inventarioLocalEscapar(inventarioLocalFecha(registro.ultima_verificacion, false)) + '</small></div>';
}

function inventarioLocalBuscarRegistro(codigo) {
    var resultado = null;
    inventarioLocalEstado.registros.some(function (registro) {
        if (String(registro.cod_insumo) === String(codigo)) {
            resultado = registro;
            return true;
        }
        return false;
    });
    return resultado;
}

function inventarioLocalHtmlDetalle(registro) {
    var tipoCosto = inventarioLocalTexto(registro.costo_tipo || "pendiente");
    var costoDetalle = tipoCosto === "unitario" ? "Costo unitario" : (tipoCosto === "lote" ? "Costo total del lote" : "Costo pendiente de validar");
    var depreciacion = Number(registro.depreciacion_acumulada || 0);
    var ultimaEdicion = registro.fecha_edit ? inventarioLocalFecha(registro.fecha_edit, true) : "Sin ediciones";
    var editor = registro.nombre_usuarioFK_edit || "—";
    return '<tr class="inventario-local-detalle"><td colspan="9"><div class="inventario-local-detalle-contenido">'
        + '<div class="inventario-local-detalle-bloque"><b>Descripción</b><span>' + inventarioLocalEscapar(registro.descripcion || "Sin descripción") + '</span><span>Observación: ' + inventarioLocalEscapar(registro.observacion || "—") + '</span></div>'
        + '<div class="inventario-local-detalle-bloque"><b>Control</b><span>' + inventarioLocalEscapar(costoDetalle) + '</span><span>Tipo: ' + inventarioLocalEscapar(registro.tipo_control || "pendiente") + '</span><span>Adquisición: ' + inventarioLocalEscapar(inventarioLocalFecha(registro.fecha_adquisicion, false)) + '</span></div>'
        + '<div class="inventario-local-detalle-bloque"><b>Depreciación manual</b><span>Acumulada: ' + inventarioLocalEscapar(inventarioLocalFormatoMoneda(depreciacion)) + '</span><span>Valor contable: ' + inventarioLocalEscapar(inventarioLocalFormatoMoneda(registro.valor_contable)) + '</span><span>Fecha: ' + inventarioLocalEscapar(inventarioLocalFecha(registro.fecha_depreciacion, false)) + '</span></div>'
        + '<div class="inventario-local-detalle-bloque"><b>Trazabilidad</b><span>Editó: ' + inventarioLocalEscapar(editor) + '</span><span>' + inventarioLocalEscapar(ultimaEdicion) + '</span>'
        + '<button type="button" onclick="event.stopPropagation();inventarioLocalEditarCodigo(' + Number(registro.cod_insumo) + ')">Editar activo</button>'
        + (inventarioLocalEstado.estructuraControl ? '<button type="button" onclick="event.stopPropagation();inventarioLocalSeleccionar(' + Number(registro.cod_insumo) + ');inventarioLocalAbrirVerificacion()">Registrar verificación</button>' : '')
        + '</div></div></td></tr>';
}

function inventarioLocalRenderizar() {
    var cuerpo = inventarioLocalElemento("tbodyInventarioLocal");
    if (!cuerpo) {
        return;
    }
    if (!inventarioLocalEstado.registros.length) {
        cuerpo.innerHTML = '<tr><td colspan="9" class="inventario-local-vacio">No se encontraron activos con los filtros seleccionados.</td></tr>';
        return;
    }
    var html = "";
    inventarioLocalEstado.registros.forEach(function (registro) {
        var codigo = Number(registro.cod_insumo);
        var seleccionado = inventarioLocalEstado.seleccionado && String(inventarioLocalEstado.seleccionado.cod_insumo) === String(codigo);
        var foto = inventarioLocalUrlImagen(registro.url1, "/GoodVentaAsisCap/iconos/imagenphoto.png");
        var detalleActivo = [registro.categoria, registro.nombre_marca, registro.modelo].filter(function (item) { return inventarioLocalTexto(item).trim() !== ""; }).join(" · ");
        var serie = registro.nro_serie ? "Serie: " + registro.nro_serie : "Sin número de serie";
        var costoPendiente = inventarioLocalTexto(registro.costo_tipo) === "pendiente" ? '<small class="inventario-local-costo-pendiente">Pendiente de validar</small>' : "";
        html += '<tr class="inventario-local-fila' + (seleccionado ? ' seleccionada' : '') + '" data-codigo="' + codigo + '" onclick="inventarioLocalSeleccionar(' + codigo + ')">'
            + '<td class="col-foto"><img class="inventario-local-foto" src="' + inventarioLocalEscapar(foto) + '" alt=""></td>'
            + '<td class="col-activo"><div class="inventario-local-activo"><strong>' + inventarioLocalEscapar(inventarioLocalCodigo(codigo) + " · " + registro.nombre) + '</strong><span>' + inventarioLocalEscapar(detalleActivo || "Sin clasificación") + '</span><span>' + inventarioLocalEscapar(serie) + '</span></div></td>'
            + '<td class="col-ubicacion"><div class="inventario-local-ubicacion"><span><b>' + inventarioLocalEscapar(registro.nombreLocal || "Sin local") + '</b></span><span>' + inventarioLocalEscapar(registro.nombre_sector || "Sin sector") + '</span></div></td>'
            + '<td class="col-personas">' + inventarioLocalHtmlPersona("Responsable actual", registro.nombre_usuario_responsable, registro.avatar_usuario_responsable, "")
            + inventarioLocalHtmlPersona("Cargó el activo", registro.nombre_usuarioFK_create, registro.avatar_usuarioFK_create, registro.fecha_creacion) + '</td>'
            + '<td class="col-cantidad"><b>' + inventarioLocalEscapar(registro.cantidad) + '</b></td>'
            + '<td class="col-valor"><b>' + inventarioLocalEscapar(inventarioLocalFormatoMoneda(registro.valor_total)) + '</b>' + costoPendiente + '</td>'
            + '<td class="col-estado">' + inventarioLocalHtmlEstado(registro.estado_fisico) + '</td>'
            + '<td class="col-verificacion">' + inventarioLocalHtmlVerificacion(registro) + '</td>'
            + '<td class="col-expandir"><button type="button" class="inventario-local-btn-expandir" aria-label="Ver detalle" aria-expanded="' + (inventarioLocalEstado.expandidos[codigo] ? "true" : "false") + '" onclick="event.stopPropagation();inventarioLocalAlternarDetalle(' + codigo + ')">' + (inventarioLocalEstado.expandidos[codigo] ? "⌃" : "⌄") + '</button></td></tr>';
        if (inventarioLocalEstado.expandidos[codigo]) {
            html += inventarioLocalHtmlDetalle(registro);
        }
    });
    cuerpo.innerHTML = html;
}

function inventarioLocalActualizarResumen(resumen) {
    resumen = resumen || {};
    inventarioLocalEstado.resumen = resumen;
    var asignar = function (id, valor) {
        var elemento = inventarioLocalElemento(id);
        if (elemento) {
            elemento.textContent = valor;
        }
    };
    asignar("resumenPendientesInventarioLocal", inventarioLocalFormatoNumero(resumen.pendientes_validar));
    asignar("resumenAtencionInventarioLocal", inventarioLocalFormatoNumero(resumen.requieren_atencion));
    asignar("resumenProximaInventarioLocal", resumen.proxima_verificacion ? inventarioLocalFecha(resumen.proxima_verificacion, false) : "Sin fecha");
    asignar("resumenRegistrosInventarioLocal", inventarioLocalFormatoNumero(resumen.registros));
    asignar("resumenUnidadesInventarioLocal", inventarioLocalFormatoNumero(resumen.unidades));
    asignar("resumenValorInventarioLocal", inventarioLocalFormatoMoneda(resumen.valor_total));
    asignar("resumenValorContableInventarioLocal", inventarioLocalFormatoMoneda(resumen.valor_contable));
    asignar("pieRegistrosInventarioLocal", inventarioLocalFormatoNumero(resumen.registros));
    asignar("pieUnidadesInventarioLocal", inventarioLocalFormatoNumero(resumen.unidades));
    asignar("pieValorInventarioLocal", inventarioLocalFormatoMoneda(resumen.valor_total));
    totalregistroinformeInsumoLocal = Number(resumen.registros || 0);
}

function inventarioLocalActualizarPaginacion(paginacion) {
    paginacion = paginacion || {};
    inventarioLocalEstado.pagina = Number(paginacion.pagina || 1);
    inventarioLocalEstado.limite = Number(paginacion.limite || inventarioLocalEstado.limite);
    inventarioLocalEstado.totalPaginas = Math.max(1, Number(paginacion.total_paginas || 1));
    inventarioLocalElemento("paginaActualInventarioLocal").textContent = "Página " + inventarioLocalEstado.pagina + " de " + inventarioLocalEstado.totalPaginas;
    inventarioLocalElemento("paginaAnteriorInventarioLocal").disabled = inventarioLocalEstado.pagina <= 1;
    inventarioLocalElemento("paginaSiguienteInventarioLocal").disabled = inventarioLocalEstado.pagina >= inventarioLocalEstado.totalPaginas;
}

function inventarioLocalFiltros() {
    return {
        busqueda: inventarioLocalElemento("filtroTextoInventarioLocal").value,
        cod_localFK: inventarioLocalElemento("filtroLocalInventarioLocal").value,
        cod_sectorFK: inventarioLocalElemento("filtroSectorInventarioLocal").value,
        cod_usuario_responsableFK: inventarioLocalElemento("filtroResponsableInventarioLocal").value,
        estado_fisico: inventarioLocalElemento("filtroEstadoFisicoInventarioLocal").value,
        ocultar_inactivo: inventarioLocalElemento("filtroOcultarInactivosInventarioLocal").checked ? "1" : "",
        pagina: inventarioLocalEstado.pagina,
        limite: inventarioLocalEstado.limite
    };
}

function obtenerVistaInformeInsumoLocal(pagina) {
    if (typeof pagina !== "undefined") {
        inventarioLocalEstado.pagina = Math.max(1, Number(pagina || 1));
    } else {
        inventarioLocalEstado.pagina = 1;
    }
    var cuerpo = inventarioLocalElemento("tbodyInventarioLocal");
    if (cuerpo) {
        cuerpo.innerHTML = '<tr><td colspan="9" class="inventario-local-vacio">Consultando activos…</td></tr>';
    }
    verCerrarEfectoCargando("1");
    inventarioLocalPeticion("buscarVista", inventarioLocalFiltros()).done(function (respuesta) {
        try {
            var datos = inventarioLocalParsearRespuesta(respuesta);
            if (datos["1"] !== "exito") {
                throw new Error(inventarioLocalMensajeError(datos));
            }
            var estructuraAnterior = inventarioLocalEstado.estructuraControl;
            inventarioLocalEstado.registros = Array.isArray(datos["3"]) ? datos["3"] : [];
            inventarioLocalEstado.estructuraControl = datos.estructura_control === true || datos.estructura_control === 1;
            inventarioLocalEstado.esquemaConsultado = true;
            registrocargadoInsumoLocal = inventarioLocalEstado.registros.length;
            inventarioLocalActualizarResumen(datos.resumen || {});
            inventarioLocalActualizarPaginacion(datos.paginacion || {});
            inventarioLocalAplicarDisponibilidadControl();
            if (inventarioLocalEstado.estructuraControl && !estructuraAnterior) {
                inventarioLocalCargarSectores("filtro");
            }
            inventarioLocalRenderizar();
        } catch (error) {
            cuerpo.innerHTML = '<tr><td colspan="9" class="inventario-local-vacio">No se pudo interpretar la respuesta del inventario.</td></tr>';
            ver_vetana_informativa("No se pudo cargar el inventario", error.message, "error");
        }
    }).fail(inventarioLocalManejarErrorAjax).always(function () {
        verCerrarEfectoCargando("");
    });
}

function obtenermasVistaInformeInsumoLocal() {
    if (inventarioLocalEstado.pagina < inventarioLocalEstado.totalPaginas) {
        obtenerVistaInformeInsumoLocal(inventarioLocalEstado.pagina + 1);
    }
}

function cancelarInformeInventarioLocal() {
    controldebusquedadInformeInsumoLocal = false;
}

function inventarioLocalIrPagina(direccion) {
    var nuevaPagina = inventarioLocalEstado.pagina + Number(direccion || 0);
    if (nuevaPagina >= 1 && nuevaPagina <= inventarioLocalEstado.totalPaginas) {
        obtenerVistaInformeInsumoLocal(nuevaPagina);
    }
}

function inventarioLocalCambiarLimite(valor) {
    inventarioLocalEstado.limite = Number(valor || 25);
    inventarioLocalEstado.pagina = 1;
    obtenerVistaInformeInsumoLocal(1);
}

function inventarioLocalLimpiarFiltros() {
    inventarioLocalElemento("filtroTextoInventarioLocal").value = "";
    inventarioLocalElemento("filtroLocalInventarioLocal").value = "";
    inventarioLocalElemento("filtroResponsableInventarioLocal").value = "";
    inventarioLocalElemento("filtroEstadoFisicoInventarioLocal").value = "";
    inventarioLocalElemento("filtroOcultarInactivosInventarioLocal").checked = true;
    inventarioLocalCargarSectores("filtro", "", function () {
        obtenerVistaInformeInsumoLocal(1);
    });
}

function inventarioLocalCambiarVista(vista, boton) {
    inventarioLocalEstado.vista = vista;
    inventarioLocalElemento("divAbmInventarioLocal1").setAttribute("data-vista", vista);
    var botones = inventarioLocalElemento("divAbmInventarioLocal1").querySelectorAll(".inventario-local-vistas button");
    Array.prototype.forEach.call(botones, function (item) {
        item.className = item === boton ? "activo" : "";
    });
}

function inventarioLocalAlternarResumen() {
    var resumen = inventarioLocalElemento("resumenInventarioLocal");
    var boton = inventarioLocalElemento("btnResumenInventarioLocal");
    var abrir = resumen.hasAttribute("hidden");
    if (abrir) {
        resumen.removeAttribute("hidden");
    } else {
        resumen.setAttribute("hidden", "hidden");
    }
    boton.setAttribute("aria-expanded", abrir ? "true" : "false");
}

function inventarioLocalAlternarMenuImpresion(boton) {
    var menu = inventarioLocalElemento("menuImpresionInventarioLocal");
    var abrir = menu.hasAttribute("hidden");
    if (abrir) {
        menu.removeAttribute("hidden");
    } else {
        menu.setAttribute("hidden", "hidden");
    }
    boton.setAttribute("aria-expanded", abrir ? "true" : "false");
}

function inventarioLocalAlternarDetalle(codigo) {
    inventarioLocalEstado.expandidos[codigo] = !inventarioLocalEstado.expandidos[codigo];
    inventarioLocalRenderizar();
}

function inventarioLocalSeleccionar(codigo) {
    var registro = inventarioLocalBuscarRegistro(codigo);
    if (!registro) {
        return;
    }
    inventarioLocalEstado.seleccionado = registro;
    cod_inventarioLocal = registro.cod_insumo;
    inventarioLocalElemento("accionesSeleccionInventarioLocal").removeAttribute("hidden");
    inventarioLocalElemento("registroSeleccionadoInventarioLocal").textContent = inventarioLocalCodigo(registro.cod_insumo);
    inventarioLocalElemento("btnVerificarSeleccionInventarioLocal").disabled = !inventarioLocalEstado.estructuraControl;
    var legadoSeleccion = inventarioLocalElemento("inptRegistroSeleccInventarioLocal");
    if (legadoSeleccion) {
        legadoSeleccion.value = registro.cod_insumo;
    }
    inventarioLocalRenderizar();
}

function inventarioLocalEditarCodigo(codigo) {
    inventarioLocalSeleccionar(codigo);
    inventarioLocalEditarSeleccionado();
}

function inventarioLocalEditarSeleccionado() {
    if (!inventarioLocalEstado.seleccionado) {
        return;
    }
    if (controlacceso("EDITARLISTADOINVENTARIOLOCAL", "accion") === false) {
        return;
    }
    inventarioLocalCargarFormulario(inventarioLocalEstado.seleccionado);
    verCerrarAbmInventarioLocal(true, true);
}

function inventarioLocalAbrirAuditoria() {
    var registro = inventarioLocalEstado.seleccionado;
    if (!registro) {
        return;
    }
    var auditoria = {
        inptUsuarioInsertadoPor: registro.nombre_usuarioFK_create,
        inptFechaInsertadoPor: registro.fecha_creacion,
        inptUsuarioEditadoPor: registro.nombre_usuarioFK_edit,
        inptFechaEditadoPor: registro.fecha_edit
    };
    Object.keys(auditoria).forEach(function (id) {
        if (inventarioLocalElemento(id)) {
            inventarioLocalElemento(id).value = auditoria[id] || "";
        }
    });
    verCerrarAuditoria(true);
}

function inventarioLocalImprimirCompromisoSeleccionado() {
    var registro = inventarioLocalEstado.seleccionado;
    if (!registro) {
        return;
    }
    if (!registro.cod_usuario_responsableFK) {
        ver_vetana_informativa("Falta responsable", "Asigne un responsable actual antes de emitir la carta de compromiso.", "error");
        return;
    }
    inventarioLocalCargarFormulario(registro);
    inventarioLocalElemento("inptUsuarioResponsableCIInventarioInsumo").value = registro.ci_usuario_responsable || "";
    inventarioLocalElemento("inptUsuarioResponsableTelInventarioInsumo").value = registro.tel_usuario_responsable || "";
    imprimirCartaCompromiso("divAbmInventarioLocal");
}

function inventarioLocalNuevoActivo() {
    if (!inventarioLocalEstado.esquemaConsultado) {
        ver_vetana_informativa("Inventario en preparación", "Espere a que finalice la carga inicial antes de crear el activo.", "info");
        return;
    }
    if (controlacceso("CREARLISTADOINVENTARIOLOCAL", "accion") === false) {
        return;
    }
    limpiarcamposInventarioLocal();
    verCerrarAbmInventarioLocal(true, true);
}

function inventarioLocalAplicarDisponibilidadControl() {
    var avisoLista = inventarioLocalElemento("avisoMigracionInventarioLocal");
    var campos = inventarioLocalElemento("ubicacionControlInventarioLocal");
    var camposTipo = inventarioLocalElemento("camposControlInventarioLocal");
    var avisoFormulario = inventarioLocalElemento("avisoControlNoDisponibleInventarioLocal");
    var filtroSector = inventarioLocalElemento("filtroSectorInventarioLocal");
    if (inventarioLocalEstado.estructuraControl) {
        avisoLista.setAttribute("hidden", "hidden");
        avisoFormulario.setAttribute("hidden", "hidden");
        campos.style.display = "";
        camposTipo.style.display = "";
        filtroSector.disabled = false;
    } else {
        avisoLista.removeAttribute("hidden");
        avisoFormulario.removeAttribute("hidden");
        campos.style.display = "none";
        camposTipo.style.display = "none";
        filtroSector.disabled = true;
    }
}

function inventarioLocalSincronizarLocales() {
    var origen = inventarioLocalElemento("inptLocalInventarioInsumo");
    var destino = inventarioLocalElemento("filtroLocalInventarioLocal");
    if (!origen || !destino || origen.options.length === 0) {
        return false;
    }
    var seleccionado = destino.value;
    var html = '<option value="">Todos los locales</option>';
    Array.prototype.forEach.call(origen.options, function (opcion) {
        if (inventarioLocalTexto(opcion.value) !== "") {
            html += '<option value="' + inventarioLocalEscapar(opcion.value) + '">' + inventarioLocalEscapar(opcion.text) + '</option>';
        }
    });
    destino.innerHTML = html;
    destino.value = seleccionado;
    return true;
}

function inventarioLocalCargarResponsables() {
    inventarioLocalPeticion("listarResponsables", {}).done(function (respuesta) {
        var datos = inventarioLocalParsearRespuesta(respuesta);
        if (datos["1"] !== "exito") {
            return;
        }
        var responsables = Array.isArray(datos.responsables) ? datos.responsables : [];
        var filtro = inventarioLocalElemento("filtroResponsableInventarioLocal");
        var formulario = inventarioLocalElemento("inptUsuarioResponsableInventarioInsumo");
        var filtroSeleccionado = filtro.value;
        var formularioSeleccionado = formulario.value;
        var opciones = "";
        responsables.forEach(function (responsable) {
            opciones += '<option value="' + Number(responsable.cod_usuario) + '">' + inventarioLocalEscapar(responsable.nombre || ("Usuario " + responsable.cod_usuario)) + '</option>';
        });
        filtro.innerHTML = '<option value="">Todos los responsables</option><option value="-1">Sin responsable</option>' + opciones;
        formulario.innerHTML = '<option value="">Sin responsable asignado</option>' + opciones;
        filtro.value = filtroSeleccionado;
        formulario.value = formularioSeleccionado;
    });
}

function inventarioLocalCargarSectores(destino, seleccionado, alFinalizar) {
    var esFormulario = destino === "formulario";
    var select = inventarioLocalElemento(esFormulario ? "inptSectorInventarioInsumo" : "filtroSectorInventarioLocal");
    var local = inventarioLocalElemento(esFormulario ? "inptLocalInventarioInsumo" : "filtroLocalInventarioLocal").value;
    var valorPrevio = typeof seleccionado !== "undefined" && seleccionado !== null ? String(seleccionado) : select.value;
    var textoInicial = esFormulario ? "Seleccionar sector" : "Todos los sectores";
    select.innerHTML = '<option value="">' + textoInicial + '</option>';
    if (!inventarioLocalEstado.estructuraControl) {
        select.disabled = true;
        if (typeof alFinalizar === "function") {
            alFinalizar();
        }
        return;
    }
    select.disabled = false;
    inventarioLocalPeticion("listarSectores", {cod_localFK: local}).done(function (respuesta) {
        var datos = inventarioLocalParsearRespuesta(respuesta);
        if (datos["1"] !== "exito") {
            return;
        }
        var sectores = Array.isArray(datos.sectores) ? datos.sectores : [];
        sectores.forEach(function (sector) {
            var etiqueta = esFormulario || local ? sector.nombre : sector.nombre_local + " · " + sector.nombre;
            var opcion = document.createElement("option");
            opcion.value = sector.id;
            opcion.textContent = etiqueta;
            select.appendChild(opcion);
        });
        select.value = valorPrevio;
    }).always(function () {
        if (typeof alFinalizar === "function") {
            alFinalizar();
        }
    });
}

function inventarioLocalCrearSector() {
    if (!inventarioLocalEstado.estructuraControl) {
        ver_vetana_informativa("Sectores no habilitados", "Primero debe aplicarse la migración preparada para el control de activos.", "info");
        return;
    }
    var local = inventarioLocalElemento("inptLocalInventarioInsumo").value;
    if (!local) {
        ver_vetana_informativa("Seleccione el local", "El sector se administra dentro de un local específico.", "error");
        return;
    }
    var nombre = window.prompt("Nombre del nuevo sector dentro del local seleccionado:", "");
    if (nombre === null) {
        return;
    }
    nombre = nombre.trim();
    if (nombre.length < 2) {
        ver_vetana_informativa("Nombre incompleto", "Ingrese al menos dos caracteres para el sector.", "error");
        return;
    }
    inventarioLocalPeticion("guardarSector", {cod_localFK: local, nombre_sector: nombre}).done(function (respuesta) {
        var datos = inventarioLocalParsearRespuesta(respuesta);
        if (datos["1"] !== "exito") {
            ver_vetana_informativa("No se pudo guardar el sector", inventarioLocalMensajeError(datos), "error");
            return;
        }
        inventarioLocalCargarSectores("formulario", datos.id);
        inventarioLocalCargarSectores("filtro");
    }).fail(inventarioLocalManejarErrorAjax);
}

function inventarioLocalInicializar() {
    inventarioLocalSincronizarLocales();
    setTimeout(inventarioLocalSincronizarLocales, 650);
    inventarioLocalCargarResponsables();
    inventarioLocalElemento("divAbmInventarioLocal1").setAttribute("data-vista", inventarioLocalEstado.vista);
    if (!inventarioLocalEstado.inicializado) {
        inventarioLocalEstado.inicializado = true;
        document.addEventListener("click", function (evento) {
            var menu = inventarioLocalElemento("menuImpresionInventarioLocal");
            if (menu && !menu.hasAttribute("hidden") && !$(evento.target).closest(".inventario-local-menu-impresion").length) {
                menu.setAttribute("hidden", "hidden");
            }
        });
    }
    obtenerVistaInformeInsumoLocal(1);
}

function verCerrarAbmInventarioLocal(mostrar, abm) {
    if (controlacceso("VERLISTADOINVENTARIOLOCAL", "accion") === false) {
        return;
    }
    if (mostrar) {
        inventarioLocalElemento("divAbmInventarioLocal").style.display = "";
        if (abm) {
            $("div[id=divAbmInventarioLocal1]").fadeOut(180);
            $("div[id=divAbmInventarioLocal2]").fadeIn(180);
            inventarioLocalAplicarDisponibilidadControl();
        } else {
            $("div[id=divAbmInventarioLocal2]").hide();
            $("div[id=divAbmInventarioLocal1]").fadeIn(180);
            inventarioLocalInicializar();
        }
    } else if (abm) {
        $("div[id=divAbmInventarioLocal2]").fadeOut(180);
        $("div[id=divAbmInventarioLocal1]").fadeIn(180);
    } else {
        $("div[id=divAbmInventarioLocal1]").fadeOut(180);
        inventarioLocalElemento("divAbmInventarioLocal").style.display = "none";
        inventarioLocalElemento("divMinimizadoInventarioLocal").style.display = "none";
    }
}

function minimizarabmInventarioLocal() {
    inventarioLocalElemento("divMinimizadoInventarioLocal").style.display = "";
    inventarioLocalElemento("divAbmInventarioLocal").style.display = "none";
}

function inventarioLocalCargarFormulario(registro) {
    cod_inventarioLocal = registro.cod_insumo;
    inventarioLocalElemento("inptCodigoInventarioInsumo").value = inventarioLocalCodigo(registro.cod_insumo).replace("ACT-", "");
    inventarioLocalElemento("inptNombreInventarioInsumo").value = registro.nombre || "";
    inventarioLocalElemento("inptDescripcionInventarioInsumo").value = registro.descripcion || "";
    inventarioLocalElemento("inptCantidadInventarioInsumo").value = registro.cantidad || 1;
    inventarioLocalElemento("inptCostoInventarioInsumo").value = inventarioLocalFormatoNumero(registro.costo);
    inventarioLocalElemento("inptLocalInventarioInsumo").value = registro.cod_localFK || "";
    inventarioLocalElemento("inptEstadoInventarioInsumo").value = inventarioLocalTexto(registro.estado).toLowerCase() || "activo";
    inventarioLocalElemento("inptObservacionInventarioInsumo").value = registro.observacion || "";
    inventarioLocalElemento("inptUsuarioResponsableInventarioInsumo").value = registro.cod_usuario_responsableFK || "";
    inventarioLocalElemento("inptUsuarioResponsableCIInventarioInsumo").value = registro.ci_usuario_responsable || "";
    inventarioLocalElemento("inptUsuarioResponsableTelInventarioInsumo").value = registro.tel_usuario_responsable || "";
    inventarioLocalElemento("inptNroSerieInventarioInsumo").value = registro.nro_serie || "";
    inventarioLocalElemento("inptModeloInventarioInsumo").value = registro.modelo || "";
    inventarioLocalElemento("inptCategoriaInventarioInsumo").value = registro.categoria || "";
    inventarioLocalElemento("inptEstadoFisicoInventarioInsumo").value = inventarioLocalTexto(registro.estado_fisico).toLowerCase();
    inventarioLocalElemento("inptMarcaInventarioInsumo").value = registro.nombre_marca || "";
    inventarioLocalElemento("inptTipoControlInventarioInsumo").value = registro.tipo_control || "pendiente";
    inventarioLocalElemento("inptTipoCostoInventarioInsumo").value = registro.costo_tipo || "pendiente";
    inventarioLocalElemento("inptFechaAdquisicionInventarioInsumo").value = inventarioLocalTexto(registro.fecha_adquisicion).substring(0, 10);
    inventarioLocalElemento("inptFrecuenciaVerificacionInventarioInsumo").value = registro.frecuencia_verificacion || "semestral";
    inventarioLocalElemento("inptDepreciacionInventarioInsumo").value = inventarioLocalFormatoNumero(registro.depreciacion_acumulada);
    inventarioLocalElemento("inptFechaDepreciacionInventarioInsumo").value = inventarioLocalTexto(registro.fecha_depreciacion).substring(0, 10);
    inventarioLocalElemento("inptObservacionDepreciacionInventarioInsumo").value = "";
    idFkProductoMarca = registro.cod_marcaFK || "";
    inventarioLocalCargarSectores("formulario", registro.cod_sectorFK || "");
    inventarioLocalActualizarTipoControl();

    inventarioLocalElemento("imgfotoInventarioLocal1").style.backgroundImage = "url(" + inventarioLocalUrlImagen(registro.url1, "/GoodVentaAsisCap/iconos/imagenphoto.png") + ")";
    inventarioLocalElemento("imgfotoInventarioLocal2").style.backgroundImage = "url(" + inventarioLocalUrlImagen(registro.url2, "/GoodVentaAsisCap/iconos/imagenphoto.png") + ")";
    inventarioLocalElemento("imgfotoInventarioLocal3").style.backgroundImage = "url(" + inventarioLocalUrlImagen(registro.url3, "/GoodVentaAsisCap/iconos/imagenphoto.png") + ")";
    inventarioLocalElemento("imgfacturaInventarioLocal").style.backgroundImage = "url(" + inventarioLocalUrlImagen(registro.url_factura, "/GoodVentaAsisCap/iconos/imagenphoto.png") + ")";
    inventarioLocalElemento("imgCompromisoInventarioLocal").style.backgroundImage = "url(" + inventarioLocalUrlImagen(registro.url_compromiso, "/GoodVentaAsisCap/iconos/imagenphoto.png") + ")";

    var auditoria = {
        inptUsuarioInsertadoPor: registro.nombre_usuarioFK_create,
        inptFechaInsertadoPor: registro.fecha_creacion,
        inptUsuarioEditadoPor: registro.nombre_usuarioFK_edit,
        inptFechaEditadoPor: registro.fecha_edit
    };
    Object.keys(auditoria).forEach(function (id) {
        if (inventarioLocalElemento(id)) {
            inventarioLocalElemento(id).value = auditoria[id] || "";
        }
    });
    buscarHistorialResponsablesAnteriores(registro.cod_insumo);
}

function obtenerDatosInsumoLocal(origen) {
    var codigo = origen && origen.getAttribute ? origen.getAttribute("data-codigo") : origen;
    if (!codigo && origen && origen.querySelector) {
        var celda = origen.querySelector("[id=td_id]");
        codigo = celda ? celda.textContent : "";
    }
    if (codigo) {
        inventarioLocalSeleccionar(codigo);
    }
}

function consultarUltimoIdInventarioLocal() {
    inventarioLocalPeticion("obtenerUltimoId", {}).done(function (respuesta) {
        var datos = inventarioLocalParsearRespuesta(respuesta);
        if (datos["1"] === "exito") {
            inventarioLocalElemento("inptCodigoInventarioInsumo").value = inventarioLocalCodigo(datos["2"]).replace("ACT-", "");
        }
    });
}

function limpiarcamposInventarioLocal() {
    cod_inventarioLocal = "";
    idFkProductoMarca = "";
    var valores = {
        inptNombreInventarioInsumo: "",
        inptDescripcionInventarioInsumo: "",
        inptCantidadInventarioInsumo: "1",
        inptCostoInventarioInsumo: "",
        inptLocalInventarioInsumo: "",
        inptEstadoInventarioInsumo: "activo",
        inptObservacionInventarioInsumo: "",
        inptUsuarioResponsableInventarioInsumo: "",
        inptNroSerieInventarioInsumo: "",
        inptModeloInventarioInsumo: "",
        inptCategoriaInventarioInsumo: "",
        inptEstadoFisicoInventarioInsumo: "",
        inptMarcaInventarioInsumo: "",
        inptTipoControlInventarioInsumo: "pendiente",
        inptTipoCostoInventarioInsumo: "pendiente",
        inptFechaAdquisicionInventarioInsumo: "",
        inptFrecuenciaVerificacionInventarioInsumo: "semestral",
        inptDepreciacionInventarioInsumo: "0",
        inptFechaDepreciacionInventarioInsumo: "",
        inptObservacionDepreciacionInventarioInsumo: ""
    };
    Object.keys(valores).forEach(function (id) {
        if (inventarioLocalElemento(id)) {
            inventarioLocalElemento(id).value = valores[id];
        }
    });
    ["imgfotoInventarioLocal1", "imgfotoInventarioLocal2", "imgfotoInventarioLocal3", "imgfacturaInventarioLocal", "imgCompromisoInventarioLocal"].forEach(function (id) {
        inventarioLocalElemento(id).style.backgroundImage = "url(/GoodVentaAsisCap/iconos/imagenphoto.png)";
    });
    fotoInventario1 = "";
    extInventario1 = "";
    fotoInventario2 = "";
    extInventario2 = "";
    fotoInventario3 = "";
    extInventario3 = "";
    fotoFacturaInventario = "";
    extFacturaInventario = "";
    fotoCompromisoInventario = "";
    extCompromisoInventario = "";
    inventarioLocalCargarSectores("formulario", "");
    inventarioLocalElemento("divHistorialResponsableInsumosLocal").style.display = "none";
    consultarUltimoIdInventarioLocal();
    inventarioLocalAplicarDisponibilidadControl();
    inventarioLocalActualizarTipoControl();
}

function inventarioLocalActualizarTipoControl() {
    var tipo = inventarioLocalElemento("inptTipoControlInventarioInsumo").value;
    var cantidad = inventarioLocalElemento("inptCantidadInventarioInsumo");
    if (tipo === "individual") {
        cantidad.value = 1;
        cantidad.disabled = true;
    } else {
        cantidad.disabled = false;
    }
}

function verificarCamposInventarioLocal() {
    var nombre = inventarioLocalElemento("inptNombreInventarioInsumo").value.trim();
    var local = inventarioLocalElemento("inptLocalInventarioInsumo").value;
    var cantidad = Number(inventarioLocalElemento("inptCantidadInventarioInsumo").value || 0);
    var categoria = inventarioLocalElemento("inptCategoriaInventarioInsumo").value;
    var estadoFisico = inventarioLocalElemento("inptEstadoFisicoInventarioInsumo").value;
    var sector = inventarioLocalElemento("inptSectorInventarioInsumo").value;
    var depreciacion = Number(inventarioLocalMontoSinFormato(inventarioLocalElemento("inptDepreciacionInventarioInsumo").value));
    var fechaDepreciacion = inventarioLocalElemento("inptFechaDepreciacionInventarioInsumo").value;
    if (!nombre || !idFkProductoMarca || !local || cantidad <= 0 || !categoria || !estadoFisico) {
        ver_vetana_informativa("Faltan datos", "Complete nombre, marca, local, cantidad, categoría y estado físico.", "error");
        return false;
    }
    if (inventarioLocalEstado.estructuraControl && !sector) {
        ver_vetana_informativa("Falta el sector", "Seleccione dónde se encuentra el activo dentro del local.", "error");
        return false;
    }
    if (inventarioLocalEstado.estructuraControl && depreciacion > 0 && !fechaDepreciacion) {
        ver_vetana_informativa("Falta la fecha", "La depreciación manual debe indicar la fecha contable correspondiente.", "error");
        return false;
    }
    abmInventarioLocal();
    return true;
}

function abmInventarioLocal() {
    var valores = {
        cod_inventario: cod_inventarioLocal,
        nombre: inventarioLocalElemento("inptNombreInventarioInsumo").value,
        descripcion: inventarioLocalElemento("inptDescripcionInventarioInsumo").value,
        estado: inventarioLocalElemento("inptEstadoInventarioInsumo").value,
        cantidad: inventarioLocalElemento("inptCantidadInventarioInsumo").value,
        costo: inventarioLocalMontoSinFormato(inventarioLocalElemento("inptCostoInventarioInsumo").value),
        observacion: inventarioLocalElemento("inptObservacionInventarioInsumo").value,
        cod_usuario_responsableFK: inventarioLocalElemento("inptUsuarioResponsableInventarioInsumo").value,
        modelo: inventarioLocalElemento("inptModeloInventarioInsumo").value,
        nro_serie: inventarioLocalElemento("inptNroSerieInventarioInsumo").value,
        cod_localFK: inventarioLocalElemento("inptLocalInventarioInsumo").value,
        cod_sectorFK: inventarioLocalElemento("inptSectorInventarioInsumo").value,
        cod_marcaFK: idFkProductoMarca,
        estado_fisico: inventarioLocalElemento("inptEstadoFisicoInventarioInsumo").value,
        categoria: inventarioLocalElemento("inptCategoriaInventarioInsumo").value,
        tipo_control: inventarioLocalElemento("inptTipoControlInventarioInsumo").value,
        costo_tipo: inventarioLocalElemento("inptTipoCostoInventarioInsumo").value,
        fecha_adquisicion: inventarioLocalElemento("inptFechaAdquisicionInventarioInsumo").value,
        frecuencia_verificacion: inventarioLocalElemento("inptFrecuenciaVerificacionInventarioInsumo").value,
        depreciacion_acumulada: inventarioLocalMontoSinFormato(inventarioLocalElemento("inptDepreciacionInventarioInsumo").value),
        fecha_depreciacion: inventarioLocalElemento("inptFechaDepreciacionInventarioInsumo").value,
        observacion_depreciacion: inventarioLocalElemento("inptObservacionDepreciacionInventarioInsumo").value
    };
    verCerrarEfectoCargando("1");
    inventarioLocalPeticion("nuevo/editar", valores).done(function (respuesta) {
        var datos = inventarioLocalParsearRespuesta(respuesta);
        if (datos["1"] !== "exito") {
            ver_vetana_informativa("No se pudo guardar", inventarioLocalMensajeError(datos), "error");
            return;
        }
        subirImagenesInsumosLocal(datos.cod_inventario);
    }).fail(inventarioLocalManejarErrorAjax).always(function () {
        verCerrarEfectoCargando("");
    });
}

function subirImagenesInsumosLocal(codigo) {
    inventarioLocalPeticion("cargar_imagen", {
        cod_inventario: codigo,
        fotos: [fotoInventario1, fotoInventario2, fotoInventario3],
        exts: [extInventario1, extInventario2, extInventario3],
        fotoFactura: fotoFacturaInventario,
        extFactura: extFacturaInventario,
        fotoCompromiso: fotoCompromisoInventario,
        extCompromiso: extCompromisoInventario
    }).done(function (respuesta) {
        var datos = inventarioLocalParsearRespuesta(respuesta);
        if (datos["1"] !== "exito") {
            ver_vetana_informativa("Activo guardado", "El registro se guardó, pero no se pudieron asociar todas las imágenes.", "info");
            return;
        }
        ver_vetana_informativa("Datos guardados", "El activo y su trazabilidad fueron actualizados.", "info");
        verCerrarAbmInventarioLocal(false, true);
        obtenerVistaInformeInsumoLocal(inventarioLocalEstado.pagina);
    }).fail(function (jqXHR, textstatus) {
        inventarioLocalManejarErrorAjax(jqXHR, textstatus);
        ver_vetana_informativa("Activo guardado", "El registro principal se guardó; revise los archivos adjuntos.", "info");
    });
}

function buscarHistorialResponsablesAnteriores(codigo) {
    var panel = inventarioLocalElemento("divHistorialResponsableInsumosLocal");
    var tabla = inventarioLocalElemento("divTableHistorialResponsableInsumosLocal");
    panel.style.display = "";
    tabla.innerHTML = '<tr><td style="padding:12px">Consultando historial…</td></tr>';
    inventarioLocalPeticion("buscarHistorialResponsablesAnteriores", {cod_inventario: codigo}).done(function (respuesta) {
        var datos = inventarioLocalParsearRespuesta(respuesta);
        if (datos["1"] === "exito" && datos["2"]) {
            tabla.innerHTML = datos["2"];
        } else {
            panel.style.display = "none";
        }
    }).fail(function () {
        panel.style.display = "none";
    });
}

function inventarioLocalAbrirVerificacion() {
    var registro = inventarioLocalEstado.seleccionado;
    if (!registro) {
        return;
    }
    if (!inventarioLocalEstado.estructuraControl) {
        ver_vetana_informativa("Verificación no habilitada", "Falta aplicar la migración de control de activos.", "info");
        return;
    }
    if (controlacceso("EDITARLISTADOINVENTARIOLOCAL", "accion") === false) {
        return;
    }
    var hoy = new Date();
    inventarioLocalElemento("fechaVerificacionInventarioLocal").value = hoy.getFullYear() + "-" + ("0" + (hoy.getMonth() + 1)).slice(-2) + "-" + ("0" + hoy.getDate()).slice(-2);
    inventarioLocalElemento("cantidadEsperadaVerificacionInventarioLocal").value = registro.cantidad || 0;
    inventarioLocalElemento("cantidadEncontradaVerificacionInventarioLocal").value = registro.cantidad || 0;
    inventarioLocalElemento("estadoFisicoVerificacionInventarioLocal").value = inventarioLocalTexto(registro.estado_fisico).toLowerCase() || "excelente";
    inventarioLocalElemento("observacionVerificacionInventarioLocal").value = "";
    inventarioLocalElemento("subtituloVerificacionInventarioLocal").textContent = inventarioLocalCodigo(registro.cod_insumo) + " · " + registro.nombre;
    inventarioLocalElemento("modalVerificacionInventarioLocal").removeAttribute("hidden");
}

function inventarioLocalCerrarVerificacion() {
    inventarioLocalElemento("modalVerificacionInventarioLocal").setAttribute("hidden", "hidden");
}

function inventarioLocalGuardarVerificacion() {
    var registro = inventarioLocalEstado.seleccionado;
    var fecha = inventarioLocalElemento("fechaVerificacionInventarioLocal").value;
    var cantidad = inventarioLocalElemento("cantidadEncontradaVerificacionInventarioLocal").value;
    var estado = inventarioLocalElemento("estadoFisicoVerificacionInventarioLocal").value;
    if (!registro || !fecha || cantidad === "" || Number(cantidad) < 0 || !estado) {
        ver_vetana_informativa("Faltan datos", "Complete fecha, cantidad encontrada y estado físico.", "error");
        return;
    }
    inventarioLocalPeticion("registrarVerificacion", {
        cod_inventario: registro.cod_insumo,
        fecha_verificacion: fecha,
        cantidad_encontrada: cantidad,
        estado_fisico: estado,
        observacion: inventarioLocalElemento("observacionVerificacionInventarioLocal").value
    }).done(function (respuesta) {
        var datos = inventarioLocalParsearRespuesta(respuesta);
        if (datos["1"] !== "exito") {
            ver_vetana_informativa("No se pudo registrar", inventarioLocalMensajeError(datos), "error");
            return;
        }
        inventarioLocalCerrarVerificacion();
        ver_vetana_informativa("Verificación registrada", "El conteo quedó trazado y la próxima fecha fue programada.", "info");
        obtenerVistaInformeInsumoLocal(inventarioLocalEstado.pagina);
    }).fail(inventarioLocalManejarErrorAjax);
}

function inventarioLocalTextoFiltroReporte() {
    var partes = [];
    var texto = inventarioLocalElemento("filtroTextoInventarioLocal").value.trim();
    var local = inventarioLocalElemento("filtroLocalInventarioLocal");
    var sector = inventarioLocalElemento("filtroSectorInventarioLocal");
    var responsable = inventarioLocalElemento("filtroResponsableInventarioLocal");
    var estado = inventarioLocalElemento("filtroEstadoFisicoInventarioLocal");
    if (texto) { partes.push("Búsqueda: " + texto); }
    if (local.value) { partes.push("Local: " + local.options[local.selectedIndex].text); }
    if (sector.value) { partes.push("Sector: " + sector.options[sector.selectedIndex].text); }
    if (responsable.value) { partes.push("Responsable: " + responsable.options[responsable.selectedIndex].text); }
    if (estado.value) { partes.push("Estado físico: " + estado.options[estado.selectedIndex].text); }
    return partes.length ? partes.join(" · ") : "Todos los activos visibles";
}

function inventarioLocalHtmlReporte(tipo, registros, resumen) {
    var esValorizado = tipo === "valorizado";
    var titulo = esValorizado ? "Listado valorizado de activos fijos" : "Planilla de conteo físico";
    var encabezados = esValorizado
        ? ["Código", "Activo", "Ubicación", "Responsable", "Cant.", "Costo cargado", "Valor total", "Depreciación", "Valor contable", "Estado físico", "Última verificación"]
        : ["Código", "Activo / serie", "Ubicación", "Responsable", "Esperado", "Encontrado", "Estado físico", "Observación"];
    var filas = "";
    registros.forEach(function (registro) {
        if (esValorizado) {
            var tipoCosto = registro.costo_tipo === "unitario" ? "por unidad" : (registro.costo_tipo === "lote" ? "total lote" : "pendiente de validar");
            filas += "<tr><td>" + inventarioLocalEscapar(inventarioLocalCodigo(registro.cod_insumo)) + "</td>"
                + "<td><b>" + inventarioLocalEscapar(registro.nombre) + "</b><small>" + inventarioLocalEscapar([registro.nombre_marca, registro.modelo, registro.nro_serie ? "Serie " + registro.nro_serie : ""].filter(Boolean).join(" · ")) + "</small></td>"
                + "<td>" + inventarioLocalEscapar([registro.nombreLocal, registro.nombre_sector].filter(Boolean).join(" · ")) + "</td>"
                + "<td><b>" + inventarioLocalEscapar(registro.nombre_usuario_responsable || "Sin asignar") + "</b><small>Cargó: " + inventarioLocalEscapar(registro.nombre_usuarioFK_create || "Sin dato") + "</small></td>"
                + "<td class='numero'>" + inventarioLocalEscapar(registro.cantidad) + "</td>"
                + "<td class='numero'>" + inventarioLocalEscapar(inventarioLocalFormatoNumero(registro.costo)) + "<small>" + inventarioLocalEscapar(tipoCosto) + "</small></td>"
                + "<td class='numero'>" + inventarioLocalEscapar(inventarioLocalFormatoNumero(registro.valor_total)) + "</td>"
                + "<td class='numero'>" + inventarioLocalEscapar(inventarioLocalFormatoNumero(registro.depreciacion_acumulada)) + "</td>"
                + "<td class='numero'>" + inventarioLocalEscapar(inventarioLocalFormatoNumero(registro.valor_contable)) + "</td>"
                + "<td>" + inventarioLocalEscapar(registro.estado_fisico || "Sin estado") + "</td>"
                + "<td>" + inventarioLocalEscapar(inventarioLocalFecha(registro.ultima_verificacion, false)) + "</td></tr>";
        } else {
            filas += "<tr><td>" + inventarioLocalEscapar(inventarioLocalCodigo(registro.cod_insumo)) + "</td>"
                + "<td><b>" + inventarioLocalEscapar(registro.nombre) + "</b><small>" + inventarioLocalEscapar(registro.nro_serie ? "Serie " + registro.nro_serie : "Sin serie") + "</small></td>"
                + "<td>" + inventarioLocalEscapar([registro.nombreLocal, registro.nombre_sector].filter(Boolean).join(" · ")) + "</td>"
                + "<td>" + inventarioLocalEscapar(registro.nombre_usuario_responsable || "Sin asignar") + "</td>"
                + "<td class='numero'>" + inventarioLocalEscapar(registro.cantidad) + "</td><td></td><td></td><td></td></tr>";
        }
    });
    var fecha = new Date();
    var fechaTexto = ("0" + fecha.getDate()).slice(-2) + "/" + ("0" + (fecha.getMonth() + 1)).slice(-2) + "/" + fecha.getFullYear() + " " + ("0" + fecha.getHours()).slice(-2) + ":" + ("0" + fecha.getMinutes()).slice(-2);
    var columnas = encabezados.map(function (encabezado) { return "<th>" + inventarioLocalEscapar(encabezado) + "</th>"; }).join("");
    var totales = esValorizado
        ? "<div class='totales'><span>Registros <b>" + inventarioLocalFormatoNumero(resumen.registros) + "</b></span><span>Unidades <b>" + inventarioLocalFormatoNumero(resumen.unidades) + "</b></span><span>Valor registrado <b>Gs. " + inventarioLocalFormatoNumero(resumen.valor_total) + "</b></span><span>Valor contable <b>Gs. " + inventarioLocalFormatoNumero(resumen.valor_contable) + "</b></span></div>"
        : "<div class='firmas'><span>Contó: ____________________</span><span>Revisó: ____________________</span><span>Fecha: ____/____/________</span></div>";
    return "<!doctype html><html lang='es'><head><meta charset='utf-8'><title>" + inventarioLocalEscapar(titulo) + "</title><style>"
        + "@page{size:A4 landscape;margin:10mm 9mm 12mm}*{box-sizing:border-box}body{margin:0;color:#152642;font:10px Arial,sans-serif}header{display:flex;justify-content:space-between;align-items:flex-start;padding:0 0 7px;border-bottom:2px solid #0b4fa5}header h1{margin:0;color:#10315d;font-size:18px}header p{margin:3px 0 0;color:#60708a;font-size:9px}.marca{text-align:right}.marca b{display:block;color:#0b4fa5;font-size:13px;letter-spacing:.6px}.meta{display:flex;justify-content:space-between;gap:12px;margin:7px 0;color:#4d5f78;font-size:8px}table{width:100%;border-collapse:collapse;table-layout:fixed}th{padding:6px 4px;border:1px solid #aac0dc;background:#0b5eae;color:#fff;font-size:7px;text-transform:uppercase}td{height:25px;padding:4px;border:1px solid #c9d3df;vertical-align:top;overflow-wrap:anywhere}tbody tr:nth-child(even) td{background:#f6f8fb}td b,td small{display:block}td small{margin-top:2px;color:#66758a;font-size:7px}.numero{text-align:right}.totales,.firmas{display:flex;justify-content:flex-end;gap:22px;margin-top:8px;padding-top:7px;border-top:1px solid #b9c7d8}.totales span{color:#5f6e82}.totales b{display:block;margin-top:2px;color:#142c4d;font-size:10px}.firmas{justify-content:space-between;margin-top:18px;font-size:9px}.aviso{margin-top:6px;color:#8a5909;font-size:7px}@media print{button{display:none}}"
        + (esValorizado ? "th:nth-child(1){width:6%}th:nth-child(2){width:17%}th:nth-child(3){width:12%}th:nth-child(4){width:12%}th:nth-child(5){width:4%}th:nth-child(6),th:nth-child(7),th:nth-child(8),th:nth-child(9){width:8%}th:nth-child(10){width:8%}th:nth-child(11){width:9%}" : "th:nth-child(1){width:7%}th:nth-child(2){width:22%}th:nth-child(3){width:15%}th:nth-child(4){width:14%}th:nth-child(5),th:nth-child(6){width:7%}th:nth-child(7){width:12%}th:nth-child(8){width:16%}")
        + "</style></head><body><header><div><h1>" + inventarioLocalEscapar(titulo) + "</h1><p>Control físico y contable del patrimonio</p></div><div class='marca'><b>CLINIDENT SALUD</b><span>Gestión de activos fijos</span></div></header>"
        + "<div class='meta'><span>" + inventarioLocalEscapar(inventarioLocalTextoFiltroReporte()) + "</span><span>Emitido: " + inventarioLocalEscapar(fechaTexto) + "</span></div>"
        + "<table><thead><tr>" + columnas + "</tr></thead><tbody>" + filas + "</tbody></table>" + totales
        + (registros.length < Number(resumen.registros || 0) ? "<p class='aviso'>El reporte alcanzó el límite técnico de 5.000 filas. Refine los filtros para imprimir el universo completo.</p>" : "")
        + "<script>window.onload=function(){window.focus();window.print();};<\/script></body></html>";
}

function imprimirInventarioLocal(tipo) {
    var menu = inventarioLocalElemento("menuImpresionInventarioLocal");
    menu.setAttribute("hidden", "hidden");
    var ventana = window.open("", "_blank");
    if (!ventana) {
        ver_vetana_informativa("Impresión bloqueada", "Permita ventanas emergentes para generar el reporte.", "error");
        return;
    }
    ventana.document.write("<p style='font:14px Arial;padding:20px'>Preparando reporte…</p>");
    var filtros = inventarioLocalFiltros();
    delete filtros.pagina;
    delete filtros.limite;
    inventarioLocalPeticion("buscarReporte", filtros).done(function (respuesta) {
        var datos = inventarioLocalParsearRespuesta(respuesta);
        if (datos["1"] !== "exito") {
            ventana.close();
            ver_vetana_informativa("No se pudo imprimir", inventarioLocalMensajeError(datos), "error");
            return;
        }
        ventana.document.open();
        ventana.document.write(inventarioLocalHtmlReporte(tipo, datos.registros || [], datos.resumen || {}));
        ventana.document.close();
    }).fail(function (jqXHR, textstatus) {
        ventana.close();
        inventarioLocalManejarErrorAjax(jqXHR, textstatus);
    });
}
