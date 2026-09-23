<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../css/cadastro_cliente.css">
    <title>Login</title>
</head>
<body>
    <div>
        <main class="container">
            <a href="#" onclick="window.history.back(); return false;" class="link-voltar">&larr; Voltar</a>

            <form id="formLogin">
                <h1>Login</h1>
                <div class="input-box">
                    <input id="loginEmail" placeholder="E-mail" type="email" required>
                    <i class="bx bxs-envelope"></i>
                </div>
                <div class="input-box">
                    <input id="loginSenha" placeholder="Senha" type="password" required>
                    <i class="bx bxs-lock-alt"></i>

                </div>

                <div class="remember-forgot">
                <label for="">
                    <input type="checkbox">
                    Lembrar Senha
                </label>
                <a href="recuperar_senha.php">Esqueci minha Senha</a>
                </div>

                <button type="submit" class="login">Login</button>

                <div class="register-link">
                    <p>Não tem uma conta? <a href="cadastro_cliente.php">Cadastre-se</a></p>
                </div>
            </form>

        </main>
    </div>

    <script src="../js/login.js"></script>
</body>
</html>