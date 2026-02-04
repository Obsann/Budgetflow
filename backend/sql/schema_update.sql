CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_ip_time ON login_attempts(ip_address, attempt_time);
