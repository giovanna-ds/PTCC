
let carrinho = JSON.parse(localStorage.getItem('carrinhoBistro')) || [];
const TAXA_ENTREGA = 5.00;

document.addEventListener('DOMContentLoaded', () => {
    renderizarCarrinho();

    // Adiciona o clique do botão "Ir para Pagamento" via Listener JS
    const btnFinalizar = document.getElementById('btnFinalizarCompra');
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', (e) => {
            e.preventDefault(); // Evita recarregar a página
            irParaPagamento();
        });
    }
});

function renderizarCarrinho() {
    const container = document.getElementById('listaItensCarrinho');
    if (!container) return;
    
    container.innerHTML = '';

    if (carrinho.length === 0) {
        container.innerHTML = `
            <div class="carrinho-vazio">
                <h3>Seu carrinho está vazio 🍰</h3>
                <p>Que tal escolher um de nossos doces deliciosos?</p>
            </div>
        `;
        atualizarTotais(0);
        const btnFinalizar = document.getElementById('btnFinalizarCompra');
        if (btnFinalizar) btnFinalizar.disabled = true;
        return;
    }

    const btnFinalizar = document.getElementById('btnFinalizarCompra');
    if (btnFinalizar) btnFinalizar.disabled = false;
    
    let subtotal = 0;

    carrinho.forEach((item, index) => {
        const itemTotal = item.preco * item.quantidade;
        subtotal += itemTotal;

        const itemElement = document.createElement('div');
        itemElement.classList.add('item-carrinho');
        itemElement.innerHTML = `
            <div class="item-info">
                <img src="${item.imagem ? '../' + item.imagem : '../img/fundo-confeitaria-1.png'}" alt="${item.titulo}" class="item-img">
                <div class="item-detalhes">
                    <h4>${item.titulo}</h4>
                    <span class="preco-unitario">R$ ${item.preco.toFixed(2).replace('.', ',')}</span>
                </div>
            </div>

            <div class="qtd-controle">
                <button type="button" class="btn-qtd" data-index="${index}" data-acao="-1">-</button>
                <span>${item.quantidade}</span>
                <button type="button" class="btn-qtd" data-index="${index}" data-acao="1">+</button>
            </div>

            <span class="item-preco-total">R$ ${itemTotal.toFixed(2).replace('.', ',')}</span>

            <button type="button" class="btn-remover" data-index="${index}" title="Remover item">
                <i class="fa-solid fa-trash"></i>
            </button>
        `;
        container.appendChild(itemElement);
    });

    // Eventos para alterar quantidade e remover
    document.querySelectorAll('.btn-qtd').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const idx = parseInt(e.target.getAttribute('data-index'));
            const acao = parseInt(e.target.getAttribute('data-acao'));
            alterarQuantidade(idx, acao);
        });
    });

    document.querySelectorAll('.btn-remover').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const button = e.target.closest('.btn-remover');
            const idx = parseInt(button.getAttribute('data-index'));
            removerItem(idx);
        });
    });

    atualizarTotais(subtotal);
}

function alterarQuantidade(index, mudanca) {
    carrinho[index].quantidade += mudanca;

    if (carrinho[index].quantidade <= 0) {
        carrinho.splice(index, 1);
    }

    salvarEAtualizar();
}

function removerItem(index) {
    carrinho.splice(index, 1);
    salvarEAtualizar();
}

function atualizarTotais(subtotal) {
    const total = subtotal > 0 ? subtotal + TAXA_ENTREGA : 0;

    const elemSubtotal = document.getElementById('subtotalValor');
    const elemTotal = document.getElementById('totalValor');

    if (elemSubtotal) elemSubtotal.innerText = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
    if (elemTotal) elemTotal.innerText = `R$ ${total.toFixed(2).replace('.', ',')}`;
}

function salvarEAtualizar() {
    localStorage.setItem('carrinhoBistro', JSON.stringify(carrinho));
    renderizarCarrinho();
}

// REDIRECIONA PARA A TELA DE PAGAMENTO
function irParaPagamento() {
    if (!carrinho || carrinho.length === 0) {
        alert("Seu carrinho está vazio!");
        return;
    }

    let subtotal = carrinho.reduce((acc, item) => acc + (item.preco * item.quantidade), 0);
    let valorTotal = (subtotal + TAXA_ENTREGA).toFixed(2);
    let resumoProdutos = carrinho.map(i => `${i.titulo} (${i.quantidade}x)`).join(', ');

    // Como carrinho.php e pagamento.php estão na MESMA pasta "html/",
    // o caminho é apenas "pagamento.php"
    window.location.href = `pagamento.php?valor=${valorTotal}&produto=${encodeURIComponent(resumoProdutos)}&qtd=${carrinho.length}`;
}