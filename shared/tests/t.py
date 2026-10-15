import sys,re,json
from playwright.sync_api import sync_playwright
URL="https://us.credityup.com/best-easy-dating-apps-lp-01/"
BAD=re.compile(r"doubleclick\.net|googlesyndication\.com|googletagservices\.com|googleadservices\.com|adservice\.google\.|viralnetwork\.com\.br|imasdk\.googleapis")
mode=sys.argv[1]
log=[]
def L(*a):
    s=" ".join(str(x) for x in a); print(s,flush=True)
with sync_playwright() as p:
    b=p.chromium.launch()
    kw=p.devices["iPhone 13"] if mode=="mobile" else {"viewport":{"width":1366,"height":900}}
    ctx=b.new_context(**kw, locale="en-US")
    def rt(route):
        u=route.request.url
        if BAD.search(u):
            L("ABORT",route.request.resource_type,u[:200]); route.abort()
        else: route.continue_()
    ctx.route("**/*",rt)
    pg=ctx.new_page()
    pg.on("framenavigated",lambda f: f==pg.main_frame and L("NAV",f.url))
    pg.on("console",lambda m: m.type in("error","warning") and L("CONSOLE",m.type,m.text[:200]))
    pg.on("pageerror",lambda e: L("PAGEERROR",str(e)[:200]))
    pg.goto(URL,wait_until="domcontentloaded",timeout=60000)
    btn=pg.get_by_text("WATCH THE AD AND CONTINUE")
    try:
        btn.first.wait_for(state="visible",timeout=15000); L("POPUP visible")
    except Exception as e:
        L("POPUP not visible",e.__class__.__name__)
    info=pg.evaluate("""()=>{const o={};const h=document.documentElement.outerHTML;
      o.hasFix=/redirect_url/.test(h);o.rot=(h.match(/viralnetwork[^"'\\s<]*/g)||[]).slice(0,5);
      o.ls=Object.keys(localStorage);o.jsonParsePatched=JSON.parse.toString().slice(0,120);
      for(const k of Object.keys(window)){try{const v=window[k];if(v&&typeof v=='object'&&'redirect_url' in v)o['win_'+k]=v.redirect_url}catch(e){}}
      return o}""")
    L("EVAL",json.dumps(info))
    pg.screenshot(path=f"/workspace/rw/test_before{'' if mode=='mobile' else '_desktop'}.png")
    if btn.count() and btn.first.is_visible():
        btn.first.click(); L("CLICKED"); pg.wait_for_timeout(10000)
    L("FINAL",pg.url)
    pg.screenshot(path=f"/workspace/rw/test_after{'' if mode=='mobile' else '_desktop'}.png")
    b.close()
