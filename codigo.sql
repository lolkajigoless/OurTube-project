-- Cria o banco de dados aula_php, caso ainda não exista
CREATE DATABASE aula_php;

-- Seleciona o banco de dados aula_php para uso
USE aula_php;

-- Criação da tabela de usuários
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,           -- ID único auto-incrementado
  nome VARCHAR(100) NOT NULL,                   -- Nome do usuário (obrigatório)
  email VARCHAR(150) NOT NULL UNIQUE,           -- Email único e obrigatório
  senha VARCHAR(255) NOT NULL,                   -- Senha criptografada
  admin BOOLEAN DEFAULT 0,                        -- Flag para administrador (0 = normal, 1 = admin)
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- Data/hora de criação do registro
);

-- Criação da tabela de vídeos
CREATE TABLE videos (
  id INT AUTO_INCREMENT PRIMARY KEY,             -- ID único auto-incrementado
  usuario_id INT NOT NULL,                        -- FK para usuário que enviou o vídeo
  nome_arquivo VARCHAR(255) NOT NULL,             -- Nome do arquivo do vídeo
  data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,  -- Data/hora do envio
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE -- Relação com usuário e remoção em cascata
);

-- Criação da tabela de curtidas (likes/dislikes)
CREATE TABLE curtidas (
    id INT AUTO_INCREMENT PRIMARY KEY,           -- ID único auto-incrementado
    usuario_id INT NOT NULL,                      -- FK para usuário que curtiu
    video_id INT NOT NULL,                        -- FK para vídeo curtido
    tipo ENUM('like', 'dislike') NOT NULL,       -- Tipo da curtida (like ou dislike)
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Data/hora da curtida
    UNIQUE(usuario_id, video_id),                 -- Garante 1 curtida por usuário por vídeo
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (video_id) REFERENCES videos(id)
);

-- Adiciona coluna descrição na tabela vídeos
ALTER TABLE videos ADD descricao TEXT;

-- Adiciona coluna foto_perfil na tabela usuários, permite null e padrão é null
ALTER TABLE usuarios ADD foto_perfil VARCHAR(255) DEFAULT NULL;

-- Atualiza o campo admin para 1 (administrador) para usuário com email específico
UPDATE usuarios SET admin = 1 WHERE email = 'duduless2020@gmail.com';

-- Atualiza o campo admin para 1 para outro email
UPDATE usuarios SET admin = 1 WHERE email = 'a@g.com';

-- Consulta que retorna vídeos com nome do usuário, total de likes e dislikes, ordenado pelos mais curtidos (top 10)
SELECT v.*, u.nome AS nome_usuario,
       SUM(c.tipo = 'like') AS total_likes,
       SUM(c.tipo = 'dislike') AS total_dislikes
FROM videos v
JOIN usuarios u ON v.usuario_id = u.id
LEFT JOIN curtidas c ON v.id = c.video_id
GROUP BY v.id
ORDER BY total_likes DESC
LIMIT 10;

-- Consulta para listar todos os usuários
select * from usuarios;

-- Consulta para listar todos os vídeos
select * from videos;

-- Exclui a tabela usuários (cuidado, perde dados)
drop table usuarios;

-- Mostra a estrutura da tabela usuarios (colunas, tipos, etc)
DESCRIBE usuarios;
