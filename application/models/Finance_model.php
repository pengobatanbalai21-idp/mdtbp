<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Finance_model extends CI_Model
{
    public function getAll($startDate = null, $endDate = null, $type = null, $limit = 50, $offset = 0)
    {
        $this->db->select('f.*, u.name as petugas')
                 ->from('finances f')
                 ->join('users u', 'u.id = f.created_by');

        if ($startDate) $this->db->where('f.date >=', $startDate);
        if ($endDate)   $this->db->where('f.date <=', $endDate);
        if ($type)      $this->db->where('f.type', $type);

        return $this->db->order_by('f.date', 'DESC')
                        ->order_by('f.created_at', 'DESC')
                        ->limit($limit, $offset)
                        ->get()->result_array();
    }

    public function countAll($startDate = null, $endDate = null, $type = null)
    {
        if ($startDate) $this->db->where('date >=', $startDate);
        if ($endDate)   $this->db->where('date <=', $endDate);
        if ($type)      $this->db->where('type', $type);
        return $this->db->count_all_results('finances');
    }

    public function add($data)
    {
        return $this->db->insert('finances', $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', $id)->delete('finances');
    }

    public function getSummary($startDate = null, $endDate = null)
    {
        if (!$startDate) $startDate = date('Y-m-01');
        if (!$endDate)   $endDate   = date('Y-m-d');

        $income = $this->db->select_sum('amount')
                           ->where('type', 'income')
                           ->where('date >=', $startDate)
                           ->where('date <=', $endDate)
                           ->get('finances')->row_array();

        $expense = $this->db->select_sum('amount')
                            ->where('type', 'expense')
                            ->where('date >=', $startDate)
                            ->where('date <=', $endDate)
                            ->get('finances')->row_array();

        $totalIncome  = $income['amount']  ?? 0;
        $totalExpense = $expense['amount'] ?? 0;

        return [
            'income'  => $totalIncome,
            'expense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
        ];
    }

    public function getMonthlySummary($year = null)
    {
        $y = $year ?: date('Y');
        return $this->db->select("MONTH(date) as bulan, type, SUM(amount) as total")
                        ->where('YEAR(date)', $y)
                        ->group_by(['MONTH(date)', 'type'])
                        ->order_by('bulan', 'ASC')
                        ->get('finances')->result_array();
    }
}
