<?php


require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true) ?? [];

$nome           = trim((string) ($dados['nome'] ?? ''));
$email          = strtolower(trim((string) ($dados['email'] ?? '')));
$senha          = (string) ($dados['senha'] ?? '');
$telefone       = trim((string) ($dados['telefone'] ?? ''));
$dataNascimento = trim((string) ($dados['dataNascimento'] ?? ''));

if ($nome === '' || $email === '' || $senha === '') {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha nome, e-mail e senha.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Informe um e-mail válido.']);
    exit;
}

if (strlen($senha) < 6) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'A senha deve ter pelo menos 6 caracteres.']);
    exit;
}

try {
    $pdo = conectarBanco();

    $verifica = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
    $verifica->execute(['email' => $email]);
    if ($verifica->fetch()) {
        http_response_code(409);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Este e-mail já está cadastrado.']);
        exit;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $pdo->beginTransaction();

    $inserirUsuario = $pdo->prepare(
        'INSERT INTO usuarios (nome, email, senha_hash, tipo_usuario, email_verificado)
         VALUES (:nome, :email, :senha_hash, "cliente", 1)'
    );
    $inserirUsuario->execute([
        'nome'       => $nome,
        'email'      => $email,
        'senha_hash' => $senhaHash,
    ]);

    $novoId = (int) $pdo->lastInsertId();

    $inserirCliente = $pdo->prepare(
        'INSERT INTO clientes (id, telefone, endereco, data_nascimento)
         VALUES (:id, :telefone, NULL, :data_nascimento)'
    );
    $inserirCliente->execute([
        'id'              => $novoId,
        'telefone'        => $telefone !== '' ? $telefone : null,
        'data_nascimento' => $dataNascimento !== '' ? $dataNascimento : null,
    ]);

    $pdo->commit();

    echo json_encode(['sucesso' => true, 'mensagem' => 'Cadastro realizado com sucesso!']);
} catch (Throwable $erro) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro no cadastro: ' . $erro->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso'  => false,
        'mensagem' => 'Erro interno ao cadastrar. Verifique se o banco de dados está configurado corretamente.',
    ]);
}
