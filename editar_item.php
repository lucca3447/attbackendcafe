<?php
// Protege a página: somente usuários autenticados têm acesso
require_once "auth.php";

// Conexão com o banco de dados e funções auxiliares
require_once "conexao.php";
require_once "funcoes.php";

$mensagem_erro = "";

// 1. Obtém e valida o ID do item passado via URL (GET) ou formulário (POST)
$id_bruto = $_GET['id'] ?? ($_POST['id'] ?? null);
$id_item = validarId($id_bruto);

if ($id_item === false) {
    header("Location: painel.php?erro=id_invalido");
    exit;
}

// 2. U - UPDATE: Processa a atualização dos dados caso o formulário tenha sido enviado (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_item = trim($_POST['nome_item'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = normalizarPreco($_POST['preco'] ?? 0);

    $validacao = validarItemCardapio($nome_item, $categoria, $preco);

    if (!$validacao['valido']) {
        $mensagem_erro = $validacao['erro'];
    } else {
        // Atualização segura utilizando Prepared Statement (mysqli_prepare)
        $sql_update = "UPDATE cardapio SET nome_item = ?, categoria = ?, descricao = ?, preco = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($conexao, $sql_update);

        if ($stmt_update) {
            // "sssdi": string, string, string, double/decimal, integer
            mysqli_stmt_bind_param($stmt_update, "sssdi", $nome_item, $categoria, $descricao, $preco, $id_item);

            if (mysqli_stmt_execute($stmt_update)) {
                mysqli_stmt_close($stmt_update);
                mysqli_close($conexao);
                // Redireciona de volta ao painel com mensagem de sucesso
                header("Location: painel.php?msg=atualizado");
                exit;
            } else {
                $mensagem_erro = "Erro ao atualizar item: " . mysqli_stmt_error($stmt_update);
                mysqli_stmt_close($stmt_update);
            }
        } else {
            $mensagem_erro = "Erro na preparação da atualização SQL: " . mysqli_error($conexao);
        }
    }
}

// 3. Busca os dados atuais do item no banco para preencher o formulário
$sql_select = "SELECT id, nome_item, categoria, descricao, preco FROM cardapio WHERE id = ? LIMIT 1";
$stmt_select = mysqli_prepare($conexao, $sql_select);

if (!$stmt_select) {
    die("Erro ao preparar consulta: " . mysqli_error($conexao));
}

mysqli_stmt_bind_param($stmt_select, "i", $id_item);
mysqli_stmt_execute($stmt_select);
$resultado = mysqli_stmt_get_result($stmt_select);

if (!$resultado || mysqli_num_rows($resultado) === 0) {
    mysqli_stmt_close($stmt_select);
    mysqli_close($conexao);
    header("Location: painel.php?erro=item_nao_encontrado");
    exit;
}

$item_atual = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt_select);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Item #<?= $item_atual['id']; ?> - Cafeteria Aroma & Sabor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Cabeçalho -->
    <header class="cabecalho-sistema">
        <div class="container cabecalho-conteudo">
            <div class="logo-area">
                <span class="logo-icone">☕</span>
                <div>
                    <div class="logo-titulo">Cafeteria Aroma & Sabor</div>
                    <div class="logo-subtitulo">Edição de Item do Cardápio</div>
                </div>
            </div>

            <div class="usuario-area">
                <a href="painel.php" class="btn btn-secundario">
                    ⬅️ Voltar ao Painel
                </a>
            </div>
        </div>
    </header>

    <!-- Conteúdo -->
    <main class="container" style="margin-top: 25px; margin-bottom: 40px; max-width: 800px;">

        <?php if (!empty($mensagem_erro)): ?>
            <div class="alerta alerta-erro">
                <span>⚠️</span>
                <span><?= htmlspecialchars($mensagem_erro); ?></span>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-cabecalho">
                <h2 class="card-titulo">✏️ Atualizar Item: <?= htmlspecialchars($item_atual['nome_item']); ?></h2>
                <span style="font-size: 0.9rem; color: var(--cor-texto-suave);">ID #<?= $item_atual['id']; ?></span>
            </div>

            <form action="editar_item.php?id=<?= $item_atual['id']; ?>" method="POST" autocomplete="off">
                <input type="hidden" name="id" value="<?= $item_atual['id']; ?>">

                <div class="grid-form">
                    <!-- Nome do Item -->
                    <div class="campo-form">
                        <label for="nome_item">Nome do Item *</label>
                        <input 
                            type="text" 
                            id="nome_item" 
                            name="nome_item" 
                            required 
                            value="<?= htmlspecialchars($item_atual['nome_item']); ?>"
                        >
                    </div>

                    <!-- Categoria -->
                    <div class="campo-form">
                        <label for="categoria">Categoria *</label>
                        <select id="categoria" name="categoria" required>
                            <?php
                            $categorias = ["Café", "Doce", "Salgado", "Bebida Gelada", "Especial de Inverno"];
                            foreach ($categorias as $cat) {
                                $selected = ($item_atual['categoria'] === $cat) ? 'selected' : '';
                                echo "<option value=\"{$cat}\" {$selected}>{$cat}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Preço -->
                    <div class="campo-form">
                        <label for="preco">Preço (R$) *</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0.01" 
                            id="preco" 
                            name="preco" 
                            required 
                            value="<?= number_format($item_atual['preco'], 2, '.', ''); ?>"
                        >
                    </div>

                    <!-- Descrição -->
                    <div class="campo-form span-full">
                        <label for="descricao">Descrição dos Ingredientes / Detalhes</label>
                        <textarea 
                            id="descricao" 
                            name="descricao"
                        ><?= htmlspecialchars($item_atual['descricao']); ?></textarea>
                    </div>
                </div>

                <div class="acoes-form">
                    <a href="painel.php" class="btn btn-voltar">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primario">
                        💾 Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

    </main>

    <footer class="rodape">
        <p>&copy; <?= date('Y'); ?> Cafeteria Aroma & Sabor — Gestão do Cardápio</p>
    </footer>

</body>
</html>
<?php
mysqli_close($conexao);
?>
