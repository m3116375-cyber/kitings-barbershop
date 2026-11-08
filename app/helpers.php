<?php

use Carbon\Carbon;

if (! function_exists('rupiah')) {
    function rupiah($amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('tgl')) {
    /** Format tanggal berbahasa Indonesia, contoh: tgl('2026-10-20') => 20 Okt 2026 */
    function tgl($date, string $format = 'd M Y'): string
    {
        if (! $date) {
            return '-';
        }
        return Carbon::parse($date)->locale('id')->translatedFormat($format);
    }
}

if (! function_exists('jam')) {
    /** Potong detik dari format jam: 09:00:00 => 09:00 */
    function jam($time): string
    {
        return $time ? substr((string) $time, 0, 5) : '-';
    }
}
