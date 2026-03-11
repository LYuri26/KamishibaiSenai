-- Criação do banco (com backticks)
CREATE DATABASE IF NOT EXISTS `kamishibai-senai` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `kamishibai-senai`;

-- Tabela da sala 104a (com carteiras A1 a E8)
CREATE TABLE IF NOT EXISTS `104a` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `data` DATETIME NOT NULL,
    `momento` ENUM('inicio', 'fim') NOT NULL,
    `porta` ENUM('sim', 'nao') NOT NULL,
    `piso` ENUM('sim', 'nao') NOT NULL,
    `carteira_A1` ENUM('sim', 'nao') NOT NULL,
    `carteira_A2` ENUM('sim', 'nao') NOT NULL,
    `carteira_A3` ENUM('sim', 'nao') NOT NULL,
    `carteira_A4` ENUM('sim', 'nao') NOT NULL,
    `carteira_A5` ENUM('sim', 'nao') NOT NULL,
    `carteira_A6` ENUM('sim', 'nao') NOT NULL,
    `carteira_A7` ENUM('sim', 'nao') NOT NULL,
    `carteira_A8` ENUM('sim', 'nao') NOT NULL,
    `carteira_B1` ENUM('sim', 'nao') NOT NULL,
    `carteira_B2` ENUM('sim', 'nao') NOT NULL,
    `carteira_B3` ENUM('sim', 'nao') NOT NULL,
    `carteira_B4` ENUM('sim', 'nao') NOT NULL,
    `carteira_B5` ENUM('sim', 'nao') NOT NULL,
    `carteira_B6` ENUM('sim', 'nao') NOT NULL,
    `carteira_B7` ENUM('sim', 'nao') NOT NULL,
    `carteira_B8` ENUM('sim', 'nao') NOT NULL,
    `carteira_C1` ENUM('sim', 'nao') NOT NULL,
    `carteira_C2` ENUM('sim', 'nao') NOT NULL,
    `carteira_C3` ENUM('sim', 'nao') NOT NULL,
    `carteira_C4` ENUM('sim', 'nao') NOT NULL,
    `carteira_C5` ENUM('sim', 'nao') NOT NULL,
    `carteira_C6` ENUM('sim', 'nao') NOT NULL,
    `carteira_C7` ENUM('sim', 'nao') NOT NULL,
    `carteira_C8` ENUM('sim', 'nao') NOT NULL,
    `carteira_D1` ENUM('sim', 'nao') NOT NULL,
    `carteira_D2` ENUM('sim', 'nao') NOT NULL,
    `carteira_D3` ENUM('sim', 'nao') NOT NULL,
    `carteira_D4` ENUM('sim', 'nao') NOT NULL,
    `carteira_D5` ENUM('sim', 'nao') NOT NULL,
    `carteira_D6` ENUM('sim', 'nao') NOT NULL,
    `carteira_D7` ENUM('sim', 'nao') NOT NULL,
    `carteira_D8` ENUM('sim', 'nao') NOT NULL,
    `carteira_E1` ENUM('sim', 'nao') NOT NULL,
    `carteira_E2` ENUM('sim', 'nao') NOT NULL,
    `carteira_E3` ENUM('sim', 'nao') NOT NULL,
    `carteira_E4` ENUM('sim', 'nao') NOT NULL,
    `carteira_E5` ENUM('sim', 'nao') NOT NULL,
    `carteira_E6` ENUM('sim', 'nao') NOT NULL,
    `carteira_E7` ENUM('sim', 'nao') NOT NULL,
    `carteira_E8` ENUM('sim', 'nao') NOT NULL,
    `janela` ENUM('sim', 'nao') NOT NULL,
    `mesa_professor` ENUM('sim', 'nao') NOT NULL,
    `cadeira_professor` ENUM('sim', 'nao') NOT NULL,
    `ar_condicionado` ENUM('sim', 'nao') NOT NULL,
    `televisao` ENUM('sim', 'nao') NOT NULL,
    `quadro` ENUM('sim', 'nao') NOT NULL,
    `observacoes` TEXT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Tabela de relatórios consolidados
CREATE TABLE IF NOT EXISTS `relatorios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `inspecao_id` INT NOT NULL,
    -- ID da inspeção na tabela da sala
    `sala` VARCHAR(50) NOT NULL,
    `data` DATE NOT NULL,
    `periodo` ENUM('manha', 'tarde', 'noite') NOT NULL,
    `momento` ENUM('inicio', 'fim') NOT NULL,
    -- início ou fim
    `observacoes` TEXT,
    `data_geracao` DATETIME NOT NULL,
    UNIQUE KEY `unique_inspecao` (`inspecao_id`, `sala`) -- garante que cada inspeção apareça uma única vez
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;