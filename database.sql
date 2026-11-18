CREATE DATABASE IF NOT EXISTS diarybook;
USE diarybook;

-- Tabel User untuk menyimpan Public Key (Autentikasi Asimetris)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    public_key TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Catatan Terenkripsi (Simetris AES-256)
CREATE TABLE IF NOT EXISTS encrypted_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    ciphertext TEXT NOT NULL,
    iv VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
