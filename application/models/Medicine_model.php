<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Medicine_model extends CI_Model
{
    // ── OBAT ──────────────────────────────────────────────
    public function getAllMedicines()
    {
        return $this->db->order_by('name', 'ASC')->get('medicines')->result_array();
    }

    public function getMedicineById($id)
    {
        return $this->db->where('id', $id)->get('medicines')->row_array();
    }

    public function updateStock($id, $qty, $type = 'add')
    {
        $med = $this->getMedicineById($id);
        if (!$med) return false;
        $newStock = $type === 'add' ? $med['stock'] + $qty : $med['stock'] - $qty;
        if ($newStock < 0) return false;
        return $this->db->where('id', $id)->update('medicines', ['stock' => $newStock, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function addMedicine($data)
    {
        return $this->db->insert('medicines', $data);
    }

    public function editMedicine($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('id', $id)->update('medicines', $data);
    }

    public function getLowStockMedicines()
    {
        return $this->db->where('stock <= min_stock', NULL, FALSE)
                        ->get('medicines')
                        ->result_array();
    }

    // ── PAKET ─────────────────────────────────────────────
    public function getAllPackages()
    {
        return $this->db->where('status', 1)->get('packages')->result_array();
    }

    public function getPackageById($id)
    {
        return $this->db->where('id', $id)->get('packages')->row_array();
    }

    public function getPackageItems($packageId)
    {
        return $this->db->select('pi.*, m.name, m.unit, m.stock, m.price')
                        ->from('package_items pi')
                        ->join('medicines m', 'm.id = pi.medicine_id')
                        ->where('pi.package_id', $packageId)
                        ->get()->result_array();
    }

    // ── PENJUALAN ─────────────────────────────────────────
    public function sellPackage($userId, $packageId, $patientName, $quantity, $notes)
    {
        $package = $this->getPackageById($packageId);
        if (!$package) return ['success' => false, 'message' => 'Paket tidak ditemukan.'];

        $items = $this->getPackageItems($packageId);

        // Cek stok semua item
        foreach ($items as $item) {
            $needed = $item['quantity'] * $quantity;
            if ($item['stock'] < $needed) {
                return ['success' => false, 'message' => "Stok {$item['name']} tidak mencukupi (tersisa {$item['stock']} {$item['unit']})."];
            }
        }

        $this->db->trans_start();

        $totalPrice = $package['price'] * $quantity;

        // Insert sale
        $this->db->insert('sales', [
            'user_id'      => $userId,
            'sale_type'    => 'package',
            'reference_id' => $packageId,
            'patient_name' => $patientName,
            'quantity'     => $quantity,
            'total_price'  => $totalPrice,
            'notes'        => $notes,
        ]);
        $saleId = $this->db->insert_id();

        // Kurangi stok & insert sale_details
        foreach ($items as $item) {
            $qtyUsed = $item['quantity'] * $quantity;
            $this->db->insert('sale_details', [
                'sale_id'     => $saleId,
                'medicine_id' => $item['medicine_id'],
                'quantity'    => $qtyUsed,
                'price'       => $item['price'],
            ]);
            $this->updateStock($item['medicine_id'], $qtyUsed, 'subtract');
        }

        // Catat pemasukan keuangan
        $this->db->insert('finances', [
            'type'         => 'income',
            'category'     => 'penjualan_obat',
            'amount'       => $totalPrice,
            'description'  => "Penjualan {$package['name']} x{$quantity} untuk: {$patientName}",
            'date'         => date('Y-m-d'),
            'reference_id' => $saleId,
            'created_by'   => $userId,
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data.'];
        }
        return ['success' => true, 'message' => "Penjualan {$package['name']} berhasil dicatat."];
    }

    public function sellItem($userId, $medicineId, $patientName, $quantity, $notes)
    {
        $med = $this->getMedicineById($medicineId);
        if (!$med) return ['success' => false, 'message' => 'Obat tidak ditemukan.'];
        if ($med['stock'] < $quantity) {
            return ['success' => false, 'message' => "Stok {$med['name']} tidak mencukupi (tersisa {$med['stock']} {$med['unit']})."];
        }

        $this->db->trans_start();

        $totalPrice = $med['price'] * $quantity;

        $this->db->insert('sales', [
            'user_id'      => $userId,
            'sale_type'    => 'item',
            'reference_id' => $medicineId,
            'patient_name' => $patientName,
            'quantity'     => $quantity,
            'total_price'  => $totalPrice,
            'notes'        => $notes,
        ]);
        $saleId = $this->db->insert_id();

        $this->db->insert('sale_details', [
            'sale_id'     => $saleId,
            'medicine_id' => $medicineId,
            'quantity'    => $quantity,
            'price'       => $med['price'],
        ]);

        $this->updateStock($medicineId, $quantity, 'subtract');

        $this->db->insert('finances', [
            'type'         => 'income',
            'category'     => 'penjualan_obat',
            'amount'       => $totalPrice,
            'description'  => "Penjualan {$med['name']} x{$quantity} untuk: {$patientName}",
            'date'         => date('Y-m-d'),
            'reference_id' => $saleId,
            'created_by'   => $userId,
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data.'];
        }
        return ['success' => true, 'message' => "Penjualan {$med['name']} berhasil dicatat."];
    }

    public function getSalesHistory($limit = 30, $offset = 0, $userId = null)
    {
        $this->db->select('s.*, u.name as petugas, u.jabatan, c.name as checker_name')
                 ->from('sales s')
                 ->join('users u', 'u.id = s.user_id')
                 ->join('users c', 'c.id = s.checked_by', 'left');

        if ($userId) {
            $this->db->where('s.user_id', $userId);
        }

        return $this->db->order_by('s.created_at', 'DESC')
                        ->limit($limit, $offset)
                        ->get()->result_array();
    }

    public function getSaleDetails($saleId)
    {
        return $this->db->select('sd.*, m.name, m.unit')
                        ->from('sale_details sd')
                        ->join('medicines m', 'm.id = sd.medicine_id')
                        ->where('sd.sale_id', $saleId)
                        ->get()->result_array();
    }

    public function getTodaySalesTotal()
    {
        $row = $this->db->select_sum('total_price')
                        ->where('DATE(created_at)', date('Y-m-d'))
                        ->get('sales')->row_array();
        return $row['total_price'] ?? 0;
    }

    /** Toggle verifikasi 1 transaksi penjualan. TRUE=tercentang, FALSE=dilepas, NULL=tidak ada. */
    public function toggleSaleCheck($id, $checkerId)
    {
        $row = $this->db->where('id', $id)->get('sales')->row_array();
        if (!$row) return null;

        if ($row['checked_by']) {
            $this->db->where('id', $id)->update('sales', ['checked_by' => null, 'checked_at' => null]);
            return false;
        }
        $this->db->where('id', $id)->update('sales', [
            'checked_by' => $checkerId,
            'checked_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    // ── Tagihan penjualan (status lunas) ─────────────────────
    /** Semua transaksi penjualan untuk rekap tagihan, opsional per user, join nama pembayar. */
    public function getSalesForBilling($userId = null)
    {
        $this->db->select("s.*, u.name AS petugas, u.jabatan, p.name AS payer_name,
                            CASE WHEN s.sale_type = 'package' THEN pk.name ELSE m.name END AS item_name", false)
                 ->from('sales s')
                 ->join('users u', 'u.id = s.user_id')
                 ->join('users p', 'p.id = s.paid_by', 'left')
                 ->join('packages pk', "pk.id = s.reference_id AND s.sale_type = 'package'", 'left')
                 ->join('medicines m', "m.id = s.reference_id AND s.sale_type = 'item'", 'left');

        if ($userId) {
            $this->db->where('s.user_id', $userId);
        }
        return $this->db->order_by('s.created_at', 'DESC')->get()->result_array();
    }

    /** Tandai/batalkan lunas 1 transaksi penjualan dengan keterangan. TRUE=lunas, FALSE=dibatalkan, NULL=tidak ada. */
    public function toggleSalePaid($id, $payerId, $note = null)
    {
        $row = $this->db->where('id', $id)->get('sales')->row_array();
        if (!$row) return null;

        if ($row['is_paid']) {
            $this->db->where('id', $id)->update('sales', [
                'is_paid'      => 0,
                'paid_at'      => null,
                'paid_by'      => null,
                'payment_note' => null,
            ]);
            return false;
        }
        $this->db->where('id', $id)->update('sales', [
            'is_paid'      => 1,
            'paid_at'      => date('Y-m-d H:i:s'),
            'paid_by'      => $payerId,
            'payment_note' => $note,
        ]);
        return true;
    }
}
