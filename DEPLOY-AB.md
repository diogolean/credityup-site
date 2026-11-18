# Deploy do teste A/B — ainda manual, sem publicar por este PR

Variante A é o site de hoje. Variante B acrescenta dois rewarded opt-in. Não desliga o popup RAM na entrada, o interstitial, `content1`/`content2` nem o Offerwall.

## Onde colar

1. Backup do snippet HFCM #2 (o quiz) antes de editar.
2. HFCM #2 continua **Footer / Specific Pages**, páginas ID 89 (LP-01) e ID 100 (LP-02). Substituir o código por `shared/snippets/02_quiz_en.html`. A única mudança obrigatória nesta rodada, se o #2 publicado ainda dispara `Lead` no resultado, é o evento do resultado: `CompleteRegistration` com `content_name: Quiz_Result_Shown_EN`. O `Lead` do rewarded (snippet #1) fica. Não criar um segundo snippet de quiz.
3. Novo snippet HFCM #15, nome `CY A/B rewarded opt-in`, arquivo `shared/snippets/15_cy_ab_reward.html`. **Footer, Site Wide, Active.** Não substituir #1, #3, #4 nem o loader.

Não mudar o HTML dos botões `.pf-dbtn`. A variante B só intercepta o clique no browser. As URLs permanecem as do post.

## O que a variante B faz

- No fim do quiz, o e-mail opcional (o mesmo formulário, com consentimento, sem o endereço na URL) aparece primeiro. Abaixo, “Watch a short ad to see your recommended apps?” com Skip. Skip abre o artigo daquela LP. Watch pede o rewarded só se o slot estiver livre e fora da pausa de 60s. Se o popup RAM ainda estiver aberto, ou o cooldown `ram_reward_cd_*` / `cy_reward_cd` tiver menos de 60s, vai direto ao artigo. Se o anúncio não preencher em cerca de 4s, também vai ao artigo.
- Nos botões `.pf-dbtn` dos artigos: “Watch a short ad to open this guide?” com a mesma regra. Skip e falha de fill abrem a mesma URL do botão.
- Não cria outro interstitial.

## Como ler no GAM

1. Inventário → Key-values → nova chave `cy_ab`, tipo dinâmico ou valores predefinidos `A` e `B`. O snippet chama `googletag.pubads().setTargeting('cy_ab', 'A'|'B')` antes do leilão.
2. Relatórios → arrastar a dimensão Key-values (ou criar um relatório com filtro `cy_ab=A` e `cy_ab=B`) e comparar receita, impressões e eCPM. O valor também entra nos parâmetros dos eventos do pixel disparados depois que o snippet #15 carrega (`cy_ab` no objeto do `fbq`). O `PageView` que já saiu antes do rodapé pode não ter a chave; os eventos seguintes (quiz, rewarded, offerwall) têm.
3. O cookie `cy_ab` e o `localStorage` `cy_ab` duram 30 dias. O mesmo visitante não troca de variante.

## Não fazer

- Não esvaziar os botões do popup RAM nas páginas de namoro. Isso desligaria o rewarded da entrada.
- Não adicionar um `<winupads rewarded>` fixo no HTML das LPs. No load ele disputaria o único slot rewarded do GPT com o RAM. O snippet só chama `defineOutOfPageSlot` quando o visitante aceita e o slot está livre.
- Não publicar segredo nenhum. Esta mudança não pede chave Firebase.
