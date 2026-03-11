async function carregarInspecoes() {
  try {
    const response = await fetch("api/listar_inspecoes.php");
    const data = await response.json();
    const tbody = document.getElementById("listaInspecoes");
    tbody.innerHTML = "";

    if (data.erro) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-danger">${data.erro}</td></tr>`;
      return;
    }

    if (data.length === 0) {
      tbody.innerHTML =
        '<tr><td colspan="6" class="text-center">Nenhuma inspeção encontrada.</td></tr>';
      return;
    }

    data.forEach((inspecao) => {
      const row = document.createElement("tr");
      const momentoTexto = inspecao.momento === "inicio" ? "Início" : "Fim";
      row.innerHTML = `
                <td>${inspecao.id}</td>
                <td>${escapeHtml(inspecao.nome)}</td>
                <td>${new Date(inspecao.data).toLocaleString("pt-BR")}</td>
                <td>${momentoTexto}</td>
                <td class="obs-preview">${escapeHtml(inspecao.observacoes?.substring(0, 100) || "")}...</td>
                <td><a href="visualizar.html?id=${inspecao.id}" class="btn btn-sm btn-info"><i class="fas fa-eye me-1"></i>Detalhes</a></td>
            `;
      tbody.appendChild(row);
    });
  } catch (error) {
    document.getElementById("listaInspecoes").innerHTML =
      `<tr><td colspan="6" class="text-danger">Erro ao carregar dados.</td></tr>`;
    console.error(error);
  }
}

async function carregarAlertas() {
  try {
    const response = await fetch("api/verificar_alertas.php");
    const alertas = await response.json();
    const container = document.getElementById("alertasContainer");
    if (!container) return;

    if (alertas.length === 0) {
      container.innerHTML =
        '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Todos os períodos de hoje foram inspecionados.</div>';
    } else {
      let html =
        '<div class="alert alert-warning"><strong><i class="fas fa-exclamation-triangle me-2"></i>Atenção!</strong> Períodos sem inspeção hoje:<ul>';
      alertas.forEach((a) => {
        html += `<li>${a.mensagem}</li>`;
      });
      html += "</ul></div>";
      container.innerHTML = html;
    }
  } catch (error) {
    console.error("Erro ao carregar alertas:", error);
  }
}

function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

carregarInspecoes();
carregarAlertas();
