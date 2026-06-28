<?php
$ci  = get_instance();
$suc = $ci->session->flashdata('success');
$err = $ci->session->flashdata('error');
$isAdmin = in_array($user['role'], ['admin','pimpinan']);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-capsule me-2 text-info"></i>Stok Obat-obatan</h4>
        <p class="text-muted small mb-0">Inventaris obat<?= $isAdmin ? ' dan paket penjualan' : '' ?></p>
    </div>
    <?php if ($isAdmin): ?>
    <a href="<?= site_url('medicines/sell') ?>" class="btn btn-success rounded-3">
        <i class="bi bi-bag-plus me-1"></i>Jual / WD Obat
    </a>
    <?php endif; ?>
</div>

<?php if ($suc): ?>
<div class="alert alert-success alert-dismissible fade show rounded-3">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($suc) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($err): ?>
<div class="alert alert-danger alert-dismissible fade show rounded-3">
    <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($err) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stok Per Item -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-4 pb-0">
        <h6 class="fw-bold"><i class="bi bi-box-seam me-2 text-primary"></i>Stok Per Item</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($medicines as $med):
                $pct = $med['min_stock'] > 0 ? min(100, round($med['stock'] / ($med['min_stock'] * 2) * 100)) : 100;
                $barColor = $med['stock'] <= $med['min_stock'] ? 'danger' : ($pct < 60 ? 'warning' : 'success');
            ?>
            <div class="col-md-4 col-lg-3">
                <div class="card rounded-4 border h-100 <?= $med['stock'] <= $med['min_stock'] ? 'border-danger border-2' : '' ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($med['name']) ?></h6>
                            <?php if ($med['stock'] <= $med['min_stock']): ?>
                            <span class="badge bg-danger rounded-pill">Low</span>
                            <?php endif; ?>
                        </div>
                        <div class="fs-3 fw-bold text-<?= $barColor ?>"><?= $med['stock'] ?></div>
                        <div class="text-muted small mb-2"><?= htmlspecialchars($med['unit']) ?></div>
                        <div class="progress" style="height:6px">
                            <div class="progress-bar bg-<?= $barColor ?>" style="width:<?= $pct ?>%"></div>
                        </div>
                        <?php if ($isAdmin): ?>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">Min: <?= $med['min_stock'] ?></small>
                            <small class="fw-semibold">Rp <?= number_format($med['price'], 0, ',', '.') ?></small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary w-100 mt-2 rounded-3"
                                data-bs-toggle="modal" data-bs-target="#addStockModal"
                                data-id="<?= $med['id'] ?>" data-name="<?= htmlspecialchars($med['name']) ?>">
                            <i class="bi bi-plus-circle me-1"></i>Tambah Stok
                        </button>
                        <button class="btn btn-sm btn-outline-danger w-100 mt-2 rounded-3"
                                data-bs-toggle="modal" data-bs-target="#reduceStockModal"
                                data-id="<?= $med['id'] ?>" data-name="<?= htmlspecialchars($med['name']) ?>"
                                data-stock="<?= $med['stock'] ?>" <?= $med['stock'] <= 0 ? 'disabled' : '' ?>>
                            <i class="bi bi-dash-circle me-1"></i>Kurangi Stok
                        </button>
                        <?php else: ?>
                        <div class="mt-2">
                            <small class="text-muted">Min: <?= $med['min_stock'] ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if ($isAdmin): ?>
<!-- Paket Obat -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 pb-0">
        <h6 class="fw-bold"><i class="bi bi-box-fill me-2 text-success"></i>Daftar Paket Obat</h6>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <?php foreach ($packages as $pkg): ?>
            <div class="col-md-6">
                <div class="card rounded-4 border-0 bg-light h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($pkg['name']) ?></h5>
                            <span class="badge bg-success fs-6 px-3 py-2 rounded-pill">
                                Rp <?= number_format($pkg['price'], 0, ',', '.') ?>
                            </span>
                        </div>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($pkg['description']) ?></p>
                        <div>
                            <strong class="small">Isi Paket:</strong>
                            <ul class="list-unstyled mt-2 mb-0">
                                <?php foreach ($pkg['items'] as $item): ?>
                                <li class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                    <span><i class="bi bi-check2 text-success me-1"></i><?= htmlspecialchars($item['name']) ?></span>
                                    <span class="badge bg-secondary"><?= $item['quantity'] ?> <?= $item['unit'] ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <a href="<?= site_url('medicines/sell') ?>" class="btn btn-success w-100 mt-3 rounded-3">
                            <i class="bi bi-bag-plus me-1"></i>Jual Paket Ini
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah Stok -->
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form method="POST" action="<?= site_url('medicines/add_stock') ?>">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Tambah Stok Obat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="medicine_id" id="stockMedicineId">
                    <div class="mb-3">
                        <label class="form-label">Nama Obat</label>
                        <input type="text" id="stockMedicineName" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Tambahan</label>
                        <input type="number" name="quantity" class="form-control" min="1" placeholder="Masukkan jumlah" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Kurangi Stok -->
<div class="modal fade" id="reduceStockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form method="POST" action="<?= site_url('medicines/reduce_stock') ?>">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-danger">Kurangi Stok Obat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="medicine_id" id="reduceMedicineId">
                    <div class="mb-3">
                        <label class="form-label">Nama Obat</label>
                        <input type="text" id="reduceMedicineName" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok Saat Ini</label>
                        <input type="text" id="reduceMedicineStock" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Pengurangan</label>
                        <input type="number" name="quantity" id="reduceQty" class="form-control" min="1" placeholder="Masukkan jumlah" required>
                        <div class="form-text">Tidak boleh melebihi stok saat ini.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-3">Kurangi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('addStockModal').addEventListener('show.bs.modal', function(e) {
    var btn = e.relatedTarget;
    document.getElementById('stockMedicineId').value = btn.getAttribute('data-id');
    document.getElementById('stockMedicineName').value = btn.getAttribute('data-name');
});
document.getElementById('reduceStockModal').addEventListener('show.bs.modal', function(e) {
    var btn   = e.relatedTarget;
    var stock = btn.getAttribute('data-stock');
    document.getElementById('reduceMedicineId').value    = btn.getAttribute('data-id');
    document.getElementById('reduceMedicineName').value  = btn.getAttribute('data-name');
    document.getElementById('reduceMedicineStock').value = stock + ' pcs';
    var q = document.getElementById('reduceQty');
    q.max = stock;
    q.value = '';
});
</script>
<?php endif; ?>
