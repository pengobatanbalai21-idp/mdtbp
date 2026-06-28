<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    public function getAll()
    {
        return $this->db->order_by('name', 'ASC')->get('users')->result_array();
    }

    public function getById($id)
    {
        return $this->db->where('id', $id)->get('users')->row_array();
    }

    public function create($data)
    {
        $data['password']   = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('users', $data);
    }

    public function update($id, $data)
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('id', $id)->update('users', $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', $id)->delete('users');
    }

    public function usernameExists($username, $excludeId = null)
    {
        $this->db->where('username', $username);
        if ($excludeId) {
            $this->db->where('id !=', $excludeId);
        }
        return $this->db->count_all_results('users') > 0;
    }

    public function countByRole()
    {
        return $this->db->select('role, COUNT(*) as total')
                        ->group_by('role')
                        ->get('users')
                        ->result_array();
    }
}
