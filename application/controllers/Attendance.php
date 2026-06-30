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

        $history       = $this->Attendance_model->getHistory($userId, $startDate, $endDate);
        $summary       = $this->Attendance_model->getSummaryByUser($this->user['id']);
        $todaySessions = $this->Attendance_model->getTodaySessions($this->user['id']);
        $openSession   = $this->Attendance_model->getOpenSession($this->user['id']);
        $todayTotal    = $this->Attendance_model->getTodayTotalHours($this->user['id']);

        $data = $this->viewData([
            'title'         => 'Absensi & Waktu Kerja',
            'todaySessions' => $todaySessions,
            'openSession'   => $openSession,
            'todayTotal'    => $todayTotal,
            'history'       => $history,
            'summary'       => $summary,
            'start_date'    => $startDate,
            'end_date'      => $endDate,
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

    /** Rekap kehadiran — bisa diakses SEMUA role (user lihat miliknya sendiri) */
    public function recap()
    {
        $this->requireLogin();
        $this->load->model('User_model');

        $isAdmin = $this->isAdmin();
        $year    = (int)($this->input->get('year')  ?: date('Y'));
        $month   = (int)($this->input->get('month') ?: 0);
        // Non admin/pimpinan dikunci ke data dirinya sendiri
        $userId  = $isAdmin ? ($this->input->get('user_id') ?: null) : $this->user['id'];

        if ($month) {
            $startDate = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
            $endDate   = date('Y-m-t',  mktime(0, 0, 0, $month, 1, $year));
        } else {
            $startDate = "{$year}-01-01";
            $endDate   = "{$year}-12-31";
        }

        $recap    = $this->Attendance_model->getWeeklyRecap($userId, $startDate, $endDate);
        $checkMap = $this->Attendance_model->getRecapCheckMap();
        $users    = $isAdmin ? $this->User_model->getAll() : [];

        $minHours    = 21;
        $finePerHour = 150000;
        $totalDenda  = 0;

        foreach ($recap as &$row) {
            $row['total_jam'] = round($row['total_jam'], 2);
            $row['kurang']    = max(0, $minHours - $row['total_jam']);
            $row['denda']     = $row['kurang'] * $finePerHour;
            $totalDenda      += $row['denda'];

            $ck = $checkMap[$row['user_id'] . '_' . $row['tahun'] . '_' . $row['minggu']] ?? null;
            $row['checked']      = (bool)$ck;
            $row['checker_name'] = $ck['checker_name'] ?? null;
            $row['checked_at']   = $ck['checked_at'] ?? null;
        }
        unset($row);

        $data = $this->viewData([
            'title'        => 'Rekap Kehadiran Mingguan',
            'recap'        => $recap,
            'users'        => $users,
            'is_admin'     => $isAdmin,
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

    /** Edit waktu absensi (clock in/out + status) — admin/pimpinan only */
    public function edit_record($id)
    {
        $this->requireRole(['admin', 'pimpinan']);

        $rec = $this->Attendance_model->getRecordById($id);
        if (!$rec) {
            $this->session->set_flashdata('error', 'Data absensi tidak ditemukan.');
            return $this->backRedirect('attendance');
        }

        $clockIn  = $this->input->post('clock_in');
        $clockOut = $this->input->post('clock_out');
        $status   = $this->input->post('status') ?: 'hadir';

        // input datetime-local -> "Y-m-d H:i:s"
        $clockIn  = $clockIn  ? date('Y-m-d H:i:s', strtotime($clockIn))  : null;
        $clockOut = $clockOut ? date('Y-m-d H:i:s', strtotime($clockOut)) : null;

        if ($clockIn && $clockOut && strtotime($clockOut) < strtotime($clockIn)) {
            $this->session->set_flashdata('error', 'Clock Out tidak boleh lebih awal dari Clock In.');
            return $this->backRedirect('attendance');
        }

        $this->Attendance_model->updateRecord($id, $clockIn, $clockOut, $status);
        $this->logActivity('edit_absensi', "Edit absensi id={$id}");
        $this->session->set_flashdata('success', 'Waktu absensi berhasil diperbarui.');
        $this->backRedirect('attendance');
    }

    /** Hapus record absensi — admin/pimpinan only */
    public function delete_record($id)
    {
        $this->requireRole(['admin', 'pimpinan']);
        $this->Attendance_model->deleteRecord($id);
        $this->logActivity('hapus_absensi', "Hapus absensi id={$id}");
        $this->session->set_flashdata('success', 'Record absensi berhasil dihapus.');
        $this->backRedirect('attendance');
    }

    /** Toggle verifikasi/checklist satu baris rekap mingguan — admin/pimpinan only */
    public function check_recap()
    {
        $this->requireRole(['admin', 'pimpinan']);

        $userId = (int)$this->input->post('user_id');
        $tahun  = (int)$this->input->post('tahun');
        $minggu = (int)$this->input->post('minggu');

        if ($userId && $tahun && $minggu) {
            $checked = $this->Attendance_model->toggleRecapCheck($userId, $tahun, $minggu, $this->user['id']);
            $this->logActivity('verifikasi_rekap', "Rekap user={$userId} {$tahun}W{$minggu} " . ($checked ? 'dicentang' : 'dilepas'));
            $this->session->set_flashdata('success', $checked ? 'Rekap diverifikasi.' : 'Verifikasi dilepas.');
        }
        $this->backRedirect('attendance/recap');
    }
}
