<?php
require_once 'config/Database.php';

$banco = new Database();
$conexao = $banco->getConnection();

if($conexao) {
    echo "Sucesso! O sistema está conectado ao banco de dados.";
}
?>