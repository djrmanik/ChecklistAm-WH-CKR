<?php
/**
 * GENERASI C - katalog halaman & aksi untuk matriks hak akses   [GENC-24SEP26-SHELL]
 *
 * Hak akses phpRAD = baris (role_id, page_name, action_name) di tabel role_permissions.
 * Katalog ini hanya memberi NAMA MANUSIA & urutan tampil. Kombinasi yang ada di DB
 * tapi tidak ada di katalog tetap tampil di bagian "Lainnya" (tidak ada yang tersembunyi).
 * Menambah mesin baru: tambah 1 baris di 'Checklist AM'.
 */
if (!function_exists('genc_perm_catalog')) {
    function genc_perm_catalog() {
        $m = array('list', 'view', 'add', 'edit', 'delete', 'approve');
        $cat = array(
            array('title' => 'Checklist AM', 'rows' => array(
                array('page' => 'palletmover',        'name' => 'Pallet Mover & Stacker', 'actions' => $m, 'machine' => true),
                array('page' => 'forklift',           'name' => 'Forklift',               'actions' => $m, 'machine' => true),
                array('page' => 'agv_table_top_lift', 'name' => 'AGV Table Top Lift',     'actions' => $m, 'machine' => true),
                array('page' => 'counterbalance',     'name' => 'AGV Counterbalance',     'actions' => $m, 'machine' => true),
                array('page' => 'kir3p01dp001',       'name' => 'Mesin Dumping KIR3',     'actions' => $m, 'machine' => true),
                array('page' => 'kir6p01dp001',       'name' => 'Mesin Dumping KIR6',     'actions' => $m, 'machine' => true),
                array('page' => 'kir7p01dp001',       'name' => 'Mesin Dumping KIR7',     'actions' => $m, 'machine' => true),
                array('page' => 'mesin_geprek',       'name' => 'Mesin Geprek',           'actions' => $m, 'machine' => true),
                array('page' => 'conveyor',           'name' => 'Conveyor',               'actions' => $m, 'machine' => true),
            )),
            array('title' => 'Pantau (semua mesin)', 'rows' => array(
                array('page' => 'palletmover', 'name' => 'Menu Approval',    'actions' => array('approval'),    'single' => true, 'sub' => 'ringkasan antrean approval semua mesin'),
                array('page' => 'palletmover', 'name' => 'Menu NOK History', 'actions' => array('uncompleted'), 'single' => true, 'sub' => 'ringkasan temuan semua mesin'),
            )),
            array('title' => 'Admin & sistem', 'rows' => array(
                array('page' => 'users',            'name' => 'Users',            'actions' => array('list', 'add', 'edit', 'delete')),
                array('page' => 'roles',            'name' => 'Roles',            'actions' => array('list', 'add', 'edit', 'delete')),
                array('page' => 'role_permissions', 'name' => 'Role Permissions', 'actions' => array('list', 'edit'), 'sub' => 'Ubah = boleh mengubah matriks ini'),
                array('page' => 'app_logs',         'name' => 'App Logs',         'actions' => array('list')),
                array('page' => 'master_select',    'name' => 'Master Select',    'actions' => array('list', 'view', 'add', 'edit', 'delete'), 'sub' => 'daftar pilihan form lama'),
                array('page' => 'mesin',            'name' => 'Mesin & Unit',     'actions' => array('list', 'add', 'edit', 'delete'), 'sub' => 'tambah / ubah mesin & unit tanpa coding'),   // [GENC-06OKT26-MESIN]
                array('page' => 'tampilan',         'name' => 'Tampilan',         'actions' => array('list', 'edit'), 'sub' => 'Lihat = Riwayat tampilan; Ubah = Mode edit tampilan (teks, susunan, tema) untuk semua user'),   // [GENC-13OKT26-TAMPILAN]
            )),
        );
        // [GENC-06OKT26-MESIN] halaman mesin yang dibuat lewat menu Mesin & Unit (route am_*)
        require_once __DIR__ . '/genc_registry.php';
        // [GENC-08OKT26-P3] mesin awal: nama baru dari Mesin & Unit; mesin / unit di Arsip tidak tampil (hak aksesnya tetap tersimpan)
        $rows = array();
        foreach ($cat[0]['rows'] as $r) {
            if (genc_reg_is_archived($r['page'], '')) { continue; }
            $f = genc_reg_folder_of_page($r['page']);
            if ($f && empty($f['jenis'])) { $r['name'] = genc_reg_rename_text($f['key'], $r['name']); }
            $rows[] = $r;
        }
        $cat[0]['rows'] = $rows;
        foreach (genc_reg_perm_rows() as $page => $name) {
            $cat[0]['rows'][] = array('page' => $page, 'name' => $name, 'actions' => $m, 'machine' => true, 'sub' => 'ditambah lewat Mesin & Unit');
        }
        return $cat;
    }

    /** Judul kolom matriks. */
    function genc_perm_columns() {
        return array(
            'list'    => array('Lihat', 'menu & daftar'),
            'view'    => array('Detail', 'buka 1 data'),
            'add'     => array('Isi', 'tambah data'),
            'edit'    => array('Ubah', ''),
            'delete'  => array('Hapus', ''),
            'approve' => array('Approve', 'paraf SPV'),
        );
    }
}
