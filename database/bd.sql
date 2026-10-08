--Database antigo
CREATE DATABASE phronesis;
USE phronesis;

CREATE TABLE cadastro (
    id_cadastro INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(255) NOT NULL
);

CREATE TABLE usuario (
    id_usuarioc INT AUTO_INCREMENT PRIMARY KEY,
    id_cadastro INT NOT NULL,
    FOREIGN KEY (id_cadastro) REFERENCES cadastro(id_cadastro)
);

CREATE TABLE materia (
    id_materia INT AUTO_INCREMENT PRIMARY KEY,
    nome_materia VARCHAR(255) NOT NULL,
    id_usuarioc INT NOT NULL,
    FOREIGN KEY (id_usuarioc) REFERENCES usuario(id_usuarioc)
);

CREATE TABLE cronograma (
    id_cronograma INT AUTO_INCREMENT PRIMARY KEY,
    tempo DATETIME NOT NULL,
    id_usuarioc INT NOT NULL,
    id_materia INT,
    FOREIGN KEY (id_usuarioc) REFERENCES usuario(id_usuarioc),
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia)
);

CREATE TABLE conteudo (
    id_conteudo INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT,
    id_cronograma INT,
    id_usuarioc INT NOT NULL,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia),
    FOREIGN KEY (id_cronograma) REFERENCES cronograma(id_cronograma),
    FOREIGN KEY (id_usuarioc) REFERENCES usuario(id_usuarioc)
);

CREATE TABLE livro (
    id_livro INT AUTO_INCREMENT PRIMARY KEY,
    nome_livro VARCHAR(255) NOT NULL,
    id_materia INT,
    id_usuarioc INT NOT NULL,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia),
    FOREIGN KEY (id_usuarioc) REFERENCES usuario(id_usuarioc)
);


-- Database novo 13/03
CREATE DATABASE phronesis;
USE phronesis;

drop database phronesis;

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20)
);

    CREATE TABLE materia (
        id_materia INT AUTO_INCREMENT PRIMARY KEY,
        nome_materia VARCHAR(255) NOT NULL
    );

CREATE TABLE usuario_materia (
	id_usuario_materia int auto_increment primary key,
    id_usuario INT NOT NULL,
    id_materia INT NOT NULL,

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_materia)
        REFERENCES materia(id_materia)
        ON DELETE CASCADE
);

CREATE TABLE conteudo (
    id_conteudo INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    id_materia INT NOT NULL,

    FOREIGN KEY (id_materia)
        REFERENCES materia(id_materia)
        ON DELETE CASCADE
);

CREATE TABLE cronograma (
    id_cronograma INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE cronograma_item (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_cronograma INT NOT NULL,
    id_materia INT NOT NULL,
    data_estudo DATE NOT NULL,
    tempo_estudo INT,
    status_estudo varchar (99) not null,

    FOREIGN KEY (id_cronograma)
        REFERENCES cronograma(id_cronograma)
        ON DELETE CASCADE,

    FOREIGN KEY (id_materia)
        REFERENCES materia(id_materia)
);

CREATE TABLE livro (
    id_livro INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    autor VARCHAR(255),
    vestibular VARCHAR(255),
    ano_vestibular INT
);

CREATE TABLE usuario_livro (
	id_usuario_livro int auto_increment primary key,
    id_usuario INT NOT NULL,
    id_livro INT NOT NULL,

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_livro)
        REFERENCES livro(id_livro)
        ON DELETE CASCADE
);

ALTER TABLE usuario ADD COLUMN google_token TEXT NULL;






-- Database novo 16/04

CREATE DATABASE phronesis;
USE phronesis;

drop database phronesis;

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20)
);

-- 1. TABELA FORTE: Materia
-- Centraliza as matérias que o usuário pode escolher.
CREATE TABLE materia (
    id_materia INT AUTO_INCREMENT PRIMARY KEY,
    nome_materia VARCHAR(100) NOT NULL UNIQUE,
    cor_hex VARCHAR(7) DEFAULT '#4285F4'
);

-- 2. CABEÇALHO: Cronograma
-- Liga o plano ao usuário e à agenda do Google.
CREATE TABLE cronograma (
    id_cronograma INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo_cronograma VARCHAR(255) NOT NULL,
    google_calendar_id VARCHAR(255), 
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Mantemos o id_usuario para garantir que o plano pertença a alguém no banco
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- 3. ITENS: Cronograma_item
-- Onde as matérias (FK) são relacionadas aos horários e eventos do Google.
CREATE TABLE cronograma_item (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_cronograma INT NOT NULL,
    id_materia INT NOT NULL, -- Relacionamento com a Tabela Forte
    google_event_id VARCHAR(255),
    data_estudo DATETIME NOT NULL,
    data_fim DATETIME NOT NULL,

    -- Se o cronograma pai for apagado, os itens somem.
    FOREIGN KEY (id_cronograma) REFERENCES cronograma(id_cronograma) ON DELETE CASCADE,
    
    -- Impede apagar uma matéria que está sendo usada em um cronograma.
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia) ON DELETE RESTRICT
);



-- Data base novo 07/05


-- 1. Cria o banco de dados (caso não exista)
CREATE DATABASE IF NOT EXISTS phronesis;
USE phronesis;

-- 2. Cria a tabela de usuários já com a coluna de foto
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    foto VARCHAR(255) DEFAULT 'assets/img/default-user.webp'
);

-- 3. TABELA FORTE: Materia
CREATE TABLE IF NOT EXISTS materia (
    id_materia INT AUTO_INCREMENT PRIMARY KEY,
    nome_materia VARCHAR(100) NOT NULL UNIQUE,
    cor_hex VARCHAR(7) DEFAULT '#4285F4'
);

-- 4. CABEÇALHO: Cronograma
CREATE TABLE IF NOT EXISTS cronograma (
    id_cronograma INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo_cronograma VARCHAR(255) NOT NULL,
    google_calendar_id VARCHAR(255), 
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- 5. ITENS: Cronograma_item
CREATE TABLE IF NOT EXISTS cronograma_item (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_cronograma INT NOT NULL,
    id_materia INT NOT NULL,
    google_event_id VARCHAR(255),
    data_estudo DATETIME NOT NULL,
    data_fim DATETIME NOT NULL,
    status_estudo VARCHAR(50) DEFAULT 'pendente',
    FOREIGN KEY (id_cronograma) REFERENCES cronograma(id_cronograma) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia) ON DELETE RESTRICT
);




-- Data base novo 08/05

-- 1. Cria o banco de dados
CREATE DATABASE IF NOT EXISTS phronesis;
USE phronesis;

-- 2. Tabela de usuários COM a coluna de tipo (admin/comum)
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    foto VARCHAR(255) DEFAULT 'assets/img/default-user.webp',
    tipo ENUM('comum', 'admin') DEFAULT 'comum' -- ADICIONADO AQUI
);

-- 3. TABELA FORTE: Materia
CREATE TABLE IF NOT EXISTS materia (
    id_materia INT AUTO_INCREMENT PRIMARY KEY,
    nome_materia VARCHAR(100) NOT NULL UNIQUE,
    cor_hex VARCHAR(7) DEFAULT '#4285F4'
);

-- 4. CABEÇALHO: Cronograma
CREATE TABLE IF NOT EXISTS cronograma (
    id_cronograma INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo_cronograma VARCHAR(255) NOT NULL,
    google_calendar_id VARCHAR(255), 
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- 5. ITENS: Cronograma_item
CREATE TABLE IF NOT EXISTS cronograma_item (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_cronograma INT NOT NULL,
    id_materia INT NOT NULL,
    google_event_id VARCHAR(255),
    data_estudo DATETIME NOT NULL,
    data_fim DATETIME NOT NULL,
    status_estudo VARCHAR(50) DEFAULT 'pendente',
    FOREIGN KEY (id_cronograma) REFERENCES cronograma(id_cronograma) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia) ON DELETE RESTRICT
);


UPDATE usuario SET tipo = 'admin' WHERE email = 'joao@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'manuella@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'annelise@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'dukye@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'miguelmig@phronesis.com';






-- Data base novo 14/05

-- 1. Cria o banco de dados
CREATE DATABASE IF NOT EXISTS phronesis;
USE phronesis;

-- 2. Tabela de usuários COM a coluna de tipo (admin/comum)
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    foto VARCHAR(255) DEFAULT 'assets/img/default-user.webp',
    tipo ENUM('comum', 'admin') DEFAULT 'comum' -- ADICIONADO AQUI
);

-- 3. TABELA FORTE: Materia
CREATE TABLE IF NOT EXISTS materia (
    id_materia INT AUTO_INCREMENT PRIMARY KEY,
    nome_materia VARCHAR(100) NOT NULL UNIQUE,
    cor_hex VARCHAR(7) DEFAULT '#4285F4'
);

-- 4. CABEÇALHO: Cronograma
CREATE TABLE IF NOT EXISTS cronograma (
    id_cronograma INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo_cronograma VARCHAR(255) NOT NULL,
    google_calendar_id VARCHAR(255), 
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- 5. ITENS: Cronograma_item
CREATE TABLE IF NOT EXISTS cronograma_item (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_cronograma INT NOT NULL,
    id_materia INT NOT NULL,
    google_event_id VARCHAR(255),
    data_estudo DATETIME NOT NULL,
    data_fim DATETIME NOT NULL,
    status_estudo VARCHAR(50) DEFAULT 'pendente',
    FOREIGN KEY (id_cronograma) REFERENCES cronograma(id_cronograma) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia) ON DELETE RESTRICT
);

CREATE TABLE favoritos (
    id_favorito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    video_id VARCHAR(50) NOT NULL,
    titulo_video VARCHAR(255),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);


UPDATE usuario SET tipo = 'admin' WHERE email = 'joao@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'manuella@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'annelise@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'dukye@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'narutomig@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'raoni@phronesis.com';



-- Data base novo 12/06

-- 1. Cria o banco de dados
CREATE DATABASE IF NOT EXISTS phronesis;
USE phronesis;

-- 2. Tabela de usuários COM a coluna de tipo e google_token integrados
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NULL, -- CORRIGIDO: Sintaxe ajustada aqui
    foto VARCHAR(255) DEFAULT 'assets/img/default-user.webp',
    tipo ENUM('comum', 'admin') DEFAULT 'comum',
    google_token TEXT NULL -- ADICIONADO AQUI: Armazenamento permanente do Google
);

-- 3. TABELA FORTE: Materia
CREATE TABLE IF NOT EXISTS materia (
    id_materia INT AUTO_INCREMENT PRIMARY KEY,
    nome_materia VARCHAR(100) NOT NULL UNIQUE,
    cor_hex VARCHAR(7) DEFAULT '#4285F4'
);

-- 4. CABEÇALHO: Cronograma
CREATE TABLE IF NOT EXISTS cronograma (
    id_cronograma INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo_cronograma VARCHAR(255) NOT NULL,
    google_calendar_id VARCHAR(255), 
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- 5. ITENS: Cronograma_item
CREATE TABLE IF NOT EXISTS cronograma_item (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_cronograma INT NOT NULL,
    id_materia INT NOT NULL,
    google_event_id VARCHAR(255),
    data_estudo DATETIME NOT NULL,
    data_fim DATETIME NOT NULL,
    status_estudo VARCHAR(50) DEFAULT 'pendente',
    FOREIGN KEY (id_cronograma) REFERENCES cronograma(id_cronograma) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia) ON DELETE RESTRICT
);

-- 6. Favoritos (Vídeos)
CREATE TABLE IF NOT EXISTS favoritos (
    id_favorito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    video_id VARCHAR(50) NOT NULL,
    titulo_video VARCHAR(255),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- 7. Vistos (Histórico de Vídeos)
CREATE TABLE IF NOT EXISTS vistos (
    id_visto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    video_id VARCHAR(50) NOT NULL,
    data_visto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- 8. Tópicos das Matérias (Gerenciado por Administradores)
CREATE TABLE IF NOT EXISTS materia_topico (
    id_topico INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT NOT NULL,
    nome_topico VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia) ON DELETE CASCADE
);

-- 9. Definição dos Administradores do Sistema
UPDATE usuario SET tipo = 'admin' WHERE email = 'joao@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'manuella@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'annelise@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'dukye@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'narutomig@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'raoni@phronesis.com';



-- Data base novo 19/06


CREATE DATABASE IF NOT EXISTS phronesis;
USE phronesis;

-- 2. Tabela de usuários
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NULL,
    foto VARCHAR(255) DEFAULT 'assets/img/default-user.webp',
    tipo ENUM('user', 'admin') DEFAULT 'user',
    google_token TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. TABELA FORTE: Materia
CREATE TABLE IF NOT EXISTS materia (
    id_materia INT AUTO_INCREMENT PRIMARY KEY,
    nome_materia VARCHAR(100) NOT NULL UNIQUE,
    cor_hex VARCHAR(7) DEFAULT '#4285F4'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Tópicos das Matérias (Gerenciado por Administradores)
CREATE TABLE IF NOT EXISTS materia_topico (
    id_topico INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT NOT NULL,
    nome_topico VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. CABEÇALHO: Cronograma
CREATE TABLE IF NOT EXISTS cronograma (
    id_cronograma INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo_cronograma VARCHAR(255) NOT NULL,
    google_calendar_id VARCHAR(255), 
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. ITENS: Cronograma_item
CREATE TABLE IF NOT EXISTS cronograma_item (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_cronograma INT NOT NULL,
    id_materia INT NOT NULL,
    google_event_id VARCHAR(255),
    data_estudo DATETIME NOT NULL,
    data_fim DATETIME NOT NULL,
    status_estudo VARCHAR(50) DEFAULT 'pendente',
    FOREIGN KEY (id_cronograma) REFERENCES cronograma(id_cronograma) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Favoritos (Vídeos)
CREATE TABLE IF NOT EXISTS favoritos (
    id_favorito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    video_id VARCHAR(50) NOT NULL,
    titulo_video VARCHAR(255),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. Vistos (Histórico de Vídeos)
CREATE TABLE IF NOT EXISTS vistos (
    id_visto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    video_id VARCHAR(50) NOT NULL,
    data_visto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 9. Progresso dos Tópicos dos Usuários
CREATE TABLE IF NOT EXISTS usuario_topico_progresso (
    id_progresso INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_cronograma INT NOT NULL,
    id_materia INT NOT NULL,
    id_topico INT NOT NULL,
    concluido TINYINT(1) DEFAULT 1,
    data_conclusao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_progresso (id_usuario, id_cronograma, id_topico),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_cronograma) REFERENCES cronograma(id_cronograma) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia) ON DELETE CASCADE,
    FOREIGN KEY (id_topico) REFERENCES materia_topico(id_topico) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS cronograma_topico_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cronograma INT NOT NULL,
    id_materia INT NOT NULL,
    id_topico INT NOT NULL,
    status INT DEFAULT 0,
    data_alteracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_progresso (id_cronograma, id_materia, id_topico)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================================================================
-- SOLUÇÃO DE COMPATIBILIDADE:
-- Cria o espelho 'cod_topicos' apontando para a tabela real 'materia_topico'
-- =========================================================================
CREATE OR REPLACE VIEW cod_topicos AS 
SELECT id_topico, id_materia, nome_topico 
FROM materia_topico;



UPDATE usuario SET tipo = 'admin' WHERE email = 'joao@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'manuella@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'annelise@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'dukye@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'narutomig@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'raoni@phronesis.com';

select * from materia_topico
select * from usuario
select * from favoritos

drop database phronesis














-- alteração 08/10
CREATE DATABASE IF NOT EXISTS phronesis;
USE phronesis;

-- 2. Tabela de usuários
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NULL,
    foto VARCHAR(255) DEFAULT 'assets/img/default-user.webp',
    tipo ENUM('user', 'admin') DEFAULT 'user',
    google_token TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. TABELA FORTE: Materia
CREATE TABLE IF NOT EXISTS materia (
    id_materia INT AUTO_INCREMENT PRIMARY KEY,
    nome_materia VARCHAR(100) NOT NULL UNIQUE,
    cor_hex VARCHAR(7) DEFAULT '#4285F4'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Tópicos das Matérias (Gerenciado por Administradores)
CREATE TABLE IF NOT EXISTS materia_topico (
    id_topico INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT NOT NULL,
    nome_topico VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. CABEÇALHO: Cronograma
CREATE TABLE IF NOT EXISTS cronograma (
    id_cronograma INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo_cronograma VARCHAR(255) NOT NULL,
    google_calendar_id VARCHAR(255), 
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. ITENS: Cronograma_item
CREATE TABLE IF NOT EXISTS cronograma_item (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_cronograma INT NOT NULL,
    id_materia INT NOT NULL,
    google_event_id VARCHAR(255),
    data_estudo DATETIME NOT NULL,
    data_fim DATETIME NOT NULL,
    status_estudo VARCHAR(50) DEFAULT 'pendente',
    FOREIGN KEY (id_cronograma) REFERENCES cronograma(id_cronograma) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Favoritos (Vídeos)
CREATE TABLE IF NOT EXISTS favoritos (
    id_favorito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    video_id VARCHAR(50) NOT NULL,
    titulo_video VARCHAR(255),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. Vistos (Histórico de Vídeos)
CREATE TABLE IF NOT EXISTS vistos (
    id_visto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    video_id VARCHAR(50) NOT NULL,
    data_visto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

USE phronesis;

-- ============================================================
-- LIVROS (cadastrados pelo ADM em admin_materias.php)
-- Cole este bloco no seu bd.sql, depois da tabela "vistos".
-- ============================================================

CREATE TABLE IF NOT EXISTS livro (
    id_livro INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    autor VARCHAR(255) NOT NULL,
    resumo TEXT NOT NULL,
    capa VARCHAR(255) NULL,                 -- caminho relativo, ex: assets/img/livros/abc123.webp
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Um livro pode cair em vários vestibulares (relação N:N simples)
CREATE TABLE IF NOT EXISTS livro_vestibular (
    id_livro INT NOT NULL,
    vestibular ENUM('fuvest','unicamp','vunesp','enem') NOT NULL,
    PRIMARY KEY (id_livro, vestibular),
    FOREIGN KEY (id_livro) REFERENCES livro(id_livro) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Livros favoritos de cada usuário
CREATE TABLE IF NOT EXISTS livro_favorito (
    id_usuario INT NOT NULL,
    id_livro INT NOT NULL,
    data_favorito TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario, id_livro),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_livro) REFERENCES livro(id_livro) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Progresso de leitura. "Não iniciada" = não existe linha para o par usuário/livro.
CREATE TABLE IF NOT EXISTS livro_progresso (
    id_usuario INT NOT NULL,
    id_livro INT NOT NULL,
    status ENUM('em_andamento','concluida') NOT NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario, id_livro),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_livro) REFERENCES livro(id_livro) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- 9. Progresso dos Tópicos dos Usuários
CREATE TABLE IF NOT EXISTS usuario_topico_progresso (
    id_progresso INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_cronograma INT NOT NULL,
    id_materia INT NOT NULL,
    id_topico INT NOT NULL,
    concluido TINYINT(1) DEFAULT 1,
    data_conclusao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_progresso (id_usuario, id_cronograma, id_topico),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_cronograma) REFERENCES cronograma(id_cronograma) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia) ON DELETE CASCADE,
    FOREIGN KEY (id_topico) REFERENCES materia_topico(id_topico) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS cronograma_topico_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cronograma INT NOT NULL,
    id_materia INT NOT NULL,
    id_topico INT NOT NULL,
    status INT DEFAULT 0,
    data_alteracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_progresso (id_cronograma, id_materia, id_topico)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================================================================
-- SOLUÇÃO DE COMPATIBILIDADE:
-- Cria o espelho 'cod_topicos' apontando para a tabela real 'materia_topico'
-- =========================================================================
CREATE OR REPLACE VIEW cod_topicos AS 
SELECT id_topico, id_materia, nome_topico 
FROM materia_topico;

INSERT INTO livro (titulo, autor, resumo, capa) VALUES
-- FUVEST
('Opúsculo Humanitário', 'Nísia Floresta',
 'Ensaio pioneiro que defende a emancipação feminina e a importância da educação das mulheres para o progresso social.',
 'assets/img/livros/opusculo_humanitario.jpg'),

('Nebulosas', 'Narcisa Amália',
 'Coletânea de poemas românticos que abordam o sofrimento humano, a natureza e o papel social da mulher no século XIX.',
 'assets/img/livros/nebulosas.jpg'),

('Memórias de Martha', 'Julia Lopes de Almeida',
 'Romance de tom memorialista que retrata as vivências de uma jovem na corte carioca e os dilemas urbanos da época.',
 'assets/img/livros/memorias_de_martha.jpg'),

('Caminho de Pedras', 'Rachel de Queiroz',
 'Obra que aborda o engajamento político, a militância de esquerda e os dilemas afetivos e sociais de seus personagens no Ceará.',
 'assets/img/livros/caminho_de_pedras.jpg'),

('A Paixão Segundo G. H.', 'Clarice Lispector',
 'Monólogo interior profundo desencadeado pelo encontro de uma mulher com uma barata, gerando uma crise existencial e filosófica.',
 'assets/img/livros/a_paixao_segundo_gh.jpg'),

('Geografia', 'Sophia de Mello Breyner Andresen',
 'Livro de poesia que explora a relação do ser humano com o espaço, a natureza, a justiça e a busca pela lucidez.',
 'assets/img/livros/geografia.jpg'),

('Balada de Amor ao Vento', 'Paulina Chiziane',
 'Narrativa moçambicana sobre as dores e os encantos de um amor trágico, marcada pelas pressões de uma sociedade patriarcal.',
 'assets/img/livros/balada_de_amor_ao_vento.jpg'),

('Canção para Ninar Menino Grande', 'Conceição Evaristo',
 'Romance que investiga as contradições da masculinidade negra e as teias afetivas de mulheres marcadas por vivências intensas.',
 'assets/img/livros/cancao_para_ninar_menino_grande.jpg'),

('A Visão das Plantas', 'Djaimilia Pereira de Almeida',
 'A história de um ex-traficante de escravizados que retorna a Portugal e passa a velhice dedicando-se de forma obsessiva ao seu jardim.',
 'assets/img/livros/a_visao_das_plantas.jpg'),
 
 
 
-- UNICAMP 

('A Vida Não é Útil', 'Ailton Krenak', 
'Ensaios que criticam as bases da civilização ocidental, o consumo desenfreado e propõem uma profunda reconexão com a Terra.', 
'assets/img/livros/a_vida_nao_e_util.jpg'),

('Memórias Póstumas de Brás Cubas', 'Machado de Assis', 
'Romance realista narrado por um defunto autor que satiriza com ironia a hipocrisia e os privilégios da elite brasileira do século XIX.', 
'assets/img/livros/memorias_postumas_de_bras_cubas.jpg'),

('No seu Pescoço', 'Chimamanda Ngozi Adichie', 
'Coletânea de contos que abordam a imigração, os choques culturais, o racismo e as complexas dinâmicas sociais entre Nigéria e Estados Unidos.', 
'assets/img/livros/no_seu_pescoco.jpg'),

('Olhos d''Água', 'Conceição Evaristo', 
'Contos marcados pelo conceito de escrevivência que retratam a violência, a ancestralidade e a resistência da população negra urbana.', 
'assets/img/livros/olhos_dagua.jpg'),

('Os Funerais da Mamãe Grande', 'Gabriel García Márquez', 
'Contos de realismo fantástico que giram em torno do universo mítico de Macondo e trazem críticas ao poder oligárquico na América Latina.', 
'assets/img/livros/os_funerais_da_mamae_grande.jpg'),

('Prosas Seguidas de Odes Mínimas', 'José Paulo Paes', 
'Reunião de textos poéticos e em prosa marcados pela concisão formal, pelo lirismo crítico, humor e reflexões do cotidiano.', 
'assets/img/livros/prosas_seguidas_de_odes_minimas.jpg'),

('Vida e Morte de M. J. Gonzaga de Sá', 'Lima Barreto', 
'Romance satírico que critica a burocracia estatal, o preconceito racial e as transformações urbanas do Rio de Janeiro no início do século XX.', 
'assets/img/livros/vida_e_morte_de_m_j_gonzaga_de_sa.jpg'),

('Morangos Mofados', 'Caio Fernando Abreu', 
'Seleção de contos que capturam a angústia existencial, a contracultura e o desejo de afeto de uma juventude sufocada durante a ditadura militar.', 
'assets/img/livros/morangos_mofados.jpg'),

('Canções Escolhidas', 'Paulo César Pinheiro', 
'Coletânea de composições poéticas e musicais que exploram a identidade nacional, as tradições populares e as feridas sociais do Brasil.', 
'assets/img/livros/cancoes_escolhidas.jpg'),



-- ENEM

('Dom Casmurro', 'Machado de Assis',
'Análise da elite carioca do século XIX marcada pela dubiedade da traição, ironia refinada e a desconstrução psicológica das relações humanas sob a ótica de um narrador não confiável.',
'assets/img/livros/dom_casmurro.jpg'),

('Memórias Póstumas de Brás Cubas', 'Machado de Assis',
'Romance realista narrado por um defunto autor que satiriza com ironia a hipocrisia, o parasitismo e os privilégios da elite aristocrática brasileira do século XIX.',
'assets/img/livros/memorias_postumas_de_bras_cubas.jpg'),

('Vidas Secas', 'Graciliano Ramos',
'Romance regionalista modernista que denuncia o sofrimento da miséria, a opressão social, a desumanização e a migração forçada de uma família de retirantes no sertão nordestino.',
'assets/img/livros/vidas_secas.jpg'),

('A Hora da Estrela', 'Clarice Lispector',
'Narrativa existencialista que acompanha a invisibilidade social, a alienação urbana e a pobreza da jovem nordestina Macabéa sob o olhar de um escritor reflexivo.',
'assets/img/livros/a_hora_da_estrela.jpg'),

('Quarto de Despejo: Diário de uma Favelada', 'Carolina Maria de Jesus',
'Relato autobiográfico em formato de diário que expõe de forma crua as mazelas da fome, da extrema pobreza, do racismo e da exclusão socioeconômica na favela de São Paulo.',
'assets/img/livros/quarto_de_despejo.jpg'),

('O Cortiço', 'Aluísio Azevedo',
'Romance naturalista que retrata a habitação coletiva carioca como um organismno vivo onde o meio social, o determinismo biológico e a exploração degradam os indivíduos.',
'assets/img/livros/o_cortico.jpg'),

('Capitães da Areia', 'Jorge Amado',
'Narrativa engajada que denuncia o abandono e a criminalização de um grupo de meninos de rua em Salvador, destacando a desigualdade e a busca por afeto.',
'assets/img/livros/capitaes_da_areia.jpg'),

('Morte e Vida Severina', 'João Cabral de Melo Neto',
'Auto de natal em poesia que acompanha a jornada fúnebre do retirante Severino, enfrentando o ciclo contínuo de fome, seca e opressão até o reencontro com a esperança.',
'assets/img/livros/morte_e_vida_severina.jpg');


 
 
-- =========================================================================

INSERT INTO livro_vestibular (id_livro, vestibular)
SELECT id_livro, v.vest
FROM livro
JOIN (
    -- FUVEST
    SELECT 'Opúsculo Humanitário' AS titulo, 'fuvest' AS vest UNION ALL
    SELECT 'Nebulosas', 'fuvest' UNION ALL
    SELECT 'Memórias de Martha', 'fuvest' UNION ALL
    SELECT 'Caminho de Pedras', 'fuvest' UNION ALL
    SELECT 'A Paixão Segundo G. H.', 'fuvest' UNION ALL
    SELECT 'Geografia', 'fuvest' UNION ALL
    SELECT 'Balada de Amor ao Vento', 'fuvest' UNION ALL
    SELECT 'Canção para Ninar Menino Grande', 'fuvest' UNION ALL
    SELECT 'A Visão das Plantas', 'fuvest' UNION ALL

    -- UNICAMP
    SELECT 'A Vida Não é Útil', 'unicamp' UNION ALL
    SELECT 'Memórias Póstumas de Brás Cubas', 'unicamp' UNION ALL
    SELECT 'No seu Pescoço', 'unicamp' UNION ALL
    SELECT 'Olhos d''Água', 'unicamp' UNION ALL
    SELECT 'Os Funerais da Mamãe Grande', 'unicamp' UNION ALL
    SELECT 'Prosas Seguidas de Odes Mínimas', 'unicamp' UNION ALL
    SELECT 'Vida e Morte de M. J. Gonzaga de Sá', 'unicamp' UNION ALL
    SELECT 'Morangos Mofados', 'unicamp' UNION ALL
    SELECT 'Canções Escolhidas', 'unicamp' UNION ALL

    -- ENEM
    SELECT 'Dom Casmurro', 'enem' UNION ALL
    SELECT 'Memórias Póstumas de Brás Cubas', 'enem' UNION ALL
    SELECT 'Vidas Secas', 'enem' UNION ALL
    SELECT 'A Hora da Estrela', 'enem' UNION ALL
    SELECT 'Quarto de Despejo: Diário de uma Favelada', 'enem' UNION ALL
    SELECT 'O Cortiço', 'enem' UNION ALL
    SELECT 'Capitães da Areia', 'enem' UNION ALL
    SELECT 'Morte e Vida Severina', 'enem' UNION ALL
    SELECT 'Olhos d''Água', 'enem' UNION ALL
    SELECT 'Canção para Ninar Menino Grande', 'enem'
) v ON v.titulo = livro.titulo;


UPDATE usuario SET tipo = 'admin' WHERE email = 'joao@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'manuella@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'annelise@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'dukye@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'narutomig@phronesis.com';
UPDATE usuario SET tipo = 'admin' WHERE email = 'raoni@phronesis.com';

select * from materia_topico
select * from usuario
select * from favoritos

drop database phronesis