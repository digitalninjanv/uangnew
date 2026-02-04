        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <!-- Mobile Quick Actions -->
    <button class="mobile-fab" id="mobileFab" aria-label="Buka aksi cepat">
        <i class="fas fa-plus"></i>
    </button>
    <div class="mobile-action-overlay" id="mobileActionOverlay" aria-hidden="true"></div>
    <div class="mobile-action-sheet" id="mobileActionSheet" role="dialog" aria-modal="true" aria-label="Aksi cepat">
        <div class="mobile-action-sheet-handle" aria-hidden="true"></div>
        <div class="mobile-action-sheet-header">
            <span>Aksi Cepat</span>
            <button class="mobile-action-close" type="button" aria-label="Tutup aksi cepat">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mobile-action-grid">
            <a class="mobile-action-item" href="/uangnew/transactions.php?action=add&type=income">
                <span class="mobile-action-icon success"><i class="fas fa-arrow-down"></i></span>
                <span>Tambah Pemasukan</span>
            </a>
            <a class="mobile-action-item" href="/uangnew/transactions.php?action=add&type=expense">
                <span class="mobile-action-icon danger"><i class="fas fa-arrow-up"></i></span>
                <span>Tambah Pengeluaran</span>
            </a>
            <a class="mobile-action-item" href="/uangnew/wallets.php?action=add">
                <span class="mobile-action-icon primary"><i class="fas fa-wallet"></i></span>
                <span>Tambah Dompet</span>
            </a>
        </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-nav" aria-label="Navigasi bawah">
        <a href="/uangnew/index.php" class="mobile-nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
            <span class="mobile-nav-icon"><i class="fas fa-home"></i></span>
            <span class="mobile-nav-label">Dashboard</span>
        </a>
        <a href="/uangnew/wallets.php" class="mobile-nav-link <?php echo $currentPage === 'wallets' ? 'active' : ''; ?>">
            <span class="mobile-nav-icon"><i class="fas fa-wallet"></i></span>
            <span class="mobile-nav-label">Dompet</span>
        </a>
        <a href="/uangnew/transactions.php" class="mobile-nav-link <?php echo $currentPage === 'transactions' ? 'active' : ''; ?>">
            <span class="mobile-nav-icon"><i class="fas fa-exchange-alt"></i></span>
            <span class="mobile-nav-label">Transaksi</span>
        </a>
        <a href="/uangnew/categories.php" class="mobile-nav-link <?php echo $currentPage === 'categories' ? 'active' : ''; ?>">
            <span class="mobile-nav-icon"><i class="fas fa-tags"></i></span>
            <span class="mobile-nav-label">Kategori</span>
        </a>
        <a href="/uangnew/history.php" class="mobile-nav-link <?php echo $currentPage === 'history' ? 'active' : ''; ?>">
            <span class="mobile-nav-icon"><i class="fas fa-chart-line"></i></span>
            <span class="mobile-nav-label">Riwayat</span>
        </a>
    </nav>

    <script src="/uangnew/assets/js/toast.js"></script>
    <script src="/uangnew/assets/js/main.js"></script>
</body>
</html>
