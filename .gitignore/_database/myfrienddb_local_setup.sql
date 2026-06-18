CREATE DATABASE IF NOT EXISTS myfrienddb;
USE myfrienddb;

CREATE TABLE IF NOT EXISTS users (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'buyer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    shop_name VARCHAR(100) NULL,
    sellerPhone VARCHAR(20) NULL
);

CREATE TABLE IF NOT EXISTS products (
    productID INT AUTO_INCREMENT PRIMARY KEY,
    sellerID INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    shop_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_seller
        FOREIGN KEY (sellerID) REFERENCES users(userID)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS transactions (
    transactionID INT AUTO_INCREMENT PRIMARY KEY,
    buyerID INT NOT NULL,
    productID INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    amount DECIMAL(10,2) NOT NULL,
    paymentStatus VARCHAR(50) DEFAULT 'Pending',
    orderStatus VARCHAR(50) DEFAULT 'Pending Payment',
    transactionReference VARCHAR(80) NULL UNIQUE,
    checkoutReference VARCHAR(80) NULL,
    gatewayReference VARCHAR(100) NULL,
    paidAt DATETIME NULL,
    transactionDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    buyerName VARCHAR(100) NULL,
    buyerPhone VARCHAR(20) NULL,
    buyerEmail VARCHAR(100) NULL,
    deliveryAddress TEXT NULL,
    paymentMethod VARCHAR(50) NULL,
    INDEX idx_checkout_reference (checkoutReference),
    CONSTRAINT fk_transactions_buyer
        FOREIGN KEY (buyerID) REFERENCES users(userID)
        ON DELETE CASCADE,
    CONSTRAINT fk_transactions_product
        FOREIGN KEY (productID) REFERENCES products(productID)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS cart (
    cartID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    productID INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    addedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cart_user_product (userID, productID),
    CONSTRAINT fk_cart_user
        FOREIGN KEY (userID) REFERENCES users(userID)
        ON DELETE CASCADE,
    CONSTRAINT fk_cart_product
        FOREIGN KEY (productID) REFERENCES products(productID)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS password_resets (
    resetID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    tokenHash CHAR(64) NOT NULL UNIQUE,
    expiresAt DATETIME NOT NULL,
    usedAt DATETIME NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_password_reset_user (userID),
    INDEX idx_password_reset_expiry (expiresAt),
    CONSTRAINT fk_password_resets_user
        FOREIGN KEY (userID) REFERENCES users(userID)
        ON DELETE CASCADE
);
