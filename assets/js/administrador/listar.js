async function carregarInspecoes() {
  try {
    const response = await fetch("api/listar_inspecoes.php");
    const data = await response.json();
    const tbody = document.getElementById("listaInspecoes");
    tbody.innerHTML = "";

    if (data.erro) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-danger">${data.erro}</td></tr>`;
      return;
    }

    data.forEach((inspecao) => {
      const row = document.createElement("tr");
      row.innerHTML = `
                <td>${inspecao.id}</td>
                <td>${escapeHtml(inspecao.nome)}</td>
                <td>${new Date(inspecao.data).toLocaleString("pt-BR")}</td>
                <td>${escapeHtml(inspecao.observacoes.substring(0, 100))}...</td>
                <td><a href="visualizar.html?id=${inspecao.id}" class="btn btn-sm btn-info">Detalhes</a></td>
            `;
      tbody.appendChild(row);
    });
  } catch (error) {
    document.getElementById("listaInspecoes").innerHTML =
      `<tr><td colspan="5" class="text-danger">Erro ao carregar dados.</td></tr>`;
  }
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

carregarInspecoes();
