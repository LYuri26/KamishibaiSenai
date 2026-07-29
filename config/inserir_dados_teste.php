<?php
// =====================================================
// INSERIR DADOS COMPLETOS 2022-2026 (KAMISHIBAI) - CORRIGIDO
// =====================================================

date_default_timezone_set('America/Sao_Paulo');

// Tenta carregar o banco e a criptografia do sistema
if (file_exists(__DIR__ . '/../../config/database.php')) {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../config/encryption.php';
} elseif (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/config/encryption.php';
} else {
    require_once __DIR__ . '/database.php';
    if (file_exists(__DIR__ . '/encryption.php')) {
        require_once __DIR__ . '/encryption.php';
    }
}

// Configurando limites de tempo e memória para não travar a execução
set_time_limit(300);
ini_set('memory_limit', '512M');

header('Content-Type: text/html; charset=utf-8');

// =====================================================
// 1. LIMPAR TABELAS (RESET COMPLETO DOS DADOS)
// =====================================================

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    $pdo->exec("TRUNCATE TABLE responsaveis");
    $pdo->exec("TRUNCATE TABLE usuarios");
    $pdo->exec("TRUNCATE TABLE relatorios");
    $pdo->exec("TRUNCATE TABLE `104a`");
    $pdo->exec("TRUNCATE TABLE `103d`");
    $pdo->exec("TRUNCATE TABLE `102c`");
    $pdo->exec("TRUNCATE TABLE `102d`");
    $pdo->exec("TRUNCATE TABLE `101d`");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "✅ Tabelas limpas com sucesso.<br>";
} catch (PDOException $e) {
    echo "❌ Erro ao limpar tabelas: " . $e->getMessage() . "<br>";
    exit;
}

// =====================================================
// 2. GERAR USUÁRIOS E RESPONSÁVEIS (PADRÃO FIEMG E CRIPTOGRAFADO)
// =====================================================

$usuariosSistema = [
    // Líderes
    ['Lenon', 'Yuri', 'lider'],
    ['José', 'Ferreira', 'lider'],
    ['Patrícia', 'Mendes', 'lider'],
    ['Gisele', 'Nunes', 'lider'],
    ['Alexandre', 'Barbosa', 'lider'],

    // Instrutores
    ['Carlos', 'Silva', 'instrutor'],
    ['Mariana', 'Souza', 'instrutor'],
    ['João', 'Pereira', 'instrutor'],
    ['Ana', 'Lima', 'instrutor'],
    ['Roberto', 'Alves', 'instrutor'],
    ['Fernanda', 'Costa', 'instrutor'],
    ['Lucas', 'Mendes', 'instrutor'],
    ['Juliana', 'Rocha', 'instrutor'],
    ['Paulo', 'Henrique', 'instrutor'],
    ['Cristina', 'Oliveira', 'instrutor'],
    ['Ricardo', 'Santos', 'instrutor'],
    ['Bruno', 'Carvalho', 'instrutor'],
    ['Tatiane', 'Martins', 'instrutor'],
    ['Gustavo', 'Almeida', 'instrutor'],
    ['Camila', 'Ferreira', 'instrutor']
];

$instrutores = [];
$lideres = [];

// Senha padrão válida (atende a regra de ter letras e números)
$senhaPadraoRaw = 'senai123';
$senhaPadraoHash = password_hash($senhaPadraoRaw, PASSWORD_DEFAULT);

$stmtUsuario = $pdo->prepare("
    INSERT INTO usuarios (nome, sobrenome, email_hash, email_encrypted, cargo, senha, data_criacao)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

foreach ($usuariosSistema as $usuario) {
    [$nome, $sobrenome, $cargo] = $usuario;

    // E-mail formatado conforme exigência (@fiemg.com.br)
    $email = strtolower($nome . "." . $sobrenome) . "@fiemg.com.br";
    $emailHash = hash('sha256', $email);

    // Criptografa o e-mail usando a função nativa do sistema
    $emailEncrypted = function_exists('encryptEmail') ? encryptEmail($email) : $email;

    $stmtUsuario->execute([
        $nome,
        $sobrenome,
        $emailHash,
        $emailEncrypted,
        $cargo,
        $senhaPadraoHash,
        date('Y-m-d H:i:s')
    ]);

    $usuarioId = $pdo->lastInsertId();

    if ($cargo === 'lider') {
        $lideres[] = $usuarioId;
    } else {
        $instrutores[] = "$nome $sobrenome";
    }
}

// Vincula responsáveis aos ambientes
$stmtResponsavel = $pdo->prepare("INSERT INTO responsaveis (usuario_id, ambiente, data_atribuicao) VALUES (?, ?, NOW())");
$stmtResponsavel->execute([$lideres[0], '104a']);
$stmtResponsavel->execute([$lideres[1], '103d']);
$stmtResponsavel->execute([$lideres[2], '102c']);
$stmtResponsavel->execute([$lideres[3], '102d']);
$stmtResponsavel->execute([$lideres[4], '101d']);

echo "✅ Usuários e Responsáveis vinculados com e-mails criptografados.<br>";

// =====================================================
// 3. DEFINIÇÃO DAS SALAS E CAMPOS
// =====================================================

$camposPorSala = [
    '104a' => [
        'carteiras_organizadas',
        'carteiras_quantidade',
        'carteiras_danificadas',
        'tv_presente',
        'tv_integra',
        'tv_hdmi',
        'tv_cabos_organizados',
        'tv_conectada',
        'tv_cabos_ok',
        'ar_presentes',
        'ar_controle',
        'ar_danos',
        'quadro_limpo',
        'quadro_danos',
        'quadro_fixo',
        'porta_funciona',
        'janelas_intactas',
        'janelas_vidros',
        'tomadas_intactas',
        'tomadas_fios',
        'tomadas_adaptadores',
        'mesa_firme',
        'mesa_gavetas',
        'cadeira_integra'
    ],
    '103d' => [
        'computadores_ligam',
        'mouses_funcionam',
        'teclados_funcionam',
        'monitores_funcionam',
        'gabinetes_estado',
        'cadeiras_baias',
        'ar_condicionado_funciona',
        'quadro_limpo',
        'mesa_instrutor',
        'cadeira_instrutor',
        'portao_funciona',
        'janelas_intactas',
        'tomadas_intactas',
        'fios_expostos'
    ],
    '102c' => [
        'portao_funciona',
        'instrutor_epi',
        'box1_epi_completo',
        'box1_ferramentas_ok',
        'box1_organizacao',
        'box2_epi_completo',
        'box2_ferramentas_ok',
        'box2_organizacao',
        'box3_epi_completo',
        'box3_ferramentas_ok',
        'box3_organizacao',
        'box4_epi_completo',
        'box4_ferramentas_ok',
        'box4_organizacao',
        'box5_epi_completo',
        'box5_ferramentas_ok',
        'box5_organizacao',
        'box6_epi_completo',
        'box6_ferramentas_ok',
        'box7_epi_completo',
        'box7_ferramentas_ok',
        'box7_organizacao',
        'box8_epi_completo',
        'box8_ferramentas_ok',
        'box8_organizacao',
        'box9_epi_completo',
        'box9_ferramentas_ok',
        'box9_organizacao',
        'box10_epi_completo',
        'box10_ferramentas_ok',
        'box10_organizacao',
        'area_limpa',
        'area_organizacao',
        'equipamentos_local',
        'macarico_ok',
        'estufa_ok'
    ],
    '102d' => [
        'porta_janelas_ok',
        'ar_condicionado_ok',
        'bancadas_limpas',
        'tomadas_fios_ok',
        'microscopios_b1_quantidade',
        'microscopios_b1_integros',
        'estufa_incubadora_b2_ok',
        'blocos_digestores_b2_ok',
        'balancas_analiticas_b3_ok',
        'centrifugas_extracao_b3_ok',
        'destilador_b4_ok',
        'cabine_seguranca_csb_ok',
        'microscopio_camera_desktop_ok',
        'rotaevaporador_gerber_vortex_ok',
        'estufas_forno_mufla_ok',
        'refrigerador_microondas_ok',
        'armario1_medidores_agua_ok',
        'armario2_3_phgametros_banhos_ok',
        'armario5_6_aquecimento_agitacao_ok',
        'armario7_medidores_campo_ok',
        'epis_seguranca_ok',
        'descarte_residuos_ok'
    ],
    '101d' => [
        'porta_janelas_ok',
        'ar_condicionado_ok',
        'bancadas_limpas',
        'tomadas_fios_ok',
        'microscopios_b1_quantidade',
        'microscopios_b1_integros',
        'estufa_incubadora_b2_ok',
        'blocos_digestores_b2_ok',
        'balancas_analiticas_b3_ok',
        'centrifugas_extracao_b3_ok',
        'destilador_b4_ok',
        'cabine_seguranca_csb_ok',
        'microscopio_camera_desktop_ok',
        'rotaevaporador_gerber_vortex_ok',
        'estufas_forno_mufla_ok',
        'refrigerador_microondas_ok',
        'armario1_medidores_agua_ok',
        'armario2_3_phgametros_banhos_ok',
        'armario5_6_aquecimento_agitacao_ok',
        'armario7_medidores_campo_ok',
        'epis_seguranca_ok',
        'descarte_residuos_ok'
    ]
];

$horariosPorPeriodo = [
    'manha' => ['inicio' => '07:30:00', 'fim' => '11:30:00'],
    'tarde' => ['inicio' => '13:30:00', 'fim' => '17:30:00'],
    'noite' => ['inicio' => '19:00:00', 'fim' => '22:30:00']
];

function getProbabilidadeProblema($ano, $mes)
{
    $sazonal = 0.20 * (1 + cos(2 * M_PI * ($mes - 6) / 12));
    $tendencia = -0.015 * ($ano - 2022);
    $base = 0.15 + $tendencia + $sazonal;
    return min(0.60, max(0.04, $base));
}

$anoAtual = (int) date('Y');
$mesAtual = (int) date('m');
$totalInsercoes = 0;

echo "🔄 Gerando histórico de inspeções...<br>";

// =====================================================
// 4. TRANSAÇÃO DE INSERÇÃO DOS CHECKLISTS E RELATÓRIOS
// =====================================================

try {
    $pdo->beginTransaction();

    for ($ano = 2022; $ano <= $anoAtual; $ano++) {
        $mesFim = ($ano == $anoAtual) ? $mesAtual : 12;

        for ($mes = 1; $mes <= $mesFim; $mes++) {
            $prob = getProbabilidadeProblema($ano, $mes);
            $mesStr = sprintf('%02d', $mes);

            $datasBase = [
                date('Y-m-d', strtotime("first friday of $ano-$mesStr")),
                date('Y-m-d', strtotime("third wednesday of $ano-$mesStr"))
            ];

            foreach ($datasBase as $dataBase) {
                if (empty($dataBase) || strpos($dataBase, "$ano-$mesStr") === false) {
                    continue;
                }

                $isSexta = (date('N', strtotime($dataBase)) == 5);

                foreach (['104a', '103d', '102c', '102d', '101d'] as $sala) {
                    $campos = $camposPorSala[$sala];

                    foreach (['manha', 'tarde', 'noite'] as $periodo) {
                        foreach (['inicio', 'fim'] as $momento) {
                            $hora = $horariosPorPeriodo[$periodo][$momento];
                            $dataStr = "$dataBase $hora";

                            $valores = [];
                            $problemas = [];

                            foreach ($campos as $campo) {
                                $var = mt_rand(-12, 12) / 100;
                                $probCampo = max(0.01, min(0.99, $prob + $var));
                                $resultado = (mt_rand(1, 100) <= $probCampo * 100) ? 'nao' : 'sim';
                                $valores[$campo] = $resultado;

                                if ($resultado === 'nao') {
                                    $problemas[] = $campo;
                                }
                            }

                            $observacao = (count($problemas) > 0)
                                ? "Não conformidades apontadas: " . implode(', ', $problemas)
                                : "Inspeção realizada com sucesso. Nada a declarar.";

                            $verificacaoSexta = null;
                            if ($isSexta && $momento === 'fim') {
                                $verificacaoSexta = json_encode([
                                    'limpeza_pesada_realizada' => (mt_rand(1, 10) <= 9) ? 'sim' : 'nao',
                                    'equipamentos_desligados_fds' => (mt_rand(1, 10) <= 8) ? 'sim' : 'nao'
                                ]);
                            }

                            $camposList = implode(', ', array_keys($valores));
                            $placeholders = implode(', ', array_fill(0, count($valores), '?'));
                            $sql = "INSERT INTO `$sala` (nome, data, momento, observacoes, verificacao_sexta, $camposList) 
                                    VALUES (?, ?, ?, ?, ?, $placeholders)";

                            $stmt = $pdo->prepare($sql);
                            $nomeInstrutor = $instrutores[array_rand($instrutores)];

                            $params = array_merge(
                                [$nomeInstrutor, $dataStr, $momento, $observacao, $verificacaoSexta],
                                array_values($valores)
                            );

                            $stmt->execute($params);
                            $inspecao_id = $pdo->lastInsertId();

                            $sqlRel = "INSERT INTO relatorios (inspecao_id, sala, data, periodo, momento, observacoes, data_geracao) 
                                       VALUES (?, ?, ?, ?, ?, ?, NOW())";
                            $stmtRel = $pdo->prepare($sqlRel);
                            $stmtRel->execute([$inspecao_id, $sala, $dataBase, $periodo, $momento, $observacao]);

                            $totalInsercoes++;
                        }
                    }
                }
            }
        }
    }

    $pdo->commit();
    echo "<hr><strong>🎉 Processamento concluído com sucesso!</strong><br>";
    echo "Foram inseridos <b>$totalInsercoes</b> registros no banco.<br><br>";
    echo "<b>Credenciais de Acesso de Teste:</b><br>";
    echo "• Líder Exemplo: <code>lenon.yuri@fiemg.com.br</code> | Senha: <code>senai123</code><br>";
    echo "• Instrutor Exemplo: <code>carlos.silva@fiemg.com.br</code> | Senha: <code>senai123</code>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<hr>❌ <strong>Falha crítica ao preencher lote:</strong> " . $e->getMessage();
}
?>