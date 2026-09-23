// Recuperação de senha conectada ao backend (php/recuperar_senha.php),
// em duas etapas: 1) confere se o e-mail existe no banco; 2) salva a
// nova senha (já com hash, feito no PHP).

const formBuscarEmail = document.getElementById('formBuscarEmail');
const formNovaSenha = document.getElementById('formNovaSenha');
let emailEmRecuperacao = '';

formBuscarEmail.addEventListener('submit', async function (e) {
    e.preventDefault();

    const email = document.getElementById('recEmail').value.trim().toLowerCase();

    try {
        const resposta = await fetch('../php/recuperar_senha.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ acao: 'verificar_email', email })
        });
        const dados = await resposta.json();

        if (!dados.sucesso) {
            alert(dados.mensagem || 'Não encontramos nenhuma conta com esse e-mail.');
            return;
        }

        emailEmRecuperacao = email;
        formBuscarEmail.style.display = 'none';
        formNovaSenha.classList.remove('form-oculto');
    } catch (erro) {
        alert('Não foi possível conectar ao servidor. Verifique se o banco de dados já foi configurado.');
    }
});

formNovaSenha.addEventListener('submit', async function (e) {
    e.preventDefault();

    const novaSenha = document.getElementById('novaSenha').value;
    const confirmarSenha = document.getElementById('confirmarSenha').value;

    if (novaSenha !== confirmarSenha) {
        alert('As senhas não coincidem. Tente novamente.');
        return;
    }

    try {
        const resposta = await fetch('../php/recuperar_senha.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ acao: 'redefinir_senha', email: emailEmRecuperacao, novaSenha })
        });
        const dados = await resposta.json();

        if (!dados.sucesso) {
            alert(dados.mensagem || 'Não foi possível alterar a senha.');
            return;
        }

        alert('Senha alterada com sucesso! Faça login com a nova senha.');
        window.location.href = 'login.php';
    } catch (erro) {
        alert('Não foi possível conectar ao servidor. Verifique se o banco de dados já foi configurado.');
    }
});
