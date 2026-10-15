import asyncio,json
from playwright.async_api import async_playwright
BLOCK=['doubleclick.net','googlesyndication.com','googletagservices.com','adservice.google','fundingchoicesmessages','googleadservices','viralnetwork']
U=['https://gifmoney.com/en-musa-wishdates-complete-guide/','https://gifmoney.com/en-wishdates-review-02/','https://gifmoney.com/en-solar-financing/',
'https://en.blovibe.com/lp08-hottest-new-casual-dating-apps/','https://us.credityup.com/best-easy-dating-apps-lp-01/','https://us.credityup.com/instant-matchmaking-apps-lp-02/',
'https://de.credityup.com/digitale-romanzen-liebe-finden-lp-01/','https://de.credityup.com/apps-leichte-dates-unkomplizierte-treffen-lp-02-art01/']
JS="""()=>{const root=document.querySelector('.entry-content')||document.querySelector('article')||document.body;
const words=(root.innerText||'').split(/\\s+/).filter(Boolean).length;
const walker=document.createTreeWalker(root,NodeFilter.SHOW_TEXT);let w=0;const pos=new Map();
const ads=[...document.querySelectorAll('winupads,ins.adsbygoogle,[id^="div-gpt"],[data-wads-slot],[data-wads-path]')];
const all=[...root.querySelectorAll('*')];
const res=ads.map(a=>{let cnt=0;const tw=document.createTreeWalker(root,NodeFilter.SHOW_TEXT);let n;while(n=tw.nextNode()){if(a.compareDocumentPosition(n)&Node.DOCUMENT_POSITION_PRECEDING)cnt+=n.textContent.split(/\\s+/).filter(Boolean).length;else break}
 const r=a.getBoundingClientRect();return {tag:a.getAttribute('tag')||(a.hasAttribute('rewarded')?'rewarded['+a.getAttribute('trigger')+']':a.tagName),lazy:a.getAttribute('lazy')||a.closest('[data-winup-lazy]')?'lazy':'',inContent:root.contains(a),wordsBefore:cnt,y:Math.round(r.top+scrollY)}});
return {words,p:root.querySelectorAll('p').length,h2:root.querySelectorAll('h2').length,h3:root.querySelectorAll('h3').length,height:document.documentElement.scrollHeight,imgs:root.querySelectorAll('img').length,ads:res,links:[...root.querySelectorAll('a')].filter(a=>a.host===location.host).length}}"""
async def main():
  out={}
  async with async_playwright() as p:
    b=await p.chromium.launch(); c=await b.new_context(**p.devices['iPhone 13'])
    async def r(rt):
      if any(x in rt.request.url for x in BLOCK): return await rt.abort()
      await rt.continue_()
    await c.route('**/*',r); pg=await c.new_page()
    for u in U:
      try:
        await pg.goto(u,wait_until='load',timeout=60000); await pg.wait_for_timeout(3000)
        out[u]=await pg.evaluate(JS)
      except Exception as e: out[u]={'err':str(e)[:100]}
      print(u, json.dumps(out[u])[:900])
    await b.close()
  json.dump(out,open('measure.json','w'),indent=1)
asyncio.run(main())
