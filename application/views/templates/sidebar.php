<!-- ── Sidebar Overlay (mobile) ── -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ── Sidebar ── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-inner">

        <a href="<?= site_url('dashboard') ?>" class="sidebar-brand">
            <span class="s-logo">
                <img src="<?= base_url('assets/images/logo.png') ?>" alt="Logo">
            </span>
            <div>
                <div class="s-name">Balai Pengobatan Indopride Roleplay</div>
                <div class="s-sub">Manajemen Sistem</div>
            </div>
        </a>

        <div class="sidebar-section-label">MENU UTAMA</div>

        <a href="<?= site_url('dashboard') ?>" class="sidebar-link <?= (uri_string() == 'dashboard') ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <a href="<?= site_url('attendance') ?>" class="sidebar-link <?= (strpos(uri_string(),'attendance') !== FALSE && uri_string() !== 'attendance/recap') ? 'active' : '' ?>">
            <i class="bi bi-clock-history"></i>
            <span>Absensi & Waktu Kerja</span>
        </a>

        <?php
        $ci = get_instance();
        $ci->load->model('Leave_model');
        $pendingLeave = in_array($user['role'], ['admin','pimpinan'])
            ? $ci->Leave_model->countPending()
            : 0;
        ?>
        <a href="<?= site_url('leave') ?>" class="sidebar-link <?= (strpos(uri_string(),'leave') !== FALSE) ? 'active' : '' ?>">
            <i class="bi bi-calendar2-check"></i>
            <span>Pengajuan Izin</span>
            <?php if ($pendingLeave > 0): ?>
            <span class="badge bg-danger rounded-pill ms-auto" style="font-size:.62rem"><?= $pendingLeave ?></span>
            <?php endif; ?>
        </a>

        <div class="sidebar-section-label">OBAT-OBATAN</div>

        <a href="<?= site_url('medicines') ?>" class="sidebar-link <?= (uri_string() == 'medicines') ? 'active' : '' ?>">
            <i class="bi bi-capsule"></i>
            <span>Stok Obat</span>
        </a>

        <?php if (in_array($user['role'], ['admin','pimpinan'])): ?>
        <a href="<?= site_url('medicines/sell') ?>" class="sidebar-link <?= (uri_string() == 'medicines/sell') ? 'active' : '' ?>">
            <i class="bi bi-bag-plus"></i>
            <span>Jual / WD Obat</span>
        </a>

        <a href="<?= site_url('medicines/history') ?>" class="sidebar-link <?= (uri_string() == 'medicines/history') ? 'active' : '' ?>">
            <i class="bi bi-receipt"></i>
            <span>Riwayat Penjualan</span>
        </a>
        <?php endif; ?>

        <div class="sidebar-section-label">P3K</div>

        <a href="<?= site_url('p3k') ?>" class="sidebar-link <?= (uri_string() == 'p3k') ? 'active' : '' ?>">
            <i class="bi bi-heart-pulse"></i>
            <span>Stok &amp; WD P3K</span>
        </a>

        <a href="<?= site_url('p3k/history') ?>" class="sidebar-link <?= (uri_string() == 'p3k/history') ? 'active' : '' ?>">
            <i class="bi bi-journal-medical"></i>
            <span>Riwayat WD P3K</span>
        </a>

        <?php if (in_array($user['role'], ['admin','pimpinan'])): ?>
        <div class="sidebar-section-label">ADMINISTRASI</div>

        <a href="<?= site_url('finance') ?>" class="sidebar-link <?= (strpos(uri_string(),'finance') !== FALSE) ? 'active' : '' ?>">
            <i class="bi bi-cash-stack"></i>
            <span>Keuangan</span>
        </a>

        <a href="<?= site_url('attendance/recap') ?>" class="sidebar-link <?= (uri_string() == 'attendance/recap') ? 'active' : '' ?>">
            <i class="bi bi-calendar3-week"></i>
            <span>Rekap Kehadiran</span>
        </a>

        <a href="<?= site_url('users') ?>" class="sidebar-link <?= (strpos(uri_string(),'users') !== FALSE) ? 'active' : '' ?>">
            <i class="bi bi-people"></i>
            <span>Manajemen Pengguna</span>
        </a>

        <a href="<?= site_url('activity_log') ?>" class="sidebar-link <?= (strpos(uri_string(),'activity_log') !== FALSE) ? 'active' : '' ?>">
            <i class="bi bi-activity"></i>
            <span>Log Aktivitas</span>
        </a>
        <?php endif; ?>

        <div class="sidebar-section-label">AKUN</div>

        <a href="<?= site_url('auth/logout') ?>" class="sidebar-link logout-link">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>

    </div>
</aside>

<!-- ── Main Content ── -->
<main class="main-content">
    <div class="container-fluid">
