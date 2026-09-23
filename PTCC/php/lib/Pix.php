<?php

/**
 * Gera o "payload" do Pix (BR Code) no padrão do Banco Central.
 * Essa classe não tem nenhuma dependência externa - só monta a string
 * que vira o QR Code e o código "copia e cola".
 */
class Pix
{
    private string $chave;
    private float $valor;
    private string $nome;
    private string $cidade;
    private string $txid;

    public function __construct(
        string $chave,
        float $valor,
        string $nome,
        string $cidade,
        string $txid
    ) {
        $this->chave  = $chave;
        $this->valor  = $valor;
        $this->nome   = $nome;
        $this->cidade = $cidade;
        $this->txid   = $txid;
    }

    private function formatField(string $id, string $value): string
    {
        $length = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);
        return $id . $length . $value;
    }

    private function getCRC16(string $payload): string
    {
        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        for ($cont = 0; $cont < strlen($payload); $cont++) {
            $resultado ^= (ord($payload[$cont]) << 8);
            for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                if (($resultado & 0x8000) !== 0) {
                    $resultado = ($resultado << 1) ^ $polinomio;
                } else {
                    $resultado = ($resultado << 1);
                }
                $resultado &= 0xFFFF;
            }
        }

        return strtoupper(dechex($resultado));
    }

    public function getPayload(): string
    {
        // 00: Payload Format Indicator (sempre 01)
        $payload  = $this->formatField('00', '01');
        $valorFormatado = number_format($this->valor, 2, '.', '');

        // 26: Merchant Account Information (BR.GOV.BCB.PIX + chave)
        $gui = $this->formatField('00', 'BR.GOV.BCB.PIX');
        $chave = $this->formatField('01', $this->chave);
        $merchantAccountInfo = $gui . $chave;
        $payload .= $this->formatField('26', $merchantAccountInfo);

        // 52: Merchant Category Code (0000 para uso genérico)
        $payload .= $this->formatField('52', '0000');

        // 53: Moeda (986 = BRL)
        $payload .= $this->formatField('53', '986');

        // 54: Valor
        $payload .= $this->formatField('54', $valorFormatado);

        // 58: País (BR)
        $payload .= $this->formatField('58', 'BR');

        // 59: Nome do recebedor
        $payload .= $this->formatField('59', $this->nome);

        // 60: Cidade
        $payload .= $this->formatField('60', $this->cidade);

        // 62: Additional Data Field Template (txid)
        $txid = $this->formatField('05', $this->txid);
        $payload .= $this->formatField('62', $txid);

        // 63: CRC
        $semCRC = $payload . '6304';
        $crc = $this->getCRC16($semCRC);
        return $semCRC . $crc;
    }
}
