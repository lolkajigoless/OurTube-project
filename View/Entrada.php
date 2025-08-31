<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OurTube - Login</title>

    <link rel="stylesheet" href="Home.css">
    <link rel="shortcut icon" href="../Images/favicon.ico" type="image/x-icon">
</head>
<body>

<?php
    session_start(); // Inicia a sessão para controlar login e estado do usuário

    // Inclui o arquivo de conexão com o banco de dados
    require '../Model/Conexao.php';
    $cnx = new Conexao(); // Cria a instância da classe de conexão (não usada nesta página)
?>

<!-- Cabeçalho com botões de login/logout e painel admin -->
<header>
  <div class="container">
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <!-- Se o usuário estiver logado, exibe botão de logout -->
        <a href="../Controller/logout.php" class="btn">Logout</a>

        <!-- Se o usuário for admin, exibe botão para o painel -->
        <?php if (isset($_SESSION['usuario_admin']) && $_SESSION['usuario_admin'] == 1): ?>
            <a href="admin.php" class="btn">Painel do Admin</a>
        <?php endif; ?>
    <?php else: ?>
        <!-- Se o usuário não estiver logado, mostra botões de login/cadastro -->
        <a href="Entrada.php" class="btn">Entrar</a>
        <a href="Cadastro.php" class="btn">Cadastrar</a>
    <?php endif; ?>
  </div>
</header>

<!-- Barra lateral (menu de navegação) -->
<nav class="sidebar">
  <div class="sidebar-logo">
    <!-- Logo redireciona para home diferente dependendo do login -->
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

  <!-- Itens de navegação no menu -->
  <h2>Menu</h2>
  <a href="home.php">Início</a>
  <a href="Todos_videos.php">Todos os vídeos</a>
  <a href="Ranking.php">Mais Curtidos</a>
</nav>

<!-- Área principal da página: formulário de login -->
<main class="content">
    <h1>Entrar em conta</h1>

    <!-- Formulário de login que envia os dados para Login.php -->
    <form action="../Model/Login.php" method="POST">
        <label for="email">E-mail:</label><br />
        <input type="email" name="email" id="email" required /><br /><br />

        <label for="senha">Senha:</label><br />
        <input type="password" name="senha" id="senha" required /><br /><br />

        <button type="submit">Entrar</button>
    </form>
</main>

</body>
</html>
