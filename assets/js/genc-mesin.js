/* ============================================================================
   MESIN & UNIT - perilaku halaman explorer               [GENC-06OKT26-MESIN]
   Vanilla JS, tanpa library. Kontrak data-*:
     [data-gm-filter]     kotak cari pohon (saring folder & unit)
     [data-gm-collapse]   tutup semua folder
     [data-gm-editor]     form item jenis mesin: tambah / naik / turun / hapus baris,
                          nomor urut, jumlah item, pratinjau & kecilkan foto, peringatan belum disimpan
     [data-gm-autounit]   nama jenis -> isi otomatis "unit pertama" (kalau belum diubah)
   Halaman tetap berfungsi tanpa JS (form biasa), kecuali tombol tambah baris item.
   ========================================================================= */
(function () {
    'use strict';
    function $all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
    function closest(el, sel) {
        while (el && el.nodeType === 1) { if (el.matches ? el.matches(sel) : el.msMatchesSelector(sel)) { return el; } el = el.parentNode; }
        return null;
    }

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
        if (ph && ph.files && ph.files[0]) { preview(ph); }
        markDirty();
    });
    form.addEventListener('input', markDirty);
    form.addEventListener('submit', function () { dirty = false; });
    window.addEventListener('beforeunload', function (e) {
        if (!dirty) { return; }
        e.preventDefault(); e.returnValue = '';
        return '';
    });

    /* Pratinjau foto + perkecil di browser (foto HP 4-8 MB -> +-300 KB) supaya unggah cepat. Server tetap memperkecil lagi. */
    function preview(input) {
        var file = input.files[0];
        var box = closest(input, '.gm-photo');
        if (!/^image\//.test(file.type)) { return; }
        var img = box.querySelector('img');
        if (!img) { img = document.createElement('img'); img.alt = ''; box.insertBefore(img, box.firstChild); }
        var url = (window.URL || window.webkitURL).createObjectURL(file);
        img.src = url;
        box.classList.add('has-img');
        var wrap = closest(input, '.gm-item__photo') || closest(input, '.gm-gslot');   /* foto item | gambar report */
        var del = wrap ? wrap.querySelector('[data-gm-photo-del]') : null;
        if (del) { del.checked = false; }
        if (file.size < 600 * 1024 || typeof DataTransfer === 'undefined' || !document.createElement('canvas').toBlob) { return; }
        var im = new Image();
        im.onload = function () {
            try {
                var max = 1600, w = im.naturalWidth, h = im.naturalHeight;
                if (w <= max && h <= max && file.size < 1500 * 1024) { return; }
                var k = Math.min(1, max / Math.max(w, h));
                var c = document.createElement('canvas');
                c.width = Math.round(w * k); c.height = Math.round(h * k);
                var g = c.getContext('2d');
                g.fillStyle = '#fff'; g.fillRect(0, 0, c.width, c.height);
                g.drawImage(im, 0, 0, c.width, c.height);
                c.toBlob(function (blob) {
                    if (!blob || blob.size >= file.size) { return; }
                    try {
                        var dt = new DataTransfer();
                        dt.items.add(new File([blob], (file.name || 'foto').replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }));
                        input.files = dt.files;
                    } catch (x) { /* browser lama: kirim file asli */ }
                }, 'image/jpeg', 0.85);
            } catch (x) { /* kirim file asli */ }
        };
        im.src = url;
    }

    renumber();
})();
