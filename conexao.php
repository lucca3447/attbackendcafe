<?php
// Configurações de conexão com o banco de dados
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "gestao_cafeteria";

// Estabelecendo a conexão procedural com MySQLi
$conexao = mysqli_connect($host, $usuario, $senha, $banco);

// Verificação de falha na conexão
if (!$conexao) {
    die("Erro ao conectar com o banco de dados: " . mysqli_connect_error());
}

// Configura o charset para suporte correto a acentuação e caracteres especiais
mysqli_set_charset($conexao, "utf8mb4");
?>
