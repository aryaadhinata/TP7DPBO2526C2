CREATE DATABASE IF NOT EXISTS db_port CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_port;

-- Entitas 1: kapal pengangkut
CREATE TABLE ships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    imo_number CHAR(7) NOT NULL UNIQUE,
    flag_country VARCHAR(60) NOT NULL,
    capacity_teu INT NOT NULL
) ENGINE=InnoDB;

-- Entitas 2: barang / komoditas yang diperdagangkan
CREATE TABLE cargos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    hs_code VARCHAR(10) NOT NULL UNIQUE,
    category VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- Entitas 3: transaksi pengiriman ekspor / impor
-- Memiliki foreign key ke ships dan cargos
CREATE TABLE shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ship_id INT NOT NULL,
    cargo_id INT NOT NULL,
    type ENUM('Ekspor', 'Impor') NOT NULL,
    origin_port VARCHAR(100) NOT NULL,
    destination_port VARCHAR(100) NOT NULL,
    quantity_ton DECIMAL(10,2) NOT NULL,
    shipment_date DATE NOT NULL,
    status ENUM('Menunggu', 'Dalam Pelayaran', 'Tiba', 'Selesai') NOT NULL DEFAULT 'Menunggu',
    FOREIGN KEY (ship_id) REFERENCES ships(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (cargo_id) REFERENCES cargos(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Data contoh (fiktif)
INSERT INTO ships (name, imo_number, flag_country, capacity_teu) VALUES
('MV Samudra Nusantara', '9100011', 'Indonesia', 4500),
('MV Pacific Voyager', '9100022', 'Singapura', 8200),
('MV Orient Star', '9100033', 'Panama', 12000);

INSERT INTO cargos (name, hs_code, category) VALUES
('Minyak Kelapa Sawit (CPO)', '151110', 'Pertanian'),
('Batu Bara', '270119', 'Pertambangan'),
('Biji Kopi', '090111', 'Pertanian'),
('Telepon Seluler', '851713', 'Elektronik'),
('Mesin Industri', '847989', 'Mesin');

INSERT INTO shipments (ship_id, cargo_id, type, origin_port, destination_port, quantity_ton, shipment_date, status) VALUES
(1, 1, 'Ekspor', 'Tanjung Priok, Jakarta', 'Rotterdam, Belanda', 1500.00, '2026-10-12', 'Dalam Pelayaran'),
(2, 4, 'Impor', 'Shanghai, Tiongkok', 'Tanjung Priok, Jakarta', 320.50, '2026-10-20', 'Tiba'),
(3, 2, 'Ekspor', 'Tanjung Perak, Surabaya', 'Mumbai, India', 8000.00, '2026-11-02', 'Menunggu'),
(1, 3, 'Ekspor', 'Tanjung Priok, Jakarta', 'Hamburg, Jerman', 450.00, '2026-09-28', 'Selesai'),
(2, 5, 'Impor', 'Busan, Korea Selatan', 'Tanjung Perak, Surabaya', 210.75, '2026-11-05', 'Menunggu');
