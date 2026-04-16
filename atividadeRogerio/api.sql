create database PhronesisLista;
use PhronesisLista;



create table estados (
id_estado int auto_increment primary key,
nome varchar(255) not null,
sigla varchar (5) not null
);



CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    fk_id_estado INT, 
    FOREIGN KEY (fk_id_estado) REFERENCES Estados(id_estado)
);



insert into estados (nome, sigla) values
("Acre", "AC"),
("Alagoas", "AL"),
("Amapá", "AP"),
("Amazonas", "AM"),
("Bahia", "BA"),
("Ceará", "CE"),
("Distrito Federal", "DF"),
("Espírito Santo", "ES"),
("Goiás", "GO"),
("Maranhão", "MA"),
("Mato Grosso", "MT"),
("Mato Grosso do Sul", "MS"),
("Minas Gerais", "MG"),
("Pará", "PA"),
("Paraíba", "PB"),
("Paraná", "PR"),
("Pernambuco", "PE"),
("Piauí", "PI"),
("Rio de Janeiro", "RJ"),
("Rio Grande do Norte", "RN"),
("Rio Grande do Sul", "RS"),
("Rondônia", "RO"),
("Roraima", "RR"),
("Santa Catarina", "SC"),
("São Paulo", "SP"),
("Sergipe", "SE"),
("Tocantins","TO")



select*from estados;