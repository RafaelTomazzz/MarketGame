---
name: marketgame-dados
description: Regras do agente de dados do MarketGame (Laravel + MySQL). Use ao criar seeders, factories e scripts SQL para popular o banco com grandes volumes de dados fictícios, porém realistas.
---

# Skill: Dados fictícios do MarketGame

Antes de qualquer análise, decisão, alteração ou outra ação, consulte primeiro o arquivo de contexto do projeto: `D:\MarketGame\.github\agents\contexto.md` (caminho relativo ao repositório: `.github/agents/contexto.md`). Leia-o integralmente e siga suas regras. Este arquivo define o contexto geral do MarketGame; as instruções deste agente complementam esse contexto para geração de dados.

## Seu objetivo

Gerar **grandes volumes** de dados fictícios e coerentes (jogos, categorias, promoções, usuários, endereços, métodos de pagamento, carrinhos e pedidos) e injetá-los no banco para testes e demonstração.

Os dados de jogos devem partir de **fontes reais** (datasets públicos). Os dados de pessoas são sempre **fictícios**.

## Arquivos que você pode alterar

Você pode ler qualquer arquivo do projeto para compreender o esquema, as regras de negócio e as dependências. A lista abaixo define seu escopo padrão de escrita; não altere arquivos fora dela sem autorização explícita do usuário. Se uma mudança fora do escopo for necessária e ainda não tiver autorização, informe os caminhos exatos e o motivo, peça autorização e aguarde uma resposta afirmativa antes de editar. Um pedido direto que identifique o caminho e a mudança pode ser tratado como autorização somente para esses itens; um pedido genérico não é autorização. Peça nova autorização se o escopo mudar.

| Tipo | Pasta |
|---|---|
| Seeders | `database/seeders/` |
| Factories | `database/factories/` |
| Scripts SQL | `database/sql/` |
| Datasets brutos (CSV/JSON) | `database/data/` |
| Migrations | `database/migrations/` (só com cuidado, veja abaixo) |

## Arquivos fora do escopo padrão
- `app/`, `routes/` e `resources/`: pertencem ao backend e ao frontend.

Não altere esses arquivos sem a autorização explícita descrita acima. Se a mudança depender deles, explique a necessidade e peça autorização antes de editar.

**Migrations** pertencem ao backend. Embora estejam no escopo listado acima, só as altere quando forem indispensáveis para os dados (ex.: coluna faltando) e registre a mudança no arquivo de contrato com o backend. Para qualquer outro arquivo fora do escopo listado, peça autorização antes de editar.

## Antes de começar
1. Leia as migrations e os models atuais. Gere dados **apenas** com as colunas que existem.
2. Rode os seeders depois que o backend tiver fechado o esquema. Se o esquema mudar, ajuste seeders e factories.

## Fontes de dados reais (jogos)
- Use datasets públicos de jogos (ex.: datasets no Kaggle, APIs públicas como RAWG ou IGDB, dados de lojas). **Verifique a licença** antes de usar e não use dados privados.
- Aproveite título, gênero/categoria, plataforma, ano de lançamento e preço de referência.
- Salve o arquivo bruto em `database/data/` e leia-o no seeder. Evite chamadas de rede durante o seed.
- O MarketGame vende **mídia física**: ajuste plataforma e preço para isso, em **reais (R$)**, com valores plausíveis (ex.: R$ 49,90 a R$ 349,90).
- Imagens: use URLs de placeholder ou imagens próprias. Não baixe nem redistribua capas protegidas por direitos autorais.

## Qualidade dos dados de pessoas (fictícios, mas com cara de reais)

- **Nomes:** Faker `pt_BR` (`fake('pt_BR')`), com nomes e sobrenomes brasileiros variados.
- **E-mails:** únicos e derivados do nome (ex.: `ana.souza84@example.com`). Use domínios reservados ou fictícios (`example.com`, `example.org`) para nunca coincidir com pessoas reais.
- **CPF:** deve ser **válido pelos dígitos verificadores**, mas fictício.
  - Use `fake('pt_BR')->cpf()` ou implemente o cálculo dos dois dígitos.
  - Rejeite sequências repetidas (`111.111.111-11`, etc.).
  - Garanta unicidade e use o formato que a migration espera (com ou sem máscara).
- **Telefone:** número brasileiro com DDD válido; celulares com 9 dígitos começando em 9.
- **Endereço:** rua, número, bairro, cidade, UF e CEP coerentes entre si (cidade e UF combinando). Prefira Faker `pt_BR` ou uma lista pequena de cidades reais.
- **Senha:** hash de uma **senha de teste conhecida** (ex.: `password`). Crie também um usuário fixo de demonstração e documente o login dele no seeder.
- **Métodos de pagamento:** nunca gere número completo de cartão real. Use apenas bandeira e últimos 4 dígitos, ou o formato que o esquema do backend definir. Inclua Pix.

## Qualidade dos dados de negócio

- **Jogos:** título único, 1 a 3 categorias cada (todo jogo tem ao menos uma), preço, estoque e ano coerentes.
- **Promoções:**
  - Tipos `percentual` (ex.: 5% a 70%) e `valor fixo` (nunca maior que o preço do jogo).
  - Toda promoção pertence a um **grupo promocional** (ex.: "Black Friday", "Semana do Consumidor", "Liquidação de Verão").
  - Datas de início e fim válidas. Deixe a maior parte dos jogos **sem promoção** (cerca de 20% a 30% com promoção).
- **Carrinhos:** 1 a 6 itens, com quantidade de 1 a 3 por jogo e sem jogos duplicados no mesmo carrinho.
- **Pedidos:**
  - Total = soma de (preço com promoção × quantidade) + frete fixo de **R$ 5,00**.
  - Status variados (pendente, aprovado, cancelado), com maioria aprovada.
  - Datas de criação distribuídas ao longo do tempo.
  - Use um método de pagamento que pertença ao próprio usuário do pedido.

## Como injetar

1. **Respeite as chaves estrangeiras:** categorias → grupos promocionais → promoções → jogos → usuários → endereços → métodos de pagamento → carrinhos → pedidos.
2. **Tabelas com dados sensíveis** (usuários, endereços, pagamentos): grave pelos **models/factories do Eloquent**, para que os casts do backend (`hashed`, `encrypted`) sejam aplicados. Nunca insira esses dados por SQL puro, pois ficariam em texto aberto.
3. **Tabelas sem dados sensíveis** (jogos, categorias, promoções, tabelas pivô): use inserção em lotes (`DB::table()->insert()` em chunks de 500 a 1000) para ganhar desempenho.
4. Envolva o seed em transação quando fizer sentido e use `fake()->seed(1234)` para resultados **reproduzíveis**.
5. O seed deve funcionar com `php artisan migrate:fresh --seed`. Registre os seeders em `DatabaseSeeder` na ordem correta.
6. Scripts SQL em `database/sql/` só para carga de dados não sensíveis. Comente a ordem de execução no topo do arquivo.

## Volume sugerido (ajuste conforme o tempo de seed)
- Categorias: 15 a 25 · Jogos: 500 a 2.000 · Grupos promocionais: 4 a 8 · Promoções: 100 a 400
- Usuários: 500 a 5.000 · Pedidos: 2.000 a 20.000

## Checklist antes de terminar
- [ ] Os CPFs passam na validação dos dígitos verificadores e são únicos?
- [ ] Nenhum dado pessoal ou cartão real foi usado?
- [ ] Usuários e dados sensíveis foram gravados pelo Eloquent (hash e criptografia aplicados)?
- [ ] O total de cada pedido bate com a regra (itens com promoção + R$ 5,00)?
- [ ] Todo jogo tem ao menos uma categoria e nenhuma promoção passa do preço do jogo?
- [ ] `php artisan migrate:fresh --seed` roda sem erros e de forma reproduzível?
- [ ] A licença do dataset foi conferida?