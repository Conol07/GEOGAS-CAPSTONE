/* GeoGas ManFort - light/dark theme switch.
   The initial theme is applied by a tiny inline script in <head> (no flash);
   this file handles the toggle button, Chart.js colours and printing. */
(function () {
    var KEY = 'ggTheme';
    var root = document.documentElement;

    function current() { return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light'; }

    function chartTheme() {
        if (!window.Chart) return;
        var dark = current() === 'dark';
        Chart.defaults.color = dark ? '#C7C7CC' : '#4B5563';
        Chart.defaults.borderColor = dark ? 'rgba(255,255,255,.10)' : 'rgba(0,0,0,.08)';
        if (Chart.instances) {
            Object.keys(Chart.instances).forEach(function (k) { try { Chart.instances[k].update(); } catch (e) {} });
        }
    }

    function apply(t, save) {
        root.setAttribute('data-theme', t);
        root.setAttribute('data-bs-theme', t);
        if (save) { try { localStorage.setItem(KEY, t); } catch (e) {} }
        document.querySelectorAll('[data-theme-toggle]').forEach(function (b) {
            b.setAttribute('aria-pressed', t === 'dark' ? 'true' : 'false');
            b.setAttribute('title', t === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
        });
        chartTheme();
        document.dispatchEvent(new CustomEvent('gg:themechange', { detail: { theme: t } }));
    }

    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-theme-toggle]');
        if (b) apply(current() === 'dark' ? 'light' : 'dark', true);
    });

    // Follow the device setting until the user picks one themselves.
    if (window.matchMedia) {
        var mq = window.matchMedia('(prefers-color-scheme: dark)');
        var onChange = function (ev) { var saved = null; try { saved = localStorage.getItem(KEY); } catch (e) {} if (!saved) apply(ev.matches ? 'dark' : 'light', false); };
        if (mq.addEventListener) mq.addEventListener('change', onChange);
    }

    // Always print on a light page.
    var before = null;
    window.addEventListener('beforeprint', function () { before = current(); if (before === 'dark') apply('light', false); });
    window.addEventListener('afterprint', function () { if (before === 'dark') apply('dark', false); before = null; });

    document.addEventListener('DOMContentLoaded', function () { apply(current(), false); });
})();
