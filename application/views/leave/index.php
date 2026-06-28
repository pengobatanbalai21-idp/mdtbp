<?php
$ci  = get_instance();
$suc = $ci->session->flashdata('success');
$err = $ci->session->flashdata('error');

$typeLabel   = ['izin' => 'Izin', 'sakit' => 'Sakit', 'cuti' => 'Cuti'];
$typeColor   = ['izin' => 'warning', 'sakit' => 'info', 'cuti' => 'success'];
$statusLabel = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];
$statusColor = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-calendar2-check me-2 text-primary"></i>Pengajuan Izin / Cuti
            <?php if ($isAdmin && $pending > 0): ?>
            <span class="badge bg-danger rounded-pill ms-2"><?= $pending ?></span>
            <?php endif; ?>
        </h4>
        <p class="text-muted small mb-0">
            <?= $isAdmin ? 'Kelola seluruh pengajuan izin staf' : 'Ajukan dan pantau status izin Anda' ?>
        </p>
    </div>
    <a href="<?= site_url('leave/create') ?>" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-1"></i>Ajukan Izin
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

<!-- Summary cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <a href="<?= site_url('leave?status=pending') ?>" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 h-100 <?= ($statusFilter === 'pending') ? 'border border-warning border-2' : '' ?>">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="p-2 bg-warning bg-opacity-15 rounded-3">
                    <i class="bi bi-hourglass-split text-warning fs-4"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 lh-1"><?= $counts['pending'] ?></div>
                    <div class="text-muted small">Menunggu</div>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="<?= site_url('leave?status=approved') ?>" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 h-100 <?= ($statusFilter === 'approved') ? 'border border-success border-2' : '' ?>">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="p-2 bg-success bg-opacity-15 rounded-3">
                    <i class="bi bi-check-circle text-success fs-4"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 lh-1"><?= $counts['approved'] ?></div>
                    <div class="text-muted small">Disetujui</div>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="<?= site_url('leave?status=rejected') ?>" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 h-100 <?= ($statusFilter === 'rejected') ? 'border border-danger border-2' : '' ?>">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="p-2 bg-danger bg-opacity-15 rounded-3">
                    <i class="bi bi-x-circle text-danger fs-4"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 lh-1"><?= $counts['rejected'] ?></div>
                    <div class="text-muted small">Ditolak</div>
                </div>
            </div>
        </div>
        </a>
    </div>
</div>

<!-- Filter bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="pending"  <?= $statusFilter === 'pending'  ? 'selected' : '' ?>>Menunggu</option>
                    <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Jenis</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Semua Jenis</option>
                    <option value="izin"  <?= $typeFilter === 'izin'  ? 'selected' : '' ?>>Izin</option>
                    <option value="sakit" <?= $typeFilter === 'sakit' ? 'selected' : '' ?>>Sakit</option>
                    <option value="cuti"  <?= $typeFilter === 'cuti'  ? 'selected' : '' ?>>Cuti</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100 rounded-3">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
            <?php if ($statusFilter || $typeFilter): ?>
            <div class="col-md-2">
                <a href="<?= site_url('leave') ?>" class="btn btn-outline-secondary btn-sm w-100 rounded-3">
                    <i class="bi bi-x me-1"></i>Reset
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabel pengajuan -->
<div class="card border-0 shadow-sm rounded-4">
    <?php
    $_pg    = max(1, (int)($_GET['page'] ?? 1));
    $_pp    = 5;
    $_all   = array_values($requests);
    $_tot   = count($_all);
    $_pages = max(1, (int)ceil($_tot / $_pp));
    $_pg    = min($_pg, $_pages);
    $_from  = ($_pg - 1) * $_pp;
    $_items = array_slice($_all, $_from, $_pp);
    ?>
    <div class="card-body p-0">
        <?php if (empty($_all)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
            <p class="mb-0">Tidak ada pengajuan izin<?= $statusFilter ? ' dengan status "'.htmlspecialchars($statusLabel[$statusFilter]).'"' : '' ?>.</p>
            <a href="<?= site_url('leave/create') ?>" class="btn btn-primary rounded-3 mt-3">
                <i class="bi bi-plus-circle me-1"></i>Buat Pengajuan Baru
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light rounded-top">
                    <tr>
                        <?php if ($isAdmin): ?><th class="ps-4">Nama</th><?php endif; ?>
                        <th <?= !$isAdmin ? 'class="ps-4"' : '' ?>>Jenis</th>
                        <th>Tanggal</th>
                        <th>Durasi</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Diajukan</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($_items as $req): ?>
                <tr class="<?= $req['status'] === 'pending' && $isAdmin ? 'table-warning bg-opacity-50' : '' ?>">

                    <?php if ($isAdmin): ?>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm"><?= strtoupper(substr($req['nama_user'],0,1)) ?></div>
                            <div>
                                <div class="fw-semibold lh-sm"><?= htmlspecialchars($req['nama_user']) ?></div>
                                <small class="text-muted"><?= str_replace('_',' ',ucwords($req['jabatan'],'_')) ?></small>
                            </div>
                        </div>
                    </td>
                    <?php endif; ?>

                    <td <?= !$isAdmin ? 'class="ps-4"' : '' ?>>
                        <span class="badge bg-<?= $typeColor[$req['type']] ?>-subtle text-<?= $typeColor[$req['type']] ?> rounded-pill px-3 py-1">
                            <i class="bi bi-<?= $req['type']==='sakit' ? 'thermometer' : ($req['type']==='cuti' ? 'airplane' : 'calendar-event') ?> me-1"></i>
                            <?= $typeLabel[$req['type']] ?>
                        </span>
                    </td>

                    <td>
                        <div class="small">
                            <?php if ($req['start_date'] === $req['end_date']): ?>
                            <?= date('d M Y', strtotime($req['start_date'])) ?>
                            <?php else: ?>
                            <?= date('d M', strtotime($req['start_date'])) ?> – <?= date('d M Y', strtotime($req['end_date'])) ?>
                            <?php endif; ?>
                        </div>
                    </td>

                    <td>
                        <span class="fw-semibold"><?= $req['days'] ?></span>
                        <span class="text-muted small"> hari</span>
                    </td>

                    <td>
                        <span class="d-inline-block text-truncate" style="max-width:150px"
                              title="<?= htmlspecialchars($req['reason']) ?>">
                            <?= htmlspecialchars($req['reason']) ?>
                        </span>
                    </td>

                    <td>
                        <span class="badge bg-<?= $statusColor[$req['status']] ?>-subtle text-<?= $statusColor[$req['status']] ?> rounded-pill px-3 py-1">
                            <?php if ($req['status'] === 'pending'): ?>
                            <i class="bi bi-hourglass-split me-1"></i>
                            <?php elseif ($req['status'] === 'approved'): ?>
                            <i class="bi bi-check-lg me-1"></i>
                            <?php else: ?>
                            <i class="bi bi-x-lg me-1"></i>
                            <?php endif; ?>
                            <?= $statusLabel[$req['status']] ?>
                        </span>
                        <?php if ($req['review_note'] && $req['status'] !== 'pending'): ?>
                        <div class="small text-muted mt-1 text-truncate" style="max-width:130px"
                             title="<?= htmlspecialchars($req['review_note']) ?>">
                            <i class="bi bi-chat-quote me-1"></i><?= htmlspecialchars($req['review_note']) ?>
                        </div>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div class="small"><?= date('d M Y', strtotime($req['created_at'])) ?></div>
                        <small class="text-muted"><?= date('H:i', strtotime($req['created_at'])) ?></small>
                    </td>

                    <td class="text-center pe-4">
                        <div class="d-flex gap-1 justify-content-center flex-nowrap">

                            <!-- Tombol Detail -->
                            <a href="<?= site_url('leave/detail/'.$req['id']) ?>"
                               class="btn btn-sm btn-outline-secondary rounded-2" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>

                            <?php if ($isAdmin && $req['status'] === 'pending'): ?>
                            <!-- Tombol Setujui -->
                            <button class="btn btn-sm btn-success rounded-2" title="Setujui"
                                    data-bs-toggle="modal" data-bs-target="#approveModal"
                                    data-id="<?= $req['id'] ?>"
                                    data-name="<?= htmlspecialchars($req['nama_user']) ?>"
                                    data-type="<?= $typeLabel[$req['type']] ?>"
                                    data-dates="<?= date('d M Y', strtotime($req['start_date'])) ?> – <?= date('d M Y', strtotime($req['end_date'])) ?>">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <!-- Tombol Tolak -->
                            <button class="btn btn-sm btn-danger rounded-2" title="Tolak"
                                    data-bs-toggle="modal" data-bs-target="#rejectModal"
                                    data-id="<?= $req['id'] ?>"
                                    data-name="<?= htmlspecialchars($req['nama_user']) ?>"
                                    data-type="<?= $typeLabel[$req['type']] ?>">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <?php endif; ?>

                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($_pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
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

<?php if ($isAdmin): ?>
<!-- ====== MODAL SETUJUI ====== -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form method="POST" id="approveForm" action="">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold text-success">
                            <i class="bi bi-check-circle me-2"></i>Setujui Pengajuan
                        </h5>
                        <p class="text-muted small mb-0" id="approveInfo"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success bg-success bg-opacity-10 border-0 rounded-3 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Setelah disetujui, absensi pada periode tersebut akan otomatis tercatat sebagai <strong>izin/sakit</strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan Persetujuan <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea name="review_note" class="form-control rounded-3" rows="3"
                                  placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-3 px-4">
                        <i class="bi bi-check-lg me-1"></i>Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ====== MODAL TOLAK ====== -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form method="POST" id="rejectForm" action="">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold text-danger">
                            <i class="bi bi-x-circle me-2"></i>Tolak Pengajuan
                        </h5>
                        <p class="text-muted small mb-0" id="rejectInfo"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="review_note" class="form-control rounded-3" rows="3"
                                  placeholder="Jelaskan alasan penolakan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-3 px-4">
                        <i class="bi bi-x-lg me-1"></i>Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var baseUrl = '<?= site_url() ?>';

document.getElementById('approveModal').addEventListener('show.bs.modal', function(e) {
    var btn = e.relatedTarget;
    var id  = btn.getAttribute('data-id');
    document.getElementById('approveForm').action = baseUrl + 'leave/approve/' + id;
    document.getElementById('approveInfo').textContent =
        btn.getAttribute('data-name') + ' – ' + btn.getAttribute('data-type') + ' (' + btn.getAttribute('data-dates') + ')';
});

document.getElementById('rejectModal').addEventListener('show.bs.modal', function(e) {
    var btn = e.relatedTarget;
    var id  = btn.getAttribute('data-id');
    document.getElementById('rejectForm').action = baseUrl + 'leave/reject/' + id;
    document.getElementById('rejectInfo').textContent =
        btn.getAttribute('data-name') + ' – ' + btn.getAttribute('data-type');
});
</script>
<?php endif; ?>
