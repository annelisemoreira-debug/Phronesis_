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