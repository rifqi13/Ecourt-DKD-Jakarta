<?php
namespace Config;

use CodeIgniter\Config\AutoloadConfig;

class Autoload extends AutoloadConfig
{
    public $psr4 = [
        APP_NAMESPACE => APPPATH,
        'App\Modules' => APPPATH . 'Modules',   // ← TAMBAHKAN
        'Config'      => APPPATH . 'Config',
    ];

    public $classmap = [];
    public $files    = [];
    public $helpers = ['url', 'form', 'harikerja', 'register', 'auth', 'setting', 'mailer', 'akun'];
}