<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Activity_log_model extends CI_Model
{
    public function log($userId, $userName, $action, $description = null, $ipAddress = null)
    {
        $this->db->insert('activity_logs', [
            'user_id'     => $userId,
            'user_name'   => $userName,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $ipAddress,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function getAll($limit = 50, $offset = 0)
    {
        return $this->db
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get('activity_logs')
            ->result_array();
    }

    public function countAll()
    {
        return $this->db->count_all_results('activity_logs');
    }

    public function getFiltered($search = '', $action = '', $limit = 50, $offset = 0)
    {
        if ($search) {
            $this->db->like('user_name', $search);
        }
        if ($action) {
            $this->db->where('action', $action);
        }
        return $this->db
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get('activity_logs')
            ->result_array();
    }

    public function countFiltered($search = '', $action = '')
    {
        if ($search) $this->db->like('user_name', $search);
        if ($action) $this->db->where('action', $action);
        return $this->db->count_all_results('activity_logs');
    }

    public function getActionList()
    {
        return $this->db->distinct()->select('action')->order_by('action')->get('activity_logs')->result_array();
    }
}
