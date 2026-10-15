import asyncio,sys,json
from playwright.async_api import async_playwright
BLOCK=['doubleclick.net','googlesyndication.com','googletagservices.com','adservice.google','fundingchoicesmessages','googleadservices','viralnetwork']
INIT="""window.__ls=[];new PerformanceObserver(l=>l.getEntries().forEach(e=>{if(e.hadRecentInput)return;__ls.push({t:Math.round(e.startTime),v:+e.value.toFixed(4),src:(e.sources||[]).map(s=>{const n=s.node;return n?(n.tagName+'#'+(n.id||'')+'.'+String(n.className||'').slice(0,40)+' '+JSON.stringify([s.previousRect.y,s.previousRect.height,s.currentRect.y,s.currentRect.height])):'?'})})})).observe({type:'layout-shift',buffered:true});"""
async def main(u,rw=None):
  async with async_playwright() as p:
    b=await p.chromium.launch(); c=await b.new_context(viewport={'width':412,'height':823},device_scale_factor=2.6,is_mobile=True,has_touch=True,user_agent='Mozilla/5.0 (Linux; Android 11; moto g power (2022)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Mobile Safari/537.36')
    async def r(rt):
      q=rt.request
      if any(x in q.url for x in BLOCK): return await rt.abort()
      if rw and q.resource_type=='document':
        resp=await rt.fetch(); return await rt.fulfill(response=resp,body=rw(await resp.text()))
      await rt.continue_()
    await c.route('**/*',r); await c.add_init_script(INIT); pg=await c.new_page()
    await pg.goto(u,wait_until='load'); await pg.wait_for_timeout(8000)
    ls=await pg.evaluate('__ls'); print(u[-30:],'CLS',round(sum(x['v'] for x in ls),3))
    for x in ls: print(' ',x)
    await b.close()
if __name__=='__main__':
  for u in sys.argv[1:]: asyncio.run(main(u))
