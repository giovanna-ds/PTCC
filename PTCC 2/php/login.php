<?php
/**
 * Login. Ver php/CONTRATO_API.md, item 2.
 *
 * Confere e-mail e senha (hash) direto no banco e devolve os dados do
 * cliente usados depois em perfil.js e nas outras telas.
 */

require_once __DIR__ . '/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true) ?? [];

$email = strtolower(trim((string) ($dados['email'] ?? '')));
$senha = (string) ($dados['senha'] ?? '');

if ($email === '' || $senha === '') {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Informe e-mail e senha.']);
    exit;
}

try {
    $pdo = conectarBanco();

    $consulta = $pdo->prepare(
        'SELECT u.id, u.nome, u.email, u.senha_hash, u.tipo_usuario, u.ativo,
                c.telefone, c.endereco
         FROM usuarios u
         LEFT JOIN clientes c ON c.id = u.id
         WHERE u.email = :email'
    );
    $consulta->execute(['email' => $email]);
    $usuario = $consulta->fetch();

    // Mensagem genérica em ambos os casos (e-mail inexistente ou senha
    // errada), para não revelar quais e-mails estão cadastrados.
    if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
        http_response_code(401);
        echo json_encode(['sucesso' => false, 'mensagem' => 'E-mail ou senha incorretos.']);
        exit;
    }

    if ((int) $usuario['ativo'] === 0) {
        http_response_code(403);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Esta conta está desativada.']);
        exit;
    }

    // Mantém também uma sessão PHP real. Ela é usada para proteger
    // o painel e todos os endpoints administrativos.
    $_SESSION['usuario_id'] = (int) $usuario['id'];
    $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
    $_SESSION['usuario_nome'] = $usuario['nome'];

    echo json_encode([
        'sucesso' => true,
        'cliente' => [
            'id'          => (int) $usuario['id'],
            'nome'        => $usuario['nome'],
            'email'       => $usuario['email'],
            'telefone'    => $usuario['telefone'] ?? '',
            'endereco'    => $usuario['endereco'] ?? '',
            'tipoUsuario' => $usuario['tipo_usuario'],
        ],
    ]);
} catch (Throwable $erro) {
    error_log('Erro no login: ' . $erro->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso'  => false,
        'mensagem' => 'Erro interno ao entrar. Verifique se o banco de dados está configurado corretamente.',
    ]);
}
