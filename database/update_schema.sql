-- update_schema.sql
-- Run this to add the allocations table

CREATE TABLE IF NOT EXISTS budget_allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    is_paid BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
