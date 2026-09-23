// Login conectado ao backend (php/login.php), que confere e-mail e
// senha (hash) direto no banco de dados.

document.getElementById('formLogin').addEventListener('submit', async function (e) {
    e.preventDefault();

    const email = document.getElementById('loginEmail').value.trim().toLowerCase();
    const senha = document.getElementById('loginSenha').value;

    try {
        const resposta = await fetch('../php/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, senha })
        });
        const dados = await resposta.json();

        if (!dados.sucesso) {
            alert(dados.mensagem || 'E-mail ou senha incorretos.');
            return;
        }

        // Guarda os dados do cliente logado (usados em perfil.js e script.js)
        // e marca a sessão como ativa neste navegador.
        localStorage.setItem('clienteSabinoBistro', JSON.stringify(dados.cliente));
        sessionStorage.setItem('sessaoAtivaSabinoBistro', 'true');

        window.location.href = dados.cliente.tipoUsuario === 'admin' ? 'admin.php' : '../index.php';
    } catch (erro) {
        alert('Não foi possível conectar ao servidor. Verifique se o banco de dados já foi configurado.');
    }
});
