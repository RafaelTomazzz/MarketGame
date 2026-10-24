---
name: marketgame-frontend
description: Regras e identidade visual do agente de frontend do MarketGame (Laravel + Blade + Bootstrap 5). Use ao criar ou alterar views, telas, componentes, CSS e JavaScript.
---

# Skill: Frontend do MarketGame

Antes de qualquer análise, decisão, alteração ou outra ação, consulte primeiro o arquivo de contexto do projeto: `D:\MarketGame\.github\agents\contexto.md` (caminho relativo ao repositório: `.github/agents/contexto.md`). Leia-o integralmente e siga suas regras. Este arquivo define o contexto geral do MarketGame; as instruções deste agente complementam esse contexto para o frontend.

## Arquivos que você pode alterar

Você pode ler qualquer arquivo do projeto para compreender integrações e dependências. A lista abaixo define seu escopo padrão de escrita; não altere arquivos fora dela sem autorização explícita do usuário. Se uma mudança fora do escopo for necessária e ainda não tiver autorização, informe os caminhos exatos e o motivo, peça autorização e aguarde uma resposta afirmativa antes de editar. Um pedido direto que identifique o caminho e a mudança pode ser tratado como autorização somente para esses itens; um pedido genérico não é autorização. Peça nova autorização se o escopo mudar.

| Tipo | Pasta |
|---|---|
| Views Blade e componentes | `resources/views/` (inclui `layouts/` e `components/`) |
| CSS | `resources/css/` |
| JavaScript | `resources/js/` |
| Imagens e assets | `public/` |
| Middlewares | `app/Http/Middleware/` (somente quando necessário para viabilizar uma funcionalidade do frontend) |

Siga a forma como o Bootstrap 5 já é carregado no projeto (Vite/npm ou CDN). Não troque essa configuração sem necessidade.

Caso precise armazer uma imagem, utilize a pasta img dentro de public

## Arquivos fora do escopo padrão
Migrations, models, services, controllers, form requests e rotas são do backend e ficam fora do seu escopo padrão. Não os altere sem a autorização explícita descrita acima. Middlewares podem ser criados ou alterados em `app/Http/Middleware/` somente quando forem necessários para uma funcionalidade do frontend; reutilize os existentes sempre que possível e não coloque regras de negócio neles. Se também for necessária uma alteração em rota, controller ou form request, registre o pedido no arquivo de contrato com o backend e peça autorização antes de editar esses arquivos.

## Inspiração
Steam e Eneba: tema escuro, vitrine de jogos em grade de cards, preço e desconto em destaque, filtros por categoria e carrinho de fácil acesso. Use só como referência de **layout e organização**. Não copie logos, imagens nem textos desses sites.

## Fonte
Utilize a fonte Roboto em 100% do site!!! Pode alterar o font weight como desejar.

### Telas e componentes esperados
- **Layout base:** navbar com logo, busca, ícone do carrinho (com contador) e login/cadastro; rodapé simples.
- **Listagem de jogos:** grade responsiva de cards. Cada card mostra imagem, título, categorias (badges), preço e promoção.
- **Card de jogo:** badge de desconto (ex.: `-20%` ou `-R$ 10`), preço original riscado e preço final em destaque.
- **Detalhes do jogo:** imagem grande, descrição, categorias, preço, promoção e botão "Adicionar ao carrinho" com quantidade.
- **Carrinho:** lista de itens com quantidade, remover, subtotal, frete (R$ 5,00), total e seleção do método de pagamento.
- **Métodos de pagamento:** lista com ações de adicionar, editar e remover.
- **Cadastro e login:** formulários limpos, com mensagens de erro de validação.

## Bootstrap primeiro, CSS próprio por último

Ordem de preferência:
1. **Classes utilitárias do Bootstrap** (`d-flex`, `gap-3`, `rounded-4`, `shadow`, `text-bg-*`, `bg-*`, grid `row`/`col-*`).
2. **Componentes do Bootstrap** (`card`, `btn`, `badge`, `navbar`, `form-control`, `modal`, `list-group`, `toast`).
3. **Variáveis CSS do Bootstrap** (`--bs-primary`, `--bs-body-bg`, `--bs-border-radius`...), sobrescritas em um único arquivo.
4. **CSS próprio** só para o que o Bootstrap não cobre (gradientes, pequenos ajustes). Mantenha tudo em `resources/css/marketgame.css`, com poucas regras.

Use o tema escuro nativo: `<html data-bs-theme="dark">`.

## Identidade visual

| Uso | Cor |
|---|---|
| Destaque, botões, preço, logo | `#F9C130` (amarelo) |
| Fundo principal / superfícies escuras | `#35304E` |
| Fundo secundário, bordas, texto de apoio | `#5E5A72` |

Cores próximas a essas são permitidas (versões mais claras ou escuras para hover, bordas e sombras).

**Logo:** estrela amarela estilizada ao lado do nome "MarketGame" em amarelo, fonte arredondada e em negrito, sobre fundo com gradiente roxo. Use o arquivo do logo em `public/` na navbar.

### Variáveis (exemplo, em `marketgame.css`)
```css
:root,
[data-bs-theme="dark"] {
  --bs-primary: #F9C130;
  --bs-primary-rgb: 249, 193, 48;
  --bs-body-bg: #35304E;
  --bs-body-color: #F4F2FA;
  --bs-secondary-bg: #5E5A72;
  --bs-border-color: #5E5A72;
  --bs-link-color: #F9C130;
  --bs-border-radius: 0.75rem;
  --mg-gradient: linear-gradient(135deg, #35304E 0%, #5E5A72 100%);
  --mg-gradient-card: linear-gradient(160deg, #433D60 0%, #35304E 100%);
}
```
Para botões amarelos, use `btn btn-primary` com texto escuro (`--bs-btn-color: #35304E`) para manter o contraste.

### Gradientes
- Use gradientes entre **cores próximas** (ex.: `#35304E` → `#5E5A72`) no fundo das telas, da navbar e dos cards.
- Aplique pelas variáveis `--mg-gradient` e `--mg-gradient-card`, não repetindo o valor em várias regras.
- Não use gradiente atrás de blocos de texto longos se isso prejudicar a leitura.

## JavaScript
- Simples e sem dependências extras: JS puro ou os recursos do próprio Bootstrap (modal, toast, dropdown).
- Casos típicos: atualizar quantidade no carrinho, confirmar remoção, mostrar mensagens.
- Regras de negócio e cálculos de preço ficam no backend. O frontend apenas exibe os valores recebidos.

## Boas práticas
- Layout responsivo (mobile primeiro) usando o grid do Bootstrap.
- Componentes repetidos viram componentes Blade (ex.: `<x-game-card />`).
- Use `@csrf` em todos os formulários e `{{ }}` para escapar dados exibidos.
- Texto alternativo nas imagens e rótulos nos campos de formulário.

## Checklist antes de terminar
- [ ] Usei classes e variáveis do Bootstrap antes de escrever CSS?
- [ ] As cores seguem a identidade do MarketGame?
- [ ] O fundo e os componentes usam gradientes de cores próximas?
- [ ] A tela funciona em celular e desktop?
- [ ] Não alterei arquivos do backend, exceto middleware estritamente necessário ou arquivos autorizados explicitamente pelo usuário?