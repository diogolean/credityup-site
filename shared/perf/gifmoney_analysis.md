# gifmoney.com (o maior da rede Winup MCM) × credityup, com en.blovibe.com lp08 de referência
Feita em 15/10/2026 só com HTML/JS estático e headless com anúncios reais bloqueados, sem cliques. Dados brutos em `shared/perf/gifmoney/`.

## TL;DR
O ganho da gifmoney **não vem de densidade de anúncios**: as páginas deles têm só content1 + interstitial + Rewarded.
Vem de dois pontos:
1. **Cascata (waterfall) de preço no Rewarded, configurada pela Winup no servidor e segmentada por UTM.**
   O manifesto público `erp.winup.network/api/public/ad-slots?domain=gifmoney.com` tem `mobile_rewarded.cascadesUtm` com 8 regras.
   Exemplo: `utm_medium=rsm-fb-id919-insurance-usa` → `base` 1050, `lowerBound` 100, `retryLimit` 20, `retryTimeout` 600 ms, `step` auto.
   Tudo roda num ad unit dedicado: `/23340104016/gifmoney_com/gifmoney_com_mobile_rewarded_cascade1_20260831`.
   O wads.js (o mesmo arquivo para todo mundo) faz o pedido com o key-value `price=<base>`.
   Se vier vazio, destrói o slot e pede de novo com `price` mais baixo, numa grade de 0,01 a 1050, até o `lowerBound` ou o limite de tentativas.
   Do lado do GAM existem line items/regras mirando `price=X`; isso não aparece no cliente.
   **Os nossos manifestos (us/de) não têm cascata nenhuma**: o Rewarded é pedido uma vez só, sem preço.
2. **Rewarded disparado no clique de links internos** (`<winupads rewarded trigger=".btn-winupads-trigger">`, `.btn-ads-1…5`).
   Cada clique em "OurTime Guide", "EliteSingles Guide" etc. mostra um Rewarded e leva para outra página do próprio site.
   Isso gera mais pageviews por sessão, mais interstitials e mais Rewardeds.
   O wads também propaga as UTM nos links internos, para a cascata continuar valendo nas páginas seguintes.

## Comparação
| | gifmoney (3 páginas top) | blovibe lp08 | us lp-01 / lp-02 | de lp-01 / lp-02-art01 |
|---|---|---|---|---|
| wads.js | `?v=2`, **arquivo idêntico ao `?v=1`** (mesmo md5; o v é só cache-buster) | sem `?v` (init antigo `wads('2334…')`) | `?v=1` defer | `?v=1` defer |
| winup-ad-engine | não (tags no tema + Ad Inserter "code-block") | não (GPT manual + Elementor) | sim (2 de 16 blocos ativos) | sim |
| Cascata no Rewarded (manifesto) | **sim, 8 regras por UTM, base $200–1050 → piso $100–350, 8–20 tentativas, 600 ms** | ? (domínio fr/en, não checado) | **não** | **não** |
| Gatilho do Rewarded | clique em links/botões internos (navegação entre guias) | plugin Rewarded Ad Modal (popup automático, cooldown de 24h) | plugin Rewarded Ad Modal (popup automático, cooldown de 24h, mesma página) | idem |
| Interstitial | sim | sim | sim | sim |
| Slots de conteúdo | **1** (content1 no topo) | 2 (content1/2 GPT manual) | 1 visível (content1 antes do 1º parágrafo) + content2 lazy | 1–2 |
| Anchor / offerwall / native / multiplex | não / no manifesto, sem tag / não / não | não | não / offerwall do GAM / não / não | idem |
| Lazy/refresh | sem refresh | – | content2 lazy | idem |
| Palavras | 1000–1490 | 880 | 391 / 445 | 378 / 626 |
| Parágrafos / H2 | 25–34 / 9–12 | 22 / 4 | 19–23 / 6–7 | 18–26 / 5–6 |
| Altura (mobile) | 7.300–10.100 px | 6.000 px | ~4.100 px | 4.000 / 8.700 px |
| Slots por 1000 palavras (conteúdo) | ~0,7–1,0 | ~2,3 | ~2,2–2,5 | ~2,6–3,2 |
| Paginação/multipágina | não; navegação interna via botões com Rewarded | não | não | não |
| Lighthouse mobile (anúncios bloqueados, mediana de 3) | 52 (FCP 2,8s, LCP 9,9s, TBT 708ms) | 48 (PSI do Diogo) | 38 live → ~68 local com v4 | ~67 local com v4 |
| Key-values enviados ao GAM | `utm_*`, `src_page/funil/msg/type`, `id_post_wp`, `price` (cascata) | idem (sem cascata) | os mesmos menos `price` | idem |

O PSI da API estourou a cota diária, então rodei Lighthouse local.
Pricing rules/floors não são visíveis no cliente. Dá para inferir que eles miram `price`, o ad unit `…_rewarded_cascade1_…` e `utm_medium`/`utm_term`, que chegam ao GAM como key-values.

## O que eles fazem e nós não (ranqueado)
1. **Cascata de preço no Rewarded por UTM.** Receita: **alta**. É a explicação mais provável para o eCPM de ~$132 no Rewarded US.
   Risco: baixo/médio. Tentativas demais atrasam o anúncio; fill rate menor no início. É configuração da Winup, não código nosso.
   → **Pedir à Winup para ativar `cascadesUtm` em us/de.credityup** com os nossos `utm_medium` das campanhas FB (ex.: base 600, piso 150, 15 tentativas, 600 ms para US).
2. **Rewarded no clique de links internos + mais páginas encadeadas** (guias que levam a outros guias). Receita: **alta**: mais pageviews, Rewardeds e interstitials por sessão.
   Risco: médio. UX e política do Rewarded: é preciso consentimento e recompensa clara (o wads tem o aviso Sim/Não `trigger-text`/aviso), e não pode virar navegação forçada.
   → Criar 3–5 LPs "irmãs" por vertical, ligadas por botões com `class="btn-winupads-trigger"` e uma tag `<winupads rewarded trigger=".btn-winupads-trigger" cooldown="…">`, além do plugin atual. Atenção: o GPT só aceita 1 Rewarded por página.
3. **Páginas mais longas e estruturadas** (1000–1500 palavras, 9–12 H2). Receita: **média**: mais tempo e scroll, mais pontos para CTA/botões internos e qualidade melhor no Google/Meta.
   Risco: baixo; custa redação e deixa a página um pouco mais lenta.
4. **Propagação de UTM nos links internos.** O wads já faz isso para links do mesmo host, de graça; o que conta é ter links internos. Receita: viabiliza o item 1 nas páginas seguintes.
5. Menos banners. A gifmoney tem 1 só slot de conteúdo e mesmo assim lidera a rede, então **mais banners não são o caminho principal**.

Não encontrei Prebid, Amazon TAM, AdSense nem outra SSP em nenhum dos sites. É AdX/GAM puro via MCM. A "cascata" é a do wads (re-request com `price` decrescente), não um header bidding.

## Recomendações
### (a) Alongar as LPs?
Sim, com moderação. Meta: **900–1200 palavras, 8–10 H2, parágrafos curtos (≤60 palavras)**, uma imagem leve (WebP) e 2–3 caixas "Next guide →" com links internos (gatilho do Rewarded).
Não é para "encher linguiça": o objetivo é dar espaço para slots bem espaçados e para a navegação interna.
### (b) Ativar mais blocos do winup-ad-engine?
Ativar só o que respeita o espaçamento:
- content1 (manter, antes do parágrafo 1)
- content2 (lazy, depois do parágrafo 3, já ativo)
- **content3** (lazy, depois do ~60% do texto ou depois do 2º/3º H2)
- **content4** (lazy, antes do CTA final), só nas páginas acima de ~900 palavras

Regra prática:
- ≥1 viewport (~800 px no mobile) ou ~250 palavras entre slots
- no máximo ~3 slots por 1000 palavras
- densidade de anúncio abaixo de **30%** da altura da página no mobile (Better Ads/Coalition)
- nada de anúncio colado em botão (risco de clique acidental e tráfego inválido)
- anchor só se o interstitial e o Rewarded continuarem dentro da política (a Better Ads proíbe interstitial antes do conteúdo e "sticky" grande, então mantenha o anchor de 50px)

Efeito na receita:
- Mais slots com lazy-load → mais impressões e viewability ok.
- Slots abaixo da dobra sem lazy derrubam a viewability e o eCPM médio e diluem a cobertura.
- Em LPs de ~400 palavras, mais de 2 slots provavelmente **reduz** o eCPM sem ganho líquido.
- O ganho grande está na cascata do Rewarded e na navegação interna, não nos banners.
