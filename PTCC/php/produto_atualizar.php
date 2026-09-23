<?php
/**
 * Atualiza um produto. A imagem é opcional: se não enviar outra,
 * a imagem atual continua intacta.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/produto_upload.php';

header('Content-Type: application/json; charset=utf-8');

try {
    exigirAdmin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        exit;
    }

    $pdo = conectarBanco();
    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto inválido.']);
        exit;
    }

    $busca = $pdo->prepare(
        'SELECT p.*, c.nome AS categoria
         FROM produtos p
         LEFT JOIN categorias c ON c.id = p.categoria_id
         WHERE p.id = :id'
    );
    $busca->execute(['id' => $id]);
    $produtoAtual = $busca->fetch();

    if (!$produtoAtual) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado.']);
        exit;
    }

    $nome = trim((string) ($_POST['nome'] ?? $produtoAtual['nome']));
    $descricao = trim((string) ($_POST['descricao'] ?? ($produtoAtual['descricao'] ?? '')));
    $precoTexto = str_replace(',', '.', trim((string) ($_POST['preco'] ?? $produtoAtual['preco'])));
    $categoria = trim((string) ($_POST['categoria'] ?? ($produtoAtual['categoria'] ?? '')));

    if ($nome === '' || $categoria === '' || $precoTexto === '' || !is_numeric($precoTexto) || (float) $precoTexto < 0) {
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha os dados do produto corretamente.']);
        exit;
    }

    $stmtCat = $pdo->prepare('SELECT id FROM categorias WHERE nome = :nome LIMIT 1');
    $stmtCat->execute(['nome' => $categoria]);
    $categoriaId = $stmtCat->fetchColumn();

    if (!$categoriaId) {
        $insCat = $pdo->prepare('INSERT INTO categorias (nome) VALUES (:nome)');
        $insCat->execute(['nome' => $categoria]);
        $categoriaId = (int) $pdo->lastInsertId();
    }

    $imagemNova = salvarImagemProduto($_FILES['imagem'] ?? null);
    $imagem = $imagemNova ?: $produtoAtual['imagem'];

    $stmt = $pdo->prepare(
        'UPDATE produtos
         SET nome = :nome, descricao = :descricao, preco = :preco,
             imagem = :imagem, categoria_id = :categoria_id
         WHERE id = :id'
    );
    $stmt->execute([
        'nome' => $nome,
        'descricao' => $descricao !== '' ? $descricao : null,
        'preco' => (float) $precoTexto,
        'imagem' => $imagem,
        'categoria_id' => (int) $categoriaId,
        'id' => $id
    ]);

    // Só remove a imagem antiga se a nova foi salva e a antiga pertence
    // à pasta criada pelo CRUD. Imagens originais do projeto nunca são apagadas.
    if ($imagemNova && $produtoAtual['imagem'] !== $imagemNova) {
        excluirImagemProdutoSePertencerAoProjeto($produtoAtual['imagem']);
    }

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Produto atualizado com sucesso.'
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $erro) {
    error_log('Erro ao atualizar produto: ' . $erro->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $erro instanceof RuntimeException
            ? $erro->getMessage()
            : 'Erro interno ao atualizar o produto.'
    ], JSON_UNESCAPED_UNICODE);
}
