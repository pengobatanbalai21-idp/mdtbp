<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-calendar3-week me-2 text-primary"></i>Rekap Kehadiran Mingguan</h4>
        <p class="text-muted small mb-0">Minimal <?= $min_hours ?> jam/minggu &nbsp;·&nbsp; Denda Rp <?= number_format($fine_per_hour, 0, ',', '.') ?>/jam kekurangan</p>
    </div>
    <a href="<?= site_url('attendance') ?>" class="btn btn-outline-secondary rounded-3">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<!-- Filter -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tahun</label>
                <select name="year" class="form-select">
                    <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Bulan</label>
                <select name="month" class="form-select">
                    <option value="0" <?= !$month ? 'selected' : '' ?>>Semua Bulan</option>
                    <?php
                    $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
                    foreach ($bulan as $i => $b):
                    ?>
                    <option value="<?= $i+1 ?>" <?= $month == ($i+1) ? 'selected' : '' ?>><?= $b ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Staf</label>
                <select name="user_id" class="form-select">
                    <option value="">Semua Staf</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $user_filter == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100 rounded-3"><i class="bi bi-search me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<?php if ($total_denda > 0): ?>
<div class="alert border-0 rounded-4 mb-4 d-flex align-items-center gap-3"
     style="background:rgba(220,38,38,.08);border-left:4px solid #ef4444!important">
    <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
    <div>
        <div class="fw-bold text-danger">Total Denda Periode Ini</div>
        <div class="fs-5 fw-bold">Rp <?= number_format($total_denda, 0, ',', '.') ?></div>
    </div>
</div>
<?php endif; ?>

<?php if (empty($recap)): ?>
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
        <p class="mb-0">Tidak ada data kehadiran untuk periode ini.</p>
    </div>
</div>
<?php else: ?>

<?php
// Group by user for subtotal rows
$byUser = [];
foreach ($recap as $row) {
    $uid = $row['user_id'];
    if (!isset($byUser[$uid])) {
        $byUser[$uid] = ['name' => $row['name'], 'jabatan' => $row['jabatan'], 'total_jam' => 0, 'total_denda' => 0];
    }
    $byUser[$uid]['total_jam']   += $row['total_jam'];
    $byUser[$uid]['total_denda'] += $row['denda'];
}
$prevUid = null;
?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama / Jabatan</th>
                        <th>Periode Minggu</th>
                        <th class="text-center">Hari Hadir</th>
                        <th class="text-center">Total Jam</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Kurang</th>
                        <th class="text-end pe-4">Denda</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recap as $row):
                    $uid = $row['user_id'];
                    $isNewUser = ($uid !== $prevUid);
                    $rowCount  = array_sum(array_map(function($r) use ($uid) { return $r['user_id'] == $uid ? 1 : 0; }, $recap));
                    $prevUid   = $uid;
                ?>

                <?php if ($isNewUser && $byUser[$uid]['total_denda'] > 0 && !($recap[0]['user_id'] == $uid)): ?>
                <!-- subtotal baris sebelumnya sudah di-render di tfoot, ini hanya spacer visual -->
                <?php endif; ?>

                <tr class="<?= $row['denda'] > 0 ? 'table-danger' : '' ?>">
                    <?php if ($isNewUser): ?>
                    <td class="ps-4" rowspan="<?= count(array_filter($recap, function($r) use ($uid){ return $r['user_id'] == $uid; })) ?>">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm flex-shrink-0"><?= strtoupper(substr($row['name'], 0, 1)) ?></div>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($row['name']) ?></div>
                                <small class="text-muted"><?= str_replace('_',' ', ucwords($row['jabatan'],'_')) ?></small>
                                <div class="mt-1">
                                    <small class="text-muted">Total: <strong><?= number_format($byUser[$uid]['total_jam'], 1) ?> jam</strong></small>
                                    <?php if ($byUser[$uid]['total_denda'] > 0): ?>
                                    <br><small class="text-danger fw-bold">Denda: Rp <?= number_format($byUser[$uid]['total_denda'], 0, ',', '.') ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <?php endif; ?>
                    <td>
                        <div class="fw-semibold">Minggu ke-<?= $row['minggu'] ?>, <?= $row['tahun'] ?></div>
                        <small class="text-muted">
                            <?= date('d M', strtotime($row['tgl_mulai'])) ?> –
                            <?= date('d M Y', strtotime($row['tgl_akhir'])) ?>
                        </small>
                    </td>
                    <td class="text-center"><?= $row['hari_hadir'] ?> hari</td>
                    <td class="text-center">
                        <span class="fw-bold <?= $row['total_jam'] < $min_hours ? 'text-danger' : 'text-success' ?>">
                            <?= number_format($row['total_jam'], 2) ?> jam
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if ($row['kurang'] == 0): ?>
                        <span class="badge bg-success-subtle text-success rounded-pill px-3">
                            <i class="bi bi-check-circle me-1"></i>Terpenuhi
                        </span>
                        <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3">
                            <i class="bi bi-x-circle me-1"></i>Kurang
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center text-danger fw-semibold">
                        <?= $row['kurang'] > 0 ? number_format($row['kurang'], 2) . ' jam' : '-' ?>
                    </td>
                    <td class="text-end pe-4 fw-bold <?= $row['denda'] > 0 ? 'text-danger' : 'text-muted' ?>">
                        <?= $row['denda'] > 0 ? 'Rp ' . number_format($row['denda'], 0, ',', '.') : '-' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <?php if ($total_denda > 0): ?>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="6" class="text-end fw-bold ps-4">Total Keseluruhan Denda:</td>
                        <td class="text-end pe-4 fw-bold text-danger fs-6">Rp <?= number_format($total_denda, 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php endif; ?>
