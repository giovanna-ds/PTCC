// Busca no banco os produtos cadastrados pelo admin (php/produtos.php)
// e injeta na seção "Novidades" do cardápio, no mesmo formato dos
// cards que já existem (com botão de favorito e modal funcionando).
//
// Enquanto o banco de dados não for criado, esse fetch falha
// silenciosamente e a seção simplesmente não aparece - o resto do
// site continua funcionando normalmente com os produtos fixos.
//
// IMPORTANTE: para não colidir com os ids 1-25 já usados pelos cards
// fixos no HTML (usados em carrinho/favoritos), os produtos vindos do
// banco recebem o id + 1000 dentro do site (ex: produto id=3 no banco
// vira id=1003 no carrinho/favoritos).

const OFFSET_ID_BANCO = 1000;

document.addEventListener('DOMContentLoaded', carregarProdutosDoBanco);

async function carregarProdutosDoBanco() {
    try {
        const resposta = await fetch('php/produtos.php');
        if (!resposta.ok) return;

        const dados = await resposta.json();
        if (!dados.sucesso || !Array.isArray(dados.produtos) || dados.produtos.length === 0) {
            return;
        }

        renderizarProdutosDoBanco(dados.produtos);
    } catch (erro) {
        // Banco/servidor PHP ainda não configurado - não faz nada.
        console.info('Produtos do banco não carregados (backend ainda não configurado).');
    }
}

function renderizarProdutosDoBanco(produtos) {
    const secao = document.getElementById('secao-novos-produtos');
    const grid = document.getElementById('gridNovosProdutos');
    if (!secao || !grid) return;

    grid.innerHTML = '';

    produtos.forEach((produto) => {
        const id = OFFSET_ID_BANCO + parseInt(produto.id);
        const titulo = produto.nome;
        const descricao = produto.descricao || '';
        const preco = parseFloat(produto.preco).toFixed(2);
        const imagem = produto.imagem || 'img/fundo-confeitaria-1.png';

        const card = document.createElement('div');
        card.className = 'card';
        const categoria = produto.categoria || '';
        card.setAttribute('onclick', `abrirModal(${id}, '${escaparAspas(titulo)}', '${escaparAspas(descricao)}', ${preco}, '${escaparAspas(imagem)}', '${escaparAspas(categoria)}')`);
        card.innerHTML = `
            <button type="button" class="btn-favorito" data-id="${id}" data-titulo="${escaparHtml(titulo)}"
                data-preco="${preco}" data-imagem="${escaparHtml(imagem)}"
                onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar">
                <i class="fa-regular fa-heart"></i>
            </button>
            <img src="${imagem}" alt="${escaparHtml(titulo)}" class="card-image">
            <div class="card-content">
                <h2 class="card-titulo">${escaparHtml(titulo)}</h2>
                <p class="card-descricao">${escaparHtml(descricao)}</p>
            </div>
        `;
        grid.appendChild(card);
    });

    secao.style.display = 'block';
    if (typeof atualizarIconesFavoritos === 'function') {
        atualizarIconesFavoritos();
    }
}

function escaparAspas(texto) {
    return String(texto).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

function escaparHtml(texto) {
    const div = document.createElement('div');
    div.textContent = String(texto);
    return div.innerHTML;
}
