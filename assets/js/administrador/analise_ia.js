// =====================================================
// ANALISE IA - KAMISHIBAI SENAI
// Versão com impressão otimizada
// =====================================================

let graficoEvolucao, graficoPrevisao, graficoSalas;

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

async function carregarDados() {
  const periodo = document.getElementById("filtroPeriodo").value;
  const ano = document.getElementById("filtroAno").value;
  const sala = document.getElementById("filtroSala").value;

  const url = `api/analise_ia.php?periodo=${periodo}&ano=${ano}&sala=${sala}`;

  try {
    const response = await fetch(url);
    if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
    const data = await response.json();

    if (data.erro) {
      alert(`Erro: ${data.erro}`);
      return;
    }

    // ========== CARDS ==========
    document.getElementById("totalInspecoes").innerHTML =
      data.total_inspecoes ?? 0;
    document.getElementById("taxaProblemas").innerHTML =
      (data.taxa_media_problemas ?? 0) + "%";
    document.getElementById("previsaoProximo").innerHTML =
      (data.previsao_proximo ?? 0) + "%";

    // ========== INFORMAÇÕES DO MODELO ==========
    const modeloInfoDiv = document.getElementById("infoModelo");
    if (modeloInfoDiv && data.previsao) {
      let html = `<strong>Modelo:</strong> ${data.previsao.modelo || "Não disponível"}`;
      if (data.previsao.mae !== null && data.previsao.mae !== undefined) {
        html += ` &nbsp;| <strong>MAE:</strong> ${data.previsao.mae}% (últimos 3 meses)`;
      }
      modeloInfoDiv.innerHTML = html;
    }

    // ========== COMPONENTES DA PREVISÃO ==========
    const compDiv = document.getElementById("componentesDetalhes");
    if (compDiv) {
      if (
        data.previsao?.componentes?.level &&
        data.previsao.componentes.level.length > 0
      ) {
        compDiv.innerHTML = `
          <div class="row mt-2">
            <div class="col-md-6">
              <strong>Últimos níveis (suavizados):</strong> ${data.previsao.componentes.level.map((v) => v.toFixed(2)).join(", ")}
            </div>
            <div class="col-md-6">
              <strong>Últimas tendências:</strong> ${data.previsao.componentes.trend.map((v) => v.toFixed(2)).join(", ")}
            </div>
          </div>
        `;
      } else {
        compDiv.innerHTML =
          '<div class="text-muted mt-2">Nenhum dado de componentes disponível para este modelo.</div>';
      }
    }

    // ========== GRÁFICO EVOLUÇÃO ==========
    if (graficoEvolucao) graficoEvolucao.destroy();
    const ctxEvo = document.getElementById("graficoEvolucao").getContext("2d");
    graficoEvolucao = new Chart(ctxEvo, {
      type: "line",
      data: {
        labels: data.evolucao?.labels || [],
        datasets: [
          {
            label: "Taxa de Problemas (%)",
            data: data.evolucao?.valores || [],
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

    // ========== GRÁFICO PREVISÃO ==========
    if (graficoPrevisao) graficoPrevisao.destroy();
    const ctxPrev = document.getElementById("graficoPrevisao").getContext("2d");

    const datasets = [
      {
        label: "Histórico",
        data: data.previsao?.historico || [],
        borderColor: "rgba(75, 192, 192, 1)",
        tension: 0.3,
        fill: false,
      },
      {
        label: "Previsão",
        data: data.previsao?.previsao || [],
        borderColor: "rgba(255, 99, 132, 1)",
        borderDash: [5, 5],
        tension: 0.3,
        fill: false,
      },
    ];

    const hasForecast = data.previsao?.previsao?.some((v) => v !== null);
    if (hasForecast) {
      const superior = data.previsao.previsao.map((v) =>
        v !== null ? v + 5 : null,
      );
      const inferior = data.previsao.previsao.map((v) =>
        v !== null ? Math.max(0, v - 5) : null,
      );
      datasets.push({
        label: "Intervalo Superior",
        data: superior,
        borderColor: "rgba(255, 99, 132, 0)",
        backgroundColor: "rgba(255, 99, 132, 0.2)",
        fill: "+1",
        pointRadius: 0,
        showLine: false,
      });
      datasets.push({
        label: "Intervalo Inferior",
        data: inferior,
        borderColor: "rgba(255, 99, 132, 0)",
        backgroundColor: "rgba(255, 99, 132, 0.2)",
        fill: "+2",
        pointRadius: 0,
        showLine: false,
      });
    }

    graficoPrevisao = new Chart(ctxPrev, {
      type: "line",
      data: {
        labels: data.previsao?.labels || [],
        datasets: datasets,
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          tooltip: {
            callbacks: {
              label: (ctx) => {
                if (
                  ctx.dataset.label === "Histórico" ||
                  ctx.dataset.label === "Previsão"
                ) {
                  return `${ctx.dataset.label}: ${ctx.raw}%`;
                }
                return null;
              },
            },
          },
        },
      },
    });

    // ========== RANKING ==========
    const tbody = document.getElementById("rankingProblemas");
    tbody.innerHTML = "";
    if (!data.ranking || data.ranking.length === 0) {
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

    // ========== GRÁFICO POR SALA ==========
    if (graficoSalas) graficoSalas.destroy();
    const ctxSalas = document.getElementById("graficoSalas").getContext("2d");
    graficoSalas = new Chart(ctxSalas, {
      type: "bar",
      data: {
        labels: data.salas?.labels || [],
        datasets: [
          {
            label: "Taxa de Problemas (%)",
            data: data.salas?.valores || [],
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

    // ========== DATA PARA IMPRESSÃO ==========
    const mainElement = document.querySelector("main.container");
    if (mainElement) {
      mainElement.setAttribute(
        "data-print-date",
        new Date().toLocaleString("pt-BR"),
      );
    }
  } catch (error) {
    console.error("Erro ao carregar dados:", error);
    alert(
      "Erro ao carregar os dados da análise. Verifique a conexão com o servidor.",
    );
  }
}

function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// =====================================================
// FUNÇÃO PARA GERAR PDF VIA IMPRESSÃO
// =====================================================
async function gerarPDF() {
  const btn = document.getElementById("btnExportarPDF");
  if (!btn) return;

  const originalText = btn.innerHTML;
  btn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Preparando impressão...';
  btn.disabled = true;

  try {
    // Pequeno delay para garantir que os gráficos estejam completamente renderizados
    await new Promise((resolve) => setTimeout(resolve, 500));

    // Força a re-renderização dos gráficos (opcional, mas ajuda)
    if (graficoEvolucao) graficoEvolucao.update();
    if (graficoPrevisao) graficoPrevisao.update();
    if (graficoSalas) graficoSalas.update();

    // Pequeno delay adicional para o update
    await new Promise((resolve) => setTimeout(resolve, 200));

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

  document
    .getElementById("btnAtualizar")
    ?.addEventListener("click", carregarDados);
  document
    .getElementById("btnExportarPDF")
    ?.addEventListener("click", gerarPDF);

  const filtroPeriodo = document.getElementById("filtroPeriodo");
  if (filtroPeriodo) {
    filtroPeriodo.addEventListener("change", (e) => {
      const periodo = e.target.value;
      const anoSelect = document.getElementById("filtroAno");
      if (periodo === "anual") {
        anoSelect.disabled = false;
      } else {
        anoSelect.disabled = true;
        anoSelect.value = new Date().getFullYear();
      }
    });
    filtroPeriodo.dispatchEvent(new Event("change"));
  }

  const btnDetalhes = document.getElementById("btnDetalhesComponentes");
  if (btnDetalhes) {
    btnDetalhes.addEventListener("click", () => {
      const div = document.getElementById("componentesDetalhes");
      if (div) {
        div.style.display = div.style.display === "none" ? "block" : "none";
      }
    });
  }
});
