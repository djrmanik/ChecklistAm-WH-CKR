/* ============================================================================
   CHECKLIST AM - GENERASI C  (perilaku halaman)                 23 Sep 2026

   Tanpa library tambahan (tidak butuh jQuery/bootstrap JS), jadi aman
   dipakai di halaman phpRAD mana pun. Semua fitur diaktifkan lewat atribut
   data-* di HTML - halaman yang tidak memakai atribut itu tidak terpengaruh.

     [data-genc-menu]          dropdown (tombol Export)
     [data-genc-confirm]       dialog konfirmasi sebelum link/form dijalankan
     [data-genc-lightbox]      perbesar foto
     [data-genc-info]          dialog penjelasan 1 tombol (foto belum ada)  [GENC-24SEP26-AGV]
     form[data-genc-checklist] progress, keterangan wajib bila ada NOK/PR,
                               validasi sebelum kirim, peringatan keluar halaman
     .genc-toast               hilang sendiri setelah 6 detik
   ========================================================================= */
(function () {
    'use strict';

    function $all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
    function closest(el, sel) {
        while (el && el.nodeType === 1) { if (el.matches ? el.matches(sel) : el.msMatchesSelector(sel)) { return el; } el = el.parentNode; }
        return null;
    }

    /* ------------------------------------------------------------ dropdown */
    document.addEventListener('click', function (e) {
        var toggle = closest(e.target, '[data-genc-menu-toggle]');
        $all('[data-genc-menu].is-open').forEach(function (m) {
            if (!toggle || m !== closest(toggle, '[data-genc-menu]')) { m.classList.remove('is-open'); }
        });
        if (toggle) {
            e.preventDefault();
            var menu = closest(toggle, '[data-genc-menu]');
            var open = menu.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { $all('[data-genc-menu].is-open').forEach(function (m) { m.classList.remove('is-open'); }); }
    });

    /* ------------------------------------------------------------ dialog */
    function makeDialog(opts) {
        var d = document.createElement('dialog');
        d.className = 'genc-dialog';
        d.innerHTML =
            '<div class="genc-dialog__body"><div class="genc-dialog__title"></div><div class="genc-dialog__msg genc-muted"></div></div>' +
            '<div class="genc-dialog__foot"><button type="button" class="genc-btn" data-act="no">Batal</button>' +
            '<button type="button" class="genc-btn" data-act="yes"></button></div>';
        d.querySelector('.genc-dialog__title').textContent = opts.title || 'Yakin?';
        d.querySelector('.genc-dialog__msg').textContent = opts.message || '';
        var yes = d.querySelector('[data-act="yes"]');
        yes.textContent = opts.ok || 'Lanjutkan';
        yes.classList.add(opts.danger ? 'genc-btn--danger' : 'genc-btn--primary');
        var wrap = document.createElement('div');
        wrap.className = 'genc';
        wrap.style.cssText = 'padding:0;min-height:0;background:none';
        wrap.appendChild(d);
        document.body.appendChild(wrap);
        return d;
    }
    function confirmAction(opts, onYes) {
        var d = makeDialog(opts);
        if (typeof d.showModal !== 'function') {        // browser lama
            d.parentNode.remove();
            if (window.confirm((opts.title || '') + '\n\n' + (opts.message || ''))) { onYes(); }
            return;
        }
        d.addEventListener('click', function (e) {
            var act = e.target.getAttribute('data-act');
            if (act === 'yes') { d.close(); onYes(); }
            if (act === 'no' || e.target === d) { d.close(); }
        });
        d.addEventListener('close', function () { setTimeout(function () { if (d.parentNode) { d.parentNode.remove(); } }, 50); });
        d.showModal();
        d.querySelector('[data-act="no"]').focus();
    }
    document.addEventListener('click', function (e) {
        var el = closest(e.target, '[data-genc-confirm]');
        if (!el || el.getAttribute('data-genc-confirmed') === '1') { return; }
        e.preventDefault();
        confirmAction({
            title:   el.getAttribute('data-genc-confirm'),
            message: el.getAttribute('data-genc-confirm-msg'),
            ok:      el.getAttribute('data-genc-confirm-ok'),
            danger:  el.hasAttribute('data-genc-danger')
        }, function () {
            if (el.tagName === 'A') { window.location.href = el.href; return; }
            var form = el.form || closest(el, 'form');
            if (form) { el.setAttribute('data-genc-confirmed', '1'); window.__gencSubmitting = true; form.submit(); }
        });
    });

    /* ------------------------------------------------------------ lightbox */
    document.addEventListener('click', function (e) {
        var t = closest(e.target, '[data-genc-lightbox]');
        if (!t) { return; }
        e.preventDefault();
        var d = document.createElement('dialog');
        d.className = 'genc-dialog genc-lightbox';
        d.innerHTML = '<img alt=""><div class="genc-lightbox__cap"><span></span><button type="button" class="genc-btn genc-btn--sm">Tutup</button></div>';
        d.querySelector('img').src = t.getAttribute('data-genc-lightbox');
        d.querySelector('img').alt = t.getAttribute('data-genc-caption') || '';
        d.querySelector('span').textContent = t.getAttribute('data-genc-caption') || '';
        document.body.appendChild(d);
        if (typeof d.showModal !== 'function') { window.open(t.getAttribute('data-genc-lightbox'), '_blank'); d.remove(); return; }
        d.addEventListener('click', function (ev) { if (ev.target === d || ev.target.tagName === 'BUTTON') { d.close(); } });
        d.addEventListener('close', function () { d.remove(); });
        d.showModal();
    });

    /* ------------------------------------------------------------ info  [GENC-24SEP26-AGV]
       [data-genc-info="Judul"] + [data-genc-info-msg]: dialog penjelasan 1 tombol
       (dipakai kotak "Belum ada foto"). Teks lewat textContent -> aman XSS. */
    document.addEventListener('click', function (e) {
        var el = closest(e.target, '[data-genc-info]');
        if (!el) { return; }
        e.preventDefault();
        var title = el.getAttribute('data-genc-info') || '';
        var msg = el.getAttribute('data-genc-info-msg') || '';
        var d = document.createElement('dialog');
        d.className = 'genc-dialog';
        d.innerHTML = '<div class="genc-dialog__body"><div class="genc-dialog__title"><i class="fa fa-camera" style="color:var(--gc-muted);margin-right:8px"></i><span></span></div>' +
            '<div class="genc-dialog__msg genc-muted"></div></div>' +
            '<div class="genc-dialog__foot"><button type="button" class="genc-btn genc-btn--primary" data-act="ok">Mengerti</button></div>';
        d.querySelector('.genc-dialog__title span').textContent = title;
        d.querySelector('.genc-dialog__msg').textContent = msg;
        var wrap = document.createElement('div');
        wrap.className = 'genc';
        wrap.style.cssText = 'padding:0;min-height:0;background:none';
        wrap.appendChild(d);
        document.body.appendChild(wrap);
        if (typeof d.showModal !== 'function') { wrap.remove(); window.alert(title + '\n\n' + msg); return; }
        d.addEventListener('click', function (ev) { if (ev.target === d || ev.target.getAttribute('data-act') === 'ok') { d.close(); } });
        d.addEventListener('close', function () { setTimeout(function () { if (wrap.parentNode) { wrap.remove(); } }, 50); if (el.focus) { el.focus(); } });
        d.showModal();
        d.querySelector('[data-act="ok"]').focus();
    });

    /* ------------------------------------------------------------ tab aktif  [GENC-24SEP26-AGV]
       Di HP tab bisa lebih lebar dari layar (AGV: 8 tab) -> geser supaya tab aktif kelihatan. */
    $all('.genc-tabs__list').forEach(function (list) {
        var act = list.querySelector('.genc-tab.is-active');
        if (!act || list.scrollWidth <= list.clientWidth) { return; }
        var lr = list.getBoundingClientRect(), ar = act.getBoundingClientRect();
        list.scrollLeft += (ar.left - lr.left) - (list.clientWidth - ar.width) / 2;
    });

    /* ------------------------------------------------------------ toast */
    $all('.genc-toast').forEach(function (t) {
        setTimeout(function () { t.style.transition = 'opacity .3s'; t.style.opacity = '0'; setTimeout(function () { t.remove(); }, 320); }, 6000);
    });

    /* ------------------------------------------------------------ form checklist */
    $all('form[data-genc-checklist]').forEach(function (form) {
        var items   = $all('[data-genc-item]', form);
        var ketWrap = form.querySelector('[data-genc-keterangan]');
        var ket     = ketWrap ? ketWrap.querySelector('textarea') : null;
        var countEl = form.querySelector('[data-genc-count]');
        var meterEl = form.querySelector('[data-genc-meter]');
        var stateEl = form.querySelector('[data-genc-state]');
        var dirty   = false;

        function valueOf(item) {
            var c = item.querySelector('input[type=radio]:checked');
            return c ? c.value : '';
        }
        function refresh() {
            var done = 0, issues = [];
            items.forEach(function (item) {
                var v = valueOf(item);
                item.classList.toggle('is-answered', v !== '');
                item.classList.toggle('is-issue', v === 'NOK' || v === 'PR');
                if (v !== '') { done++; item.classList.remove('has-error'); var er = item.querySelector('.genc-item__err'); if (er) { er.remove(); } }
                if (v === 'NOK' || v === 'PR') { issues.push(item.getAttribute('data-genc-name')); }
            });
            if (countEl) { countEl.textContent = done + ' dari ' + items.length; }
            if (meterEl) { meterEl.style.width = (items.length ? Math.round(done * 100 / items.length) : 0) + '%'; }
            if (stateEl) {
                if (done < items.length) { stateEl.innerHTML = ''; }
                else if (issues.length) { stateEl.innerHTML = '<span class="genc-badge genc-badge--nok"><i class="fa fa-exclamation-triangle"></i> Perlu tindakan: ' + issues.length + ' item</span>'; }
                else { stateEl.innerHTML = '<span class="genc-badge genc-badge--ok"><i class="fa fa-check"></i> Semua baik</span>'; }
            }
            if (ketWrap) {
                ketWrap.classList.toggle('is-required', issues.length > 0);
                if (ket) { ket.required = issues.length > 0; }
                if (issues.length === 0 || (ket && ket.value.trim() !== '')) { ketWrap.classList.remove('has-error'); }
            }
            return { done: done, issues: issues };
        }
        /* [GENC-24SEP26-FORKLIFT] isian tambahan (jam kerja): [data-genc-field] + input */
        var fields = $all('[data-genc-field]', form);
        function fieldBad(wrap) {
            var inp = wrap.querySelector('input');
            if (!inp) { return false; }
            return (inp.required && inp.value.trim() === '') || (inp.value.trim() !== '' && inp.checkValidity && !inp.checkValidity());
        }
        fields.forEach(function (wrap) {
            var inp = wrap.querySelector('input');
            if (inp) { inp.addEventListener('input', function () { dirty = true; if (!fieldBad(wrap)) { wrap.classList.remove('has-error'); var er = wrap.querySelector('.genc-item__err'); if (er) { er.remove(); } } }); }
        });

        form.addEventListener('change', function () { dirty = true; refresh(); });
        if (ket) { ket.addEventListener('input', function () { dirty = true; refresh(); }); }
        refresh();

        form.addEventListener('submit', function (e) {
            var s = refresh(), first = null;
            fields.forEach(function (wrap) {
                if (!fieldBad(wrap)) { return; }
                wrap.classList.add('has-error');
                if (!wrap.querySelector('.genc-item__err')) {
                    var m = document.createElement('div');
                    m.className = 'genc-item__err';
                    m.textContent = wrap.getAttribute('data-genc-field-msg') || 'Isian ini wajib diisi.';
                    var box = wrap.querySelector('.genc-input-suffix') || wrap.querySelector('input');
                    box.parentNode.insertBefore(m, box.nextSibling);
                }
                first = first || wrap;
            });
            items.forEach(function (item) {
                if (valueOf(item) === '') {
                    item.classList.add('has-error');
                    if (!item.querySelector('.genc-item__err')) {
                        var m = document.createElement('div');
                        m.className = 'genc-item__err';
                        m.textContent = 'Pilih hasil pengecekan item ini.';
                        item.querySelector('.genc-item__body').appendChild(m);
                    }
                    first = first || item;
                }
            });
            if (!first && s.issues.length && ket && ket.value.trim() === '') {
                ketWrap.classList.add('has-error');
                first = ketWrap;
            }
            if (first) {
                e.preventDefault();
                first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                var focusable = first.querySelector('input, textarea');
                if (focusable) { setTimeout(function () { focusable.focus({ preventScroll: true }); }, 350); }
                return;
            }
            window.__gencSubmitting = true;
            var btn = form.querySelector('[type=submit]');
            if (btn) { btn.classList.add('is-disabled'); btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menyimpan...'; }
        });

        window.addEventListener('beforeunload', function (e) {
            if (dirty && !window.__gencSubmitting) { e.preventDefault(); e.returnValue = ''; }
        });
    });
})();
