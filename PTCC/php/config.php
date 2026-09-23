<?php
/**
 * Conexão PDO com o banco de dados MySQL (confeitariabistro.sql).
 *
 * Ajuste as constantes abaixo conforme o ambiente onde o projeto for
 * executado (XAMPP/WAMP local, hospedagem, etc). Antes de usar,
 * lembre-se de importar o arquivo `confeitariabistro.sql` (na raiz do
 * projeto) no seu servidor MySQL para criar o banco e as tabelas.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'confeitariabistro');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna uma conexão PDO única (reaproveitada em toda a requisição).
 * Lança uma exceção em caso de falha, para ser tratada por quem chamar.
 */
function conectarBanco(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        $opcoes = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
    }

    return $pdo;
}
