# Sistema de Gestao de Cardapio

CRUD completo com autenticacao de usuario desenvolvido em PHP procedural e MySQLi, utilizando Prepared Statements para prevencao contra SQL Injection.

## Funcionalidades

- **Autenticacao e Sessao (`login.php`, `auth.php`, `logout.php`)**:
  - Restricao de acesso a paginas administrativas.
  - Redirecionamento de acessos nao autenticados para o login.
  - Compatibilidade com senhas em texto simples e hashes via `password_verify`.
- **Cadastro de Itens (`painel.php`)**:
  - Formulario com Nome, Categoria, Preco e Descricao.
  - Insercao no banco com `mysqli_prepare` e parametros vinculados.
- **Listagem de Itens (`painel.php`)**:
  - Tabela com listagem dos itens cadastrados.
  - Formatacao de preco em reais (`R$ 0,00`).
- **Edicao de Itens (`editar_item.php`)**:
  - Formulario pre-preenchido com os dados atuais do item selecionado.
  - Atualizacao no banco via `UPDATE cardapio SET ... WHERE id = ?`.
- **Exclusao de Itens (`excluir_item.php`)**:
  - Remocao do registro pelo ID recebido via URL (`?id=X`).
  - Confirmacao previa no navegador.

## Tecnologias

- PHP 8+ (procedural com extensao `mysqli`)
- MySQL / MariaDB
- HTML5 e CSS3
- Git

## Testes Unitarios

O projeto conta com uma suite de testes unitarios em [`testes_unitarios.php`](testes_unitarios.php), que valida regras de negocio, sanitizacao de parametros e operacoes de banco (utilizando SQLite em memoria):

```bash
php testes_unitarios.php
```

Cobertura dos testes:
1. Autenticacao e verificacao de senhas.
2. Normalizacao de precos e formatacao de moeda.
3. Validacao de campos obrigatorios de itens.
4. Sanitizacao de IDs e protecao contra SQL Injection.
5. Classes CSS de identificacao por categoria.
6. Operacoes de Create, Read, Update e Delete com Prepared Statements.

## Como Executar

### 1. Banco de Dados no phpMyAdmin
1. Inicie os servicos Apache e MySQL no XAMPP.
2. Acesse `http://localhost/phpmyadmin`.
3. Abra a aba **SQL**, cole o conteudo de [`banco.sql`](banco.sql) e clique em **Executar**.

### 2. Acesso a Aplicacao
- Coloque a pasta do projeto dentro de `htdocs` e acesse no navegador: `http://localhost/ativividade/login.php`
- Ou execute pelo servidor embutido do PHP:
  ```bash
  php -S localhost:8000
  ```
  E acesse: `http://localhost:8000/login.php`

### Credenciais Padrao
- **Usuario:** `admin`
- **Senha:** `admin123`

## Criterios Atendidos

| Criterio | Situacao | Detalhes |
| :--- | :---: | :--- |
| Bloqueio de acesso sem login | Atendido | Validado em `auth.php` em todas as rotas restritas. |
| Cadastro e listagem de itens | Atendido | Operacoes implementadas em `painel.php`. |
| Edicao sem duplicar registros | Atendido | `UPDATE` especifico por ID em `editar_item.php`. |
| Exclusao de registros | Atendido | `DELETE` especifico por ID em `excluir_item.php`. |
| Protecao contra SQL Injection | Atendido | Uso consistente de `mysqli_prepare` em todas as consultas com input. |

## Envio no Git

Para enviar ao repositorio remoto:

```bash
git branch -M main
git remote add origin https://github.com/SEU_USUARIO/SEU_REPOSITORIO.git
git push -u origin main
```
