<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OurTube - Espera 3 segundos, não seja afobado...</title>
    <link rel="stylesheet" href="../View/Home.css">
</head>
<body>
<?php
    require '../Model/Conexao.php'; // Inclui a classe de conexão com banco de dados
    $cnx = new Conexao();           // Instancia a conexão
?>
<header>
  <div class="container">
    <a href="" class="btn">Entrar</a>
    <a href="" class="btn">Cadastrar</a>
  </div>
</header>

<nav class="sidebar">
    <h2>Menu</h2>
    <a href="">Início</a>
    <a href="">Todos os vídeos</a>
    <a href="">Mais Curtidos</a>
</nav>

<main class="content" style="text-align: center;">
<?php
// Inclui a conexão novamente (redundante, pode ser removido pois já incluiu antes)
require_once __DIR__ . "/Conexao.php";

// Verifica se o formulário foi enviado via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Recebe os dados do formulário
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    // Criptografa a senha com algoritmo bcrypt (melhor prática)
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);

    try {
        $cnx = new Conexao();
        $pdo = $cnx->conectar();

        // Prepara a query para inserir o usuário no banco
        $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
        $stmt = $pdo->prepare($sql);

        // Liga os valores aos parâmetros nomeados para evitar SQL Injection
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":senha", $senha);

        // Executa o comando de inserção
        $stmt->execute();

        // Exibe mensagem de sucesso
        echo "Cadastro feito com sucesso! Você será redirecionado para a home em 3 segundos.";

        // Cabeçalho HTTP para aguardar 3 segundos e redirecionar para login
        header("Refresh: 3; url=../View/Entrada.php");
        exit;
    } catch (PDOException $err) {
        // Em caso de erro no banco de dados
        echo "Erro ao Cadastrar";
        // Redireciona para a home após 3 segundos
        header("Refresh: 3; url=../View/home.php");
        exit;
    }
} else {
    // Caso alguém acesse essa página diretamente, sem envio via POST
    echo "Erro ao Enviar as informações";
    header("Refresh: 3; url=../View/home.php");
    exit;
}
?>
</main>

</body>
</html>
