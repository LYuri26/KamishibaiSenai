// Função para carregar componente HTML
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

// Detecta a base do projeto a partir da URL atual
function getBasePath() {
  const path = window.location.pathname;
  // Se estiver em /KamishibaiSenai/...
  const match = path.match(/^(\/KamishibaiSenai\/)/);
  if (match) {
    return match[1];
  }
  // Se não, assume que a raiz é a pasta atual
  return "/";
}

// Carrega cabeçalho e rodapé
document.addEventListener("DOMContentLoaded", () => {
  const basePath = getBasePath();
  const caminhosHeader = [
    `${basePath}acessorios/header.html`, // Ex: /KamishibaiSenai/acessorios/header.html
    "../acessorios/header.html", // Relativo à página atual
    "./acessorios/header.html", // Alternativa
  ];
  const caminhosFooter = [
    `${basePath}acessorios/footer.html`,
    "../acessorios/footer.html",
    "./acessorios/footer.html",
  ];
  carregarComponente(caminhosHeader, "header");
  carregarComponente(caminhosFooter, "footer");
});
