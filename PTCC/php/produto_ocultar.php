<?php
/**
 * "Exclusão" lógica: o produto não é apagado.
 * Apenas ativo passa para 0 e deixa de aparecer no cardápio público.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/auth.php';

header('Content-Type: application/json; charset=utf-8');

try {
    exigirAdmin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        exit;
    }

    $dados = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int) ($dados['id'] ?? 0);
    $ativo = isset($dados['ativo']) ? (int) (bool) $dados['ativo'] : 0;

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto inválido.']);
        exit;
    }

    $pdo = conectarBanco();
    $stmt = $pdo->prepare('UPDATE produtos SET ativo = :ativo WHERE id = :id');
    $stmt->execute(['ativo' => $ativo ? 1 : 0, 'id' => $id]);

    if ($stmt->rowCount() === 0) {
        $check = $pdo->prepare('SELECT id FROM produtos WHERE id = :id');
        $check->execute(['id' => $id]);
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado.']);
            exit;
        }
    }

    echo json_encode([
        'sucesso' => true,
        'ativo' => $ativo ? 1 : 0,
        'mensagem' => $ativo ? 'Produto reativado.' : 'Produto ocultado do cardápio.'
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $erro) {
    error_log('Erro ao alterar visibilidade do produto: ' . $erro->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro interno ao alterar o produto.'
    ], JSON_UNESCAPED_UNICODE);
}
