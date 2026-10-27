<?php
// Inicia a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpa todas as variáveis de sessão
$_SESSION = [];

// Se desejar destruir o cookie de sessão também
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão
session_destroy();

// Redireciona para o login informando sucesso no encerramento
header("Location: login.php?msg=logout_sucesso");
exit;
?>
