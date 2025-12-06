-- Database setup for Elayadi Prestige Car
-- Run this in phpMyAdmin or MySQL command line

CREATE DATABASE IF NOT EXISTS elayadi_prestige_car;
USE elayadi_prestige_car;

-- Cars table
CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    brand VARCHAR(100),
    model VARCHAR(100),
    price_per_day DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    available BOOLEAN DEFAULT TRUE,
    fuel_type VARCHAR(50),
    transmission VARCHAR(50),
    seats INT,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reservations table
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    pickup_date DATE NOT NULL,
    return_date DATE NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id)
);

-- Insert sample cars
INSERT INTO cars (name, brand, model, price_per_day, image, fuel_type, transmission, seats, featured) VALUES
('Renault Clio 5', 'Renault', 'Clio 5', 300.00, 'images/clio5.png', 'Diesel', 'Manual/Automatic', 5, 1),
('Dacia Logan', 'Dacia', 'Logan', 300.00, 'images/logan.png', 'Diesel', 'Manual', 5, 1),
('Hyundai Tucson', 'Hyundai', 'Tucson', 500.00, 'images/tucson.jpg', 'Essence', 'Automatic', 5, 1),
('Volkswagen Golf 8', 'Volkswagen', 'Golf 8', 800.00, 'images/golff8.png', 'Petrol', 'Automatic', 5, 0),
('Golf 8 R Line', 'Volkswagen', 'Golf 8 R Line', 800.00, 'images/golf8.png', 'Petrol', 'Automatic', 5, 0),
('Economy Car', 'Generic', 'Economy', 400.00, 'images/renault.png', 'Petrol', 'Manual', 4, 0);
