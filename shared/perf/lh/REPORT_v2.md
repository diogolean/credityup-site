# v2 ao vivo (us.credityup.com) — testes de 15/10/2026 ~16:55 BRT

Snippet ativo: `shared/snippets/07_speed_v2.php` (gpt.js async, wads.js defer, preconnects).

## Funcional — `shared/tests/test_live_v2.py` (HTML ao vivo, sem reescrita, anúncios reais bloqueados, sem cliques)
| Check | lp-01 | lp-02 |
|---|---|---|
| gpt.js async / wads.js defer / 1 só wads.js | PASS | PASS |
| wads.js carrega | PASS (1,7s) | PASS (1,7s) |
| ad-engine.js carrega | PASS | PASS |
| chamada erp.winup.network ad-slots | PASS (2,7s) | PASS (2,7s) |
| loader some | PASS (5,4s) | PASS (5,4s) |
| popup Rewarded aparece | PASS | PASS |
| fix #4: redirect_url = própria LP | PASS | PASS |

## Lighthouse mobile (run A, anúncios bloqueados), mediana de 3
| LP | | Score | FCP | LCP | TBT | CLS |
|---|---|---|---|---|---|---|
| lp-01 | baseline v1 | 44 | 7,2s | 8,6s | 507ms | 0,02 |
| lp-01 | v2 | 44 | 7,5s | 8,5s | 611ms | 0 |
| lp-02 | baseline v1 | 46 | 6,6s | 7,0s | 490ms | 0 |
| lp-02 | v2 | 46 | 3,9s | 7,2s | 574ms | 0 |

Rodadas individuais: lp-01 57/41/44 (FCP 2,3/7,5/7,9s); lp-02 46/53/43 (FCP 2,4/3,9/6,4s).
Conclusão: o defer não move a mediana de forma clara; a variância entre rodadas é enorme (FCP 2,3s→7,9s na mesma página),
então o gargalo dominante ainda é outro (loader com mínimo de 1,5s/overlay, GA4 duplicado, FB pixel, imagens, servidor). Baseline v1 = 1 rodada só.
