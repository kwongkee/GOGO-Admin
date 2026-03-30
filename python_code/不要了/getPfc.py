# -*- coding:utf-8 -*-
import time,random,urllib2,sys,json,requests,io,pymysql,os,urllib3,logging,traceback,re
from bs4 import BeautifulSoup
from lxml import etree

#获取请求的参数
reload(sys)  
sys.setdefaultencoding('utf8')

#记录错误
logging.basicConfig(filename='/www/wwwroot/gogo/python_code/log.txt', level=logging.DEBUG,
     format='%(asctime)s - %(levelname)s - %(message)s')

#隧道代理-快代理
tunnel = "tps632.kdlapi.com:15818"
username = "t15102282001142"
password = "vzpo77tz"

#连接数据库
db = pymysql.connect(host='rm-wz9mt4j79jrdh0p3zgo.mysql.rds.aliyuncs.com', port=3306, user='gogo198', passwd='Gogo@198', db='shop', charset='utf8')
cursor = db.cursor()

#伪造身份
headers = {
    'user-Agent':'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36',
    'cookie':'__bid_n=1869748635b8736da7afa0; Hm_lvt_5bcabb1594bf8fb7f55f8e0969252148=1678152731,1678239233,1678325333,1678416018; sensorsdata2015jssdkcross=%7B%22distinct_id%22%3A%2218697486310e59-0f487adc9f819d-26031951-2073600-186974863111173%22%2C%22first_id%22%3A%22%22%2C%22props%22%3A%7B%22%24latest_traffic_source_type%22%3A%22%E7%9B%B4%E6%8E%A5%E6%B5%81%E9%87%8F%22%2C%22%24latest_search_keyword%22%3A%22%E6%9C%AA%E5%8F%96%E5%88%B0%E5%80%BC_%E7%9B%B4%E6%8E%A5%E6%89%93%E5%BC%80%22%2C%22%24latest_referrer%22%3A%22%22%7D%2C%22%24device_id%22%3A%2218697486310e59-0f487adc9f819d-26031951-2073600-186974863111173%22%7D; Hm_lvt_f1cb923975e241e0f1745ea1532c6063=1678152731,1678239233,1678325333,1678419476; security_session_verify=9cd72d464c4399a254d9996d144c7a06; ASPSESSIONIDAQBSCDTQ=IEDKPOPAELLPCPNECIGMNBKH; csairid=1; Hm_lvt_8d2c138a7e4ea6f47d14854f1f139190=1678239306,1678436974; Hm_lpvt_8d2c138a7e4ea6f47d14854f1f139190=1678436974; Hm_lpvt_f1cb923975e241e0f1745ea1532c6063=1678437109; Hm_lpvt_5bcabb1594bf8fb7f55f8e0969252148=1678437110',
    'Connection':'close'
}

#增加重连次数和请求头信息准备
urllib3.disable_warnings()#移除警告
#请求头
# s = requests.session()
# s.keep_alive = False
# s.headers = headers
# s.verify = False#移除SSL认证
# s.timeout = 300
# s.proxies = proxies

#定义link数组和栏目名称
link_href = []
link_name = []

#获取首页的栏目链接 
def getInnerLink(url):
    #请求并获取页面相应数据
    # response = s.get(url)
    proxies = {
        "http": "http://%(user)s:%(pwd)s@%(proxy)s/" % {"user": username, "pwd": password, "proxy": tunnel},
        "https": "http://%(user)s:%(pwd)s@%(proxy)s/" % {"user": username, "pwd": password, "proxy": tunnel}
    }     
    response = requests.get(url=url,headers=headers,proxies=proxies,verify=False)
    
    response.encoding = response.apparent_encoding
    page_text = response.text
    bs1 = BeautifulSoup(page_text,'html.parser')
    
    #根据指定的栏目（跨境物流服务，集运服务，全球仓储）来获取内页链接
    # result_ul = bs1.find('ul',id='header-menu')
    result_li = bs1.find_all('li',class_="parent")
    for item in result_li:
        #跨境物流服务
        menu_1 = item.find_all('div',class_="x12-5")
        if menu_1 != '':
            for item2 in menu_1:
                menu2 = item2.find_all('li')
                for item3 in menu2:
                    menu4 = item3.find_all('a')
                    for item4 in menu4:
                        link_href.append(item4.get('href'))
                        link_name.append(item4.get_text())
                        
        #集运服务，全球仓储
        menu_2 = item.find_all('div',class_="cangchu-nav")
        if menu_2 != '':
            for item2 in menu_2:
                menu2 = item2.find_all('li')
                for item3 in menu2:
                    menu4 = item3.find_all('a')
                    for item4 in menu4:
                        link_href.append(item4.get('href'))
                        title = item4.find('p').get_text()
                        link_name.append(title.strip())
    
    #先查询数据表有无此名称
    for index, title in enumerate(link_name):
        sql = 'select id from ims_centralize_pfc_menu_list where title = %s '
        cursor.execute(sql,(str(title)))
        data = cursor.fetchone()
        print(title)
        exit()
        pid = ''
        #没有此名称则插入数据表
        if data is None:
            sql = 'insert into ims_centralize_pfc_menu_list (title,py_id) values (%s,%s)'
            cursor.execute(sql,(title,1))
            db.commit()
            pid = db.insert_id()
        else:
            pid = data[0]
        
        #获取链接里的内页
        get_content(link_href[index],pid,title)
        
    
    #执行对比，请求接口
    # requests.get("https://admin.gogo198.cn/foll/public/?s=api/centralize/contrast")
    # for url in link_href:
        # get_content(url)
        
#根据栏目内链接进行爬取数据到数据表
def get_content(url,pid,title):
    time.sleep(1)
    
    proxies = {
        "http": "http://%(user)s:%(pwd)s@%(proxy)s/" % {"user": username, "pwd": password, "proxy": tunnel},
        "https": "http://%(user)s:%(pwd)s@%(proxy)s/" % {"user": username, "pwd": password, "proxy": tunnel}
    }  
    
    try:
        # response = s.get(url)
        response = requests.get(url=url,headers=headers,proxies=proxies,verify=False)
    except:
        response = requests.get(url=url,headers=headers,proxies=proxies,verify=False)
        try:
            response = requests.get(url=url,headers=headers,proxies=proxies,verify=False)
        except:
            response = requests.get(url=url,headers=headers,proxies=proxies,verify=False)
        
        
    response.encoding = response.apparent_encoding
    page_text = response.text
    bs1 = BeautifulSoup(page_text,'html.parser')
    
    #获取指定class=""下的html
    # title = bs1.find('title').get_text()
    # title = title.replace('PFC皇家物流','GOGO物流')
    
    content = bs1.find('article',class_="detail")
    if content is None:
        content = bs1.find('article',class_="margin-large-top")
        if content is None:
            content = bs1.find('div',class_="am-container")
            if content is None:
                content = bs1.find('article',class_="am-article")
                if content is None:
                    content = bs1.body
    
    #替换图片为空
    if content != '':
        #将内容进行json格式化后存入数据表
        content = json.dumps(str(content))
        timee = int(time.time())
        content = re.sub('\<img .*?\>','',content)
        # content = re.sub('\<form .*?\>','',content)
        
        try:
            sql = 'insert into ims_centralize_pfc_list (title,url,content,createtime,pid) values (%s,%s,%s,%s,%s)'
            cursor.execute(sql,(title,url,content,timee,pid))
            
            db.commit()
            
            # db.close()
            print(title+' completed!!')
        except:
            logging.debug(traceback.format_exc())
            db.rollback()
            print(title+' Failed!!')
    
if __name__ == '__main__':
    getInnerLink('https://www.pfcexpress.com/')
    # getInnerLink('https://admin.gogo198.cn/python_code/test.html')
    # get_content('https://www.pfcexpress.com/hkfedex')