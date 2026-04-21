<?php
/**
 * install.php - Script de instalação/atualização do banco de dados
 * Agora com retorno JSON e diagnóstico claro.
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
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN $column $definition");
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
    // Opcional: tentar criar o banco de dados se ele não existir (requer privilégio)
    // Nota: O nome do banco deve ser o mesmo usado no DSN do database.php
    // Para evitar complexidade, assumimos que o DSN já aponta para o banco correto.
    // Se quiser tentar criar o banco automaticamente, descomente as linhas abaixo:
    /*
    $dbname = 'u196097154_kamishibai';
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");
    */

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

    // Migração de dados antigos (se a coluna 'email' existir)
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `usuarios` LIKE 'email'");
        if ($stmt->rowCount() > 0) {
            $usuarios = $pdo->query("SELECT id, email FROM `usuarios` WHERE email IS NOT NULL AND (email_hash IS NULL OR email_encrypted IS NULL)");
            while ($row = $usuarios->fetch(PDO::FETCH_ASSOC)) {
                $email = strtolower(trim($row['email']));
                $hash = hash('sha256', $email);
                // Fallback de criptografia simples (se a função encryptEmail não existir)
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
        // Ignora erros de migração (coluna já removida, etc.)
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

    // Verifica se as tabelas foram criadas
    $tabelas = ['104a', '103d', '102c', 'relatorios', 'usuarios', 'responsaveis'];
    $criadas = [];
    foreach ($tabelas as $tabela) {
        if (tableExists($pdo, $tabela)) {
            $criadas[] = $tabela;
        }
    }

    resposta(true, 'Instalação concluída com sucesso.', ['tabelas_criadas_ou_existentes' => $criadas]);

} catch (PDOException $e) {
    resposta(false, 'Erro ao executar a instalação: ' . $e->getMessage(), [
        'codigo' => $e->getCode(),
        'sqlstate' => $e->errorInfo[0] ?? null
    ]);
} catch (Exception $e) {
    resposta(false, 'Erro geral: ' . $e->getMessage());
}
?>