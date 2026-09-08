<?php
header('Content-Type: application/json');
require_once '../config/Database.php';

// Recebe os dados do fetch()
$dados = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    
    // Nome: Apenas letras (incluindo acentos) e espaços. Mínimo 3, máximo 150 caracteres.
    if (!preg_match('/^[a-zA-ZÀ-ÿ\s]{3,150}$/', $dados['nome'])) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Nome inválido. Use apenas letras e espaços.']);
        exit;
    }

    // CPF: Força o formato exato com máscara (000.000.000-00)
    if (!preg_match('/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/', $dados['cpf'])) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'CPF inválido. Use o formato 000.000.000-00.']);
        exit;
    }

    // Telefone: Força o formato com DDD e hífen ( (00) 00000-0000 ou (00) 0000-0000 )
    if (!preg_match('/^\(\d{2}\)\s\d{4,5}\-\d{4}$/', $dados['telefone'])) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Telefone inválido. Use o formato (00) 00000-0000.']);
        exit;
    }

    // Data de Nascimento: Formato ISO gerado pelo <input type="date"> (YYYY-MM-DD)
    if (!preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $dados['nascimento'])) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Data de nascimento inválida.']);
        exit;
    }

    // E-mail: Usando a função nativa do PHP, que é mais segura e rápida que Regex para e-mails
    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Formato de e-mail inválido.']);
        exit;
    }

    // Senha: Pelo menos 8 caracteres (pode ser ajustado para exigir letras, números, etc.)
    if (strlen($dados['senha']) < 8) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'A senha deve ter pelo menos 8 caracteres.']);
        exit;
    }
    
    $db = new Database();
    $conn = $db->getConnection();

    try {
        $conn->beginTransaction();

        $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);

        // Insere na tb_usuarios
        $queryUser = "INSERT INTO tb_usuarios (email, senha, perfil) VALUES (:email, :senha, 'PACIENTE')";
        $stmtUser = $conn->prepare($queryUser);
        $stmtUser->execute([
            ':email' => $dados['email'],
            ':senha' => $senhaHash
        ]);

        $idUsuarioGerado = $conn->lastInsertId();

        // Insere na tb_pacientes
        $queryPaciente = "INSERT INTO tb_pacientes (usuario_id, nome_completo, cpf, telefone, data_nascimento) VALUES (:usuario_id, :nome, :cpf, :telefone, :nascimento)";
        $stmtPaciente = $conn->prepare($queryPaciente);
        $stmtPaciente->execute([
            ':usuario_id' => $idUsuarioGerado,
            ':nome' => $dados['nome'],
            ':cpf' => $dados['cpf'],
            ':telefone' => $dados['telefone'],
            ':nascimento' => $dados['nascimento']
        ]);

        $conn->commit();

        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Cadastro realizado com sucesso! Redirecionando...'
        ]);

    } catch (PDOException $e) {
        $conn->rollBack();
        
        $msgErro = 'Erro ao cadastrar.';
        if ($e->getCode() == 23000) {
            $msgErro = 'Este e-mail ou CPF já está cadastrado no sistema.';
        }
        
        echo json_encode(['sucesso' => false, 'mensagem' => $msgErro]);
    }

} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método inválido.']);
}
?>