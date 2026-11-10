/* GeoGas UI helpers: show/hide password buttons and phone-friendly (stacked) tables. */
(function () {
    // ---- Show / hide password -------------------------------------------------
    function addEye(input) {
        if (input.dataset.eye) return;
        input.dataset.eye = '1';
        var wrap = document.createElement('div');
        wrap.className = 'gg-pw-wrap';
        wrap.style.position = 'relative';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'gg-pw-toggle';
        btn.style.cssText = 'position:absolute;top:50%;right:8px;transform:translateY(-50%);border:0;background:transparent;box-shadow:none;padding:4px 6px;line-height:1;color:#9CA3AF;cursor:pointer;';
        input.style.paddingRight = '2.6rem';
        btn.setAttribute('aria-label', 'Show password');
        btn.innerHTML = '<i class="bi bi-eye-fill"></i>';
        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            btn.innerHTML = '<i class="bi ' + (show ? 'bi-eye-slash-fill' : 'bi-eye-fill') + '"></i>';
            input.focus();
        });
        wrap.appendChild(btn);
    }

    // ---- Stacked tables on phones --------------------------------------------
    function label(table) {
        var heads = Array.prototype.map.call(table.querySelectorAll('thead th'), function (th) { return th.textContent.trim(); });
        if (!heads.length) return;
        table.classList.add('gg-stack');
        Array.prototype.forEach.call(table.querySelectorAll('tbody tr'), function (tr) {
            var cells = tr.children;
            if (cells.length === 1 && cells[0].colSpan > 1) { tr.classList.add('gg-stack-single'); return; }
            for (var i = 0; i < cells.length; i++) {
                if (!cells[i].hasAttribute('data-label')) cells[i].setAttribute('data-label', heads[i] || '');
            }
        });
    }
    function scan(root) {
        Array.prototype.forEach.call((root || document).querySelectorAll('.gg-table table, table.gg-table'), label);
        Array.prototype.forEach.call((root || document).querySelectorAll('input[type="password"]'), addEye);
    }

    function start() {
        scan(document);
        // Re-label whenever a live refresh swaps in new rows.
        var t = null;
        new MutationObserver(function () { clearTimeout(t); t = setTimeout(function () { scan(document); }, 60); })
            .observe(document.body, { childList: true, subtree: true });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
})();
