// Cadastro conectado ao backend (php/cadastro.php). Enquanto o banco
// de dados não for criado, essa chamada falha e o usuário é avisado -
// não há mais fallback salvando só no localStorage, já que agora a
// fonte de verdade é o banco.

document.getElementById('formCadastro').addEventListener('submit', async function (e) {
    e.preventDefault();

    const cliente = {
        nome: document.getElementById('cadNome').value.trim(),
        email: document.getElementById('cadEmail').value.trim().toLowerCase(),
        senha: document.getElementById('cadSenha').value,
        telefone: document.getElementById('cadTelefone').value.trim(),
        dataNascimento: document.getElementById('cadNascimento').value
    };

    try {
        const resposta = await fetch('../php/cadastro.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(cliente)
        });
        const dados = await resposta.json();

        if (!dados.sucesso) {
            alert(dados.mensagem || 'Não foi possível concluir o cadastro.');
            return;
        }

        alert('Cadastro realizado com sucesso! Faça login para continuar.');
        window.location.href = 'login.php';
    } catch (erro) {
        alert('Não foi possível conectar ao servidor. Verifique se o banco de dados já foi configurado.');
    }
});
