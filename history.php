<?php
require_once __DIR__ . '/includes/header.php';

$userId = getUserId();

// Calculate monthly summaries dynamically
$summaryQuery = "
    SELECT 
        YEAR(transaction_date) as year,
        MONTH(transaction_date) as month,
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
    FROM transactions
    WHERE user_id = ?
    GROUP BY YEAR(transaction_date), MONTH(transaction_date)
    ORDER BY YEAR(transaction_date) DESC, MONTH(transaction_date) DESC
    LIMIT 12
";

$monthlySummaries = fetchAll($summaryQuery, [$userId]);

// Calculate totals and averages
$totalIncome = 0;
$totalExpense = 0;
foreach ($monthlySummaries as $summary) {
    $totalIncome += $summary['total_income'];
    $totalExpense += $summary['total_expense'];
}
$avgIncome = count($monthlySummaries) > 0 ? $totalIncome / count($monthlySummaries) : 0;
$avgExpense = count($monthlySummaries) > 0 ? $totalExpense / count($monthlySummaries) : 0;
$savingsRate = $totalIncome > 0 ? (($totalIncome - $totalExpense) / $totalIncome) * 100 : 0;

// Calculate running balance for each month
$runningBalance = 0;
$monthlyData = [];

foreach (array_reverse($monthlySummaries) as $summary) {
    $netAmount = $summary['total_income'] - $summary['total_expense'];
    $runningBalance += $netAmount;
    
    $monthlyData[] = [
        'year' => $summary['year'],
        'month' => $summary['month'],
        'month_name' => getMonthName($summary['month']) . ' ' . $summary['year'],
        'income' => $summary['total_income'],
        'expense' => $summary['total_expense'],
        'net' => $netAmount,
        'balance' => $runningBalance
    ];
}

$monthlyData = array_reverse($monthlyData);

// Get current total balance
$currentTotalBalance = fetchOne(
    "SELECT COALESCE(SUM(balance), 0) as total FROM wallets WHERE user_id = ? AND is_active = 1",
    [$userId]
)['total'];
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-sublabel {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.compact-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.compact-table thead th {
    padding: 0.875rem 1rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: rgba(99, 102, 241, 0.05);
    border-bottom: 2px solid var(--primary);
    position: sticky;
    top: 0;
    z-index: 1;
}

.compact-table thead th:first-child {
    border-top-left-radius: var(--radius-md);
}

.compact-table thead th:last-child {
    border-top-right-radius: var(--radius-md);
}

.compact-table tbody tr {
    border-bottom: 1px solid var(--border);
    transition: all var(--transition-fast);
}

.compact-table tbody tr:hover {
    background: rgba(99, 102, 241, 0.03);
}

.compact-table tbody tr:last-child {
    border-bottom: none;
}

.compact-table tbody td {
    padding: 0.875rem 1rem;
    font-size: 0.9rem;
}

.month-cell {
    font-weight: 600;
    color: var(--text-primary);
}

.amount-cell {
    text-align: right;
    font-weight: 600;
    font-family: 'Monaco', 'Courier New', monospace;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
}

.badge-success {
    background: rgba(16, 185, 129, 0.15);
    color: var(--secondary);
}

.badge-danger {
    background: rgba(239, 68, 68, 0.15);
    color: var(--danger);
}

.badge-neutral {
    background: rgba(148, 163, 184, 0.15);
    color: var(--text-muted);
}

@media (max-width: 768px) {
    .stat-value {
        font-size: 1.5rem;
    }
    
    .compact-table {
        font-size: 0.85rem;
    }
    
    .compact-table tbody td,
    .compact-table thead th {
        padding: 0.65rem 0.75rem;
    }
}
</style>

<div class="page-header mb-3">
    <div>
        <h1>📊 Riwayat Keuangan</h1>
        <p style="color: var(--text-muted);">Analisis lengkap performa keuangan Anda</p>
    </div>
</div>

<?php if (empty($monthlyData)): ?>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 4rem 2rem;">
            <i class="fas fa-chart-line" style="font-size: 5rem; color: var(--text-muted); margin-bottom: 1rem; display: block;"></i>
            <h3>Belum Ada Data Riwayat</h3>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Mulai tambahkan transaksi untuk melihat riwayat keuangan Anda</p>
            <a href="/uangnew/transactions.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                <span>Tambah Transaksi</span>
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Key Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-label">Saldo Saat Ini</div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%); color: white;">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="stat-value" style="color: var(--primary);">
                <?php echo formatCurrency($currentTotalBalance); ?>
            </div>
            <div class="stat-sublabel">Semua dompet aktif</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-label">Rata-rata Pemasukan</div>
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: var(--secondary);">
                    <i class="fas fa-arrow-trend-up"></i>
                </div>
            </div>
            <div class="stat-value" style="color: var(--secondary);">
                <?php echo formatCurrency($avgIncome); ?>
            </div>
            <div class="stat-sublabel">Per bulan (12 bulan terakhir)</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-label">Rata-rata Pengeluaran</div>
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.15); color: var(--danger);">
                    <i class="fas fa-arrow-trend-down"></i>
                </div>
            </div>
            <div class="stat-value" style="color: var(--danger);">
                <?php echo formatCurrency($avgExpense); ?>
            </div>
            <div class="stat-sublabel">Per bulan (12 bulan terakhir)</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-label">Tingkat Tabungan</div>
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: var(--warning);">
                    <i class="fas fa-piggy-bank"></i>
                </div>
            </div>
            <div class="stat-value" style="color: <?php echo $savingsRate >= 20 ? 'var(--secondary)' : ($savingsRate >= 0 ? 'var(--warning)' : 'var(--danger)'); ?>;">
                <?php echo number_format($savingsRate, 1); ?>%
            </div>
            <div class="stat-sublabel">
                <?php 
                if ($savingsRate >= 20) echo '🎉 Sangat Baik!';
                elseif ($savingsRate >= 10) echo '👍 Baik';
                elseif ($savingsRate >= 0) echo '⚠️ Perlu Ditingkatkan';
                else echo '❌ Defisit';
                ?>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-2 mb-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line" style="margin-right: 0.5rem;"></i>Perkembangan Saldo</h3>
            </div>
            <div class="card-body">
                <canvas id="balanceChart" height="280"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar" style="margin-right: 0.5rem;"></i>Pemasukan vs Pengeluaran</h3>
            </div>
            <div class="card-body">
                <canvas id="incomeExpenseChart" height="280"></canvas>
            </div>
        </div>
    </div>

    <!-- Monthly Details Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table" style="margin-right: 0.5rem;"></i>Detail Bulanan</h3>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0.5rem 0 0 0;">12 Bulan terakhir</p>
        </div>
        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="compact-table">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th style="text-align: right;">Pemasukan</th>
                            <th style="text-align: right;">Pengeluaran</th>
                            <th style="text-align: right;">Selisih</th>
                            <th style="text-align: right;">Saldo</th>
                            <th style="text-align: right;">Perubahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $prevBalance = 0;
                        foreach ($monthlyData as $index => $data): 
                            $change = $prevBalance > 0 ? (($data['balance'] - $prevBalance) / $prevBalance) * 100 : 0;
                            $prevBalance = $data['balance'];
                        ?>
                            <tr>
                                <td class="month-cell">
                                    <i class="fas fa-calendar" style="color: var(--primary); margin-right: 0.5rem; font-size: 0.875rem;"></i>
                                    <?php echo $data['month_name']; ?>
                                </td>
                                <td class="amount-cell" style="color: var(--secondary);">
                                    +<?php echo formatCurrency($data['income']); ?>
                                </td>
                                <td class="amount-cell" style="color: var(--danger);">
                                    -<?php echo formatCurrency($data['expense']); ?>
                                </td>
                                <td class="amount-cell" style="color: <?php echo $data['net'] >= 0 ? 'var(--secondary)' : 'var(--danger)'; ?>">
                                    <?php echo ($data['net'] >= 0 ? '+' : '') . formatCurrency($data['net']); ?>
                                </td>
                                <td class="amount-cell" style="font-weight: 700;">
                                    <?php echo formatCurrency($data['balance']); ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php if ($index > 0): ?>
                                        <span class="badge <?php echo $change >= 0 ? 'badge-success' : 'badge-danger'; ?>">
                                            <i class="fas fa-<?php echo $change >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                                            <?php echo number_format(abs($change), 1); ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral">
                                            <i class="fas fa-minus"></i>
                                            N/A
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
<?php if (!empty($monthlyData)): ?>
const monthlyData = <?php echo json_encode($monthlyData); ?>;

// Balance Growth Chart
const balanceCtx = document.getElementById('balanceChart');
new Chart(balanceCtx, {
    type: 'line',
    data: {
        labels: monthlyData.map(d => d.month_name),
        datasets: [{
            label: 'Saldo Berjalan',
            data: monthlyData.map(d => d.balance),
            borderColor: '#6366F1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointBackgroundColor: '#6366F1',
            pointBorderColor: '#fff',
            pointBorderWidth: 3,
            pointHoverBackgroundColor: '#6366F1',
            pointHoverBorderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                padding: 16,
                titleColor: '#F1F5F9',
                bodyColor: '#F1F5F9',
                borderColor: '#6366F1',
                borderWidth: 2,
                titleFont: {
                    size: 14,
                    weight: '600'
                },
                bodyFont: {
                    size: 13
                },
                callbacks: {
                    label: function(context) {
                        return 'Saldo: ' + formatCurrency(context.parsed.y);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: '#94A3B8',
                    font: {
                        size: 11,
                        weight: '500'
                    },
                    callback: function(value) {
                        if (value >= 1000000) {
                            return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                        } else if (value >= 1000) {
                            return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                        }
                        return 'Rp ' + value;
                    }
                },
                grid: {
                    color: 'rgba(148, 163, 184, 0.08)',
                    drawBorder: false
                }
            },
            x: {
                ticks: {
                    color: '#94A3B8',
                    font: {
                        size: 11,
                        weight: '500'
                    }
                },
                grid: {
                    display: false
                }
            }
        }
    }
});

// Income vs Expense Chart
const incomeExpenseCtx = document.getElementById('incomeExpenseChart');
new Chart(incomeExpenseCtx, {
    type: 'bar',
    data: {
        labels: monthlyData.map(d => d.month_name),
        datasets: [
            {
                label: 'Pemasukan',
                data: monthlyData.map(d => d.income),
                backgroundColor: 'rgba(16, 185, 129, 0.85)',
                borderColor: '#10B981',
                borderWidth: 2,
                borderRadius: 8,
                hoverBackgroundColor: 'rgba(16, 185, 129, 1)'
            },
            {
                label: 'Pengeluaran',
                data: monthlyData.map(d => d.expense),
                backgroundColor: 'rgba(239, 68, 68, 0.85)',
                borderColor: '#EF4444',
                borderWidth: 2,
                borderRadius: 8,
                hoverBackgroundColor: 'rgba(239, 68, 68, 1)'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: '#F1F5F9',
                    padding: 15,
                    font: {
                        size: 12,
                        weight: '600'
                    },
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                padding: 16,
                titleColor: '#F1F5F9',
                bodyColor: '#F1F5F9',
                borderColor: '#6366F1',
                borderWidth: 2,
                titleFont: {
                    size: 14,
                    weight: '600'
                },
                bodyFont: {
                    size: 13
                },
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: '#94A3B8',
                    font: {
                        size: 11,
                        weight: '500'
                    },
                    callback: function(value) {
                        if (value >= 1000000) {
                            return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                        } else if (value >= 1000) {
                            return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                        }
                        return 'Rp ' + value;
                    }
                },
                grid: {
                    color: 'rgba(148, 163, 184, 0.08)',
                    drawBorder: false
                }
            },
            x: {
                ticks: {
                    color: '#94A3B8',
                    font: {
                        size: 11,
                        weight: '500'
                    }
                },
                grid: {
                    display: false
                }
            }
        }
    }
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
