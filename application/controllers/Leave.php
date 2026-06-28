<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leave extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->load->model('Leave_model');
    }

    /** Daftar pengajuan — user lihat milik sendiri, admin/pimpinan lihat semua */
    public function index()
    {
        $isAdmin   = $this->isAdmin();
        $userId    = $isAdmin ? null : $this->user['id'];
        $status    = $this->input->get('status') ?: null;
        $type      = $this->input->get('type')   ?: null;

        $requests  = $this->Leave_model->getAll($userId, $status, $type);
        $counts    = $this->Leave_model->countByStatus($userId);
        $pending   = $isAdmin ? $this->Leave_model->countPending() : 0;

        $data = $this->viewData([
            'title'     => 'Pengajuan Izin',
            'requests'  => $requests,
            'counts'    => $counts,
            'pending'   => $pending,
            'isAdmin'   => $isAdmin,
            'statusFilter' => $status,
            'typeFilter'   => $type,
        ]);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('leave/index', $data);
        $this->load->view('templates/footer', $data);
    }

    /** Form pengajuan baru — semua role */
    public function create()
    {
        $data = $this->viewData(['title' => 'Ajukan Izin / Cuti']);

        if ($this->input->post()) {
            $startDate = $this->input->post('start_date');
            $endDate   = $this->input->post('end_date');
            $type      = $this->input->post('type');
            $reason    = $this->input->post('reason', TRUE);

            // Validasi dasar
            if (!$startDate || !$endDate || !$reason) {
                $this->session->set_flashdata('error', 'Semua field wajib diisi.');
            } elseif ($endDate < $startDate) {
                $this->session->set_flashdata('error', 'Tanggal selesai tidak boleh sebelum tanggal mulai.');
            } elseif ($this->Leave_model->hasActivePending($this->user['id'], $startDate, $endDate)) {
                $this->session->set_flashdata('error', 'Anda sudah memiliki pengajuan yang sedang menunggu persetujuan pada periode tersebut.');
            } else {
                $this->Leave_model->create([
                    'user_id'    => $this->user['id'],
                    'type'       => $type,
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                    'reason'     => $reason,
                    'status'     => 'pending',
                ]);
                $this->session->set_flashdata('success', 'Pengajuan izin berhasil disubmit. Menunggu persetujuan.');
                redirect('leave');
            }
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('leave/create', $data);
        $this->load->view('templates/footer', $data);
    }

    /** Detail pengajuan */
    public function detail($id)
    {
        $request = $this->Leave_model->getById($id);
        if (!$request) show_404();

        // User hanya bisa lihat milik sendiri
        if (!$this->isAdmin() && $request['user_id'] != $this->user['id']) {
            show_error('Akses Ditolak', 403, 'Akses Ditolak');
        }

        $data = $this->viewData([
            'title'   => 'Detail Pengajuan',
            'request' => $request,
            'isAdmin' => $this->isAdmin(),
        ]);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('leave/detail', $data);
        $this->load->view('templates/footer', $data);
    }

    /** Setujui pengajuan — admin/pimpinan only */
    public function approve($id)
    {
        $this->requireRole(['admin', 'pimpinan']);

        $note   = $this->input->post('review_note', TRUE) ?: '';
        $result = $this->Leave_model->approve($id, $this->user['id'], $note);
        if ($result['success']) {
            $this->logActivity('setujui_izin', "Setujui pengajuan izin id={$id}");
        }
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('leave');
    }

    /** Tolak pengajuan — admin/pimpinan only */
    public function reject($id)
    {
        $this->requireRole(['admin', 'pimpinan']);

        $note   = $this->input->post('review_note', TRUE) ?: '';
        $result = $this->Leave_model->reject($id, $this->user['id'], $note);
        if ($result['success']) {
            $this->logActivity('tolak_izin', "Tolak pengajuan izin id={$id}");
        }
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('leave');
    }
}
