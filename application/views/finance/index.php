<?php
$ci  = get_instance();
$suc = $ci->session->flashdata('success');
$err = $ci->session->flashdata('error');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-cash-stack me-2 text-success"></i>Manajemen Keuangan</h4>
        <p class="text-muted small mb-0">Pemasukan dan pengeluaran klinik</p>
    </div>
    <button class="btn btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#addFinanceModal">
        <i class="bi bi-plus-circle me-1"></i>Tambah Transaksi
    </button>
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

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 bg-success text-white">
            <div class="card-body d-flex align-items-center gap-3 py-4">
                <div class="fs-1 opacity-75"><i class="bi bi-arrow-down-circle-fill"></i></div>
                <div>
                    <div class="fw-bold fs-5">Rp <?= number_format($summary['income'] ?? 0, 0, ',', '.') ?></div>
                    <div class="opacity-75">Total Pemasukan</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 bg-danger text-white">
            <div class="card-body d-flex align-items-center gap-3 py-4">
                <div class="fs-1 opacity-75"><i class="bi bi-arrow-up-circle-fill"></i></div>
                <div>
                    <div class="fw-bold fs-5">Rp <?= number_format($summary['expense'] ?? 0, 0, ',', '.') ?></div>
                    <div class="opacity-75">Total Pengeluaran</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <?php $balance = $summary['balance'] ?? 0; ?>
        <div class="card border-0 shadow-sm rounded-4 <?= $balance >= 0 ? 'bg-primary' : 'bg-warning' ?> text-white">
            <div class="card-body d-flex align-items-center gap-3 py-4">
                <div class="fs-1 opacity-75"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="fw-bold fs-5">Rp <?= number_format(abs($balance), 0, ',', '.') ?></div>
                    <div class="opacity-75">Saldo (Periode ini)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter & List -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-4 pb-2">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tipe</label>
                <select name="type" class="form-select">
                    <option value="">Semua</option>
                    <option value="income"  <?= $type_filter === 'income'  ? 'selected' : '' ?>>Pemasukan</option>
                    <option value="expense" <?= $type_filter === 'expense' ? 'selected' : '' ?>>Pengeluaran</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100 rounded-3"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
    <div class="card-body">
        <?php
        $_pg    = max(1, (int)($_GET['page'] ?? 1));
        $_pp    = 10;
        $_all   = array_values($records);
        $_tot   = count($_all);
        $_pages = max(1, (int)ceil($_tot / $_pp));
        $_pg    = min($_pg, $_pages);
        $_from  = ($_pg - 1) * $_pp;
        $_items = array_slice($_all, $_from, $_pp);
        ?>
        <?php if (empty($_all)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-journal-x fs-1"></i>
            <p class="mt-2">Tidak ada data keuangan untuk periode ini.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th>Petugas</th>
                        <th class="text-end">Jumlah</th>
                        <?php if ($user['role'] === 'admin'): ?><th></th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_items as $r): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($r['date'])) ?></td>
                        <td>
                            <?php if ($r['type'] === 'income'): ?>
                            <span class="badge bg-success-subtle text-success"><i class="bi bi-arrow-down me-1"></i>Pemasukan</span>
                            <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger"><i class="bi bi-arrow-up me-1"></i>Pengeluaran</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(str_replace('_', ' ', $r['category'] ?? '-')) ?></td>
                        <td class="text-truncate" style="max-width:200px"><?= htmlspecialchars($r['description'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($r['petugas']) ?></td>
                        <td class="text-end fw-bold <?= $r['type'] === 'income' ? 'text-success' : 'text-danger' ?>">
                            <?= $r['type'] === 'income' ? '+' : '-' ?>Rp <?= number_format($r['amount'], 0, ',', '.') ?>
                        </td>
                        <?php if ($user['role'] === 'admin'): ?>
                        <td>
                            <a href="<?= site_url('finance/delete/'.$r['id']) ?>" class="btn btn-sm btn-outline-danger rounded-2"
                               onclick="return confirm('Hapus data keuangan ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                        <?php endif; ?>
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

<!-- Modal Tambah Transaksi -->
<div class="modal fade" id="addFinanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form method="POST" action="<?= site_url('finance/add') ?>">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Tambah Transaksi Keuangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipe Transaksi</label>
                        <select name="type" class="form-select" required>
                            <option value="income">Pemasukan</option>
                            <option value="expense">Pengeluaran</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kategori</label>
                        <input type="text" name="category" class="form-control" placeholder="cth: gaji, pembelian obat, listrik">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah (Rp)</label>
                        <input type="number" name="amount" class="form-control" min="1" placeholder="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal</label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Keterangan transaksi..."></textarea>
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
