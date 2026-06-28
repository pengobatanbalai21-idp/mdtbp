<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A0800">
    <title>Login — Sistem Balai Pengobatan Indopride Roleplay</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="login-page">

    <div class="login-card">

        <!-- ── Kiri: Branding ──────────────────────────────── -->
        <div class="login-brand-panel">
            <div class="login-brand-inner">
                <div class="login-logo-wrap">
                    <img src="<?= base_url('assets/images/logo.png') ?>" alt="Logo Klinik">
                </div>
                <h2 class="login-brand-title">Balai Pengobatan<br>Indopride Roleplay</h2>
                <p class="login-brand-sub">Sistem Manajemen Klinik Terpadu</p>

                <ul class="login-feature-list">
                    <li>
                        <span class="lf-icon"><i class="bi bi-clipboard2-pulse"></i></span>
                        <div>
                            <strong>Rekam Medis</strong>
                            <span>Kelola data pasien &amp; riwayat penyakit</span>
                        </div>
                    </li>
                    <li>
                        <span class="lf-icon"><i class="bi bi-box-seam"></i></span>
                        <div>
                            <strong>Stok Obat</strong>
                            <span>Pantau inventaris farmasi secara real-time</span>
                        </div>
                    </li>
                    <li>
                        <span class="lf-icon"><i class="bi bi-calendar2-check"></i></span>
                        <div>
                            <strong>Antrian &amp; Jadwal</strong>
                            <span>Atur kunjungan pasien dengan efisien</span>
                        </div>
                    </li>
                    <li>
                        <span class="lf-icon"><i class="bi bi-graph-up-arrow"></i></span>
                        <div>
                            <strong>Laporan &amp; Statistik</strong>
                            <span>Analisis data layanan kesehatan</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- ── Kanan: Form Login ───────────────────────────── -->
        <div class="login-form-panel">
            <div class="login-form-inner">

                <div class="mb-4">
                    <h4 class="fw-bold mb-1" style="color:#F5ECC0;font-size:1.35rem;letter-spacing:-.01em">
                        Selamat Datang
                    </h4>
                    <p class="mb-0" style="font-size:.82rem;color:rgba(212,160,23,.5)">
                        Masuk untuk mengakses dashboard
                    </p>
                </div>

                <?php if (!empty($error)): ?>
                <div class="alert d-flex align-items-center gap-2 py-2 px-3 mb-4"
                     style="background:rgba(220,38,38,.12);border:1px solid rgba(220,38,38,.3);border-radius:10px;color:#FCA5A5"
                     role="alert">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0" style="color:#F87171"></i>
                    <span style="font-size:.85rem"><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?= site_url('auth/login') ?>" autocomplete="on">

                    <div class="mb-3">
                        <label style="display:block;font-size:.72rem;font-weight:700;color:rgba(212,160,23,.6);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem">
                            Username
                        </label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-person icon-left"></i>
                            <input type="text" name="username" class="login-input"
                                   placeholder="Masukkan username"
                                   value="<?= set_value('username') ?>"
                                   required autofocus autocomplete="username">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label style="display:block;font-size:.72rem;font-weight:700;color:rgba(212,160,23,.6);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem">
                            Password
                        </label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-lock icon-left"></i>
                            <input type="password" name="password" id="pwInput" class="login-input"
                                   placeholder="Masukkan password"
                                   required autocomplete="current-password">
                            <button type="button" class="btn-eye" id="pwToggle" aria-label="Toggle password">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                    </button>
                </form>

                <!-- Demo accounts -->
                <div class="mt-4 pt-3" style="border-top:1px solid rgba(212,160,23,.12)">
                    <p class="text-center mb-2" style="font-size:.68rem;color:rgba(212,160,23,.4);text-transform:uppercase;letter-spacing:.08em;font-weight:700">
                        Akun Demo
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <span class="demo-chip">admin / password</span>
                        <span class="demo-chip">pimpinan / password</span>
                        <span class="demo-chip">budi / password</span>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('pwToggle').addEventListener('click', function () {
            var inp  = document.getElementById('pwInput');
            var icon = document.getElementById('eyeIcon');
            var show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>
</body>
</html>
