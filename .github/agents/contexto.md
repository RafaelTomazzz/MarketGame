# 🎮 MarketGame

> **MarketGame** é um marketplace de jogos em mídia física, desenvolvido como projeto acadêmico.

## Contexto

Aplicação web de um marketplace de jogos em **mídia física**, desenvolvida para um trabalho da faculdade e inspirada em plataformas como Steam e Eneba.

**Stack:** Laravel, Bootstrap 5 e MySQL.

**Objetivos principais:**
1. Proteger os dados sensíveis dos usuários.
2. Ter boa cobertura de testes unitários.

**Diretriz de escopo:** o sistema deve ser simples. Evite soluções robustas ou complexas demais (filas, microsserviços, integrações externas reais, painel administrativo avançado). Prefira o que for fácil de testar.

---

## Regras de negócio

### Usuário e autenticação
- O cadastro coleta os dados sensíveis definidos na migration, mais o endereço.
- Dados sensíveis devem ser armazenados com segurança (criptografia dos campos sensíveis, ex.: CPF; senha com hash via `Hash::make`).
- O login é feito com e-mail e senha.
- Rotas de carrinho, pedido e métodos de pagamento exigem usuário autenticado.

### Jogos, categorias e promoções
- Todo jogo pertence a **pelo menos uma** categoria (relação N:N).
- Um jogo pode ter ou não uma promoção.
- A promoção pode ser de dois tipos: **percentual** ou **valor fixo**.
- Toda promoção pertence a um **grupo promocional**.
- Regras de validade (confirmar): a promoção tem data de início e fim, o desconto não pode tornar o preço negativo (mínimo R$ 0,00) e o percentual fica entre 0 e 100.

### Catálogo
- A listagem exibe, para cada jogo: título, imagem, preço, promoção (se houver) e categorias.
- Ao clicar em um jogo, abre-se a página de detalhes.

### Carrinho
- O usuário pode adicionar um ou mais jogos e **várias unidades do mesmo jogo**.
- Adicionar um jogo que já está no carrinho incrementa a quantidade (não duplica o item).
- O usuário pode remover itens.
- O ícone do carrinho leva a uma tela que lista os itens e mostra o **subtotal**.
- Regras de estoque (confirmar): não permitir quantidade acima do estoque disponível nem quantidade menor que 1.

### Métodos de pagamento
- O usuário pode adicionar, editar e remover seus métodos de pagamento.
- Dados de pagamento sensíveis (ex.: número de cartão) não devem ser guardados em texto puro. Armazene apenas o necessário (ex.: últimos 4 dígitos e bandeira) ou criptografe.
- Um usuário só acessa e altera os próprios métodos.

### Pedido
- Na tela do carrinho, o usuário finaliza o pedido escolhendo o método de pagamento.
- **Cálculo do total:**

  `total = soma(preço_com_promoção × quantidade) + frete`

  - O frete é fixo: **R$ 5,00**.
  - O preço de cada jogo já considera a promoção aplicável.
- O pedido registra os preços e descontos vigentes na hora da compra, para que mudanças futuras não alterem pedidos antigos.
- Status do pedido: `pendente`, `aprovado`, `cancelado` (confirmar).
- Após o pedido ser criado, o carrinho é esvaziado.

---

## Testes unitários (prioridade)

Foque a cobertura nas regras de negócio:
- Cálculo de desconto (percentual e valor fixo), incluindo limites (desconto maior que o preço, 0% e 100%).
- Cálculo do subtotal e do total do pedido (com frete de R$ 5,00).
- Adicionar, incrementar e remover itens do carrinho.
- Validação do cadastro (campos obrigatórios, e-mail único, CPF válido).
- Armazenamento seguro: senha com hash e campos sensíveis criptografados.
- Autorização: um usuário não acessa dados de outro.
- Regra de que o jogo precisa de ao menos uma categoria.

---

## Convenções de implementação
- Use Eloquent com relacionamentos explícitos, `FormRequest` para validação e `Policy` para autorização.
- Mantenha as regras de cálculo em classes de serviço ou nos models, para serem testadas sem depender de controllers ou views.
- Use factories e seeders para dados de teste.
- Valores monetários: use `decimal(10,2)` no banco.
- Interface: Bootstrap 5, simples e responsiva.