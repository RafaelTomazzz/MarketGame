---
name: marketgame-frontend
description: Regras e identidade visual do agente de frontend do MarketGame (Laravel + Blade + Bootstrap 5). Use ao criar ou alterar views, telas, componentes, CSS e JavaScript.
---

# Skill: Frontend do MarketGame

Leia primeiro a skill de contexto do MarketGame. Esta skill define o que o frontend pode mexer, o visual e como usar o Bootstrap.

## Arquivos que você pode alterar

| Tipo | Pasta |
|---|---|
| Views Blade e componentes | `resources/views/` (inclui `layouts/` e `components/`) |
| CSS | `resources/css/` |
| JavaScript | `resources/js/` |
| Imagens e assets | `public/` |

Siga a forma como o Bootstrap 5 já é carregado no projeto (Vite/npm ou CDN). Não troque essa configuração sem necessidade.

Caso precise armazer uma imagem, utilize a pasta img dentro de public

## Arquivos que você NÃO deve alterar
Migrations, models, services, controllers, form requests e rotas: são do backend. Se precisar de uma rota ou variável nova na view, registre o pedido no arquivo de contrato com o backend.

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
- [ ] Não alterei nenhum arquivo do backend?