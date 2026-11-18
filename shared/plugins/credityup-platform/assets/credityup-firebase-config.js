/* Public Firebase web config for Credityup push.
   Replace YOUR_* values in wp-admin (Credityup → Push) or via wp-config.php constants.
   Do not put a Firebase server key or service-account JSON in this file.
   This copy in git is placeholders only. The plugin rewrites the file at the
   WordPress root when settings are saved. */
(function (root) {
    if (root.CREDITYUP_FIREBASE_CONFIG && String(root.CREDITYUP_FIREBASE_CONFIG.apiKey || '').indexOf('YOUR_') !== 0) {
        return;
    }
    root.CREDITYUP_FIREBASE_CONFIG = {
        apiKey: "YOUR_FIREBASE_API_KEY",
        authDomain: "YOUR_FIREBASE_PROJECT_ID.firebaseapp.com",
        projectId: "YOUR_FIREBASE_PROJECT_ID",
        messagingSenderId: "YOUR_MESSAGING_SENDER_ID",
        appId: "YOUR_FIREBASE_APP_ID",
        vapidKey: "YOUR_VAPID_PUBLIC_KEY"
    };
})(typeof self !== 'undefined' ? self : this);
