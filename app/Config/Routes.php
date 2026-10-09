<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ============================================================
// PUBLIC ROUTES
// ============================================================
$routes->get('/', 'Landing::index', ['namespace' => 'App\Modules\Landing\Controllers']);
$routes->get('/informasi', 'Landing::informasi', ['namespace' => 'App\Modules\Landing\Controllers']);

// Auth
$routes->group('', ['namespace' => 'App\Modules\Auth\Controllers'], function ($routes) {
    $routes->get('/login',  'Auth::loginForm');
    $routes->post('/login', 'Auth::login');
    $routes->get('/logout', 'Auth::logout');

    $routes->get('/register',       'Register::form');
    $routes->post('/register',      'Register::store');
    $routes->get('/verify-email/(:any)', 'Register::verifyEmail/$1');

    $routes->get('/lupa-password',           'LupaPassword::form');
    $routes->post('/lupa-password',          'LupaPassword::kirim');
    $routes->get('/reset-password/(:any)',   'LupaPassword::resetForm/$1');
    $routes->post('/reset-password/(:any)',  'LupaPassword::resetProses/$1');
});

// ============================================================
// AUTHENTICATED ROUTES
// ============================================================
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('/dashboard', 'Dashboard::index', ['namespace' => 'App\Modules\Dashboard\Controllers']);

    // Pengaturan
    $routes->group('pengaturan', [
        'filter'    => 'role:admin',
        'namespace' => 'App\Modules\Pengaturan\Controllers',
    ], function ($routes) {
        $routes->get('/',     'Pengaturan::index');
        $routes->post('save', 'Pengaturan::save');
    });

    // User
    $routes->group('users', [
        'filter'    => 'role:admin,panitera',
        'namespace' => 'App\Modules\User\Controllers',
    ], function ($routes) {
        $routes->get('/',          'User::index');
        $routes->get('create',     'User::create');
        $routes->post('store',     'User::store');
        $routes->get('edit/(:num)', 'User::edit/$1');
        $routes->post('update/(:num)', 'User::update/$1');
    });

    // Hari Libur
    $routes->group('hari-libur', [
        'filter'    => 'role:admin,panitera',
        'namespace' => 'App\Modules\HariLibur\Controllers',
    ], function ($routes) {
        $routes->get('/',          'HariLibur::index');
        $routes->post('store',     'HariLibur::store');
        $routes->get('delete/(:num)', 'HariLibur::delete/$1');
    });

    // Surat Masuk
    $routes->group('surat-masuk', [
        'filter'    => 'role:admin,panitera',
        'namespace' => 'App\Modules\SuratMasuk\Controllers',
    ], function ($routes) {
        $routes->get('/',          'SuratMasuk::index');
        $routes->get('create',     'SuratMasuk::create');
        $routes->post('store',     'SuratMasuk::store');
        $routes->get('show/(:num)', 'SuratMasuk::show/$1');
    });

    // Perkara
    $routes->group('perkara', [
        'namespace' => 'App\Modules\Perkara\Controllers',
    ], function ($routes) {
        $routes->get('/',          'Perkara::index');
        $routes->get('create',     'Perkara::create');
        $routes->get('show/(:num)', 'Perkara::show/$1');

        // Wizard AJAX
        $routes->post('store-identitas',  'Perkara::storeIdentitas');
        $routes->post('upload-pengaduan', 'Perkara::uploadPengaduan');
        $routes->post('upload-bukti',     'Perkara::uploadBukti');
        $routes->post('hapus-bukti/(:num)', 'Perkara::hapusBukti/$1');
        $routes->get('list-bukti',        'Perkara::listBukti');
        $routes->post('submit',           'Perkara::submit');

        // Verifikasi
        $routes->group('', ['filter' => 'role:admin,panitera'], function ($routes) {
            $routes->get('verifikasi/(:num)',  'Perkara::verifikasiForm/$1');
            $routes->post('verifikasi/(:num)', 'Perkara::verifikasi/$1');
        });
    });

    // Log Aktivitas (admin only)
    $routes->group('log-aktivitas', [
        'filter'    => 'role:admin',
        'namespace' => 'App\Modules\Log\Controllers',
    ], function ($routes) {
        $routes->get('/',         'LogAktivitas::index');
        $routes->get('detail/(:num)', 'LogAktivitas::detail/$1');
        $routes->get('export-csv',    'LogAktivitas::exportCsv');
    });

    // Notifikasi
    $routes->get('/notifikasi/unread', 'Notifikasi::unread', ['namespace' => 'App\Modules\Notifikasi\Controllers']);
});

// ============================================================
// WEBHOOK (tanpa auth)
// ============================================================
$routes->post('pembayaran/callback', 'Pembayaran::callback', ['namespace' => 'App\Modules\Pembayaran\Controllers']);

$routes->group('', ['filter' => 'auth'], function ($r) {

    // Surat Masuk — admin & panitera
    $r->group('surat-masuk', ['filter' => 'role:admin,panitera'], function ($rr) {
        $rr->get('/',           'App\Modules\SuratMasuk\Controllers\SuratMasuk::index');
        $rr->get('create',      'App\Modules\SuratMasuk\Controllers\SuratMasuk::create');
        $rr->post('store',      'App\Modules\SuratMasuk\Controllers\SuratMasuk::store');
        $rr->get('show/(:num)', 'App\Modules\SuratMasuk\Controllers\SuratMasuk::show/$1');
    });

    // Perkara (semua role)
    $r->group('perkara', function ($rr) {
        $rr->get('/',           'App\Modules\Perkara\Controllers\Perkara::index');
        $rr->get('create',      'App\Modules\Perkara\Controllers\Perkara::create');
        $rr->post('store',      'App\Modules\Perkara\Controllers\Perkara::store');
        $rr->get('show/(:num)', 'App\Modules\Perkara\Controllers\Perkara::show/$1');

        // Verifikasi (panitera)
        $rr->group('', ['filter' => 'role:admin,panitera'], function ($rrr) {
            $rrr->get('verifikasi/(:num)',  'App\Modules\Perkara\Controllers\Perkara::verifikasiForm/$1');
            $rrr->post('verifikasi/(:num)', 'App\Modules\Perkara\Controllers\Perkara::verifikasi/$1');
            $rrr->post('tolak/(:num)',      'App\Modules\Perkara\Controllers\Perkara::tolak/$1');
        });
    });
});

$routes->group('', ['filter' => 'auth'], function ($r) {

    // === Surat Masuk ===
    $r->group('surat-masuk', ['filter' => 'role:admin,panitera'], function ($rr) {
        $rr->get('/',           'App\Modules\SuratMasuk\Controllers\SuratMasuk::index');
        $rr->get('create',      'App\Modules\SuratMasuk\Controllers\SuratMasuk::create');
        $rr->post('store',      'App\Modules\SuratMasuk\Controllers\SuratMasuk::store');
        $rr->get('show/(:num)', 'App\Modules\SuratMasuk\Controllers\SuratMasuk::show/$1');
    });

    // === Perkara ===
    $r->group('perkara', function ($rr) {
        $rr->get('/',           'App\Modules\Perkara\Controllers\Perkara::index');
        $rr->get('create',      'App\Modules\Perkara\Controllers\Perkara::create');
        $rr->get('show/(:num)', 'App\Modules\Perkara\Controllers\Perkara::show/$1');

        // Wizard endpoints (AJAX)
        $rr->post('store-identitas',   'App\Modules\Perkara\Controllers\Perkara::storeIdentitas');
        $rr->post('upload-pengaduan',  'App\Modules\Perkara\Controllers\Perkara::uploadPengaduan');
        $rr->post('upload-bukti',      'App\Modules\Perkara\Controllers\Perkara::uploadBukti');
        $rr->post('hapus-bukti/(:num)','App\Modules\Perkara\Controllers\Perkara::hapusBukti/$1');
        $rr->get ('list-bukti',        'App\Modules\Perkara\Controllers\Perkara::listBukti');
        $rr->post('submit',            'App\Modules\Perkara\Controllers\Perkara::submit');

        // Verifikasi (panitera)
        $rr->group('', ['filter' => 'role:admin,panitera'], function ($rrr) {
            $rrr->get ('verifikasi/(:num)', 'App\Modules\Perkara\Controllers\Perkara::verifikasiForm/$1');
            $rrr->post('verifikasi/(:num)', 'App\Modules\Perkara\Controllers\Perkara::verifikasi/$1');
        });
    });
});

// Register
$routes->get('/register',        'App\Modules\Auth\Controllers\Register::form');
$routes->post('/register',       'App\Modules\Auth\Controllers\Register::store');
$routes->get('/verify-email/(:any)', 'App\Modules\Auth\Controllers\Register::verifyEmail/$1');

// Lupa Password
$routes->get('/lupa-password',           'App\Modules\Auth\Controllers\LupaPassword::form');
$routes->post('/lupa-password',          'App\Modules\Auth\Controllers\LupaPassword::kirim');
$routes->get('/reset-password/(:any)',   'App\Modules\Auth\Controllers\LupaPassword::resetForm/$1');
$routes->post('/reset-password/(:any)',  'App\Modules\Auth\Controllers\LupaPassword::resetProses/$1');

$routes->group('pengaturan', ['filter' => 'role:admin'], function ($r) {
    $r->get('/',     'App\Modules\Pengaturan\Controllers\Pengaturan::index');
    $r->post('save', 'App\Modules\Pengaturan\Controllers\Pengaturan::save');
});

$routes->get('dokumen/download/(:num)', 'App\Modules\Perkara\Controllers\Dokumen::download/$1');

// === PUBLIC ===
$routes->get('/register',               'App\Modules\Auth\Controllers\Register::form');
$routes->post('/register',              'App\Modules\Auth\Controllers\Register::store');
$routes->get('/verify-email/(:any)',    'App\Modules\Auth\Controllers\Register::verifyEmail/$1');

$routes->get('/lupa-password',          'App\Modules\Auth\Controllers\LupaPassword::form');
$routes->post('/lupa-password',         'App\Modules\Auth\Controllers\LupaPassword::kirim');
$routes->get('/reset-password/(:any)',  'App\Modules\Auth\Controllers\LupaPassword::resetForm/$1');
$routes->post('/reset-password/(:any)', 'App\Modules\Auth\Controllers\LupaPassword::resetProses/$1');

// === AUTH ===
$routes->group('', ['filter' => 'auth'], function ($r) {
    $r->get('dokumen/download/(:num)', 'App\Modules\Perkara\Controllers\Dokumen::download/$1');

    // Pengaturan Sistem (admin only)
    $r->group('pengaturan', ['filter' => 'role:admin'], function ($rr) {
        $rr->get('/',     'App\Modules\Pengaturan\Controllers\Pengaturan::index');
        $rr->post('save', 'App\Modules\Pengaturan\Controllers\Pengaturan::save');
    });
});

$routes->group('', ['filter' => 'auth'], function ($r) {

    // === Kelola User ===
    $r->group('users', ['filter' => 'role:admin,panitera'], function ($rr) {
        $rr->get('/',                    'App\Modules\User\Controllers\User::index');
        $rr->get('create',               'App\Modules\User\Controllers\User::create');
        $rr->post('store',               'App\Modules\User\Controllers\User::store');
        $rr->get('edit/(:num)',          'App\Modules\User\Controllers\User::edit/$1');
        $rr->post('update/(:num)',       'App\Modules\User\Controllers\User::update/$1');
        $rr->get('delete/(:num)',        'App\Modules\User\Controllers\User::delete/$1');
        $rr->get('toggle-status/(:num)', 'App\Modules\User\Controllers\User::toggleStatus/$1');
        $rr->get('resend-verification/(:num)', 'App\Modules\User\Controllers\User::resendVerification/$1');
    });

    // === Chat per Perkara ===
    $r->get('perkara/chat/list/(:num)',           'App\Modules\Perkara\Controllers\Chat::list/$1');
    $r->post('perkara/chat/kirim/(:num)',         'App\Modules\Perkara\Controllers\Chat::kirim/$1');
    $r->post('perkara/chat/hapus/(:num)',         'App\Modules\Perkara\Controllers\Chat::hapus/$1');
    $r->get('perkara/chat/unread/(:num)',         'App\Modules\Perkara\Controllers\Chat::unreadCount/$1');
    $r->get('dokumen/download-chat/(:num)',       'App\Modules\Perkara\Controllers\Chat::downloadChatFile/$1');
});