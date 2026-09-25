<?php
/**
 * PATCH LOKAL (14 Sep 2026)
 * File cache ini aslinya di-generate di mesin WINDOWS, jadi semua path font
 * ditulis pakai backslash (contoh: $fontDir . BACKSLASH Helvetica). Di macOS/Linux path itu
 * gak ketemu -> dompdf gagal baca metrik font -> nyembur ratusan
 * "Warning: Undefined array key ...lib/fonts\Helvetica" ke layar. Karena
 * warning-nya ke-echo duluan, header PDF gagal dikirim dan muncul
 * "Unable to stream pdf: headers already sent".
 * Semua backslash diganti forward slash - Windows juga nerima forward slash,
 * jadi file ini tetap aman dipakai di mesin kantor.
 */
return array (
  'sans-serif' => array(
    'normal' => $fontDir . '/Helvetica',
    'bold' => $fontDir . '/Helvetica-Bold',
    'italic' => $fontDir . '/Helvetica-Oblique',
    'bold_italic' => $fontDir . '/Helvetica-BoldOblique',
  ),
  'times' => array(
    'normal' => $fontDir . '/Times-Roman',
    'bold' => $fontDir . '/Times-Bold',
    'italic' => $fontDir . '/Times-Italic',
    'bold_italic' => $fontDir . '/Times-BoldItalic',
  ),
  'times-roman' => array(
    'normal' => $fontDir . '/Times-Roman',
    'bold' => $fontDir . '/Times-Bold',
    'italic' => $fontDir . '/Times-Italic',
    'bold_italic' => $fontDir . '/Times-BoldItalic',
  ),
  'courier' => array(
    'normal' => $fontDir . '/Courier',
    'bold' => $fontDir . '/Courier-Bold',
    'italic' => $fontDir . '/Courier-Oblique',
    'bold_italic' => $fontDir . '/Courier-BoldOblique',
  ),
  'helvetica' => array(
    'normal' => $fontDir . '/Helvetica',
    'bold' => $fontDir . '/Helvetica-Bold',
    'italic' => $fontDir . '/Helvetica-Oblique',
    'bold_italic' => $fontDir . '/Helvetica-BoldOblique',
  ),
  'zapfdingbats' => array(
    'normal' => $fontDir . '/ZapfDingbats',
    'bold' => $fontDir . '/ZapfDingbats',
    'italic' => $fontDir . '/ZapfDingbats',
    'bold_italic' => $fontDir . '/ZapfDingbats',
  ),
  'symbol' => array(
    'normal' => $fontDir . '/Symbol',
    'bold' => $fontDir . '/Symbol',
    'italic' => $fontDir . '/Symbol',
    'bold_italic' => $fontDir . '/Symbol',
  ),
  'serif' => array(
    'normal' => $fontDir . '/Times-Roman',
    'bold' => $fontDir . '/Times-Bold',
    'italic' => $fontDir . '/Times-Italic',
    'bold_italic' => $fontDir . '/Times-BoldItalic',
  ),
  'monospace' => array(
    'normal' => $fontDir . '/Courier',
    'bold' => $fontDir . '/Courier-Bold',
    'italic' => $fontDir . '/Courier-Oblique',
    'bold_italic' => $fontDir . '/Courier-BoldOblique',
  ),
  'fixed' => array(
    'normal' => $fontDir . '/Courier',
    'bold' => $fontDir . '/Courier-Bold',
    'italic' => $fontDir . '/Courier-Oblique',
    'bold_italic' => $fontDir . '/Courier-BoldOblique',
  ),
  'dejavu sans' => array(
    'bold' => $fontDir . '/DejaVuSans-Bold',
    'bold_italic' => $fontDir . '/DejaVuSans-BoldOblique',
    'italic' => $fontDir . '/DejaVuSans-Oblique',
    'normal' => $fontDir . '/DejaVuSans',
  ),
  'dejavu sans mono' => array(
    'bold' => $fontDir . '/DejaVuSansMono-Bold',
    'bold_italic' => $fontDir . '/DejaVuSansMono-BoldOblique',
    'italic' => $fontDir . '/DejaVuSansMono-Oblique',
    'normal' => $fontDir . '/DejaVuSansMono',
  ),
  'dejavu serif' => array(
    'bold' => $fontDir . '/DejaVuSerif-Bold',
    'bold_italic' => $fontDir . '/DejaVuSerif-BoldItalic',
    'italic' => $fontDir . '/DejaVuSerif-Italic',
    'normal' => $fontDir . '/DejaVuSerif',
  ),
  'fontawesome' => array(
    'normal' => $fontDir . '/be9e156733cdcc10462b58d1fbd8f78b',
  ),
) ?>