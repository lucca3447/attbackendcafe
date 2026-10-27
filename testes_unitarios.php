<?php
/**
 * testes_unitarios.php
 * Suite de Testes Unitarios e de Integracao para Gestao da Cafeteria.
 *
 * Execucao:
 *   php testes_unitarios.php
 */

require_once __DIR__ . '/funcoes.php';

$totalTestes = 0;
$testesPassaram = 0;
$testesFalharam = 0;
$inicioTempo = microtime(true);

function corVerde(string $texto): string { return "\033[32m{$texto}\033[0m"; }
function corVermelho(string $texto): string { return "\033[31m{$texto}\033[0m"; }

function registrarPassou(string $descricao): void {
    global $totalTestes, $testesPassaram;
    $totalTestes++;
    $testesPassaram++;
    echo "  " . corVerde("[PASS]") . " {$descricao}\n";
}

function registrarFalhou(string $descricao, $esperado, $obtido): void {
    global $totalTestes, $testesFalharam;
    $totalTestes++;
    $testesFalharam++;
    echo "  " . corVermelho("[FAIL]") . " {$descricao}\n";
    echo "         Esperado: " . var_export($esperado, true) . "\n";
    echo "         Obtido:   " . var_export($obtido, true) . "\n";
}

function assertEqual($esperado, $obtido, string $descricao): void {
    if ($esperado === $obtido) {
        registrarPassou($descricao);
    } else {
        registrarFalhou($descricao, $esperado, $obtido);
    }
}

function assertTrue($condicao, string $descricao): void {
    assertEqual(true, (bool)$condicao, $descricao);
}

function assertFalse($condicao, string $descricao): void {
    assertEqual(false, (bool)$condicao, $descricao);
}

echo "\n--- TESTES UNITARIOS: GESTAO DA CAFETERIA ---\n\n";

// 1. Autenticacao
echo "1. Autenticacao e Senhas:\n";
assertTrue(
    verificarSenha('admin123', 'admin123'),
    "Autentica senha em texto puro 'admin123'"
);

$hashBcrypt = password_hash('segredo456', PASSWORD_DEFAULT);
assertTrue(
    verificarSenha('segredo456', $hashBcrypt),
    "Autentica senha com hash Bcrypt"
);

assertFalse(
    verificarSenha('senha_errada', 'admin123'),
    "Rejeita senha incorreta para texto puro"
);
assertFalse(
    verificarSenha('senha_errada', $hashBcrypt),
    "Rejeita senha incorreta para hash"
);
assertFalse(
    verificarSenha('', 'admin123'),
    "Rejeita senha digitada vazia"
);
assertFalse(
    verificarSenha('admin123', ''),
    "Rejeita senha armazenada vazia"
);

// 2. Precos
echo "\n2. Normalizacao e Formatacao de Precos:\n";
assertEqual(14.50, normalizarPreco('14,50'), "Converte '14,50' com virgula para float 14.50");
assertEqual(14.50, normalizarPreco('14.50'), "Converte '14.50' com ponto para float 14.50");
assertEqual(25.90, normalizarPreco('R$ 25,90'), "Extrai valor numerico de 'R$ 25,90'");
assertEqual(0.0, normalizarPreco(''), "Retorna 0.0 para string vazia");
assertEqual(0.0, normalizarPreco('invalido'), "Retorna 0.0 para texto nao numerico");
assertEqual('R$ 6,50', formatarMoeda(6.5), "Formata 6.5 como 'R$ 6,50'");
assertEqual('R$ 14,00', formatarMoeda(14.0), "Formata 14.0 como 'R$ 14,00'");
assertEqual('R$ 1.250,90', formatarMoeda(1250.90), "Formata milhar com ponto e virgula");

// 3. Validacao de Itens
echo "\n3. Validacao de Itens do Cardapio:\n";
$resValido = validarItemCardapio('Cafe Espresso', 'Cafe', '6,50');
assertTrue($resValido['valido'], "Item com dados validos deve ser aprovado");
assertEqual(null, $resValido['erro'], "Item valido sem mensagem de erro");

$resNomeVazio = validarItemCardapio('', 'Cafe', '6,50');
assertFalse($resNomeVazio['valido'], "Item com nome vazio deve ser reprovado");

$resNomeCurto = validarItemCardapio('A', 'Cafe', '6,50');
assertFalse($resNomeCurto['valido'], "Item com nome menor que 2 caracteres deve ser reprovado");

$resCatInvalida = validarItemCardapio('Item', 'CategoriaInexistente', '8,00');
assertFalse($resCatInvalida['valido'], "Item com categoria invalida deve ser reprovado");

$resPrecoZero = validarItemCardapio('Agua', 'Bebida Gelada', '0,00');
assertFalse($resPrecoZero['valido'], "Item com preco zero deve ser reprovado");

$resPrecoNegativo = validarItemCardapio('Agua', 'Bebida Gelada', '-5.00');
assertFalse($resPrecoNegativo['valido'], "Item com preco negativo deve ser reprovado");

// 4. Validacao de ID
echo "\n4. Sanitizacao e Validacao de IDs:\n";
assertEqual(1, validarId('1'), "ID '1' numerico retorna inteiro 1");
assertEqual(42, validarId(42), "ID 42 inteiro retorna inteiro 42");
assertEqual(999, validarId('999'), "ID '999' retorna inteiro 999");
assertFalse(validarId('0'), "ID zero deve ser rejeitado");
assertFalse(validarId('-5'), "ID negativo deve ser rejeitado");
assertFalse(validarId('abc'), "ID alfanumerico deve ser rejeitado");
assertFalse(validarId(null), "ID nulo deve ser rejeitado");
assertFalse(validarId("1 OR 1=1"), "Injecao SQL '1 OR 1=1' deve ser rejeitada");
assertFalse(validarId("1; DROP TABLE usuarios;--"), "Injecao SQL '1; DROP...' deve ser rejeitada");
assertFalse(validarId("' UNION SELECT 1,2,3--"), "Injecao SQL UNION deve ser rejeitada");

// 5. Badges
echo "\n5. Classes CSS de Badges:\n";
assertEqual('badge-cafe', obterClasseBadge('Cafe'), "Categoria 'Cafe' retorna 'badge-cafe'");
assertEqual('badge-cafe', obterClasseBadge('cafe'), "Categoria 'cafe' minuscula retorna 'badge-cafe'");
assertEqual('badge-doce', obterClasseBadge('Doce'), "Categoria 'Doce' retorna 'badge-doce'");
assertEqual('badge-salgado', obterClasseBadge('Salgado'), "Categoria 'Salgado' retorna 'badge-salgado'");
assertEqual('badge-bebida', obterClasseBadge('Bebida Gelada'), "Categoria 'Bebida Gelada' retorna 'badge-bebida'");
assertEqual('badge-padrao', obterClasseBadge('Outros'), "Categoria desconhecida retorna 'badge-padrao'");

// 6. CRUD com Prepared Statements em SQLite memoria
echo "\n6. Operacoes de CRUD com Prepared Statements (SQLite memoria):\n";
$dbMemoria = new SQLite3(':memory:');
$dbMemoria->exec("
    CREATE TABLE cardapio (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome_item TEXT NOT NULL,
        categoria TEXT NOT NULL,
        descricao TEXT,
        preco REAL NOT NULL,
        disponivel INTEGER DEFAULT 1
    )
");

// Create
$stmtInsert = $dbMemoria->prepare("INSERT INTO cardapio (nome_item, categoria, descricao, preco) VALUES (:nome, :cat, :desc, :preco)");
$stmtInsert->bindValue(':nome', 'Torta de Limao', SQLITE3_TEXT);
$stmtInsert->bindValue(':cat', 'Doce', SQLITE3_TEXT);
$stmtInsert->bindValue(':desc', 'Torta de limao.', SQLITE3_TEXT);
$stmtInsert->bindValue(':preco', 16.50, SQLITE3_FLOAT);
$execInsert = $stmtInsert->execute();
assertTrue($execInsert !== false, "CREATE: Prepared statement INSERT executado");

$idCriado = $dbMemoria->lastInsertRowID();
assertEqual(1, $idCriado, "CREATE: ID gerado igual a 1");

// Read
$stmtSelect = $dbMemoria->prepare("SELECT * FROM cardapio WHERE id = :id");
$stmtSelect->bindValue(':id', $idCriado, SQLITE3_INTEGER);
$resSelect = $stmtSelect->execute();
$itemLido = $resSelect->fetchArray(SQLITE3_ASSOC);
assertEqual('Torta de Limao', $itemLido['nome_item'], "READ: Nome do item correto");
assertEqual(16.50, (float)$itemLido['preco'], "READ: Preco do item correto");

// Update
$stmtUpdate = $dbMemoria->prepare("UPDATE cardapio SET nome_item = :nome, preco = :preco WHERE id = :id");
$stmtUpdate->bindValue(':nome', 'Torta de Limao Especial', SQLITE3_TEXT);
$stmtUpdate->bindValue(':preco', 18.00, SQLITE3_FLOAT);
$stmtUpdate->bindValue(':id', $idCriado, SQLITE3_INTEGER);
$execUpdate = $stmtUpdate->execute();
assertTrue($execUpdate !== false, "UPDATE: Prepared statement UPDATE executado");

$stmtCheckUpdate = $dbMemoria->prepare("SELECT COUNT(*) as total, nome_item, preco FROM cardapio WHERE id = :id");
$stmtCheckUpdate->bindValue(':id', $idCriado, SQLITE3_INTEGER);
$itemAtualizado = $stmtCheckUpdate->execute()->fetchArray(SQLITE3_ASSOC);
assertEqual(1, $itemAtualizado['total'], "UPDATE: Nao duplicou registro");
assertEqual('Torta de Limao Especial', $itemAtualizado['nome_item'], "UPDATE: Nome atualizado");
assertEqual(18.00, (float)$itemAtualizado['preco'], "UPDATE: Preco atualizado");

// SQL Injection Test
$stringMaliciosa = "' OR '1'='1";
$stmtInjectTest = $dbMemoria->prepare("INSERT INTO cardapio (nome_item, categoria, descricao, preco) VALUES (:nome, 'Cafe', 'Teste', 5.0)");
$stmtInjectTest->bindValue(':nome', $stringMaliciosa, SQLITE3_TEXT);
$stmtInjectTest->execute();

$stmtCheckInject = $dbMemoria->prepare("SELECT nome_item FROM cardapio WHERE nome_item = :nome");
$stmtCheckInject->bindValue(':nome', $stringMaliciosa, SQLITE3_TEXT);
$itemInjetado = $stmtCheckInject->execute()->fetchArray(SQLITE3_ASSOC);
assertEqual($stringMaliciosa, $itemInjetado['nome_item'], "SEGURANCA: String de injecao SQL tratada como texto puro");

// Delete
$stmtDelete = $dbMemoria->prepare("DELETE FROM cardapio WHERE id = :id");
$stmtDelete->bindValue(':id', $idCriado, SQLITE3_INTEGER);
$execDelete = $stmtDelete->execute();
assertTrue($execDelete !== false, "DELETE: Prepared statement DELETE executado");

$stmtCheckDelete = $dbMemoria->prepare("SELECT * FROM cardapio WHERE id = :id");
$stmtCheckDelete->bindValue(':id', $idCriado, SQLITE3_INTEGER);
$itemExcluido = $stmtCheckDelete->execute()->fetchArray(SQLITE3_ASSOC);
assertFalse($itemExcluido, "DELETE: Item removido da tabela");

$dbMemoria->close();

$tempoTotal = round((microtime(true) - $inicioTempo) * 1000, 2);

echo "\n------------------------------------------------------------\n";
echo "RESULTADO: {$totalTestes} testes executados | {$testesPassaram} sucesso | {$testesFalharam} falhas ({$tempoTotal} ms)\n";
echo "------------------------------------------------------------\n\n";

exit($testesFalharam > 0 ? 1 : 0);
?>
