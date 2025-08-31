<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OurTube - Cadastro</title>

    <link rel="stylesheet" href="Home.css">
    <link rel="shortcut icon" href="../Images/favicon.ico" type="image/x-icon">
</head>
<body>

<?php
    session_start(); // Inicia a sessão para verificar se o usuário já está logado

    // Inclui o arquivo de conexão com o banco
    require '../Model/Conexao.php';
    $cnx = new Conexao(); // Instancia o objeto da conexão (não usado aqui diretamente)
?>

<!-- Cabeçalho do site -->
<header>
  <div class="container">
    <!-- Se o usuário estiver logado, mostra botão de logout e painel admin (se for admin) -->
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <a href="../Controller/logout.php" class="btn">Logout</a>

        <?php if (isset($_SESSION['usuario_admin']) && $_SESSION['usuario_admin'] == 1): ?>
            <a href="admin.php" class="btn">Painel do Admin</a>
        <?php endif; ?>
    <?php else: ?>
        <!-- Se não estiver logado, mostra os botões de entrar e cadastrar -->
        <a href="Entrada.php" class="btn">Entrar</a>
        <a href="Cadastro.php" class="btn">Cadastrar</a>
    <?php endif; ?>
  </div>
</header>

<!-- Menu lateral (sidebar) -->
<nav class="sidebar">
  <div class="sidebar-logo">
    <!-- Logo do site direciona para home com ou sem login -->
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <a href="home_login.php" class="btn logo-link">
          <img src="../Logo/logo.png" alt="Logo OurTube">
        </a>
    <?php else: ?>
        <a href="home.php" class="btn logo-link">
          <img src="../Logo/logo.png" alt="Logo OurTube">
        </a>
    <?php endif; ?>
  </div>

  <!-- Links do menu -->
  <h2>Menu</h2>
  <a href="home_login.php">Início</a>
  <a href="Todos_videos.php">Todos os vídeos</a>
  <a href="Ranking.php">Mais Curtidos</a>
</nav>

<!-- Conteúdo principal da página -->
<main class="content">
    <h1>Cadastro de Usuário</h1>

    <!-- Formulário de cadastro de novo usuário -->
    <form action="../../SIte_Bimestral_PW2/Model/Fazer_cadastro.php" method="POST">
        <!-- Campo de nome -->
        <label for="nome">Nome:</label><br />
        <input type="text" name="nome" id="nome" required /><br /><br />

        <!-- Campo de e-mail -->
        <label for="email">E-mail:</label><br />
        <input type="email" name="email" id="email" required /><br /><br />

        <!-- Campo de senha -->
        <label for="senha">Senha:</label><br />
        <input type="password" name="senha" id="senha" required /><br /><br />

        <!-- Botão de envio -->
        <button type="submit">Cadastrar</button>
    </form>
</main>

</body>
</html>
