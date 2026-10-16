/* ============================================================================
   SUSUNAN TAMPILAN (Super Editor UI 4B/4C)              [GENC-14OKT26-SUSUNAN]
   Menerapkan susunan yang disimpan lewat Mode edit tampilan ke halaman: urutan, sembunyi, lebar
   kartu (grid 12 kolom) dan posisi bebas (4C, hanya layar lebar). Dipakai juga oleh editor
   (genc-tampilan.js) supaya cara mengenali "grup" & "blok" persis sama.
     window.GX_S = { p: {kunciGrup: spek}, v: {fileView: {kunciGrup: spek}} }   (dari gx_inject)
     spek = { o:[kunciBlok...], h:[kunciBlok...], w:{kunciBlok:span}, f:{kunciBlok:[baris, kolom, lebar]}, v:fileView, n:nama,
              b:{kunciBlok:[x %, y px, lebar %, tinggi px (0 = otomatis), tumpukan]} }   [GENC-16OKT26-BEBAS] kanvas bebas ala Canva
     window.GX_I = { p: {kunciIkon: spek}, g: {fa-kelas: spek} }   [GENC-16OKT26-IKON] ikon diganti / diwarnai / dihapus
   Kunci dihitung dari STRUKTUR (tag, class, ikon, alamat tautan, atribut data-*), bukan dari teks,
   jadi tetap sama walau teksnya diubah lewat Mode edit.
   ========================================================================= */
(function () {
    'use strict';
    if (window.GXS) { return; }
    var doc = document;
    // [GENC-14OKT26-SUSUNAN2] + kartu pilihan di Mesin & Unit (Unit baru / Mesin baru), angka & aturan, dan SEMUA isi kartu
    // (anak langsung .genc-card = blok "dalam": klik pertama memilih kartunya, klik lagi masuk ke dalam)
    // [GENC-16OKT26-WARNA] + badge / label status: bisa dipilih di Susunan (sembunyikan, pindah, warna latar)
    var EXPLICIT = '.genc-card, .genc-kpi, .genc-btn, .genc-tab, .genc-fold, .genc-notice, .genc-chip, .gs-nav > li, .gs-sec, .gm-tool, .genc-tabs__group, .genc-dash__item, .gm-stat, .gm-choice, .gm-stats > div, .gm-rules > li, .genc-badge, .genc-dash__count, .genc-tmst';
    var BLOCK = EXPLICIT + ', .genc-card > *';
    var CARDLIKE = /(^|\s)(genc-card|genc-kpi|genc-fold|genc-notice|gs-sec|genc-tabs__group)(\s|$)/;
    var SKIPCLS = /^(is-|gx-|has-|active$|show$|open$|collapsed$|disabled$)/;
    var VOLATILE = { bulan: 1, tahun: 1, hari: 1, unit: 1, q: 1, page: 1, csrf_token: 1, redirect: 1, kembali: 1, limit_start: 1, siap: 1, dari: 1, sampai: 1, user: 1, jenis: 1, format: 1 };

    function cls(el) {
        return Array.prototype.filter.call(el.classList || [], function (c) { return !SKIPCLS.test(c); }).sort().join('.');
    }
    /** [GENC-16OKT26-PANTAU] Approval & NOK History pindah ke pantau/* - kunci susunan tetap memakai alamat lama. */
    function alias(p) { return p.replace(/\/pantau\/approval$/, '/palletmover/approval').replace(/\/pantau\/temuan$/, '/palletmover/uncompleted'); }
    function hrefKey(el) {
        var h = el.getAttribute && el.getAttribute('href');
        if (!h) { var a = el.querySelector && el.querySelector(':scope > a[href]'); h = a ? a.getAttribute('href') : ''; }
        if (!h || h.charAt(0) === '#') { return h || ''; }
        try {
            var u = new URL(h, location.href), q = [];
            u.searchParams.forEach(function (v, k) { if (!VOLATILE[k]) { q.push(k + '=' + v); } });
            q.sort();
            var path = u.pathname.toLowerCase().replace(/\/(index)?\/*$/, ''), here = location.pathname.toLowerCase().replace(/\/(index)?\/*$/, '');
            path = alias(path); here = alias(here);   // [GENC-16OKT26-PANTAU] route baru = kunci susunan lama
            if (path === here && u.search) { path = '.'; }   // tautan filter ke halaman ini sendiri (chip): sama di semua halaman yang memakai bagian ini
            return path + (q.length ? '?' + q.join('&') : '');
        } catch (e) { return h; }
    }
    function icon(el) {
        var i = el.querySelector && el.querySelector('i.fa');
        if (!i) { return ''; }
        return Array.prototype.filter.call(i.classList, function (c) { return c !== 'fa' && c.indexOf('fa-') === 0 && !/^fa-(spin|fw|lg|2x|rotate)/.test(c); }).join('.');
    }
    function dataKey(el) {
        var out = [];
        Array.prototype.forEach.call(el.attributes || [], function (a) {
            if (/^data-(genc-fold|gm-sel|genc-tab|gx-blk|gs-key|unit|page|sel|folder)$/.test(a.name)) { out.push(a.name.replace(/^data-/, '') + '=' + a.value); }
        });
        if (el.id && !/^(gx|ui-id)/.test(el.id)) { out.push('#' + el.id); }
        var f = el.querySelector && el.querySelector(':scope > form[action]');
        if (f) { out.push('form=' + hrefKey({ getAttribute: function () { return f.getAttribute('action'); }, querySelector: function () { return null; } })); }
        return out.join(',');
    }
    function sig(el) {
        return el.tagName.toLowerCase() + '.' + cls(el) + '|' + icon(el) + '|' + hrefKey(el) + '|' + dataKey(el);
    }
    function landmark(el) {
        var p = el.closest && el.closest('#gs-side, .gs-top, footer, #page-content, .gx-pop, .gx-bar, .gx-drawer, .gx-modal');
        if (!p) { return 'body'; }
        if (p.matches('.gx-pop, .gx-bar, .gx-drawer, .gx-modal')) { return null; }
        return p.id ? '#' + p.id : p.tagName.toLowerCase();
    }
    function isBlock(el) {
        if (!el || el.nodeType !== 1 || !el.matches) { return false; }
        if (el.matches('gx-t, gx-d, script, style, template, dialog, input, select, textarea, label, br, hr, .genc-toast, .gx-xbox, [data-gx-noarrange], [hidden]')) { return false; }
        if (el.closest('table, .genc-strip, .genc-dialog, .genc-lightbox, .gx-bar, .gx-pop, .gx-drawer, .gx-modal, .gx-pill, .genc-savebar')) { return false; }   // isi data / dialog / editor tidak disusun
        return el.matches(BLOCK);
    }
    function blocksOf(c) { return Array.prototype.filter.call(c.children, isBlock); }

    /** Semua grup di halaman: [{el, key, blocks:[{el, key}]}]. Dihitung SEKALI dari susunan asli (sebelum diubah). */
    var cache = null;
    function scan(force) {
        if (cache && !force) { return cache; }
        var seen = [], out = [], cnt = {};
        Array.prototype.forEach.call(doc.querySelectorAll(BLOCK), function (b) {
            var c = b.parentElement;
            if (!c || seen.indexOf(c) !== -1 || !isBlock(b)) { return; }
            seen.push(c);
        });
        seen.forEach(function (c) {
            var lm = landmark(c);
            if (lm === null) { return; }
            var bl = blocksOf(c);
            if (!bl.length) { return; }
            var s = lm + '>' + c.tagName.toLowerCase() + '.' + cls(c) + (c.id ? '#' + c.id : '');
            cnt[s] = (cnt[s] || 0) + 1;
            var key = s + '@' + cnt[s];
            var bc = {}, blocks = bl.map(function (b) {
                var k = sig(b); bc[k] = (bc[k] || 0) + 1;
                return { el: b, key: k + '@' + bc[k] };
            });
            out.push({ el: c, key: key, blocks: blocks });
        });
        cache = out;
        return out;
    }
    function find(key) { var l = scan(); for (var i = 0; i < l.length; i++) { if (l[i].key === key) { return l[i]; } } return null; }

    var SPANS = [3, 4, 6, 8, 9, 12];
    var NARROW = 768;
    function narrowNow() { return window.innerWidth <= NARROW; }

    /* ======================================================== KANVAS BEBAS   [GENC-16OKT26-BEBAS]
       Grup yang punya spek 'b' = kanvas: wadah position:relative, tiap blok position:absolute di (x %, y px),
       lebar %, tinggi px (0 = otomatis). Boleh menumpuk (urutan tumpuk = angka ke-5). Blok yang belum punya
       posisi (mis. mesin baru) ditaruh di bawah, lebar seperti aslinya. HP (<= 768 px): tidak ada kanvas,
       blok mengalir 1 kolom urut atas -> bawah, kiri -> kanan. */
    function clearCanvas(g) {
        if (g.el.__gxRO) { try { g.el.__gxRO.disconnect(); } catch (e) { /* lewati */ } g.el.__gxRO = null; }
        // hanya yang memang dipasang kanvas: kartu yang sekaligus jadi grup (isi kartu bisa disusun) tidak boleh kehilangan
        // tinggi yang diberikan kanvas di luarnya
        if (g.el.classList.contains('gx-canvas')) { g.el.classList.remove('gx-canvas'); g.el.style.removeProperty('height'); }
        g.blocks.forEach(function (b) {
            if (!b.el.classList.contains('gx-cv')) { return; }
            b.el.classList.remove('gx-cv', 'gx-hset');
            ['left', 'top', 'width', 'height', 'z-index'].forEach(function (k) { b.el.style.removeProperty(k); });
        });
    }
    function fitCanvas(g) {
        if (!g.el.classList.contains('gx-canvas')) { return; }
        var max = 0;
        g.blocks.forEach(function (b) { if (b.el.offsetParent !== null || b.el.classList.contains('gx-hidden')) { max = Math.max(max, b.el.offsetTop + b.el.offsetHeight); } });
        g.el.style.height = Math.max(40, max) + 'px';
        if (window.GENC_PANELS) { window.GENC_PANELS(); }
    }
    function applyCanvas(g, b) {
        clearCanvas(g);
        if (!b || narrowNow()) { return false; }
        // ukuran asli blok yang belum punya posisi (diukur sebelum jadi kanvas)
        var gr = g.el.getBoundingClientRect(), gw = gr.width || 1, nat = {};
        g.blocks.forEach(function (bl) {
            if (b[bl.key]) { return; }
            var r = bl.el.getBoundingClientRect();
            nat[bl.key] = [Math.max(0, (r.left - gr.left) / gw * 100), Math.min(100, r.width / gw * 100), r.top - gr.top];
        });
        g.el.classList.add('gx-canvas');
        var bottom = 0;
        g.blocks.forEach(function (bl) {
            var p = b[bl.key];
            if (!p) { return; }
            bl.el.classList.add('gx-cv');
            bl.el.style.left = p[0] + '%'; bl.el.style.top = p[1] + 'px'; bl.el.style.width = p[2] + '%';
            if (+p[3] > 0) { bl.el.style.height = p[3] + 'px'; bl.el.classList.add('gx-hset'); }
            bl.el.style.zIndex = String(1 + (+p[4] || 0));
        });
        g.blocks.forEach(function (bl) { if (b[bl.key]) { bottom = Math.max(bottom, bl.el.offsetTop + bl.el.offsetHeight); } });
        // blok tanpa posisi: di bawah semua, baris demi baris seperti susunan aslinya
        var rest = g.blocks.filter(function (bl) { return !b[bl.key]; }).sort(function (x, y) { return (nat[x.key][2] - nat[y.key][2]) || (nat[x.key][0] - nat[y.key][0]); });
        var rowTop = null, y = bottom + (bottom ? 16 : 0), rowH = 0;
        rest.forEach(function (bl) {
            var n = nat[bl.key];
            if (rowTop === null || Math.abs(n[2] - rowTop) > 4) { y += rowH + (rowH ? 16 : 0); rowH = 0; rowTop = n[2]; }
            bl.el.classList.add('gx-cv');
            bl.el.style.left = n[0] + '%'; bl.el.style.top = y + 'px'; bl.el.style.width = n[1] + '%'; bl.el.style.zIndex = '1';
            rowH = Math.max(rowH, bl.el.offsetHeight);
        });
        fitCanvas(g);
        if (window.ResizeObserver) {
            try { var ro = new window.ResizeObserver(function () { fitCanvas(g); }); g.blocks.forEach(function (bl) { ro.observe(bl.el); }); g.el.__gxRO = ro; } catch (e) { /* lewati */ }
        }
        return true;
    }

    /** Terapkan 1 spek ke 1 grup. */
    function applyOne(g, sp, editing) {
        var byKey = {};
        g.blocks.forEach(function (b) { byKey[b.key] = b.el; });
        // urutan: tempat-tempat blok di DOM tetap, isinya diganti sesuai urutan baru
        var order = [];
        (sp && sp.o || []).forEach(function (k) { if (byKey[k] && order.indexOf(byKey[k]) === -1) { order.push(byKey[k]); } });
        g.blocks.forEach(function (b) { if (order.indexOf(b.el) === -1) { order.push(b.el); } });
        if (sp && sp.b) {   // [GENC-16OKT26-BEBAS] kanvas: urutan DOM = atas -> bawah, kiri -> kanan (dipakai HP & pembaca layar)
            var keyOfB = function (el) { for (var i = 0; i < g.blocks.length; i++) { if (g.blocks[i].el === el) { return g.blocks[i].key; } } return ''; };
            var posB = function (el) { var f = sp.b[keyOfB(el)]; return f ? Math.round(f[1] / 8) * 1000 + f[0] : 1e9; };
            order = order.map(function (el, i) { return [el, i]; }).sort(function (x, y) { return (posB(x[0]) - posB(y[0])) || (x[1] - y[1]); }).map(function (x) { return x[0]; });
        }
        else if (sp && sp.f) {   // posisi bebas: urutan DOM = baris lalu kolom (dipakai HP & pembaca layar)
            var keyOf = function (el) { for (var i = 0; i < g.blocks.length; i++) { if (g.blocks[i].el === el) { return g.blocks[i].key; } } return ''; };
            var pos = function (el) { var f = sp.f[keyOf(el)]; return f ? f[0] * 100 + f[1] : 1e9; };
            order = order.map(function (el, i) { return [el, i]; }).sort(function (x, y) { return (pos(x[0]) - pos(y[0])) || (x[1] - y[1]); }).map(function (x) { return x[0]; });
        }
        // tempat blok sekarang, diurutkan menurut posisi di DOM (aman kalau spek diterapkan ulang)
        var slots = g.blocks.map(function (b) { var m = doc.createComment('gx'); b.el.parentNode.insertBefore(m, b.el); return m; });
        slots.sort(function (x, y) { return x === y ? 0 : (x.compareDocumentPosition(y) & 4 ? -1 : 1); });
        slots.forEach(function (m, i) { m.parentNode.insertBefore(order[i], m); });
        slots.forEach(function (m) { m.parentNode.removeChild(m); });
        // sembunyi
        var hide = (sp && sp.h) || [];
        g.blocks.forEach(function (b) {
            var h = hide.indexOf(b.key) !== -1;
            b.el.classList.toggle(editing ? 'gx-hidden' : 'gx-hide', h);
            b.el.classList.remove(editing ? 'gx-hide' : 'gx-hidden');
        });
        // [GENC-16OKT26-WARNA] warna latar blok: c = {kunciBlok: token}
        var cmap = (sp && sp.c) || {};
        g.blocks.forEach(function (b) {
            Array.prototype.slice.call(b.el.classList).forEach(function (x) { if (x.indexOf('gx-bg--') === 0) { b.el.classList.remove(x); } });
            if (cmap[b.key] && /^[a-z]{2,10}$/.test(cmap[b.key])) { b.el.classList.add('gx-bg--' + cmap[b.key]); }
        });
        // [GENC-16OKT26-BEBAS] kanvas bebas (menggantikan lebar & posisi grid untuk grup ini)
        if (sp && sp.b) {
            g.blocks.forEach(function (b) { b.el.style.removeProperty('--gx-span'); b.el.style.removeProperty('--gx-col'); b.el.style.removeProperty('--gx-row'); });
            g.el.classList.remove('gx-grid', 'gx-free');
            applyCanvas(g, sp.b);
            return;
        }
        clearCanvas(g);
        // lebar / posisi bebas
        var w = (sp && sp.w) || {}, f = (sp && sp.f) || null, any = false;
        g.blocks.forEach(function (b) {
            var s = w[b.key], fr = f && f[b.key];
            b.el.style.removeProperty('--gx-span'); b.el.style.removeProperty('--gx-col'); b.el.style.removeProperty('--gx-row');
            if (fr) { b.el.style.setProperty('--gx-row', fr[0]); b.el.style.setProperty('--gx-col', fr[1]); b.el.style.setProperty('--gx-span', fr[2]); any = true; }
            else if (s) { b.el.style.setProperty('--gx-span', s); any = true; }
        });
        g.el.classList.toggle('gx-grid', any);
        g.el.classList.toggle('gx-free', !!(f && any));
    }

    /** Spek yang berlaku untuk grup g: halaman ini > tempat (file view) > (tidak ada). */
    function specFor(g, S) {
        S = S || window.GX_S || {};
        if (S.p && S.p[g.key]) { return { sp: S.p[g.key], scope: 'halaman' }; }
        var v = S.v || {};
        for (var view in v) { if (v.hasOwnProperty(view) && v[view] && v[view][g.key]) { return { sp: v[view][g.key], scope: 'tempat', view: view }; } }
        return null;
    }

    function applyAll(S, editing) {
        S = S || window.GX_S;
        scan().forEach(function (g) { var r = specFor(g, S); applyOne(g, r ? r.sp : null, editing); });
        doc.documentElement.classList.remove('gx-sw');
    }

    /** Blok "dalam" = isi kartu yang tidak punya kelas blok sendiri (dipilih sesudah kartunya). */
    function isDeep(el) { return !el.matches(EXPLICIT); }
    /* ======================================================== TEKS TAMBAHAN (ala Canva)   [GENC-14OKT26-KOTAK]
       window.GX_X = [{id, sk, t, x (% lebar wadah), y (px dari atas wadah), w (%), s (px), b, i, c, bg, a, anc, n}]
       Wadah = #page-content. Layar lebar: posisi bebas (absolute). HP (<= 768 px): mengalir di bawah blok jangkar (anc). */
    var XHOST = function () { return doc.getElementById('page-content'); };
    var XCOL = { teks: 'var(--gc-text)', redup: 'var(--gc-muted)', utama: 'var(--gc-primary-text)', ok: 'var(--gc-ok-text)', nok: 'var(--gc-nok-text)', pr: 'var(--gc-pr-text)', pending: 'var(--gc-pending-text)' };
    function xNarrow() { return window.innerWidth <= 768; }
    function xStyle(box, sp) {
        box.className = 'gx-xbox gx-xbox--' + (sp.bg || 'none') + ' gx-xbox--a' + (sp.a || 'l');
        box.style.left = (+sp.x || 0) + '%'; box.style.top = (+sp.y || 0) + 'px'; box.style.width = (+sp.w || 30) + '%';
        box.style.fontSize = (+sp.s || 16) + 'px'; box.style.fontWeight = sp.b ? '700' : '400'; box.style.fontStyle = sp.i ? 'italic' : 'normal';
        box.style.color = XCOL[sp.c] || XCOL.teks;
    }
    function xAnchor(anc) {
        if (!anc || anc.indexOf('::') < 0) { return null; }
        var p = anc.split('::'), g = find(p[0]);
        if (!g) { return null; }
        for (var i = 0; i < g.blocks.length; i++) { if (g.blocks[i].key === p[1]) { return g.blocks[i].el; } }
        return null;
    }
    /** Gambar ulang semua teks tambahan. editing = Mode edit (editor yang menambah pegangan). Mengembalikan daftar elemen. */
    function renderBoxes(list, editing) {
        var host = XHOST();
        Array.prototype.forEach.call(doc.querySelectorAll('.gx-xbox'), function (b) { b.parentNode.removeChild(b); });
        if (!host) { return []; }
        var narrow = xNarrow(), out = [], maxY = 0;
        host.classList.toggle('gx-xhost', !!(list && list.length));
        (list || []).forEach(function (sp) {
            var box = doc.createElement('div');
            xStyle(box, sp);
            box.setAttribute('data-gx-x', sp.id);
            if (sp.ic) { var ic = doc.createElement('i'); ic.className = 'fa ' + String(sp.ic).replace(/[^a-z0-9-]/g, '') + ' gx-xbox__ic'; ic.setAttribute('aria-hidden', 'true'); box.appendChild(ic); box.classList.add('gx-xbox--ic'); }   // [GENC-16OKT26-IKON]
            var t = doc.createElement('div'); t.className = 'gx-xbox__t'; t.textContent = sp.t || '';
            box.appendChild(t);
            if (narrow) {
                box.classList.add('gx-xbox--flow');
                var a = xAnchor(sp.anc);
                if (a && a.parentNode && host.contains(a)) { a.parentNode.insertBefore(box, a.nextSibling); }
                else { var first = host.querySelector('.genc') || host; first.insertBefore(box, first.firstChild); }
            } else {
                host.appendChild(box);
                maxY = Math.max(maxY, (+sp.y || 0) + box.offsetHeight + 16);
            }
            out.push(box);
        });
        host.style.minHeight = (!narrow && maxY) ? maxY + 'px' : '';
        return out;
    }
    var xMode = null;
    function xAuto() {
        if (!window.GX_X || window.GX_EDIT) { return; }
        renderBoxes(window.GX_X, false); xMode = xNarrow();
        window.addEventListener('resize', function () { if (xNarrow() !== xMode) { xMode = xNarrow(); renderBoxes(window.GX_X, false); } });
    }

    /* ======================================================== IKON   [GENC-16OKT26-IKON]
       Kunci ikon dari STRUKTUR: penanda wilayah > rantai tag.kelas (tanpa modifier BEM "--", tanpa is-/gx-) > i.<kelas awal>#urutan.
       Ikon yang sama di baris-baris sejenis (mis. panah di tiap baris unit) berbagi kunci -> diubah sekaligus.
       Cakupan "semua halaman" = per kelas ikon (fa-info-circle di mana pun). */
    var ICON_SKIP = '.gx-bar, .gx-pop, .gx-drawer, .gx-modal, .gx-stb, .gx-xtb, .gx-ipick, .genc-toast, .gx-hl, .gx-handles';
    function iconCls(i) {
        var out = '';
        Array.prototype.forEach.call(i.classList, function (c) { if (!out && c.indexOf('fa-') === 0 && !/^fa-(spin|pulse|fw|lg|[2-5]x|rotate|flip|border|li|stack|inverse|pull)/.test(c)) { out = c; } });
        return out;
    }
    function icon0(i) { var c = i.getAttribute('data-gx-ic0'); if (c === null) { c = iconCls(i); i.setAttribute('data-gx-ic0', c); } return c; }
    function icls(el) {
        return Array.prototype.filter.call(el.classList || [], function (c) { return !SKIPCLS.test(c) && c.indexOf('--') === -1 && c.indexOf('fa') !== 0; }).sort().join('.');
    }
    function iconKey(i) {
        var parts = [], el = i.parentElement, d = 0;
        while (el && el !== doc.body && d < 7 && !(el.matches && el.matches('#page-content, #gs-side, .gs-side, .gs-top, footer'))) {
            var c = icls(el); parts.unshift(el.tagName.toLowerCase() + (c ? '.' + c : '')); el = el.parentElement; d++;
        }
        var lm = !el || el === doc.body ? 'body' : (el.id ? '#' + el.id : el.tagName.toLowerCase() + (icls(el) ? '.' + icls(el) : ''));
        var c0 = icon0(i), n = 0, par = i.parentElement;
        if (par) { for (var k = 0; k < par.children.length; k++) { var x = par.children[k]; if (x === i) { break; } if (x.tagName === 'I' && x.classList.contains('fa') && icon0(x) === c0) { n++; } } }
        return lm + '>' + parts.join('>') + '>i.' + c0 + '#' + n;
    }
    function iconList() {
        return Array.prototype.filter.call(doc.querySelectorAll('i.fa'), function (i) { return !(i.closest && i.closest(ICON_SKIP)) && icon0(i) !== ''; });
    }
    var ICOL = { teks: 'var(--gc-text)', redup: 'var(--gc-muted)', utama: 'var(--gc-primary-text)', ok: 'var(--gc-ok-text)', nok: 'var(--gc-nok-text)', pr: 'var(--gc-pr-text)', pending: 'var(--gc-pending-text)' };
    /** Terapkan ikon. I = {p, g}; editing = Mode edit (ikon dihapus tampil samar, bukan hilang). */
    function applyIcons(I, editing) {
        I = I || window.GX_I || {};
        var P = I.p || {}, G = I.g || {};
        iconList().forEach(function (i) {
            var c0 = icon0(i), now = i.getAttribute('data-gx-icn');
            if (now && now !== c0) { i.classList.remove(now); i.classList.add(c0); }
            i.removeAttribute('data-gx-icn'); i.classList.remove('gx-ihide', 'gx-idel');
            i.style.removeProperty('color'); i.style.removeProperty('font-size');
            var sp = P[iconKey(i)] || G[c0] || null;
            if (!sp) { return; }
            if (sp.i === '') { i.classList.add(editing ? 'gx-idel' : 'gx-ihide'); }
            else if (sp.i && sp.i !== c0) { i.classList.remove(c0); i.classList.add(sp.i); i.setAttribute('data-gx-icn', sp.i); }
            if (sp.c) { i.style.color = ICOL[sp.c] || sp.c; }
            if (sp.s) { i.style.fontSize = sp.s + 'px'; }
        });
    }
    /** Teks yang dihapus lewat Mode edit: ikon di sebelahnya ikut disembunyikan (bukan di tombol / tautan / sel tabel).
     *  Kalau bagian itu jadi kosong sama sekali (mis. catatan kaki "ⓘ ..."), bagian itu juga disembunyikan. */
    function applyGone(editing) {
        if (editing) { Array.prototype.forEach.call(doc.querySelectorAll('.gx-idim'), function (i) { i.classList.remove('gx-idim'); }); }
        var sel = editing ? 'gx-t.gx-del' : 'gx-z';
        Array.prototype.forEach.call(doc.querySelectorAll(sel), function (z) {
            var p = z.parentElement;
            if (!p || p.matches('a, button, label, summary, select, option, th, td, .genc-btn, .genc-chip, .genc-tab')) { return; }
            var rest = '';
            Array.prototype.forEach.call(p.childNodes, function (n) {
                if (n.nodeType === 3) { rest += n.nodeValue; }
                else if (n.nodeType === 1 && !n.matches('i.fa, gx-z, br, gx-t.gx-del')) { rest += (n.textContent || 'x') + (n.matches('img, svg, input, a, button') ? 'x' : ''); }
            });
            if (rest.replace(/\s+/g, '') !== '') { return; }
            Array.prototype.forEach.call(p.children, function (c) { if (c.matches('i.fa')) { c.classList.add(editing ? 'gx-idim' : 'gx-ihide'); } });
            if (!editing) { p.classList.add('gx-gone'); }
        });
    }

    window.GXS = { isDeep: isDeep, renderBoxes: renderBoxes, xStyle: xStyle, scan: scan, find: find, applyOne: applyOne, applyAll: applyAll, specFor: specFor, isBlock: isBlock, cardlike: function (el) { return CARDLIKE.test(el.className); }, SPANS: SPANS,
        BG: ['kartu', 'lembut', 'utama', 'ok', 'nok', 'pr', 'pending', 'gelap', 'polos'],
        applyCanvas: applyCanvas, clearCanvas: clearCanvas, fitCanvas: fitCanvas, narrow: narrowNow,
        iconKey: iconKey, icon0: icon0, iconList: iconList, applyIcons: applyIcons, applyGone: applyGone, ICOL: ICOL };
    if (!window.GX_EDIT) {
        if (window.GX_S) { applyAll(window.GX_S, false); }
        applyIcons(window.GX_I, false);
        applyGone(false);
        doc.documentElement.classList.remove('gx-sw');
        // kanvas: lewat batas HP <-> layar lebar -> terapkan ulang (HP mengalir, layar lebar bebas)
        var wasNarrow = narrowNow();
        window.addEventListener('resize', function () { if (narrowNow() !== wasNarrow) { wasNarrow = narrowNow(); if (window.GX_S) { applyAll(window.GX_S, false); } } });
    }
    xAuto();   // di Mode edit/Pratinjau editor yang menerapkan
})();
