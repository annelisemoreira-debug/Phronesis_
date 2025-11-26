CREATE DATABASE phronesis;
USE phronesis;

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(255) NOT NULL
);

CREATE TABLE cadastro (
    id_usuarioc INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE materia (
    id_materia INT AUTO_INCREMENT PRIMARY KEY,
    nome_materia VARCHAR(255) NOT NULL,
    id_usuarioc INT NOT NULL,
    FOREIGN KEY (id_usuarioc) REFERENCES cadastro(id_usuarioc)
);

CREATE TABLE cronograma (
    id_cronograma INT AUTO_INCREMENT PRIMARY KEY,
    tempo DATETIME NOT NULL,
    id_usuarioc INT NOT NULL,
    id_materia INT,
    FOREIGN KEY (id_usuarioc) REFERENCES cadastro(id_usuarioc),
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia)
);

CREATE TABLE conteudo (
    id_conteudo INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT,
    id_cronograma INT,
    id_usuarioc INT NOT NULL,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia),
    FOREIGN KEY (id_cronograma) REFERENCES cronograma(id_cronograma),
    FOREIGN KEY (id_usuarioc) REFERENCES cadastro(id_usuarioc)
);

CREATE TABLE livro (
    id_livro INT AUTO_INCREMENT PRIMARY KEY,
    nome_livro VARCHAR(255) NOT NULL,
    id_materia INT,
    id_usuarioc INT NOT NULL,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia),
    FOREIGN KEY (id_usuarioc) REFERENCES cadastro(id_usuarioc)
);
