/* Credityup FCM service worker. Must be served from the site root so the scope is /.
   Loaded by the browser only after the visitor accepts the soft opt-in. */
/* global importScripts, firebase, clients */
importScripts('/credityup-firebase-config.js');
importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js');

var cfg = self.CREDITYUP_FIREBASE_CONFIG || {};

function cyConfigReady(c) {
    if (!c || !c.apiKey || !c.projectId || !c.messagingSenderId || !c.appId) {
        return false;
    }
    var fields = [c.apiKey, c.authDomain, c.projectId, c.messagingSenderId, c.appId, c.vapidKey];
    for (var i = 0; i < fields.length; i++) {
        if (!fields[i] || String(fields[i]).indexOf('YOUR_') === 0) {
            return false;
        }
    }
    return true;
}

function cySafeUrl(url) {
    try {
        var parsed = new URL(url || '/', self.location.origin);
        if (parsed.origin !== self.location.origin) {
            return self.location.origin + '/';
        }
        return parsed.href;
    } catch (err) {
        return self.location.origin + '/';
    }
}

if (cyConfigReady(cfg) && typeof firebase !== 'undefined') {
    firebase.initializeApp({
        apiKey: cfg.apiKey,
        authDomain: cfg.authDomain,
        projectId: cfg.projectId,
        messagingSenderId: cfg.messagingSenderId,
        appId: cfg.appId
    });
    firebase.messaging().onBackgroundMessage(function (payload) {
        var note = payload.notification || {};
        var data = payload.data || {};
        var title = note.title || data.title || 'Credityup';
        var options = {
            body: note.body || data.body || '',
            icon: note.icon || '/favicon.ico',
            data: { url: cySafeUrl(data.url || '/') }
        };
        return self.registration.showNotification(title, options);
    });
}

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    var target = cySafeUrl(event.notification.data && event.notification.data.url);
    event.waitUntil(self.clients.openWindow(target));
});
