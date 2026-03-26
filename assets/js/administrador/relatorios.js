async function carregarRelatorios(data) {
  try {
    const response = await fetch(`api/listar_relatorios.php?data=${data}`);
    const relatorios = await response.json();

    const container = document.getElementById("relatoriosContainer");

    if (relatorios.erro) {
      container.innerHTML = `<div class="alert alert-danger">${relatorios.erro}</div>`;
      return;
    }

    if (relatorios.length === 0) {
      container.innerHTML =
        '<div class="alert alert-info">Nenhum relatório encontrado para esta data.</div>';
      return;
    }

    let html = '<table class="table table-bordered table-hover">';
    html += `
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
        </tr>
      </thead>
      <tbody>
    `;

    relatorios.forEach((r) => {
      const periodoTexto =
        r.periodo === "manha"
          ? "Manhã"
          : r.periodo === "tarde"
            ? "Tarde"
            : "Noite";

      const momentoTexto = r.momento === "inicio" ? "Início" : "Fim";

      // ================= IMAGENS =================
      let imagensHtml = "Sem imagem";

      if (r.imagens && r.imagens !== "null") {
        try {
          const imgs = JSON.parse(r.imagens);

          if (Array.isArray(imgs) && imgs.length > 0) {
            imagensHtml = imgs
              .map((img) => {
                const caminho = "../" + img;

                return `
        <img 
          src="${caminho}" 
          width="80" 
          class="me-1 rounded border img-thumb"
          style="cursor:pointer"
          onclick='abrirModalImagens(${JSON.stringify(imgs)})'
          onerror="this.style.display='none'"
        >
      `;
              })
              .join("");
          }
        } catch (e) {
          imagensHtml = "Erro ao carregar";
        }
      }
      html += `<tr>`;
      html += `<td>${r.id}</td>`;
      html += `<td>${r.inspecao_id}</td>`;
      html += `<td>${escapeHtml(r.sala)}</td>`;
      html += `<td>${periodoTexto}</td>`;
      html += `<td>${momentoTexto}</td>`;
      html += `<td>${escapeHtml(r.observacoes || "Nenhuma observação")}</td>`;
      html += `<td>${imagensHtml}</td>`;
      html += `<td>${new Date(r.data_geracao).toLocaleString("pt-BR")}</td>`;
      html += `</tr>`;
    });

    html += "</tbody></table>";
    container.innerHTML = html;
  } catch (error) {
    console.error(error);
    document.getElementById("relatoriosContainer").innerHTML =
      '<div class="alert alert-danger">Erro ao carregar relatórios.</div>';
  }
}

// ================= SEGURANÇA =================
function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// ================= FILTRO =================
document.getElementById("btnFiltrar").addEventListener("click", () => {
  const data = document.getElementById("filtroData").value;
  if (data) carregarRelatorios(data);
});

// ================= INICIALIZAÇÃO =================
const hoje = new Date().toISOString().split("T")[0];
document.getElementById("filtroData").value = hoje;
carregarRelatorios(hoje);

function abrirModalImagens(listaImagens) {
  const container = document.getElementById("modalImagensContainer");

  container.innerHTML = "";

  listaImagens.forEach((img) => {
    const caminho = "../" + img;

    container.innerHTML += `
      <img 
        src="${caminho}" 
        class="img-fluid rounded border"
        style="max-height:400px"
        onerror="this.style.display='none'"
      >
    `;
  });

  const modal = new bootstrap.Modal(document.getElementById("modalImagens"));
  modal.show();

  document.querySelectorAll("#modalImagensContainer img").forEach((img) => {
    img.addEventListener("click", () => {
      img.classList.toggle("zoomed");
    });
  });
}
