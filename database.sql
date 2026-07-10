CREATE DATABASE flarewise;

USE flarewise;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE symptoms (
    symptom_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    itching INT,
    redness INT,
    dryness INT,
    irritation INT,
    notes TEXT,
    symptom_date DATE,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE medications (
    medication_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    medicine_name VARCHAR(100),
    dosage VARCHAR(50),
    reminder_time TIME,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE skin_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    image_name VARCHAR(255),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE ingredients (
    ingredient_id INT AUTO_INCREMENT PRIMARY KEY,
    ingredient_name VARCHAR(100),
    status VARCHAR(30),
    description TEXT
);

INSERT INTO ingredients(ingredient_name,status,description) VALUES
('Fragrance','Avoid','Can trigger eczema flare-ups'),
('Alcohol Denat','Avoid','Can dry the skin'),
('Niacinamide','Safe','Helps strengthen the skin barrier'),
('Glycerin','Safe','Excellent moisturizer'),
('Ceramide','Safe','Repairs skin barrier'),
('Parfum','Avoid','May irritate sensitive skin');

CREATE TABLE weather_history(
    weather_id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(50),
    temperature DOUBLE,
    humidity DOUBLE,
    flare_risk VARCHAR(20),
    date_checked TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);