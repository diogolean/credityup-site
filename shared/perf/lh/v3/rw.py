import re
V3=open('/workspace/perf/v3/loader_v3.html').read()
def rewrite(h):
  h,n=re.subn(r'<!-- HFCM by 99 Robots - Snippet # 5:.*?<!-- /end HFCM by 99 Robots -->','',h,flags=re.S)
  assert n==1
  return h.replace("<meta charset='UTF-8'>","<meta charset='UTF-8'>"+V3,1)
