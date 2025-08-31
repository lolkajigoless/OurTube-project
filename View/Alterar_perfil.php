<?php
session_start(); // Inicia a sessão para manter dados entre páginas
require '../Model/Conexao.php'; // Inclui a classe de conexão com o banco

// Verifica se o usuário está logado, se não estiver redireciona para a página de login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Entrada.php");
    exit;
}

$conexao = new Conexao(); // Instancia a conexão
$pdo = $conexao->conectar(); // Conecta ao banco de dados

// Busca a foto de perfil do usuário logado
$stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>OurTube - Conta</title>

    <link rel="stylesheet" href="Home.css" />
    <link rel="shortcut icon" href="../Images/favicon.ico" type="image/x-icon" />
</head>
<body>

<!-- Exibe alertas de erro caso exista uma mensagem de erro armazenada na sessão -->
<?php if (!empty($_SESSION['erro_foto'])): ?>
<script>
    alert(<?= json_encode($_SESSION['erro_foto']) ?>); // Mostra o erro via JavaScript
</script>
<?php unset($_SESSION['erro_foto']); ?>
<?php endif; ?>

<!-- Cabeçalho com avatar e botões de navegação -->
<header>
  <div class="container">

  <!-- Se tiver foto de perfil personalizada, exibe; senão, exibe a padrão -->
  <?php if (!empty($usuario) && !empty($usuario['foto_perfil'])): ?>
    <a href="home_login.php" class="foto-perfil-link">
        <img class="foto-perfil" src="../fotos_perfil/<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil">
    </a>
<?php elseif (!empty($usuario)): ?>
    <a href="home_login.php" class="foto-perfil-link">
        <img class="foto-perfil" src="../fotos_perfil/default.jpg" alt="Sem foto">
    </a>
<?php endif; ?>

    <!-- Botões de logout, painel admin ou login/cadastro -->
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

<!-- Menu lateral -->
<nav class="sidebar">
 <div class="sidebar-logo">
  <!-- Logo do site leva à home dependendo do login -->
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
    <h1>Minha Conta</h1>
    <br>
    <h2>Conta de(a) <u><?= htmlspecialchars($_SESSION['usuario_nome']) ?></u></h2>

    <!-- Formulário de alteração de nome -->
    <h2>Alterar nome de usuário</h2>
    <form action="../Controller/alterar_nome.php" method="post" onsubmit="return confirmarAlteracaoNome()">
        <label for="novo_nome">Novo nome:</label><br>
        <input type="text" id="novo_nome" name="novo_nome" required><br><br>

        <label for="confirma_nome">Confirme o novo nome:</label><br>
        <input type="text" id="confirma_nome" name="confirma_nome" required><br><br>

        <button type="submit">Alterar nome</button>
    </form>

    <!-- Script de validação no lado do cliente para confirmar o novo nome -->
    <script>
    function confirmarAlteracaoNome() {
        const nome = document.getElementById('novo_nome').value.trim();
        const confirma = document.getElementById('confirma_nome').value.trim();

        if (nome !== confirma) {
            alert('Os nomes digitados não conferem. Por favor, tente novamente.');
            return false; // Cancela o envio do formulário
        }

        return confirm('Tem certeza que deseja alterar seu nome para "' + nome + '"?');
    }
    </script>

    <br><br>
    <hr>

    <!-- Exibição da foto de perfil atual -->
    <h3>Foto do perfil: </h3>
    <?php if ($usuario['foto_perfil']): ?>
        <img src="../fotos_perfil/<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil" width="120">
    <?php else: ?>
        <p>Você ainda não enviou uma foto.</p>
    <?php endif; ?>

    <!-- Formulário de upload de nova foto de perfil -->
    <form action="../Controller/alterar_foto.php" method="post" enctype="multipart/form-data">
        <label>Nova foto de perfil:</label><br>
        <input type="file" name="foto" accept="image/png, image/jpeg" required>
        <br><br>
        <button type="submit">Alterar foto</button>
    </form>
</main>

</body>
</html>
