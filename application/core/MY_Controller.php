<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    protected $user = null;

    public function __construct()
    {
        parent::__construct();
        $this->user = $this->session->userdata('user');
    }

    protected function requireLogin()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
    }

    /**
     * Cek apakah role user sesuai. $roles bisa string atau array.
     */
    protected function requireRole($roles)
    {
        $this->requireLogin();
        $allowed = is_array($roles) ? $roles : [$roles];
        if (!in_array($this->user['role'], $allowed)) {
            show_error('Akses Ditolak: Anda tidak memiliki izin untuk halaman ini.', 403, 'Akses Ditolak');
        }
    }

    protected function isAdmin()
    {
        return $this->user && in_array($this->user['role'], ['admin', 'pimpinan']);
    }

    protected function viewData($extra = [])
    {
        return array_merge(['user' => $this->user], $extra);
    }

    protected function logActivity($action, $description = null)
    {
        if ($this->user) {
            $this->load->model('Activity_log_model');
            $this->Activity_log_model->log(
                $this->user['id'],
                $this->user['name'],
                $action,
                $description,
                $this->input->ip_address()
            );
        }
    }
}
