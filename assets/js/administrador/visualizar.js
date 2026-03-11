const urlParams = new URLSearchParams(window.location.search);
const id = urlParams.get("id");

if (!id) {
  window.location.href = "index.html";
}

async function carregarInspecao() {
  try {
    const response = await fetch(`api/get_inspecao.php?id=${id}`);
    const data = await response.json();

    if (data.erro) {
      document.getElementById("detalhesInspecao").innerHTML =
        `<div class="alert alert-danger">${data.erro}</div>`;
      return;
    }

    // Mapeamento de campos para rótulos
    const rotulos = {
      porta: "Porta",
      piso: "Piso",
      ...Array.from({ length: 40 }, (_, i) => ({
        [`carteira_${i + 1}`]: `Carteira ${i + 1}`,
      })).reduce((acc, curr) => ({ ...acc, ...curr }), {}),
      janela: "Janela",
      mesa_professor: "Mesa do professor",
      cadeira_professor: "Cadeira do professor",
      ar_condicionado: "Ar condicionado",
      televisao: "Televisão",
      quadro: "Quadro",
    };

    let html = `<h2>Inspeção #${data.id} - ${escapeHtml(data.nome)} em ${new Date(data.data).toLocaleString("pt-BR")}</h2>`;
    html += '<h4>Itens avariados (Sim)</h4><ul class="list-group">';

    for (const [campo, rotulo] of Object.entries(rotulos)) {
      if (data[campo] === "sim") {
        html += `<li class="list-group-item list-group-item-danger">${rotulo} - <strong>Avariado</strong></li>`;
      }
    }
    html += "</ul>";

    html += '<h4 class="mt-3">Observações consolidadas</h4>';
    html += `<div class="card p-3">${escapeHtml(data.observacoes).replace(/\n/g, "<br>") || "Nenhuma observação registrada."}</div>`;

    document.getElementById("detalhesInspecao").innerHTML = html;
  } catch (error) {
    document.getElementById("detalhesInspecao").innerHTML =
      '<div class="alert alert-danger">Erro ao carregar dados.</div>';
  }
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

carregarInspecao();
