<?php
/**
 * funcoes.php
 * Funcoes auxiliares e validacoes de regras de negocio.
 */

/**
 * Valida a senha informada.
 * Suporta hash (password_verify) e comparacao de texto simples para compatibilidade.
 */
function verificarSenha(string $senhaDigitada, string $senhaArmazenada): bool {
    if (empty($senhaDigitada) || empty($senhaArmazenada)) {
        return false;
    }
    if (password_verify($senhaDigitada, $senhaArmazenada)) {
        return true;
    }
    return hash_equals($senhaArmazenada, $senhaDigitada);
}

/**
 * Normaliza o valor de preco para float.
 */
function normalizarPreco($preco): float {
    if (is_numeric($preco)) {
        return (float) $preco;
    }
    if (is_string($preco)) {
        $limpo = trim($preco);
        $limpo = str_replace(',', '.', $limpo);
        $limpo = preg_replace('/[^0-9.]/', '', $limpo);
        return is_numeric($limpo) ? (float) $limpo : 0.0;
    }
    return 0.0;
}

/**
 * Valida os dados de um item do cardapio.
 */
function validarItemCardapio(string $nome, string $categoria, $preco): array {
    $nomeLimpo = trim($nome);
    $catLimpa = trim($categoria);
    $precoNum = normalizarPreco($preco);

    if (empty($nomeLimpo)) {
        return ['valido' => false, 'erro' => 'O nome do item e obrigatorio.'];
    }

    if (mb_strlen($nomeLimpo) < 2) {
        return ['valido' => false, 'erro' => 'O nome deve ter pelo menos 2 caracteres.'];
    }

    $categoriasPermitidas = ['Cafe', 'Café', 'Doce', 'Salgado', 'Bebida Gelada'];
    $catEncontrada = false;
    foreach ($categoriasPermitidas as $permitida) {
        if (strcasecmp($catLimpa, $permitida) === 0) {
            $catEncontrada = true;
            break;
        }
    }

    if (!$catEncontrada) {
        return ['valido' => false, 'erro' => 'Selecione uma categoria valida.'];
    }

    if ($precoNum <= 0.0) {
        return ['valido' => false, 'erro' => 'O preco deve ser maior que zero.'];
    }

    return ['valido' => true, 'erro' => null];
}

/**
 * Valida se um ID e um inteiro positivo.
 */
function validarId($id) {
    if (!is_numeric($id)) {
        return false;
    }
    $idInt = (int) $id;
    return ($idInt > 0 && (string)$idInt === trim((string)$id)) ? $idInt : false;
}

/**
 * Retorna classe CSS para a categoria.
 */
function obterClasseBadge(string $categoria): string {
    switch (mb_strtolower(trim($categoria), 'UTF-8')) {
        case 'café':
        case 'cafe':
            return 'badge-cafe';
        case 'doce':
            return 'badge-doce';
        case 'salgado':
            return 'badge-salgado';
        case 'bebida gelada':
        case 'bebida':
            return 'badge-bebida';
        default:
            return 'badge-padrao';
    }
}

/**
 * Formata valor monetario (R$ 0,00).
 */
function formatarMoeda(float $valor): string {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}
?>
