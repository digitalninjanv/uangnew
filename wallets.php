<?php
require_once __DIR__ . '/includes/header.php';

$userId = getUserId();

// Get all wallets
$wallets = fetchAll(
    "SELECT * FROM wallets WHERE user_id = ? ORDER BY created_at DESC",
    [$userId]
);

// Calculate statistics
$totalBalance = 0;
$activeWallets = 0;
$inactiveWallets = 0;

foreach ($wallets as $wallet) {
    $totalBalance += $wallet['balance'];
    if ($wallet['is_active']) {
        $activeWallets++;
    } else {
        $inactiveWallets++;
    }
}

// Get wallet types and icons
$walletTypes = [
    'bank' => ['icon' => 'university', 'label' => 'Bank'],
    'e-wallet' => ['icon' => 'mobile-alt', 'label' => 'E-Wallet'],
    'cash' => ['icon' => 'money-bill-wave', 'label' => 'Cash'],
    'savings' => ['icon' => 'piggy-bank', 'label' => 'Tabungan'],
    'investment' => ['icon' => 'chart-line', 'label' => 'Investasi']
];

$walletIcons = ['wallet', 'university', 'mobile-alt', 'money-bill-wave', 'piggy-bank', 'chart-line', 'credit-card', 'dollar-sign'];
$walletColors = ['#6366F1', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#3B82F6', '#EC4899', '#14B8A6'];
?>


<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    transition: all var(--transition-fast);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
}

.stat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.wallet-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.wallet-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    transition: all var(--transition-fast);
    position: relative;
    overflow: hidden;
}

.wallet-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: transparent;
}

.wallet-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.wallet-type {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-full);
    font-size: 0.875rem;
    font-weight: 600;
}

.wallet-name {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.wallet-balance {
    font-size: 2rem;
    font-weight: 800;
    margin: 1.5rem 0;
    color: var(--text-primary);
    font-family: 'Monaco', 'Courier New', monospace;
}

.wallet-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
}

.wallet-actions .btn {
    flex: 0 0 auto;
    min-width: 40px;
    padding: 0.5rem 0.75rem;
    border-radius: var(--radius-md);
}

.wallet-actions .btn:hover {
    transform: scale(1.05);
}

@media (max-width: 768px) {
    .wallet-grid {
        grid-template-columns: 1fr;
    }
    
    .wallet-balance {
        font-size: 1.75rem;
    }
    
    .wallet-actions {
        justify-content: space-between;
    }
    
    .wallet-actions .btn {
        flex: 1 1 auto;
    }
}
</style>

<div class="page-header mb-3 d-flex justify-between align-center">
    <div>
        <h1>💼 Dompet Saya</h1>
        <p style="color: var(--text-muted);">Kelola semua dompet dan akun Anda</p>
    </div>
    <button class="btn btn-primary" onclick="openAddWalletModal()">
        <i class="fas fa-plus"></i>
        <span>Tambah Dompet</span>
    </button>
</div>

<?php if (empty($wallets)): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3 class="empty-state-title">Belum Ada Dompet</h3>
                <p class="empty-state-description">Mulai dengan menambahkan dompet pertama Anda untuk mengelola keuangan dengan lebih baik</p>
                <button class="btn btn-primary btn-lg" onclick="openAddWalletModal()">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Dompet Sekarang</span>
                </button>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Summary Cards -->
    <div class="stats-grid mb-3">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-label">Total Saldo</div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%); color: white; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="stat-value" style="font-size: 1.75rem; font-weight: 700; margin: 0.5rem 0; color: var(--primary);">
                <?php echo formatCurrency($totalBalance); ?>
            </div>
            <div class="stat-sublabel" style="font-size: 0.8rem; color: var(--text-muted);">
                Dari <?php echo count($wallets); ?> dompet
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-label">Dompet Aktif</div>
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: var(--secondary); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-value" style="font-size: 1.75rem; font-weight: 700; margin: 0.5rem 0; color: var(--secondary);">
                <?php echo $activeWallets; ?>
            </div>
            <div class="stat-sublabel" style="font-size: 0.8rem; color: var(--text-muted);">
                Sedang digunakan
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-label">Dompet Nonaktif</div>
                <div class="stat-icon" style="background: rgba(148, 163, 184, 0.15); color: var(--text-muted); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fas fa-eye-slash"></i>
                </div>
            </div>
            <div class="stat-value" style="font-size: 1.75rem; font-weight: 700; margin: 0.5rem 0; color: var(--text-muted);">
                <?php echo $inactiveWallets; ?>
            </div>
            <div class="stat-sublabel" style="font-size: 0.8rem; color: var(--text-muted);">
                Tidak aktif
            </div>
        </div>
    </div>

    <div class="wallet-grid">
        <?php foreach ($wallets as $wallet): ?>
            <div class="wallet-card" style="border-left: 4px solid <?php echo $wallet['color']; ?>;">
                <div class="wallet-header">
                    <div class="wallet-type" style="background: <?php echo $wallet['color']; ?>20; color: <?php echo $wallet['color']; ?>;">
                        <i class="fas fa-<?php echo $wallet['icon']; ?>"></i>
                        <span><?php echo $walletTypes[$wallet['type']]['label'] ?? ucfirst($wallet['type']); ?></span>
                    </div>
                    <div>
                        <?php if ($wallet['is_active']): ?>
                            <span style="color: var(--secondary); font-size: 0.75rem;">● Aktif</span>
                        <?php else: ?>
                            <span style="color: var(--text-muted); font-size: 0.75rem;">● Nonaktif</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="wallet-name"><?php echo $wallet['name']; ?></div>
                <?php if ($wallet['description']): ?>
                    <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
                        <?php echo $wallet['description']; ?>
                    </p>
                <?php endif; ?>
                
                <div class="wallet-balance"><?php echo formatCurrency($wallet['balance']); ?></div>
                
                <div class="wallet-actions">
                    <button class="btn btn-sm btn-outline" 
                            data-wallet-id="<?php echo htmlspecialchars($wallet['id']); ?>"
                            data-wallet-name="<?php echo htmlspecialchars($wallet['name']); ?>"
                            data-wallet-type="<?php echo htmlspecialchars($wallet['type']); ?>"
                            data-wallet-balance="<?php echo htmlspecialchars($wallet['balance']); ?>"
                            data-wallet-color="<?php echo htmlspecialchars($wallet['color']); ?>"
                            data-wallet-icon="<?php echo htmlspecialchars($wallet['icon']); ?>"
                            data-wallet-description="<?php echo htmlspecialchars($wallet['description'] ?? ''); ?>"
                            onclick="editWallet(this)"
                            title="Edit Dompet">
                        <i class="fas fa-edit"></i>
                    </button>
                    <?php if ($wallet['is_active']): ?>
                        <button class="btn btn-sm btn-outline" 
                                onclick="toggleWalletStatus(<?php echo $wallet['id']; ?>, 0)" 
                                style="border-color: var(--warning); color: var(--warning);"
                                title="Nonaktifkan Dompet">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    <?php else: ?>
                        <button class="btn btn-sm btn-outline" 
                                onclick="toggleWalletStatus(<?php echo $wallet['id']; ?>, 1)"
                                style="border-color: var(--secondary); color: var(--secondary);"
                                title="Aktifkan Dompet">
                            <i class="fas fa-eye"></i>
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-sm btn-outline" 
                            onclick="deleteWallet(<?php echo $wallet['id']; ?>, '<?php echo htmlspecialchars($wallet['name'], ENT_QUOTES); ?>')" 
                            style="border-color: var(--danger); color: var(--danger);"
                            title="Hapus Dompet">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Wallet Modal -->
<div class="modal" id="walletModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="walletModalTitle">Tambah Dompet</h3>
            <button class="modal-close" onclick="closeModal('walletModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="walletForm" onsubmit="saveWallet(event)">
            <input type="hidden" id="wallet_id" name="id">
            
            <div class="form-group">
                <label for="wallet_name" class="form-label">Nama Dompet</label>
                <input type="text" id="wallet_name" name="name" class="form-control" placeholder="Contoh: Bank BCA, GoPay" required>
            </div>

            <div class="form-group">
                <label for="wallet_type" class="form-label">Tipe</label>
                <select id="wallet_type" name="type" class="form-control" required>
                    <?php foreach ($walletTypes as $key => $type): ?>
                        <option value="<?php echo $key; ?>">
                            <?php echo $type['label']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="wallet_balance" class="form-label">
                    Saldo Awal
                    <span class="label-hint">(Opsional)</span>
                </label>
                <div class="input-group">
                    <span class="input-prefix">Rp</span>
                    <input type="text" id="wallet_balance" name="balance" class="form-control" placeholder="0" data-currency>
                </div>
                <div class="form-hint">Gunakan titik untuk pemisah ribuan</div>
            </div>

            <div class="form-group">
                <label for="wallet_color" class="form-label">Warna</label>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem;" role="radiogroup" aria-label="Pilih warna dompet">
                    <?php foreach ($walletColors as $index => $color): ?>
                        <label style="cursor: pointer;">
                            <input type="radio" name="color" value="<?php echo $color; ?>" style="display: none;" class="color-radio" aria-label="Warna <?php echo $index + 1; ?>">
                            <div class="color-option" style="width: 100%; height: 50px; background: <?php echo $color; ?>; border-radius: var(--radius-md); border: 2px solid transparent; transition: all var(--transition-fast);"></div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="wallet_icon" class="form-label">Icon</label>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem;" role="radiogroup" aria-label="Pilih icon dompet">
                    <?php foreach ($walletIcons as $index => $icon): ?>
                        <label style="cursor: pointer;">
                            <input type="radio" name="icon" value="<?php echo $icon; ?>" style="display: none;" class="icon-radio" aria-label="Icon <?php echo $icon; ?>">
                            <div class="icon-option" style="width: 100%; height: 50px; background: rgba(99, 102, 241, 0.1); border-radius: var(--radius-md); border: 2px solid transparent; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; transition: all var(--transition-fast);">
                                <i class="fas fa-<?php echo $icon; ?>"></i>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="wallet_description" class="form-label">Deskripsi (Opsional)</label>
                <textarea id="wallet_description" name="description" class="form-control" placeholder="Catatan tentang dompet ini"></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline" onclick="closeModal('walletModal')" style="flex: 1;">Batal</button>
                <button type="submit" class="btn btn-primary" id="saveWalletBtn" style="flex: 1;">
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Wallet management functions
function openAddWalletModal() {
    document.getElementById('walletModalTitle').textContent = 'Tambah Dompet';
    document.getElementById('walletForm').reset();
    document.getElementById('wallet_id').value = '';
    
    // Set default selections
    document.querySelector('input[name="color"][value="<?php echo $walletColors[0]; ?>"]').checked = true;
    document.querySelector('input[name="icon"][value="wallet"]').checked = true;
    updateSelections();
    
    openModal('walletModal');
    
    // Reset balance input after modal opens
    setTimeout(() => {
        const balanceInput = document.getElementById('wallet_balance');
        const hiddenInput = balanceInput.nextElementSibling;
        
        balanceInput.value = '';
        if (hiddenInput && hiddenInput.classList.contains('currency-value')) {
            hiddenInput.value = '0';
        }
    }, 100);
}

function editWallet(button) {
    // Extract wallet data from button's data attributes
    const wallet = {
        id: button.dataset.walletId,
        name: button.dataset.walletName,
        type: button.dataset.walletType,
        balance: button.dataset.walletBalance,
        color: button.dataset.walletColor,
        icon: button.dataset.walletIcon,
        description: button.dataset.walletDescription || ''
    };
    
    document.getElementById('walletModalTitle').textContent = 'Edit Dompet';
    document.getElementById('wallet_id').value = wallet.id;
    document.getElementById('wallet_name').value = wallet.name;
    document.getElementById('wallet_type').value = wallet.type;
    document.getElementById('wallet_description').value = wallet.description;
    
    // Select color radio button
    const colorRadio = document.querySelector(`input[name="color"][value="${wallet.color}"]`);
    if (colorRadio) {
        colorRadio.checked = true;
    }
    
    // Select icon radio button
    const iconRadio = document.querySelector(`input[name="icon"][value="${wallet.icon}"]`);
    if (iconRadio) {
        iconRadio.checked = true;
    }
    
    updateSelections();
    
    // Open modal
    openModal('walletModal');
    
    // Set balance with currency formatter after modal opens
    setTimeout(() => {
        const balanceInput = document.getElementById('wallet_balance');
        const hiddenInput = balanceInput.nextElementSibling;
        
        if (hiddenInput && hiddenInput.classList.contains('currency-value')) {
            // Currency input already initialized, just set values
            const numValue = parseFloat(wallet.balance) || 0;
            balanceInput.value = formatNumber(numValue);
            hiddenInput.value = numValue;
        } else {
            // Not initialized yet, set raw value and let auto-init handle it
            balanceInput.value = wallet.balance;
            if (typeof initCurrencyInput === 'function') {
                initCurrencyInput(balanceInput);
            }
        }
    }, 100);
}

async function saveWallet(event) {
    event.preventDefault();
    
    const btn = document.getElementById('saveWalletBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div><span>Menyimpan...</span>';
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());
    
    // Debug: log data being sent
    console.log('Saving wallet data:', data);
    
    try {
        const response = await fetch('/uangnew/api/wallets.php', {
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

async function toggleWalletStatus(id, status) {
    const action = status === 1 ? 'mengaktifkan' : 'menonaktifkan';
    if (!confirm(`Apakah Anda yakin ingin ${action} dompet ini?`)) return;
    
    try {
        const response = await fetch('/uangnew/api/wallets.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, is_active: status, action: 'toggle' })
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

async function deleteWallet(id, name) {
    // Confirmation with wallet name
    if (!confirm(`Apakah Anda yakin ingin menghapus dompet "${name}"?\n\n⚠️ Perhatian:\n- Dompet yang memiliki transaksi tidak dapat dihapus\n- Tindakan ini tidak dapat dibatalkan`)) {
        return;
    }
    
    try {
        const response = await fetch(`/uangnew/api/wallets.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message and reload
            showSuccess('Dompet berhasil dihapus!');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showError(result.error || 'Terjadi kesalahan');
        }
    } catch (error) {
        showError('Terjadi kesalahan: ' + error.message);
    }
}

// Handle color and icon selection visual feedback
document.addEventListener('DOMContentLoaded', function() {
    updateSelections();
    
    document.querySelectorAll('.color-radio').forEach(radio => {
        radio.addEventListener('change', updateSelections);
    });
    
    document.querySelectorAll('.icon-radio').forEach(radio => {
        radio.addEventListener('change', updateSelections);
    });
});

function updateSelections() {
    // Update color selections
    document.querySelectorAll('.color-option').forEach(el => {
        const radio = el.previousElementSibling || el.parentElement.querySelector('input');
        if (radio && radio.checked) {
            el.style.borderColor = 'var(--primary)';
            el.style.transform = 'scale(1.05)';
        } else {
            el.style.borderColor = 'transparent';
            el.style.transform = 'scale(1)';
        }
    });
    
    // Update icon selections
    document.querySelectorAll('.icon-option').forEach(el => {
        const radio = el.previousElementSibling || el.parentElement.querySelector('input');
        if (radio && radio.checked) {
            el.style.borderColor = 'var(--primary)';
            el.style.background = 'rgba(99, 102, 241, 0.2)';
            el.style.transform = 'scale(1.05)';
        } else {
            el.style.borderColor = 'transparent';
            el.style.background = 'rgba(99, 102, 241, 0.1)';
            el.style.transform = 'scale(1)';
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
