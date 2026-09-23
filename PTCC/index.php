<?php
require_once __DIR__ . '/php/config.php';

$bancoCardapioDisponivel = false;
$produtosCardapio = [];
$categoriasCardapio = [];

try {
    $pdoCardapio = conectarBanco();

    $stmtCategorias = $pdoCardapio->query(
        'SELECT id, nome FROM categorias ORDER BY nome'
    );
    $categoriasCardapio = $stmtCategorias->fetchAll();

    $stmtProdutos = $pdoCardapio->query(
        'SELECT p.id, p.nome, p.descricao, p.preco, p.imagem, p.categoria_id,
                c.nome AS categoria
         FROM produtos p
         LEFT JOIN categorias c ON c.id = p.categoria_id
         WHERE p.ativo = 1
         ORDER BY p.id DESC'
    );
    $produtosCardapio = $stmtProdutos->fetchAll();
    $bancoCardapioDisponivel = true;
} catch (Throwable $erro) {
    error_log('Cardápio dinâmico indisponível: ' . $erro->getMessage());
}

$produtosPorCategoria = [];
foreach ($produtosCardapio as $produto) {
    $chaveCategoria = (int) ($produto['categoria_id'] ?? 0);
    if (!isset($produtosPorCategoria[$chaveCategoria])) {
        $produtosPorCategoria[$chaveCategoria] = [];
    }
    $produtosPorCategoria[$chaveCategoria][] = $produto;
}

function idSecaoCategoria(int $id): string
{
    return 'secao-banco-categoria-' . $id;
}

function imagemCardapio(string $imagem): string
{
    $imagem = trim($imagem);
    return $imagem !== '' ? $imagem : 'img/fundo-confeitaria-1.png';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confeitaria - Sabino Bistrô</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-logo">Sabino Bistrô</a>
            <button class="navbar-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
            <ul class="navbar-menu">
                <li><a href="#">Home</a></li>
                <li><a href="html/carrinho.php">Carrinho</a></li>
                <li><a href="html/favoritos.php">Favoritos</a></li>
                <li><a href="#sobre">Sobre nós</a></li>
                <li>
                    <a href="html/perfil.php" class="user" aria-label="Perfil do usuário">
                        <i class="fa-solid fa-user"></i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <section class="cardapio-section">
        <section class="cardapio-container">
            
            <div class="cardapio-header">
                <h1 class="cardapio-title">Cardápio</h1>
                
                <div class="filtro-container">
                    <button type="button" onclick="abrirModalEncomendaCustom(event)" class="btn-encomenda-topo">
                         Encomendar item customizado
                    </button>

                    <label for="filtro-cardapio">Ir para:</label>
                    <select id="filtro-cardapio" onchange="rolarParaCategoria(this.value)">
                        <option value="" disabled selected>Selecione...</option>
                        <?php if ($bancoCardapioDisponivel && !empty($categoriasCardapio)): ?>
                            <?php foreach ($categoriasCardapio as $categoria): ?>
                                <option value="<?= htmlspecialchars(idSecaoCategoria((int) $categoria['id']), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($categoria['nome'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="secao-bolopote">Bolo no Pote</option>
                            <option value="secao-bolos">Bolos</option>
                            <option value="secao-cheesecake">Chessecake</option>
                            <option value="secao-churros">Churros</option>
                            <option value="secao-cones">Cones</option>
                            <option value="secao-coxinhas">Coxinhas</option>
                            <option value="secao-fondue">Fondue</option>
                            <option value="secao-morangoamor">Morango do Amor</option>
                            <option value="secao-pipoca">Pipoca Gourmet</option>
                            <option value="secao-pudim">Pudim</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            
            <!-- CARDÁPIO DINÂMICO: dados reais do MySQL -->
            <div id="cardapio-dinamico" style="display: <?= $bancoCardapioDisponivel ? 'block' : 'none' ?>;">
            <?php if ($bancoCardapioDisponivel): ?>
                <?php if (empty($produtosCardapio)): ?>
                    <div class="categoria-bloco">
                        <p class="card-descricao">Nenhum produto disponível no momento.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($categoriasCardapio as $categoria): ?>
                        <?php
                        $categoriaId = (int) $categoria['id'];
                        $produtosCategoria = $produtosPorCategoria[$categoriaId] ?? [];
                        if (empty($produtosCategoria)) continue;
                        ?>
                        <div id="<?= htmlspecialchars(idSecaoCategoria($categoriaId), ENT_QUOTES, 'UTF-8') ?>" class="categoria-bloco">
                            <h2 class="categoria-subtitulo"><?= htmlspecialchars($categoria['nome'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <div class="card-grid">
                                <?php foreach ($produtosCategoria as $produto): ?>
                                    <?php
                                    $produtoId = (int) $produto['id'];
                                    $nome = (string) $produto['nome'];
                                    $descricao = (string) ($produto['descricao'] ?? '');
                                    $preco = (float) $produto['preco'];
                                    $imagem = imagemCardapio((string) ($produto['imagem'] ?? ''));
                                    $categoriaNome = (string) ($categoria['nome'] ?? '');
                                    ?>
                                    <div class="card"
                                         onclick='abrirModal(
                                            <?= json_encode($produtoId) ?>,
                                            <?= json_encode($nome, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                                            <?= json_encode($descricao, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                                            <?= json_encode($preco) ?>,
                                            <?= json_encode($imagem, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                                            <?= json_encode($categoriaNome, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
                                         )'>
                                        <button type="button" class="btn-favorito"
                                                data-id="<?= $produtoId ?>"
                                                data-titulo="<?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>"
                                                data-preco="<?= number_format($preco, 2, '.', '') ?>"
                                                data-imagem="<?= htmlspecialchars($imagem, ENT_QUOTES, 'UTF-8') ?>"
                                                onclick="event.stopPropagation(); toggleFavorito(this)"
                                                aria-label="Favoritar">
                                            <i class="fa-regular fa-heart"></i>
                                        </button>
                                        <img src="<?= htmlspecialchars($imagem, ENT_QUOTES, 'UTF-8') ?>"
                                             alt="<?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>"
                                             class="card-image"
                                             onerror="this.onerror=null;this.src='img/fundo-confeitaria-1.png';">
                                        <div class="card-content">
                                            <h2 class="card-titulo"><?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?></h2>
                                            <p class="card-descricao"><?= htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8') ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
            </div>

            <!-- Fallback original: só aparece quando o MySQL não está disponível. -->
            <div id="cardapio-fixo" style="display: <?= $bancoCardapioDisponivel ? 'none' : 'block' ?>;">

                <!-- SEÇÃO BOLO DE POTE -->
                <div id="secao-bolopote" class="categoria-bloco">
                    <h2 class="categoria-subtitulo">Bolo no Pote</h2>
                    
                    <div class="card-grid">
                        <div class="card" onclick="abrirModal(1, 'Bolo no Pote de Ninho com Morango', 'Delicioso bolo no pote com camadas generosas de recheio de Leite Ninho artesanal e Morango.', 12.00, 'img/Bolo_no_pote_ninho_morango.jpg', 'Bolo no Pote')">
                            <button type="button" class="btn-favorito" data-id="1" data-titulo="Bolo no Pote de Ninho com Morango" data-preco="12.00" data-imagem="img/Bolo_no_pote_ninho_morango.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/Bolo_no_pote_ninho_morango.jpg" alt="Bolo no Pote de Ninho com Morango" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Bolo no Pote de Ninho com Morango</h2>
                                <p class="card-descricao">Delicioso bolo no pote com camadas generosas de recheio de Leite Ninho artesanal e Morango.</p>
                            </div>
                        </div>
                
                        <div class="card" onclick="abrirModal(2, 'Bolo no Pote Red Velvet', 'Bolo no Pote com camadas de massa vermelha aveludada com um toque suave de cacau e um recheio cremoso e azedinho clássico de cream cheese', 14.00, 'img/Bolo_no_pote_Red_Velvet.jpg', 'Bolo no Pote')">
                            <button type="button" class="btn-favorito" data-id="2" data-titulo="Bolo no Pote Red Velvet" data-preco="14.00" data-imagem="img/Bolo_no_pote_Red_Velvet.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/Bolo_no_pote_Red_Velvet.jpg" alt="Bolo no Pote Red Velvet" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Bolo no Pote Red Velvet </h2>
                                <p class="card-descricao"> Bolo no Pote com camadas de massa vermelha aveludada com um toque suave de cacau e um recheio cremoso e azedinho clássico de cream cheese</p>
                            </div>
                        </div>

                        <div class="card" onclick="abrirModal(3, 'Copo da Felicidade de Ferrero Rocher', 'Para os amantes de chocolate: muito recheio de Ferrero Rocher', 12.00, 'img/Copo_felicidade_Ferrero_Rocher.jpg', 'Bolo no Pote')">
                            <button type="button" class="btn-favorito" data-id="3" data-titulo="Copo da Felicidade de Ferrero Rocher" data-preco="12.00" data-imagem="img/Copo_felicidade_Ferrero_Rocher.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/Copo_felicidade_Ferrero_Rocher.jpg" alt="Copo da Felicidade de Ferrero Rocher" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Copo da Felicidade de Ferrero Rocher</h2>
                                <p class="card-descricao">Para os amantes de chocolate: muito recheio de Ferrero Rocher</p>
                            </div>
                        </div>

                        <div class="card" onclick="abrirModal(4, 'Copo Banoffe', 'Uma releitura prática e individual da tradicional torta inglesa, montada em camadas dentro de um copo.Ele une a crocância da massa, a doçura do doce de leite, o frescor da banana e a leveza do chantilly', 12.00, 'img/Copo_Banoffe.jpg', 'Bolo no Pote')">
                            <button type="button" class="btn-favorito" data-id="4" data-titulo="Copo Banoffe" data-preco="12.00" data-imagem="img/Copo_Banoffe.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/Copo_Banoffe.jpg" alt="Copo Banoffe" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Copo Banoffe </h2>
                                <p class="card-descricao"> Uma releitura prática e individual da tradicional torta inglesa, montada em camadas dentro de um copo. Ele une a crocância da massa, a doçura do doce de leite, o frescor da banana e a leveza do chantilly</p>
                            </div>
                        </div>

                        <div class="card" onclick="abrirModal(5, 'Copo da Felicidade de Limão', 'Combina camadas de mousse ou brigadeiro cremoso de limão, farofa de biscoito, e uma finalização de chantininho com raspas de limão.É uma sobremesa refrescante que equilibra o toque cítrico do limão com a doçura do creme', 12.00, 'img/Copo_felicidade_limao.jpg', 'Bolo no Pote')">
                            <button type="button" class="btn-favorito" data-id="5" data-titulo="Copo da Felicidade de Limão" data-preco="12.00" data-imagem="img/Copo_felicidade_limão.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/Copo_felicidade_limao.jpg" alt="Copo da Felicidade de Limão" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Copo da Felicidade de Limão</h2>
                                <p class="card-descricao">Combina camadas de mousse ou brigadeiro cremoso de limão, farofa de biscoito, e uma finalização de chantininho com raspas de limão. É uma sobremesa refrescante que equilibra o toque cítrico do limão com a doçura do creme</p>
                            </div>
                        </div>

                        <div class="card" onclick="abrirModal(6, 'Copo da Felicidade Sensação de Morango', 'Uma sobremesa irresistível que une a cremosidade do chocolate e o frescor do morango', 14.00, 'img/Copo_felicidade_sensacao_morango.jpg', 'Bolo no Pote')">
                            <button type="button" class="btn-favorito" data-id="6" data-titulo="Copo da Felicidade Sensação de Morango" data-preco="14.00" data-imagem="img/Copo_felicidade_sensacao_morango.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/Copo_felicidade_sensacao_morango.jpg" alt="Copo da Felicidade Sensação de Morango" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Copo da Felicidade Sensação de Morango</h2>
                                <p class="card-descricao">Uma sobremesa irresistível que une a cremosidade do chocolate e o frescor do morango</p>
                            </div>
                        </div>

                        <div class="card" onclick="abrirModal(7, 'Copo da Felicidade Kinder', 'Combina creme de Leite Ninho, brigadeiro cremoso ou Nutella, pedaços de brownie ou bolo de chocolate e pedaços crocantes do bombom Kinder Bueno no recheio e na decoração', 14.00, 'img/Copo_da_felicidade_kinder.jpg', 'Bolo no Pote')">
                            <button type="button" class="btn-favorito" data-id="7" data-titulo="Copo da Felicidade Kinder" data-preco="14.00" data-imagem="img/Copo_da_felicidade_kinder.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/Copo_da_felicidade_kinder.jpg" alt="Copo da Felicidade Kinder" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Copo da Felicidade Kinder</h2>
                                <p class="card-descricao">Combina creme de Leite Ninho, brigadeiro cremoso ou Nutella, pedaços de brownie ou bolo de chocolate e pedaços crocantes do bombom Kinder Bueno no recheio e na decoração.</p>
                            </div>
                        </div>

                    </div> 
                </div>

                <!-- SEÇÃO BOLOS -->
                <div id="secao-bolos" class="categoria-bloco">
                    <h2 class="categoria-subtitulo">Bolos</h2>
                    
                    <div class="card-grid">
                        <div class="card" onclick="abrirModal(8, 'Bolo Red Velvet', 'O clássico feito com todo o carinho e ingredientes selecionados.', 190.00, 'img/bolo_fatia_Red_Velvet.jpg', 'Bolos')">
                            <button type="button" class="btn-favorito" data-id="8" data-titulo="Bolo Red Velvet" data-preco="190.00" data-imagem="img/bolo_fatia_Red_Velvet.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/bolo_fatia_Red_Velvet.jpg" alt="Bolo Red Velvet" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Bolo Red Velvet</h2>
                                <p class="card-descricao">O clássico feito com todo o carinho e ingredientes selecionados.</p>
                            </div>
                        </div>
                
                        <div class="card" onclick="abrirModal(9, 'Bolo de Ninho com Morango', 'Bolo artesanal recheado com creme de leite Ninho e morangos frescos.', 130.00, 'img/Fatia_gourmet_ninho_morango.jpg', 'Bolos')">
                            <button type="button" class="btn-favorito" data-id="9" data-titulo="Bolo de  Ninho com Morango" data-preco="130.00" data-imagem="img/Fatia_gourmet_ninho_morango.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/Fatia_gourmet_ninho_morango.jpg" alt="Fatia Ninho com Morango" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Bolo de Ninho com Morango</h2>
                                <p class="card-descricao">Bolo artesanal recheado com creme de leite Ninho e morangos frescos.</p>
                            </div>
                        </div>
                    
                        <div class="card" onclick="abrirModal(10, 'Bolo de Cenoura', 'Bolo de Cenoura com cobertura de Chocolate Gourmet', 55.00, 'img/Bolo_cenoura_chocolate.jpg', 'Bolos')">
                            <button type="button" class="btn-favorito" data-id="10" data-titulo="Bolo de Cenoura" data-preco="55.00" data-imagem="img/Bolo_cenoura_chocolate.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/Bolo_cenoura_chocolate.jpg" alt="Bolo de Cenoura" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Bolo de Cenoura</h2>
                                <p class="card-descricao">Bolo de Cenoura com cobertura de Chocolate Gourmet</p>
                            </div>
                        </div>
                    
                        <div class="card" onclick="abrirModal(11, 'Brownie de Morango', 'O recheio de um Brownie de Morango é uma camada cremosa e marcante feita com brigadeiro branco, ganache de chocolate ou doce de leite, combinada com pedaços de morangos frescos ou uma calda de morango artesanal. O contraste equilibrado o doce intenso da massa com acidez da fruta.', 25.00, 'img/Brownier_Morango.jpg', 'Bolos')">
                            <button type="button" class="btn-favorito" data-id="11" data-titulo="Brownie de Morango" data-preco="25.00" data-imagem="img/Brownier_Morango.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/Brownier_Morango.jpg" alt="Brownie de Morango" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Brownie de Morango</h2>
                                <p class="card-descricao">O recheio de um Brownie de Morango é uma camada cremosa e marcante feita com brigadeiro branco, ganache de chocolate ou doce de leite, combinada com pedaços de morangos frescos ou uma calda de morango artesanal. O contraste equilibrado o doce intenso da massa com acidez da fruta.</p>
                            </div>
                        </div>
                    </div> 
                </div>

                <!-- SEÇÃO CHESSECAKE -->
                <div id="secao-cheesecake" class="categoria-bloco">
                    <h2 class="categoria-subtitulo">Chessecakes</h2>

                    <div class="card-grid">
                        <div class="card" onclick="abrirModal(12, 'Cheesecake de frutas vermelhas(Pequeno)', 'Creme denso, aveludado e levemente ácido, feito tradicionalmente com cream cheese, açúcar, ovos e creme de leite , que equilibra perfeitamente com a calda doce e azedinha de frutas vermelhas por cima.', 100.00, 'img/cheesecake.jpg', 'Chessecakes')">
                            <button type="button" class="btn-favorito" data-id="12" data-titulo="Cheesecake de frutas vermelhas(Pequeno)" data-preco="100.00" data-imagem="img/cheesecake.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/cheesecake.jpg" alt="Cheesecake" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Cheesecake de frutas vermelhas(Pequeno)</h2>
                                <p class="card-descricao">Creme denso, aveludado e levemente ácido, feito tradicionalmente com cream cheese, açúcar, ovos e creme de leite , que equilibra perfeitamente com a calda doce e azedinha de frutas vermelhas por cima.</p>
                            </div>
                        </div>

                        <div class="card" onclick="abrirModal(13, 'Creme denso, aveludado e levemente ácido, equilibrado perfeitamente com a calda doce e azedinha de frutas vermelhas por cima.', 200.00, 'img/cheesecake.jpg')">
                            <button type="button" class="btn-favorito" data-id="13" data-titulo="Cheesecake de frutas vermelhas (Grande)" data-preco="200.00" data-imagem="img/cheesecake.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/cheesecake.jpg" alt="Cheesecake Grande" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Cheesecake de frutas vermelhas (Grande)</h2>
                                <p class="card-descricao">Creme denso, aveludado e levemente ácido, equilibrado com a calda de frutas vermelhas por cima.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO CHURROS -->
                <div id="secao-churros" class="categoria-bloco">
                    <h2 class="categoria-subtitulo">Churros</h2>

                    <div class="card-grid">
                        <div class="card" onclick="abrirModal(14, 'Churros de Doce de Leite com Granulado', 'Uma massa firme, porém macia. Um recheio caprichado de doce de leite caseiro com granulado de chocolate por cima.', 10.00, 'img/churros.jpeg', 'Churros')">
                            <button type="button" class="btn-favorito" data-id="14" data-titulo="Churros de Doce de Leite com Granulado" data-preco="10.00" data-imagem="img/churros.jpeg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/churros.jpeg" alt="Churros de Doce de Leite com Granulado" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Churros de Doce de Leite com Granulado</h2>
                                <p class="card-descricao">Uma massa firme, porém macia. Um recheio caprichado de doce de leite caseiro com granulado de chocolate por cima.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO CONES -->
                <div id="secao-cones" class="categoria-bloco">
                    <h2 class="categoria-subtitulo">Cones</h2>

                    <div class="card-grid">
                        <div class="card" onclick="abrirModal(15, 'Cone de Morango trufado', 'Combina uma casquinha de sorvete crocante blindada por dentro com chocolate, recheio cremoso de brigadeiro gourmet (ao leite) e pedaços frescos de morango, finalizado com um lacre de chocolate.', 14.00, 'img/cone_trufado_de_morango.jpg', 'Cones')">
                            <button type="button" class="btn-favorito" data-id="15" data-titulo="Cone de Morango trufado" data-preco="14.00" data-imagem="img/cone_trufado_de_morango.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/cone_trufado_de_morango.jpg" alt="Cone de Morango trufado" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Cone de Morango trufado</h2>
                                <p class="card-descricao">Combina uma casquinha de sorvete crocante blindada por dentro com chocolate, recheio cremoso de brigadeiro gourmet (ao leite) e pedaços frescos de morango, finalizado com um lacre de chocolate.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO COXINHAS -->
                <div id="secao-coxinhas" class="categoria-bloco">
                    <h2 class="categoria-subtitulo">Coxinhas</h2>

                    <div class="card-grid">
                        <div class="card" onclick="abrirModal(16, 'Coxinha de Morango de Ferrero Rocher', 'A coxinha de morango sabor Ferrero Rocher tem um morango fresco no centro.Ele é envolvido por brigadeiro gourmet de chocolate com Nutella ou macarrão de avelã. Por fora, o doce recebe uma camada crocante de chocolate com pedaços de amendoim triturados.', 18.00, 'img/Coxinha_de_morango.jpg', 'Coxinhas')">
                            <button type="button" class="btn-favorito" data-id="16" data-titulo="Coxinha de Morango de Ferrero Rocher" data-preco="18.00" data-imagem="img/Coxinha_de_morango.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/Coxinha_de_morango.jpg" alt="Coxinha de Morango de Ferrero Rocher" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Coxinha de Morango de Ferrero Rocher</h2>
                                <p class="card-descricao">A coxinha de morango sabor Ferrero Rocher tem um morango fresco no centro. Ele é envolvido por brigadeiro gourmet de chocolate com Nutella ou macarrão de avelã. Por fora, o doce recebe uma camada crocante de chocolate com pedaços de amendoim triturados.</p>
                            </div>
                        </div>

                        <div class="card" onclick="abrirModal(17, 'Coxinha de Morango Ninho com Nutella', 'Um doce gourmet feito com uma base de brigadeiro cremoso de leite Ninho, envolvendo um morango fresco e suculento por dentro, empanado em leite em pó e finalizado com uma cobertura generosa de creme de avelã.', 18.00, 'img/Coxinha_de_morango_ninho_nutella.jpg', 'Coxinhas')">
                            <button type="button" class="btn-favorito" data-id="17" data-titulo="Coxinha de Morango Ninho com Nutella" data-preco="18.00" data-imagem="img/Coxinha_de_morango_ninho_nutella.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/Coxinha_de_morango_ninho_nutella.jpg" alt="Coxinha de Morango Ninho com Nutella" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Coxinha de Morango Ninho com Nutella</h2>
                                <p class="card-descricao">Um doce gourmet feito com uma base de brigadeiro cremoso de leite Ninho, envolvendo um morango fresco e suculento por dentro, empanado em leite em pó e finalizado com uma cobertura generosa de creme de avelã.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO FONDUE -->
                <div id="secao-fondue" class="categoria-bloco">
                    <h2 class="categoria-subtitulo">Fondue</h2>

                    <div class="card-grid">
                        <div class="card" onclick="abrirModal(18, 'Fondue na Marmita', 'A base doce leva ganaches cremosas de chocolate acompanhadas de frutas frescas.', 35.00, 'img/Fondue_na_marmita.jpg', 'Fondue')">
                            <button type="button" class="btn-favorito" data-id="18" data-titulo="Fondue na Marmita" data-preco="35.00" data-imagem="img/Fondue_na_marmita.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/Fondue_na_marmita.jpg" alt="Fondue na Marmita" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Fondue na Marmita</h2>
                                <p class="card-descricao"> A base doce leva ganaches cremosas de chocolate acompanhadas de frutas frescas.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO MORANGO DO AMOR -->
                <div id="secao-morangoamor" class="categoria-bloco">
                    <h2 class="categoria-subtitulo">Morango do Amor</h2>
                    
                    <div class="card-grid">
                        <div class="card" onclick="abrirModal(19, 'Morango do Amor Tradicional', 'Um doce feito com um brigadeiro cremoso, usado para envolver o morango fresco antes de receber uma casquinha crocante de caramelo vermelho. Ele equilibra o azedinho da fruta com a doçura do doce.', 18.00, 'img/morango_do_amor.jpg', 'Morango do Amor')">
                            <button type="button" class="btn-favorito" data-id="19" data-titulo="Morango do Amor Tradicional" data-preco="18.00" data-imagem="img/morango_do_amor.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/morango_do_amor.jpg" alt="Morango do Amor Tradicional" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Morango do Amor</h2>
                                <p class="card-descricao"> Um doce feito com um brigadeiro cremoso, usado para envolver o morango fresco antes de receber uma casquinha crocante de caramelo vermelho. Ele equilibra o azedinho da fruta com a doçura do doce.</p>
                            </div>
                        </div>
                
                        <div class="card" onclick="abrirModal(20, 'Morango do Amor de Pistache', 'É um brigadeiro cremoso verde feito com leite condensado, creme de leite, manteiga e pasta pura de pistache, que envolve o morango fresco antes de receber a camada de calda crocante de açúcar.', 18.00, 'img/morango_do_amor-pistache1.jpeg', 'Morango do Amor')">
                            <button type="button" class="btn-favorito" data-id="20" data-titulo="Morango do Amor de Pistache" data-preco="18.00" data-imagem="img/morango_do_amor-pistache1.jpeg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/morango_do_amor-pistache1.jpeg" alt="Morango do Amor de Pistache" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Morango do Amor de Pistache</h2>
                                <p class="card-descricao">É um brigadeiro cremoso verde feito com leite condensado, creme de leite, manteiga e pasta pura de pistache, que envolve o morango fresco antes de receber a camada de calda crocante de açúcar.</p>
                            </div>
                        </div>

                        <div class="card" onclick="abrirModal(21, 'Bombom de Morango', 'É um bombom de brigadeiro branco cremoso (feito com leite condensado, creme de leite, leite em pó e manteiga) que envolve completamente um morango fresco e inteiro , criando um contraste entre o doce marcante e a acidez da fruta.', 60.00, 'img/bombom_de_morango.jpg', 'Morango do Amor')">
                            <button type="button" class="btn-favorito" data-id="21" data-titulo="Bombom de Morango" data-preco="16.00" data-imagem="img/bombom_de_morango.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/bombom_de_morango.jpg" alt="Bombom de Morango" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Bombom de Morango</h2>
                                <p class="card-descricao">É um bombom de brigadeiro branco cremoso (feito com leite condensado, creme de leite, leite em pó e manteiga) que envolve completamente um morango fresco e inteiro , criando um contraste entre o doce marcante e a acidez da fruta.</p>
                            </div>
                        </div>

                    </div> 
                </div>

                <!-- SEÇÃO PIPOCA GOURMET -->
                <div id="secao-pipoca" class="categoria-bloco">
                    <h2 class="categoria-subtitulo">Pipoca Gourmet</h2>

                    <div class="card-grid">
                        <div class="card" onclick="abrirModal(22, 'Pipoca Gourmet de Chocolate', 'Pipoca crocante envolvida por uma deliciosa cobertura de chocolate, com pedacinhos e textura irresistíveis, perfeita para quem ama um sabor intenso e docinho.', 0.00, 'img/pipoca.jpg', 'Pipoca Gourmet')">
                            <button type="button" class="btn-favorito" data-id="22" data-titulo="Pipoca Gourmet de Chocolate" data-preco="0.00" data-imagem="img/pipoca.jpg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/pipoca.jpg" alt="Pipoca Gourmet de Chocolate" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Pipoca Gourmet de Chocolate</h2>
                                <p class="card-descricao">Pipoca crocante envolvida por uma deliciosa cobertura de chocolate, com pedacinhos e textura irresistíveis, perfeita para quem ama um sabor intenso e docinho.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO PUDIM -->
                <div id="secao-pudim" class="categoria-bloco">
                    <h2 class="categoria-subtitulo">Pudim</h2>

                    <div class="card-grid">
                        <div class="card" onclick="abrirModal(23, 'Pudim de Leite Condensado(Pequeno)', 'É um creme liso, denso e aveludado, feito com uma batida de leite condensado, ovos e leite integral , cozido em banho-maria e finalizado com uma camada de calda de caramelo dourado', 12.00, 'img/pudim.jpeg', 'Pudim')">
                            <button type="button" class="btn-favorito" data-id="23" data-titulo="Pudim de Leite Condensado(Pequeno)" data-preco="12.00" data-imagem="img/pudim.jpeg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/pudim.jpeg" alt="Pudim de Leite Condensado (Pequeno)" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Pudim de Leite Condensado(Pequeno)</h2>
                                <p class="card-descricao">Creme liso, denso e aveludado, feito com uma batida de leite condensado, ovos e leite integral , cozido em banho-maria e finalizado com uma camada de calda de caramelo dourado.</p>
                            </div>
                        </div>

                        <div class="card" onclick="abrirModal(24, 'Pudim de Leite Condensado (Grande)', 'Creme liso, denso e aveludado, feito com leite condensado, ovos e leite integral, finalizado com calda de caramelo dourado', 55.00, 'img/pudim.jpeg')">
                            <button type="button" class="btn-favorito" data-id="24" data-titulo="Pudim de Leite Condensado (Grande)" data-preco="55.00" data-imagem="img/pudim.jpeg" onclick="event.stopPropagation(); toggleFavorito(this)" aria-label="Favoritar"><i class="fa-regular fa-heart"></i></button>
                            <img src="img/pudim.jpeg" alt="Pudim de Leite Condensado Grande" class="card-image">
                            <div class="card-content">
                                <h2 class="card-titulo">Pudim de Leite Condensado (Grande)</h2>
                                <p class="card-descricao">Creme liso, denso e aveludado finalizado com uma camada de calda de caramelo dourado.</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <div id="secao-novos-produtos" class="categoria-bloco" style="display:none;">
                <h2 class="categoria-subtitulo">Novidades</h2>
                <div class="card-grid" id="gridNovosProdutos"></div>
            </div>

        </section>
    </section>

    <div id="messageBox" class="message-box">
        <div class="message-content">
            <h2>Login necessário</h2>
            <p>Você precisa fazer login para realizar um pedido.</p>
    
            <button onclick="irParaLogin()">Fazer Login</button>
            <button onclick="irParaCadastro()">Criar Conta</button>
            <button onclick="fecharMensagem()">Cancelar</button>
        </div>
    </div>
    
    <section class="sobre-nos" id="sobre">
        <div class="sobre-container">
            <h2>Sobre Nós</h2>
    
            <p>
                Minha história como empreendedora começou em um dos momentos mais desafiadores que vivemos: a pandemia. Com a redução da renda da nossa família, passei a me preocupar com o futuro e comecei a buscar uma forma de contribuir com o orçamento da casa.
            </p>
    
            <p>
                Foi então que decidi transformar vontade e dedicação em uma oportunidade. Comecei produzindo palha italiana, bolos no pote, cones recheados e pipocas gourmet, sempre com muito carinho e cuidado em cada detalhe.
            </p>
    
            <p>
                O que começou como uma maneira de complementar a renda logo se transformou em um sonho. A cada cliente satisfeito, a cada elogio e a cada nova encomenda, eu encontrava mais motivação para continuar.
            </p>

            <p>Hoje, depois de sete anos de muito trabalho, dedicação e amor pelo que faço, tenho a alegria de ver que minha loja cresceu e conquistou a confiança de muitos clientes. Mais do que vender doces, meu objetivo é proporcionar momentos de felicidade através de produtos feitos com qualidade, carinho e muito sabor.
            </p>
        </div>
    </section>
   
    <!-- MODAL DE PRODUTO PADRÃO -->
    <div id="modalProduto" class="modal-produto">
        <div class="modal-conteudo">
            <span class="fechar-modal" onclick="fecharModal()">&times;</span>
            
            <div class="modal-body">
                <img id="modalImagem" src="" alt="Imagem do produto" class="modal-img">
                
                <div class="modal-info">
                    <h2 id="modalTitulo">Nome do Produto</h2>
                    <p id="modalDescricao">Descrição do produto...</p>
                    <span id="modalPreco" class="modal-preco">R$ 0,00</span>

                    <div class="quantidade-container">
                        <label>Quantidade:</label>
                        <div class="qtd-seletor">
                            <button type="button" onclick="alterarQtd(-1)">-</button>
                            <input type="number" id="qtdInput" value="1" min="1" readonly>
                            <button type="button" onclick="alterarQtd(1)">+</button>
                        </div>
                    </div>

                    <div class="modal-acoes">
                        <button class="btn-carrinho" onclick="adicionarAoCarrinhoModal()">
                            <i class="fa-solid fa-cart-shopping"></i> Adicionar ao Carrinho
                        </button>
                        <button class="btn-comprar" onclick="comprarAgoraModal()">
                            <i class="fa-solid fa-bolt"></i> Comprar Agora
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL POPUP PARA ENCOMENDAS PERSONALIZADAS -->
    <div id="modalEncomendaCustom" class="modal-produto">
        <div class="modal-conteudo modal-encomenda-conteudo">
            <span class="fechar-modal" onclick="fecharModalEncomendaCustom()">&times;</span>
            
            <h2 class="modal-encomenda-titulo"> Faça sua Encomenda Especial</h2>
            <p class="modal-encomenda-subtitulo">Monte um pedido personalizado de qualquer produto para festas, eventos ou quantidades especiais!</p>

            <form id="formEncomendaCustom" onsubmit="encomendarProdutoCustom(event)" class="form-encomenda-custom">
                <div class="campo-encomenda">
                    <label for="encTipoItem">Tipo de Doce / Categoria:</label>
                    <select id="encTipoItem" required>
                        <option value="Bolo de Festa / Aniversário">Bolo de Festa / Aniversário</option>
                        <option value="Kit de Bolos no Pote">Kit de Bolos no Pote</option>
                        <option value="Coxinhas e Doces Gourmet">Coxinhas e Doces Gourmet</option>
                        <option value="Pipoca Gourmet / Lembrancinhas">Pipoca Gourmet / Lembrancinhas</option>
                        <option value="Outro Doce Especial">Outro Doce Personalizado</option>
                    </select>
                </div>

                <div class="campo-encomenda">
                    <label for="encQuantidade">Quantidade desejada / Tamanho:</label>
                    <input type="text" id="encQuantidade" placeholder="Ex: 50 unidades, Bolo de 2kg, Kit com 10 potes..." required>
                </div>

                <div class="campo-encomenda">
                    <label for="encSabores">Sabores, Recheios ou Ingredientes Preferidos:</label>
                    <input type="text" id="encSabores" placeholder="Ex: Ninho com Morango, Brigadeiro 50%, Nutella..." required>
                </div>

                <div class="campo-encomenda">
                    <label for="encDetalhes">Detalhes adicionais / Decoração / Observações:</label>
                    <textarea id="encDetalhes" placeholder="Descreva os detalhes da personalização (cores, embalagem, tema da festa, data de entrega...)" rows="3"></textarea>
                </div>

                <div class="modal-encomenda-rodape">
                    <p class="aviso-orcamento">* O valor final será confirmado via WhatsApp de acordo com as especificações da encomenda.</p>
                    <button type="submit" class="btn-carrinho">
                        <i class="fa-solid fa-cart-plus"></i> Adicionar Encomenda ao Carrinho
                    </button>
                </div>
            </form>
        </div>
    </div>

    <a href="html/admin.php" style="
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #5a3931;
        color: #ffffff;
        padding: 12px 20px;
        border-radius: 50px;
        text-decoration: none;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 14px;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        border: 2px solid #e57b85;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.2s ease;
    " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
        ⚙️ Painel Admin
    </a>

    <script src="js/script.js"></script>
</body>
</html>