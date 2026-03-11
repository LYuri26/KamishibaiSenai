<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$periodos = [
    'manhã' => ['inicio' => '08:00', 'fim' => '11:30'],
    'tarde' => ['inicio' => '13:30', 'fim' => '17:30'],
    'noite' => ['inicio' => '18:30', 'fim' => '22:30']
];

$hoje = date('Y-m-d');
$agora = time();
$alertas = [];
$sala = '104a';

foreach ($periodos as $nomePeriodo => $horarios) {
    $inicioPeriodo = strtotime("$hoje {$horarios['inicio']}");
    $fimPeriodo = strtotime("$hoje {$horarios['fim']}");

    if ($fimPeriodo < $agora) {
        $inicioStr = date('Y-m-d H:i:s', $inicioPeriodo);
        $fimStr = date('Y-m-d H:i:s', $fimPeriodo);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$sala` WHERE data BETWEEN ? AND ?");
        $stmt->execute([$inicioStr, $fimStr]);
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $alertas[] = [
                'sala' => $sala,
                'periodo' => $nomePeriodo,
                'data' => $hoje,
                'mensagem' => "Sala $sala não foi inspecionada no período da $nomePeriodo (encerrado às {$horarios['fim']})."
            ];
        }
    }
}

echo json_encode($alertas);
?>