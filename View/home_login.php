<?php
session_start(); // Inicia a sessão para acessar $_SESSION

require '../Model/Conexao.php'; // Inclui o arquivo de conexão com banco

// Se o usuário não estiver logado, redireciona para a página de login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Entrada.php");
    exit;
}

$cnx = new Conexao(); // Cria um objeto de conexão
$pdo = $cnx->conectar(); // Conecta ao banco de dados

// Busca todos os vídeos enviados pelo usuário logado
$stmt = $pdo->prepare("
    SELECT v.*, u.nome AS nome_usuario, u.foto_perfil 
    FROM videos v
    JOIN usuarios u ON v.usuario_id = u.id
    WHERE v.usuario_id = ?
    ORDER BY v.id DESC
");
$stmt->execute([$_SESSION['usuario_id']]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC); // Armazena os vídeos do usuário

// Busca informações básicas do usuário logado
$stmtUser = $pdo->prepare("SELECT nome, foto_perfil FROM usuarios WHERE id = ?");
$stmtUser->execute([$_SESSION['usuario_id']]);
$usuario = $stmtUser->fetch(PDO::FETCH_ASSOC); // Armazena os dados do usuário
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>OurTube - Home</title>

    <link rel="stylesheet" href="Home.css" />
    <link rel="shortcut icon" href="../Images/favicon.ico" type="image/x-icon" />
</head>
<body>

<!-- Cabeçalho com imagem de perfil e botões -->
<header>
  <div class="container">
    <!-- Exibe a foto de perfil do usuário, se existir -->
    <?php if (!empty($usuario) && !empty($usuario['foto_perfil'])): ?>
    <a href="home_login.php" class="foto-perfil-link">
        <img class="foto-perfil" src="../fotos_perfil/<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil">
    </a>
    <?php elseif (!empty($usuario)): ?>
    <!-- Caso não tenha foto, mostra imagem padrão -->
    <a href="home_login.php" class="foto-perfil-link">
        <img class="foto-perfil" src="../fotos_perfil/default.jpg" alt="Sem foto">
    </a>
    <?php endif; ?>

    <!-- Botão de logout e acesso ao painel de admin, se for administrador -->
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <a href="../Controller/logout.php" class="btn">Logout</a>
        <?php if (isset($_SESSION['usuario_admin']) && $_SESSION['usuario_admin'] == 1): ?>
            <a href="admin.php" class="btn">Painel do Admin</a>
        <?php endif; ?>
    <?php else: ?>
        <!-- Se não estiver logado, mostra botões de login e cadastro -->
        <a href="Entrada.php" class="btn">Entrar</a>
        <a href="Cadastro.php" class="btn">Cadastrar</a>
    <?php endif; ?>
  </div>
</header>

<!-- Barra lateral com menu -->
<nav class="sidebar">
  <div class="sidebar-logo">
    <!-- Link da logo varia com o login -->
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

  <!-- Itens do menu -->
  <h2>Menu</h2>
  <a href="home_login.php">Início</a>
  <a href="Todos_videos.php">Todos os vídeos</a>
  <a href="Alterar_perfil.php">Conta</a>
  <a href="Ranking.php">Mais Curtidos</a>
</nav>

<!-- Conteúdo principal da página -->
<main class="content">
    <!-- Título com nome do usuário logado -->
    <h1>Conta de(a) <?= htmlspecialchars($_SESSION['usuario_nome']) ?></h1>

    <!-- Formulário para envio de vídeo -->
    <form action="../Controller/upload_video.php" method="post" enctype="multipart/form-data">
        <label for="video">Escolha o vídeo:</label>
        <input type="file" name="video" accept="video/mp4,video/webm,video/ogg" required><br><br>

        <label for="descricao">Descrição:</label><br>
        <textarea name="descricao" id="descricao" rows="4" cols="50" placeholder="Escreva uma descrição para o vídeo..."></textarea><br><br>

        <button type="submit">Enviar</button>
    </form>

    <!-- Seção para listar os vídeos já enviados pelo usuário -->
    <h2>Seus vídeos enviados</h2>

    <!-- Caso não tenha enviado nenhum vídeo ainda -->
    <?php if (count($videos) === 0): ?>
        <p>Você ainda não enviou vídeos.</p>
    <?php else: ?>
        <!-- Loop pelos vídeos do usuário -->
        <?php foreach ($videos as $video): ?>
        <div class="video-box">
            <!-- Player de vídeo -->
            <video width="400" controls>
                <source src="../videos/<?= htmlspecialchars($video['nome_arquivo']) ?>" type="video/mp4" />
                Seu navegador não suporta o vídeo.
            </video>

            <!-- Exibe a descrição, se houver -->
            <?php if (!empty($video['descricao'])): ?>
                <p><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($video['descricao'])) ?></p>
            <?php endif; ?>

            <?php
            // Busca a contagem de curtidas e descurtidas
            $stmtLikes = $pdo->prepare("SELECT 
                SUM(tipo = 'like') AS likes, 
                SUM(tipo = 'dislike') AS dislikes 
                FROM curtidas WHERE video_id = ?");
            $stmtLikes->execute([$video['id']]);
            $curtidas = $stmtLikes->fetch(PDO::FETCH_ASSOC); // Armazena resultado
            ?>

            <!-- Mostra o total de likes e dislikes -->
            <p>
                👍 Curtidas: <?= $curtidas['likes'] ?? 0 ?> |
                👎 Dislikes: <?= $curtidas['dislikes'] ?? 0 ?>
            </p>

            <hr> 
            <br><br>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
</body>
</html>
