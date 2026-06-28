<?php
// Flash messages helper macro
function flashAlert($type) {
    $ci = get_instance();
    $msg = $ci->session->flashdata($type);
    if ($msg) {
        $cls = $type === 'success' ? 'success' : 'danger';
        echo "<div class='alert alert-{$cls} alert-dismissible fade show rounded-3'>"
           . "<i class='bi bi-" . ($type === 'success' ? 'check-circle' : 'exclamation-circle') . "-fill me-2'></i>"
           . htmlspecialchars($msg)
           . "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Dashboard</h4>
        <p class="text-muted small mb-0">Selamat datang, <?= htmlspecialchars($user['name']) ?>!</p>
    </div>
    <div class="text-end">
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-6" id="dashClockBadge">--:--:-- WIB</span>
    </div>
</div>

<?php flashAlert('success'); flashAlert('error'); ?>

<!-- Attendance Today Card -->
<div class="card border-0 shadow-sm mb-4 rounded-4">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-md-5">
                <h5 class="fw-bold mb-1"><i class="bi bi-clock me-2 text-primary"></i>Absensi Hari Ini</h5>
                <p class="text-muted small mb-3" id="dashDateText">--</p>
                <?php if ($todayAttendance): ?>
                <div class="d-flex gap-3 flex-wrap">
                    <div class="att-badge">
                        <span class="label">Clock In</span>
                        <span class="value text-success"><?= $todayAttendance['clock_in'] ? date('H:i', strtotime($todayAttendance['clock_in'])) : '-' ?></span>
                    </div>
                    <div class="att-badge">
                        <span class="label">Clock Out</span>
                        <span class="value text-danger"><?= $todayAttendance['clock_out'] ? date('H:i', strtotime($todayAttendance['clock_out'])) : '-' ?></span>
                    </div>
                    <div class="att-badge">
                        <span class="label">Jam Kerja</span>
                        <span class="value text-primary"><?= $todayAttendance['work_hours'] ?? 0 ?> jam</span>
                    </div>
                </div>
                <?php else: ?>
                <p class="text-warning mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Belum absen hari ini.</p>
                <?php endif; ?>
            </div>

            <div class="col-md-7 d-flex gap-2 justify-content-md-end flex-wrap">
                <a href="<?= site_url('attendance/clock_in') ?>" class="btn btn-success btn-lg rounded-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Clock In
                </a>
                <a href="<?= site_url('attendance/clock_out') ?>" class="btn btn-danger btn-lg rounded-3">
                    <i class="bi bi-box-arrow-right me-2"></i>Clock Out
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $stats['hadir'] ?></div>
                <div class="stat-label">Hadir Hari Ini</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-orange">
            <div class="stat-icon"><i class="bi bi-person-x-fill"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $stats['belum'] ?></div>
                <div class="stat-label">Belum Absen</div>
            </div>
        </div>
    </div>
    <?php if (isset($finance)): ?>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="stat-body">
                <div class="stat-value">Rp <?= number_format($todaySales ?? 0, 0, ',', '.') ?></div>
                <div class="stat-label">Penjualan Hari Ini</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
            <div class="stat-body">
                <div class="stat-value">Rp <?= number_format($finance['balance'] ?? 0, 0, ',', '.') ?></div>
                <div class="stat-label">Saldo Bulan Ini</div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="col-6 col-md-3">
        <a href="<?= site_url('medicines/sell') ?>" class="text-decoration-none">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-bag-heart-fill"></i></div>
            <div class="stat-body">
                <div class="stat-value"><i class="bi bi-arrow-right-circle fs-4"></i></div>
                <div class="stat-label">Jual Obat</div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= site_url('attendance') ?>" class="text-decoration-none">
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="bi bi-calendar-check-fill"></i></div>
            <div class="stat-body">
                <div class="stat-value"><i class="bi bi-arrow-right-circle fs-4"></i></div>
                <div class="stat-label">Riwayat Absensi</div>
            </div>
        </div>
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Pengajuan Izin Pending (admin) atau izin saya (user) -->
<?php if (isset($pendingLeave) && $pendingLeave > 0): ?>
<div class="card border-0 border-start border-4 border-danger shadow-sm mb-4 rounded-4">
    <div class="card-body d-flex justify-content-between align-items-center py-3">
        <div>
            <span class="fw-bold text-danger"><i class="bi bi-calendar2-x me-2"></i>Ada <?= $pendingLeave ?> pengajuan izin menunggu persetujuan</span>
        </div>
        <a href="<?= site_url('leave?status=pending') ?>" class="btn btn-sm btn-danger rounded-3">
            Review Sekarang <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
</div>
<?php elseif (isset($myPendingLeave) && $myPendingLeave > 0): ?>
<div class="card border-0 border-start border-4 border-warning shadow-sm mb-4 rounded-4">
    <div class="card-body d-flex justify-content-between align-items-center py-3">
        <div>
            <span class="fw-bold text-warning"><i class="bi bi-hourglass-split me-2"></i><?= $myPendingLeave ?> pengajuan izin Anda sedang menunggu persetujuan</span>
        </div>
        <a href="<?= site_url('leave') ?>" class="btn btn-sm btn-warning rounded-3">
            Lihat Status <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Low Stock Warning -->
<?php if (!empty($lowStock)): ?>
<div class="card border-0 border-start border-4 border-warning shadow-sm mb-4 rounded-4">
    <div class="card-body">
        <h6 class="fw-bold text-warning mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>Stok Menipis</h6>
        <div class="row g-2">
            <?php foreach ($lowStock as $m): ?>
            <div class="col-md-4">
                <div class="d-flex justify-content-between align-items-center bg-warning bg-opacity-10 rounded-3 px-3 py-2">
                    <span class="fw-semibold"><?= htmlspecialchars($m['name']) ?></span>
                    <span class="badge bg-warning text-dark"><?= $m['stock'] ?> <?= $m['unit'] ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Links -->
<div class="row g-3">
    <div class="col-md-3">
        <a href="<?= site_url('attendance') ?>" class="card border-0 shadow-sm rounded-4 quick-link-card text-decoration-none">
            <div class="card-body text-center py-4">
                <i class="bi bi-clock-history text-primary mb-2" style="font-size:2.5rem"></i>
                <h6 class="fw-bold">Absensi & Waktu Kerja</h6>
                <p class="text-muted small mb-0">Lihat & kelola absensi</p>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= site_url('medicines/sell') ?>" class="card border-0 shadow-sm rounded-4 quick-link-card text-decoration-none">
            <div class="card-body text-center py-4">
                <i class="bi bi-bag-plus text-success mb-2" style="font-size:2.5rem"></i>
                <h6 class="fw-bold">Jual / WD Obat</h6>
                <p class="text-muted small mb-0">Catat penjualan obat</p>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= site_url('medicines') ?>" class="card border-0 shadow-sm rounded-4 quick-link-card text-decoration-none">
            <div class="card-body text-center py-4">
                <i class="bi bi-capsule text-info mb-2" style="font-size:2.5rem"></i>
                <h6 class="fw-bold">Stok Obat</h6>
                <p class="text-muted small mb-0">Pantau stok obat-obatan</p>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= site_url('leave/create') ?>" class="card border-0 shadow-sm rounded-4 quick-link-card text-decoration-none">
            <div class="card-body text-center py-4">
                <i class="bi bi-calendar2-plus text-warning mb-2" style="font-size:2.5rem"></i>
                <h6 class="fw-bold">Ajukan Izin</h6>
                <p class="text-muted small mb-0">Ajukan izin / sakit / cuti</p>
            </div>
        </a>
    </div>
</div>
