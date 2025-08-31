<?php
session_start(); // Inicia a sessão
require '../Model/Conexao.php'; // Importa a classe de conexão com o banco

// Garante que apenas usuários logados acessem a página
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ./Entrada.php");
    exit;
}

$conexao = new Conexao();
$pdo = $conexao->conectar();

// Busca os dados do usuário logado
$usuario_id = $_SESSION['usuario_id'];
$stmtUsuario = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmtUsuario->execute([$usuario_id]);
$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

// Busca todos os vídeos cadastrados com o nome do dono
$stmt = $pdo->query("
    SELECT v.id, v.nome_arquivo, v.descricao, u.nome AS usuario 
    FROM videos v 
    JOIN usuarios u ON v.usuario_id = u.id
");
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OurTube - Admin's home</title>
    
    <link rel="stylesheet" href="Home.css">
    <link rel="shortcut icon" href="../Images/favicon.ico" type="image/x-icon">
</head>
<body>

<!-- Cabeçalho com avatar e navegação -->
<header>
  <div class="container">

<?php if (!empty($usuario) && !empty($usuario['foto_perfil'])): ?>
    <!-- Foto personalizada do admin -->
    <a href="home_login.php" class="foto-perfil-link">
        <img class="foto-perfil" src="../fotos_perfil/<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil">
    </a>
<?php elseif (!empty($usuario)): ?>
    <!-- Se não tiver foto, mostra padrão -->
    <a href="home_login.php" class="foto-perfil-link">
        <img class="foto-perfil" src="../fotos_perfil/default.jpg" alt="Sem foto">
    </a>
<?php endif; ?>

<!-- Botões de login/logout e painel do admin -->
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

<!-- Barra lateral de navegação -->
<nav class="sidebar">
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
    <a href="home_login.php">Início</a>
    <a href="Todos_videos.php">Todos os vídeos</a>
    <a href="Alterar_perfil.php">Conta</a>
    <a href="Ranking.php">Mais Curtidos</a>
</nav>

<!-- Área principal do conteúdo -->
<main class="content">
    <h1>Painel do administrador</h1>

    <?php if (empty($videos)): ?>
        <p><em>Não há mais nenhum vídeo aqui!</em></p>
    <?php else: ?>
        <?php foreach ($videos as $video): ?>
            <!-- Nome do usuário que postou -->
            <p><strong>Usuário:</strong> <?= htmlspecialchars($video['usuario']) ?></p>

            <!-- Reprodução do vídeo -->
            <video width="400" controls>
                <source src="../videos/<?= htmlspecialchars($video['nome_arquivo']) ?>" type="video/mp4">
                Seu navegador não suporta o vídeo.
            </video>

            <!-- Descrição -->
            <p><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($video['descricao'])) ?></p>

            <?php
            // Busca curtidas e dislikes do vídeo
            $stmtLikes = $pdo->prepare("SELECT 
                SUM(tipo = 'like') AS likes, 
                SUM(tipo = 'dislike') AS dislikes 
                FROM curtidas WHERE video_id = ?");
            $stmtLikes->execute([$video['id']]);
            $curtidas = $stmtLikes->fetch(PDO::FETCH_ASSOC);
            ?>

            <!-- Exibe contagem -->
            <p>
                👍 Curtidas: <?= $curtidas['likes'] ?? 0 ?> |
                👎 Dislikes: <?= $curtidas['dislikes'] ?? 0 ?>
            </p>

            <!-- Botão para remover o vídeo -->
            <form action="../Controller/remover_video.php" method="post" style="margin-top: 10px;" onsubmit="return confirmarRemocao();">
                <input type="hidden" name="video_id" value="<?= $video['id'] ?>">
                <button type="submit">Remover vídeo</button>
            </form>

            <hr>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<!-- Confirmação de exclusão no navegador -->
<script>
function confirmarRemocao() {
    return confirm("Tem certeza que deseja remover este vídeo?");
}
</script>

</body>
</html>
