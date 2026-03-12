async function carregarDadosUsuario() {
  try {
    const response = await fetch("../acesso/api/dados_usuario.php");
    const data = await response.json();
    if (data.erro) {
      console.warn("Usuário não autenticado ou erro ao carregar dados.");
      return null;
    }
    return data;
  } catch (error) {
    console.error("Erro ao buscar dados do usuário:", error);
    return null;
  }
}
