<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../css/cadastro_cliente.css">
    <title>Recuperar Senha</title>
</head>
<body>
    <div>
        <main class="container">

            <form id="formBuscarEmail">
                <h1>Recuperar Senha</h1>
                <p class="texto-ajuda">Informe o e-mail cadastrado para redefinir sua senha.</p>

                <div class="input-box">
                    <input id="recEmail" placeholder="E-mail" type="email" required>
                    <i class="bx bxs-envelope"></i>
                </div>

                <button type="submit" class="login">Continuar</button>

                <div class="register-link">
                    <p><a href="login.php">Voltar para o Login</a></p>
                </div>
            </form>

            <form id="formNovaSenha" class="form-oculto">
                <h1>Nova Senha</h1>
                <p class="texto-ajuda">Certo! Agora escolha sua nova senha.</p>

                <div class="input-box">
                    <input id="novaSenha" placeholder="Nova senha" type="password" required minlength="4">
                    <i class="bx bxs-lock-alt"></i>
                </div>

                <div class="input-box">
                    <input id="confirmarSenha" placeholder="Confirmar nova senha" type="password" required minlength="4">
                    <i class="bx bxs-lock-alt"></i>
                </div>

                <button type="submit" class="login">Salvar nova senha</button>
            </form>

        </main>
    </div>

    <style>
        .texto-ajuda { color: #666; font-size: 14px; text-align: center; margin-bottom: 15px; }
        .form-oculto { display: none; }
    </style>

    <script src="../js/recuperar_senha.js"></script>
</body>
</html>
