<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_model extends CI_Model
{
    public function getTodayAttendance($userId)
    {
        return $this->db->where('user_id', $userId)
                        ->where('date', date('Y-m-d'))
                        ->get('attendance')
                        ->row_array();
    }

    public function clockIn($userId)
    {
        $today    = date('Y-m-d');
        $existing = $this->getTodayAttendance($userId);

        if ($existing) {
            // Update clock_in, reset clock_out dan work_hours
            $this->db->where('id', $existing['id'])->update('attendance', [
                'clock_in'   => date('Y-m-d H:i:s'),
                'clock_out'  => null,
                'work_hours' => 0,
                'status'     => 'hadir',
            ]);
        } else {
            $this->db->insert('attendance', [
                'user_id'    => $userId,
                'date'       => $today,
                'clock_in'   => date('Y-m-d H:i:s'),
                'status'     => 'hadir',
                'work_hours' => 0,
            ]);
        }
        return ['success' => true, 'message' => 'Clock in berhasil pukul ' . date('H:i:s')];
    }

    public function clockOut($userId)
    {
        $existing = $this->getTodayAttendance($userId);

        if (!$existing || !$existing['clock_in']) {
            // Belum clock in — buat record dengan clock_in = clock_out = sekarang
            $now = date('Y-m-d H:i:s');
            $this->db->insert('attendance', [
                'user_id'    => $userId,
                'date'       => date('Y-m-d'),
                'clock_in'   => $now,
                'clock_out'  => $now,
                'status'     => 'hadir',
                'work_hours' => 0,
            ]);
        } else {
            $clockIn   = strtotime($existing['clock_in']);
            $clockOut  = time();
            $workHours = round(($clockOut - $clockIn) / 3600, 2);
            $this->db->where('id', $existing['id'])->update('attendance', [
                'clock_out'  => date('Y-m-d H:i:s'),
                'work_hours' => $workHours,
            ]);
        }
        return ['success' => true, 'message' => 'Clock out berhasil pukul ' . date('H:i:s')];
    }

    public function getHistory($userId = null, $startDate = null, $endDate = null, $limit = 30, $offset = 0)
    {
        $this->db->select('a.*, u.name, u.jabatan, u.username')
                 ->from('attendance a')
                 ->join('users u', 'u.id = a.user_id');

        if ($userId) {
            $this->db->where('a.user_id', $userId);
        }
        if ($startDate) {
            $this->db->where('a.date >=', $startDate);
        }
        if ($endDate) {
            $this->db->where('a.date <=', $endDate);
        }
        $this->db->order_by('a.date', 'DESC')->order_by('a.clock_in', 'DESC');
        return $this->db->limit($limit, $offset)->get()->result_array();
    }

    public function countHistory($userId = null, $startDate = null, $endDate = null)
    {
        if ($userId) $this->db->where('user_id', $userId);
        if ($startDate) $this->db->where('date >=', $startDate);
        if ($endDate)   $this->db->where('date <=', $endDate);
        return $this->db->count_all_results('attendance');
    }

    public function getTodayStats()
    {
        $today = date('Y-m-d');
        $hadir = $this->db->where('date', $today)->where('status', 'hadir')->count_all_results('attendance');
        $total = $this->db->where('status', 1)->count_all_results('users');
        return ['hadir' => $hadir, 'total' => $total, 'belum' => $total - $hadir];
    }

    public function getWeeklyRecap($userId = null, $startDate = null, $endDate = null)
    {
        $this->db->select('a.user_id, a.date, a.work_hours, u.name, u.jabatan')
                 ->from('attendance a')
                 ->join('users u', 'u.id = a.user_id')
                 ->where('a.status', 'hadir');

        if ($userId)    $this->db->where('a.user_id', $userId);
        if ($startDate) $this->db->where('a.date >=', $startDate);
        if ($endDate)   $this->db->where('a.date <=', $endDate);

        $rows = $this->db->order_by('u.name', 'ASC')->order_by('a.date', 'ASC')->get()->result_array();

        $grouped = [];
        foreach ($rows as $row) {
            $dt      = new DateTime($row['date']);
            $weekNum = (int)$dt->format('W');
            $isoYear = (int)$dt->format('o');
            $key     = $row['user_id'] . '_' . $isoYear . '_' . $weekNum;

            if (!isset($grouped[$key])) {
                $mon = clone $dt;
                $mon->modify('monday this week');
                $sun = clone $mon;
                $sun->modify('+6 days');
                $grouped[$key] = [
                    'user_id'    => $row['user_id'],
                    'name'       => $row['name'],
                    'jabatan'    => $row['jabatan'],
                    'tahun'      => $isoYear,
                    'minggu'     => $weekNum,
                    'tgl_mulai'  => $mon->format('Y-m-d'),
                    'tgl_akhir'  => $sun->format('Y-m-d'),
                    'total_jam'  => 0.0,
                    'hari_hadir' => 0,
                ];
            }
            $grouped[$key]['total_jam']   += (float)$row['work_hours'];
            $grouped[$key]['hari_hadir']++;
        }

        $result = array_values($grouped);
        usort($result, function($a, $b) {
            $nc = strcmp($a['name'], $b['name']);
            if ($nc !== 0) return $nc;
            if ($a['tahun'] !== $b['tahun']) return $b['tahun'] - $a['tahun'];
            return $b['minggu'] - $a['minggu'];
        });
        return $result;
    }

    public function getSummaryByUser($userId, $month = null, $year = null)
    {
        $m = $month ?: date('m');
        $y = $year  ?: date('Y');
        return $this->db->select('status, COUNT(*) as total, SUM(work_hours) as jam_kerja')
                        ->where('user_id', $userId)
                        ->where('MONTH(date)', $m)
                        ->where('YEAR(date)', $y)
                        ->group_by('status')
                        ->get('attendance')
                        ->result_array();
    }
}
