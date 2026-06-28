<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leave_model extends CI_Model
{
    // ── SUBMIT ────────────────────────────────────────────
    public function create($data)
    {
        // Hitung jumlah hari kerja (tidak menghitung weekend)
        $start = strtotime($data['start_date']);
        $end   = strtotime($data['end_date']);
        $days  = 0;
        for ($d = $start; $d <= $end; $d += 86400) {
            $dow = (int) date('N', $d);
            if ($dow < 6) $days++; // Senin–Jumat
        }
        $data['days'] = max(1, $days);
        return $this->db->insert('leave_requests', $data);
    }

    public function getInsertId()
    {
        return $this->db->insert_id();
    }

    // ── QUERY ─────────────────────────────────────────────
    public function getAll($userId = null, $status = null, $type = null, $limit = 50, $offset = 0)
    {
        $this->db->select('lr.*, u.name as nama_user, u.jabatan,
                           r.name as nama_reviewer')
                 ->from('leave_requests lr')
                 ->join('users u', 'u.id = lr.user_id')
                 ->join('users r', 'r.id = lr.reviewed_by', 'left');

        if ($userId) $this->db->where('lr.user_id', $userId);
        if ($status) $this->db->where('lr.status', $status);
        if ($type)   $this->db->where('lr.type', $type);

        return $this->db->order_by('lr.created_at', 'DESC')
                        ->limit($limit, $offset)
                        ->get()->result_array();
    }

    public function getById($id)
    {
        return $this->db->select('lr.*, u.name as nama_user, u.jabatan, u.username,
                                  r.name as nama_reviewer')
                        ->from('leave_requests lr')
                        ->join('users u', 'u.id = lr.user_id')
                        ->join('users r', 'r.id = lr.reviewed_by', 'left')
                        ->where('lr.id', $id)
                        ->get()->row_array();
    }

    public function countPending()
    {
        return $this->db->where('status', 'pending')->count_all_results('leave_requests');
    }

    public function countByStatus($userId = null)
    {
        $result = [];
        foreach (['pending', 'approved', 'rejected'] as $s) {
            $this->db->where('status', $s);
            if ($userId) $this->db->where('user_id', $userId);
            $result[$s] = $this->db->count_all_results('leave_requests');
        }
        return $result;
    }

    public function hasActivePending($userId, $startDate, $endDate)
    {
        return $this->db->where('user_id', $userId)
                        ->where('status', 'pending')
                        ->where('start_date <=', $endDate)
                        ->where('end_date >=', $startDate)
                        ->count_all_results('leave_requests') > 0;
    }

    // ── REVIEW ────────────────────────────────────────────
    public function approve($id, $reviewerId, $note = '')
    {
        $leave = $this->getById($id);
        if (!$leave || $leave['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Pengajuan tidak ditemukan atau sudah diproses.'];
        }

        $this->db->where('id', $id)->update('leave_requests', [
            'status'      => 'approved',
            'review_note' => $note,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);

        // Update atau insert attendance untuk periode izin
        $this->_syncAttendance($leave, 'approved');

        return ['success' => true, 'message' => "Pengajuan {$leave['type']} {$leave['nama_user']} disetujui."];
    }

    public function reject($id, $reviewerId, $note = '')
    {
        $leave = $this->getById($id);
        if (!$leave || $leave['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Pengajuan tidak ditemukan atau sudah diproses.'];
        }

        $this->db->where('id', $id)->update('leave_requests', [
            'status'      => 'rejected',
            'review_note' => $note,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'message' => "Pengajuan {$leave['type']} {$leave['nama_user']} ditolak."];
    }

    // Saat disetujui, otomatis buat/update record absensi
    private function _syncAttendance($leave, $newStatus)
    {
        if ($newStatus !== 'approved') return;

        $attStatus = ($leave['type'] === 'sakit') ? 'sakit' : 'izin';
        $start     = strtotime($leave['start_date']);
        $end       = strtotime($leave['end_date']);

        for ($d = $start; $d <= $end; $d += 86400) {
            $dow  = (int) date('N', $d);
            if ($dow >= 6) continue; // skip weekend

            $date = date('Y-m-d', $d);
            $existing = $this->db->where('user_id', $leave['user_id'])
                                 ->where('date', $date)
                                 ->get('attendance')->row_array();

            if (!$existing) {
                $this->db->insert('attendance', [
                    'user_id'    => $leave['user_id'],
                    'date'       => $date,
                    'status'     => $attStatus,
                    'work_hours' => 0,
                    'notes'      => 'Otomatis dari persetujuan pengajuan #' . $leave['id'],
                ]);
            } elseif (!$existing['clock_in']) {
                $this->db->where('id', $existing['id'])
                         ->update('attendance', ['status' => $attStatus]);
            }
        }
    }
}
