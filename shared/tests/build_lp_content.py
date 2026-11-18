#!/usr/bin/env python3
"""Build the paste-ready LP post HTML and the static before/after previews.

The quiz style, quiz markup, ad tag, and .pf-dbtn anchors are copied byte for
byte from the October snapshots. Only the editorial paragraphs change.
"""
import pathlib
import re

ROOT = pathlib.Path(__file__).resolve().parents[2]
US = ROOT / "us"

LP01_INTRO = """<p class="wp-block-paragraph">Most of the easy dating apps in the United States let you start on a free plan. The paid decision shows up later, as a premium subscription. The names people compare are Tinder Gold, Bumble Premium, and Hinge's paid plans (Hinge+ and HingeX).</p>
<p class="wp-block-paragraph">This page is about what those upgrades usually add, and what you can still do without them. It does not rank a winner, it does not start a subscription, and it does not promise a match. The guides linked below stay on this site.</p>"""

LP01_BODY = """<h2 class="wp-block-heading">What the free plan usually includes</h2>
<p class="wp-block-paragraph">On a free plan you can typically create a profile, set an age range and a distance, and send a limited number of likes or introductions. Use that week before you pay. It tells you whether people nearby are active, and whether the app is actually easy to finish a profile in one sitting: a few photos, a city, and one honest line about what you want.</p>
<h2 class="wp-block-heading">Tinder Gold, Bumble Premium, and Hinge's paid plans</h2>
<p class="wp-block-paragraph">These are the premium subscriptions US advertisers and app stores put next to the free tier. Tinder Gold is the Tinder plan built around seeing who already liked you, on top of a fast deck of profiles. Bumble Premium sits on Bumble's free messaging rules and usually adds more filters and more control over who can reach you. Hinge's paid plans, Hinge+ and HingeX, sit on top of an app that asks for prompts instead of a one-line bio. Plan names change. The current name is on the product page inside the app.</p>
<p class="wp-block-paragraph">What the paid plan usually sells is the same short list: more likes, a look at who already liked you, extra filters, and a boost that shows your profile to more people for a short time. The monthly price and the renewal date are on the Google Play or App Store listing, and again on the confirm screen. They are not the same in every state or every promotion. Read that screen. A review that quotes a dollar amount is already out of date.</p>
<h2 class="wp-block-heading">Compare the extra, not the badge</h2>
<p class="wp-block-paragraph">A useful comparison is one question: which single extra would change how you use the free plan you already tried? If you would not pay for "see who liked you", unlimited likes, or a boost, the premium subscription is optional. None of these plans is a match guarantee, and a paid badge is not a sign that someone is a better fit.</p>
<h2 class="wp-block-heading">Verification is not included just because you paid</h2>
<p class="wp-block-paragraph">Photo verification and phone verification are separate from the premium tier. Check whether the app offers them, who can see a verified photo, and how to pause the profile without deleting it. A subscription does not make a profile honest. Before a first meeting, pick a public place, use your own way home, and do not send money, gift cards, or identity documents to someone you have not met.</p>
<h2 class="wp-block-heading">Where the buttons go</h2>
<p class="wp-block-paragraph">The three buttons under the quiz stay on us.credityup.com. One opens the shorter beginner guide, one opens practical tips, and one opens the longer walkthrough. None of them open an app store, and none of them start a paid plan.</p>
<p class="wp-block-paragraph"><em>Prices, plan names, and renewal terms vary by app and by store. Read the listing before you subscribe. Conditions vary by state.</em></p>
<p class="wp-block-paragraph">Sources: Federal Trade Commission — Data Security; FTC — Online Dating Scams; Google Play — Subscriptions.</p>
<div style="display:none;">Keyword: easy dating apps premium subscription<br>
Tags: free vs paid dating apps, Tinder Gold, Bumble Premium, Hinge+, HingeX, premium subscription, profile verification, dating app pricing<br>
Category: Dating<br>
Meta: What the free plan includes, and what Tinder Gold, Bumble Premium, and Hinge paid plans usually add. Prices are on the store listing. Guides stay on Credityup.<br>
Slug: /best-easy-dating-apps-lp-01</div>"""

LP02_INTRO = """<p class="wp-block-paragraph">"Instant matchmaking" usually means a short profile, your location, and an alert, so the path from opening the app to sending a message is short. It does not mean someone is already nearby, and it does not mean the app is free past the first screen.</p>
<p class="wp-block-paragraph">The paid screen is a separate product: a boost, more likes, or a look at who already responded. On the big US apps those upgrades are sold as Tinder Gold, Bumble Premium, and Hinge's paid plans. This page explains the free part and the paid part, then stays on this site.</p>"""

LP02_BODY = """<h2 class="wp-block-heading">What you get before any premium subscription</h2>
<p class="wp-block-paragraph">Before a paid plan, these apps tend to offer three things. Location, so the list can be people who could actually meet. A short profile, so you are not writing a biography first. And an alert, so a new message does not wait until you remember to open the app. If one of the three is missing, "instant" is a label. Use the free plan long enough to see whether replies arrive in your city.</p>
<h2 class="wp-block-heading">Boosts, extra likes, and "see who liked you"</h2>
<p class="wp-block-paragraph">The upgrade screen is where Tinder Gold, Bumble Premium, and Hinge+ or HingeX show up, even inside apps that advertise a fast start. The extras are usually a boost, a higher like limit, and a list of people who already liked you. That is a premium subscription with a renewal date. The price is on the store listing and on the confirm screen, and it changes. A boost does not create a compatible person. It only shows the profile you already wrote to more people for a short time.</p>
<h2 class="wp-block-heading">Say what you want before the thread gets long</h2>
<p class="wp-block-paragraph">The fastest way to waste a conversation is to discover, ten messages in, that you wanted different things. One clear line — dating, a relationship, or friendship — saves both people time. You do not owe anyone a more personal story than that on the first day.</p>
<h2 class="wp-block-heading">Verification, distance, and your real address</h2>
<p class="wp-block-paragraph">Photo verification and a premium badge are different products. Paying does not verify the other person. Location features need a permission; approximate location is enough when the app offers it. Do not type your home address, workplace, or daily route into a chat. Read the privacy policy once: what other users can see, what is kept if you delete the account, and whether photos are used for anything besides your profile.</p>
<h2 class="wp-block-heading">A global member count is not your city</h2>
<p class="wp-block-paragraph">An app can quote a huge member count and still be quiet where you live on a Tuesday night. A few minutes of real replies tells you more than a review that repeats a worldwide total, and more than a paid boost spent on an empty area.</p>
<h2 class="wp-block-heading">The first in-person plan</h2>
<p class="wp-block-paragraph">Public place, your own transport, and a friend who knows the plan. Leave if the conversation turns to money, an investment, or a request to move to another app immediately. Those are common romance-scam patterns, and the FTC describes them in plain language.</p>
<h2 class="wp-block-heading">The buttons stay on this site</h2>
<p class="wp-block-paragraph">The buttons under the quiz open other Credityup guides. They do not open an app store and they do not start a Tinder Gold, Bumble Premium, or Hinge subscription for you.</p>
<p class="wp-block-paragraph"><em>Prices, plan names, and renewal terms vary by app and by store. Read the listing before you subscribe. Conditions vary by state.</em></p>
<p class="wp-block-paragraph">Sources: Federal Trade Commission — Dating and Romance Scams; National Cyber Security Alliance — Online Dating Safety; Google Play — Subscriptions.</p>
<div style="display:none;">Keyword: instant matchmaking apps premium subscription<br>
Tags: instant matchmaking, free vs paid dating apps, Tinder Gold, Bumble Premium, Hinge+, boost, see who liked you, profile verification<br>
Category: Dating<br>
Meta: How quick matching works before you pay, and how boosts and premium subscriptions differ from verification. You stay on Credityup.<br>
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
        "Easy dating apps: free plans vs Tinder Gold, Bumble Premium, and Hinge",
        "What the free plan includes, and what Tinder Gold, Bumble Premium, and Hinge paid plans usually add. Prices are on the store listing. Guides stay on Credityup.",
    )
    post2 = build_post(
        prefix2, LP02_INTRO, links2, LP02_BODY, credit2,
        "Instant matchmaking apps: free use, paid boosts, and what to check",
        "How quick matching works before you pay, and how boosts and premium subscriptions differ from verification. You stay on Credityup.",
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
        "after-lp-01.html": ("Easy dating apps: free plans vs Tinder Gold, Bumble Premium, and Hinge", "Easy dating apps: free plans vs Tinder Gold, Bumble Premium, and Hinge", post1, extra),
        "before-lp-02.html": ("Swift Dating: Explore Instant Match Solutions", "Swift Dating: Explore Instant Match Solutions", editorial_before(lp2), ""),
        "after-lp-02.html": ("Instant matchmaking apps: free use, paid boosts, and what to check", "Instant matchmaking apps: free use, paid boosts, and what to check", post2, extra),
    }
    for name, (title, h1, body, more) in pages.items():
        html = CHROME.format(title=title, h1=h1, body=preview_body(body), extra=more)
        (preview / name).write_text(html, encoding="utf-8")
    print("wrote", out, "and", preview)


if __name__ == "__main__":
    main()
