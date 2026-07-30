async function verificarAcesso() {
  try {
    const response = await fetch("../acesso/api/verificar_sessao.php");
    const data = await response.json();

    if (!data.logado || data.cargo !== "lider") {
      // Salva a página do painel administrativo garantindo a extensão .php
      const urlCorrigida = window.location.href.replace(/\.html/gi, ".php");
      sessionStorage.setItem("redirectAfterLogin", urlCorrigida);

      window.location.href = "../acesso/login.html";
    }
  } catch (error) {
    const urlCorrigida = window.location.href.replace(/\.html/gi, ".php");
    sessionStorage.setItem("redirectAfterLogin", urlCorrigida);

    window.location.href = "../acesso/login.html";
  }
}

// Execução imediata
verificarAcesso();
