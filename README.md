# ☕ Sistema de Gestão do Cardápio - Cafeteria Aroma & Sabor

Sistema completo de CRUD (Create, Read, Update, Delete) com autenticação de usuário desenvolvido em **PHP procedural** e **MySQLi**, utilizando **Prepared Statements** (`mysqli_prepare`) para proteção total contra SQL Injection.

Projeto desenvolvido para a atividade prática de gestão de cardápio de cafeteria.

---

## 📋 Funcionalidades Implementadas

- 🔐 **Autenticação e Controle de Sessão (`login.php`, `auth.php`, `logout.php`)**:
  - Acesso ao painel restrito a usuários autenticados.
  - Bloqueio de acessos diretos não autorizados com redirecionamento para o login.
  - Compatibilidade com senhas em texto puro (`admin123`) e hashes seguros (`password_verify`).
- ➕ **C - Create (`painel.php`)**:
  - Formulário para cadastrar novos itens (Nome, Categoria via `<select>`, Preço e Descrição).
  - Inserção segura no banco utilizando `mysqli_prepare` e `mysqli_stmt_bind_param`.
- 📋 **R - Read (`painel.php`)**:
  - Listagem dos itens cadastrados em tabela organizada com badges temáticas por categoria.
  - Formatação monetária brasileira (`R$ 0,00`).
- ✏️ **U - Update (`editar_item.php`)**:
  - Formulário pré-carregado com os dados do item selecionado.
  - Atualização dos dados sem duplicação de registros via Prepared Statements.
- 🗑️ **D - Delete (`excluir_item.php`)**:
  - Exclusão segura do item no banco com parâmetro de ID via URL (`?id=X`).
  - Confirmação visual em JavaScript para evitar exclusões acidentais.
- 🎨 **Interface Visual Temática**:
  - Design aconchegante com paleta de cores temática de cafeteria (tons de café espresso, creme e linho).
  - Totalmente responsivo para celulares e desktops.

---

## 🛠️ Tecnologias Utilizadas

- **PHP 8+** (paradigma procedural com extensão `mysqli`)
- **MySQL / MariaDB**
- **HTML5 & CSS3 Moderno**
- **Git** para controle de versão

---

## 🚀 Como Executar o Projeto

### 1. Configurar o Banco de Dados no phpMyAdmin
1. Abra o painel do seu **XAMPP** (ou Laragon/Wamp) e inicie os módulos **Apache** e **MySQL**.
2. Acesse o **phpMyAdmin** no seu navegador (`http://localhost/phpmyadmin`).
3. Vá na aba **Importar** (ou **SQL**).
4. Selecione ou copie e cole o conteúdo do arquivo [`banco.sql`](banco.sql) deste repositório e execute.
   - O script criará o banco `gestao_cafeteria` e as tabelas `usuarios` e `cardapio`, além do usuário administrador inicial.

### 2. Acessar o Sistema
- Se estiver utilizando a pasta `htdocs` do XAMPP:
  - Acesse no navegador: `http://localhost/ativividade/login.php` (ou o nome da pasta em que o projeto estiver).
- Se preferir executar via servidor embutido do PHP:
  ```bash
  php -S localhost:8000
  ```
  E acesse: `http://localhost:8000/login.php`

---

## 🔑 Credenciais Padrão de Acesso

- **Usuário:** `admin`
- **Senha:** `admin123`

---

## 🛡️ Critérios de Avaliação Atendidos

| Critério | Situação | Detalhe |
| :--- | :---: | :--- |
| **Bloqueio de acesso sem login?** | ✅ | Implementado via [`auth.php`](auth.php) incluído no topo de todas as páginas restritas. |
| **Cadastro e visualização na tabela?** | ✅ | Inserção no [`painel.php`](painel.php) com recarregamento limpo e listagem dinâmica. |
| **Edição correta sem duplicar item?** | ✅ | Executado no [`editar_item.php`](editar_item.php) através de `UPDATE cardapio ... WHERE id = ?`. |
| **Exclusão de item do banco?** | ✅ | Executado no [`excluir_item.php`](excluir_item.php) via `DELETE FROM cardapio WHERE id = ?`. |
| **Proteção contra SQL Injection?** | ✅ | 100% das consultas com entrada de dados usam `mysqli_prepare` e `mysqli_stmt_bind_param`. |

---

## 📦 Como Subir para o seu GitHub (Para Enviar na Atividade)

1. Crie um novo repositório no seu GitHub (ex: `gestao-cafeteria-php`).
2. No terminal desta pasta, adicione o repositório remoto e faça o push:
   ```bash
   git branch -M main
   git remote add origin https://github.com/SEU_USUARIO/SEU_REPOSITORIO.git
   git push -u origin main
   ```
3. Copie o link do seu repositório no GitHub e envie na atividade!
