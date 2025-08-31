<?php
session_start(); // Inicia a sessão do usuário
require '../Model/Conexao.php'; // Inclui o arquivo da classe de conexão

$conexao = new Conexao(); // Cria objeto da classe de conexão
$pdo = $conexao->conectar(); // Abre a conexão com o banco de dados

// Busca os dados do usuário logado pelo ID armazenado na sessão
$usuario_id = $_SESSION['usuario_id'];
$stmtUsuario = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmtUsuario->execute([$usuario_id]);
$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC); // Armazena os dados do usuário

// Busca os vídeos mais curtidos, junto com nome do autor e soma de likes/dislikes
$stmt = $pdo->query("
    SELECT v.*, u.nome AS nome_usuario,
           SUM(c.tipo = 'like') AS total_likes,
           SUM(c.tipo = 'dislike') AS total_dislikes
    FROM videos v
    JOIN usuarios u ON v.usuario_id = u.id
    LEFT JOIN curtidas c ON v.id = c.video_id
    GROUP BY v.id
    ORDER BY total_likes DESC
    LIMIT 10
");
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC); // Armazena os vídeos retornados
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Metadados e links -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OurTube - Ranking</title>
    <link rel="stylesheet" href="Home.css">
    <link rel="icon" href="../Images/favicon.ico" type="image/x-icon">
</head>
<body>

<!-- Cabeçalho do site -->
<header>
  <div class="container">
    <!-- Foto de perfil (se estiver logado) -->
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

<!-- Barra lateral com menu -->
<nav class="sidebar">
 <div class="sidebar-logo">
  <!-- Link da logo redireciona dependendo se o usuário está logado -->
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

    <!-- Menu lateral -->
    <h2>Menu</h2>
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

<!-- Conteúdo principal -->
<main class="content">
    <h1>Vídeos mais curtidos</h1>

    <!-- Mensagem se não houver vídeos -->
    <?php if (empty($videos)): ?>
        <p><em>Não há videos por aqui!</em></p>
    <?php else: ?>
        <!-- Exibe cada vídeo da lista -->
        <?php foreach ($videos as $video): ?>
            <div class="video-box">
                <!-- Nome do usuário que enviou -->
                <h3>Por: <?= htmlspecialchars($video['nome_usuario']) ?></h3>

                <!-- Player de vídeo -->
                <video width="400" controls>
                    <source src="../videos/<?= htmlspecialchars($video['nome_arquivo']) ?>" type="video/mp4">
                </video>

                <!-- Exibe descrição, se existir -->
                <?php if (!empty($video['descricao'])): ?>
                    <p><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($video['descricao'])) ?></p>
                <?php endif; ?>

                <!-- Se estiver logado, mostra botões de curtir/descurtir -->
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <div class="reacoes" data-video-id="<?= $video['id'] ?>">
                        <button class="like-btn">👍 Like (<span class="like-count"><?= $video['total_likes'] ?? 0 ?></span>)</button>
                        <button class="dislike-btn">👎 Dislike (<span class="dislike-count"><?= $video['total_dislikes'] ?? 0 ?></span>)</button>
                    </div>
                <?php else: ?>
                    <p><em>Entre na sua conta para curtir ou dar dislike.</em></p>
                <?php endif; ?>

                <hr><br>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<!-- Script para tratar cliques nos botões de votar -->
<script>
document.querySelectorAll('.reacoes').forEach(div => {
    const videoId = div.getAttribute('data-video-id');

    // Evento de clique no botão Like
    div.querySelector('.like-btn').addEventListener('click', () => votar(videoId, 'like', div));

    // Evento de clique no botão Dislike
    div.querySelector('.dislike-btn').addEventListener('click', () => votar(videoId, 'dislike', div));
});

// Função que envia o voto para o back-end via fetch
function votar(videoId, tipo, div) {
    fetch('../Controller/votar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `video_id=${videoId}&tipo=${tipo}`
    })
    .then(res => res.json())
    .then(data => {
        // Atualiza contadores na interface, se sucesso
        if (data.success) {
            div.querySelector('.like-count').textContent = data.likes;
            div.querySelector('.dislike-count').textContent = data.dislikes;
        } else {
            alert(data.message || 'Erro ao votar.');
        }
    });
}
</script>
</body>
</html>
