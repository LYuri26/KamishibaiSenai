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
    UNIQUE KEY `unique_inspecao` (`inspecao_id`, `sala`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$pdo->exec($sqlRelatorios);

// --- Tabela de usuários ---
$sqlUsuarios = "CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `sobrenome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `cargo` ENUM('instrutor','gerencia') NOT NULL,
    `senha` VARCHAR(255) NOT NULL,
    `data_criacao` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$pdo->exec($sqlUsuarios);

// --- Inserir usuário admin padrão (se não houver nenhum) ---
$stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
if ($stmt->fetchColumn() == 0) {
    $senhaHash = password_hash('senai123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, sobrenome, email, cargo, senha, data_criacao) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute(['Admin', 'Sistema', 'admin@fiemg.com.br', 'gerencia', $senhaHash]);
    echo "Usuário admin criado (email: admin@fiemg.com.br / senha: senai123)<br>";
}

echo "Banco de dados e tabelas criados com sucesso!";
?>