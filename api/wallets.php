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
        
        // Toggle wallet status
        if (isset($input['action']) && $input['action'] === 'toggle') {
            $walletId = (int)$input['id'];
            $isActive = (int)$input['is_active'];
            
            execute(
                "UPDATE wallets SET is_active = ? WHERE id = ? AND user_id = ?",
                [$isActive, $walletId, $userId]
            );
            
            successResponse(['message' => 'Status dompet berhasil diubah']);
        }
        
        // Add or update wallet
        $name = clean($input['name'] ?? '');
        $type = clean($input['type'] ?? 'cash');
        $balance = floatval($input['balance'] ?? 0);
        $color = clean($input['color'] ?? '#6366F1');
        $icon = clean($input['icon'] ?? 'wallet');
        $description = clean($input['description'] ?? '');
        
        if (empty($name)) {
            errorResponse('Nama dompet harus diisi');
        }
        
        if (!empty($input['id'])) {
            // Update existing wallet
            $walletId = (int)$input['id'];
            
            // Update all fields including balance (for manual corrections)
            execute(
                "UPDATE wallets SET name = ?, type = ?, balance = ?, color = ?, icon = ?, description = ? 
                 WHERE id = ? AND user_id = ?",
                [$name, $type, $balance, $color, $icon, $description, $walletId, $userId]
            );
            
            successResponse(['message' => 'Dompet berhasil diperbarui']);
        } else {
            // Insert new wallet
            execute(
                "INSERT INTO wallets (user_id, name, type, balance, color, icon, description) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$userId, $name, $type, $balance, $color, $icon, $description]
            );
            
            successResponse(['message' => 'Dompet berhasil ditambahkan']);
        }
    } else if ($method === 'DELETE') {
        $walletId = (int)($_GET['id'] ?? 0);
        
        // Check if wallet has transactions
        $hasTransactions = fetchOne(
            "SELECT COUNT(*) as count FROM transactions WHERE wallet_id = ? AND user_id = ?",
            [$walletId, $userId]
        )['count'];
        
        if ($hasTransactions > 0) {
            errorResponse('Tidak dapat menghapus dompet yang memiliki transaksi');
        }
        
        execute(
            "DELETE FROM wallets WHERE id = ? AND user_id = ?",
            [$walletId, $userId]
        );
        
        successResponse(['message' => 'Dompet berhasil dihapus']);
    } else if ($method === 'GET') {
        // Get all wallets
        $wallets = fetchAll(
            "SELECT * FROM wallets WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );
        
        successResponse(['wallets' => $wallets]);
    }
    
} catch (Exception $e) {
    error_log("Wallet API Error: " . $e->getMessage());
    errorResponse('Terjadi kesalahan sistem', 500);
}
