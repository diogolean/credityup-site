# Loader v3: diagnóstico e teste (15/10/2026, us lp-02, anúncios bloqueados, nada mudou no site)

## Por que o loader v1 sumia em ~5,4s
O timer de 2,5s do v1 está certo, mas ele começa quando o snippet roda, e o HFCM #5 fica no fim do `<head>`.
Na prática o script roda por volta de 2,6–2,8s: TTFB de ~1,5–1,8s, mais o parse do head bloqueado por CSS/JS.
Então 2,6s + 2,5s dá ~5,1s; somando o fade de 0,4s, o loader some em ~5,5s.
O v1 também mede com `Date.now()` (relativo ao script) e não com `performance.now()` (relativo à navegação).

## LCP
O elemento de LCP é `article#post-100 p.wp-block-paragraph` (texto).
Na v2, o tempo se divide em TTFB 16–33% + render delay 67–84%; load delay e load time são 0.
Ou seja, a página está pronta, mas o render atrasa por causa do head pesado e do overlay.
Na v3 (sem throttle), o LCP real do Chrome vira uma IMG em ~9s (a imagem de 118 KB, carregada tarde).

## v3 (shared/snippets/05_cy_modern_loader_v3.html, ~2,9 KB)
- O timer usa `performance.now()`. O loader some no `slotRenderEnded` ou aos 2,5s desde a navegação, o que vier antes.
- Se o script rodar depois de 2,1s, o loader nem aparece.
- O overlay é criado na hora (`body` ou `<html>`), sem esperar o DOMContentLoaded.
- Nenhuma requisição extra. Visual neon com dois anéis SVG girando (rotação em elemento HTML), fade de 0,4s.
- Precisa ser o **primeiro** item do `<head>`: logo depois do `<meta charset>` (Code Snippets/PHP em `wp_head` com prioridade 1), não no HFCM.

Linha do tempo no Playwright (iPhone 13), us lp-02:
- v2: loader aparece em 2,84s e some em 5,34s.
- v3: loader aparece em 1,60s e some em **2,50s**; popup do Rewarded OK.

## Lighthouse mobile, mediana de 3 (HTML servido localmente com `<base>`, mesmas condições para v2 e v3)
| | Score | FCP | LCP | TBT | CLS | SI |
|---|---|---|---|---|---|---|
| v2 | 53 | 3,79s | 4,18s | 636ms | 0,01 | 11,7s |
| v3 | 54 | 1,31s | 4,10s | 1149ms | 0,01 | 10,6s |

Sobre o TBT: o Lighthouse só conta TBT depois do FCP. Com o FCP 2,5s mais cedo, entram na conta as tarefas longas de GTM, gtag e FB pixel, que já existiam.
Uma 1ª versão do loader, com filtro animado dentro do SVG, chegou a 1,3s de TBT. Ela foi trocada pela rotação no elemento.

## TTFB (cache LiteSpeed)
`x-litespeed-cache: hit` em todas as requisições, mas o TTFB medido do box ficou em 1,5–2,0s.
Desse tempo, 0,84s foi só TLS, porque o box sai por um proxy (198.18.0.1); o número está inflado.
O Lighthouse local mediu 470–770ms na v2.

## Imagens e fontes mais pesadas
- `cropped-Imagem-do-ChatGPT-30-de-set.-de-2026-15_33_49.png`: 118 KB, PNG.
- Fontes Google: Roboto 37 KB + 16 KB, Roboto Slab 33 KB.
- `relacio4.jpg`: 13 KB.
- JS de terceiros: gtag G-G96DTLDQJV 177 KB, gtag G-NEFTQCM6YF 176 KB (GA4 duplicado), GTM 124 KB, fbevents 114 KB.
