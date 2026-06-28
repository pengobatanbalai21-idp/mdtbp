    </div><!-- /container-fluid -->
</main><!-- /main-content -->
</div><!-- /layout-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var toggle  = document.getElementById('sidebarToggle');

    function isMobile() { return window.innerWidth < 992; }

    function openMobileSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeMobileSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            if (isMobile()) {
                sidebar.classList.contains('open') ? closeMobileSidebar() : openMobileSidebar();
            } else {
                document.body.classList.toggle('sidebar-collapsed');
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeMobileSidebar);
    }

    // Close mobile sidebar on nav click
    document.querySelectorAll('.sidebar-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (isMobile()) closeMobileSidebar();
        });
    });

    // Swipe gestures (mobile)
    var touchStartX = 0;
    document.addEventListener('touchstart', function (e) { touchStartX = e.touches[0].clientX; }, { passive: true });
    document.addEventListener('touchend', function (e) {
        if (!isMobile()) return;
        var dx = e.changedTouches[0].clientX - touchStartX;
        if (touchStartX < 30 && dx > 60) openMobileSidebar();
        if (sidebar.classList.contains('open') && dx < -60) closeMobileSidebar();
    }, { passive: true });

    // Auto-hide alerts after 4.5 s
    document.querySelectorAll('.alert-dismissible').forEach(function (el) {
        setTimeout(function () {
            try { bootstrap.Alert.getOrCreateInstance(el).close(); } catch (e) {}
        }, 4500);
    });

    // Live clock & date — WIB (UTC+7)
    var HARI  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    var BULAN_PENDEK = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
    var BULAN_PANJANG = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

    function wibDate() {
        // getTime() sudah berbasis UTC, jadi cukup +7 jam untuk WIB,
        // lalu dibaca pakai getUTC*(). (Tidak perlu getTimezoneOffset.)
        var now = new Date();
        return new Date(now.getTime() + 7 * 60 * 60 * 1000);
    }

    function tickClock() {
        var wib = wibDate();
        var h = String(wib.getUTCHours()).padStart(2, '0');
        var m = String(wib.getUTCMinutes()).padStart(2, '0');
        var s = String(wib.getUTCSeconds()).padStart(2, '0');
        var time = h + ':' + m + ':' + s;

        var hari   = HARI[wib.getUTCDay()];
        var tgl    = String(wib.getUTCDate()).padStart(2, '0');
        var bln    = wib.getUTCMonth();
        var thn    = wib.getUTCFullYear();
        var tanggalPendek = hari + ', ' + tgl + ' ' + BULAN_PENDEK[bln] + ' ' + thn;
        var tanggalPanjang = hari + ', ' + tgl + ' ' + BULAN_PANJANG[bln] + ' ' + thn;

        // Navbar date
        var elNavDate = document.getElementById('navDateText');
        if (elNavDate) elNavDate.textContent = tanggalPendek;

        // Dashboard badge & date
        var elDashBadge = document.getElementById('dashClockBadge');
        if (elDashBadge) elDashBadge.textContent = time + ' WIB';
        var elDashDate = document.getElementById('dashDateText');
        if (elDashDate) elDashDate.textContent = tanggalPanjang;

        // Attendance date
        var elAttDate = document.getElementById('attDateText');
        if (elAttDate) elAttDate.textContent = tanggalPanjang;
    }
    tickClock();
    setInterval(tickClock, 1000);
});
</script>
</body>
</html>
