<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Metadados básicos da página -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OurTube - Home</title>

    <!-- Importa o CSS e ícone da aba -->
    <link rel="stylesheet" href="Home.css">
    <link rel="shortcut icon" href="..\Images\favicon.ico" type="image/x-icon">
</head>
<body>

<?php
session_start(); // Inicia a sessão para poder usar $_SESSION

require '../Model/Conexao.php'; // Importa o arquivo de conexão com banco de dados
$cnx = new Conexao(); // Cria instância da conexão (não está sendo usada nesta página diretamente)
?>

<!-- Cabeçalho com botões de navegação dependendo do estado da sessão -->
<header>
  <div class="container">
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <!-- Usuário logado: mostra botão de logout -->
        <a href="../Controller/logout.php" class="btn">Logout</a>

        <!-- Se for administrador, mostra também o botão do painel de admin -->
        <?php if (isset($_SESSION['usuario_admin']) && $_SESSION['usuario_admin'] == 1): ?>
            <a href="admin.php" class="btn">Painel do Admin</a>
        <?php endif; ?>
    <?php else: ?>
        <!-- Usuário não logado: mostra botões de entrar e cadastrar -->
        <a href="Entrada.php" class="btn">Entrar</a>
        <a href="Cadastro.php" class="btn">Cadastrar</a>
    <?php endif; ?>
  </div>
</header>

<!-- Menu lateral (sidebar) com logo e opções de navegação) -->
<nav class="sidebar">
 <div class="sidebar-logo">
    <!-- Link da logo muda se o usuário estiver logado ou não -->
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

    <!-- Itens do menu de navegação -->
    <h2>Menu</h2>
    <a href="home.php">Início</a>
    <a href="Todos_videos.php">Todos os vídeos</a>
    <a href="Ranking.php">Mais Curtidos</a>
</nav>

<!-- Conteúdo principal da página -->
<main class="content">
    <h1>Bem-vindo(a) ao OurTube!</h1>

    <p>Este é o seu espaço para assistir, interagir e compartilhar vídeos com a comunidade.</p>

    <h2>Funcionalidades disponíveis:</h2>

    <!-- Lista de funcionalidades da plataforma -->
    <ul>
        <li><strong>Ver os vídeos mais curtidos:</strong> explore os vídeos com maior número de curtidas em nosso ranking. Uma ótima forma de descobrir conteúdos populares!</li>

        <li><strong>Todos os vídeos:</strong> acesse a lista completa de vídeos enviados pelos usuários. Navegue livremente e encontre o que quiser assistir.</li>

        <li><strong>Curtir e não curtir vídeos:</strong> se você estiver logado, poderá interagir com os vídeos clicando em "Like" ou "Dislike". Sua opinião ajuda a destacar os melhores conteúdos.</li>

        <li><strong>Postar vídeos:</strong> usuários logados podem enviar seus próprios vídeos para a plataforma e compartilhar com todos. Mostre sua criatividade!</li>

        <li><strong>Alterar foto de perfil:</strong> personalize sua conta trocando sua foto de perfil. Essa opção está disponível após o login.</li>
    </ul>

    <p>Para aproveitar todas essas funcionalidades, <a href="Entrada.php">faça login</a> ou <a href="Cadastro.php">cadastre-se</a> agora mesmo!</p>
</main>

</body>
</html>
