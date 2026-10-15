# De onde vem o wads.js e como trocar por defer

## Origem
No `<head>` das LPs (us e de) aparece, fora dos blocos HFCM e logo após o gtm4wp:
```html
<script src="https://securepubads.g.doubleclick.net/tag/js/gpt.js"></script>
<script>window.wadsConfig = { network: "23340104016", domain: "us.credityup.com" };</script>
<script src="https://api.winup.network/api/v1/public/kvp/wads.js?v=1"></script>
<winupads tag="interstitial"></winupads>
```
Não tem comentário HFCM e não é `wp_enqueue_script` (sem `id=`), então é impresso por `wp_head` pelo plugin
**winup-ad-engine** (v1.3.0 — o mesmo que enfileira `ad-engine.js` no footer) como "código de cabeçalho"/bloco global.
Confirmar em wp-admin → menu do Winup Ad Engine → blocos/configurações (posição "Head"/"Header").

`wads.js` (19 KB): busca `https://erp.winup.network/api/public/ad-slots`, usa `api.winup.network/.../pais` e `/track`,
NÃO usa `document.write` e já trata `document.readyState`/`DOMContentLoaded` → compatível com `defer`.
`window.wadsConfig` é inline e roda antes, então continua definido quando o wads.js (defer) executa.
`gpt.js` também pode ser `async` (padrão oficial do Google).

## Como fazer o tag síncrono NÃO carregar (só na us lp-02)
Opções, da mais limpa para a menos:
1. **Pelo próprio winup-ad-engine**: se o bloco do head tiver regra de exibição (páginas/excluir URL), excluir
   `/instant-matchmaking-apps-lp-02/` lá e instalar o snippet HFCM #6 só nessa página. Zero código. (verificar se o plugin tem essa opção)
2. **Editar o bloco no plugin** trocando `<script src=...wads.js>` por `<script defer src=...>` (e `async` no gpt.js) — mas isso vale para TODAS as páginas onde o bloco aparece; não serve para teste em 1 LP.
3. **Mini mu-plugin / Code Snippets (PHP)** que, só nessa URL, reescreve o HTML do head com output buffer:
   ```php
   add_action('template_redirect', function () {
     if (strpos($_SERVER['REQUEST_URI'], '/instant-matchmaking-apps-lp-02') !== 0) return;
     ob_start(function ($h) {
       $h = str_replace('<script src="https://securepubads.g.doubleclick.net/tag/js/gpt.js"></script>',
         '<link rel="preconnect" href="https://api.winup.network" crossorigin><link rel="preconnect" href="https://erp.winup.network" crossorigin><script async src="https://securepubads.g.doubleclick.net/tag/js/gpt.js"></script>', $h);
       return str_replace('<script src="https://api.winup.network/api/v1/public/kvp/wads.js?v=1"></script>',
         '<script defer src="https://api.winup.network/api/v1/public/kvp/wads.js?v=1"></script>', $h);
     });
   });
   ```
   Funciona mesmo sem opção no plugin; reversível desativando o snippet. Atenção: LiteSpeed Cache — purgar a página depois.
   **Recomendado** se a opção 1 não existir (não precisa do HFCM #6 nesse caso).
4. HFCM #6 sozinho, sem remover o original: NÃO usar — carregaria wads.js 2x (slots duplicados).

Fazer só com aval da Winup (é a implementação deles no MCM).

## Resultado do teste (15/10/2026, iPhone 13 headless, anúncios reais bloqueados)
`shared/tests/test_wads_defer.py` → v1, v1f e v2 todos **PASS**: wads.js carrega, ad-engine.js carrega, a chamada `erp.winup.network/api/public/ad-slots` dispara, `wadsConfig` está definido, o loader some e o popup do Rewarded aparece.
Comparação justa v1f × v2 (2 rodadas cada): v2 ganhou ~0,3–0,5 s em FCP e no fim do loader. O resultado é ruidoso, e o gpt.js fica bloqueado no teste, então o ganho real pode ser maior. Medir com PSI depois de instalar.
