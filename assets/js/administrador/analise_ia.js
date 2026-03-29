// =====================================================
// ANALISE IA - KAMISHIBAI SENAI
// Versão com impressão otimizada e recarga no período
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
        const lastLevel =
          data.previsao.componentes.level[
            data.previsao.componentes.level.length - 1
          ];
        const lastTrend =
          data.previsao.componentes.trend[
            data.previsao.componentes.trend.length - 1
          ];

        // Formata a tendência com texto amigável
        let trendText = "";
        let trendIcon = "";
        let trendDescription = "";

        if (lastTrend > 0) {
          trendIcon = '<i class="bi bi-arrow-up-short"></i>';
          trendText = `<span class="text-success">${trendIcon} +${lastTrend.toFixed(2)}% (aumento gradual)</span>`;
          trendDescription = `Isso significa que a taxa de problemas está aumentando lentamente a cada mês. Se essa tendência continuar, a situação pode piorar no futuro.`;
        } else if (lastTrend < 0) {
          trendIcon = '<i class="bi bi-arrow-down-short"></i>';
          trendText = `<span class="text-danger">${trendIcon} ${lastTrend.toFixed(2)}% (redução gradual)</span>`;
          trendDescription = `Isso indica que a taxa de problemas está diminuindo lentamente – um sinal positivo de melhoria.`;
        } else {
          trendIcon = '<i class="bi bi-dash"></i>';
          trendText = `<span class="text-secondary">${trendIcon} estável (sem tendência significativa)</span>`;
          trendDescription = `A taxa de problemas não mostra aumento nem redução significativa nos últimos meses. Está relativamente estável.`;
        }

        // Interpretação do nível
        let levelInterpretation = "";
        if (lastLevel < 20) {
          levelInterpretation =
            "Muito baixo – a maioria dos itens está em conformidade.";
        } else if (lastLevel < 40) {
          levelInterpretation = "Baixo – poucos itens apresentam problemas.";
        } else if (lastLevel < 60) {
          levelInterpretation =
            "Moderado – metade dos itens tem algum problema; atenção necessária.";
        } else if (lastLevel < 80) {
          levelInterpretation =
            "Alto – muitos itens apresentam falhas; ação urgente recomendada.";
        } else {
          levelInterpretation =
            "Muito alto – a situação é crítica; intervenção imediata necessária.";
        }

        // Mensagem sobre o MAE (se disponível)
        let maeMessage = "";
        if (data.previsao.mae !== null && data.previsao.mae !== undefined) {
          let maeInterpretation = "";
          if (data.previsao.mae < 5) {
            maeInterpretation = "muito boa (erro pequeno)";
          } else if (data.previsao.mae < 10) {
            maeInterpretation = "boa";
          } else if (data.previsao.mae < 20) {
            maeInterpretation = "razoável";
          } else {
            maeInterpretation = "alta (previsões menos confiáveis)";
          }
          maeMessage = `<div class="mt-2 small text-muted"><i class="bi bi-check-circle"></i> <strong>Precisão do modelo:</strong> o erro médio absoluto (MAE) é de ${data.previsao.mae}%, o que indica uma precisão ${maeInterpretation} para as previsões.</div>`;
        } else {
          maeMessage = `<div class="mt-2 small text-muted"><i class="bi bi-info-circle"></i> Não há dados suficientes para calcular a precisão das previsões (MAE).</div>`;
        }

        compDiv.innerHTML = `
          <div class="mt-2 p-3 bg-light rounded shadow-sm">
            <div class="row align-items-center">
              <div class="col-md-6 mb-3 mb-md-0">
                <div class="d-flex align-items-center">
                  <i class="bi bi-graph-up fs-3 me-2 text-primary"></i>
                  <div>
                    <div class="text-muted small">📊 Nível atual (suavizado)</div>
                    <div class="fs-2 fw-bold">${lastLevel.toFixed(2)}%</div>
                    <div class="small text-muted">${levelInterpretation}</div>
                    <div class="small text-muted">Valor médio da taxa de problemas, ajustado pela suavização exponencial.</div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="d-flex align-items-center">
                  <i class="bi bi-arrow-left-right fs-3 me-2 text-warning"></i>
                  <div>
                    <div class="text-muted small">📈 Tendência atual</div>
                    <div class="fs-5">${trendText}</div>
                    <div class="small text-muted">${trendDescription}</div>
                    <div class="small text-muted">Direção e magnitude da mudança ao longo do tempo.</div>
                  </div>
                </div>
              </div>
            </div>
            <hr class="my-2">
            <div class="small text-muted">
              <i class="bi bi-info-circle"></i> Os valores são os últimos calculados pelo modelo <strong>Holt‑Winters</strong>. 
              O nível representa a componente de base, a tendência indica se a taxa está aumentando ou diminuindo.
            </div>
            ${maeMessage}
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
    await new Promise((resolve) => setTimeout(resolve, 500));
    if (graficoEvolucao) graficoEvolucao.update();
    if (graficoPrevisao) graficoPrevisao.update();
    if (graficoSalas) graficoSalas.update();
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
      // Recarrega os dados ao mudar o período
      carregarDados();
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
