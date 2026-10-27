-- Criacao do banco de dados da cafeteria
CREATE DATABASE IF NOT EXISTS gestao_cafeteria;
USE gestao_cafeteria;

-- Tabela para o sistema de login
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- Tabela para o CRUD do cardapio da cafeteria
CREATE TABLE IF NOT EXISTS cardapio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_item VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10, 2) NOT NULL,
    disponivel BOOLEAN DEFAULT TRUE
);

-- Inserindo usuario administrador padrao (Senha: admin123)
INSERT INTO usuarios (nome, usuario, senha)
SELECT 'Gerente', 'admin', 'admin123'
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE usuario = 'admin'
);

-- Itens iniciais de exemplo
INSERT INTO cardapio (nome_item, categoria, descricao, preco, disponivel)
SELECT 'Cafe Espresso', 'Cafe', 'Cafe puro tradicional.', 6.50, TRUE
WHERE NOT EXISTS (SELECT 1 FROM cardapio WHERE nome_item = 'Cafe Espresso');

INSERT INTO cardapio (nome_item, categoria, descricao, preco, disponivel)
SELECT 'Bolo de Cenoura', 'Doce', 'Bolo de cenoura com cobertura de chocolate.', 14.50, TRUE
WHERE NOT EXISTS (SELECT 1 FROM cardapio WHERE nome_item = 'Bolo de Cenoura');

INSERT INTO cardapio (nome_item, categoria, descricao, preco, disponivel)
SELECT 'Pao de Queijo', 'Salgado', 'Pao de queijo tradicional.', 7.00, TRUE
WHERE NOT EXISTS (SELECT 1 FROM cardapio WHERE nome_item = 'Pao de Queijo');
