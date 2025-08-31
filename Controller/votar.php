<?php
session_start();
header('Content-Type: application/json');  // Define que a resposta será em JSON

require '../Model/Conexao.php';  // Inclui a classe de conexão com o banco

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['erro' => 'Não autenticado']);  // Retorna erro em JSON
    exit;
}

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];        // ID do usuário logado
    $video_id = $_POST['video_id'];                // ID do vídeo recebido pelo POST
    $tipo = $_POST['tipo'];                        // Tipo do voto: 'like' ou 'dislike'

    // Valida o tipo do voto
    if (!in_array($tipo, ['like', 'dislike'])) {
        echo json_encode(['erro' => 'Tipo inválido']);
        exit;
    }

    $conexao = new Conexao();
    $pdo = $conexao->conectar();

    // Verifica se o usuário já votou nesse vídeo
    $stmt = $pdo->prepare("SELECT * FROM curtidas WHERE usuario_id = ? AND video_id = ?");
    $stmt->execute([$usuario_id, $video_id]);

    if ($stmt->rowCount() > 0) {
        // Se já votou, atualiza o tipo de voto (like/dislike)
        $stmt = $pdo->prepare("UPDATE curtidas SET tipo = ? WHERE usuario_id = ? AND video_id = ?");
        $stmt->execute([$tipo, $usuario_id, $video_id]);
    } else {
        // Se não votou ainda, insere o voto no banco
        $stmt = $pdo->prepare("INSERT INTO curtidas (usuario_id, video_id, tipo) VALUES (?, ?, ?)");
        $stmt->execute([$usuario_id, $video_id, $tipo]);
    }

    // Busca o total atualizado de likes e dislikes para o vídeo
    $stmtLikes = $pdo->prepare("SELECT 
        SUM(tipo = 'like') AS likes, 
        SUM(tipo = 'dislike') AS dislikes 
        FROM curtidas WHERE video_id = ?");
    $stmtLikes->execute([$video_id]);
    $result = $stmtLikes->fetch(PDO::FETCH_ASSOC);

    // Retorna o resultado em JSON (exemplo: {"likes":10,"dislikes":2})
    echo json_encode($result);
    exit;
}
