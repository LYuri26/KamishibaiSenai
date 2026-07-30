<?php
/**
 * install.php - Script de instalação/atualização do banco de dados
 * Atualizado para o novo Checklist de 7 Seções do Laboratório 102D (Química).
 */

// Ativa exibição de erros para depuração (remova em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
set_time_limit(0);

require_once __DIR__ . '/database.php'; // Deve definir $pdo (PDO)

// Função para responder com JSON e encerrar
function resposta($sucesso, $mensagem, $detalhes = null)
{
    echo json_encode([
        'sucesso' => $sucesso,
        'mensagem' => $mensagem,
        'detalhes' => $detalhes
    ]);
    exit;
}

// Verifica se a conexão PDO existe e está funcionando
if (!isset($pdo) || !($pdo instanceof PDO)) {
    resposta(false, 'Conexão com banco de dados não disponível. Verifique o arquivo database.php');
}

// Função auxiliar para adicionar coluna se não existir
function addColumnIfNotExists(PDO $pdo, string $table, string $column, string $definition): void
{
    try {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            throw $e;
        }
    }
}

// Função para verificar se uma tabela existe
function tableExists(PDO $pdo, string $table): bool
{
    try {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        return $result->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

try {
    // ==================== TABELA 104a ====================
    $sql104a = "CREATE TABLE IF NOT EXISTS `104a` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nome` VARCHAR(100) NOT NULL,
        `data` DATETIME NOT NULL,
        `momento` ENUM('inicio','fim') NOT NULL,
        `observacoes` TEXT,
        `carteiras_organizadas` ENUM('sim','nao') NOT NULL,
        `carteiras_quantidade` ENUM('sim','nao') NOT NULL,
        `carteiras_danificadas` ENUM('sim','nao') NOT NULL,
        `tv_presente` ENUM('sim','nao') NOT NULL,
        `tv_integra` ENUM('sim','nao') NOT NULL,
        `tv_hdmi` ENUM('sim','nao') NOT NULL,
        `tv_cabos_organizados` ENUM('sim','nao') NOT NULL,
        `tv_conectada` ENUM('sim','nao') NOT NULL,
        `tv_cabos_ok` ENUM('sim','nao') NOT NULL,
        `ar_presentes` ENUM('sim','nao') NOT NULL,
        `ar_controle` ENUM('sim','nao') NOT NULL,
        `ar_danos` ENUM('sim','nao') NOT NULL,
        `quadro_limpo` ENUM('sim','nao') NOT NULL,
        `quadro_danos` ENUM('sim','nao') NOT NULL,
        `quadro_fixo` ENUM('sim','nao') NOT NULL,
        `porta_funciona` ENUM('sim','nao') NOT NULL,
        `janelas_intactas` ENUM('sim','nao') NOT NULL,
        `janelas_vidros` ENUM('sim','nao') NOT NULL,
        `tomadas_intactas` ENUM('sim','nao') NOT NULL,
        `tomadas_fios` ENUM('sim','nao') NOT NULL,
        `tomadas_adaptadores` ENUM('sim','nao') NOT NULL,
        `mesa_firme` ENUM('sim','nao') NOT NULL,
        `mesa_gavetas` ENUM('sim','nao') NOT NULL,
        `cadeira_integra` ENUM('sim','nao') NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql104a);
    addColumnIfNotExists($pdo, '104a', 'verificacao_sexta', 'JSON NULL');

    // ==================== TABELA 103d ====================
    $sql103d = "CREATE TABLE IF NOT EXISTS `103d` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nome` VARCHAR(100) NOT NULL,
        `data` DATETIME NOT NULL,
        `momento` ENUM('inicio','fim') NOT NULL,
        `observacoes` TEXT,
        `computadores_ligam` ENUM('sim','nao') NOT NULL,
        `mouses_funcionam` ENUM('sim','nao') NOT NULL,
        `teclados_funcionam` ENUM('sim','nao') NOT NULL,
        `monitores_funcionam` ENUM('sim','nao') NOT NULL,
        `gabinetes_estado` ENUM('sim','nao') NOT NULL,
        `cadeiras_baias` ENUM('sim','nao') NOT NULL,
        `ar_condicionado_funciona` ENUM('sim','nao') NOT NULL,
        `quadro_limpo` ENUM('sim','nao') NOT NULL,
        `mesa_instrutor` ENUM('sim','nao') NOT NULL,
        `cadeira_instrutor` ENUM('sim','nao') NOT NULL,
        `portao_funciona` ENUM('sim','nao') NOT NULL,
        `janelas_intactas` ENUM('sim','nao') NOT NULL,
        `tomadas_intactas` ENUM('sim','nao') NOT NULL,
        `fios_expostos` ENUM('sim','nao') NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql103d);
    addColumnIfNotExists($pdo, '103d', 'verificacao_sexta', 'JSON NULL');

    // ==================== TABELA 102c ====================
    $sql102c = "CREATE TABLE IF NOT EXISTS `102c` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nome` VARCHAR(100) NOT NULL,
        `data` DATETIME NOT NULL,
        `momento` ENUM('inicio','fim') NOT NULL,
        `observacoes` TEXT,
        `portao_funciona` ENUM('sim','nao') NOT NULL,
        `instrutor_epi` ENUM('sim','nao') NOT NULL,
        `box1_epi_completo` ENUM('sim','nao') NOT NULL,
        `box1_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box1_box1_organizacao` ENUM('sim','nao') NULL,
        `box1_organizacao` ENUM('sim','nao') NOT NULL,
        `box2_epi_completo` ENUM('sim','nao') NOT NULL,
        `box2_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box2_organizacao` ENUM('sim','nao') NOT NULL,
        `box3_epi_completo` ENUM('sim','nao') NOT NULL,
        `box3_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box3_organizacao` ENUM('sim','nao') NOT NULL,
        `box4_epi_completo` ENUM('sim','nao') NOT NULL,
        `box4_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box4_organizacao` ENUM('sim','nao') NOT NULL,
        `box5_epi_completo` ENUM('sim','nao') NOT NULL,
        `box5_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box5_organizacao` ENUM('sim','nao') NOT NULL,
        `box6_epi_completo` ENUM('sim','nao') NOT NULL,
        `box6_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box7_epi_completo` ENUM('sim','nao') NOT NULL,
        `box7_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box7_organizacao` ENUM('sim','nao') NOT NULL,
        `box8_epi_completo` ENUM('sim','nao') NOT NULL,
        `box8_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box8_organizacao` ENUM('sim','nao') NOT NULL,
        `box9_epi_completo` ENUM('sim','nao') NOT NULL,
        `box9_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box9_organizacao` ENUM('sim','nao') NOT NULL,
        `box10_epi_completo` ENUM('sim','nao') NOT NULL,
        `box10_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box10_organizacao` ENUM('sim','nao') NOT NULL,
        `area_limpa` ENUM('sim','nao') NOT NULL,
        `area_organizacao` ENUM('sim','nao') NOT NULL,
        `equipamentos_local` ENUM('sim','nao') NOT NULL,
        `macarico_ok` ENUM('sim','nao') NOT NULL,
        `estufa_ok` ENUM('sim','nao') NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql102c);
    addColumnIfNotExists($pdo, '102c', 'verificacao_sexta', 'JSON NULL');

    // ==================== TABELA 102d (Novo Checklist Química) ====================
    $sql102d = "CREATE TABLE IF NOT EXISTS `102d` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nome` VARCHAR(100) NOT NULL,
        `data` DATETIME NOT NULL,
        `momento` ENUM('inicio','fim') NOT NULL,
        `observacoes` TEXT,
        
        -- 1. ORGANIZAÇÃO
        `org_bancadas_limpas` ENUM('sim','nao') NOT NULL,
        `org_bancadas_organizadas` ENUM('sim','nao') NOT NULL,
        `org_cadeiras_organizadas` ENUM('sim','nao') NOT NULL,
        `org_materiais_guardados` ENUM('sim','nao') NOT NULL,
        `org_armarios_fechados` ENUM('sim','nao') NOT NULL,
        `org_quadro_limpo` ENUM('sim','nao') NOT NULL,
        `org_piso_limpo` ENUM('sim','nao') NOT NULL,
        `org_corredores_desobstruidos` ENUM('sim','nao') NOT NULL,

        -- 2. SEGURANÇA
        `seg_extintor_acessivel` ENUM('sim','nao') NOT NULL,
        `seg_chuveiro_emergencia_ok` ENUM('sim','nao') NOT NULL,
        `seg_lava_olhos_ok` ENUM('sim','nao') NOT NULL,
        `seg_kit_primeiros_socorros` ENUM('sim','nao') NOT NULL,
        `seg_saidas_emergencia_livres` ENUM('sim','nao') NOT NULL,
        `seg_sinalizacao_visivel` ENUM('sim','nao') NOT NULL,
        `seg_produtos_quimicos_identificados` ENUM('sim','nao') NOT NULL,
        `seg_fispqs_disponiveis` ENUM('sim','nao') NOT NULL,

        -- 3. EQUIPAMENTOS
        `eq_balanca_limpa` ENUM('sim','nao') NOT NULL,
        `eq_balanca_desligada` ENUM('sim','nao') NOT NULL,
        `eq_phmetro_limpo` ENUM('sim','nao') NOT NULL,
        `eq_condutivimetro_limpo` ENUM('sim','nao') NOT NULL,
        `eq_espectrofotometro_limpo` ENUM('sim','nao') NOT NULL,
        `eq_estufa_desligada` ENUM('sim','nao') NOT NULL,
        `eq_autoclave_desligada` ENUM('sim','nao') NOT NULL,
        `eq_equipamentos_desligados` ENUM('sim','nao') NOT NULL,
        `eq_equipamentos_sem_avarias` ENUM('sim','nao') NOT NULL,

        -- 4. VIDRARIAS
        `vid_vidrarias_limpas` ENUM('sim','nao') NOT NULL,
        `vid_vidrarias_secas` ENUM('sim','nao') NOT NULL,
        `vid_vidrarias_guardadas` ENUM('sim','nao') NOT NULL,
        `vid_existe_vidraria_quebrada` ENUM('sim','nao') NOT NULL,

        -- 5. PRODUTOS QUÍMICOS
        `pq_frascos_identificados` ENUM('sim','nao') NOT NULL,
        `pq_frascos_fechados` ENUM('sim','nao') NOT NULL,
        `pq_produtos_armazenados` ENUM('sim','nao') NOT NULL,
        `pq_residuos_descartados` ENUM('sim','nao') NOT NULL,

        -- 6. ENCERRAMENTO DO LABORATÓRIO
        `enc_pia_limpa` ENUM('sim','nao') NOT NULL,
        `enc_torneiras_fechadas` ENUM('sim','nao') NOT NULL,
        `enc_gas_fechado` ENUM('sim','nao') NOT NULL,
        `enc_agua_fechada` ENUM('sim','nao') NOT NULL,
        `enc_equipamentos_desligados` ENUM('sim','nao') NOT NULL,
        `enc_ar_condicionado_desligado` ENUM('sim','nao') NOT NULL,
        `enc_luzes_apagadas` ENUM('sim','nao') NOT NULL,
        `enc_porta_trancada` ENUM('sim','nao') NOT NULL,
        `enc_lixeiras_esvaziadas` ENUM('sim','nao') NOT NULL,

        -- 7. NÃO CONFORMIDADES
        `nc_encontrada` ENUM('sim','nao') NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql102d);

    // Garante a existência de todas as novas colunas caso a tabela 102d já existisse com a estrutura antiga
    $colunas102d = [
        'org_bancadas_limpas' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'org_bancadas_organizadas' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'org_cadeiras_organizadas' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'org_materiais_guardados' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'org_armarios_fechados' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'org_quadro_limpo' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'org_piso_limpo' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'org_corredores_desobstruidos' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'seg_extintor_acessivel' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'seg_chuveiro_emergencia_ok' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'seg_lava_olhos_ok' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'seg_kit_primeiros_socorros' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'seg_saidas_emergencia_livres' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'seg_sinalizacao_visivel' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'seg_produtos_quimicos_identificados' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'seg_fispqs_disponiveis' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'eq_balanca_limpa' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'eq_balanca_desligada' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'eq_phmetro_limpo' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'eq_condutivimetro_limpo' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'eq_espectrofotometro_limpo' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'eq_estufa_desligada' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'eq_autoclave_desligada' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'eq_equipamentos_desligados' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'eq_equipamentos_sem_avarias' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'vid_vidrarias_limpas' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'vid_vidrarias_secas' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'vid_vidrarias_guardadas' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'vid_existe_vidraria_quebrada' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'pq_frascos_identificados' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'pq_frascos_fechados' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'pq_produtos_armazenados' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'pq_residuos_descartados' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'enc_pia_limpa' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'enc_torneiras_fechadas' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'enc_gas_fechado' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'enc_agua_fechada' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'enc_equipamentos_desligados' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'enc_ar_condicionado_desligado' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'enc_luzes_apagadas' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'enc_porta_trancada' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'enc_lixeiras_esvaziadas' => "ENUM('sim','nao') NOT NULL DEFAULT 'sim'",
        'nc_encontrada' => "ENUM('sim','nao') NOT NULL DEFAULT 'nao'",
        'verificacao_sexta' => "JSON NULL"
    ];

    foreach ($colunas102d as $coluna => $def) {
        addColumnIfNotExists($pdo, '102d', $coluna, $def);
    }

    // ==================== TABELA 101d (Microdestilaria) ====================
    $sql101d = "CREATE TABLE IF NOT EXISTS `101d` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nome` VARCHAR(100) NOT NULL,
        `data` DATETIME NOT NULL,
        `momento` ENUM('inicio','fim') NOT NULL,
        `observacoes` TEXT,
        `porta_janelas_ok` ENUM('sim','nao') NOT NULL,
        `ar_condicionado_ok` ENUM('sim','nao') NOT NULL,
        `bancadas_limpas` ENUM('sim','nao') NOT NULL,
        `tomadas_fios_ok` ENUM('sim','nao') NOT NULL,
        `microscopios_b1_quantidade` ENUM('sim','nao') NOT NULL,
        `microscopios_b1_integros` ENUM('sim','nao') NOT NULL,
        `estufa_incubadora_b2_ok` ENUM('sim','nao') NOT NULL,
        `blocos_digestores_b2_ok` ENUM('sim','nao') NOT NULL,
        `balancas_analiticas_b3_ok` ENUM('sim','nao') NOT NULL,
        `centrifugas_extracao_b3_ok` ENUM('sim','nao') NOT NULL,
        `destilador_b4_ok` ENUM('sim','nao') NOT NULL,
        `cabine_seguranca_csb_ok` ENUM('sim','nao') NOT NULL,
        `microscopio_camera_desktop_ok` ENUM('sim','nao') NOT NULL,
        `rotaevaporador_gerber_vortex_ok` ENUM('sim','nao') NOT NULL,
        `estufas_forno_mufla_ok` ENUM('sim','nao') NOT NULL,
        `refrigerador_microondas_ok` ENUM('sim','nao') NOT NULL,
        `armario1_medidores_agua_ok` ENUM('sim','nao') NOT NULL,
        `armario2_3_phgametros_banhos_ok` ENUM('sim','nao') NOT NULL,
        `armario5_6_aquecimento_agitacao_ok` ENUM('sim','nao') NOT NULL,
        `armario7_medidores_campo_ok` ENUM('sim','nao') NOT NULL,
        `epis_seguranca_ok` ENUM('sim','nao') NOT NULL,
        `descarte_residuos_ok` ENUM('sim','nao') NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql101d);
    addColumnIfNotExists($pdo, '101d', 'verificacao_sexta', 'JSON NULL');

    // ==================== TABELA relatorios ====================
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

    // ==================== TABELA usuarios (com criptografia) ====================
    $sqlUsuarios = "CREATE TABLE IF NOT EXISTS `usuarios` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nome` VARCHAR(100) NOT NULL,
        `sobrenome` VARCHAR(100) NOT NULL,
        `email_hash` VARCHAR(64) UNIQUE NOT NULL,
        `email_encrypted` TEXT NOT NULL,
        `cargo` ENUM('instrutor','lider') NOT NULL,
        `senha` VARCHAR(255) NOT NULL,
        `data_criacao` DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sqlUsuarios);

    // Migração de dados antigos de e-mail (se aplicável)
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `usuarios` LIKE 'email'");
        if ($stmt->rowCount() > 0) {
            $usuarios = $pdo->query("SELECT id, email FROM `usuarios` WHERE email IS NOT NULL AND (email_hash IS NULL OR email_encrypted IS NULL)");
            while ($row = $usuarios->fetch(PDO::FETCH_ASSOC)) {
                $email = strtolower(trim($row['email']));
                $hash = hash('sha256', $email);
                if (function_exists('encryptEmail')) {
                    $encrypted = encryptEmail($email);
                } else {
                    $key = 'k4m1sh1b41_s3cr3t_k3y_2025';
                    $method = 'AES-256-CBC';
                    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
                    $encrypted = base64_encode($iv . openssl_encrypt($email, $method, $key, 0, $iv));
                }
                $upd = $pdo->prepare("UPDATE `usuarios` SET email_hash = ?, email_encrypted = ? WHERE id = ?");
                $upd->execute([$hash, $encrypted, $row['id']]);
            }
            $pdo->exec("ALTER TABLE `usuarios` DROP COLUMN `email`");
        }
    } catch (PDOException $e) {
        // Ignora erros de migração antiga
    }

    addColumnIfNotExists($pdo, 'usuarios', 'email_hash', 'VARCHAR(64) UNIQUE');
    addColumnIfNotExists($pdo, 'usuarios', 'email_encrypted', 'TEXT NOT NULL');

    // ==================== TABELA responsaveis ====================
    $sqlResponsaveis = "CREATE TABLE IF NOT EXISTS `responsaveis` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `usuario_id` INT NOT NULL,
        `ambiente` VARCHAR(50) NOT NULL,
        `data_atribuicao` DATETIME NOT NULL,
        UNIQUE KEY `unique_ambiente` (`ambiente`),
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sqlResponsaveis);

    // Lista e verifica todas as tabelas instaladas
    $tabelas = ['104a', '103d', '102c', '102d', '101d', 'relatorios', 'usuarios', 'responsaveis'];
    $criadas = [];
    foreach ($tabelas as $tabela) {
        if (tableExists($pdo, $tabela)) {
            $criadas[] = $tabela;
        }
    }

    resposta(true, 'Instalação e atualização do banco de dados concluídas com sucesso.', [
        'tabelas_criadas_ou_existentes' => $criadas
    ]);

} catch (PDOException $e) {
    resposta(false, 'Erro ao executar a instalação: ' . $e->getMessage(), [
        'codigo' => $e->getCode(),
        'sqlstate' => $e->errorInfo[0] ?? null
    ]);
} catch (Exception $e) {
    resposta(false, 'Erro geral: ' . $e->getMessage());
}