<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Medicines extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->load->model('Medicine_model');
    }

    /** Stok obat - semua role bisa lihat, hanya admin/pimpinan bisa tambah stok */
    public function index()
    {
        $medicines = $this->Medicine_model->getAllMedicines();
        $packages  = $this->Medicine_model->getAllPackages();

        foreach ($packages as &$pkg) {
            $pkg['items'] = $this->Medicine_model->getPackageItems($pkg['id']);
        }

        $data = $this->viewData([
            'title'     => 'Manajemen Obat',
            'medicines' => $medicines,
            'packages'  => $packages,
        ]);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('medicines/index', $data);
        $this->load->view('templates/footer', $data);
    }

    /** Tambah stok - admin/pimpinan only */
    public function add_stock()
    {
        $this->requireRole(['admin', 'pimpinan']);

        $id  = $this->input->post('medicine_id');
        $qty = (int) $this->input->post('quantity');

        if ($id && $qty > 0) {
            $this->Medicine_model->updateStock($id, $qty, 'add');
            $this->logActivity('tambah_stok_obat', "Tambah stok medicine_id={$id} qty={$qty}");
            $this->session->set_flashdata('success', "Stok berhasil ditambah sebanyak {$qty}.");
        } else {
            $this->session->set_flashdata('error', 'Data tidak valid.');
        }
        redirect('medicines');
    }

    /** Kurangi stok - admin/pimpinan only */
    public function reduce_stock()
    {
        $this->requireRole(['admin', 'pimpinan']);

        $id  = $this->input->post('medicine_id');
        $qty = (int) $this->input->post('quantity');

        if (!$id || $qty <= 0) {
            $this->session->set_flashdata('error', 'Data tidak valid.');
            redirect('medicines');
            return;
        }

        $med = $this->Medicine_model->getMedicineById($id);
        if (!$med) {
            $this->session->set_flashdata('error', 'Obat tidak ditemukan.');
        } elseif ($qty > $med['stock']) {
            $this->session->set_flashdata('error', "Jumlah melebihi stok {$med['name']} (tersisa {$med['stock']}).");
        } else {
            $this->Medicine_model->updateStock($id, $qty, 'subtract');
            $this->logActivity('kurang_stok_obat', "Kurangi stok medicine_id={$id} qty={$qty}");
            $this->session->set_flashdata('success', "Stok {$med['name']} berhasil dikurangi sebanyak {$qty}.");
        }
        redirect('medicines');
    }

    /** Halaman jual/WD obat — admin/pimpinan only */
    public function sell()
    {
        $this->requireRole(['admin', 'pimpinan']);
        $medicines = $this->Medicine_model->getAllMedicines();
        $packages  = $this->Medicine_model->getAllPackages();

        foreach ($packages as &$pkg) {
            $pkg['items'] = $this->Medicine_model->getPackageItems($pkg['id']);
        }

        if ($this->input->post()) {
            $type        = $this->input->post('sale_type');
            $patientName = $this->input->post('patient_name', TRUE);
            $quantity    = (int) $this->input->post('quantity');
            $notes       = $this->input->post('notes', TRUE);
            $userId      = $this->user['id'];

            if ($type === 'package') {
                $packageId = (int) $this->input->post('package_id');
                $result    = $this->Medicine_model->sellPackage($userId, $packageId, $patientName, $quantity, $notes);
            } else {
                $medicineId = (int) $this->input->post('medicine_id');
                $result     = $this->Medicine_model->sellItem($userId, $medicineId, $patientName, $quantity, $notes);
            }

            if ($result['success']) {
                $this->logActivity('jual_obat', "Jual/WD obat untuk pasien: {$patientName}, qty: {$quantity}");
            }
            $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
            redirect('medicines/sell');
        }

        $data = $this->viewData([
            'title'     => 'Penjualan / WD Obat',
            'medicines' => $medicines,
            'packages'  => $packages,
        ]);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('medicines/sell', $data);
        $this->load->view('templates/footer', $data);
    }

    /** Verifikasi/checklist 1 transaksi penjualan — admin/pimpinan only */
    public function check_sale($id)
    {
        $this->requireRole(['admin', 'pimpinan']);
        $res = $this->Medicine_model->toggleSaleCheck($id, $this->user['id']);
        if ($res === null) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
        } else {
            $this->logActivity('verifikasi_penjualan', "Penjualan id={$id} " . ($res ? 'dicentang' : 'dilepas'));
            $this->session->set_flashdata('success', $res ? 'Transaksi terverifikasi.' : 'Verifikasi dilepas.');
        }
        $this->backRedirect('medicines/history');
    }

    /** Riwayat penjualan — admin/pimpinan only */
    public function history()
    {
        $this->requireRole(['admin', 'pimpinan']);
        $isAdmin   = true;
        $userId    = null;
        $startDate = $this->input->get('start_date') ?: date('Y-m-01');
        $endDate   = $this->input->get('end_date')   ?: date('Y-m-d');
        $sales     = $this->Medicine_model->getSalesHistory(100, 0, $userId);

        // Filter tanggal di PHP
        $sales = array_filter($sales, function($s) use ($startDate, $endDate) {
            $d = date('Y-m-d', strtotime($s['created_at']));
            return $d >= $startDate && $d <= $endDate;
        });

        $data = $this->viewData([
            'title'      => 'Riwayat Penjualan Obat',
            'sales'      => $sales,
            'isAdmin'    => $isAdmin,
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('medicines/history', $data);
        $this->load->view('templates/footer', $data);
    }
}
