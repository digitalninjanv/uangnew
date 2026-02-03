-- Personal Finance Manager Database Schema
-- Created: 2026-02-02

CREATE DATABASE IF NOT EXISTS finance_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE finance_manager;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wallet types: bank, e-wallet, cash, savings, investment
CREATE TABLE wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('bank', 'e-wallet', 'cash', 'savings', 'investment') NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    color VARCHAR(7) DEFAULT '#4F46E5',
    icon VARCHAR(50) DEFAULT 'wallet',
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transaction categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    color VARCHAR(7) DEFAULT '#6B7280',
    icon VARCHAR(50) DEFAULT 'tag',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    wallet_id INT NOT NULL,
    category_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description TEXT,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_wallet_id (wallet_id),
    INDEX idx_category_id (category_id),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Monthly summaries for quick history access
CREATE TABLE monthly_summaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    year INT NOT NULL,
    month INT NOT NULL,
    total_income DECIMAL(15, 2) DEFAULT 0.00,
    total_expense DECIMAL(15, 2) DEFAULT 0.00,
    net_amount DECIMAL(15, 2) DEFAULT 0.00,
    total_balance DECIMAL(15, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_month (user_id, year, month),
    INDEX idx_user_year_month (user_id, year, month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wallet history for tracking balance changes over time
CREATE TABLE wallet_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wallet_id INT NOT NULL,
    user_id INT NOT NULL,
    year INT NOT NULL,
    month INT NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wallet_month (wallet_id, year, month),
    INDEX idx_wallet_year_month (wallet_id, year, month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demo user (password: demo123) - INSERT FIRST
INSERT INTO users (username, email, password, full_name) VALUES
('demo', 'demo@finance.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo User');

-- Insert default categories for demo user (user_id = 1)
-- Default income categories
INSERT INTO categories (user_id, name, type, color, icon) VALUES
(1, 'Gaji', 'income', '#10B981', 'briefcase'),
(1, 'Bonus', 'income', '#3B82F6', 'gift'),
(1, 'Investasi', 'income', '#8B5CF6', 'trending-up'),
(1, 'Lainnya', 'income', '#6B7280', 'dollar-sign');

-- Default expense categories
INSERT INTO categories (user_id, name, type, color, icon) VALUES
(1, 'Makanan', 'expense', '#EF4444', 'coffee'),
(1, 'Transportasi', 'expense', '#F59E0B', 'car'),
(1, 'Belanja', 'expense', '#EC4899', 'shopping-cart'),
(1, 'Tagihan', 'expense', '#6366F1', 'file-text'),
(1, 'Hiburan', 'expense', '#14B8A6', 'film'),
(1, 'Kesehatan', 'expense', '#06B6D4', 'heart'),
(1, 'Pendidikan', 'expense', '#8B5CF6', 'book'),
(1, 'Lainnya', 'expense', '#6B7280', 'more-horizontal');

-- Demo wallets
INSERT INTO wallets (user_id, name, type, balance, color, icon) VALUES
(1, 'Bank BCA', 'bank', 5000000.00, '#0066CC', 'credit-card'),
(1, 'GoPay', 'e-wallet', 250000.00, '#00AA13', 'smartphone'),
(1, 'Dompet Cash', 'cash', 150000.00, '#10B981', 'wallet'),
(1, 'Tabungan', 'savings', 10000000.00, '#8B5CF6', 'piggy-bank');

-- Demo transactions for January 2026
INSERT INTO transactions (user_id, wallet_id, category_id, type, amount, description, transaction_date) VALUES
(1, 1, 1, 'income', 8000000.00, 'Gaji Januari', '2026-01-01'),
(1, 1, 5, 'expense', 500000.00, 'Belanja Bulanan', '2026-01-05'),
(1, 2, 6, 'expense', 150000.00, 'Isi Bensin', '2026-01-07'),
(1, 3, 5, 'expense', 75000.00, 'Makan Siang', '2026-01-10'),
(1, 1, 8, 'expense', 1000000.00, 'Bayar Listrik & Air', '2026-01-15');

-- Demo transactions for February 2026
INSERT INTO transactions (user_id, wallet_id, category_id, type, amount, description, transaction_date) VALUES
(1, 1, 1, 'income', 8000000.00, 'Gaji Februari', '2026-02-01'),
(1, 1, 2, 'income', 2000000.00, 'Bonus Kinerja', '2026-02-01'),
(1, 2, 5, 'expense', 400000.00, 'Belanja', '2026-02-02');

-- Trigger to update wallet balance after transaction insert
DELIMITER //
CREATE TRIGGER after_transaction_insert
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    IF NEW.type = 'income' THEN
        UPDATE wallets SET balance = balance + NEW.amount WHERE id = NEW.wallet_id;
    ELSE
        UPDATE wallets SET balance = balance - NEW.amount WHERE id = NEW.wallet_id;
    END IF;
END//

-- Trigger to update wallet balance after transaction update
CREATE TRIGGER after_transaction_update
AFTER UPDATE ON transactions
FOR EACH ROW
BEGIN
    -- Revert old transaction
    IF OLD.type = 'income' THEN
        UPDATE wallets SET balance = balance - OLD.amount WHERE id = OLD.wallet_id;
    ELSE
        UPDATE wallets SET balance = balance + OLD.amount WHERE id = OLD.wallet_id;
    END IF;
    
    -- Apply new transaction
    IF NEW.type = 'income' THEN
        UPDATE wallets SET balance = balance + NEW.amount WHERE id = NEW.wallet_id;
    ELSE
        UPDATE wallets SET balance = balance - NEW.amount WHERE id = NEW.wallet_id;
    END IF;
END//

-- Trigger to update wallet balance before transaction delete
CREATE TRIGGER before_transaction_delete
BEFORE DELETE ON transactions
FOR EACH ROW
BEGIN
    IF OLD.type = 'income' THEN
        UPDATE wallets SET balance = balance - OLD.amount WHERE id = OLD.wallet_id;
    ELSE
        UPDATE wallets SET balance = balance + OLD.amount WHERE id = OLD.wallet_id;
    END IF;
END//

DELIMITER ;
