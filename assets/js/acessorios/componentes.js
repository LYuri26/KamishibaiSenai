// Função para carregar componente HTML e inserir no elemento alvo
async function carregarComponente(urls, elementoId) {
  for (const url of urls) {
    try {
      const response = await fetch(url);

      if (response.ok) {
        const html = await response.text();

        document.getElementById(elementoId).innerHTML = html;

        return;
      }
    } catch (error) {
      console.warn("Falha ao carregar:", url);
    }
  }

  console.error("Nenhum caminho válido encontrado para", elementoId);
}

// Carregar cabeçalho e rodapé quando a página carregar
document.addEventListener("DOMContentLoaded", () => {
  // Caminho absoluto do projeto
  const basePath = "/KamishibaiSenai";

  carregarComponente(
    [
      basePath + "/acessorios/header.html",
      "/acessorios/header.html",
      "../acessorios/header.html",
    ],
    "header",
  );

  carregarComponente(
    [
      basePath + "/acessorios/footer.html",
      "/acessorios/footer.html",
      "../acessorios/footer.html",
    ],
    "footer",
  );
});
