<?php
require_once __DIR__ . '/database.php';

// Cria o banco se não existir
$pdo->exec("CREATE DATABASE IF NOT EXISTS `kamishibai-senai` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `kamishibai-senai`");

// --- Tabela da sala 104a com perguntas consolidadas (formulação positiva) ---
$colunas = [
    'id INT AUTO_INCREMENT PRIMARY KEY',
    'nome VARCHAR(100) NOT NULL',
    'data DATETIME NOT NULL',
    'momento ENUM("inicio","fim") NOT NULL',
    'observacoes TEXT',
    // Carteiras
    'carteiras_organizadas ENUM("sim","nao") NOT NULL',
    'carteiras_quantidade ENUM("sim","nao") NOT NULL',
    'carteiras_danificadas ENUM("sim","nao") NOT NULL',
    // Televisão
    'tv_presente ENUM("sim","nao") NOT NULL',
    'tv_integra ENUM("sim","nao") NOT NULL',
    'tv_hdmi ENUM("sim","nao") NOT NULL',
    'tv_cabos_organizados ENUM("sim","nao") NOT NULL',
    'tv_conectada ENUM("sim","nao") NOT NULL',
    'tv_cabos_ok ENUM("sim","nao") NOT NULL',
    // Ar-condicionado
    'ar_presentes ENUM("sim","nao") NOT NULL',
    'ar_controle ENUM("sim","nao") NOT NULL',
    'ar_danos ENUM("sim","nao") NOT NULL',
    // Quadro
    'quadro_limpo ENUM("sim","nao") NOT NULL',
    'quadro_danos ENUM("sim","nao") NOT NULL',
    'quadro_fixo ENUM("sim","nao") NOT NULL',
    // Porta e Janelas
    'porta_funciona ENUM("sim","nao") NOT NULL',
    'janelas_intactas ENUM("sim","nao") NOT NULL',
    'janelas_vidros ENUM("sim","nao") NOT NULL',
    // Tomadas
    'tomadas_intactas ENUM("sim","nao") NOT NULL',
    'tomadas_fios ENUM("sim","nao") NOT NULL',
    'tomadas_adaptadores ENUM("sim","nao") NOT NULL',
    // Mesa e Cadeira do Instrutor
    'mesa_firme ENUM("sim","nao") NOT NULL',
    'mesa_gavetas ENUM("sim","nao") NOT NULL',
    'cadeira_integra ENUM("sim","nao") NOT NULL'
];

$sql104a = "CREATE TABLE IF NOT EXISTS `104a` (" . implode(', ', $colunas) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$pdo->exec($sql104a);

// --- Tabela do laboratório 103d ---
$colunas103d = [
    'id INT AUTO_INCREMENT PRIMARY KEY',
    'nome VARCHAR(100) NOT NULL',
    'data DATETIME NOT NULL',
    'momento ENUM("inicio","fim") NOT NULL',
    'observacoes TEXT',
    // Computadores e periféricos
    'computadores_ligam ENUM("sim","nao") NOT NULL',
    'mouses_funcionam ENUM("sim","nao") NOT NULL',
    'teclados_funcionam ENUM("sim","nao") NOT NULL',
    'monitores_funcionam ENUM("sim","nao") NOT NULL',
    'gabinetes_estado ENUM("sim","nao") NOT NULL',
    'cadeiras_baias ENUM("sim","nao") NOT NULL',
    // Ar condicionado
    'ar_condicionado_funciona ENUM("sim","nao") NOT NULL',
    // Quadro
    'quadro_limpo ENUM("sim","nao") NOT NULL',
    // Mesa e cadeira do instrutor
    'mesa_instrutor ENUM("sim","nao") NOT NULL',
    'cadeira_instrutor ENUM("sim","nao") NOT NULL',
    // Portão
    'portao_funciona ENUM("sim","nao") NOT NULL',
    // Janelas
    'janelas_intactas ENUM("sim","nao") NOT NULL',
    // Tomadas
    'tomadas_intactas ENUM("sim","nao") NOT NULL',
    'fios_expostos ENUM("sim","nao") NOT NULL'
];

$sql103d = "CREATE TABLE IF NOT EXISTS `103d` (" . implode(', ', $colunas103d) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$pdo->exec($sql103d);

// --- Tabela da oficina de soldagem 102c ---
// ================= 102C (CORRIGIDO) =================
$colunas102c = [
    'id INT AUTO_INCREMENT PRIMARY KEY',
    'nome VARCHAR(100) NOT NULL',
    'data DATETIME NOT NULL',
    'momento ENUM("inicio","fim") NOT NULL',
    'observacoes TEXT',

    'portao_funciona ENUM("sim","nao") NOT NULL',

    // AJUSTE CRÍTICO
    'instrutor_epi ENUM("sim","nao") NOT NULL',

    'box1_epi_completo ENUM("sim","nao") NOT NULL',
    'box1_ferramentas_ok ENUM("sim","nao") NOT NULL',
    'box1_organizacao ENUM("sim","nao") NOT NULL',

    'box2_epi_completo ENUM("sim","nao") NOT NULL',
    'box2_ferramentas_ok ENUM("sim","nao") NOT NULL',
    'box2_organizacao ENUM("sim","nao") NOT NULL',

    'box3_epi_completo ENUM("sim","nao") NOT NULL',
    'box3_ferramentas_ok ENUM("sim","nao") NOT NULL',
    'box3_organizacao ENUM("sim","nao") NOT NULL',

    'box4_epi_completo ENUM("sim","nao") NOT NULL',
    'box4_ferramentas_ok ENUM("sim","nao") NOT NULL',
    'box4_organizacao ENUM("sim","nao") NOT NULL',

    'box5_epi_completo ENUM("sim","nao") NOT NULL',
    'box5_ferramentas_ok ENUM("sim","nao") NOT NULL',
    'box5_organizacao ENUM("sim","nao") NOT NULL',

    'box6_epi_completo ENUM("sim","nao") NOT NULL',
    'box6_ferramentas_ok ENUM("sim","nao") NOT NULL',

    'box7_epi_completo ENUM("sim","nao") NOT NULL',
    'box7_ferramentas_ok ENUM("sim","nao") NOT NULL',
    'box7_organizacao ENUM("sim","nao") NOT NULL',

    'box8_epi_completo ENUM("sim","nao") NOT NULL',
    'box8_ferramentas_ok ENUM("sim","nao") NOT NULL',
    'box8_organizacao ENUM("sim","nao") NOT NULL',

    'box9_epi_completo ENUM("sim","nao") NOT NULL',
    'box9_ferramentas_ok ENUM("sim","nao") NOT NULL',
    'box9_organizacao ENUM("sim","nao") NOT NULL',

    'box10_epi_completo ENUM("sim","nao") NOT NULL',
    'box10_ferramentas_ok ENUM("sim","nao") NOT NULL',
    'box10_organizacao ENUM("sim","nao") NOT NULL',

    'area_limpa ENUM("sim","nao") NOT NULL',
    'area_organizacao ENUM("sim","nao") NOT NULL',
    'equipamentos_local ENUM("sim","nao") NOT NULL',
    'macarico_ok ENUM("sim","nao") NOT NULL',
    'estufa_ok ENUM("sim","nao") NOT NULL'
];

$pdo->exec("CREATE TABLE IF NOT EXISTS `102c` (" . implode(', ', $colunas102c) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// --- Tabela de relatórios ---
$sqlRelatorios = "CREATE TABLE IF NOT EXISTS `relatorios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `inspecao_id` INT NOT NULL,
    `sala` VARCHAR(50) NOT NULL,
    `data` DATE NOT NULL,
    `periodo` ENUM('manha','tarde','noite') NOT NULL,
    `momento` ENUM('inicio','fim') NOT NULL,
    `observacoes` TEXT,
    `data_geracao` DATETIME NOT NULL,
    `imagens` TEXT NULL,
    UNIQUE KEY `unique_inspecao` (`inspecao_id`, `sala`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$pdo->exec($sqlRelatorios);

// --- Tabela de usuários ---
$sqlUsuarios = "CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `sobrenome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `cargo` ENUM('instrutor','lider') NOT NULL,
    `senha` VARCHAR(255) NOT NULL,
    `data_criacao` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$pdo->exec($sqlUsuarios);

echo "Banco de dados e tabelas criados com sucesso!";
?>