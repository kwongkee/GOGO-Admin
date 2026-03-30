package main

import (
	"fmt"
	"strings"
	"net/http"
	"io/ioutil"
	"log"
	"flag"
)


func main() {
	var err error
	//url := "http://10.0.0.184:8077/api/v1.0/OpenApi/GetParkingInfo"
	url := flag.String("url","","")
	dat :=flag.String("data","","")
	flag.Parse()
	fmt.Println(url)
	fmt.Println(dat)
	payload := strings.NewReader(*dat)
	req, err := http.NewRequest("POST", *url, payload)
	if err !=nil{
		log.Fatal(err)
	}
	req.Header.Add("Content-Type", "application/json;charset=utf-8")
	req.Header.Add("user", "ljmb001")
	//req.Header.Add("pwd", "ljmb@@@123")
	fmt.Println("sending request....")
	res, _ := http.DefaultClient.Do(req)
	defer res.Body.Close()
	fmt.Println("reading body")
	body, err := ioutil.ReadAll(res.Body)
	if err !=nil{
	  log.Fatal(err)
	}
	fmt.Println(res)
	fmt.Println(string(body))

}
