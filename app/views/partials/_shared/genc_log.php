<?php
/**
 * GENERASI C - pencatat aktivitas ke tabel `app_logs`         [GENC-24SEP26-SHELL]
 *
 * Tabel app_logs bawaan phpRAD sudah ada tapi tidak diisi lagi sejak Okt 2024
 * (entri terakhir 24 Okt 2024). Fungsi ini mengisinya lagi untuk kejadian penting:
 * login (berhasil/gagal/diblokir), logout, isi/ubah/hapus/approve checklist,
 * perubahan user, role & hak akses.
 *
 * Aman:
 *  - koneksi DB sendiri -> tidak mengganggu query/pesan error controller
 *  - kolom dicek dulu (SHOW COLUMNS); kolom yang tidak ada dilewati, kolom NOT NULL diisi ''
 *  - gagal mencatat TIDAK menggagalkan aksi user (semua error ditelan)
 *  - password tidak pernah ditulis (kunci berisi "pass" dibuang)
 */
if (!function_exists('genc_app_log')) {
    function genc_app_log($action, $table, $record_id, $message, $data = array(), $user_id = null) {
        static $cols = null;
        try {
            $db = new PDODb(DB_TYPE, DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT, DB_CHARSET);
            if ($cols === null) {
                $cols = array();
                $res = $db->rawQuery("SHOW COLUMNS FROM app_logs");
                if (is_array($res)) { foreach ($res as $c) { $cols[strtolower($c['Field'])] = $c; } }
            }
            if (empty($cols)) { return false; }
            $clean = function ($v) {
                $v = (string) $v;
                $v = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '?', $v);   // koneksi utf8 3-byte (K26)
                return $v === null ? '' : $v;
            };
            $safe = array();
            foreach ((array) $data as $k => $v) {
                if (stripos((string) $k, 'pass') !== false || stripos((string) $k, 'csrf') !== false) { continue; }
                $safe[$k] = is_scalar($v) || $v === null ? $v : json_encode($v);
            }
            $row = array(
                'timestamp'        => date('Y-m-d H:i:s'),
                'action'           => $action,
                'tablename'        => $table,
                'recordid'         => (string) $record_id,
                'sqlquery'         => '',
                'userid'           => (string) ($user_id !== null ? $user_id : (defined('USER_ID') ? USER_ID : '')),
                'serverip'         => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                'requesturl'       => (string) Router::$page_url,
                'requestdata'      => json_encode($safe, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'requestcompleted' => 'true',
                'requestmsg'       => $message,
            );
            $ins = array();
            foreach ($cols as $lc => $c) {
                if (strtolower((string) $c['Extra']) === 'auto_increment') { continue; }
                if (array_key_exists($lc, $row)) {
                    $v = $clean($row[$lc]);
                    if (preg_match('/char\((\d+)\)/i', $c['Type'], $m)) { $v = function_exists('mb_substr') ? mb_substr($v, 0, (int) $m[1], 'UTF-8') : substr($v, 0, (int) $m[1]); }
                    $ins[$c['Field']] = $v;
                } elseif ($c['Null'] === 'NO' && $c['Default'] === null) {
                    $ins[$c['Field']] = preg_match('/int|decimal|float|double/i', $c['Type']) ? 0 : '';
                }
            }
            return (bool) $db->insert('app_logs', $ins);
        } catch (Exception $e) {
            return false;
        } catch (Error $e) {
            return false;
        }
    }
}
