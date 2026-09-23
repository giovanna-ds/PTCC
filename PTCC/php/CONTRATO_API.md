# Contrato do Backend - Sabino Bistrô

Este arquivo documenta os endpoints que o front-end espera do
backend. A maior parte da pasta `php/` está vazia de propósito — a
implementação desses endpoints não é o meu escopo, fica pra quem for
montar o backend. **Exceções:**
- O Pix (`php/pix.php`, `php/lib/Pix.php`, `php/pix_config.php`) foi
  implementado de verdade — ver item 9.
- O Estoque (`php/estoque.php`, `php/estoque_atualizar.php`,
  `php/estoque_excluir.php`) também foi implementado de verdade — ver
  itens 10 a 12. Ele guarda os dados num arquivo JSON
  (`php/data/estoque.json`) em vez do banco MySQL, que ainda não está
  configurado no projeto. Quando o banco estiver pronto, troque as
  funções de `php/lib/estoque_store.php` por consultas reais numa
  tabela `estoque`.

Abaixo está exatamente o que o front-end **já está chamando**
(endpoint, método, campos do JSON de ida e volta).

O banco de dados (tabelas/campos) está descrito em `confeitariabistro.sql`.

---

## 1. Cadastro de cliente
**Chamado em:** `js/cadastro.js`
**Endpoint esperado:** `POST /php/cadastro.php`

Envia:
```json
{
  "nome": "string",
  "email": "string",
  "senha": "string",
  "telefone": "string"
}
```

Espera de volta:
```json
{ "sucesso": true, "mensagem": "string" }
```
ou, em caso de erro (ex: e-mail já cadastrado):
```json
{ "sucesso": false, "mensagem": "string" }
```

⚠️ A senha deve ser guardada com hash (`password_hash` no PHP, ou
equivalente), nunca em texto puro.

---

## 2. Login
**Chamado em:** `js/login.js`
**Endpoint esperado:** `POST /php/login.php`

Envia:
```json
{ "email": "string", "senha": "string" }
```

Espera de volta (sucesso):
```json
{
  "sucesso": true,
  "cliente": {
    "id": 1,
    "nome": "string",
    "email": "string",
    "telefone": "string",
    "endereco": "string"
  }
}
```
O objeto `cliente` inteiro é salvo em `localStorage.clienteSabinoBistro`
e usado depois em `perfil.js` e nas outras telas — por isso precisa
vir com esses campos (`id` é obrigatório, os outros podem vir vazios).

Erro (e-mail/senha errados):
```json
{ "sucesso": false, "mensagem": "string" }
```

---

## 3. Recuperar senha
**Chamado em:** `js/recuperar_senha.js`
**Endpoint esperado:** `POST /php/recuperar_senha.php`

Duas ações no mesmo endpoint, diferenciadas pelo campo `acao`:

**a) Conferir se o e-mail existe:**
```json
{ "acao": "verificar_email", "email": "string" }
```
Resposta: `{ "sucesso": true|false, "mensagem": "string" }`

**b) Salvar nova senha:**
```json
{ "acao": "redefinir_senha", "email": "string", "novaSenha": "string" }
```
Resposta: `{ "sucesso": true|false, "mensagem": "string" }`

---

## 4. Editar perfil
**Chamado em:** `js/perfil.js` (função `salvarEdicao`)
**Endpoint esperado:** `POST /php/cliente_atualizar.php`

Só é chamado se o cliente logado já tiver um `id` (ou seja, depois
que o login por banco estiver funcionando).

Envia (objeto do cliente inteiro, incluindo o `id`):
```json
{
  "id": 1,
  "nome": "string",
  "email": "string",
  "telefone": "string",
  "endereco": "string"
}
```

Resposta: `{ "sucesso": true|false, "mensagem": "string" }`

---

## 5. Listar produtos (cardápio dinâmico)
**Chamado em:** `js/produtos.js`
**Endpoint esperado:** `GET /php/produtos.php`

Resposta esperada:
```json
{
  "sucesso": true,
  "produtos": [
    {
      "id": 1,
      "nome": "string",
      "descricao": "string",
      "preco": 15.00,
      "imagem": "img/caminho-da-imagem.jpg",
      "categoria": "string"
    }
  ]
}
```

Esses produtos aparecem automaticamente numa seção "Novidades" no
final do `index.php` (id `secao-novos-produtos` / grid
`gridNovosProdutos`), com botão de favoritar já incluso. Para não
colidir com os ids 1-25 já usados pelos cards fixos do HTML, o
front-end soma **+1000** ao `id` do produto vindo do banco antes de
usar no carrinho/favoritos (ex: produto `id: 3` no banco vira `1003`
no site). Isso é só do lado do front-end, o backend pode continuar
usando o id normal (auto-incremento) do banco.

---

## 6. Criar produto (admin)
**Chamado em:** `js/admin.js` (função `salvarProdutoNoBanco`, disparada
por `salvarNovoProduto`)
**Endpoint esperado:** `POST /php/produtos.php`

Envia:
```json
{
  "nome": "string",
  "descricao": "string",
  "preco": 15.00,
  "categoria": "string",
  "imagem": "img/nome-do-arquivo.jpg"
}
```

⚠️ Sobre `imagem`: o admin ainda não tem upload de arquivo de
verdade. O que é enviado é só `"img/" + nome-do-arquivo-escolhido`.
Ou seja, pra imagem aparecer certa, alguém precisa colocar
manualmente esse arquivo dentro da pasta `img/` com esse mesmo nome
— ou o backend precisa implementar um upload de verdade (não fizemos
isso ainda).

Resposta esperada:
```json
{
  "sucesso": true,
  "mensagem": "string",
  "produto": {
    "id": 10,
    "nome": "string",
    "descricao": "string",
    "preco": 15.00,
    "imagem": "string",
    "categoria": "string"
  }
}
```
O `produto.id` retornado é salvo no atributo `data-produto-id` da
linha da tabela do admin, e usado depois para excluir o produto.

---

## 7. Editar produto (admin)
**Chamado em:** `js/admin.js` (função `atualizarProdutoNoBanco`, disparada
por `aplicarEdicao` quando o botão "Editar" de uma linha é usado)
**Endpoint esperado:** `POST /php/produto_atualizar.php`

Envia:
```json
{
  "id": 10,
  "nome": "string",
  "descricao": "string",
  "preco": 15.00,
  "categoria": "string",
  "imagem": "img/nome-do-arquivo.jpg"
}
```

⚠️ Se o admin editar um produto sem escolher uma nova imagem, o campo
`imagem` vem com o mesmo valor que já estava salvo (a imagem antiga é
mantida). Só muda se um novo arquivo for selecionado no formulário.

Resposta esperada: `{ "sucesso": true|false, "mensagem": "string" }`

⚠️ Esse botão "Editar" só consegue avisar o banco se o produto já
tiver um `id` vindo dele (ou seja, produtos que já foram criados
antes do backend existir, ou a linha de exemplo fixa no HTML, ficam
com a edição salva só na tabela local até o backend estar no ar).

---

## 8. Excluir produto (admin)
**Chamado em:** `js/admin.js` (função `excluirProdutoDaTabela`)
**Endpoint esperado:** `POST /php/produto_excluir.php`

Envia:
```json
{ "id": 10 }
```

Resposta: `{ "sucesso": true|false, "mensagem": "string" }`

Sugestão: em vez de apagar a linha do banco de verdade, marcar o
produto como inativo (ex: coluna `ativo = 0`), pra não perder
produtos que já apareceram em pedidos antigos.

---

## 9. Pix (⚠️ este, diferente dos outros, JÁ ESTÁ IMPLEMENTADO)

Ao contrário dos endpoints acima (que são só o contrato documentado
pra alguém implementar), o Pix foi implementado de verdade, porque
foi pedido separadamente. Arquivos:

- `php/lib/Pix.php` — monta o payload do Pix (BR Code) no padrão do
  Banco Central. Sem dependências externas.
- `php/pix_config.php` — **precisa preencher `CHAVE_PIX`** com uma
  chave Pix real antes de usar (hoje está vazia de propósito).
- `php/pix.php` — endpoint chamado por `js/pagamento.js`.

**Chamado em:** `js/pagamento.js`
**Endpoint:** `POST /php/pix.php`

Envia:
```json
{ "valor": 30.00 }
```

Resposta:
```json
{
  "sucesso": true,
  "copiaCola": "00020126...6304XXXX",
  "txid": "string",
  "geradoEm": "2026-08-19T12:00:00-03:00",
  "validoAte": "2026-08-19T12:05:00-03:00",
  "validadeSegundos": 300
}
```

O front-end usa `copiaCola` para desenhar o QR Code (via biblioteca
`qrcodejs`, carregada por CDN) e para o campo "copia e cola", e usa
`validadeSegundos` para o cronômetro de expiração (5 minutos por
padrão, configurável em `PIX_VALIDADE_MINUTOS` no `pix_config.php`).

⚠️ **Isso é Pix estático/manual**: o site não recebe confirmação
automática de que o pagamento foi feito. Alguém precisa checar
manualmente no aplicativo do banco e confirmar o pedido (o botão
"Confirmar Pagamento" já existe no admin). Depois dos 5 minutos, o
código expira só na tela (o front-end limpa o QR e mostra "gerar
novo") — o `txid` tem o horário de geração embutido, então dá pra
reforçar essa validação no servidor no futuro, se quiser.

---

## 10. Criar item de estoque (admin) — ✅ implementado
**Chamado em:** `js/admin.js` (função `salvarEstoqueNoBanco`, disparada
por `salvarNovoEstoque`)
**Endpoint:** `POST /php/estoque.php` (o `GET` no mesmo endpoint lista
todos os itens — usado por `carregarEstoqueDoBanco` ao abrir a página)

Envia:
```json
{
  "nome": "string",
  "descricao": "string",
  "quantidade": 20,
  "categoria": "string",
  "imagem": "img/nome-do-arquivo.jpg",
  "em_falta": false
}
```

⚠️ Assim como no cardápio (item 6), o admin ainda não tem upload de
arquivo de verdade: é enviado só `"img/" + nome-do-arquivo-escolhido`
(ou `""`, se o item for cadastrado sem foto — nesse caso o front-end
mostra um ícone padrão no lugar).

Resposta esperada:
```json
{
  "sucesso": true,
  "mensagem": "string",
  "item": {
    "id": 1,
    "nome": "string",
    "descricao": "string",
    "quantidade": 20,
    "imagem": "string",
    "categoria": "string",
    "em_falta": false
  }
}
```
O `item.id` retornado é salvo no atributo `data-estoque-id` da linha
da tabela do admin, e usado depois para editar/excluir o item.

---

## 11. Editar item de estoque (admin) — ✅ implementado
**Chamado em:** `js/admin.js` (função `atualizarEstoqueNoBanco`, disparada
por `aplicarEdicaoEstoque` e por `alternarEmFaltaEstoque`, o botão de
"Marcar Em Falta" / "Marcar Disponível" direto na tabela)
**Endpoint:** `POST /php/estoque_atualizar.php`

A atualização é **parcial**: só os campos presentes no JSON são
alterados. A edição completa (pelo modal) envia todos os campos;
o botão de alternar "Em Falta" envia só `id` e `em_falta`:
```json
{ "id": 1, "em_falta": true }
```

Edição completa:
```json
{
  "id": 1,
  "nome": "string",
  "descricao": "string",
  "quantidade": 20,
  "categoria": "string",
  "imagem": "img/nome-do-arquivo.jpg",
  "em_falta": false
}
```

⚠️ Se o admin editar um item sem escolher uma nova imagem, o campo
`imagem` vem com o mesmo valor que já estava salvo.

Resposta esperada: `{ "sucesso": true|false, "mensagem": "string" }`

---

## 12. Excluir item de estoque (admin) — ✅ implementado
**Chamado em:** `js/admin.js` (função `excluirEstoqueDaTabela`)
**Endpoint:** `POST /php/estoque_excluir.php`

Envia:
```json
{ "id": 1 }
```

Resposta: `{ "sucesso": true|false, "mensagem": "string" }`

---

## Ainda não conectados ao backend (ficam só no `localStorage` por
## enquanto — não têm chamada `fetch` no código hoje)

- **Carrinho** (`js/carrinho.js`, `js/script.js`) — usa
  `localStorage.carrinhoBistro`.
- **Favoritos** (`js/favoritos.js`, `js/script.js`) — usa
  `localStorage.favoritosBistro`.
- **Histórico de pedidos** (`js/perfil.js`) — lê
  `localStorage.historicoPedidosBistro`, que hoje nunca é escrito por
  lugar nenhum do site.
- **Pagamento com cartão** — a aba existe em `pagamento.php`, mas
  `js/pagamento.js` hoje só mostra um aviso de "ainda não disponível"
  ao selecionar essa opção (o teste anterior com Mercado Pago foi
  removido por não ter funcionado).

Se/quando o backend for implementar carrinho, favoritos e pedidos de
verdade, o formato de tabelas já está pronto em `confeitariabistro.sql`
(`carrinho_itens`, `favoritos`, `pedidos`, `pedido_itens`) — só falta
o front-end trocar o `localStorage` por chamadas `fetch`, do mesmo
jeito que já foi feito nos itens 1 a 9 acima.
