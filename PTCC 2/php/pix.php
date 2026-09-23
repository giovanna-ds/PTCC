<?php

/**
 * Gera um código Pix (BR Code) temporário, válido por alguns minutos.
 *
 * Não depende de banco de dados: o valor e a descrição vêm direto do
 * carrinho/produto (enviados pelo front-end), e a validade é embutida
 * no próprio txid + devolvida no JSON, sem precisar guardar nada em
 * lugar nenhum. Quem confere se o QR ainda vale é o front-end (com um
 * cronômetro) e, se quiser reforçar do lado do servidor, o txid pode
 * ser validado depois usando o timestamp nele embutido.
 */

require_once __DIR__ . '/pix_config.php';
require_once __DIR__ . '/lib/Pix.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

if (CHAVE_PIX === '') {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Chave Pix ainda não configurada em php/pix_config.php.'
    ]);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true) ?? [];
$valor = (float) ($dados['valor'] ?? 0);

if ($valor <= 0) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Valor inválido.']);
    exit;
}

// Remove acentos/caracteres especiais e limita o tamanho, como o
// padrão Pix exige para nome (25) e cidade (15).
function sanitizarTexto(string $texto, int $tamanhoMaximo): string
{
    $semAcento = iconv('UTF-8', 'ASCII//TRANSLIT', $texto) ?: $texto;
    $limpo = preg_replace('/[^A-Za-z0-9 ]/', '', $semAcento);
    return strtoupper(substr(trim($limpo), 0, $tamanhoMaximo));
}

// txid: só letras/números, até 25 caracteres. Aqui embutimos o
// timestamp de criação (em base36) para dar pra checar a validade
// depois sem precisar de banco de dados.
$agora = time();
$txid = 'PED' . base_convert((string) $agora, 10, 36) . bin2hex(random_bytes(3));
$txid = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $txid), 0, 25));

$pix = new Pix(
    CHAVE_PIX,
    $valor,
    sanitizarTexto(NOME_RECEBEDOR, 25),
    sanitizarTexto(CIDADE_RECEBEDOR, 15),
    $txid
);

$validoAte = $agora + (PIX_VALIDADE_MINUTOS * 60);

echo json_encode([
    'sucesso' => true,
    'copiaCola' => $pix->getPayload(),
    'txid' => $txid,
    'geradoEm' => date('c', $agora),
    'validoAte' => date('c', $validoAte),
    'validadeSegundos' => PIX_VALIDADE_MINUTOS * 60,
]);
