<?php
require_once __DIR__ . '/database.php';

// Cria o banco se não existir
$pdo->exec("CREATE DATABASE IF NOT EXISTS `kamishibai-senai` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `kamishibai-senai`");

// Definição das colunas
$colunas = [
    'id INT AUTO_INCREMENT PRIMARY KEY',
    'nome VARCHAR(100) NOT NULL',
    'data DATETIME NOT NULL',
    'porta ENUM("sim","nao") NOT NULL',
    'piso ENUM("sim","nao") NOT NULL'
];

for ($i = 1; $i <= 40; $i++) {
    $colunas[] = "carteira_$i ENUM('sim','nao') NOT NULL";
}

$outrosItens = ['janela', 'mesa_professor', 'cadeira_professor', 'ar_condicionado', 'televisao', 'quadro'];
foreach ($outrosItens as $item) {
    $colunas[] = "$item ENUM('sim','nao') NOT NULL";
}

$colunas[] = 'observacoes TEXT';

$sql = "CREATE TABLE IF NOT EXISTS `104a` (" . implode(', ', $colunas) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$pdo->exec($sql);

echo "Banco de dados e tabela 104a criados com sucesso!";
?>