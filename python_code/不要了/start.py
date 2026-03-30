#-*- coding=utf-8 -*-
import time,random,urllib2,sys,json,requests,io,pymysql,os,urllib3,logging,traceback
from bs4 import BeautifulSoup
from lxml import etree
# url = 'https://shop.gogo198.cn/api/zfb_identify_verify.php'

reload(sys)  
sys.setdefaultencoding('utf8')

logging.basicConfig(filename='/www/wwwroot/gogo/python_code/log.txt', level=logging.DEBUG,
     format='%(asctime)s - %(levelname)s - %(message)s')
#隧道代理-快代理
# tunnel = "tps632.kdlapi.com:15818"
# username = "t15102282001142"
# password = "vzpo77tz"
# proxies = {
#     "http": "http://%(user)s:%(pwd)s@%(proxy)s/" % {"user": username, "pwd": password, "proxy": tunnel},
#     "https": "http://%(user)s:%(pwd)s@%(proxy)s/" % {"user": username, "pwd": password, "proxy": tunnel}
# }

#隧道代理-品易代理
# 提取ip
# resp = requests.get("http://tiqu.pyhttp.taolop.com/getflowip?count=1&neek=50897&type=1&sep=0&sb=0&ip_si=1&mr=0")
# ip = resp.text
# ip_arr = ip.split(":")
# proxyHost = ip_arr[0]
# proxyPort = ip_arr[1]
# proxyMeta = "http://%(host)s:%(port)s" % {
#     "host": proxyHost,
#     "port": proxyPort,
# }
# proxies = {
#     "http": proxyMeta,
#     "https": proxyMeta
# }
#http://tiqu.pyhttp.taolop.com/getflowip?count=500&neek=50897&type=1&sep=1&sb=&ip_si=1&mr=0
#proxies = ''

# 伪造身份
headers = {
    'user-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36',
    'cookie':'userid360_xml=643CFA60C22A3FBF5C62BC19516CA451; time_create=1667015353690; SECKEY_ABVK=mqBirwkqMeN4/gZV1PuwkaRK/iDo7OkJlXLsRRZ1vto%3D; BMAP_SECKEY=OzWOd_OF_NbzQQRRYsMu8CDpHVyXFaNgZyMPh0SmjpD0q-ZRW4q7KDR6SAkcbBOXtARktQYAQ79CBWXywpo_BfkxUA7RXJvIMVzXICoLRnnZbDVXkiV5ZfETYXxQPrG_-qro2aorxMutIx3n4Orvk8N-md6KkRGHsgXx-Sf9BYdkXkLsaNfLqqK-6I6Oqy0F; f=n; commontopbar_new_city_info=222%7C%E4%BD%9B%E5%B1%B1%7Cfs; 58home=fs; id58=CocI02MzpdpVb3bkCDTEAg==; city=fs; 58tj_uuid=8f0166d1-7019-4590-84f9-bff0f6263b6a; als=0; wmda_uuid=6e59a46edd52ba0442a9669d9ad0a9d9; wmda_new_uuid=1; __bid_n=18381d554a6c03e3f44207; FPTOKEN=30$KGUk1HRKMnj1GofTlCukm2mwUO7NuctqykrjXVymTdASCZIyAyiVgG1e/Jbwis/KcpooEB93ZG+Kjqhd7TudksNCktBfknhIhaWJ+kQR5PKUdlJxhff8MY1HGGmvWBQBC936PMboHl1tJSy21jXUh4AHktvHPwYHAxFDdE7eMjBoMuRTKZbS+aMeoCSSeXnLv0l7kmLvyBfkxz3K4X/hcu1pFbfCrp7P3+C4bozVMGEH5V4/kN/QvZmE+hv1xUpfzAyKqUO1ZLDBsH/Ep7Cgwosyjru5nYblaiPq8ISXhKBmRy45+tBxphtlc6mzBMSzz419YThGVxDaPqEpOpe8OZVBlzNAKugzv1NTXOMDz20el6PG79ts+K7KB/+DiM/2|pCGcO9sPfySLn0uKI076/tTnKvDpZLbGc8qC4dxhF2o=|10|5db8be52f7bcd78012b9c2720c08f453; sessid=B1BCAC09-A582-49B8-9FA0-56372CD78B9B; ctid=222; __utma=253535702.2084141816.1664423265.1664440666.1664509471.3; __utmz=253535702.1664509471.3.3.utmcsr=fs.58.com|utmccn=(referral)|utmcmd=referral|utmcct=/; hots=%5B%7B%22d%22%3A0%2C%22s1%22%3A%22%E5%8D%83%E7%81%AF%E6%B9%96%22%2C%22s2%22%3A%22%22%2C%22n%22%3A%22sou%22%7D%5D; myLat=""; myLon=""; mcity=fs; ajk-appVersion=; xxzlcid=07024a00976e4bed88d8282ffea6d455; xxzl-cid=07024a00976e4bed88d8282ffea6d455; aQQ_ajkguid=80d0b9e3-77ba-4edf-8ef4-84c73672b017; wmda_visited_projects=%3B11187958619315%3B10104579731767; xxzl_smartid=46494220bb19cbd6386657e5e570fac7; fzq_h=768dc3b4c8967cb3f3b786ce6bdadd3b_1665213208452_1ce2cb17b16e4972926b12a73a69cc8a_249542383; ppStore_fingerprint=E4DEDA9EB26282A2B1765C071B21CE961BE41F4293C2A9B5%EF%BC%BF1665221536044; f=n; commontopbar_new_city_info=222%7C%E4%BD%9B%E5%B1%B1%7Cfs; commontopbar_ipcity=fs%7C%E4%BD%9B%E5%B1%B1%7C0; new_uv=22; utm_source=market; spm=u-2d2yxv86y3v43nkddh1.BDPCPZ_BT; init_refer=https%253A%252F%252Fwww.baidu.com%252Fother.php%253Fsc.0f00000oq4aANghtO1tDBZhvfzmjU2UBchbjidEEp2FbIinsSK1H28gtoVvoIJzw5anxqP4L3arDcwyP0yrI7DI1FS7wRDys8l4ZM1LxnenD7pLoLS8Ba58FSExNlu6PpvXlDofLBHhHF-_e0IC1y8lCKTAUOSxyTDwqCNP1jHcs8UWqICGztNjQ-CzSFsAAhR_ZMgcJ5tBe2gIoc0Ij2SxD-bdr.DY_NR2Ar5Od66z3PrrW6ButVvkDj3n-vHwYxw_vU85YIMAQV8qhORGyAp7WIu8L6.TLFWgv-b5HDkrfK1ThPGujYknHb0THY0IAYqPHWPoQ5Z0ZN1ugFxIZ-suHYs0A7bgLw4TARqnsKLULFb5HR31pz1ksKzmLmqn0KdThkxpyfqnHR1rHR4njmdPfKVINqGujYkPjbvP1mYrfKVgv-b5HDkP1TLPH6Y0AdYTAkxpyfqnHc3nWm0TZuxpyfqn0KGuAnqiD4K0ZKGujYY0APGujY4n0KWThnqPjcznHb%2526dt%253D1665279063%2526wd%253D58%2525E5%252590; wmda_session_id_11187958619315=1665279100084-27eacf0e-8032-cda4; new_session=0; wmda_session_id_10104579731767=1665279112489-b453983e-2732-5648; crmvip=; dk_cookie=; PPU=UID=73628606302481&UN=vuo713rso&TT=8789bd0d7c4015f4319c588afc613894&PBODY=Ul-FYCpolXHAFDzOx6R7_YnnaMqx7JpMVc6IfxBtnEcGZwK80JQo5bnfji40PidUK-sMTxCy6o4mlc5OgSeYef8hNYPayvWlMRsH3ZT2rASgfs2FFGBKXQ1BOpqiRM0tO4A_Ewr6nBlP_wkoJKPAtj9j9s5tj9ACMycsXoUR330&VER=1&CUID=kSS5K52Ufban0WcpRQlZ3Q; www58com=UserID=73628606302481&UserName=vuo713rso; 58cooper=userid=73628606302481&username=vuo713rso; 58uname=vuo713rso; passportAccount=atype=0&bstate=0; xxzl_cid=07024a00976e4bed88d8282ffea6d455; xxzl_deviceid=5tDyQOBEiDfK8UPqoRKC1Fgr7y4TZzf21VHEDaHpOKRt8lz1mS9M0eQzoVHEbvaH',
    'Connection':'close',
    'Referer':'https://fs.58.com/zhaozu/pve_1092_0/?newSearch=1&key='+sys.argv[1]
}

#连接数据库
db = pymysql.connect(host='rm-wz9mt4j79jrdh0p3zgo.mysql.rds.aliyuncs.com', port=3306, user='gogo198', passwd='Gogo@198', db='shop', charset='utf8')
cursor = db.cursor()

#定义全局的链接数组
link_href = []
titles = []

#商圈信息
city = ''
area2 = ''
town = ''

#openid+of_id(用户搜索表)
openid = sys.argv[2]
of_id = sys.argv[3]

#office_ids
office_ids = ''

#刷到指定次数就停止，通知管理员更换cookie||成功时将值设为0
stop_num = 0

#创建文件
def create_file(file_name,html):
    f = open(file_name, 'w+')
    #  写入文件
    f.write(html)
    # 关闭文件
    f.close()

#读取文件
def read_file(file_name):
    path = file_name
    htmlfile = open(path, 'r') #打开html文件
    htmlhandle = htmlfile.read() #返回整个文件
    soup = BeautifulSoup(htmlhandle, "lxml") #使用Beautifulsoup解析
    return soup

def get_proxies():
    resp = requests.get("http://tiqu.pyhttp.taolop.com/getflowip?count=1&neek=50897&type=1&sep=0&sb=0&ip_si=1&mr=0")
    ip = resp.text
    ip_arr = ip.split(":")
    proxyHost = ip_arr[0]
    proxyPort = ip_arr[1]
    proxyMeta = "http://%(host)s:%(port)s" % {
        "host": proxyHost,
        "port": proxyPort,
    }
    proxies = {
        "http": proxyMeta,
        "https": proxyMeta
    }
    return proxies

#请求方法
def http_get(url,typee,fn):
    global stop_num
    time.sleep(1.5)
    urllib3.disable_warnings()
    
    #增加重连次数
    requests.adapters.DEFAULT_RETRIES = 5
    s = requests.session()
    s.keep_alive = False
    s.headers = headers
    s.verify = False
    s.timeout = 300
    
    try:
        s.proxies = get_proxies()
        page = s.get(url)
        # ,headers=headers,proxies=proxies,verify=False,timeout=300
        #random.randint(5, 10)
    except:
        try:
            s.proxies = get_proxies()
            page = s.get(url)
        except:
            try:
                s.proxies = get_proxies()
                page = s.get(url)
            except:
                try:
                    s.proxies = get_proxies()
                    page = s.get(url)
                except:
                    try:
                        s.proxies = get_proxies()
                        page = s.get(url)
                    except:
                        try:
                            s.proxies = get_proxies()
                            page = s.get(url)
                        except:
                            try:
                                s.proxies = get_proxies()
                                page = s.get(url)
                            except:
                                try:
                                    s.proxies = get_proxies()
                                    page = s.get(url)
                                except:
                                    requests.get('https://decl.gogo198.cn/api/query_business?method=3&ips=5&id='+ str(of_id))
                                    logging.debug(traceback.format_exc())
                                    sys.exit(1)
        
    time.sleep(1)
    html = page.text
    page.close()
    soup = get_soup(fn,html)
    
    if typee == 1:
        #查询页和分页
        try:
            #无查询结果
            noresult = soup.find('p',class_='noresult-tip').get_text()
            print('查无结果:'+str(noresult))
            #通知用户
            requests.get('https://decl.gogo198.cn/api/query_business?method=2&id='+ str(of_id))
            stop_num = 0
            sys.exit(1)
        except:
            #有结果，需判断是否不是正常页面
            try:
                #该页面有数据,返回源文件数据
                lis = soup.find('ul',id='house-list-wrap').find_all('li')
                stop_num = 0
                return soup
            except:
                #如果是错误页面，重新调用此方法，更换ip
                stop_num = stop_num + 1
                if stop_num >= 8:
                    requests.get('https://decl.gogo198.cn/api/query_business?method=3&id='+ str(of_id))
                    sys.exit(1)
                    
                http_get(url,typee,fn)
                # soup = 
                # return soup
                #sys.exit(1)
    elif typee == 2:
        #内页
        try:
            lis = soup.find('div',class_='house-title').find('h1').get_text()
            stop_num = 0
            return soup
        except Exception as e:
            #内页cookie失效或其他原,使用其他ip再爬多次
            stop_num = stop_num + 1
            if stop_num >= 8:
                requests.get('https://decl.gogo198.cn/api/query_business?method=3&id='+ str(of_id))
                sys.exit(1)
                
            http_get(url,2,fn)
            # soup = 
            # return soup
            # print('内页cookie失效或其他原因：'+str(e))
            # requests.get('https://decl.gogo198.cn/api/query_business?method=3&id='+ str(of_id)+'&msg='+'内页cookie失效或其他原因：'+str(e))
            #sys.exit(1)
    
#获取源文件
def get_soup(file_name,html):
    create_file(file_name,html)
    soup = read_file(file_name)
    return soup

#获取所有链接
def send():
    keywords = sys.argv[1]
    #keywords = "千灯湖办公楼"
    #url = 'https://fs.58.com/qiandenglu/zhaozu/?key='+keywords
    url = 'https://fs.58.com/zhaozu/pve_1092_0/?newSearch=1&key='+keywords
    #获取并写入html文件
    #file_name = os.getcwd()+"/../python_code/page_html/page_"+str(random.randint(11111, 99999))+"_"+openid+".html"
    file_name = "/www/wwwroot/gogo/python_code/page_html/page_"+str(random.randint(11111, 99999))+"_"+openid+".html"
    soup = http_get(url,1,file_name)
    
    # with io.open("a.html", "w", encoding="utf-8") as f:
    #     f.write(html)
    
    #获取所有a链接
    lis = soup.find('ul',id='house-list-wrap').find_all('li')
    for li in lis:
        link_href.append(li.find('a').get('href'))
        titles.append(li.find('a').find('div',class_='list-info').find('span',class_='title_des').get_text())
        
    #获取商圈信息
    city = soup.find('div',class_='nav-top-bar').find('a').get_text()
    city = city.strip()
    area2 = soup.find('dd',id='filter-area-container').find('div',class_='area-sub-title').find('a',class_='selected').get_text()
    area2 = area2.strip()
    try:
        town = soup.find('div',class_='area-sub-content').find('a',class_='selected').get_text()
        town = town.strip()
    except:
        town = ''
        
    #获取下一分页
    try:
        next_page = soup.find('div',class_='pager').find('a',attrs={"class":'next'}).get('href')
        next_page_info(next_page,file_name)
    except:
        #没有下一页就执行爬取内页数据
        file_name2 = "/www/wwwroot/gogo/python_code/innerPage_html/page_"+str(random.randint(11111, 99999))+"_"+openid+".html"
        for index, i in enumerate(link_href):
            get_inner_page(i,city,area2,town,file_name2,titles[index])
        
        #爬完内页后将所有of_id记录在表中，并微信通知用户
        global office_ids
        if office_ids != '':
            try:
                sql = 'update ims_office_search set office_ids = %s where id = %s and openid = %s'
                cursor.execute(sql,(office_ids,of_id,openid))
                db.commit()
                #通知
                requests.get('https://decl.gogo198.cn/api/query_business?method=1&id='+ str(of_id))
                db.close()
                print('completed!!')
            except:
                logging.debug(traceback.format_exc())
                db.rollback()
        
    #requests.get('https://decl.gogo198.cn/api/query_business?method=3&id='+ str(of_id)+'&msg='+'查询页cookie失效或其他原因：'+str(e))
            
#获取下一页的所有链接
def next_page_info(link,file_name):
    soup = http_get(link,1,file_name)
    print('下一页：' + str(link))
    #获取所有内页链接和内页标题，生成数组
    lis = soup.find('ul',id='house-list-wrap').find_all('li')
    for li in lis:
        link_href.append(li.find('a').get('href'))
        titles.append(li.find('a').find('div',class_='list-info').find('span',class_='title_des').get_text())
        
    #获取下一分页
    try:
        #获取成功，有下一页
        next_page = soup.find('div',class_='pager').find('a',attrs={"class":'next'}).get('href')
        next_page_info(next_page,file_name)
    except:
        #获取失败（没有下一页）
        #没有下一页就执行爬取内页数据
        file_name2 = "/www/wwwroot/gogo/python_code/innerPage_html/page_"+str(random.randint(11111, 99999))+"_"+openid+".html"
        for indexs,i in link_href:
            get_inner_page(i,city,area2,town,file_name2,titles[indexs])
        
        #爬完内页后将所有of_id记录在表中，并微信通知用户
        global office_ids
        if office_ids != '':
            try:
                sql = 'update ims_office_search set office_ids = %s where id = %s and openid = %s'
                cursor.execute(sql,(office_ids,of_id,openid))
                db.commit()
                #通知
                requests.get('https://decl.gogo198.cn/api/query_business?method=1&id='+ str(of_id))
                print('completed!!')
            except:
                logging.debug(traceback.format_exc())
                db.rollback()

#获取内页信息
def get_inner_page(url,city,area2,town,file_name,title2):
    global office_ids
    #先查询数据表有无相同的标题
    sql = "select id from ims_office_building where title LIKE '%%%%%s%%%%'" % str(title2.strip())
    cursor.execute(sql)
    data = cursor.fetchone()
    print('内页：'+str(title2))
    # data = requests.get('https://decl.gogo198.cn/api/query_business?id='+str(of_id)+'&method=4&title='+str(title2))
    #if data.text:
    if data:
        #跳过该程序，不执行
        office_ids = office_ids + str(data[0]) + ','
        # office_ids = office_ids + str(data.text) + ','
        print('This link is existed ' + str(data[0]))
    else:
        #获取并写入html文件
        soup = http_get(url,2,file_name)
        
        #定义插入数据源
        #title
        lis = soup.find('div',class_='house-title').find('h1').get_text()
        # lis = lis.strip()
        # lis = lis.split(" ")
        # title = lis[0]+lis[1]
        # title = title.strip()
        lis = lis.strip()
        lis = lis.split(")")
        title = lis[1]
        title = title.strip()
            
        #价格
        try:
            price = soup.find('span',class_='house_basic_title_money_num_second').get_text()
            price = price.strip()
            price = price.split(" ")
            price = price[0]
        except:
            try:
                price = soup.find('span',class_='house_basic_title_money_num').get_text()
                price2 = soup.find('span',class_='house_basic_title_money_unit').get_text()
                price = price.strip()
                price2 = price2.strip()
                price = price + price2
            except:
                price = soup.find('p',class_='house_basic_title_money').get_text()
                    
        #建筑面积
        area = soup.find('p',class_='house_basic_title_info').find('span',class_='up').get_text()
            
        #推荐工位数
        staff = soup.find('p',class_='house_basic_title_info').find_all('span',class_='up')
        staff = staff[1].get_text()
            
        #装修程度
        decoration = soup.find('p',class_='house_basic_title_info').find_all('span',class_='up')
        decoration = decoration[2].get_text()
            
        #楼盘名称
        building_name = soup.find('div',class_='house_basic_title_info_2').find('a',class_='loupan').get_text()
            
        #楼盘地址
        address = soup.find('div',class_='house_basic_title_info_2').find('span',class_='address').get_text()
            
        #图片、视频列表
        pic_list2 = soup.find('div',class_='general-tupian').find_all('img')
        pic_list = ''
        for pi in pic_list2:
            pic_list = pic_list + pi.get('src') + ','
        
        #销售名称
        seller_name = soup.find('span',class_='name-text').get_text()
            
        #描述
        try:
            desc = soup.find('div',id='generalSound')
        except:
            desc = soup.find('div',class_='general-item-wrap')
            
        #配套信息
        mating2 = soup.find_all('li',class_='peitao-on')
        mating = ''
        for ma in mating2:
            mating = mating + ma.get_text() + ','
            
        #付款+起租期+性质+类型+楼层+使用率+注册+分割+物业费
        generate = soup.find('div',id='intro').find('ul',class_='general-item-wrap')
            
        #代理单位名称+商圈信息
        try:
            wuye_name = soup.find('p',class_='poster-company-4').get_text()
        except:
            wuye_name = '个人'
            
        #插入数据
        try:
            desc = json.dumps(str(desc))
            generate = json.dumps(str(generate))
            
            # pay,start_rent,nature,type2,floor,use,register,split,str(wuye_fee)
            try:
                sql = 'insert into ims_office_building (title,price,area,staff,decoration,building_name,address,pic_list,seller_name,descs,mating,intro,wuye_name,city,area2,town,link) values (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)'
                cursor.execute(sql,(title,str(price),area,staff,decoration,building_name,address,str(pic_list),seller_name,desc,mating,generate,wuye_name,city,area2,town,url))
                #记录office_ids
                office_ids = office_ids + str(db.insert_id()) + ','
                
                db.commit()
            except Exception as e:
                print('数据重复，不插入! ' + str(e))
        except Exception as e:
            logging.debug(traceback.format_exc())
            db.rollback()
            print('内页错误'+str(e))
    
if __name__ == '__main__':
    send()