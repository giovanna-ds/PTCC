<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

    <link rel="stylesheet" href="../css/cadastro_cliente.css">

    <title>Cadastro</title>
</head>
<body>

    <main class="container">
        <a href="#" onclick="window.history.back(); return false;" class="link-voltar">&larr; Voltar</a>

        <form id="formCadastro">

            <h1>Cadastro</h1>

            <div class="input-box">
                <input type="text" id="cadNome" placeholder="Nome completo" required>
                <i class="bx bxs-user"></i>
            </div>

            <div class="input-box">
                <input type="email" id="cadEmail" placeholder="E-mail" required>
                <i class="bx bxs-envelope"></i>
            </div>

            <div class="input-box">
                <input type="password" id="cadSenha" placeholder="Senha" minlength="6" required>
                <i class="bx bxs-lock-alt"></i>
            </div>

            <div class="input-box">
                <input type="tel" id="cadTelefone" placeholder="Número de Telefone" required>
                <i class="bx bxs-phone"></i>
            </div>

            <div class="input-box">
                <input type="date" id="cadNascimento">
                <i class="bx bxs-calendar"></i>
            </div>

            <button type="submit" class="login">Cadastrar</button>

        </form>

    </main>

    <script src="../js/cadastro.js"></script>
</body>
</html>