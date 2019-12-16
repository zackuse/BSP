#!/bin/sh

echo "打包后台"
if [ ! -d ./out ]; then
    #statements
    # echo "not exist"
    mkdir -p ./out
fi

a=$1

cur=`pwd`

rm -rf ./phpadminbin
mkdir -p ./phpadminbin


cp -rf ../admin/* ./phpadminbin/
cp -f $cur/config.php ./phpadminbin/application
cp -f $cur/database.php ./phpadminbin/application
tar -czvf ./out/phpadminbin_$a.tar.gz ./phpadminbin
rm -rf phpadminbin


