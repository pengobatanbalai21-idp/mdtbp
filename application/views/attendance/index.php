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
            <div class="col-md-7">
                <h5 class="fw-bold mb-1">Status Absensi Hari Ini</h5>
                <p class="text-muted small mb-3" id="attDateText">--</p>

                <?php if ($openSession): ?>
                <div class="alert alert-success rounded-3 d-flex align-items-center gap-2 mb-3 py-2">
                    <i class="bi bi-play-circle-fill"></i>
                    <span>Sesi aktif sejak <strong><?= date('H:i', strtotime($openSession['clock_in'])) ?></strong> — jangan lupa Clock Out.</span>
                </div>
                <?php endif; ?>

                <div class="d-flex gap-3 flex-wrap mb-3">
                    <div class="p-3 bg-primary bg-opacity-10 rounded-3 text-center" style="min-width:130px">
                        <div class="text-primary fw-bold fs-5"><?= number_format($todayTotal, 2) ?></div>
                        <div class="text-muted small">Total Jam Hari Ini</div>
                    </div>
                    <div class="p-3 bg-secondary bg-opacity-10 rounded-3 text-center" style="min-width:110px">
                        <div class="fw-bold fs-5"><?= count($todaySessions) ?></div>
                        <div class="text-muted small">Sesi Hari Ini</div>
                    </div>
                </div>

                <?php if (!empty($todaySessions)): ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr class="text-muted small"><th>#</th><th>Clock In</th><th>Clock Out</th><th>Jam</th></tr></thead>
                        <tbody>
                        <?php foreach ($todaySessions as $si => $s): ?>
                            <tr>
                                <td class="text-muted small"><?= $si + 1 ?></td>
                                <td class="text-success fw-semibold"><?= $s['clock_in'] ? date('H:i', strtotime($s['clock_in'])) : '-' ?></td>
                                <td class="<?= $s['clock_out'] ? 'text-danger fw-semibold' : 'text-warning' ?>"><?= $s['clock_out'] ? date('H:i', strtotime($s['clock_out'])) : 'berjalan…' ?></td>
                                <td><?= $s['work_hours'] ? '<strong>'.$s['work_hours'].'</strong> jam' : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-warning rounded-3 mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Belum ada absensi hari ini.</div>
                <?php endif; ?>
            </div>

            <div class="col-md-5 d-flex gap-2 justify-content-md-end flex-wrap align-items-start">
                <?php if ($openSession): ?>
                <a href="<?= site_url('attendance/clock_out') ?>" class="btn btn-danger rounded-3">
                    <i class="bi bi-box-arrow-right me-2"></i>Clock Out
                </a>
                <span class="btn btn-success rounded-3 disabled" aria-disabled="true" title="Clock Out dulu sebelum Clock In lagi">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Clock In
                </span>
                <?php else: ?>
                <a href="<?= site_url('attendance/clock_in') ?>" class="btn btn-success rounded-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Clock In
                </a>
                <span class="btn btn-danger rounded-3 disabled" aria-disabled="true" title="Belum ada sesi aktif">
                    <i class="bi bi-box-arrow-right me-2"></i>Clock Out
                </span>
                <?php endif; ?>
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
                        <?php if (in_array($user['role'], ['admin','pimpinan'])): ?><th class="text-end">Aksi</th><?php endif; ?>
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
                        <?php if (in_array($user['role'], ['admin','pimpinan'])): ?>
                        <td class="text-end text-nowrap">
                            <button class="btn btn-sm btn-outline-primary rounded-3 me-1"
                                    data-bs-toggle="modal" data-bs-target="#editAttModal"
                                    data-id="<?= $row['id'] ?>"
                                    data-name="<?= htmlspecialchars($row['name']) ?>"
                                    data-date="<?= htmlspecialchars(date('d M Y', strtotime($row['date']))) ?>"
                                    data-clockin="<?= $row['clock_in'] ? date('Y-m-d\TH:i', strtotime($row['clock_in'])) : '' ?>"
                                    data-clockout="<?= $row['clock_out'] ? date('Y-m-d\TH:i', strtotime($row['clock_out'])) : '' ?>"
                                    data-status="<?= $row['status'] ?>"
                                    title="Edit waktu">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="<?= site_url('attendance/delete_record/' . $row['id']) ?>" class="d-inline"
                                  onsubmit="return confirm('Hapus record absensi ini?');">
                                <button class="btn btn-sm btn-outline-danger rounded-3" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                        <?php endif; ?>
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

<?php if (in_array($user['role'], ['admin','pimpinan'])): ?>
<!-- Modal Edit Waktu Absensi -->
<div class="modal fade" id="editAttModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4">
      <form method="POST" id="editAttForm">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold">Edit Waktu Absensi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2"><label class="form-label small fw-semibold">Staf</label><input type="text" id="eaName" class="form-control" readonly></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Tanggal</label><input type="text" id="eaDate" class="form-control" readonly></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Clock In</label><input type="datetime-local" name="clock_in" id="eaClockIn" class="form-control"></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Clock Out</label><input type="datetime-local" name="clock_out" id="eaClockOut" class="form-control"></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Status</label>
            <select name="status" id="eaStatus" class="form-select">
              <option value="hadir">Hadir</option>
              <option value="izin">Izin</option>
              <option value="sakit">Sakit</option>
              <option value="alpha">Alpha</option>
            </select>
          </div>
          <div class="form-text">Kosongkan Clock Out bila sesi belum selesai. Jam kerja dihitung ulang otomatis.</div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-3">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
document.getElementById('editAttModal').addEventListener('show.bs.modal', function (e) {
  var b = e.relatedTarget;
  this.querySelector('#editAttForm').action = '<?= site_url('attendance/edit_record') ?>/' + b.getAttribute('data-id');
  this.querySelector('#eaName').value     = b.getAttribute('data-name') || '-';
  this.querySelector('#eaDate').value     = b.getAttribute('data-date') || '';
  this.querySelector('#eaClockIn').value  = b.getAttribute('data-clockin') || '';
  this.querySelector('#eaClockOut').value = b.getAttribute('data-clockout') || '';
  this.querySelector('#eaStatus').value   = b.getAttribute('data-status') || 'hadir';
});
</script>
<?php endif; ?>
