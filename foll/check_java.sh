#!/bin/bash
check=`pgrep java`
if [ -n "$check" ]; then
        exit
else
        date=$(date +"%Y-%m-%d %H:%M:%S")

        cd /www/web/default/foll/javaBridge&&java -jar JavaBridge.jar SERVLET_LOCAL:8881 &

        echo 'java at ' $date

fi
