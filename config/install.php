<?php
require_once __DIR__ . '/database.php';

// Cria o banco se não existir
$pdo->exec("CREATE DATABASE IF NOT EXISTS `kamishibai-senai` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `kamishibai-senai`");

// Definição das colunas para a tabela da sala (carteiras A1 a E8)
$colunas = [
    'id INT AUTO_INCREMENT PRIMARY KEY',
    'nome VARCHAR(100) NOT NULL',
    'data DATETIME NOT NULL',
    'momento ENUM("inicio","fim") NOT NULL',
    'porta ENUM("sim","nao") NOT NULL',
    'piso ENUM("sim","nao") NOT NULL'
];

// Gera as 40 carteiras: fileiras A, B, C, D, E e colunas 1 a 8
$fileiras = ['A', 'B', 'C', 'D', 'E'];
for ($f = 0; $f < count($fileiras); $f++) {
    for ($c = 1; $c <= 8; $c++) {
        $colunas[] = "carteira_{$fileiras[$f]}{$c} ENUM('sim','nao') NOT NULL";
    }
}

$outrosItens = ['janela', 'mesa_professor', 'cadeira_professor', 'ar_condicionado', 'televisao', 'quadro'];
foreach ($outrosItens as $item) {
    $colunas[] = "$item ENUM('sim','nao') NOT NULL";
}

$colunas[] = 'observacoes TEXT';

// Cria a tabela para a sala 104a
$sql = "CREATE TABLE IF NOT EXISTS `104a` (" . implode(', ', $colunas) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$pdo->exec($sql);

// Cria tabela de relatórios individuais (cada inspeção vira um registro)
$sql = "CREATE TABLE IF NOT EXISTS `relatorios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `inspecao_id` INT NOT NULL,
    `sala` VARCHAR(50) NOT NULL,
    `data` DATE NOT NULL,
    `periodo` ENUM('manha', 'tarde', 'noite') NOT NULL,
    `momento` ENUM('inicio', 'fim') NOT NULL,
    `observacoes` TEXT,
    `data_geracao` DATETIME NOT NULL,
    UNIQUE KEY `unique_inspecao` (`inspecao_id`, `sala`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$pdo->exec($sql);

echo "Banco de dados e tabelas criados com sucesso!";
?>