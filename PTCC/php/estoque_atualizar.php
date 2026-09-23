<?php
/**
 * Atualiza um item de estoque existente. Ver php/CONTRATO_API.md, item 11.
 *
 * A atualização é PARCIAL: só os campos enviados no JSON são alterados.
 * Isso permite tanto uma edição completa (vinda do modal de "Editar")
 * quanto uma atualização rápida de um único campo, como o botão
 * "Marcar Em Falta" / "Marcar Disponível" da tabela, que envia só
 * { "id": 1, "em_falta": true }.
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
$encontrado = false;

foreach ($itens as &$item) {
    if ($item['id'] === $id) {
        $encontrado = true;

        if (array_key_exists('nome', $dados)) {
            $item['nome'] = $dados['nome'];
        }
        if (array_key_exists('descricao', $dados)) {
            $item['descricao'] = $dados['descricao'];
        }
        if (array_key_exists('quantidade', $dados)) {
            $item['quantidade'] = (int) $dados['quantidade'];
        }
        if (array_key_exists('categoria', $dados)) {
            $item['categoria'] = $dados['categoria'];
        }
        if (array_key_exists('imagem', $dados) && $dados['imagem'] !== '') {
            $item['imagem'] = $dados['imagem'];
        }
        if (array_key_exists('em_falta', $dados)) {
            $item['em_falta'] = (bool) $dados['em_falta'];
        }

        break;
    }
}
unset($item);

if (!$encontrado) {
    http_response_code(404);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Item de estoque não encontrado.']);
    exit;
}

salvarEstoque($itens);

echo json_encode(['sucesso' => true, 'mensagem' => 'Item de estoque atualizado com sucesso.']);
