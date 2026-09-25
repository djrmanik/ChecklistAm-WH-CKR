<?php
/**
 * ROLE PERMISSIONS - matriks hak akses (Generasi C)      [GENC-24SEP26-SHELL]
 * Data: Role_permissionsController::index(). Simpan: POST role_permissions/edit/<role_id>.
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$d = $this->view_data;
$roles = $d['roles']; $role = $d['role']; $m = $d['model'];
$cols = genc_perm_columns();
$ro = !$d['can_edit'];
$tick = function ($pa, $label) use ($m, $ro, $d) {
    $on = isset($m['have'][$pa]);
    $lock = $d['is_own'] && in_array($pa, $d['locked'], true);
    $h = '<label class="genc-tick" title="' . genc_e($label . ' (' . $pa . ')') . '"><input type="checkbox" name="p[]" value="' . genc_e($pa) . '"'
       . ($on ? ' checked' : '') . ($ro || $lock ? ' disabled' : '') . ' data-rp="' . genc_e($pa) . '" data-rp-init="' . ($on ? '1' : '0') . '"><span></span><span class="sr-only">' . genc_e($label) . '</span></label>';
    if ($lock) { $h .= '<input type="hidden" name="p[]" value="' . genc_e($pa) . '">'; }
    return $h;
};
$total_rows = 0;
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container">
    <nav class="genc-crumbs" aria-label="Breadcrumb"><a href="<?php print_link('home'); ?>">Home</a><span>/</span><span>Role Permissions</span></nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title">Hak akses per role</h1>
            <p class="genc-subtitle">Centang = role ini boleh. Berlaku untuk semua user dengan role itu &mdash; langsung terasa di sidebar &amp; tombol tiap halaman.</p>
        </div>
        <?php if (ACL::is_allowed('roles/list')): ?><div class="genc-actions"><a class="genc-btn" href="<?php print_link('roles'); ?>"><i class="fa fa-shield"></i> Kelola role</a></div><?php endif; ?>
    </div>

    <div class="genc-card" style="margin-bottom:16px">
        <div class="genc-toolbar genc-toolbar--flat">
            <span class="genc-small genc-muted" style="font-weight:600">Role:</span>
            <div class="genc-chips" style="flex-wrap:wrap">
                <?php foreach ($roles as $rid => $r): ?>
                    <a class="genc-chip<?php echo $role === (string) $rid ? ' is-active' : ''; ?>" href="<?php echo genc_e(genc_url('role_permissions', array('role' => $rid))); ?>"><?php echo genc_e($r['role_name']); ?><span class="genc-chip__count"><?php echo (int) $r['n_user']; ?> user</span></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

<?php if ($role === ''): ?>
    <div class="genc-card"><div class="genc-empty"><div class="genc-empty__title">Belum ada role</div></div></div>
<?php else: $R = $roles[$role]; ?>
    <?php if ($d['is_own']): ?>
        <div class="genc-notice genc-notice--warn" style="margin-bottom:12px"><i class="fa fa-exclamation-triangle"></i><div>Ini role akun Anda sendiri. Hak <b>Role Permissions (Lihat &amp; Ubah)</b> dikunci supaya Anda tidak terkunci dari halaman ini.</div></div>
    <?php endif; ?>
    <?php if ($ro): ?>
        <div class="genc-notice genc-notice--info" style="margin-bottom:12px"><i class="fa fa-lock"></i><div>Mode lihat saja &mdash; role Anda tidak punya hak <b>Role Permissions &rarr; Ubah</b>.</div></div>
    <?php endif; ?>

    <form method="post" action="<?php print_link('role_permissions/edit/' . (int) $role . '?csrf_token=' . Csrf::$token); ?>" class="genc-card" data-rp-form>
        <div class="genc-card__head">
            <div>
                <h2 class="genc-card__title"><?php echo genc_e($R['role_name']); ?></h2>
                <span class="genc-small genc-muted"><?php echo (int) $R['n_user']; ?> user &middot; <?php echo (int) $R['n_perm']; ?> hak akses tersimpan</span>
            </div>
            <?php if (ACL::is_allowed('users/list')): ?><a class="genc-btn genc-btn--sm genc-btn--ghost" href="<?php echo genc_e(genc_url('users', array('role' => $role))); ?>"><i class="fa fa-users"></i> Lihat user role ini</a><?php endif; ?>
        </div>
        <div class="genc-perm-wrap">
        <table class="genc-perm">
            <thead><tr>
                <th>Halaman</th>
                <?php foreach ($cols as $k => $c): ?><th><?php echo genc_e($c[0]); ?><?php echo $c[1] !== '' ? '<small>' . genc_e($c[1]) . '</small>' : ''; ?></th><?php endforeach; ?>
                <th style="text-align:left">Aksi lain<small>bawaan phpRAD / halaman lama</small></th>
            </tr></thead>
            <tbody>
            <?php foreach ($m['sections'] as $sec): ?>
                <tr class="genc-perm__sec"><td colspan="<?php echo count($cols) + 2; ?>"><?php echo genc_e($sec['title']); ?></td></tr>
                <?php foreach ($sec['rows'] as $row): $total_rows++; ?>
                <tr data-rp-row="<?php echo !empty($row['machine']) ? 'machine' : ''; ?>">
                    <td class="genc-perm__page"><b<?php echo !empty($sec['other']) ? ' class="genc-mono"' : ''; ?>><?php echo genc_e($row['name']); ?></b><?php if (!empty($row['sub'])): ?><small><?php echo genc_e($row['sub']); ?></small><?php endif; ?></td>
                    <?php if (!empty($row['single'])): ?>
                        <td><?php echo $tick($row['page'] . '/' . $row['actions'][0], $row['name']); ?></td>
                        <td colspan="<?php echo count($cols) - 1; ?>" style="text-align:left" class="genc-small genc-muted">Akses menu</td>
                    <?php else: foreach ($cols as $k => $c): ?>
                        <td><?php echo in_array($k, $row['actions'], true) ? $tick($row['page'] . '/' . $k, $row['name'] . ' - ' . $c[0]) : '<span class="genc-perm__na" aria-hidden="true">&ndash;</span>'; ?></td>
                    <?php endforeach; endif; ?>
                    <td style="text-align:left"><div class="genc-perm__extra">
                        <?php foreach ($row['extras'] as $a): $pa = $row['page'] . '/' . $a; $on = isset($m['have'][$pa]); $lock = $d['is_own'] && in_array($pa, $d['locked'], true); ?>
                            <label class="genc-chipbox"><input type="checkbox" name="p[]" value="<?php echo genc_e($pa); ?>"<?php echo $on ? ' checked' : ''; ?><?php echo $ro || $lock ? ' disabled' : ''; ?> data-rp="<?php echo genc_e($pa); ?>" data-rp-init="<?php echo $on ? '1' : '0'; ?>"><?php echo genc_e($a); ?></label>
                        <?php endforeach; ?>
                    </div></td>
                </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if (!$ro): ?>
        <div class="genc-perm-bar">
            <span class="genc-perm-bar__txt" data-rp-status><i class="fa fa-info-circle"></i> Mesin: mencentang Detail/Isi/Ubah/Hapus/Approve otomatis ikut <b>Lihat</b> (tanpa itu menu mesin tidak muncul).</span>
            <button type="button" class="genc-btn" data-rp-reset>Batalkan perubahan</button>
            <button type="submit" class="genc-btn genc-btn--primary" data-rp-save><i class="fa fa-check"></i> Simpan hak akses</button>
        </div>
        <?php endif; ?>
    </form>
<?php endif; ?>
</div>
</div>
<?php echo genc_assets_js(); ?>
<script>
(function () {
    var form = document.querySelector('[data-rp-form]');
    if (!form) { return; }
    var status = form.querySelector('[data-rp-status]');
    var orig = status ? status.innerHTML : '';
    function refresh() {
        var n = 0;
        form.querySelectorAll('input[data-rp]').forEach(function (i) {
            var ch = (i.checked ? '1' : '0') !== i.getAttribute('data-rp-init');
            if (ch) { n++; }
            var box = i.closest('.genc-tick, .genc-chipbox');
            if (box) { box.classList.toggle('is-changed', ch); }
        });
        if (status) { status.innerHTML = n ? '<b style="color:var(--gc-pr)">' + n + ' perubahan belum disimpan.</b> Klik Simpan supaya berlaku.' : orig; }
        form.dataset.dirty = n ? '1' : '';
    }
    form.addEventListener('change', function (e) {
        var i = e.target;
        if (!i.matches('input[data-rp]')) { return; }
        var tr = i.closest('tr[data-rp-row="machine"]');
        if (tr) {
            var pa = i.value, page = pa.split('/')[0], list = tr.querySelector('input[value="' + page + '/list"]');
            if (i === list && !i.checked) { tr.querySelectorAll('input[data-rp]').forEach(function (x) { if (!x.disabled) { x.checked = false; } }); }
            else if (i !== list && i.checked && list) { list.checked = true; }
        }
        refresh();
    });
    var reset = form.querySelector('[data-rp-reset]');
    if (reset) { reset.addEventListener('click', function () { form.querySelectorAll('input[data-rp]').forEach(function (i) { i.checked = i.getAttribute('data-rp-init') === '1'; }); refresh(); }); }
    form.addEventListener('submit', function () { form.dataset.dirty = ''; var b = form.querySelector('[data-rp-save]'); if (b) { b.disabled = true; b.innerHTML = 'Menyimpan...'; } });
    window.addEventListener('beforeunload', function (e) { if (form.dataset.dirty) { e.preventDefault(); e.returnValue = ''; } });
})();
</script>
