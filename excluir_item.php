<?php
// Protege a página: somente usuários autenticados têm permissão para excluir
require_once "auth.php";

// Conexão com o banco de dados
require_once "conexao.php";

// Obtém o ID passado via parâmetro GET
$id_item = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Se o ID for inválido, redireciona com mensagem de erro
if ($id_item <= 0) {
    header("Location: painel.php?erro=id_invalido");
    exit;
}

// D - DELETE: Remoção segura utilizando Prepared Statement (mysqli_prepare)
$sql_delete = "DELETE FROM cardapio WHERE id = ?";
$stmt_delete = mysqli_prepare($conexao, $sql_delete);

if ($stmt_delete) {
    // "i" indica tipo inteiro
    mysqli_stmt_bind_param($stmt_delete, "i", $id_item);

    if (mysqli_stmt_execute($stmt_delete)) {
        mysqli_stmt_close($stmt_delete);
        mysqli_close($conexao);
        // Redireciona para o painel com mensagem de sucesso
        header("Location: painel.php?msg=excluido");
        exit;
    } else {
        mysqli_stmt_close($stmt_delete);
        mysqli_close($conexao);
        header("Location: painel.php?erro=falha_exclusao");
        exit;
    }
} else {
    mysqli_close($conexao);
    header("Location: painel.php?erro=falha_exclusao");
    exit;
}
?>
