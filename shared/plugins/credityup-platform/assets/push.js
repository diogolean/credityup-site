/* Soft opt-in, then the browser permission prompt, then an FCM token.
   The token is posted to WordPress. It is not forwarded to analytics or the pixel.
   Controls are input elements, not button elements, so the Meta pixel
   listener on button elements does not record an extra InitiateCheckout. */
(function () {
    if (window.__cyPushBound) {
        return;
    }
    window.__cyPushBound = true;

    var cfg = window.credityupPush || {};
    var DISMISS_KEY = 'cy_push_dismiss_until';
    var firebaseApp = null;
    var firebaseMessaging = null;

    function configReady() {
        var c = cfg.config || {};
        var fields = ['apiKey', 'authDomain', 'projectId', 'messagingSenderId', 'appId', 'vapidKey'];
        for (var i = 0; i < fields.length; i++) {
            var value = c[fields[i]];
            if (!value || String(value).indexOf('YOUR_') === 0) {
                return false;
            }
        }
        return true;
    }

    function dismissed() {
        try {
            return Date.now() < parseInt(localStorage.getItem(DISMISS_KEY) || '0', 10);
        } catch (err) {
            return false;
        }
    }

    function adsInTheWay() {
        var rewarded = document.getElementById('masterRewarded');
        if (rewarded && /(^|\s)active(\s|$)/.test(rewarded.className)) {
            return true;
        }
        if (document.getElementById('cy-loader')) {
            return true;
        }
        if (document.querySelector('[data-wads-rewarded]')) {
            return true;
        }
        return false;
    }

    function loadScript(src) {
        return new Promise(function (resolve, reject) {
            var existing = document.querySelector('script[src="' + src + '"]');
            if (existing) {
                existing.addEventListener('load', function () { resolve(); });
                if (existing.getAttribute('data-loaded') === '1') {
                    resolve();
                }
                return;
            }
            var script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = function () {
                script.setAttribute('data-loaded', '1');
                resolve();
            };
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    function ensureFirebase() {
        var base = 'https://www.gstatic.com/firebasejs/10.14.1/';
        return loadScript(base + 'firebase-app-compat.js').then(function () {
            return loadScript(base + 'firebase-messaging-compat.js');
        }).then(function () {
            if (!firebaseApp) {
                firebaseApp = firebase.initializeApp({
                    apiKey: cfg.config.apiKey,
                    authDomain: cfg.config.authDomain,
                    projectId: cfg.config.projectId,
                    messagingSenderId: cfg.config.messagingSenderId,
                    appId: cfg.config.appId
                });
                firebaseMessaging = firebase.messaging();
            }
        });
    }

    function deliver(token) {
        var hooks = window.credityupPushHooks || [];
        for (var i = 0; i < hooks.length; i++) {
            try {
                hooks[i](token);
            } catch (err) {}
        }
        if (!cfg.restUrl) {
            return Promise.resolve();
        }
        return fetch(cfg.restUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Credityup-Token': cfg.token || ''
            },
            body: JSON.stringify({ token: token })
        }).catch(function () {});
    }

    function allow() {
        hide();
        if (!('serviceWorker' in navigator) || !('Notification' in window)) {
            return;
        }
        ensureFirebase().then(function () {
            return navigator.serviceWorker.register('/firebase-messaging-sw.js', { scope: '/' });
        }).then(function (registration) {
            return Notification.requestPermission().then(function (permission) {
                if (permission !== 'granted') {
                    return null;
                }
                return firebaseMessaging.getToken({
                    vapidKey: cfg.config.vapidKey,
                    serviceWorkerRegistration: registration
                });
            });
        }).then(function (token) {
            if (token) {
                return deliver(token);
            }
        }).catch(function () {});
    }

    function hide() {
        var el = document.getElementById('cy-push');
        if (el) {
            el.remove();
        }
    }

    function dismiss() {
        try {
            localStorage.setItem(DISMISS_KEY, String(Date.now() + 14 * 24 * 60 * 60 * 1000));
        } catch (err) {}
        hide();
    }

    function show() {
        if (document.getElementById('cy-push')) {
            return;
        }
        var box = document.createElement('aside');
        box.id = 'cy-push';
        box.setAttribute('role', 'dialog');
        box.setAttribute('aria-label', 'Notifications');
        box.style.cssText = 'position:fixed;left:16px;bottom:16px;z-index:30;width:min(340px,calc(100% - 32px));background:#fff;color:#16161d;border:1px solid #e6e4eb;border-radius:16px;box-shadow:0 12px 40px rgba(16,16,20,.12);padding:16px;font:15px/1.45 system-ui,sans-serif';
        box.innerHTML =
            '<p style="margin:0 0 8px;font-weight:650">Notes from Credityup</p>' +
            '<p style="margin:0 0 12px;color:#5e5c68">Occasional notes about new dating guides. You can say no, and you can turn them off later in the browser.</p>' +
            '<div style="display:flex;gap:8px">' +
            '<input type="button" class="cy-push-no" value="Not now" style="flex:1;padding:10px;border-radius:999px;border:1px solid #e6e4eb;background:#fff;cursor:pointer">' +
            '<input type="button" class="cy-push-yes" value="Allow notifications" style="flex:1;padding:10px;border-radius:999px;border:0;background:#16161d;color:#fff;cursor:pointer">' +
            '</div>';
        box.querySelector('.cy-push-no').addEventListener('click', dismiss);
        box.querySelector('.cy-push-yes').addEventListener('click', allow);
        document.body.appendChild(box);
    }

    function maybeShow() {
        if (!configReady() || dismissed() || adsInTheWay()) {
            return false;
        }
        if (!('Notification' in window) || Notification.permission !== 'default') {
            return true;
        }
        show();
        return true;
    }

    function schedule() {
        var started = Date.now();
        var timer = setInterval(function () {
            if (Date.now() - started < 15000) {
                return;
            }
            if (adsInTheWay()) {
                if (Date.now() - started > 60000) {
                    clearInterval(timer);
                }
                return;
            }
            if (maybeShow() || Date.now() - started > 60000) {
                clearInterval(timer);
            }
        }, 2000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', schedule);
    } else {
        schedule();
    }
})();
