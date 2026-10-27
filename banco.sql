-- Criação do banco de dados da cafeteria
CREATE DATABASE IF NOT EXISTS gestao_cafeteria;
USE gestao_cafeteria;

-- Tabela para o sistema de login
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- Tabela para o CRUD do cardápio da cafeteria
CREATE TABLE IF NOT EXISTS cardapio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_item VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL, -- Ex: Café, Doce, Salgado, Bebida Gelada
    descricao TEXT,
    preco DECIMAL(10, 2) NOT NULL,
    disponivel BOOLEAN DEFAULT TRUE
);

-- Inserindo um usuário administrador padrão (Senha: admin123)
-- Caso já exista, não duplica
INSERT INTO usuarios (nome, usuario, senha)
SELECT 'Gerente', 'admin', 'admin123'
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE usuario = 'admin'
);

-- Inserindo alguns itens iniciais demonstrativos no cardápio
INSERT INTO cardapio (nome_item, categoria, descricao, preco, disponivel)
SELECT 'Café Espresso', 'Café', 'Café puro, encorpado e aromático extraído sob pressão.', 6.50, TRUE
WHERE NOT EXISTS (SELECT 1 FROM cardapio WHERE nome_item = 'Café Espresso');

INSERT INTO cardapio (nome_item, categoria, descricao, preco, disponivel)
SELECT 'Cappuccino Tradicional', 'Café', 'Espresso com leite vaporizado e crema aveludada com toque de canela.', 12.00, TRUE
WHERE NOT EXISTS (SELECT 1 FROM cardapio WHERE nome_item = 'Cappuccino Tradicional');

INSERT INTO cardapio (nome_item, categoria, descricao, preco, disponivel)
SELECT 'Bolo de Cenoura com Chocolate', 'Doce', 'Fatia generosa de bolo fofinho coberto com brigadeiro cremoso.', 14.50, TRUE
WHERE NOT EXISTS (SELECT 1 FROM cardapio WHERE nome_item = 'Bolo de Cenoura com Chocolate');

INSERT INTO cardapio (nome_item, categoria, descricao, preco, disponivel)
SELECT 'Pão de Queijo Mineiro', 'Salgado', 'Pão de queijo quentinho com queijo da Canastra.', 7.00, TRUE
WHERE NOT EXISTS (SELECT 1 FROM cardapio WHERE nome_item = 'Pão de Queijo Mineiro');
