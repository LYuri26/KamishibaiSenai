// =====================================================
// ANALISE IA - KAMISHIBAI SENAI
// Versão final com impressão otimizada
// =====================================================

let graficoEvolucao, graficoPrevisao, graficoSalas;

// Preenche os anos disponíveis (últimos 5 anos)
function preencherAnos() {
  const anoAtual = new Date().getFullYear();
  const selectAno = document.getElementById("filtroAno");
  if (!selectAno) return;
  selectAno.innerHTML = "";
  for (let i = anoAtual; i >= anoAtual - 4; i--) {
    const option = document.createElement("option");
    option.value = i;
    option.textContent = i;
    selectAno.appendChild(option);
  }
}

// Carrega os dados da API e atualiza os componentes
async function carregarDados() {
  const periodo = document.getElementById("filtroPeriodo").value;
  const ano = document.getElementById("filtroAno").value;
  const sala = document.getElementById("filtroSala").value;

  const url = `api/analise_ia.php?periodo=${periodo}&ano=${ano}&sala=${sala}`;

  try {
    const response = await fetch(url);
    if (!response.ok) throw new Error("Erro na requisição");
    const data = await response.json();

    if (data.erro) {
      alert(data.erro);
      return;
    }

    // Atualiza cards
    document.getElementById("totalInspecoes").innerHTML = data.total_inspecoes;
    document.getElementById("taxaProblemas").innerHTML =
      data.taxa_media_problemas + "%";
    document.getElementById("previsaoProximo").innerHTML =
      data.previsao_proximo + "%";

    // Gráfico de evolução
    if (graficoEvolucao) graficoEvolucao.destroy();
    const ctxEvo = document.getElementById("graficoEvolucao").getContext("2d");
    graficoEvolucao = new Chart(ctxEvo, {
      type: "line",
      data: {
        labels: data.evolucao.labels,
        datasets: [
          {
            label: "Taxa de Problemas (%)",
            data: data.evolucao.valores,
            borderColor: "rgba(54, 162, 235, 1)",
            backgroundColor: "rgba(54, 162, 235, 0.1)",
            tension: 0.3,
            fill: true,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          tooltip: { callbacks: { label: (ctx) => `${ctx.raw}%` } },
        },
      },
    });

    // Gráfico de previsão
    if (graficoPrevisao) graficoPrevisao.destroy();
    const ctxPrev = document.getElementById("graficoPrevisao").getContext("2d");
    graficoPrevisao = new Chart(ctxPrev, {
      type: "line",
      data: {
        labels: data.previsao.labels,
        datasets: [
          {
            label: "Histórico",
            data: data.previsao.historico,
            borderColor: "rgba(75, 192, 192, 1)",
            tension: 0.3,
          },
          {
            label: "Previsão",
            data: data.previsao.previsao,
            borderColor: "rgba(255, 99, 132, 1)",
            borderDash: [5, 5],
            tension: 0.3,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
      },
    });

    // Ranking de problemas
    const tbody = document.getElementById("rankingProblemas");
    tbody.innerHTML = "";
    if (data.ranking.length === 0) {
      tbody.innerHTML =
        '发展<td colspan="3" class="text-center">Nenhum dado disponível</td>发展';
    } else {
      data.ranking.forEach((item) => {
        const row = document.createElement("tr");
        row.innerHTML = `
                    <td>${escapeHtml(item.item)}</td>
                    <td>${item.incidencia}%</td>
                    <td>${item.ocorrencias}</td>
                `;
        tbody.appendChild(row);
      });
    }

    // Gráfico por sala
    if (graficoSalas) graficoSalas.destroy();
    const ctxSalas = document.getElementById("graficoSalas").getContext("2d");
    graficoSalas = new Chart(ctxSalas, {
      type: "bar",
      data: {
        labels: data.salas.labels,
        datasets: [
          {
            label: "Taxa de Problemas (%)",
            data: data.salas.valores,
            backgroundColor: "rgba(153, 102, 255, 0.6)",
            borderColor: "rgba(153, 102, 255, 1)",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            title: { display: true, text: "Porcentagem (%)" },
          },
        },
      },
    });

    // Adiciona a data atual como atributo para impressão (rodapé)
    const mainElement = document.querySelector("main.container");
    if (mainElement) {
      mainElement.setAttribute(
        "data-print-date",
        new Date().toLocaleString("pt-BR"),
      );
    }
  } catch (error) {
    console.error(error);
    alert("Erro ao carregar os dados da análise.");
  }
}

// Função para escapar HTML (segurança)
function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// =====================================================
// FUNÇÃO PARA GERAR PDF VIA IMPRESSÃO (window.print)
// =====================================================
async function gerarPDF() {
  const btn = document.getElementById("btnExportarPDF");
  if (!btn) return;

  const originalText = btn.innerHTML;
  btn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Abrindo impressão...';
  btn.disabled = true;

  try {
    // Garante que os gráficos estejam completamente renderizados
    await new Promise((resolve) => setTimeout(resolve, 300));
    // Chama a impressão nativa do navegador
    window.print();
  } catch (err) {
    console.error("Erro ao abrir impressão:", err);
    alert("Não foi possível abrir a impressão. Tente novamente.");
  } finally {
    btn.innerHTML = originalText;
    btn.disabled = false;
  }
}

// =====================================================
// INICIALIZAÇÃO
// =====================================================
document.addEventListener("DOMContentLoaded", () => {
  preencherAnos();
  carregarDados();

  const btnAtualizar = document.getElementById("btnAtualizar");
  if (btnAtualizar) {
    btnAtualizar.addEventListener("click", () => {
      carregarDados();
    });
  }

  const btnExportar = document.getElementById("btnExportarPDF");
  if (btnExportar) {
    btnExportar.addEventListener("click", gerarPDF);
  }

  // Ao mudar o período, ajusta o controle de ano
  const filtroPeriodo = document.getElementById("filtroPeriodo");
  if (filtroPeriodo) {
    filtroPeriodo.addEventListener("change", (e) => {
      const periodo = e.target.value;
      const anoSelect = document.getElementById("filtroAno");
      if (periodo === "anual") {
        anoSelect.disabled = false;
      } else {
        anoSelect.disabled = true;
        const anoAtual = new Date().getFullYear();
        anoSelect.value = anoAtual;
      }
    });
    filtroPeriodo.dispatchEvent(new Event("change"));
  }
});
