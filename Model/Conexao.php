<?php
class Conexao {
    // Dados para conexão com o banco MySQL local
    private $host = 'localhost';        // Servidor do banco de dados
    private $dbname = 'aula_php';       // Nome do banco de dados
    private $username = 'root';          // Usuário do banco (padrão root)
    private $password = '';              // Senha do banco (vazia no exemplo)

    // Método para criar e retornar uma conexão PDO
    public function conectar() {
        try {
            // Tenta criar um novo objeto PDO com as credenciais e configurações
            $pdo = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);

            // Define que erros devem lançar exceções para melhor tratamento
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Retorna o objeto PDO para ser usado para consultas
            return $pdo;
        } catch (PDOException $e) {
            // Se houver erro na conexão, exibe a mensagem e retorna null
            echo "Erro na conexão: " . $e->getMessage();
            return null;
        }
    }
}
?>
