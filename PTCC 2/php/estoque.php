<?php
/**
 * Lista (GET) e cria (POST) itens de estoque. Ver php/CONTRATO_API.md,
 * item 10, e php/lib/estoque_store.php para como os dados são
 * guardados hoje (arquivo JSON, enquanto o banco MySQL não existe).
 */

require_once __DIR__ . '/lib/estoque_store.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'sucesso' => true,
        'itens' => array_values(lerEstoque()),
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = json_decode(file_get_contents('php://input'), true) ?? [];

    $nome = trim($dados['nome'] ?? '');
    if ($nome === '') {
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Informe o nome do item.']);
        exit;
    }

    $itens = lerEstoque();

    $novoItem = [
        'id'         => proximoIdEstoque($itens),
        'nome'       => $nome,
        'descricao'  => $dados['descricao'] ?? '',
        'quantidade' => (int) ($dados['quantidade'] ?? 0),
        'categoria'  => $dados['categoria'] ?? '',
        'imagem'     => $dados['imagem'] ?? '',
        'em_falta'   => (bool) ($dados['em_falta'] ?? false),
    ];

    $itens[] = $novoItem;
    salvarEstoque($itens);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Item de estoque criado com sucesso.',
        'item' => $novoItem,
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
