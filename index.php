<?php
require_once __DIR__ . '/includes/header.php';

$userId = getUserId();

// Get total balance across all wallets
$totalBalanceQuery = "SELECT COALESCE(SUM(balance), 0) as total FROM wallets WHERE user_id = ? AND is_active = 1";
$totalBalance = fetchOne($totalBalanceQuery, [$userId])['total'];

// Get current month income and expense
$currentYear = date('Y');
$currentMonth = date('n');

$incomeQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions 
                WHERE user_id = ? AND type = 'income' 
                AND YEAR(transaction_date) = ? AND MONTH(transaction_date) = ?";
$monthlyIncome = fetchOne($incomeQuery, [$userId, $currentYear, $currentMonth])['total'];

$expenseQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions 
                 WHERE user_id = ? AND type = 'expense' 
                 AND YEAR(transaction_date) = ? AND MONTH(transaction_date) = ?";
$monthlyExpense = fetchOne($expenseQuery, [$userId, $currentYear, $currentMonth])['total'];

// Get wallet count
$walletCount = fetchOne("SELECT COUNT(*) as count FROM wallets WHERE user_id = ? AND is_active = 1", [$userId])['count'];

// Get recent transactions
$recentTransactions = fetchAll(
    "SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color, 
            w.name as wallet_name
     FROM transactions t
     JOIN categories c ON t.category_id = c.id
     JOIN wallets w ON t.wallet_id = w.id
     WHERE t.user_id = ?
     ORDER BY t.transaction_date DESC, t.created_at DESC
     LIMIT 10",
    [$userId]
);

// Get all wallets for quick access
$wallets = fetchAll(
    "SELECT * FROM wallets WHERE user_id = ? AND is_active = 1 ORDER BY created_at ASC",
    [$userId]
);
?>

<div class="page-header mb-3">
    <h1>Dashboard</h1>
    <p style="color: var(--text-muted);">Selamat datang kembali, <?php echo $user['full_name']; ?>!</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon balance">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Saldo</div>
                <div class="stat-value"><?php echo formatCurrency($totalBalance); ?></div>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon income">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Pemasukan Bulan Ini</div>
                <div class="stat-value"><?php echo formatCurrency($monthlyIncome); ?></div>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon expense">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Pengeluaran Bulan Ini</div>
                <div class="stat-value"><?php echo formatCurrency($monthlyExpense); ?></div>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon wallet">
                <i class="fas fa-credit-card"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Jumlah Dompet</div>
                <div class="stat-value"><?php echo $walletCount; ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">Aksi Cepat</h3>
    </div>
    <div class="card-body">
        <div class="d-flex gap-2" style="flex-wrap: wrap;">
            <a href="/uangnew/transactions.php?action=add&type=income" class="btn btn-success">
                <i class="fas fa-plus"></i>
                <span>Tambah Pemasukan</span>
            </a>
            <a href="/uangnew/transactions.php?action=add&type=expense" class="btn btn-danger">
                <i class="fas fa-minus"></i>
                <span>Tambah Pengeluaran</span>
            </a>
            <a href="/uangnew/wallets.php?action=add" class="btn btn-primary">
                <i class="fas fa-wallet"></i>
                <span>Tambah Dompet</span>
            </a>
        </div>
    </div>
</div>

<div class="grid grid-2">
    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Transaksi Terbaru</h3>
        </div>
        <div class="card-body">
            <?php if (empty($recentTransactions)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3 class="empty-state-title">Belum Ada Transaksi</h3>
                    <p class="empty-state-description">Mulai catat transaksi Anda untuk mengelola keuangan dengan lebih baik</p>
                </div>
            <?php else: ?>
                <div class="transaction-list">
                    <?php foreach ($recentTransactions as $transaction): ?>
                        <div class="transaction-item">
                            <div class="transaction-icon <?php echo $transaction['type']; ?>">
                                <i class="fas fa-<?php echo $transaction['category_icon']; ?>"></i>
                            </div>
                            <div class="transaction-info">
                                <div class="transaction-category"><?php echo $transaction['category_name']; ?></div>
                                <div class="transaction-description">
                                    <?php echo $transaction['description'] ?: $transaction['wallet_name']; ?>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div class="transaction-amount <?php echo $transaction['type']; ?>">
                                    <?php echo $transaction['type'] === 'income' ? '+' : '-'; ?>
                                    <?php echo formatCurrency($transaction['amount']); ?>
                                </div>
                                <div class="transaction-date"><?php echo formatDate($transaction['transaction_date']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-2">
                    <a href="/uangnew/transactions.php" class="btn btn-outline btn-sm">Lihat Semua</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Wallets -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dompet Saya</h3>
        </div>
        <div class="card-body">
            <?php if (empty($wallets)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h3 class="empty-state-title">Belum Ada Dompet</h3>
                    <p class="empty-state-description">Tambahkan dompet untuk mulai mengelola keuangan</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($wallets as $wallet): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: rgba(30, 41, 59, 0.4); border-radius: var(--radius-lg); border: 1px solid var(--border);">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: <?php echo $wallet['color']; ?>20; color: <?php echo $wallet['color']; ?>; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                                    <i class="fas fa-<?php echo $wallet['icon']; ?>"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--text-primary);"><?php echo $wallet['name']; ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;"><?php echo ucfirst($wallet['type']); ?></div>
                                </div>
                            </div>
                            <div style="text-align: right; font-weight: 700; font-size: 1.125rem;">
                                <?php echo formatCurrency($wallet['balance']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-2">
                    <a href="/uangnew/wallets.php" class="btn btn-outline btn-sm">Kelola Dompet</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
