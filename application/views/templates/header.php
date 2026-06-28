<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0A0800">
    <title><?= isset($title) ? htmlspecialchars($title) . ' — ' : '' ?>Sistem Balai Pengobatan Indopride Roleplay</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

<!-- ── Top Navbar ── -->
<header class="app-navbar">
    <button class="nav-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="bi bi-list"></i>
    </button>

    <a class="nav-brand" href="<?= site_url('dashboard') ?>">
        <span class="brand-logo">
            <img src="<?= base_url('assets/images/logo.png') ?>" alt="Logo Klinik">
        </span>
        <span class="brand-name">Sistem Balai Pengobatan Indopride RolePlay</span>
    </a>

    <div class="nav-spacer"></div>

    <span class="nav-date d-none d-md-flex align-items-center gap-1">
        <i class="bi bi-calendar3" style="opacity:.5"></i>
        <span id="navDateText">--</span>
    </span>

    <div class="dropdown">
        <a class="nav-user" href="#" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="nav-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
            <div class="nav-user-info d-none d-md-block">
                <div class="nav-user-name"><?= htmlspecialchars($user['name']) ?></div>
                <div class="nav-user-role"><?= ucfirst($user['role']) ?> &bull; <?= str_replace('_',' ', ucwords($user['jabatan'],'_')) ?></div>
            </div>
            <i class="bi bi-chevron-down chevron d-none d-md-block"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <div class="px-3 py-2" style="border-bottom:1px solid rgba(212,160,23,.1)">
                    <div class="fw-semibold" style="color:#F5ECC0;font-size:.825rem"><?= htmlspecialchars($user['name']) ?></div>
                    <div style="font-size:.72rem;color:rgba(212,160,23,.5)"><?= htmlspecialchars($user['username']) ?></div>
                </div>
            </li>
            <li class="mt-1">
                <a class="dropdown-item text-danger" href="<?= site_url('auth/logout') ?>">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</header>

<!-- ── Layout Wrapper ── -->
<div class="layout-wrapper">
