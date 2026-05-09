-- MediFind: Smart Pharmacy Inventory System
-- Import this file in phpMyAdmin or MySQL CLI.
-- Database: medifind_db
-- Default password for seeded admin/users/pharmacies: password

DROP DATABASE IF EXISTS medifind_db;
CREATE DATABASE medifind_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medifind_db;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE pharmacies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_name VARCHAR(100) NOT NULL,
    pharmacy_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    license_no VARCHAR(60) NOT NULL UNIQUE,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(80) NOT NULL,
    area VARCHAR(80) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pharmacies_city_area (city, area),
    INDEX idx_pharmacies_status (status)
) ENGINE=InnoDB;

CREATE TABLE medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    generic_name VARCHAR(150) NOT NULL,
    brand VARCHAR(120) NOT NULL,
    category VARCHAR(100) NOT NULL,
    strength VARCHAR(60) NOT NULL,
    form VARCHAR(60) NOT NULL,
    description VARCHAR(255),
    requires_prescription TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_medicines_name (name),
    INDEX idx_medicines_generic (generic_name),
    INDEX idx_medicines_category (category)
) ENGINE=InnoDB;

CREATE TABLE stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    price DECIMAL(10, 2) NOT NULL,
    batch_no VARCHAR(60) NOT NULL,
    expiry_date DATE NOT NULL,
    reorder_level INT NOT NULL DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_stock_pharmacy (pharmacy_id),
    INDEX idx_stock_medicine (medicine_id),
    INDEX idx_stock_quantity (quantity),
    INDEX idx_stock_expiry (expiry_date),
    CONSTRAINT fk_stock_pharmacy
        FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_stock_medicine
        FOREIGN KEY (medicine_id) REFERENCES medicines(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE substitute_medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id INT NOT NULL,
    substitute_medicine_id INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_substitute_pair (medicine_id, substitute_medicine_id),
    CONSTRAINT fk_substitute_main
        FOREIGN KEY (medicine_id) REFERENCES medicines(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_substitute_alternative
        FOREIGN KEY (substitute_medicine_id) REFERENCES medicines(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pharmacy_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_pharmacy (pharmacy_id),
    INDEX idx_orders_medicine (medicine_id),
    INDEX idx_orders_status (status),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_orders_pharmacy
        FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_orders_medicine
        FOREIGN KEY (medicine_id) REFERENCES medicines(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE low_stock_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_id INT NOT NULL,
    pharmacy_id INT NOT NULL,
    medicine_id INT NOT NULL,
    current_quantity INT NOT NULL,
    alert_message VARCHAR(255) NOT NULL,
    status ENUM('open', 'resolved', 'dismissed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_alerts_stock (stock_id),
    INDEX idx_alerts_pharmacy (pharmacy_id),
    INDEX idx_alerts_status (status),
    CONSTRAINT fk_alerts_stock
        FOREIGN KEY (stock_id) REFERENCES stock(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_alerts_pharmacy
        FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_alerts_medicine
        FOREIGN KEY (medicine_id) REFERENCES medicines(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO admins (name, email, password) VALUES
('MediFind Admin', 'admin@medifind.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu');

INSERT INTO users (name, email, phone, address, password) VALUES
('Aarav Sharma', 'aarav.sharma@example.com', '9876501001', 'Flat 402, Orchid Heights, Andheri West, Mumbai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu'),
('Diya Mehta', 'diya.mehta@example.com', '9876501002', '21 Lake View Road, Powai, Mumbai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu'),
('Kabir Khan', 'kabir.khan@example.com', '9876501003', '12 Hill Road, Bandra West, Mumbai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu'),
('Ananya Iyer', 'ananya.iyer@example.com', '9876501004', 'B-8 Palm Grove, Chembur, Mumbai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu'),
('Rohan Patel', 'rohan.patel@example.com', '9876501005', '5 Sardar Lane, Borivali West, Mumbai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu'),
('Nisha Rao', 'nisha.rao@example.com', '9876501006', '17 MG Road, Ghatkopar East, Mumbai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu'),
('Vivaan Das', 'vivaan.das@example.com', '9876501007', '9 Marine Drive, Churchgate, Mumbai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu'),
('Meera Nair', 'meera.nair@example.com', '9876501008', '703 Pearl Residency, Thane West', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu'),
('Aditya Singh', 'aditya.singh@example.com', '9876501009', '33 Sector 11, Vashi, Navi Mumbai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu'),
('Sara Thomas', 'sara.thomas@example.com', '9876501010', '101 Green Park, Kandivali East, Mumbai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu');

INSERT INTO pharmacies (owner_name, pharmacy_name, email, phone, license_no, address, city, area, password, status) VALUES
('Rajesh Verma', 'CarePlus Pharmacy', 'careplus.andheri@medifind.local', '9820002001', 'MH-FDA-1001', 'Shop 4, JP Road', 'Mumbai', 'Andheri West', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Priya Shah', 'HealthHub Chemist', 'healthhub.powai@medifind.local', '9820002002', 'MH-FDA-1002', 'Galleria Market, Hiranandani Gardens', 'Mumbai', 'Powai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Sameer Joshi', 'LifeLine Medicals', 'lifeline.bandra@medifind.local', '9820002003', 'MH-FDA-1003', 'Hill Road, Bandra West', 'Mumbai', 'Bandra West', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Neha Kulkarni', 'Wellness Corner', 'wellness.chembur@medifind.local', '9820002004', 'MH-FDA-1004', 'Central Avenue Road', 'Mumbai', 'Chembur', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Vikram Malhotra', 'MediQuick Store', 'mediquick.borivali@medifind.local', '9820002005', 'MH-FDA-1005', 'SV Road Market', 'Mumbai', 'Borivali West', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Pooja Menon', 'TrustMed Pharmacy', 'trustmed.ghatkopar@medifind.local', '9820002006', 'MH-FDA-1006', 'MG Road, Ghatkopar East', 'Mumbai', 'Ghatkopar East', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Arjun Reddy', 'CityCare Chemists', 'citycare.churchgate@medifind.local', '9820002007', 'MH-FDA-1007', 'Eros Building Lane', 'Mumbai', 'Churchgate', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Sneha Kapoor', 'Apollo Neighbourhood Pharmacy', 'apollo.thane@medifind.local', '9820002008', 'MH-FDA-1008', 'Station Road', 'Thane', 'Thane West', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Karan Desai', 'GreenCross Medicos', 'greencross.vashi@medifind.local', '9820002009', 'MH-FDA-1009', 'Sector 17 Plaza', 'Navi Mumbai', 'Vashi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Fatima Sheikh', 'DailyDose Pharmacy', 'dailydose.kandivali@medifind.local', '9820002010', 'MH-FDA-1010', 'Akurli Road', 'Mumbai', 'Kandivali East', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Harsh Jain', 'FamilyMed Store', 'familymed.dadar@medifind.local', '9820002011', 'MH-FDA-1011', 'Ranade Road', 'Mumbai', 'Dadar West', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Ishita Sen', 'PrimeCare Pharmacy', 'primecare.colaba@medifind.local', '9820002012', 'MH-FDA-1012', 'Colaba Causeway', 'Mumbai', 'Colaba', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Manav Batra', 'Swasthya Medicals', 'swasthya.malad@medifind.local', '9820002013', 'MH-FDA-1013', 'Link Road', 'Mumbai', 'Malad West', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Leena Fernandes', 'BlueCross Chemist', 'bluecross.juhu@medifind.local', '9820002014', 'MH-FDA-1014', 'Juhu Tara Road', 'Mumbai', 'Juhu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Omkar Pawar', 'MetroMed Pharmacy', 'metromed.kurla@medifind.local', '9820002015', 'MH-FDA-1015', 'LBS Marg', 'Mumbai', 'Kurla West', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Ritika Chopra', 'Sunrise Pharmacy', 'sunrise.sion@medifind.local', '9820002016', 'MH-FDA-1016', 'Sion Circle', 'Mumbai', 'Sion', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Naveen Gupta', 'Om Sai Medicos', 'omsai.mulund@medifind.local', '9820002017', 'MH-FDA-1017', 'MG Road', 'Mumbai', 'Mulund West', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Tara Pillai', 'HealthyLife Chemists', 'healthylife.vileparle@medifind.local', '9820002018', 'MH-FDA-1018', 'Tejpal Road', 'Mumbai', 'Vile Parle East', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Yusuf Ansari', 'Medico Mart', 'medicomart.mira@medifind.local', '9820002019', 'MH-FDA-1019', 'Mira Road Station Road', 'Mira Bhayandar', 'Mira Road', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active'),
('Bhavna Mehta', 'QuickHeal Pharmacy', 'quickheal.panvel@medifind.local', '9820002020', 'MH-FDA-1020', 'Old Panvel Market', 'Navi Mumbai', 'Panvel', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC0e58e0gUXs6wY6RGsu', 'active');

INSERT INTO medicines (name, generic_name, brand, category, strength, form, description, requires_prescription) VALUES
('Paracetamol 500', 'Paracetamol', 'MedLife', 'Analgesic', '500 mg', 'Tablet', 'Pain and fever relief tablet.', 0),
('Crocin Advance', 'Paracetamol', 'GSK', 'Analgesic', '500 mg', 'Tablet', 'Paracetamol tablet for fever and pain.', 0),
('Ibuprofen 400', 'Ibuprofen', 'Abbott', 'NSAID', '400 mg', 'Tablet', 'Anti-inflammatory pain relief medicine.', 0),
('Amoxicillin 500', 'Amoxicillin', 'Cipla', 'Antibiotic', '500 mg', 'Capsule', 'Penicillin-class antibiotic.', 1),
('Azithromycin 500', 'Azithromycin', 'Sun Pharma', 'Antibiotic', '500 mg', 'Tablet', 'Macrolide antibiotic.', 1),
('Cetirizine 10', 'Cetirizine', 'Dr. Reddy', 'Antihistamine', '10 mg', 'Tablet', 'Allergy relief antihistamine.', 0),
('Levocetirizine 5', 'Levocetirizine', 'Glenmark', 'Antihistamine', '5 mg', 'Tablet', 'Allergy relief tablet.', 0),
('Metformin 500', 'Metformin', 'USV', 'Diabetes', '500 mg', 'Tablet', 'Oral diabetes control medicine.', 1),
('Glimepiride 2', 'Glimepiride', 'Sanofi', 'Diabetes', '2 mg', 'Tablet', 'Sulfonylurea diabetes medicine.', 1),
('Amlodipine 5', 'Amlodipine', 'Pfizer', 'Hypertension', '5 mg', 'Tablet', 'Calcium channel blocker.', 1),
('Telmisartan 40', 'Telmisartan', 'Glenmark', 'Hypertension', '40 mg', 'Tablet', 'ARB blood pressure medicine.', 1),
('Atorvastatin 10', 'Atorvastatin', 'Cipla', 'Cholesterol', '10 mg', 'Tablet', 'Statin for cholesterol management.', 1),
('Pantoprazole 40', 'Pantoprazole', 'Alkem', 'Gastrointestinal', '40 mg', 'Tablet', 'Proton pump inhibitor.', 0),
('Omeprazole 20', 'Omeprazole', 'Torrent', 'Gastrointestinal', '20 mg', 'Capsule', 'Acidity relief proton pump inhibitor.', 0),
('Dolo 650', 'Paracetamol', 'Micro Labs', 'Analgesic', '650 mg', 'Tablet', 'Fever and pain relief tablet.', 0),
('Cough Relief Syrup', 'Dextromethorphan', 'Benadryl', 'Cough and Cold', '100 ml', 'Syrup', 'Dry cough relief syrup.', 0),
('Salbutamol Inhaler', 'Salbutamol', 'Cipla', 'Respiratory', '100 mcg', 'Inhaler', 'Bronchodilator inhaler.', 1),
('Montelukast 10', 'Montelukast', 'Lupin', 'Respiratory', '10 mg', 'Tablet', 'Asthma and allergy control tablet.', 1),
('ORS Sachet', 'Oral Rehydration Salts', 'Electral', 'Hydration', '21.8 g', 'Sachet', 'Oral rehydration solution powder.', 0),
('Vitamin D3 60000', 'Cholecalciferol', 'Uprise-D3', 'Vitamin', '60000 IU', 'Capsule', 'Vitamin D supplement.', 0),
('Calcium 500', 'Calcium Carbonate', 'Shelcal', 'Supplement', '500 mg', 'Tablet', 'Calcium supplement tablet.', 0),
('Insulin Glargine', 'Insulin Glargine', 'Lantus', 'Diabetes', '100 IU/ml', 'Injection', 'Long-acting insulin injection.', 1),
('Ciprofloxacin 500', 'Ciprofloxacin', 'Cipla', 'Antibiotic', '500 mg', 'Tablet', 'Fluoroquinolone antibiotic.', 1),
('Doxycycline 100', 'Doxycycline', 'Pfizer', 'Antibiotic', '100 mg', 'Capsule', 'Tetracycline-class antibiotic.', 1),
('Fluconazole 150', 'Fluconazole', 'Zydus', 'Antifungal', '150 mg', 'Tablet', 'Antifungal tablet.', 1),
('Ondansetron 4', 'Ondansetron', 'Alkem', 'Antiemetic', '4 mg', 'Tablet', 'Nausea and vomiting control tablet.', 1),
('Domperidone 10', 'Domperidone', 'Torrent', 'Gastrointestinal', '10 mg', 'Tablet', 'Motility medicine for nausea.', 1),
('Aspirin 75', 'Aspirin', 'Bayer', 'Cardiac', '75 mg', 'Tablet', 'Antiplatelet low-dose aspirin.', 1),
('Clopidogrel 75', 'Clopidogrel', 'Sanofi', 'Cardiac', '75 mg', 'Tablet', 'Antiplatelet medicine.', 1),
('Diclofenac Gel', 'Diclofenac Diethylamine', 'Novartis', 'Pain Relief', '1%', 'Gel', 'Topical anti-inflammatory gel.', 0);

-- 20 pharmacies x 30 medicines = 600 seeded stock rows.
INSERT INTO stock (pharmacy_id, medicine_id, quantity, price, batch_no, expiry_date, reorder_level)
SELECT
    p.id,
    m.id,
    CASE
        WHEN MOD(p.id * m.id, 17) = 0 THEN 4
        WHEN MOD(p.id * m.id, 19) = 0 THEN 7
        ELSE 12 + MOD((p.id * 11) + (m.id * 13), 90)
    END AS quantity,
    ROUND(18 + (m.id * 4.75) + MOD((p.id * 7) + (m.id * 5), 42), 2) AS price,
    CONCAT('MF', LPAD(p.id, 2, '0'), LPAD(m.id, 2, '0'), 'A') AS batch_no,
    DATE_ADD(CURDATE(), INTERVAL (180 + MOD((p.id * 31) + (m.id * 23), 760)) DAY) AS expiry_date,
    10 + MOD(m.id, 12) AS reorder_level
FROM pharmacies p
CROSS JOIN medicines m;

INSERT INTO substitute_medicines (medicine_id, substitute_medicine_id, reason) VALUES
(1, 2, 'Same generic molecule; verify dose before use.'),
(1, 15, 'Same generic molecule; higher strength needs dose check.'),
(2, 1, 'Same generic molecule; verify dose before use.'),
(2, 15, 'Same generic molecule; higher strength needs dose check.'),
(15, 1, 'Same generic molecule; lower strength alternative.'),
(6, 7, 'Same antihistamine family; consult pharmacist for suitability.'),
(7, 6, 'Same antihistamine family; consult pharmacist for suitability.'),
(13, 14, 'Same acid-control class; consult pharmacist for use.'),
(14, 13, 'Same acid-control class; consult pharmacist for use.'),
(4, 5, 'Antibiotic alternative only when prescribed by a doctor.'),
(5, 4, 'Antibiotic alternative only when prescribed by a doctor.'),
(23, 24, 'Antibiotic alternative only when prescribed by a doctor.'),
(24, 23, 'Antibiotic alternative only when prescribed by a doctor.'),
(8, 9, 'Diabetes therapy alternative only under medical advice.'),
(9, 8, 'Diabetes therapy alternative only under medical advice.'),
(10, 11, 'Blood pressure therapy alternative only under medical advice.'),
(11, 10, 'Blood pressure therapy alternative only under medical advice.'),
(28, 29, 'Cardiac antiplatelet alternative only under medical advice.'),
(29, 28, 'Cardiac antiplatelet alternative only under medical advice.'),
(3, 30, 'Pain relief alternative; route and dose differ.');

INSERT INTO orders (user_id, pharmacy_id, medicine_id, quantity, unit_price, total_amount, status, created_at, updated_at) VALUES
(1, 1, 1, 2, 27.75, 55.50, 'completed', DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(2, 2, 13, 1, 84.75, 84.75, 'confirmed', DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 11 DAY)),
(3, 3, 6, 3, 55.50, 166.50, 'pending', DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(4, 4, 19, 6, 120.25, 721.50, 'completed', DATE_SUB(NOW(), INTERVAL 9 DAY), DATE_SUB(NOW(), INTERVAL 8 DAY)),
(5, 5, 8, 2, 67.00, 134.00, 'cancelled', DATE_SUB(NOW(), INTERVAL 8 DAY), DATE_SUB(NOW(), INTERVAL 8 DAY)),
(6, 6, 10, 1, 73.50, 73.50, 'completed', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY)),
(7, 7, 16, 1, 112.00, 112.00, 'confirmed', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY)),
(8, 8, 20, 4, 130.00, 520.00, 'pending', DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY)),
(9, 9, 23, 2, 145.75, 291.50, 'completed', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(10, 10, 3, 2, 42.25, 84.50, 'confirmed', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 11, 15, 2, 103.50, 207.00, 'pending', DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(2, 12, 22, 1, 178.25, 178.25, 'completed', DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3, 13, 25, 1, 163.50, 163.50, 'completed', DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, 14, 30, 1, 188.50, 188.50, 'pending', DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(5, 15, 12, 2, 98.25, 196.50, 'confirmed', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6, 16, 27, 3, 172.00, 516.00, 'completed', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(7, 17, 18, 1, 125.75, 125.75, 'pending', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(8, 18, 21, 2, 141.75, 283.50, 'confirmed', DATE_SUB(NOW(), INTERVAL 18 HOUR), DATE_SUB(NOW(), INTERVAL 17 HOUR)),
(9, 19, 5, 1, 58.50, 58.50, 'completed', DATE_SUB(NOW(), INTERVAL 10 HOUR), DATE_SUB(NOW(), INTERVAL 9 HOUR)),
(10, 20, 28, 2, 177.25, 354.50, 'pending', DATE_SUB(NOW(), INTERVAL 4 HOUR), DATE_SUB(NOW(), INTERVAL 4 HOUR));

INSERT INTO low_stock_alerts (stock_id, pharmacy_id, medicine_id, current_quantity, alert_message, status)
SELECT
    s.id,
    s.pharmacy_id,
    s.medicine_id,
    s.quantity,
    CONCAT(m.name, ' is low at ', s.quantity, ' unit(s).'),
    'open'
FROM stock s
INNER JOIN medicines m ON m.id = s.medicine_id
WHERE s.quantity <= s.reorder_level
ORDER BY s.quantity ASC, s.id ASC
LIMIT 40;

-- Quick seed checks:
-- SELECT COUNT(*) FROM stock; -- 600
-- SELECT * FROM admins; -- admin@medifind.local / password
