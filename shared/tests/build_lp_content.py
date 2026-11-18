#!/usr/bin/env python3
"""Build the paste-ready LP post HTML and the static before/after previews.

The quiz style, quiz markup, ad tag, and .pf-dbtn anchors are copied byte for
byte from the October snapshots. Only the editorial paragraphs change.
"""
import pathlib
import re

ROOT = pathlib.Path(__file__).resolve().parents[2]
US = ROOT / "us"

LP01_INTRO = """<p class="wp-block-paragraph">Most dating apps are not hard because of one clever feature. They are hard because the first ten minutes are a run of screens that never say what happens next.</p>
<p class="wp-block-paragraph">This page is a map of the simpler ones. It will not name a winner, and it will not tell you a match is likely. It shows what to check before you create an account, then points you at a short guide on this same site.</p>"""

LP01_BODY = """<h2 class="wp-block-heading">A short profile is a feature</h2>
<p class="wp-block-paragraph">If an app asks for a long biography before you can see anyone, that is a product choice, not a test of character. Look for a flow you can finish in one sitting: a few photos, an age range, a city, and one honest line about what you want. You can add more later.</p>
<h2 class="wp-block-heading">Filters should narrow the search, not hide the price</h2>
<p class="wp-block-paragraph">Age, distance, and the kind of relationship you are open to are enough for a first pass. A wall of extra filters that only opens after a payment is a reason to read the price screen slowly. Recurring charges should be visible before you confirm them. Google Play's subscription notes are a useful checklist for that.</p>
<h2 class="wp-block-heading">See who can view you</h2>
<p class="wp-block-paragraph">Before you upload a face photo, open the privacy settings. You want a plain answer to three questions: who can see the photo, whether you appear in search, and how to pause the profile without deleting it. If those controls are hard to find, treat that as part of the decision.</p>
<h2 class="wp-block-heading">The first message should be easy to find</h2>
<p class="wp-block-paragraph">A usable app puts the conversation on the same screen as the match, with an ordinary text field. You do not need a perfect opening. One specific detail from the profile is enough. If the only way to talk is a paid sticker or a timed game, decide whether that is the conversation you wanted.</p>
<h2 class="wp-block-heading">Meeting up</h2>
<p class="wp-block-paragraph">If you do meet, keep the first meeting in a public place, tell a friend where you are going, and use your own way home. Do not send money, gift cards, or identity documents to someone you have not met. The FTC's notes on romance scams are short and worth reading.</p>
<h2 class="wp-block-heading">Where the buttons go</h2>
<p class="wp-block-paragraph">The three buttons under the quiz stay on us.credityup.com. One opens the shorter beginner guide, one opens practical tips, and one opens the longer walkthrough. None of them leave this domain, and none of them create an account for you.</p>
<p class="wp-block-paragraph"><em>Conditions vary by app and by state. Check the app's own rules and the store listing before you pay for anything.</em></p>
<p class="wp-block-paragraph">Sources: Federal Trade Commission — Data Security; FTC — Online Dating Scams; Google Play — Subscriptions.</p>
<div style="display:none;">Keyword: easy dating apps<br>
Tags: dating apps for beginners, simple dating apps, privacy settings, first message, online dating safety<br>
Category: Dating<br>
Meta: How to judge a dating app by the effort it asks of you: profile length, filters, privacy, and the first message. Guides stay on Credityup.<br>
Slug: /best-easy-dating-apps-lp-01</div>"""

LP02_INTRO = """<p class="wp-block-paragraph">"Instant matchmaking" sounds like a person is already nearby and ready to meet. In the product it usually means something narrower: the app shortens the path from opening it to sending a message, often with your location and a very small profile.</p>
<p class="wp-block-paragraph">Speed is not the same thing as compatibility. Use this page to see what the shortcut includes, what it leaves out, and how to keep your information small until you have a reason to share more.</p>"""

LP02_BODY = """<h2 class="wp-block-heading">What the shortcut is made of</h2>
<p class="wp-block-paragraph">These apps tend to lean on three tools. Location, so the list is people who could actually meet. A short profile, so you are not writing a biography first. And an alert, so a new message does not wait until you remember to open the app. If one of the three is missing, "instant" is mostly a label.</p>
<h2 class="wp-block-heading">Say what you want before the thread gets long</h2>
<p class="wp-block-paragraph">The fastest way to waste a conversation is to discover, ten messages in, that you wanted different things. One clear line — dating, a relationship, or friendship — saves both people time. You do not owe anyone a more personal story than that on the first day.</p>
<h2 class="wp-block-heading">A global member count is not your city</h2>
<p class="wp-block-paragraph">An app can quote a huge member count and still be quiet where you live on a Tuesday night. Spend a few minutes looking at how recent the profiles feel and whether replies actually arrive. That look is more useful than a review that repeats a worldwide total.</p>
<h2 class="wp-block-heading">Photos, distance, and your real address</h2>
<p class="wp-block-paragraph">Location features need a permission. Give the app approximate location if that option exists, and do not type your home address, workplace, or daily route into a chat. Read the privacy policy once, looking only for what other users can see, what is kept if you delete the account, and whether photos are used for anything besides your profile.</p>
<h2 class="wp-block-heading">The first in-person plan</h2>
<p class="wp-block-paragraph">Public place, your own transport, and a friend who knows the plan. Leave if the conversation turns to money, an investment, or a request to move to another app immediately. Those are common romance-scam patterns, and the FTC describes them in plain language.</p>
<h2 class="wp-block-heading">The buttons stay on this site</h2>
<p class="wp-block-paragraph">The buttons under the quiz open other Credityup guides. They do not open an app store and they do not complete a signup for you.</p>
<p class="wp-block-paragraph"><em>Conditions vary by app and by state. Check the app's own rules and the store listing before you pay for anything.</em></p>
<p class="wp-block-paragraph">Sources: Federal Trade Commission — Dating and Romance Scams; National Cyber Security Alliance — Online Dating Safety.</p>
<div style="display:none;">Keyword: instant matchmaking apps<br>
Tags: instant matchmaking, quick matching, location based dating, dating app privacy, online dating safety<br>
Category: Dating<br>
Meta: What quick matching uses — location, a short profile, alerts — and the checks to make before you rely on it. You stay on Credityup.<br>
Slug: /instant-matchmaking-apps-lp-02</div>"""

CHROME = """<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{title}</title>
<style>
  body {{ margin:0; background:#f4f5f7; color:#2d3359; font-family:system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial,sans-serif; }}
  .bar {{ background:#fff; min-height:64px; display:flex; align-items:center; gap:16px; padding:0 20px; box-shadow:0 1px 0 #eee; }}
  .logo {{ display:flex; align-items:center; gap:8px; font-weight:700; color:#243056; text-decoration:none; }}
  .nav {{ margin-left:auto; display:flex; gap:14px; overflow:auto; font-size:12px; letter-spacing:.06em; text-transform:uppercase; color:#555; }}
  .nav a {{ color:#555; text-decoration:none; white-space:nowrap; }}
  @media (max-width: 720px) {{
    .bar {{ flex-wrap: wrap; height: auto; padding: 10px 14px 8px; }}
    .nav {{ margin-left: 0; width: 100%; flex-wrap: wrap; gap: 6px 12px; }}
  }}
  .band {{ background:#d81b60; color:#fff; text-align:center; padding:28px 16px 36px; }}
  .band h1 {{ margin:0 auto; max-width:720px; font-size:16px; line-height:1.3; font-weight:700; }}
  .card {{ max-width:850px; width:92%; margin:-18px auto 32px; background:#fff; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,.06); padding:20px; box-sizing:border-box; }}
  .card .winup-ad-block {{ min-height:300px; background:#f3f1f4; border-radius:8px; display:flex; align-items:flex-start; justify-content:center; color:#9a97a3; font-size:10px; letter-spacing:.14em; text-transform:uppercase; padding-top:10px; box-sizing:border-box; }}
  p.wp-block-paragraph, .wp-block-heading {{ color:#3f3f46; }}
  .wp-block-heading {{ color:#2d3359; font-size:22px; margin:28px 0 8px; }}
  a.pf-dbtn {{ display:block; width:100%; padding:15px 20px; margin:8px 0; border-radius:8px; font-size:15px; font-weight:800; letter-spacing:.5px; text-transform:uppercase; text-align:center; color:#fff; text-decoration:none; box-sizing:border-box; }}
  a.pf-dbtn:nth-of-type(1) {{ background:#d81b60 !important; }}
  a.pf-dbtn:nth-of-type(2) {{ background:#3b62f6 !important; }}
  a.pf-dbtn:nth-of-type(3) {{ background:#42ac53 !important; }}
  p.wp-block-paragraph br {{ display:none; }}
  .foot {{ text-align:center; color:#888; font-size:13px; padding:8px 16px 28px; }}
  .foot a {{ color:#888; margin:0 8px; }}
</style>
</head>
<body>
<header class="bar">
  <a class="logo" href="/"><svg width="22" height="22" viewBox="0 0 32 32" aria-hidden="true"><path d="M16 4 L28 26 H4 Z" fill="none" stroke="#3b82f6" stroke-width="2"/><path d="M16 12 L23 26 H9 Z" fill="#2563eb"/></svg> credityup</a>
  <nav class="nav"><a href="/">Home</a><a href="/category/apps/">Apps</a><a href="/category/flirting/">Flirting</a><a href="/category/love/">Love</a><a href="/category/dating/">Dating</a><a href="/category/relationships/">Relationships</a></nav>
</header>
<div class="band"><h1>{h1}</h1></div>
<main class="card">
{body}
</main>
<footer class="foot"><a href="/terms-of-use/">Terms of Service</a><a href="/privacy-policy/">Privacy Policy</a><a href="/cookie-policy/">Cookie Policy</a></footer>
{extra}
</body>
</html>
"""


def quiz_prefix(html: str) -> str:
    start = html.find('<div class="winup-ad-block winup-ad-block-1"')
    quiz = html.find('<div id="qwu-quiz-en"', start)
    end = html.find("</div>\n\n\n\n<p class=\"wp-block-paragraph\">", quiz)
    if start < 0 or quiz < 0 or end < 0:
        raise SystemExit("quiz block not found")
    return html[start:end + len("</div>")]


def anchors(html: str) -> list[str]:
    found = re.findall(r'<a data-pf="btn" href="[^"]+" class="pf-dbtn" style="[^"]+">.*?</a>', html)
    if len(found) != 3:
        raise SystemExit(f"expected 3 anchors, got {len(found)}")
    return found


def credits(html: str) -> str:
    match = re.search(r'<div data-pf="credits"[\s\S]*?</div>', html)
    return match.group(0) if match else ""


def editorial_before(html: str) -> str:
    start = html.find('<div class="winup-ad-block winup-ad-block-1"')
    marker = "</div></p>\t\t\t</div>"
    end = html.find(marker, start)
    if start < 0 or end < 0:
        raise SystemExit("article end not found")
    return html[start:end + len("</div></p>")]


def build_post(prefix: str, intro: str, links: list[str], body: str, credit: str, title: str, meta: str) -> str:
    buttons = "<br>\n".join(links)
    parts = [
        "<!--",
        "Credityup LP post content. Paste into the WordPress code editor, replacing the post body. Keep the slug.",
        f"PAGE TITLE: {title}",
        f"META DESCRIPTION: {meta}",
        "The quiz block, content1 tag, and three .pf-dbtn anchors are unchanged from the snapshot.",
        "-->",
        prefix,
        intro,
        f'<p class="wp-block-paragraph">{buttons}</p>',
        '<p class="has-text-align-center wp-block-paragraph">You will stay on this site.</p>',
        body,
    ]
    if credit:
        parts.append(credit)
    return "\n".join(parts) + "\n"


def preview_body(html_chunk: str) -> str:
    return html_chunk.replace(
        '<winupads tag="content1" label="Ad content"></winupads>',
        '<div class="cy-ad-ph" style="padding-top:28px">Advertisement</div>',
    )


def main() -> None:
    lp1 = (US / "us_lp-01.html").read_text(encoding="utf-8", errors="replace")
    lp2 = (US / "us_lp-02.html").read_text(encoding="utf-8", errors="replace")
    prefix1 = quiz_prefix(lp1)
    prefix2 = quiz_prefix(lp2)
    links1 = anchors(lp1)
    links2 = anchors(lp2)
    credit2 = credits(lp2)

    post1 = build_post(
        prefix1, LP01_INTRO, links1, LP01_BODY, "",
        "Easy dating apps, without the extra steps",
        "How to judge a dating app by the effort it asks of you: profile length, filters, privacy, and the first message. Guides stay on Credityup.",
    )
    post2 = build_post(
        prefix2, LP02_INTRO, links2, LP02_BODY, credit2,
        "How instant matchmaking apps actually work",
        "What quick matching uses — location, a short profile, alerts — and the checks to make before you rely on it. You stay on Credityup.",
    )
    out = US / "content"
    out.mkdir(parents=True, exist_ok=True)
    (out / "lp-01.post.html").write_text(post1, encoding="utf-8")
    (out / "lp-02.post.html").write_text(post2, encoding="utf-8")

    quiz_js_match = re.search(
        r"<!-- HFCM by 99 Robots - Snippet # 2: Quiz En JS -->\s*<script>([\s\S]*?)</script>",
        lp1,
    )
    if not quiz_js_match:
        raise SystemExit("quiz script missing")
    quiz_js = "<script>" + quiz_js_match.group(1) + "</script>"
    leads = (ROOT / "shared/plugins/credityup-platform/assets/leads.js").read_text(encoding="utf-8")
    extra = (
        '<script>window.credityupLeads={restUrl:"",token:"preview",privacyUrl:"/privacy-policy/"};</script>\n'
        "<script>\n" + leads + "\n</script>\n" + quiz_js
    )

    preview = US / "preview"
    preview.mkdir(parents=True, exist_ok=True)
    pages = {
        "before-lp-01.html": ("Streamlined Dating: Discovering User-Friendly Apps", "Streamlined Dating: Discovering User-Friendly Apps", editorial_before(lp1), ""),
        "after-lp-01.html": ("Easy dating apps, without the extra steps", "Easy dating apps, without the extra steps", post1, extra),
        "before-lp-02.html": ("Swift Dating: Explore Instant Match Solutions", "Swift Dating: Explore Instant Match Solutions", editorial_before(lp2), ""),
        "after-lp-02.html": ("How instant matchmaking apps actually work", "How instant matchmaking apps actually work", post2, extra),
    }
    for name, (title, h1, body, more) in pages.items():
        html = CHROME.format(title=title, h1=h1, body=preview_body(body), extra=more)
        (preview / name).write_text(html, encoding="utf-8")
    print("wrote", out, "and", preview)


if __name__ == "__main__":
    main()
