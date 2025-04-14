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
  `nome` VARCHAR(30) NOT NULL,
  `valor` DOUBLE NOT NULL,
  `descricao` VARCHAR(200) DEFAULT NULL,
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
  `nome` VARCHAR(30) NOT NULL,
  `valor` DOUBLE NOT NULL,
  `descricao` VARCHAR(200) DEFAULT NULL,
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

INSERT INTO `usuarios` (`nome`,  `email`, `senha`, `ativo`, `nivel`) VALUES
('Erick',  'erick@hotmail.com', '$2y$10$NKTVF8Tjfc..GGhejyCli.tUfw8z2DZAo5x4NZfx.HzLAgB6ZR0mG', 'Sim', 'usuario'),
('teste',  'teste@gmail.com', '$2y$10$NKTVF8Tjfc..GGhejyCli.tUfw8z2DZAo5x4NZfx.HzLAgB6ZR0mG', 'Sim', 'usuario');

-- --------------------------------------------------------
-- Inserindo dados de exemplo 
-- --------------------------------------------------------

INSERT INTO `categoria` (`nome`, `idUsuario`) VALUES
('Alimentação', 1),
('Transporte', 1),
('Saúde', 1),
('Educação', 1),
('Lazer', 1),
('Moradia', 1),
('Serviços', 1),
('Vestuário', 1),
('Alimentação', 2),
('Transporte', 2),
('Saúde', 2),
('Educação', 2),
('Lazer', 2),
('Moradia', 2),
('Serviços', 2),
('Vestuário', 2);


INSERT INTO `formapagamento` (`nome`, `idUsuario`) VALUES
('Dinheiro', 1),
('Crédito', 1),
('Débito', 1),
('Pix', 1),
('Boleto', 1),
('PayPal', 1),
('Transferência', 1),
('Bitcoin', 1),
('Dinheiro', 2),
('Crédito', 2),
('Débito', 2),
('Pix', 2),
('Boleto', 2),
('PayPal', 2),
('Transferência', 2),
('Bitcoin', 2);

INSERT INTO `conta` (`nome`, `valor`, `descricao`, `dataPagamento`, `dataVencimento`, `categoria`, `formaPagamento`, `idUsuario`) VALUES
('Aluguel Janeiro', 1200.00, 'Pagamento mensal', '2025-01-10', '2025-02-10', 6, 2, 1),
('Supermercado Fevereiro', 350.75, 'Compras do mês', '2025-02-05', '2025-03-05', 1, 4, 1),
('Plano de saúde Março', 450.00, 'Mensalidade plano de saúde', '2025-03-01', '2025-03-31', 3, 3, 1),
('Material escolar Abril', 150.00, 'Compra material para escola', '2025-04-05', '2025-04-25', 4, 6, 1),
('Curso Online Maio', 200.00, 'Curso de programação', '2025-05-15', '2025-06-15', 4, 7, 1),
('Academia Junho', 100.00, 'Pagamento mensal da academia', '2025-06-01', '2025-06-30', 5, 1, 1),
('Internet Julho', 120.00, 'Cobrança mensal de internet', '2025-07-05', '2025-08-05', 7, 5, 1),
('Roupas Agosto', 400.00, 'Compra de roupas', '2025-08-10', '2025-09-10', 8, 2, 1),
('Aluguel Janeiro', 1200.00, 'Pagamento mensal', '2025-01-10', '2025-02-10', 6, 2, 2),
('Supermercado Fevereiro', 350.75, 'Compras do mês', '2025-02-05', '2025-03-05', 1, 4, 2),
('Plano de saúde Março', 450.00, 'Mensalidade plano de saúde', '2025-03-01', '2025-03-31', 3, 3, 2),
('Material escolar Abril', 150.00, 'Compra material para escola', '2025-04-05', '2025-04-25', 4, 6, 2),
('Curso Online Maio', 200.00, 'Curso de programação', '2025-05-15', '2025-06-15', 4, 7, 2),
('Academia Junho', 100.00, 'Pagamento mensal da academia', '2025-06-01', '2025-06-30', 5, 1, 2),
('Internet Julho', 120.00, 'Cobrança mensal de internet', '2025-07-05', '2025-08-05', 7, 5, 2),
('Roupas Agosto', 400.00, 'Compra de roupas', '2025-08-10', '2025-09-10', 8, 2, 2);

INSERT INTO `despesa` (`nome`, `valor`, `descricao`, `dataPagamento`, `categoria`, `formaPagamento`, `idUsuario`) VALUES
('Compra mercado Janeiro', 300.00, 'Alimentos', '2025-01-20', 1, 4, 1),
('Transporte Fevereiro', 150.00, 'Passagens de transporte público', '2025-02-15', 2, 3, 1),
('Exame médico Março', 180.00, 'Exame de rotina', '2025-03-10', 3, 2, 1),
('Curso presencial Abril', 350.00, 'Curso de design gráfico', '2025-04-20', 4, 1, 1),
('Cinema Maio', 45.00, 'Entrada no cinema', '2025-05-20', 5, 5, 1),
('Luz e gás Junho', 200.00, 'Contas de luz e gás', '2025-06-10', 6, 3, 1),
('Mensalidade escola Julho', 400.00, 'Escola do filho', '2025-07-01', 4, 4, 1),
('Roupa nova Agosto', 250.00, 'Compra de roupas', '2025-08-15', 8, 2, 1),
('Aluguel Setembro', 1200.00, 'Aluguel do apartamento', '2025-09-05', 6, 3, 1),
('Supermercado Outubro', 500.00, 'Compras para o mês', '2025-10-10', 1, 6, 1),
('Dentista Novembro', 250.00, 'Consulta odontológica', '2025-11-05', 3, 2, 1),
('Festa de aniversário Dezembro', 1500.00, 'Festa de aniversário de 18 anos', '2025-12-15', 5, 7, 1),
('Curso idiomas 2024', 500.00, 'Curso de inglês', '2024-06-10', 4, 1, 2),
('Ajuste de roupas 2023', 200.00, 'Ajuste de roupas antigas', '2023-05-15', 8, 4, 2),
('Plano de saúde 2022', 400.00, 'Plano de saúde anual', '2022-03-01', 3, 5, 2),
('Internet 2021', 100.00, 'Cobrança mensal de internet', '2021-02-01', 7, 6, 2),
('Assinatura Netflix 2020', 39.90, 'Assinatura mensal de streaming', '2020-08-10', 5, 3, 2);

-- Inserções para o ano de 2023
INSERT INTO `conta` (`nome`, `valor`, `descricao`, `dataPagamento`, `dataVencimento`, `categoria`, `formaPagamento`, `idUsuario`) VALUES
('Aluguel Janeiro 2023', 1200.00, 'Pagamento mensal', '2023-01-10', '2023-02-10', 6, 2, 1),
('Supermercado Fevereiro 2023', 400.00, 'Compras do mês', '2023-02-05', '2023-03-05', 1, 4, 1),
('Plano de saúde Março 2023', 450.00, 'Mensalidade plano de saúde', '2023-03-01', '2023-03-31', 3, 3, 1),
('Material escolar Abril 2023', 200.00, 'Compra material para escola', '2023-04-05', '2023-04-25', 4, 6, 1),
('Curso Online Maio 2023', 250.00, 'Curso de programação', '2023-05-15', '2023-06-15', 4, 7, 1);

-- Inserções para o ano de 2024
INSERT INTO `conta` (`nome`, `valor`, `descricao`, `dataPagamento`, `dataVencimento`, `categoria`, `formaPagamento`, `idUsuario`) VALUES
('Academia Janeiro 2024', 120.00, 'Pagamento mensal da academia', '2024-01-01', '2024-01-31', 5, 1, 1),
('Internet Fevereiro 2024', 130.00, 'Cobrança mensal de internet', '2024-02-05', '2024-03-05', 7, 5, 1),
('Roupas Março 2024', 450.00, 'Compra de roupas', '2024-03-10', '2024-04-10', 8, 2, 1),
('Aluguel Abril 2024', 1200.00, 'Pagamento mensal', '2024-04-10', '2024-05-10', 6, 2, 1),
('Supermercado Maio 2024', 380.00, 'Compras do mês', '2024-05-05', '2024-06-05', 1, 4, 1);

-- Inserções para o ano de 2022
INSERT INTO `conta` (`nome`, `valor`, `descricao`, `dataPagamento`, `dataVencimento`, `categoria`, `formaPagamento`, `idUsuario`) VALUES
('Plano de saúde Janeiro 2022', 400.00, 'Mensalidade plano de saúde', '2022-01-01', '2022-01-31', 3, 3, 2),
('Material escolar Fevereiro 2022', 150.00, 'Compra material para escola', '2022-02-01', '2022-02-28', 4, 6, 2),
('Curso Online Março 2022', 250.00, 'Curso de inglês', '2022-03-15', '2022-04-15', 4, 7, 2),
('Aluguel Abril 2022', 1200.00, 'Pagamento mensal', '2022-04-10', '2022-05-10', 6, 2, 2),
('Supermercado Maio 2022', 350.00, 'Compras do mês', '2022-05-05', '2022-06-05', 1, 4, 2);



-- Inserções para o ano de 2023
INSERT INTO `despesa` (`nome`, `valor`, `descricao`, `dataPagamento`, `categoria`, `formaPagamento`, `idUsuario`) VALUES
('Compra mercado Janeiro 2023', 320.00, 'Alimentos', '2023-01-20', 1, 4, 1),
('Transporte Fevereiro 2023', 170.00, 'Passagens de transporte público', '2023-02-15', 2, 3, 1),
('Exame médico Março 2023', 200.00, 'Exame de rotina', '2023-03-10', 3, 2, 1),
('Curso presencial Abril 2023', 400.00, 'Curso de design gráfico', '2023-04-20', 4, 1, 1),
('Cinema Maio 2023', 50.00, 'Entrada no cinema', '2023-05-20', 5, 5, 1);

-- Inserções para o ano de 2024
INSERT INTO `despesa` (`nome`, `valor`, `descricao`, `dataPagamento`, `categoria`, `formaPagamento`, `idUsuario`) VALUES
('Luz e gás Janeiro 2024', 250.00, 'Contas de luz e gás', '2024-01-10', 6, 3, 1),
('Mensalidade escola Fevereiro 2024', 420.00, 'Escola do filho', '2024-02-01', 4, 4, 1),
('Roupa nova Março 2024', 300.00, 'Compra de roupas', '2024-03-15', 8, 2, 1),
('Aluguel Abril 2024', 1200.00, 'Aluguel do apartamento', '2024-04-05', 6, 3, 1),
('Supermercado Maio 2024', 460.00, 'Compras para o mês', '2024-05-10', 1, 6, 1);

-- Inserções para o ano de 2022
INSERT INTO `despesa` (`nome`, `valor`, `descricao`, `dataPagamento`, `categoria`, `formaPagamento`, `idUsuario`) VALUES
('Plano de saúde Janeiro 2022', 420.00, 'Plano de saúde anual', '2022-01-15', 3, 5, 2),
('Ajuste de roupas Fevereiro 2022', 150.00, 'Ajuste de roupas antigas', '2022-02-10', 8, 4, 2),
('Transporte Março 2022', 180.00, 'Passagens de transporte público', '2022-03-05', 2, 3, 2),
('Curso design Abril 2022', 350.00, 'Curso de design gráfico', '2022-04-20', 4, 1, 2),
('Festa aniversário Maio 2022', 600.00, 'Festa de aniversário', '2022-05-25', 5, 6, 2);

INSERT INTO `despesa` (`nome`, `valor`, `descricao`, `dataPagamento`, `categoria`, `formaPagamento`, `idUsuario`) VALUES
('Padaria', 32.50, 'Café da manhã e pão', '2025-04-14', 1, 1, 1),
('Uber para trabalho', 45.00, 'Corrida para o escritório', '2025-04-14', 2, 4, 1),
('Farmácia', 76.30, 'Remédios para gripe', '2025-04-14', 3, 3, 1),
('Livro de filosofia', 120.00, 'Livro novo para estudos', '2025-04-15', 4, 2, 1),
('Netflix', 39.90, 'Assinatura mensal', '2025-04-15', 5, 6, 1),
('Conta de luz', 180.00, 'Energia elétrica', '2025-04-15', 6, 5, 1),
('Camisa nova', 90.00, 'Roupa social', '2025-04-15', 8, 2, 1),
('Mercado', 220.00, 'Compras semanais', '2025-04-16', 1, 4, 1),
('Ônibus ida e volta', 9.00, 'Transporte público', '2025-04-16', 2, 1, 1),
('Consulta médica', 200.00, 'Especialista', '2025-04-16', 3, 3, 1),
('Curso de frontend', 300.00, 'Módulo extra', '2025-04-17', 4, 7, 1),
('Cinema', 50.00, 'Sessão noturna', '2025-04-17', 5, 2, 1),
('Conta de gás', 140.00, 'Gás encanado', '2025-04-17', 6, 5, 1),
('Meias e cuecas', 75.00, 'Itens pessoais', '2025-04-17', 8, 4, 1),
('Almoço delivery', 38.00, 'Comida por aplicativo', '2025-04-18', 1, 1, 1),
('Passagem intermunicipal', 60.00, 'Viagem curta', '2025-04-18', 2, 3, 1),
('Exame oftalmológico', 280.00, 'Rotina anual', '2025-04-18', 3, 2, 1),
('Aula particular', 150.00, 'Refreforço de lógica', '2025-04-19', 4, 4, 1),
('Teatro', 80.00, 'Ingresso', '2025-04-19', 5, 1, 1),
('Reparo encanamento', 300.00, 'Serviço de emergência', '2025-04-20', 7, 5, 1);
