<?php
// Protege a página: somente usuários autenticados têm acesso
require_once "auth.php";

// Conexão com o banco de dados e funções auxiliares
require_once "conexao.php";
require_once "funcoes.php";

$mensagem_sucesso = "";
$mensagem_erro = "";

// Tratamento de mensagens passadas via URL (GET)
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'cadastrado':
            $mensagem_sucesso = "Novo item adicionado ao cardápio com sucesso!";
            break;
        case 'atualizado':
            $mensagem_sucesso = "Item do cardápio atualizado com sucesso!";
            break;
        case 'excluido':
            $mensagem_sucesso = "Item removido do cardápio com sucesso!";
            break;
    }
}

if (isset($_GET['erro'])) {
    switch ($_GET['erro']) {
        case 'id_invalido':
            $mensagem_erro = "Identificador do item inválido ou não fornecido.";
            break;
        case 'item_nao_encontrado':
            $mensagem_erro = "O item solicitado não foi encontrado no banco de dados.";
            break;
        case 'falha_exclusao':
            $mensagem_erro = "Ocorreu uma falha ao tentar excluir o item.";
            break;
    }
}

// C - CREATE: Processa o cadastro de novo item quando o formulário é enviado (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $nome_item = trim($_POST['nome_item'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = normalizarPreco($_POST['preco'] ?? 0);

    // Validação usando regra de negócio centralizada
    $validacao = validarItemCardapio($nome_item, $categoria, $preco);

    if (!$validacao['valido']) {
        $mensagem_erro = $validacao['erro'];
    } else {
        // Inserção segura utilizando Prepared Statement (mysqli_prepare)
        $sql_insert = "INSERT INTO cardapio (nome_item, categoria, descricao, preco) VALUES (?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($conexao, $sql_insert);

        if ($stmt_insert) {
            // "sssd": string, string, string, double/decimal
            mysqli_stmt_bind_param($stmt_insert, "sssd", $nome_item, $categoria, $descricao, $preco);

            if (mysqli_stmt_execute($stmt_insert)) {
                mysqli_stmt_close($stmt_insert);
                // Redireciona para evitar reenvio do formulário ao atualizar a página (padrão PRG)
                header("Location: painel.php?msg=cadastrado");
                exit;
            } else {
                $mensagem_erro = "Erro ao cadastrar item: " . mysqli_stmt_error($stmt_insert);
                mysqli_stmt_close($stmt_insert);
            }
        } else {
            $mensagem_erro = "Erro na preparação do comando SQL: " . mysqli_error($conexao);
        }
    }
}

// R - READ: Consulta todos os itens cadastrados no cardápio
$sql_select = "SELECT id, nome_item, categoria, descricao, preco, disponivel FROM cardapio ORDER BY id DESC";
$resultado_itens = mysqli_query($conexao, $sql_select);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Gestão do Cardápio - Cafeteria</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Cabeçalho do Painel -->
    <header class="cabecalho-sistema">
        <div class="container cabecalho-conteudo">
            <div class="logo-area">
                <span class="logo-icone">☕</span>
                <div>
                    <div class="logo-titulo">Cafeteria Aroma & Sabor</div>
                    <div class="logo-subtitulo">Painel Administrativo</div>
                </div>
            </div>

            <div class="usuario-area">
                <span class="usuario-saudacao">
                    Olá, <span class="usuario-nome"><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Gerente'); ?></span>!
                </span>
                <a href="logout.php" class="btn btn-secundario">
                    🚪 Sair
                </a>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container" style="margin-top: 25px; margin-bottom: 40px;">

        <!-- Mensagens de Feedback -->
        <?php if (!empty($mensagem_sucesso)): ?>
            <div class="alerta alerta-sucesso">
                <span>✅</span>
                <span><?= htmlspecialchars($mensagem_sucesso); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensagem_erro)): ?>
            <div class="alerta alerta-erro">
                <span>⚠️</span>
                <span><?= htmlspecialchars($mensagem_erro); ?></span>
            </div>
        <?php endif; ?>

        <!-- C - CREATE: Card com Formulário de Cadastro -->
        <section class="card">
            <div class="card-cabecalho">
                <h2 class="card-titulo">➕ Cadastrar Novo Item no Cardápio</h2>
            </div>

            <form action="painel.php" method="POST" autocomplete="off">
                <input type="hidden" name="acao" value="cadastrar">

                <div class="grid-form">
                    <!-- Nome do Item -->
                    <div class="campo-form">
                        <label for="nome_item">Nome do Item *</label>
                        <input 
                            type="text" 
                            id="nome_item" 
                            name="nome_item" 
                            placeholder="Ex: Mocha com Chocolate Suíço" 
                            required
                        >
                    </div>

                    <!-- Categoria -->
                    <div class="campo-form">
                        <label for="categoria">Categoria *</label>
                        <select id="categoria" name="categoria" required>
                            <option value="">Selecione uma categoria...</option>
                            <option value="Café">Café</option>
                            <option value="Doce">Doce</option>
                            <option value="Salgado">Salgado</option>
                            <option value="Bebida Gelada">Bebida Gelada</option>
                            <option value="Especial de Inverno">Especial</option>
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
                            placeholder="Ex: 14.50" 
                            required
                        >
                    </div>

                    <!-- Descrição -->
                    <div class="campo-form span-full">
                        <label for="descricao">Descrição dos Ingredientes / Detalhes</label>
                        <textarea 
                            id="descricao" 
                            name="descricao" 
                            placeholder="Descreva o sabor, ingredientes principais ou modo de preparo..."
                        ></textarea>
                    </div>
                </div>

                <div class="acoes-form">
                    <button type="submit" class="btn btn-primario">
                        Salvar Item no Cardápio
                    </button>
                </div>
            </form>
        </section>

        <!-- R - READ: Card com Listagem dos Itens -->
        <section class="card">
            <div class="card-cabecalho">
                <h2 class="card-titulo">📋 Cardápio Atual da Cafeteria</h2>
                <span style="font-size: 0.9rem; color: var(--cor-texto-suave);">
                    Total: <?= $resultado_itens ? mysqli_num_rows($resultado_itens) : 0; ?> itens
                </span>
            </div>

            <div class="tabela-responsiva">
                <table class="tabela-itens">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Nome do Item</th>
                            <th>Categoria</th>
                            <th>Descrição</th>
                            <th>Preço</th>
                            <th style="text-align: right; width: 170px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado_itens && mysqli_num_rows($resultado_itens) > 0): ?>
                            <?php while ($item = mysqli_fetch_assoc($resultado_itens)): ?>
                                <tr>
                                    <td><strong>#<?= (int)$item['id']; ?></strong></td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['nome_item']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge-categoria <?= obterClasseBadge($item['categoria']); ?>">
                                            <?= htmlspecialchars($item['categoria']); ?>
                                        </span>
                                    </td>
                                    <td style="color: var(--cor-texto-suave); font-size: 0.9rem;">
                                        <?= !empty($item['descricao']) ? nl2br(htmlspecialchars($item['descricao'])) : '<em>Sem descrição cadastrada</em>'; ?>
                                    </td>
                                    <td class="preco-destaque">
                                        <?= formatarMoeda((float)$item['preco']); ?>
                                    </td>
                                    <td>
                                        <div class="col-acoes">
                                            <!-- U - UPDATE: Link para editar_item.php -->
                                            <a 
                                                href="editar_item.php?id=<?= (int)$item['id']; ?>" 
                                                class="btn btn-editar"
                                                title="Editar dados deste item"
                                            >
                                                ✏️ Editar
                                            </a>

                                            <!-- D - DELETE: Link para excluir_item.php com confirmação em JS -->
                                            <a 
                                                href="excluir_item.php?id=<?= (int)$item['id']; ?>" 
                                                class="btn btn-excluir" 
                                                onclick="return confirm('Deseja realmente remover o item \'<?= htmlspecialchars(addslashes($item['nome_item'])); ?>\' do cardápio?');"
                                                title="Remover este item definitivamente"
                                            >
                                                🗑️ Excluir
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px; color: var(--cor-texto-suave);">
                                    ☕ Nenhum item cadastrado no cardápio até o momento. Utilize o formulário acima para cadastrar!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <footer class="rodape">
        <p>&copy; <?= date('Y'); ?> Cafeteria Aroma & Sabor — Sistema de Gestão de Cardápio</p>
    </footer>

</body>
</html>
<?php
// Fecha a conexão com o banco
mysqli_close($conexao);
?>
