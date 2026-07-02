<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-receipt-cutoff me-2 text-primary"></i>Rekap Tagihan
            <?php if (!$is_admin): ?>
            <span class="badge bg-primary-subtle text-primary ms-2 fs-6">Milik Saya</span>
            <?php endif; ?>
        </h4>
        <p class="text-muted small mb-0">
            <?= $is_admin ? 'Denda absensi & tagihan penjualan seluruh staf' : 'Denda absensi & tagihan penjualan milik Anda' ?>
        </p>
    </div>
</div>

<!-- Filter -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3">
        <?php $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des']; ?>
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Tahun</label>
                <select name="year" class="form-select">
                    <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Bulan Dari</label>
                <select name="month_from" class="form-select">
                    <?php foreach ($bulan as $i => $b): ?>
                    <option value="<?= $i+1 ?>" <?= $month_from == ($i+1) ? 'selected' : '' ?>><?= $b ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Bulan Sampai</label>
                <select name="month_to" class="form-select">
                    <?php foreach ($bulan as $i => $b): ?>
                    <option value="<?= $i+1 ?>" <?= $month_to == ($i+1) ? 'selected' : '' ?>><?= $b ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($is_admin): ?>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Cari Nama</label>
                <input type="text" name="name_search" class="form-control" placeholder="Nama staf..." value="<?= htmlspecialchars($name_search) ?>">
            </div>
            <?php endif; ?>
            <div class="col-md-2">
                <button class="btn btn-primary w-100 rounded-3"><i class="bi bi-search me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Ringkasan -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left:4px solid #ef4444!important">
            <div class="card-body py-3">
                <div class="text-muted small">Denda Belum Dibayar</div>
                <div class="fs-4 fw-bold text-danger">Rp <?= number_format($total_denda_belum_bayar, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left:4px solid #f59e0b!important">
            <div class="card-body py-3">
                <div class="text-muted small">Tagihan Penjualan Belum Dibayar</div>
                <div class="fs-4 fw-bold text-warning">Rp <?= number_format($total_sales_belum_bayar, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left:4px solid #0d6efd!important">
            <div class="card-body py-3">
                <div class="text-muted small">Total Paket Terjual</div>
                <div class="fs-4 fw-bold text-primary"><?= number_format($total_paket_qty, 0, ',', '.') ?> paket</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left:4px solid #0dcaf0!important">
            <div class="card-body py-3">
                <div class="text-muted small">Total Obat Terjual</div>
                <div class="fs-4 fw-bold text-info"><?= number_format($total_obat_qty, 0, ',', '.') ?> item</div>
            </div>
        </div>
    </div>
</div>

<!-- ── Denda Absensi ── -->
<div class="d-flex align-items-center gap-2 mb-2 mt-2">
    <i class="bi bi-calendar3-week text-danger"></i>
    <h6 class="fw-bold mb-0">Denda Absensi Mingguan</h6>
    <span class="text-muted small">(minimal <?= $min_hours ?> jam/minggu &middot; Rp <?= number_format($fine_per_hour, 0, ',', '.') ?>/jam kekurangan)</span>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-0">
        <?php
        $_dpg    = max(1, (int)($_GET['page_denda'] ?? 1));
        $_dpp    = 5;
        $_dall   = array_values($denda);
        $_dtot   = count($_dall);
        $_dpages = max(1, (int)ceil($_dtot / $_dpp));
        $_dpg    = min($_dpg, $_dpages);
        $_dfrom  = ($_dpg - 1) * $_dpp;
        $_ditems = array_slice($_dall, $_dfrom, $_dpp);
        ?>
        <?php if (empty($_dall)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-emoji-smile fs-1 d-block mb-2"></i>
            <p class="mb-0">Tidak ada denda absensi pada periode ini.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <?php if ($is_admin): ?><th class="ps-4">Nama / Jabatan</th><?php endif; ?>
                        <th class="<?= $is_admin ? '' : 'ps-4' ?>">Periode Minggu</th>
                        <th class="text-center">Kurang Jam</th>
                        <th class="text-end">Denda</th>
                        <th class="text-center pe-4">Status Bayar</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($_ditems as $row): ?>
                    <tr class="<?= $row['paid'] ? '' : 'table-danger' ?>">
                        <?php if ($is_admin): ?>
                        <td class="ps-4">
                            <div class="fw-bold"><?= htmlspecialchars($row['name']) ?></div>
                            <small class="text-muted"><?= str_replace('_',' ', ucwords($row['jabatan'],'_')) ?></small>
                        </td>
                        <?php endif; ?>
                        <td class="<?= $is_admin ? '' : 'ps-4' ?>">
                            <div class="fw-semibold">Minggu ke-<?= $row['minggu'] ?>, <?= $row['tahun'] ?></div>
                            <small class="text-muted">
                                <?= date('d M', strtotime($row['tgl_mulai'])) ?> –
                                <?= date('d M Y', strtotime($row['tgl_akhir'])) ?>
                            </small>
                        </td>
                        <td class="text-center text-danger fw-semibold"><?= number_format($row['kurang'], 2) ?> jam</td>
                        <td class="text-end fw-bold text-danger">Rp <?= number_format($row['amount'], 0, ',', '.') ?></td>
                        <td class="text-center pe-4 text-nowrap">
                            <?php if ($row['paid']): ?>
                                <span class="badge bg-success-subtle text-success rounded-pill px-2"
                                      title="<?= $row['note'] ? htmlspecialchars($row['note']) . ' — ' : '' ?>Dibayar <?= $row['paid_at'] ? date('d M Y H:i', strtotime($row['paid_at'])) : '' ?> oleh <?= htmlspecialchars($row['payer_name'] ?? '-') ?>">
                                    <i class="bi bi-check2-circle me-1"></i>Lunas
                                </span>
                                <?php if ($is_admin): ?>
                                <form method="POST" action="<?= site_url('tagihan/pay_fine') ?>" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                                    <input type="hidden" name="tahun"   value="<?= $row['tahun'] ?>">
                                    <input type="hidden" name="minggu"  value="<?= $row['minggu'] ?>">
                                    <input type="hidden" name="undo"    value="1">
                                    <button class="btn btn-sm btn-link text-danger p-0 ms-1 align-baseline" title="Batalkan lunas"><i class="bi bi-x-circle"></i></button>
                                </form>
                                <?php endif; ?>
                            <?php elseif ($is_admin): ?>
                                <form method="POST" action="<?= site_url('tagihan/pay_fine') ?>" class="d-flex gap-1 justify-content-center align-items-center flex-nowrap">
                                    <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                                    <input type="hidden" name="tahun"   value="<?= $row['tahun'] ?>">
                                    <input type="hidden" name="minggu"  value="<?= $row['minggu'] ?>">
                                    <input type="text" name="note" class="form-control form-control-sm" style="width:120px" placeholder="Keterangan">
                                    <button class="btn btn-sm btn-outline-success rounded-3" title="Tandai lunas"><i class="bi bi-check2"></i></button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2">Belum Dibayar</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($_dpages > 1): ?>
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
            <small class="text-muted">Menampilkan <?= $_dfrom + 1 ?>–<?= min($_dfrom + $_dpp, $_dtot) ?> dari <?= $_dtot ?> data</small>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $_dpg <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($_GET, ['page_denda' => $_dpg - 1])) ?>"><i class="bi bi-chevron-left"></i></a>
                </li>
                <?php
                $_dshow = $_dpages <= 7 ? range(1, $_dpages) : array_unique(array_filter([1, 2, $_dpg - 1, $_dpg, $_dpg + 1, $_dpages - 1, $_dpages], function($p) use ($_dpages) { return $p >= 1 && $p <= $_dpages; }));
                sort($_dshow); $_dprev = 0;
                foreach ($_dshow as $p):
                    if ($_dprev && $p - $_dprev > 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; $_dprev = $p; ?>
                <li class="page-item <?= $p === $_dpg ? 'active' : '' ?>">
                    <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($_GET, ['page_denda' => $p])) ?>"><?= $p ?></a>
                </li>
                <?php endforeach; ?>
                <li class="page-item <?= $_dpg >= $_dpages ? 'disabled' : '' ?>">
                    <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($_GET, ['page_denda' => $_dpg + 1])) ?>"><i class="bi bi-chevron-right"></i></a>
                </li>
            </ul>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ── Tagihan Penjualan ── -->
<div class="d-flex align-items-center gap-2 mb-2 mt-2">
    <i class="bi bi-receipt text-warning"></i>
    <h6 class="fw-bold mb-0">Tagihan Penjualan Obat</h6>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-0">
        <?php
        $_spg    = max(1, (int)($_GET['page_sales'] ?? 1));
        $_spp    = 5;
        $_sall   = array_values($sales);
        $_stot   = count($_sall);
        $_spages = max(1, (int)ceil($_stot / $_spp));
        $_spg    = min($_spg, $_spages);
        $_sfrom  = ($_spg - 1) * $_spp;
        $_sitems = array_slice($_sall, $_sfrom, $_spp);
        ?>
        <?php if (empty($_sall)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bag-x fs-1 d-block mb-2"></i>
            <p class="mb-0">Tidak ada tagihan penjualan pada periode ini.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tanggal</th>
                        <?php if ($is_admin): ?><th>Petugas</th><?php endif; ?>
                        <th>Pasien</th>
                        <th>Obat/Paket</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Total</th>
                        <th class="text-center pe-4">Status Bayar</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($_sitems as $s): ?>
                    <tr class="<?= $s['is_paid'] ? '' : 'table-warning' ?>">
                        <td class="ps-4">
                            <div><?= date('d M Y', strtotime($s['created_at'])) ?></div>
                            <small class="text-muted"><?= date('H:i', strtotime($s['created_at'])) ?></small>
                        </td>
                        <?php if ($is_admin): ?>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($s['petugas']) ?></div>
                            <small class="text-muted"><?= str_replace('_',' ',ucwords($s['jabatan'],'_')) ?></small>
                        </td>
                        <?php endif; ?>
                        <td><?= htmlspecialchars($s['patient_name'] ?: '-') ?></td>
                        <td>
                            <?php if ($s['sale_type'] === 'package'): ?>
                            <span class="badge bg-primary-subtle text-primary rounded-pill"><i class="bi bi-box me-1"></i>Paket</span>
                            <?php else: ?>
                            <span class="badge bg-info-subtle text-info rounded-pill"><i class="bi bi-capsule me-1"></i>Item</span>
                            <?php endif; ?>
                            <div class="mt-1"><?= htmlspecialchars($s['item_name'] ?: '-') ?></div>
                        </td>
                        <td class="text-center">x<?= $s['quantity'] ?></td>
                        <td class="text-end fw-bold text-success">Rp <?= number_format($s['total_price'], 0, ',', '.') ?></td>
                        <td class="text-center pe-4 text-nowrap">
                            <?php if ($s['is_paid']): ?>
                                <span class="badge bg-success-subtle text-success rounded-pill px-2"
                                      title="<?= $s['payment_note'] ? htmlspecialchars($s['payment_note']) . ' — ' : '' ?>Dibayar <?= $s['paid_at'] ? date('d M Y H:i', strtotime($s['paid_at'])) : '' ?> oleh <?= htmlspecialchars($s['payer_name'] ?? '-') ?>">
                                    <i class="bi bi-check2-circle me-1"></i>Lunas
                                </span>
                                <?php if ($is_admin): ?>
                                <form method="POST" action="<?= site_url('tagihan/pay_sale/' . $s['id']) ?>" class="d-inline">
                                    <button class="btn btn-sm btn-link text-danger p-0 ms-1 align-baseline" title="Batalkan lunas"><i class="bi bi-x-circle"></i></button>
                                </form>
                                <?php endif; ?>
                            <?php elseif ($is_admin): ?>
                                <form method="POST" action="<?= site_url('tagihan/pay_sale/' . $s['id']) ?>" class="d-flex gap-1 justify-content-center align-items-center flex-nowrap">
                                    <input type="text" name="note" class="form-control form-control-sm" style="width:120px" placeholder="Keterangan">
                                    <button class="btn btn-sm btn-outline-success rounded-3" title="Tandai lunas"><i class="bi bi-check2"></i></button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2">Belum Dibayar</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($_spages > 1): ?>
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
            <small class="text-muted">Menampilkan <?= $_sfrom + 1 ?>–<?= min($_sfrom + $_spp, $_stot) ?> dari <?= $_stot ?> data</small>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $_spg <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($_GET, ['page_sales' => $_spg - 1])) ?>"><i class="bi bi-chevron-left"></i></a>
                </li>
                <?php
                $_sshow = $_spages <= 7 ? range(1, $_spages) : array_unique(array_filter([1, 2, $_spg - 1, $_spg, $_spg + 1, $_spages - 1, $_spages], function($p) use ($_spages) { return $p >= 1 && $p <= $_spages; }));
                sort($_sshow); $_sprev = 0;
                foreach ($_sshow as $p):
                    if ($_sprev && $p - $_sprev > 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; $_sprev = $p; ?>
                <li class="page-item <?= $p === $_spg ? 'active' : '' ?>">
                    <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($_GET, ['page_sales' => $p])) ?>"><?= $p ?></a>
                </li>
                <?php endforeach; ?>
                <li class="page-item <?= $_spg >= $_spages ? 'disabled' : '' ?>">
                    <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($_GET, ['page_sales' => $_spg + 1])) ?>"><i class="bi bi-chevron-right"></i></a>
                </li>
            </ul>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
