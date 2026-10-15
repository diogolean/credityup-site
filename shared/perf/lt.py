import asyncio,time
from playwright.async_api import async_playwright
BLOCK=['doubleclick.net','googlesyndication.com','googletagservices.com','adservice.google','fundingchoicesmessages','googleadservices']
async def main():
  async with async_playwright() as p:
    b=await p.chromium.launch(); c=await b.new_context(**p.devices['iPhone 13'])
    async def r(route):
      u=route.request.url
      if any(x in u for x in BLOCK) or 'viralnetwork' in u: await route.abort()
      else: await route.continue_()
    await c.route('**/*',r); pg=await c.new_page(); t=time.time()
    await pg.goto('https://us.credityup.com/best-easy-dating-apps-lp-01/',wait_until='domcontentloaded')
    print('dcl',round(time.time()-t,2)); await pg.wait_for_timeout(600); await pg.screenshot(path='loader_on.png')
    for i in range(40):
      if not await pg.query_selector('#cy-loader'): print('loader gone',round(time.time()-t,2)); break
      await pg.wait_for_timeout(100)
    await pg.wait_for_timeout(5000); await pg.screenshot(path='after.png')
    print('popup', await pg.locator('text=WATCH THE AD').count())
    await b.close()
asyncio.run(main())
