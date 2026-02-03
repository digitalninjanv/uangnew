<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();

header('Content-Type: application/json');

$userId = getUserId();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $walletId = (int)($input['wallet_id'] ?? 0);
        $categoryId = (int)($input['category_id'] ?? 0);
        $type = clean($input['type'] ?? '');
        $amount = floatval($input['amount'] ?? 0);
        $description = clean($input['description'] ?? '');
        $transactionDate = clean($input['transaction_date'] ?? date('Y-m-d'));
        
        // Validation
        if (!$walletId || !$categoryId || !in_array($type, ['income', 'expense']) || $amount <= 0) {
            errorResponse('Data tidak lengkap atau tidak valid');
        }
        
        // Verify wallet and category belong to user
        $wallet = fetchOne("SELECT id FROM wallets WHERE id = ? AND user_id = ?", [$walletId, $userId]);
        $category = fetchOne("SELECT id FROM categories WHERE id = ? AND user_id = ? AND type = ?", [$categoryId, $userId, $type]);
        
        if (!$wallet || !$category) {
            errorResponse('Dompet atau kategori tidak valid');
        }
        
        if (!empty($input['id'])) {
            // Update transaction - triggers will handle wallet balance adjustment
            $transactionId = (int)$input['id'];
            
            execute(
                "UPDATE transactions 
                 SET wallet_id = ?, category_id = ?, type = ?, amount = ?, description = ?, transaction_date = ?
                 WHERE id = ? AND user_id = ?",
                [$walletId, $categoryId, $type, $amount, $description, $transactionDate, $transactionId, $userId]
            );
            
            successResponse(['message' => 'Transaksi berhasil diperbarui']);
        } else {
            // Insert transaction - triggers will handle wallet balance update
            execute(
                "INSERT INTO transactions (user_id, wallet_id, category_id, type, amount, description, transaction_date) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$userId, $walletId, $categoryId, $type, $amount, $description, $transactionDate]
            );
            
            successResponse(['message' => 'Transaksi berhasil ditambahkan']);
        }
    } else if ($method === 'DELETE') {
        $transactionId = (int)($_GET['id'] ?? 0);
        
        // Delete transaction - triggers will handle wallet balance adjustment
        $deleted = execute(
            "DELETE FROM transactions WHERE id = ? AND user_id = ?",
            [$transactionId, $userId]
        );
        
        if ($deleted) {
            successResponse(['message' => 'Transaksi berhasil dihapus']);
        } else {
            errorResponse('Transaksi tidak ditemukan');
        }
    } else if ($method === 'GET') {
        // Get all transactions
        $transactions = fetchAll(
            "SELECT t.*, c.name as category_name, w.name as wallet_name
             FROM transactions t
             JOIN categories c ON t.category_id = c.id
             JOIN wallets w ON t.wallet_id = w.id
             WHERE t.user_id = ?
             ORDER BY t.transaction_date DESC, t.created_at DESC",
            [$userId]
        );
        
        successResponse(['transactions' => $transactions]);
    }
    
} catch (Exception $e) {
    error_log("Transaction API Error: " . $e->getMessage());
    errorResponse('Terjadi kesalahan sistem', 500);
}
