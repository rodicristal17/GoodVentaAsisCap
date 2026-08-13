function abrirErroresSistema() {
    var ventana = document.getElementById('divErroresSistema');
    if (!ventana) return;
    ventana.style.display = 'block';
    buscarErroresSistema();
}

function actualizarAccesoErroresSistema() {
    var menu = document.getElementById('divMenuErroresSistema');
    if (!menu) return;
    var permiso = window.accesosuser && accesosuser['VERERRORESSISTEMA'];
    menu.style.display = permiso && permiso['accion'] === 'SI' ? '' : 'none';
    if (typeof actualizarContadorSistema === 'function') actualizarContadorSistema();
}

function cerrarErroresSistema() {
    var ventana = document.getElementById('divErroresSistema');
    if (ventana) ventana.style.display = 'none';
}

function escaparErrorSistema(valor) {
    var div = document.createElement('div');
    div.textContent = valor == null ? '' : String(valor);
    return div.innerHTML;
}

function buscarErroresSistema() {
    obtener_datos_user();
    var datos = new FormData();
    datos.append('useru', userid);
    datos.append('passu', passuser);
    datos.append('navegador', navegador);
    datos.append('nivel', document.getElementById('selErrorSistemaNivel').value);
    datos.append('buscar', document.getElementById('inptErrorSistemaBuscar').value);
    datos.append('desde', document.getElementById('inptErrorSistemaDesde').value);
    $.ajax({
        data: datos,
        url: '/GoodVentaAsisCap/php_system/abmErroresSistema.php',
        type: 'post', cache: false, contentType: false, processData: false,
        success: function (texto) {
            var respuesta;
            try { respuesta = $.parseJSON(texto); } catch (e) { ver_vetana_informativa('Respuesta inválida del monitor de errores.'); return; }
            if (respuesta['1'] === 'UI') { ir_a_login(); return; }
            if (respuesta['1'] !== 'exito') { ver_vetana_informativa(respuesta['2'] || 'No se pudieron consultar los errores.'); return; }
            var filas = respuesta['2'] || [];
            var html = '';
            for (var i = 0; i < filas.length; i++) {
                var fila = filas[i];
                html += '<tr><td>'+escaparErrorSistema(fila.fecha)+'</td><td><span class="error-sistema-nivel error-sistema-nivel--'+escaparErrorSistema(fila.nivel)+'">'+escaparErrorSistema(fila.nivel)+'</span></td><td>'+escaparErrorSistema(fila.id)+'</td><td>'+escaparErrorSistema(fila.ruta)+'</td><td>'+escaparErrorSistema(fila.mensaje)+'</td><td>'+escaparErrorSistema(fila.archivo)+':'+escaparErrorSistema(fila.linea)+'</td></tr>';
            }
            document.getElementById('tbodyErroresSistema').innerHTML = html || '<tr><td colspan="6" class="error-sistema-vacio">No hay errores para los filtros seleccionados.</td></tr>';
            document.getElementById('totalErroresSistema').textContent = respuesta['3'] || 0;
        },
        error: function () { ver_vetana_informativa('No se pudo conectar con el monitor de errores.'); }
    });
}
