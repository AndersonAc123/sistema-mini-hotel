-- Estrutura do Banco de Dados: Hostel

CREATE DATABASE hostel;
USE hostel;

-- 1. Tabela de Usuários (Acesso ao Sistema)
CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    login VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso VARCHAR(20) DEFAULT 'recepcao'
);

-- 2. Tabela de Categorias de Quartos
CREATE TABLE categoria_quarto (
    codigo_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome_categoria VARCHAR(50) NOT NULL,
    valor_hora DECIMAL(10,2) NOT NULL
);

-- 3. Tabela de Quartos
CREATE TABLE quarto (
    numero_quarto INT PRIMARY KEY,
    codigo_categoria INT,
    status_quarto VARCHAR(20) DEFAULT 'Livre',
    ativo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (codigo_categoria) REFERENCES categoria_quarto(codigo_categoria)
);

-- 4. Tabela de Clientes
CREATE TABLE cliente (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(20) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    data_nascimento DATE NOT NULL
);

-- 5. Tabela de Locações
CREATE TABLE locacao (
    id_locacao INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    numero_quarto INT,
    data_hora_entrada DATETIME NOT NULL,
    data_hora_saida_estimada DATETIME NULL,
    data_hora_saida DATETIME NULL,
    tempo_estimado_horas INT NOT NULL,
    valor_total DECIMAL(10,2) NULL,
    status_caixa VARCHAR(15) DEFAULT 'Aberto',
    placa_veiculo VARCHAR(20) NULL,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
    FOREIGN KEY (numero_quarto) REFERENCES quarto(numero_quarto)
);

-- Caso o banco já exista, rodar:
-- ALTER TABLE locacao ADD COLUMN data_hora_saida_estimada DATETIME NULL AFTER data_hora_entrada;

-- =========================================================
-- dados exenciais, o meureuuuuuuuuuuu
-- =========================================================

INSERT INTO usuario (nome, login, senha, nivel_acesso) VALUES
('Anderson (Dono)', 'admin', '1234', 'admin'),
('João (Recepção)', 'recepcao', '1234', 'recepcao');

INSERT INTO categoria_quarto (codigo_categoria, nome_categoria, valor_hora) VALUES
(1, 'Beliche Compartilhado', 30.00),
(2, 'Quarto Privativo', 45.00),
(3, 'Suíte Premium', 80.00);

-- Atualizar nomes caso o banco já exista:
-- UPDATE categoria_quarto SET nome_categoria = 'Beliche Compartilhado' WHERE codigo_categoria = 1;
-- UPDATE categoria_quarto SET nome_categoria = 'Quarto Privativo'      WHERE codigo_categoria = 2;
-- UPDATE categoria_quarto SET nome_categoria = 'Suíte Premium'         WHERE codigo_categoria = 3;

INSERT INTO quarto (numero_quarto, codigo_categoria, status_quarto) VALUES
(101, 1, 'Livre'), (102, 1, 'Livre'), (103, 1, 'Livre'),
(201, 2, 'Livre'), (202, 2, 'Livre'), (203, 2, 'Livre'),
(301, 3, 'Livre'), (302, 3, 'Livre'), (303, 3, 'Livre');