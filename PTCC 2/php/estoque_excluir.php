<?php
/**
 * Exclui um item de estoque. Ver php/CONTRATO_API.md, item 12.
 */

require_once __DIR__ . '/lib/estoque_store.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int) ($dados['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Informe o id do item.']);
    exit;
}

$itens = lerEstoque();
$itensRestantes = array_values(array_filter($itens, fn($item) => $item['id'] !== $id));

if (count($itensRestantes) === count($itens)) {
    http_response_code(404);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Item de estoque não encontrado.']);
    exit;
}

salvarEstoque($itensRestantes);

echo json_encode(['sucesso' => true, 'mensagem' => 'Item de estoque excluído com sucesso.']);
