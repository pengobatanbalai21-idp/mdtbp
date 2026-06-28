<?php
$ci  = get_instance();
$err = $ci->session->flashdata('error');
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= site_url('leave') ?>" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-calendar2-plus me-2 text-primary"></i>Ajukan Izin / Cuti</h4>
        <p class="text-muted small mb-0">Isi formulir pengajuan izin di bawah ini</p>
    </div>
</div>

<?php if ($err): ?>
<div class="alert alert-danger alert-dismissible fade show rounded-3">
    <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($err) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Form Pengajuan -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="POST" action="<?= site_url('leave/create') ?>">

                    <!-- Jenis Izin -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Jenis Pengajuan <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="type" id="typeIzin" value="izin" checked>
                                <label class="btn btn-outline-warning w-100 rounded-3 py-3" for="typeIzin">
                                    <i class="bi bi-calendar-event d-block mb-1 fs-5"></i>
                                    <span class="fw-semibold">Izin</span>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="type" id="typeSakit" value="sakit">
                                <label class="btn btn-outline-info w-100 rounded-3 py-3" for="typeSakit">
                                    <i class="bi bi-thermometer d-block mb-1 fs-5"></i>
                                    <span class="fw-semibold">Sakit</span>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="type" id="typeCuti" value="cuti">
                                <label class="btn btn-outline-success w-100 rounded-3 py-3" for="typeCuti">
                                    <i class="bi bi-airplane d-block mb-1 fs-5"></i>
                                    <span class="fw-semibold">Cuti</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="startDate" class="form-control"
                                   value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required
                                   onchange="updateDuration()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="endDate" class="form-control"
                                   value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required
                                   onchange="updateDuration()">
                        </div>
                    </div>

                    <!-- Preview durasi -->
                    <div class="mb-3 p-3 bg-light rounded-3" id="durationBox">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-clock text-primary"></i>
                            <span class="fw-semibold">Durasi: </span>
                            <span id="durationText" class="text-primary fw-bold">1 hari kerja</span>
                        </div>
                    </div>

                    <!-- Alasan -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Alasan / Keterangan <span class="text-danger">*</span>
                        </label>
                        <textarea name="reason" class="form-control rounded-3" rows="4"
                                  placeholder="Jelaskan alasan pengajuan izin Anda secara detail..."
                                  required><?= set_value('reason') ?></textarea>
                        <div class="form-text">Minimal deskripsikan alasan utama pengajuan Anda.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-3 px-4">
                            <i class="bi bi-send me-1"></i>Submit Pengajuan
                        </button>
                        <a href="<?= site_url('leave') ?>" class="btn btn-outline-secondary rounded-3">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info panel -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 bg-light mb-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Informasi Pengajuan</h6>
                <ul class="list-unstyled small text-muted mb-0">
                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Pengajuan akan diproses oleh Admin / Pimpinan</li>
                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Status dapat dipantau di halaman Daftar Pengajuan</li>
                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Jika disetujui, absensi periode tersebut otomatis tercatat</li>
                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Durasi dihitung hari kerja (Senin–Jumat)</li>
                    <li><i class="bi bi-info-circle text-warning me-2"></i>Pengajuan tidak dapat dibatalkan setelah disubmit</li>
                </ul>
            </div>
        </div>

        <!-- Tipe izin info -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-list-ul me-2 text-info"></i>Jenis Pengajuan</h6>
                <div class="mb-2 p-2 bg-warning bg-opacity-10 rounded-3">
                    <span class="badge bg-warning text-dark me-2">Izin</span>
                    <span class="small text-muted">Keperluan pribadi, keluarga, dll.</span>
                </div>
                <div class="mb-2 p-2 bg-info bg-opacity-10 rounded-3">
                    <span class="badge bg-info me-2">Sakit</span>
                    <span class="small text-muted">Tidak dapat masuk karena kondisi kesehatan</span>
                </div>
                <div class="p-2 bg-success bg-opacity-10 rounded-3">
                    <span class="badge bg-success me-2">Cuti</span>
                    <span class="small text-muted">Cuti tahunan / khusus</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateDuration() {
    var start = new Date(document.getElementById('startDate').value);
    var end   = new Date(document.getElementById('endDate').value);

    if (isNaN(start) || isNaN(end) || end < start) {
        document.getElementById('durationText').textContent = '-';
        return;
    }

    var days = 0;
    var cur  = new Date(start);
    while (cur <= end) {
        var dow = cur.getDay();
        if (dow !== 0 && dow !== 6) days++;
        cur.setDate(cur.getDate() + 1);
    }

    document.getElementById('durationText').textContent =
        days + ' hari kerja' + (days > 5 ? ' (' + Math.ceil(days/5) + ' minggu)' : '');

    // Pastikan end_date tidak sebelum start_date
    document.getElementById('endDate').min = document.getElementById('startDate').value;
}

document.getElementById('startDate').addEventListener('change', function() {
    if (document.getElementById('endDate').value < this.value) {
        document.getElementById('endDate').value = this.value;
    }
    updateDuration();
});

updateDuration();
</script>
