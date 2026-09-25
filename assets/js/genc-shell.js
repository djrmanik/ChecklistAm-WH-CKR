/* CHECKLIST AM - GENERASI C - kerangka app (sidebar, menu akun, login)   [GENC-24SEP26-SHELL]
   Vanilla JS, tanpa jQuery. Semua lewat atribut data-gs-*. */
(function () {
    'use strict';
    var body = document.body;
    var KEY = 'gs-side-hidden';
    var mq = window.matchMedia ? window.matchMedia('(max-width: 991.98px)') : { matches: false };

    function store(v) { try { if (v === null) { localStorage.removeItem(KEY); } else { localStorage.setItem(KEY, v); } } catch (e) {} }
    function stored() { try { return localStorage.getItem(KEY); } catch (e) { return null; } }

    // desktop: sidebar bisa disembunyikan, pilihan diingat di browser ini (kenyamanan saja)
    if (!mq.matches && stored() === '1') { body.classList.add('gs-side-hidden'); }

    document.addEventListener('click', function (e) {
        var t = e.target.closest ? e.target : null;
        if (!t) { return; }

        var toggle = t.closest('[data-gs-side-toggle]');
        if (toggle) {
            e.preventDefault();
            if (mq.matches) {
                body.classList.remove('gs-side-hidden');
                body.classList.toggle('gs-side-open');
            } else {
                var hidden = body.classList.toggle('gs-side-hidden');
                store(hidden ? '1' : null);
            }
            toggle.setAttribute('aria-expanded', String(mq.matches ? body.classList.contains('gs-side-open') : !body.classList.contains('gs-side-hidden')));
            return;
        }
        if (t.closest('[data-gs-overlay]')) { body.classList.remove('gs-side-open'); return; }

        var ubtn = t.closest('[data-gs-user-toggle]');
        var wrap = document.querySelector('[data-gs-user]');
        if (ubtn && wrap) {
            e.preventDefault();
            var open = wrap.classList.toggle('is-open');
            ubtn.setAttribute('aria-expanded', String(open));
            return;
        }
        if (wrap && !t.closest('[data-gs-user]')) {
            wrap.classList.remove('is-open');
            var b = wrap.querySelector('[data-gs-user-toggle]');
            if (b) { b.setAttribute('aria-expanded', 'false'); }
        }

        var eye = t.closest('[data-gs-eye]');
        if (eye) {
            var inp = document.getElementById(eye.getAttribute('data-gs-eye'));
            if (inp) {
                var show = inp.type === 'password';
                inp.type = show ? 'text' : 'password';
                eye.innerHTML = show ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
                eye.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
            }
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') { return; }
        body.classList.remove('gs-side-open');
        var wrap = document.querySelector('[data-gs-user]');
        if (wrap) { wrap.classList.remove('is-open'); }
    });

    // form login/daftar: cegah klik dobel
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (!f.matches || !f.matches('[data-gs-auth]')) { return; }
        var btn = f.querySelector('.gs-submit');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-circle-o-notch fa-spin"></i> ' + (btn.getAttribute('data-gs-busy') || 'Memproses...'); }
    });
})();
