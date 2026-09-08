<?php

class Database {
    // Propriedades privadas para manter a segurança das credenciais
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Ao instanciar a classe, ela lê o arquivo .env automaticamente
        $this->carregarEnv();
        
        // Define as variáveis baseadas no .env (ou usa padrões de fallback)
        $this->host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $this->port = $_ENV['DB_PORT'] ?? '3306';
        $this->db_name = $_ENV['DB_NAME'] ?? 'vitalis_agendamentos';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
    }

    // Função nativa para ler o arquivo .env sem precisar instalar pacotes
    private function carregarEnv() {
        // Caminho relativo: sobe uma pasta a partir de 'config' para achar o .env na raiz do 'backend'
        $caminhoEnv = __DIR__ . '/../.env';
        
        if (file_exists($caminhoEnv)) {
            $linhas = file($caminhoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($linhas as $linha) {
                // Ignora linhas de comentários que começam com #
                if (strpos(trim($linha), '#') === 0) continue;
                
                // Quebra a linha no sinal de = e salva na superglobal $_ENV
                if (strpos($linha, '=') !== false) {
                    list($chave, $valor) = explode('=', $linha, 2);
                    $_ENV[trim($chave)] = trim($valor);
                }
            }
        }
    }

    // Método principal que os Controllers vão usar para acessar o banco
    public function getConnection() {
        $this->conn = null;

        try {
            // String de conexão DSN do PDO
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            // Instancia o PDO
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // Configurações de segurança e tratamento de erros do PDO
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Define o retorno padrão como array associativo (facilita o JSON)
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $erro) {
            // Se der erro (senha errada, banco offline), o PHP avisa aqui
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro de Conexão com o Banco de Dados: ' . $erro->getMessage()
            ]);
            exit;
        }

        return $this->conn;
    }
}
?>