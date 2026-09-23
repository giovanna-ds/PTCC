<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meu Perfil - Sabino Bistrô</title>
  <link rel="stylesheet" href="../css/pefil.css">
</head>
<body>
  <div class="perfil-container">
    
    <!-- Coluna da Esquerda: Perfil, Dados e Sair -->
    <div class="coluna-esquerda">
      <div class="perfil-card main-info">
        <div class="avatar-circle" id="avatarLetra">U</div>
        <h2 id="clienteNome">Carregando...</h2>
        <p id="clienteEmail" class="email-text">carregando@email.com</p>
        <span class="badge-status">Cliente Sabino Bistrô</span>
      </div>

      <div class="perfil-card dados-card">
        <h3>📍 Dados de Entrega</h3>
        <div class="info-group">
          <label>Telefone / WhatsApp:</label>
          <span id="clienteTelefone">(00) 00000-0000</span>
        </div>
        <div class="info-group">
          <label>Endereço Cadastrado:</label>
          <span id="clienteEndereco">Endereço não informado</span>
        </div>
        <button type="button" class="btn-editar" id="btnAbrirModal">Editar Dados</button>
      </div>

      <div class="acoes-conta">
        <button type="button" class="btn-sair" id="btnSairConta">Sair da Conta</button>
      </div>
    </div>

    <!-- Coluna da Direita: Histórico de Pedidos (Lado a Lado) -->
    <div class="coluna-direita">
      <div class="perfil-card pedidos-card">
        <h3>📜 Meus Pedidos Recentes</h3>
        <div id="listaPedidos">
          <p class="sem-pedidos">Nenhum pedido realizado ainda.</p>
        </div>
      </div>
    </div>

  </div>

  <div id="modalEdicao" class="modal-overlay" style="display: none;">
    <div class="modal-content">
      <h3>✏️ Editar Meus Dados</h3>
      <form id="formEdicao">
        <div class="input-group">
          <label for="editNome">Nome Completo:</label>
          <input type="text" id="editNome" required>
        </div>
        <div class="input-group">
          <label for="editTelefone">Telefone / WhatsApp:</label>
          <input type="text" id="editTelefone" required>
        </div>
        <div class="input-group">
          <label for="editEndereco">Endereço de Entrega:</label>
          <input type="text" id="editEndereco" required>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-cancelar" id="btnFecharModal">Cancelar</button>
          <button type="submit" class="btn-salvar">Salvar Alterações</button>
        </div>
      </form>
    </div>
  </div>
  <script src="../js/perfil.js"></script>
</body>
</html>