<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OurTube - Todos os vídeos</title>

    <link rel="stylesheet" href="Home.css">
    <link rel="shortcut icon" href="../Images/favicon.ico" type="image/x-icon">
</head>
<body>

<?php
    session_start(); // Inicia a sessão do usuário
    require '../Model/Conexao.php'; // Inclui a classe de conexão com o banco
    $cnx = new Conexao(); // Cria um objeto de conexão
    $pdo = $cnx->conectar(); // Conecta ao banco de dados

    // Consulta que busca todos os vídeos com o nome do usuário que enviou
    $stmt = $pdo->prepare("
        SELECT v.*, u.nome AS nome_usuario 
        FROM videos v
        JOIN usuarios u ON v.usuario_id = u.id
        ORDER BY v.id DESC
    ");
    $stmt->execute(); // Executa a consulta
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC); // Armazena os resultados como array associativo

    $usuario = [];
    // Verifica se o usuário está logado e pega dados do usuário atual
    if (isset($_SESSION['usuario_id'])) {
        $stmtUser = $pdo->prepare("SELECT nome, foto_perfil FROM usuarios WHERE id = ?");
        $stmtUser->execute([$_SESSION['usuario_id']]);
        $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);
    }
?>

<header>
  <div class="container">
    <!-- Mostra a foto de perfil se o usuário estiver logado -->
    <?php if (!empty($usuario) && !empty($usuario['foto_perfil'])): ?>
    <a href="home_login.php" class="foto-perfil-link">
        <img class="foto-perfil" src="../fotos_perfil/<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil">
    </a>
    <?php elseif (!empty($usuario)): ?>
    <a href="home_login.php" class="foto-perfil-link">
        <img class="foto-perfil" src="../fotos_perfil/default.jpg" alt="Sem foto">
    </a>
    <?php endif; ?>

    <!-- Botões de login/logout e admin -->
    <?php if (isset($_SESSION['usuario_id'])): ?>
    <a href="../Controller/logout.php" class="btn">Logout</a>
    <?php if (isset($_SESSION['usuario_admin']) && $_SESSION['usuario_admin'] == 1): ?>
        <a href="admin.php" class="btn">Painel do Admin</a>
    <?php endif; ?>
    <?php else: ?>
    <a href="Entrada.php" class="btn">Entrar</a>
    <a href="Cadastro.php" class="btn">Cadastrar</a>
    <?php endif; ?>
  </div>
</header>

<nav class="sidebar">
  <!-- Logo com link condicional -->
  <div class="sidebar-logo">
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

  <h2>Menu</h2>
  <!-- Links de navegação -->
  <?php if (isset($_SESSION['usuario_id'])): ?>
    <a href="home_login.php">Início</a>
  <?php else: ?>
    <a href="home.php">Início</a>
  <?php endif; ?>
  <a href="Todos_videos.php">Todos os vídeos</a>
  <?php if (isset($_SESSION['usuario_id'])): ?>
    <a href="Alterar_perfil.php">Conta</a>
  <?php endif; ?>
  <a href="Ranking.php">Mais Curtidos</a>
</nav>

<main class="content">
    <h1>Bem-vindo(a) ao OurTube</h1>
    <h2>Vídeos enviados por nossos usuários</h2>

    <!-- Mensagem se não houver vídeos -->
    <?php if (empty($videos)): ?>
    <p><em>Não há videos por aqui!</em></p>
    <?php else: ?>
    <!-- Loop de exibição de vídeos -->
    <?php foreach ($videos as $video): ?>
    <div class="video-box">
        <!-- Nome do usuário -->
        <p><strong><?= htmlspecialchars($video['nome_usuario']) ?></strong> enviou:</p>

        <!-- Player de vídeo -->
        <video width="400" controls>
            <source src="../videos/<?= htmlspecialchars($video['nome_arquivo']) ?>" type="video/mp4">
            Seu navegador não suporta o vídeo.
        </video>

        <!-- Descrição do vídeo -->
        <p><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($video['descricao'])) ?></p>

        <?php
        // Consulta curtidas e descurtidas do vídeo
        $stmtLikes = $pdo->prepare("SELECT 
            SUM(tipo = 'like') AS likes, 
            SUM(tipo = 'dislike') AS dislikes 
        FROM curtidas WHERE video_id = ?");
        $stmtLikes->execute([$video['id']]);
        $curtidas = $stmtLikes->fetch(PDO::FETCH_ASSOC);

        // Verifica se o usuário já votou nesse vídeo
        $jaVotou = null;
        if (isset($_SESSION['usuario_id'])) {
            $stmtUserVote = $pdo->prepare("SELECT tipo FROM curtidas WHERE usuario_id = ? AND video_id = ?");
            $stmtUserVote->execute([$_SESSION['usuario_id'], $video['id']]);
            $jaVotou = $stmtUserVote->fetchColumn();
        }
        ?>
        <!-- Botões de votação -->
        <div class="votos" data-video-id="<?= $video['id'] ?>">
            <button class="like-btn">👍 Like (<span class="like-count"><?= $curtidas['likes'] ?? 0 ?></span>)</button>
            <button class="dislike-btn">👎 Dislike (<span class="dislike-count"><?= $curtidas['dislikes'] ?? 0 ?></span>)</button>
        </div>
        <br><br>
        <hr>
    </div>
    <?php endforeach; ?>
    <?php endif; ?> <!-- Fim da verificação de vídeos -->
</main>

<script>
// Script de interação com botões de like/dislike
document.querySelectorAll('.votos').forEach(div => {
    const videoId = div.dataset.videoId;

    // Evento de clique para like
    div.querySelector('.like-btn').addEventListener('click', () => {
        votar(videoId, 'like', div);
    });

    // Evento de clique para dislike
    div.querySelector('.dislike-btn').addEventListener('click', () => {
        votar(videoId, 'dislike', div);
    });
});

// Função para votar no vídeo (envia via fetch)
function votar(videoId, tipo, div) {
    const formData = new FormData();
    formData.append('video_id', videoId);
    formData.append('tipo', tipo);

    fetch('../Controller/votar.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.erro) {
            alert(data.erro);
        } else {
            // Atualiza contadores de likes/dislikes
            div.querySelector('.like-count').textContent = data.likes ?? 0;
            div.querySelector('.dislike-count').textContent = data.dislikes ?? 0;
        }
    })
    .catch(() => alert('Erro ao votar')); // Em caso de erro na requisição
}
</script>
</body>
</html>
