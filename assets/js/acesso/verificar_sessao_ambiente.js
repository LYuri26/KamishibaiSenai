async function verificarSessao() {
  try {
    const response = await fetch("../acesso/api/verificar_sessao.php");
    const data = await response.json();

    if (!data.logado) {
      // Salva a página atual garantindo que a extensão seja sempre .php
      const urlCorrigida = window.location.href.replace(/\.html/gi, ".php");
      sessionStorage.setItem("redirectAfterLogin", urlCorrigida);

      window.location.href = "../acesso/login.html";
    }
  } catch (error) {
    // Em caso de erro, assume não autenticado e salva a URL corrigida
    const urlCorrigida = window.location.href.replace(/\.html/gi, ".php");
    sessionStorage.setItem("redirectAfterLogin", urlCorrigida);

    window.location.href = "../acesso/login.html";
  }
}

// Execução imediata
verificarSessao();
