<?php
// Inicia a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se o usuário já estiver logado, redireciona diretamente ao painel
if (isset($_SESSION['usuario_id'])) {
    header("Location: painel.php");
    exit;
}

// Inclui a conexão com o banco de dados
require_once "conexao.php";

$mensagem_erro = "";
$mensagem_sucesso = "";

// Tratamento de mensagens passadas via GET
if (isset($_GET['erro']) && $_GET['erro'] === 'acesso_negado') {
    $mensagem_erro = "Acesso restrito! Por favor, faça login para continuar.";
} elseif (isset($_GET['msg']) && $_GET['msg'] === 'logout_sucesso') {
    $mensagem_sucesso = "Você saiu do sistema com segurança.";
}

// Processamento do formulário de login (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (empty($usuario) || empty($senha)) {
        $mensagem_erro = "Por favor, preencha todos os campos.";
    } else {
        // Consulta segura utilizando Prepared Statements contra SQL Injection
        $sql = "SELECT id, nome, usuario, senha FROM usuarios WHERE usuario = ? LIMIT 1";
        $stmt = mysqli_prepare($conexao, $sql);

        if ($stmt) {
            // Vincula o parâmetro: "s" indica string
            mysqli_stmt_bind_param($stmt, "s", $usuario);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            if ($resultado && $linha = mysqli_fetch_assoc($resultado)) {
                // Suporta tanto senha com hash seguro (password_verify) quanto texto puro (script padrão da aula)
                $senhaValida = password_verify($senha, $linha['senha']) || ($senha === $linha['senha']);

                if ($senhaValida) {
                    // Regenera o ID de sessão para segurança adicional
                    session_regenerate_id(true);

                    // Salva as credenciais do usuário na sessão
                    $_SESSION['usuario_id'] = $linha['id'];
                    $_SESSION['usuario_nome'] = $linha['nome'];
                    $_SESSION['usuario_login'] = $linha['usuario'];

                    mysqli_stmt_close($stmt);
                    mysqli_close($conexao);

                    // Redireciona para o painel principal
                    header("Location: painel.php");
                    exit;
                } else {
                    $mensagem_erro = "Usuário ou senha inválidos.";
                }
            } else {
                $mensagem_erro = "Usuário ou senha inválidos.";
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
    <title>Login - Gestão da Cafeteria</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-header">
            <div class="logo-icone">☕</div>
            <h1>Cafeteria Aroma & Sabor</h1>
            <p>Painel de Gestão do Cardápio</p>
        </div>

        <?php if (!empty($mensagem_erro)): ?>
            <div class="alerta alerta-erro">
                <span>⚠️</span>
                <span><?= htmlspecialchars($mensagem_erro); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensagem_sucesso)): ?>
            <div class="alerta alerta-sucesso">
                <span>✅</span>
                <span><?= htmlspecialchars($mensagem_sucesso); ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" autocomplete="off">
            <div class="campo-form">
                <label for="usuario">Usuário:</label>
                <input 
                    type="text" 
                    id="usuario" 
                    name="usuario" 
                    placeholder="Ex: admin" 
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
                    placeholder="Digite sua senha" 
                    required
                >
            </div>

            <div style="margin-top: 10px;">
                <button type="submit" class="btn btn-primario btn-bloco">
                    Entrar no Sistema
                </button>
            </div>
        </form>

        <div class="dica-login">
            <strong>Credenciais de Acesso (Padrão):</strong><br>
            Usuário: <code>admin</code><br>
            Senha: <code>admin123</code>
        </div>
    </div>

</body>
</html>
