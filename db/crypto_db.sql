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

-- 3. Cryptocurrencies Table (Includes `current_price` and `sign`)
CREATE TABLE cryptocurrencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    symbol VARCHAR(10) UNIQUE NOT NULL,
    sign VARCHAR(10) NOT NULL, -- Example: $, Ξ, ₿
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

-- 5. Market Prices Table
CREATE TABLE market_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crypto_id INT NOT NULL,
    price DECIMAL(18, 8) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (crypto_id) REFERENCES cryptocurrencies(id) ON DELETE CASCADE
);


DELIMITER $$

CREATE TRIGGER update_crypto_price
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
CREATE TABLE alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    crypto_id INT NOT NULL,
    price_threshold DECIMAL(18, 8) NOT NULL,
    alert_type ENUM('higher', 'lower') NOT NULL,
    notified BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (crypto_id) REFERENCES cryptocurrencies(id) ON DELETE CASCADE
);

-- 9. Logs Table (Audit logs)
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 10. Payments Table (Stripe transactions)
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

-- Insert Admin User
INSERT INTO users (email, password_hash, role, balance, two_factor_enabled)
VALUES ('admin@cryptotrade.com', SHA2('admin123', 256), 'admin', 100000.00, TRUE);

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

-- Insert Sample Users
INSERT INTO users (email, password_hash, role, balance, two_factor_enabled) VALUES 
('admin', '$2y$10$znTMP8pvA0uP5SAcd25AtuZCom/1bH351oo5bzvhZDhCMztzWglUO', 'admin', 25000, TRUE), 
('a3emond@gmail.com', '$2y$10$DaqlKpOOivaiUwbLFh7/pegZ8iCEJBbALlGhxubnnHSG0V2AWxsme', 'admin', 25000, TRUE), 
('user1@cryptotrade.com', '$2y$10$GPz5jlzQ40uW.2zHuKfuF.O6QTStPBRZJL7yeM4GxNed.5Xh1nok6', 'user', 5000, FALSE), 
('user2@cryptotrade.com', '$2y$10$dw43wQ0OcIfpUfAV6o..VOZiXttRqdE4.RietyqIQ3VB0/Q7JT6vu', 'user', 10000, TRUE);

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
INSERT INTO admin_settings (setting_key, setting_value)
VALUES 
    ('max_transactions_per_day', '10'),
    ('min_deposit_amount', '50');

-- Insert Sample Alerts
INSERT INTO alerts (user_id, crypto_id, price_threshold, alert_type)
VALUES 
    (2, 1, 50000.00, 'higher'),
    (3, 3, 0.10, 'lower');

-- Insert Sample Logs
INSERT INTO logs (user_id, action, ip_address, user_agent)
VALUES 
    (2, 'Logged in', '192.168.1.1', 'Mozilla Firefox'),
    (3, 'Bought 1000 DOGE', '192.168.1.2', 'Google Chrome');

-- Insert Sample Payments
INSERT INTO payments (user_id, stripe_transaction_id, amount, status)
VALUES 
    (2, 'txn_123456', 100.00, 'completed'),
    (3, 'txn_789012', 500.00, 'pending');
