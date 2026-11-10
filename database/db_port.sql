-- PERHATIAN: skrip ini aman diimport berulang kali, tetapi akan MENGHAPUS semua data
-- di keempat tabel (DROP TABLE IF EXISTS) lalu mengisi ulang dengan data contoh.
CREATE DATABASE IF NOT EXISTS db_port CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_port;

-- Hapus dari tabel anak ke tabel induk (urutan mengikuti foreign key)
DROP TABLE IF EXISTS shipments;
DROP TABLE IF EXISTS ports;
DROP TABLE IF EXISTS cargos;
DROP TABLE IF EXISTS ships;

-- Entitas 1: kapal pengangkut (kapasitas dalam TEU)
CREATE TABLE ships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    imo_number CHAR(7) NOT NULL UNIQUE,
    flag_country VARCHAR(60) NOT NULL,
    capacity_teu INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Entitas 2: barang / komoditas yang diperdagangkan
CREATE TABLE cargos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    hs_code VARCHAR(10) NOT NULL UNIQUE,
    category VARCHAR(50) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Entitas 3: pelabuhan (asal / tujuan). Negara 'Indonesia' dianggap pelabuhan dalam negeri.
CREATE TABLE ports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(60) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_ports_name_country UNIQUE (name, country)
) ENGINE=InnoDB;

-- Entitas 4: transaksi pengiriman ekspor / impor (muatan dalam ton)
-- Memiliki foreign key ke ships, cargos, dan ports (asal + tujuan)
CREATE TABLE shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ship_id INT NOT NULL,
    cargo_id INT NOT NULL,
    type ENUM('Ekspor', 'Impor') NOT NULL,
    origin_port_id INT NOT NULL,
    destination_port_id INT NOT NULL,
    quantity_ton DECIMAL(10,2) NOT NULL,
    shipment_date DATE NOT NULL,
    status ENUM('Menunggu', 'Dalam Pelayaran', 'Tiba', 'Selesai') NOT NULL DEFAULT 'Menunggu',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ship_id) REFERENCES ships(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (cargo_id) REFERENCES cargos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (origin_port_id) REFERENCES ports(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (destination_port_id) REFERENCES ports(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    -- Catatan: asal <> tujuan divalidasi di aplikasi (MySQL melarang CHECK pada kolom FK ber-CASCADE)
    CHECK (quantity_ton > 0)
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

INSERT INTO ports (name, country) VALUES
('Tanjung Priok', 'Indonesia'),   -- 1
('Tanjung Perak', 'Indonesia'),   -- 2
('Rotterdam', 'Belanda'),         -- 3
('Shanghai', 'Tiongkok'),         -- 4
('Mumbai', 'India'),              -- 5
('Hamburg', 'Jerman'),            -- 6
('Busan', 'Korea Selatan');       -- 7

INSERT INTO shipments (ship_id, cargo_id, type, origin_port_id, destination_port_id, quantity_ton, shipment_date, status) VALUES
(1, 1, 'Ekspor', 1, 3, 1500.00, '2026-10-12', 'Dalam Pelayaran'),
(2, 4, 'Impor', 4, 1, 320.50, '2026-10-20', 'Tiba'),
(3, 2, 'Ekspor', 2, 5, 8000.00, '2026-11-02', 'Menunggu'),
(1, 3, 'Ekspor', 1, 6, 450.00, '2026-09-28', 'Selesai'),
(2, 5, 'Impor', 7, 2, 210.75, '2026-11-05', 'Menunggu');
