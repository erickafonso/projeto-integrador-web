-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 25-Out-2024 às 01:27
-- Versão do servidor: 10.4.20-MariaDB
-- versão do PHP: 8.0.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `contador`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `categoria`
--

CREATE TABLE `categoria` (
  `idCategoria` int(11) NOT NULL,
  `nome` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `categoria`
--

INSERT INTO `categoria` (`idCategoria`, `nome`) VALUES
(5, ''),
(2, ' bicicleta '),
(3, ' pedras2 '),
(4, 'BOLHUFAS'),
(13, 'ceee'),
(14, 'ergerger'),
(15, 'sabao'),
(1, 'sem categoria'),
(12, 'skibidi'),
(6, 'testeWEB'),
(7, 'testeWEB2'),
(8, 'testeweb3'),
(11, 'testeweb4');

-- --------------------------------------------------------

--
-- Estrutura da tabela `conta`
--

CREATE TABLE `conta` (
  `idConta` int(11) NOT NULL,
  `nome` varchar(18) NOT NULL,
  `valor` double NOT NULL,
  `descricao` varchar(18) DEFAULT NULL,
  `dataPagamento` date DEFAULT NULL,
  `dataVencimento` date NOT NULL,
  `categoria` int(11) NOT NULL,
  `formaPagamento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `conta`
--

INSERT INTO `conta` (`idConta`, `nome`, `valor`, `descricao`, `dataPagamento`, `dataVencimento`, `categoria`, `formaPagamento`) VALUES
(1, 'FEDOR', 7897, 'ADADA', '7789-04-11', '7790-05-11', 2, 3),
(2, ' ererer ', 789, '1231', '0001-01-01', '0002-01-01', 1, 3),
(4, 'paulo', 2131, 'wewtw', '1111-11-11', '2223-10-22', 3, 4),
(6, 'adadasd', 12313, 'panela', '1111-11-11', '1111-11-11', 11, 4),
(7, 'adadasd', 12313, 'panela', '1111-11-11', '1111-11-11', 11, 4),
(8, 'teste0110', 1231, 'adadasd', '1111-11-11', '2222-02-22', 4, 5);

-- --------------------------------------------------------

--
-- Estrutura da tabela `despesa`
--

CREATE TABLE `despesa` (
  `idDespesa` int(11) NOT NULL,
  `nome` varchar(18) NOT NULL,
  `valor` double NOT NULL,
  `descricao` varchar(18) DEFAULT NULL,
  `dataPagamento` date NOT NULL,
  `categoria` int(11) NOT NULL,
  `formaPagamento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `despesa`
--

INSERT INTO `despesa` (`idDespesa`, `nome`, `valor`, `descricao`, `dataPagamento`, `categoria`, `formaPagamento`) VALUES
(4, ' rogerio ', 2313, ' afasf', '2020-01-01', 2, 3),
(5, 'asdadaeqr', 23424, 'asafasf', '1994-04-11', 1, 1),
(6, 'teste0110', 1231, 'adadasd', '1111-11-11', 4, 1),
(7, 'teste0110B', 1231, 'adadasd', '1111-11-11', 2, 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `formapagamento`
--

CREATE TABLE `formapagamento` (
  `idFormaPagamento` int(11) NOT NULL,
  `nome` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `formapagamento`
--

INSERT INTO `formapagamento` (`idFormaPagamento`, `nome`) VALUES
(4, ' robson3 '),
(5, 'boleto'),
(3, 'cartao'),
(1, 'sem categoria');

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `idUsuario` int(11) NOT NULL,
  `nome` varchar(18) NOT NULL,
  `login` varchar(18) NOT NULL,
  `email` varchar(30) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `ativo` enum('Sim','Não') DEFAULT 'Sim',
  `nivel` enum('admin','usuario') DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `nome`, `login`, `email`, `senha`, `ativo`, `nivel`) VALUES
(1, 'Erick', 'erickafonso', 'erick@hotmail.com', '$2y$10$NKTVF8Tjfc..GGhejyCli.tUfw8z2DZAo5x4NZfx.HzLAgB6ZR0mG', 'Sim', 'usuario');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`idCategoria`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices para tabela `conta`
--
ALTER TABLE `conta`
  ADD PRIMARY KEY (`idConta`),
  ADD KEY `categoria` (`categoria`),
  ADD KEY `formaPagamento` (`formaPagamento`);

--
-- Índices para tabela `despesa`
--
ALTER TABLE `despesa`
  ADD PRIMARY KEY (`idDespesa`);

--
-- Índices para tabela `formapagamento`
--
ALTER TABLE `formapagamento`
  ADD PRIMARY KEY (`idFormaPagamento`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idUsuario`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categoria`
--
ALTER TABLE `categoria`
  MODIFY `idCategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `conta`
--
ALTER TABLE `conta`
  MODIFY `idConta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `despesa`
--
ALTER TABLE `despesa`
  MODIFY `idDespesa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `formapagamento`
--
ALTER TABLE `formapagamento`
  MODIFY `idFormaPagamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `conta`
--
ALTER TABLE `conta`
  ADD CONSTRAINT `categoria` FOREIGN KEY (`categoria`) REFERENCES `categoria` (`idCategoria`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `formaPagamento` FOREIGN KEY (`formaPagamento`) REFERENCES `formapagamento` (`idFormaPagamento`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
