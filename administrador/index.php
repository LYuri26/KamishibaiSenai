<?php
session_start();

// Acesso permitido EXCLUSIVAMENTE para o cargo 'lider'
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_cargo'] ?? '') !== 'lider') {
    header('Location: ../acesso/login.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Inspeções | Kamishibai</title>
    <!-- Bootstrap 5 + Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- CSS Customizados -->
    <link rel="stylesheet" href="../assets/css/geral.css">
    <link rel="stylesheet" href="../assets/css/administrador/index.css">
</head>

<body class="bg-light">
    <!-- Cabeçalho Dinâmico -->
    <div id="header"></div>

    <!-- Cabeçalho Principal do Administrador -->
    <header class="bg-gradient-primary shadow-sm py-3 mb-4">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light rounded-pill shadow-sm d-flex align-items-center" onclick="history.back()"
                    aria-label="Voltar para a página anterior">
                    <i class="bi bi-arrow-left me-1"></i>Voltar
                </button>
                <h1 class="h4 mb-0 text-white d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock-fill fs-2"></i>
                    <span>Painel Administrativo</span>
                    <span class="badge bg-white text-primary rounded-pill ms-2 fs-6" id="totalInspecoesBadge"
                        aria-live="polite">0</span>
                </h1>
            </div>
            <a href="../acesso/api/logout.php" class="btn btn-outline-light btn-sm rounded-pill px-4 py-2">
                <i class="bi bi-box-arrow-right me-1"></i>Sair
            </a>
        </div>
    </header>

    <main class="container">
        <!-- Container Central de Alertas Ativos de Vistoria -->
        <section id="alertasContainer" class="alertas-container mb-4"
            aria-label="Alertas e não conformidades pendentes"></section>

        <!-- Grade de Navegação Rápida (Módulos de Controle) -->
        <section class="row g-3 mb-4" aria-label="Módulos de governança e controle">
            <div class="col-12 col-md-6 col-lg-3">
                <a href="relatorios.php" class="card nav-card text-decoration-none h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-gradient-success text-white mb-3 mx-auto">
                            <i class="bi bi-clipboard-data-fill"></i>
                        </div>
                        <h5 class="card-title fw-bold">Relatórios Consolidados</h5>
                        <p class="card-text small text-muted mb-0">Visualize, filtre e exporte relatórios e evidências
                            do histórico</p>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <a href="analise_ia.php" class="card nav-card text-decoration-none h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-gradient-success text-white mb-3 mx-auto">
                            <i class="bi bi-cpu-fill"></i>
                        </div>
                        <h5 class="card-title fw-bold">Análise Preditiva (IA)</h5>
                        <p class="card-text small text-muted mb-0">Identifique tendências de falhas e projeções com
                            Holt-Winters</p>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <a href="lider.php" class="card nav-card text-decoration-none h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-gradient-warning text-white mb-3 mx-auto">
                            <i class="bi bi-people-fill text-dark"></i>
                        </div>
                        <h5 class="card-title fw-bold">Atribuir Responsáveis</h5>
                        <p class="card-text small text-muted mb-0">Vincule líderes coordenadores de conformidade a cada
                            sala técnica</p>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <a href="editar_usuarios.php" class="card nav-card text-decoration-none h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-gradient-secondary text-white mb-3 mx-auto">
                            <i class="bi bi-person-gear"></i>
                        </div>
                        <h5 class="card-title fw-bold">Editar Usuários</h5>
                        <p class="card-text small text-muted mb-0">Gerencie permissões, crie contas, modifique cargos e
                            credenciais</p>
                    </div>
                </a>
            </div>
        </section>

        <!-- Central de Filtros e Busca Rápida -->
        <section class="row g-3 mb-4 bg-white rounded-4 p-3 shadow-sm border-0 align-items-center mx-1"
            aria-label="Filtros de busca rápida">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="pesquisaFiltro" class="form-control bg-light border-0 rounded-end-pill"
                        placeholder="Buscar por instrutor ou ocorrência...">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select id="salaFiltro" class="form-select bg-light border-0 rounded-pill">
                    <option value="todas">Filtrar por Sala (Todas)</option>
                    <option value="101d">101d (Microdestilaria)</option>
                    <option value="102c">102c (Soldagem)</option>
                    <option value="102d">102d (Química/Meio Amb.)</option>
                    <option value="103d">103d (Informática)</option>
                    <option value="104a">104a (Sala de Aula)</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <button id="btnLimparFiltros"
                    class="btn btn-outline-secondary rounded-pill w-100 d-flex align-items-center justify-content-center">
                    <i class="bi bi-x-circle me-1"></i>Limpar Filtros
                </button>
            </div>
        </section>

        <!-- Tabela Geral de Auditorias Realizadas -->
        <section class="table-wrapper bg-white rounded-4 shadow-lg p-0" id="tabelaInspecoesWrapper"
            aria-label="Inspeções recentes registradas">
            <div class="table-header d-flex justify-content-between align-items-center p-3 border-bottom">
                <h5 class="mb-0 fw-bold text-primary d-flex align-items-center">
                    <i class="bi bi-list-check me-2 fs-4"></i>Últimas Inspeções Registradas
                </h5>
                <span class="badge bg-primary rounded-pill px-3 py-2" id="contagemRegistros" aria-live="polite">0
                    registros</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabelaInspecoes">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Instrutor</th>
                            <th scope="col">Data/Hora</th>
                            <th scope="col">Momento</th>
                            <th scope="col">Sala</th>
                            <th scope="col">Observações</th>
                            <th scope="col" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="listaInspecoes">
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                    <span class="visually-hidden">Buscando auditorias...</span>
                                </div>
                                Carregando banco de inspeções recentes...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <span class="text-muted small">Sincronização em tempo real. Última atualização: <span
                        id="ultimaAtualizacao" class="fw-bold">--</span></span>
            </div>
        </section>
    </main>

    <!-- Rodapé Dinâmico -->
    <div id="footer"></div>

    <!-- Scripts de Dependências e Sessão -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/acessorios/componentes.js"></script>
    <script src="../assets/js/administrador/listar.js"></script>
    <script src="../assets/js/acesso/verificar_sessao_admin.js"></script>
</body>

</html>