ADS='--blocked-url-patterns=*doubleclick.net* --blocked-url-patterns=*googlesyndication.com* --blocked-url-patterns=*googletagservices.com* --blocked-url-patterns=*adservice.google* --blocked-url-patterns=*fundingchoicesmessages* --blocked-url-patterns=*googleadservices* --blocked-url-patterns=*viralnetwork*'
W='--blocked-url-patterns=*api.winup.network* --blocked-url-patterns=*winup-ad-engine* --blocked-url-patterns=*rewarded*'
run(){ [ -s $2.json ] || CHROME_PATH=/usr/bin/google-chrome timeout 150 lighthouse "$1" $ADS $3 --only-categories=performance --output=json --output-path=$2.json --chrome-flags="--headless=new --no-sandbox" --quiet; echo done $2; }
run https://us.credityup.com/best-easy-dating-apps-lp-01/ lp1-A
run https://us.credityup.com/best-easy-dating-apps-lp-01/ lp1-B "$W"
run https://us.credityup.com/instant-matchmaking-apps-lp-02/ lp2-A
run https://us.credityup.com/instant-matchmaking-apps-lp-02/ lp2-B "$W"
run https://de.credityup.com/apps-leichte-dates-unkomplizierte-treffen-lp-02-art01/ lp3-A
run https://de.credityup.com/apps-leichte-dates-unkomplizierte-treffen-lp-02-art01/ lp3-B "$W"
run https://de.credityup.com/digitale-romanzen-liebe-finden-lp-01/ lp4-A
run https://de.credityup.com/digitale-romanzen-liebe-finden-lp-01/ lp4-B "$W"
run https://en.blovibe.com/lp08-hottest-new-casual-dating-apps/ c1-A
run https://usa.udenze.com/tips-for-finding-a-girlfriend-online-art01/ c2-A
run https://de.bellybat.com/lp05-best-dating-coaching-fur-manner-2026/ c3-A
run https://en.minutolivre.com/bc-02-meeting-women-online/ c4-A
echo ALLDONE
