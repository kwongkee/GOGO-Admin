package main

import (
	"flag"
	"github.com/jlaffaye/ftp"
	"io/ioutil"
	"log"
	"os"
	"time"
)

var Days string

func main() {
	ftp, err := ftp.Connect("183.62.232.62:21")
	checkErr(err)
	err = ftp.Login("qs", "ylink!1qaz")
	checkErr(err)
	nTime := time.Now()
	yTime := nTime.AddDate(0, 0, -1)
	Days = yTime.Format("20060102")
	err = os.Mkdir(Days, os.ModePerm)
	path := flag.String("path", "", "")
	flag.Parse()
	file, _ := os.Create(*path + Days + "/7000000000000049" + Days + "01.TXT")
	res, err := ftp.Retr("./checkFile/access/7000000000000049/" + Days + "/7000000000000049" + Days + "01.TXT")
	if err != nil {
		log.Fatal("打开文件失败", err)
	}
	buf, err := ioutil.ReadAll(res)
	if err != nil {
		log.Fatal("read file fail", err)
	}
	file.Write(buf)
	file.Close()
	res.Close()
	ftp.Logout()
	ftp.Quit()
}

func checkErr(err error) {
	if err != nil {
		panic(err)
	}
}
