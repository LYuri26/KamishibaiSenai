async function verificarAcesso(cargoNecessario = "gerencia") {
  try {
    const response = await fetch("../acesso/api/verificar_sessao.php");
    const data = await response.json();
    if (!data.logado || (cargoNecessario && data.cargo !== cargoNecessario)) {
      window.location.href = "../acesso/login.html";
    }
  } catch (error) {
    window.location.href = "../acesso/login.html";
  }
}

verificarAcesso("lider");
