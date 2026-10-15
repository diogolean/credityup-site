import asyncio,sys
from playwright.async_api import async_playwright
BLOCK=['doubleclick.net','googlesyndication.com','googletagservices.com','adservice.google','fundingchoicesmessages','googleadservices','viralnetwork']
INIT="""window.__ev=[];const L=(e,x)=>__ev.push([Math.round(performance.now()),e,x||'']);
new MutationObserver(ms=>{for(const m of ms){m.addedNodes.forEach(n=>{if(n.id&&/loader|preload|sl-|ram/i.test(n.id))L('add',n.id)});m.removedNodes.forEach(n=>{if(n.id&&/loader|preload|sl-|ram/i.test(n.id))L('rm',n.id)});if(m.type==='attributes'&&m.target.id&&/loader|preload/i.test(m.target.id))L('attr',m.target.id+' '+m.target.className)}}).observe(document,{subtree:true,childList:true,attributes:true,attributeFilter:['class','style']});
document.addEventListener('DOMContentLoaded',()=>L('DCL'));addEventListener('load',()=>L('load'));
new PerformanceObserver(l=>l.getEntries().forEach(e=>L(e.entryType,(e.name||'')+' '+(e.element?e.element.tagName+'.'+e.element.className:'')+' '+Math.round(e.startTime)))).observe({type:'largest-contentful-paint',buffered:true});
new PerformanceObserver(l=>l.getEntries().forEach(e=>L('paint',e.name+' '+Math.round(e.startTime)))).observe({type:'paint',buffered:true});"""
async def main(u):
  async with async_playwright() as p:
    b=await p.chromium.launch(); c=await b.new_context(**p.devices['iPhone 13'])
    async def r(rt):
      if any(x in rt.request.url for x in BLOCK): return await rt.abort()
      await rt.continue_()
    await c.route('**/*',r); await c.add_init_script(INIT); pg=await c.new_page()
    pg.on('console',lambda m: print('  console',m.text[:150]) if 'RAM' in m.text else None)
    await pg.goto(u+'?ram_debug=1',wait_until='load'); await pg.wait_for_timeout(8000)
    for e in await pg.evaluate('__ev'): print(e)
    print(await pg.evaluate("()=>{const n=performance.getEntriesByType('navigation')[0];return {ttfb:Math.round(n.responseStart),dcl:Math.round(n.domContentLoadedEventEnd),load:Math.round(n.loadEventEnd)}}"))
    await b.close()
if __name__=="__main__": asyncio.run(main(sys.argv[1]))
