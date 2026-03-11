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
    };

    // Carteiras A1 a E8
    const fileiras = ["A", "B", "C", "D", "E"];
    for (let f of fileiras) {
      for (let c = 1; c <= 8; c++) {
        rotulos[`carteira_${f}${c}`] = `Carteira ${f}${c}`;
      }
    }

    // Outros itens
    const outros = {
      janela: "Janela",
      mesa_professor: "Mesa do professor",
      cadeira_professor: "Cadeira do professor",
      ar_condicionado: "Ar condicionado",
      televisao: "Televisão",
      quadro: "Quadro",
    };
    Object.assign(rotulos, outros);

    const momentoTexto =
      data.momento === "inicio" ? "Início da aula" : "Fim da aula";

    let html = `
            <div class="inspecao-header">
                <h2>Inspeção #${data.id}</h2>
                <p class="data">${new Date(data.data).toLocaleString("pt-BR")}</p>
                <p><strong>Instrutor:</strong> ${escapeHtml(data.nome)}</p>
                <p><strong>Momento:</strong> ${momentoTexto}</p>
            </div>
        `;

    html += '<h4>Itens avariados (Sim)</h4><ul class="list-group">';
    let temAvariados = false;
    for (const [campo, rotulo] of Object.entries(rotulos)) {
      if (data[campo] === "sim") {
        html += `<li class="list-group-item list-group-item-danger">${rotulo} - <strong>Avariado</strong></li>`;
        temAvariados = true;
      }
    }
    if (!temAvariados) {
      html += '<li class="list-group-item">Nenhum item avariado.</li>';
    }
    html += "</ul>";

    html += '<h4 class="mt-3">Observações consolidadas</h4>';
    html += `<div class="card p-3">${escapeHtml(data.observacoes || "Nenhuma observação registrada.").replace(/\n/g, "<br>")}</div>`;

    document.getElementById("detalhesInspecao").innerHTML = html;
  } catch (error) {
    document.getElementById("detalhesInspecao").innerHTML =
      '<div class="alert alert-danger">Erro ao carregar dados.</div>';
    console.error(error);
  }
}

function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

carregarInspecao();
