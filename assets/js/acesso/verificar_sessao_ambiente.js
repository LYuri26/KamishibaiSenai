async function verificarSessao() {
  try {
    const response = await fetch("../acesso/api/verificar_sessao.php");
    const data = await response.json();
    if (!data.logado) {
      window.location.href = "../acesso/login.html";
    }
  } catch (error) {
    window.location.href = "../acesso/login.html";
  }
}
verificarSessao();
