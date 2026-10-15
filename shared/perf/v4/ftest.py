# Funcional v4: HTML reescrito localmente pelo PHP real (php filt.php). Anúncios bloqueados, sem cliques (só scroll).
import asyncio,time,sys,subprocess
from playwright.async_api import async_playwright
BLOCK=['doubleclick.net','googlesyndication.com','googletagservices.com','adservice.google','fundingchoicesmessages','googleadservices','viralnetwork']
INIT="""(()=>{window.__t={};const M=(k)=>{if(!(k in __t))__t[k]=Math.round(performance.now())};document.addEventListener("DOMContentLoaded",()=>M("dcl"));new MutationObserver(ms=>{for(const m of ms){if(m.type==="attributes"&&m.target.id==="cy-loader"&&/cy-hide/.test(m.target.className))M("loader_hide");m.addedNodes.forEach(n=>{if(n.id==="cy-loader")M("loader_add")})}}).observe(document,{subtree:true,childList:true,attributes:true,attributeFilter:["class"]});["gtm.js","fbevents","gtag/js","ad-slots"].forEach(k=>{});})();(()=>{const P=JSON.parse;window.__rwo=[];JSON.parse=function(t,r){const v=P.apply(this,arguments);try{if(Array.isArray(v))v.forEach(b=>{if(b&&typeof b.redirect_url==='string')window.__rwo.push(b)})}catch(e){}return v};})();"""
def php(h,host): return subprocess.run(['php','/workspace/perf/v4/filt.php',host],input=h.encode(),capture_output=True).stdout.decode()
async def run(url,mode,interact_at):
  host=url.split('/')[2]; ev={}; t0=time.time()
  def mark(k):
    if k not in ev: ev[k]=round(time.time()-t0,2)
  async with async_playwright() as p:
    b=await p.chromium.launch(); c=await b.new_context(**p.devices['iPhone 13'])
    async def r(rt):
      q=rt.request
      if any(x in q.url for x in BLOCK): return await rt.abort()
      if mode in ('v4','v31f') and q.resource_type=='document' and q.url.split('?')[0]==url:
        resp=await rt.fetch(); t=await resp.text(); return await rt.fulfill(response=resp,body=t if mode=='v31f' else php(t,host))
      await rt.continue_()
    await c.route('**/*',r); await c.add_init_script(INIT); pg=await c.new_page()
    def onreq(q):
      u=q.url
      for k,pat in [('ad_slots','erp.winup.network/api/public/ad-slots'),('ad_engine','winup-ad-engine/assets/js/ad-engine.js'),('gtm_js','googletagmanager.com/gtm.js'),('gtag_js','googletagmanager.com/gtag/js'),('fbevents','fbevents.js'),('ga_collect','/g/collect'),('fb_tr','facebook.com/tr')]:
        if pat in u: mark(k)
    pg.on('request',onreq); pg.on('response',lambda s: mark('wads') if 'kvp/wads.js' in s.url and s.ok else None)
    t0=time.time(); await pg.goto(url,wait_until='commit')
    gone=None
    for i in range(int(interact_at*10)):
      await pg.wait_for_timeout(100)
      try:
        if gone is None and await pg.evaluate("()=>document.readyState!=='loading'&&!document.querySelector('#cy-loader:not(.cy-hide)')"): gone=round(time.time()-t0,2)
      except Exception: pass
    ev['interact']=round(time.time()-t0,2)
    await pg.evaluate("()=>window.scrollBy(0,300)"); await pg.mouse.wheel(0,200)
    await pg.wait_for_timeout(7000)
    popup=await pg.locator('text=/WATCH THE AD|ANZEIGE ANSEHEN|ANSEHEN/i').count()
    T=await pg.evaluate('__t'); T.update({e['name'].split('/')[-1][:20]:round(e['startTime']) for e in await pg.evaluate("()=>performance.getEntriesByType('resource').filter(e=>/ad-slots|gtm\\.js|fbevents|gtag\\/js|wads\\.js/.test(e.name)).map(e=>({name:e.name.split('?')[0],startTime:e.startTime}))")}); print('  page-ms',T)
    rw=await pg.evaluate("()=>window.__rwo.map(b=>b.redirect_url)")
    dl=await pg.evaluate("()=>(window.dataLayer||[]).map(x=>x&&x.event||(x&&x[0])||'').filter(Boolean).slice(0,12)")
    lh=await pg.evaluate("()=>{const e=performance.getEntriesByType('navigation')[0];return 0}")
    await b.close()
  fix_ok=bool(rw) and all(x==url for x in rw)
  print(mode,url[-28:],ev,'loader_gone~',gone,'popup',popup,'fix',fix_ok,'dataLayer',list(dict.fromkeys(map(str,dl))))
  return ev,popup,fix_ok,gone
IA=float(sys.argv[2]); [asyncio.run(run(u,sys.argv[1],IA)) for u in sys.argv[3:]]
