document.addEventListener('DOMContentLoaded', renderizarFavoritos);

function renderizarFavoritos() {
    const container = document.getElementById('listaFavoritos');
    if (!container) return;

    const favoritos = obterFavoritos();
    container.innerHTML = '';

    if (favoritos.length === 0) {
        container.innerHTML = `
            <div class="favoritos-vazio">
                <i class="fa-regular fa-heart"></i>
                <h3>Você ainda não tem favoritos</h3>
                <p>Toque no coração de um doce no cardápio para guardá-lo aqui.</p>
                <a href="../index.php" class="link-continuar">
                    <i class="fa-solid fa-arrow-left"></i> Ver Cardápio
                </a>
            </div>
        `;
        return;
    }

    favoritos.forEach((item) => {
        const card = document.createElement('div');
        card.classList.add('item-favorito');
        card.innerHTML = `
            <img src="../${item.imagem}" alt="${item.titulo}" class="item-favorito-img">
            <div class="item-favorito-detalhes">
                <h4>${item.titulo}</h4>
                <span class="preco-unitario">R$ ${item.preco.toFixed(2).replace('.', ',')}</span>
            </div>
            <div class="item-favorito-acoes">
                <button type="button" class="btn-add-carrinho" data-id="${item.id}" title="Adicionar ao carrinho">
                    <i class="fa-solid fa-cart-shopping"></i>
                </button>
                <button type="button" class="btn-remover" data-id="${item.id}" title="Remover dos favoritos">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(card);
    });

    container.querySelectorAll('.btn-remover').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = parseInt(btn.getAttribute('data-id'));
            removerFavorito(id);
        });
    });

    container.querySelectorAll('.btn-add-carrinho').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = parseInt(btn.getAttribute('data-id'));
            adicionarFavoritoAoCarrinho(id);
        });
    });
}

function removerFavorito(id) {
    let favoritos = obterFavoritos();
    favoritos = favoritos.filter(item => item.id !== id);
    salvarFavoritos(favoritos);
    renderizarFavoritos();
}

function adicionarFavoritoAoCarrinho(id) {
    if (!usuarioLogado) {
        document.getElementById('messageBox').style.display = 'flex';
        return;
    }

    const favoritos = obterFavoritos();
    const item = favoritos.find(f => f.id === id);
    if (!item) return;

    // Reaproveita as funções já existentes em script.js, mantendo o
    // produto atual coerente para que a imagem seja salva corretamente.
    produtoAtual = { id: item.id, titulo: item.titulo, preco: item.preco, imagemSrc: item.imagem };
    adicionarAoCarrinho(item.id, item.titulo, item.preco, 1);
    salvarCarrinhoLocalStorage();

    alert(`${item.titulo} adicionado ao carrinho com sucesso! 🛒`);
}
