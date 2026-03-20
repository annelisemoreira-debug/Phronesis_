create database projetofinal
use projetofinal

select * from cadastro;

create table cadastro (
id_cadastro int AUTO_INCREMENT PRIMARY KEY,
nome varchar(255),
email varchar(255),
senha varchar(255), 
telefone varchar(255), 
observacao varchar(45)
);