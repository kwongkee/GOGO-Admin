#-*- coding=utf-8 -*-
import importlib #提供import语句
import sys
import time #提供延时功能
# import xlrd #excel文件读取
import os
# import xlwt #excel文件写入

# from xlutils.copy import copy #excel文件复制
from selenium import webdriver #浏览器操作库

# importlib.reload(sys)


#伪装成浏览器，防止被识破
option = webdriver.ChromeOptions()
option.add_argument('--user-agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.146 Safari/537.36"')
driver = webdriver.Chrome(options=option)

#打开登录页面
driver.get('https://www.qichacha.com/user_login')
time.sleep(20)#等待20s，完成手动登录操作
# 手动登录操作


#从excel获取查询单位
# worksheet = xlrd.open_workbook(u'test.xls')
# sheet1 = worksheet.sheet_by_name("CD类未拓展客户清单")#excel有多个sheet，检索该名字的sheet表格
# rows = sheet1.nrows # 获取行数
inc_list = ['佛山市钜铭商务资讯服务有限公司']
# for i in range(1,rows) :
#     data = sheet1.cell_value(i, 1) # 取第2列数据
#     inc_list.append(data)
# print(inc_list)
inc_len = len(inc_list)

#写回数据
# writesheet1 = copy(worksheet)# 这里复制了一个excel，没有直接写回最初的文件。
# writesheet2 = writesheet1.get_sheet(1)#同样获得第一个sheet
# style = xlwt.easyxf('font:height 240, color-index red, bold on;align: wrap on, vert centre, horiz center');

#开启爬虫
for i in range(inc_len):
    txt = inc_list[i]
    time.sleep(1)

    if (i==0):
        #向搜索框注入文字
        driver.find_element_by_id('searchkey').send_keys(txt)
        #单击搜索按钮
        srh_btn = driver.find_element_by_xpath('//*[@id="indexSearchForm"]/div/span/input')
        srh_btn.click()
    else:
        #清楚搜索框内容
        driver.find_element_by_id('headerKey').clear()
        # 向搜索框注入下一个公司地址
        driver.find_element_by_id('headerKey').send_keys(txt)
        #搜索按钮
        srh_btn = driver.find_element_by_xpath('/html/body/header/div/form/div/div/span/button')
        srh_btn.click()
    try:
        # 获取网页地址，进入
        inner = driver.find_element_by_xpath('//*[@id="search-result"]/tr[1]/td[3]/a').get_attribute("href")
        driver.get(inner)
        time.sleep(2)
        # 弹出框按钮
        try:
            try:
                srh_btn = driver.find_element_by_xpath('//*[@id="firstepdadModal"]/div/div/div[2]/button')
                srh_btn.click()
            except:
                srh_btn = driver.find_element_by_xpath('//*[@id="firstcaseModal"]/div/div/div[2]/button')
                srh_btn.click()
        except:
            pass
        try:
            # 转到企业发展
            tag = driver.find_element_by_xpath('//*[@id="report_title"]')
            tag.click()
            time.sleep(2)
            #获取首个企业信用码
            try:
                credit_code = driver.find_element_by_xpath('//*[@id="0"]/table[1]/tbody/tr[1]/td[4]').text
            except:
                credit_code='none'
        except:
            credit_code = 'none'
    except:
        credit_code = 'none'
    print(credit_code)
    # writesheet2.write(i+1, 15, credit_code)  # 第16列数据sheet1.write(i, j, data[j])
    # writesheet1.save(u'test2.xls')

driver.close()
