/**
 * listar.js - Painel Principal do Administrador
 * Controla a renderização dinâmica de vistorias com busca textual de salas e instrutores,
 * gerencia contadores de registros e exibe alertas de conformidade.
 */

// Armazenamento global das inspeções carregadas para filtragem instantânea
let todasInspecoes = [];

async function carregarInspecoes() {
  const tbody = document.getElementById("listaInspecoes");
  tbody.innerHTML = `
    <tr>
      <td colspan="7" class="text-center">
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
        Sincronizando banco de inspeções...
      </td>
    </tr>
  `;

  try {
    const response = await fetch("api/listar_inspecoes.php");
    if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
    const data = await response.json();

    if (data.erro) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-danger text-center"><i class="bi bi-x-circle me-1"></i>${data.erro}</td></tr>`;
      return;
    }

    // Salva o retorno para manipulação reativa na memória
    todasInspecoes = Array.isArray(data) ? data : [];

    // Atualiza o contador de total geral de inspeções no header da página
    const totalBadge = document.getElementById("totalInspecoesBadge");
    if (totalBadge) {
      totalBadge.textContent = todasInspecoes.length;
    }

    // Executa a primeira renderização (com filtros padrões aplicados)
    filtrarERenderizarInspecoes();
  } catch (error) {
    console.error("Erro ao carregar inspeções:", error);
    tbody.innerHTML = `<tr><td colspan="7" class="text-danger text-center"><i class="bi bi-exclamation-triangle-fill me-1"></i>Erro ao carregar dados: ${error.message}</td></tr>`;
  }
}

function filtrarERenderizarInspecoes() {
  const pesquisaInput = document.getElementById("pesquisaFiltro");
  const salaInput = document.getElementById("salaFiltro");
  const tbody = document.getElementById("listaInspecoes");

  const termo = pesquisaInput ? pesquisaInput.value.trim().toLowerCase() : "";
  const sala = salaInput ? salaInput.value : "todas";

  let filtradas = todasInspecoes;

  // 1. Filtragem por sala selecionada
  if (sala !== "todas") {
    filtradas = filtradas.filter((inspecao) => inspecao.sala === sala);
  }

  // 2. Filtragem textual (busca por instrutor ou ocorrências)
  if (termo) {
    filtradas = filtradas.filter((inspecao) => {
      const instrutor = (inspecao.nome || "").toLowerCase();
      const observacao = (inspecao.observacoes || "").toLowerCase();
      return instrutor.includes(termo) || observacao.includes(termo);
    });
  }

  // 3. Regra de Negócio Padrão: Se nenhum filtro estiver ativo, exibe apenas vistorias do mês atual
  if (!termo && sala === "todas") {
    const hoje = new Date();
    const anoAtual = hoje.getFullYear();
    const mesAtual = hoje.getMonth() + 1; // Meses em JS iniciam em 0

    filtradas = filtradas.filter((inspecao) => {
      const dataInspecao = new Date(inspecao.data);
      return (
        dataInspecao.getFullYear() === anoAtual &&
        dataInspecao.getMonth() + 1 === mesAtual
      );
    });
  }

  // Limite de exibição (exibe até 50 em pesquisas ativas para agilizar o render, ou 20 no padrão mensal)
  const limite = termo || sala !== "todas" ? 50 : 20;
  const inspecoesExibir = filtradas.slice(0, limite);

  // Limpa o corpo da tabela
  tbody.innerHTML = "";

  // Atualiza o contador de registros exibidos no rodapé da tabela
  const contagemEl = document.getElementById("contagemRegistros");
  if (contagemEl) {
    contagemEl.textContent = `${inspecoesExibir.length} registros de ${filtradas.length} encontrados`;
  }

  if (inspecoesExibir.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center text-secondary py-4">
          <i class="bi bi-search me-1"></i>Nenhuma inspeção atende aos filtros atuais.
        </td>
      </tr>
    `;
    return;
  }

  // Constrói as linhas com suporte à injeção de data-label (Design Responsivo)
  inspecoesExibir.forEach((inspecao) => {
    const row = document.createElement("tr");
    const momentoTexto = inspecao.momento === "inicio" ? "Início" : "Fim";
    const dataFormatada = new Date(inspecao.data).toLocaleString("pt-BR");

    const obsPreview = inspecao.observacoes
      ? inspecao.observacoes.substring(0, 100) +
        (inspecao.observacoes.length > 100 ? "…" : "")
      : "Nenhuma observação.";

    row.innerHTML = `
      <td data-label="ID" class="fw-bold">#${inspecao.id}</td>
      <td data-label="Instrutor">${escapeHtml(inspecao.nome)}</td>
      <td data-label="Data/Hora">${dataFormatada}</td>
      <td data-label="Momento">${momentoTexto}</td>
      <td data-label="Sala"><span class="badge bg-secondary rounded-pill px-3">${escapeHtml(inspecao.sala)}</span></td>
      <td data-label="Observações" class="obs-preview">${escapeHtml(obsPreview)}</td>
      <td data-label="Ações" class="text-center">
        <a href="visualizar.html?id=${inspecao.id}&sala=${inspecao.sala}" class="btn btn-sm btn-visualizar-detalhes">
          <i class="bi bi-eye-fill"></i> Detalhes
        </a>
      </td>
    `;
    tbody.appendChild(row);
  });

  // Atualiza a hora do sincronismo
  const ultimaAtualizacaoEl = document.getElementById("ultimaAtualizacao");
  if (ultimaAtualizacaoEl) {
    ultimaAtualizacaoEl.textContent = new Date().toLocaleString("pt-BR");
  }
}

async function carregarAlertas() {
  const container = document.getElementById("alertasContainer");
  if (!container) return;

  try {
    const response = await fetch("api/verificar_alertas.php");
    if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
    const alertas = await response.json();

    if (alertas.erro) {
      container.innerHTML = `<div class="alert alert-danger"><i class="bi bi-x-circle me-1"></i>Erro: ${alertas.erro}</div>`;
      return;
    }

    if (alertas.length === 0) {
      container.innerHTML = `
        <div class="alert alert-success">
          <i class="bi bi-check-circle-fill me-2"></i>Todos os ambientes e turnos de hoje foram vistoriados com sucesso.
        </div>`;
    } else {
      let html = `
        <div class="alert alert-warning d-block">
          <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Atenção!</strong> Há períodos letivos sem vistorias registradas hoje:
          <ul class="mb-0 mt-2">
      `;
      alertas.forEach((a) => {
        html += `<li>${a.mensagem}</li>`;
      });
      html += "</ul></div>";
      container.innerHTML = html;
    }
  } catch (error) {
    console.error("Erro ao carregar alertas:", error);
    container.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Falha ao carregar alertas: ${error.message}</div>`;
  }
}

// Sanitização robusta contra injeção de scripts (XSS)
function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// ================= VÍNCULO DE ESCUTADORES DE EVENTO =================
document.addEventListener("DOMContentLoaded", () => {
  // Inicialização principal
  carregarInspecoes();
  carregarAlertas();

  // Ouvinte de digitação no campo de pesquisa textual
  const pesquisaFiltro = document.getElementById("pesquisaFiltro");
  if (pesquisaFiltro) {
    pesquisaFiltro.addEventListener("input", filtrarERenderizarInspecoes);
  }

  // Ouvinte de mudança na seleção de salas
  const salaFiltro = document.getElementById("salaFiltro");
  if (salaFiltro) {
    salaFiltro.addEventListener("change", filtrarERenderizarInspecoes);
  }

  // Ouvinte do botão de limpeza de filtros
  const btnLimpar = document.getElementById("btnLimparFiltros");
  if (btnLimpar) {
    btnLimpar.addEventListener("click", () => {
      if (pesquisaFiltro) pesquisaFiltro.value = "";
      if (salaFiltro) salaFiltro.value = "todas";
      filtrarERenderizarInspecoes();
    });
  }
});
