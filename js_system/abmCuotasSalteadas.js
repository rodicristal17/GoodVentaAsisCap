(function () {
    "use strict";

    var estado = { pagina: 1, paginas: 1, limite: 25, filas: [], cargando: false, localesCargados: false };

    function el(id) { return document.getElementById(id); }
    function texto(valor) {
        return String(valor == null ? "" : valor).replace(/[&<>"']/g, function (c) {
            return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" }[c];
        });
    }
    function numero(valor) { return Number(valor || 0).toLocaleString("es-PY"); }
    function fecha(valor) {
        if (!valor) return "Sin fecha";
        var partes = String(valor).split("-");
        return partes.length === 3 ? partes[2] + "/" + partes[1] + "/" + partes[0] : valor;
    }
    function credenciales(datos) {
        datos.append("useru", typeof userid !== "undefined" ? userid : "");
        datos.append("passu", typeof passuser !== "undefined" ? passuser : "");
        datos.append("navegador", typeof navegador !== "undefined" ? navegador : "");
        return datos;
    }
    function aviso(mensaje, tipo) {
        var caja = el("cuotasSalteadasAviso");
        if (!caja) return;
        caja.hidden = !mensaje;
        caja.className = "cs-notice cs-notice--" + (tipo || "info");
        caja.textContent = mensaje || "";
    }
    function avisoCodigo(mensaje) { var n = el("csCodigoHistorialAviso"); if (!n) return; n.hidden = !mensaje; n.textContent = mensaje || ""; }
    function setCargando(activo) {
        estado.cargando = activo;
        var nodo = el("cuotasSalteadasCargando");
        if (nodo) nodo.hidden = !activo;
        var boton = el("btnCuotasSalteadasBuscar");
        if (boton) boton.disabled = activo;
    }
    function manejarRespuestaError(respuesta) {
        if (respuesta && respuesta["1"] === "UI") {
            if (typeof sesionFinalizada === "function") sesionFinalizada();
            else window.location.href = "/GoodVentaAsisCap/system/login.php?cerrar=1";
            return true;
        }
        if (!respuesta || respuesta["1"] !== "exito") {
            aviso((respuesta && (respuesta["2"] || respuesta.detalle)) || "No se pudo cargar el informe.", "error");
            return true;
        }
        return false;
    }
    function filtros(limite, pagina) {
        var datos = credenciales(new FormData());
        datos.append("funt", "listar");
        datos.append("pagina", pagina || estado.pagina);
        datos.append("limite", limite || estado.limite);
        ["situacion", "buscar", "venta", "local", "desde", "hasta"].forEach(function (campo) {
            var nodo = el("csFiltro" + campo.charAt(0).toUpperCase() + campo.slice(1));
            datos.append(campo, nodo ? nodo.value : "");
        });
        return datos;
    }
    function cargarLocales(respuesta) {
        var select = el("csFiltroLocal");
        if (!select || estado.localesCargados) return;
        if (!respuesta.puede_cambiar_local) {
            select.closest("label").hidden = true;
            estado.localesCargados = true;
            return;
        }
        select.innerHTML = '<option value="">Todos los locales</option>' + (respuesta.locales || []).map(function (local) {
            return '<option value="' + local.id + '">' + texto(local.nombre) + "</option>";
        }).join("");
        estado.localesCargados = true;
    }
    function renderResumen(resumen, total) {
        resumen = resumen || {};
        var valores = {
            csResumenClientes: numero(resumen.clientes), csResumenVentas: numero(total),
            csResumenCuotas: numero(resumen.cuotas), csResumenSaldo: "Gs. " + numero(resumen.saldo),
            csResumenVencidas: numero(resumen.vencidas), csResumenParciales: numero(resumen.parciales)
        };
        Object.keys(valores).forEach(function (id) { if (el(id)) el(id).textContent = valores[id]; });
    }
    function renderTabla(filas) {
        var cuerpo = el("cuotasSalteadasTablaCuerpo");
        var vacio = el("cuotasSalteadasVacio");
        if (!cuerpo) return;
        estado.filas = filas || [];
        cuerpo.innerHTML = estado.filas.map(function (fila) {
            var vencida = fila.primer_vencimiento && fila.primer_vencimiento < new Date().toISOString().slice(0, 10);
            return '<tr>' +
                '<td><b>' + texto(fila.cliente || ("Cliente " + fila.cliente_id)) + '</b><small>Doc. ' + texto(fila.documento || "Sin documento") + '<br>' + texto(fila.telefono || "Sin teléfono") + '</small></td>' +
                '<td><b>#' + fila.venta + '</b><small>' + texto(fila.factura || "Sin factura") + '<br>' + texto(fila.local || "Sin local") + '</small></td>' +
                '<td><span class="cs-sequence cs-sequence--paid">' + texto(fila.cuotas_pagadas || "-") + '</span></td>' +
                '<td><span class="cs-sequence cs-sequence--gap">' + texto(fila.cuotas_pendientes || "-") + '</span><small>' + fila.cuotas_salteadas + ' cuota(s), ' + fila.cuotas_parciales + ' parcial(es)</small></td>' +
                '<td><span class="cs-due ' + (vencida ? "is-overdue" : "") + '">' + fecha(fila.primer_vencimiento) + '</span></td>' +
                '<td>' + (Number(fila.tiene_entrega) === 1 ? '<span class="cs-delivery"><b>Entrega: Gs. ' + numero(fila.monto_entrega) + '</b><small>Pagado: Gs. ' + numero(fila.pagado_entrega) + '</small></span>' : '<span class="cs-no-delivery">—</span>') + '</td>' +
                '<td class="cs-money">Gs. ' + numero(fila.saldo) + '</td>' +
                '<td><div class="cs-row-actions"><button type="button" onclick="cuotasSalteadasDetalle(' + fila.venta + ')">Ver detalle</button><button type="button" class="is-warning" onclick="cuotasSalteadasSolucionar(' + fila.venta + ')">Solucionar</button><button type="button" class="is-primary" onclick="cuotasSalteadasCobrar(' + fila.venta + ')">Cobrar cuota</button></div></td>' +
                '</tr>';
        }).join("");
        if (vacio) vacio.hidden = estado.filas.length > 0;
    }
    function renderPaginacion() {
        var nodo = el("cuotasSalteadasPaginacion");
        if (!nodo) return;
        nodo.innerHTML = '<button type="button" ' + (estado.pagina <= 1 ? "disabled" : "") + ' onclick="cuotasSalteadasPagina(' + (estado.pagina - 1) + ')">Anterior</button>' +
            '<span>Página <b>' + estado.pagina + '</b> de <b>' + estado.paginas + '</b></span>' +
            '<button type="button" ' + (estado.pagina >= estado.paginas ? "disabled" : "") + ' onclick="cuotasSalteadasPagina(' + (estado.pagina + 1) + ')">Siguiente</button>';
    }
    function cargar(limite, callback, pagina) {
        if (estado.cargando) return;
        setCargando(true); aviso("");
        fetch("/GoodVentaAsisCap/php_system/abmCuotasSalteadas.php", { method: "POST", body: filtros(limite, pagina), credentials: "same-origin" })
            .then(function (r) { return r.text(); })
            .then(function (t) { try { return JSON.parse(t); } catch (e) { throw new Error("Respuesta inválida del servidor"); } })
            .then(function (r) {
                if (manejarRespuestaError(r)) return;
                if (typeof callback === "function") { callback(r); return; }
                estado.pagina = Number(r.pagina || 1); estado.paginas = Number(r.paginas || 1);
                cargarLocales(r); renderResumen(r.resumen, r.total); renderTabla(r.filas); renderPaginacion();
            })
            .catch(function (e) { aviso(e.message || "No se pudo cargar el informe.", "error"); })
            .finally(function () { setCargando(false); });
    }
    function detalle(venta) {
        var datos = credenciales(new FormData()); datos.append("funt", "detalle"); datos.append("venta", venta);
        var panel = el("cuotasSalteadasDetalle"); var contenido = el("cuotasSalteadasDetalleContenido");
        if (panel) panel.hidden = false;
        if (contenido) contenido.innerHTML = '<div class="cs-detail-loading">Cargando detalle...</div>';
        fetch("/GoodVentaAsisCap/php_system/abmCuotasSalteadas.php", { method: "POST", body: datos, credentials: "same-origin" })
            .then(function (r) { return r.json(); }).then(function (r) {
                if (manejarRespuestaError(r) || !contenido) return;
                var entregas = (r.cuotas || []).filter(function (c) { return Number(c.es_entrega) === 1; }).length;
                contenido.innerHTML = '<div class="cs-detail-summary"><b>Venta #' + venta + '</b><span>' + (r.cuotas.length - entregas) + ' cuotas' + (entregas ? ' + ' + entregas + ' entrega(s)' : '') + '</span></div>' +
                    '<div class="cs-detail-legend"><span class="is-delivery">Entrega</span><span class="is-paid">Pagada</span><span class="is-skipped">Salteada</span><span class="is-pending">Pendiente normal</span></div>' +
                    '<div class="cs-detail-list">' + r.cuotas.map(function (c) {
                        var esEntrega = Number(c.es_entrega) === 1;
                        var esSalteada = Number(c.salteada) === 1;
                        var clase = esEntrega ? "delivery" : (Number(c.sin_monto) === 1 ? "zero" : (esSalteada ? "skipped" : (c.estado === "Pagada" ? "paid" : (c.estado === "Pago parcial" ? "partial" : (c.estado === "Vencida" ? "overdue" : "pending")))));
                        var estadoCuota = esSalteada ? (c.estado === "Pago parcial" ? "Salteada · Pago parcial" : "Salteada") : c.estado;
                        return '<article class="cs-detail-item is-' + clase + '"><div><b>Cuota ' + texto(c.plazo) + '</b><span>Vencimiento: ' + fecha(c.vencimiento) + '</span></div><span class="cs-status">' + texto(estadoCuota) + '</span><div class="cs-detail-amounts"><span>Capital: Gs. ' + numero(c.capital_pagado) + ' / ' + numero(c.capital_debido) + '</span><span>Interés: Gs. ' + numero(c.interes_pagado) + ' / ' + numero(c.interes_debido) + '</span><span>Total pagado: Gs. ' + numero(Number(c.capital_pagado) + Number(c.interes_pagado)) + '</span><b>Saldo: Gs. ' + numero(c.saldo) + '</b></div></article>';
                    }).join("") + '</div><button type="button" class="cs-button cs-button--primary cs-detail-pay" onclick="cuotasSalteadasCobrar(' + venta + ')">Abrir Cobrar cuota</button>';
            }).catch(function () { if (contenido) contenido.innerHTML = '<div class="cs-empty">No se pudo cargar el detalle.</div>'; });
    }
    function exportar() {
        cargar(5000, function (r) {
            var columnas = ["Cliente", "Documento", "Telefono", "Venta", "Factura", "Local", "Cuotas pagadas", "Cuotas salteadas", "Primer vencimiento", "Entrega", "Entrega pagada", "Saldo"];
            var csv = [columnas].concat((r.filas || []).map(function (f) {
                return [f.cliente, f.documento, f.telefono, f.venta, f.factura, f.local, f.cuotas_pagadas, f.cuotas_pendientes, f.primer_vencimiento, Number(f.tiene_entrega) === 1 ? f.monto_entrega : "-", Number(f.tiene_entrega) === 1 ? f.pagado_entrega : "-", f.saldo];
            })).map(function (fila) { return fila.map(function (v) { return '"' + String(v == null ? "" : v).replace(/"/g, '""') + '"'; }).join(";"); }).join("\r\n");
            var blob = new Blob(["\ufeff" + csv], { type: "text/csv;charset=utf-8" });
            var enlace = document.createElement("a"); enlace.href = URL.createObjectURL(blob); enlace.download = "cuotas_salteadas.csv"; enlace.click(); URL.revokeObjectURL(enlace.href);
        }, 1);
    }
    function imprimirFilas(filasDatos) {
        var ventana = window.open("", "_blank");
        if (!ventana) { aviso("El navegador bloqueó la ventana de impresión.", "error"); return; }
        var filas = (filasDatos || []).map(function (f) { var entrega = Number(f.tiene_entrega) === 1 ? 'Gs. ' + numero(f.monto_entrega) + '<br><small>Pagado: Gs. ' + numero(f.pagado_entrega) + '</small>' : '—'; return '<tr><td>' + texto(f.cliente) + '</td><td>#' + f.venta + '</td><td>' + texto(f.cuotas_pagadas) + '</td><td>' + texto(f.cuotas_pendientes) + '</td><td>' + entrega + '</td><td>Gs. ' + numero(f.saldo) + '</td></tr>'; }).join("");
        ventana.document.write('<!doctype html><html><head><meta charset="utf-8"><title>Cuotas salteadas</title><style>body{font:13px Arial;color:#172033}h1{font-size:20px}table{width:100%;border-collapse:collapse}th,td{padding:8px;border:1px solid #ccd5e1;text-align:left}th{background:#102a43;color:white}</style></head><body><h1>Informe de cuotas salteadas</h1><p>Generado: ' + new Date().toLocaleString("es-PY") + '</p><table><thead><tr><th>Cliente</th><th>Venta</th><th>Pagadas</th><th>Salteadas</th><th>Entrega</th><th>Saldo</th></tr></thead><tbody>' + filas + '</tbody></table></body></html>');
        ventana.document.close(); ventana.focus(); ventana.print();
    }
    function imprimir() { cargar(5000, function (r) { imprimirFilas(r.filas || []); }, 1); }

    function registrarHistorial(codigo) {
        if (estado.cargando) return;
        var datos = filtros(5000, 1);
        datos.set("funt", "registrar_historial");
        datos.set("codigo_seguridad", codigo || "");
        setCargando(true); aviso("");
        fetch("/GoodVentaAsisCap/php_system/abmCuotasSalteadas.php", { method: "POST", body: datos, credentials: "same-origin" })
            .then(function (r) { return r.text(); })
            .then(function (t) { try { return JSON.parse(t); } catch (e) { throw new Error("Respuesta inv\u00e1lida del servidor"); } })
            .then(function (r) {
                if (r && r["1"] === "UI") { manejarRespuestaError(r); return; }
                if (!r || r["1"] !== "exito") { avisoCodigo((r && r["2"]) || "No se pudo validar el c\u00f3digo."); var i = el("csCodigoHistorial"); if (i) { i.value = ""; i.focus(); } return; }
                window.cuotasSalteadasCerrarCodigo();
                aviso("Historial actualizado: " + numero(r.insertados) + " registro(s) nuevo(s) y " + numero(r.existentes) + " ya existente(s).", "info");
            })
            .catch(function (e) { aviso(e.message || "No se pudo registrar el historial.", "error"); })
            .finally(function () { setCargando(false); });
    }

    function regularizarPeticion(operacion, venta, extras) {
        var datos = credenciales(new FormData()); datos.append("funt", operacion); datos.append("venta", venta);
        Object.keys(extras || {}).forEach(function (k) { datos.append(k, extras[k]); });
        return fetch("/GoodVentaAsisCap/php_system/abmRegularizarPagosSalteados.php", { method: "POST", body: datos, credentials: "same-origin" })
            .then(function (r) { return r.text(); }).then(function (t) { try { return JSON.parse(t); } catch (e) { throw new Error("Respuesta inv\u00e1lida del servidor"); } });
    }
    function mostrarRegularizacion(r) {
        var modal = el("csRegularizarModal"), contenido = el("csRegularizarContenido"), aplicar = el("csRegularizarAplicar");
        if (!modal || !contenido) return;
        modal.hidden = false;
        if (!r || r["1"] !== "exito") {
            contenido.innerHTML = '<div class="cs-regularize-blocked"><b>No se puede regularizar</b><p>' + texto((r && r["2"]) || "No se pudo preparar la vista previa.") + '</p></div>';
            if (aplicar) aplicar.hidden = true; return;
        }
        estado.regularizacion = r;
        contenido.innerHTML = '<div class="cs-regularize-summary"><b>Venta #' + r.venta + '</b><span>Se reasignar\u00e1n Gs. ' + numero(r.monto) + '</span></div>' +
            '<p class="cs-regularize-help">Prioridad de origen: entrega y luego cuotas posteriores. Destino: cuotas salteadas desde la m\u00e1s antigua. No se modifican intereses ni el total cobrado.</p>' +
            '<div class="cs-regularize-grid"><section><h4>Cuotas a completar</h4>' + (r.destinos || []).map(function (d) { return '<div><b>Cuota ' + texto(d.plazo) + '</b><span>Gs. ' + numero(d.saldo) + '</span></div>'; }).join("") + '</section>' +
            '<section><h4>Pagos que se mover\u00e1n</h4>' + (r.origenes || []).map(function (o) { return '<div><b>' + texto(o.origen) + ' \u00b7 Pago #' + o.pago + '</b><span>Gs. ' + numero(o.monto) + '</span><small>' + (o.destinos || []).map(function (d) { return 'Cuota ' + d.cuota + ': Gs. ' + numero(d.monto); }).join(' / ') + '</small></div>'; }).join("") + '</section></div>';
        if (aplicar) aplicar.hidden = false;
    }

    window.verCerrarCuotasSalteadas = function (abrir) {
        var modal = el("divCuotasSalteadas"); if (!modal) return;
        modal.style.display = abrir ? "flex" : "none";
        if (abrir) { estado.pagina = 1; cargar(); setTimeout(function () { var b = el("csFiltroBuscar"); if (b) b.focus(); }, 80); }
        else window.cuotasSalteadasCerrarDetalle();
    };
    window.cuotasSalteadasBuscar = function () { estado.pagina = 1; cargar(); };
    window.cuotasSalteadasPagina = function (pagina) { if (pagina >= 1 && pagina <= estado.paginas) { estado.pagina = pagina; cargar(); } };
    window.cuotasSalteadasDetalle = detalle;
    window.cuotasSalteadasCerrarDetalle = function () { var p = el("cuotasSalteadasDetalle"); if (p) p.hidden = true; };
    window.cuotasSalteadasCobrar = function (venta) { if (typeof verCerrarCobrarCuota === "function") { window.verCerrarCuotasSalteadas(false); verCerrarCobrarCuota("1", { origen: "cuentas", venta: venta }); } else aviso("No se pudo abrir Cobrar cuota.", "error"); };
    window.cuotasSalteadasSolucionar = function (venta) { var m = el("csRegularizarModal"), c = el("csRegularizarContenido"); if (m) m.hidden = false; if (c) c.innerHTML = '<div class="cs-detail-loading">Calculando una soluci\u00f3n reversible...</div>'; regularizarPeticion("preview", venta).then(mostrarRegularizacion).catch(function (e) { mostrarRegularizacion({ "1": "error", "2": e.message }); }); };
    window.cuotasSalteadasCerrarRegularizacion = function () { var m = el("csRegularizarModal"); if (m) m.hidden = true; estado.regularizacion = null; };
    window.cuotasSalteadasAplicarRegularizacion = function () { var r = estado.regularizacion; if (!r || !window.confirm("\u00bfConfirma la reasignaci\u00f3n de pagos de la venta #" + r.venta + "? Podr\u00e1 revertirla despu\u00e9s.")) return; var b = el("csRegularizarAplicar"); if (b) b.disabled = true; regularizarPeticion("aplicar", r.venta, { huella: r.huella }).then(function (x) { if (!x || x["1"] !== "exito") { throw new Error((x && x["2"]) || "No se pudo aplicar."); } var c = el("csRegularizarContenido"); if (c) c.innerHTML = '<div class="cs-regularize-success"><b>Regularizaci\u00f3n #' + x.regularizacion + ' aplicada</b><p>Los pagos quedaron reasignados. Use Revertir para restaurarlos exactamente.</p><button type="button" class="cs-button is-danger" onclick="cuotasSalteadasRevertir(' + r.venta + ',' + x.regularizacion + ')">Revertir ahora</button></div>'; if (b) b.hidden = true; cargar(); }).catch(function (e) { aviso(e.message, "error"); }).finally(function () { if (b) b.disabled = false; }); };
    window.cuotasSalteadasRevertir = function (venta, id) { if (!window.confirm("\u00bfRevertir exactamente la regularizaci\u00f3n #" + id + "?")) return; regularizarPeticion("revertir", venta, { regularizacion: id }).then(function (x) { if (!x || x["1"] !== "exito") throw new Error((x && x["2"]) || "No se pudo revertir."); aviso("Regularizaci\u00f3n #" + id + " revertida.", "info"); window.cuotasSalteadasCerrarRegularizacion(); cargar(); }).catch(function (e) { aviso(e.message, "error"); }); };
    window.cuotasSalteadasExportar = exportar;
    window.cuotasSalteadasImprimir = imprimir;
    window.cuotasSalteadasSolicitarCodigo = function () { var p = el("csCodigoHistorialModal"), i = el("csCodigoHistorial"); avisoCodigo(""); if (p) p.hidden = false; if (i) { i.value = ""; setTimeout(function () { i.focus(); }, 50); } };
    window.cuotasSalteadasCerrarCodigo = function () { var p = el("csCodigoHistorialModal"), i = el("csCodigoHistorial"); if (p) p.hidden = true; if (i) i.value = ""; avisoCodigo(""); };
    window.cuotasSalteadasConfirmarCodigo = function () { var i = el("csCodigoHistorial"); registrarHistorial(i ? i.value : ""); };
    window.cuotasSalteadasLimpiar = function () { ["Buscar", "Venta", "Local", "Desde", "Hasta"].forEach(function (n) { if (el("csFiltro" + n)) el("csFiltro" + n).value = ""; }); if (el("csFiltroSituacion")) el("csFiltroSituacion").value = "todas"; estado.pagina = 1; cargar(); };
    document.addEventListener("keydown", function (evento) {
        if (evento.key !== "Escape") return;
        var detalleModal = el("cuotasSalteadasDetalle");
        if (detalleModal && !detalleModal.hidden) {
            evento.preventDefault();
            window.cuotasSalteadasCerrarDetalle();
            return;
        }
        var codigoModal = el("csCodigoHistorialModal");
        if (codigoModal && !codigoModal.hidden) window.cuotasSalteadasCerrarCodigo();
        var regularizarModal = el("csRegularizarModal");
        if (regularizarModal && !regularizarModal.hidden) window.cuotasSalteadasCerrarRegularizacion();
    });
})();
