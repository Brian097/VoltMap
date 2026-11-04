// view/js/mapaCargadores.js
// Mapa de VoltMap: marcadores, filtros, estado en tiempo real, reportes de la comunidad e inicio de carga.
// Variables que inyecta mapa.php: VM_T (textos ES/EN), VM_USER (null = invitado), VM_VEHICULOS, VM_CSRF.
(function () {
    'use strict';

    const T = window.VM_T || {};
    const USER = window.VM_USER || null;
    const VEHICULOS = window.VM_VEHICULOS || [];
    const CSRF = window.VM_CSRF || '';
    const INTERVALO_MS = 15000;               // refresco automático ("tiempo real")
    const COLORES = { disponible: '#22c55e', en_uso: '#f59e0b', fuera: '#ef4444', sin_datos: '#94a3b8' };

    const t = (k) => (T[k] !== undefined ? T[k] : k);
    const fmt = (txt, vars) => txt.replace(/\{(\w+)\}/g, (_, k) => (vars[k] !== undefined ? vars[k] : ''));
    const esc = (s) => String(s === null || s === undefined ? '' : s)
        .replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const filtros = { estado: 'todos', potenciaMin: 0, conector: '', origen: 'todos' };
    let map, capa, puntos = [], firma = '', popupAbierto = false, cargadorSel = null;

    /* ---------- Estado de cada cargador / punto ---------- */
    function estadoCargador(c) {
        const op = (c.estadoOperativo || '').toLowerCase();
        const uso = (c.estadoUso || '').toLowerCase();
        if (op.includes('fuera') || op.includes('out') || op.includes('fault')) return 'fuera';
        if (uso.includes('uso') || uso.includes('ocupado')) return 'en_uso';
        return 'disponible';
    }
    function estadoPunto(p) {
        if (!p.cargadores || p.cargadores.length === 0) return 'sin_datos';
        const est = p.cargadores.map(estadoCargador);
        if (est.includes('disponible')) return 'disponible';
        if (est.includes('en_uso')) return 'en_uso';
        return 'fuera';
    }
    const etiqueta = (e) => ({ disponible: t('vm_disponible'), en_uso: t('vm_en_uso'), fuera: t('vm_fuera'), sin_datos: t('vm_sin_datos') }[e]);

    /* ---------- Filtros ---------- */
    function cumpleCargador(c) {
        if (filtros.estado !== 'todos' && estadoCargador(c) !== filtros.estado) return false;
        if (filtros.potenciaMin > 0 && parseFloat(c.potenciaKilowatts) < filtros.potenciaMin) return false;
        if (filtros.conector && (c.tipoConector || '') !== filtros.conector) return false;
        return true;
    }
    function cumplePunto(p) {
        if (filtros.origen === 'privado' && !p.esPrivado) return false;
        if (filtros.origen === 'publico' && p.esPrivado) return false;
        const sinFiltroCargador = filtros.estado === 'todos' && filtros.potenciaMin <= 0 && !filtros.conector;
        if (sinFiltroCargador) return true;
        return (p.cargadores || []).some(cumpleCargador);
    }

    /* ---------- Popup ---------- */
    function htmlCargador(p, c) {
        const est = estadoCargador(c);
        let h = `<div class="vm-c"><b>${esc(c.tipoConector)}</b> · ${esc(c.potenciaKilowatts)} kW · ${esc(c.tipoCargador)}`
            + `<span class="vm-badge ${est}">${esc(etiqueta(est))}</span><br>`;
        if (parseFloat(c.precioKwh) > 0) h += `$${esc(c.precioKwh)} ${esc(t('vm_precio_kwh'))}<br>`;
        if (parseFloat(c.precioHora) > 0) h += `$${esc(c.precioHora)} ${esc(t('vm_precio_hora'))}<br>`;
        if (c.filaEspera > 0) h += `${esc(t('vm_fila'))}: ${c.filaEspera} ${esc(t('vm_vehiculos_fila'))}<br>`;
        if (est === 'en_uso' && c.minutosRestantes !== null && c.minutosRestantes !== undefined) {
            h += `⏱ ${esc(t('vm_libre_en'))}${c.minutosRestantes} ${esc(t('vm_min'))}<br>`;
        }
        if (USER) {
            h += '<div class="vm-acciones">'
                + `<button class="vm-btn sec" data-vm="reportar" data-id="${esc(c.id)}">${esc(t('vm_reportar'))}</button>`;
            if (p.esPrivado && !p.esMio && est === 'disponible') {
                h += `<button class="vm-btn verde" data-vm="carga" data-id="${esc(c.id)}">${esc(t('vm_iniciar_carga'))}</button>`;
            }
            h += '</div>';
        }
        return h + '</div>';
    }
    function htmlPopup(p) {
        const cs = p.cargadores || [];
        let h = `<div class="vm-pop"><h4>${esc(p.direccion)}</h4><p class="vm-sub">${esc(p.ciudadYDepartamento)}</p>`
            + `<span class="vm-tag ${p.esPrivado ? 'priv' : ''}">${esc(p.esPrivado ? t('vm_privado') : t('vm_publico'))}</span>`
            + `<div style="margin-top:8px"><strong style="font-size:12px">${esc(t('vm_conectores'))} (${cs.length}):</strong></div>`;
        h += cs.length ? cs.map((c) => htmlCargador(p, c)).join('') : `<div class="vm-nota">${esc(t('vm_sin_cargadores'))}</div>`;
        if (!USER) h += `<div class="vm-nota">${esc(t('vm_login_para_reportar'))}</div>`;
        else if (!p.esPrivado) h += `<div class="vm-nota">${esc(t('vm_publico_sin_reserva'))}</div>`;
        return h + '</div>';
    }

    /* ---------- Dibujo del mapa ---------- */
    function dibujar() {
        capa.clearLayers();
        let visibles = 0;
        puntos.forEach((p) => {
            if (!cumplePunto(p)) return;
            visibles++;
            const est = estadoPunto(p);
            // Color = estado. Borde azul grueso = cargador privado (se distingue del público)
            L.circleMarker([parseFloat(p.latitud), parseFloat(p.longitud)], {
                radius: p.esPrivado ? 10 : 8,
                fillColor: COLORES[est],
                color: p.esPrivado ? '#1d4ed8' : '#ffffff',
                weight: p.esPrivado ? 4 : 2,
                opacity: 1,
                fillOpacity: 0.9
            }).bindPopup(htmlPopup(p)).addTo(capa);
        });
        const aviso = document.getElementById('vm-vacio');
        if (aviso) aviso.classList.toggle('on', puntos.length > 0 && visibles === 0);
        actualizarConectores();
    }

    async function cargar(forzar) {
        try {
            const r = await fetch('/app/controller/api_puntos.php', { cache: 'no-store' });
            const datos = await r.json();
            if (!Array.isArray(datos)) { console.error('Formato inesperado:', datos); return; }
            const nueva = JSON.stringify(datos);
            if (!forzar && nueva === firma) return;       // nada cambió: no redibujar
            firma = nueva;
            puntos = datos;
            dibujar();
        } catch (e) {
            console.error('Error al cargar los puntos:', e);
        }
    }

    /* ---------- Panel "Más filtros" y chips ---------- */
    function crearPanelFiltros() {
        const barra = document.querySelector('.filtros-bar');
        if (!barra) return;                                // el mapa de invitados no tiene barra de filtros
        const panel = document.createElement('div');
        panel.className = 'vm-mas';
        panel.id = 'vm-mas';
        panel.innerHTML =
            `<label>${esc(t('vm_filtro_potencia'))}<input type="number" id="vm-f-pot" min="0" step="1" value="0"></label>`
            + `<label>${esc(t('vm_filtro_conector'))}<select id="vm-f-con"><option value="">${esc(t('vm_todos'))}</option></select></label>`
            + `<label>${esc(t('vm_filtro_origen'))}<select id="vm-f-ori"><option value="todos">${esc(t('vm_todos'))}</option>`
            + `<option value="publico">${esc(t('vm_publico'))}</option><option value="privado">${esc(t('vm_privado'))}</option></select></label>`
            + `<button type="button" class="vm-btn sec" id="vm-f-limpiar">${esc(t('vm_limpiar'))}</button>`;
        barra.insertAdjacentElement('afterend', panel);
        document.getElementById('vm-f-pot').addEventListener('input', (e) => { filtros.potenciaMin = parseFloat(e.target.value) || 0; dibujar(); });
        document.getElementById('vm-f-con').addEventListener('change', (e) => { filtros.conector = e.target.value; dibujar(); });
        document.getElementById('vm-f-ori').addEventListener('change', (e) => { filtros.origen = e.target.value; dibujar(); });
        document.getElementById('vm-f-limpiar').addEventListener('click', limpiarFiltros);

        const chips = barra.querySelectorAll('[data-vm-estado]');
        chips.forEach((chip) => chip.addEventListener('click', () => {
            chips.forEach((c) => c.classList.remove('sel'));
            chip.classList.add('sel');
            filtros.estado = chip.dataset.vmEstado;
            dibujar();
        }));
        const mas = barra.querySelector('[data-vm-mas]');
        if (mas) mas.addEventListener('click', () => { panel.classList.toggle('on'); mas.classList.toggle('sel'); });

        const aviso = document.createElement('div');
        aviso.className = 'vm-vacio'; aviso.id = 'vm-vacio'; aviso.textContent = t('vm_sin_resultados');
        document.body.appendChild(aviso);
    }
    function actualizarConectores() {
        const sel = document.getElementById('vm-f-con');
        if (!sel) return;
        const tipos = [...new Set(puntos.flatMap((p) => (p.cargadores || []).map((c) => c.tipoConector)).filter(Boolean))].sort();
        const actual = filtros.conector;
        sel.innerHTML = `<option value="">${esc(t('vm_todos'))}</option>` + tipos.map((x) => `<option value="${esc(x)}">${esc(x)}</option>`).join('');
        sel.value = tipos.includes(actual) ? actual : '';
    }
    function limpiarFiltros() {
        filtros.estado = 'todos'; filtros.potenciaMin = 0; filtros.conector = ''; filtros.origen = 'todos';
        const q = (id) => document.getElementById(id);
        if (q('vm-f-pot')) q('vm-f-pot').value = 0;
        if (q('vm-f-ori')) q('vm-f-ori').value = 'todos';
        document.querySelectorAll('[data-vm-estado]').forEach((c) => c.classList.toggle('sel', c.dataset.vmEstado === 'todos'));
        dibujar();
    }

    /* ---------- Modales y avisos ---------- */
    function raizModal() {
        let r = document.getElementById('vm-modal');
        if (!r) { r = document.createElement('div'); r.id = 'vm-modal'; r.className = 'vm-modal'; document.body.appendChild(r); }
        return r;
    }
    function abrirModal(html) { const r = raizModal(); r.innerHTML = `<div class="vm-modal-box">${html}</div>`; r.classList.add('on'); }
    function cerrarModal() { const r = raizModal(); r.classList.remove('on'); r.innerHTML = ''; }
    function toast(msg) {
        let el = document.getElementById('vm-toast');
        if (!el) { el = document.createElement('div'); el.id = 'vm-toast'; el.className = 'vm-toast'; document.body.appendChild(el); }
        el.textContent = msg; el.classList.add('on');
        clearTimeout(el._t); el._t = setTimeout(() => el.classList.remove('on'), 3500);
    }
    function mostrarError(msg) { const e = document.getElementById('vm-err'); if (e) { e.textContent = msg; e.classList.add('on'); } }
    function textoError(r) {
        const base = t('vm_err_' + (r.error || 'generico'));
        const txt = base === 'vm_err_' + r.error ? t('vm_err_generico') : base;
        return fmt(txt, { radio: r.radio_m, dist: r.distancia_m });
    }
    const opcionesVehiculos = () => VEHICULOS.map((v) => `<option value="${esc(v.id)}">${esc(v.marca_modelo)} (${esc(v.capacidad_kwh)} kWh)</option>`).join('');

    /* ---------- Reportar ---------- */
    function abrirReporte(id) {
        cargadorSel = id;
        abrirModal(
            `<h3>${esc(t('vm_modal_reporte_titulo'))}</h3><div class="vm-error" id="vm-err"></div>`
            + `<label class="vm-campo">${esc(t('vm_que_reportar'))}<select id="vm-r-tipo">`
            + `<option value="operativo">${esc(t('vm_rep_operativo'))}</option><option value="ocupacion">${esc(t('vm_rep_ocupacion'))}</option>`
            + `<option value="fila">${esc(t('vm_rep_fila'))}</option></select></label>`
            + `<div id="vm-s-operativo" class="vm-campo"><select id="vm-v-operativo"><option value="funcionando">${esc(t('vm_funcionando'))}</option>`
            + `<option value="fuera_de_servicio">${esc(t('vm_fuera_servicio_op'))}</option></select></div>`
            + `<div id="vm-s-ocupacion" style="display:none"><div class="vm-campo"><select id="vm-v-ocupacion"><option value="en_uso">${esc(t('vm_en_uso'))}</option>`
            + `<option value="disponible">${esc(t('vm_disponible'))}</option></select></div>`
            + (VEHICULOS.length
                ? `<div id="vm-s-veh"><label class="vm-campo">${esc(t('vm_vehiculo'))}<select id="vm-r-veh">${opcionesVehiculos()}</select></label>`
                + `<label class="vm-campo">${esc(t('vm_bateria_actual'))}<input type="number" id="vm-r-soc" min="0" max="99" value="30"></label></div>`
                : '')
            + `</div><div id="vm-s-fila" class="vm-campo" style="display:none"><label>${esc(t('vm_cantidad_vehiculos'))}`
            + `<input type="number" id="vm-v-fila" min="0" max="20" value="0" style="width:100%;margin-top:4px;padding:9px 10px;border:1.5px solid #CBD5E1;border-radius:8px"></label></div>`
            + `<div class="vm-fila-btns"><button class="vm-btn sec" data-vm="cerrar">${esc(t('vm_cancelar'))}</button>`
            + `<button class="vm-btn" id="vm-r-enviar" data-vm="enviar-reporte">${esc(t('vm_enviar'))}</button></div>`
        );
        const alternar = () => {
            const tipo = document.getElementById('vm-r-tipo').value;
            document.getElementById('vm-s-operativo').style.display = tipo === 'operativo' ? '' : 'none';
            document.getElementById('vm-s-ocupacion').style.display = tipo === 'ocupacion' ? '' : 'none';
            document.getElementById('vm-s-fila').style.display = tipo === 'fila' ? '' : 'none';
        };
        const alternarVeh = () => {
            const bloque = document.getElementById('vm-s-veh');
            if (bloque) bloque.style.display = document.getElementById('vm-v-ocupacion').value === 'en_uso' ? '' : 'none';
        };
        document.getElementById('vm-r-tipo').addEventListener('change', alternar);
        document.getElementById('vm-v-ocupacion').addEventListener('change', alternarVeh);
    }

    function obtenerUbicacion() {
        return new Promise((resolve) => {
            if (!navigator.geolocation) return resolve(null);
            navigator.geolocation.getCurrentPosition(
                (pos) => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
                () => resolve(null),
                { enableHighAccuracy: true, timeout: 8000, maximumAge: 10000 }
            );
        });
    }

    async function enviarReporte() {
        const tipo = document.getElementById('vm-r-tipo').value;
        const cuerpo = { cargador_id: cargadorSel, tipo };
        if (tipo === 'operativo') cuerpo.valor = document.getElementById('vm-v-operativo').value;
        else if (tipo === 'fila') cuerpo.valor = String(parseInt(document.getElementById('vm-v-fila').value, 10) || 0);
        else {
            cuerpo.valor = document.getElementById('vm-v-ocupacion').value;
            const veh = document.getElementById('vm-r-veh');
            if (cuerpo.valor === 'en_uso' && veh) { cuerpo.vehiculo_id = parseInt(veh.value, 10); cuerpo.soc = parseFloat(document.getElementById('vm-r-soc').value); }
        }
        const btn = document.getElementById('vm-r-enviar');
        btn.disabled = true; toast(t('vm_obteniendo_ubicacion'));
        const pos = await obtenerUbicacion();
        if (pos) { cuerpo.lat = pos.lat; cuerpo.lng = pos.lng; }
        try {
            const resp = await fetch('/app/controller/controlador_reporte.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF }, body: JSON.stringify(cuerpo)
            });
            const r = await resp.json();
            if (r.ok) { cerrarModal(); toast(t('vm_reporte_ok')); await cargar(true); }
            else { mostrarError(textoError(r)); btn.disabled = false; }
        } catch (e) { mostrarError(t('vm_err_generico')); btn.disabled = false; }
    }

    /* ---------- Iniciar carga (reserva) ---------- */
    function buscarCargador(id) {
        for (const p of puntos) for (const c of (p.cargadores || [])) if (c.id === id) return c;
        return null;
    }
    function abrirCarga(id) {
        cargadorSel = id;
        if (!VEHICULOS.length) {
            abrirModal(`<h3>${esc(t('vm_modal_carga_titulo'))}</h3><p>${esc(t('vm_sin_vehiculos'))}</p>`
                + `<div class="vm-fila-btns"><button class="vm-btn sec" data-vm="cerrar">${esc(t('vm_cancelar'))}</button>`
                + `<a class="vm-btn" style="text-decoration:none" href="vehiculos.php">${esc(t('vm_agregar_vehiculo'))}</a></div>`);
            return;
        }
        abrirModal(
            `<h3>${esc(t('vm_modal_carga_titulo'))}</h3><div class="vm-error" id="vm-err"></div>`
            + `<label class="vm-campo">${esc(t('vm_vehiculo'))}<select id="vm-c-veh">${opcionesVehiculos()}</select></label>`
            + `<label class="vm-campo">${esc(t('vm_bateria_actual'))}<input type="number" id="vm-c-soc" min="0" max="99" value="20"></label>`
            + `<p class="vm-nota" id="vm-c-est"></p>`
            + `<div class="vm-fila-btns"><button class="vm-btn sec" data-vm="cerrar">${esc(t('vm_cancelar'))}</button>`
            + `<button class="vm-btn verde" id="vm-c-ok" data-vm="confirmar-carga">${esc(t('vm_confirmar_carga'))}</button></div>`
        );
        const estimar = () => {
            const c = buscarCargador(cargadorSel);
            const v = VEHICULOS.find((x) => String(x.id) === document.getElementById('vm-c-veh').value);
            const soc = Math.max(0, Math.min(99, parseFloat(document.getElementById('vm-c-soc').value) || 0));
            if (!c || !v) return;
            // Mismo modelo de la sección de Física: t = E / P, con P = min(P cargador, P máx. vehículo)
            const p = Math.min(parseFloat(c.potenciaKilowatts), parseFloat(v.potencia_max_kw));
            const min = p > 0 ? Math.ceil(((100 - soc) / 100 * parseFloat(v.capacidad_kwh)) / p * 60) : 0;
            document.getElementById('vm-c-est').textContent = fmt(t('vm_tiempo_estimado'), { min });
        };
        document.getElementById('vm-c-veh').addEventListener('change', estimar);
        document.getElementById('vm-c-soc').addEventListener('input', estimar);
        estimar();
    }
    async function confirmarCarga() {
        const btn = document.getElementById('vm-c-ok'); btn.disabled = true;
        try {
            const resp = await fetch('/app/controller/controlador_reserva.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
                body: JSON.stringify({
                    accion: 'iniciar', cargador_id: cargadorSel,
                    vehiculo_id: parseInt(document.getElementById('vm-c-veh').value, 10),
                    soc: parseInt(document.getElementById('vm-c-soc').value, 10)
                })
            });
            const r = await resp.json();
            if (r.ok) { window.location.href = 'reservas.php'; return; }
            mostrarError(textoError(r)); btn.disabled = false;
        } catch (e) { mostrarError(t('vm_err_generico')); btn.disabled = false; }
    }

    /* ---------- Eventos globales ---------- */
    document.addEventListener('click', (e) => {
        const b = e.target.closest('[data-vm]');
        if (!b) { if (e.target.id === 'vm-modal') cerrarModal(); return; }
        const accion = b.dataset.vm;
        if (accion === 'reportar') abrirReporte(b.dataset.id);
        else if (accion === 'carga') abrirCarga(b.dataset.id);
        else if (accion === 'cerrar') cerrarModal();
        else if (accion === 'enviar-reporte') enviarReporte();
        else if (accion === 'confirmar-carga') confirmarCarga();
    });

    document.addEventListener('DOMContentLoaded', async () => {
        map = L.map('map').setView([-34.4811, -54.3333], 10);   // centrado en Rocha
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap contributors' }).addTo(map);
        capa = L.layerGroup().addTo(map);
        map.on('popupopen', () => { popupAbierto = true; });
        map.on('popupclose', () => { popupAbierto = false; });
        crearPanelFiltros();
        await cargar(true);
        // Tiempo real: se vuelve a consultar cada 15 s (si no hay un popup abierto ni modal en pantalla)
        setInterval(() => { if (!document.hidden && !popupAbierto && !document.querySelector('#vm-modal.on')) cargar(false); }, INTERVALO_MS);
    });

    window.VM = { recargar: () => cargar(true), filtros };
})();
