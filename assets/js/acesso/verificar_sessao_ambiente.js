async function verificarSessao() {
  try {
    const response = await fetch("../acesso/api/verificar_sessao.php");
    const data = await response.json();

    if (!data.logado) {
      // Salva página atual antes do redirect
      sessionStorage.setItem("redirectAfterLogin", window.location.href);

      window.location.href = "../acesso/login.html";
    }
  } catch (error) {
    // Em caso de erro, assume não autenticado
    sessionStorage.setItem("redirectAfterLogin", window.location.href);

    window.location.href = "../acesso/login.html";
  }
}

// Execução imediata
verificarSessao();
