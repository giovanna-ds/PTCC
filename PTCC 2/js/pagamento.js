let cronometroPixId = null;

// Pega os parâmetros enviados na URL
const urlParams = new URLSearchParams(window.location.search);
const valorTotal = urlParams.get('valor') || "0.00";
const nomeProduto = urlParams.get('produto') || "Pedido Confeitaria";
const quantidade = urlParams.get('qtd') || "1";

// Exibe os dados na tela
document.getElementById('nome-produto').innerText = `Produto: ${nomeProduto}`;
document.getElementById('qtd-produto').innerText = `Quantidade: ${quantidade}`;
document.getElementById('total-pagar').innerText = `Total: R$ ${parseFloat(valorTotal).toFixed(2).replace('.', ',')}`;

// Envio do formulário -> gera o Pix
document.getElementById('form-pagamento').addEventListener('submit', async (e) => {
    e.preventDefault();
    await gerarPix();
});

// Gera (ou renova) o código Pix, chamando o backend em php/pix.php
async function gerarPix() {
    const btnSubmit = document.getElementById('btn-submit');
    btnSubmit.innerText = "Gerando código Pix...";
    btnSubmit.disabled = true;

    try {
        const resposta = await fetch('../php/pix.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ valor: parseFloat(valorTotal) })
        });

        const resultado = await resposta.json();

        if (!resultado.sucesso) {
            alert(resultado.mensagem || 'Não foi possível gerar o código Pix.');
            return;
        }

        exibirPix(resultado.copiaCola, resultado.validadeSegundos);

    } catch (erro) {
        alert('Não foi possível conectar ao servidor. Verifique se o servidor PHP já foi configurado.');
        console.error(erro);
    } finally {
        btnSubmit.innerText = "Gerar QR Code PIX";
        btnSubmit.disabled = false;
    }
}

function exibirPix(codigoCopiaCola, validadeSegundos) {
    document.getElementById('area-pix').style.display = 'block';
    document.getElementById('codigo-pix').value = codigoCopiaCola;
    document.getElementById('btn-copiar-pix').style.display = 'inline-block';
    document.getElementById('btn-copiar-pix').disabled = false;
    document.getElementById('btn-gerar-novo-pix').style.display = 'none';

    // Renderiza o QR Code de verdade a partir do código copia-e-cola
    const containerQr = document.getElementById('qrcode-pix');
    containerQr.innerHTML = '';
    new QRCode(containerQr, {
        text: codigoCopiaCola,
        width: 220,
        height: 220
    });

    iniciarCronometroPix(validadeSegundos);

    document.getElementById('area-pix').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function iniciarCronometroPix(segundosTotais) {
    pararCronometroPix();

    let restante = segundosTotais;
    atualizarTextoCronometro(restante);

    cronometroPixId = setInterval(() => {
        restante--;

        if (restante <= 0) {
            expirarPix();
            return;
        }

        atualizarTextoCronometro(restante);
    }, 1000);
}

function atualizarTextoCronometro(segundos) {
    const min = Math.floor(segundos / 60).toString().padStart(2, '0');
    const seg = (segundos % 60).toString().padStart(2, '0');
    document.getElementById('pix-timer').innerText = `⏱️ Este código expira em ${min}:${seg}`;
}

function expirarPix() {
    pararCronometroPix();

    document.getElementById('pix-timer').innerText = '⚠️ Código expirado. Gere um novo para continuar.';
    document.getElementById('qrcode-pix').innerHTML = '';
    document.getElementById('codigo-pix').value = '';
    document.getElementById('btn-copiar-pix').style.display = 'none';
    document.getElementById('btn-gerar-novo-pix').style.display = 'inline-block';
}

function pararCronometroPix() {
    if (cronometroPixId) {
        clearInterval(cronometroPixId);
        cronometroPixId = null;
    }
}

// Copia o código Pix para a área de transferência. Usa a API moderna
// (navigator.clipboard) quando disponível, e cai para o método antigo
// (execCommand) quando não está - isso é importante porque
// navigator.clipboard só funciona em https:// ou http://localhost, e
// domínios como algo.test (Laragon) rodam em http:// puro.
function copiarCodigoPix() {
    const campo = document.getElementById('codigo-pix');
    const botao = document.getElementById('btn-copiar-pix');

    const avisarSucesso = () => {
        const textoOriginal = botao.innerText;
        botao.innerText = 'Copiado! ✅';
        setTimeout(() => { botao.innerText = textoOriginal; }, 2000);
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(campo.value)
            .then(avisarSucesso)
            .catch(() => copiarComFallback(campo, avisarSucesso));
    } else {
        copiarComFallback(campo, avisarSucesso);
    }
}

function copiarComFallback(campo, aoTerminar) {
    try {
        campo.removeAttribute('readonly');
        campo.focus();
        campo.select();
        campo.setSelectionRange(0, campo.value.length);

        const copiou = document.execCommand('copy');
        campo.setAttribute('readonly', true);

        if (copiou) {
            aoTerminar();
        } else {
            alert('Não foi possível copiar automaticamente. O código já está selecionado - use Ctrl+C (ou Cmd+C no Mac).');
        }
    } catch (erro) {
        campo.setAttribute('readonly', true);
        alert('Não foi possível copiar automaticamente. Selecione o texto e copie manualmente.');
    }
}
