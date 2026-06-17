/* leyre-cliente — JS base. leyreConfig inyectado via wp_localize_script. */
(function () {
    'use strict';

    const api   = leyreConfig.apiUrl;
    const nonce = leyreConfig.nonce;

    function get(endpoint) {
        return fetch(api + endpoint, { headers: { 'X-WP-Nonce': nonce } }).then(r => {
            if (!r.ok) throw new Error(r.status);
            return r.json();
        });
    }

    function post(endpoint, body) {
        return fetch(api + endpoint, {
            method: 'POST',
            headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
            body: JSON.stringify(body || {}),
        }).then(r => {
            if (!r.ok) throw new Error(r.status);
            return r.json();
        });
    }

    // Exportar para uso en templates inline
    window.leyreAPI = { get, post };

    // Marcar lección como completada (llamada desde template de módulo)
    window.leyreCompletarLeccion = function (leccionId, callback) {
        post('leccion/' + leccionId + '/completar').then(data => {
            if (callback) callback(data);
        }).catch(err => console.error('Error al marcar lección:', err));
    };
})();
