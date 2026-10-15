# Teste funcional nas páginas AO VIVO (sem reescrever HTML). Anúncios reais bloqueados, sem cliques.
import asyncio, time, sys, json
from playwright.async_api import async_playwright
BLOCK=['doubleclick.net','googlesyndication.com','googletagservices.com','adservice.google','fundingchoicesmessages','googleadservices','viralnetwork']
INIT="""(()=>{const P=JSON.parse;window.__rwo=[];JSON.parse=function(t,r){const v=P.apply(this,arguments);try{if(Array.isArray(v))v.forEach(b=>{if(b&&typeof b.redirect_url==='string')window.__rwo.push(b)})}catch(e){}return v};})();"""
# guarda referências: o fix (#4) envolve este wrapper e altera os MESMOS objetos depois; lemos no fim.
async def run(url):
  ev={}; t0=time.time()
  def mark(k):
    if k not in ev: ev[k]=round(time.time()-t0,2)
  async with async_playwright() as p:
    b=await p.chromium.launch(); c=await b.new_context(**p.devices['iPhone 13'])
    async def r(route):
      if any(x in route.request.url for x in BLOCK): return await route.abort()
      await route.continue_()
    await c.route('**/*',r); await c.add_init_script(INIT)
    pg=await c.new_page()
    def onreq(q):
      u=q.url
      if 'erp.winup.network/api/public/ad-slots' in u: mark('ad_slots_call')
      if 'winup-ad-engine/assets/js/ad-engine.js' in u: mark('ad_engine_req')
    pg.on('request',onreq); pg.on('response',lambda s: mark('wads_loaded') if 'kvp/wads.js' in s.url and s.ok else None)
    t0=time.time(); await pg.goto(url,wait_until='domcontentloaded'); mark('dcl')
    html=await pg.content()
    tags={'gpt_async':'async' in (await pg.evaluate("()=>{const s=document.querySelector('script[src*=\"gpt.js\"]');return s?s.outerHTML:''}")),
          'wads_defer':await pg.evaluate("()=>{const s=document.querySelector('script[src*=\"wads.js\"]');return !!(s&&s.defer)}"),
          'wads_count':await pg.evaluate("()=>document.querySelectorAll('script[src*=\"wads.js\"]').length")}
    for i in range(80):
      if not await pg.query_selector('#cy-loader'): mark('loader_gone'); break
      await pg.wait_for_timeout(100)
    await pg.wait_for_timeout(7000)
    popup=await pg.locator('text=/WATCH THE AD/i').count()
    rw=await pg.evaluate("()=>window.__rwo.map(b=>b.redirect_url)")
    await pg.screenshot(path=f'/workspace/perf/v2/live_{url.rstrip("/").split("/")[-1]}.png')
    await b.close()
  base=url.split('#')[0]
  fix_ok=bool(rw) and all(x==base for x in rw)
  print(url, ev, tags, 'popup',popup, 'redirect_urls',sorted(set(rw)))
  miss=[k for k in ['wads_loaded','ad_engine_req','ad_slots_call','loader_gone'] if k not in ev]
  if not popup: miss.append('popup')
  if not fix_ok: miss.append('same_page_fix')
  if not (tags['gpt_async'] and tags['wads_defer'] and tags['wads_count']==1): miss.append('tags')
  print('PASS' if not miss else 'FAIL: '+','.join(miss))
for u in sys.argv[1:]: asyncio.run(run(u))
