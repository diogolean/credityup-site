# História visual — us.credityup.com

Registro de 18/11/2026. Nada daqui foi publicado. Não há imagem de persona (Sophie ou outra) neste trabalho: a home nova, as LPs novas e o tópico Free Credits não usam foto de pessoa. O modal Rewarded que já está no ar continua com a imagem que o plugin imprime (`relacio4.jpg` no HTML antigo; no print de 18/11 o popup mostra um gráfico). Essa imagem não foi adicionada nem trocada aqui.

Os prints “ao vivo” foram feitos a partir do HTML salvo na hora, com os domínios de anúncio bloqueados. Mesmo assim o popup Rewarded da LP-01 aparece, porque o HTML do plugin já vem na página.

## Onde procurar arquivos antigos

| Fonte | O que deu | Data |
|---|---|---|
| Git `c950f76` (branch `main` e este PR) | HTML completo de LP-01 e LP-02 em `us/us_lp-01.html` e `us/us_lp-02.html`. É o snapshot mais antigo das duas LPs. | 15/10/2026 |
| Git `831ca0a` (branches `v2-wads-defer` e `v3-offerwall-event`) | Conteúdo v1 e rascunho v2 (mais longo, com “Free Features Versus Paid Upgrades”). Copiado para `docs/history/git-2026-10-16/`. Não está no HTML ao vivo: o primeiro parágrafo de 18/11 ainda é o de 15/10. | 16/10/2026 |
| Git `origin/v4-page-skins`, backup `backups/2026-10-30-before-skins/` | HTML de LP-01 e LP-02 outra vez, mais prints do header rosa/preto. Copiado o HTML e dois prints para `docs/history/git-2026-10-30/`. O texto das duas LPs é o mesmo de 15/10. O resto dos prints continua só naquela branch. | 30/10/2026 |
| Git `origin/email-capture` e `origin/tracking-events` | Snippets 13 e 14 (e-mail e eventos). Não há HTML novo das LPs. | 03/11/2026 |
| Wayback CDX `url=us.credityup.com/*` | Resposta `[]` enquanto a API respondia. Consultas seguintes (`matchType=prefix` e `matchType=domain`) caíram em “Internet Archive: Temporarily Offline”. Nenhum capture de `us.credityup.com` para citar. | consultado 18/11/2026 |
| Wayback, domínio `credityup.com` (sem `us.`) | Captures do apex, não deste site de namoro. Vários são a página de conta suspensa. | 01/11/2020, 21/12/2021, 04/02/2022, 01/03/2022, 27/08/2025 |
| archive.today / archive.is | Sem resposta útil e sem URL de capture. | 18/11/2026 |
| Cache do Google | `webcache.googleusercontent.com` devolve a página “Google Search”, não o HTML do site. | 18/11/2026 |

Links dos captures do apex (não são us.credityup.com):

- https://web.archive.org/web/20201101103823/https://credityup.com/
- https://web.archive.org/web/20201101185154/https://credityup.com/
- https://web.archive.org/web/20211221082205/https://credityup.com/
- https://web.archive.org/web/20220204170452/https://credityup.com/comments/feed/
- https://web.archive.org/web/20220301133055/https://credityup.com/cgi-sys/suspendedpage.cgi
- https://web.archive.org/web/20250827214411/https://credityup.com/
- https://web.archive.org/web/20250827215535/https://credityup.com/comments/feed/
- https://web.archive.org/web/20250827224554/https://credityup.com/

Não há snapshot mais antigo que 15/10/2026 para as URLs `us.credityup.com`. O HTML de 15/10 é o mais antigo que existe.

## Home

Antes (ao vivo em 18/11, igual ao screenshot que abriu o pedido): índice do Hestia. Cabeçalho branco, faixa rosa, cards de blog com foto, sidebar Archives (“setembro 2026”) e Categories (Apps, Dating, Flirting, Love, Relationships). Sem item Free Credits. Sem slot `content1` no loop.

- HTML: `docs/history/live-2026-11-18/home.html`
- Desktop: `docs/history/live-2026-11-18/home-desktop.png`
- Mobile: `docs/history/live-2026-11-18/home-mobile.png`

Depois (ainda não publicado): capa em texto, barra preta, slot `content1` com 300px, guias de namoro ao lado de Free Credits, nav com Free Credits para `/category/free-credits/`. Sem foto de pessoa.

- Desktop: `docs/screenshots/home-after-desktop.png`
- Mobile: `docs/screenshots/home-after-mobile.png`
- Página do tópico: `docs/screenshots/free-credits-desktop.png` e `docs/screenshots/free-credits-mobile.png`

## LP-01 `/best-easy-dating-apps-lp-01/`

O título ao vivo nos três momentos (15/10, 30/10, 18/11) é o mesmo: “Streamlined Dating: Discovering User-Friendly Apps”. O primeiro parágrafo também: “Navigating the online dating landscape doesn't have to be complex.” Quiz, três botões (Explore, Read tips, View guide) e `content1` já estavam lá. O texto não cita Tinder Gold, Bumble Premium nem Hinge. Em 16/10 houve um rascunho mais longo no git (`lp-01_v2.html`, seção “Free Features Versus Paid Upgrades”) que não entrou no ar.

- 15/10, HTML completo: `us/us_lp-01.html`
- 16/10, conteúdo v1 e v2: `docs/history/git-2026-10-16/lp-01_v1_rendered.html`, `docs/history/git-2026-10-16/lp-01_v2.html`
- 30/10, HTML: `docs/history/git-2026-10-30/lp-01.html`
- 18/11, HTML e prints: `docs/history/live-2026-11-18/lp-01.html`, `lp-01-desktop.png`, `lp-01-mobile.png`

O print de 18/11 mostra o popup “CONTINUE READING / WATCH THE AD AND CONTINUE”, a faixa roxa e os três botões. O quiz fica atrás do popup.

Depois (não publicado): mesmo quiz, mesmos botões, mesmo `content1`. O miolo passa a comparar plano grátis com Tinder Gold, Bumble Premium e os planos pagos do Hinge (Hinge+ e HingeX), sem preço inventado e sem “hookup”.

- HTML para colar: `us/content/lp-01.post.html`
- Desktop: `docs/screenshots/lp01-after-desktop.png`
- Mobile: `docs/screenshots/lp01-after-mobile.png`

## LP-02 `/instant-matchmaking-apps-lp-02/`

Título estável de 15/10 a 18/11: “Swift Dating: Explore Instant Match Solutions”. O bloco oculto de tags no HTML de 15/10 ainda diz “hookup”. O rascunho v2 de 16/10 também não substituiu o que está no ar. Em 30/10 o trabalho da branch `v4-page-skins` testou header rosa e header preto; o texto não mudou. Prints daquela branch: `docs/history/git-2026-10-30/lp-02-before-loaded.png` (como estava) e `lp-02-black-header-desktop.png` (teste de header preto).

- 15/10: `us/us_lp-02.html`
- 16/10: `docs/history/git-2026-10-16/lp-02_v1_rendered.html`, `lp-02_v2.html`
- 30/10: `docs/history/git-2026-10-30/lp-02.html`
- 18/11: `docs/history/live-2026-11-18/lp-02.html`, `lp-02-desktop.png`, `lp-02-mobile.png`

Depois: quiz e botões (Read tips, Learn more, View guide) iguais. O texto tira “hookup” e “spontaneous encounter” e explica o que é grátis e o que é boost / premium (Tinder Gold, Bumble Premium, Hinge+).

- `us/content/lp-02.post.html`
- `docs/screenshots/lp02-after-desktop.png`
- `docs/screenshots/lp02-after-mobile.png`

## Artigos `-art01/02/03`

Não há HTML desses artigos no git nem na Wayback. O único exemplar é o download de 18/11.

| URL | Título no ar | HTML | Print |
|---|---|---|---|
| `/best-easy-dating-apps-lp-01-art01/` | Finding the Right Connection: Simplified Dating App Choices | `docs/history/live-2026-11-18/lp-01-art01.html` | `lp-01-art01-desktop.png` |
| `/best-easy-dating-apps-lp-01-art02/` | Dating Apps: Popular Choices vs. Specific Focus | `lp-01-art02.html` | — |
| `/best-easy-dating-apps-lp-01-art03/` | Making Simplified Dating Apps Work For You | `lp-01-art03.html` | — |
| `/instant-matchmaking-apps-lp-02-art01/` | Connecting Now: Instant Date Apps | `lp-02-art01.html` | `lp-02-art01-desktop.png` |
| `/instant-matchmaking-apps-lp-02-art02/` | Choosing Your Quick Connection App | `lp-02-art02.html` (arquivo grande, ~1,8 MB) | — |
| `/instant-matchmaking-apps-lp-02-art03/` | Quick Encounters: Making Apps Work For You | `lp-02-art03.html` | — |

Os artigos da LP-02 ainda têm “hookup” e “casual” no HTML. Esta rodada não reescreve esses posts. Os botões das LPs continuam apontando para eles.

## Veredito da copy (eCPM)

A copy nova anterior (guia genérico de “perfil curto” e “simplicidade”, sem nome de plano) era mais segura que o texto ao vivo, mas mais fraca para o lance contextual do AdX: não citava assinatura premium, Tinder Gold, Bumble Premium, Hinge, boost nem verificação. Não estava pronta se o objetivo é eCPM.

A copy desta rodada está pronta para colar nas duas LPs.

- O anúncio `content1` continua acima do quiz. O tempo até o quiz e até o primeiro slot não muda, porque o bloco do quiz e a ordem CSS não foram mexidos.
- O texto abaixo do quiz agora é o contexto que o bidder de dating nos EUA reconhece: plano grátis versus premium, o que Tinder Gold / Bumble Premium / Hinge+ e HingeX costumam vender (mais likes, ver quem curtiu, filtros, boost), e que o preço está na loja, não nesta página. Isso deve subir o lance contextual sem inventar preço.
- O Rewarded continua o popup que o plugin já abre. Não há frase pedindo clique no anúncio.
- “Hookup” saiu do miolo e das tags ocultas das duas LPs. O botão do quiz “Quick Casual Meetings” fica, porque o bloco do quiz é byte a byte o de produção.
- Os artigos de destino não foram limpos. Publicar só as LPs melhora a página que concentra 49% e 40% das views. O funil inteiro só fica alinhado quando os `-art` forem reescritos numa rodada própria.
