<?php
/**
 * Funções de autenticação do painel administrativo.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function exigirAdmin(): void
{
    if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo_usuario'] ?? '') !== 'admin') {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Acesso permitido somente para administradores.'
        ]);
        exit;
    }
}
