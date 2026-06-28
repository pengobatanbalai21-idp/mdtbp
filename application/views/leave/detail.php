<?php
$typeLabel   = ['izin' => 'Izin', 'sakit' => 'Sakit', 'cuti' => 'Cuti'];
$typeColor   = ['izin' => 'warning', 'sakit' => 'info', 'cuti' => 'success'];
$statusLabel = ['pending' => 'Menunggu Persetujuan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];
$statusColor = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
$statusIcon  = ['pending' => 'hourglass-split', 'approved' => 'check-circle-fill', 'rejected' => 'x-circle-fill'];
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= site_url('leave') ?>" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-file-text me-2 text-primary"></i>Detail Pengajuan Izin</h4>
        <p class="text-muted small mb-0">ID Pengajuan #<?= $request['id'] ?></p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                <!-- Status banner -->
                <div class="p-3 rounded-3 mb-4 bg-<?= $statusColor[$request['status']] ?> bg-opacity-10 border border-<?= $statusColor[$request['status']] ?> border-opacity-25">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-<?= $statusIcon[$request['status']] ?> text-<?= $statusColor[$request['status']] ?> fs-5"></i>
                        <span class="fw-bold text-<?= $statusColor[$request['status']] ?>"><?= $statusLabel[$request['status']] ?></span>
                        <?php if ($request['reviewed_at']): ?>
                        <span class="text-muted small ms-auto">
                            <?= date('d M Y H:i', strtotime($request['reviewed_at'])) ?>
                            <?php if ($request['nama_reviewer']): ?> oleh <?= htmlspecialchars($request['nama_reviewer']) ?><?php endif; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($request['review_note']): ?>
                    <div class="mt-2 small text-muted border-top border-<?= $statusColor[$request['status']] ?> border-opacity-25 pt-2">
                        <i class="bi bi-chat-quote me-1"></i><em><?= htmlspecialchars($request['review_note']) ?></em>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Info pengajuan -->
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Pemohon</div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm"><?= strtoupper(substr($request['nama_user'],0,1)) ?></div>
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($request['nama_user']) ?></div>
                                <small class="text-muted"><?= str_replace('_',' ',ucwords($request['jabatan'],'_')) ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Jenis</div>
                        <span class="badge bg-<?= $typeColor[$request['type']] ?>-subtle text-<?= $typeColor[$request['type']] ?> px-3 py-2 rounded-pill fs-6">
                            <?= $typeLabel[$request['type']] ?>
                        </span>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Tanggal Mulai</div>
                        <div class="fw-semibold"><?= date('l, d F Y', strtotime($request['start_date'])) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Tanggal Selesai</div>
                        <div class="fw-semibold"><?= date('l, d F Y', strtotime($request['end_date'])) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Durasi</div>
                        <div class="fw-bold text-primary fs-5"><?= $request['days'] ?> <span class="fs-6 fw-normal text-muted">hari kerja</span></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Diajukan</div>
                        <div><?= date('d M Y H:i', strtotime($request['created_at'])) ?></div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Alasan / Keterangan</div>
                        <div class="p-3 bg-light rounded-3"><?= nl2br(htmlspecialchars($request['reason'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aksi (admin/pimpinan) -->
    <?php if ($isAdmin && $request['status'] === 'pending'): ?>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 border-top border-4 border-warning">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-shield-check me-2 text-warning"></i>Tindakan Review</h6>

                <!-- Form Setujui -->
                <form method="POST" action="<?= site_url('leave/approve/'.$request['id']) ?>" class="mb-3">
                    <label class="form-label small fw-semibold">Catatan Persetujuan (opsional)</label>
                    <textarea name="review_note" class="form-control form-control-sm rounded-3 mb-2" rows="2"
                              placeholder="Tambahkan catatan..."></textarea>
                    <button type="submit" class="btn btn-success w-100 rounded-3"
                            onclick="return confirm('Setujui pengajuan ini?')">
                        <i class="bi bi-check-lg me-1"></i>Setujui Pengajuan
                    </button>
                </form>

                <hr>

                <!-- Form Tolak -->
                <form method="POST" action="<?= site_url('leave/reject/'.$request['id']) ?>">
                    <label class="form-label small fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="review_note" class="form-control form-control-sm rounded-3 mb-2" rows="2"
                              placeholder="Jelaskan alasan penolakan..." required></textarea>
                    <button type="submit" class="btn btn-danger w-100 rounded-3"
                            onclick="return confirm('Tolak pengajuan ini?')">
                        <i class="bi bi-x-lg me-1"></i>Tolak Pengajuan
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
