-- 1. Create Database
CREATE DATABASE IF NOT EXISTS crypto_db;
USE crypto_db;

-- 2. Users Table
CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
email VARCHAR(255) UNIQUE NOT NULL,
password_hash VARCHAR(255) NOT NULL,
role ENUM('admin', 'user') DEFAULT 'user',
balance DECIMAL(18, 8) DEFAULT 0.00,
two_factor_enabled BOOLEAN DEFAULT FALSE,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Cryptocurrencies Table
CREATE TABLE cryptocurrencies (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) UNIQUE NOT NULL,
symbol VARCHAR(10) UNIQUE NOT NULL,
sign VARCHAR(10) NOT NULL,
initial_price DECIMAL(18, 8) NOT NULL,
current_price DECIMAL(18, 8) NOT NULL DEFAULT 0.00,
volatility ENUM('low', 'medium', 'high') NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. User Wallets Table
CREATE TABLE user_wallets (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
crypto_id INT NOT NULL,
balance DECIMAL(18, 8) DEFAULT 0.00,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (crypto_id) REFERENCES cryptocurrencies(id) ON DELETE CASCADE,
UNIQUE (user_id, crypto_id)
);

-- 5. Market Prices Table (updated with `created_at`)
CREATE TABLE market_prices (
id INT AUTO_INCREMENT PRIMARY KEY,
crypto_id INT NOT NULL,
price DECIMAL(18, 8) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (crypto_id) REFERENCES cryptocurrencies(id) ON DELETE CASCADE
);

DELIMITER $$
CREATE TRIGGER trg_update_current_price
    BEFORE INSERT ON market_prices
    FOR EACH ROW
BEGIN
    UPDATE cryptocurrencies
    SET current_price = NEW.price
    WHERE id = NEW.crypto_id;
END $$
DELIMITER ;


-- 6. Transactions Table
CREATE TABLE transactions (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
crypto_id INT NOT NULL,
transaction_type ENUM('buy', 'sell') NOT NULL,
amount DECIMAL(18, 8) NOT NULL,
price DECIMAL(18, 8) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (crypto_id) REFERENCES cryptocurrencies(id) ON DELETE CASCADE
);

-- 7. Admin Settings Table
CREATE TABLE admin_settings (
id INT AUTO_INCREMENT PRIMARY KEY,
setting_key VARCHAR(100) UNIQUE NOT NULL,
setting_value VARCHAR(255) NOT NULL
);

-- 8. Alerts Table
CREATE TABLE ale rts (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
crypto_id INT NOT NULL,
price_threshold DECIMAL(18, 8) NOT NULL,
alert_type ENUM('higher', 'lower') NOT NULL,
active BOOLEAN DEFAULT TRUE,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
last_triggered_at TIMESTAMP NULL DEFAULT NULL,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (crypto_id) REFERENCES cryptocurrencies(id) ON DELETE CASCADE
);

-- 8.5 Notifications Table
CREATE TABLE notifications (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
alert_id INT NOT NULL,
message TEXT NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
seen BOOLEAN DEFAULT FALSE,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (alert_id) REFERENCES alerts(id) ON DELETE CASCADE
);


-- 9. Logs Table
CREATE TABLE logs (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
action VARCHAR(255) NOT NULL,
ip_address VARCHAR(45) NOT NULL,
user_agent TEXT NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


-- 10. Payments Table
CREATE TABLE payments (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
stripe_transaction_id VARCHAR(255) UNIQUE NOT NULL,
amount DECIMAL(18, 8) NOT NULL,
status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 11. Email Tokens Table
CREATE TABLE email_tokens (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
token VARCHAR(255) NOT NULL,
type ENUM('verify_email', 'reset_password') NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- INDEXES
CREATE INDEX idx_alerts_user_id ON alerts(user_id);
CREATE INDEX idx_alerts_crypto_id ON alerts(crypto_id);
CREATE INDEX idx_transactions_user_id ON transactions(user_id);
CREATE INDEX idx_transactions_crypto_id ON transactions(crypto_id);
CREATE INDEX idx_notifications_user_id_seen ON notifications(user_id, seen);



-- Insert Sample Cryptos 
INSERT INTO cryptocurrencies (name, symbol, sign, initial_price, current_price, volatility)
VALUES
('Bitcoin', 'BTC', '₿', 45000.00, 45000.00, 'medium'),
('Ethereum', 'ETH', 'Ξ', 3000.00, 3000.00, 'medium'),
('Dogecoin', 'DOGE', 'Ð', 0.15, 0.15, 'high'),
('Tether', 'USDT', '$', 1.00, 1.00, 'low'),
('Solana', 'SOL', '◎', 120.00, 120.00, 'medium');

-- Insert Sample Market Prices
INSERT INTO market_prices (crypto_id, price)
VALUES
(1, 45000.00),
(2, 3000.00),
(3, 0.15),
(4, 1.00),
(5, 120.00);

-- Insert Sample Users (password = 'password')
INSERT INTO users (email, password_hash, role, balance, two_factor_enabled) VALUES
('admin', '$2y$10$kefxc4rnRatJr7f6oypzWuA4KHxO7XgdQfYZus5/1ElYHuM/DwtV2', 'admin', 25000, TRUE),
('a3emond@gmail.com', '$2y$10$DaqlKpOOivaiUwbLFh7/pegZ8iCEJBbALlGhxubnnHSG0V2AWxsme', 'admin', 25000, TRUE),
('user1@cryptotrade.com', '$2y$10$kefxc4rnRatJr7f6oypzWuA4KHxO7XgdQfYZus5/1ElYHuM/DwtV2', 'user', 5000, FALSE),
('user2@cryptotrade.com', '$2y$10$kefxc4rnRatJr7f6oypzWuA4KHxO7XgdQfYZus5/1ElYHuM/DwtV2', 'user', 10000, TRUE);

-- Insert Sample User Wallets
INSERT INTO user_wallets (user_id, crypto_id, balance)
VALUES
(2, 1, 0.02),
(2, 2, 1.50),
(3, 3, 1000.00),
(3, 5, 5.00);

-- Insert Sample Transactions
INSERT INTO transactions (user_id, crypto_id, transaction_type, amount, price)
VALUES
(2, 1, 'buy', 0.02, 45000.00),
(2, 2, 'buy', 1.50, 3000.00),
(3, 3, 'buy', 1000.00, 0.15),
(3, 5, 'buy', 5.00, 120.00);

-- Insert Sample Admin Settings
INSERT INTO admin_settings (setting_key, setting_value) VALUES
('max_transactions_per_day', '10'),
('min_deposit_amount', '25.00'),
('2fa_required', 'true'),
('auto_sell_threshold_percentage', '-10'),
('invitation_required', 'true'),
('initial_account_balance_usd', '1000'),
('price_update_interval', '1'),
('audit_log_retention_days', '90'),
('max_failed_logins', '5'),
('stripe_test_mode', 'true');


-- Insert Sample Alerts
INSERT INTO alerts (user_id, crypto_id, price_threshold, alert_type, active, created_at, last_triggered_at)
VALUES
-- User 2
(2, 1, 50000.00, 'higher', TRUE, NOW(), NULL),
(2, 2, 2500.00, 'lower', TRUE, NOW(), NULL),

-- User 3
(3, 3, 0.10, 'lower', TRUE, NOW(), NULL),
(3, 5, 130.00, 'higher', TRUE, NOW(), NULL),

-- User 4
(4, 4, 1.01, 'higher', TRUE, NOW(), NULL),
(4, 1, 44000.00, 'lower', TRUE, NOW(), NULL);

-- Insert Sample Notifications
INSERT INTO notifications (user_id, alert_id, message, created_at, seen)
VALUES
    (2, 1, 'Bitcoin has reached $50000.00', NOW(), FALSE),
    (3, 2, 'Dogecoin has dropped below $0.10', NOW(), TRUE);


-- Insert Sample Logs
INSERT INTO logs (user_id, action, ip_address, user_agent, created_at) VALUES
(2, 'Logged in', '192.168.0.10', 'Mozilla/5.0 Firefox', NOW() - INTERVAL 1 HOUR),
(2, 'Viewed Dashboard', '192.168.0.10', 'Mozilla/5.0 Firefox', NOW() - INTERVAL 58 MINUTE),
(2, 'Bought BTC', '192.168.0.10', 'Mozilla/5.0 Firefox', NOW() - INTERVAL 56 MINUTE),
(2, 'Viewed Market', '192.168.0.10', 'Mozilla/5.0 Firefox', NOW() - INTERVAL 54 MINUTE),
(2, 'Logged out', '192.168.0.10', 'Mozilla/5.0 Firefox', NOW() - INTERVAL 53 MINUTE),

(3, 'Logged in', '192.168.0.20', 'Chrome/115.0', NOW() - INTERVAL 2 HOUR),
(3, 'Viewed Wallet', '192.168.0.20', 'Chrome/115.0', NOW() - INTERVAL 118 MINUTE),
(3, 'Sold DOGE', '192.168.0.20', 'Chrome/115.0', NOW() - INTERVAL 116 MINUTE),
(3, 'Generated Report', '192.168.0.20', 'Chrome/115.0', NOW() - INTERVAL 114 MINUTE),
(3, 'Logged out', '192.168.0.20', 'Chrome/115.0', NOW() - INTERVAL 112 MINUTE),

(4, 'Failed Login Attempt', '192.168.0.30', 'Safari/15.6', NOW() - INTERVAL 3 HOUR),
(4, 'Logged in', '192.168.0.30', 'Safari/15.6', NOW() - INTERVAL 178 MINUTE),
(4, 'Viewed Market', '192.168.0.30', 'Safari/15.6', NOW() - INTERVAL 176 MINUTE),
(4, 'Set Alert on ETH', '192.168.0.30', 'Safari/15.6', NOW() - INTERVAL 175 MINUTE),
(4, 'Logged out', '192.168.0.30', 'Safari/15.6', NOW() - INTERVAL 174 MINUTE),

-- Additional randomized logs
(2, 'Reset Password', '192.168.0.11', 'Mozilla/5.0', NOW() - INTERVAL 1 DAY),
(2, 'Enabled 2FA', '192.168.0.11', 'Mozilla/5.0', NOW() - INTERVAL 1 DAY + INTERVAL 2 HOUR),
(3, 'Viewed Report', '192.168.0.21', 'Chrome/114.0', NOW() - INTERVAL 2 DAY),
(3, 'Modified Alert', '192.168.0.21', 'Chrome/114.0', NOW() - INTERVAL 2 DAY + INTERVAL 1 HOUR),
(4, 'Generated PDF Report', '192.168.0.31', 'Safari/15.6', NOW() - INTERVAL 3 DAY),
(4, 'Deleted Alert', '192.168.0.31', 'Safari/15.6', NOW() - INTERVAL 3 DAY + INTERVAL 2 HOUR);




-- Insert Sample Payments
INSERT INTO payments (user_id, stripe_transaction_id, amount, status)
VALUES
(2, 'txn_123456', 100.00, 'completed'),
(3, 'txn_789012', 500.00, 'pending');
