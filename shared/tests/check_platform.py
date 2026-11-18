#!/usr/bin/env python3
"""Invariant check: quiz, buttons, ad slot, and pixels are not rewritten."""
import pathlib
import re
import sys

ROOT = pathlib.Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT / "shared" / "tests"))
from build_lp_content import anchors, credits, quiz_prefix  # noqa: E402

FAILS = []


def fail(msg):
    FAILS.append(msg)


def quiz_core(prefix: str) -> str:
    style = prefix.find("<style>")
    return prefix[style:]


def main():
    lp1 = (ROOT / "us/us_lp-01.html").read_text(encoding="utf-8", errors="replace")
    lp2 = (ROOT / "us/us_lp-02.html").read_text(encoding="utf-8", errors="replace")
    post1 = (ROOT / "us/content/lp-01.post.html").read_text(encoding="utf-8")
    post2 = (ROOT / "us/content/lp-02.post.html").read_text(encoding="utf-8")

    if quiz_core(quiz_prefix(lp1)) != quiz_core(quiz_prefix(lp2)):
        fail("snapshot quiz style/markup differs between LP-01 and LP-02")
    if quiz_prefix(lp1) not in post1:
        fail("LP-01 post dropped the snapshot ad+quiz prefix")
    if quiz_prefix(lp2) not in post2:
        fail("LP-02 post dropped the snapshot ad+quiz prefix")

    for label, source, post in (("lp-01", lp1, post1), ("lp-02", lp2, post2)):
        for anchor in anchors(source):
            if anchor not in post:
                fail(f"{label} lost anchor {anchor[:80]}")
        if post.count('<winupads tag="content1"') != 1:
            fail(f"{label} content1 count changed")
        if 'data-pf="btn"' not in post or post.count('class="pf-dbtn"') != 3:
            fail(f"{label} pf-dbtn count is not 3")
        if "You will stay on this site." not in post:
            fail(f"{label} lost the stay-on-site line")
        if re.search(r"hookup", post, re.I):
            fail(f"{label} still contains hookup")
        if "fbq(" in post or "rewardedSlotGranted" in post or "JSON.parse" in post:
            fail(f"{label} post content grew a pixel or rewarded hook")

    if credits(lp2) not in post2:
        fail("LP-02 image credits were dropped")
    if "best-easy-dating-apps-lp-01-art01" not in post1:
        fail("LP-01 art01 href missing")
    if "instant-matchmaking-apps-lp-02-art01" not in post2:
        fail("LP-02 art01 href missing")
    if "best-easy-dating-apps-lp-01-art01" in post2.split("PAGE TITLE")[-1] and "instant-matchmaking-apps-lp-02-art01" not in post2:
        fail("LP-02 buttons were pointed at LP-01")

    plugin = ROOT / "shared/plugins/credityup-platform"
    blob = "\n".join(p.read_text(encoding="utf-8") for p in plugin.rglob("*") if p.is_file())
    for banned in ("fbq(", "rewardedSlotGranted", "JSON.parse =", "redirect_url", "ramAdGate"):
        if banned in blob:
            fail(f"plugin contains {banned}")
    if 'tag="interstitial"' in blob or "tag='interstitial'" in blob:
        fail("home template prints a second interstitial")
    if "AIza" in blob:
        fail("a real Firebase apiKey was committed")
    if "YOUR_FIREBASE_API_KEY" not in blob or "YOUR_VAPID_PUBLIC_KEY" not in blob:
        fail("Firebase placeholders missing")
    leads = (plugin / "assets/leads.js").read_text(encoding="utf-8")
    if "location.search" in leads or "location.href" in leads:
        fail("lead script reads the URL")
    if "<button" in leads:
        fail("lead form uses a button element (would fire InitiateCheckout)")
    push = (plugin / "assets/push.js").read_text(encoding="utf-8")
    if "<button" in push or "dataLayer" in push:
        fail("push script uses a button or dataLayer")
    if "masterRewarded" not in push:
        fail("push script no longer avoids the rewarded modal")
    home_php = (plugin / "includes/home.php").read_text(encoding="utf-8")
    home = (plugin / "templates/front-page.php").read_text(encoding="utf-8")
    if "'content1'" not in home_php or "<winupads" not in home:
        fail("home slot tag missing")
    if "min-height: 300px" not in (plugin / "assets/home.css").read_text(encoding="utf-8"):
        fail("home slot is not reserved")
    if "wp_head()" not in home or "wp_footer()" not in home:
        fail("home template dropped wp_head/wp_footer")
    topic = (plugin / "templates/free-credits.php").read_text(encoding="utf-8")
    if 'tag="credits_rewarded"' not in home or 'trigger="#cy-reward-unlock"' not in home or 'id="rewards"' not in home:
        fail("credits cascade mount missing on the home")
    if 'tag="credits_rewarded"' not in topic or "free-credits" not in home_php or "Free Credits" not in home_php:
        fail("free credits topic missing")
    if "No articles in this topic yet" not in topic:
        fail("topic page has no room for future articles")
    for lp_name in ("us/us_lp-01.html", "us/us_lp-02.html", "shared/snippets/04_rewarded_same_page_fix.html"):
        if "credits_rewarded" in (ROOT / lp_name).read_text(encoding="utf-8", errors="replace"):
            fail(f"credits tag leaked into {lp_name}")
    if "loan" not in home.lower() and "not a loan" not in home.lower():
        fail("rewards copy does not say this is not a loan")
    if "<button" in home:
        fail("home template added a button element")

    snippet4 = (ROOT / "shared/snippets/04_rewarded_same_page_fix.html").read_text(encoding="utf-8")
    snippet5 = (ROOT / "shared/snippets/05_cy_modern_loader.html").read_text(encoding="utf-8")
    if "MODE = 'reload'" not in snippet4 or "slotRenderEnded" not in snippet5:
        fail("HFCM snippets 4 or 5 were edited")

    for value in ("quick_casual", "serious_relationship"):
        if value not in post1 or value not in post2:
            fail(f"quiz value missing from post html: {value}")
    quiz = (ROOT / "shared/snippets/02_quiz_en.html").read_text(encoding="utf-8")
    if "We found 3 highly compatible platforms" in quiz:
        fail("quiz result claim was not replaced")
    if "instant-matchmaking-apps-lp-02-art01" not in quiz or "best-easy-dating-apps-lp-01-art01" not in quiz:
        fail("quiz CTA routing missing an LP article")
    if "IS_LP2" not in quiz or "RESULT_TEXT" not in quiz:
        fail("quiz snippet is not split per LP")
    for hook in ("QuizStarted", "CompleteRegistration", "quick_casual", "verified_secure"):
        if hook not in quiz:
            fail(f"quiz snippet lost {hook}")
    if "fbq('track', 'Lead')" in quiz:
        fail("quiz result must not fire Lead")
    ab = (ROOT / "shared/snippets/15_cy_ab_reward.html").read_text(encoding="utf-8")
    for piece in ("cy_ab", "variant !== 'B'", "pf-dbtn", "60000", "4000", "Skip", "Watch a short ad to see your recommended apps?"):
        if piece not in ab:
            fail(f"A/B snippet missing {piece}")
    if 'tag="interstitial"' in ab or "fbq('track', 'Lead')" in ab:
        fail("A/B snippet adds an interstitial or a Lead event")
    if "best-easy-dating-apps-lp-01-art01" not in ab or "instant-matchmaking-apps-lp-02-art01" not in ab:
        fail("A/B snippet lost an article URL")
    import re as _re
    live = ROOT / "docs/history/live-2026-11-18"
    for name in (
        "lp-01-art01", "lp-01-art02", "lp-01-art03",
        "lp-02-art01", "lp-02-art02", "lp-02-art03",
    ):
        post = (ROOT / "us/content" / f"{name}.post.html").read_text(encoding="utf-8")
        if "hookup" in post.lower() or "patrocino" in post.lower() or "sophie" in post.lower():
            fail(f"{name} contains a banned word")
        if post.count('tag="content1"') != 1 or post.count('tag="content2"') != 1:
            fail(f"{name} ad slots changed")
        raw = (live / f"{name}.html").read_text(encoding="utf-8", errors="replace")
        body_at = raw.find("<body")
        start = raw.find('<div class="winup-ad-block', body_at)
        end = raw.find('id="comments"', start)
        for anchor in _re.findall(r'<a [^>]*data-pf="btn"[^>]*>[\s\S]*?</a>', raw[start:end]):
            if anchor not in post:
                fail(f"{name} lost a button")
    for post_path in (ROOT / "us/content").glob("*.post.html"):
        text = post_path.read_text(encoding="utf-8").lower()
        if "hookup" in text or "patrocino" in text:
            fail(f"banned word in {post_path.name}")
    for value in ("age_18_30", "age_31_50", "age_50_plus", "verified_secure", "people_nearby", "QuizStarted", "rewardedSlotGranted"):
        if value not in lp1:
            fail(f"snapshot lost quiz/pixel hook: {value}")

    if FAILS:
        print("FAILED")
        for item in FAILS:
            print("-", item)
        return 1
    print("ok quiz, buttons, content1, pixel boundary")
    return 0


if __name__ == "__main__":
    sys.exit(main())
