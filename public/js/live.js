/* GeoGas automatic updates: quietly refreshes regions marked data-live="<seconds>"
   and any GIS map on the page. No badge, no buttons: it just keeps the data current
   while the tab is visible. */
(function () {
    var DEFAULT = 60, busy = false, lastRun = 0;
    var regions = Array.prototype.slice.call(document.querySelectorAll('[data-live]'));
    function gisFns() { return Object.keys(window).filter(function (k) { return k.indexOf('gisReload_') === 0 && typeof window[k] === 'function'; }); }
    if (!regions.length && !gisFns().length) return;

    function flash(el) { el.classList.remove('gg-live-flash'); void el.offsetWidth; el.classList.add('gg-live-flash'); }

    function tick() {
        if (busy || document.hidden) return;
        busy = true; lastRun = Date.now();
        var jobs = [];
        if (regions.length) {
            jobs.push(fetch(location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin', cache: 'no-store' })
                .then(function (r) { if (!r.ok) throw new Error(); return r.text(); })
                .then(function (html) {
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    regions.forEach(function (el) {
                        var fresh = el.id ? doc.getElementById(el.id) : null;
                        if (fresh && fresh.innerHTML.replace(/\s+/g, ' ') !== el.innerHTML.replace(/\s+/g, ' ')) { el.innerHTML = fresh.innerHTML; flash(el); }
                    });
                }));
        }
        gisFns().forEach(function (k) { jobs.push(Promise.resolve(window[k]())); });
        Promise.all(jobs.map(function (j) { return j.catch(function () {}); })).then(function () { busy = false; });
    }

    var secs = regions.reduce(function (m, el) { var v = parseInt(el.getAttribute('data-live'), 10); return v > 0 ? Math.min(m, v) : m; }, DEFAULT);
    setInterval(tick, Math.max(20, secs) * 1000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden && Date.now() - lastRun > 30000) tick(); });
})();
