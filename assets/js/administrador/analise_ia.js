/**
 * analise_ia.js - Interface de Análise Preditiva e Gráficos da IA
 * Gerencia a renderização de gráficos do Chart.js com gradientes e exibição de componentes Holt-Winters.
 */

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
      alert(`Erro no processamento da IA: ${data.erro}`);
      return;
    }

    // ========== ATUALIZAÇÃO DOS CARDS DE TENDÊNCIA ==========
    document.getElementById("totalInspecoes").innerHTML =
      data.total_inspecoes ?? 0;
    document.getElementById("taxaProblemas").innerHTML =
      (data.taxa_media_problemas ?? 0) + "%";
    document.getElementById("previsaoProximo").innerHTML =
      (data.previsao_proximo ?? 0) + "%";

    // ========== INFORMAÇÕES DO MODELO ==========
    const modeloInfoDiv = document.getElementById("infoModelo");
    if (modeloInfoDiv && data.previsao) {
      let html = `<strong>Algoritmo selecionado:</strong> ${data.previsao.modelo || "Não disponível"}`;
      if (data.previsao.mae !== null && data.previsao.mae !== undefined) {
        html += ` &nbsp;| &nbsp;<i class="bi bi-bullseye"></i> <strong>MAE (Erro Médio):</strong> ${data.previsao.mae}%`;
      }
      if (data.previsao.mape !== null && data.previsao.mape !== undefined) {
        html += ` &nbsp;| &nbsp;<i class="bi bi-graph-up"></i> <strong>MAPE:</strong> ${data.previsao.mape}%`;
      }
      modeloInfoDiv.innerHTML = html;
    }

    // ========== COMPONENTES E DETALHES DE EXPLICABILIDADE ==========
    const compDiv = document.getElementById("componentesDetalhes");
    if (compDiv) {
      const tipoModelo = data.previsao?.tipo_modelo;
      const componentes = data.previsao?.componentes;
      const mae = data.previsao?.mae;
      const mape = data.previsao?.mape;
      const rmse = data.previsao?.rmse;
      const conf80 = data.previsao?.confidence80;
      const conf95 = data.previsao?.confidence95;

      // Caso 1: Holt-Winters Otimizado
      if (
        tipoModelo === "holt_winters" &&
        componentes &&
        componentes.level &&
        componentes.level.length > 0
      ) {
        const lastLevel = componentes.level[componentes.level.length - 1];
        const lastTrend = componentes.trend[componentes.trend.length - 1];

        let trendText = "",
          trendClass = "",
          trendDescription = "";
        if (lastTrend > 0.1) {
          trendText = `<i class="bi bi-arrow-up-right text-danger"></i> +${lastTrend.toFixed(2)}% ao mês (Alta)`;
          trendClass = "text-danger fw-bold";
          trendDescription =
            "A taxa de não conformidades está aumentando lentamente. É recomendável planejar auditorias mais frequentes neste ambiente para reverter a tendência.";
        } else if (lastTrend < -0.1) {
          trendText = `<i class="bi bi-arrow-down-left text-success"></i> ${lastTrend.toFixed(2)}% ao mês (Queda)`;
          trendClass = "text-success fw-bold";
          trendDescription =
            "A taxa de ocorrências está diminuindo gradualmente, demonstrando que as ações de melhoria estão surtindo efeito positivo.";
        } else {
          trendText = `<i class="bi bi-dash-lg text-secondary"></i> Estável`;
          trendClass = "text-secondary fw-bold";
          trendDescription =
            "A incidência de falhas encontra-se estagnada, sem tendência significativa de alta ou redução para as próximas semanas.";
        }

        let levelInterpretation = "";
        let levelClass = "";
        if (lastLevel < 15) {
          levelInterpretation = "Excelente (Conformidade quase absoluta)";
          levelClass = "text-success";
        } else if (lastLevel < 35) {
          levelInterpretation = "Bom (Ocorrências isoladas)";
          levelClass = "text-success";
        } else if (lastLevel < 55) {
          levelInterpretation = "Atenção moderada (Risco médio de incidentes)";
          levelClass = "text-warning";
        } else {
          levelInterpretation =
            "Crítico (Alta incidência de não conformidades)";
          levelClass = "text-danger";
        }

        let maeMessage = "";
        if (mae !== null && mae !== undefined) {
          let maeInterpretation =
            mae < 5 ? "Excelente" : mae < 10 ? "Boa" : "Aceitável";
          maeMessage = `
            <div class="mt-3 p-2 bg-dark rounded border border-secondary text-light small d-flex align-items-center gap-2">
              <i class="bi bi-cpu text-info"></i>
              <span><strong>Métrica de Validação:</strong> Erro Médio Absoluto (MAE) de <strong>${mae}%</strong> nas previsões retroativas. Nível de precisão: <strong class="text-info">${maeInterpretation}</strong>.</span>
            </div>`;
        }

        compDiv.innerHTML = `
          <div class="p-3">
            <div class="row g-4 align-items-stretch">
              <div class="col-md-6">
                <div class="bg-dark p-3 rounded h-100 border border-secondary">
                  <div class="text-secondary small uppercase tracking-wider mb-1"><i class="bi bi-layers me-1 text-primary"></i> Nível Suavizado de Base</div>
                  <div class="fs-3 fw-bold text-white">${lastLevel.toFixed(2)}%</div>
                  <div class="small ${levelClass} fw-bold mt-1">${levelInterpretation}</div>
                  <p class="small text-muted mb-0 mt-2">Valor médio purificado da taxa de falhas, após remoção matemática de ruídos e oscilações pontuais.</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="bg-dark p-3 rounded h-100 border border-secondary">
                  <div class="text-secondary small uppercase tracking-wider mb-1"><i class="bi bi-graph-up me-1 text-warning"></i> Vetor de Tendência</div>
                  <div class="fs-4 ${trendClass}">${trendText}</div>
                  <p class="small text-muted mb-0 mt-2">${trendDescription}</p>
                </div>
              </div>
            </div>
            ${maeMessage}
          </div>
        `;
      }
      // Caso 2: Modelos Alternativos (SES / Holt Linear / Média Ponderada)
      else if (
        tipoModelo === "holt" ||
        tipoModelo === "media_movel" ||
        tipoModelo === "media"
      ) {
        let metricsHtml = "";
        if (mae !== null)
          metricsHtml += `<div><i class="bi bi-calculator me-1"></i> MAE: ${mae}%</div>`;
        if (mape !== null)
          metricsHtml += `<div><i class="bi bi-percent me-1"></i> MAPE: ${mape}%</div>`;
        if (rmse !== null)
          metricsHtml += `<div><i class="bi bi-shield-exclamation me-1"></i> RMSE (Desvio): ${rmse}%</div>`;

        let confidenceHtml = "";
        if (conf80 && conf80.lower && conf80.upper) {
          confidenceHtml = `
            <div class="mt-3 p-3 bg-dark border border-secondary rounded">
              <div class="text-secondary small mb-2"><i class="bi bi-shield-shaded me-1 text-primary"></i> Bandas de Probabilidade (Próxima Previsão)</div>
              <div><span class="badge bg-primary">80% de chance</span> de oscilar entre <strong>${conf80.lower[0]}%</strong> e <strong>${conf80.upper[0]}%</strong></div>
              <div class="mt-1"><span class="badge bg-secondary">95% de chance</span> de oscilar entre <strong>${conf95.lower[0]}%</strong> e <strong>${conf95.upper[0]}%</strong></div>
            </div>
          `;
        }

        compDiv.innerHTML = `
          <div class="p-3">
            <div class="alert alert-dark border border-secondary mb-3 text-light">
              <i class="bi bi-cpu-fill text-primary"></i> <strong>Algoritmo Adaptativo:</strong> Devido a dados insuficientes para detecção de ciclo sazonal, o sistema chaveou automaticamente para o modelo <strong>${data.previsao.modelo}</strong>.
            </div>
            ${metricsHtml ? `<div class="mb-3 text-light"><strong>Métricas Estatísticas:</strong>${metricsHtml}</div>` : ""}
            ${confidenceHtml}
          </div>
        `;
      } else {
        compDiv.innerHTML = `
          <div class="p-3 text-muted">
            <i class="bi bi-info-circle-fill"></i> Dados insuficientes para gerar componentes do modelo preditivo.
            Continue realizando as inspeções periódicas para preencher o histórico de vistorias.
          </div>
        `;
      }
    }

    // ========== CONFIGURAÇÃO DE GRADIENTES PARA OS GRÁFICOS ==========
    const drawGradient = (ctx, colorStart, colorEnd) => {
      const gradient = ctx.createLinearGradient(0, 0, 0, 300);
      gradient.addColorStop(0, colorStart);
      gradient.addColorStop(1, colorEnd);
      return gradient;
    };

    // ========== GRÁFICO 1: EVOLUÇÃO TEMPORAL (ÚLTIMOS 12 MESES) ==========
    if (graficoEvolucao) graficoEvolucao.destroy();
    const ctxEvo = document.getElementById("graficoEvolucao").getContext("2d");

    graficoEvolucao = new Chart(ctxEvo, {
      type: "line",
      data: {
        labels: data.evolucao?.labels || [],
        datasets: [
          {
            label: "Taxa de Ocorrências (%)",
            data: data.evolucao?.valores || [],
            borderColor: "#2563eb",
            backgroundColor: drawGradient(
              ctxEvo,
              "rgba(37, 99, 235, 0.2)",
              "rgba(37, 99, 235, 0.0)",
            ),
            tension: 0.35,
            fill: true,
            borderWidth: 3,
            pointBackgroundColor: "#2563eb",
            pointBorderColor: "#ffffff",
            pointHoverRadius: 7,
            pointHoverBackgroundColor: "#1d4ed8",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          tooltip: {
            backgroundColor: "#0f172a",
            titleFont: { weight: "bold" },
            callbacks: { label: (ctx) => ` Taxa: ${ctx.raw}%` },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: { callback: (val) => `${val}%` },
          },
        },
      },
    });

    // ========== GRÁFICO 2: GRÁFICO PREDITIVO COM INTERVALOS DE CONFIANÇA ==========
    if (graficoPrevisao) graficoPrevisao.destroy();
    const ctxPrev = document.getElementById("graficoPrevisao").getContext("2d");

    const datasets = [
      {
        label: "Histórico Real",
        data: data.previsao?.historico || [],
        borderColor: "#059669",
        backgroundColor: "rgba(5, 150, 105, 0.05)",
        tension: 0.35,
        fill: false,
        borderWidth: 3,
        pointBackgroundColor: "#059669",
        pointBorderColor: "#ffffff",
      },
      {
        label: "Previsão IA",
        data: data.previsao?.previsao || [],
        borderColor: "#e11d48",
        borderDash: [6, 4],
        tension: 0.35,
        fill: false,
        borderWidth: 3,
        pointBackgroundColor: "#e11d48",
        pointBorderColor: "#ffffff",
        pointHoverRadius: 7,
      },
    ];

    const previsaoArray = data.previsao?.previsao || [];
    const hasForecast = previsaoArray.some((v) => v !== null);
    const conf80 = data.previsao?.confidence80;
    const conf95 = data.previsao?.confidence95;

    // Adiciona bandas de confiança se houver projeções Holt-Winters válidas
    if (hasForecast && conf80 && conf80.lower && conf80.upper) {
      const firstForecastIdx = previsaoArray.findIndex((v) => v !== null);
      if (firstForecastIdx !== -1) {
        const upper80 = previsaoArray.map((v, idx) =>
          idx >= firstForecastIdx ? conf80.upper[idx - firstForecastIdx] : null,
        );
        const lower80 = previsaoArray.map((v, idx) =>
          idx >= firstForecastIdx ? conf80.lower[idx - firstForecastIdx] : null,
        );

        datasets.push({
          label: "Banda Confiança 80%",
          data: upper80,
          borderColor: "rgba(225, 29, 72, 0)",
          backgroundColor: "rgba(225, 29, 72, 0.12)",
          fill: "+1",
          pointRadius: 0,
          showLine: false,
        });
        datasets.push({
          label: "Banda Confiança 80% (inferior)",
          data: lower80,
          borderColor: "rgba(225, 29, 72, 0)",
          backgroundColor: "rgba(225, 29, 72, 0.12)",
          fill: "+2",
          pointRadius: 0,
          showLine: false,
        });
      }
    }

    if (hasForecast && conf95 && conf95.lower && conf95.upper) {
      const firstForecastIdx = previsaoArray.findIndex((v) => v !== null);
      if (firstForecastIdx !== -1) {
        const upper95 = previsaoArray.map((v, idx) =>
          idx >= firstForecastIdx ? conf95.upper[idx - firstForecastIdx] : null,
        );
        const lower95 = previsaoArray.map((v, idx) =>
          idx >= firstForecastIdx ? conf95.lower[idx - firstForecastIdx] : null,
        );

        datasets.push({
          label: "Banda Confiança 95%",
          data: upper95,
          borderColor: "rgba(225, 29, 72, 0)",
          backgroundColor: "rgba(225, 29, 72, 0.05)",
          fill: "+1",
          pointRadius: 0,
          showLine: false,
        });
        datasets.push({
          label: "Banda Confiança 95% (inferior)",
          data: lower95,
          borderColor: "rgba(225, 29, 72, 0)",
          backgroundColor: "rgba(225, 29, 72, 0.05)",
          fill: "+2",
          pointRadius: 0,
          showLine: false,
        });
      }
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
            backgroundColor: "#0f172a",
            titleFont: { weight: "bold" },
            callbacks: {
              label: (ctx) => {
                if (
                  ctx.dataset.label === "Histórico Real" ||
                  ctx.dataset.label === "Previsão IA"
                ) {
                  return ` ${ctx.dataset.label}: ${ctx.raw}%`;
                }
                return null;
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: { callback: (val) => `${val}%` },
          },
        },
      },
    });

    // ========== COMPILAR RANKING DE ITENS CRÍTICOS (TABLE) ==========
    const tbody = document.getElementById("rankingProblemas");
    tbody.innerHTML = "";
    if (!data.ranking || data.ranking.length === 0) {
      tbody.innerHTML =
        '<tr><td colspan="3" class="text-center text-secondary py-3">Nenhuma ocorrência registrada no intervalo</td></tr>';
    } else {
      data.ranking.forEach((item) => {
        const row = document.createElement("tr");

        // Define classe de badge dinâmico de criticidade
        const classeBadge =
          item.incidencia < 15
            ? "bg-success"
            : item.incidencia < 35
              ? "bg-warning text-dark"
              : "bg-danger";

        row.innerHTML = `
          <td data-label="Item de Inspeção" class="fw-bold text-dark">${escapeHtml(item.item)}</td>
          <td data-label="Incidência de Erros"><span class="badge ${classeBadge} rounded-pill px-3 py-1">${item.incidencia}%</span></td>
          <td data-label="Total de Ocorrências">${item.ocorrencias} ocorrências</td>
        `;
        tbody.appendChild(row);
      });
    }

    // ========== GRÁFICO 3: COMPARATIVO HISTÓRICO POR AMBIENTE ==========
    if (graficoSalas) graficoSalas.destroy();
    const ctxSalas = document.getElementById("graficoSalas").getContext("2d");

    graficoSalas = new Chart(ctxSalas, {
      type: "bar",
      data: {
        labels: data.salas?.labels || [],
        datasets: [
          {
            label: "Taxa de Ocorrências (%)",
            data: data.salas?.valores || [],
            backgroundColor: drawGradient(
              ctxSalas,
              "rgba(99, 102, 241, 0.75)",
              "rgba(99, 102, 241, 0.35)",
            ),
            borderColor: "#6366f1",
            borderWidth: 2,
            borderRadius: 8,
            hoverBackgroundColor: "rgba(99, 102, 241, 0.95)",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          tooltip: {
            backgroundColor: "#0f172a",
            callbacks: { label: (ctx) => ` Taxa Média: ${ctx.raw}%` },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: { callback: (val) => `${val}%` },
          },
        },
      },
    });

    // Metadados temporais de impressão para auditoria PDF
    const mainElement = document.querySelector("main.container");
    if (mainElement) {
      mainElement.setAttribute(
        "data-print-date",
        new Date().toLocaleString("pt-BR"),
      );
    }
  } catch (error) {
    console.error("Erro ao sincronizar dados da IA:", error);
    alert(
      "Não foi possível conectar-se ao servidor de inteligência preditiva. Verifique os dados históricos e tente novamente.",
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
// ROTINA DE IMPRESSÃO / EXPORTAÇÃO PDF (html2pdf)
// =====================================================
async function gerarPDF() {
  const btn = document.getElementById("btnExportarPDF");
  if (!btn) return;

  const originalText = btn.innerHTML;
  btn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Gerando relatório...';
  btn.disabled = true;

  try {
    // Aguarda conclusão das renderizações ativas de animação
    await new Promise((resolve) => setTimeout(resolve, 500));
    if (graficoEvolucao) graficoEvolucao.update();
    if (graficoPrevisao) graficoPrevisao.update();
    if (graficoSalas) graficoSalas.update();
    await new Promise((resolve) => setTimeout(resolve, 200));

    // Dispara a visualização nativa de impressão com formatação robusta de CSS de impressão
    window.print();
  } catch (err) {
    console.error("Falha na renderização de impressão física:", err);
    alert("Ocorreu uma falha ao renderizar a impressão. Tente novamente.");
  } finally {
    btn.innerHTML = originalText;
    btn.disabled = false;
  }
}

// =====================================================
// INICIALIZAÇÃO DE EVENTOS
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
