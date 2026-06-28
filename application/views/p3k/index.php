<?php
$ci  = get_instance();
$suc = $ci->session->flashdata('success');
$err = $ci->session->flashdata('error');
$isAdmin = in_array($user['role'], ['admin', 'pimpinan']);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-heart-pulse me-2 text-danger"></i>P3K – Pertolongan Pertama</h4>
        <p class="text-muted small mb-0">Kelola stok dan penarikan kit P3K</p>
    </div>
    <a href="<?= site_url('p3k/history') ?>" class="btn btn-outline-secondary rounded-3">
        <i class="bi bi-clock-history me-1"></i>Riwayat WD
    </a>
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

<!-- Kartu Kit P3K -->
<div class="row g-4 mb-4">
    <?php foreach ($kits as $kit):
        $pct      = $kit['min_stock'] > 0 ? min(100, round($kit['stock'] / ($kit['min_stock'] * 3) * 100)) : 100;
        $barColor = $kit['stock'] <= $kit['min_stock'] ? 'danger' : ($pct < 50 ? 'warning' : 'success');
        $isLow    = $kit['stock'] <= $kit['min_stock'];
        $badgeColor = $kit['id'] == 1 ? 'primary' : 'success';
    ?>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 <?= $isLow ? 'border border-danger border-2' : '' ?>">
            <div class="card-body p-4">
                <!-- Header kit -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-<?= $badgeColor ?> mb-2 px-3 py-1 rounded-pill">
                            <?= $kit['id'] == 1 ? 'Reguler' : 'Newbie' ?>
                        </span>
                        <h5 class="fw-bold mb-0"><?= htmlspecialchars($kit['name']) ?></h5>
                        <p class="text-muted small mb-0 mt-1"><?= htmlspecialchars($kit['description'] ?? '') ?></p>
                    </div>
                    <?php if ($isLow): ?>
                    <span class="badge bg-danger rounded-pill"><i class="bi bi-exclamation-triangle me-1"></i>Stok Menipis</span>
                    <?php endif; ?>
                </div>

                <!-- Stok info -->
                <div class="row g-2 mb-3">
                    <div class="col-4 text-center p-2 bg-light rounded-3">
                        <div class="fs-2 fw-bold text-<?= $barColor ?>"><?= $kit['stock'] ?></div>
                        <div class="text-muted small">Stok Tersedia</div>
                    </div>
                    <div class="col-4 text-center p-2 bg-light rounded-3">
                        <div class="fs-2 fw-bold text-secondary"><?= $kit['min_stock'] ?></div>
                        <div class="text-muted small">Stok Minimum</div>
                    </div>
                    <div class="col-4 text-center p-2 bg-light rounded-3">
                        <div class="fs-2 fw-bold text-info"><?= $pct ?>%</div>
                        <div class="text-muted small">Level Stok</div>
                    </div>
                </div>

                <div class="progress mb-3" style="height:8px">
                    <div class="progress-bar bg-<?= $barColor ?>" style="width:<?= $pct ?>%"></div>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-flex gap-2 flex-wrap">
                    <!-- WD button - semua role -->
                    <button class="btn btn-<?= $badgeColor ?> rounded-3 flex-fill"
                            data-bs-toggle="modal" data-bs-target="#wdModal"
                            data-kit-id="<?= $kit['id'] ?>"
                            data-kit-name="<?= htmlspecialchars($kit['name']) ?>"
                            data-kit-stock="<?= $kit['stock'] ?>"
                            <?= $kit['stock'] == 0 ? 'disabled' : '' ?>>
                        <i class="bi bi-box-arrow-up me-1"></i>WD P3K
                    </button>

                    <!-- Tambah stok - hanya admin/pimpinan -->
                    <?php if ($isAdmin): ?>
                    <button class="btn btn-outline-success rounded-3"
                            data-bs-toggle="modal" data-bs-target="#addStockModal"
                            data-kit-id="<?= $kit['id'] ?>"
                            data-kit-name="<?= htmlspecialchars($kit['name']) ?>">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Stok
                    </button>
                    <?php if ($kit['stock'] == 0): ?>
                    <span class="badge bg-danger align-self-center px-3 py-2">Stok Habis</span>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Panduan P3K -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 pb-0">
        <h6 class="fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>Informasi Kit P3K</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 bg-primary bg-opacity-10 rounded-3">
                    <h6 class="fw-bold text-primary"><i class="bi bi-box-fill me-2"></i>P3K Reguler</h6>
                    <ul class="small text-muted mb-0">
                        <li>Perban elastis &amp; kasa steril</li>
                        <li>Betadine antiseptik</li>
                        <li>Plester berbagai ukuran</li>
                        <li>Gunting medis &amp; pinset</li>
                        <li>Sarung tangan steril</li>
                        <li>Antiseptik cair</li>
                        <li>Termometer</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-success bg-opacity-10 rounded-3">
                    <h6 class="fw-bold text-success"><i class="bi bi-box me-2"></i>P3K Newbie</h6>
                    <ul class="small text-muted mb-0">
                        <li>Perban kasa steril</li>
                        <li>Plester luka</li>
                        <li>Antiseptik cair</li>
                        <li>Kapas steril</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ====================== MODAL WD ====================== -->
<div class="modal fade" id="wdModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form method="POST" action="<?= site_url('p3k/withdraw') ?>">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold"><i class="bi bi-box-arrow-up me-2 text-danger"></i>WD P3K</h5>
                        <p class="text-muted small mb-0" id="wdKitName"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="kit_id" id="wdKitId">

                    <div class="alert alert-info small rounded-3 py-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Stok tersedia: <strong id="wdKitStock">0</strong> unit
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="wdQty" class="form-control"
                               value="1" min="1" required>
                        <div class="invalid-feedback">Jumlah melebihi stok tersedia.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Link Bukti WD Discord <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-discord text-primary"></i></span>
                            <input type="url" name="purpose" class="form-control"
                                   placeholder="https://discord.com/channels/..." required>
                        </div>
                        <div class="form-text">Paste link pesan bukti WD dari Discord.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-3" id="wdSubmitBtn">
                        <i class="bi bi-check-circle me-1"></i>Konfirmasi WD
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ====================== MODAL TAMBAH STOK (admin/pimpinan) ====================== -->
<?php if ($isAdmin): ?>
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form method="POST" action="<?= site_url('p3k/add_stock') ?>">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2 text-success"></i>Tambah Stok P3K</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="kit_id" id="addKitId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jenis Kit</label>
                        <input type="text" id="addKitName" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah Tambahan <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" min="1" placeholder="Masukkan jumlah" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-3">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Isi modal WD
document.getElementById('wdModal').addEventListener('show.bs.modal', function(e) {
    var btn   = e.relatedTarget;
    var stock = parseInt(btn.getAttribute('data-kit-stock'));
    document.getElementById('wdKitId').value    = btn.getAttribute('data-kit-id');
    document.getElementById('wdKitName').textContent = btn.getAttribute('data-kit-name');
    document.getElementById('wdKitStock').textContent = stock;
    var qtyInput = document.getElementById('wdQty');
    qtyInput.setAttribute('max', stock);
    qtyInput.value = 1;
});

// Validasi jumlah sebelum submit
document.getElementById('wdModal').querySelector('form').addEventListener('submit', function(e) {
    var qty   = parseInt(document.getElementById('wdQty').value);
    var stock = parseInt(document.getElementById('wdKitStock').textContent);
    if (qty > stock) {
        e.preventDefault();
        document.getElementById('wdQty').classList.add('is-invalid');
    }
});

// Isi modal tambah stok
<?php if ($isAdmin): ?>
document.getElementById('addStockModal').addEventListener('show.bs.modal', function(e) {
    var btn = e.relatedTarget;
    document.getElementById('addKitId').value   = btn.getAttribute('data-kit-id');
    document.getElementById('addKitName').value = btn.getAttribute('data-kit-name');
});
<?php endif; ?>
</script>
