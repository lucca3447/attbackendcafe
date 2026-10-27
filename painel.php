<?php
// Protege a pagina: somente usuarios autenticados tem acesso
require_once "auth.php";

// Conexao com o banco de dados e funcoes auxiliares
require_once "conexao.php";
require_once "funcoes.php";

$mensagem_sucesso = "";
$mensagem_erro = "";

// Tratamento de mensagens passadas via URL (GET)
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'cadastrado':
            $mensagem_sucesso = "Item adicionado com sucesso.";
            break;
        case 'atualizado':
            $mensagem_sucesso = "Item atualizado com sucesso.";
            break;
        case 'excluido':
            $mensagem_sucesso = "Item excluido com sucesso.";
            break;
    }
}

if (isset($_GET['erro'])) {
    switch ($_GET['erro']) {
        case 'id_invalido':
            $mensagem_erro = "Identificador do item invalido.";
            break;
        case 'item_nao_encontrado':
            $mensagem_erro = "Item nao encontrado.";
            break;
        case 'falha_exclusao':
            $mensagem_erro = "Falha ao tentar excluir o item.";
            break;
    }
}

// C - CREATE: Cadastro de novo item
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $nome_item = trim($_POST['nome_item'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = normalizarPreco($_POST['preco'] ?? 0);

    $validacao = validarItemCardapio($nome_item, $categoria, $preco);

    if (!$validacao['valido']) {
        $mensagem_erro = $validacao['erro'];
    } else {
        $sql_insert = "INSERT INTO cardapio (nome_item, categoria, descricao, preco) VALUES (?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($conexao, $sql_insert);

        if ($stmt_insert) {
            mysqli_stmt_bind_param($stmt_insert, "sssd", $nome_item, $categoria, $descricao, $preco);

            if (mysqli_stmt_execute($stmt_insert)) {
                mysqli_stmt_close($stmt_insert);
                header("Location: painel.php?msg=cadastrado");
                exit;
            } else {
                $mensagem_erro = "Erro ao cadastrar item: " . mysqli_stmt_error($stmt_insert);
                mysqli_stmt_close($stmt_insert);
            }
        } else {
            $mensagem_erro = "Erro na preparacao do SQL: " . mysqli_error($conexao);
        }
    }
}

// R - READ: Consulta dos itens
$sql_select = "SELECT id, nome_item, categoria, descricao, preco, disponivel FROM cardapio ORDER BY id DESC";
$resultado_itens = mysqli_query($conexao, $sql_select);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Gestao da Cafeteria</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="cabecalho-sistema">
        <div class="container cabecalho-conteudo">
            <div class="logo-area">
                <div>
                    <div class="logo-titulo">Gestao da Cafeteria</div>
                    <div class="logo-subtitulo">Painel Administrativo</div>
                </div>
            </div>

            <div class="usuario-area">
                <span class="usuario-saudacao">
                    Usuario: <span class="usuario-nome"><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Gerente'); ?></span>
                </span>
                <a href="logout.php" class="btn btn-secundario">
                    Sair
                </a>
            </div>
        </div>
    </header>

    <main class="container" style="margin-top: 25px; margin-bottom: 40px;">

        <?php if (!empty($mensagem_sucesso)): ?>
            <div class="alerta alerta-sucesso">
                <?= htmlspecialchars($mensagem_sucesso); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensagem_erro)): ?>
            <div class="alerta alerta-erro">
                <?= htmlspecialchars($mensagem_erro); ?>
            </div>
        <?php endif; ?>

        <!-- C - CREATE -->
        <section class="card">
            <div class="card-cabecalho">
                <h2 class="card-titulo">Cadastrar Item no Cardapio</h2>
            </div>

            <form action="painel.php" method="POST" autocomplete="off">
                <input type="hidden" name="acao" value="cadastrar">

                <div class="grid-form">
                    <div class="campo-form">
                        <label for="nome_item">Nome do Item *</label>
                        <input 
                            type="text" 
                            id="nome_item" 
                            name="nome_item" 
                            placeholder="Ex: Cafe Espresso" 
                            required
                        >
                    </div>

                    <div class="campo-form">
                        <label for="categoria">Categoria *</label>
                        <select id="categoria" name="categoria" required>
                            <option value="">Selecione...</option>
                            <option value="Cafe">Cafe</option>
                            <option value="Doce">Doce</option>
                            <option value="Salgado">Salgado</option>
                            <option value="Bebida Gelada">Bebida Gelada</option>
                        </select>
                    </div>

                    <div class="campo-form">
                        <label for="preco">Preco (R$) *</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0.01" 
                            id="preco" 
                            name="preco" 
                            placeholder="Ex: 8.50" 
                            required
                        >
                    </div>

                    <div class="campo-form span-full">
                        <label for="descricao">Descricao</label>
                        <textarea 
                            id="descricao" 
                            name="descricao" 
                            placeholder="Descricao do produto..."
                        ></textarea>
                    </div>
                </div>

                <div class="acoes-form">
                    <button type="submit" class="btn btn-primario">
                        Salvar Item
                    </button>
                </div>
            </form>
        </section>

        <!-- R - READ -->
        <section class="card">
            <div class="card-cabecalho">
                <h2 class="card-titulo">Itens do Cardapio</h2>
                <span style="font-size: 0.9rem; color: var(--cor-texto-suave);">
                    Total: <?= $resultado_itens ? mysqli_num_rows($resultado_itens) : 0; ?>
                </span>
            </div>

            <div class="tabela-responsiva">
                <table class="tabela-itens">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Nome do Item</th>
                            <th>Categoria</th>
                            <th>Descricao</th>
                            <th>Preco</th>
                            <th style="text-align: right; width: 170px;">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado_itens && mysqli_num_rows($resultado_itens) > 0): ?>
                            <?php while ($item = mysqli_fetch_assoc($resultado_itens)): ?>
                                <tr>
                                    <td><?= (int)$item['id']; ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['nome_item']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge-categoria <?= obterClasseBadge($item['categoria']); ?>">
                                            <?= htmlspecialchars($item['categoria']); ?>
                                        </span>
                                    </td>
                                    <td style="color: var(--cor-texto-suave); font-size: 0.9rem;">
                                        <?= !empty($item['descricao']) ? nl2br(htmlspecialchars($item['descricao'])) : '-'; ?>
                                    </td>
                                    <td class="preco-destaque">
                                        <?= formatarMoeda((float)$item['preco']); ?>
                                    </td>
                                    <td>
                                        <div class="col-acoes">
                                            <!-- U - UPDATE -->
                                            <a 
                                                href="editar_item.php?id=<?= (int)$item['id']; ?>" 
                                                class="btn btn-editar"
                                            >
                                                Editar
                                            </a>

                                            <!-- D - DELETE -->
                                            <a 
                                                href="excluir_item.php?id=<?= (int)$item['id']; ?>" 
                                                class="btn btn-excluir" 
                                                onclick="return confirm('Deseja excluir o item <?= htmlspecialchars(addslashes($item['nome_item'])); ?>?');"
                                            >
                                                Excluir
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 25px; color: var(--cor-texto-suave);">
                                    Nenhum item cadastrado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <footer class="rodape">
        <p>Sistema de Gestao de Cardapio</p>
    </footer>

</body>
</html>
<?php
mysqli_close($conexao);
?>
