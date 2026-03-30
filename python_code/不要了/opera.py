# encoding: utf-8
#selenium操作
from selenium import webdriver
from selenium.webdriver.common.by import By
import time

driver = webdriver.Firefox()
driver.get("https://member.hotbuyhk.com/Member/Login")
time.sleep(1)  # 打开网站，并让它睡1s，避免渲染未完成就进行下一步操作

#输入手机号码和密码
tel = '13119893380'
pwd = '123456'
driver.find_element('id','username').send_keys(tel)
driver.find_element('id','password').send_keys(pwd)
time.sleep(1)
driver.find_element(By.CLASS_NAME,'mf_submit').click()
time.sleep(1)
#操作
driver.find_element(By.CLASS_NAME,'layui-layer-close').click()
time.sleep(1)
driver.find_element(By.CLASS_NAME,'pick-road').find_element(By.XPATH,'//a[@href="/Member/MyPack"]').click()
time.sleep(1)
driver.find_element(By.NAME,'kd_billcode').send_keys('12345454511')
driver.find_element(By.NAME,'goods').send_keys('货物名称')
driver.find_element(By.NAME,'goods_number').send_keys('2')
driver.find_element(By.NAME,'goods_memo').send_keys('备注')
driver.find_element(By.CLASS_NAME,'btn_submit').click()
#关闭alert弹框
driver.switch_to.alert.accept()