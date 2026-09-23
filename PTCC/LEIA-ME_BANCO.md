# Banco de Dados - Sabino Bistrô

O arquivo `confeitariabistro.sql` está aqui pronto pra ser
rodado, mas **nenhum PHP do projeto usa ele ainda** (nem cadastro,
nem login, nem produtos, nem carrinho). O único PHP implementado no
projeto hoje é o Pix (`php/pix.php`), que não depende de banco de
dados nenhum.

## O que tem aqui

- **`confeitariabistro.sql`** — script com todas as tabelas:
  `usuarios` (login, hierarquia cliente/admin), `clientes`,
  `administradores`, `tokens_recuperacao_senha`, `categorias`,
  `produtos`, `favoritos`, `carrinho_itens`, `pedidos`,
  `pedido_itens`, uma `VIEW` (`vw_usuarios_completo`) e dados de teste
  (24 produtos do cardápio + 3 clientes de exemplo). Já testado e sem
  erros de sintaxe.

## O que falta pra conectar o site nele

Nada no PHP consulta esse banco ainda. Pra cadastro/login/produtos/
favoritos/carrinho/pedidos funcionarem de verdade, alguém precisa:

1. Criar uma conexão PDO com o banco (`php/config.php`, por exemplo).
2. Implementar os endpoints que o front-end já espera — estão todos
   documentados em `php/CONTRATO_API.md` (URL, método HTTP, campos do
   JSON de ida e volta de cada tela).

Até lá, o site continua funcionando com cadastro/login simulados (o
`js/cadastro.js` e `js/login.js` chamam arquivos PHP que não existem
ainda) e carrinho/favoritos salvos só no navegador (`localStorage`).


## CRUD do cardápio — atualizado em 12/09/2026

O cardápio agora usa a tabela `produtos` do MySQL como fonte principal.

- `php/produtos.php`: lista produtos ativos para o site e lista todos para o admin; também cria produtos.
- `php/categorias.php`: carrega as categorias do banco.
- `php/produto_atualizar.php`: edita nome, descrição, preço, categoria e imagem.
- `php/produto_ocultar.php`: faz exclusão lógica (`ativo = 0`) sem apagar o registro.
- `php/lib/produto_upload.php`: valida e salva imagens novas em `img/produtos/` com nome único.
- `php/lib/auth.php`: protege os endpoints administrativos.
- `html/admin.php`: painel protegido para administrador, com leitura real do banco.
- `js/admin.js`: envia os formulários por `FormData`; não depende somente de JavaScript para persistir os dados.
- `index.php`: mostra os produtos ativos diretamente do MySQL. Assim, um produto criado no painel aparece automaticamente no cardápio sem precisar ser colocado manualmente no HTML.
- `atualizar_imagens_produtos.sql`: correção dos dois nomes de imagens que tinham acentos diferentes dos arquivos existentes.

### Importante sobre imagens

O arquivo da imagem escolhido no painel é enviado para o PHP e salvo em `img/produtos/`. O banco guarda somente o caminho relativo, por exemplo `img/produtos/bolo-abc123.jpg`. Na edição, se uma nova imagem não for escolhida, a imagem anterior é mantida. As imagens originais do projeto não são apagadas pelo CRUD.

### Ocultar produto

O botão **Ocultar** não exclui o produto. Ele altera `produtos.ativo` para `0`, fazendo com que o item desapareça do cardápio público. No painel, ele continua disponível e pode ser **Reativado**.

### Login administrativo

O login agora também cria uma sessão PHP. O arquivo `html/admin.php` e os endpoints de produtos exigem `tipo_usuario = 'admin'`.
