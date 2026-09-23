<?php
/**
 * CRUD de produtos do cardápio.
 *
 * GET público: retorna somente produtos ativos.
 * GET ?admin=1: retorna todos os produtos e exige administrador.
 * POST: cria produto (multipart/form-data).
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/produto_upload.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = conectarBanco();
    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo === 'GET') {
        $modoAdmin = isset($_GET['admin']) && $_GET['admin'] === '1';
        if ($modoAdmin) {
            exigirAdmin();
        }

        $sql = 'SELECT p.id, p.nome, p.descricao, p.preco, p.imagem,
                       p.categoria_id, p.ativo, p.criado_em,
                       c.nome AS categoria
                FROM produtos p
                LEFT JOIN categorias c ON c.id = p.categoria_id ';
        $sql .= $modoAdmin ? 'ORDER BY p.ativo DESC, p.id DESC' : 'WHERE p.ativo = 1 ORDER BY p.id DESC';

        $stmt = $pdo->query($sql);
        echo json_encode([
            'sucesso' => true,
            'produtos' => $stmt->fetchAll()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($metodo !== 'POST') {
        http_response_code(405);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        exit;
    }

    exigirAdmin();

    $nome = trim((string) ($_POST['nome'] ?? ''));
    $descricao = trim((string) ($_POST['descricao'] ?? ''));
    $precoTexto = str_replace(',', '.', trim((string) ($_POST['preco'] ?? '')));
    $categoria = trim((string) ($_POST['categoria'] ?? ''));

    if ($nome === '' || $categoria === '' || $precoTexto === '') {
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha nome, categoria e preço.']);
        exit;
    }

    if (!is_numeric($precoTexto) || (float) $precoTexto < 0) {
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Informe um preço válido.']);
        exit;
    }

    // Categoria nova é criada somente se ainda não existir.
    $stmtCat = $pdo->prepare('SELECT id FROM categorias WHERE nome = :nome LIMIT 1');
    $stmtCat->execute(['nome' => $categoria]);
    $categoriaId = $stmtCat->fetchColumn();

    if (!$categoriaId) {
        $insCat = $pdo->prepare('INSERT INTO categorias (nome) VALUES (:nome)');
        $insCat->execute(['nome' => $categoria]);
        $categoriaId = (int) $pdo->lastInsertId();
    }

    $imagem = salvarImagemProduto($_FILES['imagem'] ?? null);
    if ($imagem === null) {
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Selecione uma imagem para o produto.']);
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO produtos (nome, descricao, preco, imagem, categoria_id, ativo)
         VALUES (:nome, :descricao, :preco, :imagem, :categoria_id, 1)'
    );
    $stmt->execute([
        'nome' => $nome,
        'descricao' => $descricao !== '' ? $descricao : null,
        'preco' => (float) $precoTexto,
        'imagem' => $imagem,
        'categoria_id' => (int) $categoriaId
    ]);

    $id = (int) $pdo->lastInsertId();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Produto criado com sucesso.',
        'produto' => [
            'id' => $id,
            'nome' => $nome,
            'descricao' => $descricao,
            'preco' => (float) $precoTexto,
            'imagem' => $imagem,
            'categoria_id' => (int) $categoriaId,
            'categoria' => $categoria,
            'ativo' => 1
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $erro) {
    error_log('Erro no CRUD de produtos: ' . $erro->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $erro instanceof RuntimeException
            ? $erro->getMessage()
            : 'Erro interno ao salvar o produto.'
    ], JSON_UNESCAPED_UNICODE);
}
