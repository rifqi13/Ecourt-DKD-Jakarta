<?php
namespace App\Modules\Landing\Controllers;

use App\Controllers\BaseController;

class Landing extends BaseController
{
    public function index()
    {
        return view('App\Modules\Landing\Views\index', [
            'title' => 'Beranda',
        ]);
    }

    public function informasi()
    {
        return view('App\Modules\Landing\Views\informasi', [
            'title' => 'Informasi & Alur',
        ]);
    }
}