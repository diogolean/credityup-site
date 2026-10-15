# credityup-site (PRIVADO)

Versionamento das customizações de **us.credityup.com** e **de.credityup.com** (AdX via MCM Winup, rede 23340104016).
⚠️ Repositório deve ser **privado** (contém setup de anúncios). Não há senhas/cookies aqui.

## v1-baseline (estado em produção em 15/10/2026)
| Caminho | O que é | Onde está instalado |
|---|---|---|
| `us/*.html`, `de/*.html` | HTML público das LPs principais (us lp-01, us lp-02, de lp-01, de lp-02-art01) baixado via curl | — (snapshot) |
| `shared/snippets/04_rewarded_same_page_fix.html` | Wrapper de `JSON.parse` que troca o `redirect_url` (viralnetwork) dos botões do Rewarded pela própria URL (MODE `reload`) | wp-admin → HFCM (Header Footer Code Manager) → snippet **#4** "Rewarded same-page fix (header)", Header, Site Wide, Ativo — nos 2 sites |
| `shared/snippets/05_cy_modern_loader.html` | Loader moderno (overlay rosa, coração, barra), mín 1,5s / máx 2,5s, some no `slotRenderEnded` do GPT | HFCM snippet **#5** "CY modern loader", Header — nos 2 sites |
| `us/head_block_us_v1.html`, `de/head_block_de_v1.html` | Bloco exato do head: `gpt.js` + `window.wadsConfig` + `wads.js` **síncrono** + `<winupads tag="interstitial">` | Impresso no `<head>` fora do HFCM — vem do plugin **winup-ad-engine** (detalhes na branch `v2-wads-defer`, em `docs/wads-origin.md`) |
| `shared/perf/` | NOTES.md, Lighthouse (lh/REPORT.md, run.sh, JSONs), lt.py (teste do loader) | — |
| `shared/tests/` | t.py / t_de.py: testes headless do redirect do Rewarded (anúncios reais bloqueados) | — |

Regra de testes: SEMPRE bloquear doubleclick, googlesyndication, googletagservices, adservice.google, fundingchoicesmessages, googleadservices, viralnetwork. Nunca clicar em anúncios.

## Estrutura
- `us/` e `de/`: arquivos específicos de cada site (snapshots e bloco do head)
- `shared/`: snippets HFCM (iguais nos 2 sites), testes e perf
- `docs/`: documentação (a partir da v2)
