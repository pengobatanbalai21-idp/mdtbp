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

    /** Sesi yang masih terbuka hari ini (sudah clock in, belum clock out) */
    public function getOpenSession($userId)
    {
        return $this->db->where('user_id', $userId)
                        ->where('date', date('Y-m-d'))
                        ->where('clock_in IS NOT NULL', null, false)
                        ->where('clock_out IS NULL', null, false)
                        ->order_by('clock_in', 'DESC')
                        ->limit(1)
                        ->get('attendance')->row_array();
    }

    /** Semua sesi absensi hari ini (bisa lebih dari satu) */
    public function getTodaySessions($userId)
    {
        return $this->db->where('user_id', $userId)
                        ->where('date', date('Y-m-d'))
                        ->order_by('clock_in', 'ASC')
                        ->get('attendance')->result_array();
    }

    /** Total jam kerja terkumpul hari ini (semua sesi) */
    public function getTodayTotalHours($userId)
    {
        $row = $this->db->select_sum('work_hours')
                        ->where('user_id', $userId)
                        ->where('date', date('Y-m-d'))
                        ->get('attendance')->row_array();
        return (float)($row['work_hours'] ?? 0);
    }

    public function clockIn($userId)
    {
        // Boleh clock in lebih dari sekali per hari — tiap clock in = sesi baru.
        // Tapi harus clock out dulu sesi yang masih terbuka.
        if ($this->getOpenSession($userId)) {
            return ['success' => false, 'message' => 'Masih ada sesi kerja yang terbuka. Silakan Clock Out dulu.'];
        }

        $this->db->insert('attendance', [
            'user_id'    => $userId,
            'date'       => date('Y-m-d'),
            'clock_in'   => date('Y-m-d H:i:s'),
            'status'     => 'hadir',
            'work_hours' => 0,
        ]);
        return ['success' => true, 'message' => 'Clock in berhasil pukul ' . date('H:i:s')];
    }

    public function clockOut($userId)
    {
        $open = $this->getOpenSession($userId);

        if (!$open) {
            return ['success' => false, 'message' => 'Tidak ada sesi kerja aktif. Silakan Clock In dulu.'];
        }

        $workHours = round((time() - strtotime($open['clock_in'])) / 3600, 2);
        $this->db->where('id', $open['id'])->update('attendance', [
            'clock_out'  => date('Y-m-d H:i:s'),
            'work_hours' => $workHours,
        ]);
        return ['success' => true, 'message' => 'Clock out berhasil pukul ' . date('H:i:s')];
    }

    // ── Edit/hapus record (admin/pimpinan) ───────────────────
    public function getRecordById($id)
    {
        return $this->db->where('id', $id)->get('attendance')->row_array();
    }

    public function updateRecord($id, $clockIn, $clockOut, $status)
    {
        $data = [
            'clock_in'  => $clockIn  ?: null,
            'clock_out' => $clockOut ?: null,
            'status'    => $status,
        ];
        $data['work_hours'] = ($clockIn && $clockOut)
            ? max(0, round((strtotime($clockOut) - strtotime($clockIn)) / 3600, 2))
            : 0;
        return $this->db->where('id', $id)->update('attendance', $data);
    }

    public function deleteRecord($id)
    {
        return $this->db->where('id', $id)->delete('attendance');
    }

    // ── Checklist verifikasi rekap mingguan ──────────────────
    public function getRecapCheckMap()
    {
        $rows = $this->db->select('rc.user_id, rc.tahun, rc.minggu, rc.checked_at, c.name AS checker_name')
                         ->from('recap_checks rc')
                         ->join('users c', 'c.id = rc.checked_by', 'left')
                         ->get()->result_array();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['user_id'] . '_' . $r['tahun'] . '_' . $r['minggu']] = $r;
        }
        return $map;
    }

    /** Toggle verifikasi satu baris rekap. Return TRUE jika jadi tercentang, FALSE jika dilepas. */
    public function toggleRecapCheck($userId, $tahun, $minggu, $checkerId)
    {
        $existing = $this->db->where(['user_id' => $userId, 'tahun' => $tahun, 'minggu' => $minggu])
                             ->get('recap_checks')->row_array();
        if ($existing) {
            $this->db->where('id', $existing['id'])->delete('recap_checks');
            return false;
        }
        $this->db->insert('recap_checks', [
            'user_id'    => $userId,
            'tahun'      => $tahun,
            'minggu'     => $minggu,
            'checked_by' => $checkerId,
            'checked_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
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

        $grouped   = [];
        $datesSeen = [];
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
            $grouped[$key]['total_jam']      += (float)$row['work_hours'];
            $datesSeen[$key][$row['date']]    = true; // hari unik (multi-sesi tidak double-count)
        }

        // hari_hadir = jumlah tanggal unik per (user x minggu)
        foreach ($grouped as $k => &$g) {
            $g['hari_hadir'] = count($datesSeen[$k]);
        }
        unset($g);

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
        return $this->db->select('status, COUNT(DISTINCT date) as total, SUM(work_hours) as jam_kerja')
                        ->where('user_id', $userId)
                        ->where('MONTH(date)', $m)
                        ->where('YEAR(date)', $y)
                        ->group_by('status')
                        ->get('attendance')
                        ->result_array();
    }
}
