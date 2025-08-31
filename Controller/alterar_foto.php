<?php
session_start(); // Inicia a sessão para manter os dados do usuário durante a navegação
require '../Model/Conexao.php'; // Inclui o arquivo da classe de conexão com o banco de dados

// Verifica se o usuário está logado, caso contrário redireciona para a página de entrada/login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../View/Entrada.php");
    exit;
}

// Verifica se o método da requisição é POST e se o arquivo 'foto' foi enviado via formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    $foto = $_FILES['foto']; // Armazena as informações do arquivo enviado
    $permitidas = ['jpg', 'jpeg', 'png']; // Define os formatos de imagem permitidos
    $extensao = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION)); // Obtém a extensão do arquivo em minúsculo

    // Verifica se a extensão do arquivo está na lista de permitidos
    if (in_array($extensao, $permitidas)) {
        $dimensoes = getimagesize($foto['tmp_name']); // Obtém as dimensões da imagem enviada
        // Caso não consiga obter as dimensões, configura mensagem de erro e redireciona
        if (!$dimensoes) {
            $_SESSION['erro_foto'] = "Não foi possível obter as dimensões da imagem.";
            header("Location: ../View/Alterar_perfil.php");
            exit;
        }

        $largura = $dimensoes[0]; // Largura da imagem
        $altura = $dimensoes[1];  // Altura da imagem

        // Verifica se a largura ou altura ultrapassam 500 pixels
        if ($largura > 500 || $altura > 500) {
            $_SESSION['erro_foto'] = "A imagem deve ter largura e altura de no máximo 500px. Sua imagem tem {$largura}x{$altura}px.";
            header("Location: ../View/Alterar_perfil.php");
            exit;
        }

        $novo_nome = uniqid() . "." . $extensao; // Gera um nome único para o arquivo com a extensão
        $caminho = "../fotos_perfil/" . $novo_nome; // Define o caminho onde a imagem será salva

        // Tenta mover o arquivo temporário para o diretório definitivo
        if (move_uploaded_file($foto['tmp_name'], $caminho)) {
            $conexao = new Conexao(); // Cria a conexão com o banco
            $pdo = $conexao->conectar();

            // Prepara e executa o comando SQL para atualizar o nome da foto no banco para o usuário logado
            $stmt = $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
            $stmt->execute([$novo_nome, $_SESSION['usuario_id']]);

            // Redireciona para a página inicial do usuário após sucesso
            header("Location: ../View/home_login.php");
            exit;
        } else {
            // Caso o movimento do arquivo falhe, define mensagem de erro e redireciona
            $_SESSION['erro_foto'] = "Erro ao salvar a imagem.";
            header("Location: ../View/Alterar_perfil.php");
            exit;
        }
    } else {
        // Se a extensão do arquivo não for permitida, define mensagem de erro e redireciona
        $_SESSION['erro_foto'] = "Formato de imagem não permitido.";
        header("Location: ../View/Alterar_perfil.php");
        exit;
    }
}
?>
