<?php
$ci  = get_instance();
$suc = $ci->session->flashdata('success');
$err = $ci->session->flashdata('error');
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= site_url('users') ?>" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0"><?= $edit ? 'Edit Pengguna' : 'Tambah Pengguna Baru' ?></h4>
        <p class="text-muted small mb-0">Kelola akun staf klinik</p>
    </div>
</div>

<?php if ($err): ?>
<div class="alert alert-danger alert-dismissible fade show rounded-3">
    <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($err) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="POST" action="<?= site_url($edit ? 'users/edit/'.$editUser['id'] : 'users/create') ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= $edit ? htmlspecialchars($editUser['name']) : set_value('name') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control"
                                   value="<?= $edit ? htmlspecialchars($editUser['username']) : set_value('username') ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= $edit ? htmlspecialchars($editUser['email'] ?? '') : set_value('email') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <?php $selectedRole = $edit ? $editUser['role'] : 'user'; ?>
                                <option value="user"     <?= $selectedRole === 'user'     ? 'selected' : '' ?>>User (Staf)</option>
                                <option value="pimpinan" <?= $selectedRole === 'pimpinan' ? 'selected' : '' ?>>Pimpinan</option>
                                <option value="admin"    <?= $selectedRole === 'admin'    ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jabatan <span class="text-danger">*</span></label>
                            <select name="jabatan" class="form-select" required>
                                <?php $selectedJab = $edit ? $editUser['jabatan'] : 'perawat'; ?>
                                <option value="kepala_klinik" <?= $selectedJab === 'kepala_klinik' ? 'selected' : '' ?>>Kepala Klinik</option>
                                <option value="perawat"       <?= $selectedJab === 'perawat'       ? 'selected' : '' ?>>Perawat</option>
                                <option value="magang"        <?= $selectedJab === 'magang'        ? 'selected' : '' ?>>Magang</option>
                            </select>
                        </div>
                        <?php if ($edit): ?>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" <?= $editUser['status'] ? 'selected' : '' ?>>Aktif</option>
                                <option value="0" <?= !$editUser['status'] ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Password <?= $edit ? '<span class="text-muted fw-normal small">(kosongkan jika tidak diubah)</span>' : '<span class="text-danger">*</span>' ?>
                            </label>
                            <div class="input-group">
                                <input type="password" name="password" id="pw1" class="form-control"
                                       <?= !$edit ? 'required' : '' ?> placeholder="<?= $edit ? 'Isi jika ingin mengubah' : 'Password baru' ?>">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePw('pw1','eye1')">
                                    <i class="bi bi-eye" id="eye1"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Konfirmasi Password <?= !$edit ? '<span class="text-danger">*</span>' : '' ?></label>
                            <input type="password" name="password_confirm" id="pw2" class="form-control"
                                   <?= !$edit ? 'required' : '' ?> placeholder="Ulangi password">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary rounded-3 px-4">
                            <i class="bi bi-save me-1"></i>Simpan
                        </button>
                        <a href="<?= site_url('users') ?>" class="btn btn-outline-secondary rounded-3">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 bg-light">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Panduan Role</h6>
                <div class="mb-3">
                    <span class="badge bg-danger mb-1">Admin</span>
                    <p class="small text-muted mb-0">Akses penuh ke seluruh fitur termasuk manajemen pengguna dan hapus data keuangan.</p>
                </div>
                <div class="mb-3">
                    <span class="badge bg-warning text-dark mb-1">Pimpinan</span>
                    <p class="small text-muted mb-0">Akses penuh ke seluruh fitur termasuk laporan keuangan. Tidak bisa hapus data keuangan.</p>
                </div>
                <div>
                    <span class="badge bg-primary mb-1">User</span>
                    <p class="small text-muted mb-0">Hanya bisa absensi, cek waktu kerja, dan jual/WD obat. Tidak bisa lihat keuangan.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePw(inputId, iconId) {
    var inp  = document.getElementById(inputId);
    var icon = document.getElementById(iconId);
    if (inp.type === 'password') { inp.type = 'text';     icon.className = 'bi bi-eye-slash'; }
    else                         { inp.type = 'password'; icon.className = 'bi bi-eye'; }
}
</script>
