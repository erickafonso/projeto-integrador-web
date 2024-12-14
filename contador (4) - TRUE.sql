-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 14-Dez-2024 às 00:07
-- Versão do servidor: 10.4.20-MariaDB
-- versão do PHP: 8.0.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Criando o banco de dados
CREATE DATABASE IF NOT EXISTS contador;
USE contador;


-- --------------------------------------------------------
-- Estrutura da tabela `usuarios`
-- --------------------------------------------------------

CREATE TABLE `usuarios` (
  `idUsuario` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(18) NOT NULL,
  `login` VARCHAR(18) NOT NULL,
  `email` VARCHAR(30) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `ativo` ENUM('Sim','Não') DEFAULT 'Sim',
  `nivel` ENUM('admin','usuario') DEFAULT 'usuario',
  PRIMARY KEY (`idUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estrutura da tabela `categoria`
-- --------------------------------------------------------

-- Tabela Categoria
CREATE TABLE `categoria` (
  `idCategoria` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(20) NOT NULL,
  `idUsuario` INT(11) DEFAULT NULL,
  PRIMARY KEY (`idCategoria`),
  UNIQUE KEY `unique_categoria_usuario` (`idUsuario`, `nome`), -- Chave composta para garantir que o nome seja único por usuário
  KEY `fk_categoria_usuario` (`idUsuario`),
  CONSTRAINT `fk_categoria_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------
-- Estrutura da tabela `formapagamento`
-- --------------------------------------------------------

CREATE TABLE `formapagamento` (
  `idFormaPagamento` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(20) NOT NULL,
  `idUsuario` INT(11) DEFAULT NULL,
  PRIMARY KEY (`idFormaPagamento`),
  UNIQUE KEY `unique_usuario_nome` (`idUsuario`, `nome`), -- Restrições para garantir que o nome seja único por usuário
  KEY `fk_formapagamento_usuario` (`idUsuario`),
  CONSTRAINT `fk_formapagamento_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estrutura da tabela `conta`
-- --------------------------------------------------------

CREATE TABLE `conta` (
  `idConta` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(18) NOT NULL,
  `valor` DOUBLE NOT NULL,
  `descricao` VARCHAR(18) DEFAULT NULL,
  `dataPagamento` DATE DEFAULT NULL,
  `dataVencimento` DATE NOT NULL,
  `categoria` INT(11) DEFAULT NULL,
  `formaPagamento` INT(11) DEFAULT NULL,
  `idUsuario` INT(11) NOT NULL,
  PRIMARY KEY (`idConta`),
  KEY `categoria` (`categoria`),
  KEY `formaPagamento` (`formaPagamento`),
  KEY `fk_conta_usuario` (`idUsuario`),
  CONSTRAINT `fk_conta_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  CONSTRAINT `fk_conta_categoria` FOREIGN KEY (`categoria`) REFERENCES `categoria` (`idCategoria`) ON DELETE SET NULL,
  CONSTRAINT `fk_conta_formapagamento` FOREIGN KEY (`formaPagamento`) REFERENCES `formapagamento` (`idFormaPagamento`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estrutura da tabela `despesa`
-- --------------------------------------------------------

CREATE TABLE `despesa` (
  `idDespesa` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(18) NOT NULL,
  `valor` DOUBLE NOT NULL,
  `descricao` VARCHAR(18) DEFAULT NULL,
  `dataPagamento` DATE NOT NULL,
  `categoria` INT(11) DEFAULT NULL,
  `formaPagamento` INT(11) DEFAULT NULL,
  `idUsuario` INT(11) NOT NULL,
  PRIMARY KEY (`idDespesa`),
  KEY `fk_despesa_usuario` (`idUsuario`),
  CONSTRAINT `fk_despesa_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  CONSTRAINT `fk_despesa_categoria` FOREIGN KEY (`categoria`) REFERENCES `categoria` (`idCategoria`) ON DELETE SET NULL,
  CONSTRAINT `fk_despesa_formapagamento` FOREIGN KEY (`formaPagamento`) REFERENCES `formapagamento` (`idFormaPagamento`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Inserindo dados de exemplo na tabela `usuarios`
-- --------------------------------------------------------

INSERT INTO `usuarios` (`nome`, `login`, `email`, `senha`, `ativo`, `nivel`) VALUES
('Erick', 'erickafonso', 'erick@hotmail.com', '$2y$10$NKTVF8Tjfc..GGhejyCli.tUfw8z2DZAo5x4NZfx.HzLAgB6ZR0mG', 'Sim', 'usuario'),
('teste', 'teste', 'teste@gmail.com', '$2y$10$NKTVF8Tjfc..GGhejyCli.tUfw8z2DZAo5x4NZfx.HzLAgB6ZR0mG', 'Sim', 'usuario');

-- --------------------------------------------------------
-- Inserindo dados de exemplo na tabela `formapagamento`
-- --------------------------------------------------------

INSERT INTO `formapagamento` (`nome`, `idUsuario`) VALUES
('sem categoria', 1),
('cartao', 1),
(' robson3 ', 1),
('boleto', 1),
('teste1112', 1),
('erick1112', 2),
('usdt', 1);

-- --------------------------------------------------------
-- Inserindo dados de exemplo na tabela `categoria`
-- --------------------------------------------------------

INSERT INTO `categoria` (`nome`, `idUsuario`) VALUES
('sem categoria', 1),
('pedras2', 1),
('BOLHUFAS2', 1),
('testeWEB', 1),
('testeWEB2', 1),
('testeweb3', 1),
('testeweb4', 1),
('skibidi', 1);

-- --------------------------------------------------------
-- Inserindo dados de exemplo na tabela `conta`
-- --------------------------------------------------------

INSERT INTO `conta` (`nome`, `valor`, `descricao`, `dataPagamento`, `dataVencimento`, `categoria`, `formaPagamento`, `idUsuario`) VALUES
('FEDOR', 7897, 'ADADA', '7789-04-11', '7790-05-11', 2, 3, 1),
('ererer', 789, '1231', '0001-01-01', '0002-01-01', 1, 3, 1),
('paulo', 2131, 'wewtw', '1111-11-11', '2223-10-22', 3, 4, 1),
('teste0110', 1231, 'adadasd', '1111-11-11', '2222-02-22', 4, 5, 1);

-- --------------------------------------------------------
-- Inserindo dados de exemplo na tabela `despesa`
-- --------------------------------------------------------

INSERT INTO `despesa` (`nome`, `valor`, `descricao`, `dataPagamento`, `categoria`, `formaPagamento`, `idUsuario`) VALUES
('rogerio', 2313, 'afasf', '2020-01-01', 2, 3, 1),
('asdadaeqr', 23424, 'asafasf', '1994-04-11', 1, 1, 1),
('teste0110', 1231, 'adadasd', '1111-11-11', 4, 1, 1),
('teste0110B', 1231, 'adadasd', '1111-11-11', 2, 3, 1);

COMMIT;
