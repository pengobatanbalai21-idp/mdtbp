<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class P3k extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->load->model('P3k_model');
    }

    /** Halaman utama: lihat stok + form WD — semua role */
    public function index()
    {
        $kits = $this->P3k_model->getAllKits();

        $data = $this->viewData([
            'title' => 'P3K (Pertolongan Pertama)',
            'kits'  => $kits,
        ]);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('p3k/index', $data);
        $this->load->view('templates/footer', $data);
    }

    /** Tambah stok — admin/pimpinan only */
    public function add_stock()
    {
        $this->requireRole(['admin', 'pimpinan']);

        $kitId = (int) $this->input->post('kit_id');
        $qty   = (int) $this->input->post('quantity');

        $result = $this->P3k_model->addStock($kitId, $qty);
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('p3k');
    }

    /** Edit deskripsi kit — admin/pimpinan only */
    public function edit_kit($id)
    {
        $this->requireRole(['admin', 'pimpinan']);

        if ($this->input->post()) {
            $data = [
                'description' => $this->input->post('description', TRUE),
                'min_stock'   => (int) $this->input->post('min_stock'),
            ];
            $this->P3k_model->updateKit($id, $data);
            $this->session->set_flashdata('success', 'Data kit P3K berhasil diperbarui.');
        }
        redirect('p3k');
    }

    /** WD P3K — semua role */
    public function withdraw()
    {
        if ($this->input->post()) {
            $kitId    = (int) $this->input->post('kit_id');
            $qty      = (int) $this->input->post('quantity');
            $purpose  = $this->input->post('purpose', TRUE);
            $notes    = $this->input->post('notes', TRUE);

            $result = $this->P3k_model->withdraw($this->user['id'], $kitId, $qty, $purpose, $notes);
            if ($result['success']) {
                $this->logActivity('wd_p3k', "WD P3K kit_id={$kitId}, qty={$qty}");
            }
            $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        }
        redirect('p3k');
    }

    /** Verifikasi/checklist 1 WD P3K — admin/pimpinan only */
    public function check($id)
    {
        $this->requireRole(['admin', 'pimpinan']);
        $res = $this->P3k_model->toggleCheck($id, $this->user['id']);
        if ($res === null) {
            $this->session->set_flashdata('error', 'Data WD P3K tidak ditemukan.');
        } else {
            $this->logActivity('verifikasi_wd_p3k', "WD P3K id={$id} " . ($res ? 'dicentang' : 'dilepas'));
            $this->session->set_flashdata('success', $res ? 'WD P3K terverifikasi.' : 'Verifikasi dilepas.');
        }
        $this->backRedirect('p3k/history');
    }

    /** Riwayat WD — user lihat milik sendiri, admin/pimpinan lihat semua */
    public function history()
    {
        $isAdmin   = in_array($this->user['role'], ['admin', 'pimpinan']);
        $userId    = $isAdmin ? null : $this->user['id'];
        $startDate = $this->input->get('start_date') ?: date('Y-m-01');
        $endDate   = $this->input->get('end_date')   ?: date('Y-m-d');
        $kitFilter = $this->input->get('kit_id')     ?: null;

        // Terapkan filter kit pada model query dengan kondisi ekstra
        $history = $this->P3k_model->getHistory($userId, $startDate, $endDate);

        // Filter by kit di PHP (untuk simplisitas)
        if ($kitFilter) {
            $history = array_filter($history, function($r) use ($kitFilter) {
                return $r['kit_id'] == $kitFilter;
            });
        }

        $kits = $this->P3k_model->getAllKits();

        $data = $this->viewData([
            'title'      => 'Riwayat WD P3K',
            'history'    => $history,
            'kits'       => $kits,
            'isAdmin'    => $isAdmin,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'kit_filter' => $kitFilter,
        ]);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('p3k/history', $data);
        $this->load->view('templates/footer', $data);
    }
}
