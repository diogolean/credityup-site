# Uso: python3 shared/tests/test_wads_defer.py [v1|v2]
# v1f = mesmo caminho do v2 (fetch+fulfill) sem reescrever: controle justo de tempo.
# v2 = reescreve o HTML LOCALMENTE (igual ao mu-plugin da opção 3) — não altera o site.
# TODOS os anúncios reais bloqueados; nunca clica em anúncios.
import asyncio, time, sys
from playwright.async_api import async_playwright
URL='https://us.credityup.com/instant-matchmaking-apps-lp-02/'
BLOCK=['doubleclick.net','googlesyndication.com','googletagservices.com','adservice.google','fundingchoicesmessages','googleadservices','viralnetwork']
SYNC_GPT='<script src="https://securepubads.g.doubleclick.net/tag/js/gpt.js"></script>'
SYNC_WADS='<script src="https://api.winup.network/api/v1/public/kvp/wads.js?v=1"></script>'
V2_GPT='<link rel="preconnect" href="https://api.winup.network" crossorigin><link rel="preconnect" href="https://erp.winup.network" crossorigin><script async src="https://securepubads.g.doubleclick.net/tag/js/gpt.js"></script>'
V2_WADS='<script defer src="https://api.winup.network/api/v1/public/kvp/wads.js?v=1"></script>'
MODE=sys.argv[1] if len(sys.argv)>1 else 'v2'
async def main():
  ev={}; t0=None
  def mark(k):
    if k not in ev: ev[k]=round(time.time()-t0,2)
  async with async_playwright() as p:
    b=await p.chromium.launch(); c=await b.new_context(**p.devices['iPhone 13'])
    async def r(route):
      u=route.request.url
      if any(x in u for x in BLOCK): return await route.abort()
      if MODE in ('v2','v1f') and route.request.resource_type=='document' and u.split('?')[0]==URL:
        resp=await route.fetch(); h=await resp.text()
        assert SYNC_WADS in h, 'tag original não encontrado'
        h=h if MODE=='v1f' else h.replace(SYNC_GPT,V2_GPT).replace(SYNC_WADS,V2_WADS); mark('html_rewritten')
        return await route.fulfill(response=resp, body=h)
      await route.continue_()
    await c.route('**/*',r)
    pg=await c.new_page()
    def onreq(q):
      u=q.url
      if 'kvp/wads.js' in u: mark('wads_req')
      if 'erp.winup.network/api/public/ad-slots' in u: mark('ad_slots_call')
      if 'winup-ad-engine/assets/js/ad-engine.js' in u: mark('ad_engine_req')
    def onresp(s):
      if 'kvp/wads.js' in s.url: mark('wads_loaded')
    pg.on('request',onreq); pg.on('response',onresp)
    t0=time.time()
    await pg.goto(URL,wait_until='domcontentloaded'); mark('dcl')
    ok=await pg.evaluate("()=>!!window.wadsConfig")
    for i in range(60):
      if not await pg.query_selector('#cy-loader'): mark('loader_gone'); break
      await pg.wait_for_timeout(100)
    await pg.wait_for_timeout(7000)
    fcp=await pg.evaluate("()=>{const e=performance.getEntriesByName('first-contentful-paint')[0];return e?Math.round(e.startTime):null}")
    popup=await pg.locator('text=/WATCH THE AD|ASSISTIR|VER ANÚNCIO/i').count()
    await pg.screenshot(path=f'/workspace/credityup-sites/shared/tests/{MODE}_after.png')
    await b.close()
  print(MODE, ev, 'FCP_ms',fcp, 'wadsConfig',ok, 'popup',popup)
  need=['wads_loaded','ad_engine_req','ad_slots_call','loader_gone']
  miss=[k for k in need if k not in ev]+([] if popup else ['popup'])
  print('PASS' if not miss else 'FAIL missing: '+','.join(miss))
asyncio.run(main())
