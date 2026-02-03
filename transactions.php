<?php
require_once __DIR__ . '/includes/header.php';

$userId = getUserId();

// Get all transactions with filters
$filterType = $_GET['type'] ?? 'all';
$filterWallet = (int)($_GET['wallet'] ?? 0);
$filterCategory = (int)($_GET['category'] ?? 0);
$filterMonth = $_GET['month'] ?? date('Y-m');

$sql = "SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color,
               w.name as wallet_name, w.icon as wallet_icon
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        JOIN wallets w ON t.wallet_id = w.id
        WHERE t.user_id = ?";

$params = [$userId];

if ($filterType !== 'all') {
    $sql .= " AND t.type = ?";
    $params[] = $filterType;
}

if ($filterWallet > 0) {
    $sql .= " AND t.wallet_id = ?";
    $params[] = $filterWallet;
}

if ($filterCategory > 0) {
    $sql .= " AND t.category_id = ?";
    $params[] = $filterCategory;
}

if ($filterMonth) {
    $sql .= " AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?";
    $params[] = $filterMonth;
}

$sql .= " ORDER BY t.transaction_date DESC, t.created_at DESC LIMIT 100";

$transactions = fetchAll($sql, $params);

// Get wallets and categories for dropdowns
$wallets = fetchAll("SELECT * FROM wallets WHERE user_id = ? AND is_active = 1 ORDER BY name", [$userId]);
$categories = fetchAll("SELECT * FROM categories WHERE user_id = ? AND is_active = 1 ORDER BY type, name", [$userId]);

// Calculate totals for current filter
$totalIncome = 0;
$totalExpense = 0;
foreach ($transactions as $t) {
    if ($t['type'] === 'income') $totalIncome += $t['amount'];
    else $totalExpense += $t['amount'];
}
?>

<div class="page-header mb-3 d-flex justify-between align-center">
    <div>
        <h1>Transaksi</h1>
        <p style="color: var(--text-muted);">Kelola semua transaksi Anda</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-success" onclick="openAddTransactionModal('income')">
            <i class="fas fa-plus"></i>
            <span>Pemasukan</span>
        </button>
        <button class="btn btn-danger" onclick="openAddTransactionModal('expense')">
            <i class="fas fa-minus"></i>
            <span>Pengeluaran</span>
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="stats-grid mb-3">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon income">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Pemasukan</div>
                <div class="stat-value"><?php echo formatCurrency($totalIncome); ?></div>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon expense">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Pengeluaran</div>
                <div class="stat-value"><?php echo formatCurrency($totalExpense); ?></div>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon balance">
                <i class="fas fa-balance-scale"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Selisih</div>
                <div class="stat-value" style="color: <?php echo $totalIncome - $totalExpense >= 0 ? 'var(--secondary)' : 'var(--danger)'; ?>">
                    <?php echo formatCurrency($totalIncome - $totalExpense); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="d-flex gap-2" style="flex-wrap: wrap;">
            <select name="type" class="form-control" style="flex: 1; min-width: 150px;">
                <option value="all" <?php echo $filterType === 'all' ? 'selected' : ''; ?>>Semua Tipe</option>
                <option value="income" <?php echo $filterType === 'income' ? 'selected' : ''; ?>>Pemasukan</option>
                <option value="expense" <?php echo $filterType === 'expense' ? 'selected' : ''; ?>>Pengeluaran</option>
            </select>

            <select name="wallet" class="form-control" style="flex: 1; min-width: 150px;">
                <option value="0">Semua Dompet</option>
                <?php foreach ($wallets as $w): ?>
                    <option value="<?php echo $w['id']; ?>" <?php echo $filterWallet == $w['id'] ? 'selected' : ''; ?>>
                        <?php echo $w['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="category" class="form-control" style="flex: 1; min-width: 150px;">
                <option value="0">Semua Kategori</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $filterCategory == $c['id'] ? 'selected' : ''; ?>>
                        <?php echo $c['name']; ?> (<?php echo ucfirst($c['type']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="month" name="month" value="<?php echo $filterMonth; ?>" class="form-control" style="flex: 1; min-width: 150px;">

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i>
                <span>Filter</span>
            </button>

            <a href="/uangnew/transactions.php" class="btn btn-outline">
                <i class="fas fa-redo"></i>
                <span>Reset</span>
            </a>
        </form>
    </div>
</div>

<!-- Transactions List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Transaksi (<?php echo count($transactions); ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($transactions)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3 class="empty-state-title">Tidak Ada Transaksi</h3>
                <p class="empty-state-description">Tidak ada transaksi yang sesuai dengan filter Anda. Coba ubah filter atau tambahkan transaksi baru.</p>
            </div>
        <?php else: ?>
            <div class="transaction-list">
                <?php foreach ($transactions as $transaction): ?>
                    <div class="transaction-item">
                        <div class="transaction-icon <?php echo $transaction['type']; ?>">
                            <i class="fas fa-<?php echo $transaction['category_icon']; ?>"></i>
                        </div>
                        <div class="transaction-info" style="flex: 2;">
                            <div class="transaction-category"><?php echo $transaction['category_name']; ?></div>
                            <div class="transaction-description">
                                <?php echo $transaction['description'] ?: '-'; ?> • 
                                <i class="fas fa-<?php echo $transaction['wallet_icon']; ?>" style="font-size: 0.75rem;"></i>
                                <?php echo $transaction['wallet_name']; ?>
                            </div>
                        </div>
                        <div style="text-align: right; flex: 1;">
                            <div class="transaction-amount <?php echo $transaction['type']; ?>">
                                <?php echo $transaction['type'] === 'income' ? '+' : '-'; ?>
                                <?php echo formatCurrency($transaction['amount']); ?>
                            </div>
                            <div class="transaction-date"><?php echo formatDate($transaction['transaction_date']); ?></div>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-sm btn-outline" onclick='editTransaction(<?php echo json_encode($transaction); ?>)' title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteTransaction(<?php echo $transaction['id']; ?>)" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Transaction Modal -->
<div class="modal" id="transactionModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="transactionModalTitle">Tambah Transaksi</h3>
            <button class="modal-close" onclick="closeModal('transactionModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="transactionForm" onsubmit="saveTransaction(event)">
            <input type="hidden" id="transaction_id" name="id">
            <input type="hidden" id="transaction_type" name="type">

            <div class="form-group">
                <label for="transaction_wallet" class="form-label">Dompet</label>
                <select id="transaction_wallet" name="wallet_id" class="form-control" required>
                    <option value="">Pilih Dompet</option>
                    <?php foreach ($wallets as $w): ?>
                        <option value="<?php echo $w['id']; ?>"><?php echo $w['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="transaction_category" class="form-label">Kategori</label>
                <select id="transaction_category" name="category_id" class="form-control" required>
                    <option value="">Pilih Kategori</option>
                </select>
            </div>

            <div class="form-group">
                <label for="transaction_amount" class="form-label">Jumlah</label>
                <div class="input-group">
                    <span class="input-prefix">Rp</span>
                    <input type="text" id="transaction_amount" name="amount" class="form-control" placeholder="0" data-currency required>
                </div>
                <div class="form-hint">Gunakan titik untuk pemisah ribuan</div>
            </div>

            <div class="form-group">
                <label for="transaction_date" class="form-label">Tanggal</label>
                <input type="date" id="transaction_date" name="transaction_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="transaction_description" class="form-label">Deskripsi (Opsional)</label>
                <textarea id="transaction_description" name="description" class="form-control" placeholder="Catatan transaksi"></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline" onclick="closeModal('transactionModal')" style="flex: 1;">Batal</button>
                <button type="submit" class="btn btn-primary" id="saveTransactionBtn" style="flex: 1;">
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const categories = <?php echo json_encode($categories); ?>;

function openAddTransactionModal(type) {
    document.getElementById('transactionModalTitle').textContent = type === 'income' ? 'Tambah Pemasukan' : 'Tambah Pengeluaran';
    document.getElementById('transactionForm').reset();
    document.getElementById('transaction_id').value = '';
    document.getElementById('transaction_type').value = type;
    document.getElementById('transaction_date').value = '<?php echo date('Y-m-d'); ?>';
    
    updateCategoryOptions(type);
    openModal('transactionModal');
}

function editTransaction(transaction) {
    document.getElementById('transactionModalTitle').textContent = transaction.type === 'income' ? 'Edit Pemasukan' : 'Edit Pengeluaran';
    document.getElementById('transaction_id').value = transaction.id;
    document.getElementById('transaction_type').value = transaction.type;
    document.getElementById('transaction_wallet').value = transaction.wallet_id;
    document.getElementById('transaction_date').value = transaction.transaction_date;
    document.getElementById('transaction_description').value = transaction.description || '';
    
    // Set amount with currency formatter
    const amountInput = document.getElementById('transaction_amount');
    amountInput.value = transaction.amount;
    // Re-initialize currency input to format the value
    if (typeof initCurrencyInput === 'function') {
        initCurrencyInput(amountInput);
    }
    
    updateCategoryOptions(transaction.type);
    document.getElementById('transaction_category').value = transaction.category_id;
    
    openModal('transactionModal');
}

function updateCategoryOptions(type) {
    const select = document.getElementById('transaction_category');
    select.innerHTML = '<option value="">Pilih Kategori</option>';
    
    categories
        .filter(c => c.type === type)
        .forEach(c => {
            const option = document.createElement('option');
            option.value = c.id;
            option.textContent = c.name;
            select.appendChild(option);
        });
}

async function saveTransaction(event) {
    event.preventDefault();
    
    const btn = document.getElementById('saveTransactionBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div><span>Menyimpan...</span>';
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('/uangnew/api/transactions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.reload();
        } else {
            showError(result.error || 'Terjadi kesalahan');
            btn.disabled = false;
            btn.innerHTML = '<span>Simpan</span>';
        }
    } catch (error) {
        showError('Terjadi kesalahan: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<span>Simpan</span>';
    }
}

async function deleteTransaction(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus transaksi ini? Saldo dompet akan dikembalikan.')) return;
    
    try {
        const response = await fetch(`/uangnew/api/transactions.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.reload();
        } else {
            showError(result.error || 'Terjadi kesalahan');
        }
    } catch (error) {
        showError('Terjadi kesalahan: ' + error.message);
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
