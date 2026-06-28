<?php
$ci  = get_instance();
$suc = $ci->session->flashdata('success');
$err = $ci->session->flashdata('error');
?>

<div class="mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-bag-plus me-2 text-success"></i>Jual / WD Obat</h4>
    <p class="text-muted small mb-0">Catat penjualan atau pengeluaran (withdraw) obat kepada pasien</p>
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

<div class="row g-4">
    <!-- Form Penjualan -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h6 class="fw-bold">Form Penjualan</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= site_url('medicines/sell') ?>" id="sellForm">

                    <!-- Tipe Penjualan -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipe Penjualan</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="sale_type" id="typePackage" value="package" checked>
                            <label class="btn btn-outline-primary rounded-start-3" for="typePackage">
                                <i class="bi bi-box-fill me-1"></i>Paket Obat
                            </label>
                            <input type="radio" class="btn-check" name="sale_type" id="typeItem" value="item">
                            <label class="btn btn-outline-secondary rounded-end-3" for="typeItem">
                                <i class="bi bi-capsule me-1"></i>Per Item
                            </label>
                        </div>
                    </div>

                    <!-- Pilih Paket -->
                    <div id="sectionPackage" class="mb-3">
                        <label class="form-label fw-semibold">Pilih Paket</label>
                        <div class="row g-2">
                            <?php foreach ($packages as $i => $pkg): ?>
                            <div class="col-md-6">
                                <div class="package-option card rounded-3 border <?= $i === 0 ? 'border-primary' : '' ?>"
                                     data-id="<?= $pkg['id'] ?>" data-price="<?= $pkg['price'] ?>" data-name="<?= htmlspecialchars($pkg['name']) ?>">
                                    <div class="card-body py-3 px-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="package_id"
                                                   id="pkg<?= $pkg['id'] ?>" value="<?= $pkg['id'] ?>" <?= $i === 0 ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold" for="pkg<?= $pkg['id'] ?>">
                                                <?= htmlspecialchars($pkg['name']) ?>
                                            </label>
                                        </div>
                                        <div class="text-success fw-bold mt-1">Rp <?= number_format($pkg['price'], 0, ',', '.') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($pkg['description']) ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Pilih Item -->
                    <div id="sectionItem" class="mb-3 d-none">
                        <label class="form-label fw-semibold">Pilih Obat</label>
                        <select name="medicine_id" id="medicineSelect" class="form-select">
                            <?php foreach ($medicines as $med): ?>
                            <option value="<?= $med['id'] ?>" data-price="<?= $med['price'] ?>" data-stock="<?= $med['stock'] ?>">
                                <?= htmlspecialchars($med['name']) ?> - Rp <?= number_format($med['price'], 0, ',', '.') ?>/<?= $med['unit'] ?>
                                (Stok: <?= $med['stock'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Nama Pasien -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Pasien <span class="text-muted">(opsional)</span></label>
                        <input type="text" name="patient_name" class="form-control" placeholder="Nama pasien / penerima">
                    </div>

                    <!-- Jumlah -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah</label>
                        <input type="number" name="quantity" id="quantityInput" class="form-control" value="1" min="1" required>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Catatan <span class="text-muted">(opsional)</span></label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                    </div>

                    <!-- Total Harga -->
                    <div class="p-3 bg-success bg-opacity-10 rounded-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold text-success">Total Harga:</span>
                            <span class="fw-bold fs-5 text-success" id="totalPrice">Rp 0</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 btn-lg rounded-3">
                        <i class="bi bi-check-circle me-2"></i>Konfirmasi Penjualan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar info stok -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top:80px">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h6 class="fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Info Stok & Harga</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Obat</th><th class="text-end">Stok</th><th class="text-end">Harga</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicines as $m): ?>
                        <tr class="<?= $m['stock'] <= $m['min_stock'] ? 'table-warning' : '' ?>">
                            <td><?= htmlspecialchars($m['name']) ?></td>
                            <td class="text-end fw-semibold <?= $m['stock'] <= $m['min_stock'] ? 'text-danger' : 'text-success' ?>">
                                <?= $m['stock'] ?>
                            </td>
                            <td class="text-end">Rp <?= number_format($m['price'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <hr>
                <small class="text-muted"><i class="bi bi-exclamation-triangle text-warning me-1"></i>
                    Baris kuning = stok menipis
                </small>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var pkgPrices = {};
    <?php foreach ($packages as $pkg): ?>
    pkgPrices[<?= $pkg['id'] ?>] = <?= $pkg['price'] ?>;
    <?php endforeach; ?>

    function updateTotal() {
        var type = document.querySelector('input[name="sale_type"]:checked').value;
        var qty  = parseInt(document.getElementById('quantityInput').value) || 1;
        var price = 0;

        if (type === 'package') {
            var checkedPkg = document.querySelector('input[name="package_id"]:checked');
            if (checkedPkg) price = pkgPrices[checkedPkg.value] || 0;
        } else {
            var sel = document.getElementById('medicineSelect');
            price = sel ? parseFloat(sel.options[sel.selectedIndex].dataset.price) || 0 : 0;
        }
        var total = price * qty;
        document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    document.querySelectorAll('input[name="sale_type"]').forEach(function(r) {
        r.addEventListener('change', function() {
            var isPackage = this.value === 'package';
            document.getElementById('sectionPackage').classList.toggle('d-none', !isPackage);
            document.getElementById('sectionItem').classList.toggle('d-none', isPackage);
            updateTotal();
        });
    });

    document.querySelectorAll('input[name="package_id"]').forEach(function(r) {
        r.addEventListener('change', function() {
            document.querySelectorAll('.package-option').forEach(function(el) { el.classList.remove('border-primary'); });
            this.closest('.package-option').classList.add('border-primary');
            updateTotal();
        });
    });

    document.querySelectorAll('.package-option').forEach(function(el) {
        el.addEventListener('click', function() {
            var radio = this.querySelector('input[type="radio"]');
            if (radio) { radio.checked = true; radio.dispatchEvent(new Event('change')); }
        });
    });

    document.getElementById('medicineSelect').addEventListener('change', updateTotal);
    document.getElementById('quantityInput').addEventListener('input', updateTotal);

    updateTotal();
})();
</script>
