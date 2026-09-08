<?php
header('Content-Type: application/json');
require_once '../config/Database.php';

// Inicia a sessão para guardar quem está logado
session_start();

$dadosRecebidos = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $cpf = $dadosRecebidos['cpf'] ?? '';
    $senhaDigitada = $dadosRecebidos['senha'] ?? '';

    // Validação básica de formato
    if (!preg_match('/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/', $cpf) || empty($senhaDigitada)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'CPF ou senha inválidos.']);
        exit;
    }

    $db = new Database();
    $conn = $db->getConnection();

    try {
        // O JOIN: Junta os dados de acesso (tb_usuarios) com os dados pessoais (tb_pacientes)
        $query = "SELECT u.id, u.senha, u.perfil, p.nome_completo 
                  FROM tb_usuarios u 
                  INNER JOIN tb_pacientes p ON u.id = p.usuario_id 
                  WHERE p.cpf = :cpf LIMIT 1";
                  
        $stmt = $conn->prepare($query);
        $stmt->execute([':cpf' => $cpf]);
        $usuarioEncontrado = $stmt->fetch();

        // Verifica se o CPF existe e se a senha bate com a criptografia do banco
        if ($usuarioEncontrado && password_verify($senhaDigitada, $usuarioEncontrado['senha'])) {
            
            // Salva as credenciais na sessão do servidor (Seguro)
            $_SESSION['logado'] = true;
            $_SESSION['usuario_id'] = $usuarioEncontrado['id'];
            $_SESSION['perfil'] = $usuarioEncontrado['perfil'];
            $_SESSION['nome'] = $usuarioEncontrado['nome_completo'];

            // Define para qual painel o usuário vai com base no perfil
            $urlDestino = 'pages/dashboard-paciente.html'; // Padrão
            
            if ($usuarioEncontrado['perfil'] === 'MEDICO') {
                $urlDestino = 'pages/dashboard-medico.html';
            } elseif ($usuarioEncontrado['perfil'] === 'RECEPCAO' || $usuarioEncontrado['perfil'] === 'ADMIN') {
                $urlDestino = 'pages/dashboard.html';
            }

            echo json_encode([
                'sucesso' => true,
                'redirecionamento' => $urlDestino
            ]);
            
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'CPF não encontrado ou senha incorreta.']);
        }

    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro no servidor: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
}
?>