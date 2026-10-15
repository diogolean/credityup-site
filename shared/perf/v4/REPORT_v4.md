# Speed v4 (15/10/2026) — `shared/snippets/10_speed_v4.php` (us + de num arquivo só)

## O que faz (output buffer, só front-end, visitante não logado)
1. **Delay (sem remover)** de GTM-573CZ7M5, gtag GA4 (G-NEFTQCM6YF/US, G-32D020LG1Q/DE, e G-G96DTLDQJV que vem via GTM) e FB pixel: os snippets inline (dataLayer, gtag(), fbq()) rodam na hora e enfileiram; só os .js externos esperam a 1ª interação (scroll/touch/click/tecla/wheel/mousemove) ou 3s desde a navegação.
2. **CLS**: `.winup-ad-block{min-height:300px}` (content slots até 336x280 + label). Logo com width/height.
3. **Logo**: PNG 118 KB (1254px exibido a 75x50) → WebP 150x100 inline (~7 KB, 0 requisições). `relacio4.jpg` do popup Rewarded em https.
4. **Fontes**: webfontloader JS da LiteSpeed → CSS do Google Fonts direto (`display=swap`, preload não-bloqueante) + preconnect gstatic.
Não mexe em wads.js, gpt.js, ad-engine, Rewarded nem leilão.

## Funcional (Playwright, HTML reescrito pelo PHP real, anúncios bloqueados, só scroll) — us lp-02 e de lp-01: PASS
wads.js, ad-engine.js, ad-slots ✓ · loader some em 2,50s ✓ · popup Rewarded ✓ · fix #4 redirect_url = LP ✓ ·
gtm.js, gtag/js, fbevents carregam após interação/3s, `g/collect` (GA4) e `facebook.com/tr` (pixel) disparam, dataLayer tem js/config/gtm.js/gtm.dom/gtm.load/scrollDepth ✓

## Lighthouse mobile (HTML local com <base>, anúncios bloqueados) — mediana de 3
| LP | | Score | FCP | LCP | TBT | CLS | SI |
|---|---|---|---|---|---|---|---|
| us lp-02 | v3.1 live | 53 | 1,32s | 4,15s | 1160ms | 0,01 | 10,8s |
| us lp-02 | **v4** | **68** | 1,33s | 3,72s | 729ms | 0 | 4,4s |
| de lp-01 | v3.1 live | 54 | 1,30s | 4,03s | 1170ms | 0,01 | 10,6s |
| de lp-01 | **v4** | **67** | 1,32s | 3,72s | 786ms | 0 | 4,7s |

CLS 0,303 do PSI não reproduz com anúncios bloqueados (0,01): vem do preenchimento real dos slots/popup; a reserva de 300px ataca o content1. Medir no PSI após instalar.
Efeito colateral: se o slot content não preencher, fica um espaço de 300px.
