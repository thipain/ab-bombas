CREATE DATABASE ab_bombas;
USE ab_bombas;

CREATE TABLE Categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    icone VARCHAR(50),
    cor VARCHAR(20)
);

CREATE TABLE Produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10, 2) NOT NULL,
    categoria_id INT,
    estoque INT DEFAULT 0,
    imagem_url VARCHAR(255),
    FOREIGN KEY (categoria_id) REFERENCES Categorias(id) ON DELETE SET NULL
);

CREATE TABLE Usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'cliente') DEFAULT 'cliente',
    status ENUM('ativo', 'bloqueado') DEFAULT 'ativo'
);


INSERT INTO Usuarios (nome, email, senha, tipo, status) VALUES (
    'Administrador',
    'admin@abbombas.com',
    '$2y$10$82dQOPhqU0OuGQOzhWD2VuxYI2DH0tImHeRgx.coTqf7R/FZSVSl2',
    'admin',
    'ativo'
);