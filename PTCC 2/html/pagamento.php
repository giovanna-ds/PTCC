<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - Sabino Bistrô</title>

    <link rel="stylesheet" href="../css/pagamento.css">
</head>
<body>

    <div class="checkout-box">
        <a href="#" onclick="window.history.back(); return false;" class="link-voltar">&larr; Voltar</a>
        <h2>Finalizar Pagamento 🍰</h2>

        <div class="resumo-pedido">
            <h3 id="nome-produto">Produto: Carregando...</h3>
            <p id="qtd-produto">Quantidade: 1</p>
            <span class="valor-destaque" id="total-pagar">Total: R$ 0,00</span>
        </div>

        <form id="form-pagamento">
            <div class="form-campo">
                <label for="email">E-mail para confirmação:</label>
                <input type="email" id="email" placeholder="seuemail@email.com" required>
            </div>

            <button type="submit" class="btn-finalizar" id="btn-submit">Gerar QR Code PIX</button>
        </form>

        <div id="area-pix">
            <p><strong>Escaneie o QR Code ou copie o código:</strong></p>
            <div id="qrcode-pix"></div>
            <p id="pix-timer" class="pix-timer"></p>
            <textarea id="codigo-pix" readonly></textarea>
            <button type="button" id="btn-copiar-pix" onclick="copiarCodigoPix()">Copiar código</button>
            <button type="button" id="btn-gerar-novo-pix" style="display:none;" onclick="gerarPix()">Gerar novo código</button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="../js/pagamento.js"></script>
</body>
</html>