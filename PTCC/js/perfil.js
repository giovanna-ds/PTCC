document.addEventListener('DOMContentLoaded', () => {
    carregarDadosDoCliente();
    document.getElementById('btnAbrirModal').addEventListener('click', abrirModalEdicao);
    document.getElementById('btnFecharModal').addEventListener('click', fecharModalEdicao);
    document.getElementById('formEdicao').addEventListener('submit', salvarEdicao);
    document.getElementById('btnSairConta').addEventListener('click', sairDaConta);
});

function carregarDadosDoCliente() {
    const clienteLogado = JSON.parse(localStorage.getItem('clienteSabinoBistro')) || {
        nome: "Cliente Sabino Bistrô",
        email: "cliente@email.com",
        telefone: "(11) 99999-8888",
        endereco: "Rua do Bistrô, 123 - São Paulo/SP"
    };
    document.getElementById('clienteNome').innerText = clienteLogado.nome;
    document.getElementById('clienteEmail').innerText = clienteLogado.email;
    document.getElementById('clienteTelefone').innerText = clienteLogado.telefone || "Não informado";
    document.getElementById('clienteEndereco').innerText = clienteLogado.endereco || "Não informado";
    if (clienteLogado.nome) {
        document.getElementById('avatarLetra').innerText = clienteLogado.nome.charAt(0);
    }
    carregarHistoricoPedidos();
}

function abrirModalEdicao() {
    const clienteLogado = JSON.parse(localStorage.getItem('clienteSabinoBistro')) || {};
    document.getElementById('editNome').value = clienteLogado.nome || '';
    document.getElementById('editTelefone').value = clienteLogado.telefone || '';
    document.getElementById('editEndereco').value = clienteLogado.endereco || '';
    document.getElementById('modalEdicao').style.display = 'flex';
}

function fecharModalEdicao() {
    document.getElementById('modalEdicao').style.display = 'none';
}

function salvarEdicao(event) {
    event.preventDefault();
    let clienteLogado = JSON.parse(localStorage.getItem('clienteSabinoBistro')) || {};
    clienteLogado.nome = document.getElementById('editNome').value;
    clienteLogado.telefone = document.getElementById('editTelefone').value;
    clienteLogado.endereco = document.getElementById('editEndereco').value;
    localStorage.setItem('clienteSabinoBistro', JSON.stringify(clienteLogado));

    // Salva também no banco de dados (php/cliente_atualizar.php), se o
    // cliente já tiver um id vindo do banco (login feito após o backend
    // estar configurado).
    if (clienteLogado.id) {
        fetch('../php/cliente_atualizar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(clienteLogado)
        }).catch(() => {
            console.info('Dados não salvos no banco (backend ainda não configurado).');
        });
    }

    carregarDadosDoCliente();
    fecharModalEdicao();
    alert("✅ Dados atualizados com sucesso!");
}

function carregarHistoricoPedidos() {
    const historico = JSON.parse(localStorage.getItem('historicoPedidosBistro')) || [];
    const container = document.getElementById('listaPedidos');
    if (historico.length > 0) {
        container.innerHTML = '';
        historico.forEach(pedido => {
            container.innerHTML += `
                <div class="pedido-item">
                    <div>
                        <strong>Pedido #${pedido.id || '001'}</strong><br>
                        <small style="color:#666;">${pedido.data || 'Hoje'}</small>
                    </div>
                    <div style="text-align:right;">
                        <span style="color:#28a745;font-weight:bold;">R$ ${pedido.total || '0,00'}</span><br>
                        <small style="color:#888;">${pedido.metodo || 'PIX'}</small>
                    </div>
                </div>
            `;
        });
    }
}

function sairDaConta() {
    if (confirm("Deseja realmente sair da sua conta?")) {
        // CORRIGIDO: também precisa encerrar a sessão de login, senão
        // o site continuava tratando o usuário como logado depois de sair.
        sessionStorage.removeItem('sessaoAtivaSabinoBistro');
        window.location.href = "login.php";
    }
}