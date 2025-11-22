-- Database setup for SIG Minahasa application
-- Run this script to create the database and tables

CREATE DATABASE IF NOT EXISTS sig_minahasa;
USE sig_minahasa;

-- Table for flood-prone areas
CREATE TABLE IF NOT EXISTS banjir (
    id VARCHAR(50) PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    lat DECIMAL(10, 8) NOT NULL,
    lng DECIMAL(11, 8) NOT NULL,
    keterangan TEXT,
    level ENUM('Rendah', 'Sedang', 'Tinggi') DEFAULT 'Sedang',
    tanggal DATE DEFAULT CURRENT_DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for landslide-prone areas
CREATE TABLE IF NOT EXISTS longsor (
    id VARCHAR(50) PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    lat DECIMAL(10, 8) NOT NULL,
    lng DECIMAL(11, 8) NOT NULL,
    keterangan TEXT,
    level ENUM('Rendah', 'Sedang', 'Tinggi') DEFAULT 'Sedang',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for schools (evacuation points)
CREATE TABLE IF NOT EXISTS sekolah (
    id VARCHAR(50) PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    lat DECIMAL(10, 8) NOT NULL,
    lng DECIMAL(11, 8) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for hospitals
CREATE TABLE IF NOT EXISTS rs (
    id VARCHAR(50) PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    lat DECIMAL(10, 8) NOT NULL,
    lng DECIMAL(11, 8) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO banjir (id, nama, lat, lng, keterangan, level, tanggal) VALUES
('demo1', 'Banjir Tondano Timur', 1.30500000, 124.92000000, 'Luapan Danau Tondano', 'Tinggi', '2024-12-01'),
('demo2', 'Genangan Air Wawalintouan', 1.30200000, 124.91500000, 'Drainase buruk', 'Sedang', '2024-12-01');

INSERT INTO longsor (id, nama, lat, lng, keterangan, level) VALUES
('demo3', 'Longsor Ruas Tondano-Tomohon', 1.29000000, 124.89000000, 'Tebing labil', 'Tinggi');

INSERT INTO sekolah (id, nama, lat, lng, keterangan) VALUES
('demo4', 'SMA N 1 Tondano', 1.30950000, 124.91300000, 'Sekolah Menengah Atas'),
('demo5', 'Universitas Negeri Manado', 1.27730000, 124.88310000, 'Kampus Pusat');

INSERT INTO rs (id, nama, lat, lng, keterangan) VALUES
('demo6', 'RSUD Sam Ratulangi', 1.31500000, 124.90500000, 'Rumah Sakit Umum Daerah'),
('demo7', 'Puskesmas Tondano', 1.31000000, 124.91000000, 'Layanan Kesehatan Dasar');

-- Create admin user table (for future expansion)
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Insert default admin user (password: sebas)
INSERT INTO admin_users (username, password_hash, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@minahasa.go.id');
-- Note: The above hash is for 'sebas' - in production, use password_hash() function
