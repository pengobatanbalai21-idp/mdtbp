<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->load->model('Attendance_model');
    }

    public function index()
    {
        $userId    = $this->isAdmin() ? ($this->input->get('user_id') ?: null) : $this->user['id'];
        $startDate = $this->input->get('start_date') ?: date('Y-m-01');
        $endDate   = $this->input->get('end_date')   ?: date('Y-m-d');

        $todayAttendance = $this->Attendance_model->getTodayAttendance($this->user['id']);
        $history         = $this->Attendance_model->getHistory($userId, $startDate, $endDate);
        $summary         = $this->Attendance_model->getSummaryByUser($this->user['id']);

        $data = $this->viewData([
            'title'           => 'Absensi & Waktu Kerja',
            'todayAttendance' => $todayAttendance,
            'history'         => $history,
            'summary'         => $summary,
            'start_date'      => $startDate,
            'end_date'        => $endDate,
        ]);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('attendance/index', $data);
        $this->load->view('templates/footer', $data);
    }

    public function clock_in()
    {
        $this->requireLogin();
        $result = $this->Attendance_model->clockIn($this->user['id']);
        $this->logActivity('clock_in', 'Clock In pukul ' . date('H:i:s'));
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('attendance');
    }

    public function clock_out()
    {
        $this->requireLogin();
        $result = $this->Attendance_model->clockOut($this->user['id']);
        $this->logActivity('clock_out', 'Clock Out pukul ' . date('H:i:s'));
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('attendance');
    }

    public function recap()
    {
        $this->requireRole(['admin', 'pimpinan']);
        $this->load->model('User_model');

        $year      = (int)($this->input->get('year')    ?: date('Y'));
        $month     = (int)($this->input->get('month')   ?: 0);
        $userId    = $this->input->get('user_id')       ?: null;

        if ($month) {
            $startDate = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
            $endDate   = date('Y-m-t',  mktime(0, 0, 0, $month, 1, $year));
        } else {
            $startDate = "{$year}-01-01";
            $endDate   = "{$year}-12-31";
        }

        $recap = $this->Attendance_model->getWeeklyRecap($userId, $startDate, $endDate);
        $users = $this->User_model->getAll();

        $minHours    = 21;
        $finePerHour = 150000;
        $totalDenda  = 0;

        foreach ($recap as &$row) {
            $row['total_jam'] = round($row['total_jam'], 2);
            $row['kurang']    = max(0, $minHours - $row['total_jam']);
            $row['denda']     = $row['kurang'] * $finePerHour;
            $totalDenda      += $row['denda'];
        }
        unset($row);

        $data = $this->viewData([
            'title'        => 'Rekap Kehadiran Mingguan',
            'recap'        => $recap,
            'users'        => $users,
            'year'         => $year,
            'month'        => $month,
            'user_filter'  => $userId,
            'min_hours'    => $minHours,
            'fine_per_hour'=> $finePerHour,
            'total_denda'  => $totalDenda,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
        ]);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('attendance/recap', $data);
        $this->load->view('templates/footer', $data);
    }
}
