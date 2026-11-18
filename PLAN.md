# Plano — home, LPs, push e e-mail (us.credityup.com)

Estado auditado em 18/11/2026. Os snapshots em `us/*.html` e `de/*.html` são o baseline de 15/10/2026. A home e o `<head>` ao vivo já andaram (loader azul `cy-v4`, atraso de GTM/pixel, `.winup-ad-block{min-height:300px}`). Este plano não reverte nada disso.

Não há pipeline de deploy neste repositório: nenhum CI, `wp-cli`, rsync ou workflow. Publicar continua manual, pelo wp-admin e por cópia de arquivos na raiz do WordPress. Nada daqui foi para produção.

## 1. Auditoria

### Home (`us.credityup.com/`)

- Tema Hestia Pro, leitura “últimos posts” (`body.home.blog`). Cabeçalho branco, faixa magenta, card de blog e sidebar com Archives (“setembro 2026”) e Categories (Apps, Flirting, Love, Dating, Relationships). É o layout do screenshot 1.
- Os 10 posts visíveis são artigos das LPs (`subtle-signs-interested-lp-04-art*`, `first-date-conversation-starters-lp-03-art*`, `instant-matchmaking-apps-lp-02-art*`), não uma capa de produto.
- Anúncios na home hoje: `<winupads tag="interstitial">` no head (plugin `winup-ad-engine` 1.3.0 + `wads.js`) e o modal Rewarded do plugin (`#masterRewarded`, sitewide). Não há `<winupads tag="content1">` no loop. A regra CSS `min-height:300px` existe, mas não encontra bloco na home.
- A paginação da home capturada trazia `utm_source` / `utm_campaign` no `href`. O `wads.js` decora links internos com os UTMs da sessão; não é conteúdo do post. Não mexer nesse script.

### LPs que concentram a receita

| Página | Papel | O que não pode mudar |
|---|---|---|
| `/best-easy-dating-apps-lp-01/` (page id 89) | ~49% das views | Quiz `#qwu-quiz-en`, botões `.pf-dbtn` / `.quiz-option-btn`, slot `content1`, pixel, Rewarded |
| `/instant-matchmaking-apps-lp-02/` (page id 100) | ~40% | O mesmo quiz (HFCM #2 é único para as duas) |

Chrome das LPs (CSS “Additional CSS” do tema, não um header preto de verdade): navbar branca do Hestia, faixa rosa `#d81b60`, card branco `max-width:850px`. A regra visual “faixa roxa + barra preta” fica para a home nova. As LPs não são reestilizadas.

Dentro do post, a ordem que o CSS força é: `content1` → quiz → texto e botões. O quiz tem 3 passos, valores `quick_casual`, `serious_relationship`, `age_18_30`, `age_31_50`, `age_50_plus`, `verified_secure`, `people_nearby`, loading 2,5s, CTA final. No passo 1 dispara `fbq('trackCustom','QuizStarted')` e `setTargeting('quiz_step_N', valor)`. No resultado dispara `fbq('track','Lead')` sem dados pessoais.

Pixel (HFCM #1, id `2574163523103706`): `PageView`; `Lead` em `rewardedSlotGranted` com `content_name: Rewarded_Ad_Completed_EN`; `InitiateCheckout` em qualquer `button` (e em `.quiz-option`, `.quiz-btn`, `[role="button"]`). GA4 `G-NEFTQCM6YF`. GTM `GTM-573CZ7M5`.

Rewarded (plugin, não é o `wads.js`): slot web `us_credityup_com_web_rewarded_20260930` e mobile `…_mobile_rewarded_20260930`, modo `auto`, cooldown 24h. O botão do modal aponta para `viralnetwork.com.br` e o HFCM #4 reescreve esse `redirect_url` para a própria URL (`MODE=reload`). Offerwall é esse destino / a cascata de preço do `wads.js` (chave `price`, retentativas), não um bloco nosso. Interstitial, âncora e rewarded são out-of-page: não se reserva altura para eles.

`wads.js` só monta um `<winupads tag="…">` se o manifesto `erp.winup.network/api/public/ad-slots?domain=us.credityup.com` tiver `web_<tag>` ou `mobile_<tag>`. `content1` já é usado nas LPs. Tag desconhecida é ignorada. O script aplica `min-height` com a maior altura configurada e `collapseDiv: ON_NO_FILL`.

Bug deixado de propósito: o CTA do quiz nas duas LPs está hardcoded para `…/best-easy-dating-apps-lp-01-art01/`. Na LP-02 isso manda o resultado para o artigo da LP-01. Corrigir exigiria editar o HFCM #2, compartilhado. Não entra neste PR.

Copy atual é genérica. Na LP-02 o bloco oculto de tags ainda diz “hookup”. O resultado do quiz (“We found 3 highly compatible platforms!” / “Searching available profiles in your region…”) promete uma busca que não existe. As duas frases ficam como estão: moram no HFCM #2. Trocar só o texto é seguro, mas não foi feito para não haver uma segunda cópia do motor do quiz para colar por engano.

### Como o repositório chega no servidor

Não chega sozinho. O README descreve o processo real:

1. HTML de LP e home editados no wp-admin (bloco / editor de código).
2. Snippets HFCM colados à mão (#1 pixel, #2 quiz, #3 GA4, #4 rewarded same-page, #5 loader). O #5 do git está velho (coração rosa). Em produção o loader já é o `cy-v4` azul. Não recolar o #4 nem o #5 por cima do que está no ar.
3. O head `gpt.js` + `wadsConfig` (rede `23340104016`) + `wads.js` síncrono + `<winupads tag="interstitial">` sai do plugin `winup-ad-engine`, não do HFCM.
4. Servidor LiteSpeed, sem page cache na medição de outubro, sem Cloudflare. Tema Hestia Pro.

Plugin novo: zip da pasta `shared/plugins/credityup-platform/`, enviar em Plugins → Adicionar → Enviar, ativar. Service worker: o plugin tenta copiar para a raiz do WordPress (ao lado de `wp-config.php`). Se a raiz não for gravável, o aviso no admin mostra o caminho.

## 2. Home nova

Página inicial só em host `us.credityup.com` (filtro `credityup_home_enabled` para ligar noutro sítio). O template troca o `template_include` da front, chama `wp_head()` / `wp_footer()` e por isso mantém interstitial, Rewarded, HFCM, pixel e GA4. Não chama `header.php` do Hestia. O CSS do tema (bootstrap, `hestia_style`, font-sizes) é removido só na front, para o Hestia não brigar com o layout e para não pagar aquele CSS no LCP.

Arquitetura, de cima para baixo:

1. Barra preta fixa (56px no desktop; no mobile a altura sobe para caber a nav em duas linhas, `padding-top` igual no `body`). Wordmark em SVG, sem o PNG de ~500KB. Links atuais: Home, Apps, Flirting, Love, Dating, Relationships, mais âncoras Rewards e Safety. Nada de categoria é removido.
2. Hero só com texto (LCP). Sem imagem, sem webfont. Duas ações: as duas LPs. A frase deixa claro que crédito aqui é recompensa por anúncio, não empréstimo nem dinheiro.
3. Slot in-page único (`content1`).
4. Dois polos lado a lado: guias de namoro à esquerda, bloco `#rewards` à direita. No mobile o bloco de recompensa vem logo abaixo dos guias.
5. Quatro posts reais (`WP_Query`, sem thumbnail). O arquivo não filtra categoria, então um post de créditos que venha a existir continua aparecendo.
6. Faixa de segurança (dinheiro, encontro, foto).
7. Rodapé com Terms, Privacy, Cookies.

### Mapa de slots

| Slot | Tag | Onde | Reserva | Por quê |
|---|---|---|---|---|
| In-feed | `content1` | Abaixo do hero, classe `winup-ad-block winup-ad-block-1`, label Advertisement | `min-height:300px`, largura máx. 336px, sem `contain:size` (o anúncio pode ser 300x250) | Tag que o manifesto já conhece nas LPs. A reserva existe antes do `wads.js` correr. |
| Interstitial | `interstitial` | Head, plugin | Nenhuma (out-of-page) | Já impresso pelo `winup-ad-engine`. Não duplicar. |
| Rewarded de namoro + Offerwall | slots `…_rewarded_20260930` e redirect do modal | Plugin + HFCM #4, em toda página via `wp_footer` | Nenhuma | Fora da página. O prompt de push fica em `z-index` baixo e só aparece se `#masterRewarded` não estiver `.active` e o loader não estiver na tela. |
| Cascata de créditos (desligada) | `credits_rewarded` com `rewarded` e `trigger="#cy-reward-unlock"` | Dentro de `#rewards` | O cartão do controle tem `min-height:168px`. O anúncio em si é out-of-page, então não se reserva 300px para ele | A tag só pede anúncio quando o manifesto tiver a chave. Ver a seção abaixo. |
| `content2`, `top`, `sidebar`, âncora | — | Não impressos | — | Tag ausente no manifesto vira buraco de 300px. Quando a Winup criar `web_content2` / `web_sidebar`, dá para acrescentar pelo filtro `credityup_home_slots`. |

### Cascata de créditos (aberta, ainda desligada)

Em 18/11/2026 o sitemap de `us.credityup.com` não tem página, categoria nem tag de credits/rewards. Categorias no ar: Apps, Flirting, Love, Dating, Relationships. O mesmo vale para `de.credityup.com` (apps, flirten, beziehungen, liebe, dating). Existem páginas fora desses polos (`/insurance-referral-income-how-it-works/`, `/compare-car-and-home-insurance-quotes/`, duas de certificado de marketing). O template não apaga post, página, categoria nem tag, e não mexe em `wads.js`, no head do `winup-ad-engine` nem no HFCM #4. Essas páginas de seguro e marketing não entram na capa: não são guia de namoro e não são o desbloqueio de recompensa.

O que já está no site e permanece:

- `wads.js` síncrono, rede `23340104016`. A escada de preço (chave `price`, ou `priceKey` se o manifesto trocar) já inclui os degraus de 300 a 1050. Ela só anda se o JSON de `erp.winup.network/api/public/ad-slots?domain=us.credityup.com` trouxer `cascade` ou `cascadesUtm` com `base`, `lowerBound`, `retryLimit` e, se quiserem, `decayPct` e `retryTimeout`.
- Rewarded de namoro: plugin, units `us_credityup_com_web_rewarded_20260930` e `…_mobile_rewarded_20260930`, modal `#masterRewarded`. Offerwall desse fluxo é o destino do botão do modal (viralnetwork), reescrito para a própria URL pelo HFCM #4. Não foi alterado.
- Interstitial no head, impresso pelo plugin. A home não imprime um segundo.

O que a home acrescenta, sem comprar o anúncio:

- `#cy-reward-unlock` é um link para `#rewards`, não um `<button>` (o pixel trata todo button como `InitiateCheckout`).
- `<winupads tag="credits_rewarded" rewarded trigger="#cy-reward-unlock" …>`. O `wads.js` só monta se existir `web_credits_rewarded` ou `mobile_credits_rewarded`. Sem a chave, o elemento é ignorado e o clique não abre anúncio nem sai do site.

Para a Winup ligar a cascata, sem deploy novo:

1. Criar no manifesto do domínio `web_credits_rewarded` e `mobile_credits_rewarded`.
2. `path` de um ad unit novo, separado dos units de namoro. Piso de 300–1050 no unit que as LPs já usam muda o preço do tráfego de Meta.
3. `cascade.base` perto de 1050 e `cascade.lowerBound` perto de 300. A escada no `wads.js` já tem esses números; o tema não hardcoda preço.
4. Tráfego direto: o script não expressa “UTM ausente”. `cascadesUtm` só casa um valor que já está na sessão (`utm_source`, `utm_medium`, `src_type`, …). O isolamento é a tag existir só na home. Quem está na LP de namoro não pede esse unit. Um piso só para um UTM específico usa `cascadesUtm` ou `cascadePath`.
5. Se o Offerwall for o redirect do modal e não o rewarded do GPT, ele continua no plugin. Não apontar `#cy-reward-unlock` para a viralnetwork: o HFCM #4 reescreve esse tipo de destino, e o href deste controle fica em `#rewards`.
6. Nada a mudar em `wads.js`, no head block nem nas settings do `winup-ad-engine`.

O texto diz que o desbloqueio ainda não está ligado, para não prometer um crédito que o GAM não entrega. Não há oferta de empréstimo, cartão ou dinheiro.

O `wads.js` continua síncrono. Adiá-lo melhora o FCP e corta a cascata; fica fora deste PR.

## 3. Copy das LPs

Arquivos para colar no editor de código do post, no lugar do conteúdo atual. Slug, links dos três botões, `data-pf="btn"`, classe `pf-dbtn`, bloco do quiz (CSS + HTML, ids, `data-qwu-en-value`) e o `<winupads tag="content1">` são os bytes do snapshot.

| | LP-01 | LP-02 |
|---|---|---|
| Arquivo | `us/content/lp-01.post.html` | `us/content/lp-02.post.html` |
| Title / H1 (o plugin aplica na frente) | Easy dating apps, without the extra steps | How instant matchmaking apps actually work |
| O que muda | Miolo editorial: perfil curto, filtros, privacidade, primeira mensagem, encontro, o que cada botão abre | O que “instant” significa no produto (local, perfil curto, alerta), expectativa, número global ≠ cidade, foto e endereço, encontro |
| O que sai | Texto genérico e, na LP-02, as tags ocultas com “hookup” | A frase em português no meio da página em inglês |
| O que fica | Rótulos Explore / Read tips / View guide, hrefs `…-art01/02/03`, “You will stay on this site.”, créditos de imagem da LP-02, fontes FTC / Google Play | Idem, hrefs da LP-02 |
| Tom | Guia. Sem ranking falso, sem “tem gente te esperando”, sem conteúdo adulto, sem promessa de match | O mesmo |

O plugin não faz `the_content`. Colar o HTML e ativar o plugin (títulos + meta description) é um passo só. Se só o plugin entrar, o H1 muda e o miolo antigo permanece até o paste.

## 4. Web push (FCM + WegoChat)

Peça: `shared/plugins/credityup-platform/` (módulo push) e os arquivos em `assets/`.

- Prompt nosso, canto inferior, em inglês, com “Not now” (14 dias) e “Allow notifications”. Os controles são `<input type="button">`, não `<button>`, porque o pixel trata todo `<button>` como `InitiateCheckout`.
- O `Notification.requestPermission()` só roda depois desse aceite, e só se a config não tiver placeholder `YOUR_*`. Sem config, o prompt nem aparece.
- `firebase-messaging-sw.js` na raiz do site (escopo `/`). Config pública em `/credityup-firebase-config.js`, gerada ao salvar a tela Credityup → Push. Placeholders no git; valor real fica em opção do WordPress ou em constante, nunca no commit.
- Chave de servidor do Firebase e JSON de service account não entram no site. A WegoChat envia a campanha. Nós só obtemos o token do browser.
- Token: `POST /wp-json/credityup/v1/push-token` (HMAC da hora, rate limit, sem o token na resposta). O WordPress guarda o token numa tabela privada e, se houver endpoint, faz `wp_remote_post` server-side. O header `Authorization` não vai para o JavaScript.
- Hook de código, se o formato da Winup for outro: filtros `credityup_wegochat_push_url`, `credityup_wegochat_push_body`, `credityup_wegochat_push_authorization` e a action `credityup_fcm_token`. No browser, `window.credityupPushHooks.push(function (token) { … })`. Não empurrar o token para `dataLayer`, GA4 ou pixel.
- Clique na notificação só abre URL same-origin.

O que falta da Winup / do Diogo está na seção 7.

## 5. E-mail no fim do quiz

Módulo leads do mesmo plugin. Não usa plugin de formulário.

- Observa `#qwu-quiz-en`. Quando `data-qwu-phase="result"`, insere o formulário no fim de `#qwu-en-body`, depois do CTA. Some se a fase não for `result`. Não altera o JS do quiz, os valores, o `fbq('track','Lead')` do resultado nem o href do CTA.
- Campos: e-mail, checkbox de consentimento (obrigatório, link para `/privacy-policy/`), honeypot. O envio é `<input type="submit">` para não disparar `InitiateCheckout`.
- `POST /wp-json/credityup/v1/lead`. Corpo JSON. O e-mail não volta na resposta, não entra na URL, não vai para `dataLayer` / `fbq` / `gtag`.
- Tabela `{prefix}credityup_leads`: e-mail, consentimento, path (sem query), data GMT, hash de IP (salt do WP, para rate limit). Tela em Credityup → Leads, com export CSV (e-mail, consentimento, path, data). Capacidade `manage_options`.
- O quiz alemão é `#qwu-quiz-v3`. Este formulário não gruda nele.

Brevo fica para depois: a tabela já é a lista.

## 6. Riscos

- Colar o HTML da LP por cima do post apaga o conteúdo atual no banco. Exportar o post antes (o snapshot deste repo é de 15/10; o ar pode ter divergido — conferir no editor).
- Recolar HFCM #4 ou #5 a partir do git reverte o loader novo e pode reabrir o redirect do Rewarded para a viralnetwork.
- Dois `<winupads tag="interstitial">` (o do plugin e um nosso) duplicariam interstitial. O template não imprime essa tag.
- `content1` na home compete pelo mesmo ad unit das LPs. É um slot, não dois.
- Reserva de 300px fica vazia se o GAM não preencher (`ON_NO_FILL` esvazia o criativo, a caixa nossa continua). É o custo de CLS ~0. Se a Winup confirmar outra altura máxima, mudar `min_height` em `credityup_home_slots`.
- O prompt de push e o formulário não cobrem o Rewarded. Mesmo assim, medir no ar se o `InitiateCheckout` por sessão subiu (não deve, pelos tipos de controle usados).
- A home deixa de listar o blog infinito. Os posts continuam nas categorias e no bloco “From the archive”.
- Sem endpoint da WegoChat, o token fica só na tabela do WP. Campanha não sai até o reenvio.
- LP-02: o fim do quiz ainda abre o artigo da LP-01. Decisão à parte.
- Frases de “perfis encontrados” no quiz continuam até alguém editar o HFCM #2 com um diff mínimo (só as duas strings).

## 7. Deploy (Diogo aprova; ninguém publica por este PR)

1. Backup dos dois posts (exportar ou copiar o HTML do editor) e dos snippets HFCM #1–#5.
2. Enviar e ativar o plugin `credityup-platform` só depois de ler o aviso de arquivos da raiz.
3. Colar `us/content/lp-01.post.html` no post `/best-easy-dating-apps-lp-01/` e `us/content/lp-02.post.html` no post `/instant-matchmaking-apps-lp-02/`, modo código. Não mudar o slug.
4. Conferir no HTML publicado: um `content1`, `#qwu-quiz-en`, três `.pf-dbtn` com os hrefs antigos, `#masterRewarded` ainda impresso pelo plugin, pixel ainda no head.
5. Não tocar no `winup-ad-engine`, no HFCM #4 e no loader que já está no ar.
6. Firebase (quando existir), em Credityup → Push ou em `wp-config.php`:
   - `CREDITYUP_FCM_API_KEY`
   - `CREDITYUP_FCM_AUTH_DOMAIN`
   - `CREDITYUP_FCM_PROJECT_ID`
   - `CREDITYUP_FCM_SENDER_ID`
   - `CREDITYUP_FCM_APP_ID`
   - `CREDITYUP_FCM_VAPID_KEY` (chave pública Web Push)
   - Domínios autorizados: `us.credityup.com` e, se for usar lá, `de.credityup.com`.
7. WegoChat (painel Push → Firebase Configs precisa do mesmo projeto). Para o token chegar em Subscribers, falta o contrato do endpoint:
   - URL
   - método (assumimos POST JSON)
   - nome do campo do token (assumimos `token`, mais `provider=fcm` e `domain`)
   - se há `Authorization` (constante `CREDITYUP_WEGOCHAT_PUSH_AUTHORIZATION` ou filtro; não colocar segredo no JS)
   - Constantes: `CREDITYUP_WEGOCHAT_PUSH_URL` e, se preciso, `CREDITYUP_WEGOCHAT_PUSH_AUTHORIZATION`.
   - Com a URL salva, Credityup → Push → “Reenviar tokens pendentes”.
8. Permissão de escrita na raiz do WP para `firebase-messaging-sw.js` e `credityup-firebase-config.js`. Sem isso, o admin avisa e o push não registra.
9. Smoke test com anúncios bloqueados no browser de teste (regra do repo): quiz até o fim, formulário só no resultado, três botões no mesmo domínio, popup Rewarded ainda abre, nenhum e-mail na query string. Ver `shared/tests/check_platform.py`.

## 8. Checklist de integridade

Rodar `python3 shared/tests/check_platform.py`. O teste confere:

- [ ] Snapshots `us/us_lp-01.html` e `us/us_lp-02.html` não são editados por este PR (o quiz colado nos arquivos novos é cópia byte a byte do snapshot: CSS, `#qwu-quiz-en`, valores `data-qwu-en-value`).
- [ ] Três `.pf-dbtn` de cada LP conservam `href` e rótulo (Explore, Read tips, View guide na LP-01; Read tips, Learn more, View guide na LP-02) e `data-pf="btn"`.
- [ ] `content1` continua no conteúdo das LPs. A home nova adiciona um `content1` e não adiciona `interstitial`.
- [ ] Nenhum arquivo novo chama `fbq`, reescreve `JSON.parse`, mexe em `rewardedSlotGranted`, `#masterRewarded`, `ramAdGate` ou `redirect_url`.
- [ ] O formulário não usa `<button>`, não lê `location.search` para montar o lead e o endpoint de lead não devolve o e-mail.
- [ ] Config Firebase versionada só tem placeholders `YOUR_*`. Nenhum segredo no diff.
- [ ] HFCM #4 e #5 do git não foram alterados.
- [x] Screenshots antes/depois (home e as duas LPs, desktop e mobile) em `docs/screenshots/`. O “antes” é mock estático do HTML atual (WordPress não roda nesta máquina; o popup Rewarded cobre a LP ao vivo). O “depois” é o template / o conteúdo novo, com o mesmo chrome de LP (faixa rosa, card, quiz no passo 1).
- [x] Clique headless no preview da LP nova: três passos (`Quick Casual Meetings` → `18 to 30 years` → `Verified & Secure Profiles`), fase `result`, `fbq` `QuizStarted` e `Lead`, formulário com consentimento e sem `<button>`, `location.search` vazio. Prints: `lp01-result-desktop.png`.
