<?php
$actionLabels = [
    'login'            => ['Login',             'success', 'bi-box-arrow-in-right'],
    'logout'           => ['Logout',            'secondary','bi-box-arrow-right'],
    'clock_in'         => ['Clock In',          'info',    'bi-clock'],
    'clock_out'        => ['Clock Out',         'primary', 'bi-clock-history'],
    'jual_obat'        => ['Jual Obat',         'success', 'bi-bag-check'],
    'tambah_stok_obat' => ['Tambah Stok Obat',  'primary', 'bi-plus-circle'],
    'wd_p3k'           => ['WD P3K',            'danger',  'bi-heart-pulse'],
    'setujui_izin'     => ['Setujui Izin',      'success', 'bi-check-circle'],
    'tolak_izin'       => ['Tolak Izin',        'danger',  'bi-x-circle'],
    'tambah_pengguna'  => ['Tambah Pengguna',   'primary', 'bi-person-plus'],
    'edit_pengguna'    => ['Edit Pengguna',      'warning', 'bi-pencil'],
    'hapus_pengguna'   => ['Hapus Pengguna',    'danger',  'bi-person-x'],
    'tambah_keuangan'  => ['Tambah Keuangan',   'success', 'bi-cash'],
    'hapus_keuangan'   => ['Hapus Keuangan',    'danger',  'bi-trash'],
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-activity me-2 text-primary"></i>Log Aktivitas</h4>
        <p class="text-muted small mb-0">Riwayat seluruh aktivitas pengguna sistem</p>
    </div>
    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
        <?= number_format($total, 0, ',', '.') ?> total log
    </span>
</div>

<!-- Filter -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Nama Pengguna</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 rounded-end-3"
                           placeholder="Cari nama pengguna…" value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tipe Aktivitas</label>
                <select name="action" class="form-select">
                    <option value="">Semua Aktivitas</option>
                    <?php foreach ($actions as $a): ?>
                    <option value="<?= htmlspecialchars($a['action']) ?>" <?= $action === $a['action'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($actionLabels[$a['action']][0] ?? $a['action']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100 rounded-3"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
            <?php if ($search || $action): ?>
            <div class="col-md-1">
                <a href="<?= site_url('activity_log') ?>" class="btn btn-outline-secondary w-100 rounded-3"><i class="bi bi-x"></i></a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <?php if (empty($logs)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
            <p class="mb-0">Tidak ada log aktivitas.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Waktu</th>
                        <th>Pengguna</th>
                        <th>Aktivitas</th>
                        <th>Keterangan</th>
                        <th class="pe-4">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log):
                        $al = $actionLabels[$log['action']] ?? [$log['action'], 'secondary', 'bi-dot'];
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold small"><?= date('d M Y', strtotime($log['created_at'])) ?></div>
                            <small class="text-muted"><?= date('H:i:s', strtotime($log['created_at'])) ?></small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm" style="width:28px;height:28px;font-size:.72rem">
                                    <?= strtoupper(substr($log['user_name'] ?? '?', 0, 1)) ?>
                                </div>
                                <span class="fw-semibold"><?= htmlspecialchars($log['user_name'] ?? '-') ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-<?= $al[1] ?>-subtle text-<?= $al[1] ?> rounded-pill px-3 py-1">
                                <i class="bi <?= $al[2] ?> me-1"></i><?= htmlspecialchars($al[0]) ?>
                            </span>
                        </td>
                        <td class="text-muted small text-truncate" style="max-width:260px">
                            <?= htmlspecialchars($log['description'] ?? '-') ?>
                        </td>
                        <td class="pe-4">
                            <code class="small"><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
            <small class="text-muted">
                Menampilkan <?= ($page - 1) * $perPage + 1 ?>–<?= min($page * $perPage, $total) ?> dari <?= number_format($total, 0, ',', '.') ?> log
            </small>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"><i class="bi bi-chevron-left"></i></a>
                </li>
                <?php
                $showPages = $pages <= 7 ? range(1, $pages) : array_unique(array_filter([1, 2, $page-1, $page, $page+1, $pages-1, $pages], function($p) use ($pages) { return $p >= 1 && $p <= $pages; }));
                sort($showPages); $prev = 0;
                foreach ($showPages as $p):
                    if ($prev && $p - $prev > 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; $prev = $p; ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                </li>
                <?php endforeach; ?>
                <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"><i class="bi bi-chevron-right"></i></a>
                </li>
            </ul>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
