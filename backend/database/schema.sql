
CREATE DATABASE IF NOT EXISTS vitalis_agendamentos DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vitalis_agendamentos;

CREATE TABLE tb_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL, -- As senhas devem ser salvas com password_hash() no PHP
    perfil ENUM('PACIENTE', 'MEDICO', 'RECEPCAO', 'ADMIN') NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tb_pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_completo VARCHAR(150) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    data_nascimento DATE NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES tb_usuarios(id) ON DELETE CASCADE
);

CREATE TABLE tb_profissionais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_completo VARCHAR(150) NOT NULL,
    cargo ENUM('MEDICO', 'ENFERMEIRO', 'RECEPCAO') NOT NULL,
    especialidade VARCHAR(100) NULL,
    registro_conselho VARCHAR(50) NULL,-- Ex: CRM ou CORE N
    telefone VARCHAR(20) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES tb_usuarios(id) ON DELETE CASCADE
);

CREATE TABLE tb_agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    profissional_id INT NULL, 
    data_consulta DATE NOT NULL,
    horario TIME NOT NULL,
    status ENUM('AGUARDANDO', 'ATENDIMENTO', 'CONCLUIDO', 'CANCELADO', 'NAO_COMPARECEU') DEFAULT 'AGUARDANDO',
    observacoes TEXT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES tb_pacientes(id),
    FOREIGN KEY (profissional_id) REFERENCES tb_profissionais(id)
);