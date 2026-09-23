<?php
/**
 * Armazenamento temporário do estoque, num arquivo JSON.
 *
 * Segue exatamente o mesmo padrão de php/lib/clientes_store.php: isso
 * NÃO é o banco de dados definitivo do projeto, é só um jeito de ter
 * o admin de Estoque funcionando de verdade (persistindo entre
 * recarregamentos de página) enquanto o banco MySQL (database/schema.sql)
 * não é criado.
 *
 * Quando o banco estiver pronto, troque estas funções por consultas
 * reais numa tabela `estoque` (ver sugestão em database/schema.sql) e
 * pode apagar o arquivo php/data/estoque.json.
 */

define('ARQUIVO_ESTOQUE', __DIR__ . '/../data/estoque.json');

function lerEstoque(): array
{
    if (!file_exists(ARQUIVO_ESTOQUE)) {
        return [];
    }

    $conteudo = file_get_contents(ARQUIVO_ESTOQUE);
    $dados = json_decode($conteudo, true);
    return is_array($dados) ? $dados : [];
}

function salvarEstoque(array $itens): void
{
    $pasta = dirname(ARQUIVO_ESTOQUE);
    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }

    file_put_contents(ARQUIVO_ESTOQUE, json_encode($itens, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function proximoIdEstoque(array $itens): int
{
    $maiorId = 0;
    foreach ($itens as $item) {
        if ($item['id'] > $maiorId) {
            $maiorId = $item['id'];
        }
    }
    return $maiorId + 1;
}

function encontrarItemEstoquePorId(array $itens, int $id): ?array
{
    foreach ($itens as $item) {
        if ($item['id'] === $id) {
            return $item;
        }
    }
    return null;
}
