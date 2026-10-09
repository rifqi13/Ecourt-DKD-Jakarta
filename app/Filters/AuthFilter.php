<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Paksa ganti password jika flag aktif
        if (session()->get('must_change_password')) {
            $allow = ['/ganti-password', '/logout'];
            $uri   = '/' . ltrim($request->getUri()->getPath(), '/');
            if (!in_array($uri, $allow, true)) {
                return redirect()->to('/ganti-password');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}