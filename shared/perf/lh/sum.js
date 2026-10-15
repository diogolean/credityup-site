const fs=require('fs');for(const f of fs.readdirSync('.').filter(f=>f.endsWith('.json')).sort()){try{const r=JSON.parse(fs.readFileSync(f));const a=r.audits;const g=k=>a[k]?.displayValue||'-';
let lcpEl=a['largest-contentful-paint-element']?.details?.items?.[0]?.items?.[0]?.node?.snippet||'';
const cls=(a['layout-shifts']?.details?.items||[]).slice(0,2).map(i=>(i.node?.snippet||'').slice(0,60)+' '+(i.score||0).toFixed(3)).join(' | ');
const tbt=(a['bootup-time']?.details?.items||[]).slice(0,3).map(i=>i.url.replace(/https?:\/\//,'').slice(0,50)+' '+Math.round(i.scripting)+'ms').join(' | ');
const rb=(a['render-blocking-resources']?.details?.items||[]).map(i=>i.url.split('/').pop().slice(0,30)+' '+Math.round(i.totalBytes/1024)+'K').join(', ');
console.log(`${f} score=${Math.round(r.categories.performance.score*100)} FCP=${g('first-contentful-paint')} LCP=${g('largest-contentful-paint')} TBT=${g('total-blocking-time')} CLS=${g('cumulative-layout-shift')} SI=${g('speed-index')} TTFB=${g('server-response-time')} bytes=${g('total-byte-weight')} err=${r.runtimeError?.code||''}\n  LCPel: ${lcpEl.slice(0,110)}\n  CLS: ${cls}\n  TBT: ${tbt}\n  RB: ${rb}`)}catch(e){console.log(f,e.message)}}
