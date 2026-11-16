<?php

namespace App\Support;

/**
 * Thin wrapper around barryvdh/laravel-dompdf so the rest of the app still works
 * (and explains itself) when the package has not been installed yet:
 *     composer require barryvdh/laravel-dompdf
 */
class Pdf
{
    public static function available(): bool
    {
        return class_exists(\Barryvdh\DomPDF\ServiceProvider::class) && app()->bound('dompdf.wrapper');
    }

    public static function make(string $view, array $data = [], string $orientation = 'portrait')
    {
        return app('dompdf.wrapper')->loadView($view, $data)->setPaper('a4', $orientation);
    }
}
