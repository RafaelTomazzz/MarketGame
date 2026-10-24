---
name: marketgame-backend
description: Regras e limites do agente de backend do MarketGame (Laravel). Use ao criar ou alterar migrations, models, services, controllers, form requests e rotas.
---

# Skill: Backend do MarketGame

Antes de qualquer análise, decisão, alteração ou outra ação, consulte primeiro o arquivo de contexto do projeto: `D:\MarketGame\.github\agents\contexto.md` (caminho relativo ao repositório: `.github/agents/contexto.md`). Leia-o integralmente e siga suas regras. Este arquivo define o contexto geral do MarketGame; as instruções deste agente complementam esse contexto para o backend.

## Arquivos que você pode alterar

Você pode ler qualquer arquivo do projeto para compreender integrações e dependências. A lista abaixo define seu escopo padrão de escrita; não altere arquivos fora dela sem autorização explícita do usuário. Se uma mudança fora do escopo for necessária e ainda não tiver autorização, informe os caminhos exatos e o motivo, peça autorização e aguarde uma resposta afirmativa antes de editar. Um pedido direto que identifique o caminho e a mudança pode ser tratado como autorização somente para esses itens; um pedido genérico não é autorização. Peça nova autorização se o escopo mudar.

| Camada | Pasta |
|---|---|
| Migrations | `database/migrations/` |
| Models | `app/Models/` |
| Services | `app/Services/` |
| Controllers | `app/Http/Controllers/` |
| Form Requests | `app/Http/Requests/` |
| Rotas | `routes/web.php` |

## IMPORTANTE
Peça autorização também para alterações muito grandes ou bruscas, mesmo quando estiverem dentro do seu escopo padrão.

## Arquivos fora do escopo padrão
- Views Blade, CSS, JS e imagens (`resources/`, `public/`): são do frontend.
- Seeders e factories (`database/seeders/`, `database/factories/`): são do agente de dados.

Não altere esses arquivos sem a autorização explícita descrita acima. Se a mudança depender deles, explique a necessidade e peça autorização antes de editar.

## Fluxo obrigatório

**Model → Service → Controller + FormRequest**

1. **Model:** define tabela, `$fillable`, relacionamentos e casts. Sem regra de negócio.
2. **Service:** concentra a regra de negócio (ex.: calcular desconto, total do pedido, adicionar item ao carrinho). Recebe dados já validados e devolve o resultado.
3. **FormRequest:** valida e autoriza a entrada (`rules()` e `authorize()`).
4. **Controller:** fino. Recebe o FormRequest, chama o Service e devolve view ou redirect. Sem cálculos nem consultas complexas.

Ordem de criação: migration → model → service → form request (se preciso) → controller → rota.

## Regras de implementação

- **Simples e funcional.** Métodos curtos, com uma responsabilidade. Sem repositórios, eventos, filas, DTOs ou abstrações extras.
- Injete o Service no construtor do Controller.
- Use Eloquent e relacionamentos (`belongsToMany`, `hasMany`, `belongsTo`) em vez de queries manuais.
- Valores monetários: `decimal(10,2)`.
- Use chaves estrangeiras nas migrations.
- Nomeie as rotas (`->name('jogos.index')`) e proteja as rotas do usuário com o middleware `auth`.

## Segurança (objetivo principal do projeto)
- Senha: sempre com `Hash::make()` (ou cast `hashed`).
- Dados sensíveis (ex.: CPF, telefone): cast `encrypted` no model.
- Nunca guarde dados completos de cartão em texto puro.
- Um usuário só acessa e altera os próprios dados (carrinho, pedidos, métodos de pagamento). Verifique isso no `authorize()` do FormRequest ou numa Policy.

## Regras de negócio que ficam nos Services
- Total do pedido = soma de (preço com promoção × quantidade) + frete fixo de R$ 5,00.
- Promoção: percentual ou valor fixo; o preço final nunca fica abaixo de R$ 0,00.
- Todo jogo tem ao menos uma categoria.
- Adicionar um jogo já presente no carrinho incrementa a quantidade.

## Contrato com o frontend
Ao criar ou mudar rotas e variáveis enviadas às views, atualize o arquivo de contrato (rotas, nomes das variáveis e campos dos models) para o frontend seguir.

## Checklist antes de terminar
- [ ] A regra de negócio está no Service, não no Controller?
- [ ] A validação está no FormRequest?
- [ ] Os campos sensíveis estão protegidos?
- [ ] As rotas estão nomeadas e com `auth` quando necessário?
- [ ] O contrato com o frontend foi atualizado?