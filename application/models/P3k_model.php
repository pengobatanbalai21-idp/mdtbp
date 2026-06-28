<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class P3k_model extends CI_Model
{
    // ── KIT ───────────────────────────────────────────────
    public function getAllKits()
    {
        return $this->db->order_by('id', 'ASC')->get('p3k_kits')->result_array();
    }

    public function getKitById($id)
    {
        return $this->db->where('id', $id)->get('p3k_kits')->row_array();
    }

    public function addStock($kitId, $qty)
    {
        $kit = $this->getKitById($kitId);
        if (!$kit) return ['success' => false, 'message' => 'Kit tidak ditemukan.'];
        $this->db->where('id', $kitId)->update('p3k_kits', [
            'stock'      => $kit['stock'] + $qty,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'message' => "Stok {$kit['name']} berhasil ditambah {$qty} unit."];
    }

    public function updateKit($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('id', $id)->update('p3k_kits', $data);
    }

    // ── WITHDRAW ──────────────────────────────────────────
    public function withdraw($userId, $kitId, $quantity, $purpose, $notes)
    {
        $kit = $this->getKitById($kitId);
        if (!$kit) return ['success' => false, 'message' => 'Jenis P3K tidak ditemukan.'];
        if ($kit['stock'] < $quantity) {
            return ['success' => false, 'message' => "Stok {$kit['name']} tidak mencukupi (tersisa {$kit['stock']} unit)."];
        }

        $this->db->trans_start();

        $this->db->insert('p3k_wd', [
            'user_id'  => $userId,
            'kit_id'   => $kitId,
            'quantity' => $quantity,
            'purpose'  => $purpose,
            'notes'    => $notes,
        ]);

        $this->db->where('id', $kitId)->update('p3k_kits', [
            'stock'      => $kit['stock'] - $quantity,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Gagal menyimpan data WD P3K.'];
        }
        return ['success' => true, 'message' => "WD {$kit['name']} x{$quantity} berhasil dicatat."];
    }

    // ── HISTORY ───────────────────────────────────────────
    public function getHistory($userId = null, $startDate = null, $endDate = null, $limit = 50, $offset = 0)
    {
        $this->db->select('w.*, u.name as petugas, u.jabatan, k.name as kit_name')
                 ->from('p3k_wd w')
                 ->join('users u', 'u.id = w.user_id')
                 ->join('p3k_kits k', 'k.id = w.kit_id');

        if ($userId)    $this->db->where('w.user_id', $userId);
        if ($startDate) $this->db->where('DATE(w.created_at) >=', $startDate);
        if ($endDate)   $this->db->where('DATE(w.created_at) <=', $endDate);

        return $this->db->order_by('w.created_at', 'DESC')
                        ->limit($limit, $offset)
                        ->get()->result_array();
    }

    public function countHistory($userId = null)
    {
        if ($userId) $this->db->where('user_id', $userId);
        return $this->db->count_all_results('p3k_wd');
    }

    public function getTodayWdTotal()
    {
        return $this->db->where('DATE(created_at)', date('Y-m-d'))
                        ->count_all_results('p3k_wd');
    }

    public function getLowStockKits()
    {
        return $this->db->where('stock <= min_stock', NULL, FALSE)
                        ->get('p3k_kits')->result_array();
    }
}
