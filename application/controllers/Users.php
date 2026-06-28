<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireRole(['admin', 'pimpinan']);
        $this->load->model('User_model');
    }

    public function index()
    {
        $search = trim($this->input->get('search') ?? '');
        $users  = $this->User_model->getAll();

        if ($search !== '') {
            $users = array_values(array_filter($users, function($u) use ($search) {
                return stripos($u['name'], $search) !== false
                    || stripos($u['username'], $search) !== false;
            }));
        }

        $data = $this->viewData([
            'title'  => 'Manajemen Pengguna',
            'users'  => $users,
            'search' => $search,
        ]);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('users/index', $data);
        $this->load->view('templates/footer', $data);
    }

    public function create()
    {
        $data = $this->viewData(['title' => 'Tambah Pengguna', 'edit' => false, 'editUser' => null]);

        if ($this->input->post()) {
            $username = $this->input->post('username', TRUE);

            if ($this->User_model->usernameExists($username)) {
                $this->session->set_flashdata('error', 'Username sudah digunakan.');
            } elseif ($this->input->post('password') !== $this->input->post('password_confirm')) {
                $this->session->set_flashdata('error', 'Konfirmasi password tidak cocok.');
            } else {
                $this->User_model->create([
                    'name'     => $this->input->post('name', TRUE),
                    'username' => $username,
                    'email'    => $this->input->post('email', TRUE),
                    'password' => $this->input->post('password'),
                    'role'     => $this->input->post('role'),
                    'jabatan'  => $this->input->post('jabatan'),
                    'status'   => 1,
                ]);
                $this->logActivity('tambah_pengguna', "Tambah pengguna: {$username}");
                $this->session->set_flashdata('success', 'Pengguna berhasil ditambahkan.');
                redirect('users');
            }
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('users/form', $data);
        $this->load->view('templates/footer', $data);
    }

    public function edit($id)
    {
        $editUser = $this->User_model->getById($id);
        if (!$editUser) show_404();

        $data = $this->viewData(['title' => 'Edit Pengguna', 'edit' => true, 'editUser' => $editUser]);

        if ($this->input->post()) {
            $username = $this->input->post('username', TRUE);

            if ($this->User_model->usernameExists($username, $id)) {
                $this->session->set_flashdata('error', 'Username sudah digunakan.');
            } else {
                $pw      = $this->input->post('password');
                $pwConf  = $this->input->post('password_confirm');

                if ($pw && $pw !== $pwConf) {
                    $this->session->set_flashdata('error', 'Konfirmasi password tidak cocok.');
                } else {
                    $this->User_model->update($id, [
                        'name'     => $this->input->post('name', TRUE),
                        'username' => $username,
                        'email'    => $this->input->post('email', TRUE),
                        'password' => $pw,
                        'role'     => $this->input->post('role'),
                        'jabatan'  => $this->input->post('jabatan'),
                        'status'   => (int) $this->input->post('status'),
                    ]);
                    $this->logActivity('edit_pengguna', "Edit pengguna: {$username} (id={$id})");
                    $this->session->set_flashdata('success', 'Data pengguna berhasil diperbarui.');
                    redirect('users');
                }
            }
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('users/form', $data);
        $this->load->view('templates/footer', $data);
    }

    public function delete($id)
    {
        $this->requireRole(['admin']);
        if ($id == $this->user['id']) {
            $this->session->set_flashdata('error', 'Anda tidak dapat menghapus akun sendiri.');
        } else {
            $del = $this->User_model->getById($id);
            $this->User_model->delete($id);
            $this->logActivity('hapus_pengguna', 'Hapus pengguna: ' . ($del['username'] ?? $id));
            $this->session->set_flashdata('success', 'Pengguna berhasil dihapus.');
        }
        redirect('users');
    }

    public function toggle_status($id)
    {
        $user = $this->User_model->getById($id);
        if ($user) {
            $this->User_model->update($id, ['status' => $user['status'] ? 0 : 1]);
            $this->session->set_flashdata('success', 'Status pengguna diperbarui.');
        }
        redirect('users');
    }
}
