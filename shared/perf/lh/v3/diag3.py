import asyncio,sys; sys.path.insert(0,'/workspace/perf/v3'); from rw import rewrite
sys.path.insert(0,'/workspace/perf/v2'); import diag
from playwright.async_api import async_playwright
async def main(u,mode):
  async with async_playwright() as p:
    b=await p.chromium.launch(); c=await b.new_context(**p.devices['iPhone 13'])
    async def r(rt):
      q=rt.request
      if any(x in q.url for x in diag.BLOCK): return await rt.abort()
      if mode=='v3' and q.resource_type=='document':
        resp=await rt.fetch(); return await rt.fulfill(response=resp,body=rewrite(await resp.text()))
      await rt.continue_()
    await c.route('**/*',r); await c.add_init_script(diag.INIT); pg=await c.new_page()
    shot=False
    async def snap():
      for i in range(60):
        try: ok=await pg.query_selector('#cy-loader:not(.cy-hide)')
        except Exception: ok=None
        if ok:
          await pg.wait_for_timeout(700); await pg.screenshot(path=f'/workspace/perf/v3/live_{mode}_{u.rstrip("/")[-5:]}.png'); return
        await pg.wait_for_timeout(50)
    t=asyncio.create_task(snap())
    await pg.goto(u,wait_until='load'); await t; await pg.wait_for_timeout(6000)
    ev=[e for e in await pg.evaluate('__ev') if e[1] in('add','rm','attr','DCL','load','paint','largest-contentful-paint') and 'ram' not in str(e[2])]
    print(mode,u[-6:],ev)
    print('popup',await pg.locator('text=/WATCH THE AD/i').count())
    await b.close()
for m in ['v2','v3']: asyncio.run(main(sys.argv[1],m))
