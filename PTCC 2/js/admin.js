

let linhaEmEdicao = null;

document.addEventListener('DOMContentLoaded', () => {
    carregarProdutosAdmin();
});

function abrirModalProduto() {
    linhaEmEdicao = null;
    document.getElementById('tituloModalProduto').innerText = 'Adicionar Novo Doce';
    document.getElementById('btnSalvarProduto').innerText = 'Salvar Doce';
    document.getElementById('prodImagemFile').setAttribute('required', 'true');
    document.getElementById('avisoImagemAtual').style.display = 'none';
    document.getElementById('formNovoProduto').reset();
    document.getElementById('grupoNovaCategoria').style.display = 'none';
    carregarCategorias();
    document.getElementById('modalNovoProduto').style.display = 'flex';
}

function editarProduto(botao) {
    const linha = botao.closest('tr');
    linhaEmEdicao = linha;

    document.getElementById('prodNome').value = linha.dataset.nome || '';
    document.getElementById('prodDescricao').value = linha.dataset.descricao || '';
    document.getElementById('prodPreco').value = (linha.dataset.preco || '').replace('.', ',');
    document.getElementById('prodCusto').value = (linha.dataset.custo || '0.00').replace('.', ',');

    const select = document.getElementById('prodCategoria');
    const categoria = linha.dataset.categoria || '';
    if (categoria && !Array.from(select.options).some(op => op.value === categoria)) {
        const op = document.createElement('option');
        op.value = categoria;
        op.textContent = categoria;
        select.insertBefore(op, select.querySelector('option[value="NOVA_CATEGORIA"]'));
    }
    select.value = categoria;

    document.getElementById('prodImagemFile').removeAttribute('required');
    document.getElementById('avisoImagemAtual').style.display = 'block';
    document.getElementById('tituloModalProduto').innerText = 'Editar Doce';
    document.getElementById('btnSalvarProduto').innerText = 'Salvar Alterações';
    document.getElementById('modalNovoProduto').style.display = 'flex';
}

function fecharModalProduto() {
    document.getElementById('modalNovoProduto').style.display = 'none';
    document.getElementById('formNovoProduto').reset();
    document.getElementById('grupoNovaCategoria').style.display = 'none';
    document.getElementById('prodNovaCategoria').removeAttribute('required');
    document.getElementById('prodImagemFile').removeAttribute('required');
    document.getElementById('avisoImagemAtual').style.display = 'none';
    linhaEmEdicao = null;
}

function verificarNovaCategoria(valor) {
    const grupoNova = document.getElementById('grupoNovaCategoria');
    const input = document.getElementById('prodNovaCategoria');

    if (valor === 'NOVA_CATEGORIA') {
        grupoNova.style.display = 'block';
        input.setAttribute('required', 'true');
        input.focus();
    } else {
        grupoNova.style.display = 'none';
        input.removeAttribute('required');
    }
}

function formatarCampoPreco(input) {
    const valor = input.value.replace(',', '.').trim();
    if (valor !== '' && !isNaN(valor)) {
        input.value = parseFloat(valor).toFixed(2).replace('.', ',');
    }
}

async function carregarCategorias() {
    try {
        const resposta = await fetch('../php/categorias.php', { cache: 'no-store' });
        const dados = await resposta.json();
        if (!dados.sucesso) return;

        const select = document.getElementById('prodCategoria');
        const categoriaAtual = select.value;
        select.innerHTML = '';

        dados.categorias.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.nome;
            option.textContent = cat.nome;
            select.appendChild(option);
        });

        const nova = document.createElement('option');
        nova.value = 'NOVA_CATEGORIA';
        nova.textContent = '+ Adicionar Nova Categoria...';
        select.appendChild(nova);

        if (categoriaAtual && Array.from(select.options).some(op => op.value === categoriaAtual)) {
            select.value = categoriaAtual;
        }
    } catch (erro) {
        console.warn('Não foi possível carregar categorias.', erro);
    }
}

async function carregarProdutosAdmin() {
    try {
        const resposta = await fetch('../php/produtos.php?admin=1', { cache: 'no-store' });
        const dados = await resposta.json();

        if (!resposta.ok || !dados.sucesso) {
            throw new Error(dados.mensagem || 'Falha ao carregar produtos.');
        }

        const tabela = document.getElementById('tabelaProdutosCorpo');
        tabela.innerHTML = '';

        dados.produtos.forEach(produto => {
            tabela.appendChild(criarLinhaProduto(produto));
        });

        carregarCategorias();
    } catch (erro) {
        console.error(erro);
        const tabela = document.getElementById('tabelaProdutosCorpo');
        if (tabela) {
            tabela.innerHTML = '<tr><td colspan="6">Não foi possível carregar o cardápio. Verifique o banco de dados.</td></tr>';
        }
    }
}

function criarLinhaProduto(produto) {
    const linha = document.createElement('tr');
    const preco = Number(produto.preco || 0);
    const custo = Number(produto.custo_producao || 0);

    linha.dataset.produtoId = produto.id;
    linha.dataset.nome = produto.nome || '';
    linha.dataset.descricao = produto.descricao || '';
    linha.dataset.preco = preco.toFixed(2);
    linha.dataset.custo = custo.toFixed(2);
    linha.dataset.categoria = produto.categoria || '';
    linha.dataset.imagem = produto.imagem || '';
    linha.dataset.ativo = Number(produto.ativo) === 1 ? '1' : '0';

    const tdImagem = document.createElement('td');
    const img = document.createElement('img');
    img.src = imagemParaAdmin(produto.imagem);
    img.alt = produto.nome || 'Produto';
    img.className = 'img-preview-tabela';
    img.onerror = () => {
        img.onerror = null;
        img.src = '../img/fundo-confeitaria-1.png';
    };
    tdImagem.appendChild(img);

    const tdNome = document.createElement('td');
    tdNome.textContent = produto.nome || '';

    const tdCategoria = document.createElement('td');
    tdCategoria.textContent = produto.categoria || 'Sem categoria';

    const tdPreco = document.createElement('td');
    tdPreco.textContent = 'R$ ' + preco.toFixed(2).replace('.', ',');

    const tdCusto = document.createElement('td');
    tdCusto.textContent = 'R$ ' + custo.toFixed(2).replace('.', ',');

    const tdAcoes = document.createElement('td');

    const btnEditar = document.createElement('button');
    btnEditar.className = 'btn-editar';
    btnEditar.type = 'button';
    btnEditar.innerHTML = '<i class="fa-solid fa-pen"></i> Editar';
    btnEditar.onclick = () => editarProduto(btnEditar);

    const btnVisibilidade = document.createElement('button');
    btnVisibilidade.type = 'button';
    btnVisibilidade.className = Number(produto.ativo) === 1 ? 'btn-excluir' : 'btn-editar';
    btnVisibilidade.innerHTML = Number(produto.ativo) === 1
        ? '<i class="fa-solid fa-eye-slash"></i> Ocultar'
        : '<i class="fa-solid fa-eye"></i> Reativar';
    btnVisibilidade.onclick = () => alternarVisibilidadeProduto(btnVisibilidade);

    tdAcoes.appendChild(btnEditar);
    tdAcoes.appendChild(btnVisibilidade);

    linha.append(tdImagem, tdNome, tdCategoria, tdPreco, tdCusto, tdAcoes);
    atualizarVisualLinhaProduto(linha);
    return linha;
}

function imagemParaAdmin(caminho) {
    if (!caminho) return '../img/fundo-confeitaria-1.png';
    return '../' + caminho.replace(/^\/+/, '');
}

function atualizarVisualLinhaProduto(linha) {
    const ativo = linha.dataset.ativo === '1';
    linha.style.opacity = ativo ? '1' : '0.55';

    const celulas = linha.querySelectorAll('td');
    if (celulas[1]) {
        celulas[1].title = ativo ? '' : 'Produto oculto do cardápio público';
    }

    const btn = linha.querySelector('.btn-excluir, .btn-editar:last-child');
    if (btn) {
        btn.className = ativo ? 'btn-excluir' : 'btn-editar';
        btn.innerHTML = ativo
            ? '<i class="fa-solid fa-eye-slash"></i> Ocultar'
            : '<i class="fa-solid fa-eye"></i> Reativar';
    }
}

async function salvarNovoProduto(event) {
    event.preventDefault();

    const nome = document.getElementById('prodNome').value.trim();
    const descricao = document.getElementById('prodDescricao').value.trim();
    const selectCategoria = document.getElementById('prodCategoria');
    let categoria = selectCategoria.value;

    if (categoria === 'NOVA_CATEGORIA') {
        categoria = document.getElementById('prodNovaCategoria').value.trim();
    }

    const precoTexto = document.getElementById('prodPreco').value.replace(',', '.').trim();
    const custoTexto = document.getElementById('prodCusto').value.replace(',', '.').trim();
    const preco = parseFloat(precoTexto);
    const custo = parseFloat(custoTexto) || 0.00;
    const arquivo = document.getElementById('prodImagemFile').files[0];

    if (!nome || !categoria || isNaN(preco) || preco < 0) {
        alert('Preencha nome, categoria e preço corretamente.');
        return;
    }

    if (!linhaEmEdicao && !arquivo) {
        alert('Selecione uma imagem para o novo produto.');
        return;
    }

    const formData = new FormData();
    formData.append('nome', nome);
    formData.append('descricao', descricao);
    formData.append('preco', preco.toFixed(2));
    formData.append('custo_producao', custo.toFixed(2));
    formData.append('categoria', categoria);
    if (arquivo) formData.append('imagem', arquivo);

    const editando = !!linhaEmEdicao;
    if (editando) formData.append('id', linhaEmEdicao.dataset.produtoId);

    const endpoint = editando ? '../php/produto_atualizar.php' : '../php/produtos.php';
    const botao = document.getElementById('btnSalvarProduto');
    botao.disabled = true;

    try {
        const resposta = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });
        const dados = await resposta.json();

        if (!resposta.ok || !dados.sucesso) {
            throw new Error(dados.mensagem || 'Não foi possível salvar o produto.');
        }

        alert(editando ? 'Alterações salvas com sucesso!' : 'Doce adicionado ao cardápio com sucesso!');
        fecharModalProduto();
        await carregarProdutosAdmin();
    } catch (erro) {
        console.error(erro);
        alert(erro.message || 'Erro ao salvar o produto.');
    } finally {
        botao.disabled = false;
    }
}

async function alternarVisibilidadeProduto(botao) {
    const linha = botao.closest('tr');
    const id = Number(linha.dataset.produtoId);
    const ativoAtual = linha.dataset.ativo === '1';
    const novoAtivo = !ativoAtual;

    const acao = novoAtivo ? 'reativar' : 'ocultar';
    if (!confirm(`Deseja ${acao} este produto?`)) return;

    botao.disabled = true;

    try {
        const resposta = await fetch('../php/produto_ocultar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, ativo: novoAtivo })
        });
        const dados = await resposta.json();

        if (!resposta.ok || !dados.sucesso) {
            throw new Error(dados.mensagem || 'Não foi possível alterar o produto.');
        }

        linha.dataset.ativo = novoAtivo ? '1' : '0';
        atualizarVisualLinhaProduto(linha);
    } catch (erro) {
        console.error(erro);
        alert(erro.message || 'Erro ao alterar a visibilidade.');
    } finally {
        botao.disabled = false;
    }
}

async function excluirProdutoDaTabela(botao) {
    return alternarVisibilidadeProduto(botao);
}

function fazerLogout() {
    window.location.href = '../index.php';
}

let linhaEstoqueEmEdicao = null;

const IMAGEM_PADRAO_ESTOQUE = 'data:image/svg+xml;utf8,' + encodeURIComponent(
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">' +
    '<rect width="48" height="48" rx="10" fill="#f2d2ce"/>' +
    '<path d="M14 32V18l10-6 10 6v14H14z" fill="none" stroke="#5a3931" stroke-width="2"/>' +
    '<path d="M14 18l10 6 10-6" fill="none" stroke="#5a3931" stroke-width="2"/>' +
    '</svg>'
);

function resolverImagemEstoque(caminhoImagem) {
    if (!caminhoImagem) return IMAGEM_PADRAO_ESTOQUE;
    if (caminhoImagem.startsWith('data:')) return caminhoImagem;
    return '../' + caminhoImagem;
}

async function carregarEstoqueDoBanco() {
    try {
        const resposta = await fetch('../php/estoque.php');
        const dados = await resposta.json();
        if (!dados.sucesso || !Array.isArray(dados.itens)) return;

        const tabela = document.getElementById('tabelaEstoqueCorpo');
        tabela.innerHTML = '';

        dados.itens.forEach((item) => {
            const linha = document.createElement('tr');
            definirDadosDaLinhaEstoque(linha, item.nome, item.descricao, item.categoria, item.quantidade, item.imagem, item.em_falta);
            linha.setAttribute('data-estoque-id', item.id);
            linha.innerHTML = montarHtmlDaLinhaEstoque(item.nome, item.categoria, item.quantidade, resolverImagemEstoque(item.imagem), item.em_falta);
            tabela.appendChild(linha);
        });
    } catch (erro) {
        console.info('Não foi possível carregar o estoque do banco (backend ainda não configurado).');
    }
}

document.addEventListener('DOMContentLoaded', carregarEstoqueDoBanco);

function abrirModalEstoque() {
    linhaEstoqueEmEdicao = null;
    document.getElementById('tituloModalEstoque').innerText = 'Adicionar Item ao Estoque';
    document.getElementById('btnSalvarEstoque').innerText = 'Salvar Item';
    document.getElementById('avisoImagemAtualEstoque').style.display = 'none';
    document.getElementById('modalNovoEstoque').style.display = 'flex';
}

function editarEstoque(botao) {
    const linha = botao.closest('tr');
    linhaEstoqueEmEdicao = linha;

    document.getElementById('estNome').value = linha.getAttribute('data-nome') || '';
    document.getElementById('estDescricao').value = linha.getAttribute('data-descricao') || '';
    document.getElementById('estQuantidade').value = linha.getAttribute('data-quantidade') || '';

    const categoriaAtual = linha.getAttribute('data-categoria') || '';
    const selectCategoria = document.getElementById('estCategoria');
    const opcaoExiste = Array.from(selectCategoria.options).some(op => op.value === categoriaAtual);
    if (!opcaoExiste && categoriaAtual) {
        const novaOpcao = document.createElement('option');
        novaOpcao.value = categoriaAtual;
        novaOpcao.textContent = categoriaAtual;
        const opcaoNovaGlobal = selectCategoria.querySelector('option[value="NOVA_CATEGORIA"]');
        selectCategoria.insertBefore(novaOpcao, opcaoNovaGlobal);
    }
    selectCategoria.value = categoriaAtual;

    document.getElementById('avisoImagemAtualEstoque').style.display = 'block';
    document.getElementById('estEmFalta').checked = linha.getAttribute('data-em-falta') === 'true';

    document.getElementById('tituloModalEstoque').innerText = 'Editar Item do Estoque';
    document.getElementById('btnSalvarEstoque').innerText = 'Salvar Alterações';
    document.getElementById('modalNovoEstoque').style.display = 'flex';
}

function fecharModalEstoque() {
    document.getElementById('modalNovoEstoque').style.display = 'none';
    document.getElementById('formNovoEstoque').reset();
    document.getElementById('grupoNovaCategoriaEstoque').style.display = 'none';
    document.getElementById('estNovaCategoria').removeAttribute('required');
    document.getElementById('avisoImagemAtualEstoque').style.display = 'none';
    linhaEstoqueEmEdicao = null;
}

function verificarNovaCategoriaEstoque(valor) {
    const grupoNova = document.getElementById('grupoNovaCategoriaEstoque');
    const inputNova = document.getElementById('estNovaCategoria');

    if (valor === 'NOVA_CATEGORIA') {
        grupoNova.style.display = 'block';
        inputNova.setAttribute('required', 'true');
        inputNova.focus();
    } else {
        grupoNova.style.display = 'none';
        inputNova.removeAttribute('required');
    }
}

function salvarNovoEstoque(event) {
    event.preventDefault();

    const nome = document.getElementById('estNome').value;
    const descricao = document.getElementById('estDescricao').value;
    const selectCategoria = document.getElementById('estCategoria');
    let categoria = selectCategoria.value;

    if (categoria === 'NOVA_CATEGORIA') {
        const novaCatNome = document.getElementById('estNovaCategoria').value.trim();
        if (novaCatNome) {
            categoria = novaCatNome;

            const novaOpcao = document.createElement('option');
            novaOpcao.value = novaCatNome;
            novaOpcao.textContent = novaCatNome;

            const opcaoNovaGlobal = selectCategoria.querySelector('option[value="NOVA_CATEGORIA"]');
            selectCategoria.insertBefore(novaOpcao, opcaoNovaGlobal);
        }
    }

    const quantidade = parseInt(document.getElementById('estQuantidade').value, 10) || 0;
    const emFalta = document.getElementById('estEmFalta').checked;

    const inputArquivo = document.getElementById('estImagemFile');
    const arquivoNovo = inputArquivo.files && inputArquivo.files[0];

    if (linhaEstoqueEmEdicao && !arquivoNovo) {
        const imagemAtual = linhaEstoqueEmEdicao.getAttribute('data-imagem') || '';
        aplicarEdicaoEstoque(linhaEstoqueEmEdicao, nome, descricao, categoria, quantidade, resolverImagemEstoque(imagemAtual), imagemAtual, emFalta);
        return;
    }

    if (!linhaEstoqueEmEdicao && !arquivoNovo) {
        criarNovaLinhaEstoque(nome, descricao, categoria, quantidade, IMAGEM_PADRAO_ESTOQUE, '', emFalta);
        return;
    }

    if (arquivoNovo) {
        const reader = new FileReader();

        reader.onload = function (e) {
            const imagePreview = e.target.result;
            const caminhoImagem = 'img/' + arquivoNovo.name;

            if (linhaEstoqueEmEdicao) {
                aplicarEdicaoEstoque(linhaEstoqueEmEdicao, nome, descricao, categoria, quantidade, imagePreview, caminhoImagem, emFalta);
            } else {
                criarNovaLinhaEstoque(nome, descricao, categoria, quantidade, imagePreview, caminhoImagem, emFalta);
            }
        };

        reader.readAsDataURL(arquivoNovo);
    }
}

function criarNovaLinhaEstoque(nome, descricao, categoria, quantidade, imagemPreview, caminhoImagem, emFalta) {
    const tabela = document.getElementById('tabelaEstoqueCorpo');

    const novaLinha = document.createElement('tr');
    definirDadosDaLinhaEstoque(novaLinha, nome, descricao, categoria, quantidade, caminhoImagem, emFalta);
    novaLinha.innerHTML = montarHtmlDaLinhaEstoque(nome, categoria, quantidade, imagemPreview, emFalta);

    tabela.appendChild(novaLinha);

    salvarEstoqueNoBanco({
        nome, descricao, quantidade, categoria, imagem: caminhoImagem, em_falta: emFalta
    }).then((idBanco) => {
        if (idBanco) {
            novaLinha.setAttribute('data-estoque-id', idBanco);
        }
    });

    alert('Item adicionado ao estoque com sucesso!');
    fecharModalEstoque();
}

function aplicarEdicaoEstoque(linha, nome, descricao, categoria, quantidade, imagemPreview, caminhoImagem, emFalta) {
    definirDadosDaLinhaEstoque(linha, nome, descricao, categoria, quantidade, caminhoImagem, emFalta);
    linha.innerHTML = montarHtmlDaLinhaEstoque(nome, categoria, quantidade, imagemPreview, emFalta);

    const idBanco = linha.getAttribute('data-estoque-id');
    atualizarEstoqueNoBanco({
        id: idBanco, nome, descricao, quantidade, categoria, imagem: caminhoImagem, em_falta: emFalta
    });

    alert('Alterações salvas com sucesso!');
    fecharModalEstoque();
}

function definirDadosDaLinhaEstoque(linha, nome, descricao, categoria, quantidade, caminhoImagem, emFalta) {
    linha.setAttribute('data-nome', nome);
    linha.setAttribute('data-descricao', descricao);
    linha.setAttribute('data-categoria', categoria);
    linha.setAttribute('data-quantidade', quantidade);
    linha.setAttribute('data-imagem', caminhoImagem);
    linha.setAttribute('data-em-falta', emFalta ? 'true' : 'false');
}

function montarHtmlDaLinhaEstoque(nome, categoria, quantidade, imagemPreview, emFalta) {
    return `
        <td>
            <img src="${imagemPreview}" alt="${nome}" class="img-preview-tabela">
        </td>
        <td>${nome}</td>
        <td>${categoria}</td>
        <td>${quantidade}</td>
        <td>
            <span class="status-badge ${emFalta ? 'esgotado' : 'pago'}">${emFalta ? 'Em falta' : 'Em estoque'}</span>
        </td>
        <td>
            <button class="btn-editar" onclick="editarEstoque(this)">
                <i class="fa-solid fa-pen"></i> Editar
            </button>
            <button class="${emFalta ? 'btn-editar' : 'btn-excluir'}" onclick="alternarEmFaltaEstoque(this)">
                <i class="fa-solid fa-${emFalta ? 'check' : 'triangle-exclamation'}"></i> ${emFalta ? 'Marcar Disponível' : 'Marcar Em Falta'}
            </button>
            <button class="btn-excluir" onclick="excluirEstoqueDaTabela(this)">
                <i class="fa-solid fa-trash"></i> Excluir
            </button>
        </td>
    `;
}

async function alternarEmFaltaEstoque(botao) {
    const linha = botao.closest('tr');
    const nome = linha.getAttribute('data-nome');
    const descricao = linha.getAttribute('data-descricao');
    const categoria = linha.getAttribute('data-categoria');
    const quantidade = linha.getAttribute('data-quantidade');
    const caminhoImagem = linha.getAttribute('data-imagem');
    const novoEmFalta = linha.getAttribute('data-em-falta') !== 'true';

    definirDadosDaLinhaEstoque(linha, nome, descricao, categoria, quantidade, caminhoImagem, novoEmFalta);
    linha.innerHTML = montarHtmlDaLinhaEstoque(nome, categoria, quantidade, resolverImagemEstoque(caminhoImagem), novoEmFalta);

    const idBanco = linha.getAttribute('data-estoque-id');
    if (idBanco) {
        await atualizarEstoqueNoBanco({ id: idBanco, em_falta: novoEmFalta });
    }
}

async function salvarEstoqueNoBanco(item) {
    try {
        const resposta = await fetch('../php/estoque.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(item)
        });
        const dados = await resposta.json();

        if (!dados.sucesso) {
            console.warn('Não foi possível salvar o item no banco:', dados.mensagem);
            return null;
        }

        return dados.item ? dados.item.id : null;
    } catch (erro) {
        console.info('Item de estoque não salvo no banco (backend ainda não configurado).');
        return null;
    }
}

async function atualizarEstoqueNoBanco(item) {
    if (!item.id) {
        console.info('Item de estoque sem id do banco - alteração ficou só na tabela local.');
        return;
    }

    try {
        const resposta = await fetch('../php/estoque_atualizar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(item)
        });
        const dados = await resposta.json();

        if (!dados.sucesso) {
            console.warn('Não foi possível atualizar o item no banco:', dados.mensagem);
        }
    } catch (erro) {
        console.info('Item de estoque não atualizado no banco (backend ainda não configurado).');
    }
}

async function excluirEstoqueDaTabela(botao) {
    const linha = botao.closest('tr');
    const idBanco = linha.getAttribute('data-estoque-id');

    if (idBanco) {
        try {
            await fetch('../php/estoque_excluir.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: idBanco })
            });
        } catch (erro) {
            console.info('Item de estoque não removido do banco (backend ainda não configurado).');
        }
    }

    linha.remove();
}

function trocarAba(nomeAba, evento) {
    const abas = document.querySelectorAll('.aba-conteudo');
    abas.forEach(function(aba) {
        aba.classList.remove('active');
    });

    const botoes = document.querySelectorAll('.nav-btn');
    botoes.forEach(function(botao) {
        botao.classList.remove('active');
    });

    const abaSelecionada = document.getElementById('aba-' + nomeAba);

    if (abaSelecionada) {
        abaSelecionada.classList.add('active');
    }

    if (evento) {
        evento.currentTarget.classList.add('active');
    }

    if (nomeAba === 'produtos') {
        if (typeof carregarProdutosAdmin === 'function') {
            carregarProdutosAdmin();
        }
    }

    if (nomeAba === 'estoque') {
        if (typeof carregarEstoqueDoBanco === 'function') {
            carregarEstoqueDoBanco();
        }
    }
}