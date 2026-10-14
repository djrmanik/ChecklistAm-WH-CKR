/* ============================================================================
   SUSUNAN TAMPILAN (Super Editor UI 4B/4C)              [GENC-14OKT26-SUSUNAN]
   Menerapkan susunan yang disimpan lewat Mode edit tampilan ke halaman: urutan, sembunyi, lebar
   kartu (grid 12 kolom) dan posisi bebas (4C, hanya layar lebar). Dipakai juga oleh editor
   (genc-tampilan.js) supaya cara mengenali "grup" & "blok" persis sama.
     window.GX_S = { p: {kunciGrup: spek}, v: {fileView: {kunciGrup: spek}} }   (dari gx_inject)
     spek = { o:[kunciBlok...], h:[kunciBlok...], w:{kunciBlok:span}, f:{kunciBlok:[baris, kolom, lebar]}, v:fileView, n:nama }
   Kunci dihitung dari STRUKTUR (tag, class, ikon, alamat tautan, atribut data-*), bukan dari teks,
   jadi tetap sama walau teksnya diubah lewat Mode edit.
   ========================================================================= */
(function () {
    'use strict';
    if (window.GXS) { return; }
    var doc = document;
    var BLOCK = '.genc-card, .genc-kpi, .genc-btn, .genc-tab, .genc-fold, .genc-notice, .genc-chip, .gs-nav > li, .gs-sec, .gm-tool, .genc-tabs__group, .genc-dash__item, .gm-stat';
    var CARDLIKE = /(^|\s)(genc-card|genc-kpi|genc-fold|genc-notice|gs-sec|genc-tabs__group)(\s|$)/;
    var SKIPCLS = /^(is-|gx-|has-|active$|show$|open$|collapsed$|disabled$)/;
    var VOLATILE = { bulan: 1, tahun: 1, hari: 1, unit: 1, q: 1, page: 1, csrf_token: 1, redirect: 1, kembali: 1, limit_start: 1, siap: 1, dari: 1, sampai: 1, user: 1, jenis: 1, format: 1 };

    function cls(el) {
        return Array.prototype.filter.call(el.classList || [], function (c) { return !SKIPCLS.test(c); }).sort().join('.');
    }
    function hrefKey(el) {
        var h = el.getAttribute && el.getAttribute('href');
        if (!h) { var a = el.querySelector && el.querySelector(':scope > a[href]'); h = a ? a.getAttribute('href') : ''; }
        if (!h || h.charAt(0) === '#') { return h || ''; }
        try {
            var u = new URL(h, location.href), q = [];
            u.searchParams.forEach(function (v, k) { if (!VOLATILE[k]) { q.push(k + '=' + v); } });
            q.sort();
            var path = u.pathname.toLowerCase().replace(/\/(index)?\/*$/, ''), here = location.pathname.toLowerCase().replace(/\/(index)?\/*$/, '');
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
        if (el.matches('gx-t, gx-d, script, style, template, dialog, .genc-toast, [data-gx-noarrange]')) { return false; }
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
    /** Terapkan 1 spek ke 1 grup. */
    function applyOne(g, sp, editing) {
        var byKey = {};
        g.blocks.forEach(function (b) { byKey[b.key] = b.el; });
        // urutan: tempat-tempat blok di DOM tetap, isinya diganti sesuai urutan baru
        var order = [];
        (sp && sp.o || []).forEach(function (k) { if (byKey[k] && order.indexOf(byKey[k]) === -1) { order.push(byKey[k]); } });
        g.blocks.forEach(function (b) { if (order.indexOf(b.el) === -1) { order.push(b.el); } });
        if (sp && sp.f) {   // posisi bebas: urutan DOM = baris lalu kolom (dipakai HP & pembaca layar)
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

    window.GXS = { scan: scan, find: find, applyOne: applyOne, applyAll: applyAll, specFor: specFor, isBlock: isBlock, cardlike: function (el) { return CARDLIKE.test(el.className); }, SPANS: SPANS };
    if (window.GX_S && !window.GX_EDIT) { applyAll(window.GX_S, false); }   // di Mode edit/Pratinjau editor yang menerapkan
})();
