<?php
session_start();
require '../Model/Conexao.php';

// Verifica se o usuário está logado; se não, redireciona para a página de login
if(!isset($_SESSION['usuario_id'])){
    header("Location: ../View/Entrada.php");
    exit;
}

// Verifica se a requisição é POST e se o arquivo 'video' foi enviado
if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['video'])){
    $video = $_FILES['video'];                     // Recebe o arquivo enviado
    $descricao = $_POST['descricao'] ?? '';        // Recebe a descrição, caso enviada (senão, string vazia)

    // Pega a extensão do arquivo enviado (ex: mp4, webm, ogg)
    $extensao = pathinfo($video['name'], PATHINFO_EXTENSION);

    // Lista de extensões permitidas para upload
    $permitidos = ['mp4', 'webm', 'ogg'];

    // Verifica se a extensão do arquivo é permitida
    if(in_array($extensao, $permitidos)) {
        // Gera um nome único para o arquivo, evitando conflitos
        $nome_arquivo = uniqid() . "." . $extensao;

        // Caminho absoluto onde o vídeo será salvo na pasta 'videos'
        $caminho = __DIR__ . "/../videos/" . $nome_arquivo;

        // Move o arquivo temporário para a pasta destino
        if(move_uploaded_file($video['tmp_name'], $caminho)){
            $conexao = new Conexao();
            $pdo = $conexao->conectar();

            // Insere os dados do vídeo no banco: id do usuário, nome do arquivo e descrição
            $stmt = $pdo->prepare("INSERT INTO videos (usuario_id, nome_arquivo, descricao) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['usuario_id'], $nome_arquivo, $descricao]);

            // Redireciona para a página principal após o upload com sucesso
            header("Location: ../View/home_login.php");
            exit;
        } else {
            echo "Erro ao mover o vídeo";  // Erro caso o move_uploaded_file falhe
        }
    } else {
        echo "Formato do vídeo não permitido.";  // Extensão não permitida
    }
}
?>
