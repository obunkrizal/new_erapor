<?php

if (!function_exists('formatRupiah')) {
    /**
     * Format number to Indonesian Rupiah currency format.
     *
     * @param float|int $amount
     * @return string
     */
    function formatRupiah($amount)
    {
        if (!is_numeric($amount)) {
            return $amount;
        }
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('terbilang')) {
    /**
     * Convert number to Indonesian words (terbilang).
     *
     * @param int $number
     * @return string
     */
    function terbilang($number)
    {
        $number = abs($number);
        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($number < 12) {
            return $words[$number];
        } elseif ($number < 20) {
            return terbilang($number - 10) . ' belas';
        } elseif ($number < 100) {
            return terbilang(intval($number / 10)) . ' puluh ' . terbilang($number % 10);
        } elseif ($number < 200) {
            return 'seratus ' . terbilang($number - 100);
        } elseif ($number < 1000) {
            return terbilang(intval($number / 100)) . ' ratus ' . terbilang($number % 100);
        } elseif ($number < 2000) {
            return 'seribu ' . terbilang($number - 1000);
        } elseif ($number < 1000000) {
            return terbilang(intval($number / 1000)) . ' ribu ' . terbilang($number % 1000);
        } elseif ($number < 1000000000) {
            return terbilang(intval($number / 1000000)) . ' juta ' . terbilang($number % 1000000);
        } elseif ($number < 1000000000000) {
            return terbilang(intval($number / 1000000000)) . ' miliar ' . terbilang($number % 1000000000);
        } elseif ($number < 1000000000000000) {
            return terbilang(intval($number / 1000000000000)) . ' triliun ' . terbilang($number % 1000000000000);
        } else {
            return 'Jumlah terlalu besar';
        }
    }
}
