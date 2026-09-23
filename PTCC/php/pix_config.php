<?php
/**
 * Configuração do Pix. Preencha com os dados reais antes de usar em
 * produção - sem isso, o QR gerado não recebe dinheiro nenhum.
 *
 * Regras do padrão Pix (Banco Central):
 * - CHAVE_PIX: sua chave Pix real (CPF, CNPJ, e-mail, telefone ou chave aleatória)
 * - NOME_RECEBEDOR: até 25 caracteres, sem acento (ex: "SABINO BISTRO")
 * - CIDADE_RECEBEDOR: até 15 caracteres, sem acento (ex: "SAO PAULO")
 */

define('CHAVE_PIX', ''); // <-- preencher com a chave Pix real
define('NOME_RECEBEDOR', 'SABINO BISTRO');
define('CIDADE_RECEBEDOR', 'SAO PAULO');

// Validade do QR Code temporário, em minutos.
define('PIX_VALIDADE_MINUTOS', 5);
