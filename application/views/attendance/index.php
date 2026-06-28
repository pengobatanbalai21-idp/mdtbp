<?php
$ci  = get_instance();
$suc = $ci->session->flashdata('success');
$err = $ci->session->flashdata('error');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Absensi & Waktu Kerja</h4>
        <p class="text-muted small mb-0">Kelola absensi dan catatan jam kerja</p>
    </div>
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

<!-- Clock In / Out Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-md-5">
                <h5 class="fw-bold mb-1">Status Absensi Hari Ini</h5>
                <p class="text-muted small mb-3" id="attDateText">--</p>
                <?php if ($todayAttendance): ?>
                <div class="row g-2">
                    <div class="col-4">
                        <div class="p-3 bg-success bg-opacity-10 rounded-3 text-center">
                            <div class="text-success fw-bold fs-5"><?= $todayAttendance['clock_in'] ? date('H:i', strtotime($todayAttendance['clock_in'])) : '-' ?></div>
                            <div class="text-muted small">Clock In</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-danger bg-opacity-10 rounded-3 text-center">
                            <div class="text-danger fw-bold fs-5"><?= $todayAttendance['clock_out'] ? date('H:i', strtotime($todayAttendance['clock_out'])) : '-' ?></div>
                            <div class="text-muted small">Clock Out</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-primary bg-opacity-10 rounded-3 text-center">
                            <div class="text-primary fw-bold fs-5"><?= number_format($todayAttendance['work_hours'] ?? 0, 1) ?></div>
                            <div class="text-muted small">Jam Kerja</div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-warning rounded-3 mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Belum absen hari ini. Gunakan tombol Clock In di Dashboard.</div>
                <?php endif; ?>
            </div>

            <div class="col-md-7 d-flex gap-2 justify-content-md-end flex-wrap align-items-start">
                <a href="<?= site_url('attendance/clock_in') ?>" class="btn btn-success rounded-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Clock In
                </a>
                <a href="<?= site_url('attendance/clock_out') ?>" class="btn btn-danger rounded-3">
                    <i class="bi bi-box-arrow-right me-2"></i>Clock Out
                </a>
            </div>
        </div>
    </div>
</div>


<!-- Ringkasan Bulan Ini -->
<?php if (!empty($summary)): ?>
<div class="row g-3 mb-4">
    <?php
    $statMap = ['hadir' => ['Hadir', 'success'], 'izin' => ['Izin', 'warning'], 'sakit' => ['Sakit', 'info'], 'alpha' => ['Alpha', 'danger']];
    $totalJam = 0;
    foreach ($summary as $s) $totalJam += $s['jam_kerja'];
    ?>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-primary"><?= number_format($totalJam, 1) ?></div>
                <div class="text-muted small">Total Jam Kerja</div>
            </div>
        </div>
    </div>
    <?php foreach ($summary as $s):
        $info = $statMap[$s['status']] ?? [$s['status'], 'secondary'];
    ?>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-<?= $info[1] ?>"><?= $s['total'] ?></div>
                <div class="text-muted small"><?= $info[0] ?> Bulan Ini</div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Filter & History -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 pb-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-bold mb-0">Riwayat Absensi <?= !in_array($user['role'], ['admin','pimpinan']) ? '(Anda)' : '(Semua Staf)' ?></h6>
            <form class="d-flex gap-2 align-items-center" method="GET">
                <input type="date" name="start_date" class="form-control form-control-sm w-auto" value="<?= htmlspecialchars($start_date) ?>">
                <input type="date" name="end_date"   class="form-control form-control-sm w-auto" value="<?= htmlspecialchars($end_date) ?>">
                <button class="btn btn-sm btn-primary rounded-3"><i class="bi bi-search me-1"></i>Filter</button>
            </form>
        </div>
    </div>
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
            <i class="bi bi-calendar-x fs-1"></i>
            <p class="mt-2">Tidak ada data absensi.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <?php if (in_array($user['role'], ['admin','pimpinan'])): ?><th>Nama</th><?php endif; ?>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Jam Kerja</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_items as $row):
                        $statusBadge = ['hadir'=>'success','izin'=>'warning','sakit'=>'info','alpha'=>'danger'];
                        $badge = $statusBadge[$row['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($row['date'])) ?></td>
                        <?php if (in_array($user['role'], ['admin','pimpinan'])): ?>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($row['name']) ?></div>
                            <small class="text-muted"><?= str_replace('_',' ', ucwords($row['jabatan'],'_')) ?></small>
                        </td>
                        <?php endif; ?>
                        <td><?= $row['clock_in'] ? '<span class="text-success fw-semibold">'.date('H:i', strtotime($row['clock_in'])).'</span>' : '<span class="text-muted">-</span>' ?></td>
                        <td><?= $row['clock_out'] ? '<span class="text-danger fw-semibold">'.date('H:i', strtotime($row['clock_out'])).'</span>' : '<span class="text-muted">-</span>' ?></td>
                        <td><?= $row['work_hours'] ? '<strong>'.$row['work_hours'].'</strong> jam' : '-' ?></td>
                        <td><span class="badge bg-<?= $badge ?>-subtle text-<?= $badge ?>"><?= ucfirst($row['status']) ?></span></td>
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
