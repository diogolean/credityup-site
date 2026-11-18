# Deploy manual — us.credityup.com

Não há CI nem rsync. Publicar é colar no wp-admin. Não recolar os snippets HFCM #4 e #5 por cima do que está no ar: o #5 deste git está velho.

Faça um backup antes de qualquer cola: no editor de código de cada post abaixo, copiar o HTML para um arquivo local, e exportar os snippets HFCM #1, #2 e #3.

## Ordem

1. **Plugin** `shared/plugins/credityup-platform/` — zip da pasta, Plugins → Adicionar → Enviar → Ativar. Isso troca a home, cria a categoria `free-credits` se ela não existir, e deixa o push inerte enquanto as chaves Firebase estiverem vazias.
2. **Quiz** — HFCM → snippet #2 “Quiz En JS” → substituir o código pelo arquivo `shared/snippets/02_quiz_en.html`. Manter Header, sitewide, ativo. Não criar um segundo snippet com o mesmo quiz.
3. **LP-01** — post `/best-easy-dating-apps-lp-01/` — colar `us/content/lp-01.post.html` no editor de código. Título: `Easy dating apps: free plans vs Tinder Gold, Bumble Premium, and Hinge`. Não mudar o slug.
4. **LP-02** — post `/instant-matchmaking-apps-lp-02/` — colar `us/content/lp-02.post.html`. Título: `Instant matchmaking apps: free use, paid boosts, and what to check`. Não mudar o slug.
5. **Seis artigos**, no editor de código, sem mudar o slug. Em cada um, apagar a tag `hookup apps` se estiver marcada (o tema imprime essa tag mesmo quando o texto novo não a tem).

| Slug | Arquivo | Título |
|---|---|---|
| `/best-easy-dating-apps-lp-01-art01/` | `us/content/lp-01-art01.post.html` | Free vs paid on easy dating apps |
| `/best-easy-dating-apps-lp-01-art02/` | `us/content/lp-01-art02.post.html` | What Tinder Gold, Bumble Premium, and Hinge+ add |
| `/best-easy-dating-apps-lp-01-art03/` | `us/content/lp-01-art03.post.html` | When a dating premium plan is worth opening |
| `/instant-matchmaking-apps-lp-02-art01/` | `us/content/lp-02-art01.post.html` | Instant matchmaking: free start, then the paid boost |
| `/instant-matchmaking-apps-lp-02-art02/` | `us/content/lp-02-art02.post.html` | Boost or subscription: how to compare the paid screen |
| `/instant-matchmaking-apps-lp-02-art03/` | `us/content/lp-02-art03.post.html` | Filters, verification, and the price screen |

6. **Menu** — Aparência → Menus. Conferir “Free Credits” depois de Relationships. O plugin tenta inserir sozinho; se o item já existir, não duplica.
7. **Não publicar ainda** o que está na lista “ainda falta” abaixo.

## O que não substituir

- HFCM #1 (pixel), #3 (GA4), #4 (redirect do Rewarded) e o loader que já está no ar.
- Head do `winup-ad-engine` (`gpt.js`, `wads.js`, interstitial).
- Slots de namoro `…_rewarded_20260930` e o modal `#masterRewarded`.

## Conferir depois da cola

- LP-01: quiz termina em `/best-easy-dating-apps-lp-01-art01/`. A frase final não é “We found 3 highly compatible platforms!”.
- LP-02: quiz termina em `/instant-matchmaking-apps-lp-02-art01/`.
- Cada artigo ainda tem um `content1` e um `content2` com `lazy`. Os botões `.pf-dbtn` apontam para os mesmos URLs de antes.
- Busca no HTML publicado: zero `hookup`. Nenhuma menção a Patrocino. Nenhuma foto de persona nova.
- Com anúncios bloqueados no browser de teste: os três passos do quiz, o formulário de e-mail só no resultado, o popup Rewarded ainda abre.

## Ainda falta (fica inerte)

- **Push.** Credityup → Push, ou `wp-config.php`: `CREDITYUP_FCM_API_KEY`, `CREDITYUP_FCM_AUTH_DOMAIN`, `CREDITYUP_FCM_PROJECT_ID`, `CREDITYUP_FCM_SENDER_ID`, `CREDITYUP_FCM_APP_ID`, `CREDITYUP_FCM_VAPID_KEY`. Sem isso o pedido de notificação não aparece. Copiar `firebase-messaging-sw.js` para a raiz do WordPress se o plugin não conseguir gravar.
- **WegoChat.** URL do endpoint, formato do JSON (o código manda `token`, `provider=fcm`, `domain`) e, se precisarem, `CREDITYUP_WEGOCHAT_PUSH_AUTHORIZATION`. Sem a URL os tokens ficam pendentes em Credityup → Push.
- **Cascata de créditos.** A Winup cria `web_credits_rewarded` e `mobile_credits_rewarded` num ad unit novo, com `cascade.base` perto de 1050 e `lowerBound` perto de 300. Até lá a tag na home e em Free Credits não pede anúncio.
