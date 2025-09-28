import './bootstrap';

async function checkAuth() {
  const token = localStorage.getItem("access_token");
  if (!token) {
    window.location.href = "/frontend/index.html";
    return false;
  }

  try {
    const response = await fetch("http://localhost:8080/auth/check_token.php", {
      method: "POST",
      headers: {
        "Authorization": `Bearer ${token}`
      }
    });

    const data = await response.json();
    if (!data.valid) {
      localStorage.removeItem("access_token");
      window.location.href = "/frontend/index.html";
      return false;
    }

    return true;
  } catch (err) {
    console.error("Erro ao verificar autenticação:", err);
    localStorage.removeItem("access_token");
    window.location.href = "/frontend/index.html";
    return false;
  }
}

async function carregarTransacoes() {
  const token = localStorage.getItem("access_token");
  try {
    const response = await fetch("http://localhost:8080/transacoes.php", {
      headers: { "Authorization": `Bearer ${token}` }
    });
    const transacoes = await response.json();

    const tabela = document.getElementById("tabela-transacoes");
    tabela.innerHTML = "";

    transacoes.forEach(t => {
      const tr = document.createElement("tr");

      tr.innerHTML = `
        <td>${t.id}</td>
        <td>${t.descricao}</td>
        <td class="${t.valor >= 0 ? 'positivo' : 'negativo'}">
          R$ ${parseFloat(t.valor).toFixed(2)}
        </td>
        <td>${new Date(t.data).toLocaleDateString("pt-BR")}</td>
      `;

      tabela.appendChild(tr);
    });
  } catch (err) {
    console.error("Erro ao carregar transações:", err);
  }
}

checkAuth().then(authenticated => {
  if (authenticated) {
    carregarTransacoes();
  }
});

document.getElementById("logoutBtn").addEventListener("click", () => {
  localStorage.removeItem("access_token");
  window.location.href = "/frontend/index.html";
});
