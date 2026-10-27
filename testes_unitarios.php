<?php
/**
 * testes_unitarios.php
 * Suíte de Testes Unitários e de Integração para a Cafeteria Aroma & Sabor.
 * 
 * Execução simples pelo terminal:
 *   php testes_unitarios.php
 */

require_once __DIR__ . '/funcoes.php';

// Controle de Resultados
$totalTestes = 0;
$testesPassaram = 0;
$testesFalharam = 0;
$inicioTempo = microtime(true);

// Cores para saída no terminal (compatível com ANSI)
function corVerde(string $texto): string { return "\033[32m{$texto}\033[0m"; }
function corVermelho(string $texto): string { return "\033[31m{$texto}\033[0m"; }
function corAzul(string $texto): string { return "\033[34m{$texto}\033[0m"; }
function corAmarelo(string $texto): string { return "\033[33m{$texto}\033[0m"; }

function registrarPassou(string $descricao): void {
    global $totalTestes, $testesPassaram;
    $totalTestes++;
    $testesPassaram++;
    echo "  " . corVerde("✔ PASS") . " - {$descricao}\n";
}

function registrarFalhou(string $descricao, $esperado, $obtido): void {
    global $totalTestes, $testesFalharam;
    $totalTestes++;
    $testesFalharam++;
    echo "  " . corVermelho("✖ FAIL") . " - {$descricao}\n";
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

echo "\n" . corAzul("============================================================\n");
echo corAzul("   ☕ SUÍTE DE TESTES UNITÁRIOS - GESTÃO DA CAFETERIA\n");
echo corAzul("============================================================\n\n");

// =========================================================================
// 1. TESTES DE AUTENTICAÇÃO E VERIFICAÇÃO DE SENHAS
// =========================================================================
echo corAmarelo("▶ 1. Testes de Autenticação e Senhas (Login)\n");

// 1.1 Senha em texto puro padrão da atividade ('admin123')
assertTrue(
    verificarSenha('admin123', 'admin123'),
    "Deve autenticar com sucesso a senha padrão 'admin123' em texto puro"
);

// 1.2 Senha com hash Bcrypt
$hashBcrypt = password_hash('segredo456', PASSWORD_DEFAULT);
assertTrue(
    verificarSenha('segredo456', $hashBcrypt),
    "Deve autenticar com sucesso senha protegida por hash Bcrypt (password_verify)"
);

// 1.3 Senha incorreta deve ser rejeitada
assertFalse(
    verificarSenha('senha_errada', 'admin123'),
    "Deve rejeitar senha incorreta para credencial em texto puro"
);
assertFalse(
    verificarSenha('senha_errada', $hashBcrypt),
    "Deve rejeitar senha incorreta para credencial com hash"
);

// 1.4 Senhas vazias
assertFalse(
    verificarSenha('', 'admin123'),
    "Deve rejeitar quando a senha digitada estiver vazia"
);
assertFalse(
    verificarSenha('admin123', ''),
    "Deve rejeitar quando a senha armazenada estiver vazia"
);

// =========================================================================
// 2. TESTES DE NORMALIZAÇÃO E FORMATAÇÃO DE PREÇOS
// =========================================================================
echo "\n" . corAmarelo("▶ 2. Testes de Normalização e Formatação de Preços\n");

assertEqual(14.50, normalizarPreco('14,50'), "Deve converter '14,50' com vírgula para 14.50 (float)");
assertEqual(14.50, normalizarPreco('14.50'), "Deve aceitar '14.50' com ponto decimal direto");
assertEqual(25.90, normalizarPreco('R$ 25,90'), "Deve extrair valor numérico de strings formatadas com 'R$'");
assertEqual(0.0, normalizarPreco(''), "Deve retornar 0.0 para string vazia");
assertEqual(0.0, normalizarPreco('invalido'), "Deve retornar 0.0 para texto sem números");

assertEqual('R$ 6,50', formatarMoeda(6.5), "Deve formatar 6.5 como 'R$ 6,50'");
assertEqual('R$ 14,00', formatarMoeda(14.0), "Deve formatar 14.0 como 'R$ 14,00'");
assertEqual('R$ 1.250,90', formatarMoeda(1250.90), "Deve formatar valores na casa dos milhares com ponto e vírgula");

// =========================================================================
// 3. TESTES DE VALIDAÇÃO DE ITENS DO CARDÁPIO (REGRAS DE NEGÓCIO)
// =========================================================================
echo "\n" . corAmarelo("▶ 3. Testes de Validação de Itens do Cardápio (Create / Update)\n");

// 3.1 Item válido completo
$resValido = validarItemCardapio('Café Espresso', 'Café', '6,50');
assertTrue($resValido['valido'], "Item com nome, categoria válida e preço deve ser aprovado");
assertEqual(null, $resValido['erro'], "Item válido não deve conter mensagem de erro");

// 3.2 Nome vazio ou curto
$resNomeVazio = validarItemCardapio('', 'Café', '6,50');
assertFalse($resNomeVazio['valido'], "Item com nome vazio deve ser reprovado");

$resNomeCurto = validarItemCardapio('A', 'Café', '6,50');
assertFalse($resNomeCurto['valido'], "Item com nome menor que 2 caracteres deve ser reprovado");

// 3.3 Categoria inválida
$resCatInvalida = validarItemCardapio('Coxinha', 'CategoriaInexistente', '8,00');
assertFalse($resCatInvalida['valido'], "Item com categoria não permitida deve ser reprovado");

// 3.4 Preço zero ou negativo
$resPrecoZero = validarItemCardapio('Água Mineral', 'Bebida Gelada', '0,00');
assertFalse($resPrecoZero['valido'], "Item com preço zero deve ser reprovado");

$resPrecoNegativo = validarItemCardapio('Água Mineral', 'Bebida Gelada', '-5.00');
assertFalse($resPrecoNegativo['valido'], "Item com preço negativo deve ser reprovado");

// =========================================================================
// 4. TESTES DE VALIDAÇÃO DE ID (PROTEÇÃO CONTRA SQL INJECTION E INVALIDEZ)
// =========================================================================
echo "\n" . corAmarelo("▶ 4. Testes de Sanitização de IDs (Editar / Excluir)\n");

assertEqual(1, validarId('1'), "ID '1' em formato string numérica deve retornar inteiro 1");
assertEqual(42, validarId(42), "ID 42 inteiro deve retornar inteiro 42");
assertEqual(999, validarId('999'), "ID '999' deve retornar inteiro 999");

assertFalse(validarId('0'), "ID '0' deve ser rejeitado");
assertFalse(validarId('-5'), "ID negativo '-5' deve ser rejeitado");
assertFalse(validarId('abc'), "ID alfanumérico 'abc' deve ser rejeitado");
assertFalse(validarId(null), "ID nulo deve ser rejeitado");

// Tentativas de SQL Injection no parâmetro de URL
assertFalse(validarId("1 OR 1=1"), "Tentativa de SQL Injection '1 OR 1=1' deve ser rejeitada");
assertFalse(validarId("1; DROP TABLE usuarios;--"), "Tentativa de injeção '1; DROP TABLE...' deve ser rejeitada");
assertFalse(validarId("' UNION SELECT 1,2,3--"), "Tentativa de injeção com UNION deve ser rejeitada");

// =========================================================================
// 5. TESTES DE CLASSES CSS / BADGES VISUAIS
// =========================================================================
echo "\n" . corAmarelo("▶ 5. Testes de Badges de Categorias\n");

assertEqual('badge-cafe', obterClasseBadge('Café'), "Categoria 'Café' deve retornar 'badge-cafe'");
assertEqual('badge-cafe', obterClasseBadge('cafe'), "Categoria 'cafe' em minúsculas deve retornar 'badge-cafe'");
assertEqual('badge-doce', obterClasseBadge('Doce'), "Categoria 'Doce' deve retornar 'badge-doce'");
assertEqual('badge-salgado', obterClasseBadge('Salgado'), "Categoria 'Salgado' deve retornar 'badge-salgado'");
assertEqual('badge-bebida', obterClasseBadge('Bebida Gelada'), "Categoria 'Bebida Gelada' deve retornar 'badge-bebida'");
assertEqual('badge-padrao', obterClasseBadge('Outros'), "Categoria desconhecida deve retornar 'badge-padrao'");

// =========================================================================
// 6. TESTES DE FLUXO DE CRUD COM PREPARED STATEMENTS (BANCO EM MEMÓRIA)
// =========================================================================
echo "\n" . corAmarelo("▶ 6. Testes de Operações de CRUD com Prepared Statements\n");

// Usamos SQLite em memória para testar exatamente os Prepared Statements sem depender de conexão de rede
$dbMemoria = new SQLite3(':memory:');

// Cria tabela idêntica à do projeto
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

// 6.1 CREATE (Prepared Statement)
$stmtInsert = $dbMemoria->prepare("INSERT INTO cardapio (nome_item, categoria, descricao, preco) VALUES (:nome, :cat, :desc, :preco)");
$stmtInsert->bindValue(':nome', 'Torta Holandesa', SQLITE3_TEXT);
$stmtInsert->bindValue(':cat', 'Doce', SQLITE3_TEXT);
$stmtInsert->bindValue(':desc', 'Torta com creme especial e cobertura de ganache.', SQLITE3_TEXT);
$stmtInsert->bindValue(':preco', 16.50, SQLITE3_FLOAT);
$execInsert = $stmtInsert->execute();
assertTrue($execInsert !== false, "CREATE: Prepared Statement INSERT deve executar com sucesso");

$idCriado = $dbMemoria->lastInsertRowID();
assertEqual(1, $idCriado, "CREATE: ID gerado deve ser 1");

// 6.2 READ (Prepared Statement)
$stmtSelect = $dbMemoria->prepare("SELECT * FROM cardapio WHERE id = :id");
$stmtSelect->bindValue(':id', $idCriado, SQLITE3_INTEGER);
$resSelect = $stmtSelect->execute();
$itemLido = $resSelect->fetchArray(SQLITE3_ASSOC);

assertEqual('Torta Holandesa', $itemLido['nome_item'], "READ: Nome do item lido deve corresponder ao cadastrado");
assertEqual(16.50, (float)$itemLido['preco'], "READ: Preço do item lido deve corresponder ao cadastrado");

// 6.3 UPDATE (Prepared Statement)
$stmtUpdate = $dbMemoria->prepare("UPDATE cardapio SET nome_item = :nome, preco = :preco WHERE id = :id");
$stmtUpdate->bindValue(':nome', 'Torta Holandesa Especial', SQLITE3_TEXT);
$stmtUpdate->bindValue(':preco', 18.00, SQLITE3_FLOAT);
$stmtUpdate->bindValue(':id', $idCriado, SQLITE3_INTEGER);
$execUpdate = $stmtUpdate->execute();
assertTrue($execUpdate !== false, "UPDATE: Prepared Statement UPDATE deve executar com sucesso");

// Verifica se o item foi realmente atualizado sem duplicar
$stmtCheckUpdate = $dbMemoria->prepare("SELECT COUNT(*) as total, nome_item, preco FROM cardapio WHERE id = :id");
$stmtCheckUpdate->bindValue(':id', $idCriado, SQLITE3_INTEGER);
$itemAtualizado = $stmtCheckUpdate->execute()->fetchArray(SQLITE3_ASSOC);

assertEqual(1, $itemAtualizado['total'], "UPDATE: Não deve criar linhas duplicadas no banco");
assertEqual('Torta Holandesa Especial', $itemAtualizado['nome_item'], "UPDATE: Nome deve ter sido alterado");
assertEqual(18.00, (float)$itemAtualizado['preco'], "UPDATE: Preço deve ter sido atualizado");

// 6.4 IMUNIDADE A SQL INJECTION
$stringMaliciosa = "' OR '1'='1";
$stmtInjectTest = $dbMemoria->prepare("INSERT INTO cardapio (nome_item, categoria, descricao, preco) VALUES (:nome, 'Café', 'Teste', 5.0)");
$stmtInjectTest->bindValue(':nome', $stringMaliciosa, SQLITE3_TEXT);
$stmtInjectTest->execute();

$stmtCheckInject = $dbMemoria->prepare("SELECT nome_item FROM cardapio WHERE nome_item = :nome");
$stmtCheckInject->bindValue(':nome', $stringMaliciosa, SQLITE3_TEXT);
$itemInjetado = $stmtCheckInject->execute()->fetchArray(SQLITE3_ASSOC);
assertEqual($stringMaliciosa, $itemInjetado['nome_item'], "SEGURANÇA: String com injeção SQL é tratada como texto puro e não quebra a consulta");

// 6.5 DELETE (Prepared Statement)
$stmtDelete = $dbMemoria->prepare("DELETE FROM cardapio WHERE id = :id");
$stmtDelete->bindValue(':id', $idCriado, SQLITE3_INTEGER);
$execDelete = $stmtDelete->execute();
assertTrue($execDelete !== false, "DELETE: Prepared Statement DELETE deve executar com sucesso");

// Confirma que o item foi removido
$stmtCheckDelete = $dbMemoria->prepare("SELECT * FROM cardapio WHERE id = :id");
$stmtCheckDelete->bindValue(':id', $idCriado, SQLITE3_INTEGER);
$itemExcluido = $stmtCheckDelete->execute()->fetchArray(SQLITE3_ASSOC);
assertFalse($itemExcluido, "DELETE: Item deve ter sido completamente removido da tabela");

$dbMemoria->close();

// =========================================================================
// RELATÓRIO FINAL
// =========================================================================
$tempoTotal = round((microtime(true) - $inicioTempo) * 1000, 2);

echo "\n" . corAzul("============================================================\n");
echo "  RESULTADO DOS TESTES UNITÁRIOS:\n";
echo "  Total de Testes: {$totalTestes}\n";
echo "  " . corVerde("Sucesso: {$testesPassaram}") . "\n";

if ($testesFalharam > 0) {
    echo "  " . corVermelho("Falhas: {$testesFalharam}") . "\n";
} else {
    echo "  " . corVerde("Todas as asserções passaram com 100% de sucesso! 🚀") . "\n";
}

echo "  Tempo de execução: {$tempoTotal} ms\n";
echo corAzul("============================================================\n\n");

// Retorna código 0 para sucesso ou 1 para falha
exit($testesFalharam > 0 ? 1 : 0);
?>
