<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-clock-history me-2 text-danger"></i>Riwayat WD P3K
            <?php if (!$isAdmin): ?><span class="badge bg-primary-subtle text-primary ms-2 fs-6">Milik Saya</span><?php endif; ?>
        </h4>
        <p class="text-muted small mb-0">
            <?= $isAdmin ? 'Seluruh riwayat withdraw kit P3K' : 'Riwayat withdraw P3K yang Anda lakukan' ?>
        </p>
    </div>
    <a href="<?= site_url('p3k') ?>" class="btn btn-outline-secondary rounded-3">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<!-- Filter -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Dari</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Sampai</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Jenis Kit</label>
                <select name="kit_id" class="form-select">
                    <option value="">Semua Kit</option>
                    <?php foreach ($kits as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= $kit_filter == $k['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100 rounded-3"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary cards -->
<?php
$totalWd      = array_sum(array_column($history, 'quantity'));
$totalRecords = count($history);
$byKit = [];
foreach ($history as $h) {
    $byKit[$h['kit_name']] = ($byKit[$h['kit_name']] ?? 0) + $h['quantity'];
}
?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-danger"><?= $totalRecords ?></div>
                <div class="text-muted small">Total Transaksi WD</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-primary"><?= $totalWd ?></div>
                <div class="text-muted small">Total Unit Diambil</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body py-3">
                <?php foreach ($byKit as $kitName => $qty): ?>
                <div class="d-flex justify-content-between">
                    <span class="small"><?= htmlspecialchars($kitName) ?></span>
                    <span class="fw-bold"><?= $qty ?> unit</span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($byKit)): ?>
                <div class="text-center text-muted small py-2">Tidak ada data</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Riwayat -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <?php
        $_pg    = max(1, (int)($_GET['page'] ?? 1));
        $_pp    = 5;
        $_all   = array_values($history);
        $_tot   = count($_all);
        $_pages = max(1, (int)ceil($_tot / $_pp));
        $_pg    = min($_pg, $_pages);
        $_from  = ($_pg - 1) * $_pp;
        $_items = array_slice($_all, $_from, $_pp);
        ?>
        <?php if (empty($_all)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-heart-pulse fs-1"></i>
            <p class="mt-2">Tidak ada riwayat WD P3K untuk periode ini.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tanggal & Jam</th>
                        <?php if ($isAdmin): ?><th>Petugas</th><?php endif; ?>
                        <th>Jenis Kit</th>
                        <th>Qty</th>
                        <th>Keperluan</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_items as $i => $row): ?>
                    <tr>
                        <td class="text-muted small"><?= $_from + $i + 1 ?></td>
                        <td>
                            <div class="fw-semibold"><?= date('d M Y', strtotime($row['created_at'])) ?></div>
                            <small class="text-muted"><?= date('H:i', strtotime($row['created_at'])) ?> WIB</small>
                        </td>
                        <?php if ($isAdmin): ?>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($row['petugas']) ?></div>
                            <small class="text-muted"><?= str_replace('_',' ', ucwords($row['jabatan'], '_')) ?></small>
                        </td>
                        <?php endif; ?>
                        <td>
                            <?php $kitColor = ($row['kit_id'] == 1) ? 'primary' : 'success'; ?>
                            <span class="badge bg-<?= $kitColor ?>-subtle text-<?= $kitColor ?> px-3 py-1 rounded-pill">
                                <i class="bi bi-box-fill me-1"></i><?= htmlspecialchars($row['kit_name']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary rounded-pill"><?= $row['quantity'] ?> unit</span>
                        </td>
                        <td class="text-truncate" style="max-width:180px">
                            <?php $purpose = $row['purpose'] ?? '-'; ?>
                            <?php if (filter_var($purpose, FILTER_VALIDATE_URL)): ?>
                                <a href="<?= htmlspecialchars($purpose) ?>" target="_blank" rel="noopener noreferrer"
                                   class="text-primary text-decoration-none small"
                                   title="<?= htmlspecialchars($purpose) ?>">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Buka Link
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($purpose) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small text-truncate" style="max-width:150px">
                            <?= htmlspecialchars($row['notes'] ?? '-') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($_pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center px-1 pt-3 border-top mt-3">
            <small class="text-muted">Menampilkan <?= $_from + 1 ?>–<?= min($_from + $_pp, $_tot) ?> dari <?= $_tot ?> data</small>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $_pg <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($_GET, ['page' => $_pg - 1])) ?>"><i class="bi bi-chevron-left"></i></a>
                </li>
                <?php
                $showPages = $_pages <= 7 ? range(1, $_pages) : array_unique(array_filter([1, 2, $_pg - 1, $_pg, $_pg + 1, $_pages - 1, $_pages], function($p) use ($_pages) { return $p >= 1 && $p <= $_pages; }));
                sort($showPages); $prev = 0;
                foreach ($showPages as $p):
                    if ($prev && $p - $prev > 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; $prev = $p; ?>
                <li class="page-item <?= $p === $_pg ? 'active' : '' ?>">
                    <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                </li>
                <?php endforeach; ?>
                <li class="page-item <?= $_pg >= $_pages ? 'disabled' : '' ?>">
                    <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($_GET, ['page' => $_pg + 1])) ?>"><i class="bi bi-chevron-right"></i></a>
                </li>
            </ul>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
