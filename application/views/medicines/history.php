<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-receipt me-2 text-primary"></i>Riwayat Penjualan Obat
            <?php if (!$isAdmin): ?>
            <span class="badge bg-primary-subtle text-primary ms-2 fs-6">Milik Saya</span>
            <?php endif; ?>
        </h4>
        <p class="text-muted small mb-0">
            <?= $isAdmin ? 'Seluruh transaksi penjualan obat' : 'Riwayat penjualan obat yang Anda lakukan' ?>
        </p>
    </div>
</div>

<!-- Filter Tanggal -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary w-100 rounded-3"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary -->
<?php
$totalTrx      = count($sales);
$totalPendapatan = array_sum(array_column($sales, 'total_price'));
?>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-primary"><?= $totalTrx ?></div>
                <div class="text-muted small">Total Transaksi</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-success">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></div>
                <div class="text-muted small">Total Pendapatan Periode Ini</div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <?php
        $_pg    = max(1, (int)($_GET['page'] ?? 1));
        $_pp    = 5;
        $_all   = array_values($sales);
        $_tot   = count($_all);
        $_pages = max(1, (int)ceil($_tot / $_pp));
        $_pg    = min($_pg, $_pages);
        $_from  = ($_pg - 1) * $_pp;
        $_items = array_slice($_all, $_from, $_pp);
        ?>
        <?php if (empty($_all)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bag-x fs-1"></i>
            <p class="mt-2">Belum ada riwayat penjualan untuk periode ini.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <?php if ($isAdmin): ?><th>Petugas</th><?php endif; ?>
                        <th>Tipe</th>
                        <th>Pasien</th>
                        <th>Qty</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_items as $i => $s): ?>
                    <tr>
                        <td class="text-muted small"><?= $_from + $i + 1 ?></td>
                        <td>
                            <div><?= date('d M Y', strtotime($s['created_at'])) ?></div>
                            <small class="text-muted"><?= date('H:i', strtotime($s['created_at'])) ?></small>
                        </td>
                        <?php if ($isAdmin): ?>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($s['petugas']) ?></div>
                            <small class="text-muted"><?= str_replace('_',' ',ucwords($s['jabatan'],'_')) ?></small>
                        </td>
                        <?php endif; ?>
                        <td>
                            <?php if ($s['sale_type'] === 'package'): ?>
                            <span class="badge bg-primary-subtle text-primary rounded-pill"><i class="bi bi-box me-1"></i>Paket</span>
                            <?php else: ?>
                            <span class="badge bg-info-subtle text-info rounded-pill"><i class="bi bi-capsule me-1"></i>Item</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($s['patient_name'] ?: '-') ?></td>
                        <td><?= $s['quantity'] ?>x</td>
                        <td class="text-end fw-bold text-success">Rp <?= number_format($s['total_price'], 0, ',', '.') ?></td>
                        <td class="text-center text-nowrap">
                            <?php if (!empty($s['checked_by'])): ?>
                                <span class="badge bg-success-subtle text-success rounded-pill px-2"
                                      title="Diverifikasi <?= $s['checked_at'] ? date('d M Y H:i', strtotime($s['checked_at'])) : '' ?>">
                                    <i class="bi bi-check2-circle me-1"></i><?= htmlspecialchars($s['checker_name'] ?? 'OK') ?>
                                </span>
                                <?php if ($isAdmin): ?>
                                <form method="POST" action="<?= site_url('medicines/check_sale/' . $s['id']) ?>" class="d-inline">
                                    <button class="btn btn-sm btn-link text-danger p-0 ms-1 align-baseline" title="Lepas verifikasi"><i class="bi bi-x-circle"></i></button>
                                </form>
                                <?php endif; ?>
                            <?php elseif ($isAdmin): ?>
                                <form method="POST" action="<?= site_url('medicines/check_sale/' . $s['id']) ?>" class="d-inline">
                                    <button class="btn btn-sm btn-outline-success rounded-3" title="Verifikasi"><i class="bi bi-check2"></i></button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="<?= $isAdmin ? 5 : 4 ?>" class="text-end fw-bold">Total:</td>
                        <td class="fw-bold text-success" colspan="2">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></td>
                        <td></td>
                    </tr>
                </tfoot>
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
