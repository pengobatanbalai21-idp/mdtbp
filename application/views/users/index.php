<?php
$ci  = get_instance();
$suc = $ci->session->flashdata('success');
$err = $ci->session->flashdata('error');
$jabatanLabel = ['kepala_klinik' => 'Kepala Klinik', 'perawat' => 'Perawat', 'magang' => 'Magang'];
$roleLabel    = ['admin' => 'Admin', 'pimpinan' => 'Pimpinan', 'user' => 'User'];
$roleBadge    = ['admin' => 'danger', 'pimpinan' => 'warning', 'user' => 'primary'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Manajemen Pengguna</h4>
        <p class="text-muted small mb-0">Kelola akun staf klinik</p>
    </div>
    <a href="<?= site_url('users/create') ?>" class="btn btn-primary rounded-3">
        <i class="bi bi-person-plus me-1"></i>Tambah Pengguna
    </a>
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

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 pb-2">
        <form class="d-flex gap-2" method="GET">
            <div class="input-group" style="max-width:320px">
                <span class="input-group-text bg-light border-end-0 rounded-start-3">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0 rounded-end-3"
                       placeholder="Cari nama atau username…"
                       value="<?= htmlspecialchars($search ?? '') ?>" autocomplete="off">
            </div>
            <?php if (!empty($search)): ?>
            <a href="<?= site_url('users') ?>" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-x"></i>
            </a>
            <?php endif; ?>
        </form>
        <?php if (!empty($search)): ?>
        <p class="text-muted small mt-2 mb-0">
            Hasil pencarian untuk "<strong><?= htmlspecialchars($search) ?></strong>"
        </p>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php
        $_pg    = max(1, (int)($_GET['page'] ?? 1));
        $_pp    = 10;
        $_all   = array_values($users);
        $_tot   = count($_all);
        $_pages = max(1, (int)ceil($_tot / $_pp));
        $_pg    = min($_pg, $_pages);
        $_from  = ($_pg - 1) * $_pp;
        $_items = array_slice($_all, $_from, $_pp);
        ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Jabatan</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_items as $i => $u): ?>
                    <tr>
                        <td><?= $_from + $i + 1 ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($u['name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($u['email'] ?? '') ?></small>
                                </div>
                            </div>
                        </td>
                        <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                        <td><span class="badge bg-<?= $roleBadge[$u['role']] ?>-subtle text-<?= $roleBadge[$u['role']] ?>"><?= $roleLabel[$u['role']] ?></span></td>
                        <td><?= $jabatanLabel[$u['jabatan']] ?? $u['jabatan'] ?></td>
                        <td>
                            <a href="<?= site_url('users/toggle_status/'.$u['id']) ?>" class="badge text-decoration-none <?= $u['status'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $u['status'] ? 'Aktif' : 'Nonaktif' ?>
                            </a>
                        </td>
                        <td class="text-center">
                            <a href="<?= site_url('users/edit/'.$u['id']) ?>" class="btn btn-sm btn-outline-primary rounded-2 me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($u['id'] != $user['id']): ?>
                            <a href="<?= site_url('users/delete/'.$u['id']) ?>" class="btn btn-sm btn-outline-danger rounded-2"
                               onclick="return confirm('Hapus pengguna <?= htmlspecialchars($u['name']) ?>?')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php endif; ?>
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
    </div>
</div>
