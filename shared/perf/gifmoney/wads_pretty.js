!(function () {
  var t = window.wadsConfig || null,
    e = [
      "utm_source",
      "utm_medium",
      "utm_campaign",
      "utm_term",
      "utm_content",
      "utm_creative",
    ].concat(["src_page", "src_funil", "src_msg", "src_type"]),
    a = "wads_utm";
  function o() {
    return (
      (window.googletag = window.googletag || { cmd: [] }),
      window.googletag
    );
  }
  function n(t, e) {
    o().cmd.push(function () {
      var a = {};
      for (var o in t) a[o] = [t[o]];
      if ((e && (a.id_post_wp = [String(e)]), Object.keys(a).length))
        if ("function" == typeof window.googletag.setConfig)
          window.googletag.setConfig({ targeting: a });
        else {
          var n = window.googletag.pubads();
          for (var i in a) n.setTargeting(i, a[i]);
        }
    });
  }
  function i(t, e, a, o) {
    var n = {};
    if (
      (a && (n.id_post_wp = String(a)),
      Object.keys(e).length || Object.keys(n).length)
    ) {
      var i = {};
      for (var r in e)
        /^utm_/.test(r) ? (i[r.replace(/^utm_/, "")] = e[r]) : (n[r] = e[r]);
      try {
        fetch(o || "https://api.winup.network/api/v1/public/kvp/track", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            network_code: String(t),
            domain: window.location.hostname,
            utm: i,
            kv: n,
          }),
          keepalive: !0,
          credentials: "omit",
        }).catch(function () {});
      } catch (t) {}
    }
  }
  function r() {
    return (window.innerWidth || document.documentElement.clientWidth || 0) <
      768
      ? "mobile"
      : "web";
  }
  function s(t) {
    var e = [];
    if (!t) return e;
    for (var a = String(t).split(","), o = 0; o < a.length; o++) {
      var n = /^\s*(\d+)\s*[xX]\s*(\d+)\s*$/.exec(a[o]);
      n && e.push([parseInt(n[1], 10), parseInt(n[2], 10)]);
    }
    return e;
  }
  function l(t, e) {
    return t.hasAttribute(e)
      ? "bottom" === (t.getAttribute(e) || "").toLowerCase().trim()
        ? "bottom"
        : "top"
      : "";
  }
  function d(t) {
    var e = t.tagName.toLowerCase(),
      a = "winupads" === e || "winup-ads" === e,
      o = a
        ? t.getAttribute("tag") || t.getAttribute("slot") || ""
        : t.getAttribute("data-wads") || "",
      n = "/" === o.charAt(0) ? o.replace(/\s+/g, "") : "";
    return {
      posicao: n ? "" : o.toLowerCase().replace(/[^a-z0-9_]/g, ""),
      caminho: n,
      tamanhos: s(
        a ? t.getAttribute("sizes") : t.getAttribute("data-wads-sizes"),
      ),
      ancora: a ? l(t, "anchor") : "",
      fixa: a ? l(t, "fixed") : "",
      lazy: a
        ? t.hasAttribute("lazy") && "false" !== t.getAttribute("lazy")
        : "1" === t.getAttribute("data-wads-lazy"),
      forcada:
        (a
          ? t.getAttribute("platform")
          : t.getAttribute("data-wads-platform")) || "",
      recompensa: !!a && t.hasAttribute("rewarded"),
      gatilho: a ? (t.getAttribute("trigger") || "").trim() : "",
      gatilhoTexto: a
        ? (t.getAttribute("trigger-text") || "").trim().toLowerCase()
        : "",
      avisoTexto: a ? (t.getAttribute("prompt") || "").trim() : "",
      avisoSim: a ? (t.getAttribute("prompt-yes") || "").trim() : "",
      avisoNao: a ? (t.getAttribute("prompt-no") || "").trim() : "",
      espera: a ? Math.max(0, Number(t.getAttribute("cooldown") || 0)) : 0,
      custom: a,
    };
  }
  function c(t, e) {
    var a = document.createElement("div");
    (a.setAttribute("data-wads-fixa", t),
      (a.style.cssText =
        "position:fixed;left:0;right:0;" +
        t +
        ":0;z-index:2147483646;display:flex;justify-content:center;align-items:center;background:rgba(255,255,255,.96);box-shadow:0 0 8px rgba(0,0,0,.15);min-height:" +
        (e || 50) +
        "px"));
    var o = document.createElement("div");
    (o.setAttribute("data-wads-alvo", "1"), a.appendChild(o));
    var n = document.createElement("button");
    return (
      (n.type = "button"),
      n.setAttribute("aria-label", "Fechar anuncio"),
      (n.textContent = "×"),
      (n.style.cssText =
        "position:absolute;" +
        t +
        ":100%;right:4px;border:0;background:rgba(0,0,0,.55);color:#fff;font-size:14px;line-height:1;padding:3px 7px;border-radius:3px 3px 0 0;cursor:pointer"),
      (n.onclick = function () {
        a.parentNode && a.parentNode.removeChild(a);
      }),
      a.appendChild(n),
      document.body.appendChild(a),
      o
    );
  }
  var u = [
      0.01, 0.05, 0.1, 0.25, 0.5, 0.75, 0.9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11,
      12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 24, 26, 28, 30, 32, 33, 34,
      36, 38, 40, 42, 44, 46, 48, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 99,
      100, 105, 110, 115, 120, 125, 130, 135, 140, 150, 160, 170, 180, 190, 200,
      210, 220, 230, 240, 250, 275, 300, 325, 350, 375, 400, 425, 450, 475, 500,
      525, 550, 575, 600, 625, 650, 675, 700, 725, 750, 775, 800, 825, 850, 875,
      900, 925, 950, 975, 1e3, 1025, 1050,
    ],
    g = "price",
    f = {},
    p = !1;
  function m(t) {
    return Number(t).toFixed(2);
  }
  var v = [],
    w = Date.now().toString(36) + Math.random().toString(36).slice(2, 10),
    h = null,
    b = !1;
  function y() {
    return (t && t.domain) || window.location.hostname.replace(/^www\./, "");
  }
  function x() {
    try {
      if ((h && (clearTimeout(h), (h = null)), !v.length)) return;
      for (
        var e = {}, a = ["utm_source", "utm_medium", "utm_campaign"], o = 0;
        o < a.length;
        o++
      )
        F && F[a[o]] && (e[a[o]] = F[a[o]]);
      var n = JSON.stringify({
        v: 1,
        domain: y(),
        pageview: w,
        dispositivo: r(),
        utm: e,
        post: q || null,
        pais: R || null,
        eventos: v,
      });
      v = [];
      var i =
        t && t.eventos
          ? t.eventos
          : (
              (t && t.manifest) ||
              "https://erp.winup.network/api/public/ad-slots"
            ).replace("ad-slots", "cascade-eventos");
      navigator.sendBeacon
        ? navigator.sendBeacon(i, new Blob([n], { type: "text/plain" }))
        : fetch(i, {
            method: "POST",
            body: n,
            keepalive: !0,
            credentials: "omit",
          }).catch(function () {});
    } catch (t) {}
  }
  function S(t) {
    for (var e = 0, a = 1 / 0, o = 0; o < u.length; o++) {
      var n = Math.abs(u[o] - t);
      n < a && ((a = n), (e = o));
    }
    return e;
  }
  function _(t, e, a) {
    var o = m(e),
      n = { targeting: {} };
    ((n.targeting[g] = [o]), a || (n.collapseDiv = "ON_NO_FILL"));
    try {
      "function" == typeof t.setConfig ? t.setConfig(n) : t.setTargeting(g, o);
    } catch (e) {
      try {
        t.setTargeting(g, o);
      } catch (t) {}
    }
  }
  function O() {
    p ||
      ((p = !0),
      b ||
        ((b = !0),
        window.addEventListener("pagehide", x),
        document.addEventListener("visibilitychange", function () {
          "hidden" === document.visibilityState && x();
        })),
      window.googletag
        .pubads()
        .addEventListener("slotRenderEnded", function (t) {
          var e = (function (t) {
            try {
              var e = t.slot.getSlotElementId && t.slot.getSlotElementId();
              if (e && f[e]) return f[e];
            } catch (t) {}
            try {
              var a = t.slot.getAdUnitPath && t.slot.getAdUnitPath();
              if (a && f[a]) return f[a];
            } catch (t) {}
            return null;
          })(t);
          if (
            e &&
            ((function (t, e) {
              try {
                if (!t.cfg || null == t.cfg.id) return;
                if (v.length >= 60) return;
                (v.push({
                  regra: t.cfg.id,
                  slot: t.chave || t.path,
                  price: t.preco,
                  tentativa: t.tentativa,
                  ok: !e.isEmpty,
                  li: null != e.lineItemId ? String(e.lineItemId) : null,
                  t: Date.now(),
                }),
                  h && clearTimeout(h),
                  (h = setTimeout(x, 2e3)));
              } catch (t) {}
            })(e, t),
            !e.ocupado && !e.pronto)
          )
            if (t.isEmpty)
              if (((e.tentativa += 1), e.tentativa > (e.cfg.retryLimit || 3)))
                e.pronto = !0;
              else {
                var a = (function (t, e, a) {
                  var o = S(t),
                    n = Math.min(o, S(a.lowerBound));
                  if (a.decayPct) {
                    for (
                      var i = 1 - a.decayPct / 100, r = u[o], s = 0;
                      s < e;
                      s++
                    )
                      if ((r *= i) < a.lowerBound) return null;
                    var l = (function (t) {
                      for (var e = 0, a = 0; a < u.length; a++)
                        u[a] <= t && (e = a);
                      return e;
                    })(r);
                    return l <= n && e > 1 ? null : u[Math.max(l, n)];
                  }
                  var d =
                    "auto" !== a.step && a.step
                      ? Math.max(1, Math.round(Number(a.step)))
                      : Math.max(1, Math.ceil((o - n) / (a.retryLimit || 3)));
                  return o - (e - 1) * d <= n
                    ? null
                    : u[Math.max(n, o - e * d)];
                })(e.cfg.base, e.tentativa, e.cfg);
                null != a
                  ? "hidden" !== document.visibilityState
                    ? ((e.ocupado = !0),
                      setTimeout(function () {
                        window.googletag.cmd.push(function () {
                          try {
                            (e.slot &&
                              (window.googletag.destroySlots([e.slot]),
                              (e.slot = null)),
                              (e.slot = (function (t, e) {
                                var a,
                                  o = window.googletag.pubads();
                                if (t.outOfPage) {
                                  if (!window.googletag.enums) return null;
                                  a = t.rewarded
                                    ? E()
                                    : window.googletag.defineOutOfPageSlot(
                                        t.path,
                                        window.googletag.enums.OutOfPageFormat
                                          .INTERSTITIAL,
                                      );
                                } else {
                                  var n = document.getElementById(t.id);
                                  (n && (n.innerHTML = ""),
                                    (a = window.googletag.defineSlot(
                                      t.path,
                                      t.sizes,
                                      t.id,
                                    )) &&
                                      a.defineSizeMapping(
                                        window.googletag
                                          .sizeMapping()
                                          .addSize(
                                            [0, 0],
                                            t.fluid
                                              ? ["fluid"].concat(t.sizes)
                                              : t.sizes,
                                          )
                                          .build(),
                                      ));
                                }
                                return a
                                  ? (t.rewarded || a.addService(o),
                                    (t.preco = m(e)),
                                    _(a, e, t.outOfPage),
                                    window.googletag.enableServices(),
                                    window.googletag.display(
                                      t.outOfPage ? a : t.id,
                                    ),
                                    a)
                                  : null;
                              })(e, a)),
                              e.slot || (e.pronto = !0));
                          } catch (t) {
                            e.pronto = !0;
                          }
                          e.ocupado = !1;
                        });
                      }, e.cfg.retryTimeout || 0))
                    : (e.tentativa -= 1)
                  : (e.pronto = !0);
              }
            else e.pronto = !0;
        }));
  }
  var A = null,
    L = !1;
  function T() {
    if (A) {
      var t = A.alvo;
      if (
        ((A.alvo = null),
        A.relogio && (clearTimeout(A.relogio), (A.relogio = null)),
        t)
      ) {
        t.setAttribute("data-wads-liberado", "1");
        try {
          t.click();
        } catch (t) {}
      }
    }
  }
  function z() {
    (A &&
      A.caixa &&
      A.caixa.parentNode &&
      A.caixa.parentNode.removeChild(A.caixa),
      A && (A.caixa = null));
  }
  function C() {
    var t = A.mostrar;
    ((A.mostrar = null),
      (A.relogio = setTimeout(function () {
        (T(), P());
      }, A.limite)));
    try {
      "function" == typeof t ? t() : (T(), P());
    } catch (t) {
      (T(), P());
    }
  }
  function E() {
    if (
      !A ||
      !window.googletag.enums ||
      !window.googletag.enums.OutOfPageFormat
    )
      return null;
    var t = window.googletag.enums.OutOfPageFormat.REWARDED;
    if (void 0 === t) return null;
    var e = window.googletag.defineOutOfPageSlot(A.path, t);
    return e
      ? (e.addService(window.googletag.pubads()),
        (A.slot = e),
        (A.mostrar = null),
        e)
      : null;
  }
  function P() {
    A &&
      (A.espera && (A.ate = Date.now() + 1e3 * A.espera),
      window.googletag.cmd.push(function () {
        try {
          if (
            (A.slot &&
              (window.googletag.destroySlots([A.slot]), (A.slot = null)),
            E())
          ) {
            var t = f[A.path];
            (t &&
              ((t.slot = A.slot),
              (t.tentativa = 0),
              (t.ocupado = !1),
              (t.pronto = !1),
              (t.preco = m(t.cfg.base)),
              _(A.slot, t.cfg.base, !0)),
              window.googletag.enableServices(),
              window.googletag.display(A.slot));
          }
        } catch (t) {}
      }));
  }
  function k(t, e) {
    if (A) return null;
    if (!e.gatilho) return null;
    A = {
      path: t.path,
      seletor: e.gatilho,
      texto: e.gatilhoTexto,
      texto_aviso: e.avisoTexto,
      texto_sim: e.avisoSim || "Assistir",
      texto_nao: e.avisoNao || "Agora não",
      espera: e.espera,
      limite: 8e3,
      slot: null,
      mostrar: null,
      alvo: null,
      caixa: null,
      relogio: null,
      ate: 0,
    };
    var a = E();
    return a
      ? ((function () {
          if (!L) {
            L = !0;
            var t = window.googletag.pubads();
            (t.addEventListener("rewardedSlotReady", function (t) {
              A && t.slot === A.slot && (A.mostrar = t.makeRewardedVisible);
            }),
              t.addEventListener("rewardedSlotGranted", function (t) {
                A && t.slot === A.slot && T();
              }),
              t.addEventListener("rewardedSlotClosed", function (t) {
                A && t.slot === A.slot && (T(), P());
              }),
              document.addEventListener(
                "click",
                function (t) {
                  var e = (function (t) {
                    if (!(A && A.seletor && t && t.closest)) return null;
                    var e;
                    try {
                      e = t.closest(A.seletor);
                    } catch (t) {
                      return null;
                    }
                    return e
                      ? A.texto &&
                        -1 ===
                          (function (t) {
                            return (t.textContent || "")
                              .replace(/\s+/g, " ")
                              .trim()
                              .toLowerCase();
                          })(e).indexOf(A.texto)
                        ? null
                        : e
                      : null;
                  })(t.target);
                  e &&
                    ("1" !== e.getAttribute("data-wads-liberado")
                      ? A.mostrar &&
                        ((A.ate && Date.now() < A.ate) ||
                          A.caixa ||
                          (t.preventDefault(),
                          t.stopImmediatePropagation(),
                          (A.alvo = e),
                          A.texto_aviso
                            ? (function () {
                                var t = document.createElement("div");
                                (t.setAttribute("data-wads-rewarded", "1"),
                                  (t.style.cssText =
                                    "position:fixed;top:0;right:0;bottom:0;left:0;z-index:2147483647;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.6);padding:16px"));
                                var e = document.createElement("div");
                                e.style.cssText =
                                  "background:#fff;color:#111;max-width:340px;width:100%;border-radius:12px;padding:22px 20px;text-align:center;box-shadow:0 12px 40px rgba(0,0,0,.35);font:15px/1.5 system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif";
                                var a = document.createElement("p");
                                ((a.style.cssText = "margin:0 0 18px"),
                                  (a.textContent = A.texto_aviso));
                                var o = document.createElement("div");
                                o.style.cssText =
                                  "display:flex;gap:8px;justify-content:center";
                                var n = document.createElement("button");
                                ((n.type = "button"),
                                  (n.textContent = A.texto_nao),
                                  (n.style.cssText =
                                    "flex:1;border:1px solid #d4d4d8;background:#fff;color:#3f3f46;border-radius:8px;padding:10px 14px;font-size:14px;cursor:pointer"));
                                var i = document.createElement("button");
                                ((i.type = "button"),
                                  (i.textContent = A.texto_sim),
                                  (i.style.cssText =
                                    "flex:1;border:0;background:#111;color:#fff;border-radius:8px;padding:10px 14px;font-size:14px;font-weight:600;cursor:pointer"));
                                var r = function () {
                                  (z(), T(), P());
                                };
                                ((n.onclick = r),
                                  (t.onclick = function (e) {
                                    e.target === t && r();
                                  }),
                                  document.addEventListener(
                                    "keydown",
                                    function t(e) {
                                      "Escape" === e.key &&
                                        A &&
                                        A.caixa &&
                                        (document.removeEventListener(
                                          "keydown",
                                          t,
                                          !0,
                                        ),
                                        r());
                                    },
                                    !0,
                                  ),
                                  (i.onclick = function () {
                                    (z(), C());
                                  }),
                                  o.appendChild(n),
                                  o.appendChild(i),
                                  e.appendChild(a),
                                  e.appendChild(o),
                                  t.appendChild(e),
                                  document.body.appendChild(t),
                                  (A.caixa = t));
                                try {
                                  i.focus();
                                } catch (t) {}
                              })()
                            : C()))
                      : e.removeAttribute("data-wads-liberado"));
                },
                !0,
              ));
          }
        })(),
        a)
      : ((A = null), null);
  }
  function I(t) {
    return t.condicoes && t.condicoes.length
      ? t.condicoes
      : [{ chave: t.utmKey, valor: t.utmValue }];
  }
  function N(t) {
    var e,
      a = "post_id" === (e = t.chave) ? q : "pais" === e ? R : (F || {})[e];
    return !!a && String(a).toLowerCase() === String(t.valor).toLowerCase();
  }
  function M(t) {
    var e = t.cascadesUtm;
    if (e && e.length)
      for (var a = 0; a < e.length; a++) {
        for (var o = I(e[a]), n = !0, i = 0; i < o.length; i++)
          if (!N(o[i])) {
            n = !1;
            break;
          }
        if (n) return e[a];
      }
    return t.cascade || null;
  }
  var R = null,
    D = "wads_pais";
  function U(t) {
    if (!t.cascadePath) return t;
    var e = M(t);
    if (!e || !e.base) return t;
    var a = {};
    for (var o in t) a[o] = t[o];
    return ((a.path = t.cascadePath), a);
  }
  var j,
    F = (function () {
      var t = new URLSearchParams(window.location.search),
        o = {};
      if (
        (e.forEach(function (e) {
          var a = t.get(e);
          a && (o[e] = a);
        }),
        Object.keys(o).length)
      ) {
        try {
          sessionStorage.setItem(a, JSON.stringify(o));
        } catch (t) {}
        return o;
      }
      try {
        return JSON.parse(sessionStorage.getItem(a) || "{}");
      } catch (t) {
        return {};
      }
    })(),
    q = (function () {
      if (t && t.postId) {
        var e = String(t.postId).replace(/[^0-9]/g, "");
        if (e) return e;
      }
      var a = document.querySelector('link[rel="shortlink"]');
      if (a) {
        var o = /[?&]p=(\d+)/.exec(a.getAttribute("href") || "");
        if (o) return o[1];
      }
      var n = document.querySelector(
        'link[type="application/json"][href*="/wp/v2/posts/"]',
      );
      if (n) {
        var i = /\/wp\/v2\/posts\/(\d+)/.exec(n.getAttribute("href") || "");
        if (i) return i[1];
      }
      if (document.body) {
        var r = /(?:^|\s)postid-(\d+)(?:\s|$)/.exec(
          document.body.className || "",
        );
        if (r) return r[1];
      }
      return null;
    })();
  if (
    ((window.__siteUTMs = F),
    (window.__postId = q),
    (window.wads = function (t, e) {
      ((e = e || {}), n(F, q), i(t, F, q, e.endpoint));
    }),
    t)
  ) {
    (n(F, q),
      (j = t),
      o().cmd.push(function () {
        var t = window.googletag.pubads();
        if (
          (j.lazyLoad && t.enableLazyLoad(j.lazyLoad),
          !0 === j.singleRequest && t.enableSingleRequest(),
          j.interstitial && window.googletag.enums)
        ) {
          var e = window.googletag.defineOutOfPageSlot(
            j.interstitial,
            window.googletag.enums.OutOfPageFormat.INTERSTITIAL,
          );
          e && e.addService(t);
        }
      }),
      (function (t) {
        function a(a) {
          if (
            (function (t) {
              if (!t || !t.href) return !1;
              var e = t.getAttribute("href") || "";
              if (/^(#|mailto:|tel:|javascript:|sms:)/i.test(e)) return !1;
              try {
                var a = new URL(t.href, window.location.href);
                return (
                  a.host === window.location.host &&
                  ("http:" === a.protocol || "https:" === a.protocol)
                );
              } catch (t) {
                return !1;
              }
            })(a) &&
            "1" !== a.dataset.utmDecorated
          )
            try {
              var o = new URL(a.href, window.location.href);
              (e.forEach(function (e) {
                t[e] && !o.searchParams.has(e) && o.searchParams.set(e, t[e]);
              }),
                (a.href = o.toString()),
                (a.dataset.utmDecorated = "1"));
            } catch (t) {}
        }
        function o(t) {
          for (
            var e = (t || document).querySelectorAll("a[href]"), o = 0;
            o < e.length;
            o++
          )
            a(e[o]);
        }
        Object.keys(t).length &&
          ("loading" === document.readyState
            ? document.addEventListener("DOMContentLoaded", function () {
                o(document);
              })
            : o(document),
          window.MutationObserver &&
            new MutationObserver(function (t) {
              for (var e = 0; e < t.length; e++)
                for (var n = t[e].addedNodes, i = 0; i < n.length; i++) {
                  var r = n[i];
                  1 === r.nodeType &&
                    ("A" === r.tagName ? a(r) : r.querySelectorAll && o(r));
                }
            }).observe(document.documentElement, {
              childList: !0,
              subtree: !0,
            }),
          document.addEventListener(
            "click",
            function (t) {
              var e =
                t.target && t.target.closest
                  ? t.target.closest("a[href]")
                  : null;
              e && a(e);
            },
            !0,
          ));
      })(F));
    var B = function () {
      !(function (e) {
        if (t && t.slots) return e(t.slots);
        var a = y(),
          o =
            (t && t.manifest) ||
            "https://erp.winup.network/api/public/ad-slots";
        try {
          fetch(o + "?domain=" + encodeURIComponent(a), { credentials: "omit" })
            .then(function (t) {
              return t.json();
            })
            .then(function (t) {
              (t && t.priceKey && (g = t.priceKey), e((t && t.slots) || {}));
            })
            .catch(function () {
              e({});
            });
        } catch (t) {
          e({});
        }
      })(function (e) {
        !(function (e, a) {
          if (
            !(function (t) {
              for (var e in t)
                if (t[e] && (t[e].cascade || t[e].cascadesUtm)) return !0;
              return !1;
            })(e)
          )
            return a();
          var o = (function (t) {
            for (var e in t) {
              var a = t[e] && t[e].cascadesUtm;
              if (a)
                for (var o = 0; o < a.length; o++)
                  for (var n = I(a[o]), i = 0; i < n.length; i++)
                    if ("pais" === n[i].chave) return !0;
            }
            return !1;
          })(e);
          try {
            var n = window.sessionStorage.getItem(D);
            if (null !== n) return ((R = n || null), a());
          } catch (t) {}
          var i =
            (t && t.pais) || "https://api.winup.network/api/v1/public/kvp/pais";
          if (0 === i.indexOf("__")) return a();
          var r = !1,
            s = function (t, e) {
              if (!r) {
                if (((r = !0), (R = t || null), e))
                  try {
                    window.sessionStorage.setItem(D, R || "");
                  } catch (t) {}
                o && a();
              }
            };
          o || a();
          var l = setTimeout(function () {
            s(null, !1);
          }, 1e3);
          try {
            fetch(i, { credentials: "omit" })
              .then(function (t) {
                return t.json();
              })
              .then(function (t) {
                (clearTimeout(l), s(t && t.pais, !0));
              })
              .catch(function () {
                (clearTimeout(l), s(null, !1));
              });
          } catch (t) {
            (clearTimeout(l), s(null, !1));
          }
        })(e, function () {
          !(function (e) {
            var a = document.querySelectorAll(
              "winupads, winup-ads, [data-wads]",
            );
            a.length &&
              o().cmd.push(function () {
                for (
                  var o = window.googletag.pubads(), n = r(), i = [], s = 0;
                  s < a.length;
                  s++
                ) {
                  var l = a[s];
                  if ("1" !== l.getAttribute("data-wads-montado")) {
                    var u,
                      g = d(l),
                      p = g.posicao,
                      v = "";
                    if (g.caminho)
                      ((u = {
                        path: g.caminho,
                        sizes: g.tamanhos,
                        interstitial: !1,
                      }),
                        (p =
                          g.caminho
                            .split("/")
                            .pop()
                            .toLowerCase()
                            .replace(/[^a-z0-9_]/g, "") || "slot"));
                    else {
                      if (
                        (!p && g.recompensa && (p = "rewarded"),
                        !p && (g.ancora || g.fixa))
                      ) {
                        var w = g.ancora ? "anchor" : "fixed";
                        p = w + "_" + (g.ancora || g.fixa);
                        var h = (g.forcada || n) + "_" + p;
                        e[h] || e["web_" + p] || e["mobile_" + p] || (p = w);
                      }
                      if (!p) continue;
                      var b = (g.forcada || n) + "_" + p;
                      if (
                        ((v = e[b]
                          ? b
                          : e["web_" + p]
                            ? "web_" + p
                            : "mobile_" + p),
                        !(u = e[v]) || !u.path)
                      )
                        continue;
                      (g.tamanhos.length &&
                        (u = {
                          path: u.path,
                          sizes: g.tamanhos,
                          interstitial: u.interstitial,
                          cascade: u.cascade,
                          cascadesUtm: u.cascadesUtm,
                          cascadePath: u.cascadePath,
                        }),
                        (u = U(u)));
                    }
                    if (g.recompensa) {
                      ((l.style.display = "none"),
                        l.setAttribute("data-wads-montado", "1"));
                      var y = k(u, g);
                      if (y) {
                        i.push({
                          el: l,
                          id: null,
                          slot: y,
                          lazy: !1,
                          outOfPage: !0,
                        });
                        var x = M(u);
                        x &&
                          x.base &&
                          ((f[u.path] = {
                            id: null,
                            chave: v,
                            path: u.path,
                            sizes: [],
                            outOfPage: !0,
                            rewarded: !0,
                            fluid: !1,
                            cfg: x,
                            preco: m(x.base),
                            slot: y,
                            tentativa: 0,
                            ocupado: !1,
                            pronto: !1,
                          }),
                          _(y, x.base, !0),
                          O());
                      }
                    } else {
                      var S = l;
                      if (g.custom && g.fixa) {
                        if (!(S = l.querySelector("div[data-wads-alvo]"))) {
                          for (
                            var A = 0, L = 0;
                            L < (u.sizes || []).length;
                            L++
                          )
                            u.sizes[L][1] > A && (A = u.sizes[L][1]);
                          S = c(g.fixa, A);
                        }
                        l.style.display = "none";
                      } else if (g.custom && !u.interstitial && !g.ancora) {
                        ((l.style.display = "block"),
                          (l.style.textAlign = "center"),
                          (l.style.margin = "24px auto"));
                        for (var T = 0, z = 0; z < (u.sizes || []).length; z++)
                          u.sizes[z][1] > T && (T = u.sizes[z][1]);
                        (T && (l.style.minHeight = T + "px"),
                          (S = l.querySelector("div[data-wads-alvo]")) ||
                            ((S = document.createElement("div")).setAttribute(
                              "data-wads-alvo",
                              "1",
                            ),
                            l.appendChild(S)));
                      }
                      if (!S.id && !u.interstitial && !g.ancora) {
                        for (
                          var C = p, E = C, P = 2;
                          document.getElementById(E);
                        )
                          ((E = C + "-" + P), P++);
                        S.id = E;
                      }
                      var I = l.getAttribute("label");
                      if (
                        I &&
                        g.custom &&
                        !u.interstitial &&
                        !g.ancora &&
                        !l.querySelector("[data-wads-rotulo]")
                      ) {
                        var N = document.createElement("div");
                        (N.setAttribute("data-wads-rotulo", "1"),
                          (N.style.cssText =
                            "text-align:center;border-bottom:1px solid #f1f1f1;padding-bottom:1px;margin-bottom:1px"),
                          (N.innerHTML = '<span style="font-size:9px"></span>'),
                          (N.firstChild.textContent = I),
                          l.insertBefore(N, S));
                      }
                      var R,
                        D = (u.sizes || []).map(function (t) {
                          return [t[0], t[1]];
                        }),
                        j = !1,
                        F = !!u.interstitial || !!g.ancora;
                      if (F) {
                        if (!window.googletag.enums) continue;
                        var q = g.ancora
                          ? "bottom" === g.ancora
                            ? window.googletag.enums.OutOfPageFormat
                                .BOTTOM_ANCHOR
                            : window.googletag.enums.OutOfPageFormat.TOP_ANCHOR
                          : window.googletag.enums.OutOfPageFormat.INTERSTITIAL;
                        if (
                          !(R = window.googletag.defineOutOfPageSlot(u.path, q))
                        )
                          continue;
                        R.addService(o);
                      } else {
                        if (!(R = window.googletag.defineSlot(u.path, D, S.id)))
                          continue;
                        var B = (j =
                            !(!t || !0 !== t.fluid) ||
                            ((!t || !1 !== t.fluid) && !g.fixa))
                            ? ["fluid"].concat(D)
                            : D,
                          H = window.googletag
                            .sizeMapping()
                            .addSize([0, 0], B)
                            .build();
                        (R.defineSizeMapping(H).addService(o),
                          "function" == typeof R.setConfig
                            ? R.setConfig({ collapseDiv: "ON_NO_FILL" })
                            : R.setCollapseEmptyDiv(!0));
                      }
                      var J = M(u);
                      if (J && J.base) {
                        var K = F ? u.path : S.id;
                        ((f[K] = {
                          id: S.id,
                          chave: v,
                          path: u.path,
                          sizes: D,
                          outOfPage: F,
                          fluid: j,
                          cfg: J,
                          preco: m(J.base),
                          slot: R,
                          tentativa: 0,
                          ocupado: !1,
                          pronto: !1,
                        }),
                          _(R, J.base, F),
                          O());
                      }
                      (l.setAttribute("data-wads-montado", "1"),
                        i.push({
                          el: l,
                          id: S.id,
                          slot: R,
                          lazy: g.lazy,
                          outOfPage: F,
                        }));
                    }
                  }
                }
                if (i.length) {
                  if (
                    i.some(function (t) {
                      return t.lazy;
                    }) ||
                    t.lazyLoad
                  ) {
                    var W = t.lazyLoad || {
                      fetchMarginPercent: 100,
                      renderMarginPercent: 50,
                      mobileScaling: 2,
                    };
                    "function" == typeof window.googletag.setConfig
                      ? window.googletag.setConfig({ lazyLoad: W })
                      : o.enableLazyLoad(W);
                  }
                  (window.googletag.enableServices(),
                    i.forEach(function (t) {
                      window.googletag.display(t.outOfPage ? t.slot : t.id);
                    }));
                }
              });
          })(e);
        });
      });
    };
    ("loading" === document.readyState
      ? document.addEventListener("DOMContentLoaded", B)
      : B(),
      t.network &&
        window.addEventListener("load", function () {
          i(t.network, F, q);
        }));
  }
})();
