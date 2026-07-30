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
    <title>Relatórios Consolidados | Kamishibai</title>
    <!-- Bootstrap 5 + Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- CSS Customizados -->
    <link rel="stylesheet" href="../assets/css/geral.css">
    <link rel="stylesheet" href="../assets/css/acessorios/acessorios.css">
    <link rel="stylesheet" href="../assets/css/administrador/relatorios.css">
</head>

<body class="bg-light">
    <!-- Cabeçalho Dinâmico -->
    <div id="header"></div>

    <!-- Barra de Navegação Superior -->
    <div class="container d-flex justify-content-between align-items-center mt-3">
        <button class="btn btn-outline-secondary rounded-pill" onclick="history.back()">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </button>
        <h1 class="h4 mb-0 text-primary">
            <i class="bi bi-clipboard-data-fill me-2"></i>Relatórios Consolidados
        </h1>
        <a href="../acesso/api/logout.php" class="btn btn-outline-danger btn-sm rounded-pill">
            <i class="bi bi-box-arrow-right me-1"></i>Sair
        </a>
    </div>

    <main class="container">
        <!-- Central de Filtros Responsiva -->
        <div class="d-flex flex-wrap gap-3 align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm border-0">
            <div class="d-flex gap-2 align-items-center">
                <label for="filtroDataInicio" class="form-label fw-semibold mb-0">De:</label>
                <input type="date" id="filtroDataInicio" class="form-control w-auto rounded-pill">
            </div>

            <div class="d-flex gap-2 align-items-center">
                <label for="filtroDataFim" class="form-label fw-semibold mb-0">Até:</label>
                <input type="date" id="filtroDataFim" class="form-control w-auto rounded-pill">
            </div>

            <div class="d-flex gap-2 align-items-center">
                <label for="filtroSala" class="form-label fw-semibold mb-0">Sala:</label>
                <select id="filtroSala" class="form-select w-auto rounded-pill">
                    <option value="todas">Todas as salas</option>
                    <option value="101d">101d (Microdestilaria)</option>
                    <option value="102c">102c (Soldagem)</option>
                    <option value="102d">102d (Química/Meio Amb.)</option>
                    <option value="103d">103d (Informática)</option>
                    <option value="104a">104a (Sala de Aula)</option>
                </select>
            </div>

            <button id="btnFiltrar" class="btn btn-primary rounded-pill px-4 d-flex align-items-center">
                <i class="bi bi-funnel-fill me-1"></i>Filtrar
            </button>

            <a href="index.php" class="btn btn-outline-secondary rounded-pill ms-auto">
                <i class="bi bi-arrow-left me-1"></i>Voltar para listagem
            </a>
        </div>

        <!-- Painel dos Relatórios Consolidados -->
        <div id="relatoriosContainer" class="bg-white rounded-4 shadow-sm p-3 p-md-4">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Buscando vistorias...</span>
                </div>
                <p class="mt-2 text-secondary">Carregando histórico de relatórios...</p>
            </div>
        </div>

        <!-- Link Rápido para a Central de IA -->
        <div class="mb-4 mt-4">
            <a href="analise_ia.php"
                class="btn btn-primary rounded-pill px-4 py-2 shadow-sm d-inline-flex align-items-center">
                <i class="bi bi-cpu-fill me-2"></i>Acessar Análise Preditiva (IA)
            </a>
        </div>
    </main>

    <!-- MODAL DE VISUALIZAÇÃO DE IMAGENS -->
    <div class="modal fade" id="modalImagens" tabindex="-1" aria-labelledby="modalImagensLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalImagensLabel">
                        <i class="bi bi-images me-2 text-warning"></i>Galeria de Evidências Anexadas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div id="modalImagensContainer" class="d-flex flex-wrap gap-3 justify-content-center py-4"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rodapé Dinâmico -->
    <div id="footer"></div>

    <!-- Scripts de Dependência e Sessão -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/acessorios/componentes.js"></script>
    <script src="../assets/js/administrador/relatorios.js"></script>
    <script src="../assets/js/acesso/verificar_sessao_admin.js"></script>
</body>

</html>