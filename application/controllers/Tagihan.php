<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Rekap Tagihan: denda absensi mingguan + tagihan penjualan obat.
 * Role 'user' hanya lihat miliknya sendiri; 'admin'/'pimpinan' lihat semua
 * dan bisa menandai lunas dengan keterangan pembayaran.
 */
class Tagihan extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->load->model('Attendance_model');
        $this->load->model('Medicine_model');
    }

    public function index()
    {
        $this->load->model('User_model');

        $isAdmin = $this->isAdmin();
        $year    = (int)($this->input->get('year') ?: date('Y'));

        $monthFrom = (int)($this->input->get('month_from') ?: 1);
        $monthTo   = (int)($this->input->get('month_to')   ?: 12);
        $monthFrom = min(12, max(1, $monthFrom));
        $monthTo   = min(12, max(1, $monthTo));
        if ($monthFrom > $monthTo) {
            [$monthFrom, $monthTo] = [$monthTo, $monthFrom];
        }
        $startDate = date('Y-m-01', mktime(0, 0, 0, $monthFrom, 1, $year));
        $endDate   = date('Y-m-t',  mktime(0, 0, 0, $monthTo, 1, $year));

        $userId     = $isAdmin ? ($this->input->get('user_id') ?: null) : $this->user['id'];
        $nameSearch = $isAdmin ? trim($this->input->get('name_search') ?: '') : '';

        // ── Denda absensi mingguan ──
        $recap       = $this->Attendance_model->getWeeklyRecap($userId, $startDate, $endDate);
        $payMap      = $this->Attendance_model->getFinePaymentMap();
        $minHours    = 21;
        $finePerHour = 150000;
        $denda       = [];
        $totalDendaBelumBayar = 0;

        foreach ($recap as $row) {
            $row['total_jam'] = round($row['total_jam'], 2);
            $kurang           = max(0, $minHours - $row['total_jam']);
            $amount           = $kurang * $finePerHour;
            if ($amount <= 0) {
                continue; // hanya baris yang benar-benar berdenda
            }
            $key  = $row['user_id'] . '_' . $row['tahun'] . '_' . $row['minggu'];
            $pay  = $payMap[$key] ?? null;

            $row['amount']     = $amount;
            $row['paid']       = (bool)$pay;
            $row['payer_name'] = $pay['payer_name'] ?? null;
            $row['paid_at']    = $pay['paid_at'] ?? null;
            $row['note']       = $pay['note'] ?? null;

            if (!$row['paid']) {
                $totalDendaBelumBayar += $amount;
            }
            $denda[] = $row;
        }

        if ($nameSearch !== '') {
            $denda = array_values(array_filter($denda, function ($row) use ($nameSearch) {
                return stripos($row['name'], $nameSearch) !== false;
            }));
        }

        // ── Tagihan penjualan ──
        $sales = $this->Medicine_model->getSalesForBilling($userId);
        $sales = array_values(array_filter($sales, function ($s) use ($startDate, $endDate) {
            $d = date('Y-m-d', strtotime($s['created_at']));
            return $d >= $startDate && $d <= $endDate;
        }));

        if ($nameSearch !== '') {
            $sales = array_values(array_filter($sales, function ($s) use ($nameSearch) {
                return stripos($s['petugas'], $nameSearch) !== false;
            }));
        }

        $totalSalesBelumBayar = 0;
        $totalPaketQty        = 0;
        $totalObatQty         = 0;
        foreach ($sales as &$s) {
            if (!$s['is_paid']) {
                $totalSalesBelumBayar += $s['total_price'];
            }
            if ($s['sale_type'] === 'package') {
                $totalPaketQty += (int)$s['quantity'];
            } else {
                $totalObatQty += (int)$s['quantity'];
            }
        }
        unset($s);

        $users = $isAdmin ? $this->User_model->getAll() : [];

        $data = $this->viewData([
            'title'        => 'Rekap Tagihan',
            'denda'        => $denda,
            'sales'        => $sales,
            'users'        => $users,
            'is_admin'     => $isAdmin,
            'year'         => $year,
            'month_from'   => $monthFrom,
            'month_to'     => $monthTo,
            'name_search'  => $nameSearch,
            'user_filter'  => $userId,
            'min_hours'    => $minHours,
            'fine_per_hour'=> $finePerHour,
            'total_denda_belum_bayar' => $totalDendaBelumBayar,
            'total_sales_belum_bayar' => $totalSalesBelumBayar,
            'total_paket_qty'         => $totalPaketQty,
            'total_obat_qty'          => $totalObatQty,
        ]);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('tagihan/index', $data);
        $this->load->view('templates/footer', $data);
    }

    /** Toggle lunas denda absensi satu minggu, dengan keterangan — admin/pimpinan only */
    public function pay_fine()
    {
        $this->requireRole(['admin', 'pimpinan']);

        $userId = (int)$this->input->post('user_id');
        $tahun  = (int)$this->input->post('tahun');
        $minggu = (int)$this->input->post('minggu');
        $note   = $this->input->post('note', TRUE);
        $undo   = (int)$this->input->post('undo');

        if ($userId && $tahun && $minggu) {
            if ($undo) {
                $this->Attendance_model->unmarkFinePaid($userId, $tahun, $minggu);
                $this->logActivity('batal_lunas_denda', "Denda user={$userId} {$tahun}W{$minggu} dibatalkan");
                $this->session->set_flashdata('success', 'Status lunas dibatalkan.');
            } else {
                $this->Attendance_model->markFinePaid($userId, $tahun, $minggu, $this->user['id'], $note);
                $this->logActivity('tandai_lunas_denda', "Denda user={$userId} {$tahun}W{$minggu} ditandai lunas");
                $this->session->set_flashdata('success', 'Denda ditandai sudah dibayar.');
            }
        }
        $this->backRedirect('tagihan');
    }

    /** Toggle lunas tagihan penjualan, dengan keterangan — admin/pimpinan only */
    public function pay_sale($id)
    {
        $this->requireRole(['admin', 'pimpinan']);

        $note = $this->input->post('note', TRUE);
        $res  = $this->Medicine_model->toggleSalePaid($id, $this->user['id'], $note);

        if ($res === null) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
        } else {
            $this->logActivity('tandai_lunas_penjualan', 'Penjualan id=' . $id . ' ' . ($res ? 'ditandai lunas' : 'dibatalkan'));
            $this->session->set_flashdata('success', $res ? 'Tagihan ditandai sudah dibayar.' : 'Status lunas dibatalkan.');
        }
        $this->backRedirect('tagihan');
    }
}
