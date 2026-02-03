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
        
        $name = clean($input['name'] ?? '');
        $type = clean($input['type'] ?? '');
        $color = clean($input['color'] ?? '#6B7280');
        $icon = clean($input['icon'] ?? 'tag');
        
        if (empty($name) || !in_array($type, ['income', 'expense'])) {
            errorResponse('Data tidak lengkap atau tidak valid');
        }
        
        if (!empty($input['id'])) {
            // Update
            $categoryId = (int)$input['id'];
            
            execute(
                "UPDATE categories SET name = ?, type = ?, color = ?, icon = ? 
                 WHERE id = ? AND user_id = ?",
                [$name, $type, $color, $icon, $categoryId, $userId]
            );
            
            successResponse(['message' => 'Kategori berhasil diperbarui']);
        } else {
            // Insert
            execute(
                "INSERT INTO categories (user_id, name, type, color, icon) 
                 VALUES (?, ?, ?, ?, ?)",
                [$userId, $name, $type, $color, $icon]
            );
            
            successResponse(['message' => 'Kategori berhasil ditambahkan']);
        }
    } else if ($method === 'GET') {
        $categories = fetchAll(
            "SELECT * FROM categories WHERE user_id = ? ORDER BY type, name",
            [$userId]
        );
        
        successResponse(['categories' => $categories]);
    }
    
} catch (Exception $e) {
    error_log("Category API Error: " . $e->getMessage());
    errorResponse('Terjadi kesalahan sistem', 500);
}
