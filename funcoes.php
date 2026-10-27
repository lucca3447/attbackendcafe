<?php
/**
 * funcoes.php
 * Funções auxiliares e de regras de negócio para a gestão da cafeteria.
 * Compartilhadas entre as páginas web e a suíte de testes unitários.
 */

/**
 * Valida a senha digitada contra a senha armazenada no banco.
 * Suporta hash seguro (password_verify) e compatibilidade com texto puro do script escolar.
 *
 * @param string $senhaDigitada Senha fornecida no login
 * @param string $senhaArmazenada Senha registrada no banco
 * @return bool Retorna true se a senha for válida
 */
function verificarSenha(string $senhaDigitada, string $senhaArmazenada): bool {
    if (empty($senhaDigitada) || empty($senhaArmazenada)) {
        return false;
    }
    // Verifica hash seguro
    if (password_verify($senhaDigitada, $senhaArmazenada)) {
        return true;
    }
    // Fallback: comparação de texto puro (para o script padrão da atividade 'admin123')
    return hash_equals($senhaArmazenada, $senhaDigitada);
}

/**
 * Normaliza uma string ou valor numérico de preço para float.
 * Aceita formatos com vírgula ("14,50") ou ponto ("14.50").
 *
 * @param mixed $preco Valor informado
 * @return float Preço normalizado em ponto flutuante
 */
function normalizarPreco($preco): float {
    if (is_numeric($preco)) {
        return (float) $preco;
    }
    if (is_string($preco)) {
        $limpo = trim($preco);
        // Substitui vírgula por ponto
        $limpo = str_replace(',', '.', $limpo);
        // Remove caracteres não numéricos exceto o ponto
        $limpo = preg_replace('/[^0-9.]/', '', $limpo);
        return is_numeric($limpo) ? (float) $limpo : 0.0;
    }
    return 0.0;
}

/**
 * Valida os dados de um item do cardápio para cadastro ou edição.
 *
 * @param string $nome Nome do produto
 * @param string $categoria Categoria selecionada
 * @param mixed $preco Preço do item
 * @return array ['valido' => bool, 'erro' => string|null]
 */
function validarItemCardapio(string $nome, string $categoria, $preco): array {
    $nomeLimpo = trim($nome);
    $catLimpa = trim($categoria);
    $precoNum = normalizarPreco($preco);

    if (empty($nomeLimpo)) {
        return ['valido' => false, 'erro' => 'O nome do item é obrigatório.'];
    }

    if (mb_strlen($nomeLimpo) < 2) {
        return ['valido' => false, 'erro' => 'O nome do item deve ter pelo menos 2 caracteres.'];
    }

    $categoriasPermitidas = ['Café', 'Doce', 'Salgado', 'Bebida Gelada', 'Especial de Inverno', 'Especial'];
    if (empty($catLimpa) || !in_array($catLimpa, $categoriasPermitidas, true)) {
        return ['valido' => false, 'erro' => 'Selecione uma categoria válida para o item.'];
    }

    if ($precoNum <= 0.0) {
        return ['valido' => false, 'erro' => 'O preço deve ser um valor positivo maior que zero.'];
    }

    return ['valido' => true, 'erro' => null];
}

/**
 * Valida se um identificador recebido via GET/POST é um ID numérico inteiro positivo.
 *
 * @param mixed $id Identificador a ser validado
 * @return int|false Retorna o ID inteiro positivo ou false se inválido
 */
function validarId($id) {
    if (!is_numeric($id)) {
        return false;
    }
    $idInt = (int) $id;
    return ($idInt > 0 && (string)$idInt === trim((string)$id)) ? $idInt : false;
}

/**
 * Retorna a classe CSS correspondente para a tag/badge da categoria.
 *
 * @param string $categoria Nome da categoria
 * @return string Nome da classe CSS correspondente
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
 * Formata um valor numérico para o padrão de moeda brasileira (R$ 0,00).
 *
 * @param float $valor Valor em reais
 * @return string Valor formatado
 */
function formatarMoeda(float $valor): string {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}
?>
