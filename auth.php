<?php
// Inicia a sessão se ainda não tiver sido iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    // Redireciona para o login com mensagem de acesso negado
    header("Location: login.php?erro=acesso_negado");
    exit;
}
?>
