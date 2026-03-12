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

    // Mapeamento de campos para rótulos (baseado nas perguntas)
    const rotulos = {
      carteiras_organizadas: "Carteiras organizadas em fileiras regulares?",
      carteiras_quantidade: "Aproximadamente 40 carteiras disponíveis?",
      carteiras_danificadas: "Todas as carteiras em bom estado?",
      tv_presente: "Televisão presente?",
      tv_integra: "Televisão íntegra?",
      tv_hdmi: "Cabo HDMI disponível e conectado?",
      tv_cabos_organizados: "Cabos organizados sem risco?",
      tv_conectada: "Televisão conectada à tomada?",
      tv_cabos_ok: "Todos os cabos em bom estado?",
      ar_presentes: "Dois ar-condicionados presentes?",
      ar_controle: "Controle remoto disponível?",
      ar_danos: "Ar-condicionado em bom estado?",
      quadro_limpo: "Quadro limpo e utilizável?",
      quadro_danos: "Quadro em bom estado?",
      quadro_fixo: "Quadro firmemente fixado?",
      porta_funciona: "Porta abre/fecha normalmente?",
      janelas_intactas: "Janelas intactas?",
      janelas_vidros: "Todos os vidros inteiros?",
      tomadas_intactas: "Tomadas intactas?",
      tomadas_fios: "Sem fios expostos?",
      tomadas_adaptadores: "Sem adaptadores improvisados?",
      mesa_firme: "Mesa do instrutor firme e organizada?",
      mesa_gavetas: "Gavetas da mesa fechadas e funcionais?",
      cadeira_integra: "Cadeira do instrutor em bom estado?",
    };

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

    // Itens com problema (resposta "nao")
    html += '<h4>Itens com problema (Não)</h4><ul class="list-group">';
    let temProblemas = false;
    for (const [campo, rotulo] of Object.entries(rotulos)) {
      if (data[campo] === "nao") {
        html += `<li class="list-group-item list-group-item-danger">${rotulo} - <strong>Não (problema)</strong></li>`;
        temProblemas = true;
      }
    }
    if (!temProblemas) {
      html += '<li class="list-group-item">Nenhum item com problema.</li>';
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
