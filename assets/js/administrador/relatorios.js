/**
 * relatorios.js - Controle de Consolidação de Relatórios
 * Gerencia a filtragem por período/salas e aciona modais de evidências.
 */

async function carregarRelatorios(dataInicio, dataFim, sala = "todas") {
  const container = document.getElementById("relatoriosContainer");
  container.innerHTML = `
    <div class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2 text-secondary">Buscando histórico de vistorias...</p>
    </div>
  `;

  try {
    const response = await fetch(
      `api/listar_relatorios.php?data_inicio=${dataInicio}&data_fim=${dataFim}&sala=${sala}`,
    );
    const relatorios = await response.json();

    if (relatorios.erro) {
      container.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-octagon me-1"></i>Erro: ${relatorios.erro}</div>`;
      return;
    }

    if (relatorios.length === 0) {
      container.innerHTML = `
        <div class="alert alert-info text-center py-4">
          <i class="bi bi-info-circle me-1"></i>Nenhum relatório encontrado para o período e critérios selecionados.
        </div>`;
      return;
    }

    // Início da tabela responsiva (com wrapper integrado ao CSS)
    let html = `
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>ID Relatório</th>
              <th>ID Inspeção</th>
              <th>Sala</th>
              <th>Período</th>
              <th>Momento</th>
              <th>Observações</th>
              <th>Imagens</th>
              <th>Data Geração</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
    `;

    relatorios.forEach((r) => {
      const pTexto =
        r.periodo === "manha"
          ? "Manhã"
          : r.periodo === "tarde"
            ? "Tarde"
            : "Noite";
      const mTexto = r.momento === "inicio" ? "Início" : "Fim";

      // ================= IMAGENS (EVIDÊNCIAS) =================
      let imagensHtml = `<span class="text-muted small">Sem fotos</span>`;

      if (r.imagens && r.imagens !== "null") {
        try {
          const imgs = JSON.parse(r.imagens);

          if (Array.isArray(imgs) && imgs.length > 0) {
            imagensHtml = `
              <button class="btn btn-ver-imagens" onclick='abrirModalImagens(${JSON.stringify(imgs)})'>
                <i class="bi bi-images"></i> Ver fotos (${imgs.length})
              </button>
            `;
          }
        } catch (e) {
          imagensHtml = `<span class="text-danger small">Erro de mídia</span>`;
        }
      }

      // Constrói cada linha incluindo 'data-label' para responsividade no mobile
      html += `
        <tr>
          <td data-label="ID Relatório" class="fw-bold">#${r.id}</td>
          <td data-label="ID Inspeção">${r.inspecao_id}</td>
          <td data-label="Sala"><span class="badge bg-secondary rounded-pill px-3">${escapeHtml(r.sala)}</span></td>
          <td data-label="Período">${pTexto}</td>
          <td data-label="Momento">${mTexto}</td>
          <td data-label="Observações" class="text-wrap" style="max-width: 250px;">${escapeHtml(r.observacoes || "Sem ocorrências.")}</td>
          <td data-label="Imagens">${imagensHtml}</td>
          <td data-label="Data Geração">${new Date(r.data_geracao).toLocaleString("pt-BR")}</td>
          <td data-label="Ações">
            <a href="visualizar.html?id=${r.inspecao_id}&sala=${r.sala}" class="btn btn-visualizar-detalhes">
              <i class="bi bi-eye"></i> Detalhes
            </a>
          </td>
        </tr>
      `;
    });

    html += `</tbody></table></div>`;
    container.innerHTML = html;
  } catch (error) {
    console.error(error);
    container.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-octagon me-1"></i>Erro ao carregar dados do servidor.</div>`;
  }
}

// ================= SANITIZAÇÃO CONTRA XSS =================
function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// ================= CONTROLE DE FILTROS =================
document.getElementById("btnFiltrar").addEventListener("click", () => {
  const inicio = document.getElementById("filtroDataInicio").value;
  const fim = document.getElementById("filtroDataFim").value;
  const sala = document.getElementById("filtroSala").value;

  if (!inicio || !fim) {
    alert("Por favor, preencha as datas inicial e final para filtrar.");
    return;
  }

  if (new Date(inicio) > new Date(fim)) {
    alert("A data inicial não pode ser maior do que a data final.");
    return;
  }

  carregarRelatorios(inicio, fim, sala);
});

// ================= INICIALIZAÇÃO DE PERÍODO (MENSAL PADRÃO) =================
document.addEventListener("DOMContentLoaded", () => {
  const hojeObj = new Date();

  // Define como período inicial padrão o primeiro dia do mês corrente (2026-07-01)
  const primeiroDiaObj = new Date(hojeObj.getFullYear(), hojeObj.getMonth(), 1);

  const formatar = (d) => d.toISOString().split("T")[0];

  const inicioPadrao = formatar(primeiroDiaObj);
  const fimPadrao = formatar(hojeObj);

  document.getElementById("filtroDataInicio").value = inicioPadrao;
  document.getElementById("filtroDataFim").value = fimPadrao;

  carregarRelatorios(inicioPadrao, fimPadrao, "todas");
});

// ================= GALERIA DE FOTOS (MODAL) =================
function abrirModalImagens(listaImagens) {
  const container = document.getElementById("modalImagensContainer");
  container.innerHTML = "";

  listaImagens.forEach((img) => {
    const caminho = "../" + img;
    container.innerHTML += `
      <img
        src="${caminho}"
        class="img-fluid rounded border"
        alt="Foto de Evidência"
        onerror="this.style.display='none'"
      >
    `;
  });

  const modal = new bootstrap.Modal(document.getElementById("modalImagens"));
  modal.show();

  // Controle de Zoom ao clicar nas imagens da galeria
  document.querySelectorAll("#modalImagensContainer img").forEach((img) => {
    img.addEventListener("click", () => {
      img.classList.toggle("zoomed");
    });
  });
}
