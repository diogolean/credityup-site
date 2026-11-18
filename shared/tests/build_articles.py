#!/usr/bin/env python3
"""Paste-ready article HTML. Ad slots and .pf-dbtn anchors are copied from the live snapshot."""
import pathlib
import re

ROOT = pathlib.Path(__file__).resolve().parents[2]
LIVE = ROOT / "docs/history/live-2026-11-18"
OUT = ROOT / "us/content"
PREVIEW = ROOT / "us/preview"

AD1 = '<div class="winup-ad-block winup-ad-block-1" style="text-align:center;"><winupads tag="content1" label="Ad content"></winupads></div>'
AD2 = '<div class="winup-ad-block winup-ad-block-2" style="text-align:center;"><winupads tag="content2" label="Ad content" lazy></winupads></div>'

DISCLAIMER = (
    '<p><em>Plan names and prices change. Read the price, the renewal date, and what is included '
    'on the App Store or Google Play screen before you subscribe. A paid plan is not a match guarantee.</em></p>'
    '<p>Sources: Google Play — Subscriptions; Federal Trade Commission — Online Dating Scams.</p>'
)

ARTICLES = {
    "lp-01-art01": {
        "title": "Free vs paid on easy dating apps",
        "meta": "What the free plan includes, and what Tinder Gold, Tinder Platinum, Bumble Premium, and Hinge+ add. The price is on the store screen.",
        "intro": [
            "The easy dating apps people actually compare in the United States let you start free. The paid decision is a later screen: Tinder Gold or Tinder Platinum, Bumble Premium or a Bumble Boost, and Hinge+ or HingeX.",
            "This guide stays on Credityup. It does not open a store and it does not start a subscription. Use the free plan first, then decide whether one extra is worth paying for.",
        ],
        "before": [
            ("What the free plan is for", [
                "On a free plan you can usually build a profile, set an age range and a distance, and send a limited number of likes or introductions. Spend a few days there. You learn whether people nearby reply, and whether the profile takes one sitting or a long form.",
            ]),
        ],
        "after": [
            ("Tinder Gold, Tinder Platinum, and the rest", [
                "Tinder Gold is the tier built around seeing who already liked you. Tinder Platinum sits above that and is sold as more priority in front of the people you like. Bumble Premium adds filters and more control on top of Bumble's free messaging rules. A Bumble Boost is separate: it shows your profile to more people for a short time, and it is not the same product as the subscription. Hinge+ and HingeX sit on an app that asks for prompts instead of a one-line bio.",
                "The monthly price and the renewal date are on the store listing and again on the confirm screen. They are not the same in every promotion. Do not trust a dollar amount in an old review.",
            ]),
            ("One extra, not the whole badge", [
                "Ask which single extra would change the free plan you already tried: seeing who liked you, more likes, a filter, or a boost. If none of those would, skip the premium subscription. A paid badge does not make someone a better fit.",
            ]),
            ("Verification is a different feature", [
                "Photo verification and phone verification are not included just because you paid. Check who can see a verified photo, and how to pause the profile without deleting it. For a first meeting, use a public place and your own way home. Do not send money or identity documents to someone you have not met.",
            ]),
        ],
    },
    "lp-01-art02": {
        "title": "What Tinder Gold, Bumble Premium, and Hinge+ add",
        "meta": "A plain comparison of the extras behind Tinder Gold, Tinder Platinum, Bumble Premium, Bumble Boost, Hinge+, and HingeX.",
        "intro": [
            "The paid tiers on the large US dating apps sell a short list of extras. The names differ. The extras repeat: more likes, a look at who already liked you, tighter filters, and a boost.",
            "Hinge is the slower one, built around prompts. Tinder is the fast deck, with Tinder Gold and Tinder Platinum as the paid layers. Bumble Premium is the subscription; a Bumble Boost is the short burst on top. None of them is a promise that a match will reply.",
        ],
        "before": [
            ("See who liked you", [
                "This is the extra Tinder Gold is known for, and a version of it shows up on other premium plans. It saves time only if you were already going to look at those profiles. It does not create interest that was not there.",
            ]),
        ],
        "after": [
            ("Filters and like limits", [
                "Free plans already filter by age and distance. Premium filters go further, and the like limit goes up. Read the confirm screen to see which filters are actually locked. If the free filters already match what you want, the subscription is optional.",
            ]),
            ("Boosts", [
                "A boost, including a Bumble Boost, puts your current profile in front of more people for a limited time. It does not rewrite the profile and it does not fill a quiet city. Try the free plan in your area before you buy one.",
            ]),
            ("Hinge+ and HingeX", [
                "Hinge's paid plans sit on top of the prompts. They are sold as more likes and more ways to see who is interested. The current plan name is inside the app. Compare that screen with Tinder Gold and Bumble Premium only on the extras you would use, not on the logo.",
            ]),
        ],
    },
    "lp-01-art03": {
        "title": "When a dating premium plan is worth opening",
        "meta": "How to use the free plan, the first message, and verification before you open Tinder Gold, Bumble Premium, or Hinge+.",
        "intro": [
            "A premium subscription is worth opening only after the free plan has told you something. If nobody nearby is active, Tinder Gold, Bumble Premium, and Hinge+ will not invent them.",
            "Set the profile, send a few ordinary first messages, and check verification. Then look at the price screen.",
        ],
        "before": [
            ("The profile, still on the free plan", [
                "Finish a profile you can explain in one sitting: a few photos, a city, and one line about what you want. Prompts, which Hinge asks for, are useful on any app. A boost later only shows this profile. It does not improve a blank one.",
            ]),
        ],
        "after": [
            ("The first message", [
                "Use one detail from the profile. You do not need a paid sticker or a timed game to start. If the only way to talk is behind the paywall, that is part of the decision, and the price of that gate is on the store screen.",
            ]),
            ("What the paid screen is selling", [
                "When the app offers Tinder Platinum, Tinder Gold, Bumble Premium, a Bumble Boost, Hinge+, or HingeX, read the list. More likes, see who liked you, filters, boost. Pick at most one. Note the renewal date before you confirm.",
            ]),
            ("Safety does not come with the receipt", [
                "Verification is separate from the charge. A paid account can still be dishonest. Meet in public, tell a friend, and do not send money. The FTC's notes on romance scams are the right length for this.",
            ]),
        ],
    },
    "lp-02-art01": {
        "title": "Instant matchmaking: free start, then the paid boost",
        "meta": "What quick matching includes before you pay, and how a boost differs from Tinder Gold, Bumble Premium, and Hinge+.",
        "intro": [
            "Instant matchmaking, on the apps people use in the United States, means a short profile, your location, and an alert. It does not mean a person is waiting, and it does not mean the next screen is free.",
            "The next screen is often a premium subscription or a boost: Tinder Gold, Tinder Platinum, Bumble Premium, a Bumble Boost, Hinge+, or HingeX. This page separates the free start from that offer.",
        ],
        "before": [
            ("The three free tools", [
                "Location, so the list can be people who could meet. A short profile, so you are not writing a biography first. An alert, so a reply does not wait until you remember the app. If replies never come, the area is quiet. A paid boost will not fix that on its own.",
            ]),
        ],
        "after": [
            ("What the upgrade adds", [
                "A boost shows the profile you already wrote to more people for a short time. A subscription such as Tinder Gold or Bumble Premium usually adds more likes and a look at who already liked you. Hinge+ and HingeX are the same kind of layer on a slower app. The price and the renewal are on the store screen. They change.",
            ]),
            ("Say what you want early", [
                "One line — dating, a relationship, or friendship — saves a long thread. You do not owe a longer story on day one. The speed of the app is not a reason to skip that line.",
            ]),
            ("Verification and the first meeting", [
                "Paying does not verify the other person. Give approximate location if the app allows it, and do not type a home address into the chat. Meet in public and use your own way home.",
            ]),
        ],
    },
    "lp-02-art02": {
        "title": "Boost or subscription: how to compare the paid screen",
        "meta": "Bumble Boost versus Bumble Premium, and how that choice compares with Tinder Gold, Tinder Platinum, and Hinge+.",
        "intro": [
            "A fast dating app will offer two different charges. One is a boost, a short increase in how often the profile is shown. The other is a premium subscription that stays on until you cancel.",
            "Bumble sells those as a Bumble Boost and Bumble Premium. Tinder sells the longer one as Tinder Gold and Tinder Platinum. Hinge sells Hinge+ and HingeX. Compare the extra, then read the price on the store screen.",
        ],
        "before": [
            ("Activity in your city comes first", [
                "A large member count does not mean your city is busy on a Tuesday. Open the free plan and see whether new replies arrive. Buy a boost only after that look. Showing a profile more often in an empty area spends the fee on the same quiet list.",
            ]),
        ],
        "after": [
            ("Boost", [
                "The boost is the profile you have now, shown to more people, for a limited time. It does not add filters and it does not show who liked you after the timer ends. Check the length of the timer on the confirm screen.",
            ]),
            ("Subscription", [
                "Tinder Gold is aimed at seeing who liked you. Tinder Platinum is the tier above it. Bumble Premium and Hinge+ are the equivalent longer plans: more likes, more filters, and a view of interest that the free plan holds back. The renewal date is the part people miss. It is on that same screen.",
            ]),
            ("Verification is not either charge", [
                "A photo check is not included just because the boost or the subscription went through. Use it if the app offers it. Do not treat a paid badge as proof.",
            ]),
        ],
    },
    "lp-02-art03": {
        "title": "Filters, verification, and the price screen",
        "meta": "What to check on a fast dating app before you confirm Tinder Gold, Bumble Premium, a Bumble Boost, or Hinge+.",
        "intro": [
            "The fast apps put the paid offer close to the first session. You can still read it. Filters, verification, and the price are three separate lines on that screen.",
            "Tinder Gold, Tinder Platinum, Bumble Premium, a Bumble Boost, Hinge+, and HingeX are names for those lines. The amount due is written under the name, on the store screen, not on this page.",
        ],
        "before": [
            ("Filters you already have", [
                "Age and distance are on the free plan. Extra filters are a common reason the premium plan exists. If age and distance are enough, you do not need the longer list.",
            ]),
        ],
        "after": [
            ("Verification", [
                "Look for a photo or phone check before you upload a face photo. See who can view it, and how to pause the profile. A subscription does not run that check for the other person.",
            ]),
            ("The price screen", [
                "Before you confirm, you should see the plan name, the price, the period, and whether it renews. That is true for Tinder Gold, Bumble Premium, and Hinge+ alike. Leave the screen if any of those is missing. A review that quotes a number is not a substitute.",
            ]),
            ("After you match", [
                "One specific detail from the profile is enough for a first message. Keep the first meeting in public. Do not send money. Speed of matching is not a reason to skip that.",
            ]),
        ],
    },
}


def chunk_for(name: str) -> str:
    html = (LIVE / f"{name}.html").read_text(encoding="utf-8", errors="replace")
    body = html.find("<body")
    start = html.find('<div class="winup-ad-block', body)
    end = html.find('id="comments"', start)
    if start < 0 or end < 0:
        raise SystemExit(f"content bounds missing for {name}")
    return html[start:end]


def buttons_for(name: str) -> list[str]:
    return re.findall(r'<a [^>]*data-pf="btn"[^>]*>[\s\S]*?</a>', chunk_for(name))


def paragraphs(items: list[str]) -> str:
    return "\n".join(f"<p>{item}</p>" for item in items)


def sections(blocks: list[tuple[str, list[str]]]) -> str:
    parts = []
    for heading, paras in blocks:
        parts.append(f"<h2>{heading}</h2>")
        parts.append(paragraphs(paras))
    return "\n".join(parts)


def build(name: str, spec: dict) -> str:
    btns = buttons_for(name)
    parts = [
        "<!--",
        f"Paste into the WordPress code editor for {name.replace('lp-0', 'the article ')}.",
        f"PAGE TITLE: {spec['title']}",
        f"META DESCRIPTION: {spec['meta']}",
        "Keep the two winupads blocks and every data-pf button. In the post sidebar, remove any tag whose name is the adult-apps label called out in DEPLOY.md.",
        "-->",
        AD1,
        paragraphs(spec["intro"]),
    ]
    if btns:
        parts.append("\n".join(f"<p>{btn}</p>" for btn in btns))
    parts.append(sections(spec["before"]))
    parts.append(AD2)
    parts.append(sections(spec["after"]))
    parts.append(DISCLAIMER)
    return "\n".join(parts) + "\n"


CHROME = """<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{title}</title>
<style>
  body {{ margin:0; background:#f4f5f7; color:#2d3359; font-family:system-ui,sans-serif; }}
  .bar {{ background:#111; color:#fff; display:flex; gap:16px; align-items:center; padding:14px 18px; }}
  .bar a {{ color:#ddd; text-decoration:none; font-size:12px; letter-spacing:.06em; text-transform:uppercase; }}
  .band {{ background:#d81b60; color:#fff; text-align:center; padding:28px 16px; }}
  .band h1 {{ margin:0 auto; max-width:720px; font-size:22px; }}
  .card {{ max-width:760px; margin:-18px auto 40px; background:#fff; border-radius:12px; padding:20px; }}
  .ad {{ min-height:120px; background:#f3f1f4; color:#9a97a3; text-align:center; letter-spacing:.14em; font-size:10px; text-transform:uppercase; padding-top:10px; margin:12px 0; }}
  a.pf {{ display:block; margin:8px 0; padding:14px; text-align:center; color:#fff; text-decoration:none; font-weight:800; border-radius:8px; }}
</style>
</head>
<body>
<header class="bar"><strong>credityup</strong><a href="/category/apps/">Apps</a><a href="/category/dating/">Dating</a><a href="/category/relationships/">Relationships</a><a href="/category/free-credits/">Free Credits</a></header>
<div class="band"><h1>{title}</h1></div>
<main class="card">{body}</main>
</body>
</html>
"""


def preview_body(html: str) -> str:
    html = html.replace(AD1, '<div class="ad">Advertisement</div>')
    html = html.replace(AD2, '<div class="ad">Advertisement</div>')
    html = html.replace('class="pf-dbtn"', 'class="pf-dbtn pf"')
    return html


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    written = {}
    for name, spec in ARTICLES.items():
        text = build(name, spec)
        path = OUT / f"{name}.post.html"
        path.write_text(text, encoding="utf-8")
        written[name] = text
        if "hookup" in text.lower() or "patrocino" in text.lower() or "sophie" in text.lower():
            raise SystemExit(f"banned word in {name}")
    # Before/after previews for the two quiz destinations.
    before_titles = {
        "lp-01-art01": "Finding the Right Connection: Simplified Dating App Choices",
        "lp-02-art01": "Connecting Now: Instant Date Apps",
    }
    for key, label in (("lp-01-art01", "art-lp01"), ("lp-02-art01", "art-lp02")):
        old = chunk_for(key)
        old = re.sub(r"<img[^>]*>", "", old)
        (PREVIEW / f"before-{label}.html").write_text(
            CHROME.format(title=before_titles[key], body=preview_body(old)),
            encoding="utf-8",
        )
        (PREVIEW / f"after-{label}.html").write_text(
            CHROME.format(title=ARTICLES[key]["title"], body=preview_body(written[key])),
            encoding="utf-8",
        )
    print("wrote", len(written), "articles")


if __name__ == "__main__":
    main()
