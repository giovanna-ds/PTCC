
CREATE DATABASE IF NOT EXISTS confeitariabistro
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE confeitariabistro;



CREATE TABLE usuarios (
    id                          INT AUTO_INCREMENT PRIMARY KEY,
    nome                        VARCHAR(150) NOT NULL,
    email                       VARCHAR(150) NOT NULL UNIQUE,
    senha_hash                  VARCHAR(255) NOT NULL,
    tipo_usuario                ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente',

    -- Verificação de e-mail
    email_verificado            TINYINT(1) NOT NULL DEFAULT 0,
    token_verificacao_email     VARCHAR(255) DEFAULT NULL,
    token_verificacao_expira    DATETIME DEFAULT NULL,

    ativo                       TINYINT(1) NOT NULL DEFAULT 1,
    criado_em                   DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em               DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_usuarios_email (email),
    INDEX idx_usuarios_tipo (tipo_usuario)
) ENGINE=InnoDB;


CREATE TABLE clientes (
    id              INT PRIMARY KEY,
    telefone        VARCHAR(20),
    endereco        VARCHAR(255),
    data_nascimento DATE,

    FOREIGN KEY (id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE administradores (
    id      INT PRIMARY KEY,
    cargo   VARCHAR(80) NOT NULL DEFAULT 'Administrador',

    FOREIGN KEY (id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;


DELIMITER $$

CREATE TRIGGER trg_clientes_checa_tipo
BEFORE INSERT ON clientes
FOR EACH ROW
BEGIN
    DECLARE tipo VARCHAR(10);
    SELECT tipo_usuario INTO tipo FROM usuarios WHERE id = NEW.id;
    IF tipo IS NULL OR tipo <> 'cliente' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Este usuario não está marcado como tipo_usuario = cliente';
    END IF;
END$$

CREATE TRIGGER trg_administradores_checa_tipo
BEFORE INSERT ON administradores
FOR EACH ROW
BEGIN
    DECLARE tipo VARCHAR(10);
    SELECT tipo_usuario INTO tipo FROM usuarios WHERE id = NEW.id;
    IF tipo IS NULL OR tipo <> 'admin' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Este usuario não está marcado como tipo_usuario = admin';
    END IF;
END$$

DELIMITER ;

CREATE TABLE tokens_recuperacao_senha (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT NOT NULL,
    token           VARCHAR(255) NOT NULL,
    expira_em       DATETIME NOT NULL,
    usado           TINYINT(1) NOT NULL DEFAULT 0,
    criado_em       DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE categorias (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    nome    VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE produtos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(150) NOT NULL,
    descricao       VARCHAR(500),
    preco           DECIMAL(10,2) NOT NULL,
    imagem          VARCHAR(255),
    categoria_id    INT,
    ativo           TINYINT(1) NOT NULL DEFAULT 1,
    criado_em       DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB;



CREATE TABLE favoritos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id      INT NOT NULL,
    produto_id      INT NOT NULL,
    criado_em       DATETIME DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unico_favorito (cliente_id, produto_id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE carrinho_itens (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id      INT NOT NULL,
    produto_id      INT NOT NULL,
    quantidade      INT NOT NULL DEFAULT 1,
    atualizado_em   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unico_item_carrinho (cliente_id, produto_id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pedidos (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id          INT NOT NULL,
    valor_total         DECIMAL(10,2) NOT NULL,
    metodo_pagamento    VARCHAR(30),
    status              VARCHAR(30) NOT NULL DEFAULT 'pendente',
    criado_em           DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pedido_itens (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id           INT NOT NULL,
    produto_id          INT NOT NULL,
    quantidade          INT NOT NULL,
    preco_unitario      DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB;


INSERT INTO categorias (nome) VALUES
    ('Bolo no Pote'),
    ('Bolos'),
    ('Chessecake'),
    ('Churros'),
    ('Cones'),
    ('Coxinhas'),
    ('Fondue'),
    ('Morango do Amor'),
    ('Pipoca Gourmet'),
    ('Pudim');


INSERT INTO produtos (nome, descricao, preco, imagem, categoria_id) VALUES
('Bolo no Pote de Ninho com Morango', 'Delicioso bolo no pote com camadas generosas de recheio de Leite Ninho artesanal e Morango.', 12.00, 'img/Bolo_no_pote_ninho_morango.jpg', (SELECT id FROM categorias WHERE nome='Bolo no Pote')),
('Bolo no Pote Red Velvet', 'Bolo no Pote com camadas de massa vermelha aveludada com um toque suave de cacau e um recheio cremoso e azedinho clássico de cream cheese.', 14.00, 'img/Bolo_no_pote_Red_Velvet.jpg', (SELECT id FROM categorias WHERE nome='Bolo no Pote')),
('Copo da Felicidade de Ferrero Rocher', 'Para os amantes de chocolate: muito recheio de Ferrero Rocher.', 12.00, 'img/Copo_felicidade_Ferrero_Rocher.jpg', (SELECT id FROM categorias WHERE nome='Bolo no Pote')),
('Copo Banoffe', 'Uma releitura prática e individual da tradicional torta inglesa, montada em camadas dentro de um copo. Une a crocância da massa, a doçura do doce de leite, o frescor da banana e a leveza do chantilly.', 12.00, 'img/Copo_Banoffe.jpg', (SELECT id FROM categorias WHERE nome='Bolo no Pote')),
('Copo da Felicidade de Limão', 'Combina camadas de mousse ou brigadeiro cremoso de limão, farofa de biscoito, e uma finalização de chantininho com raspas de limão. Sobremesa refrescante que equilibra o toque cítrico do limão com a doçura do creme.', 12.00, 'img/Copo_felicidade_limao.jpg', (SELECT id FROM categorias WHERE nome='Bolo no Pote')),
('Copo da Felicidade Sensação de Morango', 'Uma sobremesa irresistível que une a cremosidade do chocolate e o frescor do morango.', 14.00, 'img/Copo_felicidade_sensacao_morango.jpg', (SELECT id FROM categorias WHERE nome='Bolo no Pote')),
('Copo da Felicidade Kinder', 'Combina creme de Leite Ninho, brigadeiro cremoso ou Nutella, pedaços de brownie ou bolo de chocolate e pedaços crocantes do bombom Kinder Bueno no recheio e na decoração.', 14.00, 'img/Copo_da_felicidade_kinder.jpg', (SELECT id FROM categorias WHERE nome='Bolo no Pote')),
('Bolo Red Velvet', 'O clássico feito com todo o carinho e ingredientes selecionados.', 190.00, 'img/bolo_fatia_Red_Velvet.jpg', (SELECT id FROM categorias WHERE nome='Bolos')),
('Bolo de Ninho com Morango', 'Bolo artesanal recheado com creme de leite Ninho e morangos frescos.', 130.00, 'img/Fatia_gourmet_ninho_morango.jpg', (SELECT id FROM categorias WHERE nome='Bolos')),
('Bolo de Cenoura', 'Bolo de Cenoura com cobertura de Chocolate Gourmet.', 55.00, 'img/Bolo_cenoura_chocolate.jpg', (SELECT id FROM categorias WHERE nome='Bolos')),
('Brownie de Morango', 'Recheio cremoso e marcante feito com brigadeiro branco, ganache de chocolate ou doce de leite, combinado com pedaços de morangos frescos ou calda de morango artesanal.', 25.00, 'img/Brownier_Morango.jpg', (SELECT id FROM categorias WHERE nome='Bolos')),
('Cheesecake de frutas vermelhas (Pequeno)', 'Creme denso, aveludado e levemente ácido, feito tradicionalmente com cream cheese, açúcar, ovos e creme de leite, equilibrado com a calda doce e azedinha de frutas vermelhas por cima.', 100.00, 'img/cheesecake.jpg', (SELECT id FROM categorias WHERE nome='Chessecake')),
('Cheesecake de frutas vermelhas (Grande)', 'Creme denso, aveludado e levemente ácido, equilibrado perfeitamente com a calda doce e azedinha de frutas vermelhas por cima.', 200.00, 'img/cheesecake.jpg', (SELECT id FROM categorias WHERE nome='Chessecake')),
('Churros de Doce de Leite com Granulado', 'Massa firme, porém macia, com recheio caprichado de doce de leite caseiro e granulado de chocolate por cima.', 10.00, 'img/churros.jpeg', (SELECT id FROM categorias WHERE nome='Churros')),
('Cone de Morango Trufado', 'Combina uma casquinha de sorvete crocante blindada por dentro com chocolate, recheio cremoso de brigadeiro gourmet (ao leite) e pedaços frescos de morango, finalizado com um lacre de chocolate.', 14.00, 'img/cone_trufado_de_morango.jpg', (SELECT id FROM categorias WHERE nome='Cones')),
('Coxinha de Morango de Ferrero Rocher', 'Morango fresco no centro, envolvido por brigadeiro gourmet de chocolate com Nutella ou macarrão de avelã, com camada crocante de chocolate e pedaços de amendoim triturados por fora.', 18.00, 'img/Coxinha_de_morango.jpg', (SELECT id FROM categorias WHERE nome='Coxinhas')),
('Coxinha de Morango Ninho com Nutella', 'Base de brigadeiro cremoso de leite Ninho, envolvendo um morango fresco e suculento por dentro, empanado em leite em pó e finalizado com cobertura generosa de creme de avelã.', 18.00, 'img/Coxinha_de_morango_ninho_nutella.jpg', (SELECT id FROM categorias WHERE nome='Coxinhas')),
('Fondue na Marmita', 'A base doce leva ganaches cremosas de chocolate acompanhadas de frutas frescas.', 35.00, 'img/Fondue_na_marmita.jpg', (SELECT id FROM categorias WHERE nome='Fondue')),
('Morango do Amor Tradicional', 'Brigadeiro cremoso usado para envolver o morango fresco antes de receber uma casquinha crocante de caramelo vermelho, equilibrando o azedinho da fruta com a doçura do doce.', 18.00, 'img/morango_do_amor.jpg', (SELECT id FROM categorias WHERE nome='Morango do Amor')),
('Morango do Amor de Pistache', 'Brigadeiro cremoso verde feito com leite condensado, creme de leite, manteiga e pasta pura de pistache, que envolve o morango fresco antes de receber a camada de calda crocante de açúcar.', 18.00, 'img/morango_do_amor-pistache1.jpeg', (SELECT id FROM categorias WHERE nome='Morango do Amor')),
('Bombom de Morango', 'Bombom de brigadeiro branco cremoso (leite condensado, creme de leite, leite em pó e manteiga) que envolve completamente um morango fresco e inteiro, criando contraste entre o doce marcante e a acidez da fruta.', 60.00, 'img/bombom_de_morango.jpg', (SELECT id FROM categorias WHERE nome='Morango do Amor')),
('Pipoca Gourmet de Chocolate', 'Pipoca crocante envolvida por uma deliciosa cobertura de chocolate, com pedacinhos e textura irresistíveis, perfeita para quem ama um sabor intenso e docinho.', 15.00, 'img/pipoca.jpg', (SELECT id FROM categorias WHERE nome='Pipoca Gourmet')),
('Pudim de Leite Condensado (Pequeno)', 'Creme liso, denso e aveludado, feito com uma batida de leite condensado, ovos e leite integral, cozido em banho-maria e finalizado com uma camada de calda de caramelo dourado.', 12.00, 'img/pudim.jpeg', (SELECT id FROM categorias WHERE nome='Pudim')),
('Pudim de Leite Condensado (Grande)', 'Creme liso, denso e aveludado, feito com leite condensado, ovos e leite integral, finalizado com calda de caramelo dourado.', 55.00, 'img/pudim.jpeg', (SELECT id FROM categorias WHERE nome='Pudim'));

-- =====================================================================
-- 6) DADOS INICIAIS — USUÁRIOS (hierarquia + verificação de e-mail)
-- =====================================================================
-- ATENÇÃO: as senhas abaixo já estão em hash bcrypt (compatível com
-- password_hash()/password_verify() do PHP), só para fins de teste:
--   admin@confeitariabistro.com   -> senha: admin123
--   ana.souza@example.com         -> senha: cliente123   (e-mail JÁ verificado)
--   joao.pereira@example.com      -> senha: cliente123   (e-mail JÁ verificado)
--   maria.lima@example.com        -> senha: cliente123   (e-mail AINDA NÃO verificado, com token pendente)
-- Troque essas senhas antes de usar em produção!
-- ---------------------------------------------------------------------

-- Administrador (tipo_usuario = 'admin')
INSERT INTO usuarios (nome, email, senha_hash, tipo_usuario, email_verificado)
VALUES ('Administrador Sabino Bistrô', 'admin@confeitariabistro.com',
        '$2b$10$rLCUGauV2XZCfySnOn9XweqKXwbBz7uy6/2QbkzJOQfDHPNDTouQy',
        'admin', 1);

INSERT INTO administradores (id, cargo)
VALUES ((SELECT id FROM usuarios WHERE email = 'admin@confeitariabistro.com'), 'Gerente Geral');

-- Cliente 1 — e-mail já verificado
INSERT INTO usuarios (nome, email, senha_hash, tipo_usuario, email_verificado)
VALUES ('Ana Souza', 'ana.souza@example.com',
        '$2b$10$eaghQpI9Z9LNkJDWJkMdIu1zzSb6FQfIQvbf2LJUdXHLfzUv.4tva',
        'cliente', 1);

INSERT INTO clientes (id, telefone, endereco, data_nascimento)
VALUES ((SELECT id FROM usuarios WHERE email = 'ana.souza@example.com'),
        '(11) 91234-5678', 'Rua das Flores, 120 - São Paulo/SP', '1995-04-12');

-- Cliente 2 — e-mail já verificado
INSERT INTO usuarios (nome, email, senha_hash, tipo_usuario, email_verificado)
VALUES ('João Pereira', 'joao.pereira@example.com',
        '$2b$10$eaghQpI9Z9LNkJDWJkMdIu1zzSb6FQfIQvbf2LJUdXHLfzUv.4tva',
        'cliente', 1);

INSERT INTO clientes (id, telefone, endereco, data_nascimento)
VALUES ((SELECT id FROM usuarios WHERE email = 'joao.pereira@example.com'),
        '(11) 99876-5432', 'Av. Paulista, 900 - São Paulo/SP', '1990-09-23');

-- Cliente 3 — e-mail AINDA NÃO verificado (fluxo de verificação pendente)
INSERT INTO usuarios (nome, email, senha_hash, tipo_usuario, email_verificado,
                       token_verificacao_email, token_verificacao_expira)
VALUES ('Maria Lima', 'maria.lima@example.com',
        '$2b$10$eaghQpI9Z9LNkJDWJkMdIu1zzSb6FQfIQvbf2LJUdXHLfzUv.4tva',
        'cliente', 0,
        SHA2(CONCAT('maria.lima@example.com', NOW(), RAND()), 256),
        DATE_ADD(NOW(), INTERVAL 24 HOUR));

INSERT INTO clientes (id, telefone, endereco, data_nascimento)
VALUES ((SELECT id FROM usuarios WHERE email = 'maria.lima@example.com'),
        '(21) 98888-1111', 'Rua do Comércio, 45 - Rio de Janeiro/RJ', '1998-01-30');



INSERT INTO favoritos (cliente_id, produto_id)
VALUES
    ((SELECT id FROM usuarios WHERE email='ana.souza@example.com'), (SELECT id FROM produtos WHERE nome='Bolo Red Velvet')),
    ((SELECT id FROM usuarios WHERE email='ana.souza@example.com'), (SELECT id FROM produtos WHERE nome='Morango do Amor Tradicional')),
    ((SELECT id FROM usuarios WHERE email='joao.pereira@example.com'), (SELECT id FROM produtos WHERE nome='Fondue na Marmita'));

INSERT INTO carrinho_itens (cliente_id, produto_id, quantidade)
VALUES
    ((SELECT id FROM usuarios WHERE email='joao.pereira@example.com'), (SELECT id FROM produtos WHERE nome='Churros de Doce de Leite com Granulado'), 2),
    ((SELECT id FROM usuarios WHERE email='joao.pereira@example.com'), (SELECT id FROM produtos WHERE nome='Pudim de Leite Condensado (Pequeno)'), 1);

INSERT INTO pedidos (cliente_id, valor_total, metodo_pagamento, status)
VALUES ((SELECT id FROM usuarios WHERE email='ana.souza@example.com'), 32.00, 'pix', 'concluido');

INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario)
VALUES
    (LAST_INSERT_ID(), (SELECT id FROM produtos WHERE nome='Morango do Amor Tradicional'), 1, 18.00),
    (LAST_INSERT_ID(), (SELECT id FROM produtos WHERE nome='Bolo no Pote de Ninho com Morango'), 1, 14.00);


CREATE VIEW vw_usuarios_completo AS
SELECT
    u.id, u.nome, u.email, u.tipo_usuario, u.email_verificado, u.ativo,
    c.telefone, c.endereco, c.data_nascimento,
    a.cargo
FROM usuarios u
LEFT JOIN clientes c ON c.id = u.id AND u.tipo_usuario = 'cliente'
LEFT JOIN administradores a ON a.id = u.id AND u.tipo_usuario = 'admin';

