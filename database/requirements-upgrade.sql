-- FlareWise requirements upgrade
-- Run this once in phpMyAdmin (or MySQL) after importing flarewise.sql.

ALTER TABLE medications
    ADD COLUMN reminder_type ENUM('Medication', 'Skincare') NOT NULL DEFAULT 'Medication'
    AFTER dosage;

CREATE TABLE IF NOT EXISTS weather_preferences (
    user_id INT PRIMARY KEY,
    city VARCHAR(100) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
