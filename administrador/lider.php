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
    <title>Gerenciar Líderes de Ambientes | Kamishibai</title>
    <!-- Bootstrap 5 + Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- CSS Customizados -->
    <link rel="stylesheet" href="../assets/css/geral.css">
    <link rel="stylesheet" href="../assets/css/acessorios/acessorios.css">
    <link rel="stylesheet" href="../assets/css/administrador/lider.css">
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
            <i class="bi bi-person-badge-fill me-2"></i>Responsáveis pelos Ambientes
        </h1>
        <a href="../acesso/api/logout.php" class="btn btn-outline-danger btn-sm rounded-pill">
            <i class="bi bi-box-arrow-right me-1"></i>Sair
        </a>
    </div>

    <!-- Painel de Gestão e Atribuições -->
    <main class="container">
        <!-- Container Central de Feedbacks e Mensagens -->
        <div id="message" class="message-container mb-4" aria-live="polite"></div>

        <!-- Grade Dinâmica dos Cards dos Ambientes -->
        <div id="responsaveis-list" class="responsaveis-grid" aria-label="Lista de ambientes e líderes atribuídos">
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Buscando atribuições...</span>
                </div>
                <p class="mt-2 text-secondary mb-0">Mapeando ambientes e responsáveis atribuídos...</p>
            </div>
        </div>
    </main>

    <!-- Rodapé Dinâmico -->
    <div id="footer"></div>

    <!-- Scripts de Dependências e Sessão -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/acessorios/componentes.js"></script>
    <script src="../assets/js/acesso/verificar_sessao_admin.js"></script>
    <script src="../assets/js/administrador/lider.js"></script>
</body>

</html>