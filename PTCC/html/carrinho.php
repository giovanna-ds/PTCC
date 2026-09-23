<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Carrinho - Sabino Bistrô</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/carrinho.css">
</head>
<body>

    <nav class="navbar">
        <div class="navbar-container">
            <a href="../index.php" class="navbar-logo">Sabino Bistrô</a>
            <ul class="navbar-menu">
                <li><a href="../index.php">Home</a></li>
                <li><a href="carrinho.php" class="active">Carrinho</a></li>
                <li><a href="favoritos.php">Favoritos</a></li>
            </ul>
        </div>
    </nav>

    <main class="carrinho-main-container">
        <h2>Seu Carrinho de Compras 🛒</h2>

        <div class="carrinho-layout">
            <div class="lista-itens" id="listaItensCarrinho"></div>

            <div class="resumo-carrinho">
                <h3>Resumo do Pedido</h3>
                
                <div class="resumo-linha">
                    <span>Subtotal:</span>
                    <span id="subtotalValor">R$ 0,00</span>
                </div>
                
                <div class="resumo-linha">
                    <span>Taxa de Entrega:</span>
                    <span id="taxaEntrega">R$ 5,00</span>
                </div>

                <hr>

                <div class="resumo-linha total">
                    <span>Total:</span>
                    <span id="totalValor">R$ 0,00</span>
                </div>

                <a href="pagamento.php" id="btnFinalizarCompra" class="btn-finalizar-compra">
                    <i class="fa-solid fa-credit-card"></i> Ir para o Pagamento
                </a>
                
                <a href="../index.php" class="link-continuar">
                    <i class="fa-solid fa-arrow-left"></i> Continuar Comprando
                </a>
            </div>
        </div>
    </main>

    <script src="../js/carrinho.js"></script>
</body>
</html>