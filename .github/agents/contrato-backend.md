# Contrato backend/frontend: listagem de usuários

- Rota `GET /usuarios`, nome `usuarios.index`, pública e destinada somente a testes com dados fictícios; a aplicação não deve ser implantada em produção com esse acesso.
- A rota renderiza `usuarios.index`, que deve estender `layouts.main-layout`.
- A view recebe `$usuarios`, uma paginação de 15 usuários ativos, ordenados por data de cadastro decrescente e ID decrescente, com endereços ativos carregados antecipadamente.
- Cada item expõe `id`, `nome`, `email`, `cpf`, `telefone`, `created_at` e seus endereços (`rua`, `numero`, `complemento`, `bairro`, `cidade`, `uf`, `cep`); não exibir senha ou token.

## Listagem de jogos

- Rota `GET /jogos`, nome `jogos.index`, pública e destinada somente a testes; a aplicação não deve ser implantada em produção com esse acesso.
- A rota renderiza `jogos.index`, que deve estender `layouts.main-layout`.
- A view recebe `$jogos`, uma paginação de 15 jogos ativos, ordenados por data de cadastro decrescente e ID decrescente, com categorias e promoções vigentes carregadas antecipadamente.
- A view renderiza os cards existentes em `x-game-card`, com os campos usados pelo componente: nome, imagem, categorias, preço e dados da promoção vigente.
