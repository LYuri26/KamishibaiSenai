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
    html +=
      "<thead><tr><th>ID Relatório</th><th>ID Inspeção</th><th>Sala</th><th>Período</th><th>Momento</th><th>Observações</th><th>Data Geração</th></tr></thead><tbody>";

    relatorios.forEach((r) => {
      const periodoTexto =
        r.periodo === "manha"
          ? "Manhã"
          : r.periodo === "tarde"
            ? "Tarde"
            : "Noite";
      const momentoTexto = r.momento === "inicio" ? "Início" : "Fim";
      html += `<tr>`;
      html += `<td>${r.id}</td>`;
      html += `<td>${r.inspecao_id}</td>`;
      html += `<td>${escapeHtml(r.sala)}</td>`;
      html += `<td>${periodoTexto}</td>`;
      html += `<td>${momentoTexto}</td>`;
      html += `<td>${escapeHtml(r.observacoes || "Nenhuma observação")}</td>`;
      html += `<td>${new Date(r.data_geracao).toLocaleString("pt-BR")}</td>`;
      html += `</tr>`;
    });

    html += "</tbody></table>";
    container.innerHTML = html;
  } catch (error) {
    document.getElementById("relatoriosContainer").innerHTML =
      '<div class="alert alert-danger">Erro ao carregar relatórios.</div>';
    console.error(error);
  }
}

function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

document.getElementById("btnFiltrar").addEventListener("click", () => {
  const data = document.getElementById("filtroData").value;
  if (data) carregarRelatorios(data);
});

// Data atual no input
const hoje = new Date().toISOString().split("T")[0];
document.getElementById("filtroData").value = hoje;
carregarRelatorios(hoje);
