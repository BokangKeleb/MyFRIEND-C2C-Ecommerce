USE myfrienddb;

ALTER TABLE transactions ADD COLUMN IF NOT EXISTS orderStatus VARCHAR(50) DEFAULT 'Pending Payment' AFTER paymentStatus;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS transactionReference VARCHAR(80) NULL AFTER orderStatus;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS checkoutReference VARCHAR(80) NULL AFTER transactionReference;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS gatewayReference VARCHAR(100) NULL AFTER checkoutReference;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS paidAt DATETIME NULL AFTER gatewayReference;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS quantity INT NOT NULL DEFAULT 1 AFTER productID;

UPDATE transactions
SET transactionReference = CONCAT('MF-OLD-', transactionID)
WHERE transactionReference IS NULL OR transactionReference = '';

ALTER TABLE transactions ADD UNIQUE INDEX IF NOT EXISTS uq_transaction_reference (transactionReference);
