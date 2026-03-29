async function carregarInspecoes() {
  const tbody = document.getElementById("listaInspecoes");
  tbody.innerHTML =
    '发展<td colspan="7" class="text-center"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Carregando...</td></tr>';

  try {
    const response = await fetch("api/listar_inspecoes.php");
    if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
    const data = await response.json();

    tbody.innerHTML = "";

    if (data.erro) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-danger text-center">${data.erro}</td></tr>`;
      return;
    }

    if (data.length === 0) {
      tbody.innerHTML =
        '<tr><td colspan="7" class="text-center text-secondary">Nenhuma inspeção encontrada.</td></tr>';
      return;
    }

    // Obter data atual (ano e mês)
    const hoje = new Date();
    const anoAtual = hoje.getFullYear();
    const mesAtual = hoje.getMonth() + 1; // meses em JS são 0-index

    // Filtrar inspeções do mês atual
    const inspecoesFiltradas = data.filter((inspecao) => {
      const dataInspecao = new Date(inspecao.data);
      return (
        dataInspecao.getFullYear() === anoAtual &&
        dataInspecao.getMonth() + 1 === mesAtual
      );
    });

    // Limitar aos 20 primeiros
    const inspecoesExibir = inspecoesFiltradas.slice(0, 20);

    if (inspecoesExibir.length === 0) {
      tbody.innerHTML =
        '<tr><td colspan="7" class="text-center text-secondary">Nenhuma inspeção registrada neste mês.</td></tr>';
      return;
    }

    inspecoesExibir.forEach((inspecao) => {
      const row = document.createElement("tr");
      const momentoTexto = inspecao.momento === "inicio" ? "Início" : "Fim";
      const dataFormatada = new Date(inspecao.data).toLocaleString("pt-BR");
      const obsPreview = inspecao.observacoes
        ? inspecao.observacoes.substring(0, 100) +
          (inspecao.observacoes.length > 100 ? "…" : "")
        : "";

      // Atributos data-label para responsividade mobile
      row.innerHTML = `
        <td data-label="ID">${inspecao.id}</td>
        <td data-label="Instrutor">${escapeHtml(inspecao.nome)}</td>
        <td data-label="Data/Hora">${dataFormatada}</td>
        <td data-label="Momento">${momentoTexto}</td>
        <td data-label="Sala">${escapeHtml(inspecao.sala)}</td>
        <td data-label="Observações" class="obs-preview">${escapeHtml(obsPreview)}</td>
        <td data-label="Ações">
          <a href="visualizar.html?id=${inspecao.id}&sala=${inspecao.sala}" class="btn btn-sm btn-info rounded-pill">
            <i class="bi bi-eye me-1"></i>Detalhes
          </a>
        </td>
      `;
      tbody.appendChild(row);
    });
  } catch (error) {
    console.error("Erro ao carregar inspeções:", error);
    tbody.innerHTML = `<tr><td colspan="7" class="text-danger text-center">Erro ao carregar dados: ${error.message}</td></tr>`;
  }
}

async function carregarAlertas() {
  const container = document.getElementById("alertasContainer");
  if (!container) return;

  try {
    const response = await fetch("api/verificar_alertas.php");
    if (!response.ok) {
      throw new Error(`Erro HTTP: ${response.status}`);
    }
    const alertas = await response.json();

    if (alertas.erro) {
      container.innerHTML = `<div class="alert alert-danger">${alertas.erro}</div>`;
      return;
    }

    if (alertas.length === 0) {
      container.innerHTML =
        '<div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>Todos os períodos de hoje foram inspecionados.</div>';
    } else {
      let html =
        '<div class="alert alert-warning"><strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Atenção!</strong> Períodos sem inspeção hoje:<ul>';
      alertas.forEach((a) => {
        html += `<li>${a.mensagem}</li>`;
      });
      html += "</ul></div>";
      container.innerHTML = html;
    }
  } catch (error) {
    console.error("Erro ao carregar alertas:", error);
    container.innerHTML = `<div class="alert alert-danger">Erro ao carregar alertas: ${error.message}</div>`;
  }
}

function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// Inicializa as funções
carregarInspecoes();
carregarAlertas();
