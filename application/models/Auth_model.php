<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model
{
    public function login($username, $password)
    {
        $user = $this->db->where('username', $username)
                         ->where('status', 1)
                         ->get('users')
                         ->row_array();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
