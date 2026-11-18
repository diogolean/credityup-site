/* Shows an email form when the English quiz reaches its result step.
   Does not edit quiz questions, button values, pixel calls, or the CTA href.
   Submit is an input, not a button element, so the existing InitiateCheckout
   listener (any button element) does not fire for this form.
   The address is posted as JSON and is never put on a URL. */
(function () {
    if (window.__cyLeadBound) {
        return;
    }
    window.__cyLeadBound = true;

    var cfg = window.credityupLeads || {};

    function fieldStyle() {
        return 'display:block;width:100%;box-sizing:border-box;margin:8px 0;padding:12px 14px;border:1px solid rgba(159,18,57,.25);border-radius:12px;font:16px/1.4 system-ui,sans-serif;background:#fff;color:#1c1917';
    }

    function build() {
        var form = document.createElement('form');
        form.id = 'cy-lead';
        form.setAttribute('novalidate', 'novalidate');
        form.method = 'post';
        form.action = '#cy-lead';
        form.style.cssText = 'margin:18px 16px 8px;padding:16px;border-top:1px solid rgba(159,18,57,.16)';
        form.innerHTML =
            '<p style="margin:0 0 8px;font:600 16px/1.3 system-ui,sans-serif;color:#1c1917">Email these guides?</p>' +
            '<p style="margin:0 0 8px;font:14px/1.45 system-ui,sans-serif;color:#57534e">Occasional notes about dating guides from Credityup. No account, and we don\'t add this address to ads or analytics.</p>' +
            '<label style="position:absolute;left:-9999px" aria-hidden="true">Company<input name="company" tabindex="-1" autocomplete="off"></label>' +
            '<label style="font:14px system-ui,sans-serif;color:#1c1917">Email' +
            '<input type="email" name="email" required autocomplete="email" inputmode="email" style="' + fieldStyle() + '"></label>' +
            '<label style="display:flex;gap:8px;align-items:flex-start;margin:10px 0;font:14px/1.4 system-ui,sans-serif;color:#44403c">' +
            '<input type="checkbox" name="consent" value="1" required style="margin-top:3px">' +
            '<span>I agree to receive these emails and I have read the <a href="' + (cfg.privacyUrl || '/privacy-policy/') + '">Privacy Policy</a>. I can unsubscribe later.</span></label>' +
            '<input type="submit" value="Save my email" style="display:inline-block;margin-top:4px;padding:12px 16px;border:0;border-radius:999px;background:#1c1917;color:#fff;font:600 14px system-ui,sans-serif;cursor:pointer">' +
            '<p class="cy-lead-msg" role="status" style="min-height:1.2em;margin:8px 0 0;font:14px system-ui,sans-serif"></p>';
        form.addEventListener('submit', onSubmit);
        return form;
    }

    function pathOnly() {
        var path = window.location.pathname || '/';
        if (path.charAt(0) !== '/') {
            path = '/';
        }
        return path.split('?')[0].slice(0, 200);
    }

    function onSubmit(event) {
        event.preventDefault();
        var form = event.currentTarget;
        var msg = form.querySelector('.cy-lead-msg');
        var email = (form.email && form.email.value || '').trim();
        var consent = form.consent && form.consent.checked;
        var company = form.company ? form.company.value : '';
        if (!consent || email.indexOf('@') < 1) {
            if (msg) {
                msg.textContent = 'Add an email and check the consent box.';
            }
            return;
        }
        if (!cfg.restUrl) {
            if (msg) {
                msg.textContent = 'Saved on this preview only.';
            }
            return;
        }
        var submit = form.querySelector('input[type="submit"]');
        if (submit) {
            submit.disabled = true;
        }
        fetch(cfg.restUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Credityup-Token': cfg.token || ''
            },
            body: JSON.stringify({
                email: email,
                consent: consent ? '1' : '0',
                company: company,
                page: pathOnly()
            })
        }).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (data) {
                return { ok: res.ok && data && data.ok };
            });
        }).then(function (result) {
            if (msg) {
                msg.textContent = result.ok
                    ? 'Thanks. We\'ll use this address only for the emails you just agreed to.'
                    : 'We couldn\'t save that. Check the email and the consent box.';
            }
            if (result.ok) {
                form.email.disabled = true;
                form.consent.disabled = true;
            } else if (submit) {
                submit.disabled = false;
            }
        }).catch(function () {
            if (msg) {
                msg.textContent = 'We couldn\'t save that. Please try again.';
            }
            if (submit) {
                submit.disabled = false;
            }
        });
    }

    function sync() {
        var quiz = document.getElementById('qwu-quiz-en');
        if (!quiz) {
            return;
        }
        var phase = quiz.getAttribute('data-qwu-phase') || '';
        var existing = document.getElementById('cy-lead');
        if (phase !== 'result') {
            if (existing) {
                existing.remove();
            }
            return;
        }
        if (existing) {
            return;
        }
        var body = quiz.querySelector('#qwu-en-body') || quiz;
        body.appendChild(build());
    }

    function start() {
        sync();
        var quiz = document.getElementById('qwu-quiz-en');
        if (!quiz || typeof MutationObserver !== 'function') {
            return;
        }
        new MutationObserver(sync).observe(quiz, { attributes: true, attributeFilter: ['data-qwu-phase'] });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
