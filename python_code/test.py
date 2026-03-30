# coding:utf-8
# from searx.searxng import SearxSearchWrapper
# search = SearxSearchWrapper(searx_host="http://127.0.0.1:8888")
# results = search.run("跨境电商")
from searx import SearxSearchWrapper
search = SearxSearchWrapper(searx_host="http://127.0.0.1:8888")
results = search.run("跨境电商")
print(results)