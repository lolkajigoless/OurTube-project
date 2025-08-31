<?php
session_start();
require '../Model/Conexao.php';

// Verifica se o usuário está logado; se não, redireciona para a página de login
if(!isset($_SESSION['usuario_id'])){
        header("Location: ./Entrada.php");
        exit;
}

// Verifica se a requisição é POST e se o id do vídeo foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video_id'])) {
    $video_id = $_POST['video_id'];

    $conexao = new Conexao();
    $pdo = $conexao->conectar();

    // Busca o nome do arquivo do vídeo para deletar do disco depois
    $stmt = $pdo->prepare("SELECT nome_arquivo FROM videos WHERE id = ?");
    $stmt->execute([$video_id]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);

    if($video) {
        // Deleta todas as curtidas associadas a esse vídeo (integridade referencial manual)
        $stmt = $pdo->prepare("DELETE FROM curtidas WHERE video_id = ?");
        $stmt->execute([$video_id]);

        // Deleta o registro do vídeo no banco de dados
        $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->execute([$video_id]);

        // Apaga o arquivo físico do vídeo do servidor
        $caminho = "../videos/" . $video['nome_arquivo'];
        if (file_exists($caminho)) {
            unlink($caminho);
        }
        // Redireciona para o painel admin após a remoção
        header("Location: ../View/admin.php");
        exit;
    } else {
        echo "Vídeo não encontrado"; // Caso o vídeo não exista no banco
    }
}
?>
