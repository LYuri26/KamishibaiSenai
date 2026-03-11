<?php
date_default_timezone_set('America/Sao_Paulo');
require_once __DIR__ . '/database.php';

// Períodos em minutos
$periodos = [
    'manha' => ['inicio' => 8 * 60, 'fim' => 11 * 60 + 30],
    'tarde' => ['inicio' => 13 * 60 + 30, 'fim' => 17 * 60 + 30],
    'noite' => ['inicio' => 18 * 60 + 30, 'fim' => 22 * 60 + 30]
];

function getPeriodo($minutos, $periodos)
{
    if ($minutos < $periodos['manha']['inicio'])
        return 'manha';
    if ($minutos > $periodos['noite']['fim'])
        return 'noite';
    foreach ($periodos as $nome => $p) {
        if ($minutos >= $p['inicio'] && $minutos <= $p['fim'])
            return $nome;
    }
    return 'manha';
}

// Listar todas as tabelas que são salas (ignorando 'relatorios')
$stmt = $pdo->query("SHOW TABLES");
$tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
$salas = array_filter($tabelas, function ($tabela) {
    return $tabela !== 'relatorios' && preg_match('/^\d+[a-z]?$/i', $tabela);
});

foreach ($salas as $sala) {
    $tabelaSala = "`$sala`";

    // Busca todas as inspeções da sala
    $stmt = $pdo->query("SELECT * FROM $tabelaSala");
    $inspecoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($inspecoes as $inspecao) {
        $timestamp = strtotime($inspecao['data']);
        $hora = (int) date('H', $timestamp);
        $min = (int) date('i', $timestamp);
        $minutos = $hora * 60 + $min;
        $periodo = getPeriodo($minutos, $periodos);

        // Insere ou atualiza o relatório individual
        $sql = "INSERT INTO relatorios 
                (inspecao_id, sala, data, periodo, momento, observacoes, data_geracao)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    observacoes = VALUES(observacoes), 
                    data_geracao = NOW()";
        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute([
            $inspecao['id'],
            $sala,
            date('Y-m-d', $timestamp),
            $periodo,
            $inspecao['momento'],
            $inspecao['observacoes']
        ]);
    }
}

echo "Relatórios gerados com sucesso para todas as salas.\n";
?>