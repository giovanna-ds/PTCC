
<?php
require_once __DIR__ . '/../php/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo_usuario'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Sabino Bistrô</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css"> 
</head>
<body>

    <div class="admin-layout">
        
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Sabino Bistrô</h2>
                <span class="badge-cargo">Painel da Loja</span>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <button class="nav-btn active" onclick="trocarAba('dashboard', event)">
                            <i class="fa-solid fa-chart-pie"></i> Resumo
                        </button>
                    </li>
                    <li>
                        <button class="nav-btn" onclick="trocarAba('financas', event)">
                            <i class="fa-solid fa-wallet"></i> Finanças e Lucro
                        </button>
                    </li>
                    <li>
                        <button class="nav-btn" onclick="trocarAba('pagamentos', event)">
                            <i class="fa-solid fa-receipt"></i> Pedidos e Pix
                        </button>
                    </li>
                    <li>
                        <button class="nav-btn" onclick="trocarAba('produtos', event)">
                            <i class="fa-solid fa-utensils"></i> Cardápio
                        </button>
                    </li>
                    <li>
                        <button class="nav-btn" onclick="trocarAba('estoque', event)">
                            <i class="fa-solid fa-boxes-stacked"></i> Estoque
                        </button>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <button id="btnSair" class="btn-logout" onclick="fazerLogout()">
                    <i class="fa-solid fa-arrow-left"></i> Voltar ao Site
                </button>
            </div>
        </aside>

        <main class="main-content">

            <section id="aba-dashboard" class="aba-conteudo active">
                <div class="secao-header-simples">
                    <h1>Resumo do Dia</h1>
                    <p class="subtitulo-secao">Acompanhamento das vendas da sua confeitaria.</p>
                </div>
                
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <h3>Vendas de Hoje</h3>
                        <p class="kpi-valor" id="kpiVendasHoje">R$ 0,00</p>
                    </div>
                    <div class="kpi-card">
                        <h3>Aguardando Pix</h3>
                        <p class="kpi-valor" id="kpiAguardandoPix">0 pedidos</p>
                    </div>
                </div>
            </section>

            <!-- ABA FINANÇAS E LUCRO -->
            <section id="aba-financas" class="aba-conteudo">
                <div class="secao-header-simples">
                    <h1>Finanças e Lucro</h1>
                    <p class="subtitulo-secao">Análise do balanço financeiro e margens de lucro dos produtos vendidos.</p>
                </div>

                <div class="kpi-grid">
                    <div class="kpi-card">
                        <h3>Faturamento Total</h3>
                        <p class="kpi-valor" id="finFaturamento">R$ 1.450,00</p>
                    </div>
                    <div class="kpi-card">
                        <h3>Custo de Produção</h3>
                        <p class="kpi-valor" id="finCustos" style="color: #c0392b;">R$ 480,00</p>
                    </div>
                    <div class="kpi-card">
                        <h3>Lucro Líquido</h3>
                        <p class="kpi-valor" id="finLucro" style="color: #27ae60;">R$ 970,00</p>
                    </div>
                    <div class="kpi-card">
                        <h3>Margem de Lucro Média</h3>
                        <p class="kpi-valor" id="finMargem">66,8%</p>
                    </div>
                </div>

                <div class="tabela-container" style="margin-top: 30px;">
                    <h3 style="padding: 20px 20px 0; color: #5a3931; font-family: 'Playfair Display', serif;">Histórico de Vendas e Margens</h3>
                    <table class="tabela-admin">
                        <thead>
                            <tr>
                                <th># ID</th>
                                <th>Item / Pedido</th>
                                <th>Preço Venda</th>
                                <th>Custo Prod.</th>
                                <th>Lucro Un.</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#1001</td>
                                <td>2x Fatia Red Velvet</td>
                                <td>R$ 30,00</td>
                                <td>R$ 10,00</td>
                                <td><strong style="color: #27ae60;">+ R$ 20,00</strong></td>
                                <td>20/09/2026</td>
                            </tr>
                            <tr>
                                <td>#1002</td>
                                <td>1x Bolo Personalizado (Ninho/Morango)</td>
                                <td>R$ 120,00</td>
                                <td>R$ 45,00</td>
                                <td><strong style="color: #27ae60;">+ R$ 75,00</strong></td>
                                <td>20/09/2026</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="aba-pagamentos" class="aba-conteudo">
                <div class="secao-header-simples">
                    <h1>Pedidos Recebidos</h1>
                    <p class="subtitulo-secao">Confirme os pagamentos via Pix antes de preparar o pedido:</p>
                </div>
                
                <div class="tabela-container">
                    <table class="tabela-admin">
                        <thead>
                            <tr>
                                <th># ID</th>
                                <th>Cliente</th>
                                <th>WhatsApp</th>
                                <th>Itens</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaVendasCorpo">
                            <tr>
                                <td>#1001</td>
                                <td>Maria Silva</td>
                                <td>
                                    <a href="https://wa.me/5511999999999" target="_blank" class="btn-zap">
                                        <i class="fa-brands fa-whatsapp"></i> Chat
                                    </a>
                                </td>
                                <td>2x Fatia Red Velvet</td>
                                <td>R$ 30,00</td>
                                <td><span class="status-badge pendente">Aguardando Pix</span></td>
                                <td>
                                    <button class="btn-aprovar" onclick="confirmarPagamentoPedido(1001)">
                                        <i class="fa-solid fa-check"></i> Confirmar Pix
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="aba-produtos" class="aba-conteudo">
                <div class="cabecalho-secao">
                    <div>
                        <h1>Gerenciar Cardápio</h1>
                        <p class="subtitulo-secao">Cadastre, edite e oculte itens do cardápio. Os produtos ocultos continuam salvos no banco.</p>
                    </div>
                    <button class="btn-principal" onclick="abrirModalProduto()">
                        <i class="fa-solid fa-plus"></i> Novo Doce
                    </button>
                </div>

                <div class="tabela-container">
                    <table class="tabela-admin">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nome</th>
                                <th>Categoria</th>
                                <th>Preço Venda</th>
                                <th>Custo Prod.</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaProdutosCorpo"></tbody>
                    </table>
                </div>
            </section>

            <section id="aba-estoque" class="aba-conteudo">
                <div class="cabecalho-secao">
                    <div>
                        <h1>Gerenciar Estoque</h1>
                        <p class="subtitulo-secao">Cadastre os itens do seu estoque e controle a quantidade disponível.</p>
                    </div>
                    <button class="btn-principal" onclick="abrirModalEstoque()">
                        <i class="fa-solid fa-plus"></i> Novo Item
                    </button>
                </div>

                <div class="tabela-container">
                    <table class="tabela-admin">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nome</th>
                                <th>Categoria</th>
                                <th>Quantidade</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaEstoqueCorpo">
                            <!-- Itens de estoque cadastrados aparecem aqui -->
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

    <!-- MODAL DE NOVO DOCE COM CUSTO DE PRODUÇÃO -->
    <div id="modalNovoProduto" class="modal-admin" style="display: none;">
        <div class="modal-admin-conteudo">
            <div class="modal-admin-header">
                <h2 id="tituloModalProduto">Adicionar Novo Doce</h2>
                <button class="fechar-modal" onclick="fecharModalProduto()">&times;</button>
            </div>
            
            <form id="formNovoProduto" onsubmit="salvarNovoProduto(event)">
                <div class="campo-grupo">
                    <label for="prodNome">Nome do Doce:</label>
                    <input type="text" id="prodNome" placeholder="Ex: Fatia Red Velvet" required>
                </div>

                <div class="campo-grupo">
                    <label for="prodDescricao">Descrição:</label>
                    <textarea id="prodDescricao" placeholder="Descreva o doce (ingredientes, sabor, tamanho...)" rows="3" required></textarea>
                </div>

                <div class="campo-grupo">
                    <label for="prodCategoria">Categoria:</label>
                    <select id="prodCategoria" onchange="verificarNovaCategoria(this.value)" required>
                        <option value="Bolos e CopoCakes">Bolos e CopoCakes</option>
                        <option value="Bolo de Pote">Bolo de Pote</option>
                        <option value="Morango do Amor">Morango do Amor</option>
                        <option value="NOVA_CATEGORIA">+ Adicionar Nova Categoria...</option>
                    </select>
                </div>

                <div class="campo-grupo" id="grupoNovaCategoria" style="display: none;">
                    <label for="prodNovaCategoria">Nome da Nova Categoria:</label>
                    <input type="text" id="prodNovaCategoria" placeholder="Ex: Tortas Geladas">
                </div>

                <div style="display: flex; gap: 10px;">
                    <div class="campo-grupo" style="flex: 1;">
                        <label for="prodPreco">Preço Venda (R$):</label>
                        <input type="text" id="prodPreco" placeholder="15,00" onblur="formatarCampoPreco(this)" required>
                    </div>
                    <div class="campo-grupo" style="flex: 1;">
                        <label for="prodCusto">Custo Produção (R$):</label>
                        <input type="text" id="prodCusto" placeholder="5,00" onblur="formatarCampoPreco(this)" required>
                    </div>
                </div>

                <div class="campo-grupo">
                    <label for="prodImagemFile">Imagem do Doce:</label>
                    <input type="file" id="prodImagemFile" accept="image/*">
                    <small id="avisoImagemAtual" style="display:none; color:#8a4a4b;">Deixe em branco para manter a imagem atual.</small>
                </div>

                <div class="modal-admin-footer">
                    <button type="button" class="btn-cancelar" onclick="fecharModalProduto()">Cancelar</button>
                    <button type="submit" class="btn-principal" id="btnSalvarProduto">Salvar Doce</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalNovoEstoque" class="modal-admin" style="display: none;">
        <div class="modal-admin-conteudo">
            <div class="modal-admin-header">
                <h2 id="tituloModalEstoque">Adicionar Item ao Estoque</h2>
                <button class="fechar-modal" onclick="fecharModalEstoque()">&times;</button>
            </div>

            <form id="formNovoEstoque" onsubmit="salvarNovoEstoque(event)">
                <div class="campo-grupo">
                    <label for="estNome">Nome do Item:</label>
                    <input type="text" id="estNome" placeholder="Ex: Farinha de Trigo" required>
                </div>

                <div class="campo-grupo">
                    <label for="estDescricao">Descrição:</label>
                    <textarea id="estDescricao" placeholder="Detalhes do item (marca, medida, observações...)" rows="3"></textarea>
                </div>

                <div class="campo-grupo">
                    <label for="estCategoria">Categoria:</label>
                    <select id="estCategoria" onchange="verificarNovaCategoriaEstoque(this.value)" required>
                        <option value="Ingredientes">Ingredientes</option>
                        <option value="Embalagens">Embalagens</option>
                        <option value="Descartáveis">Descartáveis</option>
                        <option value="NOVA_CATEGORIA">+ Adicionar Nova Categoria...</option>
                    </select>
                </div>

                <div class="campo-grupo" id="grupoNovaCategoriaEstoque" style="display: none;">
                    <label for="estNovaCategoria">Nome da Nova Categoria:</label>
                    <input type="text" id="estNovaCategoria" placeholder="Ex: Utensílios">
                </div>

                <div class="campo-grupo">
                    <label for="estQuantidade">Quantidade em estoque:</label>
                    <input type="number" id="estQuantidade" min="0" step="1" placeholder="Ex: 20" required>
                </div>

                <div class="campo-grupo campo-checkbox">
                    <label for="estEmFalta">
                        <input type="checkbox" id="estEmFalta">
                        Marcar como "Em Falta"
                    </label>
                </div>

                <div class="campo-grupo">
                    <label for="estImagemFile">Foto do Item (opcional):</label>
                    <input type="file" id="estImagemFile" accept="image/*">
                    <small id="avisoImagemAtualEstoque" style="display:none; color:#8a4a4b;">Deixe em branco para manter a imagem atual.</small>
                </div>

                <div class="modal-admin-footer">
                    <button type="button" class="btn-cancelar" onclick="fecharModalEstoque()">Cancelar</button>
                    <button type="submit" class="btn-principal" id="btnSalvarEstoque">Salvar Item</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>