// Função para carregar componente HTML e inserir no elemento alvo
async function carregarComponente(url, elementoId) {
  try {
    const response = await fetch(url);
    if (!response.ok) throw new Error(`Erro ao carregar ${url}`);
    const html = await response.text();
    document.getElementById(elementoId).innerHTML = html;
  } catch (error) {
    console.error(error);
  }
}

// Carregar cabeçalho e rodapé quando a página carregar
document.addEventListener("DOMContentLoaded", () => {
  carregarComponente("/acessorios/header.html", "header");
  carregarComponente("/acessorios/footer.html", "footer");
});
