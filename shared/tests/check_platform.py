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
    if 'tag="credits_rewarded"' not in home or 'trigger="#cy-reward-unlock"' not in home or 'id="rewards"' not in home:
        fail("credits cascade mount missing")
    if "loan" not in home.lower() and "not a loan" not in home.lower():
        fail("rewards copy does not say this is not a loan")
    if "<button" in home:
        fail("home template added a button element")

    snippet4 = (ROOT / "shared/snippets/04_rewarded_same_page_fix.html").read_text(encoding="utf-8")
    snippet5 = (ROOT / "shared/snippets/05_cy_modern_loader.html").read_text(encoding="utf-8")
    if "MODE = 'reload'" not in snippet4 or "slotRenderEnded" not in snippet5:
        fail("HFCM snippets 4 or 5 were edited")

    # Step 1 values live in the post HTML. Later steps live in HFCM snippet #2,
    # which this change does not replace. Both must still be in the snapshot.
    for value in ("quick_casual", "serious_relationship"):
        if value not in post1 or value not in post2:
            fail(f"quiz value missing from post html: {value}")
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
