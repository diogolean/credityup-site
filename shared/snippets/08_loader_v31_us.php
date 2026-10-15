<?php
/**
 * Code Snippets: CY loader v3.1 (US): neon, opaque, 2.5s cap from navigation start.
 * Settings: "Only run on site front-end". Prints first thing in <head> (wp_head priority 1).
 */
add_action('wp_head', function () {
    if (is_admin() || wp_doing_ajax() || is_feed() || (defined('REST_REQUEST') && REST_REQUEST)) return;
    ?>
<style id="cy-loader-css">
#cy-loader{position:fixed;inset:0;z-index:2147483000;display:flex;align-items:center;justify-content:center;flex-direction:column;background:radial-gradient(110% 80% at 50% 42%,#2c0e34,#09050d);transition:opacity .4s ease;contain:strict}
#cy-loader.cy-hide{opacity:0;pointer-events:none}
#cy-loader .n{position:relative;width:80px;height:80px}
#cy-loader .n svg{position:absolute;inset:0;width:100%;height:100%;overflow:visible;filter:drop-shadow(0 0 5px #d14cff) drop-shadow(0 0 12px rgba(255,79,216,.5));will-change:transform}
#cy-loader circle{fill:none;stroke:#ff4fd8;stroke-linecap:round}
#cy-loader .r1 circle{stroke-dasharray:150 140}
#cy-loader .r2 circle{stroke:#b07cff;stroke-dasharray:80 121}
#cy-loader .r1{animation:cyS 1.6s linear infinite}
#cy-loader .r2{animation:cyS 2.2s linear infinite reverse}
#cy-loader .c{position:absolute;top:50%;left:50%;width:6px;height:6px;margin:-3px;border-radius:50%;background:#ff7be6;box-shadow:0 0 10px #ff4fd8;will-change:transform,opacity;animation:cyP 1.6s ease-in-out infinite}
#cy-loader p{margin:18px 0 0;font:300 9.5px/1 -apple-system,"Helvetica Neue",Helvetica,Arial,sans-serif;letter-spacing:.38em;text-transform:uppercase;color:#f3d9ff;text-shadow:0 0 8px rgba(214,90,255,.7);animation:cyT 1.6s ease-in-out infinite}
@keyframes cyS{to{transform:rotate(360deg)}}
@keyframes cyP{0%,100%{transform:scale(.7);opacity:.5}50%{transform:scale(1.25);opacity:1}}
@keyframes cyT{0%,100%{opacity:.55}50%{opacity:1}}
@media (prefers-reduced-motion:reduce){#cy-loader *{animation:none!important}}
</style>
<script>
(function(){
  var CAP=2500, FLOOR=400, now=function(){return performance.now()}, t=now(), done=false, el;
  if(t>=CAP-FLOOR) return; // already late: don't show at all
  el=document.createElement('div'); el.id='cy-loader'; el.setAttribute('aria-hidden','true');
  el.innerHTML='<div class="n"><svg class="r1" viewBox="0 0 120 120"><circle cx="60" cy="60" r="46" stroke-width="2.2"/></svg><svg class="r2" viewBox="0 0 120 120"><circle cx="60" cy="60" r="32" stroke-width="1.8"/></svg><i class="c"></i></div><p>Curating matches</p>';
  // inserts NOW (body or <html>); doesn't wait for DOMContentLoaded and blocks nothing
  (document.body||document.documentElement).appendChild(el);
  function hide(){ if(done) return; var w=FLOOR-(now()-t); if(w>0&&now()+w<CAP){setTimeout(hide,w);return}
    done=true; el.classList.add('cy-hide'); setTimeout(function(){el.remove()},420); }
  setTimeout(hide, Math.max(0, CAP-now())); // hard cap: 2.5s from navigation start
  window.googletag=window.googletag||{cmd:[]};
  googletag.cmd.push(function(){ googletag.pubads().addEventListener('slotRenderEnded',hide); });
})();
</script>

    <?php
}, 1);
