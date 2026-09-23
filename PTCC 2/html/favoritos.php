<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Favoritos - Sabino Bistrô</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/favoritos.css">
</head>
<body>

    <nav class="navbar">
        <div class="navbar-container">
            <a href="../index.php" class="navbar-logo">Sabino Bistrô</a>
            <button class="navbar-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
            <ul class="navbar-menu">
                <li><a href="../index.php">Home</a></li>
                <li><a href="carrinho.php">Carrinho</a></li>
                <li><a href="favoritos.php" class="active">Favoritos</a></li>
                <li><a href="../index.php#sobre">Sobre nós</a></li>
                <li>
                    <a href="perfil.php" class="user" aria-label="Perfil do usuário">
                        <i class="fa-solid fa-user"></i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="favoritos-main-container">
        <h2>Meus Favoritos <i class="fa-solid fa-heart"></i></h2>

        <div class="lista-favoritos" id="listaFavoritos"></div>
    </main>

    <div id="messageBox" class="message-box">
        <div class="message-content">
            <h2>Login necessário</h2>
            <p>Você precisa fazer login para realizar um pedido.</p>

            <button onclick="irParaLogin()">Fazer Login</button>
            <button onclick="irParaCadastro()">Criar Conta</button>
            <button onclick="fecharMensagem()">Cancelar</button>
        </div>
    </div>

    <script src="../js/script.js"></script>
    <script src="../js/favoritos.js"></script>
</body>
</html>
