/* ============================================================================
   MODE EDIT TAMPILAN (Super Editor UI)                  [GENC-13OKT26-TAMPILAN]
   Dimuat hanya kalau Mode edit / Pratinjau aktif (window.GX dari _shared/genc_tampilan.php).
     <gx-t data-gx="N">   teks dari kode yang bisa diubah (GX.items[N])
     [data-gx-a]          atribut teks (placeholder, title, ...) & isi <option> -> laci "Teks lain"
     <gx-d data-gxd="k">  teks yang sebenarnya DATA (nama mesin / unit, area, label hasil cek):
                          disimpan lewat form asli Mesin & Unit / Master Select saat Simpan
   Semua perubahan masuk DRAF milik user (POST tampilan/edit/draf); berlaku untuk semua user sesudah Simpan.
   ========================================================================= */
(function () {
    'use strict';
    var GX = window.GX;
    if (!GX || window.__gxLoaded) { return; }
    window.__gxLoaded = true;
    var doc = document, html = doc.documentElement;
    function $all(sel, root) { return Array.prototype.slice.call((root || doc).querySelectorAll(sel)); }
    function el(tag, cls, txt) { var e = doc.createElement(tag); if (cls) { e.className = cls; } if (txt != null) { e.textContent = txt; } return e; }
    function syncDrawer() { setTimeout(function () { html.classList.toggle('gx-drawer-on', !!doc.querySelector('.gx-drawer')); }, 0); }
    function norm(s) { return String(s == null ? '' : s).replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, ''); }
    function store(k, v) { try { if (v === undefined) { return window.sessionStorage.getItem(k); } window.sessionStorage.setItem(k, v); } catch (e) { return null; } return null; }
    function url(path, q) { return GX.url.base + path + (q ? (path.indexOf('?') === -1 ? '?' : '&') + q : ''); }
    var csrfQ = 'csrf_token=' + encodeURIComponent(GX.csrf);
    var here = (location.href.indexOf(GX.url.base) === 0 ? location.href.slice(GX.url.base.length) : '').replace(/#.*$/, '');
    function modeUrl(m) { return url('tampilan/edit/mode/' + m, csrfQ + '&kembali=' + encodeURIComponent(here)); }

    var items = GX.items || [];
    var eff = {};          // kunci -> teks baru (hasil versi sekarang + draf), diisi dari jawaban server
    var draftTeks = (GX.draft && GX.draft.teks) || {};
    var draftData = (GX.draft && GX.draft.data) || {};
    var count = GX.count || 0;
    var draftSus = (GX.draft && GX.draft.susunan) || {};
    var effS = {};   // cakupan -> grup -> spek (versi sekarang + draf), diisi dari jawaban server

    function keysOf(it) { return { halaman: 'p|' + GX.page + '|' + it.h, tempat: 'v|' + it.v + '|' + it.h, semua: 'g|' + it.h }; }
    function scopeOf(k) { return !k ? '' : (k.indexOf('p|') === 0 ? 'halaman' : (k.indexOf('g|') === 0 ? 'semua' : 'tempat')); }
    function curText(it) { return it.t != null ? it.t : it.d; }
    function placeName(it) { return it.w || it.v; }

    /* ======================================================== PRATINJAU */
    if (GX.mode === 'pratinjau') {
        var pill = el('div', 'gx-pill');
        pill.setAttribute('role', 'region'); pill.setAttribute('aria-label', 'Pratinjau tampilan');
        pill.appendChild(el('span', 'gx-pill__dot'));
        pill.appendChild(el('span', '', 'Pratinjau draf' + (count ? ' · ' + count + ' perubahan' : '')));
        var back = el('a', 'gx-btn gx-btn--primary'); back.href = modeUrl('edit'); back.innerHTML = '<i class="fa fa-pencil"></i> Kembali edit';
        var out = el('a', 'gx-btn gx-btn--ghost'); out.href = modeUrl('keluar'); out.title = 'Keluar (draf tetap tersimpan)'; out.innerHTML = '<i class="fa fa-times"></i>';
        pill.appendChild(back); pill.appendChild(out);
        doc.body.appendChild(pill);
        return;
    }

    /* ======================================================== MODE EDIT */
    html.classList.add('gx-edit');
    doc.body.classList.add('gx-has-bar');
    // alat: 'teks' (klik teks = ubah), 'susunan' (klik kartu / tombol = atur), 'nav' (Jelajah: klik = biasa)
    var tool = store('gx-tool') || (store('gx-nav') === '1' ? 'nav' : 'teks');
    if (['teks', 'susunan', 'nav'].indexOf(tool) === -1) { tool = 'teks'; }
    var navMode = tool !== 'teks';

    // nama tempat (dari server: GX.where[v])
    items.forEach(function (it) { it.w = (GX.where && GX.where[it.v]) || it.v; });

    function markStates() {
        $all('gx-t').forEach(function (t) {
            var it = items[+t.getAttribute('data-gx')];
            if (!it) { return; }
            var ks = keysOf(it), inDraft = !!(draftTeks[ks.halaman] || draftTeks[ks.tempat] || draftTeks[ks.semua]);
            t.classList.toggle('gx-is-draft', inDraft);
            t.classList.toggle('gx-is-set', !inDraft && it.t != null);
        });
        $all('gx-d').forEach(function (d) { d.classList.toggle('gx-is-draft', !!draftData[d.getAttribute('data-gxd')]); });
    }
    markStates();

    /* -------------------------------------------------------- bilah bawah */
    var bar = el('div', 'gx-bar'); bar.setAttribute('role', 'toolbar'); bar.setAttribute('aria-label', 'Mode edit tampilan');
    bar.innerHTML =
        '<div class="gx-bar__brand"><i class="fa fa-paint-brush" aria-hidden="true"></i><span>Mode edit</span></div>' +
        '<div class="gx-seg" role="group" aria-label="Klik untuk">' +
            '<button type="button" data-gx-seg="teks" title="Klik teks = ubah teks"><i class="fa fa-i-cursor"></i><span class="gx-lbl">Teks</span></button>' +
            '<button type="button" data-gx-seg="susunan" title="Klik kartu / tombol / tab = geser, sembunyikan, atur lebar"><i class="fa fa-th-large"></i><span class="gx-lbl">Susunan</span></button>' +
            '<button type="button" data-gx-seg="nav" title="Klik = buka tautan seperti biasa (pindah halaman)"><i class="fa fa-mouse-pointer"></i><span class="gx-lbl">Jelajah</span></button>' +
        '</div>' +
        '<span class="gx-bar__sep"></span>' +
        '<button type="button" class="gx-btn" data-gx-act="lain" title="Placeholder, tooltip, isi pilihan, teks tombol &amp; dialog"><i class="fa fa-list-ul"></i><span class="gx-lbl">Teks lain</span> <span class="gx-count" data-gx-lain-n>0</span></button>' +
        '<button type="button" class="gx-btn" data-gx-act="kotak" title="Tambah teks baru di halaman ini (geser &amp; atur bebas seperti Canva)"><i class="fa fa-font"></i><span class="gx-lbl">+ Teks</span></button>' +
        '<button type="button" class="gx-btn" data-gx-act="ikon" title="Tambah ikon di halaman ini (geser &amp; atur bebas seperti Canva)"><i class="fa fa-star-o"></i><span class="gx-lbl">+ Ikon</span></button>' +
        '<button type="button" class="gx-btn" data-gx-act="tema" title="Warna tampilan untuk semua user: terang / gelap, warna utama, warna status"><i class="fa fa-tint"></i><span class="gx-lbl">Tema</span></button>' +
        '<button type="button" class="gx-btn" data-gx-act="reset" title="Kembalikan semua teks &amp; susunan di halaman ini ke awal (masuk draf)"><i class="fa fa-undo"></i><span class="gx-lbl">Halaman awal</span></button>' +
        '<a class="gx-btn" data-gx-act="riwayat" title="Riwayat tampilan"><i class="fa fa-history"></i><span class="gx-lbl">Riwayat</span></a>' +
        '<span class="gx-bar__sep"></span>' +
        '<span class="gx-btn gx-btn--ghost" style="cursor:default" title="Perubahan di draf (belum terlihat user lain)"><span class="gx-lbl">Draf</span> <span class="gx-count" data-gx-count>0</span></span>' +
        '<a class="gx-btn" data-gx-act="pratinjau" title="Lihat halaman seperti user lain nanti melihatnya"><i class="fa fa-eye"></i><span class="gx-lbl">Pratinjau</span></a>' +
        '<button type="button" class="gx-btn" data-gx-act="buang" title="Buang semua perubahan di draf"><i class="fa fa-trash-o"></i><span class="gx-lbl">Buang</span></button>' +
        '<button type="button" class="gx-btn gx-btn--primary" data-gx-act="simpan" title="Simpan draf jadi versi baru untuk semua user"><i class="fa fa-check"></i><span class="gx-lbl">Simpan</span></button>' +
        '<a class="gx-btn gx-btn--ghost" data-gx-act="keluar" title="Keluar dari Mode edit (draf tetap tersimpan)"><i class="fa fa-times"></i></a>';
    doc.body.appendChild(bar);
    bar.querySelector('[data-gx-act=riwayat]').href = GX.url.riwayat;
    bar.querySelector('[data-gx-act=pratinjau]').href = modeUrl('pratinjau');
    bar.querySelector('[data-gx-act=keluar]').href = modeUrl('keluar');
    function setCount(n) {
        count = n;
        var c = bar.querySelector('[data-gx-count]');
        c.textContent = n; c.classList.toggle('is-on', n > 0);
        bar.querySelector('[data-gx-act=buang]').disabled = n === 0;
        bar.querySelector('[data-gx-act=simpan]').disabled = n === 0;
    }
    setCount(count);
    function setTool(t) {
        tool = t; navMode = t !== 'teks'; store('gx-tool', t); store('gx-nav', t === 'nav' ? '1' : '0');
        html.classList.toggle('gx-nav', t !== 'teks');
        html.classList.toggle('gx-sus', t === 'susunan');
        $all('[data-gx-seg]', bar).forEach(function (b) { var is = b.getAttribute('data-gx-seg') === t; b.classList.toggle('is-on', is); b.setAttribute('aria-pressed', is ? 'true' : 'false'); });
        closePop();
        if (window.__gxSusClose) { window.__gxSusClose(); }
    }
    function setNav(on) { setTool(on ? 'nav' : 'teks'); }
    setTool(tool);
    $all('[data-gx-seg]', bar).forEach(function (b) { b.addEventListener('click', function () { setTool(b.getAttribute('data-gx-seg')); }); });

    if (!store('gx-hint')) {
        var hint = el('div', 'gx-bar__hint', 'Klik teks bergaris untuk mengubahnya. Pilih "Jelajah" untuk pindah halaman.');
        doc.body.appendChild(hint);
        setTimeout(function () { hint.classList.add('is-gone'); setTimeout(function () { hint.remove(); }, 400); }, 5200);
        store('gx-hint', '1');
    }
    if (GX.stale) {
        toast('Versi tampilan sekarang sudah berubah sejak draf ini dibuat. Draf tetap diterapkan di atas versi sekarang.', 'info');
    }

    /* -------------------------------------------------------- kirim ke server */
    function post(path, data) {
        var fd = new FormData();
        Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        return fetch(url(path, csrfQ), { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-Requested-With': 'fetch' } })
            .then(function (r) { return r.json(); });
    }
    function sendOps(ops) {
        return post('tampilan/edit/draf', { ops: JSON.stringify(ops) }).then(function (j) {
            if (!j || !j.ok) { throw new Error((j && j.msg) || 'Gagal menyimpan draf.'); }
            draftTeks = j.draft.teks || {}; draftData = j.draft.data || {}; eff = j.eff || {};
            draftSus = j.draft.susunan || {}; effS = j.effS || {};
            setCount(j.count || 0);
            return j;
        });
    }
    function resolve(it) {
        var ks = keysOf(it);
        if (eff[ks.halaman]) { return { t: eff[ks.halaman].t, k: ks.halaman }; }
        if (eff[ks.tempat]) { return { t: eff[ks.tempat].t, k: ks.tempat }; }
        if (eff[ks.semua]) { return { t: eff[ks.semua].t, k: ks.semua }; }
        return null;
    }
    /** Terapkan hasil ke semua tempat di halaman yang memakai teks yang sama. */
    function refreshHash(h) {
        items.forEach(function (it, i) {
            if (it.h !== h) { return; }
            var r = resolve(it);
            it.t = r ? r.t : null; it.k = r ? r.k : '';
            var txt = curText(it);
            $all('gx-t[data-gx="' + i + '"]').forEach(function (t) { t.textContent = txt === '' ? it.d : txt; t.classList.toggle('gx-del', txt === ''); });
            $all('[data-gx-a]').forEach(function (n) {
                n.getAttribute('data-gx-a').split(';').forEach(function (pair) {
                    var p = pair.split(':');
                    if (+p[1] !== i) { return; }
                    if (p[0] === 'text') { n.textContent = txt; } else { n.setAttribute(p[0], txt); }
                });
            });
        });
        (GX.js || []).forEach(function (j) {
            if (j.h !== h) { return; }
            var r = resolve({ h: j.h, v: '-' }); j.t = r ? r.t : null; j.k = r ? r.k : '';
            window.GX_T = window.GX_T || {};
            if (j.t != null) { window.GX_T[norm(j.d)] = j.t; } else { delete window.GX_T[norm(j.d)]; }
        });
        markStates();
        renderLain();
    }

    /* -------------------------------------------------------- toast kecil */
    function toast(msg, type) {
        var t = el('div', 'genc-toast genc-toast--' + (type || 'success'));
        t.setAttribute('role', 'status');
        t.innerHTML = '<i class="fa ' + (type === 'danger' ? 'fa-exclamation-circle' : (type === 'info' ? 'fa-info-circle' : 'fa-check-circle')) + '"></i><span></span><button type="button" class="genc-toast__close" aria-label="Tutup">&times;</button>';
        t.querySelector('span').textContent = msg;
        t.querySelector('button').onclick = function () { t.remove(); };
        t.style.zIndex = 2080;
        doc.body.appendChild(t);
        setTimeout(function () { t.style.transition = 'opacity .3s'; t.style.opacity = '0'; setTimeout(function () { t.remove(); }, 350); }, 4200);
    }

    /* -------------------------------------------------------- popover edit */
    var pop = null, popFor = null;
    function closePop() {
        if (pop) { pop.remove(); pop = null; }
        if (popFor) { popFor.classList.remove('gx-on'); if (popFor.__gxRestore) { popFor.__gxRestore(); } popFor = null; }
    }
    function place(target) {
        if (window.matchMedia && window.matchMedia('(max-width: 760px)').matches) { return; }
        var r = target.getBoundingClientRect();
        var w = pop.offsetWidth, h = pop.offsetHeight;
        var left = Math.max(8, Math.min(r.left + window.pageXOffset, window.pageXOffset + doc.documentElement.clientWidth - w - 8));
        var top = r.bottom + window.pageYOffset + 8;
        if (r.bottom + h + 16 > window.innerHeight && r.top - h - 8 > 0) { top = r.top + window.pageYOffset - h - 8; }
        pop.style.left = left + 'px'; pop.style.top = top + 'px';
    }
    function opt(name, val, title, sub, on) {
        var l = el('label', 'gx-opt' + (on ? ' is-on' : ''));
        var i = doc.createElement('input'); i.type = 'radio'; i.name = name; i.value = val; i.checked = !!on;
        var t = el('span'); t.appendChild(el('b', '', title)); if (sub) { t.appendChild(el('small', '', sub)); }
        l.appendChild(i); l.appendChild(t);
        return l;
    }

    /**
     * Popover ubah teks. it = item (h, v, d, t, k, c); target = elemen yang disorot;
     * live(txt) = pratinjau langsung di halaman; js = teks tombol/dialog JS (tanpa "tempat ini").
     */
    function editText(it, target, live, js) {
        closePop();
        popFor = target || null;
        if (popFor) { popFor.classList.add('gx-on'); }
        var before = curText(it);
        pop = el('div', 'gx-pop'); pop.setAttribute('role', 'dialog'); pop.setAttribute('aria-label', 'Ubah teks');
        var k = el('div', 'gx-pop__k', 'Ubah teks');
        if (it.c === 'attr' || it.c === 'option') { k.appendChild(el('span', 'gx-tag', it.c === 'option' ? 'isi pilihan' : (it.an || 'atribut'))); }
        if (js) { k.appendChild(el('span', 'gx-tag', 'tombol / dialog')); }
        var dyn = it.v === '#';   // teks dari data / profil / gabungan kode: tanpa "Tempat ini"
        if (dyn) { var dt = el('span', 'gx-tag', 'teks dinamis'); dt.title = 'Teks ini dibentuk dari data / kode (mis. nama jenis unit). Yang diubah hanya tampilannya, datanya tetap.'; k.appendChild(dt); }
        var ks = keysOf(it);
        if (draftTeks[ks.halaman] || draftTeks[ks.tempat] || draftTeks[ks.semua]) { k.appendChild(el('span', 'gx-tag gx-tag--draft', 'di draf')); }
        pop.appendChild(k);
        var orig = el('p', 'gx-pop__orig'); orig.appendChild(el('b', '', 'Teks awal:')); orig.appendChild(doc.createTextNode(it.d));
        pop.appendChild(orig);
        var ta = doc.createElement('textarea'); ta.value = before; ta.maxLength = 500; ta.rows = Math.min(5, Math.max(1, Math.ceil(before.length / 42)));
        ta.setAttribute('aria-label', 'Teks baru');
        pop.appendChild(ta);
        var len = el('small', 'gx-pop__len'); pop.appendChild(len);
        var cur = scopeOf(it.k) || (js ? 'semua' : (dyn ? 'halaman' : 'tempat'));
        var fs = el('fieldset', 'gx-pop__scope'); fs.appendChild(el('legend', '', 'Berlaku di'));
        var nm = 'gx-sc-' + Date.now();
        if (!js && !dyn) { fs.appendChild(opt(nm, 'tempat', 'Tempat ini', placeName(it) + ' — semua halaman yang memakai bagian ini', cur === 'tempat')); }
        fs.appendChild(opt(nm, 'halaman', 'Halaman ini saja', GX.pageName || GX.page, cur === 'halaman'));
        fs.appendChild(opt(nm, 'semua', 'Semua halaman', 'setiap teks "' + (it.d.length > 40 ? it.d.slice(0, 40) + '…' : it.d) + '" di aplikasi', cur === 'semua'));
        pop.appendChild(fs);
        $all('input', fs).forEach(function (r) { r.addEventListener('change', function () { $all('.gx-opt', fs).forEach(function (o) { o.classList.toggle('is-on', o.querySelector('input').checked); }); }); });
        var err = el('div', 'gx-pop__err'); err.hidden = true; pop.appendChild(err);
        var foot = el('div', 'gx-pop__foot');
        var bReset = el('button', 'gx-pbtn gx-pbtn--link'); bReset.type = 'button'; bReset.innerHTML = '<i class="fa fa-undo"></i> Teks awal';
        var bDel = el('button', 'gx-pbtn gx-pbtn--link gx-pbtn--danger'); bDel.type = 'button'; bDel.innerHTML = '<i class="fa fa-trash-o"></i> Hapus'; bDel.title = 'Hapus teks ini (bisa dikembalikan lewat Teks awal)';
        var bCancel = el('button', 'gx-pbtn', 'Batal'); bCancel.type = 'button';
        var bOk = el('button', 'gx-pbtn gx-pbtn--primary'); bOk.type = 'button'; bOk.innerHTML = 'Terapkan <i class="fa fa-level-down fa-rotate-90"></i>';
        foot.appendChild(bReset); foot.appendChild(bDel); foot.appendChild(el('span', 'gx-sp')); foot.appendChild(bCancel); foot.appendChild(bOk);
        pop.appendChild(foot);
        doc.body.appendChild(pop);
        function upd() { len.textContent = ta.value.length + ' / 500' + (norm(ta.value) === '' ? ' · kosong = teks dihapus' : ''); if (live) { live(ta.value); } }
        if (popFor) { popFor.__gxRestore = function () { if (live) { live(before); } }; }
        ta.addEventListener('input', upd); upd();
        place(target || bar);
        setTimeout(function () { ta.focus(); ta.select(); }, 20);
        function apply(text) {
            var sc = (fs.querySelector('input:checked') || {}).value || 'tempat';
            var key = keysOf(it)[sc];
            var clear = [];
            // hanya cakupan yang LEBIH sempit dari pilihan yang dibuang (halaman > tempat > semua), supaya pilihan ini yang berlaku
            var order = ['halaman', 'tempat', 'semua'], lvl = order.indexOf(sc);
            order.slice(0, lvl).forEach(function (s) { var kk = keysOf(it)[s]; if (eff[kk] || draftTeks[kk] || it.k === kk) { clear.push(kk); } });
            bOk.disabled = true;
            sendOps([{ op: 'teks', key: key, t: text, hapus: text === '' ? 1 : 0, d: it.d, w: sc === 'semua' ? 'Semua halaman' : (sc === 'halaman' ? (GX.pageName || GX.page) : placeName(it)), clear: clear }])
                .then(function () { if (popFor) { popFor.__gxRestore = null; } closePop(); refreshHash(it.h); if (window.GXS) { window.GXS.applyGone(true); } })
                .catch(function (e) { err.hidden = false; err.textContent = e.message; bOk.disabled = false; });
        }
        bOk.addEventListener('click', function () { apply(norm(ta.value) === '' ? '' : ta.value); });
        bReset.addEventListener('click', function () { ta.value = it.d; upd(); apply(null); });
        bDel.addEventListener('click', function () { ta.value = ''; upd(); apply(''); });
        bCancel.addEventListener('click', closePop);
        ta.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); bOk.click(); }
            if (e.key === 'Escape' || e.key === 'Esc') { e.preventDefault(); closePop(); }
        });
    }

    /** Popover data (nama mesin / unit, area, pelaksanaan, label hasil cek). */
    function editData(node) {
        closePop();
        var key = node.getAttribute('data-gxd');
        var src = (GX.data && GX.data[key]) || null;
        var dd = draftData[key] || null;
        if (!src && !dd) { return; }
        var old = dd ? dd.old : src.text;
        var cur = dd ? dd.new : src.text;
        var what = src ? src.what : (dd.kind === 'pilihan' ? 'Label hasil cek' : (dd.field === 'label' ? 'Nama unit' : 'Nama mesin'));
        var kind = src ? src.kind : dd.kind;
        var max = kind === 'pilihan' ? 20 : ((src ? src.field : dd.field) === 'pelaksanaan' ? 150 : ((src ? src.field : dd.field) === 'area' ? 100 : 60));
        popFor = node; node.classList.add('gx-on');
        var showing = node.textContent;
        pop = el('div', 'gx-pop'); pop.setAttribute('role', 'dialog'); pop.setAttribute('aria-label', 'Ubah data');
        var k = el('div', 'gx-pop__k', what); k.appendChild(el('span', 'gx-tag gx-tag--data', 'data'));
        if (dd) { k.appendChild(el('span', 'gx-tag gx-tag--draft', 'di draf')); }
        pop.appendChild(k);
        var orig = el('p', 'gx-pop__orig'); orig.appendChild(el('b', '', 'Sekarang:')); orig.appendChild(doc.createTextNode(old)); pop.appendChild(orig);
        var ta = doc.createElement('textarea'); ta.value = cur; ta.maxLength = max; ta.rows = 1; ta.setAttribute('aria-label', what + ' baru');
        pop.appendChild(ta);
        var len = el('small', 'gx-pop__len'); pop.appendChild(len);
        var info = el('p', 'gx-pop__info');
        info.innerHTML = '<i class="fa fa-database"></i>';
        info.appendChild(doc.createTextNode(kind === 'pilihan'
            ? 'Label ini diatur di Master Select. Saat Simpan, perubahan dikirim lewat Master Select (kode di database & report tetap OK / NOK / PR).'
            : 'Ini data dari menu Mesin & Unit. Saat Simpan, perubahan dikirim lewat Mesin & Unit dan tercatat di Riwayat perubahan mesinnya (bukan di riwayat tampilan).'));
        pop.appendChild(info);
        var err = el('div', 'gx-pop__err'); err.hidden = true; pop.appendChild(err);
        var foot = el('div', 'gx-pop__foot');
        var bUndo = el('button', 'gx-pbtn gx-pbtn--link'); bUndo.type = 'button'; bUndo.innerHTML = '<i class="fa fa-undo"></i> Batalkan perubahan'; bUndo.hidden = !dd;
        var bCancel = el('button', 'gx-pbtn', 'Batal'); bCancel.type = 'button';
        var bOk = el('button', 'gx-pbtn gx-pbtn--primary'); bOk.type = 'button'; bOk.innerHTML = 'Terapkan <i class="fa fa-level-down fa-rotate-90"></i>';
        foot.appendChild(bUndo); foot.appendChild(el('span', 'gx-sp')); foot.appendChild(bCancel); foot.appendChild(bOk);
        pop.appendChild(foot);
        doc.body.appendChild(pop);
        node.__gxRestore = function () { node.textContent = showing; };
        function upd() { len.textContent = ta.value.length + ' / ' + max; node.textContent = norm(ta.value) || old; }
        ta.addEventListener('input', upd); upd();
        place(node);
        setTimeout(function () { ta.focus(); ta.select(); }, 20);
        function apply(v) {
            var parts = key.split('|');
            var op = { op: 'data', kind: kind, id: src ? src.id : dd.id, field: src ? src.field : dd.field, sel: src ? src.sel : dd.sel, old: old, 'new': v };
            if (!src) { op.id = parts[1]; }
            bOk.disabled = true;
            sendOps([op]).then(function () {
                node.__gxRestore = null; closePop();
                var nowTxt = (draftData[key] && draftData[key].new) || old;
                $all('gx-d').forEach(function (d) { if (d.getAttribute('data-gxd') === key || norm(d.textContent) === norm(showing)) { d.textContent = nowTxt; d.setAttribute('data-gxd', key); } });
                markStates();
            }).catch(function (e) { err.hidden = false; err.textContent = e.message; bOk.disabled = false; });
        }
        bOk.addEventListener('click', function () { var v = norm(ta.value); if (v === '') { err.hidden = false; err.textContent = what + ' tidak boleh kosong.'; return; } apply(v === norm(old) ? null : v); });
        bUndo.addEventListener('click', function () { apply(null); });
        bCancel.addEventListener('click', closePop);
        ta.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); bOk.click(); }
            if (e.key === 'Escape' || e.key === 'Esc') { e.preventDefault(); closePop(); }
        });
    }

    /* -------------------------------------------------------- IKON   [GENC-16OKT26-IKON]
       Klik ikon di halaman (alat Teks) -> ganti ikon (daftar Font Awesome + cari), warna, ukuran, hapus, ikon awal.
       Cakupan: ikon ini di halaman ini (ikon sejenis di baris-baris yang sama ikut) / semua ikon itu di semua halaman. */
    var XGI = window.GXS;
    var effI = window.GX_I || {}, draftIkon = (GX.draft && GX.draft.ikon) || {};
    var ICOLS = [['', 'Bawaan'], ['teks', 'Teks'], ['redup', 'Redup'], ['utama', 'Warna utama'], ['ok', 'OK'], ['nok', 'NOK'], ['pr', 'PR'], ['pending', 'Menunggu']];
    function iconsApply() { if (XGI) { XGI.applyIcons(effI, true); XGI.applyGone(true); } }
    window.__gxIkonDraft = function () { return draftIkon; };
    window.__gxIkonPage = function () { var a = Object.keys(effI.p || {}).length, d = draftIkon['p|' + GX.page] || {}; Object.keys(d).forEach(function (k) { if (!(effI.p || {})[k]) { a++; } }); return a; };
    iconsApply();
    function iconSpecOf(i) {
        var c0 = XGI.icon0(i), k = XGI.iconKey(i), P = effI.p || {}, G = effI.g || {};
        if (P[k]) { return { sp: P[k], scope: 'halaman', k: k, c0: c0 }; }
        if (G[c0]) { return { sp: G[c0], scope: 'semua', k: k, c0: c0 }; }
        return { sp: null, scope: 'halaman', k: k, c0: c0 };
    }
    /** Pemilih ikon: kotak cari + kisi ikon. onPick(kelas). */
    function iconPicker(cur, onPick) {
        var wrap = el('div', 'gx-ipick');
        var q = doc.createElement('input'); q.type = 'search'; q.className = 'gx-ipick__q'; q.placeholder = 'Cari ikon (mis. info, gembok, centang, mesin)…'; q.setAttribute('aria-label', 'Cari ikon');
        var grid = el('div', 'gx-ipick__grid'); grid.setAttribute('role', 'listbox');
        var more = el('div', 'gx-ipick__more');
        wrap.appendChild(q); wrap.appendChild(grid); wrap.appendChild(more);
        var ALL = GX.icons || [], W = GX.iconWords || {};
        function draw() {
            var v = norm(q.value).toLowerCase().replace(/^fa-/, ''), out = [];
            ALL.forEach(function (c) { var name = c.slice(3); if (!v || name.indexOf(v) !== -1 || (W[c] && W[c].toLowerCase().indexOf(v) !== -1)) { out.push(c); } });
            if (!v && cur) { out = [cur].concat(out.filter(function (c) { return c !== cur; })); }
            grid.innerHTML = '';
            out.slice(0, 240).forEach(function (c) {
                var b = el('button', 'gx-ipick__i' + (c === cur ? ' is-on' : '')); b.type = 'button'; b.title = c.slice(3).replace(/-/g, ' ') + (W[c] ? ' — ' + W[c] : ''); b.setAttribute('data-gx-ic', c); b.setAttribute('role', 'option');
                b.innerHTML = '<i class="fa ' + c + '" aria-hidden="true"></i>';
                grid.appendChild(b);
            });
            more.textContent = out.length > 240 ? 'Menampilkan 240 dari ' + out.length + ' ikon — ketik untuk mencari.' : (out.length ? out.length + ' ikon' : 'Tidak ada ikon yang cocok.');
        }
        q.addEventListener('input', draw);
        grid.addEventListener('click', function (e) { var b = e.target.closest('[data-gx-ic]'); if (b) { $all('.is-on', grid).forEach(function (x) { x.classList.remove('is-on'); }); b.classList.add('is-on'); cur = b.getAttribute('data-gx-ic'); onPick(cur); } });
        draw();
        wrap.__focus = function () { q.focus(); };
        return wrap;
    }
    function editIcon(i) {
        closePop();
        var info = iconSpecOf(i), sp = info.sp || {}, c0 = info.c0;
        var st = { i: sp.i !== undefined ? sp.i : null, c: sp.c || '', s: sp.s || 0 };
        popFor = i; i.classList.add('gx-on');
        var before = { cls: i.getAttribute('data-gx-icn') || c0, color: i.style.color, size: i.style.fontSize, del: i.classList.contains('gx-idel') };
        popFor.__gxRestore = function () { iconsApply(); };
        pop = el('div', 'gx-pop gx-pop--ikon'); pop.setAttribute('role', 'dialog'); pop.setAttribute('aria-label', 'Ubah ikon');
        var k = el('div', 'gx-pop__k', 'Ubah ikon');
        var inDraft = !!((draftIkon['p|' + GX.page] || {})[info.k] !== undefined || (draftIkon.g || {})[c0] !== undefined);
        if (inDraft) { k.appendChild(el('span', 'gx-tag gx-tag--draft', 'di draf')); }
        pop.appendChild(k);
        var pv = el('div', 'gx-ipv');
        var big = el('span', 'gx-ipv__big'); var bi = doc.createElement('i'); big.appendChild(bi);
        var nm = el('div', 'gx-ipv__name');
        pv.appendChild(big); pv.appendChild(nm); pop.appendChild(pv);
        function live() {
            var cls = st.i === null ? c0 : st.i;
            bi.className = 'fa ' + (cls || c0);
            nm.innerHTML = '';
            nm.appendChild(el('b', '', st.i === '' ? 'Ikon dihapus' : (cls || c0).slice(3).replace(/-/g, ' ')));
            nm.appendChild(el('small', '', 'Ikon awal: ' + c0.slice(3).replace(/-/g, ' ')));
            big.style.color = st.c ? (XGI.ICOL[st.c] || st.c) : '';
            big.classList.toggle('is-del', st.i === '');
            // pratinjau langsung di halaman
            var now = i.getAttribute('data-gx-icn'); if (now) { i.classList.remove(now); }
            i.classList.remove(c0); i.classList.add(st.i ? st.i : c0); if (st.i && st.i !== c0) { i.setAttribute('data-gx-icn', st.i); } else { i.removeAttribute('data-gx-icn'); }
            i.classList.toggle('gx-idel', st.i === '');
            i.style.color = st.c ? (XGI.ICOL[st.c] || st.c) : ''; i.style.fontSize = st.s ? st.s + 'px' : '';
            $all('[data-gx-icol]', pop).forEach(function (b) { b.classList.toggle('is-on', b.getAttribute('data-gx-icol') === st.c); });
        }
        var picker = iconPicker(st.i || c0, function (c) { st.i = c === c0 ? null : c; live(); });
        pop.appendChild(picker);
        var row = el('div', 'gx-pop__row');
        row.appendChild(el('span', 'gx-pop__lbl', 'Warna'));
        ICOLS.forEach(function (c) { var b = el('button', 'gx-xdot gx-xdot--' + (c[0] || 'awal')); b.type = 'button'; b.title = c[1]; b.setAttribute('data-gx-icol', c[0]); row.appendChild(b); });
        var size = el('select', 'gx-pop__sel'); size.setAttribute('aria-label', 'Ukuran ikon');
        [[0, 'Ukuran bawaan'], [12, '12 px'], [14, '14 px'], [16, '16 px'], [20, '20 px'], [24, '24 px'], [32, '32 px'], [40, '40 px'], [48, '48 px']].forEach(function (o) { var op = doc.createElement('option'); op.value = o[0]; op.textContent = o[1]; op.selected = +st.s === o[0]; size.appendChild(op); });
        row.appendChild(size);
        pop.appendChild(row);
        row.addEventListener('click', function (e) { var b = e.target.closest('[data-gx-icol]'); if (b) { st.c = b.getAttribute('data-gx-icol'); live(); } });
        size.addEventListener('change', function () { st.s = +size.value; live(); });
        var fs = el('fieldset', 'gx-pop__scope'); fs.appendChild(el('legend', '', 'Berlaku di'));
        var nmr = 'gx-ic-' + Date.now();
        fs.appendChild(opt(nmr, 'halaman', 'Ikon ini, halaman ini', (GX.pageName || GX.page) + ' — ikon yang sama di baris-baris sejenis ikut berubah', info.scope === 'halaman'));
        fs.appendChild(opt(nmr, 'semua', 'Semua halaman', 'setiap ikon "' + c0.slice(3).replace(/-/g, ' ') + '" di aplikasi', info.scope === 'semua'));
        pop.appendChild(fs);
        $all('input', fs).forEach(function (r) { r.addEventListener('change', function () { $all('.gx-opt', fs).forEach(function (o) { o.classList.toggle('is-on', o.querySelector('input').checked); }); }); });
        var err = el('div', 'gx-pop__err'); err.hidden = true; pop.appendChild(err);
        var foot = el('div', 'gx-pop__foot');
        var bReset = el('button', 'gx-pbtn gx-pbtn--link'); bReset.type = 'button'; bReset.innerHTML = '<i class="fa fa-undo"></i> Ikon awal';
        var bDel = el('button', 'gx-pbtn gx-pbtn--link gx-pbtn--danger'); bDel.type = 'button'; bDel.innerHTML = '<i class="fa fa-trash-o"></i> Hapus ikon';
        var bCancel = el('button', 'gx-pbtn'); bCancel.type = 'button'; bCancel.textContent = 'Batal';
        var bOk = el('button', 'gx-pbtn gx-pbtn--primary'); bOk.type = 'button'; bOk.innerHTML = '<i class="fa fa-check"></i> Simpan ke draf';
        foot.appendChild(bReset); foot.appendChild(bDel); foot.appendChild(el('span', 'gx-grow')); foot.appendChild(bCancel); foot.appendChild(bOk);
        pop.appendChild(foot);
        doc.body.appendChild(pop); place(i); live();
        setTimeout(function () { if (picker.__focus) { picker.__focus(); } }, 30);
        function send(spec) {
            var scope = (fs.querySelector('input:checked') || {}).value || 'halaman';
            var ops = [];
            var PSKI = 'p|' + GX.page;
            if (spec === null) {   // ikon awal: buang di kedua cakupan
                ops.push({ op: 'ikon', sk: PSKI, k: info.k, spec: null });
                if ((effI.g || {})[c0] || (draftIkon.g || {})[c0] !== undefined) { ops.push({ op: 'ikon', sk: 'g', k: c0, spec: null }); }
            } else {
                spec.d = c0;
                if (scope === 'semua') { ops.push({ op: 'ikon', sk: 'g', k: c0, spec: spec }); if ((effI.p || {})[info.k]) { ops.push({ op: 'ikon', sk: PSKI, k: info.k, spec: null }); } }
                else { ops.push({ op: 'ikon', sk: PSKI, k: info.k, spec: spec }); }
            }
            bOk.disabled = true;
            sendOps(ops).then(function (j) {
                if (j.err && j.err.indexOf('ikon') !== -1) { throw new Error('Ikon tidak bisa disimpan (nama ikon tidak dikenal).'); }
                var E = j.effI || {}; draftIkon = j.draftIkon || {};
                effI = { p: E['p|' + GX.page] || {}, g: E.g || {} };
                if (popFor) { popFor.__gxRestore = null; }
                closePop(); iconsApply();
                toast(spec === null ? 'Ikon kembali ke awal (masuk draf).' : (spec.i === '' ? 'Ikon dihapus (masuk draf). Klik ikon samarnya untuk mengembalikan.' : 'Ikon diubah (masuk draf).'), 'info');
            }).catch(function (e2) { err.hidden = false; err.textContent = e2.message; bOk.disabled = false; });
        }
        bOk.addEventListener('click', function () {
            var spec = {};
            if (st.i !== null && st.i !== c0) { spec.i = st.i; }
            if (st.c) { spec.c = st.c; }
            if (st.s) { spec.s = st.s; }
            send(Object.keys(spec).length ? spec : null);
        });
        bDel.addEventListener('click', function () { var spec = { i: '' }; if (st.c) { spec.c = st.c; } send(spec); });
        bReset.addEventListener('click', function () { send(null); });
        bCancel.addEventListener('click', function () { closePop(); });
        pop.addEventListener('keydown', function (e) { if (e.key === 'Escape' || e.key === 'Esc') { e.preventDefault(); closePop(); } });
    }

    /* -------------------------------------------------------- klik di halaman */
    doc.addEventListener('click', function (e) {
        if (navMode) { return; }
        if (e.target.closest && (e.target.closest('.gx-bar') || e.target.closest('.gx-pop') || e.target.closest('.gx-drawer') || e.target.closest('.gx-modal'))) { return; }
        var t = e.target.closest ? e.target.closest('gx-t, gx-d') : null;
        // [GENC-16OKT26-IKON] klik ikon (bukan di editor / teks tambahan) = ubah ikon
        var ic = !t && e.target.matches && e.target.matches('i.fa') && !e.target.closest('.gx-xbox, .gx-stb, .gx-handles, .genc-toast') ? e.target : null;
        if (ic) { e.preventDefault(); e.stopPropagation(); if (e.stopImmediatePropagation) { e.stopImmediatePropagation(); } editIcon(ic); return; }
        if (!t) {
            if (pop && !e.target.closest('.gx-pop')) { closePop(); }
            return;
        }
        e.preventDefault(); e.stopPropagation(); if (e.stopImmediatePropagation) { e.stopImmediatePropagation(); }
        if (t.tagName.toLowerCase() === 'gx-d') { editData(t); return; }
        var it = items[+t.getAttribute('data-gx')];
        if (!it) { return; }
        var same = $all('gx-t[data-gx="' + t.getAttribute('data-gx') + '"]');
        editText(it, t, function (txt) { var del = norm(txt) === ''; same.forEach(function (s) { s.textContent = del ? it.d : txt; s.classList.toggle('gx-del', del); }); });
    }, true);
    // tombol / tautan / label di Mode edit tidak menjalankan aksinya saat teksnya diklik (tetap jalan lewat "Jelajah")
    ['mousedown', 'pointerdown'].forEach(function (ev) {
        doc.addEventListener(ev, function (e) { if (!navMode && e.target.closest && (e.target.closest('gx-t, gx-d') || (e.target.matches && e.target.matches('i.fa') && !e.target.closest('.gx-bar, .gx-xbox, .gx-stb'))) && !e.target.closest('.gx-pop')) { e.stopPropagation(); } }, true);
    });
    doc.addEventListener('submit', function (e) {
        if (!navMode && doc.activeElement && doc.activeElement.closest && doc.activeElement.closest('gx-t, gx-d')) { e.preventDefault(); }
    }, true);
    doc.addEventListener('keydown', function (e) {
        if ((e.key === 'Escape' || e.key === 'Esc') && pop) { closePop(); }
    });
    window.addEventListener('resize', function () { if (pop && popFor) { place(popFor); } });

    /* -------------------------------------------------------- laci "Teks lain" */
    var drawer = null;
    function lainList() {
        var out = [];
        items.forEach(function (it, i) {
            if (it.c === 'attr' || it.c === 'option') { out.push({ grp: 'Atribut & isi pilihan', it: it, i: i }); return; }
            var vis = $all('gx-t[data-gx="' + i + '"]').some(function (t) { return t.getClientRects().length > 0; });
            if (!vis) { out.push({ grp: 'Teks yang sedang tersembunyi (dialog, pesan, tab lain)', it: it, i: i }); }
        });
        (GX.js || []).forEach(function (j) { out.push({ grp: 'Teks tombol & dialog (semua halaman)', it: j, js: true }); });
        return out;
    }
    function renderLain() {
        var n = lainList().length;
        bar.querySelector('[data-gx-lain-n]').textContent = n;
        if (!drawer) { return; }
        var list = drawer.querySelector('.gx-drawer__list');
        var q = norm(drawer.querySelector('input').value).toLowerCase();
        list.innerHTML = '';
        var last = '', shown = 0;
        lainList().forEach(function (x) {
            var txt = curText(x.it);
            if (q && (txt + ' ' + x.it.d).toLowerCase().indexOf(q) === -1) { return; }
            if (x.grp !== last) { list.appendChild(el('div', 'gx-grp', x.grp)); last = x.grp; }
            var row = el('button', 'gx-row'); row.type = 'button';
            var t = el('span', 'gx-row__t'); t.appendChild(el('b', '', txt));
            var sub = x.js ? 'tombol / dialog' : (x.it.c === 'option' ? 'isi pilihan' : (x.it.c === 'attr' ? (x.it.an || 'atribut') : placeName(x.it)));
            if (x.it.t != null) { sub += ' · awal: ' + x.it.d; }
            t.appendChild(el('small', '', sub));
            row.appendChild(t);
            var ks = keysOf(x.it);
            if (draftTeks[ks.halaman] || draftTeks[ks.tempat] || draftTeks[ks.semua]) { row.appendChild(el('span', 'gx-tag gx-tag--draft', 'draf')); }
            var ic = el('i', 'fa fa-pencil'); ic.setAttribute('aria-hidden', 'true'); row.appendChild(ic);
            row.addEventListener('click', function () { editText(x.it, row, null, !!x.js); });
            list.appendChild(row); shown++;
        });
        if (!shown) { list.appendChild(el('div', 'gx-empty', q ? 'Tidak ada yang cocok.' : 'Tidak ada teks lain di halaman ini.')); }
    }
    function openLain() {
        if (window.__gxTemaClose) { window.__gxTemaClose(); }
        syncDrawer();
        if (drawer) { drawer.remove(); drawer = null; return; }
        drawer = el('aside', 'gx-drawer'); drawer.setAttribute('aria-label', 'Teks lain di halaman ini');
        drawer.innerHTML = '<div class="gx-drawer__head"><div><h3>Teks lain di halaman ini</h3><p>Placeholder, tooltip, isi pilihan, teks yang sedang tersembunyi, serta teks tombol &amp; dialog.</p></div><button type="button" class="gx-pbtn gx-pbtn--link" aria-label="Tutup"><i class="fa fa-times"></i></button></div>' +
            '<div class="gx-drawer__find"><input type="search" placeholder="Cari teks" aria-label="Cari teks"><div class="gx-drawer__links">Halaman yang hanya terlihat sebelum login: <a data-gx-l="masuk"><i class="fa fa-sign-in"></i> Masuk</a> <a data-gx-l="daftar"><i class="fa fa-user-plus"></i> Daftar</a></div></div><div class="gx-drawer__list"></div>';
        doc.body.appendChild(drawer);
        drawer.querySelector('.gx-drawer__head button').addEventListener('click', openLain);
        drawer.querySelector('input').addEventListener('input', renderLain);
        $all('[data-gx-l]', drawer).forEach(function (a) { a.href = GX.url[a.getAttribute('data-gx-l')]; });
        renderLain();
        drawer.querySelector('input').focus();
    }
    // nama atribut untuk item atribut (dari data-gx-a)
    $all('[data-gx-a]').forEach(function (n) {
        n.getAttribute('data-gx-a').split(';').forEach(function (pair) {
            var p = pair.split(':'), it = items[+p[1]];
            if (it && p[0] !== 'text') { it.an = { 'placeholder': 'placeholder', 'title': 'tooltip', 'aria-label': 'label pembaca layar', 'alt': 'teks gambar', 'data-label': 'judul kolom (HP)' }[p[0]] || 'teks dialog'; }
        });
    });
    renderLain();
    bar.querySelector('[data-gx-act=lain]').addEventListener('click', openLain);

    /* -------------------------------------------------------- dialog */
    function modal(title, msg, okLabel, danger, extra) {
        return new Promise(function (resolve) {
            var m = el('div', 'gx-modal'); m.setAttribute('role', 'dialog'); m.setAttribute('aria-modal', 'true');
            var b = el('div', 'gx-modal__box');
            b.appendChild(el('h3', '', title));
            if (msg) { b.appendChild(el('p', '', msg)); }
            if (extra) { b.appendChild(extra); }
            var f = el('div', 'gx-modal__foot');
            var no = el('button', 'gx-pbtn', 'Batal'); no.type = 'button';
            var yes = el('button', 'gx-pbtn ' + (danger ? 'gx-pbtn--primary' : 'gx-pbtn--primary'), okLabel || 'Lanjutkan'); yes.type = 'button';
            if (danger) { yes.style.background = 'var(--gc-nok, #d92d20)'; yes.style.borderColor = 'var(--gc-nok, #d92d20)'; }
            f.appendChild(no); f.appendChild(yes); b.appendChild(f); m.appendChild(b);
            doc.body.appendChild(m);
            yes.focus();
            function done(v) { m.remove(); resolve(v); }
            no.onclick = function () { done(false); };
            yes.onclick = function () { done(true); };
            m.addEventListener('click', function (e) { if (e.target === m) { done(false); } });
            m.addEventListener('keydown', function (e) { if (e.key === 'Escape' || e.key === 'Esc') { done(false); } });
        });
    }

    /* -------------------------------------------------------- Halaman awal */
    bar.querySelector('[data-gx-act=reset]').addEventListener('click', function () {
        var keys = [], n = 0, glob = 0;
        items.forEach(function (it) {
            var ks = keysOf(it), any = false;
            ['halaman', 'tempat', 'semua'].forEach(function (s) { if (it.k === ks[s] || draftTeks[ks[s]] || eff[ks[s]]) { keys.push(ks[s]); any = true; if (s === 'semua') { glob++; } } });
            if (any) { n++; }
        });
        var dkeys = [];
        $all('gx-d').forEach(function (d) { var k = d.getAttribute('data-gxd'); if (draftData[k]) { dkeys.push(k); } });
        var spairs = window.__gxSusPairs ? window.__gxSusPairs() : [];
        var nk = window.__gxKotakPage ? window.__gxKotakPage() : 0;
        var ni = window.__gxIkonPage ? window.__gxIkonPage() : 0;   // [GENC-16OKT26-IKON]
        if (!n && !dkeys.length && !spairs.length && !nk && !ni) { toast('Semua teks & susunan di halaman ini sudah seperti awal.', 'info'); return; }
        modal('Kembalikan halaman ini ke tampilan awal?', n + ' teks' + (spairs.length ? ' + susunan ' + spairs.length + ' bagian' : '') + (nk ? ' + ' + nk + ' teks tambahan (dihapus)' : '') + (ni ? ' + ' + ni + ' ikon' : '') + (dkeys.length ? ' + ' + dkeys.length + ' perubahan data di draf' : '') + ' kembali seperti awal (masuk draf, belum berlaku sebelum Simpan).' +
            (glob ? ' ' + glob + ' di antaranya diatur "Semua halaman", jadi halaman lain ikut kembali.' : ''), 'Kembalikan', true).then(function (ok) {
            if (!ok) { return; }
            sendOps([{ op: 'reset', keys: keys, data: dkeys, susunan: spairs, kotak: nk ? 1 : 0, ikon: ni ? 1 : 0, page: GX.page }]).then(function () { location.reload(); }).catch(function (e) { toast(e.message, 'danger'); });
        });
    });

    /* -------------------------------------------------------- Buang draf */
    function postForm(path, fields) {
        var f = doc.createElement('form'); f.method = 'post'; f.action = url(path, csrfQ); f.style.display = 'none';
        Object.keys(fields).forEach(function (k) { var i = doc.createElement('input'); i.type = 'hidden'; i.name = k; i.value = fields[k]; f.appendChild(i); });
        doc.body.appendChild(f); f.submit();
    }
    bar.querySelector('[data-gx-act=buang]').addEventListener('click', function () {
        modal('Buang draf?', count + ' perubahan di draf dibuang. Tampilan kembali seperti versi sekarang (v' + GX.cur + ').', 'Buang draf', true).then(function (ok) {
            if (ok) { postForm('tampilan/edit/batal', { kembali: here }); }
        });
    });


    /* ======================================================== TEMA (4D)   [GENC-14OKT26-TEMA]
       Satu tema untuk semua user (masuk draf, berlaku sesudah Simpan). Server menghitung variabel warna
       (kontras teks dijaga otomatis) dan mengirim CSS-nya; di sini cukup mengganti <style id="gx-tema">. */
    var TM = GX.tema || {}, temaDrawer = null;
    var temaCur = TM.cur || {}, temaDraft = (GX.draft && GX.draft.tema) || {}, temaEff = TM.eff || {};
    function temaStyle() { var st = doc.getElementById('gx-tema'); if (!st) { st = el('style'); st.id = 'gx-tema'; doc.head.appendChild(st); } return st; }
    function sendTema(k, v) {
        return sendOps([{ op: 'tema', k: k, v: v }]).then(function (j) {
            temaCur = j.tema || {}; temaDraft = j.draftTema || {}; temaEff = j.temaEff || temaEff;
            temaStyle().textContent = j.temaCss || '';
            if (j.temaCss && temaEff.dasar === 'gelap') { html.setAttribute('data-gx-tema', 'gelap'); } else { html.removeAttribute('data-gx-tema'); }
            renderTema();
        }).catch(function (e) { toast(e.message, 'danger'); });
    }
    function temaDesc(k, v) {
        var st = TM.status || {};
        var nm = k === 'dasar' ? 'Dasar tampilan' : (k === 'aksen' ? 'Warna utama' : 'Warna ' + (st[k] ? st[k][0] : k));
        if (v == null) { return nm + ' kembali ke bawaan'; }
        if (k === 'dasar') { return nm + ': ' + (v === 'gelap' ? 'Gelap' : 'Terang'); }
        if (k === 'aksen') { return nm + ': ' + (TM.aksen && TM.aksen[v] ? TM.aksen[v][0] : v); }
        return nm + ': ' + v;
    }
    function lum(h) {
        var c = [1, 3, 5].map(function (i) { var v = parseInt(h.substr(i, 2), 16) / 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); });
        return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
    }
    function contrast(a, b) { var x = lum(a), y = lum(b); return (Math.max(x, y) + 0.05) / (Math.min(x, y) + 0.05); }
    function renderTema() {
        if (!temaDrawer) { return; }
        var body = temaDrawer.querySelector('.gx-tm__body');
        body.innerHTML = '';
        var isDraft = function (k) { return temaDraft.hasOwnProperty(k); };
        var sec = function (title, k, help) {
            var s2 = el('section', 'gx-tm__sec');
            var h = el('h4', '', title);
            if (k && isDraft(k)) { h.appendChild(el('span', 'gx-tag gx-tag--draft', 'draf')); }
            s2.appendChild(h);
            if (help) { s2.appendChild(el('p', 'gx-tm__help', help)); }
            body.appendChild(s2);
            return s2;
        };
        // dasar
        var s1 = sec('Dasar', 'dasar'), row = el('div', 'gx-tm__bases');
        [['terang', 'Terang', 'fa-sun-o'], ['gelap', 'Gelap', 'fa-moon-o']].forEach(function (b) {
            var on = (temaEff.dasar || 'terang') === b[0];
            var btn = el('button', 'gx-tm__base gx-tm__base--' + b[0] + (on ? ' is-on' : ''));
            btn.type = 'button'; btn.setAttribute('aria-pressed', on ? 'true' : 'false'); btn.setAttribute('data-gx-tm-dasar', b[0]);
            btn.innerHTML = '<span class="gx-tm__mini"><i></i><i></i><i></i></span><span><i class="fa ' + b[2] + '"></i> ' + b[1] + '</span>';
            btn.addEventListener('click', function () { if (!on) { sendTema('dasar', b[0] === 'terang' ? null : b[0]); } });
            row.appendChild(btn);
        });
        s1.appendChild(row);
        // warna utama
        var s3 = sec('Warna utama', 'aksen', 'Tombol utama, tautan, tab & penanda aktif. Warna pastel otomatis memakai teks gelap supaya tetap terbaca.');
        var cur = temaCur.aksen || 'biru';
        [['', 'Tegas'], ['-p', 'Pastel']].forEach(function (grp) {
            var lab = el('div', 'gx-tm__lab', grp[1]), sw = el('div', 'gx-tm__sw');
            Object.keys(TM.aksen || {}).forEach(function (k) {
                if ((k.slice(-2) === '-p') !== (grp[0] === '-p')) { return; }
                var a = TM.aksen[k], on = cur === k;
                var b = el('button', 'gx-tm__dot' + (on ? ' is-on' : '')); b.type = 'button';
                b.style.background = a[1]; b.title = a[0] + (k === 'biru' ? ' (bawaan)' : ''); b.setAttribute('aria-label', b.title); b.setAttribute('aria-pressed', on ? 'true' : 'false');
                b.setAttribute('data-gx-tm-aksen', k);
                if (on) { b.innerHTML = '<i class="fa fa-check"></i>'; b.style.color = contrast(a[1], '#ffffff') >= 3 ? '#fff' : '#101828'; }
                b.addEventListener('click', function () { if (!on) { sendTema('aksen', k === 'biru' ? null : k); } });
                sw.appendChild(b);
            });
            s3.appendChild(lab); s3.appendChild(sw);
        });
        var own = el('label', 'gx-tm__own'), isHex = /^#/.test(cur);
        own.innerHTML = '<input type="color" aria-label="Warna utama lain"><span>Warna lain' + (isHex ? ': <b>' + cur + '</b>' : '') + '</span>';
        var oi = own.querySelector('input'); oi.value = temaEff.aksen || '#1d6fe8';
        oi.addEventListener('change', function () { sendTema('aksen', oi.value); });
        if (isHex) { own.classList.add('is-on'); }
        s3.appendChild(own);
        // status
        var s4 = sec('Warna status', null, 'Dipakai di kalender, strip hasil cek, label & tombol hasil cek. Report PDF / Print / Word tidak ikut berubah.');
        Object.keys(TM.status || {}).forEach(function (k) {
            var st = TM.status[k], v = temaEff[k] || st[1], custom = v !== st[1];
            var r = el('div', 'gx-tm__st' + (isDraft(k) ? ' is-draft' : ''));
            var lab = el('span', 'gx-tm__stl', st[0]);
            var tag = el('span', 'genc-tag genc-tag--' + k, k === 'pending' ? 'Menunggu' : (k === 'pr' ? 'PR' : k.toUpperCase()));
            var inp = doc.createElement('input'); inp.type = 'color'; inp.value = v; inp.setAttribute('aria-label', 'Warna ' + st[0]); inp.setAttribute('data-gx-tm-st', k);
            inp.addEventListener('change', function () { sendTema(k, inp.value.toLowerCase() === st[1] ? null : inp.value); });
            var rb = el('button', 'gx-pbtn gx-pbtn--link', 'Bawaan'); rb.type = 'button'; rb.disabled = !custom; rb.title = 'Kembali ke ' + st[1];
            rb.addEventListener('click', function () { sendTema(k, null); });
            r.appendChild(lab); r.appendChild(tag); r.appendChild(inp); r.appendChild(rb);
            var surf = temaEff.dasar === 'gelap' ? '#161b22' : '#ffffff', cr = contrast(v, surf);
            if (custom && cr < 4.5) { r.appendChild(el('small', 'gx-tm__warn', 'Warna ini kurang kontras sebagai teks (' + cr.toFixed(1) + ':1) — teksnya otomatis digelapkan/diterangkan supaya terbaca.')); }
            s4.appendChild(r);
        });
        // contoh
        var s5 = sec('Contoh');
        var demo = el('div', 'gx-tm__demo');
        demo.innerHTML = '<div class="gx-tm__demo-row"><span class="genc-btn genc-btn--primary genc-btn--sm"><i class="fa fa-plus"></i> Tombol utama</span><span class="genc-btn genc-btn--sm">Biasa</span><a class="gx-tm__link">Tautan</a></div>' +
            '<div class="gx-tm__demo-row"><span class="genc-tag genc-tag--ok">OK</span><span class="genc-tag genc-tag--nok">NOK</span><span class="genc-tag genc-tag--pr">PR</span><span class="genc-tag genc-tag--pending">Menunggu</span><span class="genc-tag genc-tag--primary">Info</span></div>' +
            '<div class="gx-tm__demo-row gx-tm__cal"><i class="ok"></i><i class="ok"></i><i class="nok"></i><i class="ok"></i><i class="pr"></i><i></i><i class="ok"></i></div>';
        s5.appendChild(demo);
        var all = temaDrawer.querySelector('[data-gx-tm-all]');
        all.disabled = !Object.keys(temaCur).length;
    }
    function openTema() {
        syncDrawer();
        if (temaDrawer) { temaDrawer.remove(); temaDrawer = null; return; }
        if (drawer) { drawer.remove(); drawer = null; }
        temaDrawer = el('aside', 'gx-drawer gx-tm'); temaDrawer.setAttribute('aria-label', 'Tema tampilan');
        temaDrawer.innerHTML = '<div class="gx-drawer__head"><div><h3><i class="fa fa-tint"></i> Tema</h3><p>Satu tema untuk semua user. Masuk draf dulu — terlihat user lain sesudah <b>Simpan</b>.</p></div><button type="button" class="gx-pbtn gx-pbtn--link" aria-label="Tutup"><i class="fa fa-times"></i></button></div>' +
            '<div class="gx-drawer__list gx-tm__body"></div>' +
            '<div class="gx-tm__foot"><button type="button" class="gx-pbtn" data-gx-tm-all><i class="fa fa-undo"></i> Semua ke bawaan</button></div>';
        doc.body.appendChild(temaDrawer);
        temaDrawer.querySelector('.gx-drawer__head button').addEventListener('click', openTema);
        temaDrawer.querySelector('[data-gx-tm-all]').addEventListener('click', function () {
            var ops = ['dasar', 'aksen'].concat(Object.keys(TM.status || {})).map(function (k) { return { op: 'tema', k: k, v: null }; });
            sendOps(ops).then(function (j) {
                temaCur = j.tema || {}; temaDraft = j.draftTema || {}; temaEff = j.temaEff || temaEff;
                temaStyle().textContent = j.temaCss || ''; html.removeAttribute('data-gx-tema'); renderTema();
            }).catch(function (e) { toast(e.message, 'danger'); });
        });
        renderTema();
    }
    window.__gxTemaClose = function () { if (temaDrawer) { temaDrawer.remove(); temaDrawer = null; } syncDrawer(); };
    doc.addEventListener('keydown', function (e) {
        if ((e.key === 'Escape' || e.key === 'Esc') && temaDrawer && !doc.querySelector('.gx-modal')) { window.__gxTemaClose(); }
    });
    bar.querySelector('[data-gx-act=tema]').addEventListener('click', openTema);

    /* ======================================================== TEKS TAMBAHAN (ala Canva)   [GENC-14OKT26-KOTAK]
       Tombol "+ Teks" -> kotak teks baru di tengah layar. Klik = pilih (toolbar), seret = pindah (menempel ke grid),
       tarik tepi kiri/kanan = lebar, klik 2x = ketik. Disimpan ke draf (op 'kotak'), berlaku semua user sesudah Simpan. */
    var XG = window.GXS;
    var kotakEff = {}, draftKotak = (GX.draft && GX.draft.kotak) || {}, xSel = null, xtb = null, xEditing = null;
    (window.GX_X || []).forEach(function (sp) { (kotakEff[sp.sk] = kotakEff[sp.sk] || {})[sp.id] = sp; });
    var PSK = 'p|' + GX.page;
    function xList() {
        var out = [];
        [PSK, 'g'].forEach(function (sk) { Object.keys(kotakEff[sk] || {}).forEach(function (id) { var sp = kotakEff[sk][id]; if (sp) { var c = {}; for (var k in sp) { c[k] = sp[k]; } c.id = id; c.sk = sk; out.push(c); } }); });
        out.sort(function (a, b) { return (a.n || 0) - (b.n || 0); });
        return out;
    }
    function xFind(id) { var l = xList(); for (var i = 0; i < l.length; i++) { if (l[i].id === id) { return l[i]; } } return null; }
    window.__gxKotakPage = function () { var n = 0; [PSK].forEach(function (sk) { n += Object.keys(kotakEff[sk] || {}).length + Object.keys(draftKotak[sk] || {}).filter(function (id) { return !(kotakEff[sk] || {})[id]; }).length; }); return n; };
    window.__gxKotakDraft = function () { return draftKotak; };
    function xHost() { return doc.getElementById('page-content'); }
    function xRender() {
        if (!XG) { return; }
        var keep = xSel && xSel.id;
        xCloseTb();
        XG.renderBoxes(xList(), true).forEach(function (b) {
            b.classList.add('gx-xbox--edit');
            ['l', 'r'].forEach(function (side) { var h = el('span', 'gx-xbox__h gx-xbox__h--' + side); h.setAttribute('data-gx-xh', side); h.title = 'Tarik untuk mengubah lebar'; b.appendChild(h); });
        });
        if (keep && xFind(keep)) { xSelect(keep); }
    }
    function xAnchorOf(box) {
        if (!XG || narrow()) { return null; }
        var br = box.getBoundingClientRect(), host = xHost(), best = null, bb = -1e9, hw = host ? host.getBoundingClientRect().width : 0;
        XG.scan().forEach(function (g) {
            if (!host || !host.contains(g.el)) { return; }
            g.blocks.forEach(function (b) {
                if (XG.isDeep(b.el) || b.el.closest('.gx-xbox') || b.el.classList.contains('gx-hide')) { return; }
                var r = b.el.getBoundingClientRect();
                if (!r.width || r.bottom > br.top + 4 || r.width < hw * 0.25) { return; }   // jangkar = bagian besar (kartu / baris), bukan tombol kecil
                var score = r.bottom - Math.abs(r.left - br.left) * 0.01;
                if (score > bb) { bb = score; best = g.key + '::' + b.key; }
            });
        });
        return best;
    }
    function xSend(id, spec, sk, from) {
        return sendOps([{ op: 'kotak', sk: sk, id: id, spec: spec, from: from || '' }]).then(function (j) {
            draftKotak = j.draftKotak || {}; kotakEff = j.effK || {};
            return j;
        }).catch(function (e) { toast(e.message, 'danger'); throw e; });
    }
    function xSave(id, patch, newSk) {
        var sp = xFind(id); if (!sp) { return Promise.resolve(); }
        var spec = {}; ['t', 'x', 'y', 'w', 's', 'b', 'i', 'c', 'bg', 'a', 'anc', 'n', 'ic'].forEach(function (k) { if (sp[k] !== undefined) { spec[k] = sp[k]; } });
        for (var k in (patch || {})) { spec[k] = patch[k]; }
        var box = doc.querySelector('.gx-xbox[data-gx-x="' + id + '"]');
        if (box && ('x' in (patch || {}) || 'y' in (patch || {}) || !spec.anc)) { var a = xAnchorOf(box); if (a) { spec.anc = a; } }
        var sk = newSk || sp.sk;
        // pratinjau langsung dulu (tanpa menunggu server)
        (kotakEff[sk] = kotakEff[sk] || {})[id] = spec; if (newSk && newSk !== sp.sk && kotakEff[sp.sk]) { delete kotakEff[sp.sk][id]; }
        return xSend(id, spec, sk, newSk && newSk !== sp.sk ? sp.sk : '').then(function () { xRender(); });
    }
    function xNew(ic) {
        var host = xHost(); if (!host) { toast('Halaman ini tidak punya area isi untuk teks tambahan.', 'info'); return; }
        var hr = host.getBoundingClientRect();
        var id = (Date.now().toString(36) + Math.random().toString(36).slice(2, 6)).slice(-10);
        var y = Math.max(16, Math.round((window.innerHeight * 0.3) - hr.top));
        var spec = ic   // [GENC-16OKT26-IKON] + Ikon: kotak berisi ikon saja (bisa diberi teks lewat Ketik)
            ? { t: '', ic: ic, x: 46, y: Math.round(y / 4) * 4, w: 8, s: 32, b: 0, i: 0, c: 'utama', bg: 'none', a: 'c', anc: '', n: Date.now() }
            : { t: 'Teks baru', x: 30, y: Math.round(y / 4) * 4, w: 40, s: 20, b: 1, i: 0, c: 'teks', bg: 'none', a: 'l', anc: '', n: Date.now() };
        (kotakEff[PSK] = kotakEff[PSK] || {})[id] = spec;
        xRender();
        var box = doc.querySelector('.gx-xbox[data-gx-x="' + id + '"]'); if (box) { spec.anc = xAnchorOf(box) || ''; }
        xSend(id, spec, PSK).then(function () { xRender(); xSelect(id); if (!ic) { xEdit(id); } else { xIconPick(id); } });
    }
    /** [GENC-16OKT26-IKON] pilih / ganti ikon kotak tambahan. */
    function xIconPick(id) {
        var sp = xFind(id); if (!sp || !xtb) { return; }
        var old = xtb.querySelector('.gx-xtb__ipick'); if (old) { old.remove(); return; }
        var box = el('div', 'gx-xtb__ipick');
        var pk = iconPicker(sp.ic || '', function (c) { xSave(id, { ic: c }).then(function () { xSelect(id); }); });
        box.appendChild(pk);
        if (sp.ic && norm(sp.t || '') !== '') { var no = el('button', 'gx-pbtn gx-pbtn--link'); no.type = 'button'; no.innerHTML = '<i class="fa fa-ban"></i> Tanpa ikon'; no.addEventListener('click', function () { xSave(id, { ic: '' }).then(function () { xSelect(id); }); }); box.appendChild(no); }
        xtb.appendChild(box);
        setTimeout(function () { if (pk.__focus) { pk.__focus(); } }, 20);
    }
    function xDelete(id) {
        var sp = xFind(id); if (!sp) { return; }
        delete (kotakEff[sp.sk] || {})[id]; xSel = null; xRender();
        xSend(id, null, sp.sk).then(function () { xRender(); toast('Teks tambahan dihapus (masuk draf).', 'info'); });
    }
    function xCloseTb() { if (xtb) { xtb.remove(); xtb = null; } $all('.gx-xbox.is-sel').forEach(function (b) { b.classList.remove('is-sel'); }); }
    function xDeselect() { if (xEditing) { xEditing.blur(); } xCloseTb(); xSel = null; }
    var XCOLS = [['teks', 'Teks'], ['redup', 'Redup'], ['utama', 'Warna utama'], ['ok', 'OK'], ['nok', 'NOK'], ['pr', 'PR'], ['pending', 'Menunggu']];
    var XBGS = [['none', 'Tanpa latar'], ['soft', 'Latar lembut'], ['kartu', 'Kartu'], ['utama', 'Latar warna utama'], ['kuning', 'Catatan']];
    function xSelect(id) {
        xCloseTb();
        var sp = xFind(id), box = doc.querySelector('.gx-xbox[data-gx-x="' + id + '"]');
        if (!sp || !box) { return; }
        xSel = { id: id }; box.classList.add('is-sel');
        xtb = el('div', 'gx-stb gx-xtb'); xtb.setAttribute('role', 'toolbar'); xtb.setAttribute('aria-label', 'Atur teks tambahan');
        var r1 = el('div', 'gx-stb__row');
        r1.appendChild(el('span', 'gx-stb__name', sp.ic && norm(sp.t || '') === '' ? 'Ikon tambahan' : 'Teks tambahan'));
        var sz = el('select', 'gx-stb__scope gx-xtb__size'); sz.setAttribute('aria-label', 'Ukuran huruf'); sz.setAttribute('data-gx-xs', 's');
        [12, 14, 16, 20, 24, 32, 40, 48, 64].forEach(function (n) { var o = doc.createElement('option'); o.value = n; o.textContent = n + ' px'; o.selected = +sp.s === n; sz.appendChild(o); });
        r1.appendChild(sz);
        r1.appendChild(sb('fa-bold', '', 'Tebal', 'xb', !!sp.b));
        r1.appendChild(sb('fa-italic', '', 'Miring', 'xi', !!sp.i));
        [['l', 'fa-align-left'], ['c', 'fa-align-center'], ['r', 'fa-align-right']].forEach(function (a) { r1.appendChild(sb(a[1], '', 'Rata', 'xa' + a[0], sp.a === a[0])); });
        xtb.appendChild(r1);
        var r2 = el('div', 'gx-stb__row');
        r2.appendChild(el('span', 'gx-stb__k', 'Warna'));
        XCOLS.forEach(function (c) { var b = sb('', '', c[1], 'xc' + c[0], sp.c === c[0]); b.classList.add('gx-xdot', 'gx-xdot--' + c[0]); r2.appendChild(b); });
        var bg = el('select', 'gx-stb__scope'); bg.setAttribute('aria-label', 'Latar'); bg.setAttribute('data-gx-xs', 'bg');
        XBGS.forEach(function (c) { var o = doc.createElement('option'); o.value = c[0]; o.textContent = c[1]; o.selected = sp.bg === c[0]; bg.appendChild(o); });
        r2.appendChild(bg);
        xtb.appendChild(r2);
        var r3 = el('div', 'gx-stb__row gx-stb__row--grp');
        r3.appendChild(sb('fa-pencil', 'Ketik', 'Ubah isi teks (atau klik 2x pada kotak)', 'xedit'));
        r3.appendChild(sb(sp.ic || 'fa-star-o', 'Ikon', sp.ic ? 'Ganti ikon' : 'Tambah ikon di depan teks', 'xic'));
        var scs = el('select', 'gx-stb__scope'); scs.setAttribute('aria-label', 'Berlaku di'); scs.setAttribute('data-gx-xs', 'sk');
        [[PSK, 'Halaman ini saja'], ['g', 'Semua halaman']].forEach(function (o) { var op = doc.createElement('option'); op.value = o[0]; op.textContent = o[1]; op.selected = sp.sk === o[0]; scs.appendChild(op); });
        r3.appendChild(scs);
        r3.appendChild(sb('fa-clone', '', 'Duplikat', 'xdup'));
        r3.appendChild(sb('fa-trash-o', 'Hapus', 'Hapus teks ini', 'xdel'));
        xtb.appendChild(r3);
        if (narrow()) { xtb.appendChild(el('div', 'gx-stb__hint', 'Di HP teks tambahan mengalir di bawah bagian terdekat. Geser posisinya dari layar lebar.')); }
        doc.body.appendChild(xtb);
        var br = box.getBoundingClientRect();
        var top = br.top + window.pageYOffset - xtb.offsetHeight - 10;
        if (br.top - xtb.offsetHeight - 10 < 64) { top = br.bottom + window.pageYOffset + 10; }
        xtb.style.left = Math.max(8, Math.min(br.left + window.pageXOffset, window.pageXOffset + doc.documentElement.clientWidth - xtb.offsetWidth - 8)) + 'px';
        xtb.style.top = top + 'px';
        xtb.addEventListener('click', function (e) {
            var b = e.target.closest('[data-gx-s]'); if (!b) { return; }
            var a = b.getAttribute('data-gx-s'), s2 = xFind(id); if (!s2) { return; }
            if (a === 'xb') { xSave(id, { b: s2.b ? 0 : 1 }); }
            else if (a === 'xi') { xSave(id, { i: s2.i ? 0 : 1 }); }
            else if (a.indexOf('xa') === 0) { xSave(id, { a: a.slice(2) }); }
            else if (a.indexOf('xc') === 0) { xSave(id, { c: a.slice(2) }); }
            else if (a === 'xedit') { xEdit(id); }
            else if (a === 'xic') { xIconPick(id); }
            else if (a === 'xdel') { xDelete(id); }
            else if (a === 'xdup') {
                var nid = (Date.now().toString(36) + Math.random().toString(36).slice(2, 6)).slice(-10), c = {};
                ['t', 'x', 'y', 'w', 's', 'b', 'i', 'c', 'bg', 'a', 'anc', 'ic'].forEach(function (k) { if (s2[k] !== undefined) { c[k] = s2[k]; } });
                c.y = (+c.y || 0) + 24; c.x = Math.min(97 - c.w, (+c.x || 0) + 2); c.n = Date.now();
                (kotakEff[s2.sk] = kotakEff[s2.sk] || {})[nid] = c; xRender();
                xSend(nid, c, s2.sk).then(function () { xRender(); xSelect(nid); });
            }
        });
        xtb.addEventListener('change', function (e) {
            var f = e.target.getAttribute('data-gx-xs');
            if (f === 's') { xSave(id, { s: +e.target.value }); }
            else if (f === 'bg') { xSave(id, { bg: e.target.value }); }
            else if (f === 'sk') { xSave(id, {}, e.target.value); }
        });
    }
    function xEdit(id) {
        var box = doc.querySelector('.gx-xbox[data-gx-x="' + id + '"]'); if (!box) { return; }
        var t = box.querySelector('.gx-xbox__t'), before = t.textContent;
        try { t.contentEditable = 'plaintext-only'; } catch (e) { t.contentEditable = 'true'; }
        if (t.contentEditable !== 'plaintext-only') { t.contentEditable = 'true'; }
        xEditing = t; box.classList.add('is-typing');
        t.focus();
        var rg = doc.createRange(); rg.selectNodeContents(t); var s2 = window.getSelection(); s2.removeAllRanges(); s2.addRange(rg);
        function done() {
            t.removeEventListener('blur', done); t.removeEventListener('keydown', kd);
            t.contentEditable = 'false'; box.classList.remove('is-typing'); xEditing = null;
            var now = (t.innerText || t.textContent || '').replace(/ /g, ' ').replace(/\s+$/, '');
            if (now.replace(/\s/g, '') === '') { var s3 = xFind(id); if (s3 && s3.ic) { if (before !== '') { xSave(id, { t: '' }); } return; } xDelete(id); return; }   // [GENC-16OKT26-IKON] kotak ikon boleh tanpa teks
            if (now !== before) { xSave(id, { t: now }); }
        }
        function kd(e) {
            if (e.key === 'Escape' || e.key === 'Esc') { e.preventDefault(); t.blur(); }
            if (e.key === 'Enter' && (e.metaKey || e.ctrlKey)) { e.preventDefault(); t.blur(); }
            e.stopPropagation();
        }
        t.addEventListener('blur', done); t.addEventListener('keydown', kd);
    }
    /* seret (pindah) & tarik tepi (lebar) — menempel ke grid 12 kolom wadah & kelipatan 4 px */
    doc.addEventListener('pointerdown', function (e) {
        if (navMode && tool === 'nav') { return; }
        var box = e.target.closest && e.target.closest('.gx-xbox--edit'); if (!box || xEditing && box.contains(xEditing)) { return; }
        var id = box.getAttribute('data-gx-x'), sp = xFind(id); if (!sp) { return; }
        e.preventDefault(); e.stopPropagation();
        closePop(); if (window.__gxSusClose) { window.__gxSusClose(); }
        if (narrow()) { xSelect(id); return; }
        var host = xHost(), hr = host.getBoundingClientRect(), side = e.target.getAttribute && e.target.getAttribute('data-gx-xh');
        var sx = e.clientX, sy = e.clientY, ox = +sp.x || 0, oy = +sp.y || 0, ow = +sp.w || 30, moved = false;
        var guide = el('div', 'gx-xguide'); guide.hidden = true; host.appendChild(guide);
        function snapX(v) {
            var col = 100 / 12, near = Math.round(v / col) * col;
            if (Math.abs(near - v) * hr.width / 100 < 8) { guide.hidden = false; guide.style.left = near + '%'; return near; }
            guide.hidden = true; return Math.round(v * 2) / 2;
        }
        function mv(ev) {
            var dx = (ev.clientX - sx) / hr.width * 100, dy = ev.clientY - sy;
            if (!moved && Math.abs(ev.clientX - sx) + Math.abs(dy) < 4) { return; }
            if (!moved) { moved = true; xCloseTb(); box.classList.add('is-sel', 'is-moving'); doc.body.classList.add('gx-is-dragging'); }
            if (side === 'r') { box.style.width = Math.max(3, Math.min(100 - ox, snapX(ox + ow + dx) - ox)) + '%'; return; }
            if (side === 'l') { var nx = Math.max(0, Math.min(ox + ow - 3, snapX(ox + dx))); box.style.left = nx + '%'; box.style.width = (ox + ow - nx) + '%'; return; }
            var x = Math.max(0, Math.min(100 - ow, snapX(ox + dx))), y = Math.max(0, Math.round((oy + dy) / 4) * 4);
            box.style.left = x + '%'; box.style.top = y + 'px';
        }
        function up() {
            doc.removeEventListener('pointermove', mv); doc.removeEventListener('pointerup', up);
            guide.remove(); box.classList.remove('is-moving'); doc.body.classList.remove('gx-is-dragging');
            if (!moved) { xSelect(id); return; }
            xSave(id, { x: Math.round(parseFloat(box.style.left) * 100) / 100, y: parseInt(box.style.top, 10) || 0, w: Math.round(parseFloat(box.style.width) * 100) / 100 }).then(function () { xSelect(id); });
        }
        doc.addEventListener('pointermove', mv); doc.addEventListener('pointerup', up);
    }, true);
    doc.addEventListener('dblclick', function (e) {
        var box = e.target.closest && e.target.closest('.gx-xbox--edit'); if (!box || tool === 'nav') { return; }
        e.preventDefault(); xEdit(box.getAttribute('data-gx-x'));
    }, true);
    doc.addEventListener('click', function (e) {
        if (e.target.closest && e.target.closest('.gx-xtb')) { return; }
        if (e.target.closest && e.target.closest('.gx-xbox--edit')) { if (tool !== 'nav') { e.preventDefault(); e.stopPropagation(); } return; }
        if (xSel && !(e.target.closest && e.target.closest('.gx-bar, .gx-modal'))) { xDeselect(); }
    }, true);
    doc.addEventListener('keydown', function (e) {
        if (!xSel || xEditing || (e.target && /input|textarea|select/i.test(e.target.tagName))) { return; }
        var sp = xFind(xSel.id); if (!sp) { return; }
        if (e.key === 'Escape' || e.key === 'Esc') { xDeselect(); return; }
        if (e.key === 'Delete' || e.key === 'Backspace') { e.preventDefault(); xDelete(xSel.id); return; }
        if (e.key === 'Enter') { e.preventDefault(); xEdit(xSel.id); return; }
        var st = e.shiftKey ? 5 : 0.5, sy = e.shiftKey ? 24 : 4, p = null;
        if (e.key === 'ArrowLeft') { p = { x: Math.max(0, sp.x - st) }; } else if (e.key === 'ArrowRight') { p = { x: Math.min(100 - sp.w, sp.x + st) }; }
        else if (e.key === 'ArrowUp') { p = { y: Math.max(0, sp.y - sy) }; } else if (e.key === 'ArrowDown') { p = { y: sp.y + sy }; }
        if (p) { e.preventDefault(); var id = xSel.id; xSave(id, p).then(function () { xSelect(id); }); }
    }, true);
    bar.querySelector('[data-gx-act=kotak]').addEventListener('click', function () { if (tool === 'nav') { setTool('teks'); } xNew(); });
    bar.querySelector('[data-gx-act=ikon]').addEventListener('click', function () { if (tool === 'nav') { setTool('teks'); } xNew('fa-star'); });
    window.addEventListener('resize', function () { var n = narrow(); if (n !== xRender.n) { xRender.n = n; xRender(); } });
    xRender.n = narrow();
    xRender();

    /* ======================================================== SUSUNAN (4B) + POSISI BEBAS (4C)   [GENC-14OKT26-SUSUNAN] */
    var GXS = window.GXS;
    var groups = GXS ? GXS.scan() : [];
    if (GXS) { GXS.applyAll(window.GX_S || {}, true); }
    var SPAN_LBL = { 3: '¼', 4: '⅓', 6: '½', 8: '⅔', 9: '¾', 12: 'Penuh' };
    function narrow() { return window.innerWidth <= (GX.narrow || 768); }
    function groupOf(el) { for (var i = 0; i < groups.length; i++) { if (groups[i].el === el) { return groups[i]; } } return null; }
    function blockAt(t) {
        var el = t;
        while (el && el !== doc.body) {
            if (GXS.isBlock(el)) { var g = groupOf(el.parentElement); if (g) { return { el: el, g: g }; } }
            el = el.parentElement;
        }
        return null;
    }
    /** Semua blok di bawah titik klik, dari yang paling dalam ke luar. */
    function chainAt(t) {
        var out = [], el = t;
        while (el && el !== doc.body) {
            if (GXS.isBlock(el)) { var g = groupOf(el.parentElement); if (g) { out.push({ el: el, g: g }); } }
            el = el.parentElement;
        }
        return out;
    }
    /** Blok yang dipilih klik: blok terdalam yang punya kelas sendiri (kartu, tombol, tab, …); kalau blok itu sudah
     *  terpilih, klik lagi = masuk satu tingkat ke dalam (isi kartu: judul, paragraf, daftar …) — pola Figma/Canva. */
    function pickAt(t) {
        var ch = chainAt(t);
        if (!ch.length) { return null; }
        if (sel) {
            for (var i = 0; i < ch.length; i++) { if (ch[i].el === sel.el) { return i > 0 ? ch[i - 1] : ch[0]; } }
        }
        for (var j = 0; j < ch.length; j++) { if (!GXS.isDeep(ch[j].el)) { return ch[j]; } }
        return ch[0];
    }
    function keyOfBlock(g, el) { for (var i = 0; i < g.blocks.length; i++) { if (g.blocks[i].el === el) { return g.blocks[i].key; } } return ''; }
    /** File view sebuah grup = file terbanyak di teks bertanda di dalamnya (untuk cakupan "Tempat ini"). */
    function viewOf(g) {
        if (g.view !== undefined) { return g.view; }
        var cnt = {}, best = '', n = 0, box = g.el;
        // grup tanpa teks bertanda (mis. menu berisi nama mesin = data): pakai pembungkus terdekat yang punya teks bertanda
        while (box && box !== doc.body && !box.querySelector('gx-t')) { box = box.parentElement; }
        if (!box || box === doc.body) { box = g.el; }
        $all('gx-t', box).forEach(function (t) { var it = items[+t.getAttribute('data-gx')]; if (it && it.v && it.v !== 'toast') { cnt[it.v] = (cnt[it.v] || 0) + 1; if (cnt[it.v] > n) { n = cnt[it.v]; best = it.v; } } });
        g.view = best;
        return best;
    }
    function nameOf(g) {
        var c = g.el.className || '', b0 = g.blocks[0] && g.blocks[0].el;
        if (/gs-nav/.test(c)) { return 'menu sidebar'; }
        if (/gs-side__scroll/.test(c)) { return 'kelompok menu sidebar'; }
        if (/genc-actions/.test(c)) { return 'tombol di judul'; }
        if (/genc-kpis/.test(c)) { return 'kartu angka'; }
        if (/genc-tabs/.test(c)) { return 'tab unit'; }
        if (/genc-chips/.test(c)) { return 'chip filter'; }
        if (/gm-choices/.test(c)) { return 'kartu pilihan'; }
        if (/gm-stats/.test(c)) { return 'angka'; }
        if (/gm-rules/.test(c)) { return 'daftar keterangan'; }
        if (/(^|\s)genc-card(\s|$)/.test(c) || /genc-kpi|genc-fold/.test(c)) { return 'isi kartu'; }
        if (b0 && /genc-fold/.test(b0.className)) { return 'kartu mesin'; }
        if (b0 && /genc-btn/.test(b0.className)) { return 'tombol'; }
        if (b0 && /genc-card|genc-notice/.test(b0.className)) { return 'kartu halaman'; }
        return 'bagian';
    }
    /** Spek grup saat ini (versi sekarang + draf) & cakupannya. */
    function curSpec(g) {
        var S = { p: effS['p|' + GX.page] || (window.GX_S && window.GX_S.p) || null, v: {} };
        var vw = viewOf(g);
        if (vw) { S.v[vw] = effS['v|' + vw] || (window.GX_S && window.GX_S.v && window.GX_S.v[vw]) || null; }
        if (effLoaded) { S.p = effS['p|' + GX.page] || null; if (vw) { S.v[vw] = effS['v|' + vw] || null; } }
        return GXS.specFor(g, S);
    }
    var effLoaded = false;
    /** Spek dari kondisi DOM sekarang. */
    function specFromDom(g) {
        var cur = Array.prototype.filter.call(g.el.children, function (c) { return keyOfBlock(g, c) !== ''; }).map(function (c) { return keyOfBlock(g, c); });
        var orig = g.blocks.map(function (b) { return b.key; });
        var sp = {};
        if (cur.join('\n') !== orig.join('\n')) { sp.o = cur; }
        var cc = {}, anyC = false;   // [GENC-16OKT26-WARNA]
        g.blocks.forEach(function (b) { var m = (b.el.className || '').match(/(?:^|\s)gx-bg--([a-z]+)/); if (m) { cc[b.key] = m[1]; anyC = true; } });
        if (anyC) { sp.c = cc; }
        var h = g.blocks.filter(function (b) { return b.el.classList.contains('gx-hidden'); }).map(function (b) { return b.key; });
        if (h.length) { sp.h = h; }
        if (g.el.classList.contains('gx-canvas')) {   // [GENC-16OKT26-BEBAS] kanvas: posisi & ukuran bebas
            var bb = {};
            g.blocks.forEach(function (b) { bb[b.key] = cvGet(b.el); });
            sp.b = bb;
            delete sp.o;
            if (anyC) { sp.c = cc; }
            sp.n = nameOf(g); if (viewOf(g)) { sp.v = viewOf(g); }
            return sp;
        }
        var w = {}, f = {}, anyW = false, anyF = false;
        g.blocks.forEach(function (b) {
            var span = parseInt(b.el.style.getPropertyValue('--gx-span'), 10);
            var col = parseInt(b.el.style.getPropertyValue('--gx-col'), 10), row = parseInt(b.el.style.getPropertyValue('--gx-row'), 10);
            if (g.el.classList.contains('gx-free') && col && row) { f[b.key] = [row, col, span || 12]; anyF = true; }
            else if (span) { w[b.key] = span; anyW = true; }
        });
        if (anyF) { sp.f = f; } else if (anyW) { sp.w = w; }
        if (!sp.o && !sp.h && !sp.w && !sp.f && !sp.c) { return null; }
        sp.n = nameOf(g); if (viewOf(g)) { sp.v = viewOf(g); }
        return sp;
    }
    function sendSus(g, scope) {
        var vw = viewOf(g);
        scope = scope || (g.scope || (vw ? 'tempat' : 'halaman'));
        if (scope === 'tempat' && !vw) { scope = 'halaman'; }
        g.scope = scope;
        var sk = scope === 'tempat' ? 'v|' + vw : 'p|' + GX.page;
        var clear = scope === 'tempat' ? ['p|' + GX.page] : [];
        var spec = specFromDom(g);
        if (!spec && scope === 'halaman' && vw) {   // "halaman ini = susunan awal" walau "tempat ini" punya susunan: simpan penanda awal
            var tv = effLoaded ? effS['v|' + vw] : (window.GX_S && window.GX_S.v && window.GX_S.v[vw]);
            if (tv && tv[g.key]) { spec = { a: 1, n: nameOf(g), v: vw }; }
        }
        return sendOps([{ op: 'susunan', sk: sk, c: g.key, spec: spec, clear: clear }]).then(function () { effLoaded = true; markSus(); })
            .catch(function (e) { toast(e.message, 'danger'); });
    }
    function markSus() {
        groups.forEach(function (g) {
            var inDraft = false;
            ['p|' + GX.page, 'v|' + viewOf(g)].forEach(function (sk) { if (draftSus[sk] && draftSus[sk].hasOwnProperty(g.key)) { inDraft = true; } });
            g.el.classList.toggle('gx-grp-draft', inDraft);
        });
    }
    markSus();

    /* -------------------------------------------------------- sorotan & toolbar */
    var hl = el('div', 'gx-hl'); hl.hidden = true; doc.body.appendChild(hl);
    var ghl = el('div', 'gx-ghl'); ghl.hidden = true; doc.body.appendChild(ghl);
    var stb = null, sel = null;
    function boxTo(node, el2, pad) {
        var r = el2.getBoundingClientRect(); pad = pad || 0;
        node.style.left = (r.left + window.pageXOffset - pad) + 'px'; node.style.top = (r.top + window.pageYOffset - pad) + 'px';
        node.style.width = (r.width + pad * 2) + 'px'; node.style.height = (r.height + pad * 2) + 'px';
    }
    doc.addEventListener('mousemove', function (e) {
        if (tool !== 'susunan' || dragging) { return; }
        if (e.target.closest && e.target.closest('.gx-bar, .gx-stb, .gx-pop, .gx-drawer, .gx-modal')) { hl.hidden = true; return; }
        var b = pickAt(e.target);
        if (!b) { hl.hidden = true; return; }
        hl.hidden = false; boxTo(hl, b.el, 2);
    });
    function closeSus() {
        if (stb) { stb.remove(); stb = null; }
        if (sel) { sel.el.classList.remove('gx-sel'); sel = null; }
        ghl.hidden = true; hideGuides(); hideHandles();
    }
    window.__gxSusClose = function () { closeSus(); hl.hidden = true; };
    function placeStb() {
        if (!stb || !sel) { return; }
        var r = sel.el.getBoundingClientRect();
        var top = r.top + window.pageYOffset - stb.offsetHeight - 8;
        if (r.top - stb.offsetHeight - 8 < 64) { top = r.bottom + window.pageYOffset + 8; }
        var left = Math.max(8, Math.min(r.left + window.pageXOffset, window.pageXOffset + doc.documentElement.clientWidth - stb.offsetWidth - 8));
        stb.style.left = left + 'px'; stb.style.top = top + 'px';
        boxTo(ghl, sel.g.el, 6); ghl.hidden = false;
        if (sel.g.el.classList.contains('gx-free')) { showGuides(sel.g); }
        if (handles) { boxTo(handles, sel.el, 0); }
    }
    function sb(icon, label, title, act, on) {
        var b = el('button', 'gx-sbtn' + (on ? ' is-on' : '')); b.type = 'button'; b.title = title || label || ''; b.setAttribute('data-gx-s', act);
        b.innerHTML = (icon ? '<i class="fa ' + icon + '" aria-hidden="true"></i>' : '') + (label ? '<span>' + label + '</span>' : '');
        return b;
    }
    function selectBlock(el2, g) {
        closeSus();
        sel = { el: el2, g: g }; el2.classList.add('gx-sel');
        var card = !el2.closest('#gs-side, .gs-side, .gs-top'), free = g.el.classList.contains('gx-free') || g.el.classList.contains('gx-canvas'), canvas = g.el.classList.contains('gx-canvas'), hidden = el2.classList.contains('gx-hidden');   // [GENC-14OKT26-SUSUNAN2] lebar & Bebas untuk semua bagian halaman (kecuali sidebar/topbar)
        var parent = chainAt(g.el)[0] || null, inner = !!groupOf(el2);
        var vw = viewOf(g), cs = curSpec(g);
        g.scope = g.scope || (cs ? cs.scope : (vw ? 'tempat' : 'halaman'));
        stb = el('div', 'gx-stb'); stb.setAttribute('role', 'toolbar'); stb.setAttribute('aria-label', 'Atur ' + nameOf(g));
        var row1 = el('div', 'gx-stb__row');
        var hd = el('span', 'gx-stb__name', nameOf(g) + ' · ' + (g.blocks.length) + ' blok'); row1.appendChild(hd);
        var drag = sb('fa-arrows', '', card && !narrow() ? 'Seret kotak ini (atau tombol ini) ke mana saja — posisi bebas. Tahan Alt = tanpa garis bantu' : 'Seret untuk memindah urutan', 'drag'); drag.classList.add('gx-sbtn--drag'); row1.appendChild(drag);
        if (canvas) {
            row1.appendChild(sb('fa-level-down', '', 'Kirim ke belakang (di bawah kotak lain)', 'zback'));
            row1.appendChild(sb('fa-level-up fa-flip-horizontal', '', 'Bawa ke depan (di atas kotak lain)', 'zfront'));
        } else {
            row1.appendChild(sb('fa-arrow-left', '', 'Pindah ke depan / atas', 'prev'));
            row1.appendChild(sb('fa-arrow-right', '', 'Pindah ke belakang / bawah', 'next'));
        }
        if (parent) { row1.appendChild(sb('fa-level-up', '', 'Pilih bagian luarnya (' + nameOf(parent.g) + ')', 'parent')); }
        if (!el2.matches('.genc-savebar, [type=submit], .gs-gxbtn')) { row1.appendChild(sb(hidden ? 'fa-eye' : 'fa-eye-slash', hidden ? 'Tampilkan' : 'Sembunyikan', hidden ? 'Tampilkan lagi' : 'Sembunyikan dari semua user', 'hide', hidden)); }
        stb.appendChild(row1);
        if (card && canvas && !narrow()) {   // [GENC-16OKT26-BEBAS] ukuran: tarik pegangan di tepi / sudut
            var rowc = el('div', 'gx-stb__row');
            var cv = cvGet(el2);
            rowc.appendChild(el('span', 'gx-stb__k', 'Ukuran'));
            rowc.appendChild(el('span', 'gx-stb__dim', Math.round(el2.getBoundingClientRect().width) + ' × ' + Math.round(el2.getBoundingClientRect().height) + ' px'));
            if (cv[3] > 0) { rowc.appendChild(sb('fa-arrows-v', 'Tinggi otomatis', 'Tinggi mengikuti isinya lagi', 'hauto')); }
            rowc.appendChild(sb('fa-arrows-h', 'Lebar penuh', 'Lebarkan sepenuh bagian ini', 'wfull'));
            stb.appendChild(rowc);
        }
        else if (card && g.blocks.length) {
            var row2 = el('div', 'gx-stb__row');
            row2.appendChild(el('span', 'gx-stb__k', 'Lebar'));
            var curSpan = parseInt(el2.style.getPropertyValue('--gx-span'), 10) || 0;
            row2.appendChild(sb('', 'Otomatis', 'Lebar bawaan', 'w0', !curSpan));
            [3, 4, 6, 8, 12].forEach(function (n) { row2.appendChild(sb('', SPAN_LBL[n], n + ' dari 12 kolom', 'w' + n, curSpan === n)); });
            stb.appendChild(row2);
        }
        if (!el2.closest('#gs-side, .gs-side, .gs-top')) {   // [GENC-16OKT26-WARNA] warna latar kotak ini
            var rowb = el('div', 'gx-stb__row');
            rowb.appendChild(el('span', 'gx-stb__k', 'Latar'));
            var curBg = ((el2.className || '').match(/(?:^|\s)gx-bg--([a-z]+)/) || [])[1] || '';
            var BGL = { '': 'Bawaan', kartu: 'Putih / kartu', lembut: 'Abu lembut', utama: 'Warna utama (lembut)', ok: 'Hijau lembut', nok: 'Merah lembut', pr: 'Oranye lembut', pending: 'Ungu lembut', gelap: 'Gelap', polos: 'Tanpa latar & garis' };
            [''].concat(XG.BG).forEach(function (k) { var b = sb('', '', BGL[k], 'bg' + (k || '0'), curBg === k); b.classList.add('gx-xdot', 'gx-bgdot', 'gx-bgdot--' + (k || 'awal')); rowb.appendChild(b); });
            stb.appendChild(rowb);
        }
        var row3 = el('div', 'gx-stb__row gx-stb__row--grp');
        if (card) { var bf = sb('fa-arrows-alt', 'Bebas', narrow() ? 'Posisi bebas hanya di layar lebar' : 'Geser ke segala arah & tarik ukuran bebas (boleh menumpuk, seperti Canva). Di HP tetap 1 kolom.', 'free', free); if (narrow()) { bf.disabled = true; } row3.appendChild(bf); }
        if (free) { row3.appendChild(sb('fa-magic', 'Rapikan', 'Rapikan otomatis: kembali ke grid tanpa celah & tanpa tumpukan', 'tidy')); }
        row3.appendChild(sb('fa-undo', 'Awal', 'Kembalikan bagian ini ke susunan awal', 'reset'));
        if (vw) {
            var scs = el('select', 'gx-stb__scope'); scs.setAttribute('aria-label', 'Berlaku di');
            [['tempat', 'Tempat ini — ' + (GX.where && GX.where[vw] || vw)], ['halaman', 'Halaman ini saja']].forEach(function (o) { var op = doc.createElement('option'); op.value = o[0]; op.textContent = o[1]; op.selected = g.scope === o[0]; scs.appendChild(op); });
            scs.addEventListener('change', function () { var old = g.scope; sendSus(g, scs.value).then(function () { if (old === 'halaman' && scs.value === 'tempat') { /* halaman ini dibuang di server lewat clear */ } }); });
            row3.appendChild(scs);
        } else { row3.appendChild(el('span', 'gx-stb__k', 'Berlaku: halaman ini')); }
        stb.appendChild(row3);
        if (inner) { stb.appendChild(el('div', 'gx-stb__hint', 'Klik lagi di dalam kotak ini untuk mengatur isinya (judul, teks, tombol, kartu kecil).')); }
        if (card && !narrow()) { stb.appendChild(el('div', 'gx-stb__hint', 'Seret kotaknya untuk memindah, tarik titik di tepi / sudut untuk mengubah ukuran. Tahan Alt = tanpa garis bantu.')); }
        doc.body.appendChild(stb);
        if (card && !narrow()) { showHandles(el2); }
        placeStb();
        stb.addEventListener('click', function (e) {
            var b = e.target.closest('[data-gx-s]'); if (!b || b.disabled) { return; }
            var a = b.getAttribute('data-gx-s');
            if (a === 'parent') { if (parent) { selectBlock(parent.el, parent.g); } return; }
            if (a === 'prev' || a === 'next') { move(sel, a === 'prev' ? -1 : 1); }
            else if (/^bg[a-z0]+$/.test(a)) {
                var k2 = a.slice(2) === '0' ? '' : a.slice(2);
                Array.prototype.slice.call(sel.el.classList).forEach(function (x) { if (x.indexOf('gx-bg--') === 0) { sel.el.classList.remove(x); } });
                if (k2) { sel.el.classList.add('gx-bg--' + k2); }
                commit();
            }
            else if (a === 'zfront' || a === 'zback') { cvZ(sel.g, sel.el, a === 'zfront' ? 1 : -1); commit(); }
            else if (a === 'hauto') { var c1 = cvGet(sel.el); c1[3] = 0; cvSet(sel.el, c1); XG.fitCanvas(sel.g); commit(); }
            else if (a === 'wfull') { if (!sel.g.el.classList.contains('gx-canvas')) { toCanvas(sel.g); } var c2 = cvGet(sel.el); c2[0] = 0; c2[2] = 100; cvSet(sel.el, c2); XG.fitCanvas(sel.g); commit(); }
            else if (a === 'hide') { sel.el.classList.toggle('gx-hidden'); commit(); }
            else if (a.charAt(0) === 'w' && a !== 'w') { setWidth(sel.g, sel.el, parseInt(a.slice(1), 10)); commit(); }
            else if (a === 'free') {
                if (sel.g.el.classList.contains('gx-canvas')) { fromCanvas(sel.g); }
                else if (sel.g.el.classList.contains('gx-free')) { toggleFree(sel.g); }   // susunan lama (grid bebas 14 Okt): keluar
                else { toCanvas(sel.g); }
                commit();
            }
            else if (a === 'tidy') { tidy(sel.g); commit(); }
            else if (a === 'reset') {
                g.blocks.forEach(function (bb) { bb.el.classList.remove('gx-hidden'); });
                XG.clearCanvas(g);
                GXS.applyOne(g, null, true);
                commit();
            }
        });
        drag.addEventListener('pointerdown', function (e) { if (card && !narrow()) { cvDrag(e, sel, 'move', true); } else { startDrag(e, sel); } });
    }
    function commit() {
        var s2 = sel;
        sendSus(s2.g).then(function () { if (s2 && s2.el.isConnected) { selectBlock(s2.el, s2.g); } });
    }
    function move(s2, dir) {
        var sibs = Array.prototype.filter.call(s2.g.el.children, function (c) { return keyOfBlock(s2.g, c) !== ''; });
        var i = sibs.indexOf(s2.el), j = i + dir;
        if (j < 0 || j >= sibs.length) { return; }
        if (dir < 0) { s2.g.el.insertBefore(s2.el, sibs[j]); } else { s2.g.el.insertBefore(s2.el, sibs[j].nextSibling); }
        if (s2.g.el.classList.contains('gx-free')) { reflowFreeFromOrder(s2.g); }
        commit();
    }
    /** Lebar: semua blok kartu di grup dibekukan ke lebar sekarang (dibulatkan ke 12 kolom), lalu 1 blok diubah. */
    function freezeSpans(g) {
        var gw = g.el.getBoundingClientRect().width || 1;
        g.blocks.forEach(function (b) {
            if (b.el.style.getPropertyValue('--gx-span')) { return; }
            var w = b.el.getBoundingClientRect().width;
            var n = Math.round(w / gw * 12), best = 12, d = 99;
            [3, 4, 6, 8, 9, 12].forEach(function (s3) { if (Math.abs(s3 - n) < d) { d = Math.abs(s3 - n); best = s3; } });
            b.el.style.setProperty('--gx-span', best);
        });
    }
    function setWidth(g, el2, n) {
        if (!n) {
            el2.style.removeProperty('--gx-span');
            var any = g.blocks.some(function (b) { return b.el.style.getPropertyValue('--gx-span'); });
            if (!any) { g.el.classList.remove('gx-grid'); }
            return;
        }
        if (!g.el.classList.contains('gx-grid')) { freezeSpans(g); g.el.classList.add('gx-grid'); }
        el2.style.setProperty('--gx-span', n);
        if (g.el.classList.contains('gx-free')) { fitRow(g, el2); }
    }

    /* -------------------------------------------------------- posisi bebas (grid 12 kolom, baris otomatis) */
    function freePos(b) { return [parseInt(b.style.getPropertyValue('--gx-row'), 10) || 0, parseInt(b.style.getPropertyValue('--gx-col'), 10) || 0, parseInt(b.style.getPropertyValue('--gx-span'), 10) || 12]; }
    function setPos(b, r, c, w) { b.style.setProperty('--gx-row', r); b.style.setProperty('--gx-col', c); b.style.setProperty('--gx-span', w); }
    function toggleFree(g) {
        if (g.el.classList.contains('gx-free')) {   // keluar mode bebas: urutan mengikuti baris/kolom, lebar tetap
            var list = g.blocks.map(function (b) { return [b.el, freePos(b.el)]; }).sort(function (x, y) { return (x[1][0] - y[1][0]) || (x[1][1] - y[1][1]); });
            list.forEach(function (x) { g.el.appendChild(x[0]); x[0].style.removeProperty('--gx-row'); x[0].style.removeProperty('--gx-col'); });
            g.el.classList.remove('gx-free');
            return;
        }
        if (!g.el.classList.contains('gx-grid')) { freezeSpans(g); g.el.classList.add('gx-grid'); }
        reflowFreeFromOrder(g);
        g.el.classList.add('gx-free');
    }
    /** Susun ulang posisi bebas mengikuti urutan DOM (isi baris kiri ke kanan, pindah baris kalau penuh). */
    function reflowFreeFromOrder(g) {
        var r = 1, c = 1;
        Array.prototype.forEach.call(g.el.children, function (el2) {
            if (!keyOfBlock(g, el2)) { return; }
            var w = parseInt(el2.style.getPropertyValue('--gx-span'), 10) || 12;
            if (c + w - 1 > 12) { r++; c = 1; }
            setPos(el2, r, c, w); c += w;
            if (c > 12) { r++; c = 1; }
        });
    }
    function rowsOf(g) {
        var rows = {};
        g.blocks.forEach(function (b) { var p = freePos(b.el); (rows[p[0]] = rows[p[0]] || []).push([b.el, p]); });
        return rows;
    }
    function overlaps(a, b) { return a[1] < b[1] + b[2] && b[1] < a[1] + a[2]; }
    /** Blok el2 sudah dipindah ke (r, c, w): blok lain yang bertabrakan di baris itu turun 1 baris (berantai). */
    // [GENC-16OKT26-AUDIT] dulu bernama resolve() -> menimpa resolve(it) teks (deklarasi fungsi kedua menang), sehingga
    // refreshHash() sesudah ubah teks selalu gagal diam-diam (tanda draf & laci Teks lain tidak diperbarui)
    function resolveFree(g, el2) {
        var guard = 0, moved = true;
        while (moved && guard++ < 200) {
            moved = false;
            var all = g.blocks.map(function (b) { return [b.el, freePos(b.el)]; });
            for (var i = 0; i < all.length && !moved; i++) {
                for (var j = 0; j < all.length && !moved; j++) {
                    if (i === j || all[i][1][0] !== all[j][1][0] || !overlaps(all[i][1], all[j][1])) { continue; }
                    var victim = all[i][0] === el2 ? all[j] : (all[j][0] === el2 ? all[i] : (all[i][1][1] > all[j][1][1] ? all[i] : all[j]));
                    setPos(victim[0], victim[1][0] + 1, victim[1][1], victim[1][2]); moved = true;
                }
            }
        }
        compactRows(g);
    }
    function compactRows(g) {   // nomor baris tanpa lompatan (baris kosong dibuang)
        var used = {};
        g.blocks.forEach(function (b) { used[freePos(b.el)[0]] = true; });
        var map = {}, n = 0;
        Object.keys(used).map(Number).sort(function (a, b) { return a - b; }).forEach(function (r) { map[r] = ++n; });
        g.blocks.forEach(function (b) { var p = freePos(b.el); setPos(b.el, map[p[0]], p[1], p[2]); });
    }
    function fitRow(g, el2) {
        var p = freePos(el2);
        if (p[1] + p[2] - 1 > 12) { setPos(el2, p[0], Math.max(1, 13 - p[2]), p[2]); }
        resolveFree(g, el2);
    }
    function tidy(g) {
        if (g.el.classList.contains('gx-canvas')) { fromCanvas(g); return; }   // [GENC-16OKT26-BEBAS]
        var rows = rowsOf(g);
        Object.keys(rows).forEach(function (r) { var c = 1; rows[r].sort(function (a, b) { return a[1][1] - b[1][1]; }).forEach(function (x) { if (c + x[1][2] - 1 > 12) { c = 1; } setPos(x[0], +r, c, x[1][2]); c += x[1][2]; }); });
        resolveFree(g, null);
    }
    var guides = null;
    function showGuides(g) {
        hideGuides();
        guides = el('div', 'gx-guides'); boxTo(guides, g.el, 0);
        for (var i = 0; i < 12; i++) { guides.appendChild(el('span')); }
        doc.body.appendChild(guides);
    }
    function hideGuides() { if (guides) { guides.remove(); guides = null; } }

    /* -------------------------------------------------------- KANVAS BEBAS ala Canva   [GENC-16OKT26-BEBAS]
       Posisi tiap blok = [x % lebar wadah, y px dari atas wadah, lebar %, tinggi px (0 = otomatis), tumpukan].
       Seret kotak = pindah ke segala arah; 8 pegangan = ubah ukuran (lebar & tinggi). Garis bantu muncul saat
       tepi / tengah sejajar dengan kotak lain atau wadah (menempel 6 px) — Alt = tanpa menempel. Boleh menumpuk.
       Rapikan = kembali ke grid 12 kolom (urut atas -> bawah, kiri -> kanan). HP: tetap 1 kolom. */
    var XG = window.GXS;
    function cvGet(b) {
        return [Math.round((parseFloat(b.style.left) || 0) * 100) / 100, Math.round(parseFloat(b.style.top) || 0),
            Math.round((parseFloat(b.style.width) || 100) * 100) / 100, Math.round(parseFloat(b.style.height) || 0), Math.max(0, (parseInt(b.style.zIndex, 10) || 1) - 1)];
    }
    function cvSet(b, p) {
        b.classList.add('gx-cv');
        b.style.left = p[0] + '%'; b.style.top = p[1] + 'px'; b.style.width = p[2] + '%';
        if (p[3] > 0) { b.style.height = p[3] + 'px'; b.classList.add('gx-hset'); } else { b.style.removeProperty('height'); b.classList.remove('gx-hset'); }
        b.style.zIndex = String(1 + (p[4] || 0));
    }
    /** Grup -> kanvas, posisi & ukuran persis seperti yang terlihat sekarang (tidak ada yang melompat). */
    function toCanvas(g) {
        if (g.el.classList.contains('gx-canvas')) { return; }
        var gr = g.el.getBoundingClientRect(), gw = gr.width || 1, b = {};
        g.blocks.forEach(function (bl) {
            var r = bl.el.getBoundingClientRect();
            var keepH = GXS.cardlike(bl.el) || /genc-panel/.test(bl.el.className);   // kartu: tinggi yang terlihat dipertahankan (grid sering meregangkan kartu)
            b[bl.key] = [Math.max(0, Math.round((r.left - gr.left) / gw * 10000) / 100), Math.max(0, Math.round(r.top - gr.top)),
                Math.min(100, Math.round(r.width / gw * 10000) / 100), keepH && r.height ? Math.round(r.height) : 0, 0];
        });
        g.blocks.forEach(function (bl) { bl.el.style.removeProperty('--gx-span'); bl.el.style.removeProperty('--gx-col'); bl.el.style.removeProperty('--gx-row'); });
        g.el.classList.remove('gx-grid', 'gx-free');
        XG.applyCanvas(g, b);
    }
    /** Kanvas -> grid rapi: lebar dibulatkan ke 12 kolom, urutan atas -> bawah, kiri -> kanan. */
    function fromCanvas(g) {
        var gw = g.el.getBoundingClientRect().width || 1;
        var list = g.blocks.map(function (bl) { var p = cvGet(bl.el); return [bl.el, p]; })
            .sort(function (x, y) { return (Math.round(x[1][1] / 24) - Math.round(y[1][1] / 24)) || (x[1][0] - y[1][0]); });
        XG.clearCanvas(g);
        list.forEach(function (x) {
            g.el.appendChild(x[0]);
            var n = Math.round(x[1][2] / 100 * 12), best = 12, d = 99;
            GXS.SPANS.forEach(function (s3) { if (Math.abs(s3 - n) < d) { d = Math.abs(s3 - n); best = s3; } });
            x[0].style.setProperty('--gx-span', best);
        });
        g.el.classList.add('gx-grid');
    }
    function cvZ(g, el2, dir) {
        if (!g.el.classList.contains('gx-canvas')) { return; }
        var zs = g.blocks.map(function (b) { return cvGet(b.el)[4]; });
        var p = cvGet(el2);
        p[4] = dir > 0 ? Math.min(999, Math.max.apply(null, zs) + 1) : 0;
        if (dir < 0) { g.blocks.forEach(function (b) { if (b.el !== el2) { var q = cvGet(b.el); q[4] = Math.min(999, q[4] + 1); cvSet(b.el, q); } }); }
        cvSet(el2, p);
    }
    // pegangan 8 arah di sekeliling blok terpilih
    var handles = null;
    function showHandles(el2) {
        hideHandles();
        handles = el('div', 'gx-handles');
        ['n', 'ne', 'e', 'se', 's', 'sw', 'w', 'nw'].forEach(function (d) { var h = el('span', 'gx-handle gx-handle--' + d); h.setAttribute('data-gx-hd', d); h.title = 'Tarik untuk mengubah ukuran'; handles.appendChild(h); });
        doc.body.appendChild(handles);
        boxTo(handles, el2, 0);
    }
    function hideHandles() { if (handles) { handles.remove(); handles = null; } }
    // garis bantu sejajar
    var snapLines = [];
    function clearSnap() { snapLines.forEach(function (l) { l.remove(); }); snapLines = []; }
    function snapLine(vertical, pos, from, to) {
        var l = el('div', 'gx-snap gx-snap--' + (vertical ? 'v' : 'h'));
        if (vertical) { l.style.left = pos + 'px'; l.style.top = from + 'px'; l.style.height = Math.max(1, to - from) + 'px'; }
        else { l.style.top = pos + 'px'; l.style.left = from + 'px'; l.style.width = Math.max(1, to - from) + 'px'; }
        doc.body.appendChild(l); snapLines.push(l);
    }
    /** Titik tempel (koordinat halaman) dari wadah & blok lain di grup yang sama. */
    function snapTargets(g, el2) {
        var gr = g.el.getBoundingClientRect(), sx = window.pageXOffset, sy = window.pageYOffset;
        var xs = [gr.left + sx, gr.left + gr.width / 2 + sx, gr.right + sx], ys = [gr.top + sy];
        g.blocks.forEach(function (b) {
            if (b.el === el2 || b.el.classList.contains('gx-hidden')) { return; }
            var r = b.el.getBoundingClientRect(); if (!r.width) { return; }
            xs.push(r.left + sx, r.left + r.width / 2 + sx, r.right + sx); ys.push(r.top + sy, r.top + r.height / 2 + sy, r.bottom + sy);
        });
        return { xs: xs, ys: ys };
    }
    function nearest(vals, list) {   // [selisih terkecil, nilai target]
        var best = null;
        vals.forEach(function (v, i) { list.forEach(function (t) { var d = t - v; if (best === null || Math.abs(d) < Math.abs(best[0])) { best = [d, t, i]; } }); });
        return best;
    }
    var cvJustDragged = false;
    /**
     * Seret / ubah ukuran di kanvas. mode 'move' | arah pegangan ('n','se',...). force = mulai langsung (tombol seret),
     * selain itu baru dianggap seret setelah bergerak 4 px (klik biasa tetap = pilih / masuk ke isi).
     */
    function cvDrag(e, s2, mode, force) {
        if (!s2 || narrow()) { return; }
        var g = s2.g, el2 = s2.el, sx0 = e.clientX, sy0 = e.clientY, started = false, st = null;
        if (force) { e.preventDefault(); }
        function begin() {
            started = true;
            toCanvas(g);
            if (mode === 'move') { cvZ(g, el2, 1); }   // yang diseret naik ke depan (tidak tertutup kotak lain)
            var gr = g.el.getBoundingClientRect();
            st = { gw: gr.width || 1, p: cvGet(el2), h0: el2.getBoundingClientRect().height, T: snapTargets(g, el2) };
            dragging = { g: g, el: el2, free: true };
            el2.classList.add('gx-dragging'); doc.body.classList.add(mode === 'move' ? 'gx-is-dragging' : 'gx-is-resizing');
            if (stb) { stb.style.visibility = 'hidden'; }
        }
        function onMove(ev) {
            if (!started) { if (Math.abs(ev.clientX - sx0) + Math.abs(ev.clientY - sy0) < 4) { return; } begin(); }
            ev.preventDefault();
            var dxp = ev.clientX - sx0, dy = ev.clientY - sy0, gw = st.gw, p0 = st.p, p = p0.slice();
            var x = p0[0] / 100 * gw, w = p0[2] / 100 * gw, y = p0[1], h = p0[3] > 0 ? p0[3] : st.h0;
            if (mode === 'move') { x += dxp; y += dy; }
            else {
                if (mode.indexOf('e') !== -1) { w += dxp; }
                if (mode.indexOf('w') !== -1) { x += dxp; w -= dxp; }
                if (mode.indexOf('s') !== -1) { h += dy; }
                if (mode.indexOf('n') !== -1) { y += dy; h -= dy; }
            }
            // garis bantu (menempel 6 px) — Alt: bebas tanpa menempel
            clearSnap();
            if (!ev.altKey) {
                var gr = g.el.getBoundingClientRect(), ox = gr.left + window.pageXOffset, oy = gr.top + window.pageYOffset, T = st.T;
                var cand = mode === 'move' ? [x, x + w / 2, x + w] : [mode.indexOf('w') !== -1 ? x : null, mode.indexOf('e') !== -1 ? x + w : null].filter(function (v) { return v !== null; });
                var nx = cand.length ? nearest(cand.map(function (v) { return v + ox; }), T.xs) : null;
                if (nx && Math.abs(nx[0]) <= 6) {
                    if (mode === 'move') { x += nx[0]; } else if (mode.indexOf('w') !== -1 && nx[2] === 0) { x += nx[0]; w -= nx[0]; } else { w += nx[0]; }
                    snapLine(true, nx[1], oy - 8, oy + Math.max(g.el.offsetHeight, y + h) + 8);
                }
                var hy = mode === 'move' ? [y, y + h / 2, y + h] : [mode.indexOf('n') !== -1 ? y : null, mode.indexOf('s') !== -1 ? y + h : null].filter(function (v) { return v !== null; });
                var ny = hy.length ? nearest(hy.map(function (v) { return v + oy; }), T.ys) : null;
                if (ny && Math.abs(ny[0]) <= 6) {
                    if (mode === 'move') { y += ny[0]; } else if (mode.indexOf('n') !== -1 && ny[2] === 0) { y += ny[0]; h -= ny[0]; } else { h += ny[0]; }
                    snapLine(false, ny[1], ox - 8, ox + gw + 8);
                }
            }
            w = Math.max(gw * 0.03, Math.min(w, gw)); h = Math.max(32, h);
            x = Math.max(0, Math.min(x, gw - w)); y = Math.max(0, y);
            p[0] = Math.round(x / gw * 10000) / 100; p[2] = Math.round(w / gw * 10000) / 100; p[1] = Math.round(y);
            if (mode !== 'move' && (mode.indexOf('n') !== -1 || mode.indexOf('s') !== -1)) { p[3] = Math.round(h); }
            cvSet(el2, p);
            XG.fitCanvas(g);
            boxTo(ghl, g.el, 6); if (handles) { boxTo(handles, el2, 0); }
            hl.hidden = true;
        }
        function onUp() {
            doc.removeEventListener('pointermove', onMove, true); doc.removeEventListener('pointerup', onUp, true);
            clearSnap();
            if (!started) { return; }
            el2.classList.remove('gx-dragging'); doc.body.classList.remove('gx-is-dragging', 'gx-is-resizing');
            dragging = null; cvJustDragged = true; setTimeout(function () { cvJustDragged = false; }, 50);
            if (stb) { stb.style.visibility = ''; }
            XG.fitCanvas(g);
            commit();
        }
        doc.addEventListener('pointermove', onMove, true); doc.addEventListener('pointerup', onUp, true);
    }
    // seret kotak terpilih (badan kotak) atau pegangan ukurannya
    doc.addEventListener('pointerdown', function (e) {
        if (tool !== 'susunan' || !sel || e.button !== 0 || narrow()) { return; }
        var hd = e.target.closest && e.target.closest('[data-gx-hd]');
        if (hd) { e.preventDefault(); e.stopPropagation(); cvDrag(e, sel, hd.getAttribute('data-gx-hd'), true); return; }
        if (e.target.closest && e.target.closest('.gx-stb, .gx-bar, .gx-pop, .gx-drawer, .gx-modal, .gx-xbox')) { return; }
        if (sel.el.closest('#gs-side, .gs-side, .gs-top')) { return; }
        if (!sel.el.contains(e.target)) { return; }
        cvDrag(e, sel, 'move', false);
    }, true);

    /* -------------------------------------------------------- seret (urutan biasa / posisi bebas) */
    var dragging = null;
    function startDrag(e, s2) {
        if (!s2) { return; }
        e.preventDefault();
        var g = s2.g, el2 = s2.el, free = g.el.classList.contains('gx-free') && !narrow();
        dragging = { g: g, el: el2, free: free };
        el2.classList.add('gx-dragging'); doc.body.classList.add('gx-is-dragging');
        if (free) { showGuides(g); }
        var cs = window.getComputedStyle(g.el);
        var rowLike = free || /flex/.test(cs.display) && !/column/.test(cs.flexDirection) || /grid/.test(cs.display) && cs.gridTemplateColumns.split(' ').length > 1;
        function onMove(ev) {
            if (free) {
                var r = g.el.getBoundingClientRect(), colW = r.width / 12;
                var p = freePos(el2);
                var c = Math.max(1, Math.min(13 - p[2], Math.floor((ev.clientX - r.left) / colW) + 1 - Math.floor(p[2] / 2)));
                // baris: baris blok lain yang dilewati kursor; di atas semua = baris 1 baru; di bawah semua = baris baru terakhir
                var rows = rowsOf(g), target = null, maxR = 0;
                Object.keys(rows).forEach(function (rk) {
                    rk = +rk; if (rk > maxR) { maxR = rk; }
                    var top = 1e9, bot = -1e9;
                    rows[rk].forEach(function (x) { if (x[0] === el2) { return; } var b = x[0].getBoundingClientRect(); top = Math.min(top, b.top); bot = Math.max(bot, b.bottom); });
                    if (top < 1e9 && ev.clientY >= top && ev.clientY <= bot) { target = rk; }
                });
                if (target === null) {
                    var first = g.el.firstElementChild ? g.el.getBoundingClientRect().top : 0;
                    target = ev.clientY < first + 8 ? 0 : maxR + 1;
                    if (target === 0) { g.blocks.forEach(function (b) { if (b.el !== el2) { var q = freePos(b.el); setPos(b.el, q[0] + 1, q[1], q[2]); } }); target = 1; }
                }
                setPos(el2, target, c, p[2]);
                boxTo(guides, g.el, 0); boxTo(ghl, g.el, 6);
                return;
            }
            var over = doc.elementFromPoint(ev.clientX, ev.clientY), b2 = null;
            if (over) { chainAt(over).forEach(function (x) { if (!b2 && x.g === g) { b2 = x; } }); }
            if (!b2 || b2.g !== g || b2.el === el2) { return; }
            var rr = b2.el.getBoundingClientRect();
            var after = rowLike ? ev.clientX > rr.left + rr.width / 2 : ev.clientY > rr.top + rr.height / 2;
            g.el.insertBefore(el2, after ? b2.el.nextSibling : b2.el);
            boxTo(ghl, g.el, 6);
        }
        function onUp() {
            doc.removeEventListener('pointermove', onMove); doc.removeEventListener('pointerup', onUp);
            el2.classList.remove('gx-dragging'); doc.body.classList.remove('gx-is-dragging');
            if (free) { resolveFree(g, el2); }
            dragging = null;
            commit();
        }
        doc.addEventListener('pointermove', onMove); doc.addEventListener('pointerup', onUp);
    }
    /** Ubah lebar dengan menyeret tepi kanan (posisi bebas). */
    doc.addEventListener('pointerdown', function (e) {
        if (tool !== 'susunan' || !sel || !sel.g.el.classList.contains('gx-free') || narrow()) { return; }
        var r = sel.el.getBoundingClientRect();
        if (e.target.closest('.gx-stb') || Math.abs(e.clientX - r.right) > 10 || e.clientY < r.top || e.clientY > r.bottom) { return; }
        e.preventDefault(); e.stopPropagation();
        var g = sel.g, el2 = sel.el; showGuides(g); doc.body.classList.add('gx-is-resizing');
        function mv(ev) {
            var gr = g.el.getBoundingClientRect(), colW = gr.width / 12, p = freePos(el2);
            var endCol = Math.max(p[1], Math.min(12, Math.round((ev.clientX - gr.left) / colW)));
            setPos(el2, p[0], p[1], Math.max(1, endCol - p[1] + 1));
        }
        function up() { doc.removeEventListener('pointermove', mv); doc.removeEventListener('pointerup', up); doc.body.classList.remove('gx-is-resizing'); resolveFree(g, el2); commit(); }
        doc.addEventListener('pointermove', mv); doc.addEventListener('pointerup', up);
    }, true);

    /* -------------------------------------------------------- klik di mode Susunan */
    doc.addEventListener('click', function (e) {
        if (tool !== 'susunan') { return; }
        if (cvJustDragged) { e.preventDefault(); e.stopPropagation(); if (e.stopImmediatePropagation) { e.stopImmediatePropagation(); } return; }   // [GENC-16OKT26-BEBAS] akhir seret bukan klik
        if (e.target.closest && e.target.closest('.gx-bar, .gx-stb, .gx-pop, .gx-drawer, .gx-modal, .gx-xbox, .gx-handles')) { return; }
        var b = pickAt(e.target);
        e.preventDefault(); e.stopPropagation(); if (e.stopImmediatePropagation) { e.stopImmediatePropagation(); }
        if (!b) { closeSus(); return; }
        selectBlock(b.el, b.g);
    }, true);
    doc.addEventListener('submit', function (e) { if (tool === 'susunan') { e.preventDefault(); } }, true);
    window.addEventListener('resize', function () { if (sel) { placeStb(); } });
    window.addEventListener('scroll', function () { if (sel && guides) { boxTo(guides, sel.g.el, 0); } }, { passive: true });
    doc.addEventListener('keydown', function (e) {
        if (tool !== 'susunan' || !sel || (e.target && /input|textarea|select/i.test(e.target.tagName))) { return; }
        if (e.key === 'Escape' || e.key === 'Esc') { closeSus(); return; }
        if (sel.g.el.classList.contains('gx-canvas') && /^Arrow/.test(e.key)) {   // [GENC-16OKT26-BEBAS] panah = geser halus (Shift = 10 px)
            e.preventDefault();
            var st2 = e.shiftKey ? 10 : 1, gw2 = sel.g.el.getBoundingClientRect().width || 1, q = cvGet(sel.el);
            if (e.key === 'ArrowLeft') { q[0] = Math.max(0, q[0] - st2 / gw2 * 100); } else if (e.key === 'ArrowRight') { q[0] = Math.min(100 - q[2], q[0] + st2 / gw2 * 100); }
            else if (e.key === 'ArrowUp') { q[1] = Math.max(0, q[1] - st2); } else { q[1] += st2; }
            q[0] = Math.round(q[0] * 100) / 100;
            cvSet(sel.el, q); XG.fitCanvas(sel.g); placeStb();
            clearTimeout(cvNudgeT); cvNudgeT = setTimeout(commit, 400);
            return;
        }
        if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { e.preventDefault(); move(sel, -1); }
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { e.preventDefault(); move(sel, 1); }
    });
    var cvNudgeT = null;
    window.__gxSusPairs = function () {   // untuk "Halaman awal": semua grup bersusunan di halaman ini
        var out = [];
        groups.forEach(function (g) {
            var vw = viewOf(g), S = { p: effS['p|' + GX.page] || (window.GX_S && window.GX_S.p) || null, v: {} };
            if (S.p && S.p[g.key]) { out.push(['p|' + GX.page, g.key]); }
            var sv = effS['v|' + vw] || (window.GX_S && window.GX_S.v && window.GX_S.v[vw]);
            if (vw && sv && sv[g.key]) { out.push(['v|' + vw, g.key]); }
        });
        return out;
    };

    /* -------------------------------------------------------- Simpan (+ jalan pintas data lewat form asli) */
    function serialize(form, docx) {
        var data = new FormData(), id = form.getAttribute('id');
        var ctrls = $all('input, select, textarea', form);
        if (id) { ctrls = ctrls.concat($all('[form="' + id + '"]', docx)); }
        ctrls.forEach(function (c) {
            if (!c.name || c.disabled) { return; }
            if ((c.type === 'checkbox' || c.type === 'radio') && !c.checked) { return; }
            if (c.type === 'file' || c.type === 'submit' || c.type === 'button') { return; }
            if (c.tagName.toLowerCase() === 'select' && c.multiple) { $all('option', c).forEach(function (o) { if (o.selected) { data.append(c.name, o.value); } }); return; }
            data.append(c.name, c.value);
        });
        return data;
    }
    function errorOf(d) {
        var bad = d.querySelector('.genc-toast--danger span, .genc-notice--danger, .genc-field.has-error .genc-field__err, .gm-err');
        return bad ? norm(bad.textContent) : '';
    }
    function runData(d) {
        var page = d.kind === 'pilihan' ? GX.url.pilihan : GX.url.mesin + '?pilih=' + encodeURIComponent(d.sel);
        var what = d.kind === 'pilihan' ? 'Label hasil cek (' + d.id + ')' : (d.field === 'label' ? 'Nama unit' : (d.field === 'area' ? 'Area mesin' : (d.field === 'pelaksanaan' ? 'Pelaksanaan' : 'Nama mesin')));
        var res = { key: d.kind + '|' + d.id + '|' + d.field, what: what, old: d.old, 'new': d.new, ok: false, msg: '' };
        return fetch(page, { credentials: 'same-origin' }).then(function (r) { return r.text(); }).then(function (h) {
            var dx = new DOMParser().parseFromString(h, 'text/html');
            var form = null, input = null;
            if (d.kind === 'mesin') { form = dx.getElementById('gm-folder-form'); input = form && form.querySelector('[name="' + d.field + '"]'); }
            else if (d.kind === 'unit') { input = dx.getElementById('x-label'); form = input && input.closest('form'); }
            else { form = dx.querySelector('[data-ms-pil]'); input = form && form.querySelector('[name="label[' + d.id + ']"]'); }
            if (!form || !input) { res.msg = 'Form-nya tidak ditemukan (dihapus / diarsipkan / tidak ada hak).'; return res; }
            if (input.readOnly || input.disabled) { res.msg = 'Sedang dikunci / hanya-baca di menunya.'; return res; }
            var fd = serialize(form, dx);
            fd.set(input.name, d.new);
            return fetch(form.getAttribute('action'), { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.text(); }).then(function (h2) {
                var d2 = new DOMParser().parseFromString(h2, 'text/html');
                var e = errorOf(d2);
                if (e) { res.msg = e; return res; }
                res.ok = true; return res;
            });
        }).catch(function (e) { res.msg = String(e && e.message || e); return res; });
    }
    bar.querySelector('[data-gx-act=simpan]').addEventListener('click', function () {
        var nT = Object.keys(draftTeks).length, dataList = Object.keys(draftData).map(function (k) { return draftData[k]; });
        var box = el('div');
        var ul = el('ul', 'gx-modal__list');
        Object.keys(draftTeks).slice(0, 60).forEach(function (k) {
            var v = draftTeks[k]; var li = el('li', '', v.t == null ? v.d + ' → (teks awal)' : (v.t === '' ? '"' + v.d + '" → (dihapus)' : '"' + v.d + '" → "' + v.t + '"')); li.appendChild(el('small', '', v.w || '')); ul.appendChild(li);
        });
        dataList.forEach(function (d) { var li = el('li', '', '"' + d.old + '" → "' + d.new + '"'); li.appendChild(el('small', '', (d.kind === 'pilihan' ? 'Master Select' : 'Mesin & Unit') + ' (data)')); ul.appendChild(li); });
        var nS = 0;
        Object.keys(draftSus).forEach(function (sk) { Object.keys(draftSus[sk] || {}).forEach(function (ck) {
            var v = draftSus[sk][ck]; nS++;
            var li = el('li', '', 'Susunan ' + ((v && v.n) || 'bagian') + (v ? ' diubah' : ' kembali ke awal'));
            li.appendChild(el('small', '', sk.indexOf('p|') === 0 ? (GX.pageName || GX.page) : ((GX.where && GX.where[sk.slice(2)]) || sk.slice(2)))); ul.appendChild(li);
        }); });
        var nM = 0;
        Object.keys(temaDraft).forEach(function (k) {
            nM++; var v = temaDraft[k], li = el('li', '', temaDesc(k, v));
            var hex = v && (/^#/.test(v) ? v : (k === 'aksen' && TM.aksen && TM.aksen[v] ? TM.aksen[v][1] : ''));
            if (hex) { var dot = el('span', 'gx-tm__chip'); dot.style.background = hex; li.insertBefore(dot, li.firstChild); }
            li.appendChild(el('small', '', 'Tema · semua halaman')); ul.appendChild(li);
        });
        box.appendChild(ul);
        var ta = doc.createElement('textarea'); ta.placeholder = 'Catatan (opsional), mis. "Permintaan SPV: tombol Unit baru di kanan"'; ta.maxLength = 200;
        box.appendChild(ta);
        var nK = 0, dk = window.__gxKotakDraft ? window.__gxKotakDraft() : {};
        Object.keys(dk).forEach(function (sk) { Object.keys(dk[sk] || {}).forEach(function (id) {
            nK++; var v = dk[sk][id], li = el('li', '', v ? 'Teks tambahan "' + String(v.t).slice(0, 60) + '"' : 'Teks tambahan dihapus');
            li.appendChild(el('small', '', sk === 'g' ? 'Semua halaman' : (GX.pageName || sk.slice(2)))); ul.appendChild(li);
        }); });
        var nI = 0, di = window.__gxIkonDraft ? window.__gxIkonDraft() : {};   // [GENC-16OKT26-IKON]
        Object.keys(di).forEach(function (sk) { Object.keys(di[sk] || {}).forEach(function (k2) {
            nI++; var v = di[sk][k2], from = (v && v.d) || (sk === 'g' ? k2 : 'ikon'), li = el('li', '', 'Ikon ' + String(from).replace(/^fa-/, '').replace(/-/g, ' ') + (v === null ? ' kembali ke awal' : (v.i === '' ? ' dihapus' : (v.i ? ' → ' + v.i.replace(/^fa-/, '').replace(/-/g, ' ') : ' diatur'))));
            li.appendChild(el('small', '', sk === 'g' ? 'Semua halaman' : (GX.pageName || sk.slice(2)))); ul.appendChild(li);
        }); });
        var parts = []; if (nT) { parts.push(nT + ' perubahan teks'); } if (nK) { parts.push(nK + ' teks tambahan'); } if (nI) { parts.push(nI + ' ikon'); } if (nS) { parts.push(nS + ' perubahan susunan'); } if (nM) { parts.push(nM + ' perubahan tema'); }
        modal('Simpan tampilan?', (parts.length ? parts.join(' + ') + ' jadi versi baru (v' + (GX.cur + 1) + ' atau berikutnya) dan langsung berlaku untuk semua user.' : '') +
            (dataList.length ? ' ' + dataList.length + ' perubahan data dikirim lewat menunya masing-masing.' : ''), 'Simpan', false, box).then(function (ok) {
            if (!ok) { return; }
            var wait = el('div', 'gx-modal'); wait.innerHTML = '<div class="gx-modal__box"><h3><i class="fa fa-spinner fa-spin"></i> Menyimpan…</h3><p>Jangan tutup halaman ini.</p></div>';
            doc.body.appendChild(wait);
            var results = [];
            var chain = Promise.resolve();
            dataList.forEach(function (d) { chain = chain.then(function () { return runData(d).then(function (r) { results.push(r); }); }); });
            chain.then(function () {
                return post('tampilan/edit/simpan', { ajax: '1', catatan: ta.value, data_hasil: JSON.stringify(results), kembali: here });
            }).then(function (j) {
                var bad = results.filter(function (r) { return !r.ok; });
                if (bad.length) {
                    wait.remove();
                    var ul2 = el('ul', 'gx-modal__list');
                    results.forEach(function (r) { var li = el('li', r.ok ? 'is-ok' : 'is-bad', r.what + ': "' + r.old + '" → "' + r.new + '"'); if (!r.ok) { li.appendChild(el('small', '', r.msg)); } ul2.appendChild(li); });
                    return modal('Sebagian perubahan data gagal', 'Yang gagal tetap di draf. Perubahan lain sudah disimpan.', 'Mengerti', false, ul2).then(function () { location.href = (j && j.back) || location.href; });
                }
                location.href = (j && j.back) || location.href;
            }).catch(function (e) { wait.remove(); toast('Gagal menyimpan: ' + (e && e.message || e), 'danger'); });
        });
    });
})();
