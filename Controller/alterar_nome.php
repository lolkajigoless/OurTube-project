<?php
session_start();
require '../Model/Conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../View/Entrada.php");
    exit;
}

// Só aceita requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe e "limpa" os dados enviados no formulário
    $novo_nome = trim($_POST['novo_nome'] ?? '');
    $confirma_nome = trim($_POST['confirma_nome'] ?? '');

    // Valida se os dois campos foram preenchidos
    if ($novo_nome === '' || $confirma_nome === '') {
        $_SESSION['erro_nome'] = 'Por favor, preencha ambos os campos.';
        header("Location: ../View/Alterar_perfil.php");
        exit;
    }

    // Valida se os nomes são iguais
    if ($novo_nome !== $confirma_nome) {
        $_SESSION['erro_nome'] = 'Os nomes não conferem.';
        header("Location: ../View/Alterar_perfil.php");
        exit;
    }

    // Conecta ao banco
    $conexao = new Conexao();
    $pdo = $conexao->conectar();

    // Atualiza o nome do usuário no banco de dados
    $stmt = $pdo->prepare("UPDATE usuarios SET nome = ? WHERE id = ?");
    $stmt->execute([$novo_nome, $_SESSION['usuario_id']]);

    // Atualiza o nome na sessão para refletir a alteração imediatamente
    $_SESSION['usuario_nome'] = $novo_nome;

    // Mensagem de sucesso na sessão para feedback
    $_SESSION['sucesso_nome'] = 'Nome alterado com sucesso!';
    header("Location: ../View/Alterar_perfil.php");
    exit;
} else {
    // Se não for POST, redireciona para a página de perfil
    header("Location: ../View/Alterar_perfil.php");
    exit;
}
?>
