<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Finance extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireRole(['admin', 'pimpinan']);
        $this->load->model('Finance_model');
    }

    public function index()
    {
        $startDate = $this->input->get('start_date') ?: date('Y-m-01');
        $endDate   = $this->input->get('end_date')   ?: date('Y-m-d');
        $type      = $this->input->get('type')       ?: null;

        $summary    = $this->Finance_model->getSummary($startDate, $endDate);
        $records    = $this->Finance_model->getAll($startDate, $endDate, $type);
        $monthly    = $this->Finance_model->getMonthlySummary();

        $data = $this->viewData([
            'title'      => 'Keuangan',
            'summary'    => $summary,
            'records'    => $records,
            'monthly'    => $monthly,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'type_filter'=> $type,
        ]);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('finance/index', $data);
        $this->load->view('templates/footer', $data);
    }

    public function add()
    {
        if ($this->input->post()) {
            $data = [
                'type'        => $this->input->post('type'),
                'category'    => $this->input->post('category', TRUE),
                'amount'      => (float) $this->input->post('amount'),
                'description' => $this->input->post('description', TRUE),
                'date'        => $this->input->post('date'),
                'created_by'  => $this->user['id'],
            ];

            if ($data['amount'] > 0 && $data['date']) {
                $this->Finance_model->add($data);
                $this->logActivity('tambah_keuangan', "Tambah {$data['type']}: Rp " . number_format($data['amount'], 0, ',', '.'));
                $this->session->set_flashdata('success', 'Data keuangan berhasil disimpan.');
            } else {
                $this->session->set_flashdata('error', 'Jumlah atau tanggal tidak valid.');
            }
        }
        redirect('finance');
    }

    public function delete($id)
    {
        $this->requireRole(['admin']);
        $this->Finance_model->delete($id);
        $this->logActivity('hapus_keuangan', "Hapus data keuangan id={$id}");
        $this->session->set_flashdata('success', 'Data keuangan berhasil dihapus.');
        redirect('finance');
    }
}
