function rolarParaCategoria(idCategoria) {
    const elemento = document.getElementById(idCategoria);
    if (elemento) {
        elemento.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

const botaoUsuario = document.querySelector(".user");
const messageBox = document.getElementById("messageBox");

let usuarioLogado = sessionStorage.getItem('sessaoAtivaSabinoBistro') === 'true';
let carrinho = JSON.parse(localStorage.getItem('carrinhoBistro')) || [];
let produtoAtual = null;

if (botaoUsuario) {
    botaoUsuario.addEventListener("click", function (e) {
        e.preventDefault();

        if (usuarioLogado) {
            window.location.href = "html/perfil.php";
        } else {
            messageBox.style.display = "flex";
        }
    });
}

/* Responsividade do menu mobile */
const navbarToggle = document.querySelector('.navbar-toggle');
const navbarMenu = document.querySelector('.navbar-menu');

if (navbarToggle && navbarMenu) {
    navbarToggle.addEventListener('click', () => {
        navbarToggle.classList.toggle('active');
        navbarMenu.classList.toggle('active');
    });
}

// 1. Abre APENAS o modal ao clicar no card
function abrirModal(id, titulo, descricao, preco, imagemSrc, categoria = '') {
    produtoAtual = { id, titulo, descricao, preco: parseFloat(preco), imagemSrc, categoria };

    document.getElementById('modalTitulo').innerText = titulo;
    document.getElementById('modalDescricao').innerText = descricao;
    document.getElementById('modalPreco').innerText = `R$ ${parseFloat(preco).toFixed(2)}`;
    document.getElementById('modalImagem').src = imagemSrc;
    document.getElementById('qtdInput').value = 1;

    atualizarPrecoModal(1);
    document.getElementById('modalProduto').style.display = 'flex';
}

// 2. Fecha o modal do produto
function fecharModal() {
    document.getElementById('modalProduto').style.display = 'none';
}

// 3. Altera a quantidade de itens (+ / -)
function alterarQtd(valor) {
    const input = document.getElementById('qtdInput');
    let qtd = parseInt(input.value) + valor;
    if (qtd >= 1) {
        input.value = qtd;
        atualizarPrecoModal(qtd);
    }
}

function atualizarPrecoModal(quantidade) {
    const total = produtoAtual.preco * quantidade;
    document.getElementById('modalPreco').innerText = `R$ ${total.toFixed(2).replace('.', ',')}`;
}

// 4. Ação ao clicar em "Adicionar ao Carrinho" dentro do Modal
function adicionarAoCarrinhoModal() {
    if (!usuarioLogado) {
        fecharModal();
        exibirMessageBox();
    } else {
        const qtd = parseInt(document.getElementById('qtdInput').value);

        adicionarAoCarrinho(produtoAtual.id, produtoAtual.titulo, produtoAtual.preco, qtd);
        salvarCarrinhoLocalStorage();

        fecharModal();
        alert(`${qtd}x ${produtoAtual.titulo} adicionado ao carrinho com sucesso! 🛒`);
    }
}

// 5. Ação ao clicar em "Comprar Agora" dentro do Modal
function comprarAgoraModal() {
    fecharModal();

    if (!usuarioLogado) {
        exibirMessageBox();
    } else {
        const qtd = parseInt(document.getElementById('qtdInput').value);
        const valorTotal = (produtoAtual.preco * qtd).toFixed(2);
        window.location.href = `html/pagamento.php?valor=${valorTotal}&produto=${encodeURIComponent(produtoAtual.titulo)}&qtd=${qtd}`;
    }
}

// 6. Funções de controlo da MessageBox de Login
function exibirMessageBox() {
    document.getElementById('messageBox').style.display = 'flex';
}

function fecharMensagem() {
    document.getElementById('messageBox').style.display = 'none';
}

function irParaLogin() {
    window.location.href = 'html/login.php';
}

function irParaCadastro() {
    window.location.href = 'html/cadastro_cliente.php';
}

function adicionarAoCarrinho(id, titulo, preco, quantidade) {
    const itemExistente = carrinho.find(item => item.id === id);

    if (itemExistente) {
        itemExistente.quantidade += quantidade;
    } else {
        carrinho.push({
            id: id,
            titulo: titulo,
            preco: preco,
            quantidade: quantidade,
            imagem: produtoAtual ? produtoAtual.imagemSrc : ''
        });
    }

    localStorage.setItem('carrinhoBistro', JSON.stringify(carrinho));
}

function salvarCarrinhoLocalStorage() {
    localStorage.setItem('carrinhoBistro', JSON.stringify(carrinho));
}

/* FAVORITOS */

function obterFavoritos() {
    return JSON.parse(localStorage.getItem('favoritosBistro')) || [];
}

function salvarFavoritos(favoritos) {
    localStorage.setItem('favoritosBistro', JSON.stringify(favoritos));
}

function estaFavoritado(id) {
    return obterFavoritos().some(item => item.id === id);
}

function toggleFavorito(botao) {
    const id = parseInt(botao.getAttribute('data-id'));
    const titulo = botao.getAttribute('data-titulo');
    const preco = parseFloat(botao.getAttribute('data-preco'));
    const imagem = botao.getAttribute('data-imagem');

    let favoritos = obterFavoritos();
    const index = favoritos.findIndex(item => item.id === id);

    if (index >= 0) {
        favoritos.splice(index, 1);
        botao.classList.remove('ativo');
        botao.innerHTML = '<i class="fa-regular fa-heart"></i>';
    } else {
        favoritos.push({ id, titulo, preco, imagem });
        botao.classList.add('ativo');
        botao.innerHTML = '<i class="fa-solid fa-heart"></i>';
    }

    salvarFavoritos(favoritos);
}

function atualizarIconesFavoritos() {
    const favoritos = obterFavoritos();
    document.querySelectorAll('.btn-favorito').forEach(botao => {
        const id = parseInt(botao.getAttribute('data-id'));
        if (favoritos.some(item => item.id === id)) {
            botao.classList.add('ativo');
            botao.innerHTML = '<i class="fa-solid fa-heart"></i>';
        }
    });
}

document.addEventListener('DOMContentLoaded', atualizarIconesFavoritos);

/* CONTROLO DA ENCOMENDA ESPECIAL PERSONALIZADA */

function abrirModalEncomendaCustom(e) {
    if (e) e.preventDefault();
    document.getElementById('modalEncomendaCustom').style.display = 'flex';
}

function fecharModalEncomendaCustom() {
    document.getElementById('modalEncomendaCustom').style.display = 'none';
}

function encomendarProdutoCustom(event) {
    event.preventDefault();

    if (!usuarioLogado) {
        fecharModalEncomendaCustom();
        exibirMessageBox();
        return;
    }

    const tipoItem = document.getElementById('encTipoItem').value;
    const quantidade = document.getElementById('encQuantidade').value.trim();
    const sabores = document.getElementById('encSabores').value.trim();
    const detalhes = document.getElementById('encDetalhes').value.trim();

    const tituloCustom = `Encomenda Especial: ${tipoItem} (${quantidade})`;
    const idUnico = 'encomenda_custom_' + Date.now();

    carrinho.push({
        id: idUnico,
        titulo: `${tituloCustom} - Sabores: ${sabores}` + (detalhes ? ` [Obs: ${detalhes}]` : ''),
        preco: 0.00,
        quantidade: 1,
        imagem: 'img/fundo-confeitaria-1.png'
    });

    salvarCarrinhoLocalStorage();
    fecharModalEncomendaCustom();
    alert('Sua solicitação de encomenda especial foi adicionada ao carrinho com sucesso! 🛒');
    document.getElementById('formEncomendaCustom').reset();
}