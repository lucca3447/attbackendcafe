<?php
// Inicia a sessao
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se o usuario ja estiver logado, redireciona ao painel
if (isset($_SESSION['usuario_id'])) {
    header("Location: painel.php");
    exit;
}

// Conexao com o banco de dados e funcoes auxiliares
require_once "conexao.php";
require_once "funcoes.php";

$mensagem_erro = "";
$mensagem_sucesso = "";

if (isset($_GET['erro']) && $_GET['erro'] === 'acesso_negado') {
    $mensagem_erro = "Acesso restrito. Efetue login para continuar.";
} elseif (isset($_GET['msg']) && $_GET['msg'] === 'logout_sucesso') {
    $mensagem_sucesso = "Sessao encerrada com sucesso.";
}

// Processamento do login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (empty($usuario) || empty($senha)) {
        $mensagem_erro = "Preencha todos os campos.";
    } else {
        $sql = "SELECT id, nome, usuario, senha FROM usuarios WHERE usuario = ? LIMIT 1";
        $stmt = mysqli_prepare($conexao, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $usuario);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            if ($resultado && $linha = mysqli_fetch_assoc($resultado)) {
                $senhaValida = verificarSenha($senha, $linha['senha']);

                if ($senhaValida) {
                    session_regenerate_id(true);
                    $_SESSION['usuario_id'] = $linha['id'];
                    $_SESSION['usuario_nome'] = $linha['nome'];
                    $_SESSION['usuario_login'] = $linha['usuario'];

                    mysqli_stmt_close($stmt);
                    mysqli_close($conexao);

                    header("Location: painel.php");
                    exit;
                } else {
                    $mensagem_erro = "Usuario ou senha invalidos.";
                }
            } else {
                $mensagem_erro = "Usuario ou senha invalidos.";
            }

            mysqli_stmt_close($stmt);
        } else {
            $mensagem_erro = "Erro ao processar consulta: " . mysqli_error($conexao);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gestao da Cafeteria</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-header">
            <h1>Gestao da Cafeteria</h1>
            <p>Acesso ao Sistema</p>
        </div>

        <?php if (!empty($mensagem_erro)): ?>
            <div class="alerta alerta-erro">
                <?= htmlspecialchars($mensagem_erro); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensagem_sucesso)): ?>
            <div class="alerta alerta-sucesso">
                <?= htmlspecialchars($mensagem_sucesso); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" autocomplete="off">
            <div class="campo-form">
                <label for="usuario">Usuario:</label>
                <input 
                    type="text" 
                    id="usuario" 
                    name="usuario" 
                    required 
                    autofocus
                    value="<?= isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>"
                >
            </div>

            <div class="campo-form">
                <label for="senha">Senha:</label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    required
                >
            </div>

            <div style="margin-top: 15px;">
                <button type="submit" class="btn btn-primario btn-bloco">
                    Entrar
                </button>
            </div>
        </form>

        <div class="dica-login">
            <strong>Credenciais Padrao:</strong><br>
            Usuario: <code>admin</code> | Senha: <code>admin123</code>
        </div>
    </div>

</body>
</html>
