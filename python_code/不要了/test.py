#-*- coding=utf-8 -*-
import time,random,urllib2,sys,json,requests,io,pymysql,os
from bs4 import BeautifulSoup
from lxml import etree
reload(sys)  
sys.setdefaultencoding('utf8')


office_ids = ''

#连接数据库
db = pymysql.connect(host='rm-wz9mt4j79jrdh0p3zgo.mysql.rds.aliyuncs.com', port=3306, user='gogo198', passwd='Gogo@198', db='shop', charset='utf8')
cursor = db.cursor()
lis = '(出租) 免佣  天安中心113方配全新家具31南向 现代化装修'
lis = lis.strip()
lis = lis.split(")")
title = lis[1]
title = title.strip()
print(title)


# openid = sys.argv[2]
#openid = 'sss'

# try:
#     keywords = sys.argv[1]
#     #keywords = '千灯湖办公楼'
#     url = 'https://fs.58.com/qiandenglu/zhaozu/?key='+keywords
#     # 伪造身份
#     headers = {
#         'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3947.100 Safari/537.36',
#         #'User-Agent': ua.random,str(random.randint(11111, 99999))
#         'Cookie':'f=n; commontopbar_new_city_info=222%7C%E4%BD%9B%E5%B1%B1%7Cfs; f=n; userid360_xml=643CFA60C22A3FBF5C62BC19516CA451; time_create=1667015353690; SECKEY_ABVK=uIJ1NMVr5gUt5WY5taWKrTfAp07PQVIT8Rmg8+pTFNg%3D; BMAP_SECKEY=L7qc8Mf0xz9RA9O3CjgnktBtK4mypk16ADw2Aqxby9ABsg7mzsBf9-pHUkgMp004e6xeVCgkqgQpJe4TmJo7yY0RD3MRIa8NpM-WEbWF7QXN_VujxTUdXDbqAqsUERlAbAWtKtRBF1JQvWeSDuv9uUL0c9Kw6sVI2RQCqAvkd1EOl7K7GvFDp9i6mrKikUpn; commontopbar_new_city_info=222%7C%E4%BD%9B%E5%B1%B1%7Cfs; 58home=fs; f=n; id58=CocI02MzpdpVb3bkCDTEAg==; city=fs; 58tj_uuid=8f0166d1-7019-4590-84f9-bff0f6263b6a; als=0; wmda_uuid=6e59a46edd52ba0442a9669d9ad0a9d9; wmda_new_uuid=1; wmda_visited_projects=%3B11187958619315; ajk-appVersion=; xxzlcid=07024a00976e4bed88d8282ffea6d455; xxzl-cid=07024a00976e4bed88d8282ffea6d455; aQQ_ajkguid=6b9d733d-ac41-49e2-8b0c-67deab45f630; __bid_n=18381d554a6c03e3f44207; FPTOKEN=30$KGUk1HRKMnj1GofTlCukm2mwUO7NuctqykrjXVymTdASCZIyAyiVgG1e/Jbwis/KcpooEB93ZG+Kjqhd7TudksNCktBfknhIhaWJ+kQR5PKUdlJxhff8MY1HGGmvWBQBC936PMboHl1tJSy21jXUh4AHktvHPwYHAxFDdE7eMjBoMuRTKZbS+aMeoCSSeXnLv0l7kmLvyBfkxz3K4X/hcu1pFbfCrp7P3+C4bozVMGEH5V4/kN/QvZmE+hv1xUpfzAyKqUO1ZLDBsH/Ep7Cgwosyjru5nYblaiPq8ISXhKBmRy45+tBxphtlc6mzBMSzz419YThGVxDaPqEpOpe8OZVBlzNAKugzv1NTXOMDz20el6PG79ts+K7KB/+DiM/2|pCGcO9sPfySLn0uKI076/tTnKvDpZLbGc8qC4dxhF2o=|10|5db8be52f7bcd78012b9c2720c08f453; sessid=B1BCAC09-A582-49B8-9FA0-56372CD78B9B; ctid=222; sessionid=afb18581-a887-460c-918e-8042da938bd1; __utmc=253535702; ppStore_fingerprint=E4DEDA9EB26282A2B1765C071B21CE961BE41F4293C2A9B5%EF%BC%BF1664445415701; commontopbar_new_city_info=222%7C%E4%BD%9B%E5%B1%B1%7Cfs; commontopbar_ipcity=fs%7C%E4%BD%9B%E5%B1%B1%7C0; fzq_h=2d5178bc9c89f3055a31e4edce1657c0_1664506607205_2a6f252b81004ab4953b76f0bc7b74b5_249542305; __utma=253535702.2084141816.1664423265.1664440666.1664509471.3; __utmz=253535702.1664509471.3.3.utmcsr=fs.58.com|utmccn=(referral)|utmcmd=referral|utmcct=/; hots=%5B%7B%22d%22%3A0%2C%22s1%22%3A%22%E5%8D%83%E7%81%AF%E6%B9%96%22%2C%22s2%22%3A%22%22%2C%22n%22%3A%22sou%22%7D%5D; new_session=1; new_uv=13; utm_source=; spm=; init_refer=https%253A%252F%252Ffs.58.com%252Fqiandenglu%252Fzhaozu%252F%253Fkey%253D%2525E7%25258B%2525AE%2525E5%2525B1%2525B1%2525E5%25258A%25259E%2525E5%252585%2525AC%2525E6%2525A5%2525BC; xxzl_cid=07024a00976e4bed88d8282ffea6d455; xxzl_deviceid=5tDyQOBEiDfK8UPqoRKC1Fgr7y4TZzf21VHEDaHpOKRt8lz1mS9M0eQzoVHEbvaH',
#         'Referer':'https://fs.58.com/?utm_source=market&spm=u-2d2yxv86y3v43nkddh1.BDPCPZ_BT'
#     }
#     page = requests.get(url,headers=headers,timeout=300000)
#     html = page.text
#     file_name = os.getcwd()+"/../python_code/page_html/page_"+str(random.randint(11111, 99999))+"_"+openid+".html"
    
#     try:
#         f = open(file_name, 'w')
#         #  写入文件
#         f.write(html)
#         # 关闭文件
#         f.close()
#     except Exception as e:
#         print('er1 '+str(e))
        
    
#     # htmlfile = open(file_name, 'r') #打开1.html文件
#     # htmlhandle = htmlfile.read() #返回整个文件
#     # soup = BeautifulSoup(htmlhandle, "lxml") #使用Beautifulsoup解析
# except Exception as e:
#     print('er2 '+str(e))
#     # file_name = "log.log"
#     # with io.open(file_name, "w", encoding="utf-8") as f:
#     #     f.write(f.readlines())