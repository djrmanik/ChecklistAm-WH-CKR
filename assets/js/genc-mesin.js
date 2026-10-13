/* ============================================================================
   MESIN & UNIT - perilaku halaman explorer               [GENC-06OKT26-MESIN]
   Vanilla JS, tanpa library. Kontrak data-*:
     [data-gm-filter]     kotak cari pohon (saring folder & unit)
     [data-gm-collapse]   tutup semua folder
     [data-gm-editor]     form item jenis mesin: tambah / naik / turun / hapus baris,
                          nomor urut, jumlah item, pratinjau & kecilkan foto, peringatan belum disimpan
     [data-gm-fotos]      [GENC-07OKT26-FOTO] banyak foto per item: tambah (boleh beberapa sekaligus),
                          hapus, jadikan foto utama (klik), maks. 6
     [data-gm-gsec]       gambar bagian mesin per bagian: Otomatis (kolase foto item, bernomor, ikut
                          urutan / hapus / tambah item secara langsung) atau Gambar sendiri; sembunyikan
                          foto dari report, foto tambahan untuk item tertentu; bagian tanpa item disembunyikan
     [data-gm-autounit]   nama jenis -> isi otomatis "unit pertama" (kalau belum diubah)
     [data-gm-iconbox]    [GENC-08OKT26-MESIN] pemilih ikon: cari (nama / kata kunci), pratinjau ikon terpilih
   Halaman tetap berfungsi tanpa JS (form biasa), kecuali tombol tambah baris item.
   ========================================================================= */
(function () {
    'use strict';
    function $all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
    function closest(el, sel) {
        while (el && el.nodeType === 1) { if (el.matches ? el.matches(sel) : el.msMatchesSelector(sel)) { return el; } el = el.parentNode; }
        return null;
    }

    /* ------------------------------------------------------------ pemilih ikon  [GENC-08OKT26-MESIN] */
    $all('[data-gm-iconbox]').forEach(function (box) {
        var find = box.querySelector('[data-gm-iconfind]');
        var cur = box.querySelector('[data-gm-iconcur]');
        var cnt = box.querySelector('[data-gm-iconn]');
        var none = box.querySelector('[data-gm-iconnone]');
        var picks = $all('[data-gm-iconwords]', box);
        function run() {
            var words = find.value.toLowerCase().replace(/^\s+|\s+$/g, '').split(/\s+/);
            var n = 0;
            picks.forEach(function (lb) {
                var w = lb.getAttribute('data-gm-iconwords');
                var hit = words.every(function (q) { return q === '' || w.indexOf(q) !== -1; });
                lb.hidden = !hit && !lb.querySelector('input').checked;   /* ikon terpilih tetap terlihat */
                if (hit) { n++; }
            });
            if (cnt) { cnt.textContent = n + ' ikon'; }
            if (none) { none.hidden = n > 0; }
        }
        if (find) {
            find.addEventListener('input', run);
            find.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); } });
        }
        box.addEventListener('change', function (e) {
            if (e.target.type === 'radio' && cur) { cur.className = 'fa ' + e.target.value; }
        });
    });

    /* ------------------------------------------------------------ hak akses awal: salin dari mesin lain  [GENC-08OKT26-P3] */
    $all('[data-gm-permcopy]').forEach(function (sel) {
        sel.addEventListener('change', function () {
            if (sel.value === '') { return; }
            var map = {};
            try { map = JSON.parse(sel.value) || {}; } catch (e) { map = {}; }
            $all('[data-gm-perm]').forEach(function (cb) {
                var m = /perm\[(\d+)\]/.exec(cb.name);
                var acts = m && map[m[1]] ? map[m[1]] : [];
                cb.checked = acts.indexOf(cb.getAttribute('data-gm-perm')) !== -1;
            });
            $all('[data-gm-permrow]').forEach(function (row) {   /* aksi tanpa Lihat -> Lihat ikut */
                var any = $all('[data-gm-perm]:checked', row).length > 0, l = row.querySelector('[data-gm-perm="list"]');
                if (any && l) { l.checked = true; }
            });
            sel.value = '';
        });
    });

    /* ------------------------------------------------------------ hak akses awal per jabatan  [GENC-08OKT26-AKSES] */
    document.addEventListener('change', function (e) {
        var cb = e.target;
        if (!cb.hasAttribute || !cb.hasAttribute('data-gm-perm')) { return; }
        var row = closest(cb, '[data-gm-permrow]');
        var act = cb.getAttribute('data-gm-perm');
        if (act === 'list' && !cb.checked) { $all('[data-gm-perm]', row).forEach(function (x) { x.checked = false; }); }
        if (act !== 'list' && cb.checked) { var l = row.querySelector('[data-gm-perm="list"]'); if (l) { l.checked = true; } }
    });

    /* ------------------------------------------------------------ pohon */
    var filter = document.querySelector('[data-gm-filter]');
    if (filter) {
        filter.addEventListener('input', function () {
            var q = filter.value.toLowerCase().replace(/^\s+|\s+$/g, '');
            var any = false;
            $all('[data-gm-folder]').forEach(function (f) {
                var head = f.querySelector('summary');
                var fhit = q === '' || head.getAttribute('data-gm-name').indexOf(q) !== -1;
                var shown = 0;
                $all('.gm-files > li', f).forEach(function (li) {
                    var a = li.querySelector('[data-gm-name]');
                    var hit = q === '' || fhit || a.getAttribute('data-gm-name').indexOf(q) !== -1;
                    li.style.display = hit ? '' : 'none';
                    if (hit) { shown++; }
                });
                var vis = fhit || shown > 0;
                f.style.display = vis ? '' : 'none';
                if (vis) { any = true; if (q !== '') { f.open = true; } }
            });
            var none = document.querySelector('[data-gm-none]');
            if (none) { none.hidden = any; }
        });
    }
    var collapse = document.querySelector('[data-gm-collapse]');
    if (collapse) {
        collapse.addEventListener('click', function () {
            var folders = $all('[data-gm-folder]');
            var allClosed = folders.every(function (f) { return !f.open; });
            folders.forEach(function (f) { f.open = allClosed; });
            collapse.querySelector('.fa').className = 'fa ' + (allClosed ? 'fa-minus-square-o' : 'fa-plus-square-o');
            collapse.title = allClosed ? 'Tutup semua folder' : 'Buka semua folder';
        });
    }
    /* item terpilih selalu kelihatan di pohon (pohon punya gulir sendiri) */
    var picked = document.querySelector('.gm-tree__body .gm-row.is-selected');
    var tbody = document.querySelector('.gm-tree__body');
    if (picked && tbody && tbody.scrollHeight > tbody.clientHeight) {
        var top = picked.getBoundingClientRect().top - tbody.getBoundingClientRect().top;
        if (top > tbody.clientHeight - 40 || top < 0) { tbody.scrollTop += top - tbody.clientHeight / 3; }
    }

    /* HP: setelah memilih item, langsung gulir ke panel kanan (yang ada di bawah pohon) */
    if (window.innerWidth < 992 && /[?&](pilih|baru)=/.test(location.search)) {
        var panel = document.getElementById('gm-panel');
        if (panel && panel.scrollIntoView) { setTimeout(function () { panel.scrollIntoView({ block: 'start' }); window.scrollBy(0, -70); }, 60); }
    }

    /* ------------------------------------------------------------ jenis baru: unit pertama ikut nama */
    var nama = document.querySelector('[data-gm-autounit]');
    var unitIn = document.querySelector('[data-gm-unitname]');
    if (nama && unitIn) {
        var touched = unitIn.value !== '';
        unitIn.addEventListener('input', function () { touched = unitIn.value !== ''; });
        nama.addEventListener('input', function () {
            if (!touched) { unitIn.value = nama.value.replace(/^\s+|\s+$/g, '') ? nama.value.replace(/^\s+|\s+$/g, '') + ' 1' : ''; }
        });
    }

    /* ------------------------------------------------------------ editor item */
    var form = document.querySelector('[data-gm-editor]');
    if (!form) { return; }
    var tpl = form.querySelector('template[data-gm-tpl]');
    var counter = 0;
    var dirty = false;
    var bar = form.querySelector('.gm-savebar');

    function markDirty() {
        if (dirty) { return; }
        dirty = true;
        if (bar) {
            bar.classList.add('is-dirty');
            var t = bar.querySelector('[data-gm-dirty-txt]');
            if (t) { t.textContent = 'Ada perubahan yang belum disimpan.'; }
        }
    }
    function renumber() {
        var n = 0;
        $all('[data-gm-sec] > [data-gm-list] > [data-gm-item]', form).forEach(function (it) {
            var del = it.querySelector('[data-gm-del]');
            var no = it.querySelector('[data-gm-no]');
            if (del && del.checked) { no.textContent = '–'; return; }
            n++; no.textContent = n;
        });
        var gone = 0;
        $all('.gm-list--gone [data-gm-item]', form).forEach(function (it) { var d = it.querySelector('[data-gm-del]'); if (d && !d.checked) { gone++; } });
        var tot = form.querySelector('[data-gm-total]');
        if (tot) { tot.textContent = n + gone; }
    }
    function flash(li) { li.classList.remove('is-moved'); void li.offsetWidth; li.classList.add('is-moved'); }

    form.addEventListener('click', function (e) {
        var add = closest(e.target, '[data-gm-add]');
        if (add && tpl) {
            var sec = add.getAttribute('data-gm-add');
            var idx = 'n' + Date.now().toString(36) + (counter++);
            var html = tpl.innerHTML.replace(/__SEC__/g, sec).replace(/__IDX__/g, idx);
            var list = closest(add, '[data-gm-sec]').querySelector('[data-gm-list]');
            var wrap = document.createElement('ol');
            wrap.innerHTML = html;
            var li = wrap.firstElementChild;
            list.appendChild(li);
            renumber(); markDirty();
            var first = li.querySelector('.gm-in-part');
            if (first) { first.focus(); }
            return;
        }
        var up = closest(e.target, '[data-gm-up]');
        var down = closest(e.target, '[data-gm-down]');
        if (up || down) {
            var it = closest(e.target, '[data-gm-item]');
            if (up && it.previousElementSibling) { it.parentNode.insertBefore(it, it.previousElementSibling); }
            if (down && it.nextElementSibling) { it.parentNode.insertBefore(it.nextElementSibling, it); }
            flash(it); renumber(); markDirty();
            (up || down).focus();
        }
    });
    form.addEventListener('change', function (e) {
        var del = closest(e.target, '[data-gm-del]');
        if (del) {
            var it = closest(del, '[data-gm-item]');
            if (it.classList.contains('is-new') && del.checked) { it.parentNode.removeChild(it); }   // baris baru: langsung dibuang
            else { it.classList.toggle('is-deleted', del.checked); }
            renumber(); markDirty();
            return;
        }
        var ph = closest(e.target, '[data-gm-photo]');
        if (ph && ph.files && ph.files[0]) {
            if (closest(ph, '[data-gm-fotos]')) { addItemPhotos(ph); }
            else { preview(ph); }
        }
        var xf = closest(e.target, '[data-gm-xfile]');
        if (xf) { xfileChosen(xf); }
        var mode = closest(e.target, '[data-gm-mode]');
        if (mode) { applyMode(closest(mode, '[data-gm-gsec]')); }
        var eye = closest(e.target, '.gm-kt__eye input, .gm-kt__del input');
        if (eye) { syncTileState(eye); }
        if (closest(e.target, '[data-gm-xsel]')) { refreshGambar(); }
        markDirty();
    });
    form.addEventListener('input', function (e) { if (e.target.hasAttribute && e.target.hasAttribute('data-gm-iconfind')) { return; } markDirty(); if (e.target.classList && e.target.classList.contains('gm-in-part')) { refreshOptionsSoon(); } });
    form.addEventListener('submit', function () { dirty = false; });
    window.addEventListener('beforeunload', function (e) {
        if (!dirty) { return; }
        e.preventDefault(); e.returnValue = '';
        return '';
    });

    /* Perkecil 1 foto di browser (foto HP 4-8 MB -> +-300 KB) supaya unggah cepat. Server tetap memperkecil lagi. cb(File) */
    function shrink(file, cb) {
        if (!/^image\//.test(file.type) || file.size < 600 * 1024 || !document.createElement('canvas').toBlob) { cb(file); return; }
        var url = (window.URL || window.webkitURL).createObjectURL(file);
        var im = new Image();
        im.onload = function () {
            try {
                var max = 1600, w = im.naturalWidth, h = im.naturalHeight;
                if (w <= max && h <= max && file.size < 1500 * 1024) { cb(file); return; }
                var k = Math.min(1, max / Math.max(w, h));
                var c = document.createElement('canvas');
                c.width = Math.round(w * k); c.height = Math.round(h * k);
                var g = c.getContext('2d');
                g.fillStyle = '#fff'; g.fillRect(0, 0, c.width, c.height);
                g.drawImage(im, 0, 0, c.width, c.height);
                c.toBlob(function (blob) {
                    if (!blob || blob.size >= file.size) { cb(file); return; }
                    try { cb(new File([blob], (file.name || 'foto').replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' })); }
                    catch (x) { cb(file); }
                }, 'image/jpeg', 0.85);
            } catch (x) { cb(file); }
        };
        im.onerror = function () { cb(file); };
        im.src = url;
    }
    /* Ganti isi <input type=file> dengan daftar File (kalau browser mendukung DataTransfer) */
    function setFiles(input, files) {
        if (typeof DataTransfer === 'undefined') { return false; }
        try { var dt = new DataTransfer(); files.forEach(function (f) { dt.items.add(f); }); input.files = dt.files; return true; }
        catch (x) { return false; }
    }
    function shrinkAll(input, files, done, noSet) {
        var out = new Array(files.length), left = files.length;
        if (!left) { done([]); return; }
        files.forEach(function (f, i) { shrink(f, function (r) { out[i] = r; if (--left === 0) { if (!noSet) { setFiles(input, out); } done(out); } }); });
    }

    /* Pratinjau gambar panel (1 file) */
    function preview(input) {
        var file = input.files[0];
        var box = closest(input, '.gm-photo');
        if (!/^image\//.test(file.type)) { return; }
        var img = box.querySelector('img');
        if (!img) { img = document.createElement('img'); img.alt = ''; box.insertBefore(img, box.firstChild); }
        img.src = (window.URL || window.webkitURL).createObjectURL(file);
        box.classList.add('has-img');
        var wrap = closest(input, '.gm-item__photo') || closest(input, '.gm-gslot');
        var del = wrap ? wrap.querySelector('[data-gm-photo-del]') : null;
        if (del) { del.checked = false; }
        shrinkAll(input, [file], function () {});
    }

    /* ------------------------------------------------------------ [GENC-07OKT26-FOTO] banyak foto per item */
    var MAX_FOTO = 6;
    function fotoCount(box) { return $all('.gm-foto:not(.gm-foto--add)', box).length; }
    function updateFotoBox(box) {
        var list = box.querySelector('[data-gm-fotolist]');
        var n = fotoCount(box);
        var add = list.querySelector('.gm-foto--add');
        if (add) { add.hidden = n >= MAX_FOTO; }
        list.classList.toggle('has-img', n > 0);
        list.classList.toggle('has-many', n > 1);
        var first = true;
        $all('.gm-foto:not(.gm-foto--add)', list).forEach(function (f) {
            f.classList.toggle('is-main', first);
            f.title = first ? 'Foto utama' : 'Klik: jadikan foto utama';
            first = false;
        });
        var cnt = box.querySelector('[data-gm-fotocount]');
        if (cnt) { cnt.textContent = n ? n + '/' + MAX_FOTO + ' foto' : ''; }
    }
    function addItemPhotos(input) {
        var box = closest(input, '[data-gm-fotos]');
        var list = box.querySelector('[data-gm-fotolist]');
        var room = MAX_FOTO - fotoCount(box);
        var files = Array.prototype.slice.call(input.files).filter(function (f) { return /^image\//.test(f.type); });
        var bad = input.files.length - files.length;
        var cut = Math.max(0, files.length - room);
        if (cut > 0) { files = files.slice(0, room); }
        if (bad > 0 || cut > 0) {
            var msg = (bad ? bad + ' file bukan foto dilewati. ' : '') + (cut ? 'Maksimal ' + MAX_FOTO + ' foto per item: ' + cut + ' foto tidak ditambahkan.' : '');
            note(box, msg);
        }
        if (!files.length) { input.value = ''; return; }
        if (!setFiles(input, files) && cut > 0) { input.value = ''; return; }   // browser lama tidak bisa memotong daftar: batal
        var add = list.querySelector('.gm-foto--add');
        // input yang sudah berisi dipindah keluar (tetap ikut terkirim), kotak "Foto" diberi input baru yang kosong
        var keep = document.createElement('span'); keep.hidden = true; keep.className = 'gm-foto__files';
        box.appendChild(keep);
        var fresh = input.cloneNode(false); fresh.value = '';
        input.parentNode.replaceChild(fresh, input);
        keep.appendChild(input);
        input.removeAttribute('data-gm-photo'); input.setAttribute('data-gm-photo-used', '');
        files.forEach(function (f, i) {
            var d = document.createElement('div');
            d.className = 'gm-foto is-new'; d.setAttribute('data-gm-foto', ''); d.setAttribute('data-gm-new', '');
            d._gmFile = f; d._gmInput = input; d._gmIdx = i;
            var im = document.createElement('img'); im.alt = ''; im.src = (window.URL || window.webkitURL).createObjectURL(f);
            var x = document.createElement('button'); x.type = 'button'; x.className = 'gm-foto__x'; x.setAttribute('data-gm-foto-x', ''); x.title = 'Batal'; x.setAttribute('aria-label', 'Batalkan foto ini'); x.innerHTML = '&times;';
            var b = document.createElement('span'); b.className = 'gm-foto__new'; b.textContent = 'baru';
            d.appendChild(im); d.appendChild(b); d.appendChild(x);
            list.insertBefore(d, add);
        });
        // foto diperkecil di latar belakang; yang sudah dibatalkan (x) sementara itu tidak ikut terkirim
        shrinkAll(input, files, function (shrunk) {
            var tiles = $all('[data-gm-new]', list).filter(function (t) { return t._gmInput === input; });
            tiles.forEach(function (t) { if (shrunk[t._gmIdx]) { t._gmFile = shrunk[t._gmIdx]; } });
            if (tiles.length) { setFiles(input, tiles.map(function (t) { return t._gmFile; })); } else { input.value = ''; }
        }, true);
        updateFotoBox(box);
        refreshGambar();
    }
    function note(box, msg) {
        var n = box.querySelector('.gm-fotos__note');
        if (!n) { n = document.createElement('span'); n.className = 'gm-fotos__note'; n.setAttribute('role', 'status'); box.appendChild(n); }
        n.textContent = msg;
        clearTimeout(n._t); n._t = setTimeout(function () { if (n.parentNode) { n.parentNode.removeChild(n); } }, 6000);
    }
    form.addEventListener('click', function (e) {
        var x = closest(e.target, '[data-gm-foto-x]');
        if (x) {
            e.preventDefault();
            var tile = closest(x, '[data-gm-foto]'), box = closest(x, '[data-gm-fotos]');
            if (tile.hasAttribute('data-gm-new')) {
                var inp = tile._gmInput;
                var rest = $all('[data-gm-new]', box).filter(function (t) { return t !== tile && t._gmInput === inp; });
                if (!setFiles(inp, rest.map(function (t) { return t._gmFile; }))) {   // browser lama: semua foto dari pilihan itu dibatalkan
                    inp.value = ''; rest.forEach(function (t) { t.parentNode.removeChild(t); });
                }
            }
            tile.parentNode.removeChild(tile);
            updateFotoBox(box); refreshGambar(); markDirty();
            return;
        }
        var f = closest(e.target, '.gm-foto:not(.gm-foto--add):not(.is-main)');
        if (f && !f.hasAttribute('data-gm-new') && closest(f, '[data-gm-fotos]')) {   // jadikan foto utama
            var l = f.parentNode; l.insertBefore(f, l.firstChild);
            updateFotoBox(closest(f, '[data-gm-fotos]')); refreshGambar(); markDirty();
            return;
        }
        var xb = closest(e.target, '[data-gm-xadd-btn]');
        if (xb) { addXRow(closest(xb, '[data-gm-gsec]')); return; }
        var xd = closest(e.target, '[data-gm-xdel]');
        if (xd) { var row = closest(xd, '[data-gm-xrow]'); row.parentNode.removeChild(row); refreshGambar(); markDirty(); }
    });

    /* ------------------------------------------------------------ [GENC-07OKT26-FOTO] gambar bagian mesin per bagian */
    var gsecs = $all('[data-gm-gsec]', form).filter(function (g) { return !closest(g, '[data-gm-ro]'); });   // kartu hanya-baca (dikunci) tidak disentuh
    var state = {};   // per slot: hidden {path:true}, xdel {path:true}, extra [{path,src,db}]
    gsecs.forEach(function (g) {
        var slot = g.getAttribute('data-gm-slot');
        var st = { hidden: {}, xdel: {}, extra: [] };
        try { JSON.parse(g.getAttribute('data-gm-hide') || '[]').forEach(function (p) { st.hidden[p] = true; }); } catch (x) {}
        try { st.extra = JSON.parse(g.getAttribute('data-gm-extra') || '[]'); } catch (x) {}
        state[slot] = st;
    });
    function applyMode(g) {
        var r = g.querySelector('[data-gm-mode]:checked');
        var auto = r ? r.value === 'auto' : !g.querySelector('[data-gm-auto]').hidden;
        var a = g.querySelector('[data-gm-auto]'), p = g.querySelector('[data-gm-panel]');
        if (a) { a.hidden = !auto; }
        if (p) { p.hidden = auto; }
    }
    function syncTileState(inp) {
        var g = closest(inp, '[data-gm-gsec]'); if (!g) { return; }
        var st = state[g.getAttribute('data-gm-slot')];
        var kt = closest(inp, '.gm-kt');
        if (closest(inp, '.gm-kt__eye')) { if (inp.checked) { st.hidden[inp.value] = true; } else { delete st.hidden[inp.value]; } if (kt) { kt.classList.toggle('is-off', inp.checked); } }
        else { if (inp.checked) { st.xdel[inp.value] = true; } else { delete st.xdel[inp.value]; } if (kt) { kt.classList.toggle('is-gone', inp.checked); } }
    }
    /* Item aktif per bagian, urut & bernomor seperti setelah disimpan (daftar utama dulu, lalu yang dipulihkan) */
    function activeItems() {
        var out = {}, no = 0;
        $all('[data-gm-sec]', form).forEach(function (sec) {
            var sk = sec.getAttribute('data-gm-sec'), list = [];
            var rows = $all('[data-gm-list] > [data-gm-item]', sec).concat($all('.gm-list--gone > [data-gm-item]', sec));
            rows.forEach(function (it) {
                var d = it.querySelector('[data-gm-del]');
                if (d && d.checked) { return; }
                no++;
                var db = (it.querySelector('input[name$="[db]"]') || {}).value || '';
                var key = (it.querySelector('input[name$="[key]"]') || {}).value || '';
                var part = (it.querySelector('.gm-in-part') || {}).value || '';
                var fotos = $all('.gm-foto:not(.gm-foto--add)', it).map(function (f) {
                    var pth = f.querySelector('[data-gm-fotopath]');
                    var im = f.querySelector('img');
                    return { path: pth ? pth.value : '', src: im ? im.src : '', isNew: f.hasAttribute('data-gm-new') };
                });
                list.push({ no: no, db: db, key: db || key, part: part, fotos: fotos });
            });
            out[sk] = list;
        });
        return out;
    }
    var optTimer = null;
    function refreshOptionsSoon() { clearTimeout(optTimer); optTimer = setTimeout(refreshGambar, 350); }
    function el(tag, cls, html) { var e = document.createElement(tag); if (cls) { e.className = cls; } if (html) { e.innerHTML = html; } return e; }
    function tile(slot, src, no, path, kind, st) {
        var f = el('figure', 'gm-kt' + (kind === 'x' ? ' is-extra' : '') + (kind === 'new' ? ' is-new' : ''));
        var im = document.createElement('img'); im.alt = ''; im.src = src; f.appendChild(im);
        var b = el('span', 'gm-kt__no'); b.textContent = no; f.appendChild(b);
        if (kind === 'new') { f.appendChild(el('span', 'gm-kt__new', 'baru')); return f; }
        var eye = el('label', 'gm-kt__btn gm-kt__eye'); eye.title = 'Tampil / sembunyikan di report';
        var c = document.createElement('input'); c.type = 'checkbox'; c.name = 'gambar_hide[' + slot + '][]'; c.value = path; c.checked = !!st.hidden[path];
        eye.appendChild(c); eye.appendChild(el('i', 'fa fa-eye')); f.appendChild(eye);
        if (c.checked) { f.classList.add('is-off'); }
        if (kind === 'x') {
            var dl = el('label', 'gm-kt__btn gm-kt__del'); dl.title = 'Hapus foto tambahan';
            var c2 = document.createElement('input'); c2.type = 'checkbox'; c2.name = 'gambar_extra_del[' + slot + '][]'; c2.value = path; c2.checked = !!st.xdel[path];
            dl.appendChild(c2); dl.appendChild(el('i', 'fa fa-trash-o')); f.appendChild(dl);
            if (c2.checked) { f.classList.add('is-gone'); }
        }
        return f;
    }
    function refreshGambar() {
        if (!gsecs.length) { return; }
        var items = activeItems();
        var shown = 0;
        gsecs.forEach(function (g) {
            var sk = g.getAttribute('data-gm-gsec'), slot = g.getAttribute('data-gm-slot'), st = state[slot];
            var list = items[sk] || [];
            var vis = list.length > 0 || g.getAttribute('data-gm-always') === '1';
            g.hidden = !vis; if (vis) { shown++; }
            var seg = g.querySelector('[data-gm-seg]');
            if (seg) { seg.hidden = list.length === 0; }   // bagian kosong bawaan form resmi: hanya gambar sendiri
            var box = g.querySelector('[data-gm-kolase]');
            if (!box) { return; }
            var byKey = {}, n = 0, rendered = {};
            list.forEach(function (it) { byKey[it.key] = it; if (it.db) { byKey[it.db] = it; } });
            box.innerHTML = '';
            list.forEach(function (it) {
                it.fotos.forEach(function (f) {
                    if (f.isNew) { box.appendChild(tile(slot, f.src, it.no, '', 'new', st)); }
                    else { box.appendChild(tile(slot, f.src, it.no, f.path, 'i', st)); rendered[f.path] = true; }
                    n++;
                });
                st.extra.forEach(function (x) { if (x.db === it.db && it.db) { box.appendChild(tile(slot, x.src, it.no, x.path, 'x', st)); rendered[x.path] = true; n++; } });
                $all('[data-gm-xrow]', g).forEach(function (row) {
                    var sel = row.querySelector('[data-gm-xsel]'), fi = row.querySelector('[data-gm-xfile]');
                    if (sel.value === it.key && fi.files) { Array.prototype.forEach.call(fi.files, function (file) { box.appendChild(tile(slot, (window.URL || window.webkitURL).createObjectURL(file), it.no, '', 'new', st)); n++; }); }
                });
            });
            // foto tersembunyi milik item yang sedang tidak tampil: tetap dikirim supaya tidak lupa disembunyikan
            Object.keys(st.hidden).forEach(function (p) {
                if (rendered[p]) { return; }
                var h = document.createElement('input'); h.type = 'hidden'; h.name = 'gambar_hide[' + slot + '][]'; h.value = p; box.appendChild(h);
            });
            var empty = g.querySelector('[data-gm-kempty]'); if (empty) { empty.hidden = n > 0; }
            // pilihan item di baris foto tambahan
            $all('[data-gm-xsel]', g).forEach(function (sel) {
                var cur = sel.value; sel.innerHTML = '';
                list.forEach(function (it) { var o = document.createElement('option'); o.value = it.key; o.textContent = it.no + '. ' + (it.part || 'item baru'); sel.appendChild(o); });
                if (byKey[cur]) { sel.value = byKey[cur].key; }
            });
            var xa = g.querySelector('[data-gm-xadd-btn]');
            if (xa) { xa.disabled = list.length === 0 || (st.extra.length + $all('[data-gm-xrow]', g).length) >= parseInt(g.querySelector('[data-gm-xadd]').getAttribute('data-gm-xmax'), 10); }
        });
        var none = form.querySelector('[data-gm-gnone]'); if (none) { none.hidden = shown > 0; }
    }
    var xcount = 0;
    function addXRow(g) {
        var t = g.querySelector('[data-gm-xtpl]'); if (!t) { return; }
        var wrap = document.createElement('div');
        wrap.innerHTML = t.innerHTML.replace(/__N__/g, 'x' + Date.now().toString(36) + (xcount++));
        var row = wrap.firstElementChild;
        g.querySelector('[data-gm-xrows]').appendChild(row);
        refreshGambar();
        var fi = row.querySelector('[data-gm-xfile]'); if (fi && fi.click) { fi.click(); }
    }
    function xfileChosen(fi) {
        var row = closest(fi, '[data-gm-xrow]');
        var files = Array.prototype.slice.call(fi.files || []).filter(function (f) { return /^image\//.test(f.type); });
        var nm = row.querySelector('[data-gm-xname]');
        if (nm) { nm.textContent = files.length ? files.length + ' foto dipilih' : 'Pilih foto'; }
        if (files.length !== (fi.files || []).length) { setFiles(fi, files); }
        shrinkAll(fi, files, function () {});
        refreshGambar();
    }
    gsecs.forEach(applyMode);
    $all('[data-gm-fotos]', form).forEach(updateFotoBox);

    var baseRenumber = renumber;
    renumber = function () { baseRenumber(); refreshGambar(); };
    renumber();
})();

/* ============================================================================
   [GENC-13OKT26] Riwayat perubahan ringkas + layar penuh, hapus permanen, cadangan
     [data-gm-vfull]        layar penuh riwayat (#riwayat-penuh). Tanpa JS tetap jalan lewat :target;
                            dengan JS: kunci gulir halaman, Esc / tombol Kecilkan, fokus tetap di dalam, cari versi
     [data-gm-hist-more]    "Versi lain" dibuka otomatis kalau alamat berakhiran #riwayat (sesudah Kembalikan)
     [data-gm-purge-form]   hapus permanen: tombol aktif hanya kalau nama diketik persis; tanda sedang membuat cadangan
     [data-gm-cad-ready]    cadangan baru: unduh otomatis sekali
   ========================================================================= */
(function () {
    'use strict';
    function $all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
    function norm(s) { return String(s || '').replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, '').toLowerCase(); }

    /* ------------------------------------------------------------ riwayat: "Versi lain" */
    var more = document.querySelector('[data-gm-hist-more]');
    if (more && (location.hash === '#riwayat' || location.hash === '#cadangan')) { more.open = true; }

    /* ------------------------------------------------------------ riwayat: layar penuh */
    /* [GENC-13OKT26c] berlaku untuk tiap layar penuh: #riwayat-penuh (versi) & #cadangan-penuh (riwayat hapus permanen).
       data-gm-vhome = id kartu asal, data-gm-vunit = kata di penghitung, filter opsional: jenis (chip) & bulan (select). */
    var openKey = null;
    $all('[data-gm-vfull]').forEach(function (full) {
        document.documentElement.classList.add('gm-vjs');
        var id = full.id, home = full.getAttribute('data-gm-vhome') || 'riwayat', unit = full.getAttribute('data-gm-vunit') || 'versi';
        var box = full.querySelector('.gm-vfull__box');
        var find = full.querySelector('[data-gm-vfind]');
        var isOpen = function () { return location.hash === '#' + id; };
        var sync = function () {
            var o = isOpen();
            full.classList.toggle('is-open', o);
            if (o) { openKey = id; } else if (openKey === id) { openKey = null; }
            document.documentElement.classList.toggle('gm-noscroll', !!openKey);
            if (o) { setTimeout(function () { var el = find || full.querySelector('.gm-vfull__x'); if (el) { try { el.focus({ preventScroll: true }); } catch (e) { el.focus(); } } }, 40); }
        };
        var close = function () {
            if (window.history && history.replaceState) {
                history.replaceState(null, '', location.pathname + location.search + '#' + home);
                sync();
                var card = document.getElementById(home);
                if (card && card.scrollIntoView) { card.scrollIntoView({ block: 'start' }); }
            } else { location.hash = home; }
        };
        window.addEventListener('hashchange', sync);
        sync();
        $all('[data-gm-vfull-close]', full).forEach(function (a) {
            a.addEventListener('click', function (e) { e.preventDefault(); close(); });
        });
        document.addEventListener('keydown', function (e) {
            if (!isOpen()) { return; }
            if (e.key === 'Escape' || e.key === 'Esc') {
                if (document.querySelector('dialog[open]')) { return; }   /* dialog konfirmasi menutup dirinya sendiri dulu */
                e.preventDefault(); close(); return;
            }
            if (e.key === 'Tab' && box) {   /* fokus tidak keluar dari layar penuh */
                var f = $all('a[href], button:not([disabled]), input, select, textarea', box).filter(function (x) { return x.offsetParent !== null; });
                if (!f.length) { return; }
                var first = f[0], last = f[f.length - 1];
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
        });
        if (!find) { return; }
        var items = $all('[data-gm-ver]', full);
        var cnt = full.querySelector('[data-gm-vcount]');
        var none = full.querySelector('[data-gm-vnone]');
        var chips = $all('[data-gm-vkind-btn]', full);
        var month = full.querySelector('[data-gm-vmonth-sel]');
        var total = items.length, kind = '';
        var apply = function () {
            /* kata dicocokkan dari AWAL kata ("6" cocok dengan "Pallet Mover 6", bukan "2026") */
            var tok = function (x) { return ' ' + norm(x).replace(/[^a-z0-9]+/g, ' '); };
            var words = tok(find.value).split(' '), m = month ? month.value : '', n = 0;
            items.forEach(function (li) {
                var t = tok(li.getAttribute('data-gm-vtext') || '');
                var hit = words.every(function (w) { return w === '' || t.indexOf(' ' + w) !== -1; })
                    && (kind === '' || li.getAttribute('data-gm-vkind') === kind)
                    && (m === '' || li.getAttribute('data-gm-vmonth') === m);
                li.hidden = !hit;
                if (hit) { n++; }
            });
            if (cnt) { cnt.textContent = (n === total ? total : n + ' dari ' + total) + ' ' + unit; }
            if (none) { none.hidden = n > 0; }
        };
        find.addEventListener('input', apply);
        find.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); } });
        if (month) { month.addEventListener('change', apply); }
        chips.forEach(function (c) {
            c.addEventListener('click', function () {
                kind = c.getAttribute('data-gm-vkind-btn') || '';
                chips.forEach(function (x) { var on = x === c; x.classList.toggle('is-active', on); x.setAttribute('aria-pressed', on ? 'true' : 'false'); });
                apply();
            });
        });
    });

    /* ------------------------------------------------------------ hapus permanen */
    $all('[data-gm-purge-form]').forEach(function (form) {
        var card = form.parentNode;
        var inp = form.querySelector('[data-gm-purge-name]');
        var btn = form.querySelector('[data-gm-purge-btn]');
        var wait = form.querySelector('[data-gm-purge-wait]');
        if (!inp || !btn) { return; }
        var want = norm(inp.getAttribute('data-gm-purge-name'));
        var upd = function () { btn.disabled = norm(inp.value) !== want; };
        inp.addEventListener('input', upd);
        upd();
        form.addEventListener('submit', function (e) {
            if (norm(inp.value) !== want) { e.preventDefault(); inp.focus(); return; }
            if (form.getAttribute('data-busy') === '1') { e.preventDefault(); return; }
            form.setAttribute('data-busy', '1');
            if (card && card.classList) { card.classList.add('is-busy'); }
            if (wait) { wait.hidden = false; }
            setTimeout(function () { btn.disabled = true; }, 0);   /* sesudah form terkirim */
        });
        if (location.hash === '#hapus-permanen' && card && card.tagName === 'DETAILS') {
            card.open = true;
            setTimeout(function () { inp.focus(); }, 60);
        }
    });

    /* ------------------------------------------------------------ cadangan baru: unduh otomatis sekali */
    var ready = document.querySelector('[data-gm-cad-ready]');
    if (ready) {
        var url = ready.getAttribute('data-gm-cad-ready');
        var done = false;
        try { done = sessionStorage.getItem('gm-dl:' + url) === '1'; sessionStorage.setItem('gm-dl:' + url, '1'); } catch (e) { done = false; }
        if (!done && url) {
            var a = document.createElement('a');
            a.href = url; a.setAttribute('download', ''); a.style.display = 'none';
            document.body.appendChild(a);
            setTimeout(function () { a.click(); a.parentNode.removeChild(a); }, 300);
        }
    }
})();
