<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $request;
    protected $helpers = ['url', 'form', 'harikerja', 'auth', 'register'];
    protected $session;
    protected $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->session = \Config\Services::session();
        $this->db      = \Config\Database::connect();

        // Share data ke semua view
        $user = [
            'id'           => $this->session->get('user_id'),
            'nama'         => $this->session->get('nama'),
            'username'     => $this->session->get('username'),
            'email'        => $this->session->get('email'),
            'primary_role' => $this->session->get('primary_role'),
            'is_advokat'   => $this->session->get('is_advokat'),
        ];
        \Config\Services::renderer()->setData(['currentUser' => $user]);
    }
}