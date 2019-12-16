#!/bin/bash

d=`date +%Y%m%d`

echo $d
echo "remove file"

for file in log/${d}/*.log
do
    echo $file
    cat /dev/null > $file
done

php ../../QYS/src/QYS/QYS.php -p `pwd` -c conf/debug.php
