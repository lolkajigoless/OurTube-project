<?php 
session_start();  // Inicia a sessão para armazenar dados do usuário durante a navegação
require_once '../Model/Conexao.php';  // Inclui a classe de conexão com o banco de dados

$conexao = new Conexao();
$pdo = $conexao->conectar();  // Cria a conexão PDO com o banco

// Verifica se o método da requisição é POST e se a conexão foi estabelecida
if($_SERVER["REQUEST_METHOD"] === "POST" && $pdo){
    $email = $_POST['email'];   // Recebe o email enviado pelo formulário
    $senha = $_POST['senha'];   // Recebe a senha enviada pelo formulário

    // Prepara a consulta para buscar o usuário pelo email
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);  // Executa a consulta com o email fornecido
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);  // Busca o usuário como array associativo

    // Se encontrou o usuário e a senha conferem (usando password_verify)
    if($usuario && password_verify($senha, $usuario['senha'])){
        // Define variáveis de sessão para identificar o usuário
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_admin'] = $usuario['admin'];  // Indica se é admin (1) ou não (0)
        
        // Redireciona para a página principal do usuário logado
        header("Location: ../View/home_login.php");
        exit;  // Garante que o script pare aqui após o redirecionamento
    }
    else{
        // Caso email ou senha estejam incorretos, exibe mensagem simples
        echo "E-mail ou senha incorretos.";
    }
}
?>
