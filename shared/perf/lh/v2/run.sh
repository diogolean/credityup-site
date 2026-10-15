ADS='--blocked-url-patterns=*doubleclick.net* --blocked-url-patterns=*googlesyndication.com* --blocked-url-patterns=*googletagservices.com* --blocked-url-patterns=*adservice.google* --blocked-url-patterns=*fundingchoicesmessages* --blocked-url-patterns=*googleadservices* --blocked-url-patterns=*viralnetwork*'
run(){ [ -s $2.json ] || CHROME_PATH=/usr/bin/google-chrome timeout 150 lighthouse "$1" $ADS --only-categories=performance --output=json --output-path=$2.json --chrome-flags="--headless=new --no-sandbox" --quiet; echo done $2; }
for i in 1 2 3; do
run https://us.credityup.com/best-easy-dating-apps-lp-01/ lp1-v2-$i
run https://us.credityup.com/instant-matchmaking-apps-lp-02/ lp2-v2-$i
done; echo ALLDONE
