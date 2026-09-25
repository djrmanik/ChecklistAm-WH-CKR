<?php
/**
 * ============================================================================
 *  PROFIL MESIN - PALLET STACKER (1 unit)              dibuat 23 Sep 2026
 *
 *  Isi item SAMA PERSIS dengan pallet mover (satu halaman input & satu tabel
 *  DB `palletmover`, dibedakan kolom no_palletmover = 'Pallet Stacker'), jadi
 *  profil ini cuma menimpa identitas mesinnya. Ubah item di palletmover.php.
 * ============================================================================
 */

$conf = include __DIR__ . '/palletmover.php';

$conf['key']          = 'palletstacker';
$conf['machine_code'] = 'Pallet Stacker';
$conf['title']        = 'Checklist AM Pallet Stacker';
$conf['file_slug']    = 'Pallet-Stacker';
$conf['units']        = array(
    array('label' => 'Pallet Stacker', 'alias' => array('Pallet Stacker')),
);

return $conf;
