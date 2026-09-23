<?php

function salvarImagemProduto(?array $arquivo): ?string
{
    if (!$arquivo || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($arquivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Não foi possível enviar a imagem.');
    }

    if (($arquivo['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('A imagem deve ter no máximo 5 MB.');
    }

    $tmp = $arquivo['tmp_name'] ?? '';
    if (!is_uploaded_file($tmp)) {
        throw new RuntimeException('Arquivo de imagem inválido.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);

    $extensoes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    if (!isset($extensoes[$mime])) {
        throw new RuntimeException('Formato de imagem não permitido. Use JPG, PNG, WEBP ou GIF.');
    }

    if (@getimagesize($tmp) === false) {
        throw new RuntimeException('O arquivo enviado não é uma imagem válida.');
    }

    $diretorio = dirname(__DIR__, 2) . '/img/produtos';
    if (!is_dir($diretorio) && !mkdir($diretorio, 0775, true)) {
        throw new RuntimeException('Não foi possível criar a pasta de imagens.');
    }

    $nomeOriginal = pathinfo((string) ($arquivo['name'] ?? 'produto'), PATHINFO_FILENAME);
    $nomeSeguro = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $nomeOriginal);
    $nomeSeguro = trim($nomeSeguro, '-_');
    if ($nomeSeguro === '') {
        $nomeSeguro = 'produto';
    }

    $nomeFinal = $nomeSeguro . '-' . bin2hex(random_bytes(6)) . '.' . $extensoes[$mime];
    $destino = $diretorio . '/' . $nomeFinal;

    if (!move_uploaded_file($tmp, $destino)) {
        throw new RuntimeException('Não foi possível salvar a imagem.');
    }

    return 'img/produtos/' . $nomeFinal;
}

function excluirImagemProdutoSePertencerAoProjeto(?string $caminho): void
{
    if (!$caminho || !str_starts_with($caminho, 'img/produtos/')) {
        return;
    }

    $arquivo = dirname(__DIR__, 2) . '/' . $caminho;
    if (is_file($arquivo)) {
        @unlink($arquivo);
    }
}
