<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = conectarBanco();
    $stmt = $pdo->query('SELECT id, nome FROM categorias ORDER BY nome');
    echo json_encode([
        'sucesso' => true,
        'categorias' => $stmt->fetchAll()
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $erro) {
    error_log('Erro ao listar categorias: ' . $erro->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível carregar as categorias.'
    ]);
}
