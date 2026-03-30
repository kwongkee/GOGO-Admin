# -*- coding:utf-8 -*-
import time,random,urllib2,sys,json,requests,io,pymysql,os,urllib3,logging,traceback,re,chardet
from bs4 import BeautifulSoup
from lxml import etree

#author：阿新
#function_name:在集运总后台配置通用网站爬取功能
#create_time: 2023/03/27

#获取请求的参数
reload(sys)  
sys.setdefaultencoding('utf8')
website_id = sys.argv[1]#爬取网址的id
website_url = sys.argv[2]#爬取网址的url

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
    #'cookie':'Hm_lvt_2620280f16ac55aff03ddc777d6c29da=1680252401; Hm_lpvt_2620280f16ac55aff03ddc777d6c29da=1680252911',
    'cookie':'__bid_n=1869748635b8736da7afa0; Hm_lvt_5bcabb1594bf8fb7f55f8e0969252148=1678152731,1678239233,1678325333,1678416018; sensorsdata2015jssdkcross=%7B%22distinct_id%22%3A%2218697486310e59-0f487adc9f819d-26031951-2073600-186974863111173%22%2C%22first_id%22%3A%22%22%2C%22props%22%3A%7B%22%24latest_traffic_source_type%22%3A%22%E7%9B%B4%E6%8E%A5%E6%B5%81%E9%87%8F%22%2C%22%24latest_search_keyword%22%3A%22%E6%9C%AA%E5%8F%96%E5%88%B0%E5%80%BC_%E7%9B%B4%E6%8E%A5%E6%89%93%E5%BC%80%22%2C%22%24latest_referrer%22%3A%22%22%7D%2C%22%24device_id%22%3A%2218697486310e59-0f487adc9f819d-26031951-2073600-186974863111173%22%7D; Hm_lvt_f1cb923975e241e0f1745ea1532c6063=1678152731,1678239233,1678325333,1678419476; security_session_verify=9cd72d464c4399a254d9996d144c7a06; ASPSESSIONIDAQBSCDTQ=IEDKPOPAELLPCPNECIGMNBKH; csairid=1; Hm_lvt_8d2c138a7e4ea6f47d14854f1f139190=1678239306,1678436974; Hm_lpvt_8d2c138a7e4ea6f47d14854f1f139190=1678436974; Hm_lpvt_f1cb923975e241e0f1745ea1532c6063=1678437109; Hm_lpvt_5bcabb1594bf8fb7f55f8e0969252148=1678437110',
    'Connection':'close'
}

#增加重连次数和请求头信息准备
urllib3.disable_warnings()#移除警告

#获取首页的栏目链接 
def getInnerLink(url,plan_id):
    #定义link数组和栏目名称
    link_href = []
    link_name = []
    link_img = []

    #获取方案标签
    sql = 'select * from ims_centralize_crawl_label where plain_id = %s order by displayorder asc'
    cursor.execute(sql,(plan_id))
    labels = cursor.fetchall()

    #使用代理获取页面
    proxies = {
        "http": "http://%(user)s:%(pwd)s@%(proxy)s/" % {"user": username, "pwd": password, "proxy": tunnel},
        "https": "http://%(user)s:%(pwd)s@%(proxy)s/" % {"user": username, "pwd": password, "proxy": tunnel}
    }     
    # proxies=proxies,
    response = requests.get(url=url,headers=headers,verify=False)
    response.encoding = response.apparent_encoding
    page_text = response.text
    bs1 = BeautifulSoup(page_text,'html.parser')
    content_html = ''
    #根据标签来获取内页链接
    for key,item in enumerate(labels):
        #(0：标签ID, 1：网址ID, 2：方案ID,3：标签名称,4：标签类别（1id,2class,3无）,5：类别名称,6：标签属性（1href,2text,3src,4无）, 7：往下爬取（1是2否）, 8：排序)
        if content_html != '':
            #第n次循环
            content_html = BeautifulSoup(str(content_html), "html.parser")
            if item[4] is 1:
                content_html = content_html.find_all(str(item[3]),id=str(item[5]))
            elif item[4] is 2:
                #content_html = content_html.find_all(str(item[3]),class_=str(item[5]))
                class_split = item[5].split(',')
                flag = 0
                for key2,item2 in enumerate(class_split):
                    if flag is 0:
                        content_html = content_html.find_all(str(item[3]),class_=str(item2))

                    if content_html is None:
                        continue
                    else:
                        flag = 1
            else:
                content_html = content_html.find_all(str(item[3]))
        else:
            #首次循环
            if item[4] is 1:
                content_html = bs1.find_all(str(item[3]),id=str(item[5]))
            elif item[4] is 2:
                class_split = item[5].split(',')
                flag = 0
                for key2,item2 in enumerate(class_split):
                    if flag is 0:
                        content_html = bs1.find_all(str(item[3]),class_=str(item2))

                    if content_html is None:
                        continue
                    else:
                        flag = 1
            else:
                content_html = bs1.find_all(str(item[3]))

        content_html = str(content_html)
        content_html = content_html.replace('\\\\','\\')

        if item[6] is 1:
            #获取a标签下的href
            content_html = content_html.strip('[').strip(']')
            content_html = BeautifulSoup(content_html, "html.parser")
            #content_html = content_html.decode('unicode_escape').decode('unicode_escape')
            
            for item2 in content_html:
                #decode("unicode_escape")
                
                if len(item2) != 2:
                    link_href.append(item2.get('href'))
                    link_name.append(item2.get_text())
          
            # print(labels[key+1])
            # exit()
            if item[7] is 1:
                #1、先将数据保存到数据库;
                #先查询数据表有无此名称
                for index, title in enumerate(link_name):
                    decode_title = title.decode("unicode_escape").strip()
                    
                    sql = 'select id from ims_centralize_pfc_menu_list where title = %s '
                    cursor.execute(sql,(str(decode_title)))
                    data = cursor.fetchone()
                    
                    pid = 0
                    #没有此名称则插入数据表
                    if data is None:
                        sql = 'insert into ims_centralize_pfc_menu_list (title,py_id) values (%s,%s)'
                        cursor.execute(sql,(decode_title,item[1]))
                        pid = db.insert_id()
                        db.commit()
                    else:
                        pid = data[0]

                    #2、往下爬取,获取链接里的内页
                    get_content(link_href[index],pid,decode_title,labels[key+1])
                #跳出循环
                break
            else:
                continue
        elif item[6] is 2:
            #获取标签下的text
            link_name.append(content_html.get_text())
            #待做...
            continue
        elif item[6] is 3:
            #获取img标签下的src
            link_img.append(content_html.get('src'))
            #待做...
            continue
        else:
            #获取标签下的标签
            continue

    #请求接口：执行对比-有新的内容马上通知管理员。
    # requests.get("https://admin.gogo198.cn/foll/public/?s=api/centralize/contrast")
        
#根据栏目内链接进行爬取数据到数据表
def get_content(url,pid,title,item):
    #(0：标签ID, 1：网址ID, 2：方案ID,3：标签名称,4：标签类别（1id,2class,3无）,5：类别名称,6：标签属性（1href,2text,3src,4无）, 7：往下爬取（1是2否）, 8：排序)
    time.sleep(1)

    proxies = {
        "http": "http://%(user)s:%(pwd)s@%(proxy)s/" % {"user": username, "pwd": password, "proxy": tunnel},
        "https": "http://%(user)s:%(pwd)s@%(proxy)s/" % {"user": username, "pwd": password, "proxy": tunnel}
    }  
    
    try:
        # proxies=proxies,
        response = requests.get(url=url,headers=headers,verify=False)
    except:
        response = requests.get(url=url,headers=headers,verify=False)
        try:
            # proxies=proxies,
            response = requests.get(url=url,headers=headers,verify=False)
        except:
            response = requests.get(url=url,headers=headers,verify=False)

    response.encoding = response.apparent_encoding
    page_text = response.text
    bs1 = BeautifulSoup(page_text,'html.parser')
    content = ''
    #获取指定class=""下的html
    # title = bs1.find('title').get_text()
    # title = title.replace('PFC皇家物流','GOGO物流')
    
    if item[4] is 1:
        content = bs1.find(str(item[3]),id=str(item[5]))
    elif item[4] is 2:
        #content = bs1.find(str(item[3]),class_=str(item[5]))
        class_split = item[5].split(',')
        flag = 0
        for key2,item2 in enumerate(class_split):
            if flag is 0:
                content = bs1.find(str(item[3]),class_=str(item2))

            if content is None:
                continue
            else:
                flag = 1
    else:
        content = bs1.find(str(item[3]))

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
    #获取爬取网址配置的方案
    sql = 'select * from ims_centralize_crawl_plain where pid = %s '
    cursor.execute(sql,(website_id))
    plan = cursor.fetchall()

    #循环方案获取标签
    for index,item in enumerate(plan):
        getInnerLink(website_url,item[0])