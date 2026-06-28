<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Auth_model', 'Activity_log_model']);
    }

    public function index()
    {
        if ($this->session->userdata('logged_in')) {
            $this->_redirectByRole();
        }
        redirect('auth/login');
    }

    public function login()
    {
        if ($this->session->userdata('logged_in')) {
            $this->_redirectByRole();
        }

        $data = ['error' => ''];

        if ($this->input->post()) {
            $username = $this->input->post('username', TRUE);
            $password = $this->input->post('password');

            $user = $this->Auth_model->login($username, $password);

            if ($user) {
                $this->session->set_userdata([
                    'logged_in' => true,
                    'user'      => [
                        'id'       => $user['id'],
                        'name'     => $user['name'],
                        'username' => $user['username'],
                        'email'    => $user['email'],
                        'role'     => $user['role'],
                        'jabatan'  => $user['jabatan'],
                    ],
                ]);

                $this->Activity_log_model->log(
                    $user['id'], $user['name'], 'login',
                    'Login berhasil',
                    $this->input->ip_address()
                );

                $this->_redirectByRole($user['role']);
            } else {
                $data['error'] = 'Username atau password salah.';
            }
        }

        $this->load->view('auth/login', $data);
    }

    public function logout()
    {
        $user = $this->session->userdata('user');
        if ($user) {
            $this->Activity_log_model->log(
                $user['id'], $user['name'], 'logout',
                'Logout dari sistem',
                $this->input->ip_address()
            );
        }
        $this->session->sess_destroy();
        redirect('auth/login');
    }

    private function _redirectByRole($role = null)
    {
        $role = $role ?: ($this->session->userdata('user')['role'] ?? 'user');
        redirect('dashboard');
    }
}
