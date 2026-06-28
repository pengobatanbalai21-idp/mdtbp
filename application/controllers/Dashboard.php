<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->load->model(['Attendance_model', 'Medicine_model', 'Finance_model', 'User_model', 'Leave_model']);
    }

    public function index()
    {
        $stats = $this->Attendance_model->getTodayStats();
        $todayAttendance = $this->Attendance_model->getTodayAttendance($this->user['id']);
        $lowStock = $this->Medicine_model->getLowStockMedicines();

        $data = $this->viewData([
            'title'           => 'Dashboard',
            'stats'           => $stats,
            'todayAttendance' => $todayAttendance,
            'lowStock'        => $lowStock,
        ]);

        if ($this->isAdmin()) {
            $data['finance']       = $this->Finance_model->getSummary();
            $data['todaySales']    = $this->Medicine_model->getTodaySalesTotal();
            $data['pendingLeave']  = $this->Leave_model->countPending();
        } else {
            $userLeaveCounts       = $this->Leave_model->countByStatus($this->user['id']);
            $data['myPendingLeave'] = $userLeaveCounts['pending'];
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer', $data);
    }
}
